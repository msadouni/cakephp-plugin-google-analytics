<?php
App::import(array(
    'type' => 'file',
    'name' => 'GoogleAnalytics.GOOGLE_ANALYTICS_CONFIG',
    'file' => 'config' . DS . 'google_analytics.php'));
App::import(array(
    'type' => 'file',
    'name' => 'GoogleAnalytics.GoogleAnalyticsSource',
    'file' => 'models' . DS . 'datasources' . DS . 'google_analytics_source.php'));

class GoogleAnalyticsSourceTestCase extends CakeTestCase
{
    function startTest()
    {
        // instead of ConnectionManager::getDataSource() we build it manually
        // to be able to specify $autoConnect => false
        $config =& new GOOGLE_ANALYTICS_CONFIG();
        $this->db =& new GoogleAnalyticsSource(
            $config->googleAnalytics_test, false);
    }

    function test___buildParams()
    {
        $result = $this->db->__buildParams(array(
            'conditions' => array(
                'dimensions' => 'country',
                'metrics' => 'newVisits',
                'sort' => 'newVisits')));
        $expected = array(
            'dimensions' => 'ga:country',
            'metrics' => 'ga:newVisits',
            'sort' => 'ga:newVisits');
        $this->assertEqual($result, $expected,
            "should append ga: to single parameters : %s");

        $result = $this->db->__buildParams(array(
            'conditions' => array(
                'dimensions' => array('country', 'city'),
                'metrics' => array('newVisits', 'uniquePageviews'),
                'sort' => array('newVisits', 'city'))));
        $expected = array(
            'dimensions' => 'ga:country,ga:city',
            'metrics' => 'ga:newVisits,ga:uniquePageviews',
            'sort' => 'ga:newVisits,ga:city');
        $this->assertEqual($result, $expected,
            "should append ga: to array parameters : %s");

        $result = $this->db->__buildParams(array(
            'conditions' => array(
                'sort' => '-city')));
        $expected = array('sort' => '-ga:city');
        $this->assertEqual($result, $expected,
            "should correctly treat the minus on a single sort : %s");

        $result = $this->db->__buildParams(array(
            'conditions' => array(
                'sort' => array('newVisits', '-city'))));
        $expected = array('sort' => 'ga:newVisits,-ga:city');
        $this->assertEqual($result, $expected,
            "should correctly treat the minus on a multiple sort : %s");
    }

    function test___validateQueryData()
    {
        $result = $this->db->__validateQueryData(array(
            'conditions' => array()));
        $this->assertError('start-date is required');
        $this->assertIdentical($result, null,
            "should return null when start-date is missing : %s");

        $result = $this->db->__validateQueryData(array(
            'conditions' => array(
                'start-date' => '2009-01-01')));
        $this->assertError('end-date is required');
        $this->assertIdentical($result, null,
            "should return null when end-date is missing : %s");

        $result = $this->db->__validateQueryData(array(
            'conditions' => array(
                'start-date' => '2009-01-01',
                'end-date' => '2009-02-01')));
        $this->assertError('metrics is required');
        $this->assertIdentical($result, null,
            "should return null when metrics is missing : %s");

        $result = $this->db->__validateQueryData(array(
                'conditions' => array(
                    'start-date' => '2009-01-01',
                    'end-date' => '2009-02-01',
                    'metrics' => array('a'),
                    'dimensions' => array('a','b','c','d','e','f','g','h'))));
        $this->assertError('too many dimensions, the maximum allowed is 7');
        $this->assertIdentical($result, null,
            "should return null when too many dimensions are given : %s");

        $result = $this->db->__validateQueryData(array(
                'conditions' => array(
                    'start-date' => '2009-01-01',
                    'end-date' => '2009-02-01',
                    'metrics' => array(
                        'a','b','c','d','e','f','g','h', 'i', 'j', 'k', 'l'))));
        $this->assertError('too many metrics, the maximum allowed is 10');
        $this->assertIdentical($result, null,
            "should return null when too many metrics are given : %s");

        $result = $this->db->__validateQueryData(array(
                'conditions' => array(
                    'start-date' => '2010-01-01',
                    'end-date' => '2009-01-01',
                    'metrics' => array('a'))));
        $this->assertError('date order is reversed');
        $this->assertIdentical($result, null,
            "should return null when date order is reversed : %s");
    }

    function test_listSources()
    {
        $this->assertIdentical($this->db->listSources(), false,
            "should return false : %s");
    }

    function test_read()
    {
        Mock::generatePartial(
            'GoogleAnalyticsSource',
            'MockGoogleAnalyticsSourceTestRead',
            array('accounts', 'account_data'));

        $mock =& new MockGoogleAnalyticsSourceTestRead();
        $mock->setReturnValue('accounts', 'accounts');
        $mock->setReturnValue('account_data', 'account_data');

        $result = $mock->read($model, array());
        $this->assertEqual($result, 'accounts',
            "should call accounts() when given no parameters : %s");

        $result = $mock->read($model, array(
            'conditions' => array('tableId' => 123456)));
        $this->assertEqual($result, 'account_data',
            "should call account_data() when given a profileId : %s");

        //TODO test that Model->find('all') and Model->find('first') trigger
        // the appropriate calls from read()
    }

    function test___parseDataPoint()
    {
        $result = $this->db->__parseDataPoint(array(
            'name' => 'ga:country',
            'value' => 'France'));
        $expected = array('name' => 'country', 'value' => 'France');
        $this->assertEqual($result, $expected,
            "should remove ga: from a single dimension datapoint : %s");

        $result = $this->db->__parseDataPoint(array(
            'confidenceInterval' => '0.0',
            'name' => 'ga:uniquePageViews',
            'type' => 'integer',
            'value' => 1));
        $expected = array(
            'confidenceInterval' => '0.0',
            'name' => 'uniquePageViews',
            'type' => 'integer',
            'value' => 1);
        $this->assertEqual($result, $expected,
            "should remove ga: from a single metric datapoint : %s");

        $result = $this->db->__parseDataPoint(array(
            array('name' => 'ga:country', 'value' => 'France'),
            array('name' => 'ga:country', 'value' => 'United States')));
        $expected = array(
            array('name' => 'country', 'value' => 'France'),
            array('name' => 'country', 'value' => 'United States'));
        $this->assertEqual($result, $expected,
            "should remove ga: from several dimension datapoints : %s");

        $result = $this->db->__parseDataPoint(array(
            array(
                'confidenceInterval' => '0.0',
                'name' => 'ga:uniquePageviews',
                'type' => 'integer',
                'value' => 1),
            array(
                'confidenceInterval' => '0.0',
                'name' => 'ga:newVisits',
                'type' => 'integer',
                'value' => 1)));
        $expected = array(
            array(
                'confidenceInterval' => '0.0',
                'name' => 'uniquePageviews',
                'type' => 'integer',
                'value' => 1),
            array(
                'confidenceInterval' => '0.0',
                'name' => 'newVisits',
                'type' => 'integer',
                'value' => 1));
        $this->assertEqual($result, $expected,
            "should remove ga: from several metric datapoints : %s");
    }

    function test___dataPoints()
    {
        $result = $this->db->__dataPoints(array('Entry' => array(
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => array(
                    'value' => 'ga:country=France',
                    'type' => 'text'),
                'Link' => array(
                    'rel' => 'alternate',
                    'type' => 'text/html',
                    'href' => 'http://www.google.com/analytics'),
                'Dimension' => array(
                    'name' => 'ga:country',
                    'value' => 'France'),
                'Metric' => array(
                    'confidenceInterval' => '0.0',
                    'name' => 'ga:uniquePageviews',
                    'type' => 'integer',
                    'value' => 1)),
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => array(
                    'value' => 'ga:country=United States',
                    'type' => 'text'),
                'Link' => array(
                    'rel' => 'alternate',
                    'type' => 'text/html',
                    'href' => 'http://www.google.com/analytics'),
                'Dimension' => array(
                    'name' => 'ga:country',
                    'value' => 'United States'),
                'Metric' => array(
                    'confidenceInterval' => '0.0',
                    'name' => 'ga:uniquePageviews',
                    'type' => 'integer',
                    'value' => 2)))));

        $expected = array(
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => 'ga:country=France',
                'dimensions' => array(
                    'name' => 'country',
                    'value' => 'France'),
                'metrics' => array(
                    'confidenceInterval' => '0.0',
                    'name' => 'uniquePageviews',
                    'type' => 'integer',
                    'value' => 1)),
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => 'ga:country=United States',
                'dimensions' => array(
                    'name' => 'country',
                    'value' => 'United States'),
                'metrics' => array(
                    'confidenceInterval' => '0.0',
                    'name' => 'uniquePageviews',
                    'type' => 'integer',
                    'value' => 2)));

        $this->assertEqual($result, $expected,
            "should reformat datapoints for a single dimension and metric : %s");

        $result = $this->db->__dataPoints(array('Entry' => array(
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => array(
                    'value' => 'ga:country=France',
                    'type' => 'text'),
                'Link' => array(
                    'rel' => 'alternate',
                    'type' => 'text/html',
                    'href' => 'http://www.google.com/analytics'),
                'Dimension' => array(
                    array('name' => 'ga:country', 'value' => 'France'),
                    array('name' => 'ga:city', 'value' => 'Caen')),
                'Metric' => array(
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'ga:uniquePageviews',
                        'type' => 'integer',
                        'value' => 1),
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'ga:newVisits',
                        'type' => 'integer',
                        'value' => 1))),
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => array(
                    'value' => 'ga:country=United States',
                    'type' => 'text'),
                'Link' => array(
                    'rel' => 'alternate',
                    'type' => 'text/html',
                    'href' => 'http://www.google.com/analytics'),
                'Dimension' => array(
                    array('name' => 'ga:country', 'value' => 'United States'),
                    array('name' => 'ga:city', 'value' => 'Atlanta')),
                'Metric' => array(
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'ga:uniquePageviews',
                        'type' => 'integer',
                        'value' => 2),
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'ga:newVisits',
                        'type' => 'integer',
                        'value' => 2))))));

        $expected = array(
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => 'ga:country=France',
                'dimensions' => array(
                    array('name' => 'country', 'value' => 'France'),
                    array('name' => 'city', 'value' => 'Caen')),
                'metrics' => array(
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'uniquePageviews',
                        'type' => 'integer',
                        'value' => 1),
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'newVisits',
                        'type' => 'integer',
                        'value' => 1))),
            array(
                'id' => 'http://www.google.com/analytics/feed/data?ids=xxx',
                'updated' => '2009-01-01',
                'title' => 'ga:country=United States',
                'dimensions' => array(
                    array('name' => 'country', 'value' => 'United States'),
                    array('name' => 'city', 'value' => 'Atlanta')),
                'metrics' => array(
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'uniquePageviews',
                        'type' => 'integer',
                        'value' => 2),
                    array(
                        'confidenceInterval' => '0.0',
                        'name' => 'newVisits',
                        'type' => 'integer',
                        'value' => 2))));

        $this->assertEqual($result, $expected,
            "should reformat datapoints for multiple dimensions and metrics : %s");

        $this->assertIdentical($this->db->__dataPoints(array()), array(),
            "should return array() with empty parameters : %s");
        
    }

    function test_accounts()
    {
        Mock::generatePartial(
            'GoogleAnalyticsSource',
            'GoogleAnalyticsSourceMockTestAccounts',
            array('get'));
        $mock =& new GoogleAnalyticsSourceMockTestAccounts();

        $several_accounts = array(
            'Feed' => array(
                'Entry' => array(
                    array(
                        'id' => 'http://google.com/123',
                        'updated' => 'updated',
                        'title' => array('value' => 'account1'),
                        'tableId' => 'ga:123',
                        'Property' => array(
                            array(
                                'name' => 'ga:accountId',
                                'value' => 456),
                            array(
                                'name' => 'ga:accountName',
                                'value' => 'main account'),
                            array(
                                'name' => 'ga:profileId',
                                'value' => 123),
                            array(
                                'name' => 'ga:webPropertyId',
                                'value' => 'UA1'))),
                    array(
                        'id' => 'http://google.com/321',
                        'updated' => 'updated',
                        'title' => array('value' => 'account2'),
                        'tableId' => 'ga:321',
                        'Property' => array(
                            array(
                                'name' => 'ga:accountId',
                                'value' => 456),
                            array(
                                'name' => 'ga:accountName',
                                'value' => 'main account'),
                            array(
                                'name' => 'ga:profileId',
                                'value' => 321),
                            array(
                                'name' => 'ga:webPropertyId',
                                'value' => 'UA2'))))));

        $mock->setReturnValueAt(
            0, 'get', $several_accounts, array('analytics/feeds/accounts/default'));
        $expected = array(
            array(
                'Account' => array(
                    'id' => 'http://google.com/123',
                    'updated' => 'updated',
                    'title' => 'account1',
                    'tableId' => '123',
                    'accountId' => '456',
                    'accountName' => 'main account',
                    'profileId' => '123',
                    'webPropertyId' => 'UA1')),
            array(
                'Account' => array(
                    'id' => 'http://google.com/321',
                    'updated' => 'updated',
                    'title' => 'account2',
                    'tableId' => '321',
                    'accountId' => '456',
                    'accountName' => 'main account',
                    'profileId' => '321',
                    'webPropertyId' => 'UA2')));

        $this->assertEqual($mock->accounts(), $expected,
            "should return the accounts correctly formatted : %s");

        $one_account = array(
            'Feed' => array(
                'Entry' => array(
                    'id' => 'http://google.com/123',
                    'updated' => 'updated',
                    'title' => array('value' => 'account1'),
                    'tableId' => 'ga:123',
                    'property' => array(
                        array('name' => 'ga:accountId', 'value' => 456),
                        array('name' => 'ga:accountName', 'value' => 'main account'),
                        array('name' => 'ga:profileId', 'value' => 123),
                        array('name' => 'ga:webPropertyId', 'value' => 'UA1')))));

        $mock->setReturnValueAt(
            1, 'get', $one_account, array('analytics/feeds/accounts/default'));
        $expected = array(
            array(
                'Account' => array(
                    'id' => 'http://google.com/123',
                    'updated' => 'updated',
                    'title' => 'account1',
                    'tableId' => '123',
                    'accountId' => '456',
                    'accountName' => 'main account',
                    'profileId' => '123',
                    'webPropertyId' => 'UA1')));

        $this->assertEqual($mock->accounts(), $expected,
            "should work with only one account : %s");
    }

    function test_extract_property_value() {
        $entry = array(
            array(
                'id' => 'http://google.com/123',
                'updated' => 'updated',
                'title' => array('value' => 'account1'),
                'tableId' => '123',
                'property' => array(
                    array(
                        'name' => 'ga:accountId',
                        'value' => ''),
                    array(
                        'name' => 'ga:accountName',
                        'value' => ''),
                    array(
                        'name' => 'ga:profileId',
                        'value' => ''),
                    array(
                        'name' => 'ga:webPropertyId',
                        'value' => 'UA1'))));

        $this->assertEqual(
            $this->db->__extract_property_value($entry, 'webPropertyId'),
            'UA1',
            "should extract the value of a filled-in property : %s");
        $this->assertEqual(
            $this->db->__extract_property_value($entry, 'accountId'),
            '',
            "should assign an empty string for an empty property : %s");
        $this->assertEqual(
            $this->db->__extract_property_value(array(), 'accountId'),
            '',
            "should assign an empty string for an empty entry : %s");
        $this->assertEqual(
            $this->db->__extract_property_value($entry, ''),
            '',
            "should assign an empty string for an empty property : %s");
        $this->assertEqual(
            $this->db->__extract_property_value($entry, 'non existent'),
            '',
            "should assign an empty string for a non existent property : %s");
        $this->assertEqual(
            $this->db->__extract_property_value(
                'wrong entry format', 'accountId'),
            '',
            "should assign an empty string for a wrong entry format : %s");
        $this->assertEqual(
            $this->db->__extract_property_value(
                $entry, array('wrong property format')),
            '',
            "should assign an empty string for a wrong property format : %s");
    }
}