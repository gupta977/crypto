<?php
class Crypto_Connect
{
    private $help = ' <a style="text-decoration: none;" href="https://odude.com/docs/flexi-gallery/tutorial/ultimate-member-user-gallery/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';
    private $walletconnect;
    private $metamask;
    private $disconnect;
    private $connect_class;
    private $disconnect_class;
    private $enable_metamask;
    private $enable_walletconnect;

    public function __construct()
    {
        $this->walletconnect = crypto_get_option('walletconnect_label', 'crypto_login_settings', 'WalletConnect');
        $this->metamask = crypto_get_option('metamask_label', 'crypto_login_settings', 'Metamask');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_login_settings', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_login_settings', 'fl-button fl-is-info');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_login_settings', 'fl-button fl-is-danger');
        $this->enable_metamask = crypto_get_option('enable_metamask', 'crypto_login_settings', 1);
        $this->enable_walletconnect = crypto_get_option('enable_walletconnect', 'crypto_login_settings', 1);

        add_shortcode('crypto-connect', array($this, 'crypto_connect'));
        add_action('flexi_login_form', array($this, 'crypto_connect_small'));
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
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {
            $sections = array(
                array(
                    'id' => 'crypto_login_settings',
                    'title' => __('Crypto Login', 'flexi'),
                    'description' => __('Let users to connect via Metamask or WalletConnect.', 'flexi') . "<br>" . "Get API from <a target='_blank' href='" . esc_url('https://moralis.io/') . "'>https://moralis.io/</a>",
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
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {
            $fields = array(
                'crypto_login_settings' => array(

                    array(
                        'name' => 'moralis_url',
                        'label' => __('Moralis URL', 'flexi'),
                        'description' => __('Enter Moralis API Server URL', 'flexi'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'moralis_appid',
                        'label' => __('Moralis appId', 'flexi'),
                        'description' => __('Enter Moralis application Id', 'flexi'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'moralis_chainid',
                        'label' => __('Default Network Chain ID', 'flexi'),
                        'description' => __('If specified, network wallet changes after connection. Eg. 0x89 for Matic & 0x38 for BSC', 'flexi') . " <a href='https://docs.moralis.io/moralis-server/web3-sdk/intro' target='_blank'> Reference </a>",
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'enable_metamask',
                        'label' => __('Metamask Button', 'flexi'),
                        'description' => __('Display Metamask Button', 'flexi'),
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),
                    array(
                        'name' => 'enable_walletconnect',
                        'label' => __('WalletConnect Button', 'flexi'),
                        'description' => __('Display WalletConnect Button', 'flexi'),
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),
                    array(
                        'name' => 'enable_flexi',
                        'label' => __('Enable at Flexi', 'flexi'),
                        'description' => __('Display connect button at Flexi login form.', 'flexi') . " <a target='_blank' href='" . esc_url('https://wordpress.org/plugins/flexi/') . "'>https://wordpress.org/plugins/flexi/</a>",
                        'type' => 'checkbox',
                        'sanitize_callback' => 'intval',

                    ),
                    array(
                        'name' => 'metamask_label',
                        'label' => __('Metamask button label', 'flexi'),
                        'description' => __('Label to display at metamask connect button', 'flexi'),
                        'size' => 20,
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'walletconnect_label',
                        'label' => __('WalletConnect button label', 'flexi'),
                        'description' => __('Label to display at WalletConnect button', 'flexi'),
                        'size' => 20,
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'disconnect_label',
                        'label' => __('Disconnect button label', 'flexi'),
                        'description' => __('Label to display at Disconnect Wallet button', 'flexi'),
                        'size' => 20,
                        'type' => 'text',
                    ),

                    array(
                        'name' => 'connect_class',
                        'label' => __('Connect button class rule', 'flexi'),
                        'description' => __('fl-button fl-is-info fl-is-rounded', 'flexi'),
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'disconnect_class',
                        'label' => __('Disconnect button class rule', 'flexi'),
                        'description' => __('fl-button fl-is-danger fl-is-rounded', 'flexi'),
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

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {

            $description = ' <a style="text-decoration: none;" href="' . admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_login_settings') . '"><span class="dashicons dashicons-admin-tools"></span></a>';
        } else {
            $description = '';
        }

        $fields = array('crypto_general_login' => array(
            array(
                'name' => 'enable_crypto_login',
                'label' => __('Enable Crypto Login', 'flexi'),
                'description' => __('Let users to connect/register with Metamask & WalletConnect', 'flexi') . ' ' . $this->help . ' ' . $description,
                'type' => 'checkbox',
                'sanitize_callback' => 'intval',

            ),
        ));
        $new = array_merge_recursive($new, $fields);

        return $new;
    }

    public function enqueue_scripts()
    {
        if ($this->run_script()) {
            wp_register_script('crypto_connect_ajax_process', plugin_dir_url(__DIR__) . 'public/js/crypto_connect_ajax_process.js', array('jquery'), CRYPTO_VERSION);
            wp_enqueue_script('crypto_connect_ajax_process');
            wp_enqueue_script('crypto_login', plugin_dir_url(__DIR__) . 'public/js/crypto_connect_login_script.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_moralis', plugin_dir_url(__DIR__) . 'public/js/moralis.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_web3', plugin_dir_url(__DIR__) . 'public/js/web3.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_web3-provider', plugin_dir_url(__DIR__) . 'public/js/web3-provider.min.js', array('jquery'), '', false);
        }
    }

    public function crypto_connect()
    {

        if ($this->run_script()) {
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
            if ($this->enable_walletconnect == "1") {
                ?>
    <a href="#" id="btn-login_wc"
        class="<?php echo esc_attr($this->connect_class); ?>"><?php echo esc_attr($this->walletconnect); ?></a>
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

    public function crypto_connect_small()
    {
        $enable_addon = crypto_get_option('enable_flexi', 'crypto_login_settings', 1);
        if ("1" == $enable_addon) {
            echo $this->crypto_connect();
        }
    }

    public function run_script()
    {
        global $post;
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 0);
        if ("1" == $enable_addon) {

            //add stylesheet for post/page here...
            if (is_single() || is_page()) {
                if (has_shortcode($post->post_content, 'crypto-connect')) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }
}
$connect_page = new Crypto_Connect();