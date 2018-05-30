<?php if (have_posts()):  ?>

    <div class="tainacan-grid-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col">
                <div class="tainacan-grid">
                    <a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail('medium'); ?>
					</a>
                    <p class="metadata-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</p>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-cards-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
