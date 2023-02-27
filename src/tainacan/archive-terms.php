<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">

            <div class="tainacan-title">
				<div class="tainacan-title-page">
					<ul class="list-inline mb-1 d-flex">
						<li class="list-inline-item  font-weight-bold title-page">
							<h1><?php the_title(); ?></h1>
						</li>
						<li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a></li>
					</ul>
				</div>
			</div>

            <?php if ( have_posts() ) :

                do_action( 'tainacan-interface-single-item-top' );

                while ( have_posts() ) :
                    
                    the_post();
                    
                    ?>
                    <div class="form-inline mt-4 tainacan-collection-list--simple-search justify-content-between">
                            
                        <div class="dropdown dropdown-sorting">
                            <button class="btn dropdown-toggle text-black" type="button" id="dropdownMenuSorting" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php _e( 'Sorting', 'tainacan-interface' ); ?>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuSorting">
                                <a class="dropdown-item text-black <?php tainacan_active( get_query_var( 'orderby' ), 'name' ); ?>" href="<?php echo add_query_arg( 'orderby', 'name' ); ?>"><?php _e( 'Name', 'tainacan-interface' ); ?></a>
                                <a class="dropdown-item text-black <?php tainacan_active( get_query_var( 'orderby' ), 'count' ); ?>" href="<?php echo add_query_arg( 'orderby', 'count' ); ?>"><?php _e( 'Amount of items', 'tainacan-interface' ); ?></a>
                            </div>
                        </div>
                            
                        <a class="btn btn-white <?php tainacan_active( get_query_var( 'order' ), 'ASC' ); ?>" style="width: 2rem;" href="<?php echo add_query_arg( 'order', 'ASC' ); ?>">
                            <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortascending"></i>
                        </a>
                        <a class="btn btn-white <?php tainacan_active( get_query_var( 'order' ), 'DESC' ); ?>" style="width: 2rem;" href="<?php echo add_query_arg( 'order', 'DESC' ); ?>">
                            <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortdescending"></i>
                        </a>
                        
                        <form role="search" class="ml-auto" method="get" id="tainacan-collection-search">
                            <input type="hidden" name="orderby" value="<?php echo esc_attr(get_query_var( 'orderby' )); ?>" />
                            <input type="hidden" name="order" value="<?php echo esc_attr(get_query_var( 'order' )); ?>" />
                            <input type="hidden" name="tainacan_collections_viewmode" value="<?php echo $view_mode; ?>" />
                            <div class="input-group">
                                <input class="form-control rounded-0" type="search" name="search" value="<?php echo get_query_var( 'search' ); ?>" placeholder="<?php esc_attr_e( 'Search terms', 'tainacan-interface' ); ?>" />
                                <span class="input-group-append">
                                    <button class="btn border border-left-0 rounded-0 bg-white text-midnight-blue" type="submit">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-search" style="line-height: inherit;"></i>
                                    </button>
                                </span>
                            </div>
                        </form>

                    </div>
                    <?php

                    $taxonomy_terms_list = tainacan_get_single_taxonomy_content($post, array(
                        'before_terms_list_container' => '<div class="tainacan-list-post px-md-0 mt-5 tainacan-taxonomy-terms-list-container">',
                        'before_terms_list' => '<ul class="tainacan-list-collection--container-card justify-content-center tainacan-taxonomy-terms-list">',
                        'after_terms_list' => '</ul>',
		                'before_term' => '<li class="tainacan-term-single tainacan-list-collection--card" id="term-id-$id">',
                        'after_term' => '</li>',
                        'before_term_thumbnail' => '<figure class="term-thumbnail tainacan-list-collection--card-img rounded-0 align-self-center mr-3">',
		                'after_term_thumbnail' => '</figure>',
                        'before_term_description' => '<div class="term-description media-body text-oslo-gray"><p>',
                        'thumbnails_size' => 'tainacan-medium',
		                'hide_term_thumbnail_placeholder' => false,
		                'hide_term_description' => false,
                        'trim_description_words' => 20
                    ));
                    echo $taxonomy_terms_list['content'];

                    $current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
                    $current_total_terms = $taxonomy_terms_list['total_terms'];
                    $current_first_term_index = max(($current_args['termspaged'] - 1) * $current_args['perpage'] + 1, 1);
                    $current_last_term_index = min($current_args['termspaged'] * $current_args['perpage'], $current_total_terms);

                    ?>
                    <div class="d-flex margin-pagination justify-content-between border-top pt-2">
                        <div class="col-sm-3 d-none d-lg-block pl-0 view-items">
                            <?php //translators: Example - Viewing results: 1 to 12 of 345 ?>
                            <?php printf( __('Viewing results: %1$d to %2$d of %3$d', 'tainacan-interface'), $current_first_term_index, $current_last_term_index, $current_total_terms ); ?>
                        </div>
                        <div class="col-sm-5 pr-md-0 justify-content-md-end">
                        <?php
                            tainacan_the_taxonomies_pagination(
                                $taxonomy_terms_list['total_terms'],
                                array(
                                    'mid_size'  => 2,
                                    'prev_text' => sprintf(
                                        '%s',
                                        '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>'
                                    ),
                                    'next_text' => sprintf(
                                        ' %s',
                                        '<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'
                                    ),
                                    'screen_reader_text' => ' '
                                )
                            );
                        ?>
                        </div>
                    </div>
                    <?php

                endwhile;
                do_action( 'tainacan-interface-single-item-bottom' ); ?>

            <?php else : ?>
                <?php _e( 'Nothing found', 'tainacan-interface' ); ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>