<?php
namespace DIFL\Server\Modules\PackeryGallery;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use \DIFL\Server\API;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class PackeryGallery implements DependencyInterface {
	public $slug       = 'difl_packerygallery';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'PackeryGallery';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/packerygallery' )->customCssFields;
	}

    function propMatches( $key, $value ) {
        return isset( $this->props[ $key ] ) && $this->props[ $key ] === $value;
    }

    function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    function getOverlayStyle($attr){
        $overlay_direction = $attr['overlay_direction'] ?? '180deg';
        $overlay_primary   = $attr['overlay_primary'] ?? '#00b4db';
        $overlay_secondary = $attr['overlay_secondary'] ?? '#0083b0';

        return "background-image: linear-gradient(" . $overlay_direction . ", " . $overlay_primary . " 0, " . $overlay_secondary . " 100%);";
    }

    function getBorderAnimStyle($attr){
        $styles = '--border-radius: 0px;';

        if ($this->propMatches('border_anim', 'on')) {
            $styles .= '--border-color: ' . ($attr['anm_border_color'] ?? '#ffffff') . ';';
            $styles .= '--border-width: ' . ($attr['anm_border_width'] ?? '3px') . ';';
            $styles .= '--border-margin: ' . ($attr['anm_border_margin'] ?? '15px') . ';';
        }

        $styles .= '--padding: ' . ($attr['anm_content_padding'] ?? '1em') . ';';

        return $styles;
    }

    function ig_arrow_icon($set = 'set_1', $type='prev') {
        $icons = [
            'set_1' => [
                'next' => '5',
                'prev' => '4',
            ],
            'set_2' => [
                'next' => '$',
                'prev' => '#',
            ],
            'set_3' => [
                'next' => '9',
                'prev' => '8',
            ],
            'set_4' => [
                'next' => 'E',
                'prev' => 'D',
            ],
        ];

        return $icons[$set][$type] ?? '';
    }

    public function get_common_styles( $args ) {
		$attrs      = $args['attrs'] ?? [];
        $attrs      = $this->get_merged_attrs( $args );
        $attrs      = self::build_attr_for_styles( $attrs );
        $attrs      = $this->get_default_value_for_styles($attrs);
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_container .df_pg_item",
			'attr'     => $attrs['space_between']['innerContent'] ?? null,
			'declarationFunction' => function($props){
                $space = $props['attrValue'];

                if( $space ) {
                    return "padding-left: $space; padding-bottom: $space;";
                }
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_container .df_pg_inner",
			'attr'     => $attrs['space_between']['innerContent'] ?? null,
			'declarationFunction' => function($props){
                $space = $props['attrValue'];

                if( $space ) {
                    return "margin-left: -$space;";
                }
            }
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-izmir .df-overlay",
			'attr'     => $this->propMatches('overlay', 'on') ? $attrs['overlay_settings']['innerContent'] : null,
			'declarationFunction' => function($props){
                return $this->getOverlayStyle($props['attrValue'] ?? []);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-izmir",
			'attr'     => $attrs['anm_border_style']['innerContent'] ?? null,
			'declarationFunction' => function($props){
                return $this->getBorderAnimStyle($props['attrValue'] ?? []);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_button_container",
			'attr'     => $attrs['more_btn_align']['innerContent'] ?? null,
			'property' => 'text-align'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_item",
			'attr'     => $this->propMatches('keep_dsk_tab', 'off') ? $attrs['tablet_view']['innerContent'] : null,
            'atRules'  => '@media only screen and (max-width: 1023px)',
			'declarationFunction' => function(){
                $width = (int)$this->props['tablet_view'];
                return "width: calc(100%/$width);";
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_item",
			'attr'     => $attrs['mobile_view']['innerContent'] ?? null,
            'atRules'  => '@media only screen and (max-width: 767px)',
			'declarationFunction' => function(){
                $width = (int)$this->props['mobile_view'];
                return "width: calc(100%/$width);";
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-pg-load-more-icon",
			'attr'     => $this->propMatches('more_btn_use_icon', 'on') ? $attrs['more_btn_icon_size']['innerContent'] : null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-pg-load-more-icon",
			'attr'     => $this->propMatches('more_btn_use_icon', 'on') ? $attrs['more_btn_font_icon']['innerContent'] : null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['more_btn_font_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination",
			'attr'     => $attrs['pagination_align']['innerContent'] ?? null,
			'property' => 'justify-content'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .prev:before",
			'attr'     => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return 'content: \'' . $this->ig_arrow_icon($this->props['next_prev_icon'] ?? '', 'prev') . '\';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .next:after",
			'attr'     => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return 'content: \'' . $this->ig_arrow_icon($this->props['next_prev_icon'] ?? '', 'next') . '\';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pg-more-image-btn .spinner svg",
			'attr'     => $attrs['spinner_color']['innerContent'] ?? null,
			'property' => 'fill'
        ]);

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pg-more-image-btn",
			'attr'     => $attrs['more_btn']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_pg_image",
			'attr'     => $attrs['item_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .page-numbers:not(.dots)",
			'attr'     => $attrs['pagination']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .page-numbers.current",
			'attr'     => $attrs['active_pagination']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
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

	public function render($attrs, $content, $render_slug)
    {
        // Scripts
        wp_enqueue_script('imageload');
        wp_enqueue_script('lightgallery-script');
        wp_enqueue_script('packery-script');
        wp_enqueue_script('df-packery-gallery');

        $load_more = $this->props['load_more'];
        $more_btn_use_icon = $this->props['more_btn_use_icon'];
	    $show_pagination = $this->props['show_pagination'];

        $gallery_array = API::df_pg_options($this->props);
        $images = API::df_pg_render_gallery_markup($gallery_array);

        $load_more_icon = $more_btn_use_icon === 'on' ? sprintf('<span class="df-pg-load-more-icon">%1$s</span>',
            isset($this->props['more_btn_font_icon']) && $this->props['more_btn_font_icon'] !== '' ? esc_attr(Utils::process_font_icon( $this->props['more_btn_font_icon'] )) : '4'
        ) : '';

        $load_more_icon_class = $more_btn_use_icon === 'on' ? ' has_icon' : '';

	    /** Pagination **/
	    $older = $this->props['older_text'] ? $this->props['older_text'] : 'Older Entries';
	    $next = $this->props['newer_text'] ? $this->props['newer_text'] : 'Next Entries';
	    if('on' === $this->props['use_icon_only_at_pagination']){
		    $older = '';
		    $next = '';
	    }
	    $pagination_data = '';
	    if('on' === $show_pagination && count(explode(',',$gallery_array['gallery'])) > (int)$this->props['pagination_img_count']){
		    ob_start();
		    ?>
		    <div class="df-pg-pagination pagination clearfix <?php echo 'on' === $this->props['use_icon_only_at_pagination'] ? 'only_icon':''; ?>">
			    <a class="prev page-numbers" data-current="1" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#" style="display: none;"><?php echo esc_html($older); ?></a>
			    <?php
			    if('on' === $this->props['use_number_pagination']){
				    for ($i = 0; $i < ceil(count(explode(',',$gallery_array['gallery']))/(int)$this->props['pagination_img_count']); $i++){
					    ?>
					    <a class="page-numbers <?php echo 0 === $i ? 'current' : ''; ?>" data-page="<?php echo esc_html(($i+1)); ?>" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#"><?php echo esc_html(($i+1)); ?></a>
					    <?php
				    }
			    }
			    ?>
			    <a class="next page-numbers" data-current="1" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#"><?php echo esc_html($next); ?></a>
		    </div>
		    <?php
		    $pagination_data = ob_get_clean();
	    }
	    /** Pagination **/
        $data_settings = API::df_pg_options($this->props);

        $load_more_button = ( $load_more === 'on' ) ?
            sprintf('<div class="df_pg_button_container">
                <button class="pg-more-image-btn%4$s" data-loaded="%2$s">%1$s %3$s
                <span class="spinner">
                    <svg width="135" height="140" viewBox="0 0 135 140" xmlns="http://www.w3.org/2000/svg" fill="#fff">
                        <rect y="10" width="15" height="120" rx="6">
                            <animate attributeName="height"
                                begin="0.5s" dur="1s"
                                values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                repeatCount="indefinite" />
                            <animate attributeName="y"
                                begin="0.5s" dur="1s"
                                values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                repeatCount="indefinite" />
                        </rect>
                        <rect x="30" y="10" width="15" height="120" rx="6">
                            <animate attributeName="height"
                                begin="0.25s" dur="1s"
                                values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                repeatCount="indefinite" />
                            <animate attributeName="y"
                                begin="0.25s" dur="1s"
                                values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                repeatCount="indefinite" />
                        </rect>
                        <rect x="60" width="15" height="140" rx="6">
                            <animate attributeName="height"
                                begin="0s" dur="1s"
                                values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                repeatCount="indefinite" />
                            <animate attributeName="y"
                                begin="0s" dur="1s"
                                values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                repeatCount="indefinite" />
                        </rect>
                        <rect x="90" y="10" width="15" height="120" rx="6">
                            <animate attributeName="height"
                                begin="0.25s" dur="1s"
                                values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                repeatCount="indefinite" />
                            <animate attributeName="y"
                                begin="0.25s" dur="1s"
                                values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                repeatCount="indefinite" />
                        </rect>
                        <rect x="120" y="10" width="15" height="120" rx="6">
                            <animate attributeName="height"
                                begin="0.5s" dur="1s"
                                values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                repeatCount="indefinite" />
                            <animate attributeName="y"
                                begin="0.5s" dur="1s"
                                values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                repeatCount="indefinite" />
                        </rect>
                    </svg>
                </span></button>
            </div>',
            sanitize_text_field($this->props['load_more_text']),
            sanitize_text_field($this->props['init_count']),
            $load_more_icon,
            esc_attr($load_more_icon_class)
        ) : '';

        return sprintf('<div class="df_pg_container%4$s" data-settings=\'%2$s\'>
                <div class="df_pg_inner">%1$s</div>
                %3$s
                %5$s
            </div>',
            $images,
            wp_json_encode($data_settings),
            $load_more_button,
            $this->props['use_lightbox'] === 'on' ? ' ig_has_lightbox' : '',
	        $pagination_data
        );
    }
}
