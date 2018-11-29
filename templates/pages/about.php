<?php
/**
 * About page
 *
 * @package snax 1.11
 * @subpackage Plugin
 */

?>
<div class="wrap about-wrap">

	<h1><?php esc_html_e( 'Welcome to Snax', 'snax' ); ?></h1>

	<div class="about-text">
		<?php esc_html_e( 'The first front-end uploader with open lists.', 'snax' ); ?>
	</div>

	<div id="welcome-panel" class="welcome-panel">
		<div class="welcome-panel-content">
			<h3><?php esc_html_e( 'Getting Started with Snax', 'snax' ); ?></h3>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<a class="button button-primary button-hero" href="<?php echo esc_url( snax_get_frontend_submission_page_url() ); ?>"><?php esc_html_e( 'Create first post', 'snax' ); ?></a>
				</div>
				<div class="welcome-panel-column">
					<h4><?php esc_html_e( 'Administration area', 'snax' ); ?></h4>

					<ul>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">' . esc_html( 'Customize Settings', 'snax' ) . '</a>', esc_url( snax_admin_url( add_query_arg( array( 'page' => 'snax-general-settings' ), 'admin.php' ) ) ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">' . esc_html( 'Configure Lists', 'snax' ) . '</a>', esc_url( snax_admin_url( add_query_arg( array( 'page' => 'snax-lists-settings' ), 'admin.php' ) ) ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">' . esc_html( 'Manage Pages', 'snax' ) . '</a>', esc_url( snax_admin_url( add_query_arg( array( 'page' => 'snax-pages-settings' ), 'admin.php' ) ) ) ); ?></li>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h4><?php esc_html_e( 'Support', 'snax' ); ?></h4>

					<p class="welcome-icon welcome-learn-more"><?php echo wp_kses_post( 'Looking for help? Check first Snax <a href="http://docs.snax.bringthepixel.com" target="_blank">documentation</a>.', 'snax' ) ?></p>
					<p class="welcome-icon welcome-learn-more"><?php echo wp_kses_post( 'Can&#8217;t find what you need? Stop by <a href="http://support.bringthepixel.com">our support forums</a>.', 'snax' ) ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
