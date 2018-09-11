<?php
    echo '<style>
        .t-bg-collection .t-collection--info .t-collection--info-description-text.dotmore .toggle::before {
            content: "[ ' . __("Show more", "tainacan-theme") . ' ]";
        }
        .t-bg-collection .t-collection--info .t-collection--info-description-text.dotmore.full-story .toggle::before {
            content: "[ ' . __("Show less", "tainacan-theme") . ' ]";
        }
        nav.menu-belowheader #menubelowHeader ul.dropdown-menu {
            min-width: 10rem !important;
        }
    </style>';

$term = tainacan_get_term();
$taxonomy = get_taxonomy( $term->taxonomy );

?>

<div <?php if ( get_header_image() ) : ?>class="page-header header-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter page-collection" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
    <div class="container-fluid px-0 t-bg-collection" style="<!-- z-index: 0; -->">
        <div class="collection-header position-relative max-large" style="">
            
            <?php get_template_part('template-parts/header-social-share'); ?>
			
        </div>
        <div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
            <div class="col-4 col-md-3 px-0 t-collection--col-3">
                
            </div>
            <div class="col-8 col-md-9 pl-0 t-collection--col-9 mt-md-3" style="z-index: 2">
                <h2 class="t-collection--info-title text-white">
                    <?php echo $taxonomy->labels->name; ?>:
					<?php tainacan_the_term_name(); ?>
                </h2>
                <?php $tainacan_term_description = tainacan_get_the_term_description(); ?>
                <?php if ( !empty($tainacan_term_description) ): ?>
                    <div class="text-white t-collection--info-description-text dotmore">
                        <?php
                            echo $tainacan_term_description; 
                        ?>
                        <a class="toggle" href="#"></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>