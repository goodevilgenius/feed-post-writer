<?php

function fpw_update_featured_image($post_id, $img_url) {
    // Get upload directory
    $uploads = wp_upload_dir();
    $upload_dir = $uploads['path'];
    $upload_url = $uploads['url'];

    // Download to the proper place
    $file_base = basename($img_url);
    $downloaded = "$upload_dir/$file_base";
    $downloaded_url = "$upload_url/$file_base";
    copy($img_url, $downloaded);

    // Check the type of tile. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype($downloaded, null);

    // Prepare an array of post data for the attachment.
    $attachment = array(
        'guid'           => $downloaded_url, 
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment($attachment, $downloaded, $post_id);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $downloaded);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Now set as the Featured Image (aka Post Thumbnail)
    set_post_thumbnail($post_id, $attach_id);
}

function fpw_add_feed_error($url, $error_msg) {
    $feeds = get_option('feed-post-writer-feeds');
    foreach($feeds as &$f) if ($f['url'] == $url) $f['error'] = $error_msg;
    update_option('feed-post-writer-feeds', $feeds);
}

function return_3600($seconds) { return 3600; }

function fpw_update_post($url, $post_id, $args = array()) {
    if (wp_is_post_revision($post_id)) return false;

    add_filter('wp_feed_cache_transient_lifetime', 'return_3600');    // Only cache for one hour, instead of twelve
    $feed = fetch_feed($url);
    remove_filter('wp_feed_cache_transient_lifetime', 'return_3600'); // Remove feed cache lifetime

    if (is_wp_error($feed)) {
        fpw_add_feed_error($url, $feed->get_error_message());
        return false;
    }

    $entry = $feed->get_item();
    $post = get_post($post_id);

    if (empty($post)) {
        fpw_add_feed_error($url, 'Invalid post ID.');
        return false;
    }

    $post->post_content = $entry->get_content();
    $post->post_modified_gmt = $entry->get_gmdate('Y-m-d H:i:s');
    $post->post_modified = get_date_from_gmt($post->post_modified_gmt);
    if (!empty($args['update_title'])) $post->post_title = $entry->get_title();

    $r = wp_update_post($post, true);
    
    if (is_wp_error($r)) {
        fpw_add_feed_error($url, $r->get_error_message());
        return false;
    }

    if (!empty($args['update_featured_image'])) {
        $e = $entry->get_enclosure();
        if (!empty($e)) fpw_update_featured_image($post_id, $e->get_link());
    }
}

function fpw_update_on_schedule($url) {
    $feeds = get_option('feed-post-writer-feeds');
    foreach($feeds as $f) {
        if ($f['url'] == $url) {
            fpw_update_post($url, $f['pid'], $f);
            return($f);
        }
    }
}
add_action('fpwupdateonschedulehook', 'fpw_update_on_schedule');

function fpw_update_feed_crons($oldfeeds, $newfeeds) {
    $schedules = wp_get_schedules();
    
    $oldfeed_array = array();
    $newfeed_array = array();
    if (is_array($oldfeeds)) foreach($oldfeeds as $f) if (!empty($f['url']) && !empty($f['pid'])) $oldfeed_array[$f['url']] = $f;
    if (is_array($newfeeds)) foreach($newfeeds as $f) if (!empty($f['url']) && !empty($f['pid'])) $newfeed_array[$f['url']] = $f;

    foreach($oldfeed_array as $url => $f) {
        if (!isset($newfeed_array[$url])) {
            // Remove cron job, as this feed is no longer used
            $ts = wp_next_scheduled('fpwupdateonschedulehook', array($url));
            wp_unschedule_event($ts, 'fpwupdateonschedulehook', array($url));
        } else {
            // Check that the schecule hasn't changed.
            $old = $f;
            $new = $newfeed_array[$url];
            if ($old['schedule'] != $new['schedule']) {
                // Remove old cron job, and create new one
                $ts = wp_next_scheduled('fpwupdateonschedulehook', array($url));
                wp_unschedule_event($ts, 'fpwupdateonschedulehook', array($url));
                wp_schedule_event(time(), $new['schedule'], 'fpwupdateonschedulehook', array($url));
            }
        }
    }

    foreach($newfeed_array as $url => $f) {
        if (!isset($oldfeed_array[$url])) {
            // Add new cron job for additional feed
            wp_schedule_event(time(), $f['schedule'], 'fpwupdateonschedulehook', array($url));
        }
    }
}
