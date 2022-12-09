<?php
class Crypto_Domain_Search
{
    private $market_page;
    private $search_page;
    private $url_page;
    private $primary_domain;
    private $price_ether;

    function __construct()
    {

        add_shortcode('crypto-domain-search', array($this, 'search'));
        add_shortcode('crypto-domain-market', array($this, 'market'));
        add_filter('crypto_settings_tabs', array($this, 'add_tabs'));
        add_filter('crypto_settings_sections', array($this, 'add_section'));
        add_filter('crypto_settings_fields', array($this, 'add_fields'));
        $this->search_page = crypto_get_option('search_page', 'crypto_marketplace_settings', 0);
        $this->market_page = crypto_get_option('market_page', 'crypto_marketplace_settings', 0);
        $this->url_page = crypto_get_option('url_page', 'crypto_marketplace_settings', 0);
        $this->primary_domain = crypto_get_option('primary_domain', 'crypto_marketplace_settings', 'web3');
        $this->price_ether = crypto_get_option('price_ether', 'crypto_marketplace_settings', '5');


        add_filter('crypto_dashboard_tab', array($this, 'dashboard_add_tabs'));
        add_action('crypto_dashboard_tab_content', array($this, 'dashboard_add_content'));
    }

    //add_filter flexi_settings_tabs
    public function add_tabs($new)
    {

        $tabs = array(
            'marketplace'   => __('Marketplace', 'crypto'),

        );
        $new  = array_merge($new, $tabs);

        return $new;
    }


    //Add Section title
    public function add_section($new)
    {

        $sections = array(
            array(
                'id' => 'crypto_marketplace_settings',
                'title' => __('Sell Web3 Domain Name', 'crypto'),
                'description' => __('Sell your own web3 domain name like ENS, unstoppable. ', 'crypto'),
                'tab' => 'marketplace',
            ),
        );
        $new = array_merge($new, $sections);

        return $new;
    }

    //Add section fields
    public function add_fields($new)
    {
        $fields = array(
            'crypto_marketplace_settings' => array(
                array(
                    'name' => 'search_page',
                    'label' => __('Domain Search', 'crypto'),
                    'description' => __('Search & Mint Web3Domains', 'crypto') . '[crypto-domain-search]',
                    'type' => 'pages',
                    'sanitize_callback' => 'sanitize_key',
                ),
                array(
                    'name' => 'market_page',
                    'label' => __('My Domain', 'crypto'),
                    'description' => __('List minted Web3Domains', 'crypto') . '[crypto-domain-market]',
                    'type' => 'pages',
                    'sanitize_callback' => 'sanitize_key',
                ),
                array(
                    'name' => 'url_page',
                    'label' => __('Domain URL', 'crypto'),
                    'description' => __('Manage & Redirect Domain (http://_your_website_/web3/_web3domain_name_/. ', 'crypto') . '[crypto-domain-url]',
                    'type' => 'pages',
                    'sanitize_callback' => 'sanitize_key',
                ),


                array(
                    'name' => 'primary_domain',
                    'label' => __('Domain TLD', 'crypto'),
                    'description' => __('Top level domain name you offer to your visitors.', 'crypto'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_key',
                ),

                array(
                    'name' => 'price_ether',
                    'label' => __('Price in Ether', 'crypto'),
                    'description' => __('Enter the ether amount required to mint domain. It must be equal or greater then specified in contract address of primary domain.', 'crypto'),
                    'type' => 'text',
                    'size' => 'small',
                    'sanitize_callback' => 'sanitize_text_field',
                ),


            ),
        );
        $new = array_merge($new, $fields);

        return $new;
    }

    public function market()
    {

        ob_start();

        if (0 != $this->search_page) {
            $this->search_page = esc_url(get_page_link($this->search_page));
        } else {
            $this->search_page = "#";
        }
        if (0 != $this->market_page) {
            $this->market_page = esc_url(get_page_link($this->market_page));
        } else {
            $this->market_page = "#";
        }
?>
<script>
jQuery(document).ready(function() {
    jQuery("#crypto_domain_filter").on("keyup", function() {
        var value = jQuery(this).val().toLowerCase();
        //console.log(value);
        jQuery("#crypto_domain_result a").filter(function() {
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

crypto_is_metamask_Connected().then(acc => {
    if (acc.addr == '') {
        // console.log("Metamask not connected. Please connect first...");
        jQuery('#json_container').html(
            '<div class="crypto_alert-box crypto_error">Metamask not connected. Please connect first</div>'
        );
        jQuery("#crypto_loading").hide();
    } else {
        console.log("Connected to:" + acc.addr + "\n Network:" + acc.network);

        if ((acc.network != '137')) {
            var msg =
                "Change your network to Polygon (MATIC). Your connected network is " +
                acc.network;
            // jQuery("[id=crypto_msg_ul]").empty();
            // jQuery("[id=crypto_msg_ul]").append(msg).fadeIn("normal");
            jQuery('#json_container').html(
                '<div class="crypto_alert-box crypto_error">' + msg + '</div>'
            );
            jQuery("#crypto_loading").hide();
        } else {
            //  crypto_init();
            web3 = new Web3(window.ethereum);

            const connectWallet = async () => {
                const accounts = await ethereum.request({
                    method: "eth_requestAccounts"
                });
                var persons = [];
                account = accounts[0];
                //  console.log(`Connectedxxxxxxx account...........: ${account}`);
                jQuery("[id=crypto_wallet_address]").append(crypto_wallet_short(account, 4)).fadeIn(
                    "normal");
                // getBalance(account);
                await crypto_sleep(1000);
                var domain_count = await balanceOf(account);
                console.log(domain_count);
                if (domain_count == 0) {
                    var new_row =
                        '<div class="fl-panel-block fl-is-active"><span class="fl-panel-icon"><i class="fas fa-book" aria-hidden="true"></i></span><b>No domain found in this : ' +
                        account + '</b></div>';
                    jQuery("[id=crypto_domain_result]").append(new_row).fadeIn("normal");
                    jQuery("#crypto_loading").hide();

                }
                console.log(contract);
                persons.length = 0;
                for (let i = 0; i < domain_count; i++) {
                    try {
                        const nft = await contract.methods.tokenOfOwnerByIndex(account, i).call();
                        //console.log(nft);
                        var domain_name = await titleOf(nft);
                        console.log(nft + ' = ' + domain_name);
                        // jQuery("[id=crypto_msg_ul]").append("<li>" + domain_name + "</li>").fadeIn("normal");

                        var new_row =
                            '<a href="<?php echo get_site_url(); ?>/web3/' + domain_name +
                            '/?domain=manage" class="fl-panel-block fl-is-active"><span class="fl-panel-icon"><i class="fas fa-book" aria-hidden="true"></i></span>' +
                            domain_name + '</a>';
                        jQuery("[id=crypto_domain_result]").append(new_row).fadeIn("normal");

                        persons.push(domain_name);
                        //  console.log(i + " *** " + domain_count);
                        if (i + 1 == domain_count) {
                            console.log(persons);
                            jQuery("#crypto_loading").hide();

                        }
                    } catch (error) {
                        console.log(error.message);
                    }
                }
            };

            connectWallet();
            connectContract(contractAbi, contractAddress);




        }
    }
});
</script>


<div class="fl-columns">
    <div class="fl-column fl-is-three-quarters">

        <div class="fl-buttons fl-has-addons">
            <a href="<?php echo $this->search_page; ?>" class="fl-button">Search</a>
            <a href="<?php echo $this->market_page; ?>" class="fl-button  fl-is-success fl-is-selected">My Domains</a>
        </div>
    </div>
    <div class="fl-column">
        <div id="crypto_wallet_address" class="fl-tag"></div>
    </div>

</div>
<nav class="fl-panel">
    <p class="fl-panel-heading">
        My Web3 Domain Names
    </p>
    <div class="fl-panel-block">
        <p class="fl-control fl-has-icons-left">
            <input class="fl-input fl-is-rounded" type="text" placeholder="Search My Domain" id="crypto_domain_filter"
                style="width:90%">
            <span class="icon is-left">
                <i class="fas fa-search" aria-hidden="true"></i>
            </span>
        </p>
    </div>
    <div id="crypto_domain_result">

        <!--  Dynamic Result -->
        <div class="fl-panel-block fl-is-active" id="crypto_loading"><span class="fl-panel-icon"><i class="fas fa-book"
                    aria-hidden="true"></i></span> <img
                src="<?php echo esc_url(CRYPTO_PLUGIN_URL . '/public/img/load.gif'); ?>">
        </div>

        <div id="json_container"></div>


    </div>
</nav>
<?php
        $content = ob_get_clean();
        return $content;
    }


    public function search()
    {

        ob_start();
        if (0 != $this->search_page) {
            $this->search_page = esc_url(get_page_link($this->search_page));
        } else {
            $this->search_page = "#";
        }
        if (0 != $this->market_page) {
            $this->market_page = esc_url(get_page_link($this->market_page));
        } else {
            $this->market_page = "#";
        }
    ?>

<div class="fl-columns">
    <div class="fl-column fl-is-three-quarters">

        <div class="fl-buttons fl-has-addons">
            <a href="<?php echo $this->search_page; ?>" class="fl-button fl-is-success fl-is-selected">Search</a>
            <a href="<?php echo $this->market_page; ?>" class="fl-button">My Domains</a>
        </div>
    </div>
    <div class="fl-column">
        <div id="crypto_wallet_address"></div>
    </div>

</div>




<div class="fl-field fl-has-addons">
    <div class="fl-control fl-is-expanded">
        <input class="fl-input fl-is-large" type="text" placeholder="Search names or addresses"
            id="crypto_search_domain" style="position:unset">
    </div>
    <div class="fl-control">
        <a class="fl-button fl-is-info fl-is-large" id="crypto_search">
            Search
        </a>
    </div>
</div>

<div class="fl-card" id="crypto_panel">
    <header class="fl-card-header">
        <p class="fl-card-header-title" id="crypto_domain_name">
            Web3 Domain Name
        </p>
    </header>
    <div class="fl-card-content">
        <div class="fl-content" id="crypto_domain_result_box">
            <div id="crypto_loading" style="text-align:center;"> <img
                    src="<?php echo esc_url(CRYPTO_PLUGIN_URL . '/public/img/loading.gif'); ?>">
            </div>
            <article class="fl-message fl-is-primary" id="crypto_available">
                <div class="fl-message-body">
                    <div class="fl-tags fl-has-addons">
                        <span class="fl-tag fl-is-large" id="crypto_domain_name">Domain Name</span>
                        <span class="fl-tag fl-is-primary fl-is-large">Available</span>
                    </div>
                </div>
            </article>

            <article class="fl-message fl-is-danger" id="crypto_unavailable">
                <div class="fl-message-body">
                    <div class="fl-tags fl-has-addons">
                        <span class="fl-tag fl-is-large" id="crypto_domain_name">Domain Name</span>
                        <span class="fl-tag fl-is-danger fl-is-large">Unavailable</span>
                    </div>
                </div>
            </article>



        </div>
    </div>
    <footer class="fl-card-footer">
        <a href="#" class="fl-card-footer-item" id="crypto_register_domain">Register
            Domain</a>
        <a href="#" class="fl-card-footer-item" id="crypto_manage_domain">Manage Domain</a>
        <a href="<?php echo $this->url_page; ?>" target="_blank" class="fl-card-footer-item"
            id="crypto_ipfs_domain">Visit Site</a>
    </footer>
</div>

<script>
jQuery(document).ready(function() {
    jQuery("#crypto_panel").hide();
    jQuery("#crypto_available").hide();
    jQuery("#crypto_unavailable").hide();

    jQuery("#crypto_search").click(function() {
        jQuery("#crypto_panel").slideDown();
        var str = jQuery("#crypto_search_domain").val();
        // var result = str.replace(".<?php echo   $this->primary_domain; ?>", "");
        let result = str.includes("<?php echo $this->primary_domain; ?>");
        var final_domain = str + ".<?php echo $this->primary_domain; ?>";
        if (result) {
            final_domain = str;
        }
        console.log(final_domain);
        jQuery("[id=crypto_domain_name]").html(final_domain);

        crypto_check_w3d_name_json(final_domain);
    });

    jQuery("#crypto_search_domain").on("input", function() {
        jQuery("#crypto_panel").slideUp();
        jQuery("#crypto_available").hide();
        jQuery("#crypto_unavailable").hide();
        // Print entered value in a div box

    });


    function crypto_check_w3d_name_json(final_domain) {
        fetch('https://w3d.name/api/v1/index.php?domain=' + final_domain)
            .then(res => res.json())
            .then((out) => {
                console.log('Output: ', out);
                if (typeof out.error !== 'undefined') {
                    console.log("This domain name is available to mint.");
                    jQuery("#crypto_loading").hide();
                    jQuery("#crypto_available").show();
                    jQuery("#crypto_register_domain").attr("href",
                        "<?php echo get_site_url(); ?>/web3/" + final_domain +
                        "/?domain=manage");
                    jQuery("#crypto_manage_domain").hide();
                    jQuery("#crypto_ipfs_domain").hide();
                    jQuery("#crypto_register_domain").show();

                } else {
                    console.log("Already registered");
                    jQuery("#crypto_loading").hide();
                    jQuery("#crypto_unavailable").show();
                    jQuery("#crypto_register_domain").hide();
                    jQuery("#crypto_manage_domain").show();
                    jQuery("#crypto_ipfs_domain").show();
                    jQuery("#crypto_manage_domain").attr("href",
                        "<?php echo get_site_url(); ?>/web3/" + final_domain +
                        "/?domain=manage");
                    jQuery("#crypto_ipfs_domain").attr("href",
                        "<?php echo get_site_url(); ?>/web3/" + final_domain +
                        "/");
                }
            }).catch(err => console.error(err));
    }
});
</script>


<?php
        $content = ob_get_clean();
        return $content;
    }
    public function dashboard_add_tabs($tabs)
    {

        $extra_tabs = array("domain" => 'Domain Marketplace');

        // combine the two arrays
        $new = array_merge($tabs, $extra_tabs);
        //crypto_log($new);
        return $new;
    }

    public function dashboard_add_content()
    {
        if (isset($_GET['tab']) && 'domain' == $_GET['tab']) {
            echo wp_kses_post($this->crypto_dashboard_content());
        }
    }

    public function crypto_dashboard_content()
    {
        ob_start();
    ?>
<div class="changelog section-getting-started">
    <div class="feature-section">
        <h2>Become a Web3 Domain Name provider</h2>
        <div class="wrap">
            <b>Register your primary top-level domain (TLD) Web3 Domain Name from web3domain.org and start selling
                subdomains of it.</b>


        </div>
    </div>
</div>
<?php
        $content = ob_get_clean();
        return $content;
    }
}
new Crypto_Domain_Search();