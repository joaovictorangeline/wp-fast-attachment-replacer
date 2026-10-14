# WP-CLI Fast Attachment & URL Replacer

A high-performance WordPress CLI script designed to bulk-replace URLs in the database and simultaneously delete the old media attachments from the server.

## 🚀 The Problem vs. The Solution

**The Problem:** Running standard `wp search-replace` inside a PHP loop for multiple files causes massive server load. For an array of 30 files, WordPress forces 30 complete scans of every row and column in the database, taking several minutes and risking server timeouts.

**The Solution:** This script bypasses the standard WP-CLI search routine and uses direct SQL (`$wpdb->query`) targeted specifically at `wp_posts` and `wp_postmeta`. 

**The Result:** Execution time drops from minutes to milliseconds per file with virtually zero CPU load.

## 🛠️ Features
* **O(1) Database Updates:** Uses direct SQL replacements instead of full-table scans.
* **Deep Clean Deletion:** Utilizes `wp_delete_attachment( $id, true )` to permanently remove the database row, physical file, and generated thumbnails.
* **Page Builder Friendly:** Scans `wp_postmeta` to ensure links inside Elementor, Divi, or ACF fields are caught.
* **Automatic Cache Flushing:** Clears the WordPress object cache upon completion to prevent stale data.

## 💻 Usage

1. Place the `wp-fast-attachment-replacer.php` file in the root directory of your WordPress installation.
2. Edit the `$urls` array inside the script with your `old_url => new_url` key/value pairs.
3. Run the script via SSH using WP-CLI:

```bash
wp eval-file wp-fast-attachment-replacer.php