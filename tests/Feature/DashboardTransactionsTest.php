<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\User;
use App\Models\Loan;
use App\Models\LocalCopy;
use App\Models\Library;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_sees_chart_and_transactions_on_dashboard(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Central',
            'code' => 'BC01',
            'is_active' => true
        ]);
        $manager->libraries()->attach($library->id);

        $response = $this->actingAs($manager)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHasAll([
            'chartLabels',
            'loansChartValues',
            'returnsChartValues',
            'recentTransactions'
        ]);
    }

    public function test_transactions_page_accessible_only_by_staff_users(): void
    {
        $reader = User::factory()->create(['role' => 'user']);
        $manager = User::factory()->create(['role' => 'manager']);

        // Reader gets 403
        $response1 = $this->actingAs($reader)->get(route('transactions.index'));
        $response1->assertStatus(403);

        // Manager gets 200
        $response2 = $this->actingAs($manager)->get(route('transactions.index'));
        $response2->assertStatus(200);
    }
}
