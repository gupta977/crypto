=== Crypto ===
Contributors: odude
Donate link: https://odude.com/
Tags: crypto, login, metamask, walletconnect, blockchain, donation
Requires at least: 3.0.1
Requires PHP: 5.5
Tested up to: 5.9
Stable tag: 1.16
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Crypto wallet login, donation, price & more..

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

= 1.16 =
* Fixed [crypto-connect] shortcode

= 1.15 =
* All 3 login system in tab menu.
* Everyone requested to update settings again.

= 1.14 =
* Execute javascript function as soon as wallet connected in Web3Modal

= 1.13 =
* Added parameters for [crypto-connect] shortcode
* fixed error in login action hook


= 1.12 =
* Change cache time of price to 1 second if error. 

= 1.11 =
* Added Crypto Price, based on shortcode

= 1.10 =
* Added donation widget

= 1.9 =
* If username matches with wallet address, access login (It ignores linked wallet to any other username)

= 1.8 =
* Added Web3Modal login option

= 1.7 =
* Fixed error

= 1.6 =
* Added standalone Metamask login

= 1.5 =
* Added WooCommerce support

= 1.4 =
* Added wordpress assets images
* Localize online javascripts


== Upgrade Notice ==

= 1.0 =
Initial installation