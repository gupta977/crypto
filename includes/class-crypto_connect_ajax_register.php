<?php
class crypto_connect_ajax_process
{
    private $user;

    //Refresh function of specific position after some action
    public function __construct()
    {
        add_action("wp_ajax_crypto_connect_ajax_process", array($this, "crypto_connect_ajax_process"));
        add_action("wp_ajax_nopriv_crypto_connect_ajax_process", array($this, "crypto_connect_ajax_process"));
    }

    public function crypto_connect_ajax_process()
    {
        $id = $_REQUEST["id"];
        $param1 = $_REQUEST["param1"];
        $param2 = $_REQUEST["param2"];
        $param3 = $_REQUEST["param3"];
        $method_name = $_REQUEST["method_name"];

        $response = array(
            'error' => false,
            'msg' => 'No Message',
            'count' => '0',
        );

        $msg = $this->$method_name($id, $param1, $param2, $param3);
        $response['msg'] = $msg;
        echo wp_json_encode($response);

        die();
    }

    public function get_userid_by_meta($key, $value)
    {
        global $wpdb;
        $users = $wpdb->get_results("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$key' AND meta_value = '$value'");
        if ($users) {
            foreach ($users as $user) {
                return $user->user_id;
            }
        } else {
            return 0;
        }
    }

    public function check($id, $param1, $param2, $param3)
    {
        //flexi_log("ame hree ---" . $param1);
        //Check if user is logged in
        if (is_user_logged_in()) {

            //Check if this wallet is already linked with other account
            $the_user_id = $this->get_userid_by_meta('crypto_wallet', trim($param1));

            if ($the_user_id != 0) {
                //User is found with same wallet address.
                //Delete the wallet and link with current user
                delete_user_meta($the_user_id, 'crypto_wallet');
                //Assign this wallet to current user
                update_user_meta(get_current_user_id(), 'crypto_wallet', trim($param1));
                //flexi_log("old found and replaced " . $param1);
            } else {
                //Assign this wallet to current user
                update_user_meta(get_current_user_id(), 'crypto_wallet', trim($param1));
                // flexi_log("new added : " . $param1);
            }
        }

        return "done";
    }

    public function register($id, $param1, $param2, $param3)
    {
        //flexi_log("ame hree" . $param1);

        if (!is_user_logged_in()) {
            $user_login = trim($param1);

            //Check if this wallet is already linked with other account
            $the_user_id = $this->get_userid_by_meta('crypto_wallet', trim($param1));

            if ($the_user_id != 0) {
                //This wallet is already assigned to one of the user
                //Log that user in
                $user = get_user_by('id', $the_user_id);
                return $this->log_in($user->user_login);
            } else {

                $existing_user_id = username_exists($user_login);

                if ($existing_user_id) {
                    //echo __('Username already exists.', 'crypto_connect_login');
                    // flexi_log("Username already exists " . $user_login);
                    return $this->log_in($user_login);
                } else {
                    //  flexi_log("NEw User " . $user_login);
                    if (is_multisite()) {
                        // Is this obsolete or not???
                        // https://codex.wordpress.org/WPMU_Functions says it is?
                        // But then, the new REST api uses it. What is going on?
                        $user_id = wpmu_create_user($user_login, wp_generate_password(), '');
                        if (!$user_id) {
                            return 'error';
                        }
                    } else {
                        $user_id = wp_create_user($user_login, wp_generate_password());
                        if (is_wp_error($user_id)) {
                            // echo $user_id;
                            // flexi_log(" AM into regiseter " . $param1);
                        }
                    }
                    update_user_meta($user_id, 'crypto_wallet', trim($param1));
                    return $this->log_in($user_login);
                }
            }
        }
    }

    public function log_in($username)
    {
        //---------------------Automatic login--------------------

        if (!is_user_logged_in()) {

            if ($user = get_user_by('login', $username)) {

                clean_user_cache($user->ID);
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true, is_ssl());

                $user = get_user_by('id', $user->ID);
                update_user_caches($user);

                do_action('wp_login', $user->user_login, $user);

                if (is_user_logged_in()) {

                    return "success";
                } else {
                    return "fail";
                }
            }
        } else {
            return "wrong";
        }
    }
}
$process = new crypto_connect_ajax_process();