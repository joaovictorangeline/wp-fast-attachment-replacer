<?php
/**
 * WP-CLI Fast Attachment & URL Replacer
 * 
 * Replaces old URLs with new URLs across wp_posts and wp_postmeta using
 * direct SQL for maximum performance, then securely deletes the old media attachments.
 * 
 * Run via WP-CLI: wp eval-file wp-fast-attachment-replacer.php
 */

// Define your old => new URL mappings here
$urls = [
    "https://example.com/wp-content/uploads/2023/01/old-file-1.pdf" => "https://example.com/wp-content/uploads/2023/02/new-file-1.pdf",
    "https://example.com/wp-content/uploads/2023/01/old-file-2.pdf" => "https://example.com/wp-content/uploads/2023/02/new-file-2.pdf",
];

global $wpdb;

WP_CLI::log( "Starting optimized SQL-based replace and deletion..." );

foreach ( $urls as $old_url => $new_url ) {

    // 1. Direct SQL update on post_content (Ostensibly faster than WP-CLI search-replace)
    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)",
        $old_url,
        $new_url
    ) );

    // 2. Direct SQL update on postmeta (Catches page builders like Elementor)
    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s)",
        $old_url,
        $new_url
    ) );

    // 3. Look up the old attachment by its GUID
    $attachment_id = $wpdb->get_var( $wpdb->prepare( 
        "SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND guid=%s", 
        $old_url 
    ) );

    // 4. Delete physical file, thumbnails, and database records
    if ( $attachment_id ) {
        wp_delete_attachment( $attachment_id, true );
        WP_CLI::success( "Replaced URLs and deleted old attachment ID: $attachment_id" );
    } else {
        WP_CLI::log( "Replaced content links for " . basename( $old_url ) . " (No attachment found to delete)" );
    }
}

// Clear Object Cache after direct DB manipulation
wp_cache_flush();

WP_CLI::success( "Completed all URL replacements and file deletions instantly!" );