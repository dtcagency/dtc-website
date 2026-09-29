<?php
/**
 * Plugin Name: DTC Contactformulier
 * Description: Neemt de berichten van de contactpagina aan, bewaart ze en mailt ze door.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DTC_BERICHT_TYPE = 'dtc_bericht';
const DTC_BERICHT_MAIL = 'hello@dtc.agency';

/**
 * Elk bericht wordt eerst als bericht in de database gezet en pas daarna
 * gemaild. Die volgorde is met opzet: mail kan onderweg stukgaan, de database
 * niet, en dan staat een aanvraag nog altijd in de beheeromgeving in plaats van
 * nergens. Op deze server ontbreekt sendmail, dus de mail vertrekt hier niet -
 * zodra er een verzendroute is, gaat hij mee zonder dat hier iets hoeft te
 * veranderen.
 */
add_action( 'init', function () {
	register_post_type( DTC_BERICHT_TYPE, array(
		'labels'          => array(
			'name'          => 'Berichten van de site',
			'singular_name' => 'Bericht',
			'menu_name'     => 'Berichten van de site',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 26,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
	) );
} );

function dtc_bericht_terug( $vraagtekens ) {
	$pagina = get_permalink( 283487 );
	if ( ! $pagina ) {
		$pagina = home_url( '/contact/' );
	}
	wp_safe_redirect( add_query_arg( $vraagtekens, $pagina ) . '#bericht' );
	exit;
}

function dtc_bericht_ontvangen() {
	// Het veld dtc_website staat buiten beeld en hoort dus leeg te blijven;
	// wordt het ingevuld, dan is het een robot. Stilletjes wegsturen, want een
	// foutmelding vertelt de robot alleen maar hoe het wel moet.
	if ( ! empty( $_POST['dtc_website'] ) ) {
		dtc_bericht_terug( array( 'verzonden' => 1 ) );
	}

	$naam    = sanitize_text_field( wp_unslash( $_POST['naam'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$bedrijf = sanitize_text_field( wp_unslash( $_POST['bedrijf'] ?? '' ) );
	$bericht = sanitize_textarea_field( wp_unslash( $_POST['bericht'] ?? '' ) );

	if ( '' === $naam || '' === $bericht || ! is_email( $email ) ) {
		dtc_bericht_terug( array( 'fout' => 1 ) );
	}

	$regels = array(
		'Naam: ' . $naam,
		'E-mail: ' . $email,
		'Bedrijf: ' . ( '' !== $bedrijf ? $bedrijf : '-' ),
		'',
		$bericht,
	);
	$tekst = implode( "\n", $regels );

	$id = wp_insert_post( array(
		'post_type'    => DTC_BERICHT_TYPE,
		'post_status'  => 'publish',
		'post_title'   => $naam . ( '' !== $bedrijf ? ' (' . $bedrijf . ')' : '' ),
		'post_content' => $tekst,
	), true );

	if ( ! is_wp_error( $id ) ) {
		update_post_meta( $id, 'dtc_email', $email );
		update_post_meta( $id, 'dtc_bedrijf', $bedrijf );
	}

	wp_mail(
		DTC_BERICHT_MAIL,
		'Bericht via de site - ' . $naam,
		$tekst,
		array( 'Reply-To: ' . $naam . ' <' . $email . '>' )
	);

	dtc_bericht_terug( array( 'verzonden' => 1 ) );
}
add_action( 'admin_post_dtc_bericht', 'dtc_bericht_ontvangen' );
add_action( 'admin_post_nopriv_dtc_bericht', 'dtc_bericht_ontvangen' );

/** In de lijst met berichten is het adres nuttiger dan de datum alleen. */
add_filter( 'manage_' . DTC_BERICHT_TYPE . '_posts_columns', function ( $kolommen ) {
	$nieuw = array();
	foreach ( $kolommen as $sleutel => $naam ) {
		$nieuw[ $sleutel ] = $naam;
		if ( 'title' === $sleutel ) {
			$nieuw['dtc_email'] = 'E-mail';
		}
	}
	return $nieuw;
} );

add_action( 'manage_' . DTC_BERICHT_TYPE . '_posts_custom_column', function ( $kolom, $id ) {
	if ( 'dtc_email' === $kolom ) {
		$email = get_post_meta( $id, 'dtc_email', true );
		echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;';
	}
}, 10, 2 );
