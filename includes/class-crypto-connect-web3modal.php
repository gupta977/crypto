<?php
class Crypto_Connect_Web3
{
    private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';

    private $metamask;
    private $disconnect;
    private $connect_class;
    private $disconnect_class;
    private $enable_metamask;
    private $enable_walletconnect;
    private $provider;
    private $provider_default;

    public function __construct()
    {
        $this->provider_default = "const providerOptions = {
            walletconnect: {
                package: WalletConnectProvider,
                options: {
                    // Get your infura API KEY from https://infura.io/
                    infuraId: \"8043bb2cf99347b1bfadfb233c5325c0\",
                }
            },
    
            fortmatic: {
                package: Fortmatic,
                options: {
                    // Get your api key fortmatic.com
                    key: \"pk_test_391E26A3B43A3350\"
                }
            }
        };";

        $this->metamask = crypto_get_option('metamask_label', 'crypto_login_web3', 'Connect Wallet');
        $this->disconnect = crypto_get_option('disconnect_label', 'crypto_login_web3', 'Disconnect Wallet');
        $this->connect_class = crypto_get_option('connect_class', 'crypto_login_web3', 'fl-button fl-is-info');
        $this->disconnect_class = crypto_get_option('disconnect_class', 'crypto_login_web3', 'fl-button fl-is-danger');
        $this->enable_metamask = crypto_get_option('enable_metamask', 'crypto_login_web3', 1);
        $this->provider = crypto_get_option('provider', 'crypto_login_web3', $this->provider_default);



        add_shortcode('crypto-connect', array($this, 'crypto_connect_option'));
        add_action('flexi_login_form', array($this, 'crypto_connect_small_flexi'));
        add_action('woocommerce_login_form', array($this, 'crypto_connect_small_woocommerce'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        // add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));
        add_filter('crypto_settings_fields', array($this, 'add_extension'));
        add_action('wp_head', array($this, 'crypto_head_script'));
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
        // if ("web3modal" == $enable_addon) {
        $sections = array(
            array(
                'id' => 'crypto_login_web3',
                'title' => __('Web3Modal Crypto Login', 'crypto'),
                'description' => __('Let users to connect via Metamask, WalletConnect & many more wallet', 'crypto') . "<br>" . "Project by <a target='_blank' href='" . esc_url('https://github.com/Web3Modal') . "'>Web3Modal</a><br>Shortcode eg. <code>[crypto-connect label=\"Connect to Login\" class=\"fl-button fl-is-info fl-is-light\"]</code><br>You must select provider at <a href='" . admin_url('admin.php?page=crypto_settings&tab=login&section=crypto_general_login') . "'>Login Settings</a>. Only one provider works at a time.",
                'tab' => 'login',
            ),
        );
        $new = array_merge($new, $sections);
        //   }
        return $new;
    }

    //Add section fields
    public function add_fields($new)
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        // if ("web3modal" == $enable_addon) {
        $fields = array(
            'crypto_login_web3' => array(


                array(
                    'name' => 'chainid',
                    'label' => __('Default Network Chain ID', 'crypto'),
                    'description' => __('If specified, network wallet changes notice displayed. Eg. 1 for Ethereum Mainnet & 137 for Matic', 'crypto'),
                    'type' => 'number',
                    'size' => 'small',
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
                    'label' => __('Crypto Login button label', 'crypto'),
                    'description' => __('Label to display at crypto connect button', 'crypto'),
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
                    'name' => 'provider',
                    'type' => 'textarea',
                    'size' => 'large',
                    'placeholder' => 'Leave blank for default values',
                    'label' => __('providerOptions Javascript Array', 'crypto'),
                    'description' => __('Manual javascript array based on', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/tree/master/docs/providers" target="_blank">https://github.com/Web3Modal/web3modal/tree/master/docs/providers</a>',
                ),

                array(
                    'name' => 'provider_desp',
                    'type' => 'html',
                    'label' => __('providerOptions Default Value', 'crypto'),
                    'description' => "<pre>" . $this->provider_default . "</pre>",
                ),

                array(
                    'name' => 'provider_list',
                    'label' => 'Includes related javascript of selected provider',
                    'description' => 'Only select visible provider to prevent unnecessary files.',
                    'type' => 'multicheck',
                    'options' => array(
                        'walletconnect' => __('WalletConnect', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/walletconnect.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'fortmatic' => __('Fortmatic', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/fortmatic.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'torus' => __('Torus', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/torus.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'portis' => __('Portis', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/portis.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'authereum' => __('Authereum', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/authereum.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'frame' => __('Frame', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/frame.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'bitski' => __('Bitski', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/bitski.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'venly' => __('Venly', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/venly.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'dcent' => __('DCent', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/dcent.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'burnerconnect' => __('BurnerConnect', 'crypto') .  ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/burnerconnect.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'mewconnect' => __('MEWConnect', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/mewconnect.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'bnb' => __('Binance Chain Wallet', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/binancechainwallet.md" target="_blank">' . __('Docs', 'crypto') . '</a>',
                        'walletlink' => __('WalletLink', 'crypto') . ' <a href="https://github.com/Web3Modal/web3modal/blob/master/docs/providers/walletlink.md" target="_blank">' . __('Docs', 'crypto') . '</a>',

                    ),
                ),

                array(
                    'name' => 'execute_js',
                    'label' => __('Javascript function', 'crypto'),
                    'description' => __('Execute javascript function as soon as wallet connected. Eg. alert("Hello"); ', 'crypto'),
                    'size' => 20,
                    'type' => 'text',
                ),

            ),
        );
        $new = array_merge($new, $fields);
        //   }
        return $new;
    }

    //Add enable/disable option at extension tab
    public function add_extension($new)
    {

        $fields = array('crypto_general_login' => array(
            array(
                'name' => 'enable_crypto_login',
                'label' => __('Select login provider', 'crypto'),
                'description' => '',
                'type' => 'radio',
                'options' => array(
                    'web3modal' => __('Connect using Web3Modal. Supports more then 10 wallet provider', 'crypto'),
                    'moralis' => __('Connect using moralis.io API - Metamask & WalletConnect', 'crypto'),
                    'metamask' => __('Connect using Metamask without any provider', 'crypto'),
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


            $display = crypto_get_option('provider_list', 'crypto_login_web3', '');
            if (is_array($display)) {
                foreach ($display as $x => $x_value) {
                    // flexi_log("Key=" . $x . ", Value=" . $x_value);
                    wp_enqueue_script('crypto_wallet_' . $x, plugin_dir_url(__DIR__) . 'public/js/web3modal/' . $x . '.js', array('jquery'), '', false);
                }
            }
            wp_enqueue_script('crypto_web3', plugin_dir_url(__DIR__) . 'public/js/web3modal/web3.min.js', array('jquery'), '', false);
            // wp_enqueue_script('crypto_web3-provider', plugin_dir_url(__DIR__) . 'public/js/web3modal/walletconnect.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_index', plugin_dir_url(__DIR__) . 'public/js/web3modal/index.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_index_min', plugin_dir_url(__DIR__) . 'public/js/web3modal/index.min.js', array('jquery'), '', false);
            wp_enqueue_script('crypto_login', plugin_dir_url(__DIR__) . 'public/js/web3modal/crypto_connect_login_web3modal.js', array('jquery'), '', false);
        }
    }

    public function crypto_connect_option($params)
    {

        extract(shortcode_atts(array(
            'label' => '',
            'class' => '',
        ), $params));

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');

        if ($label == '') {
            $label = $this->metamask;
        }

        if ($class == '') {
            $class = $this->connect_class;
        }


        if ("web3modal" == $enable_addon) {
            $put = "";
            ob_start();
            $nonce = wp_create_nonce("crypto_connect_ajax_process");

?>
<span>
    <?php
                if ($this->enable_metamask == "1") {
                ?>
    <a href="#" id="btn-login" class="<?php echo esc_attr($class); ?>"><?php echo esc_attr($label); ?></a>
    <?php
                }

                ?>

    <div class="fl-notification fl-is-primary fl-is-light fl-mt-1" id="flexi_notification_box">
        <button class="fl-delete" id="delete_notification"></button>
        <div id="wallet_msg">&nbsp;</div>
    </div>

    <div id="wallet_addr_box">
        <div class="fl-tags fl-has-addons">
            <span id="wallet_addr" class="fl-tag fl-is-success fl-is-light">Loading...</span>
            <a class="fl-tag fl-is-delete" id="wallet_logout" title="Logout"></a>
        </div>
    </div>
</span>

<?php
            $put = ob_get_clean();

            return $put;
        }
    }

    public function crypto_head_script()
    {
        $put = "";
        ob_start();
        ?>

<script>
/**
 * Setup the orchestra
 * 
 * 
 */

function init() {

    jQuery("[id=wallet_addr_box]").hide();
    jQuery("[id=crypto_donation_box]").hide();
    //console.log("Initializing example");
    //console.log("WalletConnectProvider is", WalletConnectProvider);
    // console.log("Fortmatic is", Fortmatic);
    // console.log("window.web3 is", window.web3, "window.ethereum is", window.ethereum);

    // Tell Web3modal what providers we have available.
    // Built-in web browser provider (only one can exist as a time)
    // like MetaMask, Brave or Opera is added automatically by Web3modal
    <?php echo wp_kses_post($this->provider); ?>

    web3Modal = new Web3Modal({
        cacheProvider: true, // optional
        providerOptions, // required
        disableInjectedProvider: false, // optional. For MetaMask / Brave / Opera.
    });

    console.log("Web3Modal instance is", web3Modal);
    starting();
    async function starting() {
        console.log(localStorage.getItem("WEB3_CONNECT_CACHED_PROVIDER"));
        if (web3Modal.cachedProvider) {
            // connected now you can get accounts
            const provider = await web3Modal.connect();
            const web3 = new Web3(provider);
            const accounts = await web3.eth.getAccounts();
            console.log(accounts);
            jQuery("[id=wallet_addr]").empty();
            jQuery("#wallet_addr_box").fadeIn("slow");
            jQuery("[id=wallet_addr]").append(crypto_wallet_short(accounts[0], 4)).fadeIn("normal");
            jQuery("[id=btn-login]").hide();
        } else {
            console.log("no provider");
            jQuery("[id=wallet_addr_box]").hide();
        }

        jQuery("[id=wallet_logout]").click(function() {
            // alert("logout");
            web3Modal.clearCachedProvider();
            jQuery("[id=btn-login]").show();
            jQuery("[id=wallet_addr]").empty();
            jQuery("[id=wallet_addr_box]").hide();

            create_link_crypto_connect_login('nonce', '', 'logout', '', '', '');
            //jQuery("#crypto_connect_ajax_process").click();
            setTimeout(function() {
                jQuery('#crypto_connect_ajax_process').trigger('click');
            }, 1000);
        });
    }



}
</script>
<?php

        $put = ob_get_clean();

        echo $put;
    }


    public function crypto_connect_small_flexi()
    {

        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            //Display at Flexi Form

            $enable_addon = crypto_get_option('enable_flexi', 'crypto_login_web3', 1);
            if ("1" == $enable_addon) {
                echo wp_kses_post($this->crypto_connect_option(''));
            }
        }
    }

    public function crypto_connect_small_woocommerce()
    {
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            //Display at WooCommerce form  
            $enable_addon_woo = crypto_get_option('enable_woocommerce', 'crypto_login_web3', 1);
            if ("1" == $enable_addon_woo) {
                echo wp_kses_post($this->crypto_connect_option(''));
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
$connect_page = new Crypto_Connect_Web3();