<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('active')->default(false);
            $table->boolean('admin')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Insert some users
        DB::table('users')->insert(
            [
                [
                    'name' => 'Tomas Marlein',
                    'email' => 'tomasmarlein@eventicks.be',
                    'password' => Hash::make('admin1234'),
                    'active' => true,
                    'admin' => true,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Laar',
                    'email' => 'laar@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Ginderbuiten',
                    'email' => 'ginderbuiten@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Statiestraat',
                    'email' => 'statie@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Boomgaard',
                    'email' => 'boomgaard@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Laken',
                    'email' => 'laken@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Wereld',
                    'email' => 'wereld@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Molen',
                    'email' => 'molen@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Gompelbaan',
                    'email' => 'gompel@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Kolkstraat',
                    'email' => 'kolkstraat@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ],
                [
                    'name' => 'Kassa Bresserdijk',
                    'email' => 'bresserdijk@rozenberglichtstoet.be',
                    'password' => Hash::make('KMDR@1885'),
                    'active' => true,
                    'admin' => false,
                    'email_verified_at' => now(),
                    'created_at' => now()
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
