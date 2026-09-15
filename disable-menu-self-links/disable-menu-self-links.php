<?php
/**
 * Plugin Name:       Disable Menu Self Links
 * Plugin URI:        https://example.com/disable-menu-self-links
 * Description:       Optionally disable self-referencing links in WordPress menus using self-anchors with multiple fallback protections.
 * Version:           1.2.2
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Marcel Lamers
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       disable-menu-self-links
 * Domain Path:       /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'DMSL_VERSION', '1.2.2' );
define( 'DMSL_PATH', plugin_dir_path( __FILE__ ) );
define( 'DMSL_URL', plugin_dir_url( __FILE__ ) );
define( 'DMSL_DEBUG', false ); // Set to true for debugging

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Disable_Menu_Self_Links {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @var Disable_Menu_Self_Links
	 */
	private static $instance = null;

	/**
	 * Anchor IDs already issued during this request.
	 *
	 * Keyed by ID, so the same menu item rendered in more than one nav menu
	 * on a page does not emit duplicate id attributes.
	 *
	 * @since 1.2.2
	 * @var array
	 */
	private $issued_anchor_ids = array();

	/**
	 * Get plugin instance.
	 *
	 * @since 1.0.0
	 * @return Disable_Menu_Self_Links
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Debug logging helper.
	 *
	 * @since 1.0.0
	 * @param string $message Message to log.
	 * @param mixed  $data    Optional data to log.
	 */
	private function debug_log( $message, $data = null ) {
		if ( ! defined( 'DMSL_DEBUG' ) || ! DMSL_DEBUG ) {
			return;
		}

		$log_message = '[DMSL] ' . $message;
		
		if ( null !== $data ) {
			$log_message .= ' | Data: ' . print_r( $data, true );
		}

		error_log( $log_message );
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		// Add custom field to menu items
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_custom_nav_fields' ) );
		
		// Add custom field to menu item settings
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ), 10, 2 );
		
		// Save custom field value
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_menu_item_custom_fields' ), 10, 2 );
		
		// Filter menu items on display
		add_filter( 'wp_nav_menu_objects', array( $this, 'modify_menu_items' ), 10, 2 );
		
		// Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// Enqueue admin styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		
		// Enqueue frontend styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		
		// Debug mode admin notice
		if ( defined( 'DMSL_DEBUG' ) && DMSL_DEBUG ) {
			add_action( 'admin_notices', array( $this, 'debug_mode_notice' ) );
		}
		
		// Add HTML comment for CSS debugging
		add_action( 'wp_head', array( $this, 'add_debug_comment' ), 999 );
	}

	/**
	 * Show admin notice when debug mode is active.
	 *
	 * @since 1.0.0
	 */
	public function debug_mode_notice() {
		$log_file = WP_CONTENT_DIR . '/debug.log';
		?>
		<div class="notice notice-warning">
			<p>
				<strong>Disable Menu Self Links:</strong> Debug mode is active. 
				Check <code><?php echo esc_html( $log_file ); ?></code> for logs.
				Set <code>DMSL_DEBUG</code> to <code>false</code> in production.
			</p>
		</div>
		<?php
	}

	/**
	 * Add HTML comment for CSS debugging.
	 *
	 * @since 1.0.0
	 */
	public function add_debug_comment() {
		if ( ! defined( 'DMSL_DEBUG' ) || ! DMSL_DEBUG ) {
			return;
		}
		
		echo "\n<!-- Disable Menu Self Links Debug Info -->\n";
		echo "<!-- Plugin active: Yes -->\n";
		echo "<!-- CSS file: " . esc_url( DMSL_URL . 'assets/frontend.css' ) . " -->\n";
		echo "<!-- Check browser DevTools > Network to verify CSS loads -->\n";
		echo "<!-- Inspect .menu-item span elements to see applied styles -->\n";
		echo "<!-- End DMSL Debug -->\n\n";
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'disable-menu-self-links',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only on nav-menus.php page
		if ( 'nav-menus.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'dmsl-admin-style',
			DMSL_URL . 'assets/admin.css',
			array(),
			DMSL_VERSION
		);
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'dmsl-frontend-style',
			DMSL_URL . 'assets/frontend.css',
			array(),
			DMSL_VERSION
		);

		wp_enqueue_script(
			'dmsl-frontend-script',
			DMSL_URL . 'assets/frontend.js',
			array(),
			DMSL_VERSION,
			true
		);
	}

	/**
	 * Add custom fields to nav menu item object.
	 *
	 * @since 1.0.0
	 * @param object $menu_item Menu item object.
	 * @return object Modified menu item object.
	 */
	public function add_custom_nav_fields( $menu_item ) {
		$menu_item->enable_self_link = get_post_meta( $menu_item->ID, '_dmsl_enable_self_link', true );
		
		// Default to enabled (link works normally)
		if ( '' === $menu_item->enable_self_link ) {
			$menu_item->enable_self_link = '1';
		}
		
		return $menu_item;
	}

	/**
	 * Output custom field in menu item settings.
	 *
	 * @since 1.0.0
	 * @param int    $item_id Menu item ID.
	 * @param object $item    Menu item object.
	 */
	public function menu_item_custom_fields( $item_id, $item ) {
		$enable_self_link = get_post_meta( $item_id, '_dmsl_enable_self_link', true );
		
		// Default to enabled
		if ( '' === $enable_self_link ) {
			$enable_self_link = '1';
		}
		?>
		<p class="field-dmsl-enable-self-link description description-wide">
			<label for="dmsl-enable-self-link-<?php echo esc_attr( $item_id ); ?>">
				<input 
					type="checkbox" 
					id="dmsl-enable-self-link-<?php echo esc_attr( $item_id ); ?>" 
					name="dmsl_enable_self_link[<?php echo esc_attr( $item_id ); ?>]" 
					value="1" 
					<?php checked( $enable_self_link, '0' ); ?>
				>
				<?php esc_html_e( 'Disable Link to Self', 'disable-menu-self-links' ); ?>
			</label>
			<span class="description">
				<?php esc_html_e( 'When checked, the link will not be clickable when viewing this page.', 'disable-menu-self-links' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Save custom field value.
	 *
	 * @since 1.0.0
	 * @param int $menu_id         Menu ID.
	 * @param int $menu_item_db_id Menu item ID.
	 */
	public function save_menu_item_custom_fields( $menu_id, $menu_item_db_id ) {
		// Reversed logic: checkbox checked = disable link (store 0)
		if ( isset( $_POST['dmsl_enable_self_link'][ $menu_item_db_id ] ) ) {
			update_post_meta( $menu_item_db_id, '_dmsl_enable_self_link', '0' );
			$this->debug_log( 'Menu item disabled', array( 'item_id' => $menu_item_db_id, 'value' => '0' ) );
		} else {
			update_post_meta( $menu_item_db_id, '_dmsl_enable_self_link', '1' );
			$this->debug_log( 'Menu item enabled', array( 'item_id' => $menu_item_db_id, 'value' => '1' ) );
		}
	}

	/**
	 * Modify menu items to disable self links when appropriate.
	 *
	 * @since 1.0.0
	 * @param array $items Menu items.
	 * @param array $args  Menu arguments.
	 * @return array Modified menu items.
	 */
	public function modify_menu_items( $items, $args ) {
		$has_children_map = self::compute_has_children_map( $items );

		foreach ( $items as $item ) {
			// Mark whether this item has children, so the frontend can keep
			// hover-triggered submenus working even when its own link is disabled.
			$item->dmsl_has_children = isset( $has_children_map[ $item->ID ] );

			// Check if self link should be disabled
			if ( '0' === $item->enable_self_link || 0 === $item->enable_self_link ) {
				// Check if this is the current page
				if ( $this->is_current_page( $item ) ) {
					// Mark this item for link removal
					$item->dmsl_remove_link = true;
				}
			}
		}

		// Now filter the actual HTML output
		add_filter( 'walker_nav_menu_start_el', array( $this, 'modify_menu_item_html' ), 10, 4 );

		return $items;
	}

	/**
	 * Build a map of menu item IDs that have at least one child item.
	 *
	 * Determines this from the parent/child relationships already present
	 * in $items, instead of relying on the theme's walker to emit a
	 * 'menu-item-has-children' class, which WordPress core does not
	 * guarantee on every walker's frontend output.
	 *
	 * @since 1.2.1
	 * @param array $items Menu items.
	 * @return array Map of parent item ID => true for items that have children.
	 */
	public static function compute_has_children_map( $items ) {
		$has_children = array();

		foreach ( $items as $item ) {
			$parent_id = isset( $item->menu_item_parent ) ? (string) $item->menu_item_parent : '';

			if ( '' !== $parent_id && '0' !== $parent_id ) {
				$has_children[ $parent_id ] = true;
			}
		}

		return $has_children;
	}

	/**
	 * Check if menu item points to current page.
	 *
	 * @since 1.0.0
	 * @param object $item Menu item object.
	 * @return bool True if current page.
	 */
	private function is_current_page( $item ) {
		// WordPress already determines this - check the classes
		if ( is_array( $item->classes ) ) {
			return in_array( 'current-menu-item', $item->classes, true ) ||
			       in_array( 'current_page_item', $item->classes, true );
		}
		return false;
	}

	/**
	 * Modify menu item HTML to use self-referencing anchor.
	 *
	 * @since 1.2.0
	 * @param string $item_output Menu item HTML.
	 * @param object $item        Menu item object.
	 * @param int    $depth       Depth of menu item.
	 * @param object $args        Menu arguments.
	 * @return string Modified HTML.
	 */
	public function modify_menu_item_html( $item_output, $item, $depth, $args ) {
		// Debug: Log all menu items being processed
		$this->debug_log( 'Processing menu item', array(
			'title'            => $item->title,
			'id'               => $item->ID,
			'url'              => $item->url,
			'enable_self_link' => $item->enable_self_link,
			'should_remove'    => isset( $item->dmsl_remove_link ) ? $item->dmsl_remove_link : 'not set',
			'classes'          => $item->classes,
		) );

		// Check if we should modify the link
		if ( isset( $item->dmsl_remove_link ) && true === $item->dmsl_remove_link ) {
			// Check if the <a> tag already has an id attribute
			$existing_id = null;
			if ( preg_match( '/id=["\']([^"\']+)["\']/', $item_output, $id_match ) ) {
				$existing_id = $id_match[1];
			}
			
			// Determine which ID to use
			if ( $existing_id ) {
				// Use existing ID
				$anchor_id = $existing_id;
				$id_action = 'preserved';
			} else {
				// Generate new ID from page slug, de-duplicated across every
				// nav menu rendered on this page.
				$slug = $this->get_page_slug( $item );
				$anchor_id = $this->unique_anchor_id( sanitize_html_class( 'dmsl-' . $slug ) );
				$id_action = 'generated';
			}
			
			$this->debug_log( 'Modifying link to self-reference', array(
				'title'     => $item->title,
				'id'        => $anchor_id,
				'id_action' => $id_action,
			) );

			// Flag parent items on the <a> itself, so the frontend CSS can keep
			// hover-triggered submenus working without depending on the theme's
			// walker to emit a 'menu-item-has-children' class.
			$data_attr = ! empty( $item->dmsl_has_children ) ? ' data-dmsl-has-children="1"' : '';

			// Replace href with self-referencing anchor
			if ( $existing_id ) {
				// ID already exists, just replace href
				$modified_output = preg_replace(
					'/(<a\s[^>]*)href=["\']([^"\']*)["\']([^>]*>)/i',
					'$1href="#' . esc_attr( $anchor_id ) . '"' . $data_attr . '$3',
					$item_output
				);
			} else {
				// No ID exists, add both href and id
				$modified_output = preg_replace(
					'/(<a\s[^>]*)href=["\']([^"\']*)["\']([^>]*>)/i',
					'$1href="#' . esc_attr( $anchor_id ) . '" id="' . esc_attr( $anchor_id ) . '"' . $data_attr . '$3',
					$item_output
				);
			}
			
			$this->debug_log( 'Final output', array(
				'original' => $item_output,
				'modified' => $modified_output,
			) );
			
			return $modified_output;
		}
		
		return $item_output;
	}

	/**
	 * Return an anchor ID that has not been issued yet during this request.
	 *
	 * A menu item can render more than once on a page, for example when a
	 * theme outputs a horizontal nav and a separate mobile or vertical nav
	 * from the same menu. Deriving the ID from the page slug alone gave every
	 * one of those renders the same id attribute, which is invalid HTML and
	 * misdirects in-page anchor jumps and assistive technology.
	 *
	 * The first use of a base ID returns it unchanged, so single-nav pages are
	 * unaffected and any existing deep link to the old ID keeps resolving.
	 * Later uses get a numeric suffix. Each candidate is checked against the
	 * IDs already issued, so a genuine page slug such as 'resources-2' and a
	 * generated 'dmsl-resources-2' cannot land on the same value.
	 *
	 * Only generated IDs pass through here. An ID the theme already placed on
	 * the <a> is preserved untouched, as before.
	 *
	 * @since 1.2.2
	 * @param string $base_id Anchor ID derived from the page slug.
	 * @return string Anchor ID unique within this request.
	 */
	public function unique_anchor_id( $base_id ) {
		if ( ! isset( $this->issued_anchor_ids[ $base_id ] ) ) {
			$this->issued_anchor_ids[ $base_id ] = true;
			return $base_id;
		}

		$suffix    = 2;
		$candidate = $base_id . '-' . $suffix;

		while ( isset( $this->issued_anchor_ids[ $candidate ] ) ) {
			$suffix++;
			$candidate = $base_id . '-' . $suffix;
		}

		$this->issued_anchor_ids[ $candidate ] = true;

		return $candidate;
	}

	/**
	 * Get page slug from menu item.
	 *
	 * @since 1.2.0
	 * @param object $item Menu item object.
	 * @return string Page slug.
	 */
	private function get_page_slug( $item ) {
		// Try to get post name from object ID if it's a post/page
		if ( 'post_type' === $item->type && ! empty( $item->object_id ) ) {
			$post = get_post( $item->object_id );
			if ( $post && ! empty( $post->post_name ) ) {
				return $post->post_name;
			}
		}
		
		// Fall back to extracting from URL
		$url = $item->url;
		
		// Remove domain and protocol
		$path = wp_parse_url( $url, PHP_URL_PATH );
		
		// Get last segment
		$segments = array_filter( explode( '/', $path ) );
		$slug = ! empty( $segments ) ? end( $segments ) : 'home';
		
		// If empty or just slash, use 'home'
		if ( empty( $slug ) || '/' === $slug ) {
			$slug = 'home';
		}
		
		return $slug;
	}
}

// Initialize plugin
Disable_Menu_Self_Links::get_instance();
