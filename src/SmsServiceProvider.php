<?php

namespace Ohanspace\Sms;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //$this->loadViewsFrom(__DIR__.'/views', 'smsview');

        $this->publishes([
            __DIR__.'/config/sms.php' => config_path('sms.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sms', function ($app) {


            $this->registerSender();
            //var_dump($app['sms.sender']);
            $sms = new SMS($app['sms.sender']);

            $this->setSMSDependencies($sms, $app);

            //Set the from and pretending settings
            if ($app['config']->has('sms.from')) {
                $sms->alwaysFrom($app['config']['sms']['from']);
            }

            $sms->setPretending($app['config']->get('sms.pretend', false));

            return $sms;
        });
    }

    /**
     * Register the correct driver based on the config file.
     *
     * @return void
     */
    public function registerSender()
    {

        $this->app->singleton('sms.sender', function($app){
            //var_dump(1);
            //var_dump((new DriverManager($app))->driver());
            return (new DriverManager($app))->driver();
        });

    }

    /**
     * Set a few dependencies on the sms instance.
     *
     * @param SMS $sms
     * @param  $app
     * @return void
     */
    private function setSMSDependencies($sms, $app)
    {
        $sms->setContainer($app);
        //$sms->setLogger($app['log']);
        $sms->setLogger(); //monolog
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('sms', 'sms.sender');
    }

}