<?php

if (!defined('WP_UNINSTALL_PLUGIN'))
	exit();

// Delete all the NewsAnglr options from the options table
delete_option('anglr_wordpress_options');


global $wpdb;
$tablename = $wpdb -> prefix . "anglr_post_articles_id";
$wpdb->query("DROP TABLE IF EXISTS $tablename");

?>