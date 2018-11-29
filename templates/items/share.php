<?php
/**
 * Template for displaying single item share
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<?php
$thumb_url 		= '';
$parent_id 		= snax_get_item_parent_id();
$share_url 		= get_permalink();
$title 			= get_the_title();
$parent_title 	= get_the_title( $parent_id );
$shortlink 		= wp_get_shortlink( $parent_id );
$fb_app_id		= snax_get_facebook_app_id();
$item_id 		= uniqid();
if ( snax_is_item( null, 'image' ) ) {
	$thumb_url = get_the_post_thumbnail_url();
}
?>
<div class="snax-item-share"
	data-snax-share-title="<?php echo esc_attr( $title ); ?>"
	data-snax-share-url="<?php echo esc_url( $share_url ); ?>"
	data-snax-share-thumb="<?php echo esc_url( $thumb_url ); ?>">
	<a class="snax-item-share-toggle" href="#"><?php esc_html_e( 'Share', 'snax' ); ?></a>
	<div class="snax-item-share-content">
		<?php
		$pinterest_url = 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( $shortlink ) . '&amp;description=' . rawurlencode( $title ) . '&amp;media=' . rawurlencode( $thumb_url );
		printf(
			'<a class="snax-share %1s" href="%2s" title="%3s" target="_blank" rel="nofollow">%4s</a>',
			sanitize_html_class( 'snax-share-pinterest' ),
			esc_url( $pinterest_url ),
			esc_attr( __( 'Share on Pinterest', 'snax' ) ),
			esc_html( __( 'Share on Pinterest', 'snax' ) )
		);?>
		<script type="text/javascript">
			(function () {
				var triggerOnLoad = false;

				window.apiShareOnFB = function() {
					jQuery('body').trigger('snaxFbNotLoaded');
					triggerOnLoad = true;
				};

				var _fbAsyncInit = window.fbAsyncInit;

				window.fbAsyncInit = function() {
					FB.init({
						appId      : '<?php echo esc_attr( $fb_app_id ); ?>',
						xfbml      : true,
						version    : 'v3.0'
					});

					window.apiShareOnFB<?php echo esc_html( $item_id ); ?> = function() {
						var shareObj        	= jQuery('.snax-share-object').data('quizzardShareObject');
						// double quotes are required so that apostrophes don't mess this up.
						var shareTitle 		    = "<?php echo sanitize_text_field( $parent_title ); ?>";
						var shareDescription	= "<?php echo sanitize_text_field( $title ); ?>";
						var shareImage	        = '<?php echo esc_html( $thumb_url ); ?>';

						FB.login(function(response) {
							if (response.status === 'connected') {
								var objectToShare = {
									'og:url':           '<?php echo esc_url( $share_url ); ?>', // Url to share.
									'og:title':         shareTitle,
									'og:description':   shareDescription
								};

								// Add image only if set. FB fails otherwise.
								if (shareImage) {
									objectToShare['og:image'] = shareImage;
								}

								FB.ui({
										method: 'share_open_graph',
										action_type: 'og.shares',
										action_properties: JSON.stringify({
											object : objectToShare
										})
									},
									// callback
									function(response) {
									});
							}
						});
					};

					// Fire original callback.
					if (typeof _fbAsyncInit === 'function') {
						_fbAsyncInit();
					}

					// Open share popup as soon as possible, after loading FB SDK.
					if (triggerOnLoad) {
						setTimeout(function() {
							apiShareOnFB();
						}, 1000);
					}
				};

				// JS SDK loaded before we hook into it. Trigger callback now.
				if (typeof window.FB !== 'undefined') {
					window.fbAsyncInit();
				}
			})();
		</script>
		<?php $fb_onclick = 'apiShareOnFB' . esc_html( $item_id );
		printf(
			'<a class="snax-share %1s" href="%2s" title="%3s" onclick="%4s(); return false;" target="_blank" rel="nofollow">%5s</a>',
			sanitize_html_class( 'snax-share-facebook' ),
			esc_url( '#' ),
			esc_attr( __( 'Share on Facebook', 'snax' ) ),
			esc_attr( $fb_onclick ),
			esc_html( __( 'Share on Facebook', 'snax' ) )
		);
		$twitter_url = 'https://twitter.com/home?status=' . rawurlencode( $title ) . '%20' . rawurlencode( $shortlink );
		printf(
			'<a class="snax-share %1s" href="%2s" title="%3s" target="_blank" rel="nofollow">%4s</a>',
			sanitize_html_class( 'snax-share-twitter' ),
			esc_url( $twitter_url ),
			esc_attr( __( 'Share on Twitter', 'snax' ) ),
			esc_html( __( 'Share on Twitter', 'snax' ) )
		);
		?>
	</div>
</div>
