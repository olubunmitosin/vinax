<?php
/**
 * Snax Vote Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Return value representing the upvote
 *
 * @return int
 */
function snax_get_upvote_value() {
	return 1;
}

/**
 * Return value representing the downvote
 *
 * @return int
 */
function snax_get_downvote_value() {
	return - 1;
}

/**
 * Return total number of upvotes
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_upvote_count( $post_id = 0 ) {
	$post = get_post( $post_id );

	$count = (int) get_post_meta( $post->ID, '_snax_upvote_count', true );

	return (int) apply_filters( 'snax_get_upvote_count', $count, $post );
}

/**
 * Return percentage proportion between upvotes and all votes
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return float|int
 */
function snax_get_upvotes_percentage( $post_id = 0 ) {
	$post   = get_post( $post_id );
	$result = 0;
	$total  = snax_get_vote_count( $post );

	if ( $total ) {
		$result = snax_get_upvote_count( $post ) / $total * 100;
	}

	return $result;
}

/**
 * Return total number of downvotes
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_downvote_count( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (int) get_post_meta( $post->ID, '_snax_downvote_count', true );
}

/**
 * Return percentage proportion between downvotes and all votes
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return float|int
 */
function snax_get_downvotes_percentage( $post_id = 0 ) {
	$post   = get_post( $post_id );
	$result = 0;
	$total  = snax_get_vote_count( $post );

	if ( $total ) {
		$result = snax_get_downvote_count( $post ) / $total * 100;
	}

	return $result;
}

/**
 * Return total number of votes
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_vote_count( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (int) get_post_meta( $post->ID, '_snax_vote_count', true );
}

/**
 * Return voting score for the $post
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_voting_score( $post_id = 0 ) {
	$post = get_post( $post_id );

	$score = (int) get_post_meta( $post->ID, '_snax_vote_score', true );

	return (int) apply_filters( 'snax_get_voting_score', $score, $post );
}

/**
 * Register new upvote for item
 *
 * @param array $vote_arr Vote arguments.
 *
 * @return bool|WP_Error
 */
function snax_upvote_item( $vote_arr ) {
	$vote_arr['vote'] = 1;

	// Register vote data.
	$inserted = snax_insert_vote( $vote_arr );

	if ( is_wp_error( $inserted ) ) {
		return $inserted;
	}

	return true;
}

/**
 * Register new downvote for item
 *
 * @param array $vote_arr Vote arguments.
 *
 * @return bool|WP_Error
 */
function snax_downvote_item( $vote_arr ) {
	$vote_arr['vote'] = - 1;

	// Register vote data.
	$inserted = snax_insert_vote( $vote_arr );

	if ( is_wp_error( $inserted ) ) {
		return $inserted;
	}

	return true;
}

/**
 * Toggle user vote (from upvote to downvote and vice versa)
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool|WP_Error
 */
function snax_toggle_vote( $item_id, $user_id ) {
	if ( ! snax_user_voted( $item_id, $user_id ) ) {
		return new WP_Error( 'snax_toogle_vote_not_allowed', __( 'User has not voted!', 'snax' ) );
	}

	$new_vote = snax_user_upvoted( $item_id, $user_id ) ? snax_get_downvote_value() : snax_get_upvote_value();

	$ret = snax_remove_vote( $item_id, $user_id );

	if ( is_wp_error( $ret ) ) {
		return $ret;
	}

	$ret = snax_insert_vote( array(
		'post_id'   => $item_id,
		'author_id' => $user_id,
		'vote'      => $new_vote,
	) );

	if ( is_wp_error( $ret ) ) {
		return $ret;
	}

	return true;
}

/**
 * Insert vote into the database.
 *
 * @param array $vote_arr Vote config.
 *
 * @return bool|WP_Error
 */
function snax_insert_vote( $vote_arr ) {
	$defaults = array(
		'post_id'   => get_the_ID(),
		'author_id' => get_current_user_id(),
		'vote'      => 1,
	);

	$vote_arr = wp_parse_args( $vote_arr, $defaults );

	global $wpdb;
	$table_name = $wpdb->prefix . snax_get_votes_table_name();

	$post_date  = current_time( 'mysql' );
	$ip_address = false;
	$host = '';

	$affected_rows = $wpdb->insert(
		$table_name,
		array(
			'post_id'     => $vote_arr['post_id'],
			'vote'        => $vote_arr['vote'],
			'author_id'   => $vote_arr['author_id'],
			'author_ip'   => $ip_address ? $ip_address : '',
			'author_host' => $host ? $host : '',
			'date'        => $post_date,
			'date_gmt'    => get_gmt_from_date( $post_date ),
		),
		array(
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	if ( false === $affected_rows ) {
		return new WP_Error( 'snax_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'snax' ) );
	}

	snax_update_votes_metadata( $vote_arr['post_id'] );

	do_action( 'snax_vote_added', $vote_arr );

	return true;
}

/**
 * Remove user vote
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool|WP_Error
 */
function snax_remove_vote( $item_id, $user_id ) {
	if ( ! snax_user_voted( $item_id, $user_id ) ) {
		return new WP_Error( 'snax_has_not_voted', __( 'User has not voted for this item!', 'snax' ) );
	}

	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$vote = snax_get_user_vote( $item_id, $user_id );

	$updated_rows = $wpdb->query(
		$wpdb->prepare(
			"
            DELETE FROM $votes_table_name
		    WHERE post_id = %d AND author_id = %d AND vote = %d
		 	LIMIT 1
			",
			$item_id,
			$user_id,
			$vote
		)
	);

	if ( false === $updated_rows ) {
		return new WP_Error( 'snax_remove_vote_failed', __( 'User vote could not be removed!', 'snax' ) );
	}

	snax_update_votes_metadata( $item_id );

	return true;
}

/**
 * Generate voting stats (upvotes, downvotes, total, score)
 *
 * @param int $item_id          Item id.
 *
 * @return array
 */
function snax_generate_votes_metadata( $item_id = 0 ) {
	$post = get_post( $item_id );

	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$votes = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT vote, count(vote) AS cnt
			FROM $votes_table_name
			WHERE post_id = %d
			GROUP BY vote",
			$post->ID
		)
	);

	$upvotes   = 0;
	$downvotes = 0;

	foreach ( $votes as $group_data ) {
		if ( snax_get_upvote_value() === (int) $group_data->vote ) {
			$upvotes = (int) $group_data->cnt;
		}

		if ( snax_get_downvote_value() === (int) $group_data->vote ) {
			$downvotes = (int) $group_data->cnt;
		}
	}

	$meta = array(
		'upvotes'   => (int) $upvotes,
		'downvotes' => (int) $downvotes,
		'total'     => (int) $upvotes + $downvotes,
		'score'     => (int) $upvotes - $downvotes,
	);

	return apply_filters( 'snax_votes_metadata', $meta, $post->ID );
}

/**
 * Return vote user added for a post
 *
 * @param int $author_id        Author id.
 * @param int $post_id          Post id.
 *
 * @return bool|array       False if there is no vote.
 */
function snax_get_vote_by_user( $author_id, $post_id = null ) {
	$post = get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$votes = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT *
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			",

			$post->ID,
			$author_id
		)
	);

	if ( ! empty( $votes ) ) {
		// It can be only one user vote for one post.
		return $votes[0];
	}

	return false;
}

/**
 * Return user latest votes
 *
 * @param int $author_id        Author id.
 * @param int $max              Max number of returned votes.
 *
 * @return array
 */
function snax_get_user_latest_votes( $author_id, $max = 5 ) {
	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	if ( $author_id ) {
		$votes = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT *
			FROM $votes_table_name
			WHERE author_id = %d
			ORDER BY date DESC
			LIMIT %d
			",
				$author_id,
				$max
			)
		);
	} else {
		$votes = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT *
			FROM $votes_table_name
			ORDER BY date DESC
			LIMIT %d
			",
				$max
			)
		);
	}

	if ( ! empty( $votes ) ) {
		return $votes;
	}

	return array();
}

/**
 * Update voting stats (upvotes, downvotes, total, score)
 *
 * @param int   $item_id            Item id.
 * @param array $meta               Current meta value.
 *
 * @return bool
 */
function snax_update_votes_metadata( $item_id = 0, $meta = array() ) {
	$post = get_post( $item_id );

	if ( empty( $meta ) ) {
		$meta = snax_generate_votes_metadata( $post );
	}

	if ( empty( $meta ) ) {
		return false;
	}

	update_post_meta( $post->ID, '_snax_upvote_count', $meta['upvotes'] );
	update_post_meta( $post->ID, '_snax_downvote_count', $meta['downvotes'] );
	update_post_meta( $post->ID, '_snax_vote_count', $meta['total'] );
	update_post_meta( $post->ID, '_snax_vote_score', $meta['score'] );

	return true;
}

/**
 * Set default values for votes.
 *
 * @param int $item_id      Item id.
 */
function snax_init_votes_metadata( $item_id ) {
	$meta = array(
		'upvotes'   => 0,
		'downvotes' => 0,
		'total'     => 0,
		'score'     => 0,
	);

	snax_update_votes_metadata( $item_id, $meta );
}

/**
 * Return user votes for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_get_user_vote( $item_id, $user_id ) {
	// Guest voting disabled.
	if ( 0 === $user_id && ! snax_guest_voting_is_enabled() ) {
		return 0;
	}

	// Guest voting enabled.
	if ( 0 === $user_id && snax_guest_voting_is_enabled() ) {
		// Read cookie setn by client.
		$vote_cookie = filter_input( INPUT_POST, 'snax_user_voted', FILTER_SANITIZE_STRING );

		// If not sent, read cookie from server.
		if ( ! $vote_cookie ) {
			$vote_cookie = filter_input( INPUT_COOKIE, 'snax_vote_item_' . $item_id, FILTER_SANITIZE_STRING );
		}

		switch ( $vote_cookie ) {
			case 'upvote':
				return snax_get_upvote_value();
			case 'downvote':
				return snax_get_downvote_value();
			default:
				return 0;
		}
	}

	// User logged in.
	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$vote = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT vote
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id
		)
	);

	return (int) $vote;
}

/**
 * Check whether user has already voted for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return mixed            User vote if voted, false othrwise.
 */
function snax_user_voted( $item_id, $user_id ) {
	return 0 !== snax_get_user_vote( $item_id, $user_id );
}

/**
 * Check whether user has already upvoted for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_user_upvoted( $item_id, $user_id ) {
	return snax_get_upvote_value() === snax_get_user_vote( $item_id, $user_id );
}

/**
 * Check whether user has already downvoted for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_user_downvoted( $item_id, $user_id ) {
	return snax_get_downvote_value() === snax_get_user_vote( $item_id, $user_id );
}

/**
 * Check whether user upvoted
 *
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_has_user_upvotes( $user_id = 0 ) {
	$has_upvotes = snax_has_user_votes( snax_get_upvote_value(), $user_id );

	return apply_filters( 'snax_has_user_upvotes', $has_upvotes, $user_id );
}

/**
 * Check whether user downvoted
 *
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_has_user_downvotes( $user_id = 0 ) {
	$has_downvotes = snax_has_user_votes( snax_get_downvote_value(), $user_id );

	return apply_filters( 'snax_has_user_downvotes', $has_downvotes, $user_id );
}

/**
 * Check whether user voted
 *
 * @param int $vote Vote type.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_has_user_votes( $vote, $user_id = 0 ) {
	$user_id = (int) $user_id;

	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$query = snax_get_user_votes_query( array(
		'author' => $user_id,
		'vote'   => $vote,
	) );

	return apply_filters( 'snax_has_user_votes', $query->have_posts(), $user_id );
}

/**
 * Join votes table and filter by it
 *
 * @param array $clauses Query clauses (join, where etc).
 *
 * @return array
 */
function snax_filter_by_votes_table( $clauses ) {
	global $wpdb;
	global $snax_votes_args;

	$author_id = (int) $snax_votes_args['author'];

	if ( 0 === $author_id ) {
		return $clauses;
	}

	// JOIN.
	if ( ! empty( $clauses['join'] ) ) {
		$clauses['join'] .= ' '; // Add a space only if we have to.
	}

	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$clauses['join'] .= "JOIN {$votes_table_name} ON {$votes_table_name}.post_id = {$wpdb->posts}.ID AND {$votes_table_name}.author_id={$author_id}";

	// WHERE.
	if ( isset( $snax_votes_args['vote'] ) ) {
		$vote = (int) $snax_votes_args['vote'];

		// WP always start with AND, because there's always a '1=1' statement as the first statement of the WHERE clause.
		$clauses['where'] .= " AND {$votes_table_name}.vote={$vote}";
	}

	return $clauses;
}

/**
 * Set up votes query
 *
 * @param array $args Query args.
 *
 * @return WP_Query
 */
function snax_get_user_votes_query( $args = array() ) {
	global $wp_rewrite;

	// Posts query args.
	$r = array(
		'post_type'      => array( 'post', snax_get_item_post_type() ),
		'posts_per_page' => snax_get_votes_per_page(),
		'paged'          => snax_get_paged(),
		'max_num_pages'  => false,
	);

	// Modify query.
	global $snax_votes_args;
	$snax_votes_args = $args;

	add_filter( 'posts_clauses', 'snax_filter_by_votes_table', 10, 2 );

	// Make query.
	$query = new WP_Query( $r );

	// Clean up.
	remove_filter( 'posts_clauses', 'snax_filter_by_votes_table' );
	unset( $GLOBALS['snax_votes_args'] );

	// Limited the number of pages shown.
	if ( ! empty( $r['max_num_pages'] ) ) {
		$query->max_num_pages = $r['max_num_pages'];
	}

	// If no limit to posts per page, set it to the current post_count.
	if ( - 1 === $r['posts_per_page'] ) {
		$r['posts_per_page'] = $query->post_count;
	}

	// Add pagination values to query object.
	$query->posts_per_page = $r['posts_per_page'];
	$query->paged          = $r['paged'];

	// Only add pagination if query returned results.
	if ( ( (int) $query->post_count || (int) $query->found_posts ) && (int) $query->posts_per_page ) {

		// Limit the number of topics shown based on maximum allowed pages.
		if ( ( ! empty( $r['max_num_pages'] ) ) && $query->found_posts > $query->max_num_pages * $query->post_count ) {
			$query->found_posts = $query->max_num_pages * $query->post_count;
		}

		$base = add_query_arg( 'paged', '%#%' );

		$base = apply_filters( 'snax_votes_pagination_base', $base, $r );

		// Pagination settings with filter.
		$pagination = apply_filters( 'snax_votes_pagination', array(
			'base'      => $base,
			'format'    => '',
			'total'     => $r['posts_per_page'] === $query->found_posts ? 1 : ceil( (int) $query->found_posts / (int) $r['posts_per_page'] ),
			'current'   => (int) $query->paged,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'mid_size'  => 1,
		) );

		// Add pagination to query object.
		$query->pagination_links = paginate_links( $pagination );

		// Remove first page from pagination.
		$query->pagination_links = str_replace( $wp_rewrite->pagination_base . "/1/'", "'", $query->pagination_links );
	}

	snax()->votes_query = $query;

	return $query;
}

/**
 * Load voting box for allowed post types
 *
 * @param string $content		Snax shortcode content.
 *
 * @return string
 */
function snax_allow_voting_for_post_types( $content ) {
	// Snax formats are handled separately.
	if ( snax_is_format() ) {
		return $content;
	}

	// Load only for supported post types.
	$post_type = get_post_type();
	$allowed_post_types = snax_voting_get_post_types();

	if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
		return $content;
	}

	ob_start();
	snax_get_template_part( 'posts/content' );
	$content .= ob_get_clean();

	return $content;
}

/**
 * Fake post vote count
 *
 * @param int     $score		Real vote count.
 * @param WP_Post $post			Post object.
 *
 * @return int
 */
function snax_fake_vote_count( $score, $post ) {
	if ( empty( $post ) ) {
		return $score;
	}

	if ( snax_is_item( $post ) ) {
		return $score;
	}

	// Get value defined for that single post (can be a number or empty string).
	$fake_count = get_post_meta( $post->ID, '_snax_fake_vote_count', true );

	// If user has not defined the counter explicitly, calculate it based on global setup.
	if ( '' === $fake_count ) {
		$fake_base = (int) snax_get_fake_vote_count_base();

		// Only if fake base is set, we can apply fake count.
		if ( $fake_base > 0 ) {
			$fake_factor = snax_get_fake_factor( $post->post_date );

			$fake_count = round( $fake_base * $fake_factor );
		}
	}

	// Cast to int. It's safe only here.
	$fake_count = (int) $fake_count;

	$fake_count = (int) apply_filters( 'snax_fake_vote_count', $fake_count, $post->ID );

	return $score + $fake_count;
}

/**
 * Return fake factor based on post creation date
 *
 * @param string $date			Post creation date.
 *
 * @return float
 */
function snax_get_fake_factor( $date ) {
	$current_time = time();
	$date_time 	  = strtotime( $date );

	$day_in_seconds = 24 * 60 * 60;

	$days_diff = round( abs( $current_time - $date_time ) / $day_in_seconds );

	$t = $days_diff;	// Current time.
	$b = 0.1;			// Start value.
	$c = 0.9;			// Change in value.
	$d = 30;			// Duration.

	// Factor function doesn't return value equal to 1 after $d time.
	// Which is normal, as it's sinus, but we want to have 1 value after $d duration.
	if ( $days_diff > $d ) {
		return 1;
	}

	// EaseOutSine.
	$factor = $c * sin( $t / $d * (pi() / 2 ) ) + $b;

	return $factor;
}

/**
 * Adds voting to comments
 *
 * @param array      $args    Comment reply link arguments. See get_comment_reply_link()
 *                            for more information on accepted arguments.
 * @param WP_Comment $comment The object of the comment being replied to.
 * @param WP_Post    $post    The WP_Post object.
 *
 * @return array
 */
function snax_add_votes_to_comments( $args, $comment, $post ) {


	return $args;
}

function snax_disable_fake_votes( $post_id, $type ) {
	if ( snax_is_fake_vote_disabled_for_new() ) {
		// Set post fake votes to 0.
		update_post_meta( $post_id, '_snax_fake_vote_count', 0 );
	}
}
