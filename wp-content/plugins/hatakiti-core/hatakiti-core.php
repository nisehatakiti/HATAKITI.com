<?php
/**
 * Plugin Name: HATAKITI Core
 * Description: Custom post types, structured fields, taxonomies, and the ChatGPT draft-integration endpoint for HATAKITI.com. Companion to the "hatakiti" theme.
 * Version: 1.0.0
 * Author: HATAKITI
 * Text Domain: hatakiti
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HATAKITI_CORE_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Category slugs used to distinguish 日々の所感 and 演劇について, both of
 * which are ordinary WordPress posts (docs/03-ContentModel.md). Defined
 * here in the plugin — rather than in the theme — so the REST draft
 * endpoint and CLI importer work even if the active theme changes later.
 */
if ( ! defined( 'HATAKITI_CAT_NIKKI' ) ) {
    define( 'HATAKITI_CAT_NIKKI', 'nikki' );
}
if ( ! defined( 'HATAKITI_CAT_ENGEKI' ) ) {
    define( 'HATAKITI_CAT_ENGEKI', 'engeki' );
}

require_once HATAKITI_CORE_DIR . 'includes/capabilities.php';
require_once HATAKITI_CORE_DIR . 'includes/taxonomies.php';
require_once HATAKITI_CORE_DIR . 'includes/cpt-theatre-record.php';
require_once HATAKITI_CORE_DIR . 'includes/cpt-film-record.php';
require_once HATAKITI_CORE_DIR . 'includes/cpt-activity-record.php';
require_once HATAKITI_CORE_DIR . 'includes/cpt-folktale.php';
require_once HATAKITI_CORE_DIR . 'includes/folktale-meta-boxes.php';
require_once HATAKITI_CORE_DIR . 'includes/folktale-json-import.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-cpt.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-meta-boxes.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-rss-fetch.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-source-fetch.php';
require_once HATAKITI_CORE_DIR . 'includes/meta-boxes.php';
require_once HATAKITI_CORE_DIR . 'includes/admin-forms.php';
require_once HATAKITI_CORE_DIR . 'includes/admin-form-activity.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-weekly-admin-form.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-ai.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-weekly-ai-edit.php';
require_once HATAKITI_CORE_DIR . 'includes/occult-weekly-pdf.php';
require_once HATAKITI_CORE_DIR . 'includes/rest-draft-endpoint.php';
require_once HATAKITI_CORE_DIR . 'includes/cli-import-theatre.php';

function hatakiti_core_activate() {
    hatakiti_register_theatre_record_cpt();
    hatakiti_register_film_record_cpt();
    hatakiti_register_activity_record_cpt();
    hatakiti_register_folktale_cpt();
    hatakiti_register_occult_cpts();
    hatakiti_register_film_genre_taxonomy();
    hatakiti_register_activity_type_taxonomy();
    hatakiti_register_activity_category_taxonomy();
    hatakiti_register_folktale_taxonomies();
    hatakiti_register_occult_taxonomy();
    hatakiti_seed_default_terms();
    hatakiti_seed_activity_type_terms();
    hatakiti_seed_activity_category_terms();
    hatakiti_seed_folktale_story_type_terms();
    hatakiti_seed_occult_category_terms();
    hatakiti_install_capabilities();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hatakiti_core_activate' );

function hatakiti_core_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'hatakiti_core_deactivate' );
