<?php

namespace Sample;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class PayPalClient
{
    /**
     * Returns PayPal HTTP client instance with environment which has access
     * credentials context. This can be used invoke PayPal API's provided the
     * credentials have the access to do so.
     */
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }
    
    /**
     * Setting up and Returns PayPal SDK environment with PayPal Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
    public static function environment()
    {
        $clientId = getenv("CLIENT_ID") ?: "Aerr5kGcTyo7DDUkVSkTl5xdX73foEWomvQmnv1wOhLhmU1hjs_0-dCV9u_l4LQYX9vPnAbUkD2-5AeB";
        $clientSecret = getenv("CLIENT_SECRET") ?: "EEcSA4fUdDKLWF0-lqOk_L-khHrN0aHU_N3pw8OxCo_vGWMFwXBdYuUgwXXWhZfN4JTgBwce8_h7EnYk";
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}
