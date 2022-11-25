=== Crypto ===
Contributors: odude
Donate link: https://odude.com/
Tags: crypto, login, metamask, NFT, Blockchain, Token
Requires at least: 3.0.1
Requires PHP: 5.5
Tested up to: 6.0.1
Stable tag: 1.18
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Crypto wallet login, donation, price box, content restrict & more..

== Description ==

Let your user to login via metamask & WalletConnect.
Automatic registration.
No more require to remember passwords for website. 

**[crypto-connect]** -  Use shortcode to any of your pages for login button.
**Secure** - Any transaction on your site has no connection with our server. It's totally dependent your server. If you are using any server's API, it only help wallet to connect but no controls over transactions. 

== Login/Register with Crypto Wallet ==

example: `[crypto-connect label="Connect Wallet" class="fl-button fl-is-info fl-is-light"]`

= Option 1: Web3Modal =
These are all the providers available with Web3Modal:

* WalletConnect
* Fortmatic
* Torus
* Portis
* Authereum
* Frame
* Bitski
* Venly
* DCent
* BurnerConnect
* MEWConnect
* Binance Chain Wallet
* WalletLink
* MetaMask

- Force to connect specified Chain ID
- Enable login button at Flexi & WooCommerce
- Execute specified javascript function as soon as wallet connected.
- [crypto-connect] Shortcode

= Option 2: Metamask Standalone =

* No API required
* Let user to login/register
* Login buttons at Flexi Gallery Login Form
* Login buttons at WooCommerce Login Form
* [crypto-connect-metamask] Shortcode

= Option 3: Metamask & WalletConnect by Moralis.io =

* Free API required from moralis.io
* Let user to login/register
* Auto switch to selected network chain
* Flexi Form support
* WooCommerce login form support
* [crypto-connect-moralis] Shortcode


== Donation Widget ==

* Get crypto donation into your desired wallet. 
* Option to set fixed crypto amount in specified token

== Crypto or Token Price ==

* Show latest price of token in selected currency
* Based on shortcode.
* Multiple token price at once.
* Cache system to restrict from frequent API call.
* Based no CoinMarketCap free API
* `[crypto-price symbol="MATIC,BTC,ETH" style="style1" currency="USD"]`

== Restrict Content/Page ==
* Show/Hide content if mentioned web3 domain available.
* Restrict full specific page. 
* Restrict part of the content controlled by shortcode. 
* `[crypto-block] Private article [/crypto-block]`

= Option 1: Restrict by Web3Domain =
* User must have specified NFT web3domain name from web3domain.org into wallet
* Restrict page shortcode: `[crypto-access-domain]`

= Option 2: Restrict by NFT or Crypto =
* User must have specified NFT & or number of tokens into wallet
* Select network chain (Ethereum Mainnet, Binance BNB Chain , Polygon Chain)
* Works with any smart contract address. 
* Restrict page shortcode: `[crypto-access-nft]`


[Live Demo](https://web3domain.org/user-dashboard/)

> If any suggestion, contact at navneet@odude.com

[GitHub](https://github.com/gupta977/crypto/)

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `crypto.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use shortcode [crypto-connect]

== Frequently Asked Questions ==

= What is Moralis ? =

Moralis is SDK for rapid blockchain development.

== Screenshots ==

1. Simple Login Interface with multiple wallet
2. Donation Widget
3. Crypto Price

== Changelog ==


= 1.19 =
* Removed moralis & web3modal due to security reasons
* All login shortcode now only supports [crypto-connect]


= 1.18 =
* Bug fixes during login & logout Web3Modal
* Removed Flexi support

= 1.17 =
* Content restriction based on web3domain & NFT tokens
* Short wallet address display after wallet connect
* Logout cross button added along with short address
* Updated language crypto.pot file.





== Upgrade Notice ==

= 1.0 =
Initial installation