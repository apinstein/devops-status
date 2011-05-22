<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * @package UI
 * @subpackage Widgets
 * @copyright Copyright (c) 2005 Alan Pinstein. All Rights Reserved.
 * @version $Id: kvcoding.php,v 1.3 2004/12/12 02:44:09 alanpinstein Exp $
 * @author Alan Pinstein <apinstein@mac.com>                        
 */

/**
 * A Menu widget for our framework. Uses {@link http://www.dynarch.com/products/dhtml-menu/ Dynarch Menu}.
 * 
 * <b>PHOCOA Builder Setup:</b>
 *
 * <b>Required:</b><br>
 * - {@link WFDynarchMenu::$menuItems menuItems}
 * 
 * <b>Optional:</b><br>
 * - {@link WFDynarchMenu::$skin skin}
 */
class WFDynarchMenu extends WFWidget
{
    static private $hasIncludedFiles = false;

    /**
     * @var array An array of objects confirming to WFMenuItem protocol.
     */
	protected $menuItems;
    /**
     * @var string The skin to use.
     */
	protected $skin;

    /**
      * Constructor.
      */
    function __construct($id, $page)
    {
        parent::__construct($id, $page);
        $this->menuItems = array();
        $this->skin = '';

        // check the existence of the www dir and throw and exception if it isn't there as people will need to install it.
        //if (!file_exists($projectRoot . $this->getWidgetWWWDir())) throw( new Exception("You need to install the hmenu files in " . $this->getWidgetWWWDir()) );
    }

    /**
     *  Set the menu items for the menu.
     *
     *  Menu items may be in a tree form. Objects should conform to {@link WFMenuItem}.
     *
     *  @param array An array of menu items.
     */
    function setMenuItems($items)
    {
        $this->menuItems = $items;
    }

    function render($blockContent = NULL)
    {
        if ($this->hidden)
        {
            return NULL;
        }
        else
        {
            $html = '';
            if (!$this->hasIncludedFiles)
            {
                $this->hasIncludedFiles = true;
                $html = '
                    <!-- load the menu stylesheet -->
                    <style type="text/css">
                      @import url("' . $this->getWidgetWWWDir() . '/skin-aqua.css");
                    </style>
                    <!-- declare the menu location -->
                    <script type="text/javascript">
                      _dynarch_menu_url = "' . $this->getWidgetWWWDir() . '/";
                      window.onload = function()
                      {
                          DynarchMenu.setup(\'' . $this->id . '\', { electric: true, timeout: 70 } );
                      }
                    </script>
                    <!-- load the menu program file -->
                    <script type="text/javascript" src="' . $this->getWidgetWWWDir() . '/hmenu.js"></script>
                ';
            }
            $html .= "\n<ul id=\"{$this->id}\">\n";
            foreach ($this->menuItems as $menuItem) {
                $html .= $this->menuItemToHTML($menuItem);
            }
            $html .= "\n</ul>\n";
            // add JS to "hide" the UL until Dynarch shows it. Using JS to do this ensures that we only hide it if JS is there, which is what is used to turn it back on.
            $html .= "<script type=\"text/javascript\">//<![CDATA[
            document.getElementById('" . $this->id . "').style.display = 'none';
            //]]></script>";
        }
        return $html;
    }

    // internal helper recursive function to output tree-form menu items.
    function menuItemToHTML($item)
    {
        $html = "\n<li";
        if ($item->toolTip())
        {
            $html .= " title=\"" . $item->toolTip() . "\" ";
        }
        $html .= ">\n";
        
        if ($item->icon())
        {
            $html .= "<img src=\"" . $this->icon() . "\" />";
        }
        
        if ($item->link())
        {
            $html .= "<a href=\"" . $item->link() . "\">" . $item->label() . "</a>";
        }
        else
        {
            $html .= $item->label();
        }

        if ($item->children())
        {
            $html .= "\n<ul>\n";
            foreach ($item->children() as $childMenu) {
                $html .= $this->menuItemToHTML($childMenu);
            }
            $html .= "\n</ul>\n";
        }
        
        $html .= "</li>\n";
        
        return $html;
    }


    function canPushValueBinding() { return false; }
}

?>
