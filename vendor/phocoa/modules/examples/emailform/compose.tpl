<p>Simple email module showcases many PHOCOA features. Code below.</p>

<ul>
    <li>Form creation.</li>
    <li>Action handlers.</li>
    <li>Validation - Notice how there are no calls in code to validate. This is done automatically via Key-Value Validation concept.</li>
    <li>Error management - Try entering invalid data. Notice how the system autmatically tracks errors per-field and per-form.</li>
    <li>Bindings - notice how there is no code to move the data from the form to the ExampleEmail object. This is all done via Bindings which are configured graphically (or in a text file).</li>
    <li>Adjusting page title for each page. This is part of the skin infrastructure.</li>
</ul>

{WFShowErrors}

<table border="0">
{WFForm id="form"}
    <tr><td valign="top">To Email:</td><td>{WFTextField id="email"}<br /> {WFShowErrors id="email"}</td></tr>
    <tr><td valign="top">Subject:</td><td>{WFTextField id="subject"}<br /> {WFShowErrors id="subject"}</td></tr>
    <tr><td valign="top">Message:</td><td>{WFTextArea id="message"}<br /></td></tr>
    {WFSubmit id="send"}
{/WFForm}
</table>

<hr>

<p>Shared instances are objects that are shared among two or more pages in a module. The shared instances mechanism allows multi-page processes to easily share the same instances. For this module, we have 2 shared instances, a WFUNIXDateFormatter, and our ExampleEmail object. PHOCOA automatically instantiates shared objects as members of your module subclass.</p>
<h3>shared.yaml file</h3>
<pre>
{php}
echo file_get_contents(FRAMEWORK_DIR . '/modules/examples/emailform/shared.yaml');
{/php}
</pre>

{capture name="emailTPL"}
{literal}
<table border="0">
{WFForm id="form"}
    <tr><td valign="top">To Email:</td><td>{WFTextField id="email"}<br /> {WFShowErrors id="email"}</td></tr>
    <tr><td valign="top">Subject:</td><td>{WFTextField id="subject"}<br /> {WFShowErrors id="subject"}</td></tr>
    <tr><td valign="top">Message:</td><td>{WFTextArea id="message"}<br /></td></tr>
    {WFSubmit id="submit"}
{/WFForm}
</table>
{/literal}
{/capture}
<h3>compose.tpl file</h3>
<pre>
{$smarty.capture.emailTPL|escape:'html'}
</pre>
    
<h3>compose.yaml file</h3>
<pre>
{php}
echo file_get_contents(FRAMEWORK_DIR . '/modules/examples/emailform/compose.yaml');
{/php}
</pre>

<h3>Module Code</h3>
<pre>
{php}
echo htmlentities(file_get_contents(FRAMEWORK_DIR . '/modules/examples/emailform/emailform.php'));
{/php}
</pre>
