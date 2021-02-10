<?php namespace Mohsin\GoogleAnalytics\Classes;

use App;
use Config;
use Google_Client;
use Google_Cache_File;
use Google_Service_AnalyticsData;
use Google_Auth_AssertionCredentials;
use ApplicationException;
use Mohsin\GoogleAnalytics\Models\Settings;

class Analytics
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var Google_Client Google API client
     */
    public $client;

    /**
     * @var Google_Service_AnalyticsData Google API analytics service
     */
    public $service;

    /**
     * @var string Google Analytics Property ID
     */
    public $propertyId;

    protected function init()
    {
        $settings = Settings::instance();
        if (!strlen($settings->property_id)) {
            throw new ApplicationException(trans('mohsin.googleanalytics::lang.strings.notconfigured'));
        }

        if (!$settings->gapi_key) {
            throw new ApplicationException(trans('mohsin.googleanalytics::lang.strings.keynotuploaded'));
        }

        $client = new Google_Client();

        /*
         * Set caching
         */
        $cache = App::make(CacheItemPool::class);
        $client->setCache($cache);

        /*
         * Set assertion credentials
         */
        $auth = json_decode($settings->gapi_key->getContents(), true);
        $client->setAuthConfig($auth);
        $client->addScope(Google_Service_AnalyticsData::ANALYTICS_READONLY);

        if ($client->isAccessTokenExpired()) {
            $client->refreshTokenWithAssertion();
        }

        $this->client = $client;
        $this->service = new Google_Service_AnalyticsData($client);
        $this->propertyId = $settings->property_id;
    }
}
