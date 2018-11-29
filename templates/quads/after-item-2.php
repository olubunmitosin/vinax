<?php
/**
 * Custom ad location after the second snax item
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<div class="snax-ad-location snax-ad-location-after-item-2">
	<?php quads_ad( array( 'location' => 'snax_after_item_2' ) ); ?>
</div>
