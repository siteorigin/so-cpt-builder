<div class="wrap" id="cpt-builder">

	<?php include dirname(__FILE__) . '/header.php' ?>

	<form action="<?php echo add_query_arg(false, false) ?>" class="hide-if-no-js siteorigin-panels-builder-form" method="post" id="panels-cpt-form">

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