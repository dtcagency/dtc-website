<?php
/**
 * Hercodeert te zware afbeeldingen in de uploads-map.
 *
 * Aanleiding: een aantal bestanden is opgeslagen met een kwaliteitsinstelling
 * die veel te hoog is voor het web. Een foto van 300x471 pixels hoort geen
 * 240 kB te wegen. De afmetingen blijven ongemoeid - er wordt alleen opnieuw
 * gecodeerd, dus er gaat geen scherpte verloren door schalen.
 *
 * Werkwijze en veiligheden:
 * - alleen bestanden boven een ondergrens, en alleen als de winst fors is
 * - het origineel gaat eerst naar uploads/_voor-optimalisatie/<zelfde pad>
 * - bestanden met een EXIF-draaiing worden overgeslagen; de browser draait
 *   die zelf en GD gooit die informatie weg
 * - afbeeldingen met transparantie blijven PNG en houden hun alfakanaal
 *
 * Gebruik (op de container):
 *   php /usr/local/bin/optimize-images.php --plan
 *   php /usr/local/bin/optimize-images.php
 *   php /usr/local/bin/optimize-images.php --herstel
 */

const UPLOADS   = '/var/www/html/wp-content/uploads';
const BACKUP    = UPLOADS . '/_voor-optimalisatie';
const ONDERGENS = 90 * 1024;   // kleiner dan dit levert te weinig op
const WINST     = 0.70;        // alleen vervangen als het nieuwe bestand kleiner is dan 70%
const KWALITEIT = 85;

function mb( $n ) {
	return number_format( $n / 1048576, 2 ) . ' MB';
}

function bestanden() {
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( UPLOADS, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $f ) {
		if ( ! $f->isFile() ) {
			continue;
		}
		$pad = $f->getPathname();
		if ( str_starts_with( $pad, BACKUP ) ) {
			continue;
		}
		$ext = strtolower( $f->getExtension() );
		if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'webp', 'png' ), true ) ) {
			continue;
		}
		if ( $f->getSize() < ONDERGENS ) {
			continue;
		}
		yield $pad => $ext;
	}
}

/** Draait de browser dit bestand zelf? Dan blijft het ongemoeid. */
function heeft_draaiing( $pad, $ext ) {
	if ( ! in_array( $ext, array( 'jpg', 'jpeg' ), true ) || ! function_exists( 'exif_read_data' ) ) {
		return false;
	}
	$e = @exif_read_data( $pad );
	return $e && isset( $e['Orientation'] ) && (int) $e['Orientation'] !== 1;
}

function hercodeer( $pad, $ext, $doel ) {
	$im = match ( $ext ) {
		'jpg', 'jpeg' => @imagecreatefromjpeg( $pad ),
		'webp'        => @imagecreatefromwebp( $pad ),
		'png'         => @imagecreatefrompng( $pad ),
		default       => false,
	};
	if ( ! $im ) {
		return false;
	}
	$ok = false;
	if ( 'png' === $ext ) {
		// transparantie behouden; PNG blijft PNG
		imagealphablending( $im, false );
		imagesavealpha( $im, true );
		$ok = imagepng( $im, $doel, 8 );
	} elseif ( 'webp' === $ext ) {
		$ok = imagewebp( $im, $doel, KWALITEIT );
	} else {
		$ok = imagejpeg( $im, $doel, KWALITEIT );
	}
	imagedestroy( $im );
	return $ok;
}

/* ------------------------------------------------------------------ */

$plan    = in_array( '--plan', $argv, true );
$herstel = in_array( '--herstel', $argv, true );

if ( $herstel ) {
	if ( ! is_dir( BACKUP ) ) {
		exit( "geen back-up gevonden\n" );
	}
	$n = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( BACKUP, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $f ) {
		if ( ! $f->isFile() ) {
			continue;
		}
		$rel  = substr( $f->getPathname(), strlen( BACKUP ) + 1 );
		$terug = UPLOADS . '/' . $rel;
		if ( copy( $f->getPathname(), $terug ) ) {
			$n++;
		}
	}
	exit( "  $n bestanden teruggezet\n" );
}

$voor = 0;
$na   = 0;
$aantal = 0;
$overgeslagen = 0;

foreach ( bestanden() as $pad => $ext ) {
	$oud = filesize( $pad );

	if ( heeft_draaiing( $pad, $ext ) ) {
		$overgeslagen++;
		continue;
	}

	$tijdelijk = sys_get_temp_dir() . '/opt-' . getmypid() . '.' . $ext;
	if ( ! hercodeer( $pad, $ext, $tijdelijk ) ) {
		$overgeslagen++;
		@unlink( $tijdelijk );
		continue;
	}
	$nieuw = filesize( $tijdelijk );

	if ( $nieuw <= 0 || $nieuw > $oud * WINST ) {
		@unlink( $tijdelijk );
		continue;
	}

	$rel = substr( $pad, strlen( UPLOADS ) + 1 );
	printf( "  %7.0f kB -> %6.0f kB  (-%2.0f%%)  %s\n",
		$oud / 1024, $nieuw / 1024, ( 1 - $nieuw / $oud ) * 100, $rel );

	$voor += $oud;
	$na   += $nieuw;
	$aantal++;

	if ( $plan ) {
		@unlink( $tijdelijk );
		continue;
	}

	$backuppad = BACKUP . '/' . $rel;
	if ( ! is_dir( dirname( $backuppad ) ) ) {
		mkdir( dirname( $backuppad ), 0775, true );
	}
	if ( ! file_exists( $backuppad ) && ! copy( $pad, $backuppad ) ) {
		echo "     back-up mislukt - overgeslagen\n";
		@unlink( $tijdelijk );
		continue;
	}
	if ( ! rename( $tijdelijk, $pad ) ) {
		copy( $tijdelijk, $pad );
		@unlink( $tijdelijk );
	}
	@chown( $pad, 'www-data' );
	@chmod( $pad, 0644 );
}

echo "\n";
echo '  ' . $aantal . ' bestanden' . ( $plan ? ' zouden worden' : ' zijn' ) . " aangepast\n";
echo '  van ' . mb( $voor ) . ' naar ' . mb( $na ) . '  (' . mb( $voor - $na ) . " minder)\n";
if ( $overgeslagen ) {
	echo '  ' . $overgeslagen . " overgeslagen (draaiing of onleesbaar)\n";
}
