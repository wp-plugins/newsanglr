<?php

if (!defined('ANGLR_API_BASE_URL'))
	define('ANGLR_API_BASE_URL', 'http://127.0.0.1:8000');
if (!defined('ANGLR_API_FREETEXT_URL'))
	define('ANGLR_API_FREETEXT_URL', ANGLR_API_BASE_URL . '/newsanglr/search/?q=');
if (!defined('ANGLR_API_IMPORT_URL'))
	define('ANGLR_API_IMPORT_URL', ANGLR_API_BASE_URL . '/newsanglr/import');
if (!defined('ANGLR_API_FINDSIM_URL'))
	define('ANGLR_API_FINDSIM_URL', ANGLR_API_BASE_URL . '/newsanglr/search/');
if (!defined('ANGLR_API_STATUS_URL'))
	define('ANGLR_API_STATUS_URL', ANGLR_API_BASE_URL . '/newsanglr/status/');
if (!defined('ANGLR_API_KEY_URL'))
	define('ANGLR_API_KEY_URL', ANGLR_API_BASE_URL . '/newsanglr/api_key/');
if (!defined('ANGLR_API_FINDBYTOPICCONTEXT_URL'))
	define('ANGLR_API_FINDBYTOPICCONTEXT_URL', ANGLR_API_BASE_URL . '/newsanglr/json/get-topic-articles/');
if(!defined('ANGLR_API_TRACE_URL'))
	define('ANGLR_API_TRACE_URL', ANGLR_API_BASE_URL. '/trace/');

?>