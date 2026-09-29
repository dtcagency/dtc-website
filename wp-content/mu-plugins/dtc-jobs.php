<?php
/**
 * Plugin Name: DTC Jobs Feed
 * Description: Haalt vacatures van jobs.dtc.agency op en toont ze via [dtc_jobs]. Cache 1 uur, met fallback.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dtc_jobs_fetch() {
    $res = wp_remote_get( 'https://jobs.dtc.agency/', array( 'timeout' => 12, 'sslverify' => false ) );
    if ( is_wp_error( $res ) ) return array();
    $html = wp_remote_retrieve_body( $res );
    if ( ! $html ) return array();
    $jobs = array();
    if ( preg_match_all( '#<a\s+href="([^"]+)"\s+class="dtc-job-list-item">(.*?)</a>#s', $html, $items, PREG_SET_ORDER ) ) {
        foreach ( $items as $it ) {
            $link = $it[1]; $body = $it[2];
            $title = preg_match( '#dtc-job-list-title">(.*?)</span>#s', $body, $m ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
            $dept  = preg_match( '#dtc-job-list-dept">(.*?)</span>#s', $body, $m ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
            $loc   = preg_match( '#dtc-job-list-loc">(.*?)</span>#s', $body, $m ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
            if ( $title ) $jobs[] = array( 'title' => $title, 'dept' => $dept, 'loc' => $loc, 'link' => $link );
        }
    }
    return $jobs;
}

function dtc_jobs_get() {
    $jobs = get_transient( 'dtc_jobs_cache' );
    if ( false === $jobs ) {
        $jobs = dtc_jobs_fetch();
        if ( ! empty( $jobs ) ) {
            set_transient( 'dtc_jobs_cache', $jobs, HOUR_IN_SECONDS );
            update_option( 'dtc_jobs_fallback', $jobs, false );
        } else {
            $jobs = get_option( 'dtc_jobs_fallback', array() ); // laatst bekende lijst
        }
    }
    return $jobs;
}

function dtc_jobs_shortcode() {
    $jobs = dtc_jobs_get();
    if ( empty( $jobs ) ) return '';
    ob_start(); ?>
<style>@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Medium.woff2') format('woff2');font-weight:500;font-display:swap}
.dtc-roles{font-family:'Arboria',sans-serif;margin-top:6px}.dtc-roles ul{list-style:none;margin:0;padding:0}
.dtc-roles li a{display:flex;justify-content:space-between;align-items:center;text-decoration:none;color:#2d2824;font-weight:500;font-size:28px;padding:22px 0;border-bottom:1px solid #e4e1dc;transition:padding-left .2s,color .2s;text-transform:lowercase}
.dtc-roles li:first-child a{border-top:1px solid #e4e1dc}
.dtc-roles .rl{display:flex;flex-direction:column;gap:5px}
.dtc-roles .rl-m{font-size:14px;font-weight:400;color:#8a7257;text-transform:none;letter-spacing:.02em}
.dtc-roles li a .ar{opacity:0;transform:translateX(-8px);transition:opacity .2s,transform .2s;color:#c0ab94}
.dtc-roles li a:hover{padding-left:12px;color:#c0ab94}.dtc-roles li a:hover .ar{opacity:1;transform:translateX(0)}
@media(max-width:600px){.dtc-roles li a{font-size:22px}}</style>
<div class="dtc-roles"><ul>
<?php foreach ( $jobs as $j ) :
    $meta = $j['dept'];
    if ( ! empty( $j['loc'] ) ) $meta = $meta ? $meta . ' &bull; ' . $j['loc'] : $j['loc']; ?>
<li><a href="<?php echo esc_url( $j['link'] ); ?>" target="_blank" rel="noopener"><span class="rl"><?php echo esc_html( $j['title'] ); ?><span class="rl-m"><?php echo esc_html( $meta ); ?></span></span><span class="ar">&#8594;</span></a></li>
<?php endforeach; ?>
</ul></div>
<?php return ob_get_clean();
}
add_shortcode( 'dtc_jobs', 'dtc_jobs_shortcode' );
