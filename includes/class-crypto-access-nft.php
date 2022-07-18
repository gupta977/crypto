<?php
class Crypto_Access_NFT
{
	public function __construct()
	{
		add_filter('crypto_settings_sections', array($this, 'add_section'));
		add_filter('crypto_settings_fields', array($this, 'add_fields'));
	}

	//Add Section title
	public function add_section($new)
	{

		$sections = array(
			array(
				'id' => 'crypto_access_other',
				'title' => __('NFT Access', 'crypto'),
				'description' => __('Let users to connect via Metamask or WalletConnect.', 'crypto'),
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
					'label' => __('Default Network Chain ID', 'crypto'),
					'description' => __('Eg. 1 for Ethereum Mainnet & 137 for Matic', 'crypto'),
					'type' => 'number',
					'size' => 'small',
					'sanitize_callback' => 'intval',
				),
				array(
					'name' => 'chain_contract',
					'label' => __('NFT contract address', 'crypto'),
					'description' => __('Contract address of NFT starts with 0x...', 'crypto'),
					'size' => 20,
					'type' => 'text',
				),

				array(
					'name' => 'nft_count',
					'label' => __('NFT or Domain count', 'crypto'),
					'description' => __('Enter the number of NFT/domain must be available.', 'crypto'),
					'type' => 'number',
					'size' => 'small',
					'sanitize_callback' => 'intval',
				),
			)
		);
		$new = array_merge($new, $fields);
		return $new;
	}
}
new Crypto_Access_NFT();