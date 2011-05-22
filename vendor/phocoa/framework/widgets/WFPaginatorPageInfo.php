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
 * A Paginator Page info widget.
 *
 * Creates a summary text element along the lines of:
 *
 * Showing items 1-20 of 152.
 *
 * Will be hidden if there are no items.
 *
 * <b>PHOCOA Builder Setup:</b>
 *
 * <b>Required:</b><br>
 * - {@link WFPaginatorPageInfo::$paginator Paginator}
 * 
 * <b>Optional:</b><br>
 * None.
 */
class WFPaginatorPageInfo extends WFView
{
    /**
     * @var object WFPaginator The paginator object that we will draw navigation for.
     */
    protected $paginator;

    /**
      * Constructor.
      */
    function __construct($id, $page)
    {
        parent::__construct($id, $page);
        $this->paginator = NULL;
    }

    public static function exposedProperties()
    {
        $items = parent::exposedProperties();
        return array_merge($items, array(
            'paginator',
            ));
    }
    function render($blockContent = NULL)
    {
        if (!$this->paginator) throw( new Exception("No paginator assigned.") );
        if ($this->paginator->itemCount() == 0) return NULL;
        if ($this->paginator->pageSize() == 1 or $this->paginator->itemCount() == 1)
        {
            return 'Showing ' . $this->paginator->itemPhrase(1) . ' ' . $this->paginator->startItem() .' of ' . $this->paginator->itemCount() . '.';
        }
        else
        {
            return 'Showing ' . $this->paginator->itemPhrase(2) . ' ' . $this->paginator->startItem() . ' - ' . $this->paginator->endItem() .' of ' . $this->paginator->itemCount() . '.';
        }
    }

    function canPushValueBinding() { return false; }
}

?>
