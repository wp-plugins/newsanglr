<?php

class AnglrMain {

	public function __construct() {
		$this -> api = new AnglrCachingAPIAdapter();
		add_action('save_post', array($this, 'anglr_import_post'));
		add_filter('the_content', array($this, 'anglr_add_topics_to_content'));
	}

	/**
	 * Transform anglr_topics on post to HTML
	 */
	public function anglr_add_topics_to_content($content) {
		global $wp;	
		$pagename = $wp -> query_vars['pagename'];
		
		if ($pagename=='topics-page' || !is_single())
			return $content;
		// Get the topics
		$post = $this -> anglr_fetch_topics(get_post());
		$angls = $post -> anglr_topics;
		
		
		// Filter the topics
		$options = get_option('anglr_wordpress_options');
		$topic_filter = $options['topic_filter'];
		$filter_words = explode(',', $topic_filter);
		
		$content .= '<div id="topics_container"><img src="'.plugins_url('/../images/menu-angle.png',__FILE__).'" style="float:left; height: 16pt; margin-right: 5pt" /><ul>';
		foreach ($angls as $key => $value) {
			$do_filter = false;
			foreach ($filter_words as $filter_key => $filter_value) {
				$filter_value = trim($filter_value);
				// changes are spaces are typed
				if (!empty($filter_value) && preg_match("#^$filter_value#i", $value -> description) === 1) {
					// topic should be filtered
					$do_filter = true;
				}
			}
			if (!$do_filter) {
				$href = "?pagename=topics-page&root_bigram=" . urlencode($value -> root_bigram) . "&topic=" . urlencode($value -> description) . "&post_id=" . $post -> ID;
				$content .= '<li><a href="' . $href . '">' . $value -> description . '</a></li>';
			}
		}
		$content .= '</ul></div>';
		return $content;
	}

	public function anglr_fetch_topics($post) {
		if (is_single()) {
			// 1 get article id
			$row = $this -> anglr_get_article_metadata($post -> ID);

			$article_id = $row['article_id'];
			$status = $row['status'];
			if ('imported' == $status) {
				// update status first

				$status = $this -> api -> status($article_id);
				// update status in DB
				$this -> anglr_update_status($post -> ID, $status);
			}

			if ('processed' == $status) {

				// Do findSimilar
				if ($article_id) {

					$search_result = $this -> api -> find_similar($article_id);
					$topics = $search_result -> topics;
					$articles = $search_result -> articles;

					// 3 add topics to $post
					$post -> anglr_topics = $topics;
					$post -> anglr_articles = $this -> filter($articles, $post);

					// 4 add related articles to post
				}
			} else {
				// Still not imported? -> Do FreeTextSearch
				// Extract the query parameters
				$title = get_the_title($post -> ID);
				$content = get_the_content($post -> ID);

				// Perform the GET
				$search_result = $this -> api -> free_text_search($title, $content);

				$topics = $search_result -> topics;
				$articles = $search_result -> articles;

				// 3 add topics to $post
				$post -> anglr_topics = $topics;
				$post -> anglr_articles = $this -> filter($articles, $post);

			}
		}
		return $post;
	}

	public function get_api_key() {
		return $this->api->get_api_key();
	}

	/**
	 * Remove the article that matches the post that is currently viewed
	 */
	private function filter($articles, $post) {
		$filtered_articles = array_values($articles);
		foreach ($articles as $key => $value) {
			if ($value -> title == $post -> post_title) {
				unset($filtered_articles[$key]);
			}
		}
		return array_values($filtered_articles);
	}

	/**
	 * function to search for articles related to this topic
	 * in the context of the article where the topic was clicked
	 */
	public function anglr_fetch_related_to_topic($root_bigram, $context) {
		$options = get_option('anglr_wordpress_options');
		$api_key = $options['api_key']; 
		
		$ret = '';
		if ($root_bigram && $context) {
			$context = str_replace("\xe2\x80\x99", "'", $context);
			$context = str_replace("\xe2\x80\x94", " ", $context);
			$context = str_replace(" ", ",", $context);
			$context = str_replace(array(".", ";", "!", "?"), ",", $context);
			$context = str_replace("\r\n", ",", $context);
			$context = str_replace("<", ",", $context);
			$context = str_replace("--more-->", ",", $context);
			$context = str_replace(",,", ",", $context);
			$context = preg_replace('/,+/', ',', $context);
			$context = str_replace("(", "", $context);
			$context = str_replace(")", "", $context);
			$result = $this -> api -> find_by_topic_and_context($root_bigram, $context);
			foreach ($result->articles as $article) {
				$url = ANGLR_API_TRACE_URL.$api_key.'/'.urlencode($article -> url);
				$ret .= '<a href="' . $url . '">' . $article -> title . '</a></br>';
			}
		}
		return $ret;
	}
	
	
	/**
	 * This should be used on on_save action
	 */
	public function anglr_import_post($post_id){
		// 1) get the categories of the post
		$post_cats = get_the_category($post_id);
		$post_cat_ids = array();
		
		foreach ($post_cats as $key => $value) {
			array_push($post_cat_ids, $value->cat_ID);
		}
		
		
		// 2) get the categories filter
		$options = get_option('anglr_wordpress_options');
		$cat_filter = $options['categories'];
		// 3) decide import / no-import
		$count = count(array_intersect($cat_filter, $post_cat_ids));
		if($count > 0){
			// 4) import post
			error_log('importing post '.$post_id);
			$this->anglr_import_filtered_post($post_id);
		}
		
	}

	/**
	 * Import single post when status is publish, and not imported before
	 */
	private function anglr_import_filtered_post($post_id) {
		$the_post = get_post($post_id);	

		
		// is this save or update?
		// currently no way to update, let's ignore it for now
		$meta = $this -> anglr_get_article_metadata($post_id);
		if ($the_post -> post_status == 'publish') {
			if (!$meta['article_id']) {
				$response = $this -> api -> import($the_post);
				$article_id= $response -> {'id'};
				$status = $response -> {'status'};
				if ($article_id) {
					$this -> anglr_persist_article_metadata($post_id, $article_id, $status);
				} else {// import failed
					$this -> anglr_persist_article_metadata($post_id, null, 'error');
				}
			}
		}
	}

/**
 * Import all articles filtered on selected categories
 */
	public function anglr_import_all($categories) {
		global $wpdb;
		global $post;

		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		$sql = "SELECT post_id FROM $tablename";
		$results = $wpdb -> get_col($sql);

		// select posts not older than 3 months
		
		$imploded = implode(", ",$categories);
		$str = "SELECT p.* FROM wp_posts p, wp_term_relationships r WHERE p.post_type = 'post' AND p.post_status = 'publish' AND p.post_date > now() - interval 3 month AND p.id = r.object_id AND r.term_taxonomy_id in ($imploded)" ;
			
		$all_posts = $wpdb -> get_results($str);
		
		$count = 0 ;

		foreach ($all_posts as $post) {
			setup_postdata($post);
			if (!in_array($post -> ID, $results)) {
				$this -> anglr_import_filtered_post($post -> ID);
				$count++;
			}
		}
		return $count;
	}

	public function anglr_persist_article_metadata($post_id, $article_id, $status) {
		global $wpdb;
		
		$sql = "INSERT INTO {$wpdb->prefix}anglr_post_articles_id (post_id,article_id, status) VALUES (%d,%s,%s) ON DUPLICATE KEY UPDATE post_id = %d";
		// var_dump($sql); // debug
		$sql = $wpdb->prepare($sql,$post_id,$article_id,$status,$post_id);
		// var_dump($sql); // debug
		$wpdb->query($sql);	
	}

	public function anglr_get_article_metadata($post_id) {
		global $wpdb;
		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		$sql = $wpdb -> prepare('SELECT article_id, status FROM ' . $tablename . ' WHERE post_id = %d', $post_id);
		$row = $wpdb -> get_row($sql, ARRAY_A);
		return $row;
	}

	public function anglr_update_status($post_id, $status) {
		global $wpdb;
		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		$update = array('status' => $status);
		$where = array('post_id' => $post_id);
		$wpdb -> update($tablename, $update, $where);
	}

}
?>