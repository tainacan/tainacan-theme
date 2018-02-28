<?php
if ( ! function_exists( 'tainacan_header_style' ) ) :
function tainacan_header_style() {
	$text_color = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	if ( $text_color == HEADER_TEXTCOLOR )
		return;

	// If we get this far, we have custom styles. Let's do this.
	?>
	<style type="text/css" id="tainacan-header-css">
		<?php
			// Has the text been hidden?
			if ( '212529' == $text_color ) :
		?>
			body{
				color = #212529;
			}
		<?php
			// If the user has set a custom color for the text use that
			else :
		?>
			body {
				color: #<?php echo $text_color; ?>;
			}
		<?php endif; ?>
	</style>
	<?php
}
endif; // tainacan_header_style