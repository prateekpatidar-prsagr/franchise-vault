<?php
/**
 * Plugin Name:       FranchiseVault
 * Plugin URI:        https://github.com/prateekpatidar-prsagr/franchise-vault
 * Description:       Premium franchise training portal — manage videos, SOPs, recipes & documents securely. Zero server storage via YouTube + Google Drive.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Prateek Patidar
 * Author URI:        https://github.com/prateekpatidar-prsagr
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       franchisevault
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Constants ────────────────────────────────────────────────────────────────
define('FV_VERSION', '1.0.0');
define('FV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FV_PLUGIN_FILE', __FILE__);

// ── Autoload ─────────────────────────────────────────────────────────────────
foreach (['class-fv-cpt', 'class-fv-settings', 'class-fv-admin', 'class-fv-frontend', 'class-fv-security'] as $class) {
    require_once FV_PLUGIN_DIR . 'includes/' . $class . '.php';
}

/**
 * Main plugin singleton.
 */
final class FranchiseVault
{

    private static ?FranchiseVault $instance = null;

    public static function instance(): FranchiseVault
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'boot']);

        register_activation_hook(FV_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(FV_PLUGIN_FILE, [$this, 'deactivate']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain('franchisevault', false, dirname(plugin_basename(FV_PLUGIN_FILE)) . '/languages');
    }

    public function boot(): void
    {
        new FV_CPT();
        new FV_Settings();
        new FV_Admin();
        new FV_Frontend();
        new FV_Security();
    }

    public function activate(): void
    {
        (new FV_CPT()); // register CPT so flush works
        $this->create_portal_page();
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        flush_rewrite_rules();
    }

    private function create_portal_page(): void
    {
        if (!get_page_by_path('training-portal')) {
            wp_insert_post([
                'post_title' => 'Training Portal',
                'post_name' => 'training-portal',
                'post_content' => '[franchise_vault]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
            ]);
        }
    }
}

FranchiseVault::instance();
