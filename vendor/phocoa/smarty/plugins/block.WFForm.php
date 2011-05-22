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
 *  Smarty plugin to include a WFForm.
 *
 *  Smarty Params:
 *  id - The id of the WFForm to use.
 *
 *  @param array The params from smarty tag.
 *  @param object WFSmarty object of the current tpl.
 *  @return string The rendered HTML.
 */
function smarty_block_WFForm($params, $content, &$smarty, &$repeat)
{
    $form = $smarty->getCurrentWidget($params);

    // beginning or end block?
    if (isset($content))
    {
        return $form->render($content);
    }
}

?>
