<div class="container">
    <?php if (have_posts()):  ?>
        <div class="clearfix tainacan-masonry-view">
            <div class="grid-sizer col col-sm-6 col-md-4 col-lg-3"></div>
            <div class="gutter-sizer"></div>
        <!-- <div class="tainacan-masonry-container card-columns p-3"> -->
            <?php while (have_posts()): the_post(); ?>
                <div class="grid-item col col-sm-6 col-md-4 col-lg-3 mb-3">
                    <div class="card">
                        <?php 
                            if(has_post_thumbnail()): 
                                echo '<img src="'.get_the_post_thumbnail_url().'">';
                            endif;
                        ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>   
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="tainacan-masonry-view">
            Nenhum item encontrado
        </div>
    <?php endif; ?>
</div>