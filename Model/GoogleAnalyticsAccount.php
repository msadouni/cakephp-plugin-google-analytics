<?php
class GoogleAnalyticsAccount extends GoogleAnalyticsAppModel
{
    var $useDbConfig = 'googleAnalytics';

    function __construct()
    {
		require APP . DS . 'Config' . DS . 'google_analytics.php';
		
        $config =& new GOOGLE_ANALYTICS_CONFIG();
        ConnectionManager::create('googleAnalytics', $config->googleAnalytics);

        parent::__construct();
    }
}