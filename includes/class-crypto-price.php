<?php
class Crypto_Price
{
	private $help = ' <a style="text-decoration: none;" href="#" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>';

	private $curr;
	private $cache;

	public function __construct()
	{
		$this->curr = crypto_get_option('base_curr', 'crypto_price_settings', 'USD');
		$this->cache = crypto_get_option('price_cache', 'crypto_price_settings', '600');

		add_shortcode('crypto-price', array($this, 'crypto_price_shortcode'));

		add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
		add_filter('crypto_settings_sections', array($this, 'add_section'));
		add_filter('crypto_settings_fields', array($this, 'add_fields'));
	}


	//add_filter flexi_settings_tabs
	public function add_tabs($new)
	{

		$tabs = array(
			'price'   => __('Price', 'crypto'),

		);
		$new  = array_merge($new, $tabs);

		return $new;
	}


	//Add Section title
	public function add_section($new)
	{

		$sections = array(
			array(
				'id' => 'crypto_price_settings',
				'title' => __('Crypto Price Box', 'crypto'),
				'description' => __('Let users to connect via Metamask, WalletConnect & many more wallet', 'crypto') . "<br>" . "Project by <a target='_blank' href='" . esc_url('https://github.com/Web3Modal') . "'>Web3Modal</a>",
				'tab' => 'price',
			),
		);
		$new = array_merge($new, $sections);

		return $new;
	}

	//Add section fields
	public function add_fields($new)
	{
		$fields = array(
			'crypto_price_settings' => array(

				array(
					'name' => 'base_curr',
					'label' => __('Token Name Symbol', 'crypto'),
					'description' => __('If specified, network wallet changes notice displayed. Eg. 1 for Ethereum Mainnet & 137 for Matic', 'crypto') . " <a href='https://docs.moralis.io/moralis-server/web3-sdk/intro' target='_blank'> Reference </a>",
					'type' => 'text',
				),
				array(
					'name' => 'price_api',
					'label' => __('CoinMarketCap API', 'crypto'),
					'description' => __('If specified, network wallet changes notice displayed. Eg. 1 for Ethereum Mainnet & 137 for Matic', 'crypto') . " <a href='https://docs.moralis.io/moralis-server/web3-sdk/intro' target='_blank'> Reference </a>",
					'type' => 'text',
				),
				array(
					'name' => 'price_cache',
					'label' => __('Crypto Data Caching', 'crypto'),
					'description' => __('If specified, network wallet changes notice displayed. Eg. 1 for Ethereum Mainnet & 137 for Matic', 'crypto') . " <a href='https://docs.moralis.io/moralis-server/web3-sdk/intro' target='_blank'> Reference </a>",
					'type' => 'text',
				),

			),
		);
		$new = array_merge($new, $fields);

		return $new;
	}


	public function crypto_price_info($coin_symbol = 'BTC')
	{
		$data_option_name = $coin_symbol . '_market_data';
		$timestamp_option_name = $coin_symbol . '_market_timestamp';
		$current_timestamp = date('Y-m-d\TH:i:s' . substr((string)microtime(), 1, 4) . '\Z');
		$cache_time = $this->cache;
		if ($cache_time == false) {
			$cache_time = 600;
		}
		if (get_option($timestamp_option_name) && (strtotime($current_timestamp) - strtotime(get_option($timestamp_option_name))) < $cache_time) {
			return get_option($data_option_name);
		} else {
			$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
			$parameters = [
				'symbol' => $coin_symbol,
			];

			$qs = http_build_query($parameters); // query string encode the parameters
			$request = "{$url}?{$qs}"; // create the request URL
			$args = array(
				'headers' => array(
					'Accepts' => 'application/json',
					'X-CMC_PRO_API_KEY' => crypto_get_option('price_api', 'crypto_price_settings', ''),
				)
			);
			$response = wp_remote_retrieve_body(wp_remote_get($request, $args));
			update_option($data_option_name, $response);
			update_option($timestamp_option_name, $current_timestamp);
			return $response;
		}
	}


	public function crypto_price_shortcode($atts)
	{


		$put = "";
		ob_start();

		extract(shortcode_atts(array(
			'symbol' => 'xxx',
		), $atts));
		if ($symbol == 'xxx') {
			return 'Please add a coin symbol to fetch its data. For example, [crypto-price symbol="BTC"].';
		} else {
			$curr = $this->curr;
			$data = json_decode($this->crypto_price_info($symbol));
			return round($data->data->$symbol->quote->$curr->price, 2) . ' USD';
		}

?>
<span>
    xxxxxxxxxxxxxxxxxxxxx
</span>

<?php
		$put = ob_get_clean();

		return $put;
	}
}
$price_page = new Crypto_Price();