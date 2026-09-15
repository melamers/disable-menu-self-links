<?php
/**
 * Standalone test for Disable_Menu_Self_Links::compute_has_children_map().
 *
 * Loads the plugin file outside WordPress by stubbing the two hook
 * functions it calls at load time, so this can run with plain `php`
 * and no WordPress bootstrap.
 *
 * Run with: php tests/test-has-children-map.php
 */

define( 'ABSPATH', __DIR__ . '/' );

function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function plugin_dir_path( ...$args ) { return __DIR__ . '/'; }
function plugin_dir_url( ...$args ) { return ''; }

require __DIR__ . '/../disable-menu-self-links.php';

function dmsl_test_make_item( $id, $parent ) {
	$item = new stdClass();
	$item->ID = $id;
	$item->menu_item_parent = $parent;
	return $item;
}

// A menu with two parent items (11 and 14), each with children, and one
// childless top-level item (10). Mirrors a typical "Resources" dropdown.
$items = array(
	dmsl_test_make_item( 10, '0' ),
	dmsl_test_make_item( 11, '0' ),
	dmsl_test_make_item( 12, '11' ),
	dmsl_test_make_item( 13, '11' ),
	dmsl_test_make_item( 14, '0' ),
	dmsl_test_make_item( 15, '14' ),
);

$map = Disable_Menu_Self_Links::compute_has_children_map( $items );

$assertions = array(
	'childless top-level item (10) is not marked as a parent' => empty( $map[10] ),
	'parent item (11) is marked as a parent'                  => ! empty( $map[11] ),
	'child item (12) is not marked as a parent'                => empty( $map[12] ),
	'child item (13) is not marked as a parent'                => empty( $map[13] ),
	'second parent item (14) is marked as a parent'            => ! empty( $map[14] ),
	'child item (15) is not marked as a parent'                => empty( $map[15] ),
	'an ID that never appears has no entry'                    => empty( $map[999] ),
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
