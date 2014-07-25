<?php

function get_feed_entry($url) {
    // For testing
    $entry = new stdClass;
    $entry->pubDate = time();
    $entry->description = $url . " " . date('r',$entry->pubDate);
    
    return $entry;
}

function update_featured_image($post_id, $img_url) {
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

function update_post($url, $post_id, $args = array()) {
    if (wp_is_post_revision($post_id)) return false;

    $entry = get_feed_entry($url);
    $post = get_post($post_id);

    $post->post_content = $entry->description;
    $post->post_date = date('Y-m-d H:i:s', $entry->pubDate);
    $post->post_date_gmt = get_gmt_from_date($post->post_date);

    wp_update_post($post);
    if (!empty($post->enclosure) && !empty($args['update_featured_image'])) 
        update_featured_image($post_id, $post->enclosure->url);
}

