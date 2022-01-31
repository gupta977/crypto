<?php
class Crypto_Connect
{
    private $help = ' <a style="text-decoration: none;" href="https://odude.com/docs/flexi-gallery/tutorial/ultimate-member-user-gallery/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';
    private  $walletconnect;
    private $metamask;
    private $disconnect;
    private $connect_class;
    private $disconnect_class;

    public function __construct()
    {
        $this->walletconnect = crypto_get_option('walletconnect_label', 'crypto_login_settings', 'WalletConnect');
        $this->metamask = crypto_get_option('metamask_label', 'crypto_login_settings', 'Metamask');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_login_settings', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_login_settings', 'fl-button fl-is-info fl-is-rounded');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_login_settings', 'fl-button fl-is-danger fl-is-rounded');

        add_shortcode('crypto-connect', array($this, 'crypto_connect'));
        add_action('flexi_login_form', array($this, 'login_extra_note'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));
        add_filter('crypto_settings_fields', array($this, 'add_extension'));
    }


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

    //Add Section title
    public function add_section($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_settings', 0);
        if ("1" == $enable_addon) {
            $sections = array(
                array(
                    'id'          => 'crypto_login_settings',
                    'title'       => __('Crypto Login', 'flexi'),
                    'description' => __('Let users to connect via Metamask or WalletConnect.', 'flexi') . "<br>" . "Get API from <a target='_blank' href='" . esc_url('https://moralis.io/') . "'>https://moralis.io/</a>",
                    'tab'         => 'login',
                ),
            );
            $new = array_merge($new, $sections);
        }
        return $new;
    }

    //Add section fields
    public function add_fields($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_settings', 0);
        if ("1" == $enable_addon) {
            $fields = array(
                'crypto_login_settings' => array(

                    array(
                        'name'              => 'moralis_url',
                        'label'             => __('Moralis URL', 'flexi'),
                        'description'       => __('Enter Moralis API Server URL', 'flexi'),
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => 'moralis_appid',
                        'label'             => __('Moralis appId', 'flexi'),
                        'description'       => __('Enter Moralis application Id', 'flexi'),
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => 'metamask_label',
                        'label'             => __('Metamask button label', 'flexi'),
                        'description'       => __('Label to display at metamask connect button', 'flexi'),
                        'size' => 20,
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => 'walletconnect_label',
                        'label'             => __('WalletConnect button label', 'flexi'),
                        'description'       => __('Label to display at WalletConnect button', 'flexi'),
                        'size' => 20,
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => 'disconnect_label',
                        'label'             => __('Disconnect button label', 'flexi'),
                        'description'       => __('Label to display at Disconnect Wallet button', 'flexi'),
                        'size' => 20,
                        'type'              => 'text',
                    ),

                    array(
                        'name'              => 'connect_class',
                        'label'             => __('Connect button class rule', 'flexi'),
                        'description'       => __('fl-button fl-is-info fl-is-rounded', 'flexi'),
                        'type'              => 'text',
                    ),
                    array(
                        'name'              => 'disconnect_class',
                        'label'             => __('Disconnect button class rule', 'flexi'),
                        'description'       => __('fl-button fl-is-danger fl-is-rounded', 'flexi'),
                        'type'              => 'text',
                    ),


                )
            );
            $new = array_merge($new, $fields);
        }
        return $new;
    }

    //Add enable/disable option at extension tab
    public function add_extension($new)
    {

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_settings', 0);
        if ("1" == $enable_addon) {

            $description = ' <a style="text-decoration: none;" href="' . admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_login_settings') . '"><span class="dashicons dashicons-admin-tools"></span></a>';
        } else {
            $description = '';
        }

        $fields = array('crypto_general_settings' => array(
            array(
                'name'              => 'enable_crypto_login',
                'label'             => __('Enable Crypto Login', 'flexi'),
                'description'       => __('Let users to connect/register with Metamask & WalletConnect', 'flexi') . ' ' . $this->help . ' ' . $description,
                'type'              => 'checkbox',
                'sanitize_callback' => 'intval',

            ),
        ),);
        $new = array_merge_recursive($new, $fields);

        return $new;
    }


    public function enqueue_scripts()
    {
        wp_enqueue_script('crypto_login', plugin_dir_url(__DIR__) . 'public/js/crypto_connectlogin_script.js', array('jquery'), '', false);
        wp_enqueue_script('crypto_moralis', 'https://unpkg.com/moralis@latest/dist/moralis.js', array('jquery'), '', false);
        wp_enqueue_script('crypto_web3', 'https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js', array('jquery'), '', false);
        wp_enqueue_script('crypto_web3-provider', 'https://github.com/WalletConnect/walletconnect-monorepo/releases/download/1.4.1/web3-provider.min.js', array('jquery'), '', false);
    }

    public function login_extra_note()
    {

        echo $this->crypto_connect('');
    }

    public function crypto_connect($params)
    {
        $put   = "";
        ob_start();
        $nonce = wp_create_nonce("crypto_connect_ajax_process");

?>
        <span>
            <a href="#" id="btn-login" class="<?php echo $this->connect_class; ?>"><?php echo $this->metamask; ?></a>
            <a href="#" id="btn-login_wc" class="<?php echo $this->connect_class; ?>"><?php echo $this->walletconnect; ?></a>
            <a href="#" id="btn-logout" class="<?php echo $this->disconnect_class; ?>"><?php echo $this->disconnect; ?></a>
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
$connect_page = new Crypto_Connect();
