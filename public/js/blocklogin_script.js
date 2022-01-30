function create_link_blocklogin(nonce, postid, method, param1, param2, param3) {

    newlink = document.createElement('a');
    newlink.innerHTML = '';
    newlink.setAttribute('id', 'block_ajax_process');
    // newlink.setAttribute('class', 'xxx');
    newlink.setAttribute('data-nonce', nonce);
    newlink.setAttribute('data-id', postid);
    newlink.setAttribute('data-method_name', method);
    newlink.setAttribute('data-param1', param1);
    newlink.setAttribute('data-param2', param2);
    newlink.setAttribute('data-param3', param3);
    document.body.appendChild(newlink);
}

function blocklogin_init() {
    /* Moralis init code */
    const serverUrl = BlockChainAjax.serverUrl;
    const appId = BlockChainAjax.appId;
    Moralis.start({
        serverUrl,
        appId
    });
}

jQuery(document).ready(function () {


    jQuery("[id=flexi_notification_box]").hide();
    jQuery("[id=delete_notification]").click(function () {
        jQuery("[id=flexi_notification_box]").fadeOut("slow");
    });

    jQuery("[id=btn-login]").show();
    jQuery("[id=btn-login_wc]").show();

    jQuery("[id=btn-login]").click(function () {
        //alert("Login");

        login();
    });

    jQuery("[id=btn-login_wc]").click(function () {
        // alert("Login");
        login('walletconnect');
    });

    jQuery("[id=btn-logout]").click(function () {
        //alert("Logout");
        logOut();
    });




    let user;
    let web3;
    let result = '';
    //const provider = 'walletconnect';
    const provider = '';
    console.log(web3);
    if (web3 == undefined) {

        // alert("not defiend");
        blocklogin_init();
        jQuery("[id=btn-logout]").hide();
    }

    onConnected();

});

async function login(provider) {
    try {
        user = await Moralis.authenticate({
            provider
        });
        web3 = await Moralis.enableWeb3({
            provider
        });

    } catch (error) {
        console.log('authenticate failed', error);
        console.log(error.message);
        jQuery("[id=wallet_msg]").empty();
        jQuery("#flexi_notification_box").fadeIn("slow");
        jQuery("[id=wallet_msg]").append(error.message).fadeIn("normal");
        logOut();
    }
    renderApp();
    jQuery("[id=btn-login]").hide();
    jQuery("[id=btn-login_wc]").hide();
    jQuery("[id=btn-logout]").show();


}


function renderApp() {
    user = Moralis.User.current();

    if (user) {
        console.log("HELLO USER! YOU HAVE SUCCESSFULLY LOGGED IN", user);
        var curr_user = user.get("ethAddress");
        console.log("HELLO " + curr_user);

        //Javascript version to check is_user_logged_in()
        if (jQuery('body').hasClass('logged-in')) {
            console.log("check after login");
            create_link_blocklogin('<?php echo $nonce; ?>', '', 'check', curr_user, '', '');
            //jQuery("#block_ajax_process").click();
            setTimeout(function () {
                jQuery('#block_ajax_process').trigger('click');
            }, 1000);
            onConnected();

        } else {
            console.log("register new");
            create_link_blocklogin('<?php echo $nonce; ?>', '', 'register', curr_user, '', '');
            //jQuery("#block_ajax_process").click();
            setTimeout(function () {
                jQuery('#block_ajax_process').trigger('click');
            }, 1000);
            onConnected();
        }
    } else {
        console.log("User not connected")

    }

}

async function enableWeb3() {
    try {
        web3 = await Moralis.enableWeb3({
            provider
        });
    } catch (error) {
        console.log('testCall failed', error);
    }
    renderApp();
}




async function logOut() {
    try {
        await Moralis.User.logOut();
    } catch (error) {
        console.log('logOut failed', error);
    }
    result = '';
    //renderApp();
    await window.Moralis.Web3.cleanup();
    // web3.currentProvider.disconnect();
    console.log("YOU ARE NOW LOGGED OUT! SORRY TO SEE YOU GO");
    jQuery("[id=btn-login]").show();
    jQuery("[id=btn-login_wc]").show();
    jQuery("[id=btn-logout]").hide();
}

async function onConnected() {
    // login();
    const web3 = await Moralis.enableWeb3();
    const chainId = await Moralis.getChainId();
    console.log(chainId); // 97 for bsc test
    let user = Moralis.User.current();

    if (user) {
        jQuery("[id=btn-login]").hide();
        jQuery("[id=btn-login_wc]").hide();
        jQuery("[id=btn-logout]").show();
        var curr_user = user.get("ethAddress");
        console.log("HELLO " + curr_user);
    } else {
        jQuery("[id=btn-logout]").hide();
    }
    if (chainId != '0x13881') {

        alert("Changing network to Polygon (MATIC)");
        const chainId = "0x13881"; //Ethereum Mainnet https://docs.moralis.io/moralis-server/web3-sdk/intro
        const chainIdHex = await Moralis.switchNetwork(chainId);
    }


    const AccountChange = Moralis.onAccountChanged(function (accounts) {
        logOut();
        //console.log('New User: ' + currentUser.get("ethAddress"));
        location.reload();
    });

    const networkChange = Moralis.onChainChanged(function (accounts) {
        location.reload();
    });



}