<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="input-group border">
		<input class="form-control py-2 border-0" type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'tainacan-interface' ); ?>" id="tainacan-search">
		<span class="input-group-append d-none d-md-block">
			<button class="btn btn-outline-secondary border-0 border bg-white" type="submit">
				<i class="mdi mdi-magnify" style="line-height: inherit;"></i>
			</button>
		</span>
	</div>
</form>
