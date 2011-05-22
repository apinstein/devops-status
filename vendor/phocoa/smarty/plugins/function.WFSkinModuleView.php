<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * @package UI
 * @subpackage Template
 * @copyright Copyright (c) 2005 Alan Pinstein. All Rights Reserved.
 * @version $Id: kvcoding.php,v 1.3 2004/12/12 02:44:09 alanpinstein Exp $
 * @author Alan Pinstein <apinstein@mac.com>                        
 */

/**
 *  Smarty plugin to allow inclusion of WFModule invocations from the skin.
 *
 *  Smarty Params:
 *  invocationPath - The invocationPath for the module. See {@link WFModuleInvocation}. Required.
 *  targetRootModule - If you want to customize the value of {@link WFModuleInvocation::$targetRootModule}, specify it in the param.
 *                     BOOLEAN, but remember that in smarty targetRootModule="false" passing the STRING false, so do targetRootModule=false
 *  respondsToForms - Controls whether or not the instance of the module will "respond" to form submissions. Default: FALSE. This is useful when using WFSkinModuleView to embed functions like login or search into skins.
 *
 *  IMPORTANT!!!! WFSkinModuleView is intended for use only from skin template files. Results if used from a WFModule/Page are UNPREDICTABLE -- basically some functions will mistake the "root" module for the root of the skin rather than the root of the current WFModuleInvocation hierarchy when programmiatcally invoking WFModuleInvocations.
 *
 *  @param array The params from smarty tag
 *  @param object WFSmarty object of the current tpl
 *  @return string The HTML snippet from the WFModule.
 *  @throws Exception if the module cannot be found or no invocationPath is specified.
 */
function smarty_function_WFSkinModuleView($params, $smarty)
{
    if (empty($params['invocationPath'])) throw( new Exception("InvocationPath is required.") );
    $respondsToForms = (isset($params['respondsToForms']) ? $params['respondsToForms'] : false);

    $rc = WFRequestController::sharedRequestController();

    // if there is no root invocation, then something bad has happened (probably an exception has been thrown during the main WFModuleInvocation setup), so just bail on any sub-modules.
    $rootInv = $rc->rootModuleInvocation();
    if (!$rootInv) return NULL;

    $modInvocation = new WFModuleInvocation($params['invocationPath'], $rootInv);
    $modInvocation->setRespondsToForms($respondsToForms);
    if (isset($params['targetRootModule']))
    {
        $modInvocation->setTargetRootModule($params['targetRootModule']);
    }
    return $modInvocation->execute();
}

/* vim: set expandtab: */

?>
