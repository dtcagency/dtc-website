<?php

namespace DIFL\Server\Modules;

use DIFL\Server\Modules\AdvancedMenu\AdvancedMenu;
use DIFL\Server\Modules\AdvancedMenuItem\AdvancedMenuItem;
use DIFL\Server\Modules\BeforeAfterSlider\BeforeAfterSlider;
use DIFL\Server\Modules\AdvancedDataTable\AdvancedDataTable;
use DIFL\Server\Modules\AdvancedTab\AdvancedTab;
use DIFL\Server\Modules\AdvancedTabItem\AdvancedTabItem;
use DIFL\Server\Modules\Blurb\Blurb;
use DIFL\Server\Modules\Breadcrumbs\Breadcrumbs;
use DIFL\Server\Modules\CFSeven\CFSeven;
use DIFL\Server\Modules\ContentToggle\ContentToggle;
use DIFL\Server\Modules\AdvancedPerson\AdvancedPerson;
use DIFL\Server\Modules\AdvancedButton\AdvancedButton;
use DIFL\Server\Modules\CptFilter\CptCarousel;
use DIFL\Server\Modules\CptFilter\CptFilter;
use DIFL\Server\Modules\CptGrid\CptGrid;
use DIFL\Server\Modules\CptItem\CptItem;
use DIFL\Server\Modules\DualButton\DualButton;
use DIFL\Server\Modules\Faq\Faq;
use DIFL\Server\Modules\FaqItem\FaqItem;
use DIFL\Server\Modules\FlipBox\FlipBox;
use DIFL\Server\Modules\GravityForm\GravityForm;
use DIFL\Server\Modules\IconList\IconList;
use DIFL\Server\Modules\IconListItem\IconListItem;
use DIFL\Server\Modules\ImageHover\ImageHover;
use DIFL\Server\Modules\ImageMask\ImageMask;
use DIFL\Server\Modules\LottieImage\LottieImage;
use DIFL\Server\Modules\MarqueeText\MarqueeText;
use DIFL\Server\Modules\MarqueeTextItem\MarqueeTextItem;
use DIFL\Server\Modules\PackeryGallery\PackeryGallery;
use DIFL\Server\Modules\PostGrid\PostGrid;
use DIFL\Server\Modules\PostCarousel\PostCarousel;
use DIFL\Server\Modules\PostList\PostList;
use DIFL\Server\Modules\PostListItem\PostListItem;
use DIFL\Server\Modules\PostItem\PostItem;
use DIFL\Server\Modules\Timeline\Timeline;
use DIFL\Server\Modules\TimelineItem\TimelineItem;
use DIFL\Server\Modules\PricingTable\PricingTable;
use DIFL\Server\Modules\PricingTableItem\PricingTableItem;
use DIFL\Server\Modules\ProductCarousel\ProductCarousel;
use DIFL\Server\Modules\ProductGrid\ProductGrid;
use DIFL\Server\Modules\ProductItem\ProductItem;
use DIFL\Server\Modules\ScrollImage\ScrollImage;
use DIFL\Server\Modules\ScrollTextReveal\ScrollTextReveal;
use DIFL\Server\Modules\SocialShare\SocialShare;
use DIFL\Server\Modules\SocialShareItem\SocialShareItem;
use DIFL\Server\Modules\ImageReveal\ImageReveal;
use DIFL\Server\Modules\AvatarStack\AvatarStack;
use DIFL\Server\Modules\AvatarStackItem\AvatarStackItem;
use DIFL\Server\Modules\InlineContents\InlineContents;
use DIFL\Server\Modules\InlineContentsItem\InlineContentsItem;
use DIFL\Server\Modules\ACFGallery\ACFGallery;
use DIFL\Server\Modules\StarRating\StarRating;
use DIFL\Server\Modules\Table\Table;
use DIFL\Server\Modules\TableRow\TableRow;
use DIFL\Server\Modules\TypingText\TypingText;
use DIFL\Server\Modules\SVGAnimator\SVGAnimator;
// use DIFL\Server\Modules\TextReveal\TextReveal;
use DIFL\Server\Modules\VerticalMenu\VerticalMenu;
use DIFL\Server\Modules\VideoModule\AdvancedVideo;
use DIFL\Server\Modules\WPForms\WPForms;
use DIFL\Server\Modules\TiltCard\TiltCard;
use DIFL\Server\Modules\LogoCarousel\LogoCarousel;
use DIFL\Server\Modules\LogoCarouselItem\LogoCarouselItem;
use DIFL\Server\Modules\TestimonialCarousel\TestimonialCarousel;
use DIFL\Server\Modules\TestimonialCarouselItem\TestimonialCarouselItem;
use DIFL\Server\Modules\TextHighlighter\TextHighlighter;
use DIFL\Server\Modules\HoverBox\HoverBox;
use DIFL\Server\Modules\AnimatedHeading\AnimatedHeading;
use DIFL\Server\Modules\FloatImage\FloatImage;
use DIFL\Server\Modules\FloatImageItem\FloatImageItem;
use DIFL\Server\Modules\JustifiedGallery\JustifiedGallery;

use DIFL\Server\Modules\TableOfContents\TableOfContents;
use DIFL\Server\Modules\BusinessHours\BusinessHours;
use DIFL\Server\Modules\BusinessHoursItem\BusinessHoursItem;
use DIFL\Server\Modules\Heading\Heading;
use DIFL\Server\Modules\Divider\Divider;
use DIFL\Server\Modules\ImageCarousel\ImageCarousel;
use DIFL\Server\Modules\ImageCarouselItem\ImageCarouselItem;
use DIFL\Server\Modules\ImageHotspot\ImageHotspot;
use DIFL\Server\Modules\ImageHotspotItem\ImageHotspotItem;
use DIFL\Server\Modules\ImageAccordion\ImageAccordion;
use DIFL\Server\Modules\ImageAccordionItem\ImageAccordionItem;
use DIFL\Server\Modules\ContentCarousel\ContentCarousel;
use DIFL\Server\Modules\ContentCarouselItem\ContentCarouselItem;
use DIFL\Server\Modules\InstagramGallery\InstagramGallery;
use DIFL\Server\Modules\InstagramCarousel\InstagramCarousel;
use DIFL\Server\Modules\ImageGallery\ImageGallery;
use DIFL\Server\Modules\ImageGalleryItem\ImageGalleryItem;
use DIFL\Server\Modules\CircleInfo\CircleInfo;
use DIFL\Server\Modules\CircleInfoItem\CircleInfoItem;
use DIFL\Server\Modules\BarChart\BarChart;
use DIFL\Server\Modules\BarChartDataset\BarChartDataset;
use DIFL\Server\Modules\LineChart\LineChart;
use DIFL\Server\Modules\LineChartDataset\LineChartDataset;
use DIFL\Server\Modules\PieChart\PieChart;
use DIFL\Server\Modules\PieChartItem\PieChartItem;
use DIFL\Server\Modules\CountdownTimer\CountdownTimer;
use DIFL\Server\Modules\Counter\Counter;
use DIFL\Server\Modules\FacebookFeed\FacebookFeed;
use DIFL\Server\Modules\XFeed\XFeed;
use DIFL\Server\Modules\Spline\Spline;

class Main {
	public function __construct() {
		add_action( 'divi_module_library_modules_dependency_tree', [ $this, 'register_to_deps' ] );
	}

	public function register_to_deps( $dependency_tree ) {
		if ( ! class_exists( 'Df_Acf_Fields' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-acf-fields.php' );
		}
		if ( ! class_exists( 'Df_Pod_Fields' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-pod-fields.php' );
		}
		if ( ! class_exists( 'Df_MetaBox_Fields' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-metabox-fields.php' );
		}

		if ( ! trait_exists( 'Df_Cpt_Taxonomy_Support' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-cpt-taxonomies.php' );
		}
		if ( ! trait_exists( 'Df_Acf_Data_Process' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-acf-data-process.php' );
		}
		if ( ! trait_exists( 'Df_Pod_Data_Process' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-pod-data-process.php' );
		}
		if ( ! trait_exists( 'Df_MetaBox_Data_Process' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-metabox-fields.php' );
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-metabox-data-process.php' );
		}

		$dependency_tree->add_dependency( new BusinessHours() );
		$dependency_tree->add_dependency( new BusinessHoursItem() );
		$dependency_tree->add_dependency( new Heading() );
		$dependency_tree->add_dependency( new Divider() );
		$dependency_tree->add_dependency( new ImageCarousel() );
		$dependency_tree->add_dependency( new ImageCarouselItem() );

		$dependency_tree->add_dependency( new ImageHotspot() );
		$dependency_tree->add_dependency( new ImageHotspotItem() );
		$dependency_tree->add_dependency( new ImageAccordion() );
		$dependency_tree->add_dependency( new ImageAccordionItem() );

		$dependency_tree->add_dependency( new ContentCarousel() );
		$dependency_tree->add_dependency( new ContentCarouselItem() );
		$dependency_tree->add_dependency( new InstagramGallery() );
		$dependency_tree->add_dependency( new InstagramCarousel() );

		$dependency_tree->add_dependency( new ImageGallery() );
		$dependency_tree->add_dependency( new ImageGalleryItem() );
		// $dependency_tree->add_dependency( new TextReveal() );
		$dependency_tree->add_dependency( new ScrollTextReveal() );
		$dependency_tree->add_dependency( new VerticalMenu() );
		$dependency_tree->add_dependency( new AdvancedPerson() );
		$dependency_tree->add_dependency( new ImageMask() );
		$dependency_tree->add_dependency( new ImageHover() );

		$dependency_tree->add_dependency( new AdvancedButton() );
		$dependency_tree->add_dependency( new SocialShare() );
		$dependency_tree->add_dependency( new SocialShareItem() );
		$dependency_tree->add_dependency( new ImageReveal() );
		$dependency_tree->add_dependency( new AvatarStack() );
		$dependency_tree->add_dependency( new AvatarStackItem() );
		$dependency_tree->add_dependency( new InlineContents() );
		$dependency_tree->add_dependency( new InlineContentsItem() );
		$dependency_tree->add_dependency( new ACFGallery() );
		$dependency_tree->add_dependency( new DualButton() );

		$dependency_tree->add_dependency( new Blurb() );
		$dependency_tree->add_dependency( new ContentToggle() );
		$dependency_tree->add_dependency( new PricingTable() );
		$dependency_tree->add_dependency( new PricingTableItem() );
		$dependency_tree->add_dependency( new ScrollImage() );
		$dependency_tree->add_dependency( new StarRating() );
		$dependency_tree->add_dependency( new TypingText());
		$dependency_tree->add_dependency( new FlipBox());
		$dependency_tree->add_dependency( new BeforeAfterSlider() );
		$dependency_tree->add_dependency( new Table() );
		$dependency_tree->add_dependency( new TableRow() );

		// $dependency_tree->add_dependency( new PricingTable() );
		// $dependency_tree->add_dependency( new PricingTableItem() );
		$dependency_tree->add_dependency( new TableOfContents() );
		$dependency_tree->add_dependency( new SVGAnimator() );
		$dependency_tree->add_dependency( new AdvancedDataTable() );
		$dependency_tree->add_dependency( new LottieImage() );
		$dependency_tree->add_dependency( new WPForms() );
		$dependency_tree->add_dependency( new CFSeven() );
		$dependency_tree->add_dependency( new GravityForm() );
		$dependency_tree->add_dependency( new Breadcrumbs() );
		$dependency_tree->add_dependency( new MarqueeText() );
		$dependency_tree->add_dependency( new MarqueeTextItem() );
		$dependency_tree->add_dependency( new AdvancedTab() );
		$dependency_tree->add_dependency( new AdvancedTabItem() );
		$dependency_tree->add_dependency( new CptGrid() );
		$dependency_tree->add_dependency( new CptCarousel() );
		$dependency_tree->add_dependency( new CptFilter() );
		$dependency_tree->add_dependency( new CptItem() );
		$dependency_tree->add_dependency( new TiltCard() );
		$dependency_tree->add_dependency( new LogoCarousel() );
		$dependency_tree->add_dependency( new LogoCarouselItem() );
		$dependency_tree->add_dependency( new AnimatedHeading() );
		$dependency_tree->add_dependency( new Faq() );
		$dependency_tree->add_dependency( new FaqItem() );
		$dependency_tree->add_dependency( new FloatImage() );
		$dependency_tree->add_dependency( new FloatImageItem() );
		$dependency_tree->add_dependency( new JustifiedGallery() );
		$dependency_tree->add_dependency( new TestimonialCarousel() );
		$dependency_tree->add_dependency( new TestimonialCarouselItem() );
		$dependency_tree->add_dependency( new TextHighlighter() );
		$dependency_tree->add_dependency( new HoverBox() );
		$dependency_tree->add_dependency( new PackeryGallery() );
		$dependency_tree->add_dependency( new ProductCarousel() );
		$dependency_tree->add_dependency( new ProductGrid() );
		$dependency_tree->add_dependency( new ProductItem() );
		$dependency_tree->add_dependency( new PostCarousel() );
		$dependency_tree->add_dependency( new PostGrid() );
		$dependency_tree->add_dependency( new PostItem() );
		$dependency_tree->add_dependency( new PostList() );
		$dependency_tree->add_dependency( new PostListItem() );
		$dependency_tree->add_dependency( new Timeline() );
		$dependency_tree->add_dependency( new TimelineItem() );
		$dependency_tree->add_dependency( new IconList() );
		$dependency_tree->add_dependency( new IconListItem() );
		$dependency_tree->add_dependency( new AdvancedMenu() );
		$dependency_tree->add_dependency( new AdvancedMenuItem() );
		$dependency_tree->add_dependency( new Spline() );
		$dependency_tree->add_dependency( new AdvancedVideo() );
		$dependency_tree->add_dependency( new CircleInfo() );
		$dependency_tree->add_dependency( new CircleInfoItem() );
		$dependency_tree->add_dependency( new BarChart() );
		$dependency_tree->add_dependency( new BarChartDataset() );
		$dependency_tree->add_dependency( new LineChart() );
		$dependency_tree->add_dependency( new LineChartDataset() );
		$dependency_tree->add_dependency( new PieChart() );
		$dependency_tree->add_dependency( new PieChartItem() );
		$dependency_tree->add_dependency( new CountdownTimer() );
		$dependency_tree->add_dependency( new Counter() );
		$dependency_tree->add_dependency( new FacebookFeed() );
		$dependency_tree->add_dependency( new XFeed() );
	}
}

new Main();
