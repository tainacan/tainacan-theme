<?php
/**
 * Textarea metadata: optional “read more” preview length on the public item page (Tainacan Interface theme).
 */
class Tainacan_Interface_Textarea_Readmore {

	/** @var string Post meta on the metadatum post (empty or positive integer = max chars before toggle). */
	public $meta_key = 'tainacan_interface_textarea_readmore_max_chars';

	/** @var bool */
	private static $readmore_assets_enqueued = false;

	public function __construct() {
		add_action( 'tainacan-register-admin-hooks', array( $this, 'register_hook' ) );
		add_action( 'tainacan-insert-tainacan-metadatum', array( $this, 'save_meta' ) );
		add_filter( 'tainacan-api-response-metadatum-meta', array( $this, 'add_meta_to_response' ), 10, 2 );
		add_filter( 'tainacan-item-metadata-get-value-as-html--type-textarea', array( $this, 'filter_textarea_value_as_html' ), 10, 2 );
		add_filter( 'tainacan-item-metadata-get-value-as-html--type-description', array( $this, 'filter_textarea_value_as_html' ), 10, 2 );
	}

	public function register_hook() {
		if ( function_exists( 'tainacan_register_admin_hook' ) ) {
			tainacan_register_admin_hook(
				'metadatum',
				array( $this, 'form' ),
				'end-left',
				array( 'metadata_type' => 'Tainacan\Metadata_Types\Textarea' )
			);
			tainacan_register_admin_hook(
				'metadatum',
				array( $this, 'form' ),
				'end-left',
				array( 'metadata_type' => 'Tainacan\Metadata_Types\Core_Description' )
			);
		}
	}

	public function form() {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="field tainacan-interface-textarea-readmore--section-header">
			<h4><?php esc_html_e( 'Tainacan Interface extra settings', 'tainacan-interface' ); ?></h4>
			<hr>
		</div>
		<div class="field tainacan-interface-textarea-readmore--max-chars">
			<label class="label" for="tainacan-interface-textarea-readmore-max-chars">
				<?php esc_html_e( 'Maximum characters to show before “Read more”', 'tainacan-interface' ); ?>
			</label>
			<div class="control">
				<input
					type="number"
					step="5"
					min="1"
					class="input"
					id="tainacan-interface-textarea-readmore-max-chars"
					name="<?php echo esc_attr( $this->meta_key ); ?>"
					placeholder="<?php esc_attr_e( 'No limit', 'tainacan-interface' ); ?>"
				/>
			</div>
			<p class="help">
				<?php esc_html_e( 'Leave empty to always show the full text on the item page. If set, long values show a short preview with a control to expand.', 'tainacan-interface' ); ?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	public function save_meta( $metadatum ) {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return;
		}

		$post = tainacan_get_api_postdata();

		if ( ! $metadatum->can_edit() ) {
			return;
		}

		if ( ! isset( $post->{ $this->meta_key } ) ) {
			return;
		}

		$raw_meta_value = $post->{ $this->meta_key };

		if ( $raw_meta_value === '' || $raw_meta_value === null ) {
			delete_post_meta( $metadatum->get_id(), $this->meta_key );
			return;
		}

		$sanitized_limit = absint( $raw_meta_value );
		if ( $sanitized_limit < 1 ) {
			delete_post_meta( $metadatum->get_id(), $this->meta_key );
			return;
		}

		update_post_meta( $metadatum->get_id(), $this->meta_key, $sanitized_limit );
	}

	public function add_meta_to_response( $extra_meta, $request ) {
		$extra_meta[] = $this->meta_key;
		return $extra_meta;
	}

	/**
	 * @param string                                   $html
	 * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata
	 * @return string
	 */
	public function filter_textarea_value_as_html( $html, $item_metadata ) {
		if ( ! is_single() ) {
			return $html;
		}

		$limit = $this->get_limit_for_metadatum( $item_metadata );
		if ( $limit < 1 ) {
			return $html;
		}

		if ( $item_metadata->is_multiple() ) {
			return $this->build_multivalue_html( $html, $item_metadata, $limit );
		}

		return $this->build_single_value_html( $html, $item_metadata, $limit );
	}

	/**
	 * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata
	 */
	private function get_limit_for_metadatum( $item_metadata ) {
		$metadatum_id = $item_metadata->get_metadatum()->get_id();
		$meta_value   = get_post_meta( $metadatum_id, $this->meta_key, true );
		if ( $meta_value === '' || $meta_value === null ) {
			return 0;
		}
		$max_characters = absint( $meta_value );
		return $max_characters > 0 ? $max_characters : 0;
	}

	/**
	 * @param string                                   $html
	 * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata
	 */
	private function build_single_value_html( $html, $item_metadata, $limit ) {
		$value = $item_metadata->get_value();
		if ( ! is_string( $value ) ) {
			return $html;
		}

		if ( ! $this->segment_exceeds_limit( $value, $limit ) ) {
			return $html;
		}

		$preview_html = $this->build_preview_html( $value, $limit );
		$metadatum_id = $item_metadata->get_metadatum()->get_id();

		return force_balance_tags( $this->wrap_readmore_widget( $preview_html, $html, $metadatum_id, 0 ) );
	}

	/**
	 * Rebuild multivalue output like core Textarea, wrapping long segments only.
	 *
	 * @param string                                   $html Unused; rebuilt from values.
	 * @param \Tainacan\Entities\Item_Metadata_Entity $item_metadata
	 */
	private function build_multivalue_html( $html, $item_metadata, $limit ) {
		$value = $item_metadata->get_value();
		if ( ! is_array( $value ) ) {
			return $html;
		}

		$html_formatting = $item_metadata->get_metadatum()->get_html_formatting();
		$metadatum_id    = $item_metadata->get_metadatum()->get_id();
		$segment_index   = 0;

		if ( $html_formatting === 'list' ) {
			$total = count( $value );
			if ( $total === 0 ) {
				return $html;
			}
			if ( $total === 1 ) {
				$segment_value = reset( $value );
				if ( ! $this->segment_exceeds_limit( $segment_value, $limit ) ) {
					return force_balance_tags( nl2br( self::make_clickable_links( $segment_value ) ) );
				}
				$full_html    = nl2br( self::make_clickable_links( $segment_value ) );
				$preview_html = $this->build_preview_html( $segment_value, $limit );

				return force_balance_tags( $this->wrap_readmore_widget( $preview_html, $full_html, $metadatum_id, $segment_index ) );
			}

			$list_html = '<ul>';
			foreach ( $value as $segment_value ) {
				if ( $this->segment_exceeds_limit( $segment_value, $limit ) ) {
					$full_html    = nl2br( self::make_clickable_links( $segment_value ) );
					$preview_html = $this->build_preview_html( $segment_value, $limit );
					$list_html   .= '<li>' . $this->wrap_readmore_widget( $preview_html, $full_html, $metadatum_id, $segment_index ) . '</li>';
				} else {
					$list_html .= '<li>' . nl2br( self::make_clickable_links( $segment_value ) ) . '</li>';
				}
				++$segment_index;
			}
			$list_html .= '</ul>';

			return force_balance_tags( $list_html );
		}

		$total   = count( $value );
		$count   = 0;
		$prefix  = $item_metadata->get_multivalue_prefix();
		$suffix  = $item_metadata->get_multivalue_suffix();
		$separator = $item_metadata->get_multivalue_separator();
		$output_html = '';

		foreach ( $value as $segment_value ) {
			if ( $this->segment_exceeds_limit( $segment_value, $limit ) ) {
				$full_html    = nl2br( self::make_clickable_links( $segment_value ) );
				$preview_html = $this->build_preview_html( $segment_value, $limit );
				$output_html .= $prefix . $this->wrap_readmore_widget( $preview_html, $full_html, $metadatum_id, $segment_index ) . $suffix;
			} else {
				$output_html .= $prefix . nl2br( self::make_clickable_links( $segment_value ) ) . $suffix;
			}
			++$segment_index;
			++$count;
			if ( $count < $total ) {
				$output_html .= $separator;
			}
		}

		return force_balance_tags( $output_html );
	}

	private function segment_exceeds_limit( $segment, $limit ) {
		if ( ! is_string( $segment ) ) {
			return false;
		}
		return mb_strlen( wp_strip_all_tags( $segment ) ) > $limit;
	}

	/**
	 * Same behavior as \Tainacan\Traits\Formatter_Text::make_clickable_links (kept here to match core Textarea output).
	 *
	 * @param string $text
	 * @return string
	 */
	private static function make_clickable_links( $text ) {
		$url  = '~((www\.|http:\/\/www\.|http:\/\/|https:\/\/www\.|https:\/\/|ftp:\/\/www\.|ftp:\/\/|ftps:\/\/www\.|ftps:\/\/)[^"<\s]+)(?![^<>]*>|[^"]*?<\/a)~i';
		$text = preg_replace( $url, '<a href="$0" target="_blank">$0</a>', $text );
		$text = str_replace( 'href="www.', 'href="http://www.', $text );

		return $text;
	}

	private function build_preview_html( $raw_segment, $limit ) {
		$excerpt = wp_html_excerpt( $raw_segment, $limit, '…' );

		return force_balance_tags( nl2br( self::make_clickable_links( $excerpt ) ) );
	}

	/**
	 * @param string $preview_html Already escaped HTML fragment.
	 * @param string $full_html    Already escaped HTML fragment.
	 */
	private function wrap_readmore_widget( $preview_html, $full_html, $metadatum_id, $index ) {
		$this->maybe_enqueue_readmore_assets();

		$region_id = 'tainacan-interface-tm-readmore-full-' . (int) $metadatum_id . '-' . (int) $index;

		ob_start();
		?>
		<div class="tainacan-interface-textarea-readmore">
			<div class="tainacan-interface-textarea-readmore__preview">
				<?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- balanced HTML from same pipeline as core. ?>
			</div>
			<div
				class="tainacan-interface-textarea-readmore__full"
				id="<?php echo esc_attr( $region_id ); ?>"
				hidden
			>
				<?php echo $full_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- from Tainacan core filter. ?>
			</div>
			<a
				href="<?php echo esc_url( '#' ); ?>"
				role="button"
				class="tainacan-interface-textarea-readmore__toggle tainacan-interface-more"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $region_id ); ?>"
			>
				[ <?php echo esc_html__( 'Show more', 'tainacan-interface' ); ?> ]
			</a>
		</div>
		<?php
		return ob_get_clean();
	}

	private function maybe_enqueue_readmore_assets() {
		if ( self::$readmore_assets_enqueued ) {
			return;
		}
		self::$readmore_assets_enqueued = true;

		wp_enqueue_script(
			'tainacan-interface-textarea-readmore',
			get_template_directory_uri() . '/assets/js/textarea-readmore.js',
			array( 'tainacan_tainacanTruncate' ),
			TAINACAN_INTERFACE_VERSION,
			true
		);

	}
}

new Tainacan_Interface_Textarea_Readmore();
