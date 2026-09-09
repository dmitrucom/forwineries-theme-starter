<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
namespace ForWineries\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Simple breadcrumb trail (Home > Section > Page) — reuses the shared
 * .fw-eyebrow typography rather than inventing a new visual style.
 * Promoted from a theme-prefixed function (byte-identical across all 5
 * original themes modulo prefix) since a shared core template can't
 * call a theme-prefixed function name.
 */
class Breadcrumbs {

	/**
	 * $items is an ordered array of array($label, $url) pairs for
	 * everything AFTER "Home" (added automatically); the last item's
	 * $url should be empty/null — that's the current page, rendered as
	 * plain text with aria-current, not a link to itself.
	 */
	public static function render( Config $config, array $items ): void {
		?>
		<nav class="<?php echo esc_attr( $config->css( 'breadcrumbs' ) . ' ' . $config->css( 'eyebrow' ) ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', $config->text_domain() ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', $config->text_domain() ); ?></a>
			<?php foreach ( $items as $item ) :
				list( $label, $url ) = $item;
				?>
				<span class="<?php echo esc_attr( $config->css( 'breadcrumb-sep' ) ); ?>" aria-hidden="true">/</span>
				<?php if ( $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
		<?php
	}
}
