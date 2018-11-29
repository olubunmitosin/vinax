<?php
/**
 * Twitter share link
 *
 * @package snax 1.11
 * @subpackage Share
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

global $snax_share_args;

$snax_twitter_url = sprintf(
	'//twitter.com/share?url=%s&amp;text=%s',
	$snax_share_args['url'],
	urlencode( $snax_share_args['title'] . '. ' . $snax_share_args['description'] )
);
?>

<a class="quizzard-share quizzard-share-twitter" href="<?php echo esc_url( $snax_twitter_url ); ?>" target="_blank">
	<?php esc_html_e( 'Share on Twitter', 'snax' ); ?>
</a>

<script>
	(function($) {
		$('.quizzard-share-twitter').click(function(e) {
			e.preventDefault();
			var width  = 600, height = 400, left = ($(window).width()  - width)  / 2, top = ($(window).height() - height) / 2;
			window.open(this.href, 'twitter', 'status=1' + ',width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
		});
	})(jQuery);
</script>