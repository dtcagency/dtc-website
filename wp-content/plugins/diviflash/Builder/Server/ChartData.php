<?php
/**
 * Shared fetcher + parser for chart URL data sources.
 *
 * Supports CSV (and Google Sheets "Publish to web → CSV" URLs) and JSON.
 * Result is normalised to { labels: [...], datasets: [{ name, values }] }.
 */

namespace DIFL\Server;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

class ChartData {

	const MAX_BYTES        = 1048576;   // 1 MB cap per response.
	const TRANSIENT_PREFIX = 'df_chart_data_';

	/**
	 * Fetch + parse a remote URL into the canonical chart shape.
	 *
	 * @param string $url   Source URL (CSV/Google Sheets CSV/JSON).
	 * @param string $type  'csv' | 'json'. Google Sheets uses 'csv'.
	 * @param int    $ttl   Cache lifetime in seconds. 0 = no cache.
	 * @return array { labels, datasets } on success, [] on failure.
	 */
	public static function fetch( $url, $type = 'csv', $ttl = 3600 ) {
		$url = self::normalize_google_sheets_url( trim( (string) $url ) );
		if ( ! self::is_valid_url( $url ) ) {
			return [];
		}

		$type   = in_array( $type, [ 'csv', 'json' ], true ) ? $type : 'csv';
		$ttl    = max( 0, (int) $ttl );
		$cached = $ttl > 0 ? get_transient( self::TRANSIENT_PREFIX . md5( $url . '|' . $type ) ) : false;

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$body = self::read_same_site( $url );
		if ( null === $body ) {
			$response = wp_remote_get( $url, [
				'timeout'     => 8,
				'redirection' => 3,
				'headers'     => [ 'Accept' => 'text/csv, application/json;q=0.9, */*;q=0.5' ],
			] );

			if ( is_wp_error( $response ) ) {
				return [];
			}
			if ( wp_remote_retrieve_response_code( $response ) >= 400 ) {
				return [];
			}

			$body = wp_remote_retrieve_body( $response );
		}

		if ( ! is_string( $body ) || '' === $body ) {
			return [];
		}
		if ( strlen( $body ) > self::MAX_BYTES ) {
			return [];
		}

		$parsed = 'json' === $type ? self::parse_json( $body ) : self::parse_csv( $body );
		if ( empty( $parsed['labels'] ) || empty( $parsed['datasets'] ) ) {
			return [];
		}

		if ( $ttl > 0 ) {
			set_transient( self::TRANSIENT_PREFIX . md5( $url . '|' . $type ), $parsed, $ttl );
		}
		return $parsed;
	}

	/**
	 * If the URL points at the current site (within wp-content/uploads), read
	 * from disk to avoid HTTP loopback issues common on local environments
	 * (Local by Flywheel, MAMP, etc.). Returns null when not same-site or
	 * the file isn't found, so the caller falls back to wp_remote_get.
	 */
	private static function read_same_site( $url ) {
		$site_url = home_url();
		$site_host = wp_parse_url( $site_url, PHP_URL_HOST );
		$req_host  = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $site_host || ! $req_host || strcasecmp( $site_host, $req_host ) !== 0 ) {
			return null;
		}

		$uploads = wp_upload_dir();
		if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) ) {
			return null;
		}
		// Strip scheme so http vs https doesn't matter.
		$uploads_url_no_scheme = preg_replace( '#^https?:#', '', $uploads['baseurl'] );
		$req_url_no_scheme     = preg_replace( '#^https?:#', '', $url );
		if ( 0 !== strpos( $req_url_no_scheme, $uploads_url_no_scheme ) ) {
			return null;
		}

		$relative = ltrim( substr( $req_url_no_scheme, strlen( $uploads_url_no_scheme ) ), '/' );
		// Drop any query string.
		$relative = explode( '?', $relative, 2 )[0];
		// Block path traversal.
		if ( false !== strpos( $relative, '..' ) ) {
			return null;
		}
		$path = trailingslashit( $uploads['basedir'] ) . $relative;
		if ( ! is_readable( $path ) || ! is_file( $path ) ) {
			return null;
		}
		if ( filesize( $path ) > self::MAX_BYTES ) {
			return null;
		}
		$contents = file_get_contents( $path );
		return false === $contents ? null : $contents;
	}

	/**
	 * Convert a Google Sheets "edit" / "view" link into a CSV export URL.
	 * Leaves any other URL untouched. Works for:
	 *   /spreadsheets/d/{ID}/edit?usp=sharing
	 *   /spreadsheets/d/{ID}/edit#gid=123
	 *   /spreadsheets/d/{ID}/view
	 * Already-published "/pub?...output=csv" and "/export?format=csv" URLs pass through.
	 */
	private static function normalize_google_sheets_url( $url ) {
		if ( '' === $url ) return $url;
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host || false === strpos( $host, 'docs.google.com' ) ) return $url;
		// Already a CSV-yielding URL — leave it alone.
		if ( false !== strpos( $url, 'output=csv' ) || false !== strpos( $url, 'format=csv' ) ) return $url;

		// Try to preserve gid from query string or fragment.
		$gid = '0';
		if ( preg_match( '/[?&#]gid=([0-9]+)/', $url, $g ) ) {
			$gid = $g[1];
		}

		// "Publish to web" link with HTML format → swap to CSV.
		// e.g. /spreadsheets/d/e/2PACX-.../pubhtml?...  →  /spreadsheets/d/e/2PACX-.../pub?...&output=csv
		if ( preg_match( '#(/spreadsheets/d/e/[a-zA-Z0-9_-]+)/pub(?:html)?#', $url, $m ) ) {
			return 'https://docs.google.com' . $m[1] . '/pub?gid=' . $gid . '&single=true&output=csv';
		}

		// Plain edit/view link → use the export endpoint.
		if ( preg_match( '#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $url, $m ) ) {
			return 'https://docs.google.com/spreadsheets/d/' . $m[1] . '/export?format=csv&gid=' . $gid;
		}

		return $url;
	}

	private static function is_valid_url( $url ) {
		if ( '' === $url ) {
			return false;
		}
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		return in_array( $scheme, [ 'http', 'https' ], true );
	}

	/**
	 * Parse CSV body. First row = headers (col 0 = label header, rest = dataset names).
	 * Subsequent rows: col 0 = label, rest = numeric values.
	 */
	private static function parse_csv( $body ) {
		// Strip UTF-8 BOM.
		if ( 0 === strncmp( $body, "\xEF\xBB\xBF", 3 ) ) {
			$body = substr( $body, 3 );
		}

		$lines = preg_split( "/\r\n|\n|\r/", $body );
		$rows  = [];
		foreach ( $lines as $line ) {
			if ( '' === trim( $line ) ) continue;
			$rows[] = str_getcsv( $line );
		}
		if ( count( $rows ) < 2 ) return [];

		$header        = array_map( 'trim', array_shift( $rows ) );
		$dataset_names = array_slice( $header, 1 );
		if ( empty( $dataset_names ) ) return [];

		$labels   = [];
		$datasets = array_fill( 0, count( $dataset_names ), [] );

		foreach ( $rows as $row ) {
			$labels[] = isset( $row[0] ) ? trim( (string) $row[0] ) : '';
			foreach ( $dataset_names as $i => $_ ) {
				$cell             = $row[ $i + 1 ] ?? '';
				$datasets[ $i ][] = (float) preg_replace( '/[^0-9.\-]/', '', (string) $cell );
			}
		}

		$out = [];
		foreach ( $dataset_names as $i => $name ) {
			$out[] = [
				'name'   => $name,
				'values' => $datasets[ $i ],
			];
		}
		return [ 'labels' => $labels, 'datasets' => $out ];
	}

	/**
	 * Parse JSON body. Accepts:
	 *   1) { labels: [...], datasets: [{ name, values: [...] }] }
	 *   2) [ { Label: "Jan", Sales: 120, Returns: 8 }, ... ] (first key = label, rest = numeric)
	 */
	private static function parse_json( $body ) {
		$decoded = json_decode( $body, true );
		if ( ! is_array( $decoded ) ) return [];

		// Shape 1: canonical.
		if ( isset( $decoded['labels'], $decoded['datasets'] )
			&& is_array( $decoded['labels'] )
			&& is_array( $decoded['datasets'] )
		) {
			$labels   = array_map( 'strval', $decoded['labels'] );
			$datasets = [];
			foreach ( $decoded['datasets'] as $ds ) {
				if ( ! is_array( $ds ) || ! isset( $ds['values'] ) ) continue;
				$datasets[] = [
					'name'   => isset( $ds['name'] ) ? (string) $ds['name'] : '',
					'values' => array_map( 'floatval', (array) $ds['values'] ),
				];
			}
			if ( empty( $datasets ) ) return [];
			return [ 'labels' => $labels, 'datasets' => $datasets ];
		}

		// Shape 2: array of row objects.
		$first = reset( $decoded );
		if ( ! is_array( $first ) ) return [];

		$keys = array_keys( $first );
		if ( count( $keys ) < 2 ) return [];

		$label_key     = $keys[0];
		$dataset_names = array_slice( $keys, 1 );

		$labels   = [];
		$buckets  = array_fill( 0, count( $dataset_names ), [] );
		foreach ( $decoded as $row ) {
			if ( ! is_array( $row ) ) continue;
			$labels[] = isset( $row[ $label_key ] ) ? (string) $row[ $label_key ] : '';
			foreach ( $dataset_names as $i => $name ) {
				$buckets[ $i ][] = (float) ( $row[ $name ] ?? 0 );
			}
		}

		$out = [];
		foreach ( $dataset_names as $i => $name ) {
			$out[] = [
				'name'   => $name,
				'values' => $buckets[ $i ],
			];
		}
		return [ 'labels' => $labels, 'datasets' => $out ];
	}
}
