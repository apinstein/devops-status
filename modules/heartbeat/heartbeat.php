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
        return array('checkId', 'reporter', 'interval');
    }
    public function noAction($page, $params)
    {
        if (!$params['checkId']) throw new WFRequestController_NotFoundException("Not a known checkId");
        if (!$params['reporter']) throw new WFRequestController_HTTPException("Reporter required.", 200);

        $checks = $page->module()->loadChecks();
        $checks[$params['checkId']] = array(
            'time'     => time(),
            'reporter' => $params['reporter'],
            'ip'       => isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
            'interval' => $params['interval'],
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
        $checksDb = $page->module()->loadChecks();
        if ($params['checkId'] && $params['checkId'][0] === '^')  // regex mode
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

        // EXPECTED check-in interval is first of OVERRIDE, CHECK VALUE, DEFAULT VALUE
        $overrideInterval = $params['since'];
        $defaultInterval = '24 hours';

        $allAlive = true;
        $alive = array();
        $now = time();
        foreach ($checks as $k => $heartbeat) {
            $checkInterval = isset($heartbeat['interval']) ? $heartbeat['interval'] : NULL;

            $expectedCheckinInterval = NULL;
            $expectedCheckinU = NULL;

            foreach (array('overrideInterval', 'checkInterval', 'defaultInterval') as $v) {
                if ($$v === NULL) continue;

                $u = strtotime($$v, 0);
                if ($u === false) continue;

                $expectedCheckinInterval = $$v;
                $expectedCheckinU = $now - $u;
                break;
            }
            if (!$expectedCheckinU) throw new Exception("Couldn't figure out interval. Should never happen if defaultInterval is sane: '{$defaultInterval}'.");

            $checks[$k]['intervalReportingBasis'] = $expectedCheckinInterval;
            $checks[$k]['expectedCheckinBy'] = $expectedCheckinU;

            $checkIsAlive = $heartbeat['time'] > $expectedCheckinU;
            $alive[$k] = $checkIsAlive;
            $allAlive &= $checkIsAlive;
        }
        $page->assign('now', $now);
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
