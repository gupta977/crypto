"use strict";

/**
 * Example JavaScript code that interacts with the page and Web3 wallets
 */

// Unpkg imports
const Web3Modal = window.Web3Modal.default;
const WalletConnectProvider = window.WalletConnectProvider.default;
const Fortmatic = window.Fortmatic;
const evmChains = window.evmChains;

// Web3modal instance
let web3Modal

// Chosen wallet provider given by the dialog window
let provider;


// Address of the selected account
let selectedAccount;


/**
 * Kick in the UI action after Web3modal dialog has chosen a provider
 */
async function fetchAccountData() {

  // Get a Web3 instance for the wallet
  const web3 = new Web3(provider);
//console.log("Provider is "+provider);
  //console.log("Web3 instance is", web3);

  // Get connected chain id from Ethereum node
  const chainId = await web3.eth.getChainId();
  //console.log("Connected chainId: "+chainId);
  const chainId_new = crypto_connectChainAjax.chainId;
  //console.log("chainid new: "+chainId_new);

  const execute_JS = crypto_connectChainAjax.executeJS;


  if ((chainId != chainId_new) && chainId_new != '') {
    //const chainData = evmChains.getChain(chainId_new);
    var msg = "Change your network to: " + chainId_new + ". Your connected network is " + chainId;
    jQuery("[id=wallet_msg]").empty();
    jQuery("#flexi_notification_box").fadeIn("slow");
    jQuery("[id=wallet_msg]").append(msg).fadeIn("normal");
    onDisconnect();
  }

  // Load chain information over an HTTP API
  const chainData = evmChains.getChain(chainId);
  //console.log("Connected network "+ chainData.name);

  // Get list of accounts of the connected wallet
  const accounts = await web3.eth.getAccounts();

  // MetaMask does not give you all accounts, only the selected account
  console.log("Got accounts", accounts);
  selectedAccount = accounts[0];

  console.log("#selected-account " + selectedAccount);
  process_login_register(selectedAccount);

  jQuery("[id=wallet_addr]").empty();
  jQuery("#wallet_addr_box").fadeIn("slow");
  jQuery("[id=wallet_addr]").append(crypto_wallet_short(accounts[0], 4)).fadeIn("normal");
  jQuery("[id=btn-login]").hide();


  if (execute_JS != '') {
       let some_code = execute_JS;
    (new Function(some_code))()
  }
}



/**
 * Fetch account data for UI when
 * - User switches accounts in wallet
 * - User switches networks in wallet
 * - User connects wallet initially
 */
async function refreshAccountData() {

  // If any current data is displayed when
  // the user is switching acounts in the wallet
  // immediate hide this data
  //document.querySelector("#connected").style.display = "none";
  //document.querySelector("#prepare").style.display = "block";

  // Disable button while UI is loading.
  // fetchAccountData() will take a while as it communicates
  // with Ethereum node via JSON-RPC and loads chain data
  // over an API call.
  //document.querySelector("#btn-connect").setAttribute("disabled", "disabled")
  await fetchAccountData(provider);
  // document.querySelector("#btn-connect").removeAttribute("disabled")
}


/**
 * Connect wallet button pressed.
 */
async function onConnect() {

  console.log("Opening a dialog", web3Modal);
  try {
    provider = await web3Modal.connect();
  } catch (e) {
    //console.log("Could not get a wallet connection", e);
    jQuery("[id=wallet_msg]").empty();
    jQuery("#flexi_notification_box").fadeIn("slow");
    jQuery("[id=wallet_msg]").append("Could not get a wallet connection").fadeIn("normal");
    return;
  }

  // Subscribe to accounts change
  provider.on("accountsChanged", (accounts) => {
    fetchAccountData();
  });

  // Subscribe to chainId change
  provider.on("chainChanged", (chainId) => {
    fetchAccountData();
  });

  // Subscribe to networkId change
  provider.on("networkChanged", (networkId) => {
    fetchAccountData();
  });

  await refreshAccountData();
}

/**
 * Disconnect wallet button pressed.
 */
async function onDisconnect() {

  console.log("Killing the wallet connection", provider);

  // TODO: Which providers have close method?
  if (provider.close) {
    await provider.close();

    // If the cached provider is not cleared,
    // WalletConnect will default to the existing session
    // and does not allow to re-scan the QR code with a new wallet.
    // Depending on your use case you may want or want not his behavir.
    await web3Modal.clearCachedProvider();
    provider = null;
  }

  selectedAccount = null;

  // Set the UI back to the initial state
  // document.querySelector("#prepare").style.display = "block";
  // document.querySelector("#connected").style.display = "none";
}

/**
 * Main entry point.
 */
window.addEventListener('load', async () => {
  init();



});

jQuery(document).ready(function () {
  jQuery("[id=flexi_notification_box]").hide();
  jQuery("[id=delete_notification]").click(function () {
    jQuery("[id=flexi_notification_box]").fadeOut("slow");
  });

  jQuery("[id=btn-login]").click(function () {
    //alert("Login");
    //console.log(jQuery(this).attr("data-nonce"));
   onConnect();
    //login();
  });


  jQuery("[id=btn-logout]").click(function () {
    //alert("Logout");
    onDisconnect();
    location.reload();
  });


});

function create_link_crypto_connect_login(nonce, postid, method, param1, param2, param3) {

  let newlink = document.createElement('a');
  newlink.innerHTML = '';
  newlink.setAttribute('id', 'crypto_connect_ajax_process');
  // newlink.setAttribute('class', 'xxx');
  newlink.setAttribute('data-nonce', nonce);
  newlink.setAttribute('data-id', postid);
  newlink.setAttribute('data-method_name', method);
  newlink.setAttribute('data-param1', param1);
  newlink.setAttribute('data-param2', param2);
  newlink.setAttribute('data-param3', param3);
  document.body.appendChild(newlink);
}

function process_login_register(curr_user) {
  //alert("register " + curr_user);
  //Javascript version to check is_user_logged_in()
  if (jQuery('body').hasClass('logged-in')) {
    // console.log("check after login");
    create_link_crypto_connect_login('nonce', '', 'check', curr_user, '', '');
    //jQuery("#crypto_connect_ajax_process").click();
    setTimeout(function () {
      jQuery('#crypto_connect_ajax_process').trigger('click');
    }, 1000);


  } else {
    // console.log("register new");
    create_link_crypto_connect_login('nonce', '', 'register', curr_user, '', '');
    //jQuery("#crypto_connect_ajax_process").click();
    setTimeout(function () {
      jQuery('#crypto_connect_ajax_process').trigger('click');
    }, 1000);

  }
}  