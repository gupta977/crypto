<?php
class Crypto_Block
{
	private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';
	private $config = '{"title":"Domain Access Control","prefix":"crypto_","domain":"crypto","class_name":"crypto_class","post-type":["post","page"],"context":"side","priority":"default","cpt":"flexi, product","fields":[{"type":"checkbox","label":"Restrict","description":"This content is only accessible by logged in user with domain name holder. ","id":"crypto_restrict"}]}';
	public function __construct()
	{

		add_shortcode('crypto-block', array($this, 'crypto_block'));
		add_action('template_redirect', array($this, 'crypto_full_page'));
		$this->config = json_decode($this->config, true);
		$this->process_cpts();
		add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		add_action('admin_head', [$this, 'admin_head']);
		add_action('save_post', [$this, 'save_post']);
	}

	public function crypto_block($atts, $content = "")
	{



		if (is_user_logged_in()) {
			if ($content && $this->crypto_can_user_view()) {
				return $content;
			} else {
				$message = '<div class="message">';
				$message .= __('You don not have NFT', 'crypto');
				$message .= '</div>';
			}
		} else {
			$message = '<div class="message">';
			$message .= __('You need to register to view the rest of the content.', 'crypto');
			$message .= '</div>';
		}



		return apply_filters('crypto_block', $message);
	}

	public function crypto_full_page()
	{
		$postID = url_to_postid($_SERVER['REQUEST_URI'], '_wpg_def_keyword', true);
		$post = get_post($postID);
		$res = get_post_meta($post->ID, 'crypto_restrict', true);
		if ($res == "on" && is_user_logged_in()) {
			//flexi_log("restrictnio is on");
			if ($this->crypto_can_user_view()) {
				//flexi_log("can iew");
			} else {
				$restrict_page = crypto_get_option('restrict_page', 'crypto_access_settings', 0);
				if (0 != $restrict_page) {
					wp_redirect(esc_url(get_page_link($restrict_page)));

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
}
$price_page = new Crypto_Block();