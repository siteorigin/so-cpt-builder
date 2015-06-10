<div class="wrap" id="panels-cpt-builder">

	<div class="page-header">
		<h2>Post Type Builder</h2>
		<div class="post-types">
			<?php
			$post_types = get_post_types();
			foreach( $post_types as $type => $name ) {
				?><a href="<?php echo add_query_arg('type', $type) ?>"><?php echo $name ?></a> <?php
			}
			?>
		</div>
	</div>


	<form action="<?php echo add_query_arg(false, false) ?>" class="hide-if-no-js siteorigin-panels-builder-form" method="post" id="panels-cpt-form">

		<div class="siteorigin-panels-builder so-panels-loading">

		</div>

		<input name="panels_data" value="" type="hidden" class="siteorigin-panels-data-field" id="panels-data-field-cpt-builder" />
		<script type="text/javascript">
			document.getElementById('panels-data-field-cpt-builder').value = decodeURIComponent("<?php echo rawurlencode( json_encode($panels_data) ); ?>");
		</script>

		<p><input type="submit" class="button button-primary" id="panels-save-home-page" value="<?php esc_attr_e('Save Post Type', 'siteorigin-panels') ?>" /></p>

		<?php wp_nonce_field('save', '_sopanels_cpt_nonce') ?>

	</form>

</div>