{* vim: set expandtab tabstop=4 shiftwidth=4 syntax=smarty: *}

<h1>PHOCOA AJAX Integration</h1>
<h2>Core Capabilities</h2>
<p>PHOCOA's integrated AJAX infrastructure makes adding dynamic Javascript and AJAX features to your application easy.</p>
<p>For any DOM event, you can configure a PHOCOA widget to:
<ul>
    <li>Call a javascript function (a JSAction)</li>
    <li>Execute a method on the server via a full page refresh (ServerAction)</li>
    <li>Execute a method on the server via AJAX, and have that method send data to the client, or effect UI updates (AjaxAction)</li>
</ul>
</p>

<p>All of this functionality uses the same PHOCOA programming model as standard form/action programming, and requires very little effort to set up.</p>
<p>PHOCOA also includes many YUI widgets that have AJAX capabilities, such as AutoComplete, TreeView, and PhocoaDialog (an AJAX-loading YUI Dialog) that have been plugged in nicely to PHOCOA for easy use and require even less setup. All you have to do is supply a PHP callback to provide dynamically loaded data. No Javascript code is required at all.</p>
<h2>AJAX Integration Basics</h2>
<h3>Setting Up AJAX Event Handlers</h3>
<p>At the highest level, PHOCOA provides an "onEvent" property for all classes in the WFView hierarchy that is used to attach Javascript behaviors to your application. Since the onEvent interface takes in a string as a parameter, you can configure AJAX behaviors via YAML with no PHP coding. If you need more complex behavior, you can always use the PHP API, but 95% of the time you'll find that onEvent works perfectly.</p>
<p>The basic syntax is:</p>
<blockquote>onEvent: &lt;eventName&gt; do &lt;typeOfAction&gt;[:&lt;target&gt;][:&lt;action&gt;]</blockquote>
<ul>
    <li><strong>eventName</strong> - The event name to listen for, i.e., <em>click</em>, <em>change</em>, <em>blur</em>, etc.</li>
    <li><strong>typeOfAction</strong> - <em>j</em> for JSAction, <em>s</em> for ServerAction, or <em>a</em> for AjaxAction.</li>
    <li><strong>target</strong> - The <em>target</em> setting used for ServerAction or AjaxAction. The default is <em>#page#</em> (the page delegate). You can use this optional setting to target the object of the action to <em>#page#outlet.keyPath</em> or <em>#module#keyPath</em>.</li>
    <li><strong>action</strong> - The <em>action</em> to be called on the target object.<br />
        <blockquote>
        <strong>JSAction</strong><br />
        The default is the Javascript function PHOCOA.widgets.&lt;widgetId&gt;.events.&lt;eventName&gt;.handleEvent.<br />
        If you put in your own action, it will be executed as an anonymous Javascript function.<br />
        By default, the underlying DOM event that triggered the JSAction will *not* be stopped. To stop event propagation, call this.stopEvent(event) from your event handler (works in handleEvent or a "j:" customer handler).<br />
        <br />
        <strong>ServerAction and AjaxAction</strong><br />
        The default is the php method &lt;widgetId&gt;Handle&lt;eventName&gt;.<br />
        The Javascript handleEvent function PHOCOA.widgets.&lt;widgetId&gt;.events.&lt;eventName&gt;.handleEvent is also called. To cancel the event, implement this function and return false to cancel the RPC. Any return value (or lack thereof) besides "false" will result in the RPC being called.<br />
        If you put in your own action, it will be interpreted as the php method call of that name on the target object.<br />
        The underlying DOM event that triggered ServerAction or AjaxAction is stopped to prevent event propagation.<br />
        </blockquote>
    </li>
</ul>
<p><strong>A few examples (all added to the widget of id myWidget):</strong></p>
<ul>
    <li><em>onEvent: click do j</em>
        <blockquote>Will call the Javascript function PHOCOA.widgets.myWidget.events.click.handleEvent. This is simly the "default" Javascript function based on a naming convention that phocoa will call.</blockquote>
    </li>
    <li><em>onEvent: click do j: myFunc()</em>
        <blockquote>Will call the Javascript function myFunc.</blockquote>
    </li>
    <li><em>onEvent: click do j: alert("HI");</em>
        <blockquote>Will execute alert("HI").</blockquote>
    </li>
    <li><em>onEvent: click do s</em>
        <blockquote>Will refresh the page and execute the server action #page#myWidgetHandleClick (which simply calls the myWidgetHandleClick method of the page delegate).</blockquote>
    </li>
    <li><em>onEvent: click do s:myPhpFunc</em>
        <blockquote>Will refresh the page and execute the server action #page#myPhpFunc (which simply calls the myPhpFunc method of the page delegate).</blockquote>
    </li>
    <li><em>onEvent: click do a:#module#:myPhpFunc</em>
        <blockquote>Will make an AJAX request, executing the server action #module#myPhpFunc (which simply calls the myPhpFunc method of the module).</blockquote>
    </li>
</ul>

<h3>Sending data from AJAX handlers back to the client</h3>
<p>For many AJAX (a:) operations, the server will need to send data back to the client, and the client will need to react to that data.</p>

<p>To return data from your Ajax callback, simply return a WFActionResponse instance. There are several subclasses for handling different return types:</p>
<ul>
    <li>JSON
        <blockquote><pre>return new WFActionResponseJSON($phpData);</pre></blockquote>
    </li>
    <li>XML
        <blockquote><pre>return new WFActionResponseJSON($xmlString);</pre></blockquote>
    </li>
    <li>Plain Text
        <blockquote><pre>return new WFActionResponsePlain($textString);</pre></blockquote>
    </li>
    <li>Cause UI Updates - PHOCOA has a special WFActionResponse subclass that the PHOCOA client layer will interrupt and process automatically.
        It is a very easy way for you to effect updates in the browser. The WFActionResponsePhocoaUIUpdater class allows you to update HTML elements, replace them, or run Javascript code that is sent from the server. WFActionResponsePhocoaUIUpdater has a fluent interface to make it easy for you to send multiple updates at the same time.
        <blockquote><pre>
return WFActionResponsePhocoaUIUpdater::WFActionResponsePhocoaUIUpdater()-&gt;
         -&gt;addUpdateHTML('myDiv', '&lt;b&gt;new html&lt;/b&gt;')
         -&gt;addReplaceHTML('myOtherDiv',
                             '&lt;div id="myOtherDiv"&gt;replacement div&lt;/div&gt;')
         -&gt;addRunScript('alert("You did it!");');
         </pre></blockquote>
    </li>
</ul>

<h3>Having the client deal with data returned from the Ajax call</h3>
<p>When your Ajax call completes successfully, the clickSuccess handler for that widget (if it exists) will be executed.</p>

<p>Example:
<blockquote><pre>
{literal}
// php handler
public function myCustomFunction($page, $params, $senderId, $eventName) {
   // do stuff
   return new WFActionResponseJSON(array('customString' => 'something meaningful'));
}

// javascript handler
PHOCOA.namespace('widgets.myLink.events.click');
PHOCOA.widgets.myLink.events.click.ajaxSuccess = function(response) {
     // response is a native Javascript object
     alert(response.CustomString);
}
{/literal}
</pre></blockquote>
</p>

<h2>Advanced AJAX Integration</h2>

<h2>Classes Involved</h2>
<p>The WFAction and WFRPC classes are responsible for the javascript/AJAX integration in phocoa.</p>
<ul>
    <li>WFRPC is a thin wrapper around the raw AJAX that provides a phocoa-aware AJAX bus for client/server communitcation.</li>
    <li>WFAction is a higher-level class that is responsible for detecting and routing events either locally (ie all client-side JS) or remotely (via WFRPC).</li>
</ul>

<h2>Available Callbacks and Sequence of Events</h2>
<h2>WFRPC</h2>
<p>WFRPC itself has 3 callbacks:</p>
<ul>
    <li><em>success</em>: Called on HTTP 2xx where no WFErrorsException was thrown.</li>
    <li><em>invalid</em>: Called on HTTP 2xx where a WFErrorsException was thrown. In this case the errors are automatically "shown" in the appropriate WFShowErrors blocks.</li>
    <li><em>failure</em>: Called on HTTP 4xx,5xx.</li>
</ul>
<p>The prototype for these callbacks is: <em>(void) rpcCallbackF(responseObj)</em>, where the responseObj is the raw XMLHttpRequest response object that has been augmented with an "argument" property if one was set via rpc.callback.argument. The "this" inside the func will be set automatically for you if rpc.callback.scope was set.</p>
<p>Exactly one callback is called for every WFRPC request.<p>

<h2>WFAction</h2>
<p>WFAction also has callbacks, but they are configured slightly differently. Since WFAction's are often produced generatively by WFWidgets, there is no easy way to "set" the callback at the time WFAction is created. Instead, WFAction looks for canonically named javascript functions to use for the various callback phases.</p>

<ul>
    <li>
        <em>collectArguments</em>:
        <p><em>(Array) PHOCOA.widgets.&lt;widgetId&gt;.events.&lt;eventName&gt;.collectArgumentsF()</em></p>
        <p>This is the first thing called after the event fires. It should return an array, each element which will be passed as arguments to later callback functions.</p>
    </li>
    <li>
        <em>handleEvent</em>:
        <p><em>(boolean) PHOCOA.widgets.&lt;widgetId&gt;.events.&lt;eventName&gt;.handleEvent(event, arg1, arg2, ...)</em></p>
        <p>Called before the action is executed. If it returns <em>false</em>, further processing of the action will be canceled.</p>
    </li>
    <li>
        <em>ajaxSuccess</em>: 
        <p><em>(boolean) PHOCOA.widgets.&lt;      widgetId&gt;.events.&lt;eventName&gt;.ajaxSuccess(event, arg1, arg2, ...)</em></p>
        <p>Called if the underlying RPC success callback fires.</p>
    </li>
    <li>
        <em>ajaxInvalid</em>: 
        <p><em>(boolean) PHOCOA.widgets.&lt;      widgetId&gt;.events.&lt;eventName&gt;.ajaxInvalid(event, arg1, arg2, ...)</em></p>
        <p>Called if the underlying RPC invalid callback fires.</p>
    </li>
    <li>
        <em>ajaxError</em>: 
        <p><em>(boolean) PHOCOA.widgets.&lt;      widgetId&gt;.events.&lt;eventName&gt;.ajaxError(event, arg1, arg2, ...)</em></p>
        <p>Called if the underlying RPC failure callback fires.</p>
    </li>
</ul>

<h2>Examples</h2>
<h3>JSAction - call a Javascript function when an event fires</h3>
{literal}
<script>
PHOCOA.namespace('widgets.localAction.events.click');
PHOCOA.widgets.localAction.events.click.collectArguments = function() { return ['myArg1', 'myArg2']; };
PHOCOA.widgets.localAction.events.click.handleEvent = function(e, myArg1, myArg2) {
    alert("I've been clicked!\nThe first argument to the callback is the event: " + e + "\nFollowed by all arguments from collectArguments(): " + myArg1 + ", " + myArg2); 
    this.stopEvent(e);
};
</script>
{/literal}
<p>{WFView id="localAction"}</p>
<p>The setup for this is done in the YAML file by specifying the <em>onEvent</em> property:</p>
<blockquote><pre>onEvent: click do j</pre></blockquote>
And, in Javascript, set up the delegate functions:
<blockquote>
{literal}
<pre>
PHOCOA.namespace('widgets.localAction.events.click');
PHOCOA.widgets.localAction.events.click.collectArguments = function() { return ['myArg1', 'myArg2']; };
PHOCOA.widgets.localAction.events.click.handleEvent = function(e, myArg1, myArg2) {
    alert("I've been clicked!\nThe first argument to the callback is the event: "
    + e + "\nFollowed by all arguments from collectArguments(): " + myArg1 + ", " + myArg2);
};
</pre>
{/literal}
</blockquote>
</p>

<h3>ServerAction - refresh the page to execute an action on the server when an event fires</h3>
<p>{WFView id="rpcPageDelegateServer"} {WFView id="ajaxTarget"}</p>
<p>The setup for this is also trivially simple. In the YAML file:
<blockquote><pre>onEvent: click do s</pre></blockquote>
In PHP, we implement the default callback method &lt;widgetId&gt;Handle&lt;EventName&gt;:
{literal}
<blockquote><pre>
function rpcPageDelegateServerHandleClick($page, $params, $senderId, $eventName)
{
    if (WFRequestController::sharedRequestController()->isAjax())
    {
        return WFActionResponsePhocoaUIUpdater::WFActionResponsePhocoaUIUpdater()
            ->addUpdateHTML('ajaxTarget', 'I am the server and this is my random number: ' . rand());
    }
    else
    {
        $page->outlet('ajaxTarget')->setValue('I am the server and this is my random number: ' . rand());
    }
}
</pre></blockquote>
{/literal}
</p>
<p>You will notice that we handle the event different based on whether the call is an AJAX call or not...</p>
<p>For the ServerAction, we need only update the widget's value. This will be reflected in the HTML response that is sent to the client, just as done in normal PHOCOA action handlers.</p>
<p>For the AjaxAction, to effect the UI updates on the client, we return a WFActionResponsePhocoaUIUpdater object. This object has addUpdateHTML(), addReplaceHTML(), and addRunScript() methods that make it easy for you to update the innerHTML of any element, replace any element, and run Javascript code in response to an AjaxAction.</p>

<h3>AjaxAction - make an AJAX call when an event fires</h3>
<p>We are using the same example as above, but turning it into an AJAX action.</p>
<p>{WFView id="rpcPageDelegate"} Click the link and look to the right of the "ServerAction" link above... </p> 
<p>For this example, we want to call a PHP method other than the default, since we've already set up the method we need for the above example:</p>
<blockquote><pre>onEvent: click do a:rpcPageDelegateServerHandleClick</pre></blockquote>

<h3>Event and Widget Support</h3>
<p>The PHOCOA Ajax integration supports several DOM events, which are allowed on most of the WFView subclasses. The blocks below demonstrate various UI widgets and DOM events:</p>

{WFForm id="eventForm"}
<ul>
    <li>{WFView id="eventClick"}</li>
    <li>{WFView id="eventMouseover"}</li>
    <li>{WFView id="eventMouseout"}</li>
    <li>{WFView id="eventMousedown"}</li>
    <li>{WFView id="eventMouseup"}</li>
    <li>Change: {WFView id="eventChange"}</li>
    <li>Focus: {WFView id="eventFocus"}</li>
    <li>Blur: {WFView id="eventBlur"}</li>
    <li>{WFView id="eventMultiple"} <span id="eventMultipleStatus" style="font-weight: bold"></span></li>
</ul>
{/WFForm}

<h3>Form Integration</h3>

<p>The PHOCOA programming model for form submission is extended with our Ajax integration. Everything works the same way, except that you can return WFActionResponse objects to effect UI changes from the server. Even PHOCOA's validation infrastructure works with Ajax.</p>
<p>Below is an example form with two fields. The first field requires any string but "bad" and the second field requires any string but "worse". If there are no errors, the two strings will be interpolated into a single response and updated in the UI.</p>
<p>The submit button is a normal form submit. The link will submit the form via Ajax.</p>

{literal}
<script>
PHOCOA.namespace('widgets.ajaxFormSubmitAjax.events.click');
PHOCOA.widgets.ajaxFormSubmitAjax.events.click.handleEvent = function(e) {
    $('ajaxFormResult').update();
};
</script>
{/literal}

{WFShowErrors id="ajaxForm"}<br />
{WFForm id="ajaxForm"}
    Enter some text: {WFView id="textField"}<br />
    <em>type 'bad' to trigger an error</em><br />
    {WFShowErrors id="textField"}<br />
    <br />
    Enter some other text: {WFView id="textField2"}<br />
    <em>type 'worse' to trigger an error</em><br />
    {WFShowErrors id="textField2"}<br />
    {WFView id="ajaxFormSubmitNormal"} {WFView id="ajaxFormSubmitAjax"}
    <br />
{/WFForm}
<div id="ajaxFormResult" style="color: blue; margin: 10px;">{$formResult}</div>

<p>Once again, the code to build this Ajax functionality is quite simple.</p>
<p>In YAML, we set up our link to trigger the form submission:
<blockquote><pre>onEvent: click do a:ajaxFormSubmitNormal</pre></blockquote>
We also implement our ajaxFormSubmitNormal action handler, which in this example, we use for both normal and ajax form submission:
<blockquote><pre>
{literal}
function ajaxFormSubmitNormal($page, $params, $senderId, $eventName)
{
    $result = 'You said: "' . $page-&gt;outlet('textField')-&gt;value() . '" and "' . $page-&gt;outlet('textField2')-&gt;value() . '".';
    if (WFRequestController::sharedRequestController()-&gt;isAjax())
    {
        return WFActionResponsePhocoaUIUpdater::WFActionResponsePhocoaUIUpdater()
            -&gt;addUpdateHTML('ajaxFormResult', $result);
    }
    else
    {
        $page-&gt;assign('formResult', $result);
    }
}
</pre></blockquote>
When combined with the template code:
<blockquote><pre>
&lt;div id="ajaxFormResult"&gt;{$formResult}&lt;/div&gt;
</pre></blockquote>
The proper result is displayed either by Ajax or by traditional template programming.
</p>
<p>In Javascript, we have an eventHandler to "remove" our "result" when the request is submitted. This prevents previous results from showing if there is an error with the current submission.
<blockquote><pre>
PHOCOA.namespace('widgets.ajaxFormSubmitAjax.events.click');
PHOCOA.widgets.ajaxFormSubmitAjax.events.click.handleEvent = function(e) {
    $('ajaxFormResult').update();
};
</pre></blockquote>
{/literal}
