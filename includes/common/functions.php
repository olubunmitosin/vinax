<?php
/**
 * Snax Common Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Check whether debug mode is enabled
 *
 * @return bool
 */
function snax_in_debug_mode() {
	return apply_filters( 'snax_debug_mode', false );
}

/**
 * Assist pagination by returning correct page number
 *
 * @return int Current page number
 */
function snax_get_paged() {
	global $wp_query;

	// Check the query var.
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );

		// Check query paged.
	} elseif ( ! empty( $wp_query->query['paged'] ) ) {
		$paged = $wp_query->query['paged'];
	}

	// Paged found.
	if ( ! empty( $paged ) ) {
		return (int) $paged;
	}

	// Default to first page.
	return 1;
}

/**
 * Snax method of formatting numeric values
 *
 * @param float  $number Number being formatted.
 * @param bool   $decimals Number of decimal points.
 * @param string $dec_point Separator for decimal point.
 * @param string $thousands_sep Thousends separator.
 *
 * @return float
 */
function snax_number_format( $number, $decimals = false, $dec_point = '.', $thousands_sep = ',' ) {
	if ( ! is_numeric( $number ) ) {
		$number = 0;
	}

	return apply_filters( 'snax_number_format', number_format( $number, $decimals, $dec_point, $thousands_sep ), $number, $decimals, $dec_point, $thousands_sep );
}

/**
 * Return the date formatted and localized
 *
 * @param string $date_string Date string in any valid format.
 *
 * @return string                   Localized date.
 */
function snax_date_format( $date_string ) {
	$format = apply_filters( 'snax_datetime_format', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

	return date_i18n( $format, strtotime( $date_string ) );
}

/**
 * Return registered social share links
 *
 * @return array
 */
function snax_get_share_links() {


	$links = array(
		'facebook'  => array(
			'pattern' => 'https://www.facebook.com/sharer.php?u=[PERMALINK]&amp;t=[TITLE]',
			'label'   => __( 'Share on Facebook', 'snax' ),
		),
		'twitter'   => array(
			'pattern' => 'https://twitter.com/home?status=[TITLE]%20[SHORTLINK]',
			'label'   => __( 'Share on Twitter', 'snax' ),
		),
		'pinterest' => array(
			'pattern' => 'https://pinterest.com/pin/create/button/?url=[PERMALINK]&amp;description=[TITLE]&amp;media=[THUMBNAIL]',
			'label'   => __( 'Share on Pinterest', 'snax' ),
		),
	);

	return apply_filters( 'snax_share_links', $links );
}

/**
 * Prepare share url
 *
 * @param string  $url      Input url.
 * @param WP_Post $post     Post object.
 *
 * @return mixed
 */
function snax_build_post_share_url( $url, $post ) {
	$placeholders = array();

	$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );

	$placeholders['TITLE']     = get_the_title( $post );
	$placeholders['PERMALINK'] = get_permalink( $post->ID );
	$placeholders['SHORTLINK'] = wp_get_shortlink( $post->ID );
	$placeholders['THUMBNAIL'] = is_array( $thumbnail ) && ! empty( $thumbnail ) ? $thumbnail[0] : '';

	$placeholders = apply_filters( 'snax_post_share_url_placeholders', $placeholders );

	foreach ( $placeholders as $name => $value ) {
		$url = str_replace( '[' . $name . ']', rawurlencode( $value ), $url );
	}

	return $url;
}

/**
 * Prevent Snax content in the excerpt
 *
 * @param string $post_excerpt          Post excerpt.
 *
 * @return string
 */
function snax_remove_post_content( $post_excerpt ) {
	remove_filter( 'the_content', 'snax_post_content' );

	return $post_excerpt;
}

/**
 * Restore Snax content after excerpt
 *
 * @param string $post_excerpt      Post excerpt.
 *
 * @return string
 */
function snax_restore_post_content( $post_excerpt ) {
	add_filter( 'the_content', 'snax_post_content' );

	return $post_excerpt;
}

/**
 * Redirect to url if exists in request object
 */
function snax_redirect_to_url() {
	$redirect_url = get_query_var( 'snax_redirect_to_url' );

	if ( ! empty( $redirect_url ) ) {
		wp_redirect( $redirect_url );
	}
}

/**
 * When new post is created (in draft mode), redirect to its edition page
 *
 * @param string $url				Redirect url.
 * @param int    $post_id			Post id.
 *
 * @return string
 */
function snax_redirect_to_draft_edition( $url, $post_id ) {
	if ( 'draft' === get_post_status( $post_id ) ) {
		$url = snax_get_post_edit_url( $post_id );

		$draft_saved_var = snax_get_url_var( 'draft_saved' );

		$url = add_query_arg( array(
			$draft_saved_var => 'success',
		), $url );
	}

	return $url;
}

function snax_get_post_preview_url( $post_id = 0 ) {
	// If not passed, try to get from url var.
	if ( ! $post_id ) {
		$post_var = snax_get_url_var( 'post' );

		$post_id = filter_input( INPUT_GET, $post_var, FILTER_SANITIZE_NUMBER_INT );
	}

	if ( ! $post_id ) {
		return '';
	}

	return get_preview_post_link( $post_id );
}

function snax_get_post_edit_url( $post_id = 0 ) {
	$post = get_post( $post_id );
	$format_var = snax_get_url_var( 'format' );
	$post_var 	= snax_get_url_var( 'post' );

	$url = add_query_arg( array(
		$format_var => snax_get_format( $post->ID ),
		$post_var	=> $post->ID,

	), snax_get_frontend_submission_page_url() );

	return $url;
}

function snax_is_post_format_editable( $post_id = 0 ) {
	$post = get_post( $post_id );

	// User can edit only Snax posts.
	$format = snax_get_format( $post->ID );

	if ( ! $format ) {
		return false;
	}

	// User can edit only posts that were not published yet.
	if ( 'publish' === $post->post_status ) {
		return false;
	}

	// Skip for non-editable formats.
	$non_editable_formats = array( 'image', 'embed', 'meme' );

	return ! in_array( $format, $non_editable_formats, true );
}

/**
 * Redirect after successful log out.
 */
function snax_logout_redirect() {
	$redirect_to_url              = filter_input( INPUT_GET, 'redirect_to', FILTER_SANITIZE_URL );
	$frontend_submission_page_url = snax_get_frontend_submission_page_url();
	if ( empty( $frontend_submission_page_url ) ) {
		return;
	}
	// After logging out, we don't want to leave user on the Frontend Submission page (it opens popup).
	if ( false !== strpos( $redirect_to_url, $frontend_submission_page_url ) ) {
		wp_redirect( home_url() );
		exit();
	}
}

/**
 * Adds admin bar items for easy access to the Snax tools
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
 */
function snax_admin_bar_menu( $wp_admin_bar ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	$nodes = array();

	// Realod post meta data (counters).
	if ( is_single() && ( snax_is_format( 'list' ) || snax_is_format( 'gallery' ) ) ) {
		$nodes[] = array(
			'id'     => 'snax_reload_meta',
			'parent' => 'snax',
			'title'  => __( 'Reload post meta', 'snax' ),
			'href'   => '?snax_post=' . get_the_ID() . '&snax_action=reload_meta',
			'meta'   => false,
		);
	}

	/**
	// Add only in admin area.
	if ( is_admin() ) {
		// User capabilities.
		$nodes[] = array(
			'id'     => 'snax_reset_user_roles',
			'parent' => 'snax',
			'title'  => __( 'Reset user roles', 'snax' ),
			'href'   => '?snax_action=reset_user_roles',
			'meta'   => false,
		);
	}
	*/

	// Snax main node.
	$submission_page_id = snax_get_frontend_submission_page_id();

	$wp_admin_bar->add_node( array(
		'id'    => 'snax',
		'title' => __( 'Snax', 'snax' ),
		'href'  => $submission_page_id ? get_permalink( $submission_page_id ) : false,
	) );

	if ( ! empty( $nodes ) ) {
		foreach ( $nodes as $node ) {
			$wp_admin_bar->add_node( $node );
		}
	}
}

/**
 * Return the post id
 *
 * @return int
 */
function snax_get_post_id() {
	$id = get_the_ID();

	return apply_filters( 'snax_get_post_id', $id );
}

/**
 * Render feedback
 */
function snax_render_feedback() {
	snax_get_template_part( 'feedback', 'processing' );
}

/**
 * Render link to edit post.
 */
function snax_render_edit_post_link() {
	if ( ! is_single() ) {
		return;
	}
	if ( ! is_preview() && 'publish' === get_post_status() ) {
		return;
	}
	if ( current_user_can( 'snax_edit_posts', get_the_ID() ) || current_user_can( 'edit_posts', get_the_ID() ) ) {
	?>
	<div class="snax-toolbar">
		<a href="<?php echo esc_url( snax_get_post_edit_url() ); ?>"><?php esc_html_e( 'Edit', 'snax' ); ?></a>
	</div>
	<?php
	}
}

/**
 * Render link to the legal page
 */
function snax_render_legal_page_link() {
	?>
	<?php if ( snax_get_legal_page_id() ) : ?>
		<a class="snax-legal-link" href="<?php echo esc_url( snax_get_legal_page_url() ); ?>"
		   target="_blank"><?php esc_html_e( 'Learn more', 'snax' ); ?></a>
	<?php endif; ?>
	<?php
}

/**
 * Check whether legal page is set so we can force user to accept its terms
 *
 * @return bool
 */
function snax_legal_agreement_required() {
	return apply_filters( 'snax_legal_agreement_required', (bool) snax_get_legal_page_id() );
}

/**
 * If media is attached to more than one post or to demo post, we don't want to delete it during this post deletion
 *
 * @param bool $delete                  True if media should be deleted.
 * @param int  $media_id                Processing media id.
 *
 * @return bool
 */
function snax_prevent_deletion_attached_media( $delete, $media_id ) {
	$args = array(
		'post_type'     => array( 'post', snax_get_item_post_type() ),
		'meta_key'      => '_thumbnail_id',
		'meta_value'    => $media_id,
	);

	$query = new WP_Query( $args );

	$found_posts = intval( $query->found_posts );

	// Attached to more than one post?
	if ( $found_posts > 1 ) {
		$delete = false;
		// Attached just to one post, but demo post or demo post item?
	} else if ( 1 === $found_posts ) {
		$post = $query->posts[0];

		// Is demo post?
		if ( snax_is_demo_post( $post ) ) {
			$delete = false;
		}

		// Is demo post item?
		if ( snax_is_item( $post ) && snax_is_demo_post( snax_get_item_parent_id( $post ) ) ) {
			$delete = false;
		}
	}

	return $delete;
}

/**
 * Sanitize content for Snax allowed HTML tags for post content.
 *
 * @param string $content       Post content.
 *
 * @return string
 */
function snax_kses_post( $content, $extra_allowed_html = array() ) {
	// Replace <b> to <strong>.
	$content = str_replace(
		array( '<b>', '</b>' ),
		array( '<strong>', '</strong>' ),
		$content
	);

	$allowed_html = array(
		'a' => array(
			'href' => true,
		),
		'strong' 		=> array(),
		'em' 			=> array(),
		'p'				=> array(),
		'h2'			=> array(),
		'h3'			=> array(),
		'ol'			=> array(),
		'ul'			=> array(),
		'li'			=> array(),
		'blockquote'	=> array(),
		'figure'		=> array(
			'class' => true,
		),
		'figcaption'	=> array(
			'class' => true,
		),
	);

	if ( ! empty( $extra_allowed_html ) ) {
		$allowed_html = array_merge( $allowed_html, $extra_allowed_html );
	}

	$allowed_html = apply_filters( 'snax_allowed_html', $allowed_html );

	$content = wp_kses( $content, $allowed_html );

	// Add nofollow to links.
	$content = str_replace( '<a ', '<a rel="nofollow" ', $content );

	return $content;
}

/**
 * Add Snax items to the menu
 *
 * @param WP_Post $menu_item        The menu item.
 *
 * @return WP_Post
 */
function snax_setup_nav_menu_item( $menu_item ) {
	if ( is_admin() ) {
		return $menu_item;
	}

	$menu_classes = $menu_item->classes;

	if ( is_array( $menu_classes ) ) {
		$menu_classes = implode( ' ', $menu_item->classes );
	}

	// The only place we can identify that the $menu_item is ours is CSS class.
	if ( ! preg_match( '/snax-([^-]+)-nav/', $menu_classes, $matches ) ) {
		return $menu_item;
	}

	$menu_item_id = $matches[1];

	switch ( $menu_item_id ) {
		case 'logout' :
			if ( ! is_user_logged_in() ) {
				$menu_item->_invalid = true;
			}

			break;

		case 'login' :
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				if ( ! is_array( $menu_item->classes ) ) {
					$menu_item->classes = array();
				}

				$menu_item->classes[] = 'snax-login-required';
			}

			break;

		case 'register' :
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			}

			break;
	}

	// Check if current page.
	$http_host      = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL );
	$request_uri    = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
	$current_url    = ( is_ssl() ? 'https://' : 'http://' ) . $http_host . $request_uri;

	if ( false !== strpos( $current_url, $menu_item->url ) ) {
		if ( ! is_array( $menu_item->classes ) ) {
			$menu_item->classes = array();
		}

		$menu_item->classes[] = 'current_page_item';
		$menu_item->classes[] = 'current-menu-item';
	}

	return $menu_item;
}

/**
 * Clean up orphan uploads
 */
function snax_do_clean_up_junk_uploads() {
	snax_remove_orphan_items();
	snax_remove_orphan_attachments();
}

/**
 * Find and remove permanently orphan items
 */
function snax_remove_orphan_items() {
	$query_args = array(
		// Orphan.
		'post_parent'       => 0,
		'post_type'         => snax_get_item_post_type(),
		'post_status'       => array( 'publish', 'pending', 'draft' ),
		// Items created over a day ago.
		'date_query'        => array(
			'column'    => 'post_date',
			'before'    => '1 day ago',
		),
		'posts_per_page'    => -1,
	);

	$query_args = apply_filters( 'snax_orphan_items_query_args', $query_args );

	$orphan_items = get_posts( $query_args );

	foreach ( $orphan_items as $orphan_item ) {
		$media_id     = get_post_thumbnail_id( $orphan_item->ID );

		// Check if media was not assigned (somehow) to any other post.
		$delete_media = apply_filters( 'snax_delete_media', true, $media_id );

		if ( $delete_media ) {
			wp_delete_attachment( $media_id, true );
		}

		wp_delete_post( $orphan_item->ID, true );
	}
}

/**
 * Find and remove permanently orphan attachments
 */
function snax_remove_orphan_attachments() {
	$query_args = array(
		'post_type'             => 'attachment',
		'post_status'           => 'inherit',
		// Orphan.
		'post_parent'           => 0,
		'meta_key'              => '_snax_media_belongs_to',
		'meta_compare'          => 'EXISTS',
		// Attachment created over a day ago.
		'date_query'        => array(
			'column'    => 'post_date',
			'before'     => '1 day ago',
		),
		'posts_per_page'    => -1,
	);

	$query_args = apply_filters( 'snax_orphan_attachments_query_args', $query_args );

	$orphan_attachments = get_posts( $query_args );

	foreach ( $orphan_attachments as $orphan_attachment ) {
		wp_delete_attachment( $orphan_attachment->ID, true );
	}
}

/**
 * Format source link.
 *
 * @param string $content Post content.
 *
 * @return string
 */
function snax_add_caption_source( $content ) {
	$content = str_replace( 'class="snax-figure-source"', 'data-snax-placeholder="' . esc_attr__( 'Source', 'snax' ) . '" class="snax-figure-source"', $content );

	return $content;
}

/**
 * Add body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function snax_body_class( $classes ) {
	$classes[] = 'snax-hoverable';

	return $classes;
}

/**
 * Return list of all tags
 *
 * @param int   $limit		Optional. If tags is more that $limit, return empty set.
 * @param array $args		Optional. Tags query args.
 *
 * @return array
 */
function snax_get_tags_array( $limit = -1, $args = array() ) {
	$arr = array();

	$defaults = array(
		'hide_empty' => false,
	);

	$args = wp_parse_args( $args, $defaults );

	$tags = get_tags( $args );

	if ( -1 !== $limit && (int) count( $tags ) > $limit ) {
		return $arr;
	}

	foreach ($tags as $tag) {
		$arr[] = $tag->name;
	}

	return $arr;
}

/**
 * Do a format specific action (backward compatibility)
 *
 * @param int    $post_id           Post id.
 * @param string $format            Post format.
 */
function snax_do_format_specific_action( $post_id, $format ) {
	do_action( 'snax_post_format_' . $format . '_created', $post_id );
}

/**
 * Send mail to admin when new item was added
 *
 * @param int    $post_id           Post id.
 * @param string $format            Post format.
 * @param string $origin            Item origin (post | contribution).
 */
function snax_notify_admin_about_new_item( $post_id, $format, $origin ) {
	if ( ! snax_mail_notifications() ) {
		return;
	}

	// Item is a part of a new post, don't notify about that.
	if ( 'post' === $origin ) {
		return;
	}

	$post            = get_post( $post_id );
	$admin_email     = get_option( 'admin_email' );
	$permalink       = get_permalink( $post );
	$link            = '<a href="' . $permalink . '">' . $permalink . '</a>';
	$review_required = snax_get_item_pending_status() === get_post_status( $post );
	$subject         = _x( 'New item was submitted.', 'Mail notification', 'snax' );

	if ( $review_required ) {
		$message = sprintf( _x( 'New item (%1$s) awaits approval: %2$s', 'Mail notification', 'snax' ), $format, $link );
	} else {
		$message = sprintf( _x( 'New item (%1$s) was published: %2$s', 'Mail notification', 'snax' ), $format, $link );
	}

	add_filter( 'wp_mail_content_type', 'allow_html_in_mails' );
	wp_mail( $admin_email, $subject, $message );
	remove_filter( 'wp_mail_content_type', 'allow_html_in_mails' );
}

/**
 * Send mail to admin when new post was added
 *
 * @param int    $post_id           Post id.
 * @param string $format            Post format.
 */
function snax_notify_admin_about_new_post( $post_id, $format ) {
	if ( ! snax_mail_notifications() ) {
		return;
	}

	$post            = get_post( $post_id );
	$admin_email     = get_option( 'admin_email' );
	$permalink       = get_permalink( $post );
	$link            = '<a href="' . $permalink . '">' . $permalink . '</a>';
	$review_required = snax_get_post_pending_status() === get_post_status( $post );
	$subject         = _x( 'New post was submitted.', 'Mail notification', 'snax' );

	if ( $review_required ) {
		$message = sprintf( _x( 'New post (%1$s) awaits approval: %2$s', 'Mail notification', 'snax' ), $format, $link );
	} else {
		$message = sprintf( _x( 'New post (%1$s) was published: %2$s', 'Mail notification', 'snax' ), $format, $link );
	}

	add_filter( 'wp_mail_content_type', 'allow_html_in_mails' );
	wp_mail( $admin_email, $subject, $message );
	remove_filter( 'wp_mail_content_type', 'allow_html_in_mails' );
}

/**
 * Set content type to allow HTML in wp_mail
 *
 * @return str
 */
function allow_html_in_mails() {
	return 'text/html';
}

/**
 * Load FB SDK script in footer
 */
function snax_enqueue_fb_sdk() {
	add_action( 'wp_footer', 'snax_print_fb_sdk', 100 );
}

/**
 * Print FB SDK
 */
function snax_print_fb_sdk() {
	$facebook_sdk_src = apply_filters( 'snax_facebook_sdk_src', '//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5' );
	?>
	<div id="fb-root"></div>
	<script type="text/javascript">
		(function (d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s);
			js.id = id;
			js.src = "<?php echo esc_url_raw( $facebook_sdk_src ); ?>";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	</script>
	<?php
}

function snax_remove_post_tags( $post_id ) {
	$post_tags = wp_get_object_terms( $post_id, 'post_tag' );

	$term_ids = array();

	foreach ( $post_tags as $post_tag ) {
		$term_ids[] = $post_tag->term_id;
	}

	wp_remove_object_terms( $post_id, $term_ids, 'post_tag' );
}

/**
 * Handles sending password retrieval email to user.
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function snax_retrieve_password() {
	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) ) {
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
	} elseif ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
		if ( empty( $user_data ) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}

	/**
	 * Fires before errors are returned from a password reset request.
	 *
	 * @since 2.1.0
	 * @since 4.4.0 Added the `$errors` parameter.
	 *
	 * @param WP_Error $errors A WP_Error object containing any errors generated
	 *                         by using invalid credentials.
	 */
	do_action( 'lostpassword_post', $errors );

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.'));
		return $errors;
	}

	// Redefining user_login ensures we return the right case in the email.
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;
	$key = get_password_reset_key( $user_data );

	if ( is_wp_error( $key ) ) {
		return $key;
	}

	$message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
	$message .= network_home_url( '/' ) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

	if ( is_multisite() ) {
		$blogname = get_network()->site_name;
	} else {
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option we want to reverse this for the plain text arena of emails.
		 */
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	}

	/* translators: Password reset email subject. 1: Site name */
	$title = sprintf( __('[%s] Password Reset'), $blogname );

	/**
	 * Filters the subject of the password reset email.
	 *
	 * @since 2.8.0
	 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $title      Default email title.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user_data  WP_User object.
	 */
	$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

	/**
	 * Filters the message body of the password reset mail.
	 *
	 * @since 2.8.0
	 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $message    Default mail message.
	 * @param string  $key        The activation key.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user_data  WP_User object.
	 */
	$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

	if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
		wp_die( __('The email could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.') );

	return true;
}

/**
 * Add .mov file to list of allowed video player files
 *
 * @param array $exts       Allowed extensions.
 *
 * @return array
 */
function snax_allow_playing_mov_files( $exts ) {
	if ( ! in_array( 'mov', $exts, true ) ) {
		$exts[] = 'mov';
	}

	return $exts;
}

/**
 * Filters the output of the video shortcode.
 *
 * @param string $output  Video shortcode HTML output.
 * @param array  $atts    Array of video shortcode attributes.
 * @param string $video   Video file.
 * @param int    $post_id Post ID.
 * @param string $library Media library used for the video shortcode.
 *
 * @return string
 */
function snax_fix_mov_videos( $output, $atts, $video, $post_id, $library ) {
	if ( strpos( $atts['src'], '.mov' ) > 0 ) {
		preg_match( '/<video.*>/Um' , $output, $tag );
		if ( ! strpos( $tag[0], 'src' ) ) {
			$src = '<video src="' . $atts['src'] . '"';
			$output = str_replace( '<video', $src, $output );
			preg_match( '/<source.*>/Um' , $output, $source_tag );
			$output = str_replace( $source_tag, '', $output );
			$output = str_replace( 'wp-video-shortcode', 'snax-native-video', $output );
		}
	}
	return $output;
}

function snax_embed_supported_services( $media_type = '' ) {
	$services = snax_get_embed_supported_services();
	?>
	<div class="snax-supported-services">
		<?php esc_html_e( 'Supported Services:', 'snax' ); ?>
		<p class="snax-supported-services-list">
		<?php foreach( $services as $service_id => $service_data ): ?>
			<?php if ( $media_type && ! in_array( $media_type, $service_data['media_types'], true ) ) continue; ?>

			<span title="<?php echo esc_attr( $service_data['label'] ); ?>" class="snax-supported-<?php echo sanitize_html_class( $service_id ); ?>"></span>

		<?php endforeach; ?>
		</p>
	</div>
	<?php
}

function snax_get_embed_supported_services() {
	$services = array(
		'fb' => array(
			'label'         => 'Facebook',
			'media_types'   => array( 'audio', 'video' ),
		),
		'twitter' => array(
			'label' => 'Twitter',
			'media_types'   => array(),
		),
		'youtube' => array(
			'label' => 'YouTube',
			'media_types'   => array( 'video' ),
		),
		'instagram' => array(
			'label' => 'Instagram',
			'media_types'   => array(),
		),
		'vimeo' => array(
			'label' => 'Vimeo',
			'media_types'   => array( 'video' ),
		),
		'spotify' => array(
			'label' => 'Spotify',
			'media_types'   => array( 'audio' ),
		),
		'soundcloud' => array(
			'label' => 'Soundcloud',
			'media_types'   => array( 'audio' ),
		),
		'mixcloud' => array(
			'label' => 'Mixcloud',
			'media_types'   => array( 'audio' ),
		),
		'v' => array(
			'label' => 'Vkontakte',
			'media_types'   => array( 'audio', 'video' ),
		),
		'dailymotion' => array(
			'label' => 'Dailymotion',
			'media_types'   => array( 'video' ),
		),
		'giphy' => array(
			'label' => 'Giphy',
			'media_types'   => array( 'video' ),
		),
		'imgur' => array(
			'label' => 'Imgur',
			'media_types'   => array(),
		),
	);

	return apply_filters( 'snax_embed_supported_services', $services );
}

/**
 * Get waiting room filter query var.
 *
 * @return string
 */
function snax_get_waiting_room_query_var() {
	return apply_filters( 'snax_get_archive_filter_query_var', 'waiting-room' );
}

/**
 * Apply the waiting room to the query.
 *
 * @param WP_Query $query Archive main query.
 */
function snax_apply_waiting_room_filter( $query ) {
	$is_collection = is_archive() || is_home() ;
	$query_var = isset( $_GET[ snax_get_waiting_room_query_var() ] );
	if ( $is_collection && $query->is_main_query() && $query_var ) {
			$query->set( 'post_status', 'pending' );
			$query->set( 'tax_query', array( array(
				'taxonomy' => snax_get_snax_format_taxonomy_slug(),
				'field' => 'slug',
				'operator' => 'EXISTS',
				),
			) );
			$query->is_archive = true;
	}
}

/**
 * Apply archive template to the waiting room
 *
 * @param  string $template Template.
 * @return string
 */
function snax_apply_waiting_room_template( $template ) {
	if ( isset( $_GET[ snax_get_waiting_room_query_var() ] ) && is_home() ) {
		global $wp_query;
		$template = get_archive_template();
	}
	return $template;
}

/**
 * Set post featured image
 *
 * @param int $post_id              Post id.
 * @param int $attachment_id        Attachment id.
 */
function snax_set_post_featured_image( $post_id, $attachment_id ) {
	if ( ! $post_id || ! $attachment_id ) {
		return;
	}

	set_post_thumbnail( $post_id, $attachment_id );

	// Attach featured media to item (Media Library, the "Uploded to" column).
	wp_update_post( array(
		'ID'            => $attachment_id,
		'post_parent'   => $post_id,
	) );
}

function snax_parse_opengraph( $url ) {
	if ( ! class_exists( 'DOMDocument' ) ) {
		return new WP_Error( 'missing_class', esc_html__( 'DOMDocument parser is not available on your server.', 'snax' ) );
	}

	$content = @file_get_contents( $url );

	if ( ! $content ) {
		return new WP_Error( 'empty_content', esc_html__( 'Page content is empty or not accessible.', 'snax' ) );
	}

	$doc = new DOMDocument();

	// Fix XML.
	if ( false === strpos( $content, 'xml encoding=' ) ) {
		$content = '<?xml encoding="utf-8" ?>' . $content;
	}

	// Load content.
	@$doc->loadHTML( $content );

	// Open Graph data.
	$og_data = array();

	// Meta tags.
	$meta_tags = $doc->getElementsByTagName( 'meta' );

	// Process OG meta.
	if ( ! empty( $meta_tags ) ) {
		for ( $i = 0; $i < $meta_tags->length; $i++ ) {
			$meta = $meta_tags->item($i);

			foreach ( array( 'name', 'property' ) as $name ) {
				$meta_name  = $meta->getAttribute( $name );
				$meta_attrs = explode(':', $meta_name );

				if ( 'og' === $meta_attrs[0]) {
					if ( isset( $og_data[ $meta_name ] ) && ( ! is_array( $og_data[ $meta_name ] ) ) ) {
						$og_data[ $meta_name ] = array( $og_data[ $meta_name ], $meta->getAttribute('content') );
					} else if ( isset( $og_data[ $meta_name ] ) && ( is_array( $og_data[ $meta_name ] ) ) ) {
						$og_data[ $meta_name ][] = $meta->getAttribute('content');
					} else {
						$og_data[ $meta_name ] = $meta->getAttribute('content');
					}
				}
			}
		}
	}

	return $og_data;
}
