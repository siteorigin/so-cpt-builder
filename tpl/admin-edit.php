<div class="wrap" id="cpt-builder" class="so-cpt-builder-edit">

	<?php include plugin_dir_path(__FILE__) . '/header.php' ?>

	<form action="<?php echo add_query_arg(false, false) ?>" method="post" class="edit-form">

		<?php if( empty($type) ) : ?>
			<h3><?php _e('Create New Post Type', 'so-cpt-builder') ?></h3>
		<?php else : ?>
			<h3><?php printf( __('Edit Post Type', 'so-cpt-builder') ) ?></h3>
		<?php endif; ?>

		<table class="form-table">
			<tbody>
				<tr class="field-slug">
					<th scope="row"><?php _e('Slug', 'so-cpt-builder') ?></th>
					<td><input type="text" name="so_post_type[slug]" value="<?php echo esc_attr($active_post_type['slug']) ?>"></td>
				</tr>

				<tr class="field-singular-name">
					<th scope="row"><?php _e('Singular Name', 'so-cpt-builder') ?></th>
					<td><input type="text" name="so_post_type[singular]" value="<?php echo esc_attr($active_post_type['singular']) ?>"></td>
				</tr>

				<tr class="field-plural-name">
					<th scope="row"><?php _e('Plural Name', 'so-cpt-builder') ?></th>
					<td><input type="text" name="so_post_type[plural]" value="<?php echo esc_attr($active_post_type['plural']) ?>"></td>
				</tr>

				<tr class="field-menu-icon">
					<th scope="row"><?php _e('Menu Icon', 'so-cpt-builder') ?></th>
					<td>
						<select name="so_post_type[icon]">
							<?php foreach( $dashicons as $dashicon ) : ?>
								<option value="<?php echo esc_attr($dashicon) ?>" <?php selected($active_post_type['icon'], $dashicon) ?>><?php echo esc_html($dashicon) ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr class="field-description">
					<th scope="row"><?php _e('Description', 'so-cpt-builder') ?></th>
					<td>
						<textarea name="so_post_type[description]" rows="8"><?php echo esc_textarea($active_post_type['description']) ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<?php wp_nonce_field('save', '_sopanels_cpt_nonce') ?>
			<input type="hidden" name="current_slug" value="<?php echo esc_attr($active_post_type['slug']) ?>" />

			<input type="submit" class="button-primary" value="<?php esc_attr_e('Save Post Type', 'so-cpt-builder') ?>" />

			<?php if( !empty($active_post_type['slug']) ) : ?>
				<input type="submit" class="button-secondary button-delete" name="delete_post_type" value="<?php esc_attr_e('Delete Post Type', 'so-cpt-builder') ?>" />
			<?php endif; ?>
		</p>

	</form>

</div>