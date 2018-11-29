<?php
/**
 * New poll type selection template part
 *
 * @package snax 1.11
 * @subpackage Forms
 *
 * @todo This belongs to admin. It shouldn't be inside the "templates" directory.
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="wrap">
	<?php
	$snax_object = get_post_type_object( snax_get_poll_post_type() );
	?>
	<h1 class="wp-heading-inline"><?php echo esc_html( $snax_object->labels->add_new_item ); ?></h1>
	<hr class="wp-header-end">

	<style>
		.quizzard-new-polls {
			margin: 0 -10px;
			display: flex;
			flex-wrap: wrap;
			align-items: stretch;
		}

		.quizzard-new-poll {
			box-sizing: border-box;

			max-width: 420px;
			padding: 30px;
			margin: 20px 10px;

			flex: 1 1 auto;

			text-align: center;
			text-decoration: none;

			background-color: #fff;
		}

		.quizzard-new-poll-icon:before {
			font-size: 68px;
			line-height: 1;
			font-family: "snaxicon";
		}

		.quizzard-new-poll-icon {
			display: inline-block;
			width: 68px;
			height: 68px;

			font-style: normal;
		}

		.quizzard-new-poll-title {
			color: inherit;
		}

		.quizzard-new-poll-desc {
			color: #72777c;
		}

		.quizzard-new-poll-classic .quizzard-new-poll-icon:before        { content: "\e01b"; }
		.quizzard-new-poll-versus .quizzard-new-poll-icon:before        { content: "\e034"; }
		.quizzard-new-poll-binary .quizzard-new-poll-icon:before        { content: "\e033"; }
	</style>

	<div class="quizzard-new-polls">
		<a class="quizzard-new-poll quizzard-new-poll-classic" href="<?php echo esc_url( snax_get_new_classic_poll_page_url() ); ?>">
			<i class="quizzard-new-poll-icon"></i>
			<h2 class="quizzard-new-poll-title"><?php esc_html_e( 'Classic Poll', 'snax' ); ?></h2>
			<p class="quizzard-new-poll-desc"></p>
		</a>
		<a class="quizzard-new-poll quizzard-new-poll-versus" href="<?php echo esc_url( snax_get_new_versus_poll_page_url() ); ?>">
			<i class="quizzard-new-poll-icon"></i>
			<h2 class="quizzard-new-poll-title"><?php esc_html_e( 'Versus', 'snax' ); ?></h2>
			<p class="quizzard-new-poll-desc"></p>
		</a>
		<a class="quizzard-new-poll quizzard-new-poll-binary" href="<?php echo esc_url( snax_get_new_binary_poll_page_url() ); ?>">
			<i class="quizzard-new-poll-icon"></i>
			<h2 class="quizzard-new-poll-title"><?php esc_html_e( 'Binary', 'snax' ); ?></h2>
			<p class="quizzard-new-poll-desc"><?php esc_html_e( 'Hot or Not, Funny or Die', 'snax' ); ?></p>
		</a>
	</div>

</div>


