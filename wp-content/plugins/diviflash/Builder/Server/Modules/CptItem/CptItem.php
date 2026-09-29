<?php

namespace DIFL\Server\Modules\CptItem;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use Df_Cpt_Taxonomy_Support;
use Df_Acf_Data_Process;
use Df_Pod_Data_Process;
use Df_MetaBox_Data_Process;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class CptItem implements DependencyInterface
{
    use ModuleUtil;
    use HasProps;

    use Df_Cpt_Taxonomy_Support;
    use Df_Acf_Data_Process;
    use Df_Pod_Data_Process;
    use Df_MetaBox_Data_Process;

    protected $config_file = 'CptItem';
    protected $module_credits = [
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => '',
    ];

    public $display_element_dependancy;

    public function __construct()
    {

        add_filter('difl_divi5_localize_data', function ($localize_data) {
            $this->init();
            $df_post_types       = [];
            $df_taxonomies       = [];
            $df_taxonomies_list  = [];
            $tax_settings        = [];
            $tax_list            = [];
            $df_acf_cpt_list     = [];
            $df_metabox_cpt_list = [];
            $df_pod_cpt_list     = [];
            $custom_field        = [];
            $type_options        = [];
            $df_acf_fields       = [];
            $df_acf_list         = [];
            $df_acf_fields_type  = [];
            $df_metabox_fields   = [];
            $df_pod_fields       = [];
            $df_pod_list         = [];
            $df_pod_fields_type  = [];

            if ($this->acf_extension === 'on' && class_exists('ACF')) {
                $type_options['acf_fields']['label'] = esc_html__('ACF Fields', 'divi_flash');
            }

            if ($this->pod_extension === 'on' && class_exists('Pods')) {
                $type_options['pod_fields']['label'] = esc_html__('Pods Field', 'divi_flash');
            }

            if ('on' === $this->metabox_extension && class_exists($this->metabox_dependent_class)) {
                $type_options['metabox_fields']['label'] = esc_html__('Meta Box Fields', 'divi_flash');
            }

            foreach ($this->df_post_types as $key => $value) {
                $df_post_types[$key]['label'] = $value;
                if ('select' === $key) {
                    continue;
                }

                foreach ($this->df_taxonomies[$key] as $tax_key => $tax_value) {
                    if ('select_tax' === $tax_key) {
                        continue;
                    }
                    $df_taxonomies_list[$key][] = ['label' => $tax_value, 'value' => $tax_key];
                }

                if (! array_key_exists($key, $this->df_acf_fields)) {
                    continue;
                }

                // foreach ($this->df_acf_fields[$key]['options'] as $acf_key => $acf_value) {
                //     if ('df_select_option' === $acf_key) {
                //         continue;
                //     }
                //     $df_acf_list[$key][] = ['label' => $acf_value, 'value' => $acf_key];
                // }

                // if (isset($this->df_pod_fields['pod-' . $key]['options']) && is_array($this->df_pod_fields['pod-' . $key]['options'])) {
                //     foreach ($this->df_pod_fields['pod-' . $key]['options'] as $pod_key => $pod_value) {
                //         if ('df_select_option' === $pod_key) {
                //             continue;
                //         }
                //         $df_pod_list[$key][] = ['label' => $pod_value, 'value' => $pod_key];
                //     }
                // }

                if (isset($this->df_acf_fields_for_filter[$key]) && is_array($this->df_acf_fields_for_filter[$key])) {
                    foreach ($this->df_acf_fields_for_filter[$key] as $acf_key => $acf_value) {
                        $df_acf_list[$key][] = ['label' => $acf_value, 'value' => $acf_key];
                    }
                }

                if (isset($this->df_pod_fields_for_filter[$key]) && is_array($this->df_pod_fields_for_filter[$key])) {
                    foreach ($this->df_pod_fields_for_filter[$key] as $pod_key => $pod_value) {
                        $df_pod_list[$key][] = ['label' => $pod_value, 'value' => $pod_key];
                    }
                }
            }

            foreach ($this->df_taxonomies as $key => $value) {

                if (is_array($value)) {
                    $options = [];
                    foreach ($value as $k => $v) {
                        $options[$k]['label'] = $v;
                    }
                }
                $df_taxonomies[$key] = $options;
            }
            foreach ($this->tax_settings as $key => $value) {
                $tax_settings[$key]['label'] = $value;
            }
            foreach ($this->tax_list as $key => $value) {
                if (is_array($value)) {
                    $options = [];
                    foreach ($value['options'] as $k => $v) {
                        $terms = get_terms([
                            'taxonomy'   => $k,
                            'hide_empty' => false,
                            'fields'     => 'id=>name',
                        ]);
                        foreach ($terms as $key_term => $term_value) {
                            $options[$k][] = ['label' => $term_value, 'value' => $key_term];
                        }
                    }
                }
                $tax_list[$key] = $options;
            }
            foreach ($this->df_acf_cpt_list as $key => $value) {
                $df_acf_cpt_list[$key]['label'] = $value;
            }
            foreach ($this->df_metabox_cpt_list as $key => $value) {
                $df_metabox_cpt_list[$key]['label'] = $value;
            }
            foreach ($this->df_pod_cpt_list as $key => $value) {
                $df_pod_cpt_list[$key]['label'] = $value;
            }

            foreach ($this->get_custom_fields_formatted_list() as $key => $value) {
                $custom_field[$key]['label'] = $value;
            }

            foreach ($this->df_acf_fields as $key => $value) {
                foreach ($value['options'] as $k => $v) {
                    $df_acf_fields[$key][$k]['label'] = $v;
                }
            }

            foreach ($this->df_metabox_fields as $key => $value) {
                foreach ($value['options'] as $k => $v) {
                    $df_metabox_fields[$key][$k]['label'] = $v;
                }
            }

            foreach ($this->df_pod_fields as $key => $value) {
                foreach ($value['options'] as $k => $v) {
                    $df_pod_fields[$key][$k]['label'] = $v;
                }
            }

            // if ( $this->df_multi_filter_acf_field_type ) {
            // 	$options = [];
            // 	foreach ( $this->df_multi_filter_acf_field_type as $filter_key => $filter_value ) {
            // 		$cpt = explode( '_', str_replace( 'acf_filter_field_type_', '', $filter_key ) );
            // 		foreach ( $filter_value['options'] as $k => $v ) {
            // 			$options[ $cpt[0] ][ $cpt[1] ]['options'][ $k ]['label'] = $v;
            // 			$options[ $cpt[0] ][ $cpt[1] ]['label']                  = $filter_value['label'];
            // 		}
            // 	}

            // 	$df_acf_fields_type = $options;
            // }

            if (! empty($this->df_multi_filter_acf_field_type)) {

                foreach ($this->df_multi_filter_acf_field_type as $filter_key => $filter_value) {

                    // Remove prefix "acf_filter_field_type_"
                    $stripped = str_replace('acf_filter_field_type_', '', $filter_key);

                    // Example:
                    // post_text → ['post', 'text']
                    // project_paragraph → ['project', 'paragraph']
                    $parts = explode('_', $stripped);

                    $cpt_type  = array_shift($parts);     // post / project
                    $field_key = implode('_', $parts);    // text, number, paragraph etc.

                    // Initialize wrapper for CPT if not exists
                    if (! isset($df_acf_fields_type[$cpt_type])) {
                        $df_acf_fields_type[$cpt_type] = [];  // ❗ NOT "acf" key now
                    }

                    // Build object for this field
                    $entry = [
                        'label'     => $filter_value['label'] ?? '',
                        'options'   => []
                    ];

                    // Add options
                    if (! empty($filter_value['options'])) {
                        foreach ($filter_value['options'] as $opt_key => $opt_label) {
                            $entry['options'][$opt_key] = ['label' => $opt_label];
                        }
                    }

                    // Assign to structure
                    $df_acf_fields_type[$cpt_type][$field_key] = $entry;
                }
            }

            // if ( $this->df_multi_filter_pod_field_type ) {
            // 	$options = [];
            // 	foreach ( $this->df_multi_filter_pod_field_type as $filter_key => $filter_value ) {
            // 		$cpt = explode( '_', str_replace( 'pod_filter_field_type_', '', $filter_key ) );
            // 		foreach ( $filter_value['options'] as $k => $v ) {
            // 			$options[ $cpt[0] ][ $cpt[1] ]['options'][ $k ]['label'] = $v;
            // 			$options[ $cpt[0] ][ $cpt[1] ]['label']                  = $filter_value['label'];
            // 		}
            // 	}

            // 	$df_pod_fields_type = $options;
            // }

            if (! empty($this->df_multi_filter_pod_field_type)) {

                foreach ($this->df_multi_filter_pod_field_type as $filter_key => $filter_value) {

                    // Remove prefix "pod_filter_field_type_"
                    $stripped = str_replace('pod_filter_field_type_', '', $filter_key);

                    // Example:
                    // post_text → ['post', 'text']
                    // project_paragraph → ['project', 'paragraph']
                    $parts = explode('_', $stripped);

                    $cpt_type  = array_shift($parts);     // post / project
                    $field_key = implode('_', $parts);    // text, number, paragraph etc.

                    // Initialize wrapper for CPT if not exists
                    if (! isset($df_pod_fields_type[$cpt_type])) {
                        $df_pod_fields_type[$cpt_type] = [];  // ❗ NOT "pod" key now
                    }

                    // Build object for this field
                    $entry = [
                        'label'     => $filter_value['label'] ?? '',
                        'options'   => []
                    ];

                    // Add options
                    if (! empty($filter_value['options'])) {
                        foreach ($filter_value['options'] as $opt_key => $opt_label) {
                            $entry['options'][$opt_key] = ['label' => $opt_label];
                        }
                    }

                    // Assign to structure
                    $df_pod_fields_type[$cpt_type][$field_key] = $entry;
                }
            }


            $localize_data['df_post_types']         = $df_post_types;
            $localize_data['df_taxonomies']         = $df_taxonomies;
            $localize_data['df_taxonomies_list']    = $df_taxonomies_list;
            $localize_data['tax_settings']          = $tax_settings;
            $localize_data['tax_list']              = $tax_list;
            $localize_data['post_type_for_acf']     = $df_acf_cpt_list;
            $localize_data['post_type_for_metabox'] = $df_metabox_cpt_list;
            $localize_data['post_type_for_pod']     = $df_pod_cpt_list;
            $localize_data['custom_field']          = $custom_field;
            $localize_data['type_options']          = $type_options;
            $localize_data['df_acf_fields']         = $df_acf_fields;
            $localize_data['df_acf_list']           = $df_acf_list;
            $localize_data['df_acf_fields_type']    = $df_acf_fields_type;
            $localize_data['df_metabox_fields']     = $df_metabox_fields;
            $localize_data['df_pod_fields']         = $df_pod_fields;
            $localize_data['df_pod_list']           = $df_pod_list;
            $localize_data['df_pod_fields_type']    = $df_pod_fields_type;

            return $localize_data;
        });
    }

    public function init()
    {
        $this->init_cpt_tax(
            'general',
            'settings',
            [
                'type' => 'taxonomy',
            ]
        );
        if (function_exists('acf_init')) {
            acf_init();
        }
        $this->df_acf_init();
        $this->df_pod_init();
        $this->df_metabox_init();

        $this->display_element_dependancy = [
            'author',
            'date',
            'taxonomy',
            'custom_text',
            'icon',
            'acf_fields',
            'pod_fields',
            'metabox_fields',
            'reading_time',
        ];
    }

    // public function font_selector($type = 'main')
    // {
    //     $selector = [
    //         'main' => [
    //             "{$this->main_css_element} .df-cpt-title",
    //             "{$this->main_css_element} .df-cpt-title a",
    //             "{$this->main_css_element}.df-item-wrap > span",
    //             "{$this->main_css_element}.df-item-wrap a",
    //             "{$this->main_css_element}.df-item-wrap",
    //             "{$this->main_css_element}.df-item-wrap p",
    //             "{$this->main_css_element} .df-cpt-read-more",
    //             "{$this->main_css_element} .df-cpt-custom-text",
    //         ],
    //     ];

    //     if ($type === 'main') {
    //         return implode(', ', $selector['main']);
    //     } else if ($type === 'hover') {
    //         return '.df-cpt-outer-wrap:hover ' . implode(',.df-cpt-outer-wrap:hover ', $selector['main']);
    //     }
    // }

    private function get_custom_fields_formatted_list($post_type = 'post')
    {
        $custom_fields = $this->et_builder_get_most_used_post_meta_keys();

        $formatted_fields = [
            'select' => esc_html__('Select Type', 'divi_flash'),
        ];

        if (! empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                $formatted_fields[$field] = esc_html__($field, 'divi_flash');
            }
        }

        return $formatted_fields;
    }

    protected function et_builder_get_most_used_post_meta_keys()
    {
        global $wpdb;

        $most_used_meta_keys = get_transient('et_builder_most_used_meta_keys');
        if (false !== $most_used_meta_keys) {
            return $most_used_meta_keys;
        }

        $public_post_types = array_keys(et_builder_get_public_post_types());
        $post_types        = "'" . implode("','", esc_sql($public_post_types)) . "'";

        $sql = "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON ( p.ID = pm.post_id AND p.post_type IN ({$post_types}) )
		WHERE pm.meta_key NOT LIKE '\_%'
		GROUP BY pm.meta_key
		ORDER BY COUNT(pm.meta_key) DESC
		LIMIT 50";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql query does not use users/visitor input
        $most_used_meta_keys = $wpdb->get_col($sql);

        set_transient('et_builder_most_used_meta_keys', $most_used_meta_keys, 5 * MINUTE_IN_SECONDS);

        return $most_used_meta_keys;
    }

    private function get_image_aspect_ratio($size)
    {
        // Keys and order match module.json image_size options (original = natural aspect).
        $ratio_mapper = [
            'original' => 'auto',
            'large'   => '8/5',
            'mid-hr'   => '7/9',
            'mid'      => '8/5',
            'mid-squ'  => '1/1',
            'sm-squ'   => '1/1',
        ];

        return $ratio_mapper[$size] ?? '1/1';
    }

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/cptitem' )->customCssFields;
	}

    function propMatches($key, $value)
    {
        return isset($this->props[$key]) && $this->props[$key] === $value;
    }

    function getOverlayStyle($attr)
    {
        $styles = '';
        if ($this->propMatches('overlay', 'on')) {
            $overlay_direction = $attr['overlay_direction'] ?? '180deg';
            $overlay_primary   = $attr['overlay_primary'] ?? '#00b4db';
            $overlay_secondary = $attr['overlay_secondary'] ?? '#0083b0';

            $styles  .= "background-image: linear-gradient(" . $overlay_direction . ", " . $overlay_primary . " 0, " . $overlay_secondary . " 100%);";
        }
        return $styles;
    }

    function getDividerColorStyle($attr)
    {
        $styles = '';
        if ($this->propMatches('type', 'divider')) {
            $divider_direction = $attr['divider_color_direction'] ?? '90deg';
            $divider_primary   = $attr['divider_color_primary'] ?? '#e02b20';
            $divider_secondary = $attr['divider_color_secondary'] ?? '#fc7069';
            $divider_start     = $attr['divider_color_start'] ?? '0%';
            $divider_end       = $attr['divider_color_end'] ?? '100%';

            $styles  .= "background-image: linear-gradient(" . $divider_direction . ", " . $divider_primary . " " . $divider_start . ", " . $divider_secondary . " " . $divider_end . ");";
        }
        return $styles;
    }

    // public function get_hover_values($attrs, $key)
    // {
    //     if (isset($attrs[$key]['innerContent']['desktop']['hover'])) {
    //         return true;
    //     }
    //     return false;
    // }

    public function get_common_styles($args)
    {
        $attrs              = $this->get_merged_attrs($args);
        $attrs              = self::build_attr_for_styles($attrs);
        $attrs              = $this->get_default_value_for_styles($attrs);
        $orderClass         = $args['orderClass'] ?? '';
        $meta               = [
            'author',
            'date',
            'taxonomy',
            'custom_text',
            'icon',
            'acf_fields',
            'pod_fields',
            'metabox_fields',
        ];

        // var_dump($attrs);

        $meta_display_props = isset($this->props['meta_display']) ? $this->props['meta_display'] : 'inline-flex';

        $icon_image_vertical_align = [
            'text-top'    => 'flex-start',
            'middle'      => 'center',
            'text-bottom' => 'flex-end',
        ];

        $styles = [];

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .item-price",
            'attr'     => $attrs['price_gap']['innerContent'] ?? null,
            'property' => 'gap',
        ]);


        if (! empty($this->props['meta_before_text']) && '' !== $this->props['meta_before_text']) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:before",
                'attr'     => $attrs['meta_before_text']['innerContent'] ?? null,
                'property' => 'content',
            ]);
        }

        if (in_array($this->props['type'], $meta)) {
            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass}.df-item-wrap",
                'attr'      => $attrs['meta_display']['innerContent'] ?? null,
                'property'  => 'display'
            ]);

            if ($meta_display_props !== 'block') {
                $common_styles[] = CommonStyle::style([
                    'selector'  => "{$orderClass}",
                    'attr'      => $attrs['meta_position']['innerContent'] ?? null,
                    'property'  => 'float'
                ]);
            }
        }

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass}.df-item-wrap .df-icon-image",
            'attr'      => $attrs['icon_image_width']['innerContent'] ?? null,
            'property'  => 'width',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass}.df-item-wrap .df-icon-image",
            'attr'      => $attrs['icon_image_verticle_align']['innerContent'] ?? null,
            'property'  => 'vertical-align',
            'important' => true,
        ]);

        if ('inline-flex' !== $meta_display_props) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap .df-icon-image",
                'attr'     => $attrs['icon_image_verticle_align']['innerContent'] ?? null,
                'property' => 'vertical-align',
            ]);
        } else {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass}.df-item-wrap",
                'attr'                => $attrs['icon_image_verticle_align']['innerContent'] ?? null,
                'declarationFunction' => function () use ($icon_image_vertical_align) {
                    return "align-items: {$icon_image_vertical_align[$this->props['icon_image_verticle_align'] ?? '']};";
                },
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .et-pb-icon",
            'attr'     => $attrs['icon_color']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                    return 'color: ' . $props['attrValue'];
                }
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => ".df-cpt-outer-wrap:hover {$orderClass} .et-pb-icon",
            'attr'     => $attrs['icon_color']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                    return 'color: ' . $props['attrValue'];
                }
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}.df-item-wrap .et-pb-icon",
            'attr'     => $attrs['icon_size']['innerContent'] ?? null,
            'property' => 'font-size',
        ]);

        if (! empty($this->props['image_size'])) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df-cpt-image-wrap img",
                'attr'                => $attrs['image_size']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'aspect-ratio: ' . $this->get_image_aspect_ratio($this->props['image_size'] ?? '') . ';';
                },
            ]);
        }

        if ('on' === ($this->props['image_full_width'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df-cpt-image-wrap img",
                'attr'                => $attrs['image_full_width']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'width: 100%;';
                },
            ]);
        }

        if ('df-image-rotate-left' === ($this->props['image_scale'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".df-hover-trigger:hover {$orderClass} .df-image-rotate-left img, :focus.df-hover-trigger:hover {$orderClass} .df-image-rotate-left img",
                'attr'                => $attrs['image_scale']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: scale({$this->props['image_scale_hover']}) rotate(-15deg);";
                },
            ]);
        }

        if ('df-image-rotate-right' === ($this->props['image_scale'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".df-hover-trigger:hover {$orderClass} .df-image-rotate-right img, :focus.df-hover-trigger:hover {$orderClass} .df-image-rotate-right img",
                'attr'                => $attrs['image_scale']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: scale({$this->props['image_scale_hover']}) rotate(15deg);";
                },
            ]);
        }

        if ('on' === ($this->props['overlay'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df-hover-effect .df-overlay",
                'attr'                => $attrs['overlay_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return $this->getOverlayStyle($props['attrValue'] ?? []);
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap .df-icon-overlay",
                'attr'     => $attrs['overlay_icon_size']['innerContent'] ?? null,
                'property' => 'font-size',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap .df-icon-overlay",
                'attr'     => $attrs['overlay_icon_color']['innerContent'] ?? null,
                'property' => 'color',
            ]);
        }

        if ('divider' === ($this->props['type'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df-cpt-ele-divider",
                'attr'                => $attrs['divider_color']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return $this->getDividerColorStyle($props['attrValue'] ?? []);
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap .df-cpt-ele-divider",
                'attr'     => $attrs['divider_line_height']['innerContent'] ?? null,
                'property' => 'height',
            ]);
        }

        if ('acf_fields' === ($this->props['type'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap img.df-acf-image",
                'attr'     => $attrs['acf_image_width']['innerContent'] ?? null,
                'property' => 'max-width',
            ]);
        }

        if ('pod_fields' === ($this->props['type'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}.df-item-wrap img.df-pod-image",
                'attr'     => $attrs['pod_image_width']['innerContent'] ?? null,
                'property' => 'max-width',
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df-icon-overlay",
            'attr'                => $attrs['overlay_font_icon']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return $this->get_font_style($this->props['overlay_font_icon'] ?? '');
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .et-pb-icon",
            'attr'                => $attrs['font_icon']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return $this->get_font_style($this->props['font_icon'] ?? '');
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}",
            'attr' => $attrs['alignment']['advanced']['text']['text'] ?? null,
            'declarationFunction' => function ($props) {
                
                $orientation = isset($props['attrValue']['orientation']) ? $props['attrValue']['orientation'] : null;
                
                if ($orientation) {
                    return "text-align: {$orientation} !important;";
                }
                return "";
            }
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}",
            'attr' => $attrs['module']['decoration']['sizing'] ?? null,
            'declarationFunction' => function ($props) {
                $alignment = isset($props['attrValue']['alignSelf']) ? $props['attrValue']['alignSelf'] : null;
                if( $alignment === "flex-start" ) {
					return "margin-left: 0 !important; margin-right: auto !important;";
                } else if ( $alignment === "end" ) {
                    return "margin-left: auto !important; margin-right: 0 !important;";
                } else if ( $alignment === "center" ) {
                    return "margin-left: auto !important; margin-right: auto !important;";
                }

                return '';
            }
        ]);


        foreach ($common_styles as $common_style) {
            if (empty($common_style)) {
                continue;
            }

            foreach ($common_style as $style) {
                $styles[] = $style;
            }
        }

        return $styles ?? [];
    }

    public function render($attrs, $content, $render_slug)
    {
        global $df_cpt_items, $df_cpt_items_outside;

        $this->init();

        $type_settings = [];

        // Defaults below match CptItem module.json (Visual-Builder). Use ?? for missing props.
        $props_type = $this->props['type'] ?? 'select';
        if ( 'select' !== $props_type ) {
            $type_settings['type']  = $props_type;
            $type_settings['class'] = $this->order_class;
            // title — default: "h2"
            $type_settings['title_tag'] = $this->props['title_tag'] ?? 'h2';
            // content — defaults: "excerpt", "off", "270"
            $type_settings['post_content']    = $this->props['post_content'] ?? 'excerpt';
            $type_settings['use_post_excrpt'] = $this->props['use_post_excrpt'] ?? 'off';
            $type_settings['excerpt_length']  = $this->props['excerpt_length'] ?? '270';
            // meta — defaults: "M j, Y", "off", "16", "off"
            $type_settings['date_format']       = str_replace( '%92', '\\', $this->props['date_format'] ?? 'M j, Y' );
            $type_settings['show_author_image'] = $this->props['show_author_image'] ?? 'off';
            $type_settings['author_image_size'] = $this->props['author_image_size'] ?? '16';
            $type_settings['hide_author_text']  = $this->props['hide_author_text'] ?? 'off';
            // Read More — default: "Read More"
            $type_settings['read_more_text'] = $this->props['read_more_text'] ?? 'Read More';
            // icon — default use_icon: "off"
            $type_settings['image_icon'] = $this->props['image_icon'] ?? '';
            $type_settings['use_icon']   = $this->props['use_icon'] ?? 'off';
            $type_settings['font_icon']  = ( ( $this->props['font_icon'] ?? '' ) !== '' )
                ? esc_attr( Utils::process_font_icon( $this->props['font_icon'] ) ) : '5';
            // image — default: "mid"
            $type_settings['image_size'] = $this->props['image_size'] ?? 'mid';

            // placement — default: "off"
            $type_settings['placement'] = $this->props['outside_wrapper'] ?? 'off';
            // overlay / scale — defaults: "no-image-scale", "off", "off", "df-fade-up"
            $type_settings['image_scale'] = $this->props['image_scale'] ?? 'no-image-scale';
            $type_settings['overlay']     = $this->props['overlay'] ?? 'off';
            $type_settings['overlay_icon']        = $this->props['overlay_icon'] ?? 'off';
            $type_settings['overlay_icon_reveal'] = $this->props['overlay_icon_reveal'] ?? 'df-fade-up';
            $type_settings['overlay_font_icon']   = ! empty( $this->props['overlay_font_icon'] ) ? $this->props['overlay_font_icon'] : '1';

            // custom text — use_custom_field default: "off"
            $type_settings['custom_text'] = $this->props['custom_text'] ?? '';

            // taxonomy — custom_field default: "select", use_custom_field: "off"
            $type_settings['custom_field']     = $this->props['custom_field'] ?? 'select';
            $type_settings['use_custom_field'] = $this->props['use_custom_field'] ?? 'off';
            $type_settings['before_tax']       = '';
            $type_settings['separator_tax']    = ', ';
            $type_settings['after_tax']        = '';
            $post_type_key = $this->props['post_type'] ?? '';
            $type_settings['taxonomy'] = ( $post_type_key !== '' && isset( $this->props['tax_for_' . $post_type_key ] ) )
                ? $this->props['tax_for_' . $post_type_key ] : '';
            if ( ( $this->props['enable_tax_custom_separator'] ?? '' ) === 'on' ) {
                $type_settings['separator_tax']    = $this->props['tax_custom_separator_text'] ?? ', ';
                $type_settings['tax_before_label'] = $this->props['tax_before_label'] ?? '';
                $type_settings['tax_after_label']  = $this->props['tax_after_label'] ?? '';
            }

            // $check = $this->acf_extension;
            // $check2 = get_option( 'df_general_acf_field_support' );


            // acf fields
            if (
                'acf_fields' === $props_type &&
                class_exists( 'ACF' ) &&
                'on' === $this->acf_extension
            ) {
                $type_settings['post_type_for_acf']   = $this->props['post_type_for_acf'] ?? '';
                $post_type_for_acf = $this->props['post_type_for_acf'] ?? '';
                $type_settings['acf_field'] = ( ! empty( $post_type_for_acf ) && $post_type_for_acf !== 'select' && isset( $this->props['acf_' . $post_type_for_acf ] ) )
                    ? $this->props['acf_' . $post_type_for_acf ] : '';

                $type_settings['acf_before_label']    = $this->props['acf_before_label'] ?? '';
                $type_settings['acf_after_label']     = $this->props['acf_after_label'] ?? '';
                $type_settings['acf_url_text']        = $this->props['acf_url_text'] ?? '';
                $type_settings['acf_url_new_window']  = $this->props['acf_url_new_window'] ?? 'off';
                $type_settings['acf_email_text']      = $this->props['acf_email_text'] ?? '';
            }

            // Pods Field
            if (
                'pod_fields' === $props_type &&
                class_exists( 'Pods' ) &&
                'on' === $this->pod_extension
            ) {
                $type_settings['post_type_for_pod'] = $this->props['post_type_for_pod'] ?? '';
                $post_type_for_pod = $this->props['post_type_for_pod'] ?? '';
                $type_settings['pod_field'] = ( ! empty( $post_type_for_pod ) && $post_type_for_pod !== 'select' && isset( $this->props['pod_fields_for_' . $post_type_for_pod ] ) )
                    ? $this->props['pod_fields_for_' . $post_type_for_pod ] : '';

                $type_settings['pod_before_label']   = $this->props['pod_before_label'] ?? '';
                $type_settings['pod_after_label']    = $this->props['pod_after_label'] ?? '';
                $type_settings['pod_url_text']       = $this->props['pod_url_text'] ?? '';
                $type_settings['pod_url_new_window'] = $this->props['pod_url_new_window'] ?? 'off';
                $type_settings['pod_email_text']     = $this->props['pod_email_text'] ?? '';
            }
            if ( ( $this->props['use_custom_field'] ?? '' ) === 'on' ) {
                $type_settings['custom_field_before_label'] = $this->props['custom_field_before_label'] ?? '';
                $type_settings['custom_field_after_label']  = $this->props['custom_field_after_label'] ?? '';
            }

            // Meta Box fields
            if (
                'metabox_fields' === $props_type &&
                class_exists( $this->metabox_dependent_class ) &&
                'on' === $this->metabox_extension
            ) {
                $type_settings['post_type_for_metabox'] = $this->props['post_type_for_metabox'] ?? '';
                $post_type_for_metabox = $this->props['post_type_for_metabox'] ?? '';
                $type_settings['metabox_field'] = ( ! empty( $post_type_for_metabox ) && $post_type_for_metabox !== 'select' && isset( $this->props['metabox_fields_for_' . $post_type_for_metabox ] ) )
                    ? $this->props['metabox_fields_for_' . $post_type_for_metabox ] : '';

                $type_settings['metabox_before_label']   = $this->props['metabox_before_label'] ?? '';
                $type_settings['metabox_after_label']    = $this->props['metabox_after_label'] ?? '';
                $type_settings['metabox_url_text']       = $this->props['metabox_url_text'] ?? '';
                $type_settings['metabox_url_new_window'] = $this->props['metabox_url_new_window'] ?? 'off';
                $type_settings['metabox_email_text']     = $this->props['metabox_email_text'] ?? '';
            }

            // Background
            $type_settings['background_enable_mask_style']    = $this->props['background_enable_mask_style'] ?? 'off';
            $type_settings['background_enable_pattern_style'] = $this->props['background_enable_pattern_style'] ?? 'off';

            // Reading Time
            $type_settings['reading_time_before_label'] = $this->props['reading_time_before_label'] ?? '';
            $type_settings['reading_time_after_label']  = $this->props['reading_time_after_label'] ?? '';
        }

        if ( ( $this->props['outside_wrapper'] ?? '' ) === 'on' ) {
            $df_cpt_items_outside[] = $type_settings;
        } else {
            $df_cpt_items[] = $type_settings;
        }

        return;
    }
}
