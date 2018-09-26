<div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else : ?>class="page-header header-filter clear-filter align-items-center" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
	<div class="container-fluid max-large p-0 ph-title-description">
		<div class="bg-white-title title-header <?php if ( is_singular() || is_archive() || is_search() || is_home() ) { echo 'singular-title'; }?>">
			<h1 class="mb-0 text-truncate">
				<?php
				if ( is_home() ) {
					bloginfo( 'title' );
				} elseif ( is_singular() ) {
					bloginfo( 'title' );
				} elseif ( is_search() ) {
					_e( 'Search Results', 'tainacan-interface' );
				} elseif ( is_tag() || is_category() || is_tax() ) {
					single_term_title();
				} elseif ( is_archive() ) {
					if ( have_posts() ) {
						if ( is_day() ) :
							printf( __( 'Daily Archives: %s', 'tainacan-interface' ), get_the_date() );
						elseif ( is_month() ) :
							printf( __( 'Monthly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ) ) );
						elseif ( is_year() ) :
							printf( __( 'Yearly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'Y', 'yearly archives date format', 'tainacan-interface' ) ) );
						elseif ( is_author() ) :
							echo get_the_author();
						else :
							echo get_the_archive_title();
						endif;
					}
				} ?>
			</h1>
		</div>
	</div>
</div>
