<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: cloudflare_extension - enable Cloudflare API
 *
 * Provides limited control and cache purging when hosting your site behind CloudFlare.
 * 1. Override the browser cache time-to-live.
 * 2. Set 'cf-edge-cache' header to trigger APO or custom rules.
 *      custom rules can look for this header set to 'cache,platform=wordpress' or 'no-cache' or not set.
 * 3. Add 'Cache-Tag' header with mime type that can be used for selective cache purge.
 * 4. Set certain cloudflare toggle options directly from WordPress.
 * 5. Automatically purge cloudflare cache (by host name).
 *
 * Filters:
 * 1. 'cloudflare_purge_everything_actions' (array) - filter actions that trigger a cache purge.
 * 2. 'cloudflare_use_cache' (bool) - override `cf-edge-cache` setting (cache/no-cache).
 *
 * Recommended for free plans. Paid plans should use the official Cloudflare plugin with
 * Automatic Platform Optimization (APO).
 *
 * @see https://wordpress.org/plugins/cloudflare/
 * @see https://developers.cloudflare.com/automatic-platform-optimization/
 * @see https://blog.cloudflare.com/building-automatic-platform-optimization-for-wordpress-using-cloudflare-workers/
 *
 * @category    WordPress Plugin
 * @package     {eac}Doojigger\Extensions
 * @author      Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright   Copyright (c) 2025 EarthAsylum Consulting <www.EarthAsylum.com>
 */

class cloudflare_extension extends \EarthAsylumConsulting\abstract_extension
{
    /**
     * @var string extension version
     */
    const VERSION           = '25.0528.1';

    /**
     * @var string to set default tab name
     */
    const TAB_NAME          = 'CloudFlare';

    /**
     * @var string|array|bool to set (or disable) default group display/switch
     *      false       disable the 'Enabled'' option for this group
     *      string      the label for the 'Enabled' option
     *      array       override options for the 'Enabled' option (label,help,title,info, etc.)
     */
    const ENABLE_OPTION     = [
        'label'     =>  "<abbr title='With your Cloudflare account information, we can set certain common Cloudflare options as well as ".
                         "automatically purge the Cloudflare cache when purging the local WordPress caches.'>Cloudflare</abbr>",
    ];


    /**
     * @var array Cloudflare on/off settings
     * @see https://cfapi.centminmod.com/#zone-settings-get-development-mode-setting
     * @see https://developers.cloudflare.com/api/resources/zones/subresources/settings/models/waf/#(schema)%20%3E%20(property)%20value
     */
    const CLOUDFLARE_OPTIONS = [
    /*
        "<abbr title=''>".
            "</abbr>"                           => '',
    */
        "<abbr title='Speed Brain speeds up page load times by leveraging the Speculation Rules API. This instructs browsers to make speculative prefetch requests as a way to speed up next page navigation loading time.'>".
            "Speed Brain</abbr>"                => 'speed_brain',

        "<abbr title='Prioritizes your website content (text, images, fonts, and more) by deferring the loading of all of your JavaScript until after rendering.'>".
            "Rocket Loader</abbr>"              => 'rocket_loader',

        "<abbr title='When the client requesting an asset supports the Brotli compression algorithm, Cloudflare will serve a Brotli compressed version of the asset.'>".
            "Brotli Compression</abbr>"         => 'brotli',

        "<abbr title='Reply to all requests for URLs that use http with a 301 redirect to the equivalent https URL.'>".
            "Always Use Https</abbr>"           => 'always_use_https',

        "<abbr title='Enable the Automatic HTTPS Rewrites feature for this zone.'>".
            "Automatic Https Rewrites</abbr>"   => 'automatic_https_rewrites',

        "<abbr title='Browser Integrity Check looks for common HTTP headers abused most commonly by spammers and denies access to your page. It will also challenge visitors that do not have a user agent or a non standard user agent.'>".
            "Browser Check</abbr>"              => 'browser_check',

        "<abbr title='Reordering of query strings. When query strings have the same structure, caching improves.'>".
            "Sort Query String For Cache</abbr>"=> 'sort_query_string_for_cache',

        "<abbr title='Cloudflare will attempt to speed up overall page loads by serving 103 responses with Link headers from the final response.'>".
            "Early Hints</abbr>"                => 'early_hints',

        "<abbr title='Cloudflare will prefetch any URLs that are included in the response headers. (Enterprise)'>".
            "Prefetch Preload</abbr>"           => 'prefetch_preload',

        "<abbr title='Provides on-demand resizing, conversion and optimisation for images served through Cloudflare.'>".
            "Image Resizing</abbr>"             => 'image_resizing',

        "<abbr title='When the client requesting the image supports the WebP image codec, and WebP offers a performance advantage over the original image format, Cloudflare will serve a WebP version of the original image.'>".
            "WebP Image Codec</abbr>"           => 'webp',

        "<abbr title='Optimises the delivery of resources served through HTTP/2 to improve page load performance.'>".
            "HTTP/2 Edge Prioritization</abbr>" => 'h2_prioritization',

        "<abbr title='Encrypt email adresses on your web page from bots, while keeping them visible to humans.'>".
            "Email Obfuscation</abbr>"          => 'email_obfuscation',

        "<abbr title='Ensures that other sites cannot suck up your bandwidth by building pages that use images hosted on your site.'>".
            "Hotlink Protection</abbr>"         => 'hotlink_protection',

        "<abbr title='Enable IP Geolocation to have Cloudflare geolocate visitors to your website and pass the country code to you.'>".
            "IP Geolocation</abbr>"             => 'ip_geolocation',

        "<abbr title='Bypass the Cloudflare accelerated cache. Development mode will last for 3 hours and then automatically toggle off.'>".
            "Development Mode</abbr>"           => 'development_mode',
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
     * @var string cloudflare settings
     */
    private $cloudflare_settings;


    /**
     * constructor method
     *
     * @param   object  $plugin main plugin object
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin, self::ALLOW_ALL|self::ALLOW_NON_PHP|self::DEFAULT_DISABLED);

        add_action('admin_init', function()
        {
            $this->registerExtension( $this->className );
            // Register plugin options when needed
            $this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
            // Add contextual help
            $this->add_action( 'options_settings_help', array($this, 'admin_options_help') );
        },50);  // <- tab at the right end
    }


    /**
     * initialize method - called from main plugin
     *
     */
    public function initialize()
    {
        if ( ! parent::initialize() ) return; // disabled

        // check for required settings
        if (!$this->get_cf_auth()) {
            return $this->isEnabled(false);
        }
        $this->cloudflare_url = sprintf("https://api.cloudflare.com/client/v4/zones/%s/",$this->cloudflare_zone);
    }


    /**
     * register options on options_settings_page
     *
     * @access public
     */
    public function admin_options_settings()
    {
        $this->registerExtensionOptions( $this->className,
            [
                'cloudflare_email'  => array(
                        'type'      =>  'text',
                        'label'     =>  'Account Email',
                        'info'      =>  'Your Cloudflare account email address.',
                        'default'   =>  get_bloginfo('admin_email'),
                ),
                'cloudflare_key'    => array(
                        'type'      =>  'text',
                        'label'     =>  'Global API Key',
                        'info'      =>  'Your Cloudflare global API key.'.
                                        '<br><small>From your <em>Account Profile</em> &rarr; API Tokens &rarr; API Keys &rarr; Global API Key</small>',
                ),
            ]
        );

        if ($this->get_cf_auth())
        {
            $this->get_cf_options();
            $this->update_option('cloudflare_cachettl',(string)$this->cloudflare_settings['browser_cache_ttl']);
            $this->registerExtensionOptions( $this->className,
            [
                'cloudflare_zone'   => array(
                        'type'      =>  'select',
                        'label'     =>  'Domain Zone',
                        'options'   =>  $this->get_zone_list(),
                        'info'      =>  'Your Cloudflare zone for this domain.',
                ),
                'cloudflare_cachettl'=> array(
                        'type'      =>  'select',
                        'label'     =>  'Browser Cache Time-To-Live',
                        'options'   => [
                            'Respect Existing Headers'  => 0,
                            '2 minutes'                 => 2*MINUTE_IN_SECONDS,
                            '5 minutes'                 => 5*MINUTE_IN_SECONDS,
                            '20 minutes'                => 20*MINUTE_IN_SECONDS,
                            '30 minutes'                => 30*MINUTE_IN_SECONDS,
                            '1 hour'                    => 1*HOUR_IN_SECONDS,
                            '2 hours'                   => 2*HOUR_IN_SECONDS,
                            '3 hours'                   => 3*HOUR_IN_SECONDS,
                            '4 hours'                   => 4*HOUR_IN_SECONDS,
                            '5 hours'                   => 5*HOUR_IN_SECONDS,
                            '8 hours'                   => 8*HOUR_IN_SECONDS,
                            '12 hours'                  => 12*HOUR_IN_SECONDS,
                            '16 hours'                  => 16*HOUR_IN_SECONDS,
                            '20 hours'                  => 20*HOUR_IN_SECONDS,
                            '1 day'                     => 1*DAY_IN_SECONDS,
                            '2 days'                    => 2*DAY_IN_SECONDS,
                            '3 days'                    => 3*DAY_IN_SECONDS,
                            '4 days'                    => 4*DAY_IN_SECONDS,
                            '5 days'                    => 5*DAY_IN_SECONDS,
                            '8 days'                    => 8*DAY_IN_SECONDS,
                            '16 days'                   => 16*DAY_IN_SECONDS,
                            '24 days'                   => 24*DAY_IN_SECONDS,
                            '1 month'                   => 1*MONTH_IN_SECONDS,
                            '2 months'                  => 2*MONTH_IN_SECONDS,
                            '6 months'                  => 6*MONTH_IN_SECONDS,
                            '1 year'                    => 1*YEAR_IN_SECONDS,
                        ],
                        'info'      =>  'Controls how long resources cached by client browsers remain valid.',
                        'validate'  =>  [$this,'set_cache_ttl'],
                ),
                'cloudflare_options'=> array(
                        'type'      =>  'switch',
                        'label'     =>  'CloudFlare Options',
                        'options'   =>  array_merge(
                            ["<abbr title='Set cf-edge-cache header for use with Automatic Platform Optimization or custom rules.'>Caching for WP APO</abbr>" => 'wp_edge_cache'],
                            array_filter(self::CLOUDFLARE_OPTIONS, function($v) {return isset($this->cloudflare_settings[$v]);})
                        ),
                        'info'      =>  'Enable/disable certain CloudFlare options.',
                        'validate'  =>  [$this,'set_cf_settings'],
                ),
            ],
            );
        }
    }


    /**
     * Add help tab on admin page
     *
     */
    public function admin_options_help()
    {
        if (!$this->plugin->isSettingsPage(self::TAB_NAME)) return;
    }


    /**
     * Add filters and actions - called from main plugin
     *
     */
    public function addActionsAndFilters()
    {
        if ($this->is_option('cloudflare_options','wp_edge_cache'))
        {
            // do this late
            add_filter('wp_headers',            array($this, 'cloudflare_wp_edge_cache'),900,1);
            add_filter('nocache_headers',       function($headers)
            {
                $headers['cf-edge-cache'] = 'no-cache';
                return $headers;
            });
        }

        $this->add_filter('after_flush_caches', array($this,'cloudflare_purge'),100,1);

        $purge_actions = array(
            'switch_theme',                     // Switch theme
            'customize_save_after',             // Customizer
        //    'edit_post',                        // Edit a post
        //    'deleted_post',                     // Delete a post
        //    'delete_attachment',                // Delete/replace an attachment
        //    'transition_post_status',           // When published
            'clean_post_cache',                 // When a post changes
            'clean_page_cache',                 // When a page changes
            'transition_comment_status',        // When approved
            'comment_post',                     // When added
        /*
            'autoptimize_action_cachepurged'    // Autoptimize plugin
            'cache_enabler_clear_site_cache'    // Cache Enabler plugin
            'w3tc_flush_all'                    // W3 Total Cache plugin
            'wpo_cache_flush'                   // WP Optimize plugin
            'after_rocket_clean_domain'         // WP Rocket plugin
            'wp_cache_cleared'                  // WP Super Cache plugin
        */
        );

        /**
         * filter 'cloudflare_purge_everything_actions' (same as cloudflare plugin)
         * @param array actions to trigger cache purge
         */
        $purge_actions = apply_filters('cloudflare_purge_everything_actions', $purge_actions);

        foreach ($purge_actions as $action) {   // no arguments
            add_action($action,                 array($this,'cloudflare_purge'),100,0);
        }
    }


    /**
     * get/set cloudflare auth
     *
     */
    public function get_cf_auth(): bool
    {
        // check for required settings
        if ( (!$this->cloudflare_key   && !($this->cloudflare_key   = $this->get_option('cloudflare_key'))) ) {
            return false;
        }
        if ( (!$this->cloudflare_email && !($this->cloudflare_email = $this->get_option('cloudflare_email'))) ) {
            return false;
        }
        $this->cloudflare_zone  = $this->get_option('cloudflare_zone');
        return true;
    }


    /**
     * Get the cloudflare cache time
     *
     */
    public function get_zone_list(): array
    {
        $zones = [];
        if ($this->get_cf_auth())
        {
            $cloudflare_url = sprintf( 'https://api.cloudflare.com/client/v4/zones?page=%s&per_page=%s', 1, 50 );
            $result = wp_remote_get($cloudflare_url,
                [
                    'headers'   => $this->cloudflare_headers(),
                ]
            );
            $result = json_decode( wp_remote_retrieve_body($result), true );
            if ($result['success']) {
                foreach ( $result['result'] as $list ) {
                    if ( isset( $list['name'],$list['id'] ) ) {
                        $zones[ $list['name'] ] = $list['id'];
                    }
                }
            }
        }
        return $zones;
    }


    /**
     * Set the cloudflare browser or edge cache time
     *
     */
    public function set_cache_ttl($value, $fieldName, $metaData, $priorValue)
    {
        if ($value == $priorValue) return;

        if ($this->get_cf_auth() && $this->cloudflare_url)
        {
            $this->set_cf_setting('browser_cache_ttl', (int)$value);
        }

        return $value;
    }


    /**
     * Get/set our cloudflare options
     *
     */
    public function get_cf_options(): array
    {
        $options    = (isset($_POST,$_POST['cloudflare_options']))
            ? $_POST['cloudflare_options']
            : $this->get_option('cloudflare_options');

        if ($this->get_cf_auth() && $this->cloudflare_url)
        {
            $settings = $this->get_cf_settings();
            $options = (is_array($options) && in_array('wp_edge_cache',$options)) ? ['wp_edge_cache'] : [];
            foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
                if (isset($settings[$id]) && $settings[$id] == 'on') {
                    $options[] = $id;
                }
            }
        }

        $this->update_option('cloudflare_options',$options);
        return (array)$options;
    }


    /**
     * Get all settings from cloudflare
     *
     */
    private function get_cf_settings(): array
    {
        $settings = [];
        $result = wp_remote_get($this->cloudflare_url . "settings",
            [
                'headers'   => $this->cloudflare_headers(),
            ]
        );

        $result = json_decode( wp_remote_retrieve_body($result), true );
        if ($result['success']) {
            //$this->add_admin_notice("<pre>CF settings ".var_export($result['result'],true)."</pre>");
            foreach ( $result['result'] as $list ) {
                if (isset($list['value'],$list['id'])) {
                    if ($list['editable']) $settings[ $list['id'] ] = $list['value'];
                }
            }
        } else {
            $this->add_admin_notice('Unable to retrieve Cloudflare settings.','error');
        }

        $this->cloudflare_settings = $settings;
        return (array)$settings;
    }


    /**
     * Set the cloudflare options
     *
     */
    public function set_cf_settings($value, $fieldName, $metaData, $priorValue)
    {
        if ($value === $priorValue) return $value;

        if (!is_array($value)) $value = [];
        if (!is_array($priorValue)) $priorValue = [];

        if ($this->get_cf_auth() && $this->cloudflare_url)
        {
            foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
                if ( (in_array($id,$value) && !in_array($id,$priorValue))
                ||   (!in_array($id,$value) && in_array($id,$priorValue))
                ) {
                    $this->set_cf_setting($id, in_array($id,$value) ? 'on' : 'off');
                }
            }
        }

        return (array)$value;
    }


    /**
     * Set a single cloudflare option
     *
     */
    private function set_cf_setting($option, $value)
    {
        $result = wp_remote_post($this->cloudflare_url . "settings/{$option}",
            [
                'method'    => 'PATCH',
                'headers'   => $this->cloudflare_headers(),
                'body'      => wp_json_encode([ 'value' => $value ])
            ]
        );

        $result = json_decode( wp_remote_retrieve_body($result), true );
        if (!$result['success']) {
            $this->add_admin_notice("Cloudflare {$option}: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
            return false;
        }

        return true;
    }


    /**
     * Eligible/Ineligible for cloudflare caching
     *
     * @param array $headers http headers
     */
    public function cloudflare_wp_edge_cache($headers)
    {
        /**
         * filter 'cloudflare_use_cache' - override cache setting (same as cloudflare plugin)
         * @param bool enable caching
         */
        if (apply_filters('cloudflare_use_cache', $this->is_cacheable($headers))) {
            $headers['cf-edge-cache']       = 'cache,platform=wordpress';
            $headers['Cache-Tag']           = $this->get_mime_type($headers);
        } else {
            $headers['cf-edge-cache']       = 'no-cache';
        }
        return $headers;
    }


    /**
     * Eligible/Ineligible for cloudflare caching
     *
     * @todo - check cookies and query parameters ?
     *
     * @param array $headers http headers
     */
    protected function is_cacheable($headers)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
           return false;
        }
        if (http_response_code() != 200) {
           return false;
        }
        if ($this->doing_ajax() || is_user_logged_in()) {
           return false;
        }
        if (class_exists('woocommerce') &&
            (is_cart() || is_checkout() || is_order_received_page() || is_account_page())) {
            return false;
        }
        $checkHeaders = array_merge(
            $this->explode_with_keys(':',headers_list()),
            (array)$headers
        );
        if (isset($checkHeaders['Cache-Control'])) {
            if (str_contains($checkHeaders['Cache-Control'],'no-cache')) {
                return false;
            }
            if (str_contains($checkHeaders['Cache-Control'],'private')) {
                return false;
            }
        }
        if (isset($checkHeaders['Pragma'])) {
            if (str_contains($checkHeaders['Pragma'],'no-cache')) {
                return false;
            }
        }
    }


    /**
     * Get the mime type for the current request
     *
     */
    protected function get_mime_type($headers,$extension='')
    {
        // get mime type from content-type header
        if (isset($headers['Content-Type']))
        {
            list($contentType,) = explode(';',$headers['Content-Type']);
            return trim($contentType);
        }

        // get mime type from request extension
        if (empty($extension) && array_key_exists('REQUEST_URI', $_SERVER))
        {
            $extension = explode('?',$_SERVER['REQUEST_URI']);
            $extension = pathinfo(trim($extension[0],'/'),PATHINFO_EXTENSION);
        }

        if ($extension)
        {
            $mime_types = array_merge(wp_get_mime_types(),[
                'json'      => 'application/json',
                'xml'       => 'application/xml',
                'rss'       => 'application/rss+xml',
            ]);
            $extensions = array_keys( $mime_types );

            foreach ( $extensions as $_extension ) {
                if ( preg_match( "/{$extension}/i", $_extension ) ) {
                    return $mime_types[ $_extension ];
                }
            }
        }

        // get mime type from WP default
        return get_option( 'html_type' );
    }

    /**
     * Purge the cloudflare cache
     *
     * @param array caches purged - only with 'after_flush_caches'
     */
    public function cloudflare_purge($caches = [])
    {
        static $onlyOnce = 0;
        if ($onlyOnce++) return $caches;

        if ($this->get_cf_auth() && $this->cloudflare_url)
        {
            $site_url = parse_url( get_site_url() );
            $site_url = trailingslashit($site_url['host'] . $site_url['path'] ?? '').'*';
            $result = wp_remote_post($this->cloudflare_url . "purge_cache",
                    [
                        'headers'   => $this->cloudflare_headers(),
                        'body'      => wp_json_encode([ "prefixes" => [$site_url] ])
                    ]
            );
            $result = json_decode( wp_remote_retrieve_body($result), true );
            if (is_array($result)) {
                if ($result['success']) {
                    $this->add_admin_notice('The Cloudflare cache for '.$site_url.' has been purged','success');
                    $caches[] = 'Cloudflare Cache';
                } else {
                    $this->add_admin_notice("Cloudflare Cache Purge: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
                }
            } else {
                $this->add_admin_notice("Cloudflare Cache Purge: failed on unknown error",'error');
            }
        }
        return $caches;
    }


    /**
     * Set the cloudflare header array
     *
     */
    public function cloudflare_headers(): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'X-Auth-Key'    => $this->cloudflare_key,
            'X-Auth-Email'  => $this->cloudflare_email,
        ];
    }
}
/**
* return a new instance of this class
*/
if (isset($this)) return new cloudflare_extension($this);
?>
