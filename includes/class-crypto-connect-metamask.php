<?php
class Crypto_Connect_Metamask
{
    private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';
    private $walletconnect;
    private $metamask;
    private $disconnect;
    private $connect_class;
    private $disconnect_class;
    private $enable_metamask;
    private $enable_walletconnect;

    public function __construct()
    {
        $this->metamask = crypto_get_option('metamask_label', 'crypto_login_metamask', 'Metamask');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_login_metamask', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_login_metamask', 'fl-button fl-is-info');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_login_metamask', 'fl-button fl-is-danger');

        add_shortcode('crypto-connect-metamask', array($this, 'crypto_connect_Metamask'));
        add_action('woocommerce_login_form', array($this, 'crypto_connect_Metamask_small_woocommerce'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));
        add_filter('crypto_settings_fields', array($this, 'add_extension'));

        add_filter('crypto_dashboard_tab', array($this, 'dashboard_add_tabs'));
        add_action('crypto_dashboard_tab_content', array($this, 'dashboard_add_content'));
    }


    //Add Section title
    public function add_section($new)
    {

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        //  if ("metamask" == $enable_addon) {
        $sections = array(
            array(
                'id' => 'crypto_login_metamask',
                'title' => __('Metamask Login', 'crypto'),
                'description' => __('Login with Metamask without any 3rd party provider', 'crypto') . "<br>" . "No API required<br>Shortcode eg. <code>[crypto-connect-metamask label=\"Connect to Login\" class=\"fl-button fl-is-info fl-is-light\"]</code><br>You must select provider at <a href='" . admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_general_login') . "'>Login Settings</a>. Only one provider works at a time.",
                'tab' => 'login',
            ),
        );
        $new = array_merge($new, $sections);
        //  }
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

    //Add section fields
    public function add_fields($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        //  if ("metamask" == $enable_addon) {
        $fields = array(
            'crypto_login_metamask' => array(

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
                    'name' => 'connect_class',
                    'label' => __('Connect button class rule', 'crypto'),
                    'description' => __('fl-button fl-is-info fl-is-rounded', 'crypto'),
                    'type' => 'text',
                ),


            ),
        );
        $new = array_merge($new, $fields);
        // }
        return $new;
    }

    public function enqueue_scripts()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("metamask" == $enable_addon) {
            if ($this->run_script()) {
                wp_register_script('crypto_connect_ajax_process', plugin_dir_url(__DIR__) . 'public/js/crypto_connect_ajax_process.js', array('jquery'), CRYPTO_VERSION);
                wp_enqueue_script('crypto_connect_ajax_process');
                wp_enqueue_script('crypto_login', plugin_dir_url(__DIR__) . 'public/js/metamask/crypto_connect_login_metamask.js', array('jquery'), '', false);

                wp_enqueue_script('crypto_web3', plugin_dir_url(__DIR__) . 'public/js/web3.min.js', array('jquery'), '', false);
                wp_enqueue_script('crypto_web3-provider', plugin_dir_url(__DIR__) . 'public/js/web3-provider.min.js', array('jquery'), '', false);
            }
        }
    }

    public function crypto_connect_Metamask()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("metamask" == $enable_addon) {

            if ($this->run_script()) {
                $put = "";
                ob_start();
                $nonce = wp_create_nonce("crypto_connect_Metamask_ajax_process");
?>
<a href="#" id="btn-login"
    class="<?php echo esc_attr($this->connect_class); ?>"><?php echo esc_attr($this->metamask); ?></a>
<div class="fl-notification fl-is-primary fl-is-light fl-mt-1" id="flexi_notification_box">
    <button class="fl-delete" id="delete_notification"></button>
    <div id="wallet_msg">&nbsp;</div>
</div>

<?php
                $put = ob_get_clean();

                return $put;
            }
        }
    }

    public function crypto_connect_Metamask_small_woocommerce()
    {

        //Display at WooCommerce form
        $enable_addon_woo = crypto_get_option('enable_woocommerce', 'crypto_login_metamask', 1);
        if ("1" == $enable_addon_woo) {
            echo wp_kses_post($this->crypto_connect_Metamask());
        }
    }

    public function run_script()
    {
        global $post;
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("metamask" == $enable_addon) {

            //add stylesheet for post/page here...
            if (is_single() || is_page()) {
                return true;
            }
        }
        return false;
    }

    public function dashboard_add_tabs($tabs)
    {

        $extra_tabs = array("login" => 'Login & Register');

        // combine the two arrays
        $new = array_merge($tabs, $extra_tabs);
        //crypto_log($new);
        return $new;
    }

    public function dashboard_add_content()
    {
        if (isset($_GET['tab']) && 'login' == $_GET['tab']) {
            echo wp_kses_post($this->crypto_dashboard_content());
        }
    }

    public function crypto_dashboard_content()
    {
        ob_start();
        ?>
<div class="changelog section-getting-started">
    <div class="feature-section">
        <h2>Login & Register</h2>
        <div class="wrap">
            <b>It connects your MetaMask or other crypto wallet.<br>After
                connection user automatically logged in without registration. </b>
            <br><br><a class="button button-primary"
                href="<?php echo admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_general_login'); ?>">Settings</a>
            <br><br>
            <b>Tips</b>
            <ul>
                <li>* Web3 Modal login is better to use as it has wider options. </li>
                <li>* If user already logged by traditional username & password. It will bind current wallet address. So
                    that next time same username auto logged in if same wallet is used. </li>
                <li>* 'Network Chain ID' means the crypto blockchain. Eg. Ethereum mainnet id is 1.</li>
                <li>By default public API is used in Web3 Modal. Get your own free for faster and uptime.</li>
            </ul>

        </div>
    </div>
</div>
<?php
        $content = ob_get_clean();
        return $content;
    }
}
$connect_page = new Crypto_Connect_Metamask();