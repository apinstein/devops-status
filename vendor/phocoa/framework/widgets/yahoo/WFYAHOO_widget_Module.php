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
 * A YAHOO Module widget for our framework.
 *
 * NOTE: You can include YAHOO containers with WFView or WFViewBlock. If you use WFViewBlock, the block content will be used as the container body.
 * 
 * <b>PHOCOA Builder Setup:</b>
 *
 * <b>Required:</b><br>
 * 
 * <b>Optional:</b><br>
 *
 * NOTE: Effects don't work on Modules, but do on all subclasses. Bugfix coming from YUI.
 * @todo if buildModuleProgrammatically is true, we *shouldn't be* dropping in an empty div. Not sure why we are, it causes the module to
 *       be put in wrong spot in DOM (or at least diff than expected).
 * @todo TEST! We should build selenium tests for Container family. Scenarios include: dynamic/static build, DOM insertion location, and inside/outside form in Module.
 */
class WFYAHOO_widget_Module extends WFYAHOO
{
    protected $header;
    protected $body;
    protected $footer;

    /**
     * @var Raw javascript string of argument passed to container.render(). Defaults to NULL.
     */
    protected $renderTo;

    /**
     * @var boolean Whether or not the module is visible. DEFAULT: false
     */
    protected $visible;
    protected $monitorresize;
    /**
     * @var array An array of ContainerEffects to use for show/hide.
     * @todo Move these to Overlay (which is the first class that can have effects)
     */
    protected $effects;

    /**
     * @var string The name of the YAHOO Container class to instantiate. Subclasses should set this method to the proper name.
     */
    protected $containerClass;
    /**
     * @var boolean Whether or not to build the module programmatically (ie via javascript) or to inline the module HTML when rendering this widget.
     *              By default, WFYAHOO_widget_Module is set to FALSE, and all other subclasses are set to TRUE.
     *              This setup works best for the respective types; since Modules are inline, they would get re-ordered if they were re-attached to document.body.
     *              All subclasses are positioned absolutely so they benefit from being attached to document.body inline, to prevent rendering artifacts during load.
     */
    protected $buildModuleProgrammatically;

    /**
      * Constructor.
      */
    function __construct($id, $page)
    {
        parent::__construct($id, $page);
        $this->value = NULL;

        $this->visible = false;
        $this->monitorresize = true;
        $this->effects = array();

        $this->header = NULL;
        $this->body = NULL;
        $this->footer = NULL;

        $this->containerClass = 'Module';
        $this->buildModuleProgrammatically = (get_class($this) !== 'WFYAHOO_widget_Module');
        $this->renderTo = NULL;

        $this->yuiloader()->yuiRequire('container');
    }

    public static function exposedProperties()
    {
        $items = parent::exposedProperties();
        return array_merge($items, array(
            ));
    }

    public function setBuildModuleProgrammatically($b)
    {
        $this->buildModuleProgrammatically = $b;
    }

    /**
     * Set the element that the container renders to.
     *
     * There are a *lot* of caveats with this... see the YUI docs on render.
     * By default all non-Module containers that are rendered programmatically are rendered to document.body. This is the recommended behavior.
     * IF you really need your container's content to be in a particular DOM element, you can set the element here. This is useful for popups that add form inputs to exists forms.
     * 
     * This value will be passed thru as raw javascript; so if you want to use a string element id, make sure the string includes enclosing quotes.
     *
     * @param string The element to render the container to. Should have '' surrounding the value if it's an HTML id.
     */
    public function setRenderTo($el)
    {
        $this->renderTo = $el;
    }

    function addEffect($effectName, $duration = 0.5)
    {
        $this->effects[$effectName] = $duration;
        $this->yuiloader()->yuiRequire('animation');
    }

    function setupExposedBindings()
    {
        $myBindings = parent::setupExposedBindings();

        $newValBinding = new WFBindingSetup('header', 'The header HTML.', array(WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_NAME => WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_VALUE));
        $newValBinding->setReadOnly(true);
        $newValBinding->setBindingType(WFBindingSetup::WFBINDINGTYPE_MULTIPLE_PATTERN);
        $myBindings[] = $newValBinding;

        $newValBinding = new WFBindingSetup('body', 'The body HTML.', array(WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_NAME => WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_VALUE));
        $newValBinding->setReadOnly(true);
        $newValBinding->setBindingType(WFBindingSetup::WFBINDINGTYPE_MULTIPLE_PATTERN);
        $myBindings[] = $newValBinding;

        $newValBinding = new WFBindingSetup('footer', 'The footer HTML.', array(WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_NAME => WFBindingSetup::WFBINDINGSETUP_PATTERN_OPTION_VALUE));
        $newValBinding->setReadOnly(true);
        $newValBinding->setBindingType(WFBindingSetup::WFBINDINGTYPE_MULTIPLE_PATTERN);
        $myBindings[] = $newValBinding;
        return $myBindings;
    }

    function setVisible($show)
    {
        $this->visible = $show;
    }

    function setHeader($html)
    {
        $this->header = $html;
    }

    function setBody($html)
    {
        $this->body = $html;
    }

    function setFooter($html)
    {
        $this->footer = $html;
    }

    function render($blockContent = NULL)
    {
        if ($this->hidden)
        {
            return NULL;
        }
        else
        {
            if ($this->buildModuleProgrammatically)
            {
                // if buildModuleProgrammatically, the dom el with the real ID can't be there when we call new YAHOO.widget.Module or YUI will use that el and it won't truly be programmatic.
                // but we still need a dom element to use to bootstrap our code as part of our YUI loading infrastructure (see base class onContentReady),
                // so we manufacture one with a different id.
                // @todo we can probable get rid of this hack and use initializeWaitsForID === NULL as a flag to *not* hang things on onContentReady().
                $this->initializeWaitsForID = "{$this->id}_bootstrap";
            }

            $html = parent::render($blockContent);
            // determine body html
            $bodyHTML = ($blockContent === NULL ? $this->body : $blockContent);

            // set up basic HTML -- in order to prevent a "flash of content" for non-visible content, we must make it visibility: hidden
            // however, while that prevents the content from being SEEN, you will still see BLANK space where it goes, thus we must also set display: none
            // YUI's show()/hide() functions to display the module content work differently depending on the module's class...
            // show()/hide() on Module toggles display: none|block... on subclasses toggles visibility: visible|hidden
            // thus we need to use a different mechanism to prevent the "Flash of content" and "blank space" issues completely.
            $visibility = NULL;
            if (!$this->visible)
            {
                if (get_class($this) == 'WFYAHOO_widget_Module')
                {
                    $visibility = " style=\"display: none;\"";
                }
                else
                {
                    $visibility = " style=\"display: none; visibility: hidden;\"";
                }
            }
            if ($this->buildModuleProgrammatically === false)
            {
                $html .= "
<div id=\"{$this->id}\"{$visibility}>
    <div class=\"hd\">" . $this->header . "</div>
    <div class=\"bd\">" . $bodyHTML . "</div>
    <div class=\"ft\">" . $this->footer . "</div>
</div>
";
            }
            else
            {
                $html .= "<div id=\"{$this->initializeWaitsForID}\" style=\"display:none;\"></div>";
            }
            return $html;
        }
    }

    function initJS($blockContent)
    {
        // determine body html
        $bodyHTML = ($blockContent === NULL ? $this->body : $blockContent);
        // calcualte effects
        $effects = array();
        foreach ($this->effects as $name => $duration) {
            $effects[] = "{ effect: {$name}, duration: {$duration} }";
        }
        $addEffectsJS = NULL;
        if (count($effects))
        {
            $addEffectsJS = '[ ' . join(', ', $effects) . ' ]';
        }

        $script = "
PHOCOA.namespace('widgets.{$this->id}.Module');
PHOCOA.widgets.{$this->id}.Module.queueProps = function(o) {
    // alert('id={$this->id}: queue Module props');
    // queue Module props here
};
PHOCOA.widgets.{$this->id}.Module.init = function() {
    var module = new YAHOO.widget.{$this->containerClass}(\"{$this->id}\");
    module.subscribe('changeBody', function(el) { PHOCOA.widgets.{$this->id}.scriptsEvald = false; } );
    module.showEvent.subscribe(function(el) {
        if (!PHOCOA.widgets.{$this->id}.scriptsEvald)
        {
            PHOCOA.widgets.{$this->id}.scriptsEvald = true;
            this.body.innerHTML.evalScripts();
        }
    }, module);
    module.cfg.queueProperty('visible', " . ($this->visible ? 'true' : 'false') . ");
    module.cfg.queueProperty('monitorresize', " . ($this->monitorresize ? 'true' : 'false') . ");
    PHOCOA.widgets.{$this->id}.{$this->containerClass}.queueProps(module);";

        if ($this->buildModuleProgrammatically)
        {
            // renderTo is required for buildModuleProgrammatically. should default to document.body if user doesn't specify something more specific
            $renderTo = ($this->renderTo ? $this->renderTo : 'document.body');
            $script .= "
    module.setHeader(" . ($this->header === NULL ? '""' : WFJSON::json_encode($this->header)) . ");
    module.setBody(" . ($bodyHTML === NULL ? '""' : WFJSON::json_encode($bodyHTML)) . ");
    module.setFooter(" . ($this->footer  === NULL ? '""' : WFJSON::json_encode($this->footer)) . ");
    module.render({$renderTo});
";
        }
        else
        {
            $script .= "
    module.render();
";
        }
        $script .= 
( $addEffectsJS ? "\n    module.cfg.setProperty('effect', {$addEffectsJS});" : NULL ) . 
// Module visibility controlled by display attr; subclass visibility controlled by visibilty. Non-modules must be display: block so that they'll appear when asked
( (get_class($this) != 'WFYAHOO_widget_Module') ? "\n    YAHOO.util.Dom.setStyle('{$this->id}', 'display', 'block')" : NULL) . "
    PHOCOA.runtime.addObject(module, '{$this->id}');
};
";
        if ( get_class($this) == 'WFYAHOO_widget_Module')
        {
           $script .= "PHOCOA.widgets.{$this->id}.init = function() { PHOCOA.widgets.{$this->id}.Module.init(); };";
        }
        return $script;
    }

    function canPushValueBinding() { return false; }
}

?>
