<?php
/**
 * Registers the fv_module Custom Post Type and its taxonomies.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class FV_CPT {

    public function __construct() {
        $this->register_post_type();
        $this->register_taxonomies();
    }

    private function register_post_type(): void {
        $labels = [
            'name'               => 'Training Modules',
            'singular_name'      => 'Training Module',
            'add_new'            => 'Add Module',
            'add_new_item'       => 'Add New Module',
            'edit_item'          => 'Edit Module',
            'new_item'           => 'New Module',
            'view_item'          => 'View Module',
            'search_items'       => 'Search Modules',
            'not_found'          => 'No modules found.',
            'not_found_in_trash' => 'No modules in Trash.',
            'menu_name'          => 'FranchiseVault',
            'all_items'          => 'All Modules',
        ];

        register_post_type( 'fv_module', [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'capability_type'   => 'post',
            'hierarchical'      => false,
            'supports'          => [ 'title', 'editor', 'thumbnail' ],
            'has_archive'       => false,
            'rewrite'           => false,
            'query_var'         => false,
            'menu_icon'         => 'dashicons-welcome-learn-more',
            'menu_position'     => 30,
        ] );
    }

    private function register_taxonomies(): void {
        // fv_category — hierarchical (like WordPress categories)
        register_taxonomy( 'fv_category', 'fv_module', [
            'labels' => [
                'name'          => 'Categories',
                'singular_name' => 'Category',
                'add_new_item'  => 'Add New Category',
                'edit_item'     => 'Edit Category',
                'all_items'     => 'All Categories',
                'menu_name'     => 'Categories',
            ],
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'hierarchical'      => true,
            'rewrite'           => false,
        ] );
    }
}
