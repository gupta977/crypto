<?php
class Crypto_Admin_Dashboard_Intro
{
    public function __construct()
    {
        add_filter('crypto_dashboard_tab', array($this, 'add_tabs'));
        add_action('crypto_dashboard_tab_content', array($this, 'add_content'));
    }

    public function add_tabs($tabs)
    {

        $extra_tabs = array("intro" => 'Introduction');

        // combine the two arrays
        $new = array_merge($tabs, $extra_tabs);
        //crypto_log($new);
        return $new;
    }

    public function add_content()
    {
        if (!isset($_GET['tab'])) {
            echo wp_kses_post($this->crypto_dashboard_content());
        }

        if (isset($_GET['tab']) && 'intro' == $_GET['tab']) {
            echo wp_kses_post($this->crypto_dashboard_content());
        }
    }

    public function crypto_dashboard_content()
    {
        ob_start();
?>
<div class="changelog section-getting-started">
    <div class="feature-section">
        <h2>Blockchain Tools</h2>
        <div class="wrap">

            <pre>
                We are trying to build all in one crypto tools.<br>
                Slowly we will be adding all major frequently used crypto tools that will help us to build Web3 platforms.
      </pre>

            <br>
            <b>Support: </b> <a href="<?php echo esc_url('https://wordpress.org/support/plugin/crypto/'); ?>">Wordpress
                Forum</a><br>
            <b>Telegram : </b> <a href="<?php echo esc_url('https://t.me/web3_domain'); ?>">@Web3_Domain</a><br>
            <b>Twitter: </b> <a href="<?php echo esc_url('https://twitter.com/web3_domain'); ?>">@Web3_Domain</a><br>
        </div>
    </div>
</div>
<?php
        $content = ob_get_clean();
        return $content;
    }
}
$add_tabs = new Crypto_Admin_Dashboard_Intro();