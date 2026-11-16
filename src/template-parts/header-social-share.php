<?php
/**
 * Share buttons of the collection header.
 *
 * @package tainacan-interface
 */

?>
<div class="collection-header--share">
	<div class="btn trigger">
		<span class="tainacan-icon tainacan-icon-share"></span>
	</div>
	<div class="icons">
		<?php tainacan_interface_the_share_links( tainacan_interface_current_url(), wp_get_document_title(), 'icons' ); ?>
	</div>
</div>
