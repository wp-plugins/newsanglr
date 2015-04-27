<?php
/*
 Plugin Name: newsAnglr
 Version: 1.0
 Author: Kristof Taveirne
 Author URI: https://www.linkedin.com/in/kristoftaveirne
 License: Unknown
 */

require_once (dirname(__FILE__) . '/includes/anglr_properties.php');
require_once (dirname(__FILE__) . '/includes/anglr_setup_and_options.php');
require_once (dirname(__FILE__) . '/includes/anglr_class.php');
require_once (dirname(__FILE__) . '/includes/anglr_topic_page.php');
require_once (dirname(__FILE__) . '/includes/anglr_widget.php');
require_once (dirname(__FILE__) . '/includes/anglr_api.php');
require_once (dirname(__FILE__) . '/includes/anglr_remote.php');
require_once (dirname(__FILE__) . '/includes/anglr_caching_api_adapter.php');

global $anglr;
$anglr = new AnglrMain();

$anglr_topic_page = new AnglrTopicPage();

function anglr_add_admin_hooks() {

	if (is_admin()) {
		// Add the plugin settings page to the menu. See anglr_setup_and_options.php
		add_action('admin_menu', 'anglr_wordpress_add_page');
		// Init the plugin settings page. See anglr_setup_and_options.php
		add_action('admin_init', 'anglr_wordpress_admin_init');
		add_action('admin_init', 'anglr_addcolumns', 1);
		// add_action('add_meta_boxes', 'anglr_add_post_metabox');
	}
}

add_action('init', 'anglr_add_admin_hooks');

// Add custom column to All Posts page
function anglr_addcolumns() {
	global $pagenow;
	if (!empty($pagenow) && ($pagenow == 'upload.php'))
		$post_type = 'attachment';
	elseif (!isset($_REQUEST['post_type']))
		$post_type = 'post';
	else
		$post_type = $_REQUEST['post_type'];

	if ($post_type != 'page' && $post_type != 'attachment') {
		add_filter('manage_posts_columns', 'anglr_mngmt_addstatuscolumn');
		add_action('manage_posts_custom_column', 'anglr_mngmt_displaystatuscolumn', 10, 2);
	}
}

// Define custom column
function anglr_mngmt_addstatuscolumn($columns) {
	$columns['anglr_status'] = __('NewsAnglr Status', 'anglr_wordpress');
	return $columns;
}

// Populate custom
function anglr_mngmt_displaystatuscolumn($column, $post_id) {
	if ('anglr_status' == $column) {
		global $anglr;
		$meta_data = $anglr -> anglr_get_article_metadata($post_id);
		if ($meta_data['status'] == 'imported') {
			echo '<b>queued</b>';
		} else if ($meta_data['status'] == 'processed') {
			echo '<b>ready</b>';
		} else if ($meta_data['status'] == 'error') {
			echo '<b>failed</b>';
		}
	}
}

/**
 * Register style sheet.
 */
function anglr_register_plugin_styles() {
	wp_register_style('anglr_wordpress', plugins_url('newsanglr-wordpress/css/anglr.css'));
	wp_enqueue_style('anglr_wordpress');
}

add_action('wp_enqueue_scripts', 'anglr_register_plugin_styles');

/**
 * Hide the widget when not on a single-post page
 */

function anglr_filter_sidebars_widgets($sidebars_widgets) {
	global $wp;	
	$pagename = $wp -> query_vars['pagename'];
	if (is_admin()) {
		return $sidebars_widgets;
	}
	
	

	if ($pagename=='topics-page' || !is_single()) {
		foreach ((array) $sidebars_widgets as $index => $sidebar) {
			if (count($sidebars_widgets[$index])) {
				foreach ((array) $sidebars_widgets[$index] as $windex => $widget) {
					if (preg_match('#^AnglrWidget#i', $widget) === 1) {
						unset($sidebars_widgets[$index][$windex]);
					}
				}
			}
		}
	} 

	return $sidebars_widgets;
}

add_filter('sidebars_widgets', 'anglr_filter_sidebars_widgets', 1, 1);

/**
 * Bridge between action and strategy method.
 */
function get_the_angls($post_id) {
	global $anglr;
	return $anglr -> get_the_angls($post_id);
}

// Run activation code to create database tables.
function anglr_activation() {
	global $anglr;
	// Create Tables
	anglr_topics_create_table();
	// Import all posts into the engine
	// TODO Maybe we should do this from the settings page?
	// $anglr -> anglr_import_all();
}

register_activation_hook(__FILE__, 'anglr_activation');

function anglr_register_widget() {
	register_widget('AnglrWidget');
}

add_action('widgets_init', 'anglr_register_widget');

// Make it possible to pass a topic as query var
function anglr_add_query_vars_filter($vars) {
	array_push($vars, 'topic');
	array_push($vars, 'post_id');
	array_push($vars, 'root_bigram');
	return $vars;
}

add_filter('query_vars', 'anglr_add_query_vars_filter');
?>