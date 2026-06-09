<?php
if ( ! class_exists( 'WP_Media_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php';
}
class DIPL_Media_List_Table extends WP_Media_List_Table {

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 *
	 * @global string $mode List table view mode.
	 */
	public function inline_edit() {
		global $mode;

		$screen = $this->screen;

		$post             = get_default_post_to_edit( $screen->post_type );
		$post_type_object = get_post_type_object( $screen->post_type );

		$taxonomy_names          = get_object_taxonomies( $screen->post_type );
		$hierarchical_taxonomies = array();
		$flat_taxonomies         = array();

		foreach ( $taxonomy_names as $taxonomy_name ) {

			$taxonomy = get_taxonomy( $taxonomy_name );

			$show_in_quick_edit = $taxonomy->show_in_quick_edit;

			/**
			 * Filters whether the current taxonomy should be shown in the Quick Edit panel.
			 *
			 * @since 4.2.0
			 *
			 * @param bool   $show_in_quick_edit Whether to show the current taxonomy in Quick Edit.
			 * @param string $taxonomy_name      Taxonomy name.
			 * @param string $post_type          Post type of current Quick Edit post.
			 */
			if ( ! apply_filters( 'quick_edit_show_taxonomy', $show_in_quick_edit, $taxonomy_name, $screen->post_type ) ) {
				continue;
			}

			if ( $taxonomy->hierarchical ) {
				$hierarchical_taxonomies[] = $taxonomy;
			} else {
				$flat_taxonomies[] = $taxonomy;
			}
		}

		$m            = ( isset( $mode ) && 'list' === $mode ) ? 'list' : 'grid';
		$can_publish  = current_user_can( $post_type_object->cap->publish_posts );
		$core_columns = array(
			'cb'         => true,
			'title'      => true,
			'author'     => true,
			'date'       => true,
		);

		?>

		<form method="get">
		<table style="display: none"><tbody id="inlineedit">
		<?php
		$hclass              = count( $hierarchical_taxonomies ) ? 'post' : 'page';
		$inline_edit_classes = "inline-edit-row inline-edit-row-$hclass";
		$bulk_edit_classes   = "bulk-edit-row dge-attachment-bulk-edit-row bulk-edit-row-$hclass bulk-edit-{$screen->post_type}";
		$quick_edit_classes  = "quick-edit-row quick-edit-row-$hclass inline-edit-{$screen->post_type}";

		$bulk = 0;
		while ( $bulk < 2 ) :
			$classes  = $inline_edit_classes . ' ';
			$classes .= $bulk ? $bulk_edit_classes : $quick_edit_classes;
			?>
			<tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="<?php echo esc_attr( $classes ); ?>" style="display: none">
			<td colspan="<?php echo esc_attr( $this->get_column_count() ); ?>" class="colspanchange">

			<fieldset class="inline-edit-col-left">
				<legend class="inline-edit-legend"><?php echo $bulk ? esc_html__( 'Bulk Edit', 'divi-plus' ) : esc_html__( 'Quick Edit', 'divi-plus' ); ?></legend>
				<div class="inline-edit-col">

				<?php if ( post_type_supports( $screen->post_type, 'title' ) ) : ?>

					<?php if ( $bulk ) : ?>

						<div id="bulk-title-div">
							<div id="bulk-titles"></div>
						</div>
	
					<?php endif; // $bulk ?>

				<?php endif; // post_type_supports( ... 'title' ) ?>

				</div>
			</fieldset>

			<?php if ( count( $hierarchical_taxonomies ) && ! $bulk ) : ?>

				<fieldset class="inline-edit-col-center inline-edit-categories">
					<div class="inline-edit-col">

					<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

						<span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ); ?></span>
						<input type="hidden" name="<?php echo ( 'category' === $taxonomy->name ) ? 'post_category[]' : 'tax_input[' . esc_attr( $taxonomy->name ) . '][]'; ?>" value="0" />
						<ul class="cat-checklist <?php echo esc_attr( $taxonomy->name ); ?>-checklist">
							<?php wp_terms_checklist( null, array( 'taxonomy' => $taxonomy->name ) ); ?>
						</ul>

					<?php endforeach; // $hierarchical_taxonomies as $taxonomy ?>

					</div>
				</fieldset>

			<?php endif; // count( $hierarchical_taxonomies ) && ! $bulk ?>

			<fieldset class="inline-edit-col-right">
				<div class="inline-edit-col">
					<?php if ( 'attachment' === $screen->post_type && $can_publish && current_user_can( $post_type_object->cap->edit_others_posts ) ) : ?>
						<?php if ( $bulk ) : ?>
							<div class="inline-edit-group inline-edit-alt-text wp-clearfix">
								<label>
									<span class="title"><?php esc_html_e( 'Alt Text', 'divi-plus' ); ?></span>
									<input type="text" name="meta_input[_wp_attachment_image_alt]" value="">
								</label>
							</div>
							<div class="inline-edit-group inline-edit-custom-link wp-clearfix">
								<label>
									<span class="title"><?php esc_html_e( 'Custom Link', 'divi-plus' ); ?></span>
									<input type="text" name="meta_input[dge_attachment_link]" value="">
								</label>
							</div>
							<div class="inline-edit-group wp-clearfix">
								<label>
									<span class="title"><?php esc_html_e( 'Caption', 'divi-plus' ); ?></span>
									<textarea name="caption" value=""></textarea>
								</label>
							</div>
							<div class="inline-edit-group wp-clearfix">
								<label>
									<span class="title"><?php esc_html_e( 'Description', 'divi-plus' ); ?></span>
									<textarea name="description" value=""></textarea>
								</label>
							</div>
						<?php endif; // $bulk ?>
					<?php endif; ?>
				</div>
			</fieldset>

			<?php
			list( $columns ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {
				if ( isset( $core_columns[ $column_name ] ) ) {
					continue;
				}

				if ( $bulk ) {

					/**
					 * Fires once for each column in Bulk Edit mode.
					 *
					 * @since 2.7.0
					 *
					 * @param string $column_name Name of the column to edit.
					 * @param string $post_type   The post type slug.
					 */
					do_action( 'bulk_edit_custom_box', $column_name, $screen->post_type );
				} else {

					/**
					 * Fires once for each column in Quick Edit mode.
					 *
					 * @since 2.7.0
					 *
					 * @param string $column_name Name of the column to edit.
					 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
					 * @param string $taxonomy    The taxonomy name, if any.
					 */
					do_action( 'quick_edit_custom_box', $column_name, $screen->post_type, '' );
				}
			}
			?>

			<div class="submit inline-edit-save">
				<button type="button" class="button cancel alignleft"><?php esc_html_e( 'Cancel', 'divi-plus' ); ?></button>

				<?php if ( ! $bulk ) : ?>
					<?php wp_nonce_field( 'inlineeditnonce', '_inline_edit', false ); ?>
					<button type="button" class="button button-primary save alignright"><?php esc_html_e( 'Update', 'divi-plus' ); ?></button>
					<span class="spinner"></span>
				<?php else : ?>
					<?php submit_button( esc_html__( 'Update', 'divi-plus' ), 'primary alignright dge_save_bulk_edit', 'dge_bulk_edit', false ); ?>
				<?php endif; ?>
				<?php wp_nonce_field( 'bulkeditnonce', 'dge_bulk_edit_nonce', false ); ?>
				<input type="hidden" name="post_view" value="<?php echo esc_attr( $m ); ?>" />
				<input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
				<?php if ( ! $bulk && ! post_type_supports( $screen->post_type, 'author' ) ) : ?>
					<input type="hidden" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
				<?php endif; ?>
				<br class="clear" />

				<div class="notice notice-error notice-alt inline hidden">
					<p class="error"></p>
				</div>
			</div>

			</td></tr>

			<?php
			$bulk++;
		endwhile;
		?>
		</tbody></table>
		</form>
		<?php
	}
}