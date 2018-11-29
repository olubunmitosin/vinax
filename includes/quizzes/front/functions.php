<?php
/**
 * Front Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Load javascripts.
 */
function snax_quiz_enqueue_scripts() {
	if ( ! is_singular( snax_get_quiz_post_type() ) ) {
		return;
	}

	$url = trailingslashit( snax_get_assets_url() );

	wp_enqueue_script( 'snax-quiz', $url . 'js/quiz.js', array( 'jquery' ), '1.0', true );

	global $page;

	$config = array(
		'ajax_url'          			=> admin_url( 'admin-ajax.php' ),
		'debug'							=> snax_in_debug_mode(),
		'quiz_id'						=> get_the_ID(),
		'all_questions'					=> snax_get_questions_count(),
		'questions_answers_arr'			=> snax_get_questions_answers(),
		'page'							=> $page,
		'reveal_correct_wrong_answers'	=> snax_reveal_correct_wrong_answers(),
		'one_question_per_page' 		=> snax_one_question_per_page(),
		'shuffle_questions' 			=> snax_shuffle_questions(),
		'questions_per_quiz'			=> snax_get_questions_per_quiz(),
		'shuffle_answers' 				=> snax_shuffle_answers(),
		'next_question_reload'			=> snax_next_question_reload(),
		'share_to_unlock'				=> snax_share_to_unlock(),
		'share_description'				=> snax_get_share_description(),
	);

	$config = apply_filters( 'snax_quiz_config', $config );

	wp_localize_script( 'snax-quiz', 'snax_quiz_config', wp_json_encode( $config ) );
}

/**
 * Render quiz
 *
 * @param string $content		Post content.
 *
 * @return string
 */
function snax_render_quiz( $content ) {
	$shortcode = '';
	if ( strpos( $content, '[snax_content]' ) > -1 ) {
		$shortcode = '[snax_content]';
		$content = str_replace( '[snax_content]', '', $content );
	}
	if ( is_singular( snax_get_quiz_post_type() ) ) {
		ob_start();

		echo '<div class="snax">';
		if ( ! is_user_logged_in() && ! snax_quiz_allow_guests_to_play() ) {
			snax_get_template_part( 'quizzes/quiz-cta' );
		} else {
			snax_get_template_part( 'quizzes/quiz' );
		}
		echo '</div>';

		$content .= ob_get_clean();
	}
	$content .= $shortcode;

	return $content;
}

/**
 * Generate quiz pagination using built-in WP page links
 *
 * @param array    $posts           Array of posts.
 * @param WP_Query $wp_query        WP Query.
 *
 * @return array
 */
function snax_generate_quiz_pagination( $posts, $wp_query ) {
	/**
	 * Check if query is an instance of WP_Query.
	 * Some plugins, like BuddyPress may change it.
	 */
	if ( ! ( $wp_query instanceof WP_Query ) ) {
		return $posts;
	}

	// Apply only for the_content on a single post.
	if ( ! ( $wp_query->is_main_query() && $wp_query->is_singular() ) ) {
		return $posts;
	}

	foreach ( $posts as $post ) {

		if ( ! snax_is_quiz( $post ) ) {
			continue;
		}

		// We don't need pagination if all questions are displated on a page at once.
		if ( ! snax_one_question_per_page( $post ) ) {
			continue;
		}

		$pages = snax_get_questions_count( $post );

		if ( $pages < 2 ) {
			continue;
		}

		// WP skips <!--nextpage--> quick tag if it's placed at the beggining of a post.
		// So if post content is empty we need to add one extra quick tag as a workaround.
		if ( empty( $post->post_content ) ) {
			$post->post_content .= '<!--nextpage-->';
		}

		// The <!--nextpage--> tag is a divider between two pages. Number of dividers = pages - 1.
		$post->post_content .= str_repeat( '<!--nextpage-->', $pages - 1 );
	}

	return $posts;
}

