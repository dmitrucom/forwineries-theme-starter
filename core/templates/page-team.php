<?php
// GENERATED FILE — do not edit directly. Synced from forwineries-theme-core.
// Edit the source there and re-run tools/sync-core.sh.
/**
 * Team page. A product theme's own page-templates/page-team.php stub
 * sets the "Template Name:" header, then requires this file with
 * $fw_config already in scope. Team member list comes from the Pages &
 * Content tab's 'team_members' repeater field.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use ForWineries\Core\Config;
use ForWineries\Core\ElementorDefer;
use ForWineries\Core\Breadcrumbs;
use ForWineries\Core\ContentSettings\Framework;
use ForWineries\Core\PageSetup;

$fw_config = new Config( isset( $fw_config ) ? $fw_config : array() );

get_header();

if ( ElementorDefer::is_built( get_the_ID() ) ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
else :
	?>

	<?php
	// Breadcrumb label for the parent page reads the theme's own configured
	// title (PageSetup's marketing_pages()) rather than a hardcoded string —
	// "estate" defaults to "How We Work" for 4 of 5 themes, only Larkhaven
	// overrides it to "The Estate".
	$fw_estate_pages = PageSetup::marketing_pages( $fw_config );
	$fw_estate_title = isset( $fw_estate_pages['estate'][0] ) ? $fw_estate_pages['estate'][0] : __( 'The Estate', $fw_config->text_domain() );
	?>
	<section class="fw-section">
		<div class="fw-container">
			<?php Breadcrumbs::render( $fw_config, array( array( $fw_estate_title, home_url( '/estate' ) ), array( __( 'Team', $fw_config->text_domain() ), '' ) ) ); ?>
			<div class="fw-section-head">
				<span class="fw-eyebrow"><?php echo esc_html( Framework::field( $fw_config, 'team_eyebrow' ) ); ?></span>
				<h1><?php echo esc_html( Framework::field( $fw_config, 'team_heading' ) ); ?></h1>
				<p><?php echo esc_html( Framework::field( $fw_config, 'team_intro' ) ); ?></p>
			</div>

			<div class="fw-team-grid">
				<?php foreach ( Framework::field_repeater( $fw_config, 'team_members' ) as $fw_member ) :
					$fw_photo = isset( $fw_member['photo'] ) ? trim( $fw_member['photo'] ) : '';
					$fw_name  = isset( $fw_member['name'] ) ? $fw_member['name'] : '';
					$fw_role  = isset( $fw_member['role'] ) ? $fw_member['role'] : '';
					$fw_bio   = isset( $fw_member['bio'] ) ? $fw_member['bio'] : '';
					?>
					<div class="fw-team-card">
						<?php if ( $fw_photo ) : ?>
							<img class="fw-team-photo" src="<?php echo esc_url( $fw_photo ); ?>" alt="<?php echo esc_attr( $fw_name ); ?>" loading="lazy">
						<?php else : ?>
							<div class="ph-img ph-portrait fw-team-photo"><span class="ph-tag">team-portrait — 500x600</span></div>
						<?php endif; ?>
						<div class="fw-team-card-body">
							<h2><?php echo esc_html( $fw_name ); ?></h2>
							<?php if ( $fw_role ) : ?><div class="fw-team-role"><?php echo esc_html( $fw_role ); ?></div><?php endif; ?>
							<?php if ( $fw_bio ) : ?><p><?php echo esc_html( $fw_bio ); ?></p><?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

<?php
endif;

get_footer();
