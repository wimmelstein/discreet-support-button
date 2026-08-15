<?php
/**
 * Plugin Name:       Discreet Support Button
 * Description:       A small fixed support button that expands to your donation links. The title and the donation methods are configurable under Settings > Support button.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Wim Wiltenburg
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       discreet-support-button
 *
 * @package DPSupportButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DPSB_VERSION', '1.0.0' );
define( 'DPSB_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPSB_URL', plugin_dir_url( __FILE__ ) );
define( 'DPSB_OPTION', 'dp_support_button' );

require DPSB_DIR . 'includes/class-dp-support-settings.php';
require DPSB_DIR . 'includes/class-dp-support-render.php';

add_action(
	'plugins_loaded',
	function () {
		DP_Support_Settings::instance();
		DP_Support_Render::instance();
	}
);

/**
 * Seed sensible defaults the first time the plugin is activated.
 */
register_activation_hook(
	__FILE__,
	function () {
		if ( false === get_option( DPSB_OPTION ) ) {
			add_option(
				DPSB_OPTION,
				array(
					'enabled' => 1,
					'title'   => __( 'Support this site', 'discreet-support-button' ),
					'methods' => array(),
				)
			);
		}
	}
);
