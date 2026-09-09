<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Three placeholder, published Journal posts so the blog isn't empty on
 * launch — same guarded-once self-heal pattern as the rest of this
 * theme (see the admin_init/init note on newtheme_seed_journal_posts()
 * below). Replace this copy; it exists to prove the template against
 * real content, not as permanent copy.
 */
function newtheme_get_default_journal_author_id() {
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	return $admins ? $admins[0]->ID : 0;
}

/**
 * The Journal page (ForWineries\Core\PageSetup) only ever sets
 * page_for_posts on after_switch_theme — a one-time hook that doesn't
 * re-fire on a plain git-push deploy — so if that option was ever wrong
 * (unset, or pointing at a stale/deleted page ID) it stays wrong
 * indefinitely: the Journal page renders as a bare empty page instead of
 * home.php's real post loop, and every post seeded below is published
 * but effectively unreachable from the site's own nav.
 *
 * show_on_front/page_on_front/page_for_posts are healed TOGETHER,
 * defensively — only creating/assigning a placeholder page_on_front
 * when the current one is missing or invalid, never overwriting an
 * already-valid one a site might already have. Setting page_for_posts
 * alone (with show_on_front left at 'posts') silently does nothing;
 * setting show_on_front='page' alone (with page_on_front invalid) makes
 * the HOMEPAGE become the blog index instead — a strictly worse bug.
 * Both were shipped separately, for real, on sibling sites before this
 * combined fix existed — see forwineries-theme-core/docs/LESSONS.md.
 */
function newtheme_ensure_page_for_posts_is_journal() {
	$journal = get_page_by_path( 'journal' );
	if ( ! $journal ) return;

	$front_id = (int) get_option( 'page_on_front' );
	if ( ! $front_id || get_post_status( $front_id ) !== 'publish' ) {
		$home_page = get_page_by_path( 'home' );
		$front_id  = $home_page ? $home_page->ID : wp_insert_post( array(
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( $front_id && ! is_wp_error( $front_id ) ) {
			update_option( 'page_on_front', $front_id );
		}
	}

	if ( get_option( 'show_on_front' ) !== 'page' ) {
		update_option( 'show_on_front', 'page' );
	}
	if ( (int) get_option( 'page_for_posts' ) !== $journal->ID ) {
		update_option( 'page_for_posts', $journal->ID );
	}
}
add_action( 'admin_init', 'newtheme_ensure_page_for_posts_is_journal', 19 );
add_action( 'init', 'newtheme_ensure_page_for_posts_is_journal', 19 );

function newtheme_seed_journal_posts() {
	if ( get_option( 'newtheme_journal_posts_seeded' ) ) return;

	$author_id = newtheme_get_default_journal_author_id();
	if ( ! $author_id ) return; // no admin user yet — try again next request

	$posts = array(
		array(
			'post_title'   => 'Welcome to the Journal',
			'post_name'    => 'welcome-to-the-journal',
			'post_content' => newtheme_seed_post_1_content(),
		),
		array(
			'post_title'   => 'A Season in the Vineyard',
			'post_name'    => 'a-season-in-the-vineyard',
			'post_content' => newtheme_seed_post_2_content(),
		),
		array(
			'post_title'   => 'What We Look for in a Bottle',
			'post_name'    => 'what-we-look-for-in-a-bottle',
			'post_content' => newtheme_seed_post_3_content(),
		),
	);

	foreach ( $posts as $p ) {
		if ( get_page_by_path( $p['post_name'], OBJECT, 'post' ) ) continue;
		wp_insert_post( array(
			'post_title'   => $p['post_title'],
			'post_name'    => $p['post_name'],
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_author'  => $author_id,
			'post_content' => $p['post_content'],
		) );
		// Deliberately NOT setting a featured image here, unlike the
		// sibling product themes' equivalent file — this starter ships
		// with zero real photos (see assets/images/README.md), so there
		// is nothing safe to sideload yet. Add your own
		// set_post_thumbnail() call here once you have a real,
		// brand-safety-verified journal photo.
	}

	update_option( 'newtheme_journal_posts_seeded', 1 );
}
/**
 * Same self-heal reasoning as every other guarded-once seed in this
 * theme: a plain admin_init callback never fires on its own after a
 * git-push deploy, so this also runs on 'init' — the first real
 * front-end or back-end request after deploy reaches one of these and
 * the guard option keeps it a no-op on every request after that.
 */
add_action( 'admin_init', 'newtheme_seed_journal_posts', 20 );
add_action( 'init', 'newtheme_seed_journal_posts', 20 );

function newtheme_seed_post_1_content() {
	return <<<HTML
<p>Replace this placeholder post with your own introduction — who you are, where the estate is, and what a visitor to this journal should expect to find here.</p>

<h2>Why keep a journal at all</h2>
<p>A wine journal works best as a running record of real, specific things: a harvest date, a barrel decision, a new release — not generic marketing copy. Replace this paragraph with something concrete from your own season.</p>

<p>Once you have real posts, delete this seed content — it exists only to prove the template renders correctly against real content on a fresh install.</p>
HTML;
}

function newtheme_seed_post_2_content() {
	return <<<HTML
<p>Replace this placeholder with a real account of a season on your own estate — pruning, canopy work, harvest, and the quieter months in between.</p>

<h2>A structure worth borrowing, even if the words aren't</h2>
<p>A four-part seasonal structure (dormant pruning, canopy management, harvest, post-harvest) is a reliable shape for this kind of post regardless of climate or region — replace the specifics with what actually happens on your own vineyard.</p>
HTML;
}

function newtheme_seed_post_3_content() {
	return <<<HTML
<p>Replace this placeholder with your own winemaking philosophy — what you're actually optimizing for when you decide a wine is ready to bottle.</p>

<h2>Specific beats abstract</h2>
<p>"We care about quality" says nothing a reader can picture. Replace this section with one concrete practice — a fermentation choice, an aging regimen, a sourcing rule — that actually distinguishes your winemaking.</p>

<p><a href="/collection/wines/">Browse the current release</a> to see it in the glass.</p>
HTML;
}
