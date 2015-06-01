<?php

require_once (ABSPATH . 'wp-admin/includes/template.php');

function anglr_topics_create_table() {
	global $wpdb;
	require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

	$tablename = $wpdb -> prefix . "anglr_post_articles_id";

	if ($wpdb -> get_var("SHOW TABLES LIKE '$tablename'") != $tablename) {
		$sql = "CREATE TABLE `$tablename` (`post_id`  INT(11) NOT NULL, `article_id` VARCHAR(100) NOT NULL, `status` VARCHAR(45), `cached_result` text, `cache_timestamp` datetime DEFAULT NULL)";
		dbDelta($sql);
	}
}

/**
 * Get the api key and insert it in the database directly
 * This happens upon plugin activation
 */
function anglr_get_api_key_onactivation() {
	global $anglr;

	$response = $anglr -> get_api_key();
	$json = json_decode($response);
	if (property_exists($json, 'error_code')) {
		error_log('we have an error_code: ' . $json -> error_code);
		// What to do in case of error, how can we notify user during activation?
		// Cannot be done here, check on admin_init hook.
	} else {
		$options = get_option('anglr_wordpress_options');
		$options['api_key'] = $json -> api_key;
		update_option('anglr_wordpress_options', $options);
	}
}

function anglr_apikey_activationwarning() {
	error_log('executing activationwarning');
	$options = get_option('anglr_wordpress_options');
	$key = $options['api_key'];
	error_log('key: ' . $key . ', isset()' . isset($key));
	if (!isset($key) || $key == '') {
		if (!get_option('anglr_apikey_warning_shown')) {
			error_log('No key, adding action');
			add_action('admin_notices', 'anglr_apikey_warning');
		}
	}
}

add_action('admin_init', 'anglr_apikey_activationwarning');

function anglr_apikey_warning() {
	error_log('output warning for missing key');
	echo '<div id="message" class="error">';
	echo '	<p>Your site cannot be accessed from the WWW. Please go to the NewsAnglr settings page and request an API key to get detailed info.</p>';
	echo '</div>';
	update_option('anglr_apikey_warning_shown', true);
}

/**
 * Add the options page in wp-admin
 */
function anglr_wordpress_add_page() {
	// Page title, Menu title, Capability, Menu-slug, Function that draws the content
	$settings = add_options_page('NewsAnglr', 'NewsAnglr', 'manage_options', 'anglr-wordpress', 'anglr_wordpress_options_page');
	add_action('load-' . $settings, 'anglr_load_admin_scripts');
}

function anglr_load_admin_scripts() {
	wp_enqueue_script('anglr_admin_script', plugin_dir_url(__FILE__) . '../js/anglr_admin.js', array('jquery'), '', true);
	// Get current page protocol
	$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
	// Output admin-ajax.php URL with same protocol as current page
	$params = array('ajaxurl' => admin_url('admin-ajax.php', $protocol));
	//output the params in a <script>-tag so javascript can access this
	wp_localize_script('anglr_admin_script', 'anglr_params', $params);
}

function anglr_get_api_key_process_ajax() {
	global $anglr;
	$response = $anglr -> get_api_key();
	echo $response;
	die();
}

// register the anglr_get_api_key_request ajax call
add_action('wp_ajax_anglr_get_api_key_request', 'anglr_get_api_key_process_ajax');

/**
 * Draw the options page in wp-admin
 */
function anglr_wordpress_options_page() {

	echo '<div class="wrap">';
	echo '	<h2>NewsAnglr</h2>';
	echo '	<form action="options.php" method="post">';
	settings_fields('anglr_wordpress_options');
	do_settings_sections('anglr_wordpress');
	echo '		<input name="Submit" type="submit" value="Save Changes"/>';
	echo '	</form>';
	echo '</div>';
}

/**
 * Initialise the NewsAnglr settings
 */
function anglr_wordpress_admin_init() {
	// Group name, Option name
	register_setting('anglr_wordpress_options', 'anglr_wordpress_options');
	// How are the settings visually grouped

	// HTML-ID, section title, Function with section description, settings page (the slug)
	add_settings_section('anglr_wordpress_security', 'Security settings', 'anglr_wordpress_security_section_text', 'anglr_wordpress');
	// HTML-ID for the field, field label, callback function echo form field, the slug of the settings page, the section html-id
	add_settings_field('anglr_wordpress_api_key', 'API key', 'anglr_wordpress_api_key', 'anglr_wordpress', 'anglr_wordpress_security');

	// HTML-ID, section title, Function with section description, settings page (the slug)
	add_settings_section('anglr_wordpress_main', 'Basic settings', 'anglr_wordpress_basic_section_text', 'anglr_wordpress');
	// HTML-ID for the field, field label, callback function echo form field, the slug of the settings page, the section html-id
	// Add a filter to remove topics from the result set
	add_settings_field('anglr_wordpress_topic_filter', 'List topics to filter', 'anglr_wordpress_topic_filter_input', 'anglr_wordpress', 'anglr_wordpress_main');
	add_settings_field('anglr_wordpress_import_settings', 'Import settings', 'anglr_wordpress_import_settings', 'anglr_wordpress', 'anglr_wordpress_main');

}

function anglr_wordpress_security_section_text() {
	echo '<ul><li>API Key linked to your Site Address. Changing your Site Address, will require a new API Key.</li></ul>';
}

function anglr_wordpress_basic_section_text() {

	echo '<ul>';
	echo '<li><strong>Topic Filter: </strong>Comma separated list of topics that will not be shown as part of the topic list of an article</li>';
	echo '<li><strong>Import Settings:</strong>Choose the categories of articles that should be imported into newsAnglr</li>';
	echo '</ul>';

}

function anglr_wordpress_import_settings() {
	$options = get_option('anglr_wordpress_options');
	$selected_categories = $options['categories'];
	echo "<ul class='children'>";
	wp_category_checklist(0, 0, $selected_categories, false, new AnglrCustomCategoryWalkerChecklist());
	echo "</ul>";
	echo "<em>Be sure to hit &quot;Save Changes&quot; first for the changes to take effect during import</em><br/>";
	echo "<a id='import_articles' href='#'>Import articles</a>";
}

/**
 * Process the request to import articles
 * Using the Categories configured on the settings page.
 */
function anglr_import_articles_process_ajax() {
	global $anglr;
	$options = get_option('anglr_wordpress_options');
	$cats = $options['categories'];
	error_log(print_r($cats, true));
	$count = $anglr -> anglr_import_all($cats);
	echo $count;
	die();
}

// register the anglr_get_api_key_request ajax call
add_action('wp_ajax_anglr_import_articles_request', 'anglr_import_articles_process_ajax');

/**
 * Draw the form to show and request the API key
 */
function anglr_wordpress_api_key() {
	$options = get_option('anglr_wordpress_options');
	$api_key = $options['api_key'];
	echo "<input id='api_key' name='anglr_wordpress_options[api_key]' type='text' value='$api_key' readonly/><a id='get_api_key' href='#'>Get API Key</a></br>";
	echo "<span id='api_key_error' style='font-size: small; font-style:italic; color: #ff0000;'></span>";
}

function anglr_wordpress_topic_filter_input() {
	$options = get_option('anglr_wordpress_options');
	$topic_filter = $options['topic_filter'];
	echo "<input id='topic_filter' name='anglr_wordpress_options[topic_filter]' type='text' value='$topic_filter'/><br/>";
}

/**
 * A custom checklist walker so we can use wp_category_checklist in a settings form
 */
class AnglrCustomCategoryWalkerChecklist extends Walker_Category_Checklist {
	public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0) {
		if (empty($args['taxonomy'])) {
			$taxonomy = 'category';
		} else {
			$taxonomy = $args['taxonomy'];
		}
		$name = 'anglr_wordpress_options[categories]';
		$args['popular_cats'] = empty($args['popular_cats']) ? array() : $args['popular_cats'];
		$class = in_array($category -> term_id, $args['popular_cats']) ? ' class="popular-category"' : '';
		$args['selected_cats'] = empty($args['selected_cats']) ? array() : $args['selected_cats'];
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}' $class >" . '<label class="selectit"><input style="margin-left: '.($depth*16).'px" value="' . $category -> term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category -> term_id . '"' . checked(in_array($category -> term_id, $args['selected_cats']), true, false) . disabled(empty($args['disabled']), false, false) . ' /> ' . esc_html(apply_filters('the_category', $category -> name)) . '</label>';
	}
	
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		error_log('start_lvl: '.$depth);
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children' style='margin-top: 6px'>\n";
    }
	
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		error_log('end_lvl: '.$depth);
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

}
?>