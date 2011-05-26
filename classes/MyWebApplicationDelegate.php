<?php

/**
  * This file contains delegate implementations for the basic parts of this Web Application.
  */


// custom WFWebApplication delegate
class MyWebApplicationDelegate
{
    function initialize()
    {
        // manifest core modules that we want to use -- if you don't want people to access a module, remove it from this list!
        $webapp = WFWebApplication::sharedWebApplication();
        $webapp->addModulePath('login', FRAMEWORK_DIR . '/modules/login');
        $webapp->addModulePath('css', FRAMEWORK_DIR . '/modules/css');

        // bootstrap shared dirs
        foreach (array('log', 'runtime', 'runtime/smarty/templates_c') as $d) {
            $dPath = SHARED_DIR . "/{$d}";
            if (!file_exists($dPath))
            {
                $ok = mkdir($dPath, 2775, true);
                if (!$ok) throw new Exception("Couldn't bootstrap shared dir {$dPath}.");
            }
        }
    }
    
    function defaultInvocationPath()
    {
        return 'heartbeat';
    }

    // switch between different skin catalogs; admin, public, partner reporting, etc
    function defaultSkinDelegate()
    {
        return 'simple';
    }

    function autoload($className)
    {
        $requirePath = NULL;
        switch ($className) {
            // Custom Classes - add in handlers for any custom classes used here.
            case 'Propel':
                $requirePath = 'propel/Propel.php';
                break;
        }
        if ($requirePath)
        {
            require($requirePath);
            return true;
        }
        return false;
    }
}

?>
