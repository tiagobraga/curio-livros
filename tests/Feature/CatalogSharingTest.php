<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\User;
use App\Models\Library;
use App\Models\LocalCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_central_item_automatically_generates_six_character_share_code(): void
    {
        $item = CentralItem::create([
            'title' => 'O Senhor dos Anéis',
            'isbn' => '9788595086357',
            'item_type' => 'book',
            'metadata' => [
                'author' => 'J.R.R. Tolkien',
                'publisher' => 'HarperCollins',
                'publication_year' => '2019',
                'description' => 'Um grande clássico da fantasia.',
                'subjects' => ['Fantasia', 'Aventura'],
                'isbn' => '9788595086357'
            ]
        ]);

        $this->assertNotEmpty($item->share_code);
        $this->assertEquals(6, strlen($item->share_code));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{6}$/', $item->share_code);
    }

    public function test_guest_can_access_shared_book_page_via_short_url(): void
    {
        $item = CentralItem::create([
            'title' => 'O Senhor dos Anéis',
            'isbn' => '9788595086357',
            'item_type' => 'book',
            'metadata' => [
                'author' => 'J.R.R. Tolkien',
                'publisher' => 'HarperCollins',
                'publication_year' => '2019',
                'description' => 'Um grande clássico da fantasia.',
                'subjects' => ['Fantasia', 'Aventura'],
                'isbn' => '9788595086357'
            ]
        ]);

        $reader = User::factory()->create(['role' => 'reader']);
        \App\Models\BookReview::create([
            'central_item_id' => $item->id,
            'user_id' => $reader->id,
            'rating' => 5,
            'comment' => 'Livro maravilhoso!'
        ]);

        $response = $this->get('/' . $item->share_code);
        $this->assertEquals(1, \App\Models\BookReview::count());
        $this->assertEquals($item->id, \App\Models\BookReview::first()->central_item_id);
        
        $response->assertStatus(200);
        $response->assertSee($item->title);
        $response->assertSee('Entre na comunidade');
        $response->assertDontSee('Exemplares nas Unidades');
        
        // Zotero DC meta verification
        $response->assertSee('meta name="DC.title" content="' . $item->title . '"', false);
        $response->assertSee('meta name="DC.creator" content="J.R.R. Tolkien"', false);
        $response->assertSee('meta name="DC.description" content="Um grande clássico da fantasia."', false);
        
        // Zotero COinS validation
        $response->assertSee('class="Z3988"', false);

        // Zotero Alternate HTML Reviews link validation should be removed
        $response->assertDontSee('link rel="alternate" type="text/html"', false);

        // Canonical link validation (forces Zotero to save the short URL as the main source and snapshot target)
        $shortUrl = route('catalog.shared', $item->share_code);
        $response->assertSee('<link rel="canonical" href="' . $shortUrl . '" />', false);

        // Open Graph & Twitter Card validation
        $response->assertSee('<meta property="og:title" content="' . $item->title . '" />', false);
        $response->assertSee('<meta property="og:type" content="book" />', false);
        $response->assertSee('<meta property="og:url" content="' . $shortUrl . '" />', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image" />', false);
        $response->assertSee('<meta name="twitter:title" content="' . $item->title . '" />', false);
    }

    public function test_invalid_short_url_returns_404(): void
    {
        $response = $this->get('/AAAAAA');
        $response->assertStatus(404);
    }
}
