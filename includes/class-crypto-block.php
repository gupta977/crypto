<?php
class Crypto_Block
{
	private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';
	private $config = '{"title":"Domain Access Control","prefix":"crypto_","domain":"crypto","class_name":"crypto_class","post-type":["post","page"],"context":"side","priority":"default","cpt":"flexi, product","fields":[{"type":"checkbox","label":"Restrict","description":"This content is only accessible by logged in user with domain name holder. ","id":"crypto_restrict"}]}';
	private $domain_name;
	private $restrict_page;

	public function __construct()
	{
		$this->domain_name = crypto_get_option('domain_name', 'crypto_access_settings', 'web3');
		$this->restrict_page = crypto_get_option('restrict_page', 'crypto_access_settings', 0);
		add_shortcode('crypto-block', array($this, 'crypto_block'));
		add_action('template_redirect', array($this, 'crypto_full_page'));
		$this->config = json_decode($this->config, true);
		$this->process_cpts();
		add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		add_action('admin_head', [$this, 'admin_head']);
		add_action('save_post', [$this, 'save_post']);
		add_filter('crypto_dashboard_tab', array($this, 'dashboard_add_tabs'));
		add_action('crypto_dashboard_tab_content', array($this, 'dashboard_add_content'));
	}

	public function crypto_block($atts, $content = "")
	{



		if (is_user_logged_in()) {
			if ($content && $this->crypto_can_user_view()) {
				return $content;
			} else {
				$message = '<div class="message">';
				$message .= __('You must have Web3Domain in your wallet', 'crypto') . " : <strong><a href='" . esc_url(get_page_link($this->restrict_page)) . "'>." . $this->domain_name . "</a></strong>";
				$message .= '</div>';
			}
		} else {
			$message = '<div class="message">';
			$message .= __('You must login to view content.', 'crypto');
			$message .= '</div>';
		}



		return apply_filters('crypto_block', $message);
	}

	public function crypto_full_page()
	{
		$postID = url_to_postid($_SERVER['REQUEST_URI'], '_wpg_def_keyword', true);
		$post = get_post($postID);
		if (isset($post->ID)) {
			$res = get_post_meta($post->ID, 'crypto_restrict', true);
			if ($res == "on" && is_user_logged_in()) {
				//flexi_log("restrictnio is on");
				if ($this->crypto_can_user_view()) {
					//flexi_log("can iew");
				} else {
					//$restrict_page = crypto_get_option('restrict_page', 'crypto_access_settings', 0);
					if (0 != $this->restrict_page) {
						wp_redirect(esc_url(get_page_link($this->restrict_page)));

						exit();
					} else {
						wp_redirect(home_url('/'));
					}
				}
			}


			$login_page = crypto_get_option('login_page', 'crypto_access_settings', 0);
			if ($res == "on" && !is_user_logged_in()) {
				if (0 != $login_page) {
					wp_redirect(get_page_link($login_page));
					exit();
				}
			}
		}
		//flexi_log("xxxxx " . $postID);
	}

	public function crypto_can_user_view()
	{

		$ret = false;

		if (is_user_logged_in()) {


			$check = get_user_meta(get_current_user_id(),  'domain_block', 'true');
			if ($check == 'false') {
				$ret = true;
			} else {
				$ret = false;
			}
		}

		return apply_filters('crypto_can_user_view', $ret);
	}

	public function process_cpts()
	{
		if (!empty($this->config['cpt'])) {
			if (empty($this->config['post-type'])) {
				$this->config['post-type'] = [];
			}
			$parts = explode(',', $this->config['cpt']);
			$parts = array_map('trim', $parts);
			$this->config['post-type'] = array_merge($this->config['post-type'], $parts);
		}
	}

	public function add_meta_boxes()
	{
		foreach ($this->config['post-type'] as $screen) {
			add_meta_box(
				sanitize_title($this->config['title']),
				$this->config['title'],
				[$this, 'add_meta_box_callback'],
				$screen,
				$this->config['context'],
				$this->config['priority']
			);
		}
	}

	public function admin_head()
	{
		global $typenow;
		if (in_array($typenow, $this->config['post-type'])) {
?><?php
		}
	}

	public function save_post($post_id)
	{
		foreach ($this->config['fields'] as $field) {
			switch ($field['type']) {
				case 'checkbox':
					update_post_meta($post_id, $field['id'], isset($_POST[$field['id']]) ? $_POST[$field['id']] : '');
					break;
				default:
					if (isset($_POST[$field['id']])) {
						$sanitized = sanitize_text_field($_POST[$field['id']]);
						update_post_meta($post_id, $field['id'], $sanitized);
					}
			}
		}
	}

	public function add_meta_box_callback()
	{
		if (isset($this->config['description']))
			echo '<div class="rwp-description">' . $this->config['description'] . '</div>';
		$this->fields_div();
	}

	private function fields_div()
	{
		foreach ($this->config['fields'] as $field) {
	?><div class="components-base-control">
    <div class="components-base-control__field"><?php
												$this->label($field);
												$this->field($field);
												?></div>
</div><?php
		}
	}

	private function label($field)
	{
		switch ($field['type']) {
			default:
				printf(
					'<label class="components-base-control__label" for="%s">%s</label> ',
					$field['id'],
					$field['label']
				);
		}
	}

	private function field($field)
	{
		switch ($field['type']) {
			case 'checkbox':
				$this->checkbox($field);
				break;
			default:
				$this->input($field);
		}
	}

	private function checkbox($field)
	{
		printf(
			'<label class="rwp-checkbox-label"><input %s id="%s" name="%s" type="checkbox"> %s</label>',
			$this->checked($field),
			$field['id'],
			$field['id'],
			isset($field['description']) ? $field['description'] : ''
		);
	}

	private function input($field)
	{
		printf(
			'<input class="components-text-control__input %s" id="%s" name="%s" %s type="%s" value="%s">',
			isset($field['class']) ? $field['class'] : '',
			$field['id'],
			$field['id'],
			isset($field['pattern']) ? "pattern='{$field['pattern']}'" : '',
			$field['type'],
			$this->value($field)
		);
	}

	private function value($field)
	{
		global $post;
		if (metadata_exists('post', $post->ID, $field['id'])) {
			$value = get_post_meta($post->ID, $field['id'], true);
		} else if (isset($field['default'])) {
			$value = $field['default'];
		} else {
			return '';
		}
		return str_replace('\u0027', "'", $value);
	}

	private function checked($field)
	{
		global $post;
		if (metadata_exists('post', $post->ID, $field['id'])) {
			$value = get_post_meta($post->ID, $field['id'], true);
			if ($value === 'on') {
				return 'checked';
			}
			return '';
		} else if (isset($field['checked'])) {
			return 'checked';
		}
		return '';
	}


	public function dashboard_add_tabs($tabs)
	{

		$extra_tabs = array("access" => 'Restrict User');

		// combine the two arrays
		$new = array_merge($tabs, $extra_tabs);
		//crypto_log($new);
		return $new;
	}

	public function dashboard_add_content()
	{
		if (isset($_GET['tab']) && 'access' == $_GET['tab']) {
			echo wp_kses_post($this->crypto_dashboard_content());
		}
	}

	public function crypto_dashboard_content()
	{
		ob_start();
		?>
<div class="changelog section-getting-started">
    <div class="feature-section">
        <h2>Access restrictions for Member</h2>
        <div class="wrap">
            <b>Restrict content/pages based on crypto/NFT holding inside 'crypto wallet'</b>
            <br><br><a class="button button-primary"
                href="<?php echo admin_url('admin.php?page=crypto_settings&tab=access&section=crypto_access_settings_start'); ?>">Settings</a>
            <br><br>
            <b>Tips</b>
            <ul>
                <li>* You must use correct smart contract address which starts from 0x.... </li>
                <li>* Crypto & NFT count is calculated as balanceOf ether function. </li>
            </ul>
            <hr>
            <b>Do you know about <a href='<?php echo esc_url('https://web3domain.org/'); ?>'
                    target='_blank'>Web3Domain</a> ? </b><br>
            <ul>
                <li>
                    * It is best option to earn for membership by letting user to obtain subdomain of your web3 primary
                    domain.</li>
                <li>* You earn as soon as domain minted.</li>
                <li>* You can set the price for your subdomain yourself.</li>
                <li>* You can also restrict not to be minted by public. Only you can mint it and transfer. Hence you can
                    save commission fees too. </li>
                <li>* All Web3Domains are NFTs. Which you can sell at opensea.io</li>
            </ul>

        </div>
    </div>
</div>
<?php
		$content = ob_get_clean();
		return $content;
	}
}
$price_page = new Crypto_Block();