<?php

class AnglrWidget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'anglr_widget_class', 'description' => 'NewsAnglr Widget');

		parent::__construct('AnglrWidget', 'NewsAnglr', $widget_ops);
	}

	/**
	 * Form shown in the admin section for the widget
	 */
	function form($instance) {
		$defaults = array('title' => 'NewsAnglr - related articles', 'count' => 5);
		$instance = wp_parse_args((array)$instance, $defaults);
		$title = $instance['title'];
		$count = $instance['count'];

		echo '<p>Title:';
		echo '<input class="widefat" name="' . $this -> get_field_name('title') . '" type="text" value="' . esc_attr($title) . '"/>';
		echo '</p>';
		echo '<p>Count:';
		echo '<input class="widefat" name="' . $this -> get_field_name('count') . '" type="text" value="' . esc_attr($count) . '"/>';
		echo '</p>';

	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = strip_tags($new_instance['count']);
		return $instance;
	}

	function widget($args, $instance) {

		extract($args);

		$options = get_option('anglr_wordpress_options');
		$api_key = $options['api_key'];

		$title = apply_filters('widget_title', $instance['title']);
		if (!empty($title)) { echo $before_title . $title . $after_title;
		};

		$count = empty($instance['count']) ? 5 : $instance['count'];

		$post = get_post();
		$articles = $post -> anglr_articles;

		$articles = array_slice($articles, 0, $count);
		// Make article count configurable
		foreach ($articles as $key => $value) {
			$img = $this -> get_image($value -> content);
			$content = $this -> get_snippet(strip_tags($value -> content), 15);
			$url = $value -> url;
			$url = ANGLR_API_TRACE_URL . $api_key . '/' . urlencode($url);
			echo '<div class="anglr_wdgt_article_container">';
			echo '	<div class="anglr_wdgt_article_img_wrapper">';
			if ($img['src'] != null) {
				echo '		<img src="' . $img['src'] . '" class="anglr_wdgt_article_img"/>';
			}
			echo '	</div>';
			echo '	<span class="anglr_wdgt_article_title"><a href="' . $url . '" target="_blank">' . $this -> get_snippet($value -> title) . '</a></span>';
			echo '	<p class="anglr_wdgt_article_snippet">' . $content . '...</p>';
			echo '</div>';
		}
	}

	function get_snippet($str, $count = 10) {
		return implode('', array_slice(preg_split('/([\s,\.;\?\!]+)/', $str, $count * 2 + 1, PREG_SPLIT_DELIM_CAPTURE), 0, $count * 2 - 1));
	}

	function get_image($html) {
		if (class_exists('DOMDocument')) {
			$doc = new DOMDocument();
			$doc -> loadHTML($html);
			$xml = simplexml_import_dom($doc);
			$images = $xml -> xpath('//img');
			if (count($images) > 0) {
				return $images[0];
			}
		} else {
			error_log('Class not found: DOMDocument. Please be sure to install the php-xml library.');
		}
		return null;
	}

}
?>