<?php
/**
 * Plugin Name: Tainacan DearFlip Integration
 * Description: Display Tainacan PDF documents and attachments with DearFlip, including the media gallery lightbox.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Text Domain: tainacan-dearflip
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-integration.php';

add_action( 'plugins_loaded', array( '\Tainacan\DearFlip\Integration', 'register' ) );
