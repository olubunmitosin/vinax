<?php
/**
 * Snax Memes Submission Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}


add_action( 'init',     'snax_register_meme_template_post_type', 11 );
/**
 * Register post type for a single "Poll"
 */
function snax_register_meme_template_post_type() {
	$args = array(
		'labels' => array(
			'name'                  => _x( 'Meme Templates', 'post type general name', 'snax' ),
			'singular_name'         => _x( 'Meme Template', 'post type singular name', 'snax' ),
			'menu_name'             => _x( 'Meme Templates', 'admin menu', 'snax' ),
			'name_admin_bar'        => _x( 'Meme Template', 'add new on admin bar', 'snax' ),
			'add_new'               => _x( 'Add New', 'poll item', 'snax' ),
			'add_new_item'          => __( 'Add New Meme Template', 'snax' ),
			'new_item'              => __( 'New Meme Template', 'snax' ),
			'edit_item'             => __( 'Edit Meme Template', 'snax' ),
			'view_item'             => __( 'View Meme Template', 'snax' ),
			'all_items'             => __( 'All Meme Templates', 'snax' ),
			'search_items'          => __( 'Search Meme Templates', 'snax' ),
			'parent_item_colon'     => __( 'Parent Meme Templates:', 'snax' ),
			'not_found'             => __( 'No Meme Templates found.', 'snax' ),
			'not_found_in_trash'    => __( 'No Meme Templates found in Trash.', 'snax' ),
		),
		'public'                    => true,
		// Below values are inherited from the 'public' if not set.
		// ------.
		'exclude_from_search'       => false,       // for readers
		'publicly_queryable'        => true,        // for readers
		'show_in_nav_menus'         => true,       	// for authors
		'show_ui'                   => true,        // for authors
		'rewrite'            		=> array(
			'slug' => snax_get_url_var( 'meme_template' ),
			'feeds' 				=> true,
		),
		'has_archive'        => true,
		// ------.
		'supports'                  => array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
		),
	);

	if ( ! defined( 'BTP_DEV' ) || ! BTP_DEV ) {
		$args['show_ui'] = false;
		$args['show_in_nav_menus'] = false;
	}

	register_post_type( snax_get_meme_template_post_type(), apply_filters( 'snax_meme_template_post_type_args', $args ) );
}

/**
 * Return meme template post type name
 *
 * @return string
 */
function snax_get_meme_template_post_type() {
	return 'snax_meme_template';
}

add_action( 'pre_get_posts', 'snax_meme_archive_filter_by_template' );
/**
 * Apply meme template filter.
 *
 * @param WP_Query $query Archive main query.
 */
function snax_meme_archive_filter_by_template( $query ) {
	if ( is_archive() && isset( $query->query[ snax_get_snax_format_taxonomy_slug() ] ) && 'meme' === $query->query[ snax_get_snax_format_taxonomy_slug() ] ) {
		$filter = $_GET[ snax_meme_get_archive_filter_by_template_query_var() ];
		if ( get_post( (int) $filter ) ) {
			$query->set('meta_query', array(
				array(
					'key'   => '_snax_meme_template',
					'value' => (int) $filter,
				),
			));
		}
	}
}

add_filter( 'get_the_archive_title', 'snax_meme_archive_filter_by_template_title', 10, 1 );
/**
 * Filter archive title by meme template
 *
 * @param  string $title  The title.
 * @return string
 */
function snax_meme_archive_filter_by_template_title( $title ) {
	if ( ! isset( $_GET[ snax_meme_get_archive_filter_by_template_query_var() ] ) ) {
		return $title;
	}
	$filter = $_GET[ snax_meme_get_archive_filter_by_template_query_var() ];
	$template_title = get_the_title( (int) $filter );
	if ( ! empty( $template_title ) ) {
		$title = __( 'Memes: ', 'snax' ) . $template_title;
	}
	return $title;
}

/**
 * Get archive filter by meme template query var.
 *
 * @return string
 */
function snax_meme_get_archive_filter_by_template_query_var() {
	return apply_filters( 'snax_meme_get_archive_filter_by_template_query_var', 'meme_template' );
}

add_action( 'admin_notices', 'snax_meme_add_admin_import_notice' );

function snax_meme_add_admin_import_notice() {
	$screen = get_current_screen();
	if ( 'edit' === $screen->parent_base && $screen->post_type === snax_get_meme_template_post_type() ) {
		?>
		<div class="notice notice-info">
			<p>
				<?php 
				$url = get_admin_url( null, 'options-general.php?page=snax-general-settings' );
				echo wp_kses_post( sprintf( __( 'Most popular meme templates can be automatically imported in the <a href="%s">Snax options panel</a>', 'snax' ), $url ) ) ;?>
			</p>
		</div>
		<?php
	}
}

/**
 * Get the count of memes by template.
 *
 * @param int $template  Meme template id.
 * @return int
 */
function snax_count_memes_by_template( $template ) {
	$count_query = new WP_Query( array(
		'meta_query'    => array(
			array(
				'key'       => '_snax_meme_template',
				'value'     => $template,
				'compare'   => '=',
			),
		),
	) );
	return $count_query->post_count;
}

add_filter( 'the_content', 'snax_add_recaption_and_see_more_to_single_meme_template', 1000, 1 );

/**
 * Add recaption and see more memes to meme template single view
 *
 * @param  string $content The cotnent
 * @return string
 */
function snax_add_recaption_and_see_more_to_single_meme_template( $content ) {
	if ( is_singular( snax_get_meme_template_post_type() ) ) {
		ob_start();
		snax_render_meme_recaption();
		snax_render_meme_see_similar();
		$content .= ob_get_clean();
	}
	return $content;
}
