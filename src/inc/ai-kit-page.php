<?php
/**
 * Appearance > Kit for AI: explains the kit, hands out the packages (kit with
 * the context of this site, starter child theme), shows the ready prompts for
 * each AI and checks the child themes. Also the notice that presents the kit
 * and its entry in Customize.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAINACAN_INTERFACE_KIT_PAGE', 'tainacan-interface-ai-kit' );

/**
 * Address of the page.
 *
 * @param array $args Query arguments.
 * @return string
 */
function tainacan_interface_kit_page_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => TAINACAN_INTERFACE_KIT_PAGE ), $args ), admin_url( 'themes.php' ) );
}

/**
 * The page under Appearance.
 */
function tainacan_interface_kit_menu() {
	$hook = add_theme_page(
		__( 'Kit for AI', 'tainacan-interface' ),
		__( 'Kit for AI', 'tainacan-interface' ),
		'edit_theme_options',
		TAINACAN_INTERFACE_KIT_PAGE,
		'tainacan_interface_kit_render'
	);
	if ( $hook ) {
		add_action( 'admin_print_styles-' . $hook, 'tainacan_interface_kit_assets' );
	}
}
add_action( 'admin_menu', 'tainacan_interface_kit_menu' );

/**
 * Style and script of the page, only on it.
 */
function tainacan_interface_kit_assets() {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'tainacan-interface-ai-kit', $uri . '/assets/css/ai-kit.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tainacan-interface-ai-kit', $uri . '/assets/js/ai-kit.js', array(), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tainacan-interface-ai-kit',
		'tt2Kit',
		array(
			'copied' => __( 'Copied', 'tainacan-interface' ),
			'copy'   => __( 'Copy', 'tainacan-interface' ),
		)
	);
}

/**
 * The AIs of the page: the prompt file and how to hand the kit to each one.
 *
 * @return array<string, array{title: string, file: string, steps: string[]}>
 */
function tainacan_interface_kit_assistants() {
	return array(
		'claude'  => array(
			'title' => 'Claude (claude.ai)',
			'file'  => 'prompts/claude.md',
			'steps' => array(
				__( 'Create a Project and add the files of the kit (or the whole ZIP) to its knowledge.', 'tainacan-interface' ),
				__( 'Paste the request below in a new chat of the Project and describe the site you want.', 'tainacan-interface' ),
				__( 'Ask for the child theme as a ZIP or as files, and for the pages as block markup.', 'tainacan-interface' ),
			),
		),
		'chatgpt' => array(
			'title' => 'ChatGPT',
			'file'  => 'prompts/chatgpt.md',
			'steps' => array(
				__( 'Create a Project (or a GPT) and upload the ZIP of the kit to its files.', 'tainacan-interface' ),
				__( 'Paste the request below in a chat of the Project and describe the site you want.', 'tainacan-interface' ),
				__( 'Ask it to give back the child theme as a ZIP to download.', 'tainacan-interface' ),
			),
		),
		'gemini'  => array(
			'title' => 'Gemini',
			'file'  => 'prompts/gemini.md',
			'steps' => array(
				__( 'Create a Gem with the request below as its instructions and the files of the kit as its knowledge (or attach the ZIP to a chat).', 'tainacan-interface' ),
				__( 'Describe the site you want; it answers with the files of the child theme and the block markup of the pages.', 'tainacan-interface' ),
			),
		),
		'agents'  => array(
			'title' => __( 'Coding agents (Claude Code, Codex, Gemini CLI, Cursor, Copilot)', 'tainacan-interface' ),
			'file'  => 'prompts/coding-agents.md',
			'steps' => array(
				__( 'Unzip the kit in an empty folder and open the agent there: it reads AGENTS.md (CLAUDE.md and GEMINI.md point to it).', 'tainacan-interface' ),
				__( 'With access to the site (WP-CLI), the agent checks its own work: wp tainacan-interface kit check and kit blocks.', 'tainacan-interface' ),
			),
		),
	);
}

/**
 * The request inside a prompt file: its first fenced block.
 *
 * @param string $file Path inside the kit.
 * @return string
 */
function tainacan_interface_kit_prompt( $file ) {
	$path = tainacan_interface_kit_path( $file );
	if ( ! is_readable( $path ) ) {
		return '';
	}
	$text = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a file of this theme, read to be shown.
	return preg_match( '/```text\R(.*?)\R```/s', $text, $match ) ? trim( $match[1] ) : '';
}

/**
 * The page.
 */
function tainacan_interface_kit_render() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the page only reads which child theme to check and which error to show; nothing is written.
	$check = isset( $_GET['check'] ) ? sanitize_key( wp_unslash( $_GET['check'] ) ) : '';
	$error = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	$children = tainacan_interface_kit_child_themes();
	$zip      = class_exists( 'ZipArchive' );
	?>
	<div class="wrap tt2kit">
		<h1 class="tt2kit-title"><?php esc_html_e( 'Kit for AI', 'tainacan-interface' ); ?></h1>
		<p class="tt2kit-lead"><?php esc_html_e( 'A package that lets Claude, ChatGPT, Gemini or any other AI change this theme, make child themes and build pages with blocks the way that keeps Tainacan and the block editor working: the guides, the reference of options and hooks, the starter child theme, ready requests and the context of this site.', 'tainacan-interface' ); ?></p>

		<?php if ( '' !== $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( (string) get_transient( 'tainacan_interface_kit_error_' . get_current_user_id() ) ); ?></p></div>
		<?php endif; ?>

		<ol class="tt2kit-steps">
			<li><span class="tt2kit-n">1</span><strong><?php esc_html_e( 'Download the kit', 'tainacan-interface' ); ?></strong><br><?php esc_html_e( 'It goes with the context of this site: collections, metadata, taxonomies, pages and the options you changed.', 'tainacan-interface' ); ?></li>
			<li><span class="tt2kit-n">2</span><strong><?php esc_html_e( 'Hand it to the AI', 'tainacan-interface' ); ?></strong><br><?php esc_html_e( 'With the request of the AI you use (below) and a description of the site you want.', 'tainacan-interface' ); ?></li>
			<li><span class="tt2kit-n">3</span><strong><?php esc_html_e( 'Install and check', 'tainacan-interface' ); ?></strong><br><?php esc_html_e( 'Send the child theme in Appearance > Themes, check it here, preview it and only then activate it.', 'tainacan-interface' ); ?></li>
		</ol>

		<div class="tt2kit-grid">
			<section class="tt2kit-card">
				<h2><?php esc_html_e( 'Kit with the context of this site', 'tainacan-interface' ); ?></h2>
				<p><?php esc_html_e( 'The guides, references, prompts and the starter child theme, plus site-context.json. The context has only public collections, metadata and taxonomies, pages and theme options: no users, e-mails or items. Read it before handing it to an outside service.', 'tainacan-interface' ); ?></p>
				<?php if ( $zip ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="tainacan_interface_kit_download">
						<?php wp_nonce_field( 'tainacan_interface_kit_download' ); ?>
						<button class="button button-primary button-hero" type="submit"><?php esc_html_e( 'Download the kit (.zip)', 'tainacan-interface' ); ?></button>
					</form>
				<?php else : ?>
					<p class="tt2kit-warn"><?php esc_html_e( 'This server cannot make ZIP files. The kit is in the ai-kit folder of the theme.', 'tainacan-interface' ); ?></p>
				<?php endif; ?>
				<p class="description">
					<?php
					/* translators: %s: path of the folder of the kit. */
					printf( esc_html__( 'The same files are in %s on the server.', 'tainacan-interface' ), '<code>' . esc_html( 'wp-content/themes/' . get_template() . '/' . TAINACAN_INTERFACE_KIT_DIR . '/' ) . '</code>' );
					?>
				</p>
				<details class="tt2kit-context">
					<summary><?php esc_html_e( 'See the context of this site', 'tainacan-interface' ); ?></summary>
					<pre><?php echo esc_html( (string) wp_json_encode( tainacan_interface_kit_context(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></pre>
				</details>
			</section>

			<section class="tt2kit-card">
				<h2><?php esc_html_e( 'Starter child theme', 'tainacan-interface' ); ?></h2>
				<p><?php esc_html_e( 'A child theme that already follows the standard: loads after Tainacan Interface, takes the colors and fonts from Customize, adds block patterns for a home page of a collection and leaves the Tainacan pages to the parent. The AI starts from it.', 'tainacan-interface' ); ?></p>
				<?php if ( $zip ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="tt2kit-form">
						<input type="hidden" name="action" value="tainacan_interface_kit_child">
						<?php wp_nonce_field( 'tainacan_interface_kit_child' ); ?>
						<label for="tt2kit-name"><?php esc_html_e( 'Name of the child theme', 'tainacan-interface' ); ?></label>
						<input type="text" id="tt2kit-name" name="name" class="regular-text" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<label for="tt2kit-slug"><?php esc_html_e( 'Folder (letters, numbers and hyphens; empty = made from the name)', 'tainacan-interface' ); ?></label>
						<input type="text" id="tt2kit-slug" name="slug" class="regular-text" pattern="[a-z0-9-]*">
						<p><button class="button" type="submit"><?php esc_html_e( 'Download the child theme (.zip)', 'tainacan-interface' ); ?></button></p>
					</form>
					<p class="description"><?php esc_html_e( 'Install it in Appearance > Themes > Add New > Upload Theme. On the command line: wp tainacan-interface kit child <folder>.', 'tainacan-interface' ); ?></p>
				<?php endif; ?>
			</section>
		</div>

		<section class="tt2kit-card tt2kit-wide">
			<h2><?php esc_html_e( 'Requests ready for each AI', 'tainacan-interface' ); ?></h2>
			<p><?php esc_html_e( 'Paste the request, then describe the site: audience, tone, colors, which collections go on the home page, the pages you need.', 'tainacan-interface' ); ?></p>
			<?php foreach ( tainacan_interface_kit_assistants() as $id => $assistant ) : ?>
				<?php $prompt = tainacan_interface_kit_prompt( $assistant['file'] ); ?>
				<details class="tt2kit-ai" <?php echo 'claude' === $id ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $assistant['title'] ); ?></summary>
					<ol>
						<?php foreach ( $assistant['steps'] as $step ) : ?>
							<li><?php echo esc_html( $step ); ?></li>
						<?php endforeach; ?>
					</ol>
					<?php if ( '' !== $prompt ) : ?>
						<div class="tt2kit-prompt">
							<textarea readonly rows="10" id="<?php echo esc_attr( 'tt2kit-prompt-' . $id ); ?>"><?php echo esc_textarea( $prompt ); ?></textarea>
							<button type="button" class="button" data-tt2kit-copy="<?php echo esc_attr( 'tt2kit-prompt-' . $id ); ?>"><?php esc_html_e( 'Copy', 'tainacan-interface' ); ?></button>
						</div>
					<?php endif; ?>
				</details>
			<?php endforeach; ?>
		</section>

		<section class="tt2kit-card tt2kit-wide" id="tt2kit-check">
			<h2><?php esc_html_e( 'Check a child theme', 'tainacan-interface' ); ?></h2>
			<?php if ( ! $children ) : ?>
				<p><?php esc_html_e( 'No child theme of Tainacan Interface is installed yet.', 'tainacan-interface' ); ?></p>
			<?php else : ?>
				<form method="get" action="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>" class="tt2kit-inline">
					<input type="hidden" name="page" value="<?php echo esc_attr( TAINACAN_INTERFACE_KIT_PAGE ); ?>">
					<label for="tt2kit-check-theme" class="screen-reader-text"><?php esc_html_e( 'Child theme', 'tainacan-interface' ); ?></label>
					<select id="tt2kit-check-theme" name="check">
						<?php foreach ( $children as $stylesheet => $name ) : ?>
							<option value="<?php echo esc_attr( $stylesheet ); ?>" <?php selected( $check, $stylesheet ); ?>><?php echo esc_html( $name . ' (' . $stylesheet . ')' ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="button button-primary" type="submit"><?php esc_html_e( 'Check', 'tainacan-interface' ); ?></button>
				</form>
				<?php if ( '' !== $check && isset( $children[ $check ] ) ) : ?>
					<ul class="tt2kit-results">
						<?php foreach ( tainacan_interface_kit_check_child( $check ) as $row ) : ?>
							<li class="<?php echo esc_attr( 'is-' . $row[0] ); ?>"><?php echo esc_html( $row[1] ); ?></li>
						<?php endforeach; ?>
					</ul>
					<p>
						<a class="button" href="<?php echo esc_url( add_query_arg( 'theme', $check, admin_url( 'customize.php' ) ) ); ?>"><?php esc_html_e( 'Preview it in Customize', 'tainacan-interface' ); ?></a>
					</p>
				<?php endif; ?>
			<?php endif; ?>
			<p class="description"><?php esc_html_e( 'What is checked: the header and the parent, files loaded from outside servers, copies of templates, the patterns (blocks that exist here, queries of collections that exist here) and theme.json. The code itself still needs a review: the kit lists what to look at.', 'tainacan-interface' ); ?></p>
		</section>

		<section class="tt2kit-card tt2kit-wide">
			<h2><?php esc_html_e( 'Blocks that an AI can write', 'tainacan-interface' ); ?></h2>
			<ul class="tt2kit-list">
				<li><?php esc_html_e( 'Search the collection (tainacan-interface/search): a search box rendered by the server, for all items or one collection.', 'tainacan-interface' ); ?></li>
				<li><?php esc_html_e( 'Query loop of Tainacan items, collections or taxonomies, with the theme styles Carousel and Cards.', 'tainacan-interface' ); ?></li>
				<li><?php esc_html_e( 'Group styles Panel and Band on top; Cover style Brand veil; the colors of the theme in the palette (they follow Customize).', 'tainacan-interface' ); ?></li>
				<li><?php esc_html_e( 'Tainacan blocks whose HTML only the editor makes (carousels, lists, faceted search): the theme rebuilds them when the page opens in the editor; then save the page.', 'tainacan-interface' ); ?></li>
			</ul>
		</section>
	</div>
	<?php
}

/**
 * Sends a ZIP made by $build and deletes it.
 *
 * @param string   $filename Name offered to the browser.
 * @param callable $build    Receives the path of the temporary file; returns true or WP_Error.
 */
function tainacan_interface_kit_send_zip( $filename, $build ) {
	$tmp    = wp_tempnam( $filename );
	$result = call_user_func( $build, $tmp );
	if ( is_wp_error( $result ) ) {
		wp_delete_file( $tmp );
		set_transient( 'tainacan_interface_kit_error_' . get_current_user_id(), $result->get_error_message(), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( tainacan_interface_kit_page_url( array( 'error' => 'zip' ) ) );
		exit;
	}
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
	header( 'Content-Length: ' . (string) filesize( $tmp ) );
	readfile( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streams a temporary ZIP this request made; WP_Filesystem has no streaming read.
	wp_delete_file( $tmp );
	exit;
}

/**
 * Download of the kit with the context of this site.
 */
function tainacan_interface_kit_download() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_kit_download' );
	tainacan_interface_kit_send_zip( 'tainacan-interface-ai-kit-' . sanitize_title( get_bloginfo( 'name' ) ) . '.zip', 'tainacan_interface_kit_package' );
}
add_action( 'admin_post_tainacan_interface_kit_download', 'tainacan_interface_kit_download' );

/**
 * Download of the starter child theme.
 */
function tainacan_interface_kit_child_download() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_kit_child' );
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$slug  = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
	$names = tainacan_interface_kit_child_names( $name, $slug );
	if ( is_wp_error( $names ) ) {
		set_transient( 'tainacan_interface_kit_error_' . get_current_user_id(), $names->get_error_message(), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( tainacan_interface_kit_page_url( array( 'error' => 'name' ) ) );
		exit;
	}
	tainacan_interface_kit_send_zip(
		$names[1] . '.zip',
		static function ( $tmp ) use ( $names ) {
			return tainacan_interface_kit_child_package( $tmp, $names[0], $names[1] );
		}
	);
}
add_action( 'admin_post_tainacan_interface_kit_child', 'tainacan_interface_kit_child_download' );

/**
 * Presents the kit once, on the Dashboard and on Themes, until dismissed.
 */
function tainacan_interface_kit_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) || tainacan_interface_state( 'kit_notice_seen' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes' ), true ) ) {
		return;
	}
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_kit_dismiss' ), 'tainacan_interface_kit_dismiss' );
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'New in Tainacan Interface: Kit for AI.', 'tainacan-interface' ); ?></strong>
		<?php esc_html_e( 'Guides, references, ready requests and a starter child theme so Claude, ChatGPT, Gemini or another AI can make child themes and pages with blocks for this site without breaking Tainacan.', 'tainacan-interface' ); ?></p>
		<p><a class="button button-primary" href="<?php echo esc_url( tainacan_interface_kit_page_url() ); ?>"><?php esc_html_e( 'Open the Kit for AI', 'tainacan-interface' ); ?></a> <a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan-interface' ); ?></a></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_kit_notice' );

/**
 * Dismisses the notice (the page stays in Appearance).
 */
function tainacan_interface_kit_dismiss() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_kit_dismiss' );
	tainacan_interface_state_set( 'kit_notice_seen', TAINACAN_INTERFACE_VERSION );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_tainacan_interface_kit_dismiss', 'tainacan_interface_kit_dismiss' );

/**
 * The entry in Customize: a section at the end that explains and opens the page.
 *
 * @param WP_Customize_Manager $wp_customize Customize.
 */
function tainacan_interface_kit_customize( $wp_customize ) {
	$wp_customize->add_section(
		'tainacan_interface_ai_kit',
		array(
			'title'       => __( 'Kit for AI', 'tainacan-interface' ),
			'description' => __( 'Guides, references, ready requests and a starter child theme so an AI can make child themes and pages for this site the right way.', 'tainacan-interface' ),
			'priority'    => 310,
			'capability'  => 'edit_theme_options',
		)
	);
	$wp_customize->add_setting(
		'tainacan_interface_kit_link',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'sanitize_callback' => '__return_empty_string',
		)
	);
	require_once __DIR__ . '/class-tainacan-interface-kit-link-control.php';
	$wp_customize->add_control(
		new Tainacan_Interface_Kit_Link_Control(
			$wp_customize,
			'tainacan_interface_kit_link',
			array(
				'section'  => 'tainacan_interface_ai_kit',
				'settings' => 'tainacan_interface_kit_link',
			)
		)
	);
}
add_action( 'customize_register', 'tainacan_interface_kit_customize', 30 );
