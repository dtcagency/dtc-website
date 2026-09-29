<?php
/**
 * TableRow Module class.
 *
 * @package DIVIFLASH5\Modules\TableRow;
 */

namespace DIFL\Server\Modules\TableRow;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;


/**
 * Class TableRow
 *
 * @package DIVIFLASH5\Modules\TableRow
 */
class TableRow implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/datatableitem')->customCssFields;
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
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/datatableitem');
        $styles = [];

        if ( $instance->props['full_width_badge'] === "on" ) {
            $styles[]=CommonStyle::style( [
                "selector"            => "{$order_class} th.df_dt_table_body_column_cell.badge .table_badge",
                "attr"                => $attrs['badge_obj_settings']['innerContent'],
                "declarationFunction" => fn( $props ) => "width:100%;"
            ] );
        }

        if ( $instance->props['full_width_badge'] !== "on" ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "table.df_dt_content tr{$order_class} th.df_dt_table_body_column_cell.badge .table_badge",
                "attr"                => $attrs['badge_obj_settings']['innerContent'],
                "declarationFunction" => function ( $props ) {
                    if ( $props['attrValue']['badge_alignment'] === "center" ) {
                        return "left: 50%; transform: translate(-50%, -100%);";
                    }
                    if ( $props['attrValue']['badge_alignment'] === "right" ) {
                        return "right: 0; left: unset;";
                    }

                    return '';
                }
            ] );
        }

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

    public function render($id,$parent_attrs)
    {

        global $table_data;
        global $data_lebel;
        global $marge_settings;

        $order_class =$id;
//        $parent_module = self::get_parent_modules('page')['difl_datatable'];
        $marge_settings['row_merge'] = $this->props['enable_row_merge'];
        $marge_settings['col_merge'] = $this->props['enable_column_merge'];

        $tablerowsData = $this->props['row'];

        $rowCounts = array();
        $tableDataArray = array();
        $tags = array("<p>", "</p>");
        $tablerowsData = trim(str_replace($tags, "", $tablerowsData), "\n");
        $RowData = explode("\n", str_replace("\r", "", $tablerowsData));

        $rowCounts = count($RowData);
        $tableDataArray = $RowData;

        if (($parent_attrs['responsive_obj_settings']['innerContent']['desktop']['value']['responsive_mode']??"on")==="on" && $this->props['row_type'] === 'head') {
            $data_lebel = $RowData;
        }



        $tag = 'td';
        if ($this->props['row_type'] === 'head') {
            $tag = 'th';
        }

        $row_html = sprintf('<tr class="%1$s">', $order_class);
        for ($index = 0; $index < $rowCounts; $index++) {

            if ($index % 2 === 0 && $index !== 0) {
                $row_class = '';
            } else {
                $row_class = '';
            }
            if ($index === 0 && $this->props['body_row_span_item_value'] !== '' && $marge_settings['row_merge'] === 'on') {

                $row_span_code = sprintf('rowspan="%1$s"', esc_attr($this->props['body_row_span_item_value']));
            } else {
                $row_span_code = '';
            }

            if ($index + 1 == $this->props['body_col_span_item'] && $this->props['body_col_span_item_value'] !== '' && $marge_settings['col_merge'] === 'on') {

                $body_col_span_code = sprintf('colspan="%1$s"', esc_attr($this->props['body_col_span_item_value']));
            } else {
                $body_col_span_code = '';
            }
            $class = 'df_dt_table_body_column_cell';
            if (($this->props['row_type'] === 'head' || $this->props['row_type'] === 'foot') &&  ($parent_attrs['head_obj_settings']['innerContent']['desktop']['value']['exclude_head_foot']??"off") === 'on') {
                $class .= ' exclude_head_foot';
            }

            if (($this->props['row_type'] === 'head' || $this->props['row_type'] === 'foot' ) &&  ($parent_attrs['head_obj_settings']['innerContent']['desktop']['value']['exclude_head_foot_last_col']??'off')==='on' ) {
                $class .= ' exclude_head_foot_last_col';
            }
            // Badge
            if ($this->props['row_type'] === 'head' && $this->props['badge_enable'] === 'on' && $index + 1 == $this->props['badge_position'] && $this->props['badge'] !== '') {
                $badge_text_html = sprintf('<div class="table_badge"><span class="table_badge_text">%1$s</span></div>', $this->props['badge']);
                $class .= ' badge';
            } else {
                $badge_text_html = '';
            }
            $row_html .= sprintf(
                '<%7$s data-label="%6$s" %4$s %5$s class="%2$s" >%8$s %1$s</%7$s>',
                $RowData[$index],
                $class,
                $row_class,
                $row_span_code,
                $body_col_span_code,
                (!empty($data_lebel[$index])) ? wp_strip_all_tags($data_lebel[$index]) : '',
                $tag,
                $badge_text_html
            );

        }
        $row_html .= '</tr>';

        if (isset($this->props['row_type']) && $this->props['row_type'] === 'head') {
            if (isset($table_data['head'])) {
                $table_data['head'] .= $row_html;
            } else {
                $table_data['head'] = $row_html;
            }

        } else if (isset($this->props['row_type']) && $this->props['row_type'] === 'foot') {
            if (isset($table_data['foot'])) {
                $table_data['foot'] .= $row_html;
            } else {
                $table_data['foot'] = $row_html;
            }
        } else {
            if (isset($table_data['body'])) {
                $table_data['body'] .= $row_html;
            } else {
                $table_data['body'] = $row_html;
            }
        }

        return;
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
        $parent_attrs = $parent->attrs ?? [];

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-table-row');

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
                'classnamesFunction' => [TableRow::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [TableRow::class, 'module_styles'],
                'scriptDataComponent' => [TableRow::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render(str_replace(["-","/"],"_",$block->parsed_block['id']),$parent_attrs),
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/table-row';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [TableRow::class, 'render_callback'],
                    ]
                );
            }
        );
    }


}
