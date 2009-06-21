=== PHP Execution ===
Contributors: nzeh
Tags: php, code, execution, exec, run, eval 
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 1.0.0

The PHP Execution plugin allows embedding php code inside of posts and pages.

== Description ==

The PHP Execution Plugin is a Wordpress plugin which allows users to write php code inside of their posts and pages. Embedded php code will be executed when the post is shown. In doing so, the plugin offers the possibility to utilize dynamic content inside of posts.

In contrast to other plugins with the same aim, this one integrates well with Wordpress’ visual editor. So there is **no need to turn off the visual editor in order to edit your php code**.
Additionally it provides an admin section which lets administrators edit the blog users' php execution rights easily. The plugin also automatically prevents users with no php execution rights from editing posts or pages of users with these rights. It thus fixes a possible security leak where people without php execution rights can still execute code with editing other users' posts.

Features:

* executes php code in your posts and pages (full, excerpts, feeds).
* integrates well with Wordpress’ visual editor. No need to turn it off.
* write php code in the usual `<?php ... ?>` syntax in the html view of the editor.
* admin section to edit the blog users' php execution rights.
* plugin automatically prevents users with no php execution rights to edit posts of users with rights to execute php code.

Project was now moved out of beta.

Further information at [zehnet.de: PHP Execution Plugin home](http://www.zehnet.de/2009/02/25/wordpress-php-execution-plugin/ "PHP Execution home")

== Installation ==

* Download the latest version of the plugin.
* Unzip it into the `/wp-content/plugins/` folder of your Wordpress installation resulting in a `/wp-content/plugins/php_execution/` folder.
* Login as administrator and activate the plugin.
* Wordpress’ tag balancing has to be turned **off**. It is by default. If you have enabled tag balancing, turn it off again by unchecking "WordPress should correct invalidly nested XHTML automatically" in Settings»Writing of the admin section.
* Optionally set PHP execution permissions in the Settings»PHP Execution panel. By default all administrators possess the permission to execute php code.
* **As javascript code is added to the editor, you have to clear your browser cache !!!** Otherwise the plugin won't work as the code added by it simply won't get executed. For information on how to clear the browser cache, follow this [link](http://kb.iu.edu/data/ahic.html "How do I clear my web browser's cache?").

== Editing PHP code ==

* php code can be edited in the html view of the editor.
* php code is embedded into a post in the same way as you are used to: a php block begins with `<?php` and ends with `?>`. The short open tag, i.e. `<?`, is not supported.
* One restriction is, that you are not allowed to use the ending delimeter (`?>`) somewhere inside a string in your php code block. If you do so, this ending delimeter will be matched and your code will inevitably break apart. So don’t write e.g.:
<pre>
    some text
    &lt;?php $test = "hello ?&gt;"  ?&gt;
    more text
</pre>
* Writing `$test = "hello ?&amp;gt;"` instead will not lead to any problems.
* Php code is not executed in the global scope. If you need to gain access to variables in the global scope you need to "import" them first with `global $var1, $var2;`.
* This plugin does not evaluate every single code snippet, but the content of the post as a whole. So the following lines won’t result in errors:
<pre>
    &lt;?php if ($test==true) { ?&gt;
    The test was successful.
    &lt;?php } else { ?&gt;
    The test failed.
    &lt;?php } ?&gt;
</pre>
* When switching between html and visual view the php code is not altered in any case. All html tags, whitespaces etc. in your code are preserved.


== Screenshots ==

1. editing in html view
2. php code in the visual wysiwyg view
3. dragging php code snippets
3. the admin section