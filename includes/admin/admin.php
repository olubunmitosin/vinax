<?php
/**
 * Main Snax Admin Class
 *
 * @package snax
 * @subpackage admin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

if ( ! class_exists( 'Snax_Admin' ) ) :

	/**
	 * Snax Main Admin class
	 */
	final class Snax_Admin {

		/**
		 * Snax Admin instance
		 *
		 * @var Snax_Admin
		 */
		private static $instance;

		/**
		 * Admin version
		 *
		 * @var string
		 */
		public $version;

		/**
		 * Admin dir path
		 *
		 * @var string
		 */
		public $admin_dir;

		/**
		 * Admin dir url
		 *
		 * @var string
		 */
		public $admin_url;

		/**
		 * Admin assets dir path
		 *
		 * @var string
		 */
		public $assets_dir;

		/**
		 * Admin assets dir url
		 *
		 * @var string
		 */
		public $assets_url;

		/**
		 * Admin capability
		 *
		 * @var string
		 */
		public $capability;

		/**
		 * Admin settings page
		 *
		 * @var string
		 */
		public $settings_page;

		/**
		 * Return the only instance of admin class
		 *
		 * @return Snax_Admin
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new Snax_Admin();
			}

			return self::$instance;
		}

		/**
		 * Private constructor to prevent creating a new instance
		 * via the 'new' operator from outside of this class.
		 */
		private function __construct() {
			$this->setup_globals();
			$this->includes();
			$this->setup_hooks();
		}

		/**
		 * Private clone method to prevent cloning of the instance
		 */
		private function __clone() {
		}

		/**
		 * Plugin variables
		 */
		private function setup_globals() {

			/** Versions ********************************************************* */

			$this->version = '1.0';

			/** Paths ************************************************************ */

			$admin_dir = apply_filters( 'snax_plugin_admin_dir_path', plugin_dir_path( __FILE__ ) );
			$admin_url = apply_filters( 'snax_plugin_admin_dir_url', plugin_dir_url( __FILE__ ) );

			// Base.
			$this->admin_dir = trailingslashit( $admin_dir );
			$this->admin_url = trailingslashit( $admin_url );

			// Assets.
			$this->assets_dir = trailingslashit( $admin_dir . 'assets' );
			$this->assets_url = trailingslashit( $admin_url . 'assets' );

			/** Other ************************************************************ */

			// Main capability.
			$this->capability = is_multisite() ? 'manage_network_options' : 'manage_options';

			// Main settings page.
			$this->settings_page = 'options-general.php';
		}

		/**
		 * Plugin resources
		 */
		private function includes() {

			/** Core ************************************************************* */

			require( $this->admin_dir . 'tgm-config.php' );
			require( $this->admin_dir . 'hooks.php' );
			require( $this->admin_dir . 'functions.php' );
			require( $this->admin_dir . 'settings/navigation.php' );
			require( $this->admin_dir . 'settings/sections.php' );

			/** Components ******************************************************* */

			// Metaboxes.
			require( $this->admin_dir . 'metaboxes/menu-endpoints-metabox.php' );
			require( $this->admin_dir . 'metaboxes/fake-votes-metabox.php' );
			require( $this->admin_dir . 'metaboxes/posts/post-metabox.php' );
			require( $this->admin_dir . 'metaboxes/posts/meme-template-metabox.php' );
			require( $this->admin_dir . 'metaboxes/posts/list-post-metabox.php' );
			require( $this->admin_dir . 'metaboxes/posts/gallery-post-metabox.php' );
			require( $this->admin_dir . 'metaboxes/items/item-metabox.php' );
		}

		/**
		 * Define all admin hooks
		 */
		private function setup_hooks() {

			/** Menu ****************************************** */

			add_action( 'admin_menu',           array( $this, 'admin_menus' ) );
			add_action( 'admin_head',           array( $this, 'admin_head' ) );
			add_action( 'admin_init',           array( $this, 'register_admin_settings' ) );

			/** Assets **************************************** */

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			/** Settings **************************************** */

			add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );
		}

		/**
		 * Register menus in admin area
		 */
		public function admin_menus() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// About.
			add_dashboard_page(
				__( 'Welcome to Snax',  'snax' ),
				__( 'Welcome to Snax',  'snax' ),
				'manage_options',
				'snax-about',
				array( $this, 'about_page' )
			);

			$hooks = array();

			// General.
			$hooks[] = add_options_page(
				__( 'Snax', 'snax' ),
				__( 'Snax', 'snax' ),
				$this->capability,
				'snax-general-settings',
				'snax_admin_general_settings'
			);

			// Pages.
			$hooks[] = add_options_page(
				__( 'Snax Pages', 'snax' ),
				__( 'Snax Pages', 'snax' ),
				$this->capability,
				'snax-pages-settings',
				'snax_admin_pages_settings'
			);

			// Lists.
			$hooks[] = add_options_page(
				__( 'Snax Lists', 'snax' ),
				__( 'Snax Lists', 'snax' ),
				$this->capability,
				'snax-lists-settings',
				'snax_admin_lists_settings'
			);

			// Quizzes.
			$hooks[] = add_options_page(
				__( 'Snax Quizzes', 'snax' ),
				__( 'Snax Quizzes', 'snax' ),
				$this->capability,
				'snax-quizzes-settings',
				'snax_admin_quizzes_settings'
			);

			// Polls.
			$hooks[] = add_options_page(
				__( 'Snax Polls', 'snax' ),
				__( 'Snax Polls', 'snax' ),
				$this->capability,
				'snax-polls-settings',
				'snax_admin_polls_settings'
			);

			// Stories.
			$hooks[] = add_options_page(
				__( 'Snax Stories', 'snax' ),
				__( 'Snax Stories', 'snax' ),
				$this->capability,
				'snax-stories-settings',
				'snax_admin_stories_settings'
			);

			// Memes.
			$hooks[] = add_options_page(
				__( 'Snax Memes', 'snax' ),
				__( 'Snax Memes', 'snax' ),
				$this->capability,
				'snax-memes-settings',
				'snax_admin_memes_settings'
			);

			// Audios.
			$hooks[] = add_options_page(
				__( 'Snax Audios', 'snax' ),
				__( 'Snax Audios', 'snax' ),
				$this->capability,
				'snax-audios-settings',
				'snax_admin_audios_settings'
			);

			// Videos.
			$hooks[] = add_options_page(
				__( 'Snax Videos', 'snax' ),
				__( 'Snax Videos', 'snax' ),
				$this->capability,
				'snax-videos-settings',
				'snax_admin_videos_settings'
			);

			// Images.
			$hooks[] = add_options_page(
				__( 'Snax Images', 'snax' ),
				__( 'Snax Images', 'snax' ),
				$this->capability,
				'snax-images-settings',
				'snax_admin_images_settings'
			);

			// Galleries.
			$hooks[] = add_options_page(
				__( 'Snax Galleries', 'snax' ),
				__( 'Snax Galleries', 'snax' ),
				$this->capability,
				'snax-galleries-settings',
				'snax_admin_galleries_settings'
			);

			// Embeds.
			$hooks[] = add_options_page(
				__( 'Snax Embeds', 'snax' ),
				__( 'Snax Embeds', 'snax' ),
				$this->capability,
				'snax-embeds-settings',
				'snax_admin_embeds_settings'
			);

			// Voting.
			$hooks[] = add_options_page(
				__( 'Snax Voting', 'snax' ),
				__( 'Snax Voting', 'snax' ),
				$this->capability,
				'snax-voting-settings',
				'snax_admin_voting_settings'
			);

			// Limits.
			$hooks[] = add_options_page(
				__( 'Snax Limits', 'snax' ),
				__( 'Snax Limits', 'snax' ),
				$this->capability,
				'snax-limits-settings',
				'snax_admin_limits_settings'
			);

			// Auth.
			$hooks[] = add_options_page(
				__( 'Snax Auth', 'snax' ),
				__( 'Snax Auth', 'snax' ),
				$this->capability,
				'snax-auth-settings',
				'snax_admin_auth_settings'
			);

			// Demo.
			$hooks[] = add_options_page(
				__( 'Snax Demo', 'snax' ),
				__( 'Snax Demo', 'snax' ),
				$this->capability,
				'snax-demo-settings',
				'snax_admin_demo_settings'
			);

			$hooks[] = add_options_page(
				__( 'Snax Embedly', 'snax' ),
				__( 'Snax Embedly', 'snax' ),
				$this->capability,
				'snax-embedly-settings',
				'snax_admin_embedly_settings'
			);

			$hooks = apply_filters( 'snax_settings_pages', $hooks, $this->capability );

			// Highlight Settings > Snax menu item regardless of current tab.
			foreach ( $hooks as $hook ) {
				add_action( "admin_head-$hook", 'snax_admin_settings_menu_highlight' );
			}
		}

		/**
		 * Output the about page.
		 */
		public function about_page() {
			snax_get_template_part( 'pages/about' );
		}

		/**
		 * Hide submenu items under the Settings section
		 */
		public function admin_head() {
			// Settings pages.
			remove_submenu_page( $this->settings_page, 'snax-pages-settings' );
			remove_submenu_page( $this->settings_page, 'snax-lists-settings' );
			remove_submenu_page( $this->settings_page, 'snax-quizzes-settings' );
			remove_submenu_page( $this->settings_page, 'snax-polls-settings' );
			remove_submenu_page( $this->settings_page, 'snax-stories-settings' );
			remove_submenu_page( $this->settings_page, 'snax-memes-settings' );
			remove_submenu_page( $this->settings_page, 'snax-audios-settings' );
			remove_submenu_page( $this->settings_page, 'snax-videos-settings' );
			remove_submenu_page( $this->settings_page, 'snax-images-settings' );
			remove_submenu_page( $this->settings_page, 'snax-galleries-settings' );
			remove_submenu_page( $this->settings_page, 'snax-embeds-settings' );
			remove_submenu_page( $this->settings_page, 'snax-voting-settings' );
			remove_submenu_page( $this->settings_page, 'snax-limits-settings' );
			remove_submenu_page( $this->settings_page, 'snax-auth-settings' );
			remove_submenu_page( $this->settings_page, 'snax-embedly-settings' );
			remove_submenu_page( $this->settings_page, 'snax-demo-settings' );

			// About page.
			remove_submenu_page( 'index.php', 'snax-about' );
		}

		/**
		 * Register settings
		 *
		 * @return void
		 */
		public function register_admin_settings() {
			// Bail if no sections available.
			$sections = snax_admin_get_settings_sections();

			if ( empty( $sections ) ) {
				return;
			}

			// Loop through sections.
			foreach ( (array) $sections as $section_id => $section ) {

				// Only add section and fields if section has fields.
				$fields = snax_admin_get_settings_fields_for_section( $section_id );

				if ( empty( $fields ) ) {
					continue;
				}

				$page = $section['page'];

				// Add the section.
				add_settings_section(
					$section_id,
					$section['title'],
					$section['callback'],
					$page
				);

				// Loop through fields for this section.
				foreach ( (array) $fields as $field_id => $field ) {

					// Add the field.
					if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
						add_settings_field(
							$field_id,
							$field['title'],
							$field['callback'],
							$page,
							$section_id,
							$field['args']
						);
					}

					// Register the setting.
					register_setting( $page, $field_id, $field['sanitize_callback'] );
				}
			}
		}

		/**
		 * Load CSS
		 */
		public function enqueue_styles() {

			wp_enqueue_style( 'snax-admin', $this->assets_url . 'css/admin.css', array(), $this->version );
		}

		/**
		 * Load JS
		 */
		public function enqueue_scripts() {

			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'snax-admin', $this->assets_url . 'js/admin.js', array( 'jquery' ), $this->version );
			$config = array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
			);
			wp_localize_script( 'snax-admin', 'snax_admin_config', wp_json_encode( $config ) );
		}

		/**
		 * Registers Settings link on plugin description.
		 *
		 * @param array  $links          Links array.
		 * @param string $file           Plugin filename.
		 *
		 * @return	array
		 */
		public function add_plugin_settings_link( $links, $file ) {
			$basename = snax_get_plugin_basename();

			if ( is_plugin_active( $basename ) && $basename === $file ) {
				$links[] = '<a href="' . esc_url( snax_admin_url( add_query_arg( array( 'page' => 'snax-general-settings' ), 'admin.php' ) ) ) . '">'. esc_html( 'Settings', 'snax' ) .'</a>';
			}

			return $links;
		}
	}

	/**
	 * Return admin object instance
	 *
	 * @return Snax_Admin
	 */
	function snax_admin() {
		return Snax_Admin::get_instance();
	}

	snax_admin();

endif;

