<?php
/**
 * Frontend — Shortcode, Login Wall, Portal Grid, Single Module View.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class FV_Frontend {

    // ── Hero Banner ──
    private array $type_meta = [
        'video'    => [ 'label' => 'Video',    'icon' => '▶', 'color' => '#6366f1', 'bg' => 'linear-gradient(135deg,#1e1b4b,#0f172a)' ],
        'pdf'      => [ 'label' => 'PDF',      'icon' => '📄', 'color' => '#06b6d4', 'bg' => 'linear-gradient(135deg,#083344,#0f172a)' ],
        'recipe'   => [ 'label' => 'Recipe',   'icon' => '📋', 'color' => '#f59e0b', 'bg' => 'linear-gradient(135deg,#451a03,#0f172a)' ],
        'image'    => [ 'label' => 'Gallery',  'icon' => '🖼', 'color' => '#10b981', 'bg' => 'linear-gradient(135deg,#064e3b,#0f172a)' ],
        'audio'    => [ 'label' => 'Audio',    'icon' => '🎧', 'color' => '#a855f7', 'bg' => 'linear-gradient(135deg,#3b0764,#0f172a)' ],
        'document' => [ 'label' => 'Document', 'icon' => '📜', 'color' => '#ec4899', 'bg' => 'linear-gradient(135deg,#500724,#0f172a)' ],
    ];

    public function __construct() {
        add_shortcode( 'franchise_vault', [ $this, 'shortcode' ] );
        add_action( 'wp_enqueue_scripts',  [ $this, 'maybe_enqueue' ] );

        // Hide WordPress admin bar for non-administrators on the portal
        add_filter( 'show_admin_bar', [ $this, 'hide_admin_bar_for_subscribers' ] );

        // Remove theme padding from body/page when portal is active
        add_action( 'wp_head', [ $this, 'portal_page_styles' ] );
    }

    /**
     * Hide the WP admin bar for everyone except admins & editors.
     */
    public function hide_admin_bar_for_subscribers( bool $show ): bool {
        if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
            return $show;
        }
        return false;
    }

    /**
     * Helper to get a clean first name (handles email usernames gracefully).
     */
    private function get_clean_first_name( WP_User $user ): string {
        if ( ! empty( $user->first_name ) ) {
            return $user->first_name;
        }
        $name = $user->display_name;
        if ( str_contains( $name, '@' ) ) {
            $parts = explode( '@', $name );
            $name  = ucwords( str_replace( [ '.', '_', '-' ], ' ', $parts[0] ) );
        }
        return strtok( $name, ' ' );
    }

    /**
     * Helper to get a clean full display name.
     */
    private function get_clean_display_name( WP_User $user ): string {
        if ( ! empty( $user->display_name ) && ! str_contains( $user->display_name, '@' ) ) {
            return $user->display_name;
        }
        if ( ! empty( $user->first_name ) || ! empty( $user->last_name ) ) {
            return trim( $user->first_name . ' ' . $user->last_name );
        }
        $parts = explode( '@', $user->user_email );
        return ucwords( str_replace( [ '.', '_', '-' ], ' ', $parts[0] ) );
    }

    /**
     * Inject scoped CSS on portal pages to neutralize theme interference & force 100vw full width.
     */
    public function portal_page_styles(): void {
        global $post;
        if ( ! is_singular() || ! is_a( $post, 'WP_Post' ) ) return;
        if ( ! has_shortcode( $post->post_content, 'franchise_vault' ) ) return;
        echo '<style id="fv-page-isolation">
/* Reset & Force dark background on portal page */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #090d16 !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
}

/* Neutralize theme wrappers & containers */
.site-content, .entry-content, .page-content, .wp-block-post-content,
.container, .site-main, .main-content, .content-area,
#content, #main, #page, .page-template-default article,
.entry, .post, .page, .uicore-body-content, #uicore-page {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

/* Suppress theme header, navigation & footer elements (without hiding .fv-header) */
body > header, body > footer, nav.site-navigation,
.site-header, .site-footer, .nav-bar, .navigation,
#masthead, #colophon, .wp-site-blocks > .wp-block-template-part,
.uicore-navbar, .uicore-header, .uicore-footer, .uicore-extra-header,
.elementor-location-header, .elementor-location-footer,
.fl-page-header, .fl-page-footer, .fusion-header-wrapper, .fusion-footer {
    display: none !important;
}

/* Ensure fv-header is always displayed cleanly at top */
.fv-header, div.fv-header {
    display: block !important;
    top: 0 !important;
}
</style>' . PHP_EOL;
    }

    // ── Enqueue ──────────────────────────────────────────────────────────────

    public function maybe_enqueue(): void {
        global $post;
        if ( ! is_singular() || ! is_a( $post, 'WP_Post' ) ) return;
        if ( ! has_shortcode( $post->post_content, 'franchise_vault' ) ) return;
        $this->enqueue_assets();
    }

    private function enqueue_assets(): void {
        wp_enqueue_style(
            'fv-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'fv-public',
            FV_PLUGIN_URL . 'public/css/fv-public.css',
            [ 'fv-fonts' ],
            FV_VERSION
        );
        wp_enqueue_script(
            'fv-public',
            FV_PLUGIN_URL . 'public/js/fv-public.js',
            [ 'jquery' ],
            FV_VERSION,
            true
        );

        $brand  = get_option( 'fv_brand_color', '#6C63FF' );
        $accent = get_option( 'fv_accent_2', '#FF6B6B' );
        $user   = is_user_logged_in() ? wp_get_current_user() : null;
        $wm     = get_option( 'fv_watermark_text', '' ) ?: ( $user ? $user->user_email : 'CONFIDENTIAL' );

        wp_localize_script( 'fv-public', 'fvData', [
            'brand'     => $brand,
            'accent'    => $accent,
            'watermark' => $wm,
            'userName'  => $user ? $user->display_name : '',
            'userEmail' => $user ? $user->user_email : '',
        ] );

        // CSS custom properties for brand colors
        wp_add_inline_style( 'fv-public', ":root{--fv-brand:{$brand};--fv-accent2:{$accent};}" );
    }

    // ── Shortcode entry ──────────────────────────────────────────────────────

    public function shortcode( $atts ): string {
        // Ensure assets are loaded even if maybe_enqueue missed it
        $this->enqueue_assets();

        // Login wall
        if ( ! is_user_logged_in() ) {
            return $this->login_wall();
        }

        // Single module view?
        if ( ! empty( $_GET['fv_module'] ) ) {
            $id = absint( $_GET['fv_module'] );
            $p  = get_post( $id );
            if ( $p && 'fv_module' === $p->post_type && 'publish' === $p->post_status ) {
                return $this->module_view( $p );
            }
        }

        // Default: portal grid
        return $this->portal();
    }

    // ── Login Wall ───────────────────────────────────────────────────────────

    private function login_wall(): string {
        $brand   = get_option( 'fv_brand_name', get_bloginfo( 'name' ) );
        $msg     = get_option( 'fv_login_message', 'Please log in to access your training materials.' );
        $login   = wp_login_url( get_permalink() );
        $color   = get_option( 'fv_brand_color', '#6C63FF' );
        $accent  = get_option( 'fv_accent_2', '#FF6B6B' );

        ob_start(); ?>
        <div class="fv-wrap fv-login-wall" style="--fv-brand:<?php echo esc_attr($color);?>;--fv-accent2:<?php echo esc_attr($accent);?>">
            <!-- Animated Background -->
            <div class="fv-lw-bg" aria-hidden="true">
                <div class="fv-orb fv-orb-1"></div>
                <div class="fv-orb fv-orb-2"></div>
                <div class="fv-orb fv-orb-3"></div>
                <div class="fv-grid-lines"></div>
            </div>

            <!-- Card -->
            <div class="fv-lw-card">
                <div class="fv-lw-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <h2 class="fv-lw-brand"><?php echo esc_html($brand); ?></h2>
                <p class="fv-lw-subtitle">Training Portal</p>
                <div class="fv-lw-divider"></div>
                <p class="fv-lw-msg"><?php echo esc_html($msg); ?></p>
                <a href="<?php echo esc_url($login); ?>" class="fv-lw-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Log In to Continue
                </a>
                <p class="fv-lw-powered">FranchiseVault by <strong>Prateek Patidar</strong></p>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    // ── Portal Grid ──────────────────────────────────────────────────────────

    private function portal(): string {
        $brand_name   = get_option( 'fv_brand_name', get_bloginfo( 'name' ) );
        $portal_title = get_option( 'fv_portal_title', 'Training Portal' );
        $user         = wp_get_current_user();

        $categories = get_terms( [ 'taxonomy' => 'fv_category', 'hide_empty' => true ] );
        $modules    = get_posts( [
            'post_type'      => 'fv_module',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        // Collect which types exist so filter buttons only show relevant types
        $types_used = array_unique( array_map( fn($m) => get_post_meta($m->ID, '_fv_type', true) ?: 'video', $modules ) );

        ob_start(); ?>
        <div class="fv-wrap fv-portal" id="fv-portal">

            <!-- ═══ TOP NAVBAR ═══ -->
            <div class="fv-header">
                <div class="fv-hdr-inner">
                    <div class="fv-hdr-brand">
                        <div class="fv-hdr-logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fv-hdr-name"><?php echo esc_html($brand_name); ?></div>
                            <div class="fv-hdr-sub"><?php echo esc_html($portal_title); ?></div>
                        </div>
                    </div>

                    <div class="fv-hdr-user">
                        <div class="fv-hdr-avatar">
                            <?php echo get_avatar($user->ID, 40, '', $user->display_name, ['class'=>'fv-avatar-img']); ?>
                        </div>
                        <div class="fv-hdr-user-info">
                            <span class="fv-hdr-username"><?php echo esc_html($this->get_clean_display_name($user)); ?></span>
                            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="fv-logout">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ HERO BANNER ═══ -->
            <div class="fv-hero">
                <div class="fv-hero-inner">
                    <div class="fv-hero-text">
                        <h1 class="fv-hero-title">Welcome Back, <?php echo esc_html( $this->get_clean_first_name($user) ); ?> 👋</h1>
                        <p class="fv-hero-subtitle">Access your confidential franchise training modules, SOPs, recipes, and operations guides.</p>
                    </div>
                    <div class="fv-hero-badges">
                        <div class="fv-hero-badge">
                            <span class="fv-hb-icon">🔒</span>
                            <span>Protected Portal</span>
                        </div>
                        <div class="fv-hero-badge">
                            <span class="fv-hb-icon">⚡</span>
                            <span>Zero Storage Cloud</span>
                        </div>
                        <div class="fv-hero-badge">
                            <span class="fv-hb-icon">🛡️</span>
                            <span>Watermarked Access</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ CONTROLS & FILTER BAR ═══ -->
            <div class="fv-filters">
                <div class="fv-filters-inner">

                    <!-- Search Input -->
                    <div class="fv-search-box">
                        <svg class="fv-search-ico" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="search" id="fv-search" class="fv-search-inp"
                               placeholder="Search training modules, recipes, SOPs..." autocomplete="off" />
                        <button class="fv-search-clear" id="fv-search-clear" aria-label="Clear search" style="display:none;">✕</button>
                    </div>

                    <!-- Sort Selector -->
                    <div class="fv-sort-wrap">
                        <label for="fv-sort" class="fv-sort-label">Sort:</label>
                        <div class="fv-select-custom">
                            <select id="fv-sort" class="fv-sort-sel">
                                <option value="newest">✨ Newest First</option>
                                <option value="oldest">⏳ Oldest First</option>
                                <option value="az">🔤 Title A → Z</option>
                                <option value="za">🔤 Title Z → A</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Secondary Filter Bar (Content Types & Categories) -->
                <div class="fv-pills-container">
                    <div class="fv-pills-inner">
                        <!-- Type Pills -->
                        <div class="fv-pill-group" id="fv-type-pills">
                            <span class="fv-pill-group-title">Type:</span>
                            <button class="fv-pill fv-pill-active" data-fv-type="all">All Types</button>
                            <?php foreach ( $this->type_meta as $key => $tm ) :
                                if ( ! in_array( $key, $types_used, true ) ) continue; ?>
                                <button class="fv-pill" data-fv-type="<?php echo esc_attr($key); ?>"
                                        style="--pill-color:<?php echo esc_attr($tm['color']); ?>">
                                    <?php echo $tm['icon']; ?> <?php echo esc_html($tm['label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Category Pills -->
                        <?php if ( ! empty($categories) && ! is_wp_error($categories) ) : ?>
                        <div class="fv-pill-group" id="fv-cat-pills">
                            <span class="fv-pill-group-title">Category:</span>
                            <button class="fv-pill fv-pill-active" data-fv-cat="all">All Categories</button>
                            <?php foreach ( $categories as $cat ) : ?>
                                <button class="fv-pill" data-fv-cat="<?php echo esc_attr($cat->slug); ?>">
                                    📁 <?php echo esc_html($cat->name); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ═══ MODULE COUNTER ═══ -->
            <div class="fv-stats">
                <div class="fv-stats-inner">
                    <span class="fv-stats-badge"><strong id="fv-count"><?php echo count($modules); ?></strong> Modules Found</span>
                </div>
            </div>

            <!-- ═══ MODULE CARDS GRID ═══ -->
            <?php if ( empty($modules) ) : ?>
                <div class="fv-empty-state">
                    <div class="fv-empty-icon">📚</div>
                    <h3>No Training Modules Added Yet</h3>
                    <p>Your portal is ready! Start adding training videos, SOPs, recipes, and documents from your WordPress Admin panel.</p>
                    <?php if ( current_user_can('manage_options') ) : ?>
                        <div class="fv-admin-quick-tip">
                            <span>💡 Admin Tip: Go to <strong>WP Admin ➔ Training Modules ➔ Add New</strong> to publish your first module.</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="fv-grid" id="fv-grid">
                    <?php foreach ( $modules as $mod ) :
                        $type   = get_post_meta($mod->ID, '_fv_type',       true) ?: 'video';
                        $short  = get_post_meta($mod->ID, '_fv_short_desc',  true);
                        $thumb  = get_post_meta($mod->ID, '_fv_thumb_url',   true);
                        $access = get_post_meta($mod->ID, '_fv_brand_access',true) ?: 'all';
                        $tm     = $this->type_meta[$type] ?? $this->type_meta['video'];
                        $cats   = get_the_terms($mod->ID, 'fv_category');
                        $cslugs = $cats && !is_wp_error($cats) ? implode(',', wp_list_pluck($cats,'slug')) : '';
                        $cnames = $cats && !is_wp_error($cats) ? wp_list_pluck($cats,'name') : [];

                        if ( ! $thumb && has_post_thumbnail($mod->ID) ) {
                            $thumb = get_the_post_thumbnail_url($mod->ID, 'medium_large');
                        }

                        $url = add_query_arg('fv_module', $mod->ID, get_permalink());
                    ?>
                        <article class="fv-card"
                                 data-type="<?php echo esc_attr($type); ?>"
                                 data-cat="<?php echo esc_attr($cslugs); ?>"
                                 data-brand="<?php echo esc_attr($access); ?>"
                                 data-title="<?php echo esc_attr(strtolower($mod->post_title)); ?>"
                                 data-date="<?php echo esc_attr(get_the_date('U', $mod->ID)); ?>">
                            <a href="<?php echo esc_url($url); ?>" class="fv-card-link">

                                <!-- Visual Header -->
                                <div class="fv-card-visual"
                                     style="<?php echo $thumb ? 'background-image:url('.esc_url($thumb).')' : 'background:'.$tm['bg']; ?>">
                                    <?php if (!$thumb) : ?>
                                        <div class="fv-card-default-icon"><?php echo $tm['icon']; ?></div>
                                    <?php endif; ?>

                                    <!-- Type badge -->
                                    <span class="fv-type-badge" style="--badge-color:<?php echo esc_attr($tm['color']); ?>">
                                        <?php echo $tm['icon']; ?> <?php echo esc_html($tm['label']); ?>
                                    </span>

                                    <!-- Hover Overlay -->
                                    <div class="fv-card-hover-overlay">
                                        <div class="fv-open-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="currentColor">
                                                <polygon points="5 3 19 12 5 21 5 3"/>
                                            </svg>
                                        </div>
                                        <span class="fv-open-label">Open Module</span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="fv-card-body">
                                    <h3 class="fv-card-title"><?php echo esc_html($mod->post_title); ?></h3>
                                    <?php if ($short) : ?>
                                        <p class="fv-card-desc"><?php echo esc_html($short); ?></p>
                                    <?php else : ?>
                                        <p class="fv-card-desc"><?php echo esc_html( wp_trim_words( $mod->post_content, 14, '...' ) ); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($cnames)) : ?>
                                        <div class="fv-card-tags">
                                            <?php foreach ($cnames as $cn) : ?>
                                                <span class="fv-tag"><?php echo esc_html($cn); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="fv-card-footer">
                                        <time class="fv-card-date"><?php echo get_the_date('M j, Y', $mod->ID); ?></time>
                                        <span class="fv-card-cta">View Module →</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div><!-- .fv-grid -->

                <div class="fv-no-results" id="fv-no-results" style="display:none;">
                    <div class="fv-empty-icon">🔍</div>
                    <h3>No Training Modules Found</h3>
                    <p>We couldn't find any modules matching your filter criteria.</p>
                    <button class="fv-reset-btn" id="fv-reset-filters">Clear All Filters</button>
                </div>
            <?php endif; ?>

            <footer class="fv-portal-footer">
                <span><?php echo esc_html($brand_name); ?> Training Vault</span>
                <span class="fv-footer-sep">•</span>
                <span>FranchiseVault by <strong>Prateek Patidar</strong></span>
            </footer>
        </div><!-- .fv-portal -->
        <?php
        return ob_get_clean();
    }

    // ── Single Module View ───────────────────────────────────────────────────

    private function module_view( WP_Post $mod ): string {
        $type      = get_post_meta($mod->ID, '_fv_type',       true) ?: 'video';
        $yt_url    = get_post_meta($mod->ID, '_fv_yt_url',     true);
        $drive_url = get_post_meta($mod->ID, '_fv_drive_url',  true);
        $audio_url = get_post_meta($mod->ID, '_fv_audio_url',  true);
        $img_urls  = get_post_meta($mod->ID, '_fv_img_urls',   true);
        $tm        = $this->type_meta[$type] ?? $this->type_meta['video'];
        $cats      = get_the_terms($mod->ID, 'fv_category');
        $brand     = get_option('fv_brand_name', get_bloginfo('name'));
        $user      = wp_get_current_user();
        $back      = remove_query_arg('fv_module');
        $wm        = get_option('fv_watermark_text','') ?: $user->user_email;

        ob_start(); ?>
        <div class="fv-wrap fv-module-view" id="fv-module-view">

            <!-- HEADER -->
            <div class="fv-header">
                <div class="fv-hdr-inner">
                    <div class="fv-hdr-brand">
                        <div class="fv-hdr-logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fv-hdr-name"><?php echo esc_html($brand); ?></div>
                            <div class="fv-hdr-sub">Training Portal</div>
                        </div>
                    </div>
                    <div class="fv-hdr-user">
                        <div class="fv-hdr-avatar">
                            <?php echo get_avatar($user->ID, 38, '', $user->display_name, ['class'=>'fv-avatar-img']); ?>
                        </div>
                        <div class="fv-hdr-user-info">
                            <span class="fv-hdr-username"><?php echo esc_html($this->get_clean_display_name($user)); ?></span>
                            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="fv-logout">
                                Logout
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BREADCRUMB -->
            <nav class="fv-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url($back); ?>" class="fv-back-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Back to Portal
                </a>
                <span class="fv-bc-sep">›</span>
                <span class="fv-bc-current"><?php echo esc_html($mod->post_title); ?></span>
            </nav>

            <!-- MODULE CONTENT -->
            <div class="fv-mv-inner">

                <!-- Module Header -->
                <div class="fv-mv-header">
                    <span class="fv-mv-type-badge" style="--badge-color:<?php echo esc_attr($tm['color']); ?>">
                        <?php echo $tm['icon']; ?> <?php echo esc_html($tm['label']); ?>
                    </span>
                    <h1 class="fv-mv-title"><?php echo esc_html($mod->post_title); ?></h1>
                    <?php if ($cats && !is_wp_error($cats)) : ?>
                        <div class="fv-mv-cats">
                            <?php foreach ($cats as $cat) : ?>
                                <span class="fv-tag"><?php echo esc_html($cat->name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <time class="fv-mv-date"><?php echo get_the_date('F j, Y', $mod->ID); ?></time>
                </div>

                <!-- MEDIA -->
                <div class="fv-media-wrap" id="fv-media-wrap">
                    <?php echo $this->render_media($type, $yt_url, $drive_url, $audio_url, $img_urls); ?>

                    <!-- Watermark Overlay -->
                    <div class="fv-wm-overlay" id="fv-wm" aria-hidden="true">
                        <!-- SVG watermark built by JS -->
                    </div>
                </div>

                <!-- Description -->
                <?php if ( trim($mod->post_content) ) : ?>
                    <div class="fv-mv-desc">
                        <?php echo wp_kses_post(apply_filters('the_content', $mod->post_content)); ?>
                    </div>
                <?php endif; ?>

                <!-- Nav buttons -->
                <div class="fv-mv-nav">
                    <a href="<?php echo esc_url($back); ?>" class="fv-mv-back-btn">
                        ← Back to all modules
                    </a>
                </div>
            </div><!-- .fv-mv-inner -->

            <footer class="fv-portal-footer">
                <span><?php echo esc_html($brand); ?> Training Portal</span>
                <span class="fv-footer-sep">•</span>
                <span>Powered by FranchiseVault</span>
            </footer>
        </div><!-- .fv-module-view -->
        <?php
        return ob_get_clean();
    }

    // ── Media Renderers ──────────────────────────────────────────────────────

    private function render_media( string $type, string $yt='', string $drive='', string $audio='', string $imgs='' ): string {
        switch ($type) {
            case 'video':    return $this->render_youtube($yt);
            case 'pdf':
            case 'recipe':
            case 'document': return $this->render_gdrive($drive);
            case 'audio':    return $this->render_audio($audio);
            case 'image':    return $this->render_gallery($imgs);
            default:         return '<div class="fv-no-media">No media configured for this module.</div>';
        }
    }

    private function render_youtube( string $url ): string {
        if ( ! $url ) return '<div class="fv-no-media">No YouTube URL configured for this module.</div>';

        $vid = '';
        $url = trim( $url );

        // ── Step 1: Try PHP parse_url approach (most reliable) ──────────────
        $parsed = parse_url( $url );
        if ( isset( $parsed['host'] ) ) {
            // Normalize host: remove www. and m. prefix
            $host = strtolower( preg_replace('/^(www\.|m\.)/', '', $parsed['host']) );
            $path = $parsed['path'] ?? '';

            if ( 'youtu.be' === $host ) {
                // https://youtu.be/VIDEO_ID
                $vid = trim( $path, '/' );
                // Remove any query string appended to path
                $vid = strtok( $vid, '?' );

            } elseif ( 'youtube.com' === $host || 'youtube-nocookie.com' === $host ) {
                // Parse query string
                parse_str( $parsed['query'] ?? '', $q );

                if ( ! empty( $q['v'] ) ) {
                    // https://www.youtube.com/watch?v=VIDEO_ID
                    $vid = $q['v'];

                } elseif ( preg_match( '#^/embed/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
                    // https://www.youtube.com/embed/VIDEO_ID
                    $vid = $m[1];

                } elseif ( preg_match( '#^/shorts/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
                    // https://www.youtube.com/shorts/VIDEO_ID
                    $vid = $m[1];

                } elseif ( preg_match( '#^/v/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
                    // https://www.youtube.com/v/VIDEO_ID
                    $vid = $m[1];

                } elseif ( preg_match( '#^/live/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
                    // https://www.youtube.com/live/VIDEO_ID
                    $vid = $m[1];
                }
            }
        }

        // ── Step 2: Fallback — broad regex on the raw URL string ─────────────
        if ( empty( $vid ) ) {
            if ( preg_match(
                '#(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|v/|shorts/|live/)|youtu\.be/)([a-zA-Z0-9_-]{11})#i',
                $url, $m
            ) ) {
                $vid = $m[1];
            }
        }

        // ── Validate: YouTube video IDs are always exactly 11 chars ──────────
        if ( ! preg_match( '/^[a-zA-Z0-9_-]{11}$/', $vid ) ) {
            $vid = '';
        }

        if ( ! $vid ) {
            return '<div class="fv-no-media" style="padding:40px;text-align:center;color:#9ca3af;">
                        <div style="font-size:40px;margin-bottom:12px;">⚠</div>
                        <strong style="color:#f9fafb;">Could not load video</strong><br>
                        <small>Please check the YouTube URL entered for this module.<br>
                        Supported formats: youtube.com/watch?v=, youtu.be/, youtube.com/shorts/</small>
                    </div>';
        }

        $src = "https://www.youtube.com/embed/{$vid}?rel=0&modestbranding=1&iv_load_policy=3&fs=1&playsinline=1";
        return '<div class="fv-video-ratio">
                    <iframe class="fv-video-frame" src="' . esc_url( $src ) . '"
                            title="Training Video" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy">
                    </iframe>
                </div>';
    }

    private function render_gdrive( string $url ): string {
        if ( ! $url ) return '<div class="fv-no-media">⚠️ No Google Drive URL configured.</div>';

        $embed = $url;
        if ( preg_match('|drive\.google\.com/file/d/([a-zA-Z0-9_\-]+)|', $url, $m) ) {
            $embed = "https://drive.google.com/file/d/{$m[1]}/preview";
        } elseif ( preg_match('/[?&]id=([a-zA-Z0-9_\-]+)/', $url, $m) ) {
            $embed = "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        return '<div class="fv-doc-wrap">
                    <iframe class="fv-doc-frame" src="' . esc_url($embed) . '"
                            title="Document Viewer" frameborder="0" loading="lazy">
                    </iframe>
                </div>';
    }

    private function render_audio( string $url ): string {
        if ( ! $url ) return '<div class="fv-no-media">⚠️ No audio URL configured.</div>';

        $embed = $url;
        if ( preg_match('|drive\.google\.com/file/d/([a-zA-Z0-9_\-]+)|', $url, $m) ) {
            $embed = "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        return '<div class="fv-audio-wrap">
                    <div class="fv-audio-icon">🎧</div>
                    <iframe class="fv-audio-frame" src="' . esc_url($embed) . '"
                            title="Audio Player" frameborder="0" allow="autoplay">
                    </iframe>
                </div>';
    }

    private function render_gallery( string $raw ): string {
        if ( ! $raw ) return '<div class="fv-no-media">⚠️ No images configured.</div>';

        $urls = array_filter( array_map('trim', explode("\n", $raw)) );
        $out  = '<div class="fv-gallery">';
        foreach ( $urls as $u ) {
            // Convert Google Drive share link to direct image view
            if ( preg_match('|drive\.google\.com/file/d/([a-zA-Z0-9_\-]+)|', $u, $m) ) {
                $u = "https://drive.google.com/uc?export=view&id={$m[1]}";
            }
            $out .= '<div class="fv-gal-item">
                        <img src="' . esc_url($u) . '" alt="" class="fv-gal-img" loading="lazy" />
                     </div>';
        }
        $out .= '</div>';
        return $out;
    }
}
