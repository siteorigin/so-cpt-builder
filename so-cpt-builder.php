<?php
/*
Plugin Name: SiteOrigin Post Type Builder
Description: Create page layouts and use any widget as a custom field type
Version: 1.0
Author: SiteOrigin
Author URI: https://siteorigin.com
Plugin URI: https://siteorigin.com/page-builder/post-types/
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

/**
 * Class SiteOrigin_Panels_CPT_Builder
 */
class SiteOrigin_Panels_CPT_Builder {

	const PAGE_ID = 'so_cpt_builder';
	const VERSION = '1.0';
	const JS_SUFFIX = '';
	const REQUIRED_PANELS = '2.1.2';

	public $form_errors;
	public $post_types;

	function __construct(){
		// These are post types created by the post type builder
		$this->post_types = get_option( 'socpt_types', array() );

		add_action( 'plugins_loaded', array($this, 'load_installer') );

		// Register the post types fairly late to make sure we're not conflicting
		add_action( 'init', array($this, 'register_post_types'), 15 );

		add_action( 'admin_menu', array($this, 'admin_menu') );

		add_action( 'admin_print_scripts-tools_page_' . self::PAGE_ID, array($this, 'enqueue_admin_scripts') );
		add_action( 'admin_print_styles-tools_page_' . self::PAGE_ID, array($this, 'enqueue_admin_styles') );

		// For saving the page layout
		add_action( 'load-tools_page_' . self::PAGE_ID, array($this, 'save_cpt_layout') );

		// Enqueue the admin scripts
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_post_admin_scripts') );

		// This is to modify Page Builder style sections for the custom post type interface
		add_filter( 'siteorigin_panels_widget_style_groups', array($this, 'widget_style_groups'), 10, 3 );
		add_filter( 'siteorigin_panels_widget_style_fields', array($this, 'widget_style_fields'), 10, 3 );

		// This is for displaying the metaboxes
		add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
		add_action( 'save_post', array($this, 'save_post'), 10, 2 );

		// Filter the single template
		add_filter( 'single_template', array($this, 'change_single_template') );

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
		if( function_exists('siteorigin_panels_admin_enqueue_scripts') ) {
			siteorigin_panels_admin_enqueue_scripts('', true);
		}
		wp_enqueue_script('siteorigin-panels-cpt-builder', plugin_dir_url(__FILE__) . '/js/so-cpt-builder' . self::JS_SUFFIX . '.js', array('jquery'), self::VERSION );
	}

	/**
	 * Enqueue any admin styles
	 */
	function enqueue_admin_styles(){
		if( function_exists('siteorigin_panels_admin_enqueue_styles') ) {
			siteorigin_panels_admin_enqueue_styles( '', true );
		}
		wp_enqueue_style('siteorigin-panels-cpt-builder', plugin_dir_url(__FILE__) . '/css/admin.css', array(), self::VERSION );
	}

	/**
	 * Load the Page Builder installer
	 */
	function load_installer(){
		include plugin_dir_path( __FILE__ ) . 'inc/panels-activation.php';
	}

	/**
	 * register all the neccessary post types
	 */
	function register_post_types(){
		if (empty($this->post_types) ) return;

		// Get all the post types registered elsewhere
		$post_types = get_post_types();
		foreach( $this->post_types as $slug => $args ) {
			if( !empty($post_types[$slug]) ) continue;

			$labels = array(
				'name' => $args['singular'],
				'singular_name' => $args['singular'],
				'menu_name' => $args['plural'],
				'name_admin_bar' => $args['singular'],
				'add_new' => __('Add New', 'so-cpt-builder'),
				'add_new_item' => sprintf( __('Add New %s', 'so-cpt-builder'), $args['singular'] ),
				'new_item' => sprintf( __('New %s', 'so-cpt-builder'), $args['singular'] ),
				'edit_item' => sprintf( __('Edit %s', 'so-cpt-builder'), $args['singular'] ),
				'view_item' => sprintf( __('View %s', 'so-cpt-builder'), $args['singular'] ),
				'all_items' => sprintf( __('All %s', 'so-cpt-builder'), $args['plural'] ),
				'search_items' => sprintf( __('Search %s', 'so-cpt-builder'), $args['plural'] ),
				'parent_item_colon' => sprintf( __('Parent %s:', 'so-cpt-builder'), $args['plural'] ),
				'not_found' => sprintf( __('No %s found.', 'so-cpt-builder'), strtolower( $args['plural'] ) ),
				'not_found_in_trash' => sprintf( __('No %s found in trash.', 'so-cpt-builder'), strtolower( $args['plural'] ) ),
			);

			$post_type_args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'menu_icon' => !empty($args['icon']) && $args['icon'] != 'admin-post' ? 'dashicons-' . $args['icon'] : null,
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
			);

			register_post_type($slug, $post_type_args);
		}
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
		if( empty( $_GET['page'] ) || $_GET['page'] !== self::PAGE_ID ) return;
		if( empty( $_POST['_sopanels_cpt_nonce'] ) || !wp_verify_nonce( $_POST['_sopanels_cpt_nonce'], 'save' ) ) return;

		if( !empty( $_POST['panels_data'] ) ) {
			$panels_data = json_decode( filter_input(INPUT_POST, 'panels_data', FILTER_DEFAULT), true );
			$panels_data['widgets'] = siteorigin_panels_process_raw_widgets($panels_data['widgets']);
			$panels_data = siteorigin_panels_styles_sanitize_all( $panels_data );

			// Lets process the panels_data
			foreach( $panels_data['widgets'] as &$widget ) {
				if( empty($widget['panels_info']['style']['so_cpt_custom_field']) ) continue;
				if( $widget['panels_info']['style']['so_cpt_custom_field'] && empty($widget['panels_info']['style']['so_cpt_id']) ) {
					$widget['panels_info']['style']['so_cpt_id'] = uniqid('', true);
				}
				$widget['panels_info']['style']['so_cpt_id'] = preg_replace('/[^A-Za-z0-9]+/', '', $widget['panels_info']['style']['so_cpt_id']);
			}

			update_option( 'so_cpt_layout[' . $_GET['type'] . ']', $panels_data );
			update_option( 'so_cpt_template[' . $_GET['type'] . ']', $_POST['post_type_template'] );
		}

		else if( !empty($_POST['so_post_type']) ) {
			// In this case, we're adding or editing a custom post type
			$post_type = stripslashes_deep( $_POST['so_post_type'] );

			$this->form_errors = array();

			if( empty( $post_type['slug'] ) ) {
				$this->form_errors['slug'] = __('Slug is required.', 'so-cpt-builder');
			}
			else if( $post_type['slug'] != sanitize_title_with_dashes($post_type['slug']) ) {
				$this->form_errors['slug'] = __('Invalid characters in post slug.', 'so-cpt-builder');
			}

			// There were no errors so we can handle the form input
			if( empty($this->form_errors) ) {
				if( empty($post_type['singular']) ) {
					$post_type['singular'] = ucfirst($post_type['slug']);
				}
				if( empty($post_type['plural']) ) {
					$post_type['plural'] = $post_type['singular'] . 's';
				}
				if( empty($post_type['description']) ){
					$post_type['description'] = __('Post type created with SiteOrigin Post Type Builder', 'so-cpt-builder');
				}

				// We're creating a new type
				$this->post_types[ $post_type['slug'] ] = $post_type;
				update_option('socpt_types', $this->post_types);
			}

			// Flush rewrite rules every time we edit post types
			flush_rewrite_rules();

			// Redirect back to the page to force a refresh
			wp_redirect( admin_url('tools.php?page=so_cpt_builder&action=build&type=' . esc_attr( $post_type['slug'] ) ) );
			die();
		}
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
				wp_enqueue_style('so-cpt-builder-posts', plugin_dir_url(__FILE__) .'css/metaboxes.css', array(), self::VERSION );
			}
		}
	}

	/**
	 * Display the admin page
	 */
	function admin_page(){

		if( !defined('SITEORIGIN_PANELS_VERSION') || ( SITEORIGIN_PANELS_VERSION !== 'dev' && version_compare( SITEORIGIN_PANELS_VERSION, self::REQUIRED_PANELS, '<' ) ) ) {
			include plugin_dir_path(__FILE__).'tpl/admin-no-pb.php';
			return;
		}

		$action = !empty($_GET['action']) ? $_GET['action'] : 'build';
		$type = !empty($_GET['type']) ? $_GET['type'] : '';

		switch( $action ) {
			case 'build' :
				$page_template = get_option('so_cpt_template[' . $type  . ']', '');
				if( empty($type) ) {
					include plugin_dir_path(__FILE__).'tpl/admin-home.php';
				}
				else {
					$panels_data = get_option( 'so_cpt_layout[' . $type . ']', array() );
					include plugin_dir_path(__FILE__).'tpl/admin-build.php';
				}
				break;

			case 'edit' :
				$active_post_type = !empty( $type ) && !empty( $this->post_types[$type] ) ? $this->post_types[$type] : array();
				$active_post_type = wp_parse_args( $active_post_type, array(
					'slug' => '',
					'singular' => '',
					'plural' => '',
					'icon' => 'admin-post',
					'description' => '',
				) );
				$dashicons = include plugin_dir_path(__FILE__) . '/inc/dashicons.php';

				include plugin_dir_path(__FILE__).'tpl/admin-edit.php';
				break;
		}

	}

	/**
	 * Add the style groups required for CPT
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	function widget_style_groups( $groups, $post_id, $args ) {
		// Ignore this when not displaying the Post Type Builder
		if( ( !empty( $args['builderType'] ) && $args['builderType'] === 'post_type_builder' ) || $args === false ) {
			$groups['so_cpt'] = array(
				'name' => __('Custom Post Type', 'siteorigin-panels'),
				'priority' => 1
			);
		}

		return $groups;
	}

	/**
	 * Add the styles fields required for the CPT interface
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function widget_style_fields($fields, $post_id, $args ) {
		// Ignore this when not displaying the Post Type Builder
		if( ( !empty( $args['builderType'] ) && $args['builderType'] === 'post_type_builder' ) || $args === false ) {
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
		}
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

					$label = !empty($widget['panels_info']['style']['so_cpt_custom_label']) ? $widget['panels_info']['style']['so_cpt_custom_label'] : __('Untitled', 'so-cpt-builder');
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

		$form = siteorigin_panels_render_form($widget['panels_info']['class'], $widget, false, $widget_id );
		$form = str_replace( 'widgets[' . $widget_id . ']', 'so_cpt_widget[' . $widget_id . ']', $form );

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

				return array($panels_data);
			}
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

	/**
	 * Get all the widgets that the post will use for.
	 *
	 * @param WP_Post|bool $post The post we're checking. False for the global post.
	 * @return array|bool Either the array of widgets or false if there are none.
	 */
	function get_post_widgets( $post = false ){
		if( empty($post) ) {
			// We'll use the global post
			$post = get_post();
		}
		$panels_data = get_option( 'so_cpt_layout[' . $post->post_type . ']', array() );
		return !empty($panels_data['widgets']) ? $panels_data['widgets'] : false;
	}

	/**
	 * Filter the template so we use the one Post Type Builder wants
	 *
	 * @param $template
	 *
	 * @return string
	 */
	function change_single_template( $template ){
		global $post;

		$cpt_template = get_option( 'so_cpt_template[' . $post->post_type . ']', '' );
		if( !empty($cpt_template) ) {
			if( $cpt_template == 'default' ) {
				$cpt_template = locate_template('page.php');
			}
			else {
				$cpt_template = locate_template( $cpt_template );
			}

			if( !empty($cpt_template) ) {
				$template = $cpt_template;
			}
		}

		return $template;
	}

}

// Create the initial single instance
SiteOrigin_Panels_CPT_Builder::single();