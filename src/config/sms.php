<?php

/*
 * SMS DRIVERS
 * -------------------------------------------------------------------------------------------------------
 *
 * SSL
 * ---------------------------------------------------------------------------
 * API endpoint : http://sms.sslwireless.com/pushapi/dynamic/server.php
 * METHOD: POST
 * params:
 *      user
 *      pass
 *      sid
 *      sms[0][0]   :  telephone(8801922503521)
 *      sms[0][1]   :  message
 *      sms[0][2]   :  unique id
 * --------------------------------------------------------------------------
 *
 *
 */

return [
    'driver' => 'SslSMS', //default driver name
    'from' => '8801922503521',
    'pretend' => false, // fake or real
    'SslSMS' => [
        'user' => env('SSL_USER'),
        'pass' => env('SSL_PASS'),
        'sid' => env('SSL_SID')
    ]
];