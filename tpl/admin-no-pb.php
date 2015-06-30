<div class="wrap" id="cpt-builder" class="so-cpt-builder-edit">

	<?php include dirname(__FILE__) . '/header.php' ?>

	<div class="intro-container">

		<?php if( !defined('SITEORIGIN_PANELS_VERSION') && class_exists('SiteOrigin_Panels_Plugin_Activation') ) : ?>

			<p>
				<?php _e("The post type builder requires SiteOrigin Page Builder. It's a powerful free plugin.", 'so-cpt-builder') ?>
				<a href="<?php echo esc_url( SiteOrigin_Panels_Plugin_Activation::get_install_url() ) ?>">
					<?php _e('Install Now', 'so-cpt-builder') ?>
				</a>
			</p>

		<?php elseif( defined('SITEORIGIN_PANELS_VERSION') && version_compare( SITEORIGIN_PANELS_VERSION, self::REQUIRED_PANELS ) ) : ?>

			<p>
				<?php _e("You need to update to the latest version of Page Builder to use this plugin.", 'so-cpt-builder') ?>
				<a href="<?php echo esc_url( SiteOrigin_Panels_Plugin_Activation::get_update_url() ) ?>">
					<?php _e('Update Now', 'so-cpt-builder') ?>
				</a>
			</p>

		<?php endif; ?>

	</div>

</div>