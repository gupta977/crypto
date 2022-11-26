(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

function crypto_wallet_short(str, keep) {
    var len = str.length,
        re = new RegExp("(.{" + keep + "})(.{" + (len - keep * 2) + "})(.{" + keep + "})", "g")
   // console.log(re)
    return str.replace(re, function(match, a, b, c) {
        var xx = a + ("" + b).replace(/./g, "*") + c;
        return xx.replace('**********************************', '***');
    });
}


/** add a parameter at the end of the URL. Manage '?'/'&', but not the existing parameters.
   *  does escape the value (but not the key)
   */


const crypto_uniqueId = (length=16) => {
	return parseInt(Math.ceil(Math.random() * Date.now()).toPrecision(length).toString().replace(".", ""))
  }

  //if (crypto_connectChainAjax == 'undefined') {
	
	const crypto_plugin_url = crypto_connectChainAjax.crypto_plugin_url;
	const contractAbi = crypto_plugin_url+"/public/js/web3domain.json?8g"; // Update with an ABI file, for example "./sampleAbi.json"

  //}

//console.log(crypto_plugin_url);
   // const contractAddress = "0x8344FbC87f18830054f7b6BA82F02E0fe4ACab61"; // Update with the address of your smart contract
	const contractAddress = "0x545c3915f30204081A05894ee91330d9728C3718"; // Update with the address of your smart contract
     let web3; // Web3 instance
    let contract; // Contract instance
    let account; // Your account as will be reported by Metamask