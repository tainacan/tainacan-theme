        <tr class="tainacan-list-collection">
            <th scope="row" class="collection-checkbox"><input type="checkbox" name="collection" id="collection"></th>
            <th class="collection-miniature">
                <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-table') ?>" class="img-fluid rounded-circle" alt="">
                <?php endif; ?>
            </th>
            <th class="collection-title"><a href="<?php the_permalink(); ?>" class=""><?php the_title(); ?></a></th>
            <th class="collection-create-by text-oslo-gray"><?php echo 'Create by '; the_author_posts_link(); ?></th>
            <th></th>
        </tr>