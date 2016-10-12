<?php
namespace Ohanspace\Sms;

use GuzzleHttp\Client;
use Illuminate\Support\Manager;
use Ohanspace\Sms\Drivers\SslSMS;

class DriverManager extends Manager
{
    /**
     * Get the default sms driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['sms.driver'];
    }

    /**
     * Set the default sms driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['sms.driver'] = $name;
    }


    /**
     * Create an instance of the sslsms driver
     *
     * @return SslSms
     */
    protected function createSslSMSDriver()
    {
        //var_dump(2);
        $config = $this->app['config']->get('sms.SslSMS', []);

        $provider = new SslSMS(new Client);
        //var_dump($provider);
        $data = [
            'user' => $config['user'],
            'pass' => $config['pass'],
            'sid' => $config['sid']
        ];
        $provider->buildBody($data);

        return $provider;
    }

}
