<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
    <div class="input-group border">
    	<label for="tainacan-search" class="sr-only">Digite o que procura</label>
        <input class="form-control py-2 border-right-0 border-0" type="search" name="s" placeholder="<?php _e('Search', 'tainacan-theme'); ?>" id="tainacan-search">
        <span class="input-group-append">
            <button class="btn btn-outline-secondary border-0 border bg-white" type="submit">
                <i class="mdi mdi-magnify" style="line-height: inherit;"></i>
            </button>
        </span>
    </div>
</form>