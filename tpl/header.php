<div class="page-header">
	<h1>
		<span class="icon"><img src="<?php echo plugin_dir_url(__FILE__) . '../css/images/icon.png' ?>" width="32px" height="40px" /></span>
		<?php _e('Post Type Builder', 'so-cpt-builder') ?>
	</h1>
	<ul class="post-types">
		<?php
		$post_types = get_post_types();
		foreach( $post_types as $t => $n ) {
			// We'll skip out attachments
			if( $t == 'attachment' ) continue;

			$pt = get_post_type_object($t);
			if( !$pt->public ) continue;

			$args = array(
				'action' => 'build',
				'type' => $t
			);

			$icon = empty($pt->menu_icon) ? 'dashicons-admin-post' : $pt->menu_icon;

			?>
			<li class="post-type <?php if( isset($_GET['type']) && $_GET['type'] == $t ) echo 'active' ?>">
				<span class="<?php if( strpos($icon, 'dashicons-') === 0 ) echo 'dashicons' ?> <?php echo sanitize_html_class($icon) ?>"></span>
				<a href="<?php echo add_query_arg($args) ?>"><?php echo $pt->labels->name ?></a>
			</li>
			<?php
		}
		?>

		<li class="post-type <?php if( !empty($_GET['action']) && $_GET['action'] == 'edit' && empty($_GET['type']) ) echo 'active' ?>">
			<span class="dashicons dashicons-plus"></span>
			<a href="<?php echo admin_url('tools.php?page=so_cpt_builder&action=edit') ?>" class="add-new"><?php _e('Add New', 'so-cpt-builder') ?></a>
		</li>
	</ul>
</div>