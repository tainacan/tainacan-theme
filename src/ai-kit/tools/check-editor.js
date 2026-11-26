#!/usr/bin/env node
/**
 * Kit for AI of Tainacan Interface: opens pages in the block editor with a
 * headless browser and lists the blocks the editor finds invalid or unknown,
 * the notices it shows (the theme rebuilding Tainacan blocks, for example) and
 * whether the page would need saving.
 *
 * Optional tool, for coding agents and developers. It needs Node 18+ and
 * Puppeteer in the folder where it runs:
 *
 *   npm install puppeteer
 *   WP_USER=editor WP_PASS='…' node check-editor.js https://example.org 123 [456 …] [--save]
 *
 * The user and the password come from the environment, never from the command
 * line or a file, and are only typed into wp-login.php of the given site. Use
 * a test site, or a user with only the Editor role. --save saves each page
 * after the check (useful after the theme rebuilt Tainacan blocks).
 */
'use strict';

let puppeteer;
try {
	puppeteer = require( 'puppeteer' );
} catch ( e ) {
	console.error( 'Puppeteer is missing: npm install puppeteer' );
	process.exit( 2 );
}

const args = process.argv.slice( 2 );
const save = args.includes( '--save' );
const [ site, ...ids ] = args.filter( ( a ) => a !== '--save' );
const user = process.env.WP_USER;
const pass = process.env.WP_PASS;

if ( ! site || ! ids.length || ! user || ! pass ) {
	console.error( 'Usage: WP_USER=… WP_PASS=… node check-editor.js <site-url> <post-id> [<post-id> …] [--save]' );
	process.exit( 2 );
}

const base = site.replace( /\/+$/, '' );

( async () => {
	const browser = await puppeteer.launch( { headless: 'new', args: process.getuid && process.getuid() === 0 ? [ '--no-sandbox' ] : [] } );
	const page = await browser.newPage();
	await page.setViewport( { width: 1440, height: 1000 } );

	await page.goto( base + '/wp-login.php', { waitUntil: 'networkidle2', timeout: 120000 } );
	await page.type( '#user_login', user );
	await page.type( '#user_pass', pass );
	await Promise.all( [ page.waitForNavigation( { waitUntil: 'networkidle2', timeout: 120000 } ), page.click( '#wp-submit' ) ] );
	if ( page.url().includes( 'wp-login.php' ) ) {
		console.error( 'Login failed.' );
		await browser.close();
		process.exit( 1 );
	}

	let failed = false;
	for ( const id of ids ) {
		await page.goto( base + '/wp-admin/post.php?post=' + encodeURIComponent( id ) + '&action=edit', { waitUntil: 'networkidle2', timeout: 120000 } );
		await page.waitForFunction( () => window.wp && wp.data && wp.data.select( 'core/block-editor' ) && wp.data.select( 'core/block-editor' ).getBlocks().length > 0, { timeout: 90000 } );
		// The theme rebuilds the Tainacan blocks a moment after the editor loads.
		await new Promise( ( r ) => setTimeout( r, 6000 ) );
		const report = await page.evaluate( () => {
			const problems = [];
			let count = 0;
			const walk = ( blocks, trail ) => blocks.forEach( ( b ) => {
				count++;
				if ( ! wp.blocks.getBlockType( b.name ) ) {
					problems.push( trail + b.name + ': not registered' );
				} else if ( b.isValid === false ) {
					problems.push( trail + b.name + ': invalid' );
				}
				walk( b.innerBlocks || [], trail + b.name + ' > ' );
			} );
			walk( wp.data.select( 'core/block-editor' ).getBlocks(), '' );
			return {
				blocks: count,
				problems,
				notices: wp.data.select( 'core/notices' ).getNotices().map( ( n ) => n.status + ': ' + n.content ),
				needs_saving: wp.data.select( 'core/editor' ).isEditedPostDirty(),
			};
		} );
		if ( save && report.needs_saving ) {
			await page.evaluate( () => wp.data.dispatch( 'core/editor' ).savePost() );
			await page.waitForFunction( () => ! wp.data.select( 'core/editor' ).isSavingPost(), { timeout: 60000 } );
			report.saved = true;
		}
		failed = failed || report.problems.length > 0;
		// tnc-check-ignore TNC-B04: the report is what this command-line tool prints.
		console.log( JSON.stringify( { post: id, ...report }, null, 1 ) );
	}

	await browser.close();
	process.exit( failed ? 1 : 0 );
} )().catch( ( e ) => {
	console.error( e.message );
	process.exit( 1 );
} );
