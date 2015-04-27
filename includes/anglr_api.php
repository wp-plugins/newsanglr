<?php

abstract class AnglrAPI {

	abstract public function find_by_topic_and_context($bigram, $context);
	abstract public function find_similar($article_id);
	abstract public function free_text_search($title, $content);

	/**
	 * Check the import status on an article
	 */
	public function status($article_id) {
		$api_response = wp_remote_get(ANGLR_API_STATUS_URL . $article_id . '/', array('timeout' => 120));
		$json = wp_remote_retrieve_body($api_response);
		if (empty($json))
			return false;
		// Decode the JSON object
		$json = json_decode($json);
		return $json -> status;
	}

	public function get_api_key() {
		$site_url = urlencode(site_url());
		$api_response = wp_remote_get(ANGLR_API_KEY_URL . $site_url . '/', array('timeout' => 120));
		$json = wp_remote_retrieve_body($api_response);
		if (empty($json))
			return false;
		// Decode the JSON object
		$json = json_decode($json);
		return $json -> api_key;
	}

	/**
	 * Import a post into NewsAnglr
	 * Return the article_id assigned by NewsAnglr
	 */
	public function import($the_post) {
		$options = get_option('anglr_wordpress_options');
		$the_post -> api_key = $options['api_key'];
		$json = json_encode($the_post);

		$api_response = wp_remote_post(ANGLR_API_IMPORT_URL, array('timeout' => 45, 'body' => $json, 'headers' => array('Content-Type' => 'application/json')));
		// print_r($api_response);
		$wp_error = is_wp_error($api_response);
		$response_code = wp_remote_retrieve_response_code($api_response);
		if ($response_code != 400) {
			$json = wp_remote_retrieve_body($api_response);

			// Make sure the request was successful or return false
			if (empty($json))
				return false;
			// Decode the JSON object
			$json = json_decode($json);

			return $json;

		} else {
			return;
		}
		// Get the JSON object
	}

}
?>