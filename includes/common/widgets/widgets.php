<?php
/**
 * Snax Widgets
 *
 * @package snax
 * @subpackage Widgets
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Init widgets
 */
function snax_widgets_init() {
	register_widget( 'Snax_Widget_Lists' );
	register_widget( 'Snax_Widget_CTA' );
	register_widget( 'Snax_Widget_CTA_Button' );
	register_widget( 'Snax_Widget_Teaser' );
	register_widget( 'Snax_Widget_Latest_Votes' );
}

$widgets_path = trailingslashit( dirname( __FILE__ ) );

require_once $widgets_path . 'snax-widget-lists.class.php';
require_once $widgets_path . 'snax-widget-cta.class.php';
require_once $widgets_path . 'snax-widget-cta-button.class.php';
require_once $widgets_path . 'snax-widget-teaser.class.php';
require_once $widgets_path . 'snax-widget-latest-votes.class.php';