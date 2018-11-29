<?php
/**
 * New image part of the new post form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<?php snax_get_template_part( 'form-upload-media', 'image' ); ?>
