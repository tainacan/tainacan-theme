<?php if (have_posts()):  ?>

    <div class="tainacan-mosaic-container card-columns">

        <?php while (have_posts()): the_post(); ?>
        
                <div class="tainacan-mosaic">
                    <a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail('large'); ?>
					</a>	
                    <p class="metadata-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</p>
                </div>      

        <?php endwhile; ?>

    </div>

<?php else: ?>
    <div class="tainacan-cards-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
