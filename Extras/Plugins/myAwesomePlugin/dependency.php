<?php
namespace EarthAsylumConsulting;

/**
 * For self-hosted plugin dependency, provides administrator notification
 * with links for "View Details" and "Install and Activate" or "Activate".
 *
 * @category	WordPress Plugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 * @license		https://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 * @link		https://github.com/EarthAsylum/docs.eacDoojigger/tree/main/Doodads
 * @version		26.0922.1
 */

if (! defined( 'ABSPATH' )) exit;

/*
 * Usage:
 * 1. Add this file to the root folder of your plugin.
 * 2. Add this code early in your plugin loader/primary plugin file...
 * --
	if (! class_exists( 'EarthAsylumConsulting\eacDoojigger', false ) )
	{
		require_once 'dependency.php';
		return \EarthAsylumConsulting\dependency::notice([
			'plugin'	=>	[ 'My Awesome Plugin' 	=> plugin_basename( __FILE__ ) ],
			'requires'	=> 	[ '{eac}Doojigger' 		=> 'eacDoojigger/eacDoojigger.php' ],
			'manifest'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.json',
			'download'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.zip',
			'after'		=> '/wp-admin/admin.php?page=eacdoojigger-settings&tab=registration'
		]);
	}
 * --
 *
 * 'plugin' 	- (required) the plugin [name => slug] of the requiring plugin.
 *
 * 'requires' 	- (required) the plugin [name => slug] of the required plugin.
 *
 * 'manifest' 	- (required) the url to the plugin json update/information file.
 * 					Must provide a valid & complete json file for both "View Details" and plugin install.
 *
 * 'download' 	- (optional) the url to the download .zip file.
 * 					If omitted, the download link from the json file is used.
 *
 * 'after'		- (optional) the url to redirect to after installing and activating the plugin.
 * 					If omitted, the current page (e.g. /plugins) is reloaded.
 *
 * Be aware of plugin load order! {eac}Doojigger loads early; others load alphabetically.
 */


if (! class_exists('\EarthAsylumConsulting\dependency'))
{
	class dependency
	{
		/**
		 * Report dependency via admin notice with links for info and install/activate.
		 *
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		public static function notice( array $dependency ): bool
		{
			if (! isset($dependency['plugin'],$dependency['requires']) ) {
				// nothing we can do
				return false;
			}

			if (! isset($dependency['manifest']) ) {
				// not much we can do
				add_action( 'admin_notices', function() use($dependency)
					{
						printf('<div class="notice notice-error is-dismissible"><strong>%s</strong> requires installation & activation of %s.</div>',
							key($dependency['plugin']),
							key($dependency['requires'])
						);
					}
				);
				return false;
			}

			// where we go after installing
			if (! isset($dependency['after']) )
			{
				$dependency['after'] = $_SERVER['REQUEST_URI'];
			}

			$slug = dirname( current($dependency['requires']) );

			// only do this once even if multiple plugins are dependent
			if (! has_action("plugin_dependency_{$slug}"))
			{
				add_action( 'current_screen',	function($screen) use($dependency)
					{
						self::remote_plugin_dependency($screen, $dependency);
					},10,1
				);
				add_filter( 'plugins_api',		function($res, $action, $args) use($dependency)
					{
						return self::remote_plugin_information($res, $action, $args, $dependency);
					},20,3
				);
				add_action("plugin_dependency_{$slug}", '__return_true');
			}

			return true;
		}


		/**
		 * Create the dependency notice and maybe trigger the install.
		 *
		 * @param object the current screen
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		public static function remote_plugin_dependency($screen, array $dependency): void
		{
			global $pagenow;

			// Pages where we want to show our dependency and allow updating
			if ( ! in_array($pagenow, ['plugins.php','plugin-install.php','update-core.php'] ) ) {
				return;
			}
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin 		= current($dependency['requires']);
			$slug 			= dirname($plugin);
			$install_action	= "install_{$slug}";

			// Catch custom action when the user clicks our installer link
			if ( isset( $_GET['action'] ) && $_GET['action'] === $install_action ) {
				self::handle_remote_action($slug,$install_action,$dependency);
				return;
			}

			// Evaluate the status of dependency
			if ( is_plugin_active( $plugin ) ) {
				return;
			}

			// Deactivate the plugin
			//deactivate_plugins( current($dependency['plugin']) );

			// Suppress default "Plugin activated" notice
			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			// Check whether it is not installed or just deactivated
			$plugins_list = get_plugins();

			if (isset( $plugins_list[ $plugin ] )) {
				$action_url = wp_nonce_url(
					add_query_arg(['action'=>'activate','plugin'=>urlencode( $plugin )]),
					"activate-plugin_".$plugin
				);
				$link_text = __( "Activate ".key($dependency['requires']) );
			} else {
				$action_url = wp_nonce_url(
					add_query_arg(['action'=>$install_action,'plugin'=>urlencode( $plugin )]),
					$install_action
				);
				$link_text = __( "Install and Activate ".key($dependency['requires']) );
			}

			// Pass the generated link cleanly into the admin notices hook
			add_action( 'admin_notices', function() use ( $dependency, $action_url, $link_text )
				{
					printf('<div class="notice notice-error update-now is-dismissible">'.
							'<span class="dashicons dashicons-warning"></span> '.
							'<strong>%s</strong> requires installation & activation of %s.'.
							'<p style="margin-left:2em;line-height:1.75;">&rarr; %s <br>&rarr; <a href="%s">%s</a></p></div>',
						key($dependency['plugin']),
						key($dependency['requires']),
						self::get_plugin_info_link( $dependency ),
						esc_url( $action_url ),
						esc_html( $link_text ),
					);
				},5
			);
		}


		/**
		 * Handle the requested install/activate action
		 *
		 * @param string $slug plugin slug
		 * @param string $install_action string the url & nonce action
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function handle_remote_action(string $slug, string $install_action, array $dependency ): bool
		{
			check_admin_referer( $install_action );

			if (empty($dependency['download']))
			{
				$result = self::remote_plugin_information( false, 'plugin_information', (object)['slug'=>$slug], $dependency);
				if (is_wp_error($result)) {
					add_action('admin_notices', function() use($result) {
						echo "<div class='notice notice-warning'>".$result->get_error_message()."</div>";
					},1);
				} else {
					$dependency['download'] = $result->download_link;
				}
			}

			if (!empty($dependency['download']))
			{
				self::handle_remote_install( $dependency );
			}

			return true;
		}


		/**
		 * Uses WordPress core upgraders to remotely download, extract, and activate the zip.
		 *
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function handle_remote_install( array $dependency )
		{
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );
			}

			$plugin 		= current($dependency['requires']);

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			// Set up standard WordPress UI skin for plugin installation
			$upgrader = new \Plugin_Upgrader(
				new \Plugin_Installer_Skin([
					'title'		=> key($dependency['requires']).' Installation',
					'plugin'	=> urlencode($plugin),
					'nonce' 	=> 'install-plugin_'.$plugin
				])
			);

			// Redirect back to the plugins screen (before any output)
			wp_redirect( remove_query_arg(['activate','action','plugin','_wpnonce'],$dependency['after']) );

			// We can't capture the output but we can wrap it (if not redirecting)
			ob_start();
			echo "<div class='notice notice-warning'>";
			$result = $upgrader->install( $dependency['download'] );
			echo "</div>";

			// Automatically activate after a successful installation pass
			if ( $result && ! is_wp_error( $result ) ) {
				activate_plugin( $plugin );
			}
			exit;
		}


		/**
		 * Gets the "view details" link
		 *
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function get_plugin_info_link( array $dependency )
		{
			if (!isset($dependency['manifest'])) return '';

			// Use the exact folder/slug path of your self-hosted plugin
			$slug = dirname( current($dependency['requires']) );

			$action_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=550' );

			// This anchor HTML triggers the built-in WordPress Modal backdrop automatically
			return sprintf(
				'<a href="%1$s" class="thickbox open-plugin-details-modal" aria-label="View details about %2$s" data-title="%2$s Details">%3$s</a>',
				esc_url( $action_url ),
				esc_html(key($dependency['requires'])),
				esc_html__( "View ".key($dependency['requires'])." details" )
			);
		}


		/**
		 * Intercepts the core plugins API call to inject self-hosted metadata.
		 *
		 * @param object $res reulst from plugins_api
		 * @param string $action action from plugins_api
		 * @param object $args arguments from plugins_api
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function remote_plugin_information( $res, $action, $args, array $dependency )
		{
			$slug = dirname(current($dependency['requires']));

			// Only if we're looking for our specific self-hosted plugin slug
			// ajax installer (from view details screen) converts slug to lower case
			if ( $action != 'plugin_information' || strtolower($args->slug) != strtolower($slug) ) {
				return $res;
			}

			// Check for cached results
			$cacheName = "plugin_dependency_{$slug}";
			if ($cache = wp_cache_get($cacheName, 'plugin_dependency')) {
			//	return (object)$cache;
			}

			// Get remote json file
			$response	= wp_remote_get( $dependency['manifest'] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$status 	= wp_remote_retrieve_response_code( $response );
			if ($status != 200 ) {
				$response	= json_decode( wp_remote_retrieve_body( $response ), true );
				$message 	= $response['message'] ?? status_header($status) ?? '';
				$message 	= "{$slug} - An error occured while retrieving the remote file<br>".
										"<em>Status: {$status}, {$message}</em>.";
				return new \WP_Error('plugin_info_failed',$message,['status'=>$status]);
			}

			$res = json_decode( wp_remote_retrieve_body( $response ), true );
			// Cache and return the results
			if ($status == 200) {
				wp_cache_set($cacheName, $res, 'plugin_dependency', 4 * HOUR_IN_SECONDS);
			}
			return (object)$res;
		}
	}
}
