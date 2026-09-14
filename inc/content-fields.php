<?php
/**
 * [Your Winery Name]'s field SCHEMA for ForWineries\Core\ContentSettings\
 * Framework — the generic engine lives in core; this file is 100%
 * per-theme content, handed to Boot::init() via the 'content_fields' key.
 * Based on Heronrest Vineyards' schema (forwineries-theme-core/docs/
 * LESSONS.md notes it as the most complete of the 5 original themes),
 * genericized: every default value below is obvious placeholder copy,
 * not real brand voice — replace it, don't just restyle around it.
 *
 * Field shapes:
 *   Scalar:   'key' => array( $label, $type, $default )
 *   Repeater: 'key' => array( $label, 'repeater', $subfields, $default_rows )
 *   $type: 'text' | 'textarea' | 'url' | 'number' | 'image'
 *
 * Every group below is read by either front-page.php (this theme's own
 * file) or one of forwineries-theme-core's core/templates/page-*.php
 * files — see NEW-THEME-CHECKLIST.md for which template reads which
 * group. Don't rename a group/field key without checking both places.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

return array(
	'site_identity_global' => array(
		'title'  => __( 'Logo', 'newtheme' ),
		'fields' => array(
			'logo_image' => array( __( 'Logo image (optional — leave blank to use the default wordmark)', 'newtheme' ), 'image', '' ),
		),
	),
	'hero' => array(
		'title'  => __( 'Hero (Homepage)', 'newtheme' ),
		'fields' => array(
			'hero_heading'             => array( __( 'Heading', 'newtheme' ), 'text', 'Rooted in the valley. Made for the table.' ),
			'hero_subtext'             => array( __( 'Subtext', 'newtheme' ), 'textarea', 'Replace this placeholder with a one-line description of your winery.' ),
			'hero_btn_primary_label'   => array( __( 'Primary button label', 'newtheme' ), 'text', 'Explore Our Wines' ),
			'hero_btn_secondary_label' => array( __( 'Secondary button label', 'newtheme' ), 'text', 'Reserve a Tasting' ),
		),
	),
	'story' => array(
		'title'  => __( 'Our Story (Homepage)', 'newtheme' ),
		'fields' => array(
			'story_eyebrow'     => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Our Story' ),
			'story_heading'     => array( __( 'Heading', 'newtheme' ), 'text', 'A short history worth telling' ),
			'story_paragraph_1' => array( __( 'Paragraph 1', 'newtheme' ), 'textarea', 'Replace this placeholder with your winery\'s founding story — who started it, when, and why.' ),
			'story_paragraph_2' => array( __( 'Paragraph 2', 'newtheme' ), 'textarea', 'Replace this placeholder with a second paragraph — what makes your approach to winemaking distinct.' ),
			'story_btn_label'   => array( __( 'Button label', 'newtheme' ), 'text', 'Read Our Story' ),
		),
	),
	'stats' => array(
		'title'  => __( 'Stats Row (Homepage)', 'newtheme' ),
		'fields' => array(
			'stat1_number' => array( __( 'Stat 1 — Number', 'newtheme' ), 'text', '19XX' ),
			'stat1_label'  => array( __( 'Stat 1 — Label', 'newtheme' ), 'text', 'Founded' ),
			'stat2_number' => array( __( 'Stat 2 — Number', 'newtheme' ), 'text', '00' ),
			'stat2_label'  => array( __( 'Stat 2 — Label', 'newtheme' ), 'text', 'Estate acres' ),
			'stat3_number' => array( __( 'Stat 3 — Number', 'newtheme' ), 'text', '0' ),
			'stat3_label'  => array( __( 'Stat 3 — Label', 'newtheme' ), 'text', 'Wines produced' ),
		),
	),
	'wines' => array(
		'title'  => __( 'Featured Wines Section (Homepage)', 'newtheme' ),
		'fields' => array(
			'wines_eyebrow'     => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Current Releases' ),
			'wines_heading'     => array( __( 'Heading', 'newtheme' ), 'text', 'Our Wines' ),
			'wines_description' => array( __( 'Description', 'newtheme' ), 'textarea', 'Replace this placeholder with a short line introducing your current lineup.' ),
			'wines_limit'       => array( __( 'Number of wines to show', 'newtheme' ), 'number', '6' ),
			'wines_btn_label'   => array( __( 'Button label', 'newtheme' ), 'text', 'View All Wines' ),
		),
	),
	'club' => array(
		'title'  => __( 'Wine Club CTA (Homepage)', 'newtheme' ),
		'fields' => array(
			'club_eyebrow'   => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Membership' ),
			'club_heading'   => array( __( 'Heading', 'newtheme' ), 'text', 'Join the club' ),
			'club_paragraph' => array( __( 'Paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with a short pitch for your wine club — what members get and why it\'s worth joining.' ),
		),
	),
	'reservation_cta' => array(
		'title'  => __( 'Reservation CTA (Homepage)', 'newtheme' ),
		'fields' => array(
			'reservation_eyebrow'   => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Tastings by Appointment' ),
			'reservation_heading'   => array( __( 'Heading', 'newtheme' ), 'text', 'Come taste with us' ),
			'reservation_paragraph' => array( __( 'Paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with a line about what a tasting visit looks like — hours, format, what to expect.' ),
			'reservation_btn_label' => array( __( 'Button label', 'newtheme' ), 'text', 'Check Availability' ),
		),
	),
	'visit_cta' => array(
		'title'  => __( 'Visit CTA (Homepage)', 'newtheme' ),
		'fields' => array(
			'visit_eyebrow'           => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Plan Your Visit' ),
			'visit_heading'           => array( __( 'Heading', 'newtheme' ), 'text', 'Find us' ),
			// Read by BOTH the homepage CTA and the footer's "Visit" column
			// (see footer.php) — same field, single source of truth.
			'visit_hours'             => array( __( 'Hours line', 'newtheme' ), 'text', 'Daily 10am–5pm · By appointment' ),
			'visit_reserve_btn_label' => array( __( '"Reserve" button label', 'newtheme' ), 'text', 'Book a Tasting' ),
			'visit_btn_label'         => array( __( '"Plan your trip" button label', 'newtheme' ), 'text', 'Plan Your Trip' ),
		),
	),

	'estate_page' => array(
		'title'  => __( 'Estate Page', 'newtheme' ),
		'fields' => array(
			'estate_eyebrow'      => array( __( 'Eyebrow', 'newtheme' ), 'text', 'The Estate' ),
			'estate_heading'      => array( __( 'Heading', 'newtheme' ), 'text', 'How We Work' ),
			'estate_intro'        => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with an introduction to your estate — location, founding, and what makes it distinct.' ),
			'estate_history'      => array( __( 'History paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with the estate\'s history.' ),
			'estate_philosophy'   => array( __( 'Winemaking philosophy paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with your winemaking philosophy.' ),
			'estate_stat1_number' => array( __( 'Stat 1 — Number', 'newtheme' ), 'text', '19XX' ),
			'estate_stat1_label'  => array( __( 'Stat 1 — Label', 'newtheme' ), 'text', 'Founded' ),
			'estate_stat2_number' => array( __( 'Stat 2 — Number', 'newtheme' ), 'text', '00' ),
			'estate_stat2_label'  => array( __( 'Stat 2 — Label', 'newtheme' ), 'text', 'Estate acres' ),
			'estate_stat3_number' => array( __( 'Stat 3 — Number', 'newtheme' ), 'text', '0' ),
			'estate_stat3_label'  => array( __( 'Stat 3 — Label', 'newtheme' ), 'text', 'Wines produced' ),
			// Defaults match functions.php's pages.estate.hero/.history/
			// .winemaking — those config values are now just the fallback
			// used when this field is left blank, see core/templates/
			// page-estate.php.
			'estate_hero_image'       => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
			'estate_history_image'    => array( __( 'History photo', 'newtheme' ), 'image', '' ),
			'estate_winemaking_image' => array( __( 'Winemaking photo', 'newtheme' ), 'image', '' ),
		),
	),

	'team_page' => array(
		'title'  => __( 'Team Page', 'newtheme' ),
		'fields' => array(
			'team_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Our Team' ),
			'team_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'The People Behind the Wine' ),
			'team_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with a short intro to your team.' ),
			'team_members' => array(
				__( 'Team Members', 'newtheme' ),
				'repeater',
				array(
					'photo' => array( __( 'Photo', 'newtheme' ), 'image' ),
					'name'  => array( __( 'Name', 'newtheme' ), 'text' ),
					'role'  => array( __( 'Role', 'newtheme' ), 'text' ),
					'bio'   => array( __( 'Bio', 'newtheme' ), 'textarea' ),
				),
				array(
					// Blank photo URLs on purpose — see assets/images/README.md.
					array( 'photo' => '', 'name' => '[Team Member Name]', 'role' => 'Winemaker', 'bio' => 'Replace this placeholder with a short bio.' ),
					array( 'photo' => '', 'name' => '[Team Member Name]', 'role' => 'Vineyard Manager', 'bio' => 'Replace this placeholder with a short bio.' ),
					array( 'photo' => '', 'name' => '[Team Member Name]', 'role' => 'Tasting Room Director', 'bio' => 'Replace this placeholder with a short bio.' ),
				),
			),
		),
	),

	'visit_page' => array(
		'title'  => __( 'Visit Page', 'newtheme' ),
		'fields' => array(
			'visit_page_eyebrow'   => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Plan Your Visit' ),
			'visit_page_heading'   => array( __( 'Heading', 'newtheme' ), 'text', 'Taste at the Source' ),
			'visit_page_intro'     => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with details about visiting — format, what to expect, how to book.' ),
			// core's page-visit.php reads this SEPARATE field, not the
			// homepage/footer's 'visit_hours' — a real drift risk called
			// out directly in forwineries-theme-core/docs/LESSONS.md
			// (Heronrest's own bug-fix history removed the duplicate
			// field, but core/templates/page-visit.php was ported from a
			// sibling that still needs it). Kept as two fields here,
			// deliberately defaulted to the SAME text, so there's no
			// visible drift today — if you ever edit hours, update BOTH
			// 'visit_hours' above and 'visit_page_hours' below together.
			'visit_page_hours'     => array( __( 'Hours (keep in sync with "Hours line" in Visit CTA above)', 'newtheme' ), 'textarea', 'Daily 10am–5pm · By appointment' ),
			'visit_page_map_embed' => array( __( 'Map embed URL (Google Maps "Embed a map" src)', 'newtheme' ), 'url', '' ),
			// Default matches functions.php's pages.visit.image — that
			// config value is now just the fallback, see core/templates/
			// page-visit.php.
			'visit_page_image'     => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
		),
	),

	'gift_cards_page' => array(
		'title'  => __( 'Gift Cards Page', 'newtheme' ),
		'fields' => array(
			'gift_cards_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Give the Gift of Wine' ),
			'gift_cards_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'Gift Cards' ),
			'gift_cards_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with a short pitch for gift cards — how they\'re delivered and redeemed.' ),
			// Default matches functions.php's pages.gift_cards.image —
			// that config value is now just the fallback, see
			// core/templates/page-gift-cards.php.
			'gift_cards_image'  => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
		),
	),

	'wines_page' => array(
		'title'  => __( 'Wines / Shop Page (Commerce7)', 'newtheme' ),
		'fields' => array(
			// Default matches functions.php's pages.c7_content.wines.image
			// — that config value is now just the fallback, see
			// core/templates/page-c7-content.php.
			'wines_page_image' => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
		),
	),

	'club_page' => array(
		'title'  => __( 'Wine Club Page', 'newtheme' ),
		'fields' => array(
			'club_page_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Membership' ),
			'club_page_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'Wine Club' ),
			'club_page_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with an overview of your club tiers.' ),
			// Default matches functions.php's pages.c7_content.club.image
			// — that config value is now just the fallback, see
			// core/templates/page-c7-content.php.
			'club_page_image'   => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
			'club_tiers'        => array(
				__( 'Tiers', 'newtheme' ),
				'repeater',
				array(
					'name'        => array( __( 'Tier name', 'newtheme' ), 'text' ),
					'price'       => array( __( 'Price', 'newtheme' ), 'text' ),
					'description' => array( __( 'Description', 'newtheme' ), 'textarea' ),
					'slug'        => array( __( 'Commerce7 club slug (optional — leave blank to use the default club slug from Setup)', 'newtheme' ), 'text' ),
				),
				array(
					array( 'name' => '[Tier One Name]', 'price' => '$00 / quarter', 'description' => 'Replace this placeholder with what this tier includes.', 'slug' => '' ),
					array( 'name' => '[Tier Two Name]', 'price' => '$00 / quarter', 'description' => 'Replace this placeholder with what this tier includes.', 'slug' => '' ),
					array( 'name' => '[Tier Three Name]', 'price' => '$00 / quarter', 'description' => 'Replace this placeholder with what this tier includes.', 'slug' => '' ),
				),
			),
		),
	),

	'reservation_page' => array(
		'title'  => __( 'Reservations Page', 'newtheme' ),
		'fields' => array(
			'reservation_page_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Tastings by Appointment' ),
			'reservation_page_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'Reserve a Tasting' ),
			'reservation_page_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with reservation details — pick a date and time below.' ),
			// Default matches functions.php's
			// pages.c7_content.reservation.image — that config value is
			// now just the fallback, see core/templates/page-c7-content.php.
			'reservation_page_image'   => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
		),
	),

	'profile_page' => array(
		'title'  => __( 'Account Page', 'newtheme' ),
		'fields' => array(
			'profile_page_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Your Account' ),
			'profile_page_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'Welcome Back' ),
			'profile_page_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Manage your orders, wine club membership, and saved details below.' ),
			// Default matches functions.php's
			// pages.c7_content.profile.image — that config value is now
			// just the fallback, see core/templates/page-c7-content.php.
			'profile_page_image'   => array( __( 'Hero photo', 'newtheme' ), 'image', '' ),
		),
	),

	'contact_page' => array(
		'title'  => __( 'Contact Page', 'newtheme' ),
		'fields' => array(
			'contact_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Get in Touch' ),
			'contact_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'We\'d Love to Hear From You' ),
			'contact_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with a short line inviting visitors to reach out.' ),
		),
	),

	'trade_page' => array(
		'title'  => __( 'Trade & Press Page', 'newtheme' ),
		'fields' => array(
			'trade_eyebrow' => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Trade & Press' ),
			'trade_heading' => array( __( 'Heading', 'newtheme' ), 'text', 'For Restaurants, Retailers & Press' ),
			'trade_intro'   => array( __( 'Intro paragraph', 'newtheme' ), 'textarea', 'Replace this placeholder with an invitation for trade/press inquiries — this goes to a different inbox than the general contact form.' ),
		),
	),

	'age_gate_global' => array(
		'title'  => __( 'Age Verification Gate', 'newtheme' ),
		'fields' => array(
			// See functions.php's own 'age_gate.bg_image' TODO — this
			// field's default (below) is intentionally blank, meaning
			// core's OWN generic fallback would apply if you don't set
			// either one. See NEW-THEME-CHECKLIST.md.
			'age_gate_bg_image'        => array( __( 'Background photo (optional — leave blank to use the config bg_image / homepage hero photo)', 'newtheme' ), 'image', '' ),
			'age_gate_eyebrow'         => array( __( 'Eyebrow', 'newtheme' ), 'text', 'Please Confirm' ),
			'age_gate_heading'         => array( __( 'Heading', 'newtheme' ), 'text', 'Please Confirm Your Date of Birth' ),
			'age_gate_intro'           => array( __( 'Intro text', 'newtheme' ), 'textarea', 'Please indicate your country/region and date of birth to enter this site. You must be of legal drinking age to view this content.' ),
			'age_gate_region_label'    => array( __( '"Country / Region" field label', 'newtheme' ), 'text', 'Country / Region' ),
			'age_gate_remember_label'  => array( __( '"Remember me" checkbox label', 'newtheme' ), 'text', 'Remember me (do not tick the box if your device is shared)' ),
			'age_gate_submit_label'    => array( __( 'Submit button label', 'newtheme' ), 'text', 'Submit' ),
			'age_gate_min_age'         => array( __( 'Minimum age required to enter', 'newtheme' ), 'text', '21' ),
			'age_gate_disclaimer'      => array( __( 'Disclaimer text (shown below the form)', 'newtheme' ), 'textarea', 'By entering this site, you confirm you are of legal drinking age in your country/region of residence. [Your Winery Name] supports the responsible consumption of its wines — please enjoy responsibly.' ),
			'age_gate_refusal_heading' => array( __( 'Refusal heading (shown if the entered date of birth is under the minimum age)', 'newtheme' ), 'text', 'We\'re Sorry' ),
			'age_gate_refusal_message' => array( __( 'Refusal message', 'newtheme' ), 'textarea', 'You must be of legal drinking age to visit this site. Please enjoy responsibly.' ),
		),
	),
);
