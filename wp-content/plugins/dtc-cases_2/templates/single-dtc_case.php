<?php
/**
 * Single case template — Volledige, onverkorte DTC-productiecode.
 * Geladen via de single_template filter in dtc-cases.php.
 */
defined( 'ABSPATH' ) || exit;

// Bepaal de template stijl voordat er output wordt gegenereerd
global $wp_query;
$_dtc_post_id       = isset( $wp_query->post->ID ) ? (int) $wp_query->post->ID : 0;
$_dtc_tpl           = $_dtc_post_id ? (string) get_post_meta( $_dtc_post_id, 'dtc_case_page_template', true ) : 'default';
$_dtc_blank         = ( 'blank' === $_dtc_tpl );

if ( $_dtc_blank ) : ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title( '|', true, 'right' ); ?><?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'dtc-fullscreen' ); ?>>
<?php else :
    get_header();
endif;

while ( have_posts() ) :
    the_post();

    $post_id = get_the_ID();

    // ── 1. Meta Fields ophalen uit Advanced Custom Fields / Meta ───────────
    $hero_subtitle      = (string) get_post_meta( $post_id, 'dtc_case_hero_subtitle',      true );

    $brand_subtitle     = (string) get_post_meta( $post_id, 'dtc_case_brand_subtitle',     true );
    $brand_text         = (string) get_post_meta( $post_id, 'dtc_case_brand_text',         true );
    $brand_images       =          get_post_meta( $post_id, 'dtc_case_brand_images',       true );
    $brand_image_text   = (string) get_post_meta( $post_id, 'dtc_case_brand_image_text',   true );
    $brand_cta_text     = (string) get_post_meta( $post_id, 'dtc_case_brand_cta_text',     true );
    $brand_cta_url      = (string) get_post_meta( $post_id, 'dtc_case_brand_cta_url',      true );

    $collage_images     =          get_post_meta( $post_id, 'dtc_case_collage_images',     true );

    $fullwidth_img_id   = absint(   get_post_meta( $post_id, 'dtc_case_fullwidth_image',   true ) );

    $strategy_subtitle  = (string) get_post_meta( $post_id, 'dtc_case_strategy_subtitle',  true );
    $strategy_text      = (string) get_post_meta( $post_id, 'dtc_case_strategy_text',      true );
    $strategy_images    =          get_post_meta( $post_id, 'dtc_case_strategy_images',    true );
    $strategy_cta_text  = (string) get_post_meta( $post_id, 'dtc_case_strategy_cta_text',  true );
    $strategy_cta_url   = (string) get_post_meta( $post_id, 'dtc_case_strategy_cta_url',   true );

    $creation_subtitle  = (string) get_post_meta( $post_id, 'dtc_case_creation_subtitle',  true );
    $creation_text      = (string) get_post_meta( $post_id, 'dtc_case_creation_text',      true );
    $creation_images    =          get_post_meta( $post_id, 'dtc_case_creation_images',    true );
    $creation_cta_text  = (string) get_post_meta( $post_id, 'dtc_case_creation_cta_text',  true );
    $creation_cta_url   = (string) get_post_meta( $post_id, 'dtc_case_creation_cta_url',   true );

    $growth_subtitle    = (string) get_post_meta( $post_id, 'dtc_case_growth_subtitle',    true );
    $growth_text        = (string) get_post_meta( $post_id, 'dtc_case_growth_text',        true );
    $growth_images      =          get_post_meta( $post_id, 'dtc_case_growth_images',      true );
    $growth_cta_text    = (string) get_post_meta( $post_id, 'dtc_case_growth_cta_text',    true );
    $growth_cta_url     = (string) get_post_meta( $post_id, 'dtc_case_growth_cta_url',     true );

    $contact_name    = (string) get_post_meta( $post_id, 'dtc_case_contact_name',  true );
    $contact_role    = (string) get_post_meta( $post_id, 'dtc_case_contact_role',  true );
    $contact_phone   = (string) get_post_meta( $post_id, 'dtc_case_contact_phone', true );
    $contact_email   = (string) get_post_meta( $post_id, 'dtc_case_contact_email', true );
    $contact_img_id  = absint( get_post_meta( $post_id, 'dtc_case_contact_image',  true ) );

    // ── 2. Project detail kolommen (Bulletpoint lijstjes onder de tekst) ──
    $detail_cols = [];
    for ( $i = 1; $i <= 3; $i++ ) {
        $lbl  = (string) get_post_meta( $post_id, 'dtc_case_detail_' . $i . '_label', true );
        $txt  = (string) get_post_meta( $post_id, 'dtc_case_detail_' . $i . '_text',  true );
        if ( $lbl || $txt ) {
            $detail_cols[] = [ 'label' => $lbl, 'text' => $txt ];
        }
    }
    $has_details = ! empty( $detail_cols );

    // Normaliseer afbeeldings-arrays (zorgt voor schone IDs)
    foreach ( [ 'brand_images', 'collage_images', 'strategy_images', 'creation_images', 'growth_images' ] as $var ) {
        $$var = is_array( $$var ) ? array_values( array_filter( array_map( 'absint', $$var ) ) ) : [];
    }

    // ── 3. Volledige Hero Image Srcset Logica ──────────────────────────────
    $thumb_id       = get_post_thumbnail_id();
    $hero_img_url   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : '';
    $hero_img_srcset = $thumb_id ? wp_get_attachment_image_srcset( $thumb_id, 'full' ) : '';
    $hero_img_alt   = $thumb_id
        ? esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) )
        : esc_attr( get_the_title( $post_id ) );

    $hero_display = $hero_subtitle ?: get_the_title( $post_id );

    // Sectie validatievlaggen
    $has_brand     = $brand_text    || ! empty( $brand_images );
    $has_collage   = ! empty( $collage_images );
    $has_strategy  = $strategy_text || ! empty( $strategy_images );
    $has_creation  = $creation_text || ! empty( $creation_images );
    $has_fullwidth = (bool) $fullwidth_img_id;
    $has_growth    = $growth_text   || ! empty( $growth_images );
    $has_contact   = $contact_name  || $contact_email;
?>

<?php
/**
 * Converteert **bold** markdown-syntaxis veilig naar sterke HTML tags.
 */
if ( ! function_exists( 'dtc_cases_parse_bold' ) ) {
    function dtc_cases_parse_bold( string $text ): string {
        return preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', esc_html( $text ) );
    }
}
?>

<div class="dtc-case-single">

    <section class="dtc-case-hero">
        <?php if ( $hero_img_url ) : ?>
            <img
                class="dtc-case-hero__img"
                src="<?php echo esc_url( $hero_img_url ); ?>"
                <?php if ( $hero_img_srcset ) : ?>srcset="<?php echo esc_attr( $hero_img_srcset ); ?>"<?php endif; ?>
                sizes="95vw"
                alt="<?php echo $hero_img_alt; ?>"
                loading="eager"
                decoding="async"
            />
        <?php endif; ?>

        <div class="dtc-case-hero__gradient" aria-hidden="true"></div>

        <div class="dtc-case-hero__inner">
            <div class="dtc-case-hero__sidebar">
                <div class="dtc-case-hero__sidebar-line" aria-hidden="true"></div>
                <h1 class="dtc-case-hero__sidebar-label" aria-label="<?php the_title_attribute(); ?>">
                    <?php the_title(); ?>
                </h1>
            </div>
            <div class="dtc-case-hero__content">
                <p class="dtc-case-hero__display">
                    <?php echo dtc_cases_parse_bold( $hero_display ); ?>
                </p>
            </div>
        </div>
    </section>

    <div class="custom-campaign-wrapper">

     

        <?php if ( $has_brand ) :
            $brand_portrait_id = ! empty( $brand_images ) ? (int) $brand_images[0] : 0;
        ?>
        <div class="sect-brand">
            <div class="sect-brand__text-col">
                <div class="dtc-section-header uppercase-header">
                    <h2><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
                    <div class="dtc-line"></div>
                </div>
                <?php if ( $brand_subtitle ) : ?><h3><?php echo esc_html( $brand_subtitle ); ?></h3><?php endif; ?>
                <div class="sect-brand__content">
                    <?php if ( $brand_text ) :
                        $paras = array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', $brand_text ) ) );
                        foreach ( $paras as $p ) { echo '<p>' . nl2br( esc_html( $p ) ) . '</p>'; }
                    endif; ?>
                </div>
                <?php if ( $brand_cta_text ) : ?>
                <a href="<?php echo esc_url( $brand_cta_url ?: '#' ); ?>" class="custom-btn">
                    <span class="arrow">&darr;</span> <?php echo esc_html( $brand_cta_text ); ?>
                </a>
                <?php endif; ?>
                
                <?php if ( $has_details ) : ?>
                <div class="dtc-case-phase__details">
                    <?php foreach ( $detail_cols as $col ) : ?>
                    <div class="dtc-case-phase__detail-col">
                        <?php if ( $col['label'] ) : ?><h4 class="dtc-case-phase__detail-label"><?php echo esc_html( $col['label'] ); ?></h4><?php endif; ?>
                        <?php if ( $col['text'] ) : ?>
                        <div class="dtc-case-phase__detail-items">
                            <?php foreach ( array_filter( array_map( 'trim', explode( "\n", $col['text'] ) ) ) as $line ) : ?>
                                <p><?php echo esc_html( $line ); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

             <?php if ( $has_collage ) :
            $collage_main_id  = ! empty( $collage_images ) ? (int) $collage_images[0] : 0;
            $collage_small_id = isset( $collage_images[1] ) ? (int) $collage_images[1] : 0;
        ?>
        <div class="sect-collage">
            <?php if ( $collage_small_id ) : ?>
            <div class="sect-collage__img-small">
                <?php echo wp_get_attachment_image( $collage_small_id, 'large', false, [ 'loading' => 'lazy' ] ); ?>
            </div>
            <?php endif; ?>
            <?php if ( $collage_main_id ) : ?>
            <div class="sect-collage__img-main">
                <?php echo wp_get_attachment_image( $collage_main_id, 'full', false, [ 'loading' => 'lazy' ] ); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

            <?php if ( $brand_portrait_id ) : ?>
            <div class="sect-brand__img-col">
                <?php echo wp_get_attachment_image( $brand_portrait_id, 'full', false, [ 'loading' => 'eager' ] ); ?>
                <?php if ( $brand_image_text ) : ?>
                    <h2 class="overlay-title"><?php echo nl2br( esc_html( $brand_image_text ) ); ?></h2>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>


        <?php if ( $has_strategy ) :
            $strat_feat_id = ! empty( $strategy_images ) ? (int) $strategy_images[0] : 0;
        ?>
        <div class="sect-strategy">
            <div class="sect-strategy__text-col">
                <div class="dtc-section-header lowercase-header">
                    <h2>strategy</h2>
                    <div class="dtc-line"></div>
                </div>
                <?php if ( $strategy_subtitle ) : ?>h3><?php echo esc_html( $strategy_subtitle ); ?></h3><?php endif; ?>
                <div class="sect-strategy__content">
                    <?php if ( $strategy_text ) :
                        $paras = array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', $strategy_text ) ) );
                        foreach ( $paras as $p ) { echo '<p>' . nl2br( esc_html( $p ) ) . '</p>'; }
                    endif; ?>
                </div>
                <?php if ( $strategy_cta_text ) : ?>
                <a href="<?php echo esc_url( $strategy_cta_url ?: '#' ); ?>" class="custom-btn">
                    <span class="arrow">&darr;</span> <?php echo esc_html( $strategy_cta_text ); ?>
                </a>
                <?php endif; ?>
            </div>

            <?php if ( $strat_feat_id ) : ?>
            <div class="sect-strategy__img-col">
                <?php echo wp_get_attachment_image( $strat_feat_id, 'full', false, [ 'loading' => 'lazy' ] ); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ( $has_creation ) :
            $c_img1 = ! empty( $creation_images )  ? (int) $creation_images[0] : 0;
            $c_img2 = isset( $creation_images[1] ) ? (int) $creation_images[1] : 0;
            $c_img3 = isset( $creation_images[2] ) ? (int) $creation_images[2] : 0;
            $c_img4 = isset( $creation_images[3] ) ? (int) $creation_images[3] : 0;
        ?>
        <div class="sect-creation">
            <div class="dtc-section-header lowercase-header">
                <h2>creation</h2>
                <div class="dtc-line"></div>
            </div>
            <div class="sect-creation__advanced-grid">
                
                <div class="sect-creation__wing-left">
                    <?php if ( $c_img1 ) : ?>
                    <div class="sect-creation__block-1">
                        <?php echo wp_get_attachment_image( $c_img1, 'full', false, [ 'loading' => 'lazy' ] ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $c_img2 ) : ?>
                    <div class="sect-creation__block-2">
                        <?php echo wp_get_attachment_image( $c_img2, 'full', false, [ 'loading' => 'lazy' ] ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="sect-creation__center-core">
                    <div class="sect-creation__text-card">
                        <?php if ( $creation_subtitle ) : ?><h3><?php echo esc_html( $creation_subtitle ); ?></h3><?php endif; ?>
                        <?php if ( $creation_text ) :
                            $paras = array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', $creation_text ) ) );
                            foreach ( $paras as $p ) { echo '<p>' . nl2br( esc_html( $p ) ) . '</p>'; }
                        endif; ?>
                        <?php if ( $creation_cta_text ) : ?>
                        <a href="<?php echo esc_url( $creation_cta_url ?: '#' ); ?>" class="custom-btn">
                            <span class="arrow">&darr;</span> <?php echo esc_html( $creation_cta_text ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php if ( $c_img3 ) : ?>
                    <div class="sect-creation__block-3">
                        <?php echo wp_get_attachment_image( $c_img3, 'full', false, [ 'loading' => 'lazy' ] ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="sect-creation__wing-right">
                    <?php if ( $c_img4 ) : ?>
                    <div class="sect-creation__block-4">
                        <?php echo wp_get_attachment_image( $c_img4, 'full', false, [ 'loading' => 'lazy' ] ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( $has_fullwidth ) : ?>
        <div class="sect-fullwidth">
            <?php echo wp_get_attachment_image( $fullwidth_img_id, 'full', false, [ 'loading' => 'lazy' ] ); ?>
        </div>
        <?php endif; ?>

        <?php if ( $has_growth ) :
            $growth_main_id = ! empty( $growth_images ) ? (int) $growth_images[0] : 0;
            $growth_small_id  = isset( $growth_images[1] ) ? (int) $growth_images[1] : 0;
        ?>
        <div class="sect-growth">
            <div class="sect-growth__left-col">
                <div class="sect-growth__text-wrap">
                    <div class="dtc-section-header lowercase-header">
                        <h2>growth</h2>
                        <div class="dtc-line"></div>
                    </div>
                    <?php if ( $growth_subtitle ) : ?><h3><?php echo esc_html( $growth_subtitle ); ?></h3><?php endif; ?>
                    <div class="sect-growth__content">
                        <?php if ( $growth_text ) :
                            $paras = array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', $growth_text ) ) );
                            foreach ( $paras as $p ) { echo '<p>' . nl2br( esc_html( $p ) ) . '</p>'; }
                        endif; ?>
                    </div>
                    <?php if ( $growth_cta_text ) : ?>
                    <a href="<?php echo esc_url( $growth_cta_url ?: '#' ); ?>" class="custom-btn">
                        <span class="arrow">&darr;</span> <?php echo esc_html( $growth_cta_text ); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php if ( $growth_small_id ) : ?>
                <div class="sect-growth__img-small">
                    <?php echo wp_get_attachment_image( $growth_small_id, 'large', false, [ 'loading' => 'lazy' ] ); ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ( $growth_main_id ) : ?>
            <div class="sect-growth__img-main">
                <?php echo wp_get_attachment_image( $growth_main_id, 'full', false, [ 'loading' => 'lazy' ] ); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div><?php
    $related = new WP_Query( [
        'post_type'      => 'dtc_case',
        'posts_per_page' => 4,
        'post__not_in'   => [ $post_id ],
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ] );
    if ( $related->have_posts() ) :
    ?>
    <section class="dtc-case-related">
        <div class="dtc-case-related__header">
            <h2 class="dtc-case-related__heading">the other work we did</h2>
        </div>
        <div class="dtc-case-related__grid">
            <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="dtc-case-related__item">
                    <div class="dtc-case-related__thumb">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large', [ 'loading' => 'lazy' ] ); ?>
                        <?php endif; ?>
                        <div class="dtc-case-related__thumb-overlay" aria-hidden="true"></div>
                    </div>
                    <span class="dtc-case-related__title"><?php the_title(); ?></span>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    endif;
    ?>

    <?php if ( $has_contact ) : ?>
    <section class="dtc-case-contact">
        <div class="dtc-case-contact__left">
            <h2 class="dtc-case-contact__heading">want to know more?</h2>
            <p class="dtc-case-contact__intro">Let's talk about what we can do for your brand.</p>
            <a href="mailto:<?php echo esc_attr( sanitize_email( $contact_email ) ); ?>" class="dtc-case-contact__btn">
                <span class="dtc-case-contact__btn-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="7" y1="17" x2="17" y2="7"/>
                        <polyline points="7 7 17 7 17 17"/>
                    </svg>
                </span>
                let's talk
            </a>
        </div>

        <div class="dtc-case-contact__right">
            <div class="dtc-case-contact__card">
                <?php if ( $contact_img_id ) : ?>
                    <div class="dtc-case-contact__avatar">
                        <?php echo wp_get_attachment_image( $contact_img_id, 'thumbnail', false, [ 'class' => 'dtc-case-contact__img' ] ); ?>
                    </div>
                <?php endif; ?>

                <div class="dtc-case-contact__info">
                    <?php if ( $contact_name ) : ?>
                        <strong class="dtc-case-contact__name"><?php echo esc_html( $contact_name ); ?></strong>
                    <?php endif; ?>

                    <?php if ( $contact_role ) : ?>
                        <span class="dtc-case-contact__role"><?php echo esc_html( $contact_role ); ?></span>
                    <?php endif; ?>

                    <?php if ( $contact_phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $contact_phone ) ); ?>" class="dtc-case-contact__link"><?php echo esc_html( $contact_phone ); ?></a>
                    <?php endif; ?>

                    <?php if ( $contact_email ) : ?>
                        <a href="mailto:<?php echo esc_attr( sanitize_email( $contact_email ) ); ?>" class="dtc-case-contact__link"><?php echo esc_html( $contact_email ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div><footer class="dtc-site-footer">
    <div class="dtc-site-footer__inner">
        
        <div class="dtc-site-footer__left">
            <div class="aj-hero-container">
                <div class="aj-hero-line">
                    <span class="aj-c-dark">how</span>
                    <span class="aj-space"></span>
                    <span class="aj-c-beige">about</span>
                    <span class="aj-space"></span>
                    <span class="aj-c-dark">we do</span>
                </div>

                <div class="aj-hero-line">
                    <span class="aj-c-dark">a thing or</span>
                    <span class="aj-space"></span>
                    <span class="aj-slot-wrapper">
                        <span class="aj-slot-track">
                            <span class="aj-slot-item">two,</span>
                            <span class="aj-slot-item">three,</span>
                            <span class="aj-slot-item">four,</span>
                            <span class="aj-slot-item">five,</span>
                            <span class="aj-slot-item">six,</span>
                            <span class="aj-slot-item">seven,</span>
                            <span class="aj-slot-item">eight,</span>
                            <span class="aj-slot-item">nine,</span>
                            <span class="aj-slot-item">ten,</span>
                            <span class="aj-slot-item">two,</span>
                        </span>
                    </span>
                </div>

                <div class="aj-bottom-row">
                    <span class="aj-c-blue">together</span>
                    <a href="/lets-talk/" class="aj-btn-pill" aria-label="Make an appointment">
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <span class="aj-small-text">make an appointment</span>
                </div>
            </div>
        </div>

        <div class="dtc-site-footer__right">
            <div class="dtc-site-footer__nav-section">
                <span class="dtc-site-footer__section-label">sitemap</span>
                <div class="dtc-site-footer__nav-cols">
                    <div class="dtc-site-footer__nav-col">
                        <a href="/">home</a>
                        <a href="/our-vision/">our vision</a>
                        <a href="/work/">work</a>
                        <a href="/services/">services</a>
                    </div>
                    <div class="dtc-site-footer__nav-col">
                        <a href="/hubspot/">hubspot</a>
                        <a href="/insights/">insights</a>
                        <a href="/lets-talk/">let's talk</a>
                    </div>
                </div>
            </div>

            <div class="dtc-site-footer__social-section">
                <span class="dtc-site-footer__section-label">social media</span>
                <div class="dtc-site-footer__social-links">
                    <a href="https://www.linkedin.com/company/dtcagency" target="_blank" rel="noopener noreferrer">Linkedin</a>
                    <a href="https://www.instagram.com/dtc.agency" target="_blank" rel="noopener noreferrer">Instagram</a>
                    <a href="https://www.tiktok.com/@dtc.agency" target="_blank" rel="noopener noreferrer">Tiktok</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dtc-site-footer__bottom">
        <span class="dtc-site-footer__logo-text">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="346" height="112" viewBox="0 0 346 112" fill="none">
                <g clip-path="url(#clip0_38_803)">
                    <path d="M95.798 56.3399C86.3309 50.9313 75.5114 47.7762 63.7902 47.7762C28.6267 47.7762 0 76.3969 0 111.553C0 146.709 28.6267 175.33 63.7902 175.33C75.5114 175.33 86.3309 172.175 95.798 166.766V175.33H127.806V0H95.798V56.3399ZM63.7902 143.554C46.2085 143.554 31.7824 129.131 31.7824 111.553C31.7824 93.9749 46.2085 79.5519 63.7902 79.5519C81.372 79.5519 95.798 93.9749 95.798 111.553C95.798 129.131 81.372 143.554 63.7902 143.554Z" fill="#2D2824"/>
                    <path d="M319.402 85.1855V138.37L346 111.778L319.402 85.1855Z" fill="#2D2824"/>
                    <path d="M175.592 127.553V63.7767H191.596V31.7757H175.592V0H143.81V127.779C143.81 127.779 143.81 175.78 191.822 175.78V143.554C175.592 143.554 175.592 127.553 175.592 127.553Z" fill="#2D2824"/>
                    <path d="M255.612 79.7774C265.079 79.7774 273.644 83.8339 279.505 90.3693L303.398 68.9602C291.677 55.8893 274.771 47.7764 255.837 47.7764C220.448 47.7764 192.047 76.397 192.047 111.553C192.047 146.709 220.674 175.33 255.837 175.33C274.771 175.33 291.677 166.992 303.398 154.146L279.505 132.737C273.644 139.272 265.079 143.329 255.612 143.329C238.03 143.329 223.604 129.131 223.604 111.328C223.604 93.5244 237.805 79.7774 255.612 79.7774Z" fill="#2D2824"/>
                </g>
                <defs>
                    <clipPath id="clip0_38_803)"><rect width="346" height="112" fill="white"/></clipPath>
                </defs>
            </svg>
        </span>

        <div class="dtc-site-footer__legal">
            <a href="/algemene-voorwaarden/" class="dtc-site-footer__legal-btn">Algemene voorwaarden</a>
            <a href="/cookie-en-privacystatement/" class="dtc-site-footer__legal-btn">Cookie- en privacystatement</a>
        </div>
    </div>
</footer>

<?php
endwhile;

if ( $_dtc_blank ) :
    wp_footer();
?>
</body>
</html>
<?php else :
    get_footer();
endif;