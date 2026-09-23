<?php
/**
 * Security — Cache-control headers for portal pages.
 * Main security (right-click, keyboard, watermark) lives in fv-public.js
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class FV_Security {

    public function __construct() {
        add_action( 'template_redirect', [ $this, 'headers' ] );
        add_action( 'wp_head',           [ $this, 'meta_tags' ] );
    }

    public function headers(): void {
        if ( ! is_singular() ) return;
        global $post;
        if ( ! $post || ! has_shortcode( $post->post_content, 'franchise_vault' ) ) return;

        // Prevent browser caching of portal pages
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
        header( 'Pragma: no-cache' );
        header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
    }

    public function meta_tags(): void {
        if ( ! is_singular() ) return;
        global $post;
        if ( ! $post || ! has_shortcode( $post->post_content, 'franchise_vault' ) ) return;

        // Tell crawlers not to index portal pages
        echo '<meta name="robots" content="noindex, nofollow" />' . PHP_EOL;

        // Disable browser's built-in translation on media pages
        echo '<meta name="google" content="notranslate" />' . PHP_EOL;
    }
}
