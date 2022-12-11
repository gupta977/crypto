<?php
class Crypto_Domain_INFO
{
    private $market_page;
    private $search_page;
    private $url_page;
    private $price_ether;

    public function __construct()
    {

        add_shortcode('crypto-domain-info', array($this, 'start'));
        $this->search_page = crypto_get_option('search_page', 'crypto_marketplace_settings', 0);
        $this->market_page = crypto_get_option('market_page', 'crypto_marketplace_settings', 0);
        $this->url_page = crypto_get_option('url_page', 'crypto_marketplace_settings', 0);
        $this->price_ether = crypto_get_option('price_ether', 'crypto_marketplace_settings', '5');
    }

    public function start()
    {
        ob_start();
        global $wp_query;
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


        if (isset($_GET['domain'])) {
            // echo $_GET['domain'];
?>

<script>
jQuery(document).ready(function() {
    var final_domain = "<?php echo $_GET['domain']; ?>";

    jQuery("[id=crypto_domain_name]").html(final_domain);

    jQuery("#crypto_manage_domain").attr("href",
        "<?php echo get_site_url(); ?>/web3/" + final_domain +
        "/?domain=manage");
    jQuery("#crypto_ipfs_domain").attr("href",
        "<?php echo get_site_url(); ?>/web3/" + final_domain +
        "/");
});
</script>
<div class="fl-columns">
    <div class="fl-column fl-is-three-quarters">

        <div class="fl-buttons fl-has-addons">
            <a href="<?php echo $this->search_page; ?>" class="fl-button ">Search</a>
            <a href="<?php echo $this->market_page; ?>" class="fl-button">My Domains</a>
            <a href="#" class="fl-button fl-is-success fl-is-selected">Domain Information</a>
        </div>
    </div>
    <div class="fl-column">
        <div id="crypto_wallet_address" class="fl-tag"></div>
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


            <div id="crypto_loading_url" style="text-align:center;"> Redirection on progress..
                <br>
                <a href="#" id="crypto_loading_url_link">External Link</a>
            </div>


            <div id="json_container"></div>

        </div>

    </div>
    <footer class="fl-card-footer">
        <a href="#" class="fl-card-footer-item" id="crypto_blockchain_url">Blockchain Record</a>
        <a href="#" class="fl-card-footer-item" id="crypto_manage_domain">Manage Domain</a>
        <a href="<?php echo $this->url_page; ?>" target="_blank" class="fl-card-footer-item"
            id="crypto_ipfs_domain">Visit Site</a>
    </footer>
</div>
<?php
        } else {
            echo "No domain";
        }


        $content = ob_get_clean();
        return $content;
    }
}
new Crypto_Domain_INFO();