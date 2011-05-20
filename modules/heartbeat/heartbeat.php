<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

class module_heartbeat extends WFModule
{
    function sharedInstancesDidLoad()
    {
    }

    function defaultPage()
    {
        return 'status';
    }

}

class module_heartbeat_ping
{
    public function parameterList()
    {
        return array();
    }
}
