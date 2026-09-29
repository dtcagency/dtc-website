<?php
/**
 * VerticalMenu Module class.
 *
 * @package DIVIFLASH5\Modules\VerticalMenu;
 */

namespace DIFL\Server\Modules\VerticalMenu;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class VerticalMenu
 *
 * @package DIVIFLASH5\Modules\VerticalMenu
 */
class VerticalMenu implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/vertical-menu')->customCssFields;
    }

    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs = $args['attrs'];

        // Text Options.
        $classnames_instance->add(
            TextClassnames::text_options_classnames(
                $attrs['module']['advanced']['text'] ?? [],
                [
                    'orientation' => false,
                ]
            ),
            true
        );

        // Module.
        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => $attrs['module']['decoration'] ?? [],
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? [],
                        [
                            'link' => $attrs['module']['advanced']['link'] ?? [],
                        ]
                    ),
                ]
            )
        );
    }

    public static function module_script_data($args)
    {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }

    protected function getDefaultKeys($args)
    {
        $attrs = $args['attrs'];

        $metaData = $args['elements']->module_metadata->attributes;
        foreach ($metaData as $key => $value) {
            if ( isset( $attrs[ $key ] ) && ! is_array( $attrs[ $key ] ) ) {
                continue;
            }
            if (empty($attrs[$key])) {
                $attrs[$key] = [
                    'innerContent' => [
                        'desktop' => [
                            'value' => []
                        ]
                    ],
                ];
            }
        }
        return $attrs;
    }

    public static function module_styles($args)
    {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/vertical-menu');
        $styles = [];

        if (($instance->props['style_settings__alignment']) === 'left') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap ul[class*="df-vertical-menu-nav-level-"] li.df-vertical-menu-item .df_vertical_menu_item_elements_wrapper .dropdown-arrow',
                'attr' => $attrs['alignment_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'margin-left: auto !important;',
            ]);
        }

        $styles[] = $elements->style([
            'attrName' => 'submenu_item_obj_settings',
            'subAttrName' => 'style_settings__sub_menu__item',
            'styleProps' => [
                'selector' => $args['orderClass'] . ' .df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-inside-mega-menu):not(.df-vertical-custom-submenu) > li > .df_vertical_menu_item_elements_wrapper',
                'selectorFunction' => function ($props) use ($args) {
                    if (($props['state'] ?? '') === 'hover') {
                        return $args['orderClass'] . ' .df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-inside-mega-menu):not(.df-vertical-custom-submenu) > li:hover > .df_vertical_menu_item_elements_wrapper';
                    }
                    return $props['selector'] ?? '';
                }
            ]
        ]);
        if($instance->props['settings__badge_visiblity']!=="on"){
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' ul li a .df-vertical-nav-item-badge',
                'attr' => $attrs['badge_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'display:none !important;',
            ]);
        }
        if($instance->props['settings__tooltip_visiblity']!=="on"){
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' ul li a .df-vertical-nav-item-tooltip',
                'attr' => $attrs['tooltip_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'display:none !important;',
            ]);
        }
        // Active Main Menu
        if ($instance->props['settings__clicked_menu_element_style'] === 'on' && $instance->props['settings__submenu_reveal_type'] == 'df-vertical-sub-menu-reveal-stack') {
            //main menu active font color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav .df_vertical_menu_item_elements_wrapper.active',
                'attr' => $attrs['menu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__menu__active__font_color']??'') . '!important;',
            ]);
            //main menu active icon color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav li>.df_vertical_menu_item_elements_wrapper.active>.df-vertical-menu-icon',
                'selectorFunction' => function ($props) use ($args) {
                    if (($props['state'] ?? '') === 'hover') {
                        return $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav li>.df_vertical_menu_item_elements_wrapper.active:hover>.df-vertical-menu-icon';
                    }
                    return $props['selector'] ?? '';
                },
                'attr' => $attrs['menu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__menu__active__icon_color']??'') . '!important;',
            ]);
            //main menu active bg color
            $styles[] = $elements->style([
                'attrName' => 'menu_active_obj_settings',
                'styleProps' => [
                    'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav .df_vertical_menu_item_elements_wrapper.active',
                ]
            ]);


            //sub menu active icon color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav .df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-col-added>ul):not(.df-vertical-inside-mega-menu)>li>.df_vertical_menu_item_elements_wrapper.active>.df-vertical-menu-icon',
                'selectorFunction' => function ($props) use ($args) {
                    if (($props['state'] ?? '') === 'hover') {
                        return $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav .df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-col-added>ul):not(.df-vertical-inside-mega-menu)>li>.df_vertical_menu_item_elements_wrapper.active:hover>.df-vertical-menu-icon';
                    }
                    return $props['selector'] ?? '';
                },
                'attr' => $attrs['submenu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__sub_menu__active__icon_color']??'') . '!important;',
            ]);
            //sub menu active font color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav ul.df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-inside-mega-menu) > li > .df_vertical_menu_item_elements_wrapper.active',
                'attr' => $attrs['submenu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__sub_menu__active__font_color']??'') . '!important;',
            ]);
            //sub menu active bg color
            $styles[] = $elements->style([
                'attrName' => 'submenu_active_obj_settings',
                'styleProps' => [
                    'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav ul.df-vertical-sub-menu:not(.df-vertical-col-added):not(.df-vertical-inside-mega-menu) > li > .df_vertical_menu_item_elements_wrapper.active',
                ]
            ]);


            //mega menu active icon color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav  ul.df-vertical-sub-menu.df-vertical-col-added  li > .df_vertical_menu_item_elements_wrapper.active .df-vertical-menu-icon',
                'selectorFunction' => function ($props) use ($args) {
                    if (($props['state'] ?? '') === 'hover') {
                        return $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav  ul.df-vertical-sub-menu.df-vertical-col-added  li > .df_vertical_menu_item_elements_wrapper.active:hover .df-vertical-menu-icon';
                    }
                    return $props['selector'] ?? '';
                },
                'attr' => $attrs['megamenu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__mega_menu__active__icon_color']??'') . '!important;',
            ]);
            //mega menu active font color
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav  ul.df-vertical-sub-menu.df-vertical-col-added  li > .df_vertical_menu_item_elements_wrapper.active',
                'attr' => $attrs['megamenu_active_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => 'color:' . ($props['attrValue']['style_settings__mega_menu__active__font_color']??'') . '!important;',
            ]);
            //mega menu active bg color
            $styles[] = $elements->style([
                'attrName' => 'megamenu_active_obj_settings',
                'styleProps' => [
                    'selector' => $args['orderClass'] . ' .df_vertical_menu_main_container .df-vertical-menu-nav-wrap  ul.df-vertical-menu-nav  ul.df-vertical-sub-menu.df-vertical-col-added  li > .df_vertical_menu_item_elements_wrapper.active',
                ]
            ]);
        }

        $styles[] = CommonStyle::style([
            'selector' => "{$args['orderClass']} .df-vertical-humberger-container span.df-vertical-menu-hamburger-icon .hamburger",
            'attr' => $attrs['hamburger_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function($props) use ($instance) {
                return "width: " . ($props['attrValue']['style_settings__hamburger_icon_font_size'] ?? $instance->props['style_settings__hamburger_icon_font_size'] ) . " !important;";
            }
        ]);

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );

        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => [
                    // Element: Module.
                    $elements->style(
                        [
                            'attrName' => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                    CssStyle::style(
                        [
                            'selector' => $args['orderClass'],
                            'attr' => $attrs['css'] ?? [],
                            'cssFields' => self::custom_css(),
                        ]
                    ),

                    ...$instance->styles,
                    ...$styles,

                    //  CommonStyle::style(
                    //      [
                    //          'selector'            => "{$order_class} .df_text_reveal_main_container",
                    //          'attr'                => $attrs['settings__reveal_color']['decoration'] ?? [],
                    //          'property' => '--secondary-reveal-color'
                    //      ]
                    //  ),
                ],
            ]
        );
    }

    public function render()
    {
        wp_enqueue_script('df_vertical_menu');

        $menu_slug = $this->props['settings__select_menu_slug'];
        $animation_type = '';
        $animation_type = $this->props['settings__menu_item_hover_animation'] == 'on' ? $this->props['settings__select_animation_type'] : '';
        $settings__submenu_reveal_type = $this->props['settings__submenu_reveal_type'];
        $content_alignment = $this->props['style_settings__alignment'];
        $content_alignment_tablet = $this->props['style_settings__alignment_tablet'];
        $content_alignment_phone = $this->props['style_settings__alignment_phone'];
        $settings__hamburger_icon_preset = $this->props['settings__hamburger_icon_preset'];
        $settings__hamburger_text = $this->props['settings__hamburger_text'];
        $settings__use_hamburger_for_mobile = '';
        $settings__submenu_reveal_dir = $this->props['settings__submenu_reveal_dir'];
        $settings__hamburger_menu_reveal_type = $this->props['settings__hamburger_menu_reveal_type'];

        if ($this->props['settings__use_hamburger_for_mobile'] == 'on') {
            $settings__use_hamburger_for_mobile = 'df_enabled_hamburger';
        };

        if ($this->props['settings__submenu_reveal_type'] === 'df-vertical-sub-menu-reveal-stack') {
            $style_settings__sub_menu__tree_view = ($this->props['style_settings__sub_menu__tree_view'] === 'off' && $this->props['settings__submenu_reveal_type'] == 'df-vertical-sub-menu-reveal-stack') ? 'df_disable_sub_menu__tree_view' : 'df_enable_sub_menu__tree_view';
        } else {
            $style_settings__sub_menu__tree_view = '';
        }


        $style_settings__badge__alignment = $this->props['style_settings__badge__alignment'];
        if ($animation_type != "") {
            $animation_type = "df-vertical-has-item-animation $animation_type";
        }
        $menu_items = df_vertical_get_am_menu(
            [
                'menu' => '',
                'menu_id' => $menu_slug,
            ]
        );
        add_filter('et_global_assets_list', [$this, 'difl_load_required_divi_assets'], 10, 3);
        add_filter('et_late_global_assets_list', [$this, 'difl_load_required_divi_assets'], 10, 3);

        return sprintf(
            '<div class="%12$s %10$s %13$s df_vertical_menu_main_container  %7$s %4$s %5$s-tablet %6$s-phone %1$s badge-position-%2$s %3$s" >
%11$s %8$s</div>',
            esc_html($settings__submenu_reveal_type),
            esc_html($style_settings__badge__alignment),
            esc_html($animation_type),
            esc_html($content_alignment),
            esc_html($content_alignment_tablet),
            esc_html($content_alignment_phone),
            esc_html($style_settings__sub_menu__tree_view),
            $menu_items,
            esc_html($settings__hamburger_icon_preset),
            esc_html($settings__use_hamburger_for_mobile),
            $this->hamburgerMarkup($settings__use_hamburger_for_mobile, $settings__hamburger_text),
            esc_html($settings__submenu_reveal_dir),
            esc_html($settings__hamburger_menu_reveal_type)

        );
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-vertical-menu');

        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'moduleClassName' => '',
                'name' => $block->block_type->name,
                'classnamesFunction' => [VerticalMenu::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [VerticalMenu::class, 'module_styles'],
                'scriptDataComponent' => [VerticalMenu::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render(),
            ]
        );
    }


    public function hamburgerMarkup($status, $content)
    {
        if ($status == "df_enabled_hamburger") {
            return sprintf('<span class="df-vertical-humberger-container">
                            		<span class="df-vertical-menu-hamburger-text">%1$s</span>
                            		<span class="df-vertical-menu-hamburger-icon">
                            			<span class="hamburger">
																		<div class="close-hamburger"> %2$s</div>
																		<div class="open-hamburger"> %3$s </div>
																	</span>
                        				</span>
																</span>', $content, $this->hamburger_svg(), $this->hamburger_svg(true));
        } else {
            return '';
        }
    }


    public function hamburger_svg($close = false)
    {
        $jsonString = file_get_contents(DIFL_ADMIN_DIR_PATH . "assets/svg/verticalMenu.json");
        $svgArray = json_decode($jsonString, true);

        if ($close) {
            return $svgArray['VerticalMenu']['close'];
        } else {
            if (empty($this->props['settings__hamburger_icon_preset'])) {
                return $svgArray['VerticalMenu']['icon1'];
            }
        }


        return $svgArray['VerticalMenu'][$this->props['settings__hamburger_icon_preset']];
    }


    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/vertical-menu';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [VerticalMenu::class, 'render_callback'],
                    ]
                );
            }
        );
    }


}
