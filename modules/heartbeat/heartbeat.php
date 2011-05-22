<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

class module_heartbeat extends WFModule
{
    protected $checksFile;

    function sharedInstancesDidLoad()
    {
        $this->checksFile = SHARED_DIR . '/checks.json';
    }

    function defaultPage()
    {
        return 'status';
    }

    function loadChecks()
    {
        if (!file_exists($this->checksFile))
        {
            file_put_contents($this->checksFile, '[]');
        }
        $checks = json_decode(file_get_contents($this->checksFile), true);
        return $checks;
    }

    function saveChecks($checks)
    {
        file_put_contents($this->checksFile, json_encode($checks));
    }
}

class module_heartbeat_ping
{
    public function parameterList()
    {
        return array('checkId');
    }
    public function noAction($page, $params)
    {
        if (!$params['checkId']) throw new Exception("checkId required for ping.");

        $checks = $page->module()->loadChecks();
        $checks[$params['checkId']] = time();
        $page->module()->saveChecks($checks);
    }
}

class module_heartbeat_status
{
    public function parameterList()
    {
        return array('checkId', 'since');
    }
    public function noAction($page, $params)
    {
        $since = $params['since'] ? $params['since'] : '24 hours';

        $now = time();
        $sinceU = $now - strtotime($since, 0);

        $checks = $page->module()->loadChecks();
        if ($params['checkId'])
        {
            $checks = array($params['checkId'] => $checks[$params['checkId']]);
        }

        $alive = array();
        foreach ($checks as $k => $last) {
            $alive[$k] = $last > $sinceU;
        }
        $page->assign('checks', $checks);
        $page->assign('alive', $alive);
    }
}
