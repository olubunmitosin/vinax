<?php
/**
 * Snax Snippets
 *
 * @package snax
 * @subpackage Snippets
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/* -------------------------------------------------------------------------------- */

// How to check whether a post was added via the frontend submission form.
if ( 'post' === get_post_meta( $post_id, '_snax_origin', true ) ) {
	return true;
}

// Custom WP_Query.
$my_query = new WP_Query();







/* -------------------------------------------------------------------------------- */

// Frontend submission form states.
?>
<form class="snax-form-frontend snax-form-frontend-without-media"></form>

<form class="snax-form-frontend"></form>


