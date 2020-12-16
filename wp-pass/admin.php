<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class WP_Pass_Admin {
    public function __construct() {
		add_action( 'admin_menu', array( $this, 'toolbar_item' ) );
    }

	/**
	 * Toolbar Item
	 *
	 * @internal  Private. Called via `wp_before_admin_bar_render` actions.
	 */
	public function toolbar_item() {
		$capability = apply_filters( 'password_protected_options_page_capability', 'manage_options' );
        add_menu_page(
            __('Password Protected Pages', 'wp-pass'),
            __('Password Protected Pages', 'wp-pass'),
            $capability,
            'wp-pass-admin',
            array( $this, 'wp_pass_admin_page' ),
            'dashicons-lock',
            22
        );
	}

    public function wp_pass_admin_page() {
        global $wpdb;
        switch($_GET['action']) {
        case 'list':
            $results = $wpdb->get_results("select * from " . $wpdb->prefix . "wpp_page_views where post_id = " . (int)$_GET['post_id'] . ' ORDER BY id desc');
            echo '
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.  __( 'Page Views', 'wp-pass' ) . '</h2>
        <table class="widefat fixed striped table-view-list pages">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="email"><a href="#"><span>Email</span></a></th>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="page-view">Time</th>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="page-view">IP</th>
            </tr>
        </thead>
        <tbody>
';
            foreach($results as $res) {
                $date = new DateTime($res->view_date, new DateTimeZone('UTC'));
                $date->setTimezone(new DateTimeZone('America/New_York'));
                echo '<tr>' .
                    '<td class="title column-title has-row-actions column-primary page-title"><strong>' . $res->email . '</strong></td>' .
                    '<td>' . $date->format('Y-m-d H:i:s') . '</td>' .
                    '<td>' . preg_replace('/:.*/', '', $res->ip_address) . '</td>' .
                '</tr>';
            }
            break;
        default:
            $query = new WP_Query( array( 'has_password' => true, 'post_type' => 'page', 'post_status' => 'publish' ) );
            if( $query->have_posts() ) {
                $results = $wpdb->get_results("select post_id, count(id) as pv, count(distinct email) as uv from " . $wpdb->prefix . "wpp_page_views group by post_id");
                $views = [];
                foreach($results as $res) {
                    $views[$res->post_id] = $res;
                }

                echo '
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.  __( 'Page Views', 'wp-pass' ) . '</h2>
        <table class="widefat fixed striped table-view-list pages">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="title"><a href="#"><span>Title</span></a></th>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="page-view">Page View</th>
                <th scope="col" class="manage-column column-title column-primary sortable desc" id="unique-view">Unique View</th>
            </tr>
        </thead>
        <tbody>
    ';
                while( $query->have_posts() ) {
                    $query->the_post();
                    echo '<tr>' .
                        '<td class="title column-title has-row-actions column-primary page-title"><strong><a class="row-title" href="' . esc_url( site_url( 'wp-admin/admin.php?page=wp-pass-admin&action=list&post_id=' . get_the_ID(), 'wp-pass' ) ) . '">' . get_the_title() . '</a></strong></td>' .
                        '<td>' . (isset($views[get_the_ID()]) ? $views[get_the_ID()]->pv : '') . '</td>' .
                        '<td>' . (isset($views[get_the_ID()]) ? $views[get_the_ID()]->uv : '') . '</td>' .
                        '</tr>';
                }
                echo '<tbody></table></div>';
            }
            wp_reset_postdata();
        }
    }
}

$WP_Pass_Admin = new WP_Pass_Admin();
