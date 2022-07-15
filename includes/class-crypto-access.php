<?php
class Crypto_Access
{
	private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';

	private $api;
	private $cache;

	public function __construct()
	{
		$this->api = strtoupper(crypto_get_option('alchemy_api', 'crypto_access_settings', 'USD'));
		$this->cache = crypto_get_option('alchemy_cache', 'crypto_access_settings', '600');
		add_shortcode('crypto-access', array($this, 'crypto_access_box'));
		add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
		add_filter('crypto_settings_sections', array($this, 'add_section'));
		add_filter('crypto_settings_fields', array($this, 'add_fields'));
		add_filter('init', array($this, 'access_control_check'));
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
				'title' => __('Crypto Access Controls', 'crypto'),
				'description' => __('Restrict user to access certain part of the website based on domain/NFT availability. ', 'crypto') . "<br>" . "<b>Shortcode examples</b><br><code> [crypto-price symbol=\"BTC\"] </code><br><code>[crypto-price symbol=\"MATIC,BTC,XRP\" style=\"style1\"]</code><br><code>[crypto-price symbol=\"BTC\" style=\"style1\" currency=\"INR\" color=\"fl-is-warning\"]</code>",
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
					'name' => 'alchemy_api',
					'label' => __('Alchemy API', 'crypto'),
					'description' => __('Get free API key from Alchemy', 'crypto') . " <a href='https://www.alchemy.com/' target='_blank'>Click Here </a>",
					'type' => 'text',
					'sanitize_callback' => 'sanitize_key',
				),
				array(
					'name' => 'alchemy_cache',
					'label' => __('Alchemy Data Caching', 'crypto'),
					'description' => __('Enter cache time for crypto data in seconds. It saves API limit and speed up results.', 'crypto'),
					'type' => 'number',
					'size' => 'small',
					'sanitize_callback' => 'intval',
				),
				array(
					'name' => 'login_page',
					'label' => __('Member Login Page', 'crypto'),
					'description' => __('If not selected, opens wp-admin login page.', 'crypto'),
					'type' => 'pages',
					'sanitize_callback' => 'sanitize_key',
				),
				array(
					'name' => 'redirect_page',
					'label' => __('Redirect page', 'crypto'),
					'description' => __('Page to redirect after user logged in via wallet', 'crypto'),
					'type' => 'pages',
					'sanitize_callback' => 'sanitize_key',
				),
				array(
					'name' => 'restrict_page',
					'label' => __('Restrict Page', 'crypto'),
					'description' => __('Page to display reason to restrict.', 'crypto'),
					'type' => 'pages',
					'sanitize_callback' => 'sanitize_key',
				),

			),
		);
		$new = array_merge($new, $fields);

		return $new;
	}

	public function access_control_check()
	{
		$run = 'https://polygon-mainnet.g.alchemy.com/v2/' . $this->api . '/getNFTs?owner=0xC3bC7e78Dd1aB7c64aAACc98abCd052DB91A37f4';

		$api_url = 'https://polygon-mainnet.g.alchemy.com/v2/' . $this->api . '/getNFTs';


		$args = array(
			'headers' => array(
				'Accepts' => 'application/json',
				'owner' => '0xC3bC7e78Dd1aB7c64aAACc98abCd052DB91A37f4',
			)
		);



		//	$request = wp_remote_post($api_url, $args);
		//	flexi_log($request);
	}

	public function crypto_access_box()
	{
		$put = "";
		ob_start();

		if (is_user_logged_in()) {
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
        console.log(accounts);
        // Get connected chain id from Ethereum node
        const chainId = await web3.eth.getChainId();
        const chainId_new = crypto_connectChainAjax.chainId;
        console.log(chainId + "----");
        const contractAddress = '0x3bA26d4d5250E82936F281805423A1ABEaEfC3B5';
        const myContract = new web3.eth.Contract(contractAbi, contractAddress);
        var curr_user = accounts[0];
        console.log(curr_user);
        run_start(myContract, curr_user);

    }

    function run_start(myContract, curr_user) {
        // alert(claim_id);
        myContract.methods.balanceOf(curr_user).call().then(function(count) {

            //console.log("Balance is " + count);
            jQuery("[id=crypto_msg_ul]").empty();
            jQuery("[id=crypto_msg_ul]").append("<li>Number of domains found: <strong>" + count +
                "</strong></li>").fadeIn("normal");
            if (count == 0) {
                console.log("zero domain");
                jQuery("[id=crypto_msg_ul]").append(
                        "<li>Your wallet do not have .gupta Domain. <strong>Account restricted.</strong> </li>"
                    )
                    .fadeIn("normal");
                create_link_crypto_connect_login('nounce', '', 'savenft', curr_user, '', count);

                setTimeout(function() {
                    jQuery('#crypto_connect_ajax_process').trigger('click');
                }, 1000);
            }


            //Empty array
            persons.length = 0;
            for (let i = 0; i < count; i++) {

                myContract.methods.tokenOfOwnerByIndex(curr_user, i).call().then(function(nft) {

                    console.log(nft);
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

            if (i == count) {
                //console.log(persons);
                // console.log("sssss");
                process_login_savenft(curr_user, persons, count);
            }

        });

    }

    function process_login_savenft(curr_user, persons, count) {

        create_link_crypto_connect_login('nounce', '', 'savenft', curr_user, persons, count);
        console.log(persons);
        setTimeout(function() {
            jQuery('#crypto_connect_ajax_process').trigger('click');
        }, 1000);

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
    <span class="fl-tag fl-is-primary">.gupta domain holder</span>
</div>
<?php
			} else {
			?>

<div class="fl-tags fl-has-addons">
    <span class="fl-tag">Account Status (<?php echo $current_user->user_login; ?>)</span>
    <span class="fl-tag fl-is-danger">.gupta domain required</span>
</div>
<?php
			}
			?>
<br>
<div class="fl-message fl-is-dark">
    <div class="fl-message-body">
        Some content or pages on the site is accessible only to the selected member who owns <strong>.gupta</strong>
        domain from web3domain.org
    </div>
</div>
<br>
<div class="fl-message" id="crypto_msg">
    <div class="fl-message-header">
        <p>Available domains into wallet</p>
    </div>
    <div class="fl-message-body" id="crypto_msg_body">
        <ul id="crypto_msg_ul">

        </ul>
    </div>
</div>
<a href="#" id="check_domain" class="fl-button fl-is-link fl-is-light">Check .gupta Domains</a>

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
		$put = ob_get_clean();

		return $put;
	}
}
$price_page = new Crypto_Access();