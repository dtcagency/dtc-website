<?php

namespace DIFL\Server\Modules\TestimonialCarouselItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class TestimonialCarouselItem implements DependencyInterface{

    use ModuleUtil;
    use HasProps;
    use StyleUtil;

    protected $config_file = 'TestimonialCarouselItem';
    public $slug       = 'difl_testimonialcarouselitem';
    public $vb_support = 'on';
    public $type       = 'child';
    public $child_title_var          = 'author';
	public $child_title_fallback_var = 'admin_label';
    protected $module_classnames = 'swiper-slide';

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/testimonialcarouselitem' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs         = $this->get_merged_attrs( $args );
		$attrs         = self::build_attr_for_styles( $attrs );
		$attrs         = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

        {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class}.difl_testimonialcarouselitem.difl_testimonialcarouselitem .et_pb_module_inner .df_tci_container", 
                'attr' => $attrs['module']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );

        $common_styles[] = CssStyle::style( [
            'selector'  => $order_class,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        if ( ( $this->props['quote_icon_use_icon'] ?? '' ) === 'on' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$order_class} .df_tc_quote_image .et-pb-icon.df_tc_quote_icon",
                'attr'                => $attrs['item_settings']['innerContent'] ?? [],
                'declarationFunction' => function () {
                    return self::get_font_style( $this->props['quote_icon_font_icon'] ?? '' );
                },
            ] );
        }

        foreach ( $common_styles as $common_style ) {
            if ( empty( $common_style ) ) {
                continue;
            }
            foreach ( $common_style as $style ) {
                $styles[] = $style;
            }
        }

        return $styles ?? [];
    }
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
        
        $classname = 'swiper-slide et_pb_module';

        $company_logo_image = ! empty( $this->props['company_logo'] ) ? $this->props['company_logo'] : '';
        $company_logo_alt_text = ! empty( $this->props['company_logo_alt_text'] ) ? $this->props['company_logo_alt_text'] : '';
        $company_logo = '';
        if ( ! empty( $this->props['company_logo'] ) ) {
            $company_logo = sprintf(
                '<div class="df_tc_company_logo">
                <img class="tc_company_logo" alt="%2$s" src="%1$s" />
            </div>',
                $company_logo_image,
                $company_logo_alt_text
            );
        }

        $content = $this->props['content'] !== '' ? sprintf('
            <div class="df_tc_content">
                %1$s
            </div>    
        ', $this->props['content']) : '';
        $author_image_url = $this->props['author_image'] !== '' ? $this->props['author_image'] : '';
        $author_image_alt_text = $this->props['author_image_alt_text'] !== '' ? $this->props['author_image_alt_text']  : '';

        $author_image = $this->props['author_image'] !== '' ? sprintf('
            <div class="df_tc_author_image">
                <img class="tc_author_image" alt="%2$s" src="%1$s" />
            </div>  
        ',
        $author_image_url ,
        $author_image_alt_text
        ) : '';
        
        $author_name = $this->props['author'] !== '' ?
            sprintf('<h4 class="author_name">%1$s</h4>', $this->props['author'] ) : '';
        
        $job_title = $this->props['job_title'] !== '' ?
            sprintf('<span class="tc_job_title">%1$s</span>', $this->props['job_title']) : '';

        $company = '';
        if ($this->props['company'] !== '') {
            if ($this->props['company_url'] !== '') {
                $company = sprintf('
                    <a href="%2$s" target="_blank" class="tc_company">%1$s</a>', 
                    $this->props['company'], esc_attr($this->props['company_url'])) ;
            } else {
                $company = sprintf('
                    <span class="tc_company">%1$s</span>', 
                    $this->props['company']);
            }
        }
        
        $separator = $job_title !== '' && $company !== '' ? ', ' : '';

        $info = $author_name !== '' || $job_title !== '' || $company !== '' ?
            sprintf('<div class="df_tc_author_info">%1$s%2$s %3$s</div>',
            $author_name, $job_title, $company) : '';
        
        $info_box = $author_image !== '' || $info !== '' ? sprintf('
                <div class="df_tc_author_box">%1$s%2$s</div>
            ', $author_image, $info) : '';

        // Rating Icon only
        $get_rating_icon = "☆";

        // Rating scale type
        $rating_scale_type =  !empty($this->props['rating_scale_type']) ? $this->props['rating_scale_type'] : 5;

        $rating_value = $rating_scale_type == 5
            ? ($this->props['rating_value_5'] <= 5 && $this->props['rating_value_5'] >= 0
                ? $this->props['rating_value_5'] : 5) : ($this->props['rating_value_10'] <= 10 && $this->props['rating_value_10'] >= 0
                ? $this->props['rating_value_10'] : 10);

        // Get float value
        $get_float = explode('.', $rating_value);

        $rating_icon = '';
        $rating_active_class = '';

        // Display Rating Icon
        for ($i = 1; $i <= $rating_scale_type; $i++) {
            if (!isset($rating_value)) {
                $rating_active_class = '';
            } else if ($i <= $rating_value) {
                $rating_active_class = 'df_rating_icon_fill';
            } else if ($i == $get_float[0] + 1 && isset($get_float[1]) && $get_float[1] != '' && $get_float[1] != 0) {
                $rating_active_class = 'df_rating_icon_fill df_rating_icon_empty df_fill_' . $get_float[1];
            } else {
                $rating_active_class = 'df_rating_icon_empty';
            }
            $rating_icon .= '<span class="et-pb-icon ' . $rating_active_class . '" data-icon="' . $get_rating_icon . '">' . $get_rating_icon . '</span>';
        }
        $rating = $this->props['enable_rating'] === 'on' ?  //df_tc_ratings
                sprintf('
                    <div class="df_tc_ratings">
                        %1$s
                    </div>
                ',
                $rating_icon 
                ) : '';

        return sprintf('<div class="et_pb_module_inner"><div class="df_tci_container">
                <div class="df_tci_inner">
                    %5$s
                    %1$s
                    %2$s
                    %3$s
                    %4$s
                </div>
            </div></div>',  
            $company_logo,
            $content,
            $info_box,
            $rating,
            $this->df_render_image()
        );
    }
    /**
     * Render image for the front image
     * 
     * @param String $key
     * @return HTML | markup for the image
     */
    public function df_render_image($key = '') {
        if ( $this->props['quote_icon_use_icon'] === 'on' && ! empty( $this->props['quote_icon_font_icon'] ) ) {
            $icon = esc_html( Utils::process_font_icon( $this->props['quote_icon_font_icon'] ) ?? '{' );
            return sprintf( '<div class="df_tc_quote_image"><span class="et-pb-icon df_tc_quote_icon">%1$s</span></div>', $icon );
        }
        if ( ! empty( $this->props['quote_icon_image'] ) ) {
            $image_alt = ! empty( $this->props['quote_icon_alt_text'] ) ? $this->props['quote_icon_alt_text'] : '';
            $image_url = $this->props['quote_icon_image'];

            return sprintf( '<div class="df_tc_quote_image">
                        <span class="df_tc_quote_img_wrap">
							<img class="tc_quote_image" alt="%2$s" src="%1$s" />
						</span>
                    </div>',
                $image_url,
                $image_alt
            );
        }
        return '';
    }
}