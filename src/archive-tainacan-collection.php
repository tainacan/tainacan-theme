<?php get_header(); ?>

<?php 
	$view_mode = esc_attr(get_query_var( 'tainacan_collections_viewmode' ));

	// Builds an array of possible taxonomies and terms to be used
	$collection_taxonomies = get_object_taxonomies( 'tainacan-collection', 'objects' );
	$collection_taxonomies_terms = [];
	$has_collection_taxonomies = !empty($collection_taxonomies);

	if ( $has_collection_taxonomies ) {
		$collection_taxonomies_terms = get_terms( get_object_taxonomies( 'tainacan-collection') );
	}
?>



<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main role="main" class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<div class="tainacan-title">
				<div class="tainacan-title-page">
					<ul class="list-inline mb-1 d-flex">
						<li class="list-inline-item  font-weight-bold title-page">
							<h1><?php echo get_the_archive_title(); ?></h1>
						</li>
						<li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a></li>
					</ul>
				</div>
			</div>
			<div class="form-inline mt-4 tainacan-collection-list--simple-search justify-content-between">
				
				<div class="dropdown dropdown-sorting">
					<button class="btn dropdown-toggle text-black" type="button" id="dropdownMenuSorting" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php _e( 'Sorting', 'tainacan-interface' ); ?>
					</button>
					<div class="dropdown-menu" aria-labelledby="dropdownMenuSorting">
						<a class="dropdown-item text-black <?php tainacan_active( get_query_var( 'orderby' ), 'date' ); ?>" href="<?php echo esc_url(add_query_arg( 'orderby', 'date' )); ?>"><?php _e( 'Creation date', 'tainacan-interface' ); ?></a>
						<a class="dropdown-item text-black <?php tainacan_active( get_query_var( 'orderby' ), 'title' ); ?>" href="<?php echo esc_url(add_query_arg( 'orderby', 'title' )); ?>"><?php _e( 'Title', 'tainacan-interface' ); ?></a>
					</div>
				</div>
					
				<a class="btn btn-white <?php tainacan_active( get_query_var( 'order' ), 'ASC' ); ?>" style="width: 2rem;" href="<?php echo esc_url(add_query_arg( 'order', 'ASC' )); ?>">
					<i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortascending"></i>
				</a>
				<a class="btn btn-white <?php tainacan_active( get_query_var( 'order' ), 'DESC' ); ?>" style="width: 2rem;" href="<?php echo esc_url(add_query_arg( 'order', 'DESC' )); ?>">
					<i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-sortdescending"></i>
				</a>
				
				<div class="dropdown margin-one-column dropdown-viewMode">
					<button class="btn dropdown-toggle text-black" type="button" id="dropdownMenuViewMode" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php 
							switch($view_mode) {
								case 'table':
									echo '<i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewtable text-oslo-gray"></i>';
									break;
								case 'grid':
									echo '<i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewminiature text-oslo-gray"></i>';
									break;
								case 'cards':
								 default:
									echo '<i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewcards text-oslo-gray"></i>';
									break;
							}
						?>
						<span class="d-none d-md-inline"><?php _e( 'View Mode', 'tainacan-interface' ); ?></span>
					</button>
					<div class="dropdown-menu" aria-labelledby="dropdownMenuViewMode">
						<a class="dropdown-item text-black <?php tainacan_active( $view_mode, 'cards' ); ?>" href="<?php echo esc_url(add_query_arg( 'tainacan_collections_viewmode', 'cards' )); ?>"><i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewcards text-oslo-gray"></i>&nbsp;<?php _e( 'Cards', 'tainacan-interface' ); ?></a>
						<a class="dropdown-item text-black <?php tainacan_active( $view_mode, 'grid' ); ?>" href="<?php echo esc_url(add_query_arg( 'tainacan_collections_viewmode', 'grid' )); ?>"><i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewminiature text-oslo-gray"></i>&nbsp;<?php _e( 'Thumbnails', 'tainacan-interface' ); ?></a>
						<a class="dropdown-item text-black <?php tainacan_active( $view_mode, 'table' ); ?>" href="<?php echo esc_url(add_query_arg( 'tainacan_collections_viewmode', 'table' )); ?>"><i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-viewtable text-oslo-gray"></i>&nbsp;<?php _e( 'Table', 'tainacan-interface' ); ?></a>
					</div>
				</div>

				<?php if ( $has_collection_taxonomies && !empty( $collection_taxonomies_terms ) ) : ?>
					<?php foreach($collection_taxonomies as $collection_taxonomy_slug => $collection_taxonomy) : ?>
						<div class="dropdown dropdown-sorting margin-one-column-right">
							<button class="btn dropdown-toggle text-black" type="button" id="dropdownMenuSorting" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo $collection_taxonomy->label; ?>
							</button>
							<div class="dropdown-menu" aria-labelledby="dropdownMenuSorting">
								<a
										class="dropdown-item text-black <?php echo ( isset($_GET[$collection_taxonomy_slug]) ) ? '' : 'active'; ?>"
										href="<?php echo esc_url(remove_query_arg( $collection_taxonomy_slug )); ?>">
									<?php _e('None', 'tainacan-interface'); ?>
								</a>
								<?php foreach($collection_taxonomies_terms as $collection_taxonomy_term) : ?>
									<?php if ( $collection_taxonomy_term->taxonomy == $collection_taxonomy_slug ) : ?>
										<a
												class="dropdown-item text-black <?php tainacan_active( get_query_var( $collection_taxonomy_slug ), $collection_taxonomy_term->slug ); ?>"
												href="<?php echo esc_url(add_query_arg( $collection_taxonomy_slug, $collection_taxonomy_term->slug )); ?>">
											<?php echo $collection_taxonomy_term->name; ?>
										</a>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				
				<form role="search" class="ml-auto" method="get" id="tainacan-collection-search">
					<input type="hidden" name="orderby" value="<?php echo esc_attr(get_query_var( 'orderby' )); ?>" />
					<input type="hidden" name="order" value="<?php echo esc_attr(get_query_var( 'order' )); ?>" />
					<input type="hidden" name="tainacan_collections_viewmode" value="<?php echo $view_mode; ?>" />
					<?php if ( $has_collection_taxonomies ) : ?>
						<?php foreach($collection_taxonomies as $collection_taxonomy_slug => $collection_taxonomy) : ?>
							<?php if ( isset( $_GET[$collection_taxonomy_slug] ) ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $collection_taxonomy_slug ); ?>" value="<?php echo esc_attr( $_GET[$collection_taxonomy_slug] ); ?>" />
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<div class="input-group">
						<input class="form-control rounded-0" type="search" name="s" value="<?php echo get_query_var( 's' ); ?>" placeholder="<?php esc_attr_e( 'Search collections', 'tainacan-interface' ); ?>" />
						<span class="input-group-append">
							<button class="btn border border-left-0 rounded-0 bg-white text-midnight-blue" type="submit">
								<i class="tainacan-icon tainacan-icon-20px tainacan-icon-search" style="line-height: inherit;"></i>
							</button>
						</span>
					</div>
				</form>
			</div>

			<?php get_template_part( 'template-parts/loop-tainacan-collection', $view_mode ); ?>
		</div>
	</div><!-- /.row -->
</main>
<?php get_footer(); ?>
