<?php
/**
 * Plugin Name: WP Customize Password
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: WP Customize Password
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://www.mywebsite.com
 */

if(!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if(!class_exists('WPPass')):
/**
 * Main Class.
 *
 */
final class WPPass {
    public $version = '1.0.0';
    public $dbVersion = '1.0.0';
    public $addonName = 'wp-custom-pass';

    public function __construct() {
        $this->defines();
        $this->hooks();

		if ( is_admin() ) {
			include_once( dirname( __FILE__ ) . '/admin.php' );
		}
    }

    public function defines() {
    }

    public function hooks() {
        add_filter('the_password_form', [$this, 'customize_the_password_form']);
        add_filter('post_password_required', [$this, 'customize_post_password_required']);
        add_action('login_form_custom_pass', [$this, 'customize_pass']);

        register_activation_hook(__FILE__, [$this, 'onActivate']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
    }

    public function customize_the_password_form() {
        global $post;

        $labelEmail  = 'pw-email-' . ( empty( $post->ID ) ? rand() : $post->ID );
        $label  = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
        $output = '<form action="' . esc_url( site_url( 'wp-login.php?action=custom_pass', 'login_post' ) ) . '" class="post-password-form" method="post" style="margin-top:50px; text-align:center;">';
        if($post->post_password == 'emailonly') {
            $output .= '<p>' . __( 'This page is protected, strictly private and confidential. Please enter your email to access this page:' ) . '</p>';
        } else {
            $output .= '<p>' . __( 'This page is password protected, strictly private and confidential. Please enter your email and password to access this page:' ) . '</p>';
        }
        $output .= '<p><label for="' . $labelEmail . '">' . __( 'Email:&nbsp;&nbsp;&nbsp;' ) . ' <input name="post_email" id="' . $labelEmail . '" type="email" size="20" /></label> </p>';
        if($post->post_password == 'emailonly') {
            $output .= '<input name="post_password" type="hidden" value="' . $post->post_password . '" />';
        } else {
            $output .= '<p><label for="' . $label . '">' . __( 'Password:' ) . ' <input name="post_password" id="' . $label . '" type="password" size="20" /></label></p>';
        }
            $output .= '<p><input type="submit" name="Submit" value="' . esc_attr_x( 'Enter', 'post password form' ) . '" /></p></form>
';
        return $output;
    }

    public function customize_post_password_required() {
        global $post, $wpdb;
        if ( empty( $post->post_password ) ) {
            return false;
        }

        if ( ! isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) ) {
            return true;
        }

        require_once ABSPATH . WPINC . '/class-phpass.php';
        $hasher = new PasswordHash( 8, true );

        $hash = wp_unslash( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );
        if ( 0 !== strpos( $hash, '$P$B' ) ) {
            $required = true;
        } else {
            $required = ! $hasher->CheckPassword( $post->post_password, $hash );
            if( !$required && $_COOKIE[ 'wp-postemail_' . COOKIEHASH]) {
                $wpdb->insert($wpdb->prefix . 'wpp_page_views', array (
                    'post_id' => $post->ID,
                    'email' => $_COOKIE[ 'wp-postemail_' . COOKIEHASH],
                    'ip_address' => (!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
                        (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 
                            $_SERVER['REMOTE_ADDR']
                        )
                    )
                ), array('%d', '%s', '%s') );
                unset($_COOKIE[ 'wp-postemail_' . COOKIEHASH]);
            }
        }

        return $required;
    }

    public function customize_pass() {
		if ( ! array_key_exists( 'post_password', $_POST ) ) {
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new PasswordHash( 8, true );

		/**
		 * Filters the life span of the post password cookie.
		 *
		 * By default, the cookie expires 10 days from creation. To turn this
		 * into a session cookie, return 0.
		 *
		 * @since 3.7.0
		 *
		 * @param int $expires The expiry time, as passed to setcookie().
		 */
		$expire  = apply_filters( 'post_password_expires', time() + 10 * DAY_IN_SECONDS );
		$referer = wp_get_referer();

		if ( $referer ) {
			$secure = ( 'https' === parse_url( $referer, PHP_URL_SCHEME ) );
		} else {
			$secure = false;
		}

		setcookie( 'wp-postpass_' . COOKIEHASH, $hasher->HashPassword( wp_unslash( $_POST['post_password'] ) ), $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
        if(isset($_POST['post_email']) && $_POST['post_email'])
            setcookie( 'wp-postemail_' . COOKIEHASH, $_POST['post_email'], $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );

		wp_safe_redirect( wp_get_referer() );
		exit;
    }

    public function registerAddon($installedAddons) {
        $installedAddons[] = ['name' => $this->addonName, 'db_version' => $this->dbVersion, 'version' => $this->version];
        return $installedAddons;
    }

    public function onDeactivate() {
    }

    public function onActivate() {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();


        $sqls = [];
        $sqls[] = "CREATE TABLE " . $wpdb->prefix . "wpp_page_views (
            id int(11) NOT NULL AUTO_INCREMENT,
            post_id int(11) NOT NULL,
            email varchar(128) NOT NULL default '',
            ip_address varchar(128) NOT NULL default '',
            view_date datetime NOT NULL default now(),
            PRIMARY KEY (id)
        ) CHARSET=utf8";

        if ( ! function_exists( 'dbDelta' ) ) {
            require( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }

        foreach($sqls as $sql){
            error_log(print_r(dbDelta( $sql ), true));
        }
    }
}
endif;

$WPPass = new WPPass();
