<?php
class Crypto_Access_NFT
{
	private $chainid;
	private $contract;
	private $nft_name;
	private $nft_count;

	public function __construct()
	{

		$this->chainid = crypto_get_option('chainid', 'crypto_access_other', '1');
		$this->contract = crypto_get_option('chain_contract', 'crypto_access_other', '0x.......');
		$this->nft_name = crypto_get_option('nft_name', 'crypto_access_other', 'NFT of Something');
		$this->nft_count = crypto_get_option('nft_count', 'crypto_access_other', '1');
		add_filter('crypto_settings_sections', array($this, 'add_section'));
		add_filter('crypto_settings_fields', array($this, 'add_fields'));
		add_shortcode('crypto-access-nft', array($this, 'crypto_access_box'));
	}

	//Add Section title
	public function add_section($new)
	{

		$sections = array(
			array(
				'id' => 'crypto_access_other',
				'title' => __('NFT & Crypto Access', 'crypto'),
				'description' => __('Restrict user to access certain part of the website based on NFT token availability. ', 'crypto') . "<br><br><b>Shortcode to restrict content</b><br><code>[crypto-block] Private information or content between shortcode. [/crypto-block]</code><b><br><br>Restrict full page</b><br><code>Edit the page and choose option from setting panel</code>",
				'tab' => 'access',
			),
		);
		$new = array_merge($new, $sections);
		//  }
		return $new;
	}

	//Add section fields
	public function add_fields($new)
	{

		$fields = array(
			'crypto_access_other' => array(
				array(
					'name' => 'chainid',
					'label' => __('Blockchain Network', 'crypto'),
					'description' => __('Select your blockchain of contract address used', 'crypto'),
					'type' => 'select',
					'options' => array(

						'1' => __('Ethereum Mainnet', 'crypto'),
						'137' => __('Matic - Polygon Mainnet', 'crypto'),
						'56' => __('BNB Smart Chain', 'crypto'),
					)
				),
				array(
					'name' => 'chain_contract',
					'label' => __('NFT contract address', 'crypto'),
					'description' => __('Contract address of NFT starts with 0x...', 'crypto'),
					'size' => 'large',
					'type' => 'text',
				),
				array(
					'name' => 'nft_name',
					'label' => __('NFT Name', 'crypto'),
					'description' => __('Name of the NFT Token for visitors', 'crypto'),
					'size' => 'large',
					'type' => 'text',
				),
				array(
					'name' => 'nft_count',
					'label' => __('NFT or Crypto count', 'crypto'),
					'description' => __('Enter the number of NFT/crypto must be available.', 'crypto'),
					'type' => 'number',
					'size' => 'medium',
					'sanitize_callback' => 'intval',
				),
				array(
					'name' => 'restrict_page',
					'label' => __('Restrict Page', 'crypto'),
					'description' => __('Page must contain shortcode as ', 'crypto') . '[crypto-connect label="Connect Wallet" class="fl-button fl-is-info fl-is-light"] [crypto-access-nft]',
					'type' => 'pages',
					'sanitize_callback' => 'sanitize_key',
				),
			)
		);
		$new = array_merge($new, $fields);
		return $new;
	}

	public function crypto_access_box()
	{

		$arr = array('1' => 'Ethereum Mainnet', '137' => 'Matic - Polygon Mainnet', '56' => 'BNB Smart Chain');


		$put = "";
		ob_start();
		$nonce = wp_create_nonce('crypto_ajax');
		$enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
		if ("web3modal" == $enable_addon) {
			if (is_user_logged_in()) {
				$default_access = crypto_get_option('select_access_control', 'crypto_access_settings_start', 'web3domain');
				if ($default_access == 'nft') {
					$saved_array = get_user_meta(get_current_user_id(),  'domain_names');
					// flexi_log($saved_array);
					//$check = new crypto_connect_ajax_process();
					//$check->checknft(get_current_user_id(),  $saved_array);
?>
<script>
jQuery(document).ready(function() {



    async function access() {

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

        if ((chainId != '<?php echo $this->chainid; ?>')) {
            var msg =
                "Change your network to <?php echo  $arr[$this->chainid]; ?>";
            jQuery("[id=crypto_msg_ul]").empty();
            jQuery("[id=crypto_msg_ul]").append(msg).fadeIn("normal");
        } else {

            const balanceOfABI = [{
                "constant": true,
                "inputs": [{
                    "name": "_owner",
                    "type": "address"
                }],
                "name": "balanceOf",
                "outputs": [{
                    "name": "balance",
                    "type": "uint256"
                }],
                "payable": false,
                "stateMutability": "view",
                "type": "function"
            }, ];


            const contractAddress = '<?php echo $this->contract; ?>';
            const myContract = new web3.eth.Contract(balanceOfABI, contractAddress);
            var curr_user = accounts[0];
            console.log(curr_user);
            run_start(myContract, curr_user);
        }

        function run_start(myContract, curr_user) {
            // alert(claim_id);
            myContract.methods.balanceOf(curr_user).call().then(function(count) {

                const formattedResult = web3.utils.fromWei(count, "ether");
                //      console.log(count + " Balance is " + formattedResult + " -- " + count / 100000000);
                jQuery("[id=crypto_msg_ul]").empty();
                jQuery("[id=crypto_msg_ul]").append("<li>Found: <strong>" +
                    formattedResult +
                    "</strong></li>").fadeIn("normal");
                if (formattedResult < <?php echo $this->nft_count; ?>) {
                    // console.log("zero domain");
                    jQuery("[id=crypto_msg_ul]").append(
                            "<li>Your wallet do not have sufficient '<?php echo $this->nft_name; ?>'. <br>Required: <strong><?php echo $this->nft_count; ?></strong> <br><strong>Account restricted.</strong> </li>"
                        )
                        .fadeIn("normal");

                } else {
                    console.log("sufficient");
                }

                create_link_crypto_connect_login('<?php echo sanitize_key($nonce); ?>', '',
                    'savenft',
                    curr_user, '', formattedResult);

                setTimeout(function() {
                    jQuery('#crypto_connect_ajax_process').trigger('click');
                }, 1000);

            }).catch(function(tx) {
                console.log(tx);
                jQuery("[id=crypto_msg_ul]").append(
                        "<li>Wrong contract address or network seems unstable. </li>"
                    )
                    .fadeIn("normal");
                // coin_toggle_loading("end");

            });

        }
    }

    jQuery("#check_domain").click(function() {
        access();
        // alert("hello");

    });

    setTimeout(function() {
        jQuery('#check_domain').trigger('click');
    }, 1000);

});
</script>
<?php
					$check_access = new Crypto_Block();
					$current_user = wp_get_current_user();
					if ($check_access->crypto_can_user_view()) {

					?>

<div class="fl-tags fl-has-addons">
    <span class="fl-tag">Account Status (<?php echo $current_user->user_login; ?>)</span>
    <span class="fl-tag fl-is-primary"><?php echo "." . $this->nft_name; ?> holder</span>
</div>
<?php
					} else {
					?>

<div class="fl-tags fl-has-addons">
    <span class="fl-tag">Account Status (<?php echo $current_user->user_login; ?>)</span>
    <span class="fl-tag fl-is-danger"><?php echo "." . $this->nft_name; ?>: <?php echo "." . $this->nft_count; ?>
        required</span>
</div>
<?php
					}
					?>
<br>
<br>
<div class="fl-message fl-is-dark">
    <div class="fl-message-body">
        Some content or pages on the site is accessible only to the selected member who owns
        <strong><?php echo $this->nft_name; ?></strong>
    </div>
</div>
<br>
<div class="fl-message" id="crypto_msg">
    <div class="fl-message-header">
        <p>Available domains into network ID : <b><?php echo $arr[$this->chainid]; ?></b></p>
    </div>
    <div class="fl-message-body" id="crypto_msg_body">
        <ul id="crypto_msg_ul">

        </ul>
    </div>
</div>
<a href="#" id="check_domain" class="fl-button fl-is-link fl-is-light">Check <?php echo $this->nft_name; ?></a>

<a class="fl-button" href="#" onclick="location.reload();" title="Refresh">
    <span class="fl-icon fl-is-small">
        <i class="fas fa-sync"></i>
    </span>
</a>
<br>

<br>
<?php
				} else {
					echo "NFT & Crypto access is disabled. Enable it from settings";
				}
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
new Crypto_Access_NFT();