<?php 

function register_cpt_artichoke_template() {
 
    $labels = array(
        'name' => _x( 'Artichoke Templates', 'artichoke_domain' ),
        'singular_name' => _x( 'Artichoke Template', 'artichoke_domain' ),
        'add_new' => _x( 'Add New', 'artichoke_domain' ),
        'add_new_item' => _x( 'Add New Artichoke Template', 'artichoke_domain' ),
        'edit_item' => _x( 'Edit Artichoke Template', 'artichoke_domain' ),
        'new_item' => _x( 'New Artichoke Template', 'artichoke_domain' ),
        'view_item' => _x( 'View Artichoke Template', 'artichoke_domain' ),
        'search_items' => _x( 'Search Artichoke Templates', 'artichoke_domain' ),
        'not_found' => _x( 'No Artichoke Templates found', 'artichoke_domain' ),
        'not_found_in_trash' => _x( 'No Artichoke Templates found in Trash', 'artichoke_domain' ),
        'parent_item_colon' => _x( 'Parent Artichoke Template:', 'artichoke_domain' ),
        'menu_name' => _x( 'Artichoke Templates', 'artichoke_domain' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Artichoke Templates',
        'supports' => array('title', 'author'),
        'taxonomies' => array(),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 2,
        'menu_icon' => 'dashicons-welcome-widgets-menus',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'page'
    );
 
    register_post_type( 'artichoke_template', $args );
}
 
add_action( 'init', 'register_cpt_artichoke_template' );
