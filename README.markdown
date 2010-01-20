# Install
Several possibilities :

* `git clone git://github.com/msadouni/cakephp-plugin-google-analytics.git google_analytics` in your plugin directory
* Download and unzip into a `google_analytics` folder in your `plugins` folder
* If your project is already versioned with Git : `git submodule add git://github.com/msadouni/cakephp-plugin-google-analytics.git plugins/google_analytics`

# Configuration
Duplicate `config/google_analytics.php.default` into `config/google_analytics.php` and fill your account information in `$google_analytics` array

# Usage
In a controller :

* `var $uses = array('GoogleAnalytics.GoogleAnalyticsAccount');` or
* `$this->loadModel('GoogleAnalytics.GoogleAnalyticsAccount')`

To get all accounts for the given credentials : `$this->GoogleAnalyticsAccount->find('all');`

This will return an array of Accounts :

    [0] =>
        [Account] =>
            [id] => account url
            [updated] => last update datetime
            [title] => account title
            [tableId] => tableId (the id you need to perform searches on)
            [accountId] => account id
            [profileId] => profile id
            [webPropertyId] => tracker id on your website
    [1] =>
        [Account] => ...


Grab the Account.profileId you need and get the Account data :

    $data = $this->GoogleAnalyticsAccount->find('first', array(
        'conditions' => array(
            'tableId' => $tableId,
            'start-date' => 'YYYY-MM-DD',
            'end-date' => 'YYYY-MM-DD')));

The `start-date` and `end-date` conditions are mandatory. You can add other conditions to perform searches :

    $data = $this->GoogleAnalyticsAccount->find('first', array(
        'conditions' => array(
            'tableId' => $tableId,
            'start-date' => 'YYYY-MM-DD',
            'end-date' => 'YYYY-MM-DD'
            'dimensions' => 'country',
            'metrics' => 'newVisits',
            'sort' => '-newVisits')));

will get you the new visits per country, ordered by descending new visits, for the period given. you can also pass several dimensions (maximum 7), metrics (maximum 10) and sort options (must match dimensions or metrics given) by passing arrays :

    $data = $this->GoogleAnalyticsAccount->find('first', array(
        'conditions' => array(
            'tableId' => $tableId,
            'start-date' => 'YYYY-MM-DD',
            'end-date' => 'YYYY-MM-DD'
            'dimensions' => array('country', 'city'),
            'metrics' => array('newVisits', 'uniquePageviews'),
            'sort' => array('country', 'city', '-newVisits'))));

You can find the allowed dimensions, metrics and sort options on [Google Analytics API page](http://code.google.com/apis/analytics/docs/gdata/1.0/gdataProtocol.html)

# TODO
* Test HTTP errors on connection and requests
* Add support for filters
* Many other stuff I guess, fork it and have fun

# THANKS
Sources that have been of great help :

* Python example : [http://blog.clintecker.com/post/100021441/python-google-analytics-client-how-to-use-it-and-how](http://blog.clintecker.com/post/100021441/python-google-analytics-client-how-to-use-it-and-how)
* Ruby example : [http://github.com/cannikin/gattica/tree/master](http://github.com/cannikin/gattica/tree/master)
* How to access a datasource in a pugin by Matt Curry : [http://www.pseudocoder.com/archives/2009/02/10/yahoo-search-boss-as-a-cakephp-plugin/](http://www.pseudocoder.com/archives/2009/02/10/yahoo-search-boss-as-a-cakephp-plugin/)