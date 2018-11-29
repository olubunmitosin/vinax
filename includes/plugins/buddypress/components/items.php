<?php
/**
 * Snax BuddyPress Items Component Class
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

if ( ! class_exists( 'Snax_Items_BP_Component' ) ) :
	/**
	 * Loads Component for BuddyPress
	 */
	class Snax_Items_BP_Component extends BP_Component {

		/**
		 * Start the Snax component creation process
		 */
		public function __construct() {
			parent::start(
				snax_items_bp_component_id(),
				__( 'Submissions', 'snax' )
			);

			$this->fully_loaded();
		}

		/**
		 * Setup globals
		 *
		 * @param array $args           Component global variables.
		 */
		public function setup_globals( $args = array() ) {
			// All arguments for forums component.
			$args = array(
				'path'                  => BP_PLUGIN_DIR,
				'search_string'         => __( 'Search Submissions...', 'snax' ),
				'notification_callback' => 'snax_bp_format_notifications',
			);

			parent::setup_globals( $args );
		}

		/**
		 * Setup hooks
		 */
		public function setup_actions() {

			add_filter( 'snax_user_pending_items_page',     array( $this, 'user_pending_items_page' ), 10, 2 );
			add_filter( 'snax_user_approved_items_page',    array( $this, 'user_approved_items_page' ), 10, 2 );
			add_filter( 'snax_items_pagination_base',       array( $this, 'user_items_pagination_base' ), 10, 2 );

			$items_filter = 'bp_walker_nav_menu_start_el';
			add_filter( $items_filter,       array( $this, 'force_icon_id' ), 10, 1 );

			parent::setup_actions();
		}

		/**
		 * Return BP pending items page url
		 *
		 * @param string $url               Current url.
		 * @param int    $user_id           User id.
		 *
		 * @return string
		 */
		public function user_pending_items_page( $url, $user_id ) {
			$base_url       = bp_core_get_user_domain( $user_id );
			$component_slug = $this->slug;
			$status_slug    = snax_get_user_pending_items_slug();

			$url = $base_url . $component_slug . '/' . $status_slug;

			return $url;
		}

		/**
		 * Return BP approved items page url
		 *
		 * @param string $url               Current url.
		 * @param int    $user_id           User id.
		 *
		 * @return string
		 */
		public function user_approved_items_page( $url, $user_id ) {
			$base_url       = bp_core_get_user_domain( $user_id );
			$component_slug = $this->slug;
			$status_slug    = snax_get_user_approved_items_slug();

			$url = $base_url . $component_slug . '/' . $status_slug;

			return $url;
		}

		/**
		 * Change pagination base url
		 *
		 * @param string $base          Current base url.
		 * @param array  $args          WP Query args.
		 *
		 * @return string
		 */
		public function user_items_pagination_base( $base, $args ) {
			global $wp_rewrite;

			if ( $wp_rewrite->using_permalinks() && isset( $args['author'] ) ) {
				$user_id = $args['author'];
				$component_slug = $this->slug;

				$sub_component_slug = '';

				if ( isset( $args['post_status'] ) ) {
					switch ( $args['post_status'] ) {
						case snax_get_item_approved_status():
							$sub_component_slug = snax_get_user_approved_items_slug() . '/';
							break;

						case snax_get_item_pending_status():
							$sub_component_slug = snax_get_user_pending_items_slug() . '/';
							break;
					}
				}

				$base = bp_core_get_user_domain( $user_id ) . $component_slug . '/'. $sub_component_slug;

				$base = trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );
			}

			return $base;
		}

		/**
		 * Allow the variables, actions, and filters to be modified by third party
		 * plugins and themes.
		 */
		private function fully_loaded() {
			do_action_ref_array( 'snax_items_bp_component_loaded', array( $this ) );
		}

		/**
		 * Setup BuddyBar navigation
		 *
		 * @param array $main_nav               Component main navigation.
		 * @param array $sub_nav                Component sub navigation.
		 */
		public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

			// Stop if there is no user displayed or logged in.
			if ( ! is_user_logged_in() && ! bp_displayed_user_id() ) {
				return;
			}

			// Submissions.
			$main_nav = array(
				'name'                => __( 'Submissions', 'snax' ),
				'slug'                => $this->slug,
				'position'            => 94,
				'screen_function'     => 'snax_member_screen_approved_items',
				'default_subnav_slug' => snax_get_user_approved_items_slug(),
				'item_css_id'         => $this->id,
			);

			// Determine user to use.
			if ( bp_displayed_user_id() ) {
				$user_domain = bp_displayed_user_domain();
			} elseif ( bp_loggedin_user_domain() ) {
				$user_domain = bp_loggedin_user_domain();
			} else {
				return;
			}

			$component_link = trailingslashit( $user_domain . $this->slug );

			// Submissions > Approved.
			$sub_nav[] = array(
				'name'            => __( 'Approved', 'snax' ),
				'slug'            => snax_get_user_approved_items_slug(),
				'parent_url'      => $component_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'snax_member_screen_approved_items',
				'position'        => 40,
				'item_css_id'     => 'approved-items',
			);

			if ( bp_loggedin_user_id() ) {
				// Submissions > Pending (only for logged in user).
				$sub_nav[] = array(
					'name'            => __( 'Pending', 'snax' ),
					'slug'            => snax_get_user_pending_items_slug(),
					'parent_url'      => $component_link,
					'parent_slug'     => $this->slug,
					'screen_function' => 'snax_member_screen_pending_items',
					'position'        => 60,
					'item_css_id'     => 'pending-items',
				);
			}

			$main_nav = apply_filters( 'snax_bp_component_main_nav', $main_nav, $this->id );
			$sub_nav  = apply_filters( 'snax_bp_component_sub_nav', $sub_nav, $this->id );

			parent::setup_nav( $main_nav, $sub_nav );
		}

		/**
		 * Set up the admin bar
		 *
		 * @param array $wp_admin_nav       Component entries in the WordPress Admin Bar.
		 */
		public function setup_admin_bar( $wp_admin_nav = array() ) {

			// Menus for logged in user.
			if ( is_user_logged_in() ) {

				// Setup the logged in user variables.
				$user_domain = bp_loggedin_user_domain();
				$component_link = trailingslashit( $user_domain . $this->slug );

				// Submissions.
				$wp_admin_nav[] = array(
					'parent' => buddypress()->my_account_menu_id,
					'id'     => 'my-account-' . $this->id,
					'title'  => __( 'Submissions', 'snax' ),
					'href'   => trailingslashit( $component_link ),
				);

				// Submissions > Approved.
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-approved-items',
					'title'  => __( 'Approved', 'snax' ),
					'href'   => trailingslashit( $component_link . snax_get_user_approved_items_slug() ),
				);

				// Submissions > Pending.
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-pending-items',
					'title'  => __( 'Pending', 'snax' ),
					'href'   => trailingslashit( $component_link . snax_get_user_pending_items_slug() ),
				);
			}

			parent::setup_admin_bar( $wp_admin_nav );
		}

		/**
		 * Sets up the title for pages and <title>
		 */
		public function setup_title() {
			$bp = buddypress();

			// Adjust title based on view.
			$is_snax_component = (bool) bp_is_current_component( $this->id );

			if ( $is_snax_component ) {
				if ( bp_is_my_profile() ) {
					$bp->bp_options_title = __( 'Submissions', 'snax' );
				} elseif ( bp_is_user() ) {
					$bp->bp_options_avatar = bp_core_fetch_avatar( array(
						'item_id' => bp_displayed_user_id(),
						'type'    => 'thumb',
					) );

					$bp->bp_options_title = bp_get_displayed_user_fullname();
				}
			}

			parent::setup_title();
		}

		/**
		 * Append default link id to ensure we have an icon
		 *
		 * @param str $html  menu item html.
		 * @return str
		 */
		public function force_icon_id( $html ) {
			if ( snax_items_bp_component_id() !== 'snax_items' ) {
				$search = 'user-' . snax_items_bp_component_id();
				$replace = 'user-snax_items';
				$html = str_replace( $search, $replace , $html );
			}
			return $html;
		}
	}
endif;
