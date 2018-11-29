<?php
/**
 * Quiz template part
 *
 * @package snax 1.11
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$login_permalink = wp_login_url( apply_filters( 'the_permalink', get_permalink() ) );
?>

<a href="<?php echo esc_url( $login_permalink );?>" ><?php echo esc_html__( 'Please login/register to play', 'snax' );?></a>
