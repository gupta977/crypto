<?php
class Crypto_Access
{
    private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';

    private $api;
    private $cache;
    private $domain_name;

    public function __construct()
    {
        $this->api = strtoupper(crypto_get_option('alchemy_api', 'crypto_access_settings', 'USD'));
        $this->cache = crypto_get_option('alchemy_cache', 'crypto_access_settings', '600');
        $this->domain_name = crypto_get_option('domain_name', 'crypto_access_settings', 'web3');
        add_shortcode('crypto-access-domain', array($this, 'crypto_access_box'));
        add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));

        //add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('crypto_web3domain', plugin_dir_url(__DIR__) . 'public/js/web3domain.js', array('jquery'), '', false);
    }


    //add_filter flexi_settings_tabs
    public function add_tabs($new)
    {

        $tabs = array(
            'access'   => __('Access Control', 'crypto'),

        );
        $new  = array_merge($new, $tabs);

        return $new;
    }


    //Add Section title
    public function add_section($new)
    {

        $sections = array(
            array(
                'id' => 'crypto_access_settings',
                'title' => __('Web3Domain Access', 'crypto'),
                'description' => __('Restrict user to access certain part of the website based on domain/NFT availability. ', 'crypto') . "<br>" . "<b>Shortcode to restrict content</b><br><code>[crypto-block] Private information or content between shortcode. [/crypto-block]</code><b><br><br>Restrict full page</b><br><code>Edit the page and choose option from setting panel</code>",
                'tab' => 'access',
            ),
        );
        $new = array_merge($new, $sections);

        return $new;
    }

    //Add section fields
    public function add_fields($new)
    {
        $fields = array(
            'crypto_access_settings' => array(

                array(
                    'name' => 'domain_name',
                    'label' => __('Web3Domain Name', 'crypto'),
                    'description' => __('Enter Web3Domain primary domain name. Access to page is available to user, only if sub-domain exist in wallet.', 'crypto'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_key',
                ),

                array(
                    'name' => 'restrict_page',
                    'label' => __('Restrict Page', 'crypto'),
                    'description' => __('Page must contain shortcode as ', 'crypto') . '[crypto-connect label="Connect Wallet" class="fl-button fl-is-info fl-is-light"] [crypto-access-domain]',
                    'type' => 'pages',
                    'sanitize_callback' => 'sanitize_key',
                ),

            ),
        );
        $new = array_merge($new, $fields);

        return $new;
    }

    public function crypto_access_box()
    {



        $put = "";
        ob_start();
        $nonce = wp_create_nonce('crypto_ajax');
        $enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
        if ("web3modal" == $enable_addon) {
            if (is_user_logged_in()) {
                $saved_array = get_user_meta(get_current_user_id(),  'domain_names');
                // flexi_log($saved_array);
                $check = new crypto_connect_ajax_process();
                $check->checknft(get_current_user_id(),  $saved_array);
?>
<script>
jQuery(document).ready(function() {
    jQuery("[id=crypto_msg]").hide();
    var persons = [];


    async function getABI() {

        fetch(
                '<?php echo COIN_PLUGIN_URL; ?>/public/js/web3domain.json?ver=<?php echo COIN_VERSION; ?>'
            )
            .then(res => {
                return res.text();
            }).then(json => {
                var ca = JSON.parse(json);
                var contractAbi = ca.abi;
                access(contractAbi);
                jQuery("[id=crypto_msg]").show();
            });

    }

    async function access(contractAbi) {

        if (provider == undefined) {
            provider = await web3Modal.connect();
        }

        // Get a Web3 instance for the wallet
        const web3 = new Web3(provider);
        const accounts = await web3.eth.getAccounts();
        // console.log(accounts);
        // Get connected chain id from Ethereum node
        const chainId = await web3.eth.getChainId();
        const chainId_new = crypto_connectChainAjax.chainId;
        // console.log(chainId + "----");
        if ((chainId != '137')) {
            var msg = "Change your network to Polygon (MATIC). Your connected network is " + chainId;
            jQuery("[id=crypto_msg_ul]").empty();
            jQuery("[id=crypto_msg_ul]").append(msg).fadeIn("normal");
        } else {
            const contractAddress = '0x3bA26d4d5250E82936F281805423A1ABEaEfC3B5';
            const myContract = new web3.eth.Contract(contractAbi, contractAddress);
            var curr_user = accounts[0];
            console.log(curr_user);
            run_start(myContract, curr_user);
        }

    }

    function run_start(myContract, curr_user) {
        // alert(claim_id);
        myContract.methods.balanceOf(curr_user).call().then(function(count) {

            //console.log("Balance is " + count);
            jQuery("[id=crypto_msg_ul]").empty();
            jQuery("[id=crypto_msg_ul]").append("<li>Number of web3domains found: <strong>" + count +
                "</strong></li>").fadeIn("normal");
            if (count == 0) {
                // console.log("zero domain");
                jQuery("[id=crypto_msg_ul]").append(
                        "<li>Your wallet do not have <?php echo "." . $this->domain_name; ?> Domain. <strong>Account restricted.</strong> </li>"
                    )
                    .fadeIn("normal");
                create_link_crypto_connect_login('<?php echo sanitize_key($nonce); ?>', '', 'savenft',
                    curr_user, '', count);

                setTimeout(function() {
                    jQuery('#crypto_connect_ajax_process').trigger('click');
                }, 1000);
            }


            //Empty array
            persons.length = 0;
            for (let i = 0; i < count; i++) {

                myContract.methods.tokenOfOwnerByIndex(curr_user, i).call().then(function(nft) {

                    //console.log(nft);
                    get_domain_name(nft, myContract, curr_user, i + 1, count);

                });

            }

        }).catch(function(tx) {
            console.log(tx);
            // coin_toggle_loading("end");

        });

    }

    async function get_domain_name(nft, myContract, curr_user, i, count) {
        // console.log("----");
        myContract.methods.titleOf(nft).call().then(function(domain) {

            // console.log(domain);
            jQuery("[id=crypto_msg_ul]").append("<li>" + domain + "</li>").fadeIn("normal");
            persons.push(domain);
            console.log(count);
            if (i == count) {
                //console.log(persons);
                // console.log("sssss");
                process_login_savenft(curr_user, persons, count);
            }

        });

    }

    function process_login_savenft(curr_user, persons, count) {


        create_link_crypto_connect_login('<?php echo sanitize_key($nonce); ?>', '', 'savenft', curr_user,
            persons, count);
        console.log(persons);
        setTimeout(function() {
            jQuery('#crypto_connect_ajax_process').trigger('click');
        }, 100);

    }


    jQuery("#check_domain").click(function() {
        getABI();

    });

});
</script>
<?php
                $check_access = new Crypto_Block();
                $current_user = wp_get_current_user();
                if ($check_access->crypto_can_user_view()) {

                ?>

<div class="fl-tags fl-has-addons">
    <span class="fl-tag">Account Status (<?php echo $current_user->user_login; ?>)</span>
    <span class="fl-tag fl-is-primary"><?php echo "." . $this->domain_name; ?> sub-domain holder</span>
</div>
<?php
                } else {
                ?>

<div class="fl-tags fl-has-addons">
    <span class="fl-tag">Account Status (<?php echo $current_user->user_login; ?>)</span>
    <span class="fl-tag fl-is-danger"><?php echo "." . $this->domain_name; ?> sub-domain required</span>
</div>
<?php
                }
                ?>
<br>
<div class="fl-message fl-is-dark">
    <div class="fl-message-body">
        Some content or pages on the site is accessible only to the selected member who owns
        <strong><?php echo "." . $this->domain_name; ?></strong>'s
        sub-domain from <a href="https://www.web3domain.org/" target="_blank">web3domain.org</a>
    </div>
</div>
<br>
<div class="fl-message" id="crypto_msg">
    <div class="fl-message-header">
        <p>Available domains into polygon address</p>
    </div>
    <div class="fl-message-body" id="crypto_msg_body">
        <ul id="crypto_msg_ul">

        </ul>
    </div>
</div>
<a href="#" id="check_domain" class="fl-button fl-is-link fl-is-light">Check <?php echo "." . $this->domain_name; ?>
    Domains</a>

<a class="fl-button" href="#" onclick="location.reload();" title="Refresh">
    <span class="fl-icon fl-is-small">
        <i class="fas fa-sync"></i>
    </span>
</a>
<br>
<?php
            } else {
            ?>
<br>
<div class="fl-message">
    <div class="fl-message-header">
        <p>Please login</p>

    </div>
    <div class="fl-message-body">
        After login you can check your wallet for eligibility.
    </div>
</div>
<?php
            }
        } else {
            echo "Login provider must be 'Web3Modal'. Access control is not supported with other login provider.";
        }
        $put = ob_get_clean();

        return $put;
    }
}
$price_page = new Crypto_Access();