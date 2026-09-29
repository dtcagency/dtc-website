<?php
/**
 * Table Module class.
 *
 * @package DIVIFLASH5\Modules\Table;
 */

namespace DIFL\Server\Modules\Table;

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
 * Class Table
 *
 * @package DIVIFLASH5\Modules\Table
 */
class Table implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/datatable')->customCssFields;
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
            if ( ! isset( $attrs[ $key ] ) ) {
                $attrs[ $key ] = array();
            }
            if (empty($attrs[$key]['innerContent'])) {
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
        $orderClass = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $orderClass, 'difl/datatable');
        $styles = [];
        if ($instance->props['make_head_cell_equal_border'] === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content thead tr th.df_dt_table_body_column_cell:not(:last-child)",
                'attr' => $attrs['head_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-right:0px;"
            ]);
        }

        if ($instance->props['make_foot_cell_equal_border'] === "on" && $instance->props['responsive_mode'] === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tfoot tr .df_dt_table_body_column_cell:not(:last-child)",
                'attr' => $attrs['foot_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-right:0px;"
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tfoot tr .df_dt_table_body_column_cell:first-child",
                'attr' => $attrs['foot_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-bottom:0px;"
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tfoot tr:last-child .df_dt_table_body_column_cell",
                'attr' => $attrs['foot_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-top:0px;"
            ]);
        }

        if ($instance->props['make_foot_cell_equal_border'] === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tfoot tr .df_dt_table_body_column_cell:not(:last-child)",
                'attr' => $attrs['foot_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-right:0px;"
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tfoot tr:last-child .df_dt_table_body_column_cell",
                'attr' => $attrs['foot_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-top:0px;"
            ]);
        }

        if ($instance->props['make_row_cell_equal_border'] === "on") {
            if ($instance->props['responsive_mode'] === 'on') {
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr:first-child .df_dt_table_body_column_cell",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-top:0px;",
                    "atRules" => '@media only screen and (min-width: 768px)'
                ]);
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr > td.df_dt_table_body_column_cell:not(:last-child)",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-right:0px;",
                    "atRules" => '@media only screen and (min-width: 768px)'
                ]);
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr:not(:last-child) .df_dt_table_body_column_cell",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-bottom:0px;"
                ]);
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr:last-child .df_dt_table_body_column_cell:not(:last-child)",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-bottom:0px;",
                    "atRules" => '@media only screen and (max-width: 767px)'
                ]);
            } else {
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr:first-child .df_dt_table_body_column_cell",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-top:0px !important;"
                ]);
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr > td.df_dt_table_body_column_cell:not(:last-child)",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-right:0px !important;"
                ]);
                $styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} table.df_dt_content tbody tr:not(:last-child) .df_dt_table_body_column_cell",
                    'attr' => $attrs['row_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn($props) => "border-bottom:0px !important;"
                ]);
            }
        }

        if ($instance->props['make_row_last_cell_border_right_0'] === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tr > td.df_dt_table_body_column_cell:last-child",
                'attr' => $attrs['row_last_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-right:0px;"
            ]);
        }

        if ($instance->props['make_row_first_cell_border_left_0'] === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} table.df_dt_content tr > td.df_dt_table_body_column_cell:first-child",
                'attr' => $attrs['row_first_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-left:0px;"
            ]);
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $orderClass, $attrs, $elements->module_metadata->attributes ?? [] ) );

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
        global $table_data;
        global $marge_settings;
        $raw_table_data = $table_data;
        $responsive_mode = $this->props['responsive_mode'] === 'on' ? 'responsive_mode_active' : 'scroll_mode_active';
        $html_code = sprintf(
            '<div class="df_dt_container %4$s">
                    <div class="df_dt_wrapper">
                        <table class="df_dt_content">
                            <thead>%1$s</thead>
                            <tbody>%2$s</tbody>
                            <tfoot>%3$s</tfoot>
                        </table>
                    </div>
                </div>',
            (!empty($raw_table_data['head'])) ? $raw_table_data['head'] : '',
            (!empty($raw_table_data['body'])) ? $raw_table_data['body'] : '',
            (!empty($raw_table_data['foot'])) ? $raw_table_data['foot'] : '',
            $responsive_mode
        );

        $table_data = array();
        return $html_code;
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-table');

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
                'classnamesFunction' => [Table::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [Table::class, 'module_styles'],
                'scriptDataComponent' => [Table::class, 'module_script_data'],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/table';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [Table::class, 'render_callback'],
                    ]
                );
            }
        );
    }


}
