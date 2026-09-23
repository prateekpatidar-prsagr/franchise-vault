<?php
/**
 * Plugin Settings Page — Brand name, color, portal title, login message, watermark.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class FV_Settings {

    public function __construct() {
        add_action( 'admin_menu',  [ $this, 'add_settings_page' ] );
        add_action( 'admin_init',  [ $this, 'register_settings' ] );
    }

    public function add_settings_page(): void {
        add_submenu_page(
            'edit.php?post_type=fv_module',
            'FranchiseVault Settings',
            'Settings',
            'manage_options',
            'fv-settings',
            [ $this, 'render_page' ]
        );
    }

    public function register_settings(): void {
        register_setting( 'fv_group', 'fv_brand_name',     [ 'default' => get_bloginfo( 'name' ) ] );
        register_setting( 'fv_group', 'fv_brand_color',    [ 'default' => '#6C63FF' ] );
        register_setting( 'fv_group', 'fv_portal_title',   [ 'default' => 'Training Portal' ] );
        register_setting( 'fv_group', 'fv_login_message',  [ 'default' => 'Please log in to access your training materials.' ] );
        register_setting( 'fv_group', 'fv_watermark_text', [ 'default' => '' ] );
        register_setting( 'fv_group', 'fv_accent_2',       [ 'default' => '#FF6B6B' ] );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $portal_page = get_page_by_path( 'training-portal' );
        $portal_url  = $portal_page ? get_permalink( $portal_page->ID ) : '';
        ?>
        <div class="wrap fv-settings-page">
            <div class="fv-settings-header">
                <div class="fv-settings-logo">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                </div>
                <div>
                    <h1>FranchiseVault Settings</h1>
                    <p>Configure your franchise training portal.</p>
                </div>
            </div>

            <?php settings_errors(); ?>

            <div class="fv-settings-layout">
                <!-- Left: Form -->
                <div class="fv-settings-form-wrap">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'fv_group' ); ?>

                        <div class="fv-settings-card">
                            <h2 class="fv-card-heading">🏷️ Branding</h2>

                            <div class="fv-row">
                                <label for="fv_brand_name">Brand / Company Name</label>
                                <input type="text" id="fv_brand_name" name="fv_brand_name"
                                    value="<?php echo esc_attr( get_option( 'fv_brand_name', get_bloginfo( 'name' ) ) ); ?>"
                                    class="fv-input" />
                                <p class="fv-hint">Shown in the portal header.</p>
                            </div>

                            <div class="fv-row fv-row-colors">
                                <div>
                                    <label for="fv_brand_color">Primary Accent Color</label>
                                    <div class="fv-color-wrap">
                                        <input type="color" id="fv_brand_color" name="fv_brand_color"
                                            value="<?php echo esc_attr( get_option( 'fv_brand_color', '#6C63FF' ) ); ?>" />
                                        <span class="fv-color-val"><?php echo esc_html( get_option( 'fv_brand_color', '#6C63FF' ) ); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <label for="fv_accent_2">Secondary Accent Color</label>
                                    <div class="fv-color-wrap">
                                        <input type="color" id="fv_accent_2" name="fv_accent_2"
                                            value="<?php echo esc_attr( get_option( 'fv_accent_2', '#FF6B6B' ) ); ?>" />
                                        <span class="fv-color-val"><?php echo esc_html( get_option( 'fv_accent_2', '#FF6B6B' ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fv-settings-card">
                            <h2 class="fv-card-heading">🖥️ Portal</h2>

                            <div class="fv-row">
                                <label for="fv_portal_title">Portal Title</label>
                                <input type="text" id="fv_portal_title" name="fv_portal_title"
                                    value="<?php echo esc_attr( get_option( 'fv_portal_title', 'Training Portal' ) ); ?>"
                                    class="fv-input" />
                            </div>

                            <div class="fv-row">
                                <label for="fv_login_message">Login Wall Message</label>
                                <textarea id="fv_login_message" name="fv_login_message" rows="3" class="fv-textarea"
                                ><?php echo esc_textarea( get_option( 'fv_login_message', 'Please log in to access your training materials.' ) ); ?></textarea>
                            </div>
                        </div>

                        <div class="fv-settings-card">
                            <h2 class="fv-card-heading">🔐 Security</h2>

                            <div class="fv-row">
                                <label for="fv_watermark_text">Watermark Text</label>
                                <input type="text" id="fv_watermark_text" name="fv_watermark_text"
                                    value="<?php echo esc_attr( get_option( 'fv_watermark_text', '' ) ); ?>"
                                    class="fv-input" placeholder="Leave blank to use user's email automatically" />
                                <p class="fv-hint">This text is tiled as a watermark over all media. Leave blank to auto-use the logged-in user's email address.</p>
                            </div>
                        </div>

                        <?php submit_button( 'Save Settings', 'primary fv-save-btn', 'submit', true ); ?>
                    </form>
                </div>

                <!-- Right: Info Panel -->
                <div class="fv-settings-sidebar">
                    <div class="fv-info-card">
                        <h3>🚀 Quick Start</h3>
                        <ol>
                            <li>Go to <strong>FranchiseVault → Add Module</strong> to add your first training module.</li>
                            <li>Paste your <strong>YouTube URL</strong> (for videos) or <strong>Google Drive link</strong> (for PDFs, docs).</li>
                            <li>Create a WordPress user for each franchise partner.</li>
                            <li>Share the portal link below with your franchisees.</li>
                        </ol>
                    </div>

                    <div class="fv-info-card fv-portal-link-card">
                        <h3>🔗 Your Portal URL</h3>
                        <?php if ( $portal_url ) : ?>
                            <a href="<?php echo esc_url( $portal_url ); ?>" target="_blank" class="fv-portal-url">
                                <?php echo esc_html( $portal_url ); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        <?php else : ?>
                            <p>Portal page not found. Use shortcode <code>[franchise_vault]</code> on any page.</p>
                        <?php endif; ?>
                    </div>

                    <div class="fv-info-card">
                        <h3>📌 Shortcode</h3>
                        <code class="fv-shortcode">[franchise_vault]</code>
                        <p>Add this to any page to display the training portal.</p>
                    </div>

                    <div class="fv-info-card fv-tip-card">
                        <h3>💡 Tips</h3>
                        <ul>
                            <li>Set YouTube videos to <strong>Unlisted</strong> for best security.</li>
                            <li>For Google Drive files, set share to <strong>"Anyone with link can view"</strong>.</li>
                            <li>Use a <strong>full-width page template</strong> for best portal display.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
