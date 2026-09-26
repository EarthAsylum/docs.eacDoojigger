<?php
namespace EarthAsylumConsulting;

/**
 * For self-hosted plugin dependency, provides administrator notification
 * with links for "View Details" and "Install and Activate" or "Activate"
 * on the 'plugins.php', 'plugin-install.php', and 'update-core.php' pages.
 *
 * @category	WordPress Plugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 * @license		https://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 * @link		https://github.com/EarthAsylum/docs.eacDoojigger/tree/main/Doodads
 * @version		26.0926.1
 */

if (! defined( 'ABSPATH' )) exit;

/*
 * Usage:
 * 1. Add this file to the root folder of your plugin.
 * 2. Add this code in the load process of your main plugin file...
 * --
	if (! class_exists( 'EarthAsylumConsulting\eacDoojigger', false ) )
	{
		require_once 'dependency.php';
		return \EarthAsylumConsulting\dependency::notice([
			'plugin'	=>	[ 'My Awesome Plugin' 	=> plugin_basename( __FILE__ ) ],
			'requires'	=> 	[ '{eac}Doojigger' 		=> 'eacDoojigger/eacDoojigger.php' ],
			'manifest'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.json',
			'download'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.zip',
			'after'		=> self_admin_url('/admin.php?page=eacdoojigger-settings&tab=registration')
		]);
	}
 * --
 *
 * 'plugin' 	- (required) the plugin [name => slug] of the requiring plugin.
 *
 * 'requires' 	- (required) the plugin [name => slug] of the required plugin.
 *
 * 'manifest' 	- (optional) the url to the plugin json update/information file.
 * 					Should provide a valid & complete json file for both "View Details" and plugin install.
 *					Required for "View Details"; Required for plugin install if "download" is omitted.
 *
 * 'download' 	- (optional) the url to the download .zip file for plugin install.
 * 					If omitted, the download link from the manifest file is used.
 *
 * 'after'		- (optional) the url to redirect to after installing and activating the plugin.
 * 					If omitted, the current page (e.g. /plugins) is reloaded.
 *
 */


if (! class_exists('\EarthAsylumConsulting\dependency'))
{
	class dependency
	{
		/**
		 * @var string notification of dependency
		 */
		const NOTICE_STRING 	= '<strong>%1$s</strong> requires installation &amp; activation of <em>%2$s</em>.';
		/**
		 * @var int notification priority (admin_notices)
		 * (we'd like to be near the top of the screen with secondary notifications closely following)
		 */
		const NOTICE_PRIORITY 	= 5;

		/**
		 * @var string notification hook ('network_admin_notices', 'admin_notices')
		 */
		public static $notices_hook;


		/**
		 * Report dependency via admin notice with links for info and install/activate.
		 *
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		public static function notice( array $dependency ): bool
		{
			if (! isset( $dependency['plugin'], $dependency['requires'] ) ) {
				return false; 	// nothing we can do
			}

			self::$notices_hook = (is_network_admin()) ? 'network_admin_notices' : 'admin_notices';

			// where we go after installing
			if (! isset( $dependency['after'] ) ) {
				$dependency['after'] = $_SERVER['REQUEST_URI'];
			}

			$slug 		= dirname( current($dependency['requires']) );

			// only do this once even if multiple plugins are dependent
			if ( (isset($dependency['manifest']) || isset($dependency['download']))
					&& (! is_multisite() || is_network_admin())
					&& (! has_action("plugin_dependency_{$slug}")) )
			{
				add_action( 'current_screen',	function($screen) use($dependency)
					{
						self::remote_plugin_dependency($screen, $dependency);
					},10,1
				);
				add_filter( 'plugins_api',		function($res, $action, $args) use($dependency)
					{
						return self::remote_plugin_information($res, $action, $args, $dependency);
					},10,3
				);
				add_action( "plugin_dependency_{$slug}", '__return_true' );
			}
			else // otherwise, display a simple notice
			{
				add_action( 'current_screen',	function($screen) use($dependency)
					{
						self::remote_plugin_notice($screen, $dependency);
					},10,1
				);
			}

			add_action( 'after_plugin_row_meta', function($plugin_file, $plugin_data) use($dependency)
				{
					self::remote_plugin_message($plugin_file, $plugin_data, $dependency);
				},10,2
			);
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

			if ( ! (current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' )) ) {
				return;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin 	= current($dependency['requires']);
			$slug 		= dirname($plugin);

			// Catch custom action when the user clicks our installer link
			if ( isset( $_GET['action'] ) && str_starts_with($_GET['action'],'dependency') && wp_unslash($_GET['plugin']) == $plugin )
			{
				self::handle_remote_action($_GET['action'],$plugin,$slug,$dependency);
				return;
			}

			// We shouldn't be here if this is true
			if ( is_plugin_active( $plugin ) ) {
				return;
			}

			// Deactivate the deependent plugin
			//deactivate_plugins( current($dependency['plugin']) );

			// Pass the generated links to the admin notices hook
			add_action( self::$notices_hook, function() use ($plugin, $slug, $dependency)
				{
					if ( $action_link = self::get_plugin_action_link( $plugin, $slug, $dependency ) )
					{
						echo '<style>@keyframes spin-icon {0% {transform: rotate(0deg);} 100% {transform: rotate(360deg);}}'.
							 '.spin-icon {color:#cc1818; animation: spin-icon 2s linear infinite;}</style>';
						echo '<div class="notice notice-error is-dismissible">'.
							 '<span id="spin-icon" class="dashicons dashicons-update"></span>&nbsp;';
						printf(self::NOTICE_STRING.
							 '<div style="width:max-content;margin:0 1em;line-height:2;">%3$s</div></div>',
							key($dependency['plugin']),
							key($dependency['requires']),
							nl2br(ltrim(
								self::get_plugin_info_link( $plugin, $slug, $dependency ) . "\n" .
								$action_link
							))
						);
					}
				},self::NOTICE_PRIORITY
			);
		}


		/**
		 * Display a secondary dependency notice.
		 *
		 * @param object the current screen
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		public static function remote_plugin_notice($screen, array $dependency): void
		{
			global $pagenow;

			// Pages where we want to show our dependency and allow updating
			if ( ! in_array($pagenow, ['plugins.php','plugin-install.php','update-core.php'] ) ) {
				return;
			}

			$plugin 	= current($dependency['requires']);

			add_action( self::$notices_hook, function() use($dependency)
				{
					printf('<div class="notice notice-warning is-dismissible">'.self::NOTICE_STRING.'</div>',
						key($dependency['plugin']),
						key($dependency['requires'])
					);
				},self::NOTICE_PRIORITY+1
			);
		}


		/**
		 * Display a message on the plugins screen.
		 *
		 * @param string $plugin_file Refer to {@see 'plugin_row_meta'} filter.
		 * @param array  $plugin_data Refer to {@see 'plugin_row_meta'} filter.
		 * @param array  $dependency array (plugin,requires,manifest)
		 */
		public static function remote_plugin_message( $plugin_file, $plugin_data, $dependency )
		{
			if ($plugin_file == current($dependency['plugin']))
			{
				printf( '<div class="notice notice-warning inline">'.
						strip_tags(self::NOTICE_STRING).'</div>',
					key($dependency['plugin']),
					key($dependency['requires'])
				);
			}
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
			if (!isset($dependency['manifest'])) return $res;

			$slug = dirname(current($dependency['requires']));

			// Only if we're looking for our specific self-hosted plugin slug
			// ajax installer (ajax-action.php) converts slug to lower case
			if ( $action != 'plugin_information' || strtolower($args->slug) != strtolower($slug) ) {
				return $res;
			}

			// Check for cached results
			$cacheName = "plugin_dependency_{$slug}";
			if ($cache = wp_cache_get($cacheName, 'plugin_dependency')) {
				return (object)$cache;
			}

			// Get remote json file
			$response	= wp_remote_get( $dependency['manifest'] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$status 	= wp_remote_retrieve_response_code( $response );
			if ($status != 200 ) {
				$response	= json_decode( wp_remote_retrieve_body( $response ), true );
				$message 	= $response['message'] ?? status_header($status) ?? 'Unknown';
				$message 	= "{$slug} - An error occured while retrieving the remote file<br>".
										"<em>Status: {$status}, {$message}</em>.";
				return new \WP_Error('plugin_info_failed',$message,['status'=>$status]);
			}

			$res = json_decode( wp_remote_retrieve_body( $response ), true );
			// Cache and return the results
			if ($status == 200) {
				wp_cache_set($cacheName, $res, 'plugin_dependency', HOUR_IN_SECONDS);
			}
			return (object)$res;
		}


		/**
		 * Handle the requested install/activate action
		 *
		 * @param string $action url action=dependency_activate/install
		 * @param string $plugin plugin name (plugin/plugin.php)
		 * @param string $slug plugin slug (plugin)
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function handle_remote_action(string $action, string $plugin, string $slug, array $dependency ): void
		{
			// Validate the nonce
			check_admin_referer( $action );

			if ( $action == 'dependency_activate' )
			{
				activate_plugin( $plugin, '', (is_multisite() && is_network_admin()) );

				// Redirect (back to the plugins screen)
				wp_safe_redirect( remove_query_arg(['action','plugin','_wpnonce'],$dependency['after']) );
			}
			else if ( str_starts_with($action,'dependency_install') )
			{
				// Use the manifest file to get the download link
				if ( empty( $dependency['download'] ) && !empty( $dependency['manifest'] ) )
				{
					$result = self::remote_plugin_information( false, 'plugin_information', (object)['slug'=>$slug], $dependency);
					if (is_wp_error($result)) {
						add_action(self::$notices_hook, function() use($result) {
							echo "<div class='notice notice-warning'>".$result->get_error_message()."</div>";
						},self::NOTICE_PRIORITY);
					} else {
						$dependency['download'] = $result->download_link;
					}
				}

				if ( !empty( $dependency['download'] ) )
				{
					self::handle_remote_install( $action, $plugin, $slug, $dependency );
				}
			}
			exit;
		}


		/**
		 * Uses WordPress core upgraders to remotely download, extract, and activate the zip.
		 *
		 * @param string $action url action=dependency_activate/install
		 * @param string $plugin plugin name (plugin/plugin.php)
		 * @param string $slug plugin slug (plugin)
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function handle_remote_install(string $action, string $plugin, string $slug,  array $dependency )
		{
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			// Use standard WordPress skin & installer
			$upgrader = new \Plugin_Upgrader(
				new \Plugin_Installer_Skin([
					'title'		=> key($dependency['requires']).' Installation',
					'plugin'	=> urlencode($plugin),
					'nonce' 	=> $action
				])
			);

			// Redirect (back to the plugins screen) (before any output)
			if ($action == 'dependency_install_activate') {
				wp_safe_redirect( remove_query_arg(['action','plugin','_wpnonce'],$dependency['after']) );
			} else {
				wp_safe_redirect( remove_query_arg(['action','plugin','_wpnonce'],$_SERVER['REQUEST_URI']) );
			}

			// We can't capture the output (install flushes the buffers) but we can wrap it (if not redirecting)
			ob_start();
			echo "<div class='notice notice-warning'>";
			$result = $upgrader->install( $dependency['download'] );
			echo "</div>";

			// Automatically activate after a successful installation
			if ( $result && ! is_wp_error( $result ) && $action == 'dependency_install_activate' ) {
				activate_plugin( $plugin, '', (is_multisite() && is_network_admin()) );
			}
		}


		/**
		 * Get the "view details" link
		 *
		 * @param string $plugin plugin name (plugin/plugin.php)
		 * @param string $slug plugin slug (plugin)
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function get_plugin_info_link(string $plugin, string $slug,  array $dependency ): string
		{
			if (! isset( $dependency['manifest'] ) ) return '';

			$action_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=550' );

			// Link triggers the built-in WordPress Modal backdrop
			return sprintf(
				'&rarr; <a href="%1$s" class="thickbox open-plugin-details-modal" aria-label="View details about %2$s" data-title="%2$s Details">%3$s</a>',
				esc_url( $action_url ),
				esc_html(key($dependency['requires'])),
				esc_html__( "View \"".key($dependency['requires'])."\" details" )
			);
		}


		/**
		 * Get the install/activate link
		 *
		 * @param string $plugin plugin name (plugin/plugin.php)
		 * @param string $slug plugin slug (plugin)
		 * @param array $dependency array (plugin,requires,manifest)
		 */
		private static function get_plugin_action_link(string $plugin, string $slug,  array $dependency ): string
		{
			// Should we network activate?
			$network = (is_multisite() && is_plugin_active_for_network(current($dependency['plugin'])))
				? 'Network ' : '';

			// Check whether it is not installed or just deactivated
			$plugins_list = get_plugins();

			if (isset( $plugins_list[ $plugin ] ))
			{
				if ( current_user_can( 'activate_plugins' ) && (!is_network_admin() || !empty($network)) ) {
					// Action to activate an installed plugin
					$action 	= 'dependency_activate';
					$link_text 	= "{$network}Activate \"".key($dependency['requires'])."\"";
				}
			}
			else if ( current_user_can( 'install_plugins' ) )
			{
				if (is_multisite() && empty($network)) {
					$action 	= 'dependency_install';
					$link_text 	= "Install \"".key($dependency['requires'])."\"";
				} else {
					$action 	= 'dependency_install_activate';
					$link_text 	= "Install and {$network}Activate \"".key($dependency['requires'])."\"";
				}
			}

			if ( ! isset( $link_text ) ) return '';

			// Action to install self-hosted plugin
			$action_url = wp_nonce_url(
				add_query_arg( ['action'=>$action,'plugin'=>urlencode( $plugin )] ), $action
			);

			return sprintf(
				'&rarr; <a href="%1$s" aria-label="%2$s" data-title="%2$s" ' .
					'onclick="this.parentElement.style.opacity=0.5;'.
					'document.getElementById(\'spin-icon\').classList.add(\'spin-icon\')">%2$s</a>',
				esc_url( $action_url ),
				esc_html__( $link_text )
			);
		}
	}
}
