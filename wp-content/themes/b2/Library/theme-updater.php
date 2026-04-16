
<?php
// Define the theme version for use in the updater.
if (!defined('B2_VERSION')) {
    define('B2_VERSION', '1.0.0');
}

// Define the remote URL where the update metadata is located.
if (!defined('B2_UPDATE_URL')) {
    define('B2_UPDATE_URL', 'https://7b2.com/check/theme-update.json');
}

// Include the WordPress Theme Updater.
if (!class_exists('Theme_Updater')) {
    class Theme_Updater {
        private $theme_slug;
        private $theme_version;
        private $update_url;

        public function __construct($theme_slug, $theme_version, $update_url) {
            $this->theme_slug = $theme_slug;
            $this->theme_version = $theme_version;
            $this->update_url = $update_url;

            // Register the theme updater.
            add_filter('pre_set_site_transient_update_themes', array($this, 'check_for_updates'));
            add_filter('upgrader_source_selection', array($this, 'verify_update_source'), 10, 3);
        }

        public function check_for_updates($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            // Check for theme updates.
            $update = $this->get_update_metadata();

            if (isset($update->new_version) && version_compare($this->theme_version, $update->new_version, '<')) {
                $transient->response[$this->theme_slug] = (array) $update;
            }

            return $transient;
        }

        public function get_update_metadata() {
            $request = wp_remote_get($this->update_url);

            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                return false;
            }

            $body = wp_remote_retrieve_body($request);
            $update = json_decode($body);

            if (!is_object($update)) {
                return false;
            }

            return $update;
        }

        public function verify_update_source($source, $remote_source, $upgrader) {
            if (!isset($upgrader->skin->theme_info) || $upgrader->skin->theme_info->get_template() != $this->theme_slug) {
                return $source;
            }

            // Verify the update source.
            $remote_source_exists = is_dir($remote_source);

            if (!$remote_source_exists) {
                $upgrader->skin->feedback('The remote update source does not exist. Please try again later.');
                return new WP_Error('source_not_found', 'The remote update source does not exist.');
            }

            return $source;
        }
    }
}

new Theme_Updater('your-theme-slug', B2_VERSION, B2_UPDATE_URL);