<h1>Social Poster</h1>

<p>Social Poster is the add-on for ExpressionEngine 2 that allows automatic updates to be sent to social networks when user performs certain actions. The user need to have social account(s) linked to his EE profile using <a href="http://www.intoeetive.com/index.php/comments/social-login-pro">Social Login PRO</a>, which is required for Social Poster to work.</p>

<p>The action user makes must be associated with an extension hook. You can disable posting on certain hooks as well as use different templates. It is also easy to create your own posting rules by following the example.</p>

<p class="important"><a href="http://www.intoeetive.com/index.php/comments/social-login-pro">Social Login PRO</a> is REQUIRED for this add-on to work.</p>

<p><strong>REQUIREMENTS:</strong> the user has to have social network account added to his EE profile using Social Login Pro and "Send status updates from user's account about his activity" must be checked in Social Login Pro settings.</p>

<ul>		
    
    <li><a href="#settings">Settings &amp; configuration</a></li>
    <li><a href="#permissions">Default Permissions tag</a></li>
    <li><a href="#permissions-detailed">Detailed Permissions tag</a></li>
    <li><a href="#custom">Extending with custom hooks</a></li>
</ul>


<h2><a name="settings" href="#top">&uarr;</a>Settings &amp; configuration</h2>


<p>Settings page consists of 2 sections: 'default posting settings' and 'hooks and templates'.</p>

<p>'Default posting settings' lists all social networks supported for posting by Social Login Pro (even if you don't have keys for all of them yet). You can select to which networks you want the posts to be made when user performs actions on your site. The user can set his personal permissions for than using <a href="#permissions">permissions</a> and <a href="#permissions-detailed">permissions_detailed</a> template tags.</p>

<p>'Hooks and templates' section displays the list of installed hooks and associated posting templates. Each hook represents certain user action performed on site. The templates are set separately for each hook, there are 2 fields: for post text and for link. The available variables are listed at the left.</p>

<p> If your ExpressionEngine installation is using MSM, the setting are saved for each site separately.</p>


<h2><a name="permissions" href="#top">&uarr;</a>Default Permissions tag</h2>

<p><dfn>{exp:social_poster:default_permissions}</dfn> tag is used to present user a form in which he can allow or disallow posting to each social network that is added to his account. If your ExpressionEngine installation is using MSM, the permissions are saved for each site.</p>

<code>
{exp:social_poster:default_permissions return="SAME_PAGE"}<br />
{permissions}<br />
&lt;p&gt;<br />
Allow posting to {field_label}?<br />
&lt;input type="radio" value="y" name="{field_name}" {selected_yes} /&gt; Yes<br />
&lt;input type="radio" value="n" name="{field_name}" {selected_no} /&gt; No<br />
&lt;/p&gt;<br />
{/permissions}<br />
&lt;input type="submit" value="Save" /&gt;<br />
{/exp:social_poster:default_permissions}
</code>

<p><strong>Tag parameters:</strong>
<ul>
<li><dfn>return</dfn> &mdash; a page to return after submitting form. Can be a full URL or URI segments.<br />Use <em>return="SAME_PAGE"</em> to return user to the page used to display form.</li>
<li><dfn>id</dfn> &mdash; form ID (defaults to 'status_updates_form')</li>
<li><dfn>class</dfn> &mdash; form class (defaults to 'status_updates_form')</li>
<li><dfn>name</dfn> &mdash; form name (defaults to 'status_updates_form')</li>
<li><dfn>ajax="yes"</dfn> &mdash; process form in AJAX mode (will return success/error text without formatting)</li>
</ul>
</p>

<p>All variables must be placed inside <dfn>{permissions}...{/permissions}</dfn> pair. You need to use radio buttons type of input to make sure the preferences will be saved correctly.</p>


<p><strong>Variables</strong>:

<ul>
<li><dfn>field_name</dfn> &mdash; field name, to be used as value of "name" parameter of input tag</li>
<li><dfn>field_label</dfn> &mdash; field label</li>
<li><dfn>selected_yes</dfn> &mdash; make radio button selected if posting is allowed</li>
<li><dfn>selected_no</dfn> &mdash; make radio button selected if posting is not allowed</li>
</ul>





<h2><a name="permissions-detailed" href="#top">&uarr;</a>Detailed Permissions tag</h2>

<p><dfn>{exp:social_poster:permissions}</dfn> tag will create a form in which the user can can allow or disallow posting to each social network upon each suported event/user action. Total and precise control of what is being posted, separate setting for each network/action pair. If your ExpressionEngine installation is using MSM, the permissions are saved for each site.</p>

<code>
{exp:social_poster:permissions return="SAME_PAGE"}<br />
{permissions}<br />
&lt;p&gt;<br />
{field_label}<br />
&lt;input type="radio" value="y" name="{field_name}" {selected_yes} /&gt; Yes<br />
&lt;input type="radio" value="n" name="{field_name}" {selected_no} /&gt; No<br />
&lt;/p&gt;<br />
{/permissions}<br />
&lt;input type="submit" value="Save" /&gt;<br />
{/exp:social_poster:permissions}
</code>


<p>Parameter and variables are exactly the same that for {exp:social_poster:default_permissions} tag.</p>




<h2><a name="custom" href="#top">&uarr;</a>Extending with custom hooks</h2>

<p>You can easily extend the module to support custom actions by creating your own hooks.</p> 

<p class="important">If you need integration with some well-known ExpressionEngine add-on, feel free to request it by sending email to <a href="mailto:support@intoeetive.com">support@intoeetive.com</a></p>

<p>First of all, make sure that the module that is processing user's action contain some extension hook. If it does not, you can either request developers to add it or add it yourself.</p>

<p>Next, familarize yourself with code in /hooks/entry_submission_end.php file. It contains comments that should help you to get started.</p>

<p>Create new file in /hooks/ directory (you may copy entry_submission_end.php). Name the file after name of extension hook that will trigger it. Example: 'entry_submission_end' is the extension hook which is called when entry is submitted. We want it to also trigger Social Poster, so we name the file 'entry_submission_end.php'</p>

<p>The class name will be (first letter capitalized) name of extension hook + '_sp_hook' prefix. Your class should contain constructor and at least one function, named - again - after extension hook (in our case, entry_submission_end()). It is of course recommended to also have all other functions and variables found in example.</p>

<p>The purpose of 'main' (entry_submission_end()) function is to build text and links to be posted and to actually post it.</p>

<p>You mostly need to modify only one part of function, which is used to create array of variables which will be used to transform templates into actual message and link. You'll need to know a tiny bit of PHP to do it, but as you can see in example, it is usually just couple of lines of code.</p>

