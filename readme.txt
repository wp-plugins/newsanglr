=== newsAnglr ===
Contributors: NewsAnglr, kristof.taveirne 
Tags: related content, related articles, related posts, smart, news, anglr, semantic, search, context 
Requires at least: 4.0.0
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The newsAnglr for Wordpress Plugin shows related articles from a continuously growing pool of sources using a unique matching algorithm. 

== Description ==

The "newsAnglr for Wordpress" Plugin allows bloggers to Angle their posts.

This mean that the visitors of this blog will be served with new information that will enable them to discover more about the topic you're writing about. 
BUT, it also means that the articles will appear on other sites that talk about the same subject.

In short: your readers will discover new and relevant content and readers on other newsAnglr-empowered websites will find their way to your blog.

The better your content, the more likely it will draw in new readers! 

The plugin provides 3 visualisations:

1. A widget that shows related articles next to your post.
1. A collection of topics that are related to your post.
1. A topic-page that shows articles that share a certain topic in the context of your article

For more information about the plugin visit [our demo site](http://wordpress-demo.newsanglr.com/ "newsAnglr for Wordpress").
For more information about newsAnglr visit [newsAnglr.com](http://www.newsanglr.com/ "newsAnglr.com").

== Installation ==

### Via Admin

1. Go to Plugins -> Add New
1. Search for newsAnglr
1. Install the plugin called newsAnglr
1. Press Activate
1. Go to Settings -> newsAnglr
1. Click "Get API Key"
1. Press "Save"

An API Key will automatically be fetched for you upon activation of the plugin.
However, if your site is not yet public, this will fail and the plugin will run in testing mode.
This means that your articles will not yet be indexed by NewsAnglr, the article will have less accurate related articles and your articles will not show up on other blogs.

### Import your articles

1. Select the categories of articles you wish to import
1. Click the import articles button

### The widget

The widget can be placed in any of your side-bars using the Wordpress widgets manager.
It will show related articles next to your blog post.
The widget will only appear on the single post view.

You can configure in the widget configuration howmany related articles should be listed.

### The topic list

You can filter topics from appearing using the topic filter setting on the plugin's settings page.

== Frequently Asked Questions ==

= How can I Angle my post? I don't see a button for it =

After the initial import of the articles you've allready writting before installing the plugin, your post will be "angled" by newsAnglr upon publish. Your post will then be queued for import by our servers. 
You can see the status of this in the All Posts overview screen. 
While your post is in the queue for processing you can still see related articles but your article with not yet be shown elsewhere.
This is then temporarily using a free-text algorithm, which is less accurate.

= Will the topic of my blog be covered? =

It could very well be that the topic you are writing about doesn't have proper coverage in our system. 
If that is the case, it could very well be that not much interesting content will appear next to your articles.

But, don't be affraid to be the first! We monitor topic coverage on a daily basis and do our best to enrich our article set when required.
newsAnglr is growing and coverage will improve the more users use the system.

= How can I Angle my older posts that are already published? =

Upon activation you can import articles of certain categories using the settings screen.
Choose the categories you wish to import and click the import articles button. Only the most recent posts (not older than 3 months will be inserted).

= How can I remove my post from newsAnglr? = 

At this moment, this feature is not yet supported.
However, when the post itself is no longer available it will be concidered to be a broken link. When it stays offline, your article will be removed from the index.


= Will this plugin work in other languages than English? =

Currently we only support the English language. Keep your eyes on this site for any updates on this!

== Screenshots ==

1. Related articles shown as widget.
2. Angles generated for your article. Clicking these Angles will show of overview over articles that share the same Angles withing the context of article currenty showing.


== Changelog ==

= 2.0.1 =
* Minor bug fixes
* Clean-up of settings section
* Admin email is send to server when registering your blog using the API Key
* Sites will enter test-mode when the site is not yet publicaly available

= 2.0.0 =
* First public version

