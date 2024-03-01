<?php
namespace EarthAsylumConsulting\Extensions;

if (! class_exists(__NAMESPACE__.'\test_api_debugging_extension', false) )
{
	/**
	 * test_api_debugging extension, debugging API - {eac}Doojigger for WordPress
	 *
	 * @category	WordPress Plugin
	 * @package		{eac}Doojigger
	 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
	 * @copyright	Copyright (c) 2022 EarthAsylum Consulting <www.EarthAsylum.com>
	 * @version		1.x
	 * @link		https://eacDoojigger.earthasylum.com/
	 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
	 */

	class test_api_debugging_extension extends \EarthAsylumConsulting\abstract_extension
	{
		/**
		 * @var string extension version
		 */
		const VERSION	= '23.1005.1';


		/**
		 * constructor method
		 *
		 * @param 	object	$plugin main plugin object
		 * @return 	void
		 */
		public function __construct($plugin)
		{
			parent::__construct($plugin);

			if ($this->is_admin() && $this->isEnabled('debugging_extension'))
			{
				// Register plugin options to/after debugging_extension options
				$this->add_action( "options_settings_page", function()
				{
					$this->registerExtensionOptions( 'debugging_extension',
						[
							'debug_test_api' 		=> array(
											'type'		=> 	'checkbox',
											'label'		=> 	'Debugging API',
											'options'	=> 	['Enabled'],
											'info'		=>	'<a href="'.home_url("/wp-json/{$this->pluginName}/debug").'" target="_blank">'.
																home_url("/wp-json/{$this->pluginName}/debug").'</a> - polls plugin &amp; extensions for debugging<br/>'.
															'<a href="'.home_url("/wp-json/{$this->pluginName}/log").'" target="_blank">'.
																home_url("/wp-json/{$this->pluginName}/log").'</a> - shows current debugging log entries<br/>'.
															'<a href="'.home_url("/wp-json/{$this->pluginName}/filters").'" target="_blank">'.
																home_url("/wp-json/{$this->pluginName}/filters").'</a> - tests '.$this->pluginName.' filters'
										),
						]
					);
				},99);
			}
		}


		/**
		 * initialize method - called from main plugin
		 *
		 * @return 	void
		 */
		public function initialize()
		{
			if ( ! parent::initialize() ) return; // disabled
			$this->current_user = wp_get_current_user();
		}


		/**
		 * Add filters and actions - called from main plugin
		 *
		 * @return	void
		 */
		public function addActionsAndFilters()
		{
			if ($this->is_option('debug_test_api'))
			{
				add_action( 'rest_api_init',	array($this, "register_api") );
			}
		}


		/**
		 * Register a WP REST api
		 *
		 * @return void
		 */
		public function register_api()
		{
			register_rest_route( $this->pluginName, '/debug', array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'api_debug_state' ),
						'permission_callback' => array( $this, 'api_authentication' ),
					),
			));
			register_rest_route( $this->pluginName, '/log', array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'api_debug_log' ),
						'permission_callback' => array( $this, 'api_authentication' ),
					),
			));
			register_rest_route( $this->pluginName, '/filters', array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'api_debug_filter' ),
						'permission_callback' => array( $this, 'api_authentication' ),
					),
			));
		}


		/**
		 * API Authentication
		 *
		 * @param 	object	$request - WP_REST_Request Request object.
		 * @return 	object	stdClass|WP_Error - Post object or WP_Error.
		 */
		public function api_authentication($request)
		{
			return user_can($this->current_user, 'manage_options');
		}


		/**
		 * API testing - state
		 *
		 * @param 	object	$request - WP_REST_Request Request object.
		 * @return 	object	stdClass|WP_Error - result object or WP_Error.
		 */
		public function api_debug_state($request)
		{
			/**
			 * filter {classname}_debugging get debugging information
			 * @param	array 	current array
			 * @return	array	extended array with [ extension_name => [key=>value array] ]
			 */
			$response = $this->apply_filters( 'debugging', [] );
			unset($response['Log Data']);
			return rest_ensure_response( $response );
		}


		/**
		 * API testing - log
		 *
		 * @param 	object	$request - WP_REST_Request Request object.
		 * @return 	object	stdClass|WP_Error - result object or WP_Error.
		 */
		public function api_debug_log($request)
		{
			/**
			 * filter {classname}_debugging get debugging information
			 * @param	array 	current array
			 * @return	array	extended array with [ extension_name => [key=>value array] ]
			 */
			$response = $this->apply_filters( 'debuglog', [] );
			return rest_ensure_response( $response );
		}


		/**
		 * API testing - filters
		 *
		 * @param 	object	$request - WP_REST_Request Request object.
		 * @return 	object	stdClass|WP_Error - result object or WP_Error.
		 */
		public function api_debug_filter($request)
		{
			$filters = $shortcodes = $applied = array();

			try {
				// plugin shortcode tests

				$shortcodes['method get_the_field'] 	= do_shortcode("[{$this->pluginName} method='get_the_field' args='_wp_old_date,17']");
				$shortcodes['method getVisitorIP'] 		= do_shortcode("[{$this->pluginName} method='getVisitorIP']");
				$shortcodes['method getPluginValue'] 	= do_shortcode("[{$this->pluginName} method='getPluginValue' args='PluginSlug']");
				$shortcodes['method getSemanticVersion']= do_shortcode("[{$this->pluginName} method='getSemanticVersion' index='version']");
				$shortcodes['method SemanticVersion']	= do_shortcode("[{$this->pluginName} method='getSemanticVersion']"); //__toString()
				$shortcodes['method getVersion'] 		= do_shortcode("[{$this->pluginName} method='getVersion' args='debugging']");
				$shortcodes['method currentURL'] 		= do_shortcode("[{$this->pluginName} method='currentURL']");
				$shortcodes['method getPageName'] 		= do_shortcode("[{$this->pluginName} method='getPageName']");
				$shortcodes['method sessionId'] 		= do_shortcode("[{$this->pluginName} method='session_extension.sessionId']");
				$shortcodes['method server_name'] 		= do_shortcode("[{$this->pluginName} method='varServer' args='server_name']");
				$shortcodes['option siteEnvironment'] 	= do_shortcode("[{$this->pluginName} option='siteEnvironment']");
				$shortcodes['option cybersource(1)'] 	= do_shortcode("[{$this->pluginName} option='woocommerce_cybersource_credit_card_settings' index='description' default='not installed']");
				$shortcodes['option cybersource(2)'] 	= do_shortcode("[{$this->pluginName} option='woocommerce_cybersource_credit_card_settings' index='description']not installed[/{$this->pluginName}]");
				$shortcodes['bloginfo name'] 			= do_shortcode("[{$this->pluginName} bloginfo='name']");
				$shortcodes['bloginfo description'] 	= do_shortcode("[{$this->pluginName} bloginfo='description']");
				$shortcodes['bloginfo admin_email'] 	= do_shortcode("[{$this->pluginName} bloginfo='admin_email']");

				// plugin filter tests

				$filters[] = $this->plugin_filter( false, ['method'=>'get_the_field','args'=>'_wp_old_date,17'] );
				$filters[] = $this->plugin_filter( false, ['bloginfo'=>'name'] );
				$filters[] = $this->plugin_filter( false, ['bloginfo'=>'description'] );
				$filters[] = $this->plugin_filter( false, ['bloginfo'=>'admin_email'] );
				// sanitize a value
				$filters[] = $this->plugin_filter( null, ['method'=>'sanitize', 'args'=>[\apply_filters( $this->pluginName, null, ['method'=>'varServer','args'=>'user_agent'] )] ]);
				// get a static or stored variable
				$filters[] = $this->plugin_filter( false, ['method'=>'getVariable','args'=>'PluginURI'] );
				// get a stored prefixed option
				$filters[] = $this->plugin_filter( false, ['method'=>'is_option','args'=>['debug_test_api','Enabled']] );
				$filters[] = $this->plugin_filter( false, ['option'=>'debug_test_api'] );
				$filters[] = $this->plugin_filter( false, ['method'=>'is_option','args'=>['adminSettingsMenu','Main Sidebar']] );

				// two ways to set default value, using index to get an array key
				$filters[] = $this->plugin_filter( null, ['option'=>'woocommerce_cybersource_credit_card_settings', 'index'=>'description', 'default'=>'Cybersource not installed'] );
				$filters[] = $this->plugin_filter( 'Cybersource not installed', ['option'=>'woocommerce_cybersource_credit_card_settings', 'index'=>'description'] );

				// get a stored prefixed network option - get_network_option may return false, make it null
				$filters[] = $this->plugin_filter( 'no network option', ['method'=>'get_network_option','args'=>['siteEnvironment',null]] );

				// get a _GET or _POST variable
				foreach ($_REQUEST as $name=>$value) {
					$filters[] = $this->plugin_filter( null, ['method'=>'varRequest', 'args'=>$name] );
				}
				// get a _SERVER variable
				$filters[] = $this->plugin_filter( null, ['method'=>'varServer','args'=>'user_agent'] );

				// call plugin method
				$filters[] = $this->plugin_filter( null, ['method'=>'getMySqlVersion'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getPluginValue','args'=>'PluginSlug'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_admin'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_network_admin'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_frontend'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_backend'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_plugin_active'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'is_network_enabled'] );			// plugin
				$filters[] = $this->plugin_filter( null, ['method'=>'createUniqueId'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getClassName'] );					// plugin
				$filters[] = $this->plugin_filter( null, ['method'=>'debugging.getClassName'] );		// extension
				$filters[] = $this->plugin_filter( null, ['method'=>'getVersion'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getVersion', 'args'=>'debugging'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getSemanticVersion','index'=>'version'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getSemanticVersion','args'=>'1.2.3-RC.1+23.1005.1','index'=>'version']);
				$filters[] = $this->plugin_filter( null, ['method'=>'getSemanticVersion','args'=>'1.2.3-RC.1+23.1005.1','index'=>'primary']);
				$filters[] = $this->plugin_filter( null, ['method'=>'getSemanticVersion','args'=>'1.2.3-RC.1+23.1005.1']); // __toString()
				$filters[] = $this->plugin_filter( null, ['method'=>'getSemanticVersion','args'=>\apply_filters( $this->pluginName, null, ['method'=>'getMySqlVersion'] )]); // __toString()
				$filters[] = $this->plugin_filter( null, ['method'=>'isTestSite'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'isAjaxRequest'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getVisitorIP'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getVisitorCountry'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getVisitorId'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'currentURL'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getPageName'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'isCurrentScreen'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'parseAttributes', 'args'=>"data-1='one' data-2='two'"] );

				// is an extension loaded and enabled
				$filters[] = $this->plugin_filter( null, ['method'=>'isExtension', 'args'=>'debugging'] );
				// call all extensions
				$filters[] = $this->plugin_filter( [], ['method'=>'callAllExtensions', 'args'=>'isEnabled'] );

				// getClassObject() returns $this
				$filters[] = $this->plugin_filter( null, ['method'=>'getClassObject'] );
				// getClassObject() returns extension object
				$filters[] = $this->plugin_filter( null, ['method'=>'getClassObject','args'=>'debugging'] );

				$filters[] = $this->plugin_filter( null, ['method'=>'encryption.encode','args'=>'string to encrypt'] );

				$filters[] = $this->plugin_filter( null, ['method'=>'setVariable', 'args'=>'testkey,testing'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'getVariable', 'args'=>'testkey'] );

				// session extension enabled
				$filters[] = $this->plugin_filter( null, ['method'=>['session','sessionId']] );
				$filters[] = $this->plugin_filter( null, ['method'=>'session.sessionId'] );

				$filters[] = $this->plugin_filter( [], ['method'=>'session.getSession'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'session.getSession','args'=>'testkey'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'session.getSession','index'=>'testkey'] );
				$filters[] = $this->plugin_filter( null, ['method'=>'session.isNewSession'] );

				// plugin must use \EarthAsylumConsulting\Traits\datetime;
				$filters[] = $this->plugin_filter( false, ['method'=>'getDateTime','args'=>['now', '+1 hour', 'UTC']] );
				$filters[] = $this->plugin_filter( false, ['method'=>'getDateTime'] );
				$filters[] = $this->plugin_filter( false, ['method'=>'isValidDate','args'=>wp_date('Y-m-d')] );
				$filters[] = $this->plugin_filter( false, ['method'=>'isValidTime','args'=>wp_date('H:i:s')] );
				$filters[] = $this->plugin_filter( false, ['method'=>'isValidDateTime','args'=>[wp_date('Y-m-d H:i:s'), 'm/d/Y H:i:s']] );
				$filters[] = $this->plugin_filter( false, ['method'=>'isDateTimeBetween','args'=>['now', 'yesterday', 'tomorrow']] );

				$filters[] = $this->plugin_filter( false, ['method'=>'implode_with_keys','args'=>[',',['a'=>'a_value', 'b'=>'b_value']]] );
				$filters[] = $this->plugin_filter( [], ['method'=>'explode_with_keys','args'=>[',','a=a_value,b=b_value']] );

				$filters[] = $this->plugin_filter( false, ['method'=>'logInfo','args'=>['log message', __METHOD__ ]] );

				// named filters

				$applied['encrypt_string'] 	= $this->debug_filter( 'encrypt_string', 'string to encrypt' );

				$applied['set_variable'] 	= $this->debug_filter( 'set_variable', 'testing2', 'testkey2' );
				$applied['get_variable'] 	= $this->debug_filter( 'get_variable', '', 'testkey2' );

				$applied['get_session'] 	= $this->debug_filter( 'get_session', []);
				$applied['testkey'] 		= $this->debug_filter( 'get_session', null, 'testkey' );		// use get_variable
				$applied['is_new_session'] 	= $this->debug_filter( 'is_new_session', null );
			}
			catch (\Throwable $e) {
				$response = ( new \WP_Error( 'exception', $e->getMessage() ) );
			}

			return rest_ensure_response(
				[
					$this->pluginName.' Shortcode'	=> $shortcodes,
					$this->pluginName.' Filter'		=> $filters,
					'Named Filters'					=> $applied
				]
			);
		}


		/**
		 * apply {pluginName} filter
		 *
		 * @param mixed $default default return value (if no filter)
		 * @param array $args filter arguments
		 *
		 * @return array  filter result
		 */
		private function plugin_filter($default,$args)
		{
			$result = \apply_filters( $this->pluginName, $default, $args );
			if (is_object($result)) {
				try {
					$result = (string) $result;
				} catch (\Throwable $e) {$result = '(object) '.get_class($result);}
			}
			return [ 'Arguments'=>$args, 'Result'=>$result ];
		}

		/**
		 * apply named filter
		 *
		 * @param string $filterName filter name
		 * @param array $args filter arguments
		 *
		 * @return array  filter result
		 */
		private function debug_filter($filterName,...$args)
		{
			$result = $this->apply_filters( $filterName, ...$args );
			if (is_object($result)) {
				try {
					$result = (string) $result;
				} catch (\Throwable $e) {$result = '(object) '.get_class($result);}
			}
			return [ 'Arguments'=>$args, 'Result'=>$result ];
		}
	}
}
/**
 * return a new instance of this class
 */
if (isset($this)) return new test_api_debugging_extension($this);
?>
