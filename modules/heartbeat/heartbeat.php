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
        return array('checkId', 'reporter');
    }
    public function noAction($page, $params)
    {
        if (!$params['checkId']) throw new WFRequestController_NotFoundException("Not a known checkId");
        if (!$params['reporter']) throw new WFRequestController_HTTPException("Reporter required.", 200);

        $checks = $page->module()->loadChecks();
        $checks[$params['checkId']] = array(
            'time'     => time(),
            'reporter' => $params['reporter'],
            'ip'       => isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']
            );
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

        $checksDb = $page->module()->loadChecks();
        if ($params['checkId'][0] === '^')  // regex mode
        {
            $regexFilter = "/{$params['checkId']}/";
        }
        else if ($params['checkId'])        // match one mode (BC)
        {
            $regexFilter = "/^{$params['checkId']}\$/";
        }
        else                                // show call
        {
            $regexFilter = "/.*/";
        }
        $checks = array();
        foreach ($checksDb as $k => $v) {
            if (preg_match($regexFilter, $k) === 1)
            {
                $checks[$k] = $v;
            }
        }

        $allAlive = true;
        $alive = array();
        foreach ($checks as $k => $hearbeat) {
            $checkIsAlive = $hearbeat['time'] > $sinceU;
            $alive[$k] = $checkIsAlive;
            $allAlive &= $checkIsAlive;
        }
        $page->assign('checks', $checks);
        $page->assign('alive', $alive);
        if ($allAlive)
        {
            if (!empty($checks))
            {
                $overallStatus = "ALL ALIVE";
            }
            else
            {
                $overallStatus = "NO CHECKS";
            }
        }
        else
        {
            $overallStatus = "PROBLEM";
        }
        $page->assign('overallStatus', $overallStatus);
    }
}

class module_heartbeat_clear
{
    public function parameterList()
    {
        return array('checkId');
    }
    public function noAction($page, $params)
    {
        if (!$params['checkId']) throw new WFRequestController_NotFoundException("Not a known checkId");

        $checks = $page->module()->loadChecks();
        if ($params['checkId'] === 'ALL')
        {
            $checks = array();
        }
        else if (isset($checks[$params['checkId']])) 
        {
            unset($checks[$params['checkId']]);
        }
        $page->module()->saveChecks($checks);
    }
}
