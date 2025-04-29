<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: Webhook debugging - debugging output through WooCommerce webhooks.
 *
 * Adds a 'Webhooks' section to the {eac}Doojigger - Debugging tab.
 *      Add a new webhook in WooCommerce -> Settings -> Advanced -> Webhooks
 *      Use the generated Delivery URL and Webhook Secret in your webhook.
 * Outputs the webhook payload to the debugging log.
 *
 * Drop this into
 *      /wp-content/themes/{your-theme}/eacDoojigger/Doolollys
 *
 * @category    WordPress Plugin
 * @package     {eac}Doojigger\Extensions
 * @author      Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright   Copyright (c) 2025 EarthAsylum Consulting <www.EarthAsylum.com>
 * @link        https://eacDoojigger.earthasylum.com/
 */

class webhooks_debugging_extension extends \EarthAsylumConsulting\abstract_extension
{
    /**
     * @var string extension version
     */
    const VERSION           = '25.0429.1';

    /**
     * @var string extension tab name
     */
    const TAB_NAME          = 'Debugging';

    /**
     * @var string|array|bool to set (or disable) default group display/switch
     */
    const ENABLE_OPTION     = 'WebHooks';


    /**
     * @var string build rest route
     */
    const API_ROUTE         = 'eac/debugging/v1';

    /**
     * @var string webhook action (order.created, order.updated, order.deleted, order.restored)
     */
    private $webhookAction;

    /**
     * @var string webhook source (origin)
     */
    private $webhookSource;


    /**
     * constructor method
     *
     * @param   object  $plugin main plugin object
     * @return  void
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin, self::ALLOW_ADMIN | self::ALLOW_CRON | self::DEFAULT_DISABLED);

        add_action('admin_init', function()
        {
            $this->registerExtension( $this->className );
            // Register plugin options when needed
            $this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
        });

        add_action( 'rest_api_init',                    array($this, 'register_api_routes') );
    }


        /**
         * initialize method - called from main plugin
         *
         * @return  void
         */
        public function initialize()
        {
            if (! $this->isEnabled('debugging')) return $this->isEnabled(false);
            return parent::initialize();
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
                '_wc_webhook_url'   => array(
                    'type'      =>  'disabled',
                    'label'     =>  'WC Delivery URL',
                    'default'   =>  home_url("/wp-json/".self::API_ROUTE."/wc-webhook"),
                    'title'     =>  'Your WooCommerce Webhook Delivery URL.',
                    'info'      =>  'The webhook end-point.<br>'.
                                    'WooCommerce &rarr; Settings &rarr; Advanced &rarr; Webhooks.'
                ),
                'debug_webhook_key' => array(
                    'type'      =>  'disabled',
                    'label'     =>  'Webhook Secret',
                    'default'   =>  hash('md5', uniqid(), false),
                    'title'     =>  'Your Webhook Secret.',
                    'info'      =>  'Used to authenticate webhook requests.',
                ),
            ]
        );
    }


    /**
     * Register a WP REST api
     *
     * @return void
     */
    public function register_api_routes($restServer)
    {
        register_rest_route( self::API_ROUTE, '/wc-webhook', array(
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'wc_webhook' ),
                    'permission_callback' => array( $this, 'wc_webhook_auth' ),
                ),
        ));
    }


    /**
     * REST API Authentication
     *
     * @param   object  $request - WP_REST_Request Request object.
     * @return  bool
     */
    public function wc_webhook_auth($rest)
    {
        if ( ($authKey = $rest->get_header( 'x-wc-webhook-signature' )) )
        {
            $hash   = $this->get_option('debug_webhook_key');
            $hash   = base64_encode(hash_hmac('sha256', $rest->get_body(), $hash, true));

            if ($hash == $authKey)
            {
                $this->webhookAction = $rest->get_header( 'x-wc-webhook-topic' );
                $origin = parse_url($rest->get_header( 'x-wc-webhook-source' ));
                $this->webhookSource = $origin['host'];
                // allow CORS origin
                $origin = $origin['scheme'].'://'.$origin['host'];
                add_filter( 'http_origin', function() use ($origin) {
                    return $origin;
                });
                add_filter( 'allowed_http_origins', function($allowed) use ($origin) {
                    $allowed[] = $origin;
                    return $allowed;
                });
                return true;
            }
        }
        else if (isset($_POST['webhook_id']))
        {
            // test ping from woo when the webhook is first created
            http_response_code(200);
            die();
        }

        http_response_code(401);
        return false;
    }


    /**
     * Debug log the webhook data
     *
     * @param   object  $rest - WP_REST_Request Request object.
     * @return  void
     */
    public function wc_webhook($rest)
    {
    //  $headers = $rest->get_headers();
        $payload = ($rest->is_json_content_type())
            ? $rest->get_json_params()
            : $rest->get_params();

        do_action( 'eacDoojigger_log_debug', $payload, $this->webhookAction.' ('.$this->webhookSource.')' );
    }
}
/**
 * return a new instance of this class
 */
return new webhooks_debugging_extension($this);
?>
