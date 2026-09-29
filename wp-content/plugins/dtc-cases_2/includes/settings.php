<?php
defined( 'ABSPATH' ) || exit;

// Submenu under the Cases CPT.
add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=dtc_case',
		'Archive Instellingen',
		'Archive Instellingen',
		'manage_options',
		'dtc-cases-settings',
		'dtc_cases_settings_page'
	);
} );

function dtc_cases_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if (
		isset( $_POST['dtc_cases_settings_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['dtc_cases_settings_nonce'] ) ),
			'dtc_cases_settings_save'
		)
	) {
		update_option( 'dtc_cases_archive_title', sanitize_text_field( wp_unslash( $_POST['dtc_cases_archive_title'] ?? '' ) ) );
		update_option( 'dtc_cases_archive_text',  sanitize_textarea_field( wp_unslash( $_POST['dtc_cases_archive_text'] ?? '' ) ) );
		echo '<div class="notice notice-success is-dismissible"><p>Instellingen opgeslagen.</p></div>';
	}

	$title = (string) get_option( 'dtc_cases_archive_title', '' );
	$text  = (string) get_option( 'dtc_cases_archive_text',  '' );
	?>
	<div class="wrap">
		<h1>Archive instellingen — Cases</h1>
		<p style="color:#666;margin-bottom:20px;">
			Beheer de titel en omschrijving op de work/cases overzichtspagina.<br>
			Gebruik <code>**vet**</code> voor <strong>vetgedrukte</strong> tekst en <code>%%goud%%</code> voor <span style="color:#c0ab94;font-weight:600;">goudkleurige</span> tekst in de titel.
		</p>
		<form method="post">
			<?php wp_nonce_field( 'dtc_cases_settings_save', 'dtc_cases_settings_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="dtc_cases_archive_title">Paginatitel</label></th>
					<td>
						<input
							type="text"
							id="dtc_cases_archive_title"
							name="dtc_cases_archive_title"
							value="<?php echo esc_attr( $title ); ?>"
							class="large-text"
							placeholder="the work your %%competitors%% wish **they had**"
						/>
						<p class="description">Voorbeeld: <code>ons %%werk%% voor **jou**</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dtc_cases_archive_text">Omschrijving</label></th>
					<td>
						<textarea
							id="dtc_cases_archive_text"
							name="dtc_cases_archive_text"
							rows="4"
							class="large-text"
							placeholder="Korte introductietekst onder de titel…"
						><?php echo esc_textarea( $text ); ?></textarea>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
	</div>
	<?php
}
