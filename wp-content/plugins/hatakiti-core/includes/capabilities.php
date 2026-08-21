<?php
/**
 * Least-privilege role/capability for the future ChatGPT → WordPress draft
 * integration (docs/03-ContentModel.md §8, docs/04-UXandWordPress.md §8).
 *
 * The integration is draft-only by design: this role can never publish,
 * edit others' content, or touch site settings/plugins/themes/users.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const HATAKITI_DRAFT_CAP  = 'hatakiti_create_draft';
const HATAKITI_EDITOR_ROLE = 'hatakiti_chatgpt_editor';

function hatakiti_install_capabilities() {
    add_role( HATAKITI_EDITOR_ROLE, 'ChatGPT下書き係', array(
        'read'         => true,
        'edit_posts'   => true,
        'upload_files' => true,
        HATAKITI_DRAFT_CAP => true,
        // Deliberately no publish_posts, edit_others_posts, delete_posts,
        // edit_pages, manage_categories, manage_options, edit_theme_options,
        // install_plugins, or any user-management capability.
    ) );

    $admin = get_role( 'administrator' );
    if ( $admin ) {
        $admin->add_cap( HATAKITI_DRAFT_CAP );
    }
}

function hatakiti_current_user_can_create_draft() {
    return current_user_can( HATAKITI_DRAFT_CAP );
}
