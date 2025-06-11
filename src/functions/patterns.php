<?php 

/*
* Register Tainacan Heading Section Pattern
*/
function tainacan_block_patterns_init() {
	global $wp_version;

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ((is_plugin_active('gutenberg/gutenberg.php') || $wp_version >= '5') && function_exists('register_block_pattern_category')) {
		register_block_pattern_category(
			'tainacan-interface',
			array( 'label' => _x( 'Tainacan Interface', 'Block pattern category', 'tainacan-interface' ) ) );
	}

	if ((is_plugin_active('gutenberg/gutenberg.php') || $wp_version >= '5') && function_exists('register_block_pattern')) {
		register_block_pattern(
			'tainacan-interface/tainacan-heading-section-pattern',
			array(
				'title'       => __( 'Tainacan heading section', 'tainacan-interface' ),
				'description' => _x( 'A left-aligned heading section containing a light sub-heading and an underline below the title.', 'Block pattern description', 'tainacan-interface' ),
				'content'     => $wp_version >= '5.9'
                    ?
                        '<!-- wp:group {"style":{"spacing":{"blockGap":"6px"}},"className":"tainacan-heading-section-pattern-pattern"} -->
                        <div class="wp-block-group tainacan-heading-section-pattern-pattern"><!-- wp:heading {"style":{"typography":{"fontSize":24}},"textColor":"default"} -->
                        <h2 class="has-default-color has-text-color" style="font-size:24px">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2>
                        <!-- /wp:heading -->
                        
                        <!-- wp:paragraph {"style":{"color":{"text":"#838386"}}} -->
                        <p class="has-text-color" style="color:#838386">' . esc_html__( 'Section optional description...', 'tainacan-interface' ) . '</p>
                        <!-- /wp:paragraph -->
                
                        <!-- wp:separator {"opacity":"css","backgroundColor":"default"} -->
                        <hr class="wp-block-separator has-text-color has-default-color has-css-opacity has-default-background-color has-background-color"/>
                        <!-- /wp:separator -->
                        
                        <!-- wp:spacer {"height":"32px"} -->
                        <div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
                        <!-- /wp:spacer --></div>
                        <!-- /wp:group -->'
                    :
                        '<!-- wp:group {"className":"tainacan-heading-section-pattern-pattern"} --><div class="wp-block-group tainacan-heading-section-pattern-pattern"><div class="wp-block-group__inner-container"><!-- wp:heading {"textColor":"default","style":{"typography":{"fontSize":24}}} --><h2 class="has-default-color has-text-color" style="font-size:24px">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"style":{"color":{"text":"#838386"}}} --><p class="has-text-color" style="color:#898d8f">' . esc_html__( 'Section optional description...', 'tainacan-interface' ) . '</p><!-- /wp:paragraph --><!-- wp:separator {"color":"default"} --><hr class="wp-block-separator has-text-color has-background has-default-background-color has-default-color"/><!-- /wp:separator --><!-- wp:spacer {"height":32} --><div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer --></div></div><!-- /wp:group -->',
				'categories'  => array('tainacan-interface')
			)
		);
		register_block_pattern(
			'tainacan-interface/tainacan-highlight-section-pattern',
			array(
				'title'       => __( 'Tainacan highlight section', 'tainacan-interface' ),
				'description' => _x( 'A hero section with a background color and columns for image and text.', 'Block pattern description', 'tainacan-interface' ),
				'content'     => $wp_version >= '5.9'
                ?
                    '<!-- wp:cover {"overlayColor":"default","align":"full","style":{"spacing":{"padding":{"top":"28px","bottom":"28px"}}}} -->
                    <div class="wp-block-cover alignfull" style="padding-top:28px;padding-bottom:28px"><span aria-hidden="true" class="wp-block-cover__background has-default-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"inherit":true}} -->
                    <div class="wp-block-group"><!-- wp:heading {"style":{"color":{"text":"#ffffff"}}} -->
                    <h2 class="has-text-color" style="color:#ffffff">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2>
					<!-- /wp:heading -->

                    <!-- wp:columns {"style":{"color":{"text":"#ffffff"},"spacing":{"blockGap":"4.2%"}}} -->
                    <div class="wp-block-columns has-text-color" style="color:#ffffff"><!-- wp:column {"width":"33.33%"} -->
                    <div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:image {"id":152274,"sizeSlug":"large"} -->
                    <figure class="wp-block-image size-large"><img src="' . esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png' . '" alt="" class="wp-image-152274"/></figure>
					<!-- /wp:image --></div>
                    <!-- /wp:column -->

                    <!-- wp:column {"width":"66.66%"} -->
                    <div class="wp-block-column" style="flex-basis:66.66%">
                    <!-- wp:paragraph {"fontSize":"normal"} -->
                    <p class="has-normal-font-size">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:paragraph {"align":"right","style":{"color":{"text":"#ffffff"}}} -->
						<p class="has-text-align-right has-text-color" style="color:#ffffff"><a href="/"> ' .esc_html__( 'View more...', 'tainacan-interface' ) . '</a></p>
					<!-- /wp:paragraph -->
                    </div>
                    <!-- /wp:column --></div>
                    <!-- /wp:columns --></div>
                    <!-- /wp:group --></div></div>
                    <!-- /wp:cover -->'
                :
                '<!-- wp:cover {"overlayColor":"default","align":"full"} -->
				<div class="wp-block-cover alignfull has-default-background-color has-background-dim">

					<div class="wp-block-cover__inner-container">
						<!-- wp:spacer {"height": 24} -->
						<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						<!-- wp:heading {"style":{"color":{"text":"#ffffff"}}} -->
						<h2 class="has-text-color" style="color:#ffffff">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2>
						<!-- /wp:heading -->
						<!-- wp:spacer {"height": 16} -->
						<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						<!-- wp:columns {"style":{"color":{"text":"#ffffff"}}} -->
						<div class="wp-block-columns has-text-color" style="color:#ffffff">
							<!-- wp:column {"width":33.33} -->
							<div class="wp-block-column" style="flex-basis:33.33%">
								<!-- wp:image {"id":152274,"sizeSlug":"large"} -->
								<figure class="wp-block-image size-large"><img src="' . esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png' . '" alt="" class="wp-image-152274"/></figure>
								<!-- /wp:image -->
							</div>
							<!-- /wp:column -->

							<!-- wp:column {"width":66.66} -->
							<div class="wp-block-column" style="flex-basis:66.66%">
								<!-- wp:paragraph {"fontSize":"normal"} -->
								<p class="has-normal-font-size">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
								<!-- /wp:paragraph -->

								<!-- wp:paragraph {"align":"right","style":{"color":{"text":"#ffffff"}}} -->
								<p class="has-text-align-right has-text-color" style="color:#ffffff"><a href="/"> ' .esc_html__( 'View more...', 'tainacan-interface' ) . '</a></p>
								<!-- /wp:paragraph -->
							</div>
							<!-- /wp:column -->
						</div>
						<!-- /wp:columns -->
						<!-- wp:spacer {"height": 32} -->
						<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
					</div>
				</div>
				<!-- /wp:cover -->',
				'categories'  => array('tainacan-interface')
			)
		);

		register_block_pattern(
			'tainacan-interface/tainacan-highlight-section-pattern-alt',
			array(
				'title'       => __( 'Tainacan highlight section (alt)', 'tainacan-interface' ),
				'description' => _x( 'Another hero section with a background color and columns for image headings and text.', 'Block pattern description', 'tainacan-interface' ),
				'content'     => $wp_version >= '5.9'
                    ?  
                    '<!-- wp:cover {"customOverlayColor":"#062a57","align":"full"} -->
                    <div class="wp-block-cover alignfull"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim" style="background-color:#062a57"></span><div class="wp-block-cover__inner-container">
                    
                    <!-- wp:group {"layout":{"inherit":true}} -->
                    <div class="wp-block-group"><!-- wp:columns {"style":{"color":{"text":"#ffffff"},"spacing":{"blockGap":"4.2%"}}} -->
                    <div class="wp-block-columns has-text-color" style="color:#ffffff"><!-- wp:column {"width":"33.33%","style":{"spacing":{"blockGap":"4.2%"}}} -->
                    <div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:image {"id":152274,"sizeSlug":"large"} -->
                    <figure class="wp-block-image size-large"><img src="' . esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png' . '" alt="" class="wp-image-152274"/></figure><!-- /wp:image --></div>
                    <!-- /wp:column -->
                    
                    <!-- wp:column {"width":"33.33%"} -->
                    <div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:heading {"style":{"color":{"text":"#ffffff"}}} -->
                    <h2 class="has-text-color" style="color:#ffffff">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2>
                    <!-- /wp:heading -->
                    
                    <!-- wp:paragraph {"fontSize":"normal"} -->
                    <p class="has-normal-font-size">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    <!-- /wp:paragraph --></div>
                    <!-- /wp:column -->
                    
                    <!-- wp:column {"width":"33.33%"} -->
                    <div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:paragraph {"fontSize":"normal"} -->
                    <p class="has-normal-font-size">Sed at orci ex. Nulla pulvinar, lorem vel eleifend maximus, nunc elit porta felis, id placerat orci dui sit amet ligula. Mauris fermentum dui quam, eget blandit ligula vulputate sit amet. Integer aliquet eget urna ac tempor. Nunc viverra faucibus magna ac aliquet. Integer ullamcorper lacinia elit quis gravida. Duis placerat pulvinar arcu nec faucibus. Sed sit amet enim iaculis, facilisis est non, venenatis nibh.</p>
                    <!-- /wp:paragraph -->
                    
                    <!-- wp:paragraph {"align":"right","textColor":"white"} -->
                    <p class="has-text-align-right has-text-color" style="color:#ffffff"><a href="/"> ' .esc_html__( 'View more...', 'tainacan-interface' ) . '</a></p>
                    <!-- /wp:paragraph --></div>
                    <!-- /wp:column --></div>
                    <!-- /wp:columns --></div>
                    <!-- /wp:group --></div></div>
                    <!-- /wp:cover -->'
                :
                    '<!-- wp:cover {"customOverlayColor":"#062a57","align":"full"} -->
                    <div class="wp-block-cover alignfull has-background-dim" style="background-color:#062a57">
                        <div class="wp-block-cover__inner-container">
                            <!-- wp:spacer {"height": 16} -->
                            <div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
                            <!-- /wp:spacer -->
                            <!-- wp:columns {"style":{"color":{"text":"#ffffff"}}} -->
                            <div class="wp-block-columns has-text-color" style="color:#ffffff">
                                <!-- wp:column {"width":33.33} -->
                                <div class="wp-block-column" style="flex-basis:33.33%">
                                    <!-- wp:image {"id":152274,"sizeSlug":"large"} -->
                                    <figure class="wp-block-image size-large"><img src="' . esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png' . '" alt="" class="wp-image-152274"/></figure>
                                    <!-- /wp:image -->
                                </div>
                                <!-- /wp:column -->

                                <!-- wp:column {"width":33.33} -->
                                <div class="wp-block-column" style="flex-basis:33.33%">
                                    <!-- wp:heading {"style":{"color":{"text":"#ffffff"}}} -->
                                    <h2 class="has-text-color" style="color:#ffffff">' . esc_html__( 'Section title', 'tainacan-interface' ) . '</h2>
                                    <!-- /wp:heading -->

                                    <!-- wp:paragraph {"fontSize":"normal"} -->
                                    <p class="has-normal-font-size">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                    <!-- /wp:paragraph -->
                                </div>
                                <!-- /wp:column -->

                                <!-- wp:column {"width":33.33} -->
                                <div class="wp-block-column" style="flex-basis:33.33%">
                                    <!-- wp:paragraph {"fontSize":"normal"} -->
                                    <p class="has-normal-font-size">Sed at orci ex. Nulla pulvinar, lorem vel eleifend maximus, nunc elit porta felis, id placerat orci dui sit amet ligula. Mauris fermentum dui quam, eget blandit ligula vulputate sit amet. Integer aliquet eget urna ac tempor. Nunc viverra faucibus magna ac aliquet. Integer ullamcorper lacinia elit quis gravida. Duis placerat pulvinar arcu nec faucibus. Sed sit amet enim iaculis, facilisis est non, venenatis nibh.</p>
                                    <!-- /wp:paragraph -->

                                    <!-- wp:paragraph {"align":"right","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white"} -->
                                    <p class="has-text-align-right has-white-color has-text-color has-link-color">
                                        <p class="has-text-align-right has-text-color" style="color:#ffffff">
                                            <a href="/">' .esc_html__( 'View more...', 'tainacan-interface' ) . '</a>
                                        </p>
                                    </p>
                                    <!-- /wp:paragraph -->
                                </div>
                                <!-- /wp:column -->
                            </div>
                            <!-- /wp:columns -->
                        </div>
                    </div>
                    <!-- /wp:cover -->',
				'categories'  => array('tainacan-interface')
			)
		);

        /* Default header content */
        register_block_pattern(
			'tainacan-interface/tainacan-header',
			array(
				'title'       => __( 'Tainacan header section', 'tainacan-interface' ),
				'description' => _x( 'A block-based header that can be used as alterative to the default template.', 'Block pattern description', 'tainacan-interface' ),
				'content'     => '<!-- wp:group {"tagName":"header","style":{"border":{"bottom":{"color":"#ededed","style":"solid","width":"1px"}},"spacing":{"padding":{"top":"0.6rem","bottom":"0.6rem","right":"4.2vw","left":"4.2vw"}}},"layout":{"type":"constrained","contentSize":"1400px"}} -->
                    <header class="wp-block-group" style="border-bottom-color:#ededed;border-bottom-style:solid;border-bottom-width:1px;padding-top:0.6rem;padding-right:4.2vw;padding-bottom:0.6rem;padding-left:4.2vw"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
                    <div class="wp-block-group"><!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"#1d1d1d"}}},"typography":{"fontSize":"1.5rem","fontStyle":"normal","fontWeight":"400"}}} /-->
                    
                    <!-- wp:navigation {"ref":8332,"icon":"menu","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"},"style":{"layout":{"selfStretch":"fill"},"spacing":{"blockGap":"var:preset|spacing|60"}},"fontSize":"small"} /--></div>
                    <!-- /wp:group --></header>
                    <!-- /wp:group -->',
				'categories'  => array('tainacan-interface'),
                'inserter' => false
			)
		);

        /* Default footer content */
        register_block_pattern(
			'tainacan-interface/tainacan-footer',
			array(
				'title'       => __( 'Tainacan footer section', 'tainacan-interface' ),
				'description' => _x( 'A block-based footer that can be used as alterative to the default template.', 'Block pattern description', 'tainacan-interface' ),
				'content'     => '<!-- wp:group {"tagName":"footer","style":{"elements":{"link":{"color":{"text":"var:preset|color|lightgray"},":hover":{"color":{"text":"var:preset|color|white"}}}},"spacing":{"padding":{"top":"3rem","right":"4.2vw","bottom":"3rem","left":"4.2vw"}},"color":{"background":"#2c2d2d"}},"textColor":"white","layout":{"type":"constrained","contentSize":"1400px"}} -->
                    <footer class="wp-block-group has-white-color has-text-color has-background has-link-color" style="background-color:#2c2d2d;padding-top:3rem;padding-right:4.2vw;padding-bottom:3rem;padding-left:4.2vw"><!-- wp:group {"style":{"spacing":{"blockGap":"4.2%","padding":{"right":"4.2%","left":"4.2%"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
                    <div class="wp-block-group" style="padding-right:4.2%;padding-left:4.2%"><!-- wp:group {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"layout":{"type":"default"}} -->
                    <div class="wp-block-group has-link-color"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"1.13rem"}},"textColor":"white"} -->
                    <h6 class="wp-block-heading has-white-color has-text-color" style="font-size:1.13rem"><strong>' .esc_html__( 'Archive', 'tainacan-interface' ) . '</strong></h6>
                    <!-- /wp:heading -->
                    
                    <!-- wp:archives {"style":{"spacing":{"margin":{"right":"0","left":"0","bottom":"0rem","top":"1.5rem"},"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}}} /--></div>
                    <!-- /wp:group -->
                    
                    <!-- wp:group {"layout":{"type":"default"}} -->
                    <div class="wp-block-group"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"1.13rem"}},"textColor":"white"} -->
                    <h6 class="wp-block-heading has-white-color has-text-color" style="font-size:1.13rem"><strong>' .esc_html__( 'Latest posts', 'tainacan-interface' ) . '</strong></h6>
                    <!-- /wp:heading -->
                    
                    <!-- wp:latest-posts {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} /--></div>
                    <!-- /wp:group -->
                    
                    <!-- wp:group {"layout":{"type":"default"}} -->
                    <div class="wp-block-group"><!-- wp:heading {"level":6,"style":{"typography":{"fontSize":"1.13rem"}},"textColor":"white"} -->
                    <h6 class="wp-block-heading has-white-color has-text-color" style="font-size:1.13rem"><strong>' .esc_html__( 'Categories', 'tainacan-interface' ) . '</strong></h6>
                    <!-- /wp:heading -->
                    
                    <!-- wp:categories {"style":{"spacing":{"margin":{"top":"1.5rem"},"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}}} /--></div>
                    <!-- /wp:group --></div>
                    <!-- /wp:group -->
                    
                    <!-- wp:separator {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"default","className":"is-style-wide"} -->
                    <hr class="wp-block-separator has-text-color has-default-color has-alpha-channel-opacity has-default-background-color has-background is-style-wide" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--60)"/>
                    <!-- /wp:separator -->
                    
                    <!-- wp:group {"style":{"spacing":{"blockGap":"4.2%"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
                    <div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"padding":{"right":"0","left":"4%"}}},"layout":{"type":"default"}} -->
                    <div class="wp-block-group" style="padding-right:0;padding-left:4%"><!-- wp:site-title {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"var:preset|spacing|20","right":"0","bottom":"var:preset|spacing|20","left":"0"}}},"fontSize":"regular"} /--></div>
                    <!-- /wp:group -->
                    
                    <!-- wp:group {"style":{"spacing":{"padding":{"right":"4%"}}},"layout":{"type":"default"}} -->
                    <div class="wp-block-group" style="padding-right:4%"><!-- wp:image {"id":11051,"width":120,"sizeSlug":"thumbnail","linkDestination":"none"} -->
                    <figure class="wp-block-image size-thumbnail is-resized"><img src="' . esc_url( get_template_directory_uri() ) . '/assets/images/logo-footer.svg' . '" alt="" class="wp-image-11051" width="120"/></figure>
                    <!-- /wp:image --></div>
                    <!-- /wp:group --></div>
                    <!-- /wp:group --></footer>
                    <!-- /wp:group -->',
				'categories'  => array('tainacan-interface'),
                'inserter' => false
			)
		);
	}
}
add_action( 'init', 'tainacan_block_patterns_init' );