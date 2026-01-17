/**
 * Frontend JavaScript for Disable Menu Self Links
 *
 * Provides JavaScript-based preventDefault as backup protection
 *
 * @package Disable_Menu_Self_Links
 * @since   1.0
 */

(function() {
	'use strict';

	/**
	 * Prevent default behavior on self-referencing menu links.
	 *
	 * This is a backup protection layer in addition to:
	 * - Self-referencing hrefs (#slug)
	 * - CSS pointer-events: none
	 */
	function disableSelfLinks() {
		// Find all links in current menu items that reference themselves
		const currentMenuItems = document.querySelectorAll('.current-menu-item a, .current_page_item a');
		
		currentMenuItems.forEach(function(link) {
			const href = link.getAttribute('href');
			
			// Check if it's a self-referencing anchor
			if (href && href.startsWith('#')) {
				const anchorId = href.substring(1); // Remove the #
				const linkId = link.getAttribute('id');
				
				// If the href anchor matches the link's own id, prevent clicks
				if (anchorId === linkId) {
					link.addEventListener('click', function(e) {
						e.preventDefault();
						
						// Debug logging if enabled
						if (window.console && window.console.log) {
							console.log('[DMSL] Prevented self-link click:', link.textContent.trim());
						}
						
						return false;
					});
					
					// Also prevent keyboard activation
					link.addEventListener('keydown', function(e) {
						// Enter or Space key
						if (e.key === 'Enter' || e.key === ' ') {
							e.preventDefault();
							return false;
						}
					});
				}
			}
		});
	}

	// Run when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', disableSelfLinks);
	} else {
		// DOM already loaded
		disableSelfLinks();
	}

	// Re-run after AJAX navigation (for SPAs)
	document.addEventListener('load', disableSelfLinks);
	
})();
