<?php
/**
 * TGM Configuration
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.6.0 for plugin Snax
 * @author     Thomas Griffin, Gary Jones, Juliette Reinders Folmer
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/TGMPA/TGM-Plugin-Activation
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

require_once dirname( __FILE__ ) . '/lib/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'snax_register_required_plugins' );

/**
 * Register plugin to recommend via TGMPA
 */
function snax_register_required_plugins() {
	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugin_dir = snax_get_plugin_dir();

	$plugins = array(
		array(
			'name'               => 'Envato Market',
			// The plugin name.
			'slug'               => 'envato-market',
			// The plugin slug (typically the folder name).
			'description'        => esc_html__( 'Automatic plugin updates', 'snax' ),
			'source'             => $plugin_dir . 'includes/plugins/zip/envato-market.zip',
			// The plugin source.
			'required'           => false,
			// If false, the plugin is only 'recommended' instead of required.
			'version'            => '1.0.0-RC2',
			// E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented.
			'force_activation'   => false,
			// If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false,
			// If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '',
			// If set, overrides default API URL and points to an external URL.
		),
	);

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'id'           => 'snax',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.

		'strings'          => array(
			'page_title'                      => esc_html__( 'Install Required Plugins', 'snax' ),
			'menu_title'                      => esc_html__( 'Install Plugins', 'snax' ),
			'installing'                      => esc_html__( 'Installing Plugin: %s', 'snax' ),
			// %1$s = plugin name
			'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'snax' ),
			'notice_can_install_required'     => _n_noop( 'This plugin requires the following plugin: %1$s.', 'This plugin requires the following plugins: %1$s.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_can_install_recommended'  => _n_noop( 'This plugin recommends the following plugin: %1$s.', 'This plugin recommends the following plugins: %1$s.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'snax' ),
			// %1$s = plugin name(s)
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this plugin: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this plugin: %1$s.', 'snax' ),
			// %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'snax' ),
			// %1$s = plugin name(s).
			'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'snax' ),
			'activate_link'                   => _n_noop( 'Activate installed plugin', 'Activate installed plugins', 'snax' ),
			'return'                          => esc_html__( 'Return to Required Plugins Installer', 'snax' ),
			'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'snax' ),
			'complete'                        => esc_html__( 'All plugins installed and activated successfully. %s', 'snax' ),
			// %1$s = dashboard link .
			'nag_type'                        => esc_html__( 'updated', 'snax' ),
			// Determines admin notice type - can only be 'updated' or 'error'.
		),
	);

	tgmpa( $plugins, $config );
}
