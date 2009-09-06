=== Snips ===
Author: Quentin T
Plugin link: http://toki-woki.net/blog/
Tags: snippet, replace
Version: 0.2
Requires at least: 2.0
Tested up to: 2.3.3
Stable tag: trunk
Contributors: quentin.t

Easy snippet plugin for Wordpress

== Description ==

Allows to define snippets for easy replacement.
Handy for recurrent copy/paste's from sites like YouTube or DailyMotion... But not only. You decide!
Also allows you not to write un-valid tags that would be stripped out by Wordpress (<object>s for example)

Here is the syntax to use :
[key:parameter1,parameter2,parameter3...]

1. The 'key' has to correspond to a file called 'key-model.txt' stored in `/wp-content/plugins/snips/`
2. This file would contain a template (see 'yt-model.txt' for a YouTube example)
3. This template can contain variables (in the form #index#) that will be replaced either by values passed as parameters (see the syntax), or by default values stored in the model text file.
4. 

== Installation ==

1. Upload `snips` folder and its content to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the syntax described above...
4. Enjoy!