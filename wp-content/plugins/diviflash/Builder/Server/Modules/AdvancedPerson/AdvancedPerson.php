<?php
/**
 * AdvancedPerson Module class.
 *
 * @package DIVIFLASH5\Modules\AdvancedPerson;
 */

namespace DIFL\Server\Modules\AdvancedPerson;

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
 * Class AdvancedPerson
 *
 * @package DIVIFLASH5\Modules\AdvancedPerson
 */
class AdvancedPerson implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/advanced-person')->customCssFields;
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

        $df_transform_values = function ($key = 'bottom', $state = 'default') {
            $transform_values = array(
                'top' => [
                    'default' => 'translateY(0px)',
                    'hover' => 'translateY(-100%)'
                ],
                'bottom' => [
                    'default' => 'translateY(0px)',
                    'hover' => 'translateY(100%)'
                ],
                'left' => [
                    'default' => 'translateX(0px)',
                    'hover' => 'translateX(-100%)'
                ],
                'right' => [
                    'default' => 'translateX(0px)',
                    'hover' => 'translateX(100%)'
                ],
                'center' => [
                    'default' => 'scale(1)',
                    'hover' => 'scale(0)'
                ],
                'top_right' => [
                    'default' => 'translateX(0px) translateY(0px)',
                    'hover' => 'translateX(100%) translateY(-100%)'
                ],
                'top_left' => [
                    'default' => 'translateX(0px) translateY(0px)',
                    'hover' => 'translateX(-100%) translateY(-100%)'
                ],
                'bottom_right' => [
                    'default' => 'translateX(0px) translateY(0px)',
                    'hover' => 'translateX(100%) translateY(100%)'
                ],
                'bottom_left' => [
                    'default' => 'translateX(0px) translateY(0px)',
                    'hover' => 'translateX(-100%) translateY(100%)'
                ]
            );
            return $transform_values[$key][$state];
        };


        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/advanced-person');

        //Additional Style
        $styles = [];
        if (($instance->props['style_type']) !== 'default_style') {
            $styles[] = $elements->style([
                'attrName' => 'ekip_overlay_obj_settings',
                'attrSubName' => 'ekip_overlay_background',
            ]);
        }

        if (($instance->props['style_type']) === 'default_style') {
            $styles[] = $elements->style([
                'attrName' => 'default_overlay_obj_settings',
                'attrSubName' => 'default_overlay_background',
            ]);
        }
        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .df_ap_person_container .df_ap_person_desc',
            'attr' => $attrs['anm_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) use ($df_transform_values) {
                return 'transform: ' . ($df_transform_values($props['attrValue']['anim_direction'] ?? 'bottom','hover') ?? '') . ';';
            },
        ]);

        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .df_ap_person_container:hover .df_ap_person_desc',
            'attr' => $attrs['anm_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) use ($df_transform_values) {
                return 'transform: ' . ($df_transform_values($props['attrValue']['anim_direction'] ?? 'bottom','default') ?? '') . ';';
            },
        ]);

        $styleType = $attrs['style_obj_settings']['innerContent']['desktop']['value']['style_type'] ?? 'default_style';

        if ($styleType !== 'default_style') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .c4-izmir .df-overlay',
                'attr' => $attrs['style_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) =>
                ($props['attrValue']['style_type'] ?? '') !== 'default_style'
                    ? 'background:unset !important'
                    : null,
            ]);
        }

        if ($styleType === 'default_style') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_person_overlay',
                'attr' => $attrs['style_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) =>
                ($props['attrValue']['style_type'] ?? 'default_style') === 'default_style'
                    ? 'background:unset !important'
                    : null,
            ]);
        }

        if ($instance->props['make_full_with_icon'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_person_socail_wrapper',
                'attr' => $attrs['social_icon_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'display: flex;',
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_person_socail_wrapper .df_person_social_icon',
                'attr' => $attrs['social_icon_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'flex-grow: 1; margin: 0px;',
            ]);
        }
        if (
            ($instance->props['image_force_to_fullwidth'] ?? null) === 'on' &&
            ($instance->props['enable_alternative_photo'] ?? null) === 'off'
        ) {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .c4-izmir',
                'attr' => $attrs['image_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'display:block',
            ]);
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $args['orderClass'] ?? '', $attrs, $elements->module_metadata->attributes ?? [] ) );

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
        $name_level = esc_attr($this->props['ap_name_tag']);
        $role_level = esc_attr($this->props['ap_role_tag']);
        $name_html = $this->props['ap_name'] !== '' ?
            sprintf('<%1$s  class="df_person_name">%2$s</%1$s >', $name_level, esc_attr($this->props['ap_name'])) : '';
        $role_html = $this->props['ap_role'] !== '' ?
            sprintf('<%1$s  class="df_person_role">%2$s</%1$s >', $role_level, esc_attr($this->props['ap_role'])) : '';
        $description = $this->props['ap_description'] !== '' ?
            sprintf('<div class="df_person_description"> %1$s</div>', $this->props['ap_description']) : '';
        $pattern = "/<p[^>]*><\/p[^>]*>/";
        $description = preg_replace($pattern, '', $description);  //$description = preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '', $description); // remove 1.2.4 version
        $member_details_html = ($name_html !== '') || ($role_html !== '') || ($description !== '') ?
            sprintf('<div class="df_person_details">
                                %1$s%2$s%3$s
                            </div>',
                $name_html,
                $role_html,
                $description
            ) : '';

        $facebook = !empty($this->props['ap_facebook']) ?
            sprintf('<a href="%1$s" class="df_person_social_icon facebook" target="_blank" df-tooltip="Facebook" title="" aria-expanded="false">
        %2$s
    </a>', esc_url($this->props['ap_facebook']), $this->df_social_icon(['unicode' => '&#xe093;', 'type' => "divi", 'weight' => 400])) : '';

        $twitter = !empty($this->props['ap_twitter']) ?
            sprintf('<a href="%1$s" class="df_person_social_icon twitter" target="_blank" df-tooltip="Twitter" title="" aria-expanded="false">
        %2$s
    </a>', esc_url($this->props['ap_twitter']), $this->df_social_icon(['unicode' => '&#xe094;', 'type' => "divi", 'weight' => 400])) : '';

        $linkedin = !empty($this->props['ap_linkedin']) ?
            sprintf('<a href="%1$s" class="df_person_social_icon linkedin" target="_blank" df-tooltip="Linkedin" title="" aria-expanded="false">
        %2$s
    </a>', esc_url($this->props['ap_linkedin']), $this->df_social_icon(['unicode' => '&#xe09d;', 'type' => "divi", 'weight' => 400])) : '';

        $instagram = !empty($this->props['ap_instagram']) ?
            sprintf('<a href="%1$s" class="df_person_social_icon instagram" target="_blank" df-tooltip="Instagram" title="" aria-expanded="false">
        %2$s
    </a>', esc_url($this->props['ap_instagram']), $this->df_social_icon(['unicode' => '&#xe09a;', 'type' => "divi", 'weight' => 400])) : '';

        $pinterest = !empty($this->props['ap_pinterest']) ?
            sprintf('<a href="%1$s" class="df_person_social_icon pinterest" target="_blank" df-tooltip="Pinterest" title="" aria-expanded="false">
    %2$s
</a>', esc_url($this->props['ap_pinterest']), $this->df_social_icon(['unicode' => '&#xe095;', 'type' => "divi", 'weight' => 400])) : '';

        $email = !empty($this->props['ap_email']) ?
            sprintf('<a href="mailto:%1$s" class="df_person_social_icon email" target="_blank" df-tooltip="Email" title="" aria-expanded="false">
    %2$s
</a>', esc_attr($this->props['ap_email']), $this->df_social_icon(['unicode' => '&#xe076;', 'type' => "divi", 'weight' => 400])) : '';

        $phone = !empty($this->props['ap_phone']) ?
            sprintf('<a href="tel:%1$s" class="df_person_social_icon phone" target="_blank" df-tooltip="Phone" title="" aria-expanded="false">
    %2$s
</a>', esc_attr($this->props['ap_phone']), $this->df_social_icon(['unicode' => '&#xe090;', 'type' => "divi", 'weight' => 400])) : '';
        $socail_html = ($facebook !== '' || $twitter !== '' || $linkedin !== '' || $instagram !== '' || $pinterest !== '' || $email !== '' || $phone !== '') ?
            sprintf('<div class="df_person_socail_wrapper %8$s">
                                %1$s
                                %2$s
                                %3$s
                                %4$s
                                %5$s
                                %6$s
                                %7$s
                            </div>',
                $facebook,
                $twitter,
                $linkedin,
                $instagram,
                $pinterest,
                $email,
                $phone,
                $this->props['make_vertical_icon'] === 'on' && $this->props['style_type'] === 'default_style' ? 'vertical' : ''
            ) : '';
        $border_anim_class = ($this->props['style_type']==='default_style' && $this->props['border_anim'] === 'on') ? $this->props['border_anm_style'] : '';

        $icon_reveal_class = $this->props['always_show_icon'] === 'on' ?
            'always-show-title' : $this->props['icon_reveal_caption'];
        $overlay_social = $this->props['enable_icon_on_overlay'] === 'on' ? sprintf('<div class="%1$s">%2$s</div>',
            $icon_reveal_class,
            $socail_html
        ) : '';

        $overlay_content = ($this->props['style_type'] === 'default_style' && $this->props['enable_icon_on_overlay'] === 'on') ?
            sprintf('<figcaption class="df_ap_person_content %1$s ">
                            %2$s
                            %3$s
                            %4$s
                        </figcaption>',
                $this->props['content_position'] !== '' ? $this->props['content_position'] : '',
                isset($overlay_name) ? $overlay_name : '',
                isset($overlay_role) ? $overlay_role : '',
                isset($overlay_social) ? $overlay_social : ''
            )
            :
            '<figcaption class="df_ap_person_content"></figcaption>';

        $person_photo = !empty($this->df_render_image('ap_photo'))?
            sprintf('<div class="df_person_photo_wrapper">
                                    <div class=" %6$s %5$s">
                                        %2$s
                                        <div class="df_person_photo">
                                            %1$s
                                        </div>
                                        %3$s
                                    </div>
                                </div>',
                $this->df_render_image('ap_photo'),
                ($this->props['overlay'] === 'on' && $this->props['enable_alternative_photo'] !== 'on') ? '<span class="df-overlay"></span>' : '',
                $overlay_content,
                $this->props['image_scale_type'] !== '' ? $this->props['image_scale_type'] : '',
                $border_anim_class,
                ($this->props['overlay'] === 'on' || $this->props['border_anim'] === 'on' || $this->props['enable_alternative_photo'] === 'on' || $this->props['enable_icon_on_overlay'] === 'on') ? 'c4-izmir' : 'c4-izmir'
            ) : '';


        if ($this->props['style_type'] === 'default_style') {
            $person_html = sprintf('<div class="df_ap_person_wrapper %6$s">
                                    %1$s
                                    <div class="df_ap_person_desc_wrapper">
                                        <div class="df_person_details">
                                            %2$s
                                            %3$s
                                            %4$s
                                        </div>
                                        %5$s
                                    </div>
                                </div>',

                $person_photo,
                $name_html,
                $role_html,
                $description,
                (isset($this->props['enable_icon_on_overlay']) && $this->props['enable_icon_on_overlay'] !== 'on') ? $socail_html : '',
                $this->props['image_scale_type'] !== '' ? $this->props['image_scale_type'] : ''
            );
        } else if ($this->props['style_type'] === 'ekip_style') {
            $overlay_class = 'df_person_overlay';
            $person_html = sprintf('<div class="df_ap_person_wrapper">
                                    %1$s
                                    <div class="df_ap_person_desc %4$s">
                                        <div class="df_ap_person_desc_wrapper">
                                            %2$s
                                            %3$s
                                        </div>
                                    </div>
                                </div>
                            ', $person_photo,
                $member_details_html,
                $socail_html,
                $overlay_class
            );
        } else if ($this->props['style_type'] === 'ekip_style_2') {

            $person_html = sprintf('<div class="df_ap_person_wrapper %4$s">
                                        %1$s
                                        <div class="df_ap_person_desc">
                                            <div class="df_ap_person_desc_wrapper">
                                                %2$s
                                                %3$s
                                            </div>
                                        </div>
                                    </div>
                                ', $person_photo,
                $member_details_html,
                $socail_html,
                $this->props['image_scale_type'] !== '' ? $this->props['image_scale_type'] : ''
            );
        }

        $layout_class = 'df_ap_' . esc_attr($this->props['style_type']);

        $html_code = sprintf('<div class="df_ap_person_container %2$s"> %1$s</div>', $person_html, $layout_class);

        return sprintf('%1$s', $html_code);
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-advanced-person');

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
                'classnamesFunction' => [AdvancedPerson::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [AdvancedPerson::class, 'module_styles'],
                'scriptDataComponent' => [AdvancedPerson::class, 'module_script_data'],
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

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/advanced-person';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [AdvancedPerson::class, 'render_callback'],
                    ]
                );
            }
        );
    }

    public function df_social_icon($icon)
    {
        if(is_array($icon)){
            $icon = Utils::process_font_icon($icon);
        }
        return sprintf(
            '<span class="et-pb-icon socail_icon">%1$s</span>',
            $icon !== '' ?
                esc_attr(et_pb_process_font_icon($icon)) : '5'
        );
    }


    public function df_render_image($props_key)
    {
        if (isset($this->props[$props_key]) && !empty($this->props[$props_key]) ) {

            $photo_alt_text = $this->props['photo_alt_text'] !== '' ? $this->props['photo_alt_text'] : df_image_alt_by_url($this->props[$props_key]);
            $src = 'src';
            $image_html = sprintf(
                '<img class="person_photo" %3$s="%1$s" alt="%4$s" />',
                df_sanitize_src($this->props[$props_key]),
                $props_key,
                $src,
                $photo_alt_text
            );
            if (isset($this->props['ap_alternative_photo']) && $this->props['enable_alternative_photo'] === 'on' && !empty($this->props['ap_alternative_photo']) && $this->props['style_type'] === 'default_style') {
                $alternative_photo_alt_text = $this->props['alternative_photo_alt_text'] !== '' ? $this->props['alternative_photo_alt_text'] : df_image_alt_by_url($this->props['ap_alternative_photo']);
                $image_html .= sprintf(
                    '<img class="person_photo img-top" %3$s="%1$s" alt="%4$s"/>',
                    esc_url($this->props['ap_alternative_photo']),
                    'ap_alternative_photo',
                    $src,
                    $alternative_photo_alt_text
                );

                return sprintf(
                    '<div class="alter_image"> %1$s</div>',
                    $image_html
                );


            }

            return $image_html;

        }
    }


}
