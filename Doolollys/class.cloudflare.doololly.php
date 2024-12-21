<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: cloudflare_extension - enable Cloudflare API
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Extensions
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2024 EarthAsylum Consulting <www.EarthAsylum.com>
 */

class cloudflare_extension extends \EarthAsylumConsulting\abstract_extension
{
	/**
	 * @var string extension version
	 */
	const VERSION			= '24.1221.1';

	/**
	 * @var string|array|bool to set (or disable) default group display/switch
	 */
	const ENABLE_OPTION		= [
		'label' 	=>	"<abbr title='With your Cloudflare account information, we can automatically purge ".
						 "the Cloudflare cache when purging the local WordPress caches.'>Cloudflare Extension</abbr>",
	];

	/**
	 * @var string cloudflare api url
	 */
	private $cloudflare_url;

	/**
	 * @var string cloudflare zone
	 */
	private $cloudflare_zone;

	/**
	 * @var string cloudflare api key
	 */
	private $cloudflare_key;

	/**
	 * @var string cloudflare account email
	 */
	private $cloudflare_email;


	/**
	 * constructor method
	 *
	 * @param	object	$plugin main plugin object
	 */
	public function __construct($plugin)
	{
		parent::__construct($plugin, self::ALLOW_ALL|self::DEFAULT_DISABLED);

		if ($this->is_admin())
		{
		//	$this->registerExtension( $this->className );
			// Register plugin options when needed
			$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
			// Add contextual help
			$this->add_action( 'options_settings_help', array($this, 'admin_options_help') );
		}
	}


	/**
	 * register options on options_settings_page
	 *
	 * @access public
	 */
	public function admin_options_settings()
	{
	//	$this->registerExtensionOptions( $this->className,
		$this->registerExtension( $this->className,
			[
				'cloudflare_email'	=> array(
						'type'		=>	'text',
						'label'		=>	'Account Email',
						'info'		=>	'Your Cloudflare account email address.',
						'default'	=>	get_bloginfo('admin_email'),
				),
				'cloudflare_zone'	=> array(
						'type'		=>	'text',
						'label'		=>	'Domain Zone',
						'info'		=>	'Your Cloudflare zone for this domain.'.
										'<br><small>From your <em>Account Home</em>, select <em>Copy zone ID</em> from the drop-down next to the domain name.</small>',
				),
				'cloudflare_key'	=> array(
						'type'		=>	'text',
						'label'		=>	'Global API Key',
						'info'		=>	'Your Cloudflare global API key'.
										'<br><small>From your <em>Account Profile</em> &rarr; API Tokens &rarr; API Keys &rarr; Global API Key</small>',
				),
				'cloudflare_options'=> array(
						'type'		=>	'switch',
						'label'		=>	'Cache Optimization',
						'options'	=>	[
							"Caching for WP" => 'cache',
						],
						'default'	=>	['cache'],
						'info'		=>	'Set platform=wordpress on cf-edge-cache header',
				),
			]
		);
	}


	/**
	 * Add help tab on admin page
	 *
	 */
	public function admin_options_help()
	{
		if (!$this->plugin->isSettingsPage('General')) return;
	}


	/**
	 * initialize method - called from main plugin
	 *
	 */
	public function initialize()
	{
		if ( ! parent::initialize() ) return; // disabled

		// check for required settings
		if ( !($this->cloudflare_zone  = $this->get_option('cloudflare_zone')) ||
			 !($this->cloudflare_key   = $this->get_option('cloudflare_key'))  ||
			 !($this->cloudflare_email = $this->get_option('cloudflare_email'))
		) {
			return $this->isEnabled(false);
		}
		$this->cloudflare_url = sprintf("https://api.cloudflare.com/client/v4/zones/%s/",$this->cloudflare_zone);
	}


	/**
	 * Add filters and actions - called from main plugin
	 *
	 */
	public function addActionsAndFilters()
	{
		if ($this->is_option('cloudflare_options','cache')) {
			add_filter('wp_headers',		array($this, 'cloudflare_cache'),100,1);
		}

		$purge_actions = array(
			'eacDoojigger_after_flush_caches',
			'switch_theme',						// Switch theme
			'customize_save_after',				// Customizer
			'deleted_post',						// Delete a post
			'delete_attachment',				// Delete/replace an attachment
			'transition_post_status',			// When published
			'transition_comment_status',		// When approved
			'comment_post',						// When added
		);
		foreach ($purge_actions as $action) {
			add_action($action,			array($this,'cloudflare_purge'),100);
		}
	}


	/**
	 * Enable the cloudflare cache
	 *
	 * @param array $headers http headers
	 */
	public function cloudflare_cache($headers): array
	{
		$useCache = (!isset($headers['Cache-Control']) || !str_contains($headers['Cache-Control'],'no-cache'));
		if (apply_filters('cloudflare_use_cache', $useCache && !is_user_logged_in())) {
			$headers['cf-edge-cache'] = 'cache,platform=wordpress';
		} else {
			$headers['cf-edge-cache'] = 'no-cache';
		}
		return $headers;
	}


	/**
	 * Purge the cloudflare cache
	 *
	 */
	public function cloudflare_purge()
	{
		$result = wp_remote_post($this->cloudflare_url . "purge_cache",
				[
					'headers'	=> [
						'Accept'		=> 'application/json',
						'X-Auth-Key'	=> $this->cloudflare_key,
						'X-Auth-Email'	=> $this->cloudflare_email,
					],
					'body'		=> wp_json_encode([ 'purge_everything' => true ])
				]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );
		if ($result['success']) {
			$this->add_admin_notice('The Cloudflare cache has been purged','success');
		} else {
			$this->add_admin_notice("Cloudflare Cache: ".$result['errors'][0]['message'],'error');
		}
	}
}
/**
* return a new instance of this class
*/
if (isset($this)) return new cloudflare_extension($this);
?>
