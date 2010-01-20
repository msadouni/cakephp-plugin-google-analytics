<?php
class GoogleAnalyticsController extends GoogleAnalyticsAppController
{
    var $uses = array('GoogleAnalytics.GoogleAnalyticsAccount');

    function index()
    {
        $accounts = $this->GoogleAnalyticsAccount->find('all');
        $this->set(compact('accounts'));
    }

    function show($tableId = null)
    {
        if (empty($tableId))
        {
            if (empty($this->params['named']['tableId']))
            {
                $this->redirect(array('action' => 'index'));
            }
            $tableId = $this->params['named']['tableId'];
        }

        $params = array(
            'start-date' => strftime('%Y-%m-%d'),
            'end-date' => strftime('%Y-%m-%d'),
            'dimensions' => array(),
            'metrics' => array('newVisits'),
            'sort' => array());

        if (!empty($this->params['named']['start-date']))
        {
            $params['start-date'] = strftime(
                '%Y-%m-%d', strtotime($this->params['named']['start-date']));
        }
        if (!empty($this->params['named']['end-date']))
        {
            $params['end-date'] = strftime(
                '%Y-%m-%d', strtotime($this->params['named']['end-date']));
        }
        if (!empty($this->params['named']['dimensions']))
        {
            $params['dimensions'] = explode(
                ',', str_replace(' ', '', $this->params['named']['dimensions']));
        }
        if (!empty($this->params['named']['metrics']))
        {
            $params['metrics'] = explode(
                ',', str_replace(' ', '', $this->params['named']['metrics']));
        }
        if (!empty($this->params['named']['sort']))
        {
            $params['sort'] = explode(
                ',', str_replace(' ', '', $this->params['named']['sort']));
        }
        $conditions['conditions'] = array_merge(
            array('tableId' => $tableId), $params);

        $account = $this->GoogleAnalyticsAccount->find('first', $conditions);

        $start_date = $params['start-date'];
        $end_date = $params['end-date'];
        $dimensions = join($params['dimensions'], ',');
        $metrics = join($params['metrics'], ',');
        $sort = join($params['sort'], ',');
        $dimensionsArray = $params['dimensions'];
        $metricsArray = $params['metrics'];
        $sortArray = $params['sort'];

        $this->set(compact(
            'account', 'start_date', 'end_date', 'dimensions', 'metrics', 'sort', 'dimensionsArray', 'metricsArray', 'sortArray'));
        $this->set('tableId', $tableId);
    }

    function search()
    {
        $params = $this->params['form'];
        if (empty($params['tableId']))
        {
            $this->redirect(array('action' => 'index'));
        }
        $tableId = $params['tableId'];
        $start_date = $end_date = $dimensions = $metrics = $sort = '';
        if (!empty($params['start-date']))
        {
            $start_date = $params['start-date'];
        }
        if (!empty($params['end-date']))
        {
            $end_date = $params['end-date'];
        }
        if (!empty($params['dimensions']))
        {
            $dimensions = $params['dimensions'];
        }
        if (!empty($params['metrics']))
        {
            $metrics = $params['metrics'];
        }
        if (!empty($params['sort']))
        {
            $sort = $params['sort'];
        }
        $this->redirect(array(
            'action' => 'show',
            'tableId' => $tableId,
            'start-date' => $start_date,
            'end-date' => $end_date,
            'dimensions' => $dimensions,
            'metrics' => $metrics,
            'sort' => $sort));
    }
}