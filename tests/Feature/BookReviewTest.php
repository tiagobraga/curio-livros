<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\User;
use App\Models\BookReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_submit_review_with_valid_rating_and_comment(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $book = CentralItem::create([
            'title' => 'Livro de Teste',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Livro de Teste',
                'author' => 'Autor Teste',
                'subjects' => ['Ficção']
            ]
        ]);

        $response = $this->actingAs($reader)->post(route('catalog.reviews.store', $book->id), [
            'rating' => 4,
            'comment' => 'Ótimo livro, gostei bastante da leitura.',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('book_reviews', [
            'user_id' => $reader->id,
            'central_item_id' => $book->id,
            'rating' => 4,
            'comment' => 'Ótimo livro, gostei bastante da leitura.',
        ]);
    }

    public function test_reader_comment_filters_swear_words(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $book = CentralItem::create([
            'title' => 'Livro de Teste',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Livro de Teste',
                'author' => 'Autor Teste',
                'subjects' => []
            ]
        ]);

        // "puta" and "caralho" are swear words and should be replaced by "***"
        $response = $this->actingAs($reader)->post(route('catalog.reviews.store', $book->id), [
            'rating' => 3,
            'comment' => 'Que livro puta e caralho de bom!',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('book_reviews', [
            'user_id' => $reader->id,
            'central_item_id' => $book->id,
            'rating' => 3,
            'comment' => 'Que livro *** e *** de bom!',
        ]);
    }

    public function test_reader_comment_validation_limit_of_1000_characters(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $book = CentralItem::create([
            'title' => 'Livro de Teste',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro de Teste']
        ]);

        // Comment with 1001 characters
        $longComment = str_repeat('a', 1001);

        $response = $this->actingAs($reader)->post(route('catalog.reviews.store', $book->id), [
            'rating' => 5,
            'comment' => $longComment,
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseMissing('book_reviews', [
            'user_id' => $reader->id,
            'central_item_id' => $book->id,
        ]);
    }

    public function test_curator_or_admin_can_delete_comments_but_manager_cannot(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $manager = User::factory()->create(['role' => 'manager']);
        $curator = User::factory()->create(['role' => 'curator']);
        $book = CentralItem::create([
            'title' => 'Livro de Teste',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro de Teste']
        ]);

        $review = BookReview::create([
            'user_id' => $reader->id,
            'central_item_id' => $book->id,
            'rating' => 5,
            'comment' => 'Um comentário qualquer.',
        ]);

        // Attempting to delete review with another reader should fail
        $otherReader = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($otherReader)->delete(route('catalog.reviews.destroy', $review->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('book_reviews', ['id' => $review->id, 'deleted_at' => null]);

        // Attempting with manager should fail
        $response2 = $this->actingAs($manager)->delete(route('catalog.reviews.destroy', $review->id));
        $response2->assertStatus(403);
        $this->assertDatabaseHas('book_reviews', ['id' => $review->id, 'deleted_at' => null]);

        // Attempting with curator should succeed
        $response3 = $this->actingAs($curator)->delete(route('catalog.reviews.destroy', $review->id));
        $response3->assertRedirect();
        
        // Assert review is soft deleted
        $this->assertSoftDeleted('book_reviews', ['id' => $review->id]);
    }

    public function test_reviews_are_paginated_on_book_details_page(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $book = CentralItem::create([
            'title' => 'Livro de Teste',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Livro de Teste',
                'author' => 'Autor Teste',
                'description' => 'Minha Sinopse',
                'subjects' => []
            ]
        ]);

        // Create 11 reviews
        for ($i = 0; $i < 11; $i++) {
            $user = User::factory()->create(['role' => 'user']);
            BookReview::create([
                'user_id' => $user->id,
                'central_item_id' => $book->id,
                'rating' => 4,
                'comment' => "Review number {$i}",
            ]);
        }

        $response = $this->actingAs($reader)->get(route('catalog.show', $book->id));
        $response->assertStatus(200);
        $response->assertViewHas('reviews');
        
        $reviewsInView = $response->viewData('reviews');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $reviewsInView);
        $this->assertEquals(11, $reviewsInView->total());
        $this->assertEquals(10, $reviewsInView->count()); // Page 1 has 10 reviews
    }
}
