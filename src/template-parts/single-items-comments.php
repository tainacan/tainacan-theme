<?php
/**
 * Comments section of a single Tainacan item.
 *
 * @package tainacan-interface
 */

?>
<?php if ( comments_open() || get_comments_number() ) : ?>
	<div class="mt-3 tainacan-single-post">
		<div class="row">
			<!-- Container -->
			<div class="col mt-3 mx-auto">
				<?php comments_template(); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
