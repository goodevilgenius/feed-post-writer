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

function return_3600($seconds) { return 3600; }

function fpw_update_post($url, $post_id, $args = array()) {
    if (wp_is_post_revision($post_id)) return false;

    add_filter('wp_feed_cache_transient_lifetime', 'return_3600');    // Only cache for one hour, instead of twelve
    $feed = fetch_feed($url);
    remove_filter('wp_feed_cache_transient_lifetime', 'return_3600'); // Remove feed cache lifetime

    $entry = $feed->get_item();
    $post = get_post($post_id);

    $post->post_content = $entry->get_content();
    $post->post_modified_gmt = $entry->get_gmdate('Y-m-d H:i:s');
    $post->post_modified = get_date_from_gmt($post->post_modified_gmt);
    if (!empty($args['update_title'])) $post->post_title = $entry->get_title();

    wp_update_post($post);
    if (!empty($args['update_featured_image'])) {
        $e = $entry->get_enclosure();
        if (!empty($e)) fpw_update_featured_image($post_id, $e->get_link());
    }
}

