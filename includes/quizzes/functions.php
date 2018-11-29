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
 * Register quiz image sizes
 */
function snax_add_image_sizes() {
	$width = 600;

	global $content_width;
	if ( isset( $content_width ) ) {
		$width = $content_width;
	}

	// Two images in a row.
	$width /= 2;

	// Add some spacing around.
	$width -= 20;

	// Square size.
	add_image_size( 'quizzard-answer-grid-1of2', $width, $width, true );
}

/**
 * Return default quiz settings
 *
 * @param string $name		Optional. Setting name.
 *
 * @return mixed			Array or single value if $name set
 */
function snax_get_quiz_defaults( $name = '' ) {
	$defaults = array(
		'reveal_correct_wrong_answers'	=> 'immediately',
		'one_question_per_page' 		=> 'none',
		'shuffle_questions' 			=> 'none',
		'questions_per_quiz' 			=> '',
		'shuffle_answers' 				=> 'none',
		'start_quiz' 					=> 'standard',
		'play_again' 					=> 'standard',
		'share_results' 				=> 'standard',
		'share_to_unlock' 				=> 'none',
	);

	$defaults = apply_filters( 'snax_quiz_defaults', $defaults );

	if ( $name && isset( $defaults[ $name ] ) ) {
		return $defaults[ $name ];
	}

	return $defaults;
}

/**
 * Return quiz setting
 *
 * @param string  $name			Setting name.
 * @param WP_Post $quiz			Options. Post object or id.
 *
 * @return string
 */
function snax_get_quiz_setting( $name, $quiz = null ) {
	$quiz = get_post( $quiz );

	$prefix = '_snax_';

	$setting = get_post_meta( $quiz->ID, $prefix . $name, true );

	if ( empty( $setting ) ) {
		$setting = snax_get_quiz_defaults( $name );
	}

	return $setting;
}

/**
 * Return list of valid quiz types
 *
 * @return array
 */
function snax_get_quiz_types() {
	return apply_filters( 'snax_quiz_types', array(
		snax_get_trivia_quiz_type(),
		snax_get_personality_quiz_type(),
	) );
}

/**
 * Return type name for Trivia quiz
 *
 * @return string
 */
function snax_get_trivia_quiz_type() {
	return 'trivia';
}

/**
 * Return type name for Personality quiz
 *
 * @return string
 */
function snax_get_personality_quiz_type() {
	return 'personality';
}

/**
 * Check whether the quiz is a Trivia quiz
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return string
 */
function snax_is_trivia_quiz( $quiz = null ) {
	$quiz = get_post( $quiz );

	return snax_get_trivia_quiz_type() === snax_get_quiz_type( $quiz );
}

/**
 * Check whether the quiz is a Personality quiz
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return string
 */
function snax_is_personality_quiz( $quiz = null ) {
	$quiz = get_post( $quiz );

	return snax_get_personality_quiz_type() === snax_get_quiz_type( $quiz );
}

/**
 * Return quiz type
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return string
 */
function snax_get_quiz_type( $quiz = null ) {
	$quiz = get_post( $quiz );

	return get_post_meta( $quiz->ID, '_snax_quiz_type', true );
}

/**
 * Check whether type of quiz is valid
 *
 * @param string $type		Quiz type.
 *
 * @return bool
 */
function snax_is_valid_quiz_type( $type ) {
	$types = snax_get_quiz_types();

	return in_array( $type, $types, true );
}

/**
 * Check whether the post is a quiz
 *
 * @param WP_Post $quiz		Optional. Quiz post or id.
 *
 * @return bool
 */
function snax_is_quiz( $quiz = null ) {
	return snax_get_quiz_post_type() === get_post_type( $quiz );
}

/**
 * Return quiz post type name
 *
 * @return string
 */
function snax_get_quiz_post_type() {
	return 'snax_quiz';
}

/**
 * Return question post type name
 *
 * @return string
 */
function snax_get_question_post_type() {
	return 'snax_question';
}

/**
 * Return answer post type name
 *
 * @return string
 */
function snax_get_answer_post_type() {
	return 'snax_answer';
}

/**
 * Return result post type name
 *
 * @return string
 */
function snax_get_result_post_type() {
	return 'snax_result';
}

/**
 * Register all post types
 */
function snax_register_post_types() {
	snax_register_quiz_post_type();
	snax_register_question_post_type();
	snax_register_answer_post_type();
	snax_register_result_post_type();
}

/**
 * Register post type for a single "Quiz"
 */
function snax_register_quiz_post_type() {
	$args = array(
		'labels' => array(
			'name'                  => _x( 'Quizzes', 'post type general name', 'snax' ),
			'singular_name'         => _x( 'Quiz', 'post type singular name', 'snax' ),
			'menu_name'             => _x( 'Quizzes', 'admin menu', 'snax' ),
			'name_admin_bar'        => _x( 'Quiz', 'add new on admin bar', 'snax' ),
			'add_new'               => _x( 'Add New', 'quizzard item', 'snax' ),
			'add_new_item'          => __( 'Add New Quiz', 'snax' ),
			'new_item'              => __( 'New Quiz', 'snax' ),
			'edit_item'             => __( 'Edit Quiz', 'snax' ),
			'view_item'             => __( 'View Quiz', 'snax' ),
			'all_items'             => __( 'All Quizzes', 'snax' ),
			'search_items'          => __( 'Search Quizzes', 'snax' ),
			'parent_item_colon'     => __( 'Parent Quizzes:', 'snax' ),
			'not_found'             => __( 'No quizzes found.', 'snax' ),
			'not_found_in_trash'    => __( 'No quizzes found in Trash.', 'snax' ),
		),
		'public'                    => true,
		// Below values are inherited from the 'public' if not set.
		// ------.
		'exclude_from_search'       => false,       // for readers
		'publicly_queryable'        => true,        // for readers
		'show_in_nav_menus'         => true,       	// for authors
		'show_ui'                   => true,        // for authors
		'rewrite'            		=> array(
			'slug' => snax_get_url_var( 'quiz' ),
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

	register_post_type( snax_get_quiz_post_type(), apply_filters( 'snax_quiz_post_type_args', $args ) );
}

/**
 * Register post type for a single "Question"
 */
function snax_register_question_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_question_post_type(), apply_filters( 'snax_question_post_type_args', $args ) );
}

/**
 * Register post type for a single "Answer"
 */
function snax_register_answer_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_answer_post_type(), apply_filters( 'snax_answer_post_type_args', $args ) );
}

/**
 * Register post type for a single "Result"
 */
function snax_register_result_post_type() {
	$args = array(
		'public'		=> false,
		'supports'      => array(
			'title',
		),
	);

	register_post_type( snax_get_result_post_type(), apply_filters( 'snax_result_post_type_args', $args ) );
}

/**
 * Return quiz questions query.
 *
 * @param WP_Post $quiz				Optional. Post object or id.
 * @param array   $query_args		Optional. Query arguments.
 *
 * @return WP_Query
 */
function snax_get_questions_query( $quiz = null, $query_args = array() ) {
	$quiz = get_post( $quiz );

	$defaults = array(
		'post_parent'		=> $quiz->ID,
		'post_type' 		=> snax_get_question_post_type(),
		'order'				=> 'ASC',
		'orderby'			=> 'menu_order',
		'posts_per_page'	=> -1,
	);

	$query_args = wp_parse_args( $query_args, $defaults );

	$query = new WP_Query( $query_args );

	return $query;
}

/**
 * Return quiz questions.
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_questions( $quiz = null ) {
	global $post;
	$current_post = $post;

	$quiz = get_post( $quiz );

	$query = snax_get_questions_query( $quiz );
	$arr = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$arr[] = snax_get_question( $query->post );
	}

	$post = $current_post;
	wp_reset_postdata();

	return $arr;
}

/**
 * Return quiz questions count.
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return int
 */
function snax_get_questions_count( $quiz = null ) {
	$quiz = get_post( $quiz );

	$query = snax_get_questions_query( $quiz );

	$count = (int) $query->post_count;

	$per_quiz_limit = snax_get_questions_per_quiz( $quiz );

	if ( -1 !== $per_quiz_limit ) {
		$count = min( $per_quiz_limit, $count );
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
function snax_get_question( $question = null ) {
	$question = get_post( $question );

	return array(
		'id' 			        => (int) $question->ID,
		'order' 		        => $question->menu_order,
		'title'			        => $question->post_title,
		'title_hide'	        => snax_get_title_hide( $question ),
		'media' 		        => snax_get_question_media( $question ),
		'answers'		        => snax_get_answers( $question ),
		'answers_tpl'	        => snax_get_answers_tpl( $question ),
		'answers_labels_hide'   => snax_get_answers_labels_hide( $question ),
	);
}

/**
 * Return question media attributes (id, image HTML)
 *
 * @param WP_Post $question		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_question_media( $question = null ) {
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
function snax_get_answers_query( $question = null, $query_args = array() ) {
	$question = get_post( $question );

	$default_args = array(
		'post_parent'		=> $question->ID,
		'post_type' 		=> snax_get_answer_post_type(),
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
function snax_get_answers( $question = null, $query_args = array() ) {
	global $post;
	$current_post = $post;

	$question = get_post( $question );

	$query = snax_get_answers_query( $question, $query_args );
	$arr = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$arr[] = snax_get_answer( $query->post );
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
function snax_get_answers_tpl( $question = null ) {
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
function snax_get_title_hide( $question = null ) {
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
function snax_get_answers_labels_hide( $question = null ) {
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
function snax_get_answer( $answer = null ) {
	$answer = get_post( $answer );

	return array(
		'id' 			=> (int) $answer->ID,
		'order' 		=> $answer->menu_order,
		'question_id' 	=> $answer->post_parent,
		'title'			=> $answer->post_title,
		'correct'		=> snax_get_answer_correct( $answer ),
		'media'			=> snax_get_answer_media( $answer ),
	);
}

/**
 * Check whether the answer is correct
 *
 * @param WP_Post $answer		Optional. Post object or id.
 *
 * @return mixed				Bool or int.
 */
function snax_get_answer_correct( $answer = null ) {
	$answer = get_post( $answer );

	return get_post_meta( $answer->ID, '_snax_correct', true );
}

/**
 * Return answer media attributes (id, image HTML)
 *
 * @param WP_Post $answer		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_answer_media( $answer = null ) {
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
 * Return array of pairs question id => correct answer id
 *
 * @param WP_Post $quiz		Optional. Quiz object or quiz id.
 *
 * @return array
 */
function snax_get_questions_answers($quiz = null ) {
	$quiz 	 				= get_post( $quiz );
	$is_trivia_quiz 		= snax_is_trivia_quiz( $quiz );
	$question_answer_map 	= array();

	$questions = snax_get_questions( $quiz );

	foreach ( $questions as $question ) {
		$question_id = $question['id'];
		$answer = '';

		if ( $is_trivia_quiz ) {
			$answers = snax_get_answers( $question_id, array(
				'meta_key' 		=> '_snax_correct',
				'meta_value'	=> true,
			) );

			foreach ( $answers as $answer_data ) {
				$answer = $answer_data['id'];
			}
		}

		$question_answer_map[] = array(
			'question_id' 	=> $question_id,
			'answer' 		=> $answer,
		);
	}

	return $question_answer_map;
}

/**
 * Return percentage result
 *
 * @param int     $correct_answers		Number of correct answers.
 * @param WP_Post $quiz					Optional. Quiz post or id.
 *
 * @return float
 */
function snax_get_percentage_result( $correct_answers, $quiz = null ) {
	$quiz 			= get_post( $quiz );
	$all_questions 	= snax_get_questions_count( $quiz );
	$percentage 	= round( $correct_answers / $all_questions * 100 );

	return $percentage;
}

/**
 * Return quiz results query.
 *
 * @param WP_Post $quiz				Optional. Post object or id.
 * @param array   $query_args		Optional. Query args.
 *
 * @return WP_Query
 */
function snax_get_results_query( $quiz = null, $query_args = array() ) {
	$quiz = get_post( $quiz );

	$default_args = array(
		'post_parent'		=> $quiz->ID,
		'post_type' 		=> snax_get_result_post_type(),
		'order'				=> 'ASC',
		'orderby'			=> 'menu_order',
		'posts_per_page'	=> -1,
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$query = new WP_Query( $query_args );

	return $query;
}

/**
 * Return quiz matching results query.
 *
 * @param int     $correct_answers		Number of correct answers.
 * @param WP_Post $quiz					Optional. Post object or id.
 * @param array   $query_args			Optional. Query args.
 *
 * @return WP_Query
 */
function snax_get_matching_results_query( $correct_answers, $quiz = null, $query_args = array() ) {
	$quiz = get_post( $quiz );
	$percentage_result 	= snax_get_percentage_result( $correct_answers, $quiz );

	$default_args = array(
		'posts_per_page'    => 1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => '_snax_range_low',
				'value'   => (int) $percentage_result,
				'type'    => 'numeric',
				'compare' => '<=',
			),
			array(
				'key'     => '_snax_range_high',
				'value'   => (int) $percentage_result,
				'type'    => 'numeric',
				'compare' => '>=',
			)
		),
	);

	$query_args = wp_parse_args( $query_args, $default_args );

	$query = snax_get_results_query( $quiz, $query_args );

	return $query;
}

/**
 * Return quiz results.
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_results( $quiz = null ) {
	global $post;
	$current_post = $post;

	$quiz = get_post( $quiz );

	$query = snax_get_results_query( $quiz );
	$arr = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$arr[] = snax_get_result( $query->post );
	}

	$post = $current_post;
	wp_reset_postdata();

	return $arr;
}

/**
 * Return a result.
 *
 * @param WP_Post $result		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_result( $result = null ) {
	$result = get_post( $result );

	return array(
		'id' 			=> (int) $result->ID,
		'order' 		=> (int) $result->menu_order,
		'title'			=> $result->post_title,
		'description'	=> $result->post_content,
		'range'			=> snax_get_result_range( $result ),
		'media' 		=> snax_get_result_media( $result ),
	);
}

/**
 * Return result media attributes (id, image HTML)
 *
 * @param WP_Post $result		Optional. Post object or id.
 *
 * @return array
 */
function snax_get_result_media( $result = null ) {
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
function snax_get_result_range( $result = null ) {
	$result = get_post( $result );

	$range = array(
		'low' 	=> (int) get_post_meta( $result->ID, '_snax_range_low', true ),
		'high'	=> (int) get_post_meta( $result->ID, '_snax_range_high', true ),
	);

	return $range;
}

/**
 * Render share buttons for quiz
 *
 * @param WP_Post $quiz		Options. Post object or id.
 * @param array   $args		Optional. Share data.
 */
function snax_render_quiz_share( $quiz = null, $args = array() ) {
	?>
	<div class="quizzard-quiz-share">
		<div class="quizzard-item-share-content">
			<?php // If user had to unlock quiz, he has already shared to FB. ?>
			<?php if ( snax_share_to_unlock( $quiz ) ) : ?>
				<?php $args['links'] = array( 'twitter' ); ?>
			<?php endif; ?>

			<?php snax_quiz_share_links( $quiz, $args ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render share buttons for quiz
 *
 * @param WP_Post $quiz		Options. Post object or id.
 * @param array   $args		Optional. Share data.
 */
function snax_render_quiz_share_to_unlock( $quiz = null, $args = array() ) {
	?>
	<div class="quizzard-quiz-share">
		<div class="quizzard-item-share-content">
			<?php // We can unlock only via FB. ?>
			<?php $args['links'] = array( 'facebook' ); ?>

			<?php snax_quiz_share_links( $quiz, $args ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render item share links.
 *
 * @param WP_Post $quiz			Optional. Post object or id.
 * @param array   $args 		Optional. Share data.
 */
function snax_quiz_share_links( $quiz = null, $args = array() ) {
	$quiz = get_post( $quiz );

	$quiz_title = get_the_title( $quiz );

	$defaults = array(
		'title' 		=> '',
		'url'			=> get_permalink( $quiz ),
		'thumb'			=> get_the_post_thumbnail_url( $quiz ),
		'description'	=> '',
		'links'			=> apply_filters( 'snax_quiz_share_links', array( 'facebook', 'twitter' ) ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Title not set, use default.
	if ( empty( $args['title'] ) ) {
		$args['title'] = $quiz_title;
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
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return string
 */
function snax_reveal_correct_wrong_answers( $quiz = null ) {
	$quiz = get_post( $quiz );

	if ( snax_is_personality_quiz( $quiz ) ) {
		return '';
	}

	$ret = snax_get_quiz_setting( 'reveal_correct_wrong_answers', $quiz );

	return $ret;
}

/**
 * Check whether to show all questions at once or one question per page
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_one_question_per_page( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'one_question_per_page', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether to load next question with or without page reload
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_next_question_reload( $quiz = null ) {
	return true;
}

/**
 * Check whether to shuffle quiz questions
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_shuffle_questions( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'shuffle_questions', $quiz );

	return 'standard' === $ret;
}

/**
 * Return number of questions to show. works only if "shuffle_questions" option is enabled
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return int				Number of questions to show. -1 to load all.
 */
function snax_get_questions_per_quiz($quiz = null ) {
	$quiz = get_post( $quiz );

	if ( ! snax_shuffle_questions( $quiz ) ) {
		return -1;
	}

	$ret = snax_get_quiz_setting( 'questions_per_quiz', $quiz );

	if ( ! is_numeric( $ret ) ) {
		return -1;
	}

	return $ret;
}

/**
 * Check whether to shuffle question answers
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_shuffle_answers( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'shuffle_answers', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether to show the "Start Quiz" button
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_show_start_quiz_button( $quiz = null ) {
	global $page;

	if ( $page > 1 ) {
		return false;
	}

	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'start_quiz', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether to show the "Play Again" button
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_show_play_again_button( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'play_again', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether to show share results buttons
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_show_share_results_buttons( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'share_results', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether to force user to share the quiz to see results
 *
 * @param WP_Post $quiz		Optional. Post object or id.
 *
 * @return bool
 */
function snax_share_to_unlock( $quiz = null ) {
	$quiz = get_post( $quiz );

	$ret = snax_get_quiz_setting( 'share_to_unlock', $quiz );

	return 'standard' === $ret;
}

/**
 * Check whether the current page is quiz last page
 *
 * @param WP_Post $quiz			Optional. Post object or id.
 *
 * @return bool
 */
function snax_is_quiz_last_page( $quiz = null ) {
	$quiz = get_post( $quiz );

	if ( ! snax_one_question_per_page() ) {
		return true;
	}

	global $page;

	$current_page 	= (int) $page;
	$last_page		= (int) snax_get_questions_count( $quiz );

	return $current_page === $last_page;
}

/**
 * Return Trivia quiz result
 *
 * @param array   $answers			Answer list.
 * @param WP_Post $quiz				Optional. Post object or id.
 * @param string  $quiz_summary		Optional. Short info about quiz.
 *
 * @return string
 */
function snax_get_trivia_quiz_result( $answers, $quiz = null, $quiz_summary = '' ) {
	global $post;
	$current_post = $post;

	$quiz = get_post( $quiz );

	$questions_count 		= snax_get_questions_count( $quiz );
	$correct_answers_count 	= snax_count_correct_answers( $answers, $quiz );

	$query = snax_get_matching_results_query( $correct_answers_count, $quiz->ID );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			global $snax_quiz_result_data;

			$snax_quiz_result_data = array(
				'all' 				=> $questions_count,
				'correct' 			=> $correct_answers_count,
				'score_percentage'	=> round( $correct_answers_count / $questions_count * 100 ),
				'share_description'	=> $quiz_summary,
			);

			snax_get_template_part( 'quizzes/trivia/result' );

			unset( $GLOBALS['snax_quiz_result_data'] );
		}

		$post = $current_post;
		wp_reset_postdata();
	}

	$html = ob_get_clean();

	return $html;
}

/**
 * Return Personality quiz result
 *
 * @param array   $answers			Answer list.
 * @param WP_Post $quiz				Optional. Post object or id.
 * @param string  $quiz_summary		Optional. Short info about quiz.
 *
 * @return string
 */
function snax_get_personality_quiz_result( $answers, $quiz = null, $quiz_summary = '' ) {
	$quiz = get_post( $quiz );

	$personalities = snax_get_results( $quiz );

	if ( empty( $personalities ) ) {
		return __( 'You have to define and assign personalities first!', 'snax' );
	}

	$personality_score = array();

	// Init.
	foreach ( $personalities as $personality ) {
		$personality_score[ $personality['id'] ] = 0;
	}

	// Count final score.
	foreach ( $answers as $question_id => $answer_id ) {
		$personality_id = snax_get_answer_correct( $answer_id );

		if ( isset( $personality_score[ $personality_id ] ) ) {
			$personality_score[ $personality_id ]++;
		}
	}

	// Sort by scores, in descending order.
	arsort( $personality_score );

	// Flip array.
	$keys = array_keys( $personality_score );

	// Get first key (best matching personality).
	$best_matching_personality_id = array_shift( $keys );

	global $post;
	$current_post = $post;

	$query = new WP_Query( array(
		'p' 				=> $best_matching_personality_id,
		'post_type'         => snax_get_result_post_type(),
		'posts_per_page'    => 1,
	) );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			global $snax_quiz_result_data;

			$snax_quiz_result_data = array(
				'share_description'	=> $quiz_summary,
			);

			snax_get_template_part( 'quizzes/personality/result' );

			unset( $GLOBALS['snax_quiz_result_data'] );
		}

		$post = $current_post;
		wp_reset_postdata();
	}

	$html = ob_get_clean();

	return $html;
}

/**
 * Return quiz questions count.
 *
 * @param array   $answers		Answer list.
 * @param WP_Post $quiz			Optional. Post object or id.
 *
 * @return int
 */
function snax_count_correct_answers( $answers, $quiz = null ) {
	$quiz = get_post( $quiz );

	$count = 0;
	$correct_answers = snax_get_questions_answers( $quiz );

	foreach ( $correct_answers as $correct_answer ) {
		$question_id 		= $correct_answer['question_id'];
		$correct_answer_id	= $correct_answer['answer'];

		if ( $answers[ $question_id ] === $correct_answer_id ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Check whether the quiz has next page
 *
 * @return bool
 */
function snax_quiz_has_next_page() {
	add_filter( 'wp_link_pages', 'snax_link_pages', 10, 2 );

	global $page, $numpages;

	return $numpages > $page;
}

/**
 * Render quiz's next page link
 *
 * @param string $anchor_text		Anchor_text.
 * @param array  $classes			Optional. CSS classes.
 *
 * @return string
 */
function snax_quiz_next_page( $anchor_text, $classes = array() ) {
	global $page;

	$open_a  = _wp_link_page( $page + 1 );
	$close_a = '</a>';

	// Add CSS classes.
	$open_a = str_replace( '<a href', '<a class="' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '" href', $open_a );

	echo filter_var( $open_a . $anchor_text . $close_a );
}

/**
 * Disable default pagination on a quiz single page
 *
 * @param string $output				Pagination html.
 *
 * @return string
 */
function snax_link_pages( $output ) {
	global $wp_query;

	if ( $wp_query->is_main_query() && snax_is_quiz() ) {
		$output = '';
	}

	return $output;
}

/**
 * Return quiz share description
 *
 * @return string
 */
function snax_get_share_description() {
	remove_filter( 'the_content', 'snax_render_quiz' );
	$excerpt = get_the_excerpt();
	add_filter( 'the_content', 'snax_render_quiz' );

	return apply_filters( 'snax_share_description', $excerpt );

}

/**
 * Return user's quizzes
 *
 * @param string $quiz_type             Quiz type.
 * @param int    $user_id               Optional. User id.
 * @param array  $query_args            Optional. Extra query args.
 *
 * @return array
 */
function snax_get_user_draft_quizz( $quiz_type, $user_id = 0, $query_args = array() ) {
	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$default_args = array(
		'post_type'         => snax_get_quiz_post_type(),
		'author'            => $user_id,
		'post_status'       => 'draft',
		'posts_per_page'    => 1,
		'meta_query'		=> array(
			'relation' => 'AND',
			array(
				'key'          => '_snax_quiz_type',
				'value'        => $quiz_type,
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

	$quizzes = get_posts( $query_args );

	if ( ! empty( $quizzes ) ) {
		$quiz = $quizzes[0];
	} else {
		// Create new.
		remove_filter( 'the_content', 'snax_append_frontend_submission_form' );
		$quiz_id = wp_insert_post( array(
			'post_title'   => '[draft]',
			'post_author'   => $user_id,
			'post_status'   => 'draft',
			'post_type'     => snax_get_quiz_post_type(),
		) );
		add_filter( 'the_content', 'snax_append_frontend_submission_form' );	
		if ( ! $quiz_id ) {
			return false;
		}

		$quiz = get_post( $quiz_id );

		update_post_meta( $quiz_id, '_snax_quiz_type', $quiz_type );
	}

	return $quiz;
}

/**
 * Return list of auto-generated scores for Trvia quiz
 */
function snax_get_trivia_quiz_scores() {
	$scores = array(
		'1'	=> 	_x( 'Bad day, right?', 'Trivia quiz score', 'snax' ),
		'2' => 	_x( 'Could be better', 'Trivia quiz score', 'snax' ),
		'3' =>  _x( 'Not so bad', 'Trivia quiz score', 'snax' ),
		'4'	=>	_x( 'Great', 'Trivia quiz score', 'snax' ),
		'5' =>  _x( 'Perfect. You rock!', 'Trivia quiz score', 'snax' ),
	);

	return apply_filters( 'snax_trivia_quiz_scores', $scores );
}

/**
 * Delete quiz children
 *
 * @param int $post_id  ID of deleted post.
 * @return void
 */
function snax_remove_quiz_children( $post_id ) {
	if ( ! snax_is_quiz( $post_id ) ) {
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
