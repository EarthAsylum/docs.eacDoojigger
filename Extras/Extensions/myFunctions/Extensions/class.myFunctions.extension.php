<?php
/**
 * myFunctions - {eac}Doojigger for WordPress
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 * @uses		EarthAsylumConsulting\eacDoojigger
 */

namespace EarthAsylumConsulting\Extensions;

class functions_extension extends \EarthAsylumConsulting\abstract_extension
{
	/**
	 * @var string extension version
	 */
	const VERSION	= '25.0423.1';


	/**
	 * constructor method
	 *
	 * @param 	object	$plugin main plugin object
	 * @return 	void
	 */
	public function __construct($plugin)
	{
		parent::__construct($plugin, self::DEFAULT_DISABLED);

		$this->registerExtension( $this->className );	// loads on 'General' tab
		// Register plugin options when needed
		$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
	}


	/**
	 * register options on options_settings_page
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options_settings()
	{
		$this->registerExtensionOptions( $this->className,
			[
				'function_options'	=> array(
						'type'		=> 	'checkbox',
						'label'		=> 	'Options',
						'options'	=>	[
											['Load custom StyleSheet (functions.css)'	=> 'css'],
											['Load custom JavaScript (functions.js)'	=> 'js'],
											['Set uniqie visitor cookie'				=> 'visitor'],
										],
						'style'		=> 'display:block;',
						'default'	=> 	['css','js'],
						'help'		=>	'Enable (or disable) loading of custom style sheet and/or javascript; '.
										'and, optionally, set a unique visitor cookie.',
				),
			]
		);
	}


	/**
	 * initialize method - called from main plugin
	 *
	 * @return 	void
	 */
	public function initialize()
	{
		if ( ! parent::initialize() ) return; // disabled
	}


	/**
	 * Add filters and actions - called from main plugin
	 *
	 * @return	void
	 */
	public function addActionsAndFilters()
	{
		parent::addActionsAndFilters();

		/*
		 * WordPress actions & filters
		 */

		// WordPress init
	//	\add_action( 'init', 					array($this, 'wordpressInit' ) );

		// WordPress ready
	//	\add_action( 'wp', 						array($this, 'wordpressReady' ) );

		// WordPress shutdown
	//	\add_action( 'shutdown', 				array($this, 'wordpressShutdown' ) );

		// load child theme
	//	\add_action( 'wp_enqueue_scripts', 		array($this, 'loadChildTheme') );

		// add custom style sheet and javascript
		\add_action( 'wp_enqueue_scripts', 		array($this, 'enqueueScripts') );

		// when theme is loaded...
	//	\add_action( 'after_setup_theme', 		array($this, 'afterThemeSetup') );

		// Page Header
	//	\add_action( 'wp_head',					array($this, 'pageHeader') );

		// Page Footer
	//	\add_action( 'wp_footer',				array($this, 'pageFooter') );

		// post content
	//	\add_filter( 'the_content', 			array($this, 'postContent' ) );

		/*
		 * {eac}Doojigger actions & filters
		 */

		if ( $this->is_option('function_options','visitor') )
		{
			// append unique id to visitor id
			$this->add_filter( 'set_visitor_id',		function($id)
				{
					return  $id.':'.$this->plugin->createUniqueId(); // eacDoojiggerid = sha1($id)
				}
			);
			// and set time (days) period of cookie
			$this->add_filter( 'enable_visitor_cookie',	function($days)
				{
					return 90;
				}
			);
		}

		// when eacDoojigger is ready
	//	$this->add_action( 'ready', 					array($this, 'eacDoojiggerReady' ) );

		// do this daily (or 'hourly' on the hour or 'weekly' at start_of_week)
	//	$this->plugin->add_cron_task( 'daily', 			array($this, 'eacDoojiggerEvent') );
	}


	/**
	 * Add shortcodes - called from main plugin
	 *
	 * @return	void
	 */
	public function addShortcodes()
	{
		parent::addShortcodes();
	}


	/**
	 * WP init
	 *
	 * @return	void
	 */
	public function wordpressInit()
	{
	}


	/**
	 * WP ready
	 *
	 * @return	void
	 */
	public function wordpressReady()
	{
	}


	/**
	 * WP shutdown
	 *
	 * @return	void
	 */
	public function wordpressShutdown()
	{
	}


	/**
	 * wp_enqueue_scripts - load child theme with parent
	 *
	 * @return	void
	 */
	public function loadChildTheme()
	{
	/*
		$parenthandle = 'parent-theme-name';
		$childhandle  = 'child-theme-name';
		$theme = wp_get_theme();
		wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css',
			array(),  // if the parent theme code has a dependency, copy it to here
			$theme->parent()->get('Version') 	// if parent has Version in the style header
		);
		wp_enqueue_style( $childhandle, get_stylesheet_uri(),
			array( $parenthandle ),
			$theme->get('Version') 				// if child has Version in the style header
		);
	*/
	}


	/**
	 * wp_enqueue_scripts - enqueue custom style sheet and javascript
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{
		if ( $this->is_option('function_options','css') )
		{
			$custFile = '/css/functions.css';
			if (file_exists(dirname(__DIR__).$custFile))
			{
				wp_register_style('myFunctions', plugins_url($custFile,__DIR__));
				wp_enqueue_style('myFunctions');
			}
		}
		if ( $this->is_option('function_options','js') )
		{
			$custFile = '/js/functions.js';
			if (file_exists(dirname(__DIR__).$custFile))
			{
				wp_register_script('myFunctions', plugins_url($custFile,__DIR__));
				wp_enqueue_script('myFunctions');
			}
		}
	}


	/**
	 * after_setup_theme - after the theme is setup, do stuff
	 *
	 * @return	void
	 */
	public function afterThemeSetup()
	{
		/* post formats */
		//add_theme_support( 'post-formats', array( 'aside', 'quote' ) );

		/* post thumbnails */
		//add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );

		/* HTML5 */
		//add_theme_support( 'html5' );

		/* automatic feed links */
		//add_theme_support( 'automatic-feed-links' );

		/* add theme support for WooCommerce */
		//\add_theme_support( 'woocommerce', array(
		//	'thumbnail_image_width' => 300,
		//	'single_image_width'	=> 600,
		//	'product_grid'			=> array(
		//		'default_rows'	  => 2,
		//		'min_rows'		  => 1,
		//		'max_rows'		  => 8,
		//		'default_columns' => 7,
		//		'min_columns'	  => 1,
		//		'max_columns'	  => 8,
		//	))
		//);
		// \add_theme_support( 'wc-product-gallery-zoom' );
		// \add_theme_support( 'wc-product-gallery-lightbox' );
		// \add_theme_support( 'wc-product-gallery-slider' );
	}


	/**
	 * wp_head - when building page <head>
	 *
	 * @return	void
	 */
	public function pageHeader()
	{
		// print header output
	}


	/**
	 * wp_footer - when building page footer (before </body>
	 *
	 * @return	void
	 */
	public function pageFooter()
	{
		// print footer output
	}


	/**
	 * the_content
	 *
	 * @param string $content the content
	 * @return	string the content
	 */
	public function postContent($content)
	{
		return $content;
	}


	/**
	 * eacDoojigger ready
	 *
	 * @return	void
	 */
	public function eacDoojiggerReady()
	{
	}


	/**
	 * eacDoojigger scheduled event
	 *
	 * @return	void
	 */
	public function eacDoojiggerEvent()
	{
	}


	/**
	 * version updated
	 *
	 * @param	string	$curVersion currently installed version number
	 * @param	string	$newVersion version being installed/updated
	 * @return	bool
	 */
	public function adminVersionUpdate($curVersion,$newVersion)
	{
	}
}

/**
 * return a new instance of this class
 */
return new functions_extension($this);
?>
