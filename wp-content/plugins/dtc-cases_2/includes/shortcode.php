<?php
defined( 'ABSPATH' ) || exit;

/**
 * Converts **bold** and %%gold%% markdown to HTML.
 * Safe: esc_html runs first.
 */
if ( ! function_exists( 'dtc_cases_parse_title' ) ) {
	function dtc_cases_parse_title( string $text ): string {
		$text = esc_html( $text );
		$text = preg_replace( '/%%(.+?)%%/u', '<strong class="dtc-accent">$1</strong>', $text );
		$text = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text );
		return $text;
	}
}

/**
 * [dtc_cases_grid] — archive hero + 3-column staggered portfolio grid.
 *
 * Attributes:
 *   posts_per_page   (int)    default -1
 *   orderby          (string) default 'date'
 *   order            (string) default 'DESC'
 *   show_hero        (string) 'yes' | 'no'  default 'yes'
 *   hero_title       (string) supports **bold** (dark) and %%gold%% markdown
 *   hero_text        (string) body paragraph below title
 *   hero_button_text (string) CTA label
 *   hero_button_url  (string) CTA href
 */
add_shortcode( 'dtc_cases_grid', function ( $atts ): string {
	$atts = shortcode_atts(
		[
			'posts_per_page'   => -1,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'show_hero'        => 'yes',
			'hero_title'       => (string) get_option( 'dtc_cases_archive_title', 'the work your %%competitors%% wish **they had**' ),
			'hero_text'        => (string) get_option( 'dtc_cases_archive_text',  '' ),
			'hero_button_text' => 'Find out more',
			'hero_button_url'  => '',
		],
		$atts,
		'dtc_cases_grid'
	);

	$allowed_orderby = [ 'date', 'title', 'menu_order', 'rand', 'modified' ];
	$orderby = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'menu_order';
	$order   = in_array( strtoupper( (string) $atts['order'] ), [ 'ASC', 'DESC' ], true )
		? strtoupper( (string) $atts['order'] )
		: 'ASC';

	$query = new WP_Query( [
		'post_type'      => 'dtc_case',
		'posts_per_page' => intval( $atts['posts_per_page'] ),
		'orderby'        => $orderby,
		'order'          => $order,
		'post_status'    => 'publish',
	] );

	wp_enqueue_style(
		'dtc-cases',
		DTC_CASES_URL . 'assets/css/dtc-cases.css',
		[],
		DTC_CASES_VERSION
	);

	ob_start();

	echo '<div class="dtc-cases-archive-wrap">';

	// ── Archive hero ────────────────────────────────────────────────────────────
	if ( 'yes' === $atts['show_hero'] ) :
	?>
	<div class="dtc-case-archive-hero">
		<div class="dtc-case-archive-hero__inner">
			<h1 class="dtc-work-page__title">
				<?php echo dtc_cases_parse_title( $atts['hero_title'] ); ?>
			</h1>
			<?php if ( $atts['hero_text'] ) : ?>
				<p class="dtc-case-archive-hero__text"><?php echo esc_html( $atts['hero_text'] ); ?></p>
			<?php endif; ?>
			<?php if ( $atts['hero_button_text'] ) :
				$btn_url = $atts['hero_button_url'] ? esc_url( $atts['hero_button_url'] ) : '#';
			?>
				<a href="<?php echo $btn_url; ?>" class="dtc-case-archive-hero__btn">
					<span><?php echo esc_html( $atts['hero_button_text'] ); ?></span>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<line x1="5" y1="12" x2="19" y2="12"/>
						<polyline points="12,5 19,12 12,19"/>
					</svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	endif;

	// ── Collect & distribute posts round-robin into 3 columns ───────────────────
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		$output = ob_get_clean();
		return $output . '<p class="dtc-case-no-results">' . esc_html__( 'No cases found.', 'dtc-cases' ) . '</p>';
	}

	$all_posts = [];
	while ( $query->have_posts() ) {
		$query->the_post();
		$all_posts[] = get_post();
	}
	wp_reset_postdata();

	// col 0 = items 1,4,7…  col 1 = items 2,5,8…  col 2 = items 3,6,9…
	$columns = [ [], [], [] ];
	foreach ( $all_posts as $i => $post_obj ) {
		$columns[ $i % 3 ][] = $post_obj;
	}
	?>
	<div class="dtc-case-grid">
		<?php foreach ( $columns as $col_i => $col_posts ) : ?>
		<div class="dtc-case-grid__col dtc-case-grid__col--<?php echo ( $col_i + 1 ); ?>">
			<?php foreach ( $col_posts as $row_i => $post_obj ) :
				setup_postdata( $post_obj );
				// Sequential number across the full grid (1, 2, 3, 4 …)
				$item_number = $col_i + 1 + $row_i * 3;
			?>
				<a href="<?php echo get_permalink( $post_obj ); ?>" class="dtc-case-grid__item" aria-label="<?php echo esc_attr( get_the_title( $post_obj ) ); ?>">

					<?php if ( has_post_thumbnail( $post_obj ) ) : ?>
						<div class="dtc-case-grid__image">
							<?php echo get_the_post_thumbnail( $post_obj, 'large', [ 'loading' => 'lazy' ] ); ?>
						</div>
					<?php else : ?>
						<div class="dtc-case-grid__image dtc-case-grid__image--no-thumb"></div>
					<?php endif; ?>

					<div class="dtc-case-grid__overlay" aria-hidden="true"></div>

					<span class="dtc-case-grid__number" aria-hidden="true"><?php echo $item_number; ?></span>

					<span class="dtc-case-grid__title"><?php echo esc_html( get_the_title( $post_obj ) ); ?></span>

					<span class="dtc-case-grid__arrow" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="5" y1="12" x2="19" y2="12"/>
							<polyline points="12,5 19,12 12,19"/>
						</svg>
					</span>

				</a>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
	echo '</div><!-- .dtc-cases-archive-wrap -->';
	wp_reset_postdata();
	return ob_get_clean();
} );
