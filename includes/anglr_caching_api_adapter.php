<?php

class AnglrCachingAPIAdapter extends AnglrAPI {
	
	const OUT_DATED= 'cache_out_dated';
	const CACHE_MISS = 'cache_miss';

	public function __construct() {
		$this -> api = new AnglrAPIClient();
	}

	public function find_by_topic_and_context($bigram, $context) {
		return $this -> api -> find_by_topic_and_context($bigram, $context);
	}

	public function find_similar($article_id) {
		$search_result = $this -> find_similar_in_cache($article_id);
		// null means cache_miss or out_dated
		if ($search_result==self::CACHE_MISS || $search_result==self::OUT_DATED) {
			$cache_event = $search_result;
			$search_result = $this -> api -> find_similar($article_id);
			if($search_result){

				$this->update_cache($article_id, json_encode($search_result));
				return $search_result;
			} else if($cache_event == self::OUT_DATED){
				// API not available, but we have something OUT_DATED in cache
				$search_result = $this->get_outdated($article_id);
				return $search_result;
			}
		}
		return $search_result;
	}

	public function free_text_search($title, $content) {
		return $this -> api -> free_text_search($title, $content);
	}

	/**
	 * Query the cache for Find Similar results
	 */
	private function find_similar_in_cache($article_id) {
		// Fetch cached result from database
		global $wpdb;
		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		$sql = $wpdb -> prepare('SELECT cached_result, cache_timestamp FROM ' . $tablename . ' WHERE article_id = %s', $article_id);
		$row = $wpdb -> get_row($sql, ARRAY_A);
		
		$cached_result = $row['cached_result'];
		$cache_timestamp = $row['cache_timestamp'];
		if ($cached_result) {
			// check timestamp, if older than 15 minutes the return nothing
			$interval = date_create_from_format('Y-m-d H:i:s',$cache_timestamp)->diff(new DateTime());
			
			$minutes_ago= $interval->i;

			if($minutes_ago > 15){
				// let client know, there is a record, but it is out-dated

				return self::OUT_DATED;
			}

			return json_decode($cached_result);		
		}

		// return cache miss if nothing is found
		return self::CACHE_MISS;
	}
	
	private function get_outdated($article_id){
		// Fetch cached result from database
		global $wpdb;
		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		$sql = $wpdb -> prepare('SELECT cached_result, cache_timestamp FROM ' . $tablename . ' WHERE article_id = %d', $article_id);
		$row = $wpdb -> get_row($sql, ARRAY_A);
		$cached_result = $row['cached_result'];
		$cache_timestamp = $row['cache_timestamp'];
		if ($cached_result) {
			return json_decode($cached_result);		
		}
		return self::CACHE_MISS;
	}
	
	private function update_cache($article_id, $search_result){
		global $wpdb;
		$tablename = $wpdb -> prefix . "anglr_post_articles_id";
		
		// $update = array('cached_result'=> $search_result, 'cache_timestamp'=> date_create()->format('Y-m-d H:i:s'));
		$update = array('cached_result'=> $search_result, 'cache_timestamp'=> current_time('mysql',1));
		$where = array('article_id' => $article_id);
		$wpdb->update($tablename, $update, $where);
	}

}
?>