<?php
/**
 * Landingspagina van de hub: /hub/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
dtc_hub_styles();
?>
<div class="dtc-hub">
	<div class="in">
		<span class="kick">the hub</span>
		<h1>what we<br /><span>know.</span></h1>
		<p class="lead">Everything we keep learning about strategy, creation, growth, media and HubSpot
			&mdash; written down so you can use it, whether you work with us or not.</p>

		<?php dtc_hub_topic_bar(); ?>

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
				Nothing published here yet. The first pieces are being written &mdash;
				in the meantime, have a look at <a href="<?php echo esc_url( home_url( '/insights/' ) ); ?>">our insights</a>.
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
