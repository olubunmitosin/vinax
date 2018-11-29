<?php
/**
 * Snax Formats
 *
 * @package snax
 * @subpackage Formats
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$formats_path = trailingslashit( dirname( __FILE__ ) );
require_once $formats_path . 'functions.php';
require_once $formats_path . 'taxonomy.php';
require_once $formats_path . 'story/loader.php';
require_once $formats_path . 'lists/loader.php';
require_once $formats_path . 'quizzes/loader.php';
require_once $formats_path . 'polls/loader.php';
require_once $formats_path . 'meme/loader.php';
require_once $formats_path . 'audio/loader.php';
require_once $formats_path . 'video/loader.php';
require_once $formats_path . 'image/loader.php';
require_once $formats_path . 'gallery/loader.php';
require_once $formats_path . 'embed/loader.php';
require_once $formats_path . 'link/loader.php';
