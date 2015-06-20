<div class="wrap" id="cpt-builder">

	<?php include dirname(__FILE__) . '/header.php' ?>

	<form action="<?php echo add_query_arg(false, false) ?>" class="hide-if-no-js siteorigin-panels-builder-form" method="post" id="panels-cpt-form" data-type="post_type_builder">

		<div class="page-template">
			<label for="page-template-selector"><?php _e('Page Template: ', 'so-cpt-builder') ?></label>
			<?php $templates = get_page_templates(); ?>
			<select name="post_type_template" id="page-template-selector">
				<option value="" <?php selected($page_template, '') ?>><?php esc_html_e( 'Default', 'so-cpt-builder' ) ?></option>
				<option value="default" <?php selected($page_template, 'default') ?>><?php esc_html_e( 'Default Page Template', 'so-cpt-builder' ) ?></option>
				<?php foreach( $templates as $template_name => $template_path ) : ?>
					<option value="<?php echo esc_attr($template_path) ?>" <?php selected($page_template, $template_path) ?>><?php echo esc_html($template_name) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="siteorigin-panels-builder so-panels-loading">

		</div>

		<input name="panels_data" value="" type="hidden" class="siteorigin-panels-data-field" id="panels-data-field-cpt-builder" />
		<script type="text/javascript">
			document.getElementById('panels-data-field-cpt-builder').value = decodeURIComponent("<?php echo rawurlencode( json_encode($panels_data) ); ?>");
		</script>


		<?php if( !empty($this->post_types[$type]) ) : ?>
			<a href="<?php echo add_query_arg('action', 'edit') ?>" class="button-secondary edit-post-type"><?php _e('Edit Post Type', 'so-cpt-builder') ?></a>
		<?php endif; ?>

		<p><input type="submit" class="button button-primary" id="panels-save-home-page" value="<?php esc_attr_e('Save Post Type', 'so-cpt-builder') ?>" /></p>

		<?php wp_nonce_field('save', '_sopanels_cpt_nonce') ?>

	</form>

</div>