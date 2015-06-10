<?php
/*
Plugin Name: SiteOrigin Custom Post Type Builder
Description: Create page layouts and use any widget as a custom field type
Version: 1.0
Author: SiteOrigin
Author URI: https://siteorigin.com
Plugin URI: https://siteorigin.com/cpt-builder/
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

define('SO_CPT_BUILDER_VERSION', '1.0');

class SiteOrigin_Panels_CPT_Builder {

	const PAGE_ID = 'so_cpt_builder';

	function __construct(){
		add_action( 'admin_menu', array($this, 'admin_menu') );

		add_action( 'admin_print_scripts-tools_page_' . self::PAGE_ID, array($this, 'enqueue_admin_scripts') );
		add_action( 'admin_print_styles-tools_page_' . self::PAGE_ID, array($this, 'enqueue_admin_styles') );

		// For saving the page layout
		add_action( 'load-tools_page_' . self::PAGE_ID, array($this, 'save_cpt_layout') );

		// Enqueue the admin scripts
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_post_admin_scripts') );

		// This is to modify Page Builder style sections for the custom post type interface
		add_filter( 'siteorigin_panels_widget_style_groups', array($this, 'widget_style_groups') );
		add_filter( 'siteorigin_panels_widget_style_fields', array($this, 'widget_style_fields') );

		// This is for displaying the metaboxes
		add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
		add_action( 'save_post', array($this, 'save_post'), 10, 2 );

		// Filter panels_data
		if( !is_admin() ) {
			add_filter( 'get_post_metadata', array($this, 'post_metadata'), 10, 3 );
		}
	}

	/**
	 * Get the single instance
	 *
	 * @return SiteOrigin_Panels_CPT_Builder
	 */
	static function single(){
		static $single;
		if( empty($single) ) {
			$single = new SiteOrigin_Panels_CPT_Builder();
		}
		return $single;
	}

	/**
	 * Check if this is an admin page
	 *
	 * @param $is_page
	 *
	 * @return bool
	 */
	function is_cpt_builder_page( $is_page ){
		$screen = get_current_screen();
		if( $screen->id == 'tools_page_so_cpt_builder' ) {
			$is_page = true;
		}

		return $is_page;
	}

	/**
	 * Enqueue any admin scripts
	 */
	function enqueue_admin_scripts(){
		siteorigin_panels_admin_enqueue_scripts('', true);
		wp_enqueue_script('siteorigin-panels-cpt-builder', plugin_dir_url(__FILE__) . '/js/so-cpt-builder.js', array('jquery') );
	}

	/**
	 * Enqueue any admin styles
	 */
	function enqueue_admin_styles(){
		siteorigin_panels_admin_enqueue_styles('', true);
		wp_enqueue_style('siteorigin-panels-cpt-builder', plugin_dir_url(__FILE__) . '/css/admin.css' );
	}

	/**
	 * Add the admin menu entry
	 */
	function admin_menu(){
		add_submenu_page( 'tools.php', __('Post Type Builder', 'so-cpt-builder'), __('Post Type Builder', 'so-cpt-builder'), 'manage_options', self::PAGE_ID, array($this, 'admin_page') );
	}

	/**
	 * Initialize the admin
	 */
	function save_cpt_layout(){
		// Lets check if we're saving something
		if( empty($_GET['page']) || $_GET['page'] !== self::PAGE_ID ) return;
		if( empty($_POST['panels_data']) ) return;
		if( empty($_POST['_sopanels_cpt_nonce']) || !wp_verify_nonce($_POST['_sopanels_cpt_nonce'], 'save') ) return;

		$panels_data = json_decode( filter_input(INPUT_POST, 'panels_data', FILTER_DEFAULT), true );

		// Lets process the panels_data
		foreach( $panels_data['widgets'] as &$widget ) {
			if( empty($widget['panels_info']['style']['so_cpt_custom_field']) ) continue;
			if( $widget['panels_info']['style']['so_cpt_custom_field'] && empty($widget['panels_info']['style']['so_cpt_id']) ) {
				$widget['panels_info']['style']['so_cpt_id'] = uniqid('', true);
			}
			$widget['panels_info']['style']['so_cpt_id'] = preg_replace('/[^A-Za-z0-9]+/', '', $widget['panels_info']['style']['so_cpt_id']);
		}

		update_option( 'so_cpt_layout[' . $_GET['type'] . ']', $panels_data );
	}

	/**
	 * Enqueue admin scripts
	 */
	function enqueue_post_admin_scripts(){
		$screen = get_current_screen();
		if( $screen->base == 'post' ) {
			global $post;
			$panels_data = get_option( 'so_cpt_layout[' . $post->post_type . ']', array() );
			if( !empty( $panels_data['widgets'] ) ) {
				wp_enqueue_style('so-cpt-builder-posts', plugin_dir_url(__FILE__) .'css/admin.css', array(), SO_CPT_BUILDER_VERSION );
			}
		}
	}

	/**
	 * Display the admin page
	 */
	function admin_page(){
		if( !empty($_GET['type']) ) {
			$panels_data = get_option( 'so_cpt_layout[' . $_GET['type'] . ']', array() );
		}
		else {
			$panels_data = array();
		}

		include plugin_dir_path(__FILE__).'tpl/admin-page.php';
	}

	/**
	 * Add the style groups required for CPT
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	function widget_style_groups($groups) {
		$groups['so_cpt'] = array(
			'name' => __('Custom Post Type', 'siteorigin-panels'),
			'priority' => 1
		);

		return $groups;
	}

	/**
	 * Add the styles fields required for the CPT interface
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function widget_style_fields($fields) {

		$fields['so_cpt_custom_field'] = array(
			'name' => __('Custom Field', 'siteorigin-panels'),
			'type' => 'checkbox',
			'group' => 'so_cpt',
			'description' => __('Is this a custom field', 'siteorigin-panels'),
			'priority' => 5,
		);

		$fields['so_cpt_custom_label'] = array(
			'name' => __('Field Label', 'siteorigin-panels'),
			'type' => 'text',
			'group' => 'so_cpt',
			'description' => __('Label for this field', 'siteorigin-panels'),
			'priority' => 10,
		);

		$fields['so_cpt_id'] = array(
			'name' => __('Field ID', 'siteorigin-panels'),
			'type' => 'text',
			'group' => 'so_cpt',
			'description' => __('ID that identifies this field (auto generated)', 'siteorigin-panels'),
			'priority' => 15,
		);
		return $fields;
	}

	/**
	 * Add the widget metaboxes
	 */
	function add_meta_boxes(){
		$post_types = get_post_types();
		foreach( $post_types as $post_type ) {
			$panels_data = get_option( 'so_cpt_layout[' . $post_type . ']', array() );
			if( !empty($panels_data['widgets']) ) {
				foreach( $panels_data['widgets'] as $widget ) {
					if( empty($widget['panels_info']['style']['so_cpt_custom_field']) ) continue;
					$widget['panels_info']['style']['so_cpt_custom_label'];

					$label = $widget['panels_info']['style']['so_cpt_custom_label'];
					if( empty($label) ) {
						$label = __('Untitled', 'so-cpt-builder');
					}
					$widget_id = $widget['panels_info']['style']['so_cpt_id'];
					add_meta_box(
						'so-cpt-' . $widget_id,
						sprintf( __('Custom Field: %s', 'so-cpt-builder'), $label ),
						array($this, 'display_metabox'),
						$post_type,
						'advanced',
						'default',
						array(
							'widget' => $widget
						)
					);
				}
			}
		}
	}

	/**
	 * Display a widget metabox
	 */
	function display_metabox( $post, $metabox ){
		$default_widget = $metabox['args']['widget'];
		$widget_id = $default_widget['panels_info']['style']['so_cpt_id'];

		$widget = get_post_meta( $post->ID, 'socpt[' . $widget_id . ']', true );
		if( empty($widget) ) $widget = array();

		$widget = wp_parse_args( $widget, $default_widget );

		$form = siteorigin_panels_render_form($widget['panels_info']['class'], $widget);
		$form = str_replace( 'widgets[{$id}]', 'so_cpt_widget[' . $widget_id . ']', $form );
		$form = str_replace( '{$id}', $widget_id, $form );

		?><div class="so-cpt-widget-wrapper"><?php echo $form ?></div><?php
	}

	/**
	 * @param $post_id
	 * @param $post
	 */
	function save_post($post_id, $post) {
		if( empty($_POST['so_cpt_widget']) ) return;
		$panels_data = get_option( 'so_cpt_layout[' . $post->post_type . ']', array() );

		if( !empty($panels_data['widgets']) ) {

			$post_widgets = stripslashes_deep( $_POST['so_cpt_widget'] );

			foreach( $panels_data['widgets'] as $widget ){
				if( empty( $widget['panels_info']['style']['so_cpt_custom_field'] ) ) continue;
				if( empty( $widget['panels_info']['style']['so_cpt_id'] ) ) continue;

				$widget_id = $widget['panels_info']['style']['so_cpt_id'];
				if( empty($post_widgets[$widget_id]) ) continue;

				update_post_meta( $post_id, 'socpt[' . $widget_id . ']', $post_widgets[$widget_id] );
			}
		}
	}

	/**
	 * Filter the post metadata
	 *
	 * @param $value
	 * @param $post_id
	 * @param $meta_key
	 *
	 * @return mixed|void
	 */
	function post_metadata( $value, $post_id, $meta_key ){
		if( $meta_key === 'panels_data' ) {
			$post = get_post($post_id);
			$panels_data = get_option( 'so_cpt_layout[' . $post->post_type . ']', array() );

			if( !empty( $panels_data['widgets'] ) ) {
				foreach( $panels_data['widgets'] as $i => $widget ) {
					if( empty( $widget['panels_info']['style']['so_cpt_custom_field'] ) ) continue;
					if( empty( $widget['panels_info']['style']['so_cpt_id'] ) ) continue;

					$widget_id = $widget['panels_info']['style']['so_cpt_id'];

					$post_widget = get_post_meta( $post_id, 'socpt[' . $widget_id . ']', true );
					if( !empty($post_widget) ) {
						$panels_data['widgets'][$i] = wp_parse_args( $post_widget, $widget );
					}
				}
			}
			return array($panels_data);
		}

		return $value;
	}

	/**
	 * Get the post type panels data.
	 *
	 * @param $post_type
	 *
	 * @return array
	 */
	function post_type_panels_data( $post_type ) {
		$panels_data = get_option( 'so_cpt_layout[' . $post_type . ']', array() );
		return $panels_data;
	}

}

// Create the initial single instance
SiteOrigin_Panels_CPT_Builder::single();