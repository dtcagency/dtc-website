<?php
/**
 * Plugin Name: DTC menu
 * Description: Het hamburgermenu op telefoon en tablet vult het hele scherm. Het paneel schuift van boven in en de menu-items komen er kort na elkaar van links bij. De links komen uit het bestaande menu, dus wijzigingen daarin komen hier vanzelf in mee.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen; dan komt het uitklapmenu van
 * DiviFlash weer terug.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_footer', 'dtc_menu_opmaak', 12 );
function dtc_menu_opmaak() {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-menu">
@media (max-width: 980px) {
	/* het uitklappaneel van DiviFlash blijft dicht; wij nemen het over */
	html.dtc-menu-klaar .difl_advancedmenu .df-menu-wrap { display: none !important; }

	.dtc-menu {
		position: fixed;
		inset: 0;
		/* Het paneel dekt het hele scherm. Het stond eerder onder de
		   WordPress-balk van een ingelogde beheerder, maar dan bleef er een
		   strook pagina zichtbaar bovenin. De balk zelf ligt hoger in de
		   stapeling en blijft dus gewoon bruikbaar; alleen de inhoud van het
		   paneel schuift eronderdoor weg. */
		top: 0;
		z-index: 99990;
		display: flex;
		flex-direction: column;
		background: #2d2824;
		transform: translateY(-100%);
		transition: transform .42s cubic-bezier(.16, 1, .3, 1);
		overflow-y: auto;
		-webkit-overflow-scrolling: touch;
		visibility: hidden;
		font-family: 'Arboria', Helvetica, Arial, sans-serif;
	}
	html.dtc-menu-open .dtc-menu { transform: translateY(0); visibility: visible; }

	/* de balk bovenin het paneel: logo links, sluiten rechts */
	.dtc-menu__balk {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 26px 20px 0;
		flex: 0 0 auto;
	}
	/* logo en sluitknop onder de beheerdersbalk houden */
	body.admin-bar .dtc-menu__balk {
		padding-top: calc( 26px + var(--wp-admin--admin-bar--height, 46px) );
	}
	.dtc-menu__logo img { width: 104px; height: auto; display: block; filter: brightness(0) invert(1); }
	.dtc-menu__dicht {
		width: 46px; height: 46px;
		display: flex; align-items: center; justify-content: center;
		background: none; border: 0; padding: 0; cursor: pointer;
		color: #f4f1ec;
	}
	.dtc-menu__dicht svg { width: 24px; height: 24px; }

	.dtc-menu__lijst {
		list-style: none;
		margin: 0;
		padding: 40px 20px 0;
		flex: 0 0 auto;
	}
	.dtc-menu__lijst > li { margin: 0 0 26px; }

	/* de regel met de paginanaam en, als er onderliggende pagina's zijn,
	   het pijltje om ze uit te klappen */
	.dtc-menu__rij { display: flex; align-items: center; gap: 14px; }

	.dtc-menu__rij > a {
		display: block;
		font-size: clamp(32px, 9vw, 46px);
		font-weight: 500;
		line-height: 1.05;
		letter-spacing: -.01em;
		color: #f4f1ec;
		text-decoration: none;
		text-transform: lowercase;
	}
	.dtc-menu__rij > a:active { color: #c0ab94; }

	.dtc-menu__klap {
		flex: 0 0 auto;
		width: 38px; height: 38px;
		display: flex; align-items: center; justify-content: center;
		background: none; border: 0; padding: 0; cursor: pointer;
		color: #f4f1ec;
		transition: transform .3s ease;
	}
	.dtc-menu__klap svg { width: 20px; height: 20px; }
	.dtc-menu__lijst > li.open .dtc-menu__klap { transform: rotate(180deg); }

	/* De onderliggende pagina's zitten ingeklapt. Het raster van 0fr naar 1fr
	   laat de hoogte vloeiend meegroeien zonder dat we hem hoeven te meten. */
	.dtc-menu__subwrap {
		display: grid;
		grid-template-rows: 0fr;
		transition: grid-template-rows .34s cubic-bezier(.4, 0, .2, 1);
	}
	.dtc-menu__lijst > li.open .dtc-menu__subwrap { grid-template-rows: 1fr; }

	.dtc-menu__sub {
		list-style: none;
		margin: 0;
		padding: 0;
		overflow: hidden;
		min-height: 0;
	}
	.dtc-menu__sub li:first-child { margin-top: 12px; }
	.dtc-menu__sub li { margin: 0 0 8px; }
	.dtc-menu__sub a {
		font-size: 17px;
		font-weight: 400;
		line-height: 1.7;
		color: #fff;
		text-decoration: none;
		text-transform: lowercase;
	}

	/* de knop staat direct onder het laatste menu-item en loopt over de
	   volle breedte van de kolom */
	.dtc-menu__voet { flex: 0 0 auto; padding: 10px 20px 40px; }
	.dtc-menu__voet a {
		display: block;
		width: 100%;
		box-sizing: border-box;
		text-align: center;
		text-transform: lowercase;
		background: #c0ab94;
		color: #2d2824;
		font-size: 17px;
		font-weight: 500;
		line-height: 1.7;
		text-decoration: none;
		border-radius: 100px;
		padding: 14px 30px;
	}

	/* de sociale kanalen onderin, gecentreerd */
	.dtc-menu__social {
		flex: 0 0 auto;
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 26px;
		padding: 6px 20px 38px;
		margin-top: auto;
	}
	.dtc-menu__social a {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 44px; height: 44px;
		color: #fff;
	}
	.dtc-menu__social svg { width: 23px; height: 23px; }
	.dtc-menu__social a:active { color: #c0ab94; }

	/* de items komen kort na elkaar van links in beeld */
	@keyframes dtc-menu-in {
		from { opacity: 0; transform: translateX(-44px); }
		to   { opacity: 1; transform: translateX(0); }
	}
	.dtc-menu__lijst > li,
	.dtc-menu__voet,
	.dtc-menu__social { opacity: 0; }
	html.dtc-menu-open .dtc-menu__lijst > li,
	html.dtc-menu-open .dtc-menu__voet,
	html.dtc-menu-open .dtc-menu__social {
		animation: dtc-menu-in .55s cubic-bezier(.34, 1.56, .64, 1) both;
		animation-delay: var(--dtc-stap, 0ms);
	}

	/* zolang het menu open staat schuift de pagina eronder niet mee */
	html.dtc-menu-open, html.dtc-menu-open body { overflow: hidden; }

	@media (prefers-reduced-motion: reduce) {
		.dtc-menu { transition: none; }
		html.dtc-menu-open .dtc-menu__lijst > li,
		html.dtc-menu-open .dtc-menu__voet,
		html.dtc-menu-open .dtc-menu__social { animation: none; opacity: 1; }
	}
}

/* op desktop bestaat het paneel niet */
@media (min-width: 981px) { .dtc-menu { display: none !important; } }
</style>
	<?php
}

add_action( 'wp_footer', 'dtc_menu_script', 13 );
function dtc_menu_script() {
	if ( is_admin() ) { return; }
	?>
<script id="dtc-menu-js">
( function () {
	var wortel = document.documentElement;
	var smal   = window.matchMedia( '(max-width: 980px)' );

	function bouw() {
		if ( document.querySelector( '.dtc-menu' ) ) { return document.querySelector( '.dtc-menu' ); }

		var bron = document.querySelector( '.difl_advancedmenu .df-menu-nav-level-0' );
		if ( ! bron ) { return null; }

		var paneel = document.createElement( 'div' );
		paneel.className = 'dtc-menu';
		paneel.setAttribute( 'role', 'dialog' );
		paneel.setAttribute( 'aria-modal', 'true' );
		paneel.setAttribute( 'aria-label', 'menu' );
		paneel.hidden = false;

		/* balk met logo en sluitknop */
		var balk = document.createElement( 'div' );
		balk.className = 'dtc-menu__balk';

		var logoBron = document.querySelector( '.difl_advancedmenu img, .et-l--header img' );
		if ( logoBron ) {
			var logoLink = document.createElement( 'a' );
			logoLink.className = 'dtc-menu__logo';
			logoLink.href = '/';
			var logo = document.createElement( 'img' );
			logo.src = logoBron.getAttribute( 'src' );
			logo.alt = 'dtc';
			logoLink.appendChild( logo );
			balk.appendChild( logoLink );
		} else {
			balk.appendChild( document.createElement( 'span' ) );
		}

		var dicht = document.createElement( 'button' );
		dicht.type = 'button';
		dicht.className = 'dtc-menu__dicht';
		dicht.setAttribute( 'aria-label', 'menu sluiten' );
		dicht.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line></svg>';
		dicht.addEventListener( 'click', function () { zet( false ); } );
		balk.appendChild( dicht );
		paneel.appendChild( balk );

		/* de links overnemen uit het bestaande menu */
		var lijst = document.createElement( 'ul' );
		lijst.className = 'dtc-menu__lijst';

		[].slice.call( bron.children ).forEach( function ( li ) {
			var a = li.querySelector( ':scope > a' );
			if ( ! a ) { return; }
			var tekst = ( a.textContent || '' ).trim();
			if ( ! tekst ) { return; }   // het logo-item heeft geen tekst

			var nieuw = document.createElement( 'li' );

			var rij = document.createElement( 'div' );
			rij.className = 'dtc-menu__rij';
			var link = document.createElement( 'a' );
			link.href = a.getAttribute( 'href' ) || '#';
			link.textContent = tekst;
			rij.appendChild( link );
			nieuw.appendChild( rij );

			var sub = li.querySelector( ':scope > ul' );
			if ( sub ) {
				var subLijst = document.createElement( 'ul' );
				subLijst.className = 'dtc-menu__sub';
				[].slice.call( sub.querySelectorAll( 'a' ) ).forEach( function ( sa ) {
					var st = ( sa.textContent || '' ).trim();
					if ( ! st ) { return; }
					var sli = document.createElement( 'li' );
					var sl = document.createElement( 'a' );
					sl.href = sa.getAttribute( 'href' ) || '#';
					sl.textContent = st;
					sli.appendChild( sl );
					subLijst.appendChild( sli );
				} );
				if ( subLijst.children.length ) {
					/* het pijltje klapt uit; de naam zelf blijft gewoon een link
					   naar die pagina, dus niemand raakt vast in een tak */
					var klap = document.createElement( 'button' );
					klap.type = 'button';
					klap.className = 'dtc-menu__klap';
					klap.setAttribute( 'aria-expanded', 'false' );
					klap.setAttribute( 'aria-label', tekst + ' uitklappen' );
					klap.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>';
					klap.addEventListener( 'click', function ( e ) {
						e.preventDefault();
						e.stopPropagation();
						var open = nieuw.classList.toggle( 'open' );
						klap.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
						klap.setAttribute( 'aria-label', tekst + ( open ? ' inklappen' : ' uitklappen' ) );
					} );
					rij.appendChild( klap );

					var wrap = document.createElement( 'div' );
					wrap.className = 'dtc-menu__subwrap';
					wrap.appendChild( subLijst );
					nieuw.appendChild( wrap );
				}
			}
			lijst.appendChild( nieuw );
		} );
		paneel.appendChild( lijst );

		/* de knop uit de menubalk onderaan herhalen */
		/* op de tekst zoeken, niet op de link: er staan meer verwijzingen naar
		   contact in de balk (zoals "book a demo") en we willen deze ene */
		var praat = [].slice.call( document.querySelectorAll( '.et-l--header a' ) ).filter( function ( a ) {
			return /let.s talk/i.test( ( a.textContent || '' ).trim() );
		} )[0];
		if ( praat ) {
			var voet = document.createElement( 'div' );
			voet.className = 'dtc-menu__voet';
			var pl = document.createElement( 'a' );
			pl.href = praat.getAttribute( 'href' );
			pl.textContent = ( praat.textContent || "let's talk" ).trim().slice( 0, 24 );
			voet.appendChild( pl );
			paneel.appendChild( voet );
		}

		/* de sociale kanalen uit de voettekst overnemen, zodat ze op één plek
		   worden bijgehouden */
		var merken = [
			{ zoek: 'linkedin',  naam: 'LinkedIn',  pad: '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle>' },
			{ zoek: 'instagram', naam: 'Instagram', pad: '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>' },
			{ zoek: 'tiktok',    naam: 'TikTok',    pad: '<path d="M14 4v9.8a3.7 3.7 0 1 1-3.7-3.7c.36 0 .71.05 1.04.15"></path><path d="M14 4.2c.55 2.3 2.35 4 4.7 4.2"></path>' }
		];

		var social = document.createElement( 'div' );
		social.className = 'dtc-menu__social';

		merken.forEach( function ( merk ) {
			var bron = [].slice.call( document.querySelectorAll( '.et-l--footer a[href], .et-l--header a[href]' ) ).filter( function ( a ) {
				return ( a.getAttribute( 'href' ) || '' ).toLowerCase().indexOf( merk.zoek ) > -1;
			} )[0];
			if ( ! bron ) { return; }
			var a = document.createElement( 'a' );
			a.href = bron.getAttribute( 'href' );
			a.target = '_blank';
			a.rel = 'noopener';
			a.setAttribute( 'aria-label', merk.naam );
			a.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + merk.pad + '</svg>';
			social.appendChild( a );
		} );

		if ( social.children.length ) { paneel.appendChild( social ); }

		/* elke regel krijgt zijn eigen vertraging, zodat ze na elkaar binnenkomen */
		var stap = 0;
		[].slice.call( lijst.children ).forEach( function ( li ) {
			li.style.setProperty( '--dtc-stap', ( 80 + stap * 60 ) + 'ms' );
			stap++;
		} );
		var voetEl = paneel.querySelector( '.dtc-menu__voet' );
		if ( voetEl ) { voetEl.style.setProperty( '--dtc-stap', ( 80 + stap * 60 ) + 'ms' ); stap++; }
		if ( social.children.length ) { social.style.setProperty( '--dtc-stap', ( 80 + stap * 60 ) + 'ms' ); }

		/* een link volgen betekent het menu sluiten */
		paneel.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) ) { zet( false ); }
		} );

		document.body.appendChild( paneel );
		wortel.classList.add( 'dtc-menu-klaar' );
		return paneel;
	}

	var scrollStand = 0;

	function zet( open ) {
		if ( open ) {
			if ( ! bouw() ) { return; }
			scrollStand = window.scrollY;
			wortel.classList.add( 'dtc-menu-open' );
		} else {
			wortel.classList.remove( 'dtc-menu-open' );
			window.scrollTo( 0, scrollStand );
		}
		var knop = document.querySelector( '.df-mobile-menu-button' );
		if ( knop ) { knop.setAttribute( 'aria-expanded', open ? 'true' : 'false' ); }
	}

	/* De hamburger van DiviFlash blijft de knop; we vangen de klik af voordat
	   hun eigen uitklapmenu erop reageert. */
	document.addEventListener( 'click', function ( e ) {
		var knop = e.target.closest( '.df-mobile-menu-button' );
		if ( ! knop || ! smal.matches ) { return; }
		e.preventDefault();
		e.stopPropagation();
		zet( ! wortel.classList.contains( 'dtc-menu-open' ) );
	}, true );

	addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && wortel.classList.contains( 'dtc-menu-open' ) ) { zet( false ); }
	} );

	/* bij draaien naar desktop hoort het paneel dicht te zijn */
	if ( smal.addEventListener ) {
		smal.addEventListener( 'change', function () { if ( ! smal.matches ) { zet( false ); } } );
	}

	/* het paneel alvast klaarzetten zodat de eerste tik meteen soepel opent */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { if ( smal.matches ) { bouw(); } } );
	} else if ( smal.matches ) {
		bouw();
	}
} )();
</script>
	<?php
}
