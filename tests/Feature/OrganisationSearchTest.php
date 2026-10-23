<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganisationSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.tickets_mysql', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        DB::purge('tickets_mysql');

        Schema::connection('tickets_mysql')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->boolean('admin')->default(false);
        });
        Schema::connection('tickets_mysql')->create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->softDeletes();
        });
        Schema::connection('tickets_mysql')->create('organisation_users', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('user_id');
        });
    }

    public function test_search_only_returns_matching_organisations_the_user_can_access(): void
    {
        $connection = DB::connection('tickets_mysql');
        $connection->table('users')->insert(['id' => 1, 'name' => 'Scanner', 'email' => 'scanner@example.test']);
        $connection->table('organisations')->insert([
            ['id' => 1, 'name' => 'Muziekhuis Noord', 'slug' => 'muziekhuis-noord'],
            ['id' => 2, 'name' => 'Theater Zuid', 'slug' => 'theater-zuid'],
            ['id' => 3, 'name' => 'Muziekhuis Privé', 'slug' => 'muziekhuis-prive'],
        ]);
        $connection->table('organisation_users')->insert([
            ['organisation_id' => 1, 'user_id' => 1],
            ['organisation_id' => 2, 'user_id' => 1],
        ]);

        $this->actingAs(User::findOrFail(1));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Muziekhuis Noord')
            ->assertDontSee('Muziekhuis Privé');

        $this->getJson(route('organisations.search', ['q' => 'muziek']))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSee('Muziekhuis Noord')
            ->assertDontSee('Muziekhuis Privé')
            ->assertDontSee('Theater Zuid');
    }

    public function test_search_requires_authentication(): void
    {
        $this->getJson(route('organisations.search', ['q' => 'muziek']))->assertUnauthorized();
    }
}
