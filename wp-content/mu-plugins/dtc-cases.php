<?php
/**
 * Plugin Name: DTC Cases
 * Description: De strook "our other cases" onderaan elke case-pagina, met de andere cases erin.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * De cases waarvan echt beeldmateriaal bestaat - dezelfde negen die op de
 * Work-pagina staan, met dezelfde afbeelding, zodat de twee overzichten niet
 * uit elkaar lopen.
 *
 * De overige case-pagina's (ford, farizon, alpine, byd, autosturm,
 * mercedes-benz) hebben nog geen eigen beeld. Die staan hier bewust niet in:
 * met de algemene kantoorfoto erbij ziet de strook eruit als opvulling. Zodra
 * er beeld is, is een regel toevoegen genoeg.
 */
function dtc_cases_list() {
	return array(
		//                   naam               beeld                                                    groep
		'nissan'          => array( 'Nissan',          '/wp-content/uploads/2026/01/Cases-Opzet.webp',             'case' ),
		'mazda'           => array( 'Mazda',           '/wp-content/uploads/2026/01/Cases-mazda.webp',             'case' ),
		'mitsubishi'      => array( 'Mitsubishi',      '/wp-content/uploads/2026/01/Cases-Opzet-mitsubishi.webp',  'case' ),
		'renault'         => array( 'Renault',         '/wp-content/uploads/2026/01/Cases-renault.webp',           'case' ),
		'lekkerboats'     => array( 'Lekkerboats',     '/wp-content/uploads/2026/01/Cases-lekkerboats.webp',       'case' ),
		'energiemanagers' => array( 'Energiemanagers', '/wp-content/uploads/2026/04/energiemanagers_panel.jpg',    'case' ),
		'louwman'         => array( 'Louwman',         '/wp-content/uploads/2026/01/Cases-louwman.webp',           'dealer' ),
		'van-mossel-2'    => array( 'Van Mossel',      '/wp-content/uploads/2026/06/Cases-vanmossel-1.webp',       'dealer' ),
		'bochane-2'       => array( 'Bochane',         '/wp-content/uploads/2026/06/Cases-bochane-1.webp',         'dealer' ),
	);
}

/**
 * De case-pagina's zonder eigen beeld. Ze staan niet als kaartje in de strook,
 * maar hun soort is wel nodig: daarmee weet de strook welk tabblad open moet
 * staan wanneer je zo'n pagina bekijkt.
 */
function dtc_cases_zonder_beeld() {
	return array(
		'ford'          => 'case',
		'farizon'       => 'case',
		'alpine'        => 'case',
		'byd'           => 'case',
		'mercedes-benz' => 'case',
		'autosturm'     => 'dealer',
	);
}

/** De twee groepen, in de volgorde waarin de tabbladen staan. */
function dtc_cases_soorten() {
	return array(
		'case'   => 'cases',
		'dealer' => 'dealers',
	);
}

/** Het soort van een case, ook als die geen kaartje heeft. */
function dtc_cases_soort_van( $slug ) {
	$lijst = dtc_cases_list();
	if ( isset( $lijst[ $slug ][2] ) ) {
		return $lijst[ $slug ][2];
	}
	$rest = dtc_cases_zonder_beeld();
	return $rest[ $slug ] ?? 'case';
}

/** Alle pagina's die een case zijn - ook die zonder eigen beeld. */
function dtc_cases_pages() {
	return array_merge(
		array_keys( dtc_cases_list() ),
		array_keys( dtc_cases_zonder_beeld() )
	);
}

function dtc_cases_is_case( $post ) {
	return $post instanceof WP_Post
		&& 'page' === $post->post_type
		&& in_array( $post->post_name, dtc_cases_pages(), true );
}

/* -------------------------------------------------------------------------
 * De strook
 * ---------------------------------------------------------------------- */

/**
 * Bouwt de strook met de andere cases. De huidige case valt eruit, want naar
 * jezelf verwijzen heeft geen zin.
 *
 * De cases staan in twee groepen: cases en dealers.
 * Het tabblad van de case die je bekijkt staat open, zodat je eerst de
 * soortgenoten ziet en met een klik naar de andere groep gaat.
 *
 * Zonder JavaScript staan alle kaarten gewoon zichtbaar en zijn de knoppen
 * verborgen - dan is het weer de strook zoals hij was.
 */
function dtc_cases_strip( $huidig = '' ) {
	$cases = dtc_cases_list();
	unset( $cases[ $huidig ] );
	if ( count( $cases ) < 2 ) {
		return '';
	}

	$soorten = dtc_cases_soorten();
	$open    = dtc_cases_soort_van( $huidig );

	// Een groep zonder kaarten heeft geen knop nodig.
	$aantal = array_fill_keys( array_keys( $soorten ), 0 );
	foreach ( $cases as $case ) {
		$aantal[ $case[2] ]++;
	}
	if ( ! $aantal[ $open ] ) {
		$open = array_search( max( $aantal ), $aantal, true );
	}

	ob_start();
	?>
	<section class="dtc-oc" data-open="<?php echo esc_attr( $open ); ?>">
		<div class="in">
			<h2>our other cases</h2>

			<div class="tabs" role="tablist" aria-label="cases per groep">
				<?php foreach ( $soorten as $sleutel => $label ) : ?>
					<?php if ( ! $aantal[ $sleutel ] ) { continue; } ?>
					<button type="button" class="tab<?php echo $sleutel === $open ? ' on' : ''; ?>"
						role="tab" data-soort="<?php echo esc_attr( $sleutel ); ?>"
						aria-selected="<?php echo $sleutel === $open ? 'true' : 'false'; ?>">
						<?php echo esc_html( $label ); ?>
						<span class="n"><?php echo (int) $aantal[ $sleutel ]; ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="rail">
				<?php foreach ( $cases as $slug => $case ) : ?>
					<a class="c" data-soort="<?php echo esc_attr( $case[2] ); ?>"
						href="<?php echo esc_url( home_url( '/' . $slug . '/' ) ); ?>">
						<span class="im">
							<img src="<?php echo esc_url( $case[1] ); ?>"
								alt="<?php echo esc_attr( $case[0] ); ?>"
								loading="lazy" decoding="async" width="570" height="894">
						</span>
						<?php /* De merknaam staat al in de foto; hieronder alleen de verwijzing. */ ?>
						<span class="nm">view case
							<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
								stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>

			<a class="all" href="<?php echo esc_url( home_url( '/cases_new/' ) ); ?>">view all work</a>
		</div>
	</section>
	<script>
	(function () {
		var sectie = document.currentScript.previousElementSibling;
		if ( ! sectie || ! sectie.classList.contains( 'dtc-oc' ) ) { return; }
		sectie.classList.add( 'heeft-js' );

		function toon( soort ) {
			sectie.querySelectorAll( '.tab' ).forEach( function ( knop ) {
				var aan = knop.dataset.soort === soort;
				knop.classList.toggle( 'on', aan );
				knop.setAttribute( 'aria-selected', aan ? 'true' : 'false' );
			} );
			sectie.querySelectorAll( '.rail .c' ).forEach( function ( kaart ) {
				kaart.hidden = kaart.dataset.soort !== soort;
			} );
			// terug naar het begin, anders sta je middenin de vorige groep
			var rail = sectie.querySelector( '.rail' );
			if ( rail ) { rail.scrollLeft = 0; }
		}

		sectie.querySelectorAll( '.tab' ).forEach( function ( knop ) {
			knop.addEventListener( 'click', function () { toon( knop.dataset.soort ); } );
		} );
		toon( sectie.dataset.open );
	})();
	</script>
	<?php
	return ob_get_clean();
}

/**
 * De strook staat onderaan de pagina-inhoud, dus na de laatste Divi-sectie en
 * voor de footer.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$post = get_post();
	if ( ! dtc_cases_is_case( $post ) ) {
		return $content;
	}
	return $content . dtc_cases_strip( $post->post_name );
}, 20 );

/* -------------------------------------------------------------------------
 * Opmaak
 * ---------------------------------------------------------------------- */

add_action( 'wp_head', function () {
	if ( ! is_singular( 'page' ) || ! dtc_cases_is_case( get_post() ) ) {
		return;
	}
	$css = "
	.dtc-oc{font-family:'Arboria',sans-serif;color:#2d2824;background:#fff;padding:0 0 70px;overflow:hidden}
	.dtc-oc *{margin:0;padding:0;box-sizing:border-box}
	.dtc-oc .in{width:76%;max-width:100%;margin:0 auto}
	.dtc-oc h2{font-weight:500;font-size:clamp(34px,5vw,64px);line-height:1;letter-spacing:-.02em;margin-bottom:28px;text-transform:lowercase}
	/* Zelfde pillen als de themakeuze in de hub, zodat het een bekend element is. */
	.dtc-oc .tabs{display:none;flex-wrap:wrap;gap:8px;margin:0 0 34px;padding:6px;background:#f2ede5;border:1px solid #e5dccd;border-radius:999px;width:fit-content;max-width:100%}
	.dtc-oc.heeft-js .tabs{display:flex}
	.dtc-oc .tab{display:inline-flex;align-items:center;gap:9px;padding:11px 24px;border:0;border-radius:999px;background:none;cursor:pointer;font:inherit;font-weight:500;font-size:16px;color:#4a443e;text-transform:lowercase;transition:background .2s,color .2s}
	.dtc-oc .tab:hover{background:#fff;color:#231d1c}
	.dtc-oc .tab.on{background:#231d1c;color:#fff}
	.dtc-oc .tab .n{font-size:12px;opacity:.6}
	.dtc-oc .c[hidden]{display:none}
	/* De rij schuift horizontaal; zo passen alle cases erin zonder dat de
	   kaarten smal worden op een klein scherm. */
	.dtc-oc .rail{display:flex;gap:24px;overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:14px;-webkit-overflow-scrolling:touch}
	.dtc-oc .rail::-webkit-scrollbar{height:4px}
	.dtc-oc .rail::-webkit-scrollbar-thumb{background:#e5dccd;border-radius:4px}
	.dtc-oc .c{flex:0 0 300px;scroll-snap-align:start;text-decoration:none;color:inherit;display:block}
	.dtc-oc .im{display:block;overflow:hidden;border-radius:10px;background:#f2ede5}
	/* De beelden zijn 570x894; met diezelfde verhouding valt de merknaam die
	   in de foto staat niet weg. */
	.dtc-oc .im img{display:block;width:100%;height:auto;aspect-ratio:570/894;object-fit:cover;transition:transform .5s ease}
	.dtc-oc .c:hover .im img{transform:scale(1.03)}
	.dtc-oc .nm{display:inline-flex;align-items:center;gap:8px;margin-top:16px;font-weight:500;font-size:16px;color:#8a7257;text-transform:lowercase}
	.dtc-oc .c:hover .nm{color:#2d2824}
	.dtc-oc .all{display:inline-flex;align-items:center;margin-top:34px;padding:14px 34px;border-radius:999px;background:#2d2824;color:#fff;text-decoration:none;font-weight:500;font-size:16px;text-transform:lowercase;transition:background .2s}
	.dtc-oc .all:hover{background:#8a7257}
	/* De kaarten worden eerder smaller dan de houder breder wordt; dat zijn
	   twee verschillende breekpunten. */
	@media(max-width:900px){.dtc-oc .c{flex-basis:240px}}
	/* Zelfde breekpunt als .dtc-camp .wrap op de case-pagina zelf, anders
	   staat de strook tussen 721 en 900px breder dan de tekst erboven. */
	@media(max-width:720px){.dtc-oc .in{width:88%}}
	";
	echo '<style id="dtc-oc">' . preg_replace( '/\s+/', ' ', $css ) . '</style>';
}, 20 );
