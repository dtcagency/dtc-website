<?php
/**
 * Plugin Name: DTC inlogpagina
 * Description: Geeft het inlogscherm van WordPress de huisstijl van dtc /always on. Alleen opmaak: de werking van het formulier, wachtwoord-vergeten en eventuele extra beveiliging blijft zoals WordPress het doet.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_LOGIN_OPTIE = 'dtc_login_opties';

/** de instellingen, met de standaardwaarden waar nog niets is gekozen */
function dtc_login_opties() {
	$standaard = array(
		'logo'        => '/wp-content/uploads/2025/10/logo.svg',
		'foto'        => '/wp-content/uploads/2026/01/SVM_DTCALWAYSON_130126_0553.jpg',
		'zin'         => 'always on.',
		'welkom'      => 'Welkom terug. Log in om de site bij te werken.',
		'vergeten'    => 'Vul je e-mailadres in, dan sturen we een link om een nieuw wachtwoord te kiezen.',
		'paneel'      => '#2d2824',
		'achtergrond' => '#f4f1ec',
		'accent'      => '#c0ab94',
		'tekst'       => '#2d2824',
		'breedte'     => 42,
		'aan'         => 1,
	);
	$bewaard = get_option( DTC_LOGIN_OPTIE );
	if ( ! is_array( $bewaard ) ) { $bewaard = array(); }
	return array_merge( $standaard, $bewaard );
}

/** een kleur alleen doorlaten als het echt een hex-kleur is */
function dtc_login_kleur( $waarde, $terugval ) {
	return preg_match( '/^#[0-9a-fA-F]{3,8}$/', (string) $waarde ) ? $waarde : $terugval;
}

/* het logo linkt naar de site zelf in plaats van naar wordpress.org */
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );

/* een eigen tekst boven het wachtwoord-vergeten-scherm laten staan zoals hij is,
   maar de standaardmelding vriendelijker maken */
add_filter( 'login_message', function ( $bericht ) {
	$o = dtc_login_opties();
	if ( ! $o['aan'] ) { return $bericht; }
	$actie = isset( $_GET['action'] ) ? $_GET['action'] : 'login';

	/* Op het wachtwoord-vergeten-scherm zet WordPress zelf al een uitleg neer.
	   Die vervangen we door de eigen tekst uit de instellingen. */
	if ( 'lostpassword' === $actie ) {
		$tekst = trim( (string) $o['vergeten'] );
		return '' === $tekst ? $bericht : '<p class="dtc-login-intro">' . esc_html( $tekst ) . '</p>';
	}

	/* Op de andere schermen alleen invullen waar nog niets staat, zodat echte
	   meldingen (zoals 'je wachtwoord is gewijzigd') blijven staan. */
	if ( '' !== $bericht ) { return $bericht; }
	$tekst = trim( (string) $o['welkom'] );
	return '' === $tekst ? $bericht : '<p class="dtc-login-intro">' . esc_html( $tekst ) . '</p>';
} );

add_action( 'login_enqueue_scripts', 'dtc_login_opmaak' );
function dtc_login_opmaak() {
	$o = dtc_login_opties();
	if ( ! $o['aan'] ) { return; }
	$logo    = esc_url( $o['logo'] );
	$foto    = esc_url( $o['foto'] );
	$zin     = esc_html( $o['zin'] );
	$paneel  = dtc_login_kleur( $o['paneel'], '#2d2824' );
	$achtergrond = dtc_login_kleur( $o['achtergrond'], '#f4f1ec' );
	$accent  = dtc_login_kleur( $o['accent'], '#c0ab94' );
	$tekst   = dtc_login_kleur( $o['tekst'], '#2d2824' );
	$breedte = max( 20, min( 60, (int) $o['breedte'] ) );
	?>
<style id="dtc-login">
@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Medium.woff2') format('woff2');font-weight:500;font-display:swap}
@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Light.woff2') format('woff2');font-weight:300;font-display:swap}
@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Book.woff2') format('woff2');font-weight:400;font-display:swap}

html{background:<?php echo $achtergrond; ?>}
body.login{
	--band:210px;
	font-family:'Arboria',Helvetica,Arial,sans-serif;
	background:<?php echo $achtergrond; ?>;
	color:<?php echo $tekst; ?>;
	min-height:100vh;
	margin:0;
	/* Twee kolommen: links de foto over de volle hoogte, rechts het formulier
	   in het midden met de taalkiezer rustig onderaan. De taalkiezer staat in
	   de opmaak van WordPress naast het formulier; zo blijft hij in de kolom. */
	display:grid;
	grid-template-columns:<?php echo $breedte; ?>% 1fr;
	grid-template-rows:1fr auto;
}
body.login > *{grid-column:2}
body.login #login{
	grid-column:2;
	grid-row:1;
	justify-self:center;
	align-self:center;
	width:min(420px,92vw);
	margin:0;
	padding:40px 0;
}

/* ---------- het fotopaneel links ---------- */
/* Geen blend-mode meer: die maakte de foto bijna zwart. Een verloop van
   boven licht naar onder donker houdt het kantoor zichtbaar en de regel
   onderin leesbaar. */
body.login::before{
	content:'';
	grid-column:1;
	grid-row:1 / span 2;
	min-height:100vh;
	background-color:<?php echo $paneel; ?>;
	background-image:linear-gradient(180deg,rgba(0,0,0,.04) 0%,rgba(0,0,0,.16) 42%,rgba(0,0,0,.62) 100%),url('<?php echo $foto; ?>');
	background-size:cover,cover;
	background-position:center,center 42%;
	background-repeat:no-repeat,no-repeat;
}
body.login::after{
	content:'<?php echo $zin; ?>';
	position:fixed;
	left:clamp(24px,4vw,70px);
	bottom:clamp(28px,4vw,56px);
	color:#fff;
	font-weight:500;
	font-size:clamp(26px,2.6vw,42px);
	line-height:.95;
	letter-spacing:-.01em;
	pointer-events:none;
}

/* ---------- het logo ---------- */
body.login h1{margin:0 0 22px}
/* 'contain' in plaats van een vaste breedte: zo past elk logo helemaal in het
   vak, ook als er later een ander bestand met een andere verhouding wordt
   gekozen. Het vak heeft de verhouding van het dtc-logo (484 x 255). */
body.login h1 a{
	background-image:url('<?php echo $logo; ?>')!important;
	background-size:contain!important;
	background-repeat:no-repeat!important;
	background-position:left center!important;
	width:150px!important;
	height:79px!important;
	margin:0!important;
}

/* ---------- het formulier ---------- */
/* Alleen de echte inlogformulieren krijgen de witte kaart. De taalkiezer is
   ook een <form> en kreeg hem eerder ten onrechte mee. */
body.login #loginform,
body.login #lostpasswordform,
body.login #registerform,
body.login #resetpassform{
	background:#fff;
	border:1px solid #e8e0d4;
	border-radius:18px;
	box-shadow:none;
	padding:28px 26px 24px;
	margin-top:0;
}
body.login label{
	font-size:13px;
	font-weight:500;
	color:#6b6258;
	letter-spacing:.02em;
}
body.login input[type=text],
body.login input[type=password],
body.login input[type=email]{
	font-family:inherit;
	font-size:16px;
	color:#2d2824;
	background:#faf8f5;
	border:1px solid #ddd3c4;
	border-radius:10px;
	padding:12px 14px;
	box-shadow:none;
	margin:6px 0 18px;
}
body.login input[type=text]:focus,
body.login input[type=password]:focus,
body.login input[type=email]:focus{
	border-color:<?php echo $accent; ?>;
	box-shadow:0 0 0 3px rgba(0,0,0,.06);
	outline:none;
}
body.login .wp-pwd .button.wp-hide-pw{color:#8a7257;top:2px}

/* het vinkje 'onthoud mij' in de stijl van de site */
body.login .forgetmenot{margin:0;padding-top:13px}
body.login .forgetmenot label{
	display:inline-flex;
	align-items:center;
	font-size:13px;
	color:#6b6258;
}
body.login .submit{margin:0}
body.login input[type=checkbox]{
	-webkit-appearance:none;
	appearance:none;
	width:18px;
	height:18px;
	min-width:18px;
	border:1px solid #ddd3c4;
	border-radius:5px;
	background:#faf8f5;
	box-shadow:none;
	margin:0 8px 0 0;
	transition:background .2s,border-color .2s;
}
body.login input[type=checkbox]:checked{background:<?php echo $tekst; ?>;border-color:<?php echo $tekst; ?>}
body.login input[type=checkbox]:checked::before{
	content:'';
	display:block;
	width:100%;
	height:100%;
	margin:0;
	background:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' d='M3.4 8.4l3.1 3.1 6.1-6.5'/%3E%3C/svg%3E") center/12px no-repeat;
}
body.login input[type=checkbox]:focus{border-color:<?php echo $accent; ?>;box-shadow:0 0 0 3px rgba(0,0,0,.06);outline:none}

/* het vraagtekentje met de uitleg bij 'onthoud mij' */
body.login .wp-tooltip{display:inline-flex;align-items:center}
body.login .wp-tooltip__toggle{
	background:none;
	border:0;
	box-shadow:none;
	padding:0;
	margin:0 0 0 2px;
	color:#a99a86;
	line-height:1;
	cursor:help;
}
body.login .wp-tooltip__toggle:hover{color:#8a7257}
body.login .wp-tooltip__toggle .dashicons{font-size:17px;width:17px;height:17px;line-height:1}

/* de knop in de stijl van de site: donkere pil */
body.login #wp-submit{
	font-family:inherit;
	font-weight:500;
	font-size:16px;
	line-height:1.7;
	color:#fff;
	background:<?php echo $tekst; ?>;
	border:none;
	border-radius:100px;
	padding:10px 30px;
	text-shadow:none;
	box-shadow:none;
	transition:background .25s,color .25s;
}
body.login #wp-submit:hover,
body.login #wp-submit:focus{background:<?php echo $accent; ?>;color:<?php echo $tekst; ?>}

/* ---------- meldingen en links ---------- */
body.login .dtc-login-intro{
	font-weight:300;
	font-size:16px;
	line-height:1.7;
	color:#6b6258;
	background:none;
	border:0;
	box-shadow:none;
	padding:0;
	margin:0 0 20px;
}
body.login #login_error,
body.login .message,
body.login .success{
	border-left:4px solid <?php echo $accent; ?>;
	background:#fff;
	border-radius:10px;
	box-shadow:none;
	font-size:14px;
	line-height:1.6;
	color:#2d2824;
}
body.login #login_error{border-left-color:#b4492f}
body.login #login_error a,
body.login .message a,
body.login .success a{
	color:#8a7257;
	text-decoration:underline;
	text-underline-offset:2px;
}
body.login #login_error a:hover,
body.login .message a:hover{color:#2d2824}
body.login #nav,
body.login #backtoblog{
	padding:0;
	margin:14px 0 0;
	font-size:14px;
}
body.login #nav a,
body.login #backtoblog a{
	color:#8a7257;
	text-decoration:none;
	border-bottom:1px solid transparent;
	transition:border-color .2s;
}
body.login #nav a:hover,
body.login #backtoblog a:hover{border-bottom-color:#8a7257;color:#2d2824}
body.login .privacy-policy-page-link{display:none}

/* ---------- de taalkiezer ---------- */
/* Hij hoort er te blijven voor wie de site in een andere taal beheert, maar
   rustig, op een regel, onder de links. */
body.login .language-switcher{
	grid-column:2;
	grid-row:2;
	justify-self:center;
	width:min(420px,92vw);
	margin:0 0 30px;
	font-size:13px;
}
body.login form#language-switcher{
	background:none;
	border:0;
	border-radius:0;
	box-shadow:none;
	padding:0;
	margin:0;
	display:flex;
	align-items:center;
	gap:8px;
	flex-wrap:wrap;
}
body.login .language-switcher label{
	display:inline-flex;
	align-items:center;
	gap:5px;
	margin:0;
	font-size:13px;
	font-weight:400;
	color:#a99a86;
	letter-spacing:0;
}
body.login .language-switcher label .dashicons{font-size:16px;width:16px;height:16px;line-height:1}
body.login .language-switcher select{
	font-family:inherit;
	font-size:13px;
	color:#6b6258;
	background:#fff;
	border:1px solid #e8e0d4;
	border-radius:8px;
	padding:5px 8px;
	height:auto;
	min-height:0;
	box-shadow:none;
}
body.login .language-switcher .button{
	margin:0;
	font-family:inherit;
	font-size:13px;
	font-weight:500;
	color:#6b6258;
	background:none;
	border:1px solid #e8e0d4;
	border-radius:999px;
	padding:4px 14px;
	height:auto;
	min-height:0;
	line-height:1.6;
	box-shadow:none;
	text-shadow:none;
}
body.login .language-switcher .button:hover{border-color:#8a7257;color:#2d2824}

/* ---------- telefoon en tablet ---------- */
@media (max-width:900px){
	body.login{display:block;background:<?php echo $achtergrond; ?>}
	body.login::before{
		display:block;
		width:100%;
		height:var(--band);
		min-height:0;
		background-position:center,center 26%;
	}
	body.login::after{
		position:absolute;
		top:calc(var(--band) - 58px);
		left:22px;
		bottom:auto;
		font-size:clamp(26px,8vw,34px);
	}
	body.login #login{width:min(420px,90vw);margin:0 auto;padding:28px 0 30px}
	body.login .language-switcher{width:min(420px,90vw);margin:0 auto 34px}
	body.login #loginform,
	body.login #lostpasswordform,
	body.login #registerform,
	body.login #resetpassform{padding:24px 20px 20px}
	body.login h1{margin:0 0 18px}
	body.login h1 a{width:130px!important;height:69px!important}
}
</style>
	<?php
}

/* ============================================================ instellingen */

add_action( 'admin_menu', function () {
	/* onder het dtc agency-menu, of als eigen menu als die plugin er niet is */
	if ( menu_page_url( DTC_TK_SLUG, false ) ) {
		add_submenu_page( DTC_TK_SLUG, 'Inlogpagina', 'Inlogpagina', 'manage_options', 'dtc-inlogpagina', 'dtc_login_scherm' );
	} else {
		add_menu_page( 'Inlogpagina', 'Inlogpagina', 'manage_options', 'dtc-inlogpagina', 'dtc_login_scherm', 'dashicons-admin-appearance', 4 );
	}
}, 11 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( false !== strpos( $hook, 'dtc-inlogpagina' ) ) { wp_enqueue_media(); }
} );

add_action( 'admin_post_dtc_login_opslaan', 'dtc_login_opslaan' );
function dtc_login_opslaan() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Geen rechten.' ); }
	check_admin_referer( 'dtc_login_opties' );

	$in = wp_unslash( $_POST );
	$nieuw = array(
		'logo'        => esc_url_raw( trim( (string) ( $in['logo'] ?? '' ) ) ),
		'foto'        => esc_url_raw( trim( (string) ( $in['foto'] ?? '' ) ) ),
		'zin'         => sanitize_text_field( (string) ( $in['zin'] ?? '' ) ),
		'welkom'      => sanitize_text_field( (string) ( $in['welkom'] ?? '' ) ),
		'vergeten'    => sanitize_text_field( (string) ( $in['vergeten'] ?? '' ) ),
		'paneel'      => dtc_login_kleur( $in['paneel'] ?? '', '#2d2824' ),
		'achtergrond' => dtc_login_kleur( $in['achtergrond'] ?? '', '#f4f1ec' ),
		'accent'      => dtc_login_kleur( $in['accent'] ?? '', '#c0ab94' ),
		'tekst'       => dtc_login_kleur( $in['tekst'] ?? '', '#2d2824' ),
		'breedte'     => max( 20, min( 60, (int) ( $in['breedte'] ?? 42 ) ) ),
		'aan'         => empty( $in['aan'] ) ? 0 : 1,
	);
	if ( '' === $nieuw['logo'] ) { unset( $nieuw['logo'] ); }
	if ( '' === $nieuw['foto'] ) { unset( $nieuw['foto'] ); }

	if ( isset( $in['herstel'] ) ) {
		delete_option( DTC_LOGIN_OPTIE );
		$melding = 'hersteld';
	} else {
		update_option( DTC_LOGIN_OPTIE, $nieuw, false );
		$melding = 'opgeslagen';
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'dtc-inlogpagina', 'dtc' => $melding ), admin_url( 'admin.php' ) ) );
	exit;
}

function dtc_login_scherm() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Geen rechten.' ); }
	$o = dtc_login_opties();
	?>
	<div class="wrap dtc-tk dtc-login-instel">
		<?php if ( function_exists( 'dtc_tk_titelbalk' ) ) : ?>
			<?php dtc_tk_titelbalk( 'Inlogpagina — logo, foto, teksten en kleuren van het scherm waar je inlogt.' ); ?>
		<?php else : ?>
			<h1>Inlogpagina</h1>
		<?php endif; ?>

		<?php if ( isset( $_GET['dtc'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php echo 'hersteld' === $_GET['dtc'] ? 'De standaardinstellingen zijn teruggezet.' : 'De instellingen zijn opgeslagen.'; ?>
			</p></div>
		<?php endif; ?>

		<p>Zo ziet het inlogscherm eruit voor iedereen die op <code><?php echo esc_html( wp_login_url() ); ?></code> uitkomt.
		<a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener">bekijk de inlogpagina</a> (open hem eventueel in een privévenster om hem als bezoeker te zien).</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dtc_login_opslaan">
			<?php wp_nonce_field( 'dtc_login_opties' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Huisstijl gebruiken</th>
					<td><label><input type="checkbox" name="aan" value="1" <?php checked( $o['aan'], 1 ); ?>> aan</label>
					<p class="description">Uit = het standaard inlogscherm van WordPress.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-logo">Logo</label></th>
					<td>
						<div class="dtc-login-beeld">
							<img src="<?php echo esc_url( $o['logo'] ); ?>" alt="" class="voorbeeld logo">
							<div>
								<input type="text" id="dtc-logo" name="logo" value="<?php echo esc_attr( $o['logo'] ); ?>" class="regular-text">
								<button type="button" class="button dtc-kies" data-doel="dtc-logo">Kiezen&hellip;</button>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-foto">Foto op het paneel</label></th>
					<td>
						<div class="dtc-login-beeld">
							<img src="<?php echo esc_url( $o['foto'] ); ?>" alt="" class="voorbeeld foto">
							<div>
								<input type="text" id="dtc-foto" name="foto" value="<?php echo esc_attr( $o['foto'] ); ?>" class="regular-text">
								<button type="button" class="button dtc-kies" data-doel="dtc-foto">Kiezen&hellip;</button>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-zin">Regel op het paneel</label></th>
					<td><input type="text" id="dtc-zin" name="zin" value="<?php echo esc_attr( $o['zin'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-welkom">Tekst boven het formulier</label></th>
					<td><input type="text" id="dtc-welkom" name="welkom" value="<?php echo esc_attr( $o['welkom'] ); ?>" class="large-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-vergeten">Tekst bij wachtwoord vergeten</label></th>
					<td><input type="text" id="dtc-vergeten" name="vergeten" value="<?php echo esc_attr( $o['vergeten'] ); ?>" class="large-text"></td>
				</tr>
				<tr>
					<th scope="row">Kleuren</th>
					<td class="dtc-login-kleuren">
						<label>paneel <input type="color" name="paneel" value="<?php echo esc_attr( $o['paneel'] ); ?>"></label>
						<label>achtergrond <input type="color" name="achtergrond" value="<?php echo esc_attr( $o['achtergrond'] ); ?>"></label>
						<label>accent <input type="color" name="accent" value="<?php echo esc_attr( $o['accent'] ); ?>"></label>
						<label>tekst en knop <input type="color" name="tekst" value="<?php echo esc_attr( $o['tekst'] ); ?>"></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc-breedte">Breedte van het fotopaneel</label></th>
					<td><input type="number" id="dtc-breedte" name="breedte" value="<?php echo esc_attr( $o['breedte'] ); ?>" min="20" max="60" step="1"> %
					<p class="description">Alleen op desktop; op de telefoon staat de foto als band bovenaan.</p></td>
				</tr>
			</table>

			<p class="submit">
				<?php submit_button( 'Opslaan', 'primary', 'submit', false ); ?>
				<button type="submit" name="herstel" value="1" class="button" onclick="return confirm('Alle instellingen terugzetten naar de standaard van dtc?')">Standaard terugzetten</button>
			</p>
		</form>
	</div>

	<?php if ( function_exists( 'dtc_tk_stijl' ) ) { dtc_tk_stijl(); } ?>
	<style>
		.dtc-login-instel .dtc-login-beeld{display:flex;align-items:center;gap:16px}
		.dtc-login-instel .voorbeeld{background:#f4f1ec;border:1px solid #e8e0d4;border-radius:10px;padding:6px}
		.dtc-login-instel .voorbeeld.logo{width:140px;height:52px;object-fit:contain}
		.dtc-login-instel .voorbeeld.foto{width:140px;height:84px;object-fit:cover}
		.dtc-login-instel .dtc-login-kleuren label{display:inline-flex;align-items:center;gap:7px;margin-right:22px;font-size:13px;color:#6b6258}
		.dtc-login-instel input[type=color]{width:46px;height:32px;padding:2px;border:1px solid #ddd3c4;border-radius:8px;background:#fff}
	</style>
	<script>
	jQuery( function ( $ ) {
		$( document ).on( 'click', '.dtc-kies', function () {
			var knop = $( this ), veld = $( '#' + knop.data( 'doel' ) );
			var kader = wp.media( { title: 'Kies een bestand', button: { text: 'Deze gebruiken' }, multiple: false } );
			kader.on( 'select', function () {
				var bestand = kader.state().get( 'selection' ).first().toJSON();
				veld.val( bestand.url );
				knop.closest( '.dtc-login-beeld' ).find( 'img.voorbeeld' ).attr( 'src', bestand.url );
			} );
			kader.open();
		} );
	} );
	</script>
	<?php
}
