<form role="search" method="post" name="tainacan-search-form" class="search-form" onsubmit="return onTainacanSearchSubmit()" style="margin-block-end: 0px;">
	<div class="input-group">
		<input class="form-control py-2" type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'tainacan-interface' ); ?>" id="tainacan-search">
		<span class="input-group-append d-none d-md-block">
			<button class="btn btn-outline-secondary bg-white" type="submit">
				<i class="tainacan-icon tainacan-icon-search" style="line-height: inherit;"></i>
			</button>
		</span>
	</div>
	<?php if ( defined ( 'TAINACAN_VERSION' ) ): ?>
		<div class="search-controls" <?php echo (get_theme_mod('tainacan_search_on_items', false) || get_theme_mod('tainacan_search_on_collections', false) || get_theme_mod('tainacan_search_on_posts', false) ) ? '' : 'style="display:none;visibility:hidden;"'; ?>>

			<label for="search-global">
				<input
					type="radio" 
					value="global" 
					name="archive"
					<?php if (get_theme_mod('tainacan_search_default_option', 'global') == 'global') echo 'checked="checked"'; ?>
					id="search-global">
				<?php echo esc_html( get_theme_mod('tainacan_search_global_label', __( 'Global', 'tainacan-interface' ) ) ); ?>
			</label>

			<?php if ( get_theme_mod('tainacan_search_on_posts', false) || get_theme_mod('tainacan_search_default_option', 'global') == 'posts' ) : ?>
				<label for="search-on-posts">
					<input
						type="radio" 
						value="posts" 
						name="archive"
						<?php if (get_theme_mod('tainacan_search_default_option', 'global') == 'posts') echo 'checked="checked"'; ?>
						id="search-on-posts">
					<?php echo esc_html( get_theme_mod('tainacan_search_on_posts_label', __( 'Posts', 'tainacan-interface' ) ) ); ?>
				</label>

			<?php endif;
				if ( get_theme_mod('tainacan_search_on_pages', false) || get_theme_mod('tainacan_search_default_option', 'global') == 'pages' ) : ?>
				
				<label for="search-on-pages">
					<input
						type="radio" 
						value="pages" 
						name="archive"
						<?php if (get_theme_mod('tainacan_search_default_option', 'global') == 'pages') echo 'checked="checked"'; ?>
						id="search-on-pages">
					<?php echo esc_html( get_theme_mod('tainacan_search_on_pages_label', __( 'Pages', 'tainacan-interface' ) ) ); ?>
				</label>
	
			<?php endif;
				if ( get_theme_mod('tainacan_search_on_items', false) || get_theme_mod('tainacan_search_default_option', 'global') == 'tainacan-items' ) : ?>
				
				<label for="search-on-items">
					<input
						type="radio" 
						value="tainacan-items" 
						name="archive"
						<?php if (get_theme_mod('tainacan_search_default_option', 'global') == 'tainacan-items') echo 'checked="checked"'; ?>
						id="search-on-items">
					<?php echo esc_html( get_theme_mod('tainacan_search_on_items_label', __( 'Items', 'tainacan-interface' ) ) ); ?>
				</label>

			<?php endif; 
				if ( get_theme_mod('tainacan_search_on_collections', false) || get_theme_mod('tainacan_search_default_option', 'global') == 'tainacan-collections' ) : ?>

				<label for="search-on-collections">
					<input
						type="radio" 
						value="tainacan-collections" 
						name="archive"
						<?php if (get_theme_mod('tainacan_search_default_option', 'global') == 'tainacan-collections') echo 'checked="checked"'; ?>
						id="search-on-collections">
					<?php echo esc_html( get_theme_mod('tainacan_search_on_collections_label', __( 'Collections', 'tainacan-interface' ) ) ); ?>
				</label>

			<?php endif; ?>
		</div>
	<?php endif; ?>
</form>
