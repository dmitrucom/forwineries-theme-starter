<?php
/**
 * Custom search form — what get_search_form() renders everywhere it's
 * called (404 page, search results page), matching this theme's own
 * design language instead of WordPress's default markup.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<form role="search" method="get" class="newtheme-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="newtheme-search-form-field">
		<input type="search" class="newtheme-search-input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="<?php esc_attr_e( 'Search', 'newtheme' ); ?>" placeholder="<?php esc_attr_e( 'Search the site…', 'newtheme' ); ?>">
		<button type="submit" class="newtheme-search-submit" aria-label="<?php esc_attr_e( 'Submit search', 'newtheme' ); ?>">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
				<circle cx="7" cy="7" r="5.25" stroke="currentColor" stroke-width="1.5"/>
				<line x1="11.25" y1="11.25" x2="14.5" y2="14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
			</svg>
		</button>
	</div>
</form>
