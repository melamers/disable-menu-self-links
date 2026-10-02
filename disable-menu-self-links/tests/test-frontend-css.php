<?php
/**
 * Standalone test for assets/frontend.css.
 *
 * Reproduces the contrast defect: the stylesheet dimmed the current page's
 * menu link with opacity 0.85, which pulls a theme's link colour below the
 * WCAG 4.5:1 minimum (kikkergroep.nl: leaf-800 dropped to about 4.2:1) and
 * overrides the dimming example in the README, whose selector is less
 * specific. The current item must keep the theme's colour; it must still
 * be inert (pointer-events none).
 *
 * Run with: php tests/test-frontend-css.php
 */

$css = file_get_contents( __DIR__ . '/../assets/frontend.css' );
if ( false === $css ) {
	echo "FAIL: assets/frontend.css could not be read.\n";
	exit( 1 );
}

// Strip comments, then split into selector { declarations } rules.
$css = preg_replace( '#/\*.*?\*/#s', '', $css );
preg_match_all( '/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER );

$failures      = 0;
$inert_current = false;

foreach ( $rules as $rule ) {
	$selector     = trim( $rule[1] );
	$declarations = $rule[2];
	$targets_current = ( false !== strpos( $selector, 'current-menu-item' ) || false !== strpos( $selector, 'current_page_item' ) );
	if ( ! $targets_current ) {
		continue;
	}
	if ( preg_match( '/(^|;)\s*opacity\s*:/i', $declarations ) ) {
		echo "FAIL: a current-item rule sets opacity: {$selector}\n";
		$failures++;
	}
	if ( preg_match( '/pointer-events\s*:\s*none/i', $declarations ) ) {
		$inert_current = true;
	}
}

if ( ! $inert_current ) {
	echo "FAIL: no current-item rule sets pointer-events: none, so the link would be clickable.\n";
	$failures++;
}

if ( $failures ) {
	echo "\n{$failures} assertion(s) failed.\n";
	exit( 1 );
}
echo "OK: current-item rules keep the theme's colour and stay inert.\n";
