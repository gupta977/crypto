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
        $this->metamask = crypto_get_option('metamask_label', 'crypto_metamask_settings', 'Metamask');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_metamask_settings', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_metamask_settings', 'fl-button fl-is-info');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_metamask_settings', 'fl-button fl-is-danger');

        add_shortcode('crypto-connect-metamask', array($this, 'crypto_connect_Metamask'));
        add_action('flexi_login_form', array($this, 'crypto_connect_Metamask_small_flexi'));
        add_action('woocommerce_login_form', array($this, 'crypto_connect_Metamask_small_woocommerce'));
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
    $enable_addon = crypto_get_option('enable_metamask_login', 'crypto_general_settings', 0);
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
        $enable_addon = crypto_get_option('enable_metamask_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {
            $sections = array(
                array(
                    'id' => 'crypto_metamask_settings',
                    'title' => __('Metamask Login', 'crypto'),
                    'description' => __('Login with Metamask without any 3rd party provider', 'crypto') . "<br>" . "No API required",
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
        $enable_addon = crypto_get_option('enable_metamask_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {
            $fields = array(
                'crypto_metamask_settings' => array(


                    array(
                        'name' => 'metamask_chainid',
                        'label' => __('Default Network Chain ID', 'crypto'),
                        'description' => __('If specified, network wallet changes after connection. Eg. 0x89 for Matic & 0x38 for BSC', 'crypto') . " <a href='#' target='_blank'> Reference </a>",
                        'type' => 'text',
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

        $enable_addon = crypto_get_option('enable_metamask_login', 'crypto_general_login', 1);
        if ("1" == $enable_addon) {

            $description = ' <a style="text-decoration: none;" href="' . admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_metamask_settings') . '"><span class="dashicons dashicons-admin-tools"></span></a>';
        } else {
            $description = '';
        }

        $fields = array('crypto_general_login' => array(
            array(
                'name' => 'enable_metamask_login',
                'label' => __('Enable Metamask Login without API', 'crypto'),
                'description' => __('Let users to connect/register with Metamask without any 3rd party', 'crypto') . ' ' . $description,
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
            wp_enqueue_script('crypto_web3', plugin_dir_url(__DIR__) . 'public/js/web3.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_web3-provider', plugin_dir_url(__DIR__) . 'public/js/web3-provider.min.js', array('jquery'), '', false);
        }
    }

    public function crypto_connect_Metamask()
    {
        crypto_set_option('enable_crypto_login', 'crypto_general_login', 0);

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

<script>
jQuery(document).ready(function() {

    jQuery("[id=delete_notification]").click(function() {
        jQuery("[id=flexi_notification_box]").fadeOut("slow");
    });

    jQuery("[id=btn-login]").click(function() {
        // alert("Login");

        login();
    });

});
if (typeof window.ethereum !== 'undefined') {
    console.log('MetaMask is installed!');

    jQuery("[id=flexi_notification_box]").hide();
} else {
    //console.log("MetaMask is not installed");
    jQuery("#flexi_notification_box").fadeIn("slow");
    jQuery("[id=wallet_msg]").append("Metamask not installed").fadeIn("normal");
}

async function login() {
    if (typeof window.ethereum !== 'undefined') {
        // Instance web3 with the provided information
        web3 = new Web3(window.ethereum);
        try {
            // Request account access
            await window.ethereum.enable();
            onInit();
            return true
        } catch (error) {
            // User denied access
            jQuery("[id=wallet_msg]").empty();
            jQuery("#flexi_notification_box").fadeIn("slow");
            jQuery("[id=wallet_msg]").append(error.message).fadeIn("normal");
            return false
        }
    }
}
async function onInit() {
    await window.ethereum.enable();
    const accounts = await window.ethereum.request({
        method: 'eth_requestAccounts'
    });
    const account = accounts[0];
    console.log(account);
    process_login_register(account);
    window.ethereum.on('accountsChanged', function(accounts) {
        // Time to reload your interface with accounts[0]!
        console.log(accounts[0])
    });
}

function create_link_crypto_connect_login(nonce, postid, method, param1, param2, param3) {

    newlink = document.createElement('a');
    newlink.innerHTML = '';
    newlink.setAttribute('id', 'crypto_connect_ajax_process');
    // newlink.setAttribute('class', 'xxx');
    newlink.setAttribute('data-nonce', nonce);
    newlink.setAttribute('data-id', postid);
    newlink.setAttribute('data-method_name', method);
    newlink.setAttribute('data-param1', param1);
    newlink.setAttribute('data-param2', param2);
    newlink.setAttribute('data-param3', param3);
    document.body.appendChild(newlink);
}

function process_login_register(curr_user) {
    //alert("register " + curr_user);
    //Javascript version to check is_user_logged_in()
    if (jQuery('body').hasClass('logged-in')) {
        // console.log("check after login");
        create_link_crypto_connect_login('<?php echo sanitize_key($nonce); ?>', '', 'check', curr_user, '', '');
        //jQuery("#crypto_connect_ajax_process").click();
        setTimeout(function() {
            jQuery('#crypto_connect_ajax_process').trigger('click');
        }, 1000);


    } else {
        // console.log("register new");
        create_link_crypto_connect_login('<?php echo sanitize_key($nonce); ?>', '', 'register', curr_user, '', '');
        //jQuery("#crypto_connect_ajax_process").click();
        setTimeout(function() {
            jQuery('#crypto_connect_ajax_process').trigger('click');
        }, 1000);

    }
}
</script>
<?php
            $put = ob_get_clean();

            return $put;
        }
    }

    public function crypto_connect_Metamask_small_flexi()
    {
        //Display at Flexi Form
        $enable_addon = crypto_get_option('enable_flexi', 'crypto_metamask_settings', 1);
        if ("1" == $enable_addon) {
            echo $this->crypto_connect_Metamask();
        }
    }

    public function crypto_connect_Metamask_small_woocommerce()
    {

        //Display at WooCommerce form
        $enable_addon_woo = crypto_get_option('enable_woocommerce', 'crypto_metamask_settings', 1);
        if ("1" == $enable_addon_woo) {
            echo $this->crypto_connect_Metamask();
        }
    }

    public function run_script()
    {
        global $post;
        $enable_addon = crypto_get_option('enable_metamask_login', 'crypto_general_login', 0);
        if ("1" == $enable_addon) {

            //add stylesheet for post/page here...
            if (is_single() || is_page()) {
                return true;
            }
        }
        return false;
    }
}
$connect_page = new Crypto_Connect_Metamask();