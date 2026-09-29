<?php

namespace DIFL\Server\Modules\SocialShareItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class SocialShareItem implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

	public $icon_path;

    protected $config_file  = 'SocialShareItem';
    public $slug            = 'difl_social_share_item';
    public $vb_support      = 'on';
    public $type            = 'child';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Social Share Item', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/logo-carousel.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/social-share-item' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

		$common_styles[] = CommonStyle::style([
			'selector' => "{$order_class} .difl_social_share_item_wrapper .difl_social_share_icon i:before",
			'attr'     => $attrs['iconImage']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["icon_color"])){
					return 'color: '.$value['attrValue']["icon_color"].' !important;';
				}
			}
		]);
		if( $this->props["use_icon_font_size"] === 'on' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_social_share_item_wrapper .difl_social_share_icon",
				'attr'                => $attrs['iconImage']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					if(isset($value['attrValue']["icon_font_size"])){
						return '--df-ss-icon-font-size: '.$value['attrValue']["icon_font_size"].';';
					}
				}
			]);
		}
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_item_wrapper",
			'attr'                => $attrs['content']['innerContent'] ?? null,
			'declarationFunction' => function () {
				$platformName = $this->props['social_network'];
				if( !empty($platformName) && $platformName !== 'none' ){
					$brand_color = $this->get_social_network_data($platformName)['data']['color'];
					return 'background-image: initial !important;';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_item_wrapper",
			'attr'                => $attrs['module']['decoration']['background'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']['color']) && !empty($value['attrValue']['color'])){
					return 'background-color: '.$value['attrValue']['color'].' !important;';
				}else{
					return 'background-color: transparent !important;';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_item_wrapper.difl_social_share_item_wrapper .difl_social_share_icon",
			'attr'                => $attrs['iconImage']['decoration']['spacing'] ?? null,
			'declarationFunction' => function ($props) {
				return 'width: auto; height: auto;';
			}
		]);
		
		{/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class}.difl_social_share_item .difl_social_share_item_wrapper", 
                'attr' => $attrs['module']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        ); 
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class}.difl_social_share_item .difl_social_share_item_wrapper .difl_social_share_icon", 
                'attr' => $attrs['iconImage']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        ); 
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class}.difl_social_share_item .difl_social_share_item_wrapper .difl_social_share_content_container", 
                'attr' => $attrs['labelContainer']['decoration']['border'] ?? null, 
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

    private function get_social_network_data( $field ) {
        $social_network_data = [
			'amazon'        => [
				'value' => esc_html__( 'Amazon', 'divi_flash' ),
				'data'  => [
					'color' => '#ff9900',
				],
			],
			'bandcamp'      => [
				'value' => esc_html__( 'Bandcamp', 'divi_flash' ),
				'data'  => [
					'color' => '#629aa9',
				],
			],
			'behance'       => [
				'value' => esc_html__( 'Behance', 'divi_flash' ),
				'data'  => [
					'color' => '#0057ff',
				],
			],
			'bitbucket'     => [
				'value' => esc_html__( 'BitBucket', 'divi_flash' ),
				'data'  => [
					'color' => '#205081',
				],
			],
			'buffer'        => [
				'value' => esc_html__( 'Buffer', 'divi_flash' ),
				'data'  => [
					'color' => '#000000',
				],
			],
			'codepen'       => [
				'value' => esc_html__( 'CodePen', 'divi_flash' ),
				'data'  => [
					'color' => '#000000',
				],
			],
			'deviantart'    => [
				'value' => esc_html__( 'DeviantArt', 'divi_flash' ),
				'data'  => [
					'color' => '#05cc47',
				],
			],
			'dribbble'      => [
				'value' => esc_html__( 'Dribbble', 'divi_flash' ),
				'data'  => [
					'color' => '#ea4c8d',
				],
			],
			'facebook'      => [
				'value' => esc_html__( 'Facebook', 'divi_flash' ),
				'data'  => [
					'color' => '#1877F2',
				],
			],
			'flikr'         => [
				'value' => esc_html__( 'Flickr', 'divi_flash' ),
				'data'  => [
					'color' => '#ff0084',
				],
			],
			'flipboard'     => [
				'value' => esc_html__( 'FlipBoard', 'divi_flash' ),
				'data'  => [
					'color' => '#e12828',
				],
			],
			'foursquare'    => [
				'value' => esc_html__( 'Foursquare', 'divi_flash' ),
				'data'  => [
					'color' => '#f94877',
				],
			],
			'github'        => [
				'value' => esc_html__( 'GitHub', 'divi_flash' ),
				'data'  => [
					'color' => '#333333',
				],
			],
			'goodreads'     => [
				'value' => esc_html__( 'Goodreads', 'divi_flash' ),
				'data'  => [
					'color' => '#553b08',
				],
			],
			'google'        => [
				'value' => esc_html__( 'Google', 'divi_flash' ),
				'data'  => [
					'color' => '#4285f4',
				],
			],
			'houzz'         => [
				'value' => esc_html__( 'Houzz', 'divi_flash' ),
				'data'  => [
					'color' => '#7ac142',
				],
			],
			'instagram'     => [
				'value' => esc_html__( 'Instagram', 'divi_flash' ),
				'data'  => [
					'color' => '#ea2c59',
				],
			],
			'itunes'        => [
				'value' => esc_html__( 'iTunes', 'divi_flash' ),
				'data'  => [
					'color' => '#fe7333',
				],
			],
			'last_fm'       => [
				'value' => esc_html__( 'Last.fm', 'divi_flash' ),
				'data'  => [
					'color' => '#b90000',
				],
			],
			'line'          => [
				'value' => esc_html__( 'Line', 'divi_flash' ),
				'data'  => [
					'color' => '#00c300',
				],
			],
			'linkedin'      => [
				'value' => esc_html__( 'LinkedIn', 'divi_flash' ),
				'data'  => [
					'color' => '#007bb6',
				],
			],
			'medium'        => [
				'value' => esc_html__( 'Medium', 'divi_flash' ),
				'data'  => [
					'color' => '#00ab6c',
				],
			],
			'meetup'        => [
				'value' => esc_html__( 'Meetup', 'divi_flash' ),
				'data'  => [
					'color' => '#e0393e',
				],
			],
			'myspace'       => [
				'value' => esc_html__( 'MySpace', 'divi_flash' ),
				'data'  => [
					'color' => '#3b5998',
				],
			],
			'odnoklassniki' => [
				'value' => esc_html__( 'Odnoklassniki', 'divi_flash' ),
				'data'  => [
					'color' => '#ed812b',
				],
			],
			'patreon'       => [
				'value' => esc_html__( 'Patreon', 'divi_flash' ),
				'data'  => [
					'color' => '#f96854',
				],
			],
			'periscope'     => [
				'value' => esc_html__( 'Periscope', 'divi_flash' ),
				'data'  => [
					'color' => '#3aa4c6',
				],
			],
			'pinterest'     => [
				'value' => esc_html__( 'Pinterest', 'divi_flash' ),
				'data'  => [
					'color' => '#cb2027',
				],
			],
			'quora'         => [
				'value' => esc_html__( 'Quora', 'divi_flash' ),
				'data'  => [
					'color' => '#a82400',
				],
			],
			'reddit'        => [
				'value' => esc_html__( 'Reddit', 'divi_flash' ),
				'data'  => [
					'color' => '#ff4500',
				],
			],
			'researchgate'  => [
				'value' => esc_html__( 'ResearchGate', 'divi_flash' ),
				'data'  => [
					'color' => '#40ba9b',
				],
			],
			'rss'           => [
				'value' => esc_html__( 'RSS', 'divi_flash' ),
				'data'  => [
					'color' => '#ff8a3c',
				],
			],
			'skype'         => [
				'value' => esc_html__( 'Skype', 'divi_flash' ),
				'data'  => [
					'color' => '#12A5F4',
				],
			],
			'snapchat'      => [
				'value' => esc_html__( 'Snapchat', 'divi_flash' ),
				'data'  => [
					'color' => '#fffc00',
				],
			],
			'soundcloud'    => [
				'value' => esc_html__( 'SoundCloud', 'divi_flash' ),
				'data'  => [
					'color' => '#ff8800',
				],
			],
			'spotify'       => [
				'value' => esc_html__( 'Spotify', 'divi_flash' ),
				'data'  => [
					'color' => '#1db954',
				],
			],
			'steam'         => [
				'value' => esc_html__( 'Steam', 'divi_flash' ),
				'data'  => [
					'color' => '#00adee',
				],
			],
			'telegram'      => [
				'value' => esc_html__( 'Telegram', 'divi_flash' ),
				'data'  => [
					'color' => '#179cde',
				],
			],
			'tiktok'        => [
				'value' => esc_html__( 'TikTok', 'divi_flash' ),
				'data'  => [
					'color' => '#fe2c55',
				],
			],
			'tripadvisor'   => [
				'value' => esc_html__( 'TripAdvisor', 'divi_flash' ),
				'data'  => [
					'color' => '#00af87',
				],
			],
			'tumblr'        => [
				'value' => esc_html__( 'Tumblr', 'divi_flash' ),
				'data'  => [
					'color' => '#32506d',
				],
			],
			'twitch'        => [
				'value' => esc_html__( 'Twitch', 'divi_flash' ),
				'data'  => [
					'color' => '#6441a5',
				],
			],
			'twitter'       => [
				'value' => esc_html__( 'X', 'divi_flash' ),
				'data'  => [
					'color' => '#000000',
				],
			],
			'vimeo'         => [
				'value' => esc_html__( 'Vimeo', 'divi_flash' ),
				'data'  => [
					'color' => '#45bbff',
				],
			],
			'vk'            => [
				'value' => esc_html__( 'VK', 'divi_flash' ),
				'data'  => [
					'color' => '#45668e',
				],
			],
			'weibo'         => [
				'value' => esc_html__( 'Weibo', 'divi_flash' ),
				'data'  => [
					'color' => '#eb7350',
				],
			],
			'whatsapp'      => [
				'value' => esc_html__( 'WhatsApp', 'divi_flash' ),
				'data'  => [
					'color' => '#25D366',
				],
			],
			'xing'          => [
				'value' => esc_html__( 'XING', 'divi_flash' ),
				'data'  => [
					'color' => '#026466',
				],
			],
			'yelp'          => [
				'value' => esc_html__( 'Yelp', 'divi_flash' ),
				'data'  => [
					'color' => '#af0606',
				],
			],
			'youtube'       => [
				'value' => esc_html__( 'Youtube', 'divi_flash' ),
				'data'  => [
					'color' => '#a82400',
				],
			],
			'digg'          => [
				'value' => esc_html__( 'Digg', 'divi_flash' ),
				'data'  => [
					'color' => '#005be2',
				],
			],
			'stumbleupon'   => [
				'value' => esc_html__( 'Stumbleupon', 'divi_flash' ),
				'data'  => [
					'color' => '#eb4924',
				],
			],
			'mix'           => [
				'value' => esc_html__( 'Mix', 'divi_flash' ),
				'data'  => [
					'color' => '#f3782b',
				],
			],
			'pocket'        => [
				'value' => esc_html__( 'Pocket', 'divi_flash' ),
				'data'  => [
					'color' => '#ef3f56',
				],
			],
			'email'         => [
				'value' => esc_html__( 'Email', 'divi_flash' ),
				'data'  => [
					'color' => '#ea4335',
				],
			],
			'print'         => [
				'value' => esc_html__( 'Print', 'divi_flash' ),
				'data'  => [
					'color' => '#aaa',
				],
			],
		];

        return $social_network_data[$field] ?? null;
    }

    private function get_current_page_url() {
		// return get_permalink();
		global $post;
		if ( $post instanceof \WP_Post ) {
			$permalink = get_permalink( $post );
			return $permalink ? $permalink : home_url( '/' );
		}

		return home_url( '/' );
	}

	private function get_current_page_title() {
		return wp_get_document_title() ?: 'Check this out!';
	}

	// custom function for fontawesome icon supported brand list 
	private function is_fa_supported_icon( $name ) {
		$fa_supported_social_networks = [
			'reddit', 'vk', 'telegram', 'xing', 'whatsapp',
			'buffer', 'flipboard', 'line', 'odnoklassniki', 'weibo'
		];

		if (in_array($name, $fa_supported_social_networks, true)) {
			return true;
		}

		return false;
	}

    public function generate_share_link( $network_name ) {
		$list_share_links = [
			'facebook'    => 'https://www.facebook.com/sharer/sharer.php?u=%1$s',
			'x'     => 'https://twitter.com/intent/tweet?text=%1$s',
			'linkedin'    => 'https://www.linkedin.com/shareArticle?mini=true&url=%1$s/&title=&summary=&source=',
			'pinterest'   => 'https://www.pinterest.com/pin/create/button/?url=%1$s&media=',
			'reddit'      => 'https://www.reddit.com/submit?url=%1$s&title=',
			'vk'          => 'https://vk.com/share.php?url=%1$s',
			'tumblr'      => 'https://tumblr.com/share/link?url=%1$s',
			'digg'        => 'https://digg.com/submit?url=%1$s',
			'skype'       => 'https://web.skype.com/share?url=%1$s',
			'stumbleupon' => 'https://www.stumbleupon.com/submit?url=%1$s',
			'mix'         => 'https://mix.com/add?url=%1$s',
			'telegram'    => 'https://telegram.me/share/url?url=%1$s&text=',
			'pocket'      => 'https://getpocket.com/edit?url=%1$s',
			'xing'        => 'https://www.xing.com/spi/shares/new?url=%1$s',
			'whatsapp'    => 'https://api.whatsapp.com/send?text=**%1$s',
			'email'       => 'mailto:?body=%1$s',
			'print'       => '#',

			'buffer'        => 'https://buffer.com/add?url=%1$s&text=',
			'flipboard'     => 'https://share.flipboard.com/bookmarklet/popout?v=2&url=%1$s',
			'line'          => 'https://social-plugins.line.me/lineit/share?url=%1$s',
			'myspace'       => 'https://myspace.com/post?u=%1$s',
			'odnoklassniki' => 'https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl=%1$s',
			'weibo'         => 'https://service.weibo.com/share/share.php?url=%1$s',
		];

		if ( ! isset( $list_share_links[ $network_name ] ) ) {
			return '#';
		}

		// Properly encode URL and title for sharing
		$encoded_url   = urlencode( $this->get_current_page_url() );
		return sprintf( $list_share_links[ $network_name ], $encoded_url );
	}
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
		$url_new_window                 = $this->parent_props['url_new_window'] === 'on' ? 'target="_blank"' : '';
	    $item_view                      = $this->parent_props['item_view'] ?? 'iconAndText';
        $social_network                 = $this->props['social_network'] ?? '';
		$network_name                   = '';
		$use_icon_font_size             = $this->props['use_icon_font_size'];

        $social_network_link_url = $this->generate_share_link( $social_network );
        
        $social_icon_class = '';
        if ( '' !== $social_network ) {
			$network_name = $this->get_social_network_data($social_network)['value'];
			$social_icon_class = sprintf( ' df-social-share-%s', esc_attr( $social_network ) );
			if ( $this->is_fa_supported_icon( $social_network ) ) {
				$social_icon_class .= ' df-social-share-fa-icon';
			}
		}

        $use_custom_image_icon = $this->props['use_custom_image_icon'];
		$src                   = $this->props['src'];
		$alt                   = $this->props['alt'];
		$title_text            = $this->props['titleText'];

        $images_output = sprintf(
			'<img src="%1$s" class="difl_custom_image_icon" alt="%2$s"%3$s />',
			esc_url( $src ),
			esc_attr( $alt ),
			( '' !== $title_text ? sprintf( ' title="%1$s"', esc_attr( $title_text ) ) : '' )
		);

		$share_icon    = '';
		$share_content = '';

		if ( 'iconAndText' === $item_view ) {
			$share_icon    = sprintf(
				'<div class="difl_social_share_icon">
						%1$s
					</div>', ( 'on' === $use_custom_image_icon && '' !== $social_network ) ? $images_output : sprintf( '<i class="%1$s"></i>', $social_icon_class ) );
			$share_content = sprintf(
				'<div class="difl_social_share_content_container">
                        <div class="difl_social_share_content">
                            <span class="difl_social_share_text">%1$s</span>
                        </div>
                    </div>', ! empty( $this->props['custom_label'] ) ? esc_html($this->props['custom_label']) : $network_name );
		}
		if ( 'icon' === $item_view ) {
			$share_icon = sprintf(
				'<div class="difl_social_share_icon">
						%1$s
					</div>', ( 'on' === $use_custom_image_icon && '' !== $social_network ) ? $images_output : sprintf( '<i class="%1$s"></i>', $social_icon_class ) );
		}
		if ( 'text' === $item_view ) {
			$share_content = sprintf(
				'<div class="difl_social_share_content_container">
                        <div class="difl_social_share_content">
                            <span class="difl_social_share_text">%1$s</span>
                        </div>
                    </div>', ! empty( $this->props['custom_label'] ) ? esc_html($this->props['custom_label']) : $network_name );
		}

		// Tooltip
		$tooltip_content      = $this->props['tooltipContent'];

		$tooltip_content_data = "";
		if ( ! empty( $tooltip_content ) ) {
			$tooltip_content_data = sprintf( '<noscript>%1$s</noscript>', $tooltip_content );
		}

        $hover_animation = '';
        $difl_printClass = 'print' === $social_network ? ' difl_print' : '';

        return sprintf(
			'<div><a href="%1$s" class="difl_social_share_item_wrapper%7$s" data-animation="" title="%4$s" %6$s role="link" rel="noopener noreferrer">
                %2$s%3$s%5$s
            </a></div>',
			$social_network_link_url,
			$share_icon,
			$share_content,
            $network_name,
			$tooltip_content_data,
            $url_new_window,
            $social_network === 'print' ? ' difl_print' : ''
		);
    }
}
