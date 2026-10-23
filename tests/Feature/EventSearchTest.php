<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventSearchTest extends TestCase
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
        Schema::connection('tickets_mysql')->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisation_id');
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('postcode')->nullable();
            $table->string('plaats')->nullable();
            $table->dateTime('start')->nullable();
            $table->softDeletes();
        });
        Schema::connection('tickets_mysql')->create('orderlines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->boolean('scanned')->default(false);
        });
    }

    public function test_ajax_search_filters_events_within_the_accessible_organisation_and_keeps_scan_counts(): void
    {
        $connection = DB::connection('tickets_mysql');
        $connection->table('users')->insert(['id' => 1, 'name' => 'Scanner', 'email' => 'scanner@example.test']);
        $connection->table('organisations')->insert([
            ['id' => 1, 'name' => 'Mol Events', 'slug' => 'mol-events-vzw'],
            ['id' => 2, 'name' => 'Other Events', 'slug' => 'other-events'],
        ]);
        $connection->table('organisation_users')->insert(['organisation_id' => 1, 'user_id' => 1]);
        $connection->table('events')->insert([
            ['id' => 1, 'organisation_id' => 1, 'name' => 'Zomerfeest', 'slug' => 'zomerfeest'],
            ['id' => 2, 'organisation_id' => 1, 'name' => 'Winterfeest', 'slug' => 'winterfeest'],
            ['id' => 3, 'organisation_id' => 2, 'name' => 'Zomerfeest Privé', 'slug' => 'zomerfeest-prive'],
        ]);
        $connection->table('orderlines')->insert([
            ['event_id' => 1, 'scanned' => true],
            ['event_id' => 1, 'scanned' => false],
        ]);

        $this->actingAs(User::findOrFail(1));

        $this->get(route('organisation.overview', 'mol-events-vzw'))
            ->assertOk()
            ->assertSee('Zoek een evenement');

        $response = $this->getJson(route('organisation.events.search', ['slug' => 'mol-events-vzw', 'q' => 'zomer']))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSee('Zomerfeest')
            ->assertDontSee('Winterfeest')
            ->assertDontSee('Zomerfeest Privé');

        $this->assertStringContainsString('1 / 2', $response->json('html'));
        $this->assertStringContainsString('50%', $response->json('html'));

        $this->getJson(route('organisation.events.search', ['slug' => 'other-events', 'q' => 'zomer']))
            ->assertForbidden();
    }

    public function test_ajax_search_requires_authentication(): void
    {
        $this->getJson(route('organisation.events.search', ['slug' => 'mol-events-vzw', 'q' => 'zomer']))
            ->assertUnauthorized();
    }
}
