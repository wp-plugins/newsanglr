<?php
/**
 * A virtual topic page hosted on
 * /?pagename=topics-page&topic=<topic>
 *
 * Test this with slugs
 */
class AnglrTopicPage {

	public function __construct() {
		add_action('parse_request', array($this, 'parse_request'));
		add_shortcode('topic-content', array($this, 'shortcode'));
	}

	public function parse_request(&$wp) {
		// no pagename provided, return asap.
		if (empty($wp -> query_vars['pagename']))
			return;

		$page = $wp -> query_vars['pagename'];
		if (($home = get_option('home')) == FALSE)
			return;
		// Check if it's our topic page
		if ($page == 'topics-page') {

			$this -> topic = $wp -> query_vars['topic'];
			$this -> root_bigram = $wp -> query_vars['root_bigram'];
			// $this -> article_id = $wp -> query_vars['article_id'];
			$this -> post_id = $wp -> query_vars['post_id'];

			// setup hooks and filters to generate virtual movie page
			add_action('template_redirect', array($this, 'virtual_page_template'));
			add_filter('the_posts', array($this, 'virtual_page_content'));

			// now that we know it's my page,
			// prevent shortcode content from having spurious <p> and <br> added
			remove_filter('the_content', 'wpautop');
		}
	}

	public function virtual_page_template() {
		get_template_part('page', 'topics-page');
		exit ;
	}

	/**
	 * Make it look like our page actually comes from the database
	 * This makes many templates happy.
	 */
	public function virtual_page_content($posts) {
		$some_post = the_post();
		// have to create a dummy post as otherwise many templates
		// don't call the_content filter
		global $wp, $wp_query;
		//create a fake post
		$p = new stdClass;
		// fill $p with everything a page in the database would have
		$p -> ID = -1;
		$p -> post_author = 1;
		$p -> post_date = current_time('mysql');
		$p -> post_date_gmt = current_time('mysql', $gmt = 1);
		$p -> post_content = "[topic-content root_bigram='".$this -> root_bigram."' topic='" . $this -> topic . "' post_id='" . $this -> post_id . "']";
		$p -> post_title = __($this -> topic, 'topics-page');
		$p -> post_excerpt = '';
		$p -> post_status = 'publish';
		$p -> ping_status = 'closed';
		$p -> post_password = '';
		$p -> post_name = __('all-topics', 'topics-page');
		// slug
		$p -> to_ping = '';
		$p -> pinged = '';
		$p -> modified = $p -> post_date;
		$p -> modified_gmt = $p -> post_date_gmt;
		$p -> post_content_filtered = '';
		$p -> post_parent = 0;
		$p -> guid = get_home_url('/' . $p -> post_name);
		// use url instead?
		$p -> menu_order = 0;
		$p -> post_type = 'page';
		$p -> post_mime_type = '';
		$p -> comment_status = 'closed';
		$p -> comment_count = 0;
		$p -> filter = 'raw';
		$p -> ancestors = array();

		// Let's fool wp_query as well, so everybody is happy.
		$wp_query -> is_page = TRUE;
		$wp_query -> is_singular = TRUE;
		$wp_query -> is_home = FALSE;
		$wp_query -> is_archive = FALSE;
		$wp_query -> is_category = FALSE;
		unset($wp_query -> query['error']);
		$wp -> query = array();
		$wp_query -> query_vars['error'] = '';
		$wp_query -> is_404 = FALSE;

		$wp_query -> current_post = $p -> ID;
		$wp_query -> found_posts = 1;
		$wp_query -> post_count = 1;
		$wp_query -> comment_count = 0;

		$wp_query -> current_comment = null;
		$wp_query -> is_singular = 1;

		$wp_query -> post = $p;
		$wp_query -> posts = array($p);
		$wp_query -> queried_object = $p;
		$wp_query -> queried_object_id = $p -> ID;
		$wp_query -> current_post = $p -> ID;
		$wp_query -> post_count = 1;

		remove_action('template_redirect', array($this, 'virtual_page_template'));
		remove_filter('the_posts', array($this, 'virtual_page_content'));

		return array($p);
	}

	public function shortcode($atts) {
		global $anglr;
		$topic = $atts['topic'];
		$post_id = $atts['post_id'];
		$root_bigram = $atts['root_bigram'];

		global $wpdb;
		$tablename = $wpdb -> prefix . "posts";
		$sql = $wpdb -> prepare('SELECT post_content FROM ' . $tablename . ' WHERE id = %d', $post_id);
		$context = $wpdb -> get_col($sql);
		$context = $context[0];
		$ret = $anglr -> anglr_fetch_related_to_topic($root_bigram, $context);

		return $ret;
	}

}
?>