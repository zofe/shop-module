<?php

namespace App\Modules\Shop\Tests;

use App\Modules\Shop\ShopServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Zofe\Rapyd\RapydServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('view:clear');
        \Illuminate\Support\Facades\Storage::fake('public'); // the seeder publishes demo images
    }

    protected function getPackageProviders($app)
    {
        return [
            RapydServiceProvider::class,
            LivewireServiceProvider::class,
            \Lab404\Impersonate\ImpersonateServiceProvider::class,
            ShopServiceProvider::class,
        ];
    }

    /** The two pages rpd:make:setup gives a host app; the shop's breadcrumbs start from them. */
    protected function defineRoutes($router)
    {
        $router->middleware('web')->get('/', fn () => 'home')->name('home')
            ->crumbs(fn ($crumbs) => $crumbs->push('Home', route('home')));
        $router->middleware('web')->get('/admin', fn () => 'admin')->name('admin.home')
            ->crumbs(fn ($crumbs) => $crumbs->push('Admin', route('admin.home')));
    }

    protected function defineDatabaseMigrations(): void
    {
        // The users table of the host app (uuid keys, as rpd:install --uuid-users creates them).
        $this->app['migrator']->path(__DIR__ . '/database/migrations');
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('auth.providers.users.model', Models\User::class);
        $app['config']->set('rapyd.search.models', []);
    }
}
