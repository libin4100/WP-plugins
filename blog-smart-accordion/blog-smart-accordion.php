<?php
/**
 * Plugin Name: Blog Smart Accordion
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: Blog Smart Accordion
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://www.mywebsite.com
 */

if(!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if(!class_exists('JetBlogSmartAccordion')):
/**
 * Main Class.
 *
 */
final class JetBlogSmartAccordion {
    public $version = '1.0.0';
    public $dbVersion = '1.0.0';
    public $addonName = 'blog-smart-accordion';

    public function __construct() {
        $this->defines();
        $this->hooks();
    }

    public function defines() {
    }

    public function hooks() {
        add_action('wp_ajax_get_ajax_post', [$this, 'get_post_json']);
        add_action('wp_ajax_nopriv_get_ajax_post', [$this, 'get_post_json']);
        add_action('wp_enqueue_scripts', [$this, 'frontScripts']);

        register_activation_hook(__FILE__, [$this, 'onActivate']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
    }

    // Make ajax requests to /wp-admin/admin-ajax.php?action=get_post&id=127
    public function get_post_json() {
        $post_id = isset($_GET['id']) ? htmlspecialchars($_GET["id"]) : (
            isset($_GET['page']) ? url_to_postid($_GET['page']) : 0
        );
        $post = get_post($post_id); 
        $output = apply_filters('the_content', $post->post_content);

        echo json_encode(['success' => true, 'content' => $output]);

        die();
    }

    public function frontScripts() {
        $jsFolder = plugin_dir_url( __FILE__ ) . 'public/js/';
        wp_enqueue_script('latepoint-conditions',  $jsFolder . 'front.js', array('jquery'), $this->version);
    }

    public function registerAddon($installedAddons) {
        $installedAddons[] = ['name' => $this->addonName, 'db_version' => $this->dbVersion, 'version' => $this->version];
        return $installedAddons;
    }

    public function onDeactivate() {
    }

    public function onActivate() {
    }
}
endif;

$jetBlogSmartAccordion = new JetBlogSmartAccordion();
