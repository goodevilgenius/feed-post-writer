<?php

function get_feed_entry($url) {
    // For testing
    $entry = new stdClass;
    $entry->pubDate = time();
    $entry->description = $url . " " . date('r',$entry->pubDate);
    
    return $entry;
}

function update_post($url, $post_id) {
    if (wp_is_post_revision($post_id)) return false;

    $entry = get_feed_entry($url);
    $post = get_post($post_id);

    $post->post_content = $entry->description;
    $post->post_date = date('Y-m-d H:i:s', $entry->pubDate);
    $post->post_date_gmt = get_gmt_from_date($post->post_date);

    wp_update_post($post);
}

