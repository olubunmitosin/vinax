<?php
/**
 * Custom ad location after the first snax item
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<div class="snax-ad-location snax-ad-location-after-item-1">
	<?php quads_ad( array( 'location' => 'snax_after_item_1' ) ); ?>
</div>
