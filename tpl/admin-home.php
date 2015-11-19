<div class="wrap" id="cpt-builder" class="so-cpt-builder-edit">

	<?php include plugin_dir_path(__FILE__) . '/header.php' ?>

	<div class="intro-container">

		<?php if( !empty($_GET['notify']) && $_GET['notify'] == 'deleted' ) : ?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><strong><?php _e('Post type deleted', 'so-cpt-builder') ?></strong></p>
			</div>
		<?php endif; ?>

		<?php _e('Welcome to the SiteOrigin Post Type Builder.', 'siteorigin-panels') ?>
		<?php _e('Click on one of the tabs above or click Add New to create a new post type.', 'siteorigin-panels') ?>

	</div>

</div>