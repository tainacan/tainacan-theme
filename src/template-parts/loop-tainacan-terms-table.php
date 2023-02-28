<?php if ( have_posts() ) : ?>
    
    <?php while ( have_posts() ) {
        the_post();
        $taxonomy_terms_list = tainacan_get_single_taxonomy_content($post, array(
            'before_terms_list_container' => '<div class="mt-5 tainacan-list-post table-responsive tainacan-taxonomy-terms-list-container">',
            'after_terms_list_container' => '</div>',
            'before_terms_list' => '<table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">' . __( 'Name', 'tainacan-interface' ) . '</th>
                                                <th scope="col">' . __( 'Description', 'tainacan-interface' ) . '</th>
                                                <th scope="col">' . __( 'Children', 'tainacan-interface' ) . '</th>
                                                <th scope="col">' . __( 'Items', 'tainacan-interface' ) . '</th>
                                            </tr>
                                        </thead>
                                        <tbody>',
            'after_terms_list' => '</tbody></table>',
            'before_term' => '<tr class="tainacan-list-collection" id="term-id-$id">',
            'after_term' => '</tr>',
            'before_term_thumbnail' => '<td class="collection-miniature">',
            'after_term_thumbnail' => '</td>',
            'before_term_name' => '<td class="collection-title text-oslo-gray">',
            'after_term_name' => '</td>',
            'before_term_description' => '<td class="collection-description term-description text-oslo-gray">',
            'after_term_description' => '</td>',
            'before_term_children_link' => '<td>',
            'after_term_children_link' => '</td>',
            'before_term_items_link' => '<td>',
            'after_term_items_link' => '</td>',
            'hide_term_empty_name' => false,
            'hide_term_empty_description' => false,
            'hide_term_empty_children_link' => false,
            'hide_term_empty_items_link' => false,
            'thumbnails_size' => 'tainacan-medium',
            'hide_term_thumbnail_placeholder' => false,
            'hide_term_description' => false,
            'trim_description_words' => 32
        ));
        echo $taxonomy_terms_list['content'];

        $current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
        $current_total_terms = $taxonomy_terms_list['total_terms'];
        $current_first_term_index = max(($current_args['termspaged'] - 1) * $current_args['perpage'] + 1, 1);
        $current_last_term_index = min($current_args['termspaged'] * $current_args['perpage'], $current_total_terms);

    } ?>


    <?php tainacan_pagination_terms($current_total_terms); ?>

<?php else : ?>
    <?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>