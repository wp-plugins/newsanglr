<?php

class AnglrAPIClient extends AnglrAPI{

	var $version = '1.0.0';
	var $api_key = '';

	public function __construct() {

	}

	/**
	 * Find related articles by topic and context
	 * $context needs to be comma-separated list of words
	 */
	public function find_by_topic_and_context($bigram, $context) {
		$api_response = wp_remote_get(ANGLR_API_FINDBYTOPICCONTEXT_URL . '?root_bigram=' . $bigram . '&context=' . $context, array('timeout' => 120));
		$json = wp_remote_retrieve_body($api_response);
		if (empty($json))
			return false;
		// Decode the JSON object
		$json = json_decode($json);
		return $json;
	}

	/**
	 * Find similar articles
	 */
	public function find_similar($article_id) {
		$api_response = wp_remote_get(ANGLR_API_FINDSIM_URL . $article_id . '/', array('timeout' => 120));
		$json = wp_remote_retrieve_body($api_response);
		if (empty($json))
			return false;
		// Decode the JSON object
		$json = json_decode($json);
		return $json;
	}

	/**
	 * Perform free text search
	 */
	public function free_text_search($title, $content) {
		// Perform the GET
		$api_response = wp_remote_get(ANGLR_API_FREETEXT_URL . urlencode($title . ' ' . $content), array('timeout' => 120));
		$json = wp_remote_retrieve_body($api_response);
		if (empty($json))
			return false;
		// Decode the JSON object
		$json = json_decode($json);
		return $json;
	}

}
?>