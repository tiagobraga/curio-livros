<?php

namespace Tests\Feature;

use App\Mail\BookBorrowedMail;
use App\Mail\BookReturnedMail;
use App\Mail\UserCreatedMail;
use App\Mail\UserPasswordChangedMail;
use App\Mail\UserUpdatedMail;
use App\Models\CentralItem;
use App\Models\Library;
use App\Models\LocalCopy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_user_sends_user_created_mail(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Teste',
            'code' => 'BT01',
            'is_active' => true
        ]);
        $manager->libraries()->attach($library->id);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Novo Usuario Teste',
            'email' => 'novouser@teste.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'role' => 'user',
            'library_ids' => [$library->id],
        ]);

        $response->assertRedirect();
        Mail::assertSent(UserCreatedMail::class, function ($mail) {
            return $mail->hasTo('novouser@teste.com') && $mail->password === 'senha123456';
        });
    }

    public function test_updating_user_sends_user_updated_mail(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Teste',
            'code' => 'BT01',
            'is_active' => true
        ]);
        $manager->libraries()->attach($library->id);

        $user = User::factory()->create(['role' => 'user']);
        $user->libraries()->attach($library->id);

        $response = $this->actingAs($manager)->put(route('users.update', $user->id), [
            'name' => 'Usuario Nome Alterado',
            'email' => $user->email,
            'role' => 'user',
            'library_ids' => [$library->id],
        ]);

        $response->assertRedirect();
        Mail::assertSent(UserUpdatedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->user->name === 'Usuario Nome Alterado';
        });
    }

    public function test_updating_user_password_sends_password_changed_mail(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Teste',
            'code' => 'BT01',
            'is_active' => true
        ]);
        $manager->libraries()->attach($library->id);

        $user = User::factory()->create(['role' => 'user']);
        $user->libraries()->attach($library->id);

        $response = $this->actingAs($manager)->put(route('users.update-password', $user->id), [
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ]);

        $response->assertRedirect();
        Mail::assertSent(UserPasswordChangedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->password === 'novasenha123';
        });
    }

    public function test_creating_loan_sends_book_borrowed_mail(): void
    {
        Mail::fake();

        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create([
            'name' => 'Biblioteca Teste',
            'code' => 'BT01',
            'is_active' => true
        ]);
        $attendant->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Livro Teste Email',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro Teste Email']
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 2,
            'status' => 'available',
        ]);

        $response = $this->actingAs($attendant)->post(route('loans.store'), [
            'user_id' => $reader->id,
            'local_copy_id' => $copy->id,
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        $response->assertRedirect();
        Mail::assertSent(BookBorrowedMail::class, function ($mail) use ($reader) {
            return $mail->hasTo($reader->email);
        });
    }

    public function test_returning_loan_sends_book_returned_mail(): void
    {
        Mail::fake();

        $attendant = User::factory()->create(['role' => 'attendant']);
        $library = Library::create([
            'name' => 'Biblioteca Teste',
            'code' => 'BT01',
            'is_active' => true
        ]);
        $attendant->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($library->id);

        $book = CentralItem::create([
            'title' => 'Livro Teste Email',
            'isbn' => '9788535902777',
            'item_type' => 'book',
            'metadata' => ['title' => 'Livro Teste Email']
        ]);

        $copy = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 2,
            'status' => 'available',
        ]);

        $loan = Loan::create([
            'local_copy_id' => $copy->id,
            'user_id' => $reader->id,
            'attendant_id' => $attendant->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($attendant)->post(route('loans.return', $loan->id));

        $response->assertRedirect();
        Mail::assertSent(BookReturnedMail::class, function ($mail) use ($reader) {
            return $mail->hasTo($reader->email);
        });
    }
}
