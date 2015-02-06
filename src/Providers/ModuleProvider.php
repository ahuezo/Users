<?php
namespace TypiCMS\Modules\Users\Providers;

use App;
use Config;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Lang;
use TypiCMS\Modules\Users\Models\User;
use TypiCMS\Modules\Users\Repositories\SentryUser;
use TypiCMS\Modules\Users\Services\Form\UserForm;
use TypiCMS\Modules\Users\Services\Form\UserFormLaravelValidator;
use TypiCMS\Observers\FileObserver;
use View;

class ModuleProvider extends ServiceProvider
{

    public function boot()
    {
        // Bring in the routes
        require __DIR__ . '/../routes.php';

        // Add dirs
        View::addNamespace('users', __DIR__ . '/../views/');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'users');
        $this->publishes([
            __DIR__ . '/../config/' => config_path('typicms/users'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/../migrations/' => base_path('/database/migrations'),
        ], 'migrations');

        // Add user preferences to Config
        $prefs = App::make('TypiCMS\Modules\Users\Repositories\UserInterface')->getPreferences();
        Config::set('current_user', $prefs);

        // Observers
        User::observe(new FileObserver);
    }

    public function register()
    {

        $app = $this->app;

        /**
         * Sidebar view composer
         */
        $app->view->composer('core::admin._sidebar', 'TypiCMS\Modules\Users\Composers\SideBarViewComposer');

        $app->bind('TypiCMS\Modules\Users\Repositories\UserInterface', function (Application $app) {
            return new SentryUser(
                $app['sentry']
            );
        });

        $app->bind('TypiCMS\Modules\Users\Services\Form\UserForm', function (Application $app) {
            return new UserForm(
                new UserFormLaravelValidator($app['validator']),
                $app->make('TypiCMS\Modules\Users\Repositories\UserInterface')
            );
        });
    }
}
