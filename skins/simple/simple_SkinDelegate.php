<?php

// custom skin delegates
class simple_SkinDelegate
{
    function loadDefaults($skin)
    {
        // look at URL and determine skin.
        $skin->setSkin('topnav');
        $skin->setTheme('default');

        // set up other skin defaults
        $skin->setTitle('DevOps Heartbeat Status');
    }

    /**
      * The namedContent mechanism for our skin. Here is the catalog:
      * 
      * mainMenu - An associative array of links ('link name' => 'link url') for the header area.
      * copyright - a copyright notice, as a string.
      *
      */
    function namedContent($name, $options = NULL)
    {
        switch ($name) {
            case 'copyright':
                return "&copy; 2005-" . date('Y') . " Alan Pinstein. All Rights Reserved.";
                break;
        }
    }

    function namedContentList()
    {
        return array('copyright');
    }
}

?>
