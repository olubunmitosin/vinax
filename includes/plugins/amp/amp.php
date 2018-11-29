<?php
/**
 * AMP plugin functions
 *
 * @license For the full license information, please view the Licensing folder
 * that was distributed with this source code.
 *
 * @package Snax_Plugin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$amp_path = trailingslashit( dirname( __FILE__ ) );
require_once( $amp_path . 'ajax.php' );

add_action( 'amp_init', 		'snax_amp_add_post_types' );
add_action( 'template_redirect', 	'snax_amp_initialize' );

/**
 * Add Snax post types to AMP
 */
function snax_amp_add_post_types() {
	add_post_type_support( snax_get_item_post_type(), AMP_QUERY_VAR );
	add_post_type_support( snax_get_quiz_post_type(), AMP_QUERY_VAR );
	add_post_type_support( snax_get_poll_post_type(), AMP_QUERY_VAR );
}

/**
 * Replace content hooks with AMP ones
 */
function snax_amp_initialize() {
	if ( is_amp_endpoint() ) {

		remove_action( 'snax_after_content_single_post',	'snax_render_post_origin' );

		remove_filter( 'the_content', 			'snax_render_quiz' );
		add_filter( 'the_content', 				'snax_amp_render_quiz' );

		remove_filter( 'the_content', 			'snax_render_poll' );
		add_filter( 'the_content', 				'snax_amp_render_poll' );

		remove_shortcode( 'snax_content', 		'snax_content_shortcode' );
		add_shortcode( 'snax_content', 			'snax_amp_content_shortcode' );

		remove_filter( 'the_content', 			'snax_item_content' );
		add_filter( 'the_content', 				'snax_amp_item_content' );

		add_filter( 'amp_content_sanitizers', 	'snax_amp_replace_sanitizer' );

		add_action( 'amp_post_template_css', 	'snax_amp_add_css' );
		add_filter( 'amp_post_template_data', 	'snax_amp_postprocess' );

		add_filter( 'amp_content_sanitizers', 'snax_amp_add_sanitizers',10, 2 );
	}
}

/**
 * Inject AMP CSS
 */
function snax_amp_add_css() {
	$snax_font_dir_uri = trailingslashit( snax()->plugin_url ) . '/css/snaxicon/fonts/';
?>
	@font-face {
	font-family: "snaxicon";
	src:url("<?php echo $snax_font_dir_uri; ?>snaxicon.eot");
	src:url("<?php echo $snax_font_dir_uri; ?>snaxicon.eot?#iefix") format("embedded-opentype"),
	url("<?php echo $snax_font_dir_uri; ?>snaxicon.woff") format("woff"),
	url("<?php echo $snax_font_dir_uri; ?>snaxicon.ttf") format("truetype"),
	url("<?php echo $snax_font_dir_uri; ?>snaxicon.svg#snaxicon") format("svg");
	font-weight: normal;
	font-style: normal;
	}
<?php
	if ( is_rtl() ) {
		include( trailingslashit( snax_get_plugin_dir() ) . '/css/amp-rtl.min.css' );
	} else {
		include( trailingslashit( snax_get_plugin_dir() ) . '/css/amp.min.css' );
	}
}

/**
 * Render AMP quiz
 *
 * @param string $content		Post content.
 *
 * @return string
 */
function snax_amp_render_quiz( $content ) {
	if ( is_singular( snax_get_quiz_post_type() ) ) {

		ob_start();

		echo '<div class="snax">';
		snax_get_template_part( 'amp/quiz' );
		echo '</div>';

		$content .= ob_get_clean();
	}

	return $content;
}

/**
 * Render AMP poll
 *
 * @param string $content		Post content.
 *
 * @return string
 */
function snax_amp_render_poll( $content ) {
	if ( is_singular( snax_get_poll_post_type() ) ) {

		ob_start();

		echo '<div class="snax">';
		snax_get_template_part( 'amp/poll' );
		echo '</div>';

		$content .= ob_get_clean();
	}

	return $content;
}

/**
 * Return snax content
 *
 * @return string
 */
function snax_amp_content_shortcode() {
	$content = '';

	if ( snax_is_format() ) {
		ob_start();
		snax_get_template_part( 'amp/content', snax_get_format() );
		$content = ob_get_clean();
	}

	return apply_filters( 'snax_amp_content_shortcode_output', $content );
}

/**
 * Render referral link
 */
function snax_amp_render_referral_link() {
	snax_get_template_part( 'amp/referral-link' );
}

/**
 * Render comments box
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 */
function snax_amp_render_comments_box( $post = null ) {
	snax_get_template_part( 'amp/item-comments' );
}

/**
 * Append item HTML to post content
 *
 * @param string $content           Post content.
 *
 * @return string
 */
function snax_amp_item_content( $content ) {
	if ( snax_is_item() ) {

		ob_start();
		snax_get_template_part( 'amp/single-item' );
		$out = ob_get_clean();

		$out = str_replace( '%%SNAX_ITEM_DESCRIPTION%%', $content, $out );

		// Append to content.
		$content = $out;
	}

	return $content;
}

/**
 * Render new item form.
 */
function snax_amp_render_new_item_form() {
	$show = false;

	if ( snax_is_post_open_list() ) {
		$show = true;
	}

	if ( apply_filters( 'snax_render_new_item_form', $show ) ) {
		snax_get_template_part( 'amp/form-new' );
	}
}


/**
 * Render upvote/downvote box
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 */
function snax_amp_render_voting_box( $post = null, $user_id = 0) {
	if ( snax_show_item_voting_box( $post ) ) {
		$post = get_post( $post );

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}
		$snax_voting_score = (int) snax_get_voting_score( $post );
		if ( ! $snax_voting_score ) {
			$snax_class[] = 'snax-voting-score-0';
		}
		$current_vote 	= snax_user_upvoted( $post->ID, $user_id ) ? 'upvote' : '';
		$current_vote 	= snax_user_downvoted( $post->ID, $user_id ) ? 'downvote' : $current_vote;
		?>
		<div class="snax-voting" data-snax-item-id="<?php echo absint( $post->ID ); ?>">

		<amp-state id="count<?php echo esc_attr( $post->ID );?>"> <script type="application/json">
		"<?php echo (int) $snax_voting_score?>"
		</script></amp-state>
		<amp-state id="class<?php echo esc_attr( $post->ID );?>"><script type="application/json">
		"<?php echo esc_attr( $current_vote );?>"
		</script></amp-state>

			<div class="snax-voting-score">
				<strong snax-amp-text="count<?php echo esc_attr( $post->ID );?>"><?php echo (int) $snax_voting_score?></strong>
			</div>

			<?php
			if ( snax_show_item_upvote_link( $post ) ) :
				$nonce = wp_create_nonce( 'snax-vote-item' );?>
				<form target="_blank" id="snax-form-upvote-<?php echo esc_attr( $post->ID );?>" method="post" target="_blank"
				snax-amp-class="class<?php echo esc_attr( $post->ID );?>"  class="<?php echo esc_attr( $current_vote );?>"
				action-xhr="<?php echo admin_url('admin-ajax.php')?>"
				on="submit-success:AMP.setState({count<?php echo esc_attr( $post->ID );?>: event.response.args.html}),AMP.setState({class<?php echo esc_attr( $post->ID );?>: event.response.args.class})">
				<input type="hidden" name="action" 			value="snax_amp_vote_item">
				<input type="hidden" name="security" 		value="<?php echo esc_attr( $nonce );?>">
				<input type="hidden" name="snax_item_id" 	value="<?php echo esc_attr( $post->ID );?>">
				<input type="hidden" name="snax_author_id" 	value="<?php echo esc_attr( $user_id );?>">
				<input type="hidden" name="snax_vote_type" 	value="upvote">
				<button type="submit" class="snax-voting-upvote" title="Upvote">Upvote</button>
				</form>
			<?php endif;
			?>

			<?php
			if ( snax_show_item_downvote_link( $post ) ) :
				$nonce = wp_create_nonce( 'snax-vote-item' );?>
				<form target="_blank" id="snax-form-downvote-<?php echo esc_attr( $post->ID );?>" method="post" target="_blank"
				snax-amp-class="class<?php echo esc_attr( $post->ID );?>"  class="<?php echo esc_attr( $current_vote );?>"
				action-xhr="<?php echo admin_url('admin-ajax.php')?>"
				on="submit-success:AMP.setState({count<?php echo esc_attr( $post->ID );?>: event.response.args.html}),AMP.setState({class<?php echo esc_attr( $post->ID );?>: event.response.args.class})">
				<input type="hidden" name="action" 			value="snax_amp_vote_item">
				<input type="hidden" name="security" 		value="<?php echo esc_attr( $nonce );?>">
				<input type="hidden" name="snax_item_id" 	value="<?php echo esc_attr( $post->ID );?>">
				<input type="hidden" name="snax_author_id" 	value="<?php echo esc_attr( $user_id );?>">
				<input type="hidden" name="snax_vote_type" 	value="downvote">
				<button type="submit" class="snax-voting-downvote" title="Downvote">Downvote</button>
				</form>
			<?php endif;
			?>
		</div>
		<?php
	}
}

/**
 * Replace AMP Sanitizer with our own to allow forms
 *
 * @param array $sanitizers Sanitizer classes.
 * @return array
 */
function snax_amp_replace_sanitizer( $sanitizers ) {
	require_once( 'amp-sanitizer-class.php' );
	unset( $sanitizers['AMP_Blacklist_Sanitizer'] );
	$sanitizers['Snax_AMP_Blacklist_Sanitizer'] = array();
	return $sanitizers;
}

/**
 * Render item author in AMP
 *
 * @param array $args       Extra arguments.
 */
function snax_amp_render_item_author( $args = array() ) {
	if ( snax_show_item_author() ) {
			$out = '';

			$args = wp_parse_args( $args, array(
				'avatar'      => true,
				'avatar_size' => 40,
			) );

			$out .= '<span class="snax-item-author" itemscope="" itemprop="author" itemtype="http://schema.org/Person">';
			$out .= sprintf(
				'<a href="%s" title="%s" rel="author">',
				snax_get_item_author_url(),
				sprintf( __( 'Posts by %s', 'snax' ), get_the_author() )
			);

			$out .= get_avatar( get_the_author_meta( 'email' ), $args['avatar_size'] );

			$out .= '<strong itemprop="name">' . get_the_author() . '</strong>';
			$out .= '</a>';
			$out .= '</span>';

			echo $out;
	}
}

/**
 * Force AMP permalinks.
 *
 * @param str     $url   The url.
 * @param WP_Post $post  The post.
 * @return str
 */
function snax_amp_force_amp_permalinks( $url, $post ) {
	$amp_url = amp_get_permalink( $post->ID );
	if ( isset( $amp_url ) ) {
		return $amp_url;
	} else {
		return $url;
	}
}

/**
 * Postprocess final output.
 *
 * @param array $data  AMP template data.
 * @return array
 */
function snax_amp_postprocess( $data ) {
	if ( isset( $data['post_amp_content'] ) ) {

		// retain binds.
		$content = $data['post_amp_content'];
		$pattern = '/snax-amp-(\w+)=/i';
		$replacement = '[$1]=';
		$content = preg_replace( $pattern, $replacement, $content );

		// retain JSON.
		$content = str_replace( '<script type="application/json"><![CDATA[', '<script type="application/json">', $content );
		$content = str_replace( ']]></script></amp-state>', '</script></amp-state>', $content );

		// add scripts.
		if ( 0 !== substr_count( $content, 'snax-voting' ) ) {
			$data['amp_component_scripts']['amp-form'] = 'https://cdn.ampproject.org/v0/amp-form-0.1.js';
			$data['amp_component_scripts']['amp-bind'] = 'https://cdn.ampproject.org/v0/amp-bind-0.1.js';
		}

		$data['post_amp_content'] = $content;
	}
	return $data;
}

/**
 * Add amp sanitizers.
 *
 * @param array   $sanitizers  Sanitizers.
 * @param WP_Post $post	Post.
 * @return array
 */
function snax_amp_add_sanitizers( $sanitizers, $post ) {

	$globally_allowed_attributes = AMP_Allowed_Tags_Generated::get_allowed_attributes();
		$allowed_attributes = array_merge( $globally_allowed_attributes, array(
			'target' => array(),
			'action-xhr' => array(),
		));
	$sanitizers['AMP_Tag_And_Attribute_Sanitizer']['amp_globally_allowed_attributes'] = $allowed_attributes;

	$globally_allowed_tags = AMP_Allowed_Tags_Generated::get_allowed_tags();
			$allowed_tags = array_merge( $globally_allowed_tags, [
				'form' => array(
					array(
						'attr_spec_list' => array(),
						'tag_spec' => array(),
					),
				),
				'amp-state' => array(
					array(
						'attr_spec_list' => array(),
						'tag_spec' => array(),
					),
				),
			]);
	$sanitizers['AMP_Tag_And_Attribute_Sanitizer']['amp_allowed_tags'] = $allowed_tags;
	return $sanitizers;
}
