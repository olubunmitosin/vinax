<?php
/**
 * Snax Vote Template Tags
 *
 * @package snax
 * @subpackage TemplateTags
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Render upvote/downvote box
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 * @param string      $class                CSS class.
 */
function snax_render_voting_box( $post = null, $user_id = 0, $class = 'snax-voting-simple' ) {
	if ( snax_show_item_voting_box( $post ) ) {
		$post = get_post( $post );

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$final_class = array(
			'snax-voting',
		);
		$final_class = array_merge( $final_class, explode( ' ', $class ) );
		?>
		<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $final_class ) ); ?>" data-snax-item-id="<?php echo absint( $post->ID ); ?>">
			<?php
			$snax_class = array(
				'snax-voting-score'
			);

			$snax_voting_score = snax_get_voting_score( $post );
			if ( 0 === $snax_voting_score ) {
				$snax_class[] = 'snax-voting-score-0';
			}
			?>

			<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
				<?php
				printf( wp_kses_post( _n( '<strong>%d</strong> point', '<strong>%d</strong> points', (int) $snax_voting_score, 'snax' ) ), (int) $snax_voting_score );
				?>
			</div>

			<?php
			if ( snax_show_item_upvote_link( $post ) ) :
				snax_render_upvote_link( $post, $user_id );
			endif;
			?>

			<?php
			if ( snax_show_item_downvote_link( $post ) ) :
				snax_render_downvote_link( $post, $user_id );
			endif;
			?>

		</div>
		<?php
	}
}

/**
 * Return HTML formatted link to upvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 *
 * @return string
 */
function snax_get_upvote_link( $post = null, $user_id = 0 ) {
	$post = get_post( $post );

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$classes = array(
		'snax-voting-upvote'
	);

	if ( snax_user_upvoted( $post->ID, $user_id ) ) {
		$classes[] = 'snax-user-voted';
	}

	$user = get_user_by( 'id', $user_id );

	// User with this id doesn't exist.
	if ( 0 !== $user_id && false === $user ) {
		return '';
	}

	// User exists.
	if ( $user ) {
		// Is logged-in but has no access.
		if ( $user->exists() && ! user_can( $user_id, 'snax_vote_items', $post->ID ) ) {
			return '';
		}

		// Is logged-out?
		if ( ! $user->exists() ) {
			$classes[] = 'snax-login-required';
		}
	} elseif ( snax_guest_voting_is_enabled() ) {
		// Guest can vote.
		$classes[] = 'snax-guest-voting';
	} else {
		// User not logged in.
		$classes[] = 'snax-login-required';
	}

	$link = sprintf(
		'<a href="#" class="' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '" title="%s" data-snax-item-id="%d" data-snax-author-id="%d" data-snax-nonce="%s">%s</a>',
		__( 'Upvote', 'snax' ),
		$post->ID,
		$user_id,
		wp_create_nonce( 'snax-vote-item' ),
		__( 'Upvote', 'snax' )
	);

	return $link;
}

/**
 * Render HTML formatted link to upvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 */
function snax_render_upvote_link( $post = null, $user_id = 0 ) {
	$link = snax_get_upvote_link( $post, $user_id );

	echo wp_kses( $link, array(
		'a' => array(
			'href'                  => array(),
			'class'                 => array(),
			'title'                 => array(),
			'data-snax-item-id'     => array(),
			'data-snax-author-id'   => array(),
			'data-snax-nonce'       => array(),
		),
	) );
}

/**
 * Return HTML formatted link to downvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 *
 * @return string
 */
function snax_get_downvote_link( $post = null, $user_id = 0 ) {
	$post = get_post( $post );

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$classes = array(
		'snax-voting-downvote'
	);

	if ( snax_user_downvoted( $post->ID, $user_id ) ) {
		$classes[] = 'snax-user-voted';
	}

	$user = get_user_by( 'id', $user_id );

	// User with this id doesn't exist.
	if ( 0 !== $user_id && false === $user ) {
		return '';
	}

	// User exists.
	if ( $user ) {
		// Is logged-in but has no access.
		if ( $user->exists() && ! user_can( $user_id, 'snax_vote_items', $post->ID ) ) {
			return '';
		}

		// Is logged-out?
		if ( ! $user->exists() ) {
			$classes[] = 'snax-login-required';
		}
	} elseif ( snax_guest_voting_is_enabled() ) {
		// Guest can vote.
		$classes[] = 'snax-guest-voting';
	} else {
		// User not logged in.
		$classes[] = 'snax-login-required';
	}

	$link = sprintf(
		'<a href="#" class="' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '" title="%s" data-snax-item-id="%d" data-snax-author-id="%d" data-snax-nonce="%s">%s</a>',
		__( 'Downvote', 'snax' ),
		$post->ID,
		$user_id,
		wp_create_nonce( 'snax-vote-item' ),
		__( 'Downvote', 'snax' )
	);

	return $link;
}

/**
 * Render HTML formatted link to downvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 */
function snax_render_downvote_link( $post = null, $user_id = 0 ) {
	$link = snax_get_downvote_link( $post, $user_id );

	echo wp_kses( $link, array(
		'a' => array(
			'href'                  => array(),
			'class'                 => array(),
			'title'                 => array(),
			'data-snax-item-id'     => array(),
			'data-snax-author-id'   => array(),
			'data-snax-nonce'       => array(),
		),
	) );
}

/**
 * Whether there are more votes available in the loop
 *
 * @return bool
 */
function snax_votes() {

	$have_posts = snax()->votes_query->have_posts();

	// Reset the post data when finished.
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();
	}

	return $have_posts;
}

/**
 * Loads up the current vote in the loop
 */
function snax_the_vote() {
	snax()->votes_query->the_post();
}

/**
 * Output the pagination count
 */
function snax_votes_pagination_count() {
	echo esc_html( snax_get_votes_pagination_count() );
}

/**
 * Return the pagination count
 *
 * @return string
 */
function snax_get_votes_pagination_count() {
	$query = snax()->votes_query;

	if ( empty( $query ) ) {
		return false;
	}

	// Set pagination values.
	$start_num = intval( ( $query->paged - 1 ) * $query->posts_per_page ) + 1;
	$from_num  = snax_number_format( $start_num );
	$to_num    = snax_number_format( ( $start_num + ( $query->posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts : $start_num + ( $query->posts_per_page - 1 ) );
	$total_int = (int) ! empty( $query->found_posts ) ? $query->found_posts : $query->post_count;
	$total     = snax_number_format( $total_int );

	// Several topics in a forum with a single page.
	if ( empty( $to_num ) ) {
		$retstr = sprintf( _n( 'Viewing %1$s vote', 'Viewing %1$s votes', $total_int, 'snax' ), $total );

		// Several topics in a forum with several pages.
	} else {
		$retstr = sprintf( _n( 'Viewing vote %2$s (of %4$s total)', 'Viewing %1$s votes - %2$s through %3$s (of %4$s total)', $total_int, 'snax' ), $query->post_count, $from_num, $to_num, $total );
	}

	// Filter and return.
	return apply_filters( 'snax_get_votes_pagination_count', esc_html( $retstr ) );
}

/**
 * Output pagination links
 */
function snax_votes_pagination_links() {
	echo wp_kses_post( snax_get_votes_pagination_links() );
}

/**
 * Return pagination links
 *
 * @return string
 */
function snax_get_votes_pagination_links() {
	$query = snax()->votes_query;

	if ( empty( $query ) ) {
		return false;
	}

	return apply_filters( 'snax_get_votes_pagination_links', $query->pagination_links );
}


/**
 * Format vote count number
 *
 * @param int $number               Input value.
 *
 * @return string
 */
function snax_format_vote_count( $number ) {
	$string = number_format_i18n( intval( $number ) );

	return $string;
}
