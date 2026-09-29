<?php
/**
 * 404-pagina in de dtc-huisstijl.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<style>
.dtc-404{font-family:'Arboria',sans-serif;color:#2d2824;background-color:#fff;
	background-image:url('/wp-content/uploads/2025/10/Achtergrond-patroon-30.svg');
	background-repeat:no-repeat;background-position:center 40px;background-size:min(1500px,130%) auto;
	padding:clamp(120px,12vw,180px) 0 70px;overflow-x:hidden}
.dtc-404 .in{width:76%;max-width:100%;margin:0 auto;position:relative}
.dtc-404 .wm{position:absolute;right:0;top:-70px;font-weight:500;font-size:clamp(180px,26vw,380px);
	line-height:1;color:#efedea;z-index:0;user-select:none;pointer-events:none}
.dtc-404 .body{position:relative;z-index:1}
.dtc-404 .kick{display:block;font-size:14px;font-weight:600;color:#8a7257;text-transform:lowercase;margin-bottom:16px}
.dtc-404 h1{font-weight:500;font-size:clamp(40px,7vw,96px);line-height:.95;margin:0 0 26px;text-transform:lowercase}
.dtc-404 h1 span{color:#c0ab94}
.dtc-404 .lead{font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;max-width:560px;margin:0 0 34px}

/* wat bedoelde je waarschijnlijk */
.dtc-404 .tip{font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;margin:0 0 30px}
.dtc-404 .tip a{color:#8a7257;font-weight:500;text-decoration:underline;text-underline-offset:3px}
.dtc-404 .tip a:hover{color:#2d2824}

/* zoeken */
.dtc-404 form.zoek{display:flex;gap:10px;max-width:520px;margin:0 0 56px}
.dtc-404 form.zoek input[type=search]{
	flex:1 1 auto;min-width:0;font-family:inherit;font-size:16px;font-weight:300;color:#2d2824;
	background:#faf8f5;border:1px solid #ddd3c4;border-radius:12px;padding:13px 16px;
	-webkit-appearance:none;appearance:none;
}
.dtc-404 form.zoek input[type=search]:focus{border-color:#c0ab94;box-shadow:0 0 0 3px rgba(45,40,36,.06);outline:none}
.dtc-404 form.zoek button{
	flex:0 0 auto;font-family:inherit;font-size:16px;font-weight:500;line-height:1.7;color:#fff;
	background:#2d2824;border:0;border-radius:100px;padding:10px 28px;cursor:pointer;
	transition:background .25s,color .25s;
}
.dtc-404 form.zoek button:hover{background:#c0ab94;color:#2d2824}
.dtc-404 .routes{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:0;
	border-top:1px solid #ddd3c4}
.dtc-404 .route{display:block;text-decoration:none;color:inherit;padding:26px 30px 26px 0;
	border-bottom:1px solid #ddd3c4;transition:padding-left .2s ease}
.dtc-404 .route:hover{padding-left:12px}
.dtc-404 .route b{display:flex;align-items:center;gap:10px;font-weight:500;font-size:24px;
	line-height:1.15;margin-bottom:8px;text-transform:lowercase}
.dtc-404 .route svg{color:#c0ab94;opacity:0;transform:translateX(-6px);
	transition:opacity .2s ease,transform .2s ease;flex:0 0 auto}
.dtc-404 .route:hover svg{opacity:1;transform:translateX(0)}
.dtc-404 .route span{display:block;font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;max-width:330px}
.dtc-404 .back{display:inline-block;margin-top:44px;font-size:16px;color:#8a7257;text-decoration:underline;
	text-underline-offset:3px}
@media(max-width:900px){.dtc-404{padding:14vh 0 70px}.dtc-404 .in{width:88%}
	.dtc-404 .wm{top:-40px;right:0;font-size:clamp(130px,34vw,220px)}
	.dtc-404 form.zoek{flex-wrap:wrap}
	.dtc-404 form.zoek button{width:100%}}
</style>

<div class="dtc-404">
	<div class="in">
		<span class="wm" aria-hidden="true">404</span>
		<div class="body">
			<span class="kick">page not found</span>
			<h1>this page took<br /><span>a different route.</span></h1>
			<p class="lead">It may have moved, or the link that brought you here is out of date.
				Nothing dramatic &mdash; here is where most people were heading.</p>

			<?php $tip = dtc_404_bedoelde_je(); ?>
			<?php if ( $tip ) : ?>
				<p class="tip">Did you mean <a href="<?php echo esc_url( $tip[1] ); ?>"><?php echo esc_html( $tip[0] ); ?></a>?</p>
			<?php endif; ?>

			<form class="zoek" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="dtc-404-zoek">Search this site</label>
				<input type="search" id="dtc-404-zoek" name="s" placeholder="Search the site&hellip;"
				       value="<?php echo esc_attr( get_search_query() ); ?>">
				<button type="submit">search</button>
			</form>

			<div class="routes">
				<?php foreach ( dtc_404_routes() as $route ) : ?>
					<a class="route" href="<?php echo esc_url( home_url( $route[2] ) ); ?>">
						<b><?php echo wp_kses_post( $route[0] ); ?>
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
								stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg>
						</b>
						<span><?php echo wp_kses_post( $route[1] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>">back to the homepage</a>
		</div>
	</div>
</div>
<?php
get_footer();
