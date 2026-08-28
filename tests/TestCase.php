<?php

namespace App\Modules\Shop\Tests;

use App\Modules\Shop\ShopServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->afterApplicationCreated(fn () => $this->makeACleanSlate());
        $this->beforeApplicationDestroyed(fn () => $this->makeACleanSlate());

        parent::setUp();
    }

    public function makeACleanSlate(): void
    {
        Artisan::call('view:clear');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            ShopServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('session.driver', 'array');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Stub tables required by FK in orders/subscriptions
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
