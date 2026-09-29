<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'dtc_cases_register_cpt' );

function dtc_cases_register_cpt() {
	$labels = [
		'name'                  => 'Work',
		'singular_name'         => 'Case',
		'add_new'               => 'Add New Case',
		'add_new_item'          => 'Add New Case',
		'edit_item'             => 'Edit Case',
		'new_item'              => 'New Case',
		'view_item'             => 'View Case',
		'view_items'            => 'View Cases',
		'search_items'          => 'Search Cases',
		'not_found'             => 'No cases found.',
		'not_found_in_trash'    => 'No cases found in Trash.',
		'all_items'             => 'All Cases',
		'archives'              => 'Case Archives',
		'menu_name'             => 'Work',
		'name_admin_bar'        => 'Case',
		'featured_image'        => 'Hero / Thumbnail Image',
		'set_featured_image'    => 'Set hero image',
		'remove_featured_image' => 'Remove hero image',
		'use_featured_image'    => 'Use as hero image',
	];

	register_post_type( 'dtc_case', [
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => [ 'slug' => 'work', 'with_front' => false ],
		'supports'      => [ 'title', 'thumbnail', 'revisions', 'page-attributes' ],
		'show_in_rest'  => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-portfolio',
		'show_in_menu'  => true,
	] );
}
