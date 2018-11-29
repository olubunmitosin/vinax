<?php
/**
 * Common Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Register poll image sizes
 */
function snax_add_polls_image_sizes() {
	$width = 313;

	// Square size.
	add_image_size( 'poll-answer-grid-1of2', $width );
}

/**
 * Return default poll settings
 *
 * @param string $name		Optional. Setting name.
 *
 * @return mixed			Array or single value if $name set
 */
function snax_get_poll_defaults( $name = '' ) {
	$defaults = array(
		'answers_set'	                => 'yes-no',
		'reveal_correct_wrong_answers'	=> 'immediately',
		'one_question_per_page' 		=> 'none',
		'shuffle_questions' 			=> 'none',
		'questions_per_poll' 			=> '',
		'shuffle_answers' 				=> 'none',
		'share_results' 				=> 'standard',
		'share_to_unlock' 				=> 'none',
	);

	$defaults = apply_filters( 'snax_poll_defaults', $defaults );

	if ( $name && isset( $defaults[ $name ] ) ) {
		return $defaults[ $name ];
	}

	return $defaults;
}

/**
 * Return poll setting
 *
 * @param string  $name			Setting name.
 * @param WP_Post $poll			Options. Post object or id.
 *
 * @return string
 */
function snax_get_poll_setting( $name, $poll = null ) {
	$poll = get_post( $poll );

	$prefix = '_snax_';

	$setting = get_post_meta( $poll->ID, $prefix . $name, true );

	if ( empty( $setting ) ) {
		$setting = snax_get_poll_defaults( $name );
	}

	return $setting;
}

/**
 * Return list of valid poll types
 *
 * @return array
 */
function snax_get_poll_types() {
	return apply_filters( 'snax_poll_types', array(
		snax_get_classic_poll_type(),
		snax_get_versus_poll_type(),
		snax_get_binary_poll_type(),
	) );
}

/**
 * Return type name for Classic poll
 *
 * @return string
 */
function snax_get_classic_poll_type() {
	return 'classic';
}

/**
 * Return type label for Classic poll
 *
 * @return string
 */
function snax_get_classic_poll_label() {
	return __( 'Classic', 'snax' );
}

/**
 * Check whether the poll is a Classic poll
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string
 */
function snax_is_classic_poll( $poll = null ) {
	$poll = get_post( $poll );

	return snax_get_classic_poll_type() === snax_get_poll_type( $poll );
}

/**
 * Return type name for Versus poll
 *
 * @return string
 */
function snax_get_versus_poll_type() {
	return 'versus';
}

/**
 * Return type label for Versus poll
 *
 * @return string
 */
function snax_get_versus_poll_label() {
	return __( 'Versus', 'snax' );
}

/**
 * Check whether the poll is a Versus poll
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string
 */
function snax_is_versus_poll( $poll = null ) {
	$poll = get_post( $poll );

	return snax_get_versus_poll_type() === snax_get_poll_type( $poll );
}

/**
 * Return type name for Binary poll
 *
 * @return string
 */
function snax_get_binary_poll_type() {
	return 'binary';
}

/**
 * Return type label for Binary poll
 *
 * @return string
 */
function snax_get_binary_poll_label() {
	return __( 'Binary', 'snax' );
}

/**
 * Check whether the poll is a Binary poll
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string
 */
function snax_is_binary_poll( $poll = null ) {
	$poll = get_post( $poll );

	return snax_get_binary_poll_type() === snax_get_poll_type( $poll );
}

/**
 * Return poll type
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string
 */
function snax_get_poll_type( $poll = null ) {
	$poll = get_post( $poll );

	return get_post_meta( $poll->ID, '_snax_poll_type', true );
}

/**
 * Return poll type label
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string | bool    False is not a poll type.
 */
function snax_get_poll_type_label( $poll = null ) {
	$poll = get_post( $poll );

	$type = get_post_meta( $poll->ID, '_snax_poll_type', true );

	if ( $type === snax_get_classic_poll_type() ) {
		return snax_get_classic_poll_label();
	}

	if ( $type === snax_get_versus_poll_type() ) {
		return snax_get_versus_poll_label();
	}

	if ( $type === snax_get_binary_poll_type() ) {
		return snax_get_binary_poll_label();
	}

	return false;
}

/**
 * Check whether type of poll is valid
 *
 * @param string $type		poll type.
 *
 * @return bool
 */
function snax_is_valid_poll_type( $type ) {
	$types = snax_get_poll_types();

	return in_array( $type, $types, true );
}

/**
 * Check whether the post is a poll
 *
 * @param WP_Post $poll		Optional. poll post or id.
 *
 * @return bool
 */
function snax_is_poll( $poll = null ) {
	return snax_get_poll_post_type() === get_post_type( $poll );
}

/**
 * Return poll post type name
 *
 * @return string
 */
function snax_get_poll_post_type() {
	return 'snax_poll';
}

/**
 * Return question post type name
 *
 * @return string
 */
function snax_get_poll_question_post_type() {
	return 'snax_poll_question';
}

/**
 * Return answer post type name
 *
 * @return string
 */
function snax_get_poll_answer_post_type() {
	return 'snax_poll_answer';
}

/**
 * Return result post type name
 *
 * @return string
 */
function snax_get_poll_result_post_type() {
	return 'snax_poll_result';
}

/**
 * Register all post types
 */
function snax_register_polls_post_types() {
	snax_register_poll_post_type();
	snax_register_poll_question_post_type();
	snax_register_poll_answer_post_type();
	snax_register_poll_result_post_type();
}

/**
 * Register post type for a single "Poll"
 */
function snax_register_poll_post_type() {
	$args = array(
		'labels' => array(
			'name'                  => _x( 'Polls', 'post type general name', 'snax' ),
			'singular_name'         => _x( 'Poll', 'post type singular name', 'snax' ),
			'menu_name'             => _x( 'Polls', 'admin menu', 'snax' ),
			'name_admin_bar'        => _x( 'Poll', 'add new on admin bar', 'snax' ),
			'add_new'               => _x( 'Add New', 'poll item', 'snax' ),
			'add_new_item'          => __( 'Add New Poll', 'snax' ),
			'new_item'              => __( 'New Poll', 'snax' ),
			'edit_item'             => __( 'Edit Poll', 'snax' ),
			'view_item'             => __( 'View Poll', 'snax' ),
			'all_items'             => __( 'All Polls', 'snax' ),
			'search_items'          => __( 'Search Polls', 'snax' ),
			'parent_item_colon'     => __( 'Parent Polls:', 'snax' ),
			'not_found'             => __( 'No polls found.', 'snax' ),
			'not_found_in_trash'    => __( 'No polls found in Trash.', 'snax' ),
		),
		'public'                    => true,
		// Below values are inherited from the 'public' if not set.
		// ------.
		'exclude_from_search'       => false,       // for readers
		'publicly_queryable'        => true,        // for readers
		'show_in_nav_menus'         => true,       	// for authors
		'show_ui'                   => true,        // for authors
		'rewrite'            		=> array(
			'slug' => snax_get_url_var( 'poll' ),
			'feeds' 				=> true,
		),
		'has_archive'        => true,
		// @todo This should be optional via plugin settings
		'taxonomies'                => array( 'category', 'post_tag' ),
		// ------.
		'supports'                  => array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
			'comments',
		),
	);

	register_post_type( snax_get_poll_post_type(), apply_filters( 'snax_poll_post_type_args', $args ) );
}

/**
 * Register post type for a single "Question"
 */
function snax_register_poll_question_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_poll_question_post_type(), apply_filters( 'snax_question_post_type_args', $args ) );
}

/**
 * Register post type for a single "Answer"
 */
function snax_register_poll_answer_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_poll_answer_post_type(), apply_filters( 'snax_answer_post_type_args', $args ) );
}

/**
 * Register post type for a single "Result"
 */
function snax_register_poll_result_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_poll_result_post_type(), apply_filters( 'snax_result_post_type_args', $args ) );
}

/**
 * Return poll questions query.
 *
 * @param WP_Post $poll				Optional. Post object or id.
 * @param array   $query_args		Optional. Query arguments.
 *
 * @return WP_Query
 */
function snax_get_poll_questions_query( $poll = null, $query_args = array() ) {
	$poll = get_post( $poll );

	$defaults = array(
		'post_parent'		=> $poll->ID,
		'post_type' 		=> snax_get_poll_question_post_type(),
		'order'				=> 'ASC',
		'orderby'			=> 'menu_order',
		'posts_per_page'	=> -1,
	);

	$query_args = wp_parse_args( $query_args, $defaults );

	$query = new WP_Query( $query_args );

	return $query;
}

/**
 * Return poll questions.
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_questions( $poll = null ) {
	global $post;
	$current_post = $post;

	$poll = get_post( $poll );

	$query = snax_get_poll_questions_query( $poll );
	$arr = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$arr[] = snax_get_poll_question( $query->post );
	}

	$post = $current_post;
	wp_reset_postdata();

	return $arr;
}

/**
 * Return poll questions count.
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return int
 */
function snax_get_poll_questions_count( $poll = null ) {
	$poll = get_post( $poll );

	$query = snax_get_poll_questions_query( $poll );

	$count = (int) $query->post_count;

	$per_poll_limit = snax_get_questions_per_poll( $poll );

	if ( -1 !== $per_poll_limit ) {
		$count = min( $per_poll_limit, $count );
	}

	return $count;
}

/**
 * Return a question.
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_question( $question = null ) {
	$question = get_post( $question );

	return array(
		'id' 			        => (int) $question->ID,
		'order' 		        => $question->menu_order,
		'title'			        => $question->post_title,
		'title_hide'	        => snax_get_poll_title_hide( $question ),
		'media' 		        => snax_get_poll_question_media( $question ),
		'answers'		        => snax_get_poll_answers( $question ),
		'answers_tpl'	        => snax_get_poll_answers_tpl( $question ),
		'answers_labels_hide'   => snax_get_poll_answers_labels_hide( $question ),
	);
}

/**
 * Return question media attributes (id, image HTML)
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_question_media( $question = null ) {
	$question = get_post( $question );

	$media = array(
		'id' 	=> '',
		'image'	=> '',
	);

	$media_id = (int) get_post_thumbnail_id( $question );

	if ( $media_id ) {
		$media['id'] 	= $media_id;
		$media['image']	= wp_get_attachment_image( $media_id, 'thumbnail', false, array( 'class' => 'quizzard-question-media-image' ) );
	}

	return $media;
}

/**
 * Return question answers query.
 *
 * @param WP_Post $question		Optional. Post object or id.
 * @param array   $query_args	Optional. Query arguments.
 *
 * @return WP_Query
 */
function snax_get_poll_answers_query( $question = null, $query_args = array() ) {
	$question = get_post( $question );

	$default_args = array(
		'post_parent'		=> $question->ID,
		'post_type' 		=> snax_get_poll_answer_post_type(),
		'order'				=> 'ASC',
		'orderby'			=> 'menu_order',
		'posts_per_page'	=> -1,
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$query = new WP_Query( $query_args );

	return $query;
}

/**
 * Return answers.
 *
 * @param WP_Post $question		Optional. Post object or id.
 * * @param array $query_args	Optional. Query arguments.
 *
 * @return array
 */
function snax_get_poll_answers( $question = null, $query_args = array() ) {
	global $post;
	$current_post = $post;

	$question = get_post( $question );

	$query = snax_get_poll_answers_query( $question, $query_args );
	$arr = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$arr[] = snax_get_poll_answer( $query->post );
	}

	$post = $current_post;
	wp_reset_postdata();

	return $arr;
}

/**
 * Return answers template name.
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return string
 */
function snax_get_poll_answers_tpl( $question = null ) {
	$question = get_post( $question );

	$tpl = get_post_meta( $question->ID, '_snax_answers_tpl', true );

	if ( ! $tpl ) {
		$tpl = 'anchor_text';
	}

	return $tpl;
}

/**
 * Return title hide option.
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return string
 */
function snax_get_poll_title_hide( $question = null ) {
	$question = get_post( $question );

	return (bool) get_post_meta( $question->ID, '_snax_title_hide', true );
}

/**
 * Return answers labels hide option.
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return string
 */
function snax_get_poll_answers_labels_hide( $question = null ) {
	$question = get_post( $question );

	return (bool) get_post_meta( $question->ID, '_snax_answers_labels_hide', true );
}

/**
 * Return an answer.
 *
 * @param WP_Post $answer		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_answer( $answer = null ) {
	$answer = get_post( $answer );

	return array(
		'id' 			=> (int) $answer->ID,
		'order' 		=> $answer->menu_order,
		'question_id' 	=> $answer->post_parent,
		'title'			=> $answer->post_title,
		'media'			=> snax_get_poll_answer_media( $answer ),
	);
}

/**
 * Return answer media attributes (id, image HTML)
 *
 * @param WP_Post $answer		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_answer_media( $answer = null ) {
	$answer = get_post( $answer );

	$media = array(
		'id' 	=> '',
		'image'	=> '',
	);

	$media_id = (int) get_post_thumbnail_id( $answer );

	if ( $media_id ) {
		$media['id'] 	= $media_id;
		$media['image']	= wp_get_attachment_image( $media_id, 'thumbnail', false, array( 'class' => 'quizzard-answer-media-image' ) );
	}

	return $media;
}

/**
 * Return array of pairs question id => answer id
 *
 * @param WP_Post $poll		Optional. Poll object or poll id.
 *
 * @return array
 */
function snax_get_poll_questions_answers($poll = null ) {
	$poll 	 				= get_post( $poll );
	$question_answer_map 	= array();

	$questions = snax_get_poll_questions( $poll );

	foreach ( $questions as $question ) {
		$question_id = $question['id'];
		$answer = '';

		$question_answer_map[] = array(
			'question_id' 	=> $question_id,
			'answer' 		=> $answer,
		);
	}

	return $question_answer_map;
}

/**
 * Return poll results query.
 *
 * @param WP_Post $poll				Optional. Post object or id.
 * @param array   $query_args		Optional. Query args.
 *
 * @return WP_Query
 */
function snax_get_poll_results_query( $poll = null, $query_args = array() ) {
	$poll = get_post( $poll );

	$default_args = array(
		'post_parent'		=> $poll->ID,
		'post_type' 		=> snax_get_poll_result_post_type(),
		'order'				=> 'ASC',
		'orderby'			=> 'menu_order',
		'posts_per_page'	=> -1,
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$query = new WP_Query( $query_args );

	return $query;
}

/**
 * Return poll matching results query.
 *
 * @param WP_Post $poll					Optional. Post object or id.
 * @param array   $query_args			Optional. Query args.
 *
 * @return WP_Query
 */
function snax_get_poll_matching_results_query( $poll = null, $query_args = array() ) {
	$poll = get_post( $poll );

	$default_args = array(
		'posts_per_page'    => 1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => '_snax_range_low',
				'value'   => 0,
				'type'    => 'numeric',
				'compare' => '<=',
			),
			array(
				'key'     => '_snax_range_high',
				'value'   => 0,
				'type'    => 'numeric',
				'compare' => '>=',
			)
		),
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$query = snax_get_poll_results_query( $poll, $query_args );

	return $query;
}

/**
 * Return poll results.
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_results( $poll = null ) {
	global $wpdb;
	$table_name = $wpdb->prefix . snax_get_polls_table_name();
	$poll = get_post( $poll );

	$out = $wpdb->get_results(
		"
		SELECT question_id, answer_id, COUNT(answer_id) as amount
		FROM $table_name
		WHERE poll_id = $poll->ID
		GROUP BY question_id, answer_id
		", ARRAY_A
	);

	$results = array(
		'total'     => 0,
		'questions' => array(),
	);

	foreach( $out as $group ) {
		$q_id   = $group['question_id'];
		$a_id   = $group['answer_id'];
		$amount = $group['amount'];

		if ( ! isset( $results['questions'][ $q_id ] ) ) {
			$results['questions'][ $q_id ] = array(
				'total'     => 0,
				'answers'   => array()
			);
		}

		$results['total'] += $amount;
		$results['questions'][ $q_id ]['total'] += $amount;
		$results['questions'][ $q_id ]['answers'][ $a_id ] = $amount;
	}

	return $results;
}

/**
 * Return a result.
 *
 * @param WP_Post $result		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_result( $result = null ) {
	$result = get_post( $result );

	return array(
		'id' 			=> (int) $result->ID,
		'order' 		=> (int) $result->menu_order,
		'title'			=> $result->post_title,
		'description'	=> $result->post_content,
		'range'			=> snax_get_poll_result_range( $result ),
		'media' 		=> snax_get_poll_result_media( $result ),
	);
}

/**
 * Return result media attributes (id, image HTML)
 *
 * @param WP_Post $result		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_result_media( $result = null ) {
	$result = get_post( $result );

	$media = array(
		'id' 	=> '',
		'image'	=> '',
	);

	$media_id = (int) get_post_thumbnail_id( $result );

	if ( $media_id ) {
		$media['id'] 	= $media_id;
		$media['image']	= wp_get_attachment_image( $media_id, 'thumbnail', false, array( 'class' => 'quizzard-result-media-image' ) );
	}

	return $media;
}

/**
 * Return result range
 *
 * @param WP_Post $result		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_poll_result_range( $result = null ) {
	$result = get_post( $result );

	$range = array(
		'low' 	=> (int) get_post_meta( $result->ID, '_snax_range_low', true ),
		'high'	=> (int) get_post_meta( $result->ID, '_snax_range_high', true ),
	);

	return $range;
}

/**
 * Render share buttons for poll
 *
 * @param WP_Post $poll		Options. Post object or id.
 * @param array   $args		Optional. Share data.
 */
function snax_render_poll_share( $poll = null, $args = array() ) {
	?>
	<div class="quizzard-poll-share">
		<div class="quizzard-item-share-content">
			<?php // If user had to unlock poll, he has already shared to FB. ?>
			<?php if ( snax_poll_share_to_unlock( $poll ) ) : ?>
				<?php $args['links'] = array( 'twitter' ); ?>
			<?php endif; ?>

			<?php snax_poll_share_links( $poll, $args ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render share buttons for poll
 *
 * @param WP_Post $poll		Options. Post object or id.
 * @param array   $args		Optional. Share data.
 */
function snax_render_poll_share_to_unlock( $poll = null, $args = array() ) {
	?>
	<div class="quizzard-poll-share">
		<div class="quizzard-item-share-content">
			<?php // We can unlock only via FB. ?>
			<?php $args['links'] = array( 'facebook' ); ?>

			<?php snax_poll_share_links( $poll, $args ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render item share links.
 *
 * @param WP_Post $poll			Optional. Post object or id.
 * @param array   $args 		Optional. Share data.
 */
function snax_poll_share_links( $poll = null, $args = array() ) {
	$poll = get_post( $poll );

	$poll_title = get_the_title( $poll );

	$defaults = array(
		'title' 		=> '',
		'url'			=> get_permalink( $poll ),
		'thumb'			=> get_the_post_thumbnail_url( $poll ),
		'description'	=> '',
		'links'			=> apply_filters( 'snax_poll_share_links', array( 'facebook', 'twitter' ) ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Title not set, use default.
	if ( empty( $args['title'] ) ) {
		$args['title'] = $poll_title;
	}

	global $snax_share_args;
	$snax_share_args = $args;

	foreach ( $args['links'] as $link_id ) {
		snax_get_template_part( 'share-links/' . $link_id );
	}

	unset( $GLOBALS['snax_share_args'] );
}

/**
 * Return when show correct/wrong answers feedback
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return string
 */
function snax_poll_reveal_correct_wrong_answers( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'reveal_correct_wrong_answers', $poll );

	return $ret;
}

/**
 * Check whether to show all questions at once or one question per page
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_one_question_per_page( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'one_question_per_page', $poll );

	return 'standard' === $ret;
}

/**
 * Check whether to load next question with or without page reload
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_next_question_reload( $poll = null ) {
	return true;
}

/**
 * Check whether to shuffle poll questions
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_shuffle_questions( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'shuffle_questions', $poll );

	return 'standard' === $ret;
}

/**
 * Return number of questions to show. works only if "shuffle_questions" option is enabled
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return int				Number of questions to show. -1 to load all.
 */
function snax_get_questions_per_poll($poll = null ) {
	$poll = get_post( $poll );

	if ( ! snax_poll_shuffle_questions( $poll ) ) {
		return -1;
	}

	$ret = snax_get_poll_setting( 'questions_per_poll', $poll );

	if ( ! is_numeric( $ret ) ) {
		return -1;
	}

	return $ret;
}

/**
 * Check whether to shuffle question answers
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_shuffle_answers( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'shuffle_answers', $poll );

	return 'standard' === $ret;
}

/**
 * Check whether to show share results buttons
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_show_share_results_buttons( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'share_results', $poll );

	return 'standard' === $ret;
}

/**
 * Check whether to force user to share the poll to see results
 *
 * @param WP_Post $poll		Optional. Post object or id.
 *
 * @return bool
 */
function snax_poll_share_to_unlock( $poll = null ) {
	$poll = get_post( $poll );

	$ret = snax_get_poll_setting( 'share_to_unlock', $poll );

	return 'standard' === $ret;
}

/**
 * Check whether the current page is poll last page
 *
 * @param WP_Post $poll			Optional. Post object or id.
 *
 * @return bool
 */
function snax_is_poll_last_page( $poll = null ) {
	$poll = get_post( $poll );

	if ( ! snax_poll_one_question_per_page() ) {
		return true;
	}

	global $page;

	$current_page 	= (int) $page;
	$last_page		= (int) snax_get_poll_questions_count( $poll );

	return $current_page === $last_page;
}

/**
 * Return Classic poll result
 *
 * @param WP_Post $poll				Post object or id.
 * @param string  $poll_summary		Optional. Short info about poll.
 *
 * @return string
 */
function snax_get_classic_poll_result( $poll, $poll_summary = '' ) {
	global $post;
	$current_post = $post;

	$poll = get_post( $poll );

	$query = snax_get_poll_matching_results_query( 0, $poll->ID );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			global $snax_poll_result_data;

			$snax_poll_result_data = array(
				'share_description'	=> $poll_summary,
			);

			snax_get_template_part( 'polls/classic/result' );

			unset( $GLOBALS['snax_poll_result_data'] );
		}

		$post = $current_post;
		wp_reset_postdata();
	}

	$html = ob_get_clean();

	return $html;
}

/**
 * Check whether the poll has next page
 *
 * @return bool
 */
function snax_poll_has_next_page() {
	add_filter( 'wp_link_pages', 'snax_poll_link_pages', 10, 2 );

	global $page, $numpages;

	return $numpages > $page;
}

/**
 * Render poll's next page link
 *
 * @param string $anchor_text		Anchor_text.
 * @param array  $classes			Optional. CSS classes.
 *
 * @return string
 */
function snax_poll_next_page( $anchor_text, $classes = array() ) {
	global $page;

	$open_a  = _wp_link_page( $page + 1 );
	$close_a = '</a>';

	// Add CSS classes.
	$open_a = str_replace( '<a href', '<a class="' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '" href', $open_a );

	echo filter_var( $open_a . $anchor_text . $close_a );
}

/**
 * Disable default pagination on a poll single page
 *
 * @param string $output				Pagination html.
 *
 * @return string
 */
function snax_poll_link_pages( $output ) {
	global $wp_query;

	if ( $wp_query->is_main_query() && snax_is_poll() ) {
		$output = '';
	}

	return $output;
}

/**
 * Return poll share description
 *
 * @return string
 */
function snax_get_poll_share_description() {
	remove_filter( 'the_content', 'snax_render_poll' );
	$excerpt = get_the_excerpt();
	add_filter( 'the_content', 'snax_render_poll' );

	return apply_filters( 'snax_share_description', $excerpt );

}

/**
 * Return user's polls
 *
 * @param string $poll_type             Poll type.
 * @param int    $user_id               Optional. User id.
 * @param array  $query_args            Optional. Extra query args.
 *
 * @return array
 */
function snax_get_user_draft_poll( $poll_type, $user_id = 0, $query_args = array() ) {
	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$default_args = array(
		'post_type'         => snax_get_poll_post_type(),
		'author'            => $user_id,
		'post_status'       => 'draft',
		'posts_per_page'    => 1,
		'meta_query'		=> array(
			'relation' => 'AND',
			array(
				'key'          => '_snax_poll_type',
				'value'        => $poll_type,
			),
		),
		'tax_query'            => array(
			array(
				'taxonomy' => snax_get_snax_format_taxonomy_slug(),
				'field' => 'slug',
				'operator' => 'NOT EXISTS',
			),
		),
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$polls = get_posts( $query_args );

	if ( ! empty( $polls ) ) {
		$poll = $polls[0];
	} else {
		// Create new.
		remove_filter( 'the_content', 'snax_append_frontend_submission_form' );
		$poll_id = wp_insert_post( array(
			'post_title'   => '[draft]',
			'post_author'   => $user_id,
			'post_status'   => 'draft',
			'post_type'     => snax_get_poll_post_type(),
		) );
		add_filter( 'the_content', 'snax_append_frontend_submission_form' );
		if ( ! $poll_id ) {
			return false;
		}

		$poll = get_post( $poll_id );

		update_post_meta( $poll_id, '_snax_poll_type', $poll_type );
	}

	return $poll;
}

/**
 * Delete poll children
 *
 * @param int $post_id  ID of deleted post.
 * @return void
 */
function snax_remove_poll_children( $post_id ) {
	if ( ! snax_is_poll( $post_id ) ) {
		return;
	}

	$args = array(
	'post_parent' => $post_id,
	'post_type'   => 'snax_question',
	'numberposts' => -1,
	'post_status' => 'any',
	);
	$children = get_children( $args );

	$args['post_type'] = 'snax_result';
	$children = array_merge( $children, get_children( $args ) );

	$args['post_type'] = 'snax_answer';
	foreach ( $children as $key => $value ) {
		$args['post_parent'] = $value->ID;
		$children = array_merge( $children, get_children( $args ) );
	}

	foreach ( $children as $key => $value ) {
		wp_delete_post( $value->ID, true );
	}
}

function snax_poll_add_answer( $poll_id, $author_id, $question_id, $answer_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . snax_get_polls_table_name();

	$post_date  = current_time( 'mysql' );

	$affected_rows = $wpdb->insert(
		$table_name,
		array(
			'poll_id'     => $poll_id,
			'question_id' => $question_id,
			'answer_id'   => $answer_id,
			'author_id'   => $author_id,
			'date'        => $post_date,
			'date_gmt'    => get_gmt_from_date( $post_date ),
		),
		array(
			'%d',
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
		)
	);

	if ( false === $affected_rows ) {
		return new WP_Error( 'snax_insert_poll_answer_failed', esc_html__( 'Could not insert new poll answer into the database!', 'snax' ) );
	}

	do_action( 'snax_poll_saved', $poll_id );

	return true;
}

/**
 * Return poll share links.
 *
 * @param WP_Post $poll_id	Post object or id.
 * @param int	  $question_id	The question to share.
 * @param int	  $answer_id	The answer to share.
 *
 * @return str
 */
function snax_get_poll_share_links( $poll_id, $question_id, $answer_id ) {
 
	if ( ! snax_poll_show_share_results_buttons( $poll_id ) ) {
		return '';
	}

	$question	 = snax_get_poll_question( $question_id );
	$answer		 = snax_get_poll_answer( $answer_id );

	$poll_type	 = snax_get_poll_type( $poll_id );
	$url 		 = get_permalink( $poll_id );
	$title 		 = get_the_title( $poll_id );

	// in other polls when there is no title, the image will be self evident.
	if ( $question['title_hide'] && 'versus' === $poll_type ) {
		return '';
	}

	if ( 'binary' === $poll_type && 'hot-not' === snax_get_poll_setting( 'answers_set', $poll_id ) ) {
		if ( 'Yes' === $answer['title'] ) {
			$answer['title'] = esc_html__( 'Hot', 'snax' );
		} else {
			$answer['title'] = esc_html__( 'Not', 'snax' );
		}
	}

	global $snax_share_args;
	if ( ! $question['title_hide'] ) {
		$snax_share_args['description'] = $question['title'] . esc_html__( ' My choice: ', 'snax' ) . $answer['title'] . esc_html__( '. Play!', 'snax' );
	} else {
		$snax_share_args['description'] = esc_html__( ' My choice: ', 'snax' ) . $answer['title'] . esc_html__( '. Play!', 'snax' );
	}

	$snax_share_args['url'] = $url;
	if ( $question['media']['id'] ) {
		$image = wp_get_attachment_image_src( $question['media']['id'], 'full' );
		$image = $image[0];
		$snax_share_args['thumb'] = $image;
	}
	if ( 'versus' === $poll_type && $question['answers'][0]['media']['id'] ) {
		$image = wp_get_attachment_image_src( $question['answers'][0]['media']['id'], 'full' );
		$image = $image[0];
		$snax_share_args['thumb'] = $image;
	}

	if ( ! isset( $snax_share_args['thumb'] ) ) {
		$snax_share_args['thumb'] = get_the_post_thumbnail_url( $poll_id, 'full' );
	}

	$snax_share_args['title'] = html_entity_decode( $title );

	ob_start();
	snax_get_template_part( 'share-links/poll' );
	$html = ob_get_clean();
	unset( $GLOBALS['snax_share_args'] );
	return $html;
}
