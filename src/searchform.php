<form role="search" method="post" name="tainacan-search-form" class="search-form" onsubmit="return onTainacanSearchSubmit()">
	<div class="input-group">
		<input class="form-control py-2" type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'tainacan-interface' ); ?>" id="tainacan-search">
		<span class="input-group-append d-none d-md-block">
			<button class="btn btn-outline-secondary bg-white" type="submit">
				<i class="tainacan-icon tainacan-icon-search" style="line-height: inherit;"></i>
			</button>
		</span>
	</div>
	<?php if ( defined ( 'TAINACAN_VERSION' ) ) : ?>
		<div class="search-controls">

			<label for="search-on-posts">
				<input
					type="radio" 
					value="posts" 
					name="archive"
					checked="checked"
					id="search-on-posts">
				<?php _e( 'Site posts', 'tainacan-interface' ); ?>
			</label>
	
			<label for="search-on-items">
				<input
					type="radio" 
					value="tainacan-items" 
					name="archive"
					formaction="/items"
					id="search-on-items">
					<?php _e( 'Archive items', 'tainacan-interface' ); ?>
			</label>

			<label for="search-on-collections">
				<input
					type="radio" 
					value="tainacan-collections" 
					name="archive"
					formaction="/collections"
					id="search-on-collections">
					<?php _e( 'Archive collections', 'tainacan-interface' ); ?>
			</label>
		</div>
	<?php endif; ?>
</form>
