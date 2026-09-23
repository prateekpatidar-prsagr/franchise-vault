<?php
/**
 * Admin meta boxes, columns, and asset loading for fv_module CPT.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class FV_Admin {

    /** @var array<string, array> Content type definitions */
    private array $content_types = [
        'video'    => [ 'label' => 'Video (YouTube)',    'icon' => '🎬', 'color' => '#FF6B6B' ],
        'pdf'      => [ 'label' => 'PDF / Document',     'icon' => '📄', 'color' => '#4ECDC4' ],
        'recipe'   => [ 'label' => 'Recipe Card',        'icon' => '🍽️', 'color' => '#FFE66D' ],
        'image'    => [ 'label' => 'Image Gallery',      'icon' => '🖼️', 'color' => '#A8E6CF' ],
        'audio'    => [ 'label' => 'Audio',              'icon' => '🎧', 'color' => '#C9B1FF' ],
        'document' => [ 'label' => 'Document (LOI/EOI)', 'icon' => '📋', 'color' => '#FFB347' ],
    ];

    public function __construct() {
        add_action( 'add_meta_boxes',                        [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_fv_module',                   [ $this, 'save_meta' ], 10, 2 );
        add_filter( 'manage_fv_module_posts_columns',        [ $this, 'columns' ] );
        add_action( 'manage_fv_module_posts_custom_column',  [ $this, 'column_content' ], 10, 2 );
        add_action( 'admin_enqueue_scripts',                 [ $this, 'enqueue' ] );
    }

    // ── Meta Boxes ───────────────────────────────────────────────────────────

    public function add_meta_boxes(): void {
        add_meta_box(
            'fv_module_details',
            'Module Details',
            [ $this, 'render_meta_box' ],
            'fv_module',
            'normal',
            'high'
        );
    }

    public function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'fv_save_module', 'fv_nonce' );

        $type      = get_post_meta( $post->ID, '_fv_type',       true ) ?: 'video';
        $yt_url    = get_post_meta( $post->ID, '_fv_yt_url',     true );
        $drive_url = get_post_meta( $post->ID, '_fv_drive_url',  true );
        $audio_url = get_post_meta( $post->ID, '_fv_audio_url',  true );
        $img_urls  = get_post_meta( $post->ID, '_fv_img_urls',   true );
        $short     = get_post_meta( $post->ID, '_fv_short_desc', true );
        $thumb     = get_post_meta( $post->ID, '_fv_thumb_url',  true );
        ?>
        <div id="fv-mb" class="fv-mb">

            <!-- Short Description -->
            <div class="fv-mb-field">
                <label for="fv_short_desc">
                    <span class="fv-label-icon">📝</span>
                    Short Description <span class="fv-label-hint">(shown on module card)</span>
                </label>
                <input type="text" id="fv_short_desc" name="fv_short_desc"
                    value="<?php echo esc_attr( $short ); ?>"
                    placeholder="One-line summary of this training module..." />
            </div>

            <!-- Card Thumbnail -->
            <div class="fv-mb-field">
                <label for="fv_thumb_url">
                    <span class="fv-label-icon">🖼️</span>
                    Card Thumbnail URL <span class="fv-label-hint">(optional — or use Featured Image)</span>
                </label>
                <div class="fv-thumb-row">
                    <input type="url" id="fv_thumb_url" name="fv_thumb_url"
                        value="<?php echo esc_url( $thumb ); ?>"
                        placeholder="https://..." />
                    <button type="button" id="fv_pick_thumb" class="button button-secondary">
                        📁 Choose from Library
                    </button>
                </div>
                <?php if ( $thumb ) : ?>
                    <img id="fv-thumb-preview" src="<?php echo esc_url( $thumb ); ?>" />
                <?php else : ?>
                    <img id="fv-thumb-preview" src="" style="display:none;" />
                <?php endif; ?>
            </div>

            <!-- Content Type Selector -->
            <div class="fv-mb-field">
                <label>
                    <span class="fv-label-icon">📂</span>
                    Content Type
                </label>
                <div class="fv-type-grid">
                    <?php foreach ( $this->content_types as $key => $ct ) : ?>
                        <label class="fv-type-tile <?php echo $type === $key ? 'is-active' : ''; ?>"
                               style="--tile-color: <?php echo esc_attr( $ct['color'] ); ?>">
                            <input type="radio" name="fv_type" value="<?php echo esc_attr( $key ); ?>"
                                <?php checked( $type, $key ); ?> />
                            <span class="fv-tile-icon"><?php echo $ct['icon']; ?></span>
                            <span class="fv-tile-label"><?php echo esc_html( $ct['label'] ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Conditional Fields ──────────────────────────────────── -->

            <!-- VIDEO -->
            <div class="fv-cond" data-types="video">
                <div class="fv-cond-inner fv-cond-video">
                    <div class="fv-mb-field">
                        <label for="fv_yt_url">
                            <span class="fv-label-icon">▶️</span>
                            YouTube Video URL
                        </label>
                        <input type="url" id="fv_yt_url" name="fv_yt_url"
                            value="<?php echo esc_url( $yt_url ); ?>"
                            placeholder="https://www.youtube.com/watch?v=..." />
                        <p class="fv-desc">
                            ✅ Set the video to <strong>Unlisted</strong> on YouTube.<br>
                            ✅ Make sure <strong>embedding is allowed</strong> in YouTube Studio settings.
                        </p>
                    </div>
                </div>
            </div>

            <!-- PDF / RECIPE / DOCUMENT -->
            <div class="fv-cond" data-types="pdf recipe document">
                <div class="fv-cond-inner fv-cond-doc">
                    <div class="fv-mb-field">
                        <label for="fv_drive_url">
                            <span class="fv-label-icon">☁️</span>
                            Google Drive File URL
                        </label>
                        <input type="url" id="fv_drive_url" name="fv_drive_url"
                            value="<?php echo esc_url( $drive_url ); ?>"
                            placeholder="https://drive.google.com/file/d/..." />
                        <p class="fv-desc">
                            ✅ In Google Drive → Share → Set to <strong>"Anyone with the link can view"</strong>.<br>
                            ✅ Paste the full share URL here. The file will open in an embedded inline viewer — no download button.
                        </p>
                    </div>
                </div>
            </div>

            <!-- AUDIO -->
            <div class="fv-cond" data-types="audio">
                <div class="fv-cond-inner fv-cond-audio">
                    <div class="fv-mb-field">
                        <label for="fv_audio_url">
                            <span class="fv-label-icon">🎵</span>
                            Audio File URL (Google Drive)
                        </label>
                        <input type="url" id="fv_audio_url" name="fv_audio_url"
                            value="<?php echo esc_url( $audio_url ); ?>"
                            placeholder="https://drive.google.com/file/d/..." />
                        <p class="fv-desc">Upload your MP3/WAV to Google Drive and paste the share link.</p>
                    </div>
                </div>
            </div>

            <!-- IMAGE GALLERY -->
            <div class="fv-cond" data-types="image">
                <div class="fv-cond-inner fv-cond-image">
                    <div class="fv-mb-field">
                        <label for="fv_img_urls">
                            <span class="fv-label-icon">🖼️</span>
                            Image URLs <span class="fv-label-hint">(one per line)</span>
                        </label>
                        <textarea id="fv_img_urls" name="fv_img_urls" rows="6"
                            placeholder="https://drive.google.com/uc?id=FILE_ID&#10;https://..."><?php echo esc_textarea( $img_urls ); ?></textarea>
                        <p class="fv-desc">For Google Drive images: Share → Copy Link → Paste here. Each image on a new line.</p>
                    </div>
                </div>
            </div>

        </div><!-- #fv-mb -->
        <?php
    }

    // ── Save Meta ────────────────────────────────────────────────────────────

    public function save_meta( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['fv_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['fv_nonce'], 'fv_save_module' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $meta_map = [
            '_fv_type'       => [ 'sanitize_key',              'fv_type' ],
            '_fv_yt_url'     => [ 'esc_url_raw',               'fv_yt_url' ],
            '_fv_drive_url'  => [ 'esc_url_raw',               'fv_drive_url' ],
            '_fv_audio_url'  => [ 'esc_url_raw',               'fv_audio_url' ],
            '_fv_img_urls'   => [ 'sanitize_textarea_field',   'fv_img_urls' ],
            '_fv_short_desc' => [ 'sanitize_text_field',       'fv_short_desc' ],
            '_fv_thumb_url'  => [ 'esc_url_raw',               'fv_thumb_url' ],
        ];

        foreach ( $meta_map as $meta_key => [ $fn, $field ] ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $meta_key, call_user_func( $fn, $_POST[ $field ] ) );
            }
        }
    }

    // ── Admin Columns ────────────────────────────────────────────────────────

    public function columns( array $cols ): array {
        $new = [];
        foreach ( $cols as $k => $v ) {
            $new[ $k ] = $v;
            if ( 'title' === $k ) {
                $new['fv_type'] = 'Type';
                $new['fv_cat']  = 'Category';
            }
        }
        return $new;
    }

    public function column_content( string $col, int $id ): void {
        if ( 'fv_type' === $col ) {
            $type = get_post_meta( $id, '_fv_type', true ) ?: 'video';
            $ct   = $this->content_types[ $type ] ?? [ 'icon' => '📁', 'label' => $type, 'color' => '#6C63FF' ];
            printf(
                '<span class="fv-col-badge" style="background:%s20;color:%s;border-color:%s40;">%s %s</span>',
                esc_attr( $ct['color'] ), esc_attr( $ct['color'] ), esc_attr( $ct['color'] ),
                $ct['icon'], esc_html( $ct['label'] )
            );
        }
        if ( 'fv_cat' === $col ) {
            $terms = get_the_terms( $id, 'fv_category' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
            } else {
                echo '<span style="color:#999;">—</span>';
            }
        }
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueue( string $hook ): void {
        $screen = get_current_screen();
        if ( ! $screen ) return;

        // Load on module edit AND settings page
        $is_module = ( 'fv_module' === $screen->post_type );
        $is_settings = ( str_contains( $hook, 'fv-settings' ) );

        if ( $is_module ) {
            wp_enqueue_media();
        }

        if ( $is_module || $is_settings ) {
            wp_enqueue_style(
                'fv-admin-css',
                FV_PLUGIN_URL . 'admin/css/fv-admin.css',
                [],
                FV_VERSION
            );
            wp_enqueue_script(
                'fv-admin-js',
                FV_PLUGIN_URL . 'admin/js/fv-admin.js',
                [ 'jquery' ],
                FV_VERSION,
                true
            );
        }
    }
}
