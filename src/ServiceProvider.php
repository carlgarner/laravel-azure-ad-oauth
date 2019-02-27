<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Support\Facades\Auth;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Metrogistics\AzureSocialite\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        // $this->app->bind('azure-user', function(){
        //     return new AzureUser(
        //         session('azure_user')
        //     );
        // });
    }

    public function boot()
    {
        // Auth::extend('azure', function(){
        //     dd('test');
        //     return new Authenticate();
        // });

        $this->publishes([
            __DIR__ . '/config/azure-oauth.php' => config_path('azure-oauth.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/azure-oauth.php', 'azure-oauth'
        );

        $this->app['Laravel\Socialite\Contracts\Factory']->extend('azure-oauth', function($app){
            return $app['Laravel\Socialite\Contracts\Factory']->buildProvider(
                'Metrogistics\AzureSocialite\AzureOauthProvider',
                config('azure-oauth.credentials')
            );
        });

        $this->app['router']->group(['middleware' => config('azure-oauth.routes.middleware')], function($router){
            $router->get(config('azure-oauth.routes.login'), 'Metrogistics\AzureSocialite\AuthController@redirectToOauthProvider')->name('oauth.login');
            $router->get(config('azure-oauth.routes.callback'), 'Metrogistics\AzureSocialite\AuthController@handleOauthResponse')->name('oauth.redirect');
        });
    }
}
