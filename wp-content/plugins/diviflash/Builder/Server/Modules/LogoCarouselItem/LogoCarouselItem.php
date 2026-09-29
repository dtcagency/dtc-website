<?php

namespace DIFL\Server\Modules\LogoCarouselItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class LogoCarouselItem implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    protected $config_file = 'LogoCarouselItem';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name                        = esc_html__( 'Logo Carousel Item', 'divi_flash' );
		$this->plural                      = esc_html__( 'Logo Carousel Items', 'divi_flash' );
		$this->slug                        = 'difl_logocarouselitem';
		$this->vb_support                  = 'on';
		$this->type                        = 'child';
		$this->child_title_var             = 'admin_label';
		$this->child_title_fallback_var    = 'item_type';
		$this->settings_text               = esc_html__( 'Logo Carousel Item Settings', 'divi_flash' );
		$this->advanced_setting_title_text = esc_html__( 'Logo Carousel Item', 'divi_flash' );
    }

    public static function custom_css(){
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/logocarouselitem' )->customCssFields;
    }

    public function get_common_styles( $args ) {
        $attrs        = $args['attrs'] ?? [];
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

        {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_logocarouselitem.difl_logocarouselitem", 
                'attr' => ( $attrs['module']['decoration'] ?? [] )['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    $radius = ( $props['attrValue'] ?? [] )['radius'] ?? null;
                    return $radius ? 'overflow: hidden !important' : '';
                } 
            ] 
        );
        
        $common_styles[] = CssStyle::style( [
            'selector'  => $order_class,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );
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

        $image_url = $this->props['image'];
        $image_alt = $this->props['alt_text'];

        $link_option_url = '#';
        $link_option_url_new_window = '_self';

        if(isset($attrs['module'])){
            $link_option_url = $attrs['module']['advanced']['link']['desktop']['value']['url'] ?? '';
            $link_option_url_new_window = $attrs['module']['advanced']['link']['desktop']['value']['target'] ?? '';
        }

        if($link_option_url_new_window === 'on'){
            $link_option_url_new_window = '_blank';
        }

        $_image = $image_url !== '' ? 
            sprintf( '<img class="df-lc-image" src="%1$s" alt="%2$s" />',
                esc_attr( $image_url ),
                esc_attr( $image_alt )
            )
            : '';

         if (!empty($link_option_url) &&
            !preg_match("~^(?:f|ht)tps?://~i", $link_option_url) &&
            !str_starts_with($link_option_url, '#')) {
            $link_option_url = "https://" . $link_option_url; // Default to HTTPS only if not a fragment link
        }

        $imgMarkup = sprintf('<div class="df_lci_container">%1$s</div>', $_image);

        if (!empty($link_option_url)) {
            $imgMarkup = sprintf('<div class="df_lci_container"><a href="%2$s" target="%3$s">%1$s</a></div>', $_image, $link_option_url,$link_option_url_new_window);
        }

        return $imgMarkup;
    }
}
 