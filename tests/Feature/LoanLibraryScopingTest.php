<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\Library;
use App\Models\Loan;
use App\Models\LocalCopy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanLibraryScopingTest extends TestCase
{
    use RefreshDatabase;

    private Library $libraryManaged;
    private Library $libraryAttended;
    private Library $libraryReaderOnly;
    private User $staffUser;
    private User $curatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Cria três bibliotecas ativas
        $this->libraryManaged = Library::create([
            'name' => 'Biblioteca Gestão Alpha',
            'code' => 'BGA01',
            'is_active' => true,
        ]);

        $this->libraryAttended = Library::create([
            'name' => 'Biblioteca Atendimento Beta',
            'code' => 'BAB02',
            'is_active' => true,
        ]);

        $this->libraryReaderOnly = Library::create([
            'name' => 'Biblioteca Apenas Leitor Gamma',
            'code' => 'BLG03',
            'is_active' => true,
        ]);

        // 2. Cria usuário que é Gestor na Alpha, Atendente na Beta e Leitor na Gamma
        $this->staffUser = User::factory()->create([
            'name' => 'Funcionário Misto',
            'role' => 'manager',
        ]);
        $this->staffUser->libraries()->attach($this->libraryManaged->id, ['role' => 'manager']);
        $this->staffUser->libraries()->attach($this->libraryAttended->id, ['role' => 'attendant']);
        $this->staffUser->libraries()->attach($this->libraryReaderOnly->id, ['role' => 'user']);

        // 3. Cria Curador Global
        $this->curatorUser = User::factory()->create([
            'name' => 'Curador Geral',
            'role' => 'curator',
        ]);
    }

    public function test_loan_filter_options_show_only_manager_or_attendant_libraries_and_not_reader_libraries(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('loans.index'));

        $response->assertStatus(200);

        // Deve conter a unidade onde é gestor e a onde é atendente
        $response->assertSee('Biblioteca Gestão Alpha');
        $response->assertSee('Biblioteca Atendimento Beta');

        // NÃO deve conter como opção de filtro a unidade onde é apenas leitor
        $response->assertDontSee('Biblioteca Apenas Leitor Gamma');
    }

    public function test_loan_list_does_not_display_loans_from_reader_only_library(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $reader->libraries()->attach($this->libraryManaged->id, ['role' => 'user']);
        $reader->libraries()->attach($this->libraryReaderOnly->id, ['role' => 'user']);

        $book = CentralItem::create([
            'title' => 'Livro de Circulação',
            'item_type' => 'book',
        ]);

        $copyManaged = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $this->libraryManaged->id,
            'call_number' => 'CALL-MNG',
            'quantity' => 2,
            'status' => 'borrowed',
        ]);

        $copyReaderOnly = LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $this->libraryReaderOnly->id,
            'call_number' => 'CALL-RDR',
            'quantity' => 2,
            'status' => 'borrowed',
        ]);

        // Empréstimo na biblioteca gerenciada
        $loanManaged = Loan::create([
            'local_copy_id' => $copyManaged->id,
            'user_id' => $reader->id,
            'attendant_id' => $this->staffUser->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(7),
            'status' => 'borrowed',
        ]);

        // Empréstimo na biblioteca onde o staff é apenas leitor
        $otherAttendant = User::factory()->create(['role' => 'attendant']);
        $loanReaderOnly = Loan::create([
            'local_copy_id' => $copyReaderOnly->id,
            'user_id' => $reader->id,
            'attendant_id' => $otherAttendant->id,
            'loan_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(7),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($this->staffUser)->get(route('loans.index'));
        $response->assertStatus(200);

        // O exemplar da biblioteca gerenciada deve ser listado
        $response->assertSee('CALL-MNG');

        // O exemplar da biblioteca onde ele é apenas leitor NÃO deve ser listado
        $response->assertDontSee('CALL-RDR');
    }

    public function test_curator_sees_all_libraries_in_loan_filter(): void
    {
        $response = $this->actingAs($this->curatorUser)->get(route('loans.index'));

        $response->assertStatus(200);
        $response->assertSee('Biblioteca Gestão Alpha');
        $response->assertSee('Biblioteca Atendimento Beta');
        $response->assertSee('Biblioteca Apenas Leitor Gamma');
    }

    public function test_transactions_page_filter_options_exclude_reader_only_libraries(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Biblioteca Gestão Alpha');
        $response->assertSee('Biblioteca Atendimento Beta');
        $response->assertDontSee('Biblioteca Apenas Leitor Gamma');
    }
}
