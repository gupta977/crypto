<?php
class Crypto_Connect
{
    private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';

    private $metamask;
    private $disconnect;
    private $connect_class;
    private $disconnect_class;
    private $enable_metamask;
    private $enable_walletconnect;

    public function __construct()
    {

        $this->metamask = crypto_get_option('metamask_label', 'crypto_login_settings', 'Connect Wallet');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_login_settings', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_login_settings', 'fl-button fl-is-info');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_login_settings', 'fl-button fl-is-danger');
        $this->enable_metamask = crypto_get_option('enable_metamask', 'crypto_login_settings', 1);

        add_shortcode('crypto-connect', array($this, 'crypto_connect_option'));
        add_action('flexi_login_form', array($this, 'crypto_connect_small_flexi'));
        add_action('woocommerce_login_form', array($this, 'crypto_connect_small_woocommerce'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));
        add_filter('crypto_settings_fields', array($this, 'add_extension'));
    }

    /*
    //add_filter flexi_settings_tabs
    public function add_tabs($new)
    {
    $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_settings', 0);
    if ("1" == $enable_addon) {

    $tabs = array(
    'login'   => __('Login', 'crypto'),

    );
    $new  = array_merge($new, $tabs);
    }
    return $new;
    }
     */

    //Add Section title
    public function add_section($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            $sections = array(
                array(
                    'id' => 'crypto_login_settings',
                    'title' => __('Web3Modal Crypto Login', 'crypto'),
                    'description' => __('Let users to connect via Metamask, WalletConnect & many more wallet', 'crypto') . "<br>" . "Project by <a target='_blank' href='" . esc_url('https://github.com/Web3Modal') . "'>Web3Modal</a>",
                    'tab' => 'login',
                ),
            );
            $new = array_merge($new, $sections);
        }
        return $new;
    }

    //Add section fields
    public function add_fields($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            $fields = array(
                'crypto_login_settings' => array(

                    array(
                        'name' => 'moralis_url',
                        'label' => __('Moralis URL', 'crypto'),
                        'description' => __('Enter Moralis API Server URL', 'crypto'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'moralis_appid',
                        'label' => __('Moralis appId', 'crypto'),
                        'description' => __('Enter Moralis application Id', 'crypto'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'moralis_chainid',
                        'label' => __('Default Network Chain ID', 'crypto'),
                        'description' => __('If specified, network wallet changes after connection. Eg. 0x89 for Matic & 0x38 for BSC', 'crypto') . " <a href='https://docs.moralis.io/moralis-server/web3-sdk/intro' target='_blank'> Reference </a>",
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'enable_metamask',
                        'label' => __('Metamask Button', 'crypto'),
                        'description' => __('Display Metamask Button', 'crypto'),
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),

                    array(
                        'name' => 'enable_flexi',
                        'label' => __('Enable at Flexi', 'crypto'),
                        'description' => __('Display connect button at Flexi login form.', 'crypto') . " <a target='_blank' href='" . esc_url('https://wordpress.org/plugins/flexi/') . "'>https://wordpress.org/plugins/flexi/</a>",
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),
                    array(
                        'name' => 'enable_woocommerce',
                        'label' => __('Enable at WooCommerce', 'crypto'),
                        'description' => __('Display connect button at WooCommmerce Login form', 'crypto') . " <a target='_blank' href='" . esc_url('https://wordpress.org/plugins/woocommerce/') . "'>WooCommerce</a>",
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),
                    array(
                        'name' => 'metamask_label',
                        'label' => __('Metamask button label', 'crypto'),
                        'description' => __('Label to display at metamask connect button', 'crypto'),
                        'size' => 20,
                        'type' => 'text',
                    ),

                    array(
                        'name' => 'disconnect_label',
                        'label' => __('Disconnect button label', 'crypto'),
                        'description' => __('Label to display at Disconnect Wallet button', 'crypto'),
                        'size' => 20,
                        'type' => 'text',
                    ),

                    array(
                        'name' => 'connect_class',
                        'label' => __('Connect button class rule', 'crypto'),
                        'description' => __('fl-button fl-is-info fl-is-rounded', 'crypto'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'disconnect_class',
                        'label' => __('Disconnect button class rule', 'crypto'),
                        'description' => __('fl-button fl-is-danger fl-is-rounded', 'crypto'),
                        'type' => 'text',
                    ),

                ),
            );
            $new = array_merge($new, $fields);
        }
        return $new;
    }

    //Add enable/disable option at extension tab
    public function add_extension($new)
    {

        $fields = array('crypto_general_login' => array(
            array(
                'name' => 'enable_crypto_login',
                'label' => __('Select login provider', 'flexi'),
                'description' => '',
                'type' => 'radio',
                'options' => array(
                    'web3modal' => __('Connect using Web3Modal', 'flexi'),
                    'moralis' => __('Connect using moralis.io API - Metamask & WalletConnect', 'flexi'),
                    'metamask' => __('Connect using Metamask without any provider', 'flexi'),
                ),
                'sanitize_callback' => 'sanitize_key',
            ),
        ));
        $new = array_merge_recursive($new, $fields);

        return $new;
    }

    public function enqueue_scripts()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            wp_register_script('crypto_connect_ajax_process', plugin_dir_url(__DIR__) . 'public/js/crypto_connect_ajax_process.js', array('jquery'), CRYPTO_VERSION);
            wp_enqueue_script('crypto_connect_ajax_process');
            wp_enqueue_script('crypto_fortmatic', plugin_dir_url(__DIR__) . 'public/js/web3modal/fortmatic.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_web3', plugin_dir_url(__DIR__) . 'public/js/web3modal/web3.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_web3-provider', plugin_dir_url(__DIR__) . 'public/js/web3modal/wallet.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_index', plugin_dir_url(__DIR__) . 'public/js/web3modal/index.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_index_min', plugin_dir_url(__DIR__) . 'public/js/web3modal/index.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_login', plugin_dir_url(__DIR__) . 'public/js/web3modal/crypto_connect_login_web3modal.js', array('jquery'), '', false);
        }
    }

    public function crypto_connect_option()
    {

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');

        if ("web3modal" == $enable_addon) {
            $put = "";
            ob_start();
            $nonce = wp_create_nonce("crypto_connect_ajax_process");
?>
<span>
    <?php
                if ($this->enable_metamask == "1") {
                ?>
    <a href="#" id="btn-login"
        class="<?php echo esc_attr($this->connect_class); ?>"><?php echo esc_attr($this->metamask); ?></a>
    <?php
                }

                ?>
    <a href="#" id="btn-logout"
        class="<?php echo esc_attr($this->disconnect_class); ?>"><?php echo esc_attr($this->disconnect); ?></a>
    <div class="fl-notification fl-is-primary fl-is-light fl-mt-1" id="flexi_notification_box">
        <button class="fl-delete" id="delete_notification"></button>
        <div id="wallet_msg">&nbsp;</div>
    </div>
</span>


<?php
            $put = ob_get_clean();

            return $put;
        }
    }

    public function crypto_connect_small_flexi()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            //Display at Flexi Form
            $enable_addon = crypto_get_option('enable_flexi', 'crypto_login_settings', 1);
            if ("1" == $enable_addon) {
                echo wp_kses_post($this->crypto_connect_option());
            }
        }
    }

    public function crypto_connect_small_woocommerce()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            //Display at WooCommerce form  
            $enable_addon_woo = crypto_get_option('enable_woocommerce', 'crypto_login_settings', 1);
            if ("1" == $enable_addon_woo) {
                echo wp_kses_post($this->crypto_connect_option());
            }
        }
    }

    public function run_script()
    {
        global $post;
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {

            //add stylesheet for post/page here...
            if (is_single() || is_page()) {
                return true;
            }
        }
        return false;
    }
}
$connect_page = new Crypto_Connect();