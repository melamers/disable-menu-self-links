<?php
/**
 * Standalone test for Disable_Menu_Self_Links::unique_anchor_id().
 *
 * Reproduces the duplicate-ID defect: one menu item rendered in more than
 * one nav menu on the same page emitted the same anchor ID every time, so
 * a page with two nav widgets carried two copies of id="dmsl-resources".
 *
 * Loads the plugin file outside WordPress by stubbing the hook functions
 * it calls at load time, so this runs with plain `php` and no WordPress
 * bootstrap.
 *
 * Run with: php tests/test-unique-anchor-id.php
 */

define( 'ABSPATH', __DIR__ . '/' );

function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function plugin_dir_path( ...$args ) { return __DIR__ . '/'; }
function plugin_dir_url( ...$args ) { return ''; }

require __DIR__ . '/../disable-menu-self-links.php';

$plugin = Disable_Menu_Self_Links::get_instance();

if ( ! method_exists( $plugin, 'unique_anchor_id' ) ) {
	echo "FAIL: Disable_Menu_Self_Links::unique_anchor_id() does not exist, so anchor IDs are not de-duplicated at all.\n";
	echo "\n1 assertion(s) failed.\n";
	exit( 1 );
}

// The reported case: the "Resources" item renders in four nav widgets on
// https://www.marcellabremer.com/resources/ and produced id="dmsl-resources"
// four times.
$resources = array(
	$plugin->unique_anchor_id( 'dmsl-resources' ),
	$plugin->unique_anchor_id( 'dmsl-resources' ),
	$plugin->unique_anchor_id( 'dmsl-resources' ),
	$plugin->unique_anchor_id( 'dmsl-resources' ),
);

// A second disabled item on the same page must keep its own sequence.
$about = array(
	$plugin->unique_anchor_id( 'dmsl-about' ),
	$plugin->unique_anchor_id( 'dmsl-about' ),
);

// A real page slug of 'resources-2' generates the base ID 'dmsl-resources-2',
// which the suffixing above has already handed out. It must not collide.
$collider = $plugin->unique_anchor_id( 'dmsl-resources-2' );

$assertions = array(
	'first render keeps the bare ID, so existing deep links still resolve' => 'dmsl-resources' === $resources[0],
	'second render of the same item gets a distinct ID'                    => 'dmsl-resources-2' === $resources[1],
	'third render of the same item gets a distinct ID'                     => 'dmsl-resources-3' === $resources[2],
	'fourth render of the same item gets a distinct ID'                    => 'dmsl-resources-4' === $resources[3],
	'all four rendered IDs are unique'                                     => 4 === count( array_unique( $resources ) ),
	'a different item keeps its own bare ID'                               => 'dmsl-about' === $about[0],
	'a different item suffixes independently'                              => 'dmsl-about-2' === $about[1],
	'a real slug colliding with a generated suffix is still made unique'   => ! in_array( $collider, $resources, true ),
	'no ID is issued twice across every call'                              => 7 === count( array_unique( array_merge( $resources, $about, array( $collider ) ) ) ),
);

$failures = 0;

foreach ( $assertions as $label => $passed ) {
	if ( $passed ) {
		echo "PASS: $label\n";
	} else {
		echo "FAIL: $label\n";
		$failures++;
	}
}

if ( $failures > 0 ) {
	echo "\n$failures assertion(s) failed.\n";
	exit( 1 );
}

echo "\nAll assertions passed.\n";
exit( 0 );
