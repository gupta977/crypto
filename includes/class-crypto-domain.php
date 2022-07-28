<?php
class Crypto_Domain
{
    function __construct()
    {
        add_filter('init', array($this, 'rw_init'));
        add_filter('query_vars', array($this,  'rw_query_vars'));
        add_filter('init', array($this, 'start'));
        add_shortcode('crypto-domain', array($this, 'start'));


        add_filter('crypto_dashboard_tab', array($this, 'dashboard_add_tabs'));
        add_action('crypto_dashboard_tab_content', array($this, 'dashboard_add_content'));
    }

    public function rw_query_vars($aVars)
    {
        $aVars[] = "web3domain"; // represents the name of the variable as shown in the URL
        return $aVars;
    }

    public function rw_init()
    {
        add_rewrite_rule(
            '^web3/([^/]*)$',
            'index.php?web3domain=$matches[1]&page_id=777',
            'top'
        );
    }

    public function start()
    {
        global $wp_query;
        if (isset($wp_query->query_vars['web3domain'])) {
            $subdomain = $wp_query->query_vars['web3domain'];
            $contract = "0xA36b16342A5706Cd41Fe56b9AC7D2d1e88F89b8e";
?>
<script>
const serverUrl = 'https://speedy-nodes-nyc.moralis.io/2c75242a1300b82823e16514/polygon/mumbai';
const contractAddress = '0xA36b16342A5706Cd41Fe56b9AC7D2d1e88F89b8e';
var domain = "<?php echo $subdomain; ?>";
let web3 = new Web3(Web3.givenProvider || serverUrl);
var x;

function web3domain_id(domain, type) {

    console.log("Getting ID");
    const ABI_ID = [{
        "inputs": [{
            "internalType": "string",
            "name": "title",
            "type": "string"
        }],
        "name": "getID",
        "outputs": [{
            "internalType": "string",
            "name": "",
            "type": "string"
        }],
        "stateMutability": "view",
        "type": "function",
        "constant": true
    }, ];
    const myContract = new web3.eth.Contract(ABI_ID, contractAddress);

    myContract.methods.getID(domain).call().then(function(id) {
        console.log(id);
        jQuery("#web3domain_id").val(id);
        if (type == 'uri') {
            web3domain_uri(domain, id);
        }
    }).catch(function(tx) {
        console.log(tx);

    });
}

async function web3domain_uri(domain, id) {


    console.log("Getting URI " + id);

    const ABI_ID = [{
        inputs: [{
            internalType: "uint256",
            name: "tokenId",
            type: "uint256",
        }, ],
        name: "tokenURI",
        outputs: [{
            internalType: "string",
            name: "",
            type: "string",
        }, ],
        stateMutability: "view",
        type: "function",
    }, ];

    const myContract = new web3.eth.Contract(ABI_ID, contractAddress);

    myContract.methods.tokenURI(id).call().then(function(uri) {
        console.log(uri);
        jQuery("#web3domain_uri").val(uri);
        get_IPFS_from_JSON(uri);
    }).catch(function(tx) {
        console.log(tx);

    });

}

function resolve(domain, type) {
    if (type == 'id') {
        web3domain_id(domain, '');
    } else if (type == 'uri') {
        web3domain_id(domain, 'uri');
    } else {
        console.log("nothing to do");
    }
}

function get_IPFS_from_JSON(url) {
    var _c = new Date().getTime();
    console.log(url);
    fetch(url)
        .then(res => res.json())
        .then((out) => {
            var web_url = out.records['50'].value;

            console.log("https://ipfs.io/ipfs/" + web_url);
            //window.location.replace("https://ipfs.io/ipfs/" + web_url);
            // $("#domain_name").html(out.name);
            //  document.title = out.name + ".net";
            //  $("#profile_name").html(out.records['1'].value);
            //  $("#profile_image").attr("src", out.image);
            //  $("#profile_desp").html(out.description);
        }).catch(function(e) {
            console.log(e);
            // document.getElementById("loading").style.visibility = "hidden";
        });

}

resolve(domain, 'id');
resolve(domain, 'uri');
</script>

<h1 id="step1">Connecting to SignID Blockchain Smart Contract</h1>
ID <input id="web3domain_id" value=".."><br>
URI <input id="web3domain_uri" value="..">
<?php
        }
    }

    public function dashboard_add_tabs($tabs)
    {

        $extra_tabs = array("access" => 'Member Restrict');

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
                <li>* You wallet may have 100 'Some Token' but while calculating it may show as 10000. So you must enter
                    10000 instead 100</li>
                <li>* By default public API is used in Web3 Modal. Get your own free for faster and site uptime.</li>
            </ul>
            <hr>
            <b>Do you know about <a href='<?php echo esc_url('https://web3domain.org/'); ?>'
                    target='_blank'>Web3Domain</a> ? </b><br>
            <ul>
                <li>
                    * It is best option to earn for membership by letting user to obtain subdomain of your web3 primary
                    domain.</li>
                <li>* Each subdomain sold will have 80% commission in your wallet and rest 20% is commission fees.</li>
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
new Crypto_Domain();