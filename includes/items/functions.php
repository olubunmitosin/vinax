<?php
/**
 * Snax Items Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Return the unique id of the custom post type for items
 *
 * @return string The unique item post type id
 */
function snax_get_item_post_type() {
	return snax()->item_post_type;
}

/**
 * Return post status for newly added items
 *
 * @param int $author_id            Author id.
 * @param int $post_id              Post id.
 *
 * @return string
 */
function snax_get_item_init_status( $author_id, $post_id ) {
	$post = get_post( $post_id );

	if ( (int) $post->post_author === (int) $author_id ) {
		$status = snax_get_item_approved_status();
	} else {
		$status = user_can( $author_id, 'snax_publish_items' ) ? snax_get_item_approved_status() : snax_get_item_pending_status();
	}

	return apply_filters( 'snax_item_init_status', $status );
}

/**
 * Return post status for approved items
 *
 * @return string
 */
function snax_get_item_approved_status() {
	return apply_filters( 'snax_item_approved_status', 'publish' );
}

/**
 * Return post status for pending items
 *
 * @return string
 */
function snax_get_item_pending_status() {
	return apply_filters( 'snax_item_pending_status', 'pending' );
}

/**
 * Add a new image item to the post
 *
 * @param int    $post_id           Post ID.
 * @param string $format            Media format (image, audio, video).
 * @param array  $item_args         Item data.
 *
 * @return int|WP_Error             Newly added item id (success) or WP_Error object (failure)
 */
function snax_add_media_item( $post_id = 0, $format, $item_args ) {
	$item_args = wp_parse_args( $item_args, array(
		'title'         => '',
		'media_id'      => '',
		'source'        => '',
		'ref_link'      => '',
		'description'   => '',
		'author_id'     => '',
		'status'        => '',
		'parent_format' => 'list',
		'origin'        => 'post',
		'meme_template' => '',
	) );

	// Check media id.
	$media_id = $item_args['media_id'];

	if ( empty( $media_id ) ) {
		return new WP_Error( 'empty_media_id', 'Item media id is required' );
	}

	// Check author.
	$author_id = $item_args['author_id'];

	if ( empty( $author_id ) ) {
		return new WP_Error( 'empty_author_id', 'Item author id is required' );
	}

	// Check creds.
	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		return new WP_Error( 'user_can_test_failed', sprintf( 'Author %d cannot submit another item.', $author_id ) );
	}

	$title         = $item_args['title'];
	$source        = $item_args['source'];
	$ref_link      = $item_args['ref_link'];
	$status        = $item_args['status'];
	$parent_format = $item_args['parent_format'];
	$origin        = $item_args['origin'];
	$meme_template = $item_args['meme_template'];

	switch( $format ) {
		case 'image':
			$description = $item_args['description'];
			break;

		case 'audio':
		case 'video':
			$description = wp_get_attachment_url( $media_id );

			if ( ! empty( $item_args['description'] ) ) {
				$description .= "\n\n" . $item_args['description'];
			}
			break;

		default:
			$description = '';
			break;
	}

	$new_post = array(
		'post_title'   => wp_strip_all_tags( $title ),
		'post_type'    => snax_get_item_post_type(),
		'post_parent'  => (int) $post_id,
		'post_content' => snax_kses_post( $description ),
		'post_status'  => ! empty( $status ) ? $status : snax_get_item_init_status( $author_id, $post_id ),
		'post_author'  => (int) $author_id,
		'menu_order'   => 999999,
	);

	// Create new item.
	$item_id = wp_insert_post( $new_post, false );

	if ( 0 === $item_id ) {
		return new WP_Error( 'wp_insert_post_failed', 'Item could not be added' );
	}

	/**
	 * Update post modified date.
	 */

	if ('image' === $format) {
		// Set item featured media.
		set_post_thumbnail( $item_id, $media_id );
	}

	// Attach media to item (Media Library, the "Uploded to" column).
	wp_update_post( array(
		'ID'            => $media_id,
		'post_parent'   => $item_id,
	) );

	add_post_meta( $item_id, '_snax_source', $source );
	add_post_meta( $item_id, '_snax_ref_link', $ref_link );
	add_post_meta( $item_id, '_snax_media_id', $media_id );
	add_post_meta( $item_id, '_snax_item_format', $format );
	add_post_meta( $item_id, '_snax_parent_format', $parent_format );
	add_post_meta( $item_id, '_snax_origin', $origin );
	add_post_meta( $item_id, '_snax_meme_template', $meme_template );


	// Increase submissions counter.
	if ( 0 !== $post_id && ! snax_is_item_pending_for_review( $item_id ) ) {
		snax_bump_post_submission_count( $post_id );
	}

	do_action( 'snax_item_added', $item_id, _x( $format, 'Item type', 'snax' ), $origin );

	return $item_id;
}

/**
 * Add a new embed item to the post
 *
 * @param int   $post_id                Post ID.
 * @param array $item_args              Item data.
 *
 * @return int|WP_Error   Newly added item id (success) or WP_Error object (failure)
 */
function snax_add_embed_item( $post_id = 0, $item_args ) {
	$item_args = wp_parse_args( $item_args, array(
		'title'         => '',
		'author_id'     => '',
		'embed_meta'    => array(),
		'description'   => '',
		'status'        => '',
		'parent_format' => 'list',
		'origin'        => 'post',
		'source'        => '',
		'ref_link'      => '',
	) );

	// Check embed code.
	$embed_meta = $item_args['embed_meta'];

	if ( empty( $embed_meta ) ) {
		return new WP_Error( 'empty_embed_meta', 'Embed meta data not defined' );
	}

	// Check author.
	$author_id = $item_args['author_id'];

	if ( empty( $author_id ) ) {
		return new WP_Error( 'empty_author_id', 'Item author field is required' );
	}

	// Check creds.
	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		return new WP_Error( 'user_can_test_failed', sprintf( 'Author %d cannot submit another item.', $author_id ) );
	}

	$title         = $item_args['title'];
	$description   = $embed_meta['url'];
	$status        = $item_args['status'];
	$parent_format = $item_args['parent_format'];
	$origin        = $item_args['origin'];
	$source        = $item_args['source'];
	$ref_link      = $item_args['ref_link'];

	if ( ! empty( $item_args['description'] ) ) {
		$description .= "\n\n" . $item_args['description'];
	}

	$new_post = array(
		'post_title'   => wp_strip_all_tags( $title ),
		'post_type'    => snax_get_item_post_type(),
		'post_parent'  => (int) $post_id,
		'post_content' => snax_kses_post( $description ),
		'post_status'  => ! empty( $status ) ? $status : snax_get_item_init_status( $author_id, $post_id ),
		'post_author'  => (int) $author_id,
		'menu_order'   => 999999,
	);

	// Create new item.
	$item_id = wp_insert_post( $new_post, false );

	if ( 0 === $item_id ) {
		return new WP_Error( 'wp_insert_post_failed', 'Item could not be added' );
	}

	// Set post format.
	set_post_format( $item_id, $embed_meta['post_format'] );

	// Don't remove this meta. It's not for displaying embed.
	// It's a copy to prepend to content while item updating on a list.
	add_post_meta( $item_id, '_snax_embed_url', $embed_meta['url'] );

	add_post_meta( $item_id, '_snax_embed_provider_name', $embed_meta['provider_name'] );
	add_post_meta( $item_id, '_snax_item_format', 'embed' );
	add_post_meta( $item_id, '_snax_parent_format', $parent_format );
	add_post_meta( $item_id, '_snax_origin', $origin );
	add_post_meta( $item_id, '_snax_source', $source );
	add_post_meta( $item_id, '_snax_ref_link', $ref_link );


	// Increase submissions counter.
	if ( 0 !== $post_id && ! snax_is_item_pending_for_review( $item_id ) ) {
		snax_bump_post_submission_count( $post_id );
	}

	do_action( 'snax_item_added', $item_id, _x( 'embed', 'Item type', 'snax' ), $origin );

	return $item_id;
}

/**
 * Add a new text item to the post
 *
 * @param int   $post_id                Post ID.
 * @param array $item_args              Item data.
 *
 * @return int|WP_Error   Newly added item id (success) or WP_Error object (failure)
 */
function snax_add_text_item( $post_id = 0, $item_args ) {
	$item_args = wp_parse_args( $item_args, array(
		'title'         => '',
		'author_id'     => '',
		'description'   => '',
		'status'        => '',
		'parent_format' => 'list',
		'origin'        => 'post',
		'ref_link'      => '',
	) );

	// Check author.
	$author_id = $item_args['author_id'];

	if ( empty( $author_id ) ) {
		return new WP_Error( 'empty_author_id', 'Item author field is required' );
	}

	// Check creds.
	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		return new WP_Error( 'user_can_test_failed', sprintf( 'Author %d cannot submit another item.', $author_id ) );
	}

	$title         = $item_args['title'];
	$description   = $item_args['description'];
	$status        = $item_args['status'];
	$parent_format = $item_args['parent_format'];
	$origin        = $item_args['origin'];
	$ref_link      = $item_args['ref_link'];

	$new_post = array(
		'post_title'   => wp_strip_all_tags( $title ),
		'post_type'    => snax_get_item_post_type(),
		'post_parent'  => (int) $post_id,
		'post_content' => snax_kses_post( $description ),
		'post_status'  => ! empty( $status ) ? $status : snax_get_item_init_status( $author_id, $post_id ),
		'post_author'  => (int) $author_id,
		'menu_order'   => 999999,
	);

	// Create new item.
	$item_id = wp_insert_post( $new_post, false );

	if ( 0 === $item_id ) {
		return new WP_Error( 'wp_insert_post_failed', 'Item could not be added' );
	}

	add_post_meta( $item_id, '_snax_item_format', 'text' );
	add_post_meta( $item_id, '_snax_parent_format', $parent_format );
	add_post_meta( $item_id, '_snax_origin', $origin );
	add_post_meta( $item_id, '_snax_ref_link', $ref_link );


	// Increase submissions counter.
	if ( 0 !== $post_id && ! snax_is_item_pending_for_review( $item_id ) ) {
		snax_bump_post_submission_count( $post_id );
	}

	do_action( 'snax_item_added', $item_id, _x( 'text', 'Item type', 'snax' ), $origin );

	return $item_id;
}

/**
 * Remove the item
 *
 * @param int $item_id      Item ID.
 * @param int $user_id      User ID.
 *
 * @return bool|WP_Error
 */
function snax_delete_item( $item_id, $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Check creds.
	if ( ! user_can( $user_id, 'snax_delete_items', $item_id ) ) {
		return new WP_Error( 'user_can_test_failed', sprintf( 'Author %d is not allowed to delete this item.', $user_id ) );
	}

	// Delete permanently, not move it to the trash.
	$force_delete = apply_filters( 'snax_force_delete_item', true, $item_id );

	// Get featured media id.
	$thumbnail_id = get_post_thumbnail_id( $item_id );

	$item_was_published = ! snax_is_item_pending_for_review( $item_id );

	// Delete item, $post represents post state before deletion.
	$post = wp_delete_post( $item_id, $force_delete );

	// On failure, return.
	if ( false === $post ) {
		return new WP_Error( 'wp_delete_post_failed', 'Item could not be deleted.' );
	}

	$snax_meme_template = get_post_meta( $item_id, '_snax_meme_template', true );
	$delete_media = ! empty( $snax_meme_template );
	$delete_media = apply_filters( 'snax_delete_media', $delete_media, $thumbnail_id );

	if ( $delete_media ) {
		$force_delete_media = apply_filters( 'snax_force_delete_media', true, $thumbnail_id );

		// Delete item attachments.
		wp_delete_attachment( $thumbnail_id, $force_delete_media );
	}

	// On success, update counter.
	$post_id = $post->post_parent;

	if ( 0 !== $post_id && $item_was_published ) {
		snax_bump_post_submission_count( $post->post_parent, - 1 );
	}

	return true;
}

/**
 * Set the item as featured
 *
 * @param int $item_id      Item ID.
 *
 * @return bool|WP_Error
 */
function snax_set_item_as_featured( $item_id ) {
	$item_thumbnail_id = (int) get_post_thumbnail_id( $item_id );
	$parent_id = wp_get_post_parent_id( $item_id );

	$res = set_post_thumbnail( $parent_id, $item_thumbnail_id );

	if ( false === $res ) {
		return new WP_Error( 'set_post_thumbnail_failed', 'Item could not be set as featured.' );
	}

	return true;
}

/**
 * Update item
 *
 * @param int|WP_Post $item_id          Optional. Post ID or WP_Post object. Default global $post.
 * @param array       $data             Data to update.
 *
 * @return bool|WP_Error
 */
function snax_update_item( $item_id = 0, $data = array() ) {
	$item = get_post( $item_id );

	$defaults = array(
		'title'       => '',
		'source'      => '',
		'ref_link'    => '',
		'description' => '',
		'order'       => 999999,  // Always at the end of current collection.
	);

	$data = wp_parse_args( $data, $defaults );

	$post_data = array(
		'ID'           => $item->ID,
		'post_title'   => wp_strip_all_tags( $data['title'] ),
		'post_content' => snax_kses_post( $data['description'] ),
		'menu_order'   => (int) $data['order'],
	);

	if ( snax_is_item( $item_id, 'embed' ) ) {
		$embed_url = get_post_meta( $item_id, '_snax_embed_url', true );

		// Prepend url to content if not exists.
		if ( false === strpos( $post_data['post_content'], $embed_url ) ) {
			$post_data['post_content'] = $embed_url . "\n\n" . $post_data['post_content'];
		}
	}

	if ( snax_is_item( $item_id, 'audio' ) || snax_is_item( $item_id, 'video' ) ) {
		$media_id  = get_post_meta( $item_id, '_snax_media_id', true );
		$media_url = wp_get_attachment_url( $media_id );

		// Prepend url to content if not exists.
		if ( false === strpos( $post_data['post_content'], $media_url ) ) {
			$post_data['post_content'] = $media_url . "\n\n" . $post_data['post_content'];
		}
	}

	$updated_post_id = wp_update_post( $post_data );

	// Update post meta.
	update_post_meta( $item->ID, '_snax_source', esc_url_raw( $data['source'] ) );
	update_post_meta( $item->ID, '_snax_ref_link', esc_url_raw( $data['ref_link'] ) );

	do_action( 'snax_item_updated', $item->ID, $data );

	if ( 0 === $updated_post_id ) {
		return new WP_Error( 'snax_update_item_failed', 'Item ' . $item->ID . ' update failed.' );
	}

	return true;
}

/**
 * Check wheter $post is a snax item
 *
 * @param int|WP_Post $post         Optional. Post ID or WP_Post object. Default global $post.
 * @param string      $format       Optional. Item format (image | embed).
 *
 * @return bool
 */
function snax_is_item( $post = null, $format = '' ) {
	$post = get_post( $post );

	$is_post_type_valid = snax_get_item_post_type() === get_post_type( $post );
	$is_format_valid    = true;

	if ( ! empty( $format ) ) {
		$is_format_valid = snax_get_item_format( $post ) === $format;
	}

	return $is_post_type_valid && $is_format_valid;
}

/**
 * Add "x of y" to the item title
 *
 * @param string $title Post title.
 * @param int    $id Post ID.
 *
 * @return string
 */
function snax_item_title( $title, $id = null ) {

	if ( ! snax_is_item( $id ) ) {
		return $title;
	}

	// Prevents empty titles when not on a single snax post page.
	if ( empty( $title ) ) {
		$parent_id = snax_get_item_parent_id( $id );

		if ( ! is_single( $parent_id ) ) {
			if ( apply_filters( 'snax_item_use_parent_title', true ) ) {
				$parent = get_post( $parent_id );

				if ( $parent ) {
					$title = snax_replace_title_placeholder( $parent->post_title, $parent_id );
				}
			} else {
				$title = esc_html__( '(no title)', 'snax' );
			}
		}
	}

	if ( snax_show_item_position_in_title() ) {
		$post_id        = snax_get_item_parent_id( $id );
		$item_position  = snax_get_item_position();
		$all_item_count = snax_get_post_submission_count( $post_id );

		if ( $all_item_count ) {
			$title .= ' ' . sprintf( __( '(%d/%d)', 'snax' ), $item_position, $all_item_count );
		}
	}

	return $title;
}

/**
 * Check wether to show item postion
 *
 * @return bool
 */
function snax_show_item_position_in_title() {
	$show = false;

	if ( is_singular( snax_get_item_post_type() ) && get_post_status() === 'publish' ) {
		if ( in_the_loop() && ! snax_in_custom_loop() ) {
			$show = true;
		}
	}

	return apply_filters( 'snax_show_item_position_in_title', $show );
}

/**
 * Strim embed url from the beginning of embed item content
 *
 * @param string $content       Post content.
 *
 * @return string
 */
function snax_strip_embed_url_from_embed_content( $content ) {
	if ( snax_is_item( null, 'embed' ) || snax_is_item( null, 'audio' ) || snax_is_item( null, 'video' ) ) {
		$content = snax_strip_embed_url_from_content( $content );
	}

	return $content;
}

/**
 * Strim embed url from the beginning of content
 *
 * @param string  $content      Post content.
 * @param WP_Post $post         Options. Post.
 *
 * @return string
 */
function snax_strip_embed_url_from_content( $content, $post = null ) {
	$embed_url = snax_get_item_embed_code( $post );

	// Remove embed url.
	$content = str_replace( $embed_url, '', $content );

	// And all new lines after it.
	$content = preg_replace( '/^[\n\s]+/', '', $content );

	return $content;
}

/**
 * Strim caption from the beginning of post content
 *
 * @param string $content       Post content.
 *
 * @return string
 */
function snax_get_caption_from_content( $content ) {
	$ret = array(
		'content' => $content,
		'text' => false,
	);

	// Find caption shortcode.
	if ( preg_match( '/^\[caption[^\]]+\](<img[^>]+>)([^\[]*)\[\/caption\]/', $content, $matches ) ) {
		$caption_shortcode 	= $matches[0];
		$a_tag 				= $matches[2];

		$content = str_replace( $caption_shortcode, '', $content );

		// And all new lines after it.
		$content = preg_replace( '/^[\s\n]+/', '', $content );

		$ret['content'] = $content;

		$caption_text = trim( strip_tags( $a_tag ) );

		if ( ! empty( $caption_text ) ) {
			$ret['text'] = $caption_text;
		}
	}

	return $ret;
}

function snax_conver_captions_into_froala_images( $content ) {
	if ( preg_match_all( '/\[caption[^\]]+\](<img[^>]+>)([^\[]*)\[\/caption\]/', $content, $all_matches ) ) {
		foreach ( $all_matches[0] as $index => $caption_shortcode ) {
			$img_tag = $all_matches[1][ $index ];
			$a_tag = $all_matches[2][ $index ];

			// Strip all attributes that are not necessary for edition.
			$extra_allowed_html = array(
				'img' => array(
					'src' 		=> true,
					'alt' 		=> true,
				),
			);

			$img_tag = snax_kses_post( $img_tag, $extra_allowed_html );

			$caption_text = '';

			// Strip <a> tag, leave only anchor text.
			if ( $a_tag ) {
				$caption_text = trim( strip_tags( $a_tag ) );
			}

			$media_src = '';

			if ( preg_match( '/src="([^"]*)"/i', $img_tag, $src ) ) {
				$media_src = $src[1];
			}

			$media_src = preg_replace('/\-\d+x\d+/', '', $media_src); // Strip thumb part.
			$media_id = snax_get_image_id( $media_src );

			$img_tag = str_replace( 'src=', 'class="fr-dib" style="width: 300px;" data-snax-id="'. $media_id .'" data-snax-source="'. $caption_text .'" src=', $img_tag );

			$content = str_replace( $caption_shortcode, $img_tag, $content );

			// And all new lines after it.
			$content = preg_replace( '/^[\s\n]+/', '', $content );
		}
	}

	return $content;
}

function snax_conver_urls_into_froala_embeds( $content ) {
	// Replace line breaks from all HTML elements with placeholders.
	$content = wp_replace_in_html_tags( $content, array( "\n" => '<!-- wp-line-break -->' ) );
	$content = wpautop( $content );

	if ( preg_match( '#(^|\s|>)https?://#i', $content ) ) {
		// Find URLs on their own line.
		$content = preg_replace_callback( '|^(\s*)(https?://[^\s<>"]+)(\s*)$|im', 'snax_load_content_embed_tpl_for_url', $content );
		// Find URLs in their own paragraph.
		$content = preg_replace_callback( '|(<p(?: [^>]*)?>\s*)(https?://[^\s<>"]+)(\s*<\/p>)|i', 'snax_load_content_embed_tpl_for_url', $content );
	}

	// Put the line breaks back.
	$content = str_replace( '<!-- wp-line-break -->', "\n", $content );

	return $content;
}

function snax_load_content_embed_tpl_for_url( $match ) {
	$url = $match[2];

	// Fake query, just to allow use of the $wp_embed.
	$query = new WP_Query( array(
		'posts_per_page'    => 1,
	) );

	ob_start();
	?>
	<?php
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			global $wp_embed;

			$shortcode_out = $wp_embed->run_shortcode( '[embed]' . $url . '[/embed]' );

			if ( empty( $shortcode_out ) || preg_match("/<a/", $shortcode_out)) {
				$key_suffix = md5( $url );
				$transient = '_oembed_' . $key_suffix;
				$cached_value = get_transient( $transient );
				if ( ! empty( $cached_value ) ) {
					$shortcode_out = $cached_value;
				}
			}

			echo filter_var( $shortcode_out );
		}

		wp_reset_postdata();
	}

	$parsed_shortcode = ob_get_clean();

	$is_embed = ( false === strpos( $shortcode_out, $url ) );

	if ( ! $is_embed ) {
		return $match[0];
	}

	$out = '<span class="snax-embed-layer">
				<span class="snax-embed-url">' . esc_url( $url ) . '</span>' .
				$parsed_shortcode .
		'</span>';

	return $out;
}

function snax_get_image_id( $image_url ) {
	global $wpdb;

	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );

	return $attachment[0];
}

/**
 * Append item HTML to post content
 *
 * @param string $content           Post content.
 *
 * @return string
 */
function snax_item_content( $content ) {
	if ( snax_is_item() ) {

		ob_start();
		snax_get_template_part( 'content', 'single-item' );
		$out = ob_get_clean();

		$out = str_replace( '%%SNAX_ITEM_DESCRIPTION%%', $content, $out );

		// Append to content.
		$content = $out;
	}

	return $content;
}

/**
 * Render item scripts
 */
function snax_item_scripts() {
	?>
	<script type="text/javascript">
		(function() {
			if ( typeof window.snax === 'undefined' ) {
				window.snax = {};
			}

			var ctx = window.snax;

			ctx.currentUserId = <?php echo intval( get_current_user_id() ); ?>;
		})();
	</script>
	<?php
}

/**
 * Get the id of the parent post of an item
 *
 * @param int|WP_Post $post Item.
 *
 * @return int
 */
function snax_get_item_parent_id( $post = null ) {
	$post = get_post( $post );

	$id = wp_get_post_parent_id( $post->ID );

	return $id;
}

/**
 * Get the id of the previous item
 *
 * @param int|WP_Post $post Item.
 *
 * @return int
 */
function snax_get_previous_item_id( $post = null ) {
	$post = get_post( $post );

	$post_id   = snax_get_item_parent_id( $post->ID );
	$items_ids = snax_get_items_ids( $post_id );

	$prev_id = false;

	do {
		if ( current( $items_ids ) === $post->ID ) {
			$prev_id = prev( $items_ids );
			break;
		}
	} while ( next( $items_ids ) );

	return $prev_id;
}

/**
 * Get the id of the next item
 *
 * @param int|WP_Post $post Item.
 *
 * @return int
 */
function snax_get_next_item_id( $post = null ) {
	$post = get_post( $post );

	$post_id   = snax_get_item_parent_id( $post->ID );
	$items_ids = snax_get_items_ids( $post_id );

	$next_id = false;

	do {
		if ( current( $items_ids ) === $post->ID ) {
			$next_id = next( $items_ids );
			break;
		}
	} while ( next( $items_ids ) );

	return $next_id;
}

/**
 * Return url of a post containg the `$item`, taking into account the fact that post can be paged
 *
 * @param int|WP_Post $item Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param bool        $hash Whether to add #ITEM_ID to returned url.
 *
 * @return string
 */
function snax_get_item_paged_url( $item = null, $hash = false ) {
	$item    = get_post( $item );
	$post_id = $item->post_parent;

	$items_per_page = snax_get_items_per_page();
	$item_position  = snax_get_item_position( $item->ID );
	$item_page      = floor( $item_position / $items_per_page ) + 1;

	$url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( $item_page, 'single_paged' );

	if ( $hash ) {
		$url .= '#snax-item-' . $item->ID;
	}

	return $url;
}

/**
 * Return item postion number inside post
 *
 * @param int|WP_Post $item             Optional. Item ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_item_position( $item = null ) {
	$item    = get_post( $item );
	$post_id = $item->post_parent;

	// Shouldn't happen.
	$position = - 1;

	$found_index = array_search( $item->ID, snax_get_items_ids( $post_id ) );

	if ( false !== $found_index ) {
		$position = $found_index + 1; // Array keys start from 0.
	}

	return $position;
}

/**
 * Return item format
 *
 * @param int|WP_Post $item          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return mixed
 */
function snax_get_item_format( $item = null ) {
	$item = get_post( $item );

	if ( ! $item ) {
		return false;
	}

	$format = get_post_meta( $item->ID, '_snax_item_format', true );

	return $format;
}

/**
 * Return item embed code
 *
 * @param int|WP_Post $item         Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return mixed                    Embed code or false if not set.
 */
function snax_get_item_embed_code( $item = null ) {
	$item = get_post( $item );

	$embed_code = snax_get_first_url_in_content( $item );

	return $embed_code;
}

/**
 * Return item embed code CSS classes
 *
 * @param int|WP_Post $item         Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return array
 */
function snax_get_item_embed_code_classes( $item = null ) {
	$item = get_post( $item );

	$embed_code = snax_get_item_embed_code( $item );
	$embed_provider_name = get_post_meta( $item->ID, '_snax_embed_provider_name', true );

	$snax_embed_classes = array(
		'snax-item-embed-code',
		'snax-item-embed-' . $embed_provider_name,
	);

	return apply_filters( 'snax_item_embed_code_classes', $snax_embed_classes, $embed_code );
}

/**
 * Check whether item has set the Source field value
 *
 * @param int|WP_Post $item          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return bool
 */
function snax_item_has_source( $item = null ) {
	$source = snax_get_item_source( $item );

	return ! empty( $source );
}

/**
 * Return item source value
 *
 * @param int|WP_Post $item          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return string
 */
function snax_get_item_source( $item = null ) {
	$item = get_post( $item );

	return (string) get_post_meta( $item->ID, '_snax_source', true );
}

/**
 * Check whether item has set the Referral Link field value
 *
 * @param int|WP_Post $item          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return bool
 */
function snax_item_has_ref_link( $item = null ) {
	$ref_link = snax_get_item_ref_link( $item );

	return ! empty( $ref_link );
}

/**
 * Return item referral link value
 *
 * @param int|WP_Post $item          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return string
 */
function snax_get_item_ref_link( $item = null ) {
	$item = get_post( $item );

	return (string) get_post_meta( $item->ID, '_snax_ref_link', true );
}

/**
 * Return item permalink data
 *
 * @param WP_Post $item         Optional. Post id or object.
 *
 * @return bool|array           Data array of false if has no link.
 */
function snax_get_item_permalink( $item = null ) {
	$item       = get_post( $item );
	$parent_id  = snax_get_item_parent_id( $item );
	$permalink  = false;

	// Open list items should link to single pages.
	if ( snax_is_post_open_list( $parent_id ) ) {
		$permalink['url'] = get_permalink( $item );
	}

	// If item has referral link, override default permalink with it.
	if ( snax_item_has_ref_link() ) {
		$permalink['url'] = snax_get_item_ref_link( $item );
		$permalink['target'] = '_blank';
	}

	return $permalink;
}

/**
 * Return all items assigned to post
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default global $post.
 * @param array       $args             Extra WP_Query arguments.
 *
 * @return array
 */
function snax_get_items( $post_id = 0, $args = array() ) {
	$post = get_post( $post_id );

	$args = wp_parse_args( $args, array(
		'post_parent' => $post->ID,
	) );

	$query_args = snax_get_items_query_args( $args );

	$items = get_posts( $query_args );

	return $items;
}

/**
 * Return ids of all items assigned to post
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default global $post.
 *
 * @return array
 */
function snax_get_items_ids( $post_id = null ) {
	$items = snax_get_items( $post_id );

	$ids = wp_list_pluck( $items, 'ID' );

	return $ids;
}

/**
 * Check whether user has approved items
 *
 * @param string $parent_format Parent format.
 * @param int    $user_id User id.
 *
 * @return bool
 */
function snax_has_user_approved_items( $parent_format, $user_id = 0 ) {
	$has = snax_has_user_items( $parent_format, snax_get_item_approved_status(), $user_id );

	return apply_filters( 'snax_has_user_approved_items', $has, $parent_format, $user_id );
}

/**
 * Check whether user has pending items
 *
 * @param string $parent_format Parent format.
 * @param int    $user_id User id.
 *
 * @return bool
 */
function snax_has_user_pending_items( $parent_format, $user_id = 0 ) {
	$has = snax_has_user_items( $parent_format, snax_get_item_pending_status(), $user_id );

	return apply_filters( 'snax_has_user_pending_items', $has, $parent_format, $user_id );
}

/**
 * Check whether user has items
 *
 * @param string $parent_format             Format of parent post.
 * @param int    $status                    Post status.
 * @param int    $user_id                   User id.
 *
 * @return bool
 */
function snax_has_user_items( $parent_format, $status, $user_id = 0 ) {
	$user_id = (int) $user_id;

	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$query = snax_get_items_query( $parent_format, 'contribution', array(
		'author'      => $user_id,
		'post_status' => $status,
	) );

	return apply_filters( 'snax_has_user_items', $query->have_posts(), $user_id );
}

/**
 * Check whether post has items
 *
 * @param int    $post_id           Post id.
 * @param string $parent_format     Parent format items belong to.
 * @param array  $args              WP Query args.
 *
 * @return bool
 */
function snax_has_items( $post_id = 0, $parent_format = 'list', $args = array() ) {
	$post = get_post( $post_id );

	if ( ! snax_is_format( $parent_format, $post ) ) {
		return false;
	}

	$args = wp_parse_args( $args, array(
		'post_parent' => $post->ID,
	) );

	$query = snax_get_items_query( $parent_format, 'all', $args );

	return apply_filters( 'snax_has_items', $query->have_posts(), $post->ID, $args );
}

/**
 * Check whether gallery has items
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global $post.
 * @param array       $args                 Extra WP_Query arguments.
 *
 * @return bool
 */
function snax_has_gallery_items( $post_id = 0, $args = array() ) {
	$post = get_post( $post_id );

	if ( ! snax_is_format( 'gallery', $post ) ) {
		return false;
	}

	$args = wp_parse_args( $args, array(
		'post_parent' => $post->ID,
	) );

	$query = snax_get_items_query( 'gallery', 'all', $args );

	return apply_filters( 'snax_has_gallery_items', $query->have_posts(), $post->ID, $args );
}

/**
 * Set up items query
 *
 * @param string $parent_format         Format of item parent (image | embed | gallery | list).
 * @param string $origin                Origin type ( all | contribution | post ).
 * @param array  $args                  WP Query extra args.
 *
 * @return WP_Query
 */
function snax_get_items_query( $parent_format, $origin = 'all', $args = array() ) {
	global $wp_rewrite;

	$default_args = array(
		'posts_per_page' => snax_get_items_per_page(),
		'paged'          => snax_get_paged(),
		'max_num_pages'  => false,
		'meta_query'     => array(
			array(
				'key'     => '_snax_parent_format',
				'value'   => $parent_format,
				'compare' => '=',
			),
		),
	);

	// Restrict to origin.
	if ( 'all' !== $origin ) {
		$default_args['meta_query']['relation'] = 'AND';
		$default_args['meta_query'][]           = array(
			'key'     => '_snax_origin',
			'value'   => $origin,
			'compare' => '=',
		);
	}

	// Posts query args.
	$r = snax_get_items_query_args( $default_args );

	// We get author items, not items assigned to a particular post.
	unset( $r['post_parent'] );

	$r = wp_parse_args( $args, $r );

	// Make query.
	$query = new WP_Query( $r );

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

		$base = apply_filters( 'snax_items_pagination_base', $base, $r );

		// Pagination settings with filter.
		$pagination = apply_filters( 'snax_items_pagination', array(
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

	snax()->items_query = $query;

	return $query;
}

/**
 * Returns default items query args
 *
 * @param array $args Optional.
 *
 * @return array
 */
function snax_get_items_query_args( $args = array() ) {
	$defaults = array(
		'post_type'      => snax_get_item_post_type(),
		'post_status' => array( 'publish', 'pending', 'draft' ),    // Items have the same status as parent.
		'post_parent'    => 0,
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'menu_order'     => 'ASC',
			'post_date'      => 'ASC',
		),
		'meta_key'       => '_snax_vote_score',
		'posts_per_page' => - 1,
	);

	$args = wp_parse_args( $args, $defaults );

	return apply_filters( 'snax_item_query_args', $args );
}

/**
 * Update post on item change
 *
 * @param int     $post_id          Post ID.
 * @param WP_Post $post             Post object.
 */
function snax_update_post_on_item_change( $post_id, $post ) {
	// When item assigned to a post is updated.
	if ( snax_get_item_post_type() === $post->post_type && 0 !== $post->post_parent ) {
		update_post_meta( $post->post_parent, '_snax_post_modified_date', $post->post_date );
	}
}

/**
 * Return url of item author profiel pages
 *
 * @return string
 */
function snax_get_item_author_url() {
	$author_id = get_the_author_meta( 'ID' );
	$url       = get_author_posts_url( $author_id );

	return apply_filters( 'snax_get_item_author_url', $url, $author_id );
}

/**
 * Check whether item was submitted
 *
 * @return bool
 */
function snax_item_submitted() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$post = get_post();

	if ( null === $post ) {
		return false;
	}

	$post_author_id  = (int) $post->post_author;
	$current_user_id = (int) get_current_user_id();

	if ( $current_user_id !== $post_author_id ) {
		return false;
	}

	$url_var = snax_get_url_var( 'item_submission' );
	$submission = filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );

	return ! empty( $submission );
}

/**
 * Check whether item is in review queue
 *
 * @param int|WP_Post $item_id              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return bool
 */
function snax_is_item_pending_for_review( $item_id = 0 ) {
	$item = get_post( $item_id );

	return snax_get_item_pending_status() === $item->post_status;
}

/**
 * Check whether to show item position
 *
 * @return bool
 */
function snax_show_item_position() {
	$bool = true;

	$parent_id = snax_get_item_parent_id();

	$format = snax_get_format( $parent_id );

	if ( ! is_single( $parent_id ) ) {
		$bool = false;
	}

	if ( is_single( $parent_id ) && in_array( $format, array( 'image', 'embed', 'gallery', 'story' ), true ) ) {
		$bool = false;
	}

	return (bool) apply_filters( 'snax_show_item_position', $bool );
}

/**
 * Check whether to show item parent
 *
 * @return bool
 */
function snax_show_item_parent() {
	$post_id = snax_get_post_id();

	$bool      = false;
	$parent_id = snax_get_item_parent_id( $post_id );

	if ( ! is_single( $parent_id ) ) {
		$bool = true;
	}

	return (bool) apply_filters( 'snax_show_item_parent', $bool );
}

/**
 * Check whether to show item voting box
 *
 * @param int|WP_Post $post              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return bool
 */
function snax_show_item_voting_box( $post = null ) {
	$post 				= get_post( $post );
	$post_type 			= get_post_type( $post );
	$allowed_post_types	= snax_voting_get_post_types();

	$bool = snax_voting_is_enabled() && in_array( $post_type, $allowed_post_types, true );

	// Snax item?
	if ( snax_is_item( $post ) ) {
		$parent_id = snax_get_item_parent_id( $post );

		// List items can't be voted.
		if ( ! snax_is_post_ranked_list( $parent_id ) ) {
			$bool = false;
		}

		// List items can't be voted anymore.
		if ( ! snax_is_post_open_for_voting( $parent_id ) ) {
			$bool = false;
		}
	}

	return (bool) apply_filters( 'snax_show_item_voting_box', $bool, $post );
}

/**
 * Check whether to show item comments box
 *
 * @param int|WP_Post $post              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return bool
 */
function snax_show_item_comments_box( $post = null ) {
	$bool = false;
	if (snax_display_comments_on_lists()){
		$post = get_post( $post );

		$bool = snax_is_item( $post );
		$bool = true;
	}
	return (bool) apply_filters( 'snax_show_item_comments_box', $bool, $post );
}

/**
 * Check whether to show item upvote link
 *
 * @param int|WP_Post $post              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return bool
 */
function snax_show_item_upvote_link( $post = null ) {
	$post = get_post( $post );
	$bool = true;

	return (bool) apply_filters( 'snax_show_item_upvote_link', $bool, $post );
}

/**
 * Check whether to show item downvote link
 *
 * @param int|WP_Post $post              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return bool
 */
function snax_show_item_downvote_link( $post = null ) {
	$post = get_post( $post );
	$bool = true;

	return (bool) apply_filters( 'snax_show_item_downvote_link', $bool, $post );
}

/**
 * Check whether to show item sharing buttons
 *
 * @return bool
 */
function snax_show_item_share() {
	$bool = true;
	if ( snax_is_item( null, 'text' ) ) {
		$bool = false;
	}

	return (bool) apply_filters( 'snax_show_item_share', $bool );
}


/**
 * Check whether to show item author
 *
 * @return bool
 */
function snax_show_item_author() {
	$bool = false;

	$post = get_post();

	$parent_id = snax_get_item_parent_id( $post );

	if ( snax_is_post_open_list( $parent_id) ) {
		$bool = true;
	}

	return (bool) apply_filters( 'snax_show_item_author', $bool, $post );
}

/**
 * Check whether to show item date
 *
 * @return bool
 */
function snax_show_item_date() {
	$bool = false;

	$post = get_post();

	$parent_id = snax_get_item_parent_id( $post );

	if ( snax_is_post_open_list( $parent_id) ) {
		$bool = true;
	}

	return (bool) apply_filters( 'snax_show_item_date', $bool, $post );
}

/**
 * Render item title
 */
function snax_render_item_title() {
	snax_get_template_part( 'items/title' );
}


/**
 * Render item parent
 */
function snax_render_item_parent() {
	if ( snax_show_item_parent() ) {
		?>
		<p class="snax-item-parent">
			<?php
			$parent_id = snax_get_item_parent_id();

			printf(
				wp_kses_post( __( 'Submitted to: <a href="%s">%s</a>', 'snax' ) ),
				esc_url( get_the_permalink( $parent_id ) ),
				esc_html( get_the_title( $parent_id ) )
			);
			?>
		</p>
		<?php
	}
}

/**
 * Render item position
 *
 * @param array $args               Config.
 */
function snax_render_item_position( $args = array() ) {
	if ( snax_show_item_position() ) {
		echo wp_kses_post( snax_capture_item_position( $args ) );
	}
}

/**
 * Capture item position
 *
 * @param array $args       Config.
 *
 * @return string
 */
function snax_capture_item_position( $args = array() ) {
	$out = '';

	$defaults = array(
		'prefix' => '',
		'suffix' => '. ',
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'snax_capture_item_position_args', $args );

	if ( snax_show_item_position() ) {
		$out .= '<span class="snax-item-position">' . $args['prefix'] . intval( snax_get_item_position() ) . $args['suffix'] . '</span>';
	}

	return $out;
}

/**
 * Check whether to show item media description
 *
 * @return bool
 */
function snax_show_item_media_description() {
	$bool = false;
	if ( snax_has_item_description() ) {
		$bool = true;
	}

	return (bool) apply_filters( 'snax_show_item_media_description', $bool );
}

/**
 * Return format of item parent
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global $post.
 *
 * @return mixed|void
 */
function snax_get_item_parent_format( $post_id = 0 ) {
	$post = get_post( $post_id );

	$format = get_post_meta( $post->ID, '_snax_parent_format', true );

	return apply_filters( 'snax_get_item_parent_format', $format, $post );
}

/**
 * Listen on item status change
 *
 * @param string  $new_status           New status.
 * @param string  $old_status           Old status.
 * @param WP_Post $post                 Affected post.
 */
function snax_item_status_changed( $new_status, $old_status, $post ) {
	if ( ! snax_is_item( $post ) || 'new' === $old_status ) {
		return;
	}

	$approved_status = snax_get_item_approved_status();
	$pending_status = snax_get_item_pending_status();

	if ( $approved_status !== $old_status && $approved_status === $new_status ) {
		snax_item_approved( $post );
	}
	if ( ( $approved_status === $old_status || $pending_status === $old_status ) && 'trash' === $new_status ) {
		snax_item_rejected( $post );
	}
}

/**
 * Fires when post is approved.
 *
 * @param WP_Post $post		Post object.
 */
function snax_item_approved( $post ) {
	// Store info about action.
	update_post_meta( $post->ID, 'snax_approved_by', get_current_user_id() );
	update_post_meta( $post->ID, 'snax_approval_data', current_time( 'mysql' ) );

	do_action( 'snax_item_approved', $post );
}

/**
 * Fires when post is rejected.
 *
 * @param WP_Post $post		Post object.
 */
function snax_item_rejected( $post ) {
	// Store info about action.
	update_post_meta( $post->ID, 'snax_rejected_by', get_current_user_id() );
	update_post_meta( $post->ID, 'snax_rejection_data', current_time( 'mysql' ) );

	do_action( 'snax_item_rejected', $post );
}

/**
 * Sanitize item title
 *
 * @param string $val           Input value.
 *
 * @return string
 */
function snax_sanitize_item_title( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, snax_get_item_title_max_length() );

	return $val;
}

/**
 * Sanitize item content
 *
 * @param string $val           Input value.
 *
 * @return string
 */
function snax_sanitize_item_content( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, snax_get_item_content_max_length() );

	return $val;
}

/**
 * Sanitize item source
 *
 * @param string $val           Input value.
 *
 * @return string
 */
function snax_sanitize_item_source( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, snax_get_item_source_max_length() );

	return $val;
}

/**
 * Sanitize item referral link
 *
 * @param string $val           Input value.
 *
 * @return string
 */
function snax_sanitize_item_ref_link( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, snax_get_item_ref_link_max_length() );

	return $val;
}

/**
 * Sanitize item origin value.
 *
 * @param string $value         Input value.
 *
 * @return string
 */
function snax_sanitize_item_origin_value( $value ) {
	// Origins:
	// post         - when item is created as a part of an entire post (via Frontend Submission page).
	// contribution - when item is created as a contribution to already existing post.
	if ( ! in_array( $value, array( 'post', 'contribution' ), true ) ) {
		return '';
	}

	return $value;
}

/**
 * Render referral link
 */
function snax_render_item_referral_link() {
	if ( snax_is_item() || snax_is_format() ) {
		echo snax_capture_item_referral_link();
	}
}

/**
 * Return referral link for an item
 *
 * @param int $post_id      Optional. Post id.
 *
 * @return string
 */
function snax_capture_item_referral_link( $post_id = null ) {
	$link = '';
	$post = get_post( $post_id );

	$ref_link = get_post_meta( $post->ID, '_snax_ref_link', true );

	if ( ! empty( $ref_link ) ) {
		$link = sprintf(
			'<form class="snax-item-referral-form" action="%s" method="get" onclick="window.open(this.action); return false;"><button type="submit">%s</button></form>',
			esc_url_raw( $ref_link ),
			esc_html__( 'Buy now', 'snax' )
		);
	}

	return apply_filters( 'snax_item_referral_link_html', $link, $post->ID );
}

/**
 * Print embed form hidden fields
 */
function snax_render_embed_form_internals() {
	?>
	<input
		type="hidden"
		name="snax-add-embed-item-nonce"
		value="<?php echo esc_attr( wp_create_nonce( 'snax-add-embed-item' ) ); ?>"
		/>
	<?php
}

/**
 * Print embed form hidden fields
 */
function snax_render_text_form_internals() {
	?>
	<input
		type="hidden"
		name="snax-add-text-item-nonce"
		value="<?php echo esc_attr( wp_create_nonce( 'snax-add-text-item' ) ); ?>"
		/>
	<?php
}

/**
 * Set canonical url to parent for items
 *
 * @param str     $url		canonical url.
 * @param Wp_Post $post		post object.
 * @return str
 */
function snax_item_canonical_url( $url, $post ) {
	if ( snax_is_item( $post ) ) {
		$url = get_permalink( $post->post_parent );
	}
	return $url;
}

/**
 * Hide New Item submenu entry in Snax Items dashboard
 *
 * @return void
 */
function disable_new_item_for_snax_items() {
	global $submenu;
	unset( $submenu['edit.php?post_type=snax_item'][10] );
}

/**
 * Return item comment when posted via AJAX
 *
 * @param string     $location The 'redirect_to' URI sent via $_POST.
 * @param WP_Comment $comment  Comment object.
 * @return null
 */
function snax_return_item_comment_via_ajax( $location, $comment ) {
	$ajax_item_form 	= (bool) filter_input( INPUT_POST, 'is_ajax_item_comment_form');
	if ( $ajax_item_form ) {
		$args = array(
			'comment__in' 	=> $comment->comment_ID,
		);
		$comments = get_comments( $args );

		$response = wp_list_comments( array(
			'type'     			=> 'comment',
			'echo'		=> false,
		), $comments );
		echo $response;
		exit;
	}
	return $location;
}

/**
 * Reload meta to refresh the item count
 *
 * @param int $post_id  Post id.
 */
function snax_reload_meta_when_item_saved( $post_id ) {
	if ( snax_is_item( $post_id ) && 'publish' !== get_post_status( $post_id ) ) {
		$parent = wp_get_post_parent_id( $post_id );
		if ( $parent ) {
			snax_post_reload_meta( $parent );
		}
	}
}
