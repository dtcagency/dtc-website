<?php
/**
 * Themapagina van de hub: /hub/<thema>/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dtc_term = get_queried_object();

get_header();
dtc_hub_styles();
?>
<div class="dtc-hub">
	<div class="in">
		<span class="kick">the hub</span>
		<h1><?php echo esc_html( $dtc_term->name ); ?><span>.</span></h1>
		<?php if ( $dtc_term->description ) : ?>
			<p class="lead"><?php echo esc_html( $dtc_term->description ); ?></p>
		<?php endif; ?>

		<?php dtc_hub_topic_bar( $dtc_term ); ?>

		<?php if ( have_posts() ) : ?>
			<div class="grid">
				<?php
				while ( have_posts() ) {
					the_post();
					dtc_hub_card( get_post() );
				}
				?>
			</div>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 2,
				'prev_text' => 'previous',
				'next_text' => 'next',
			) );
			?>
		<?php else : ?>
			<div class="empty">
				Nothing on <?php echo esc_html( $dtc_term->name ); ?> yet.
				<a href="<?php echo esc_url( get_post_type_archive_link( DTC_HUB_CPT ) ); ?>">See everything in the hub</a>.
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
