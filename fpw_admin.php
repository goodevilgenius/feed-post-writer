<?php

// Admin Menu Options
function fpw_panel_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    wp_enqueue_style('fpw-styles',plugins_url('css/style.css', __FILE__));

    $feeds = get_option('feed-post-writer-feeds');
    $updated = (!empty($_POST['action'])) && ($_POST['action'] == "update");

    if ($updated) {
        // Check nonce
        check_admin_referer('feed-post-writer-options');

        $feeds = $_POST['feeds'];

        foreach($_POST['delete_feed'] as $v) {
            unset($feeds[$v]);
        }
        $feeds = array_merge($feeds);
        if (is_array($feeds)) update_option('feed-post-writer-feeds', $feeds);
        if (!empty($_POST['add-feed']) && $_POST['add-feed'] == "Add feed") $feeds[] = array('url'=>'','pid'=>0);
    }

    if (empty($feeds)) $feeds = array();
    include(plugin_dir_path(__FILE__) . 'settings.tpl.php');
}

// Admin Menu
function fpw_panel() {
    if (function_exists('add_management_page')) {
        add_management_page('Feed Post Writer', 'Feed Post Writer', 'manage_options', 'feed_post_writer', 'fpw_panel_options');
    }
}

// Add Admin Menu
add_action('admin_menu', 'fpw_panel');
