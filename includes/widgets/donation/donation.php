<?php
class CryptoDonation_Widget extends WP_Widget
{

	public function __construct()
	{
		parent::__construct(
			'cryptodonation_widget',
			esc_html__('Crypto Donation', 'crypto'),
			array('description' => esc_html__('Get tips or donation in crypto', 'crypto')) // Args
		);
	}

	private $widget_fields = array(
		array(
			'label' => 'Receiver Wallet Address',
			'id' => 'wallet_address',
			'default' => '0xC3bC7e78Dd1aB7c64aAACc98abCd052DB91A37f4',
			'type' => 'text',
		),

		array(
			'label' => 'Amount title',
			'id' => 'amount_title',
			'default' => 'Enter number of token',
			'type' => 'text',
		),
		array(
			'label' => 'Amount (Number of token of selected chain)',
			'id' => 'amount',
			'default' => '100',
			'type' => 'number',
		),
		array(
			'label' => 'Hide amount',
			'id' => 'hide_amount',
			// 'default' => '1',
			'type' => 'checkbox',
		),
		array(
			'label' => 'Button Label',
			'id' => 'button_label',
			'default' => 'Donate',
			'type' => 'text',
		),
	);

	public function widget($args, $instance)
	{

		echo $args['before_widget'];

		//flexi_log($instance);

		if (!empty($instance['title'])) {
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}

		$enable_addon = crypto_get_option('enable_crypto_login', 'crypto_general_login', 'metamask');
		if ("web3modal" == $enable_addon) {
?>
<script>
jQuery(document).ready(function() {

    jQuery("[id=crypto_donation_box]").hide();
    jQuery("[id=delete_notification]").click(function() {
        jQuery("[id=crypto_donation_box]").fadeOut("slow");
    });



    jQuery("[id=crypto_donation]").click(function() {
        //alert("Loginxxxxxxxxxxx");
        start_donation();

    });

    function crypto_donation_msg($msg) {
        jQuery("[id=donation_wallet_msg]").empty();
        jQuery("#crypto_donation_box").fadeIn("slow");
        jQuery("[id=donation_wallet_msg]").append($msg).fadeIn("normal");
    }

    async function start_donation() {
        var MY_ADDRESS =
            '<?php echo isset($instance['wallet_address']) ? $instance['wallet_address'] : '0xC3bC7e78Dd1aB7c64aAACc98abCd052DB91A37f4'; ?>';
        var fee = jQuery('#amount_fee').val();
        //alert("hi");
        if (provider == undefined) {
            provider = await web3Modal.connect();
        }
        const web3 = new Web3(provider);
        const accounts = await web3.eth.getAccounts();
        //console.log(accounts);
        var curr_user = accounts[0];
        console.log(curr_user);
        const chainId = await web3.eth.getChainId();
        //console.log("Connected chainId: "+chainId);
        const chainId_new = crypto_connectChainAjax.chainId;
        //console.log("chainid new: "+chainId_new);

        if ((chainId != chainId_new) && chainId_new != '') {
            var msg = "Change your network to: " + chainId_new + ". Your connected network is " + chainId;
            crypto_donation_msg(msg);
        } else {
            web3.eth.sendTransaction({
                    to: MY_ADDRESS,
                    from: curr_user,
                    value: Web3.utils.toWei(fee, 'ether'),
                },
                function(err, transactionHash) {
                    if (err) return crypto_donation_msg('There was a problem!: ' + err.message)
                    crypto_donation_msg('Thanks for the generosity!!')
                })
        }
    }

});
</script>
<?php
			if (isset($instance['hide_amount']) && '1' == $instance['hide_amount']) {
			?>
<input id="amount_fee" class="input" type="hidden"
    value="<?php echo isset($instance['amount']) ? $instance['amount'] : '1'; ?>">
<?php
			} else {
			?>
<div class="fl-field">
    <label
        class="fl-label"><?php echo isset($instance['amount_title']) ? $instance['amount_title'] : 'Token Quantity'; ?></label>
    <div class="fl-control">
        <input id="amount_fee" class="input" type="text" placeholder="e.g 100"
            value="<?php echo isset($instance['amount']) ? $instance['amount'] : '1'; ?>">
    </div>
</div>
<?php
			}
			?>

<div class="fl-field">
    <div class="fl-control">
        <button id="crypto_donation"
            class="fl-button fl-is-primary"><?php echo isset($instance['button_label']) ? $instance['button_label'] : 'Donate Me'; ?></button>
    </div>
</div>
<div class="fl-notification fl-is-primary fl-is-light fl-mt-1" id="crypto_donation_box">
    <button class="fl-delete" id="delete_notification"></button>
    <div id="donation_wallet_msg">&nbsp;</div>
</div>




<?php
		} else {
			echo "Donation only works on 'Connect using web3modal' on Login Settings.";
		}
		echo $args['after_widget'];
	}

	public function field_generator($instance)
	{
		$output = '';
		foreach ($this->widget_fields as $widget_field) {
			$default = '';
			if (isset($widget_field['default'])) {
				$default = $widget_field['default'];
			}
			$widget_value = !empty($instance[$widget_field['id']]) ? $instance[$widget_field['id']] : esc_html__($default, 'crypto');
			switch ($widget_field['type']) {
				case 'checkbox':
					$output .= '<p>';
					$output .= '<input class="checkbox" type="checkbox" ' . checked($widget_value, true, false) . ' id="' . esc_attr($this->get_field_id($widget_field['id'])) . '" name="' . esc_attr($this->get_field_name($widget_field['id'])) . '" value="1">';
					$output .= ' <label for="' . esc_attr($this->get_field_id($widget_field['id'])) . '">' . esc_attr($widget_field['label'], 'crypto') . '</label>';
					$output .= '</p>';
					break;
				case 'select':
					$output .= '<p>';
					$output .= '<label for="' . esc_attr($this->get_field_id($widget_field['id'])) . '">' . esc_attr($widget_field['label'], 'textdomain') . ':</label> ';
					$output .= '<select class="widefat" id="' . esc_attr($this->get_field_id($widget_field['id'])) . '" name="' . esc_attr($this->get_field_name($widget_field['id'])) . '">';
					foreach ($widget_field['options'] as $option => $value) {
						if ($widget_value == $value) {
							$output .= '<option value="' . $value . '" selected>' . $option . '</option>';
						} else {
							$output .= '<option value="' . $value . '">' . $option . '</option>';
						}
					}
					$output .= '</select>';
					$output .= '</p>';
					break;
				default:
					$output .= '<p>';
					$output .= '<label for="' . esc_attr($this->get_field_id($widget_field['id'])) . '">' . esc_attr($widget_field['label'], 'crypto') . ':</label> ';
					$output .= '<input class="widefat" id="' . esc_attr($this->get_field_id($widget_field['id'])) . '" name="' . esc_attr($this->get_field_name($widget_field['id'])) . '" type="' . $widget_field['type'] . '" value="' . esc_attr($widget_value) . '">';
					$output .= '</p>';
			}
		}
		echo $output;
	}

	public function form($instance)
	{
		$title = !empty($instance['title']) ? $instance['title'] : '';
		$cat = !empty($instance['cat']) ? $instance['cat'] : '';
		?>
<p>
    <label
        for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Widget Title:', 'crypto'); ?></label>
    <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
        name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
        value="<?php echo esc_attr($title); ?>">
</p>

<?php
		$this->field_generator($instance);
	}

	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['cat'] = (!empty($new_instance['cat'])) ? strip_tags($new_instance['cat']) : '';
		foreach ($this->widget_fields as $widget_field) {
			switch ($widget_field['type']) {
				default:
					$instance[$widget_field['id']] = (!empty($new_instance[$widget_field['id']])) ? strip_tags($new_instance[$widget_field['id']]) : '';
			}
		}
		return $instance;
	}
}

function register_crypto_donation_widget()
{
	register_widget('CryptoDonation_Widget');
}
add_action('widgets_init', 'register_crypto_donation_widget');
?>