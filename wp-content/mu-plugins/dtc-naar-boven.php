<?php
/**
 * Plugin Name: DTC Naar boven
 * Description: Knop rechtsonder om terug naar de bovenkant van de pagina te gaan.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * De knop verschijnt pas als er iets te scrollen valt, en blijft weg in de
 * beheeromgeving en de Divi-bouwer - daar zit hij alleen in de weg.
 *
 * Hij staat rechtsonder. De cookiemelding gebruikt de linkerkant en zijn
 * eigen knop staat op een hogere laag, dus die twee bijten elkaar niet.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() || isset( $_GET['et_fb'] ) ) {
		return;
	}
	?>
	<button type="button" class="dtc-top" aria-label="back to top" hidden>
		<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
			stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<line x1="12" y1="19" x2="12" y2="5"></line>
			<polyline points="5 12 12 5 19 12"></polyline>
		</svg>
	</button>
	<style>
	.dtc-top{
		position:fixed;right:28px;bottom:28px;z-index:9000;
		width:52px;height:52px;padding:0;border:0;border-radius:50%;
		background:#2d2824;color:#c0ab94;cursor:pointer;
		display:flex;align-items:center;justify-content:center;
		box-shadow:0 6px 20px rgba(0,0,0,.18);
		opacity:0;transform:translateY(10px);
		transition:opacity .25s ease,transform .25s ease,background .2s ease,color .2s ease;
	}
	.dtc-top[hidden]{display:none}
	.dtc-top.zichtbaar{opacity:1;transform:translateY(0)}
	.dtc-top:hover{background:#8a7257;color:#fff}
	.dtc-top:focus-visible{outline:2px solid #8a7257;outline-offset:3px}
	@media(max-width:720px){.dtc-top{right:16px;bottom:16px;width:46px;height:46px}}
	@media print{.dtc-top{display:none}}
	@media(prefers-reduced-motion:reduce){
		.dtc-top{transition:opacity .01s}
	}
	</style>
	<script>
	(function () {
		var knop = document.querySelector('.dtc-top');
		if (!knop) { return; }

		// Op een korte pagina valt er niets te doen.
		function relevant() {
			return document.documentElement.scrollHeight - window.innerHeight > 800;
		}

		function toon() {
			if (!relevant()) {
				knop.hidden = true;
				return;
			}
			knop.hidden = false;
			knop.classList.toggle('zichtbaar', window.scrollY > 500);
		}

		var wacht = false;
		window.addEventListener('scroll', function () {
			if (wacht) { return; }
			wacht = true;
			window.requestAnimationFrame(function () { toon(); wacht = false; });
		}, { passive: true });
		window.addEventListener('resize', toon, { passive: true });

		knop.addEventListener('click', function () {
			var zacht = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: zacht ? 'smooth' : 'auto' });
			// focus terug naar het begin van de pagina, voor toetsenbordgebruik
			var eerste = document.querySelector('#main-content, main, body');
			if (eerste) {
				eerste.setAttribute('tabindex', '-1');
				eerste.focus({ preventScroll: true });
			}
		});

		toon();
	})();
	</script>
	<?php
}, 30 );
