<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\User;
use App\Models\LocalCopy;
use App\Models\Library;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class CatalogUnavailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendant_can_mark_own_library_copy_as_unavailable(): void
    {
        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create([
            'name' => 'Biblioteca Leste',
            'code' => 'BL01',
            'is_active' => true
        ]);
        $attendant->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'subjects' => []
            ]
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'call_number' => '869 M149d',
            'shelf_location' => 'Corredor B',
            'quantity' => 5,
            'status' => 'available'
        ]);

        $response = $this->actingAs($attendant)->put(route('catalog.update', $book->id), [
            'title' => 'Dom Casmurro',
            'author' => 'Machado de Assis',
            'copies' => [
                $copy->id => [
                    'library_id' => $library->id,
                    'call_number' => '869 M149d',
                    'shelf_location' => 'Corredor B',
                    'quantity' => 5,
                    'is_available' => 0
                ]
            ]
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('local_copies', [
            'id' => $copy->id,
            'status' => 'unavailable'
        ]);
    }

    public function test_attendant_ignoring_copy_status_change_for_other_library_without_aborting(): void
    {
        $attendant = User::factory()->create(['role' => 'attendant']);
        $libraryOwn = Library::create([
            'name' => 'Biblioteca Leste',
            'code' => 'BL01',
            'is_active' => true
        ]);
        $attendant->libraries()->attach($libraryOwn->id);

        $libraryOther = Library::create([
            'name' => 'Biblioteca Oeste',
            'code' => 'BO01',
            'is_active' => true
        ]);

        $book = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'subjects' => []
            ]
        ]);

        $copyOther = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryOther->id,
            'call_number' => '869 M149d',
            'shelf_location' => 'Corredor B',
            'quantity' => 3,
            'status' => 'available'
        ]);

        $response = $this->actingAs($attendant)->put(route('catalog.update', $book->id), [
            'title' => 'Dom Casmurro',
            'author' => 'Machado de Assis',
            'copies' => [
                $copyOther->id => [
                    'library_id' => $libraryOther->id,
                    'call_number' => '869 M149d',
                    'shelf_location' => 'Corredor B',
                    'quantity' => 3,
                    'is_available' => 0
                ]
            ]
        ]);

        // Should redirect instead of 403, but not update the database
        $response->assertRedirect();
        
        $this->assertDatabaseHas('local_copies', [
            'id' => $copyOther->id,
            'status' => 'available'
        ]);
    }

    public function test_cannot_borrow_unavailable_copy(): void
    {
        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Leste', 'code' => 'L01', 'is_active' => true]);
        $attendant->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Livro Teste',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro Teste', 'subjects' => []]
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 2,
            'status' => 'unavailable'
        ]);

        $response = $this->actingAs($attendant)->post(route('loans.store'), [
            'user_id' => $reader->id,
            'local_copy_id' => $copy->id,
            'due_date' => Carbon::tomorrow()->toDateString()
        ]);

        $response->assertSessionHas('error', 'Este exemplar está marcado como indisponível na unidade no momento.');
    }

    public function test_borrowing_last_copy_changes_status_to_borrowed(): void
    {
        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Leste', 'code' => 'L01', 'is_active' => true]);
        $attendant->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Livro Teste',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro Teste', 'subjects' => []]
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 1,
            'status' => 'available'
        ]);

        $response = $this->actingAs($attendant)->post(route('loans.store'), [
            'user_id' => $reader->id,
            'local_copy_id' => $copy->id,
            'due_date' => Carbon::tomorrow()->toDateString()
        ]);

        $response->assertRedirect();
        
        $copy->refresh();
        $this->assertEquals('borrowed', $copy->status);
    }

    public function test_returning_copy_restores_status_to_available(): void
    {
        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Leste', 'code' => 'L01', 'is_active' => true]);
        $attendant->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Livro Teste',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro Teste', 'subjects' => []]
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 1,
            'status' => 'borrowed'
        ]);

        $loan = Loan::create([
            'local_copy_id' => $copy->id,
            'user_id' => $reader->id,
            'attendant_id' => $attendant->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::tomorrow(),
            'status' => 'borrowed'
        ]);

        $response = $this->actingAs($attendant)->post(route('loans.return', $loan->id));

        $response->assertRedirect();
        
        $copy->refresh();
        $this->assertEquals('available', $copy->status);
    }
}
