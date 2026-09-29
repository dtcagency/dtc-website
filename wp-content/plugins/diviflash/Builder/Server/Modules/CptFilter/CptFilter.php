<?php

namespace DIFL\Server\Modules\CptFilter;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use Df_Cpt_Taxonomy_Support;
use Df_Acf_Data_Process;
use Df_Pod_Data_Process;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class CptFilter implements DependencyInterface
{
    use ModuleUtil;
    use HasProps;

    protected $config_file = 'CptFilter';

    public $slug = 'difl_cptfilter';
    public $vb_support = 'on';
    public $child_slug = 'difl_cptitem';
    public $icon_path, $filter_modile_view_header, $filter_modile_view_footer;
    use Df_Cpt_Taxonomy_Support;
    use Df_Acf_Data_Process;
    use Df_Pod_Data_Process;

    protected $module_credits = [
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => '',
    ];

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/cptfilter' )->customCssFields;
	}

    //	public function init() {
    //		$this->name             = esc_html__( 'Filterable CPT', 'divi_flash' );
    //		$this->main_css_element = "%%order_class%%";
    //		$this->icon_path        = DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/cpt-filter.svg';
    //		$this->init_cpt_tax(
    //			'general',
    //			'settings',
    //			[],
    //			[],
    //			[
    //				'terms' => 'Filter by terms:',
    //			]
    //		);
    //		$this->df_acf_init();
    //		$this->df_pod_init();
    //
    //		$this->filter_modile_view_header = "{$this->main_css_element} .df_phn_resp_cls .difl_filter_short_desc_card_header";
    //		$this->filter_modile_view_footer = "{$this->main_css_element} .df_phn_resp_cls .difl_filter_short_desc_card";
    //	}


    // public function before_render()
    // {
    //     $this->props['equal_height__hover']         = '1px||||false|false';
    //     $this->props['equal_height__hover_enabled'] = 'on|hover';
    // }

    public static function get_item_row_data($multi_filter_item_in_row)
    {
        $flex  = '25%';
        $width = '24%';
        switch ($multi_filter_item_in_row) {
            case '1':
                $flex  = '100%';
                $width = '100%';
                break;
            case '2':
                $flex  = '49%';
                $width = '50%';
                break;
            case '3':
                $flex  = '32%';
                $width = '33.33%';
                break;
            case '5':
                $flex  = '19%';
                $width = '20%';
                break;
            case '6':
                $flex  = '16%';
                $width = '16.66%';
                break;
            default:
                $flex  = '24%';
                $width = '25%';
                break;
        }

        return [
            "flex"  => $flex,
            "width" => $width,
        ];
    }

    /**
     * Get blog posts for cptfilter module
     *
     * @return string blog post markup
     */
    public function get_posts($df_cpt_items, $df_cpt_items_outside)
    {

        global $post, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged;

        $main_query = $wp_the_query;

        $use_current_loop        = isset($this->props['use_current_loop']) ? $this->props['use_current_loop'] : 'off';
        $post_type               = $this->props['post_type'] ?: 'project';
        $post_display            = isset($this->props['post_display']) ? sanitize_text_field($this->props['post_display']) : 'recent';
        $offset_number           = $this->props['offset_number'] ?: '0';
        $posts_number            = $this->props['posts_number'] ?: 10;
        $initial_term_id         = '';
        $order_by                = $this->props['orderby'];

        if (! isset($this->props['post_type']) || 'select' === $this->props['post_type']) {
            return;
        }

        $query_args = [
            'posts_per_page' => intval($posts_number),
            'post_status'    => ['publish'],
            'perm'           => 'readable',
            'post_type'      => $post_type,
        ];

        // order by
        if (isset($order_by) && $order_by === '2') {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'ASC';
        } else if (isset($order_by) && '3' === $order_by) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'ASC';
        } else if (isset($order_by) && '4' === $order_by) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'DESC';
        } else if (isset($order_by) && $order_by === '5') {
            $query_args['orderby'] = 'rand';
        } else if (isset($order_by) && '6' === $order_by) {
            $query_args['orderby'] = 'menu_order';
            $query_args['order']   = 'ASC';
        } else if (isset($order_by) && '7' === $order_by) {
            $query_args['orderby'] = 'menu_order';
            $query_args['order']   = 'DESC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }

        // display post_types by taxonomies
        $this->d5_get_taxonomy_values();
        if ('multiple_filter' === $post_display && ! empty($this->selected_taxonomy_list)) {

            $terms_query      = [];
            $multi_texonomies = $this->selected_taxonomy_list;
            foreach ($multi_texonomies as $multi_texonomy) {

                $terms = get_terms([
                    'taxonomy'   => $multi_texonomy,
                    'hide_empty' => true,
                ]);

                $permittedValues = array_values($terms);

                $all_terms = $str = array_column($permittedValues, 'term_id');

                $terms_query[] = [
                    'taxonomy' => $multi_texonomy,
                    'field'    => 'term_id',
                    'terms'    => $all_terms,
                ];
            }
            //$multi_filter_type = isset($this->props['multi_filter_type']) ? sanitize_text_field($this->props['multi_filter_type']) : '';
            if (! empty($terms_query)) {
                $query_args['tax_query']             = $terms_query;
                $query_args['tax_query']['relation'] = 'OR'; //$multi_filter_type;
            }
        } else if ('' !== $this->selected_terms) {
            $selected_terms  = is_array($this->selected_terms) ? $this->selected_terms : explode(',', $this->selected_terms);
            $initial_term_id = $selected_terms[0];
            if (array_key_exists('current', $this->selected_terms) && is_single()) {
                // Get the actual current post ID using ET_Post_Stack (more reliable than global $post)
                $main_query_post = \ET_Post_Stack::get_main_post();
                $actual_post_id = ( null !== $main_query_post && isset( $main_query_post->ID ) ) ? $main_query_post->ID : $current_post_id;
                
                $current_terms = implode(",", array_column(wp_get_post_terms($actual_post_id , $this->selected_taxonomy), "term_taxonomy_id") );
                $terms = explode(',', $current_terms);
            } else {
                $terms = 'current' !== $initial_term_id && 'on' !== $this->props['all_items'] ? $initial_term_id : $selected_terms;
            }
            $query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
                'relation' => 'AND',
                [
                    'taxonomy' => $this->selected_taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $terms,
                ],
            ];
        }

        if (is_single()) { // Exclude Current post. This feature added from version 1.2.10
            $main_query_post = \ET_Post_Stack::get_main_post();

            if (null !== $main_query_post) {
                $query_args['post__not_in'][] = $main_query_post->ID;
            }
        }

        $df_pg_paged = is_front_page() ? get_query_var('page') : get_query_var('paged');
        if (is_front_page()) {
            $paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        if ($__et_blog_module_paged > 1) {
            $df_pg_paged         = $__et_blog_module_paged;
            $paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
            $query_args['paged'] = $__et_blog_module_paged;
        } else {
            $query_args['paged'] = $df_pg_paged;
        }

        if ('' !== $offset_number && ! empty($offset_number)) {
            /**
             * Offset + pagination don't play well. Manual offset calculation required
             *
             * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
             */
            if ($paged > 1) {
                $query_args['offset'] = (($df_pg_paged - 1) * intval($posts_number)) + intval($offset_number);
            } else {
                $query_args['offset'] = intval($offset_number);
            }
        }

        // set post orderby
        $post_orderby = isset($query_args['orderby']) ? $query_args['orderby'] : 'date';

        $cpt_grid_options = [
            'layout'                      => $this->props['layout'],
            'cpt_item_inner'              => $df_cpt_items,
            'cpt_item_outer'              => $df_cpt_items_outside,
            'equal_height'                => $this->props['equal_height'],
            'use_image_as_background'     => $this->props['use_image_as_background'],
            'use_background_scale'        => $this->props['use_background_scale'],
            'load_more'                   => $this->props['use_load_more'],
            'use_load_more_icon'          => $this->props['use_load_more_icon'],
            'load_more_font_icon'         => $this->props['load_more_font_icon'],
            'load_more_icon_pos'          => $this->props['load_more_icon_pos'],
            'use_load_more_text'          => $this->props['use_load_more_text'],
            'use_search_bar'              => isset($this->props['use_search_bar']) ? $this->props['use_search_bar'] : 'off',
            'use_search_bar_icon'         => isset($this->props['use_search_bar_icon']) ? $this->props['use_search_bar_icon'] : '',
            'search_bar_font_icon'        => isset($this->props['search_bar_font_icon']) ? $this->props['search_bar_font_icon'] : '',
            'use_only_search_bar_icon'    => isset($this->props['use_only_search_bar_icon']) ? $this->props['use_only_search_bar_icon'] : 'off',
            'search_bar_button_text'      => isset($this->props['search_bar_button_text']) ? $this->props['search_bar_button_text'] : '',
            'search_bar_placeholder_text' => isset($this->props['search_bar_placeholder_text']) ? $this->props['search_bar_placeholder_text'] : '',
            'use_empty_post_message'      => isset($this->props['use_empty_post_message']) ? $this->props['use_empty_post_message'] : 'off',
            'empty_post_message'          => isset($this->props['empty_post_message']) ? $this->props['empty_post_message'] : '',
            'orderby'                     => $post_orderby,
            'entire_item_clickable'       => ! empty($this->props['entire_item_clickable']) ? $this->props['entire_item_clickable'] : 'off',
        ];
        ob_start();

        if ('off' === $use_current_loop) {
            query_posts($query_args); // phpcs:ignore WordPress.WP.DiscouragedFunctions
        } elseif (is_singular()) {
            // Force an empty result set in order to avoid loops over the current post.
            query_posts(['post__in' => [0]]); // phpcs:ignore WordPress.WP.DiscouragedFunctions
            // $show_no_results_template = false;
        } else {
            // Only allow certain args when `Posts For Current Page` is set.
            $original = $wp_query->query_vars;
            $custom   = array_intersect_key($query_args, array_flip(['posts_per_page', 'offset', 'paged']));

            // Trick WP into reporting this query as the main query so third party filters
            // that check for is_main_query() are applied.
            $wp_the_query = $wp_query = new \WP_Query(array_merge($original, $custom)); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        // Manually set the max_num_pages to make the `next_posts_link` work
        if ('' !== $offset_number && ! empty($offset_number)) {
            global $wp_query;
            $wp_query->found_posts   = max(0, $wp_query->found_posts - intval($offset_number));
            $posts_number            = intval($posts_number);
            $wp_query->max_num_pages = $posts_number > 1 ? ceil($wp_query->found_posts / $posts_number) : 1;
        }

        // render Cpt Filter navigation
        echo $this->props['post_display'] === 'by_tax' ? et_core_esc_previously(render_cpt_filter_nav(
            $post_type,
            $this->selected_taxonomy,
            $this->selected_terms,
            $this->props['all_items'],
            $this->props['all_items_text'],
            $post_orderby,
            $this->props['show_filter_label']
        )) : '';


        echo '<div class="df-cpts-wrap layout-' . esc_attr($cpt_grid_options['layout']) . '">';

        if ($this->props['loader_overlay'] === 'on') {
            echo '<div id="overlay"></div>';
        }
        $initial_term_id_for_load_more = 'current' !== $initial_term_id && 'on' !== $this->props['all_items'] ? $initial_term_id : $this->selected_terms;
        echo et_core_esc_previously(df_process_filter_grid_data($cpt_grid_options, $initial_term_id_for_load_more));
        echo '</div>';

        $wp_the_query = $wp_query = $main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions

        $posts = ob_get_clean();

        if (empty($df_cpt_items)) {
            $posts = '';
        }

        return $posts;
    }

    public function generate_multi_filter_select($class_name, $selector, $title, $label, $options)
    {
        return sprintf(
            '
                    <li class="%5$s">
	                    <div class="dropdown-container">
		                        %3$s
		                        <select id="%1$s" name="%1$s" title="%4$s" multiple data-multi-select-plugin>
		                            %2$s
		                        </select>
	                    <div/>
                    </li>
                    ',
            $selector,
            $options,
            $title,
            esc_html__($label),
            $class_name
        );
    }

    public function generate_multi_filter_checkbox($class_name, $selector, $label, $options)
    {
        return sprintf(
            '
                    <li class="%4$s">
                    	<div class="checkbox_container" id="%1$s">
	                        <span class="multi_filter_label">%3$s</span>
	                        %2$s
                    	</div>
                    </li>
                    ',
            $selector,
            $options,
            esc_html__($label),
            $class_name
        );
    }

    public function generate_multi_filter_range($args = [])
    {
        return sprintf(
            '
                    <li class="%5$s">
                        %2$s
                        <input type="text" class="df-rangle-slider" id="%1$s" data-range_value = "%4$s" data-range=\'%3$s\' data-value="%1$s"/>
                    </li>
                    ',
            $args['selector'],
            $args['title'],
            wp_json_encode($args['data']),
            wp_json_encode([$args['data']['min'], $args['data']['max']]),
            $args['class_name']
        );
    }

    public function termlist_options($tex_name, $tex_label)
    {
        $terms = get_terms($tex_name);
        //$term_name_html = "<option value='all'> All $tex_label</option>";
        $term_name_html = "";
        foreach ($terms as $term) {
            $term_id        = $term->term_id;
            $name           = $term->name;
            $term_name_html .= "<option value=$term->slug> $name </option>";
        }

        return $term_name_html;
    }

    public function get_taxonomy_field_options($tex_name)
    {
        $terms       = get_terms($tex_name);
        $option_html = "";
        foreach ($terms as $term) {
            $option_html .= sprintf(
                '
							<label class="checkbox_content">%1$s
							  <input type="checkbox"  data-value="%2$s">
							  <span class="checkmark"></span>
							</label>',
                $term->name,
                $term->slug
            );
        }

        return $option_html;
    }

    public function generate_taxonomy_dropdown($post_type, $taxonomy_lists, $props)
    {
        if (empty($taxonomy_lists)) {
            return;
        }
        $html         = "";
        $heading_text =
            $this->props['use_multi_filter_label'] === 'on' &&
            $this->props['enable_single_filter_label'] === 'on' &&
            ! empty( $this->props['single_label_text'] )
                ? sprintf(
                    '<li><span class="multi_filter_label">%1$s</span></li>',
                    esc_html__( $this->props['single_label_text'], 'divi_flash' )
                )
                : '';
        $html         .= $heading_text;
        $index        = 0;
        foreach ($taxonomy_lists as $key => $value) {
            $index++;
            $taxonomy_details   = get_taxonomy($value);
            $term_options       = $this->termlist_options($value, $taxonomy_details->label);
            $prefix_label       = $this->props['use_multi_filter_label'] === 'on' && $this->props['prefix_multi_filter_label'] !== '' ? $this->props['prefix_multi_filter_label'] : '';
            $label_text         = $this->props['enable_single_filter_label'] === 'on' && $this->props['single_label_text'] !== '' ? $this->props['single_label_text'] : $prefix_label . " " . $taxonomy_details->label;
            $multi_filter_label = $this->props['use_multi_filter_label'] === 'on' ? sprintf('<span class="multi_filter_label"> %1$s </span>', esc_html__($label_text, 'divi_flash')) : '';
            if ($this->props['enable_single_filter_label'] === 'on') {
                $multi_filter_label = '';
            }
            $field_type = $props['tax_filter_field_type_' . $post_type . '_' . $value];
            if ('select' === $field_type) {
                $html .= $this->generate_multi_filter_select('multiple_taxonomy_filter', $value, $multi_filter_label, $taxonomy_details->label, $term_options);
            } else if ('checkbox' === $field_type) {
                $html .= $this->generate_multi_filter_checkbox('multiple_taxonomy_filter', $value, $taxonomy_details->label, $this->get_taxonomy_field_options($value));
            } else {
                $html .= $this->generate_multi_filter_select('multiple_taxonomy_filter', $value, $multi_filter_label, $taxonomy_details->label, $term_options);
            }
        }

        return $html;
    }

    public function get_acf_field_options($post_type, $field, $type)
    {
        $posts = new \WP_Query([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
        ]);

        $options = [];

        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                $acf_meta_value = trim(get_post_meta(get_the_ID(), $field['name'], true));
                if (! in_array($acf_meta_value, $options) && "" !== $acf_meta_value && "0" !== $acf_meta_value) {
                    $options[] = $acf_meta_value;
                }
            }
            wp_reset_postdata();
        }
        $options         = array_unique($options);
        $acf_option_html = "";
        foreach ($options as $option) {
            if ('checkbox' === $type) {
                $acf_option_html .= sprintf(
                    '
							<label class="checkbox_content">%2$s %1$s %3$s
							  <input type="checkbox"  data-value="%1$s">
							  <span class="checkmark"></span>
							</label>',
                    $option,
                    $field['prepend'],
                    $field['append']
                );
            }
            if ('select' === $type) {
                $acf_option_html .= sprintf('<option value="%1$s">%2$s %1$s %3$s</option>', $option, $field['prepend'], $field['append']);
            }
        }

        return $acf_option_html;
    }

    public function get_pod_field_options($post_type, $field, $type)
    {
        return $this->get_acf_field_options($post_type, $field, $type);
    }

    public function get_acf_field_range_min_max($post_type, $field)
    {
        $posts = new \WP_Query([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
        ]);

        $options = [];

        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                $acf_meta_value = trim(get_post_meta(get_the_ID(), $field['name'], true));
                if (! in_array($acf_meta_value, $options) && "" !== $acf_meta_value && "0" !== $acf_meta_value) {
                    $options[] = $acf_meta_value;
                }
            }
            wp_reset_postdata();
        }
        $options = array_unique($options);
        $values  = [];
        foreach ($options as $option) {
            $values[] = (int) $option;
        }

        return ['min' => min($values), 'max' => max($values)];
    }

    public function get_pod_field_range_min_max($post_type, $field)
    {
        return $this->get_acf_field_range_min_max($post_type, $field);
    }

    public function get_acf_field_choices($post_type, $field, $type)
    {
        $get_field_choice = $field['choices'];
        $html             = "";
        foreach ($get_field_choice as $key => $value) {
            if ('checkbox' === $type) {
                $html .= sprintf(
                    '
							<label class="checkbox_content">%1$s
							  <input type="checkbox"  data-key="%2$s" data-value="%1$s">
							  <span class="checkmark"></span>
							</label>',
                    $value,
                    $key
                );
            }
            if ('select' === $type) {
                $html .= "<option value=$key> $value </option>";
            }
        }

        return $html;
    }

    public function get_pod_field_choices($post_type, $field, $type)
    {
        return $this->get_acf_field_choices($post_type, $field, $type);
    }

    public function generate_acf_filter_fields($post_type, $selected_acf_filter_fields, $df_acf_field_details_for_filter, $props)
    {
        if (empty($selected_acf_filter_fields)) {
            return;
        }
        $html              = "";
        $acf_field_details = $df_acf_field_details_for_filter[$post_type];
        $acf_field_details = array_intersect_key(
            $acf_field_details,
            array_unique(array_column($acf_field_details, 'key'))
        );
        foreach ($acf_field_details as $field) {
            $multi_filter_label = $props['use_multi_filter_label'] === 'on' ?
                sprintf('<span class="multi_filter_label"> %1$s </span>', esc_html__($field['label'], 'divi_flash'))
                : '';
            if ($props['enable_single_filter_label'] === 'on') {
                $multi_filter_label = '';
            }
            $field_type = $props['acf_filter_field_type_' . $post_type . '_' . $field['name']];
            if (in_array($field['name'], $selected_acf_filter_fields)) {
                if (in_array($field['type'], ['textarea', 'text', 'number'])) {
                    if ('select' === $field_type) {
                        $html .= $this->generate_multi_filter_select('multiple_acf_filter', $field['name'], $multi_filter_label, $field['label'], $this->get_acf_field_options($post_type, $field, 'select'));
                    } else if ('checkbox' === $field_type) {
                        $html .= $this->generate_multi_filter_checkbox('multiple_acf_filter', $field['name'], $field['label'], $this->get_acf_field_options($post_type, $field, 'checkbox'));
                    } else if ('range' === $field_type) {
                        $range_label = sprintf('<span class="multi_filter_range_label"> %1$s </span>', esc_html__($field['label'], 'divi_flash'));
                        $min_max     = $this->get_acf_field_range_min_max($post_type, $field);
                        $html        .= $this->generate_multi_filter_range([
                            'class_name' => 'multiple_acf_filter',
                            'selector'   => $field['name'],
                            'title'      => $range_label,
                            'data'       => [
                                'skin'    => 'flat',
                                'type'    => "double",
                                'grid'    => false,
                                'min'     => $min_max['min'],
                                'max'     => $min_max['max'],
                                'from'    => $min_max['min'],
                                'to'      => $min_max['max'],
                                'prefix'  => $field['prepend'],
                                'postfix' => $field['append'],
                            ],
                        ]);
                    } else {
                        $html .= $this->generate_multi_filter_select('multiple_acf_filter', $field['name'], $multi_filter_label, $field['label'], $this->get_acf_field_options($post_type, $field, 'select'));
                    }
                }
                if ('select' === $field['type']) {
                    if ('select' === $field_type) {
                        $html .= $this->generate_multi_filter_select('multiple_acf_filter', $field['name'], $multi_filter_label, $field['label'], $this->get_acf_field_choices($post_type, $field, 'select'));
                    } else if ('checkbox' === $field_type) {
                        $html .= $this->generate_multi_filter_checkbox('multiple_acf_filter', $field['name'], $field['label'], $this->get_acf_field_choices($post_type, $field, 'checkbox'));
                    } else {
                        $html .= $this->generate_multi_filter_select('multiple_acf_filter', $field['name'], $multi_filter_label, $field['label'], $this->get_acf_field_choices($post_type, $field, 'select'));
                    }
                }
                if ('range' === $field['type']) {
                    if( ( isset( $field_type ) && 'range' !== $field_type ) ) {
                        return '';
                    }
                    $range_label = sprintf('<span class="multi_filter_range_label"> %1$s </span>', esc_html__($field['label'], 'divi_flash'));
                    $html        .= $this->generate_multi_filter_range([
                        'class_name' => 'multiple_acf_filter',
                        'selector'   => $field['name'],
                        'title'      => $range_label,
                        'data'       => [
                            'skin'    => 'flat',
                            'type'    => "double",
                            'grid'    => false,
                            'min'     => $field['min'],
                            'max'     => $field['max'],
                            'from'    => $field['min'],
                            'to'      => $field['max'],
                            'prefix'  => $field['prepend'],
                            'postfix' => $field['append'],
                        ],
                    ]);
                }
            }
        }

        return $html;
    }

    public function generate_pod_filter_fields($post_type, $selected_pod_filter_fields, $df_pod_field_details_for_filter, $props)
    {
        if (empty($selected_pod_filter_fields)) {
            return '';
        }
        $html                = '';
        $pod_field_details   = $df_pod_field_details_for_filter[$post_type];
        $use_multi_label     = $props['use_multi_filter_label'] === 'on';
        $disable_multi_label = $props['enable_single_filter_label'] === 'on';

        foreach ($pod_field_details as $field) {
            if (! in_array($field['name'], $selected_pod_filter_fields)) {
                continue;
            }

            $field_type         = $props['pod_filter_field_type_' . $post_type . '_' . $field['name']];
            $multi_filter_label = ($use_multi_label && ! $disable_multi_label) ? sprintf('<span class="multi_filter_label">%1$s</span>', esc_html__($field['label'], 'divi_flash')) : '';

            $html .= $this->generate_pod_field_html($field, $field_type, $post_type, $multi_filter_label);
        }

        return $html;
    }

    private function generate_pod_field_html($field, $field_type, $post_type, $multi_filter_label)
    {
        $html = '';

        if (in_array($field['type'], ['paragraph', 'text', 'number'])) {
            $html .= $this->generate_pod_field_by_type($field_type, $post_type, $field, $multi_filter_label);
        } elseif ($field['type'] === 'select') {
            $html .= $this->generate_pod_field_by_type($field_type, $post_type, $field, $multi_filter_label, 'choices');
        } elseif ($field['type'] === 'range' && $field_type === 'range') {
            $html .= $this->generate_pod_range_field($post_type, $field);
        } else {
            $html .= $this->generate_multi_filter_checkbox(
                'multiple_pod_filter',
                $field['name'],
                $field['label'],
                $this->get_pod_field_options($post_type, $field, 'checkbox')
            );
        }

        return $html;
    }

    private function generate_pod_field_by_type($field_type, $post_type, $field, $multi_filter_label, $option_type = 'options')
    {
        $html             = '';
        $options_function = $option_type === 'choices' ? 'get_pod_field_choices' : 'get_pod_field_options';

        if ($field_type === 'select') {
            $html .= $this->generate_multi_filter_select(
                'multiple_pod_filter',
                $field['name'],
                $multi_filter_label,
                $field['label'],
                $this->$options_function($post_type, $field, 'select')
            );
        } elseif ($field_type === 'checkbox') {
            $html .= $this->generate_multi_filter_checkbox(
                'multiple_pod_filter',
                $field['name'],
                $field['label'],
                $this->$options_function($post_type, $field, 'checkbox')
            );
        } elseif ($field_type === 'range') {
            $html .= $this->generate_pod_range_field($post_type, $field);
        } else {
            $html .= $this->generate_multi_filter_select(
                'multiple_pod_filter',
                $field['name'],
                $multi_filter_label,
                $field['label'],
                $this->$options_function($post_type, $field, 'select')
            );
        }

        return $html;
    }

    private function generate_pod_range_field($post_type, $field)
    {
        $range_label = sprintf('<span class="multi_filter_range_label">%1$s</span>', esc_html__($field['label'], 'divi_flash'));
        $min_max     = $this->get_pod_field_range_min_max($post_type, $field);

        return $this->generate_multi_filter_range([
            'class_name' => 'multiple_pod_filter',
            'selector'   => $field['name'],
            'title'      => $range_label,
            'data'       => [
                'skin'    => 'flat',
                'type'    => 'double',
                'grid'    => false,
                'min'     => $min_max['min'] ?? $field['min'],
                'max'     => $min_max['max'] ?? $field['max'],
                'from'    => $min_max['min'] ?? $field['min'],
                'to'      => $min_max['max'] ?? $field['max'],
                'prefix'  => $field['prepend'],
                'postfix' => $field['append'],
            ],
        ]);
    }

    public function df_search_filter_html($use_icon = 'on', $font_icon = '5', $placeholder_text = 'Search', $button_text = 'Search', $only_icon = 'off', $button_icon_placement = 'right')
    {
        $icon = 'on' === $use_icon ?
            sprintf(
                '<span class="et-pb-icon search_icon">%1$s</span>',
                esc_attr(is_array($font_icon) && method_exists(Utils::class, 'process_font_icon') ? Utils::process_font_icon($font_icon) : et_pb_process_font_icon($font_icon))
            ) : '';
        $button_icon_html = sprintf(
            '<span class="search_bar_button">
                                    %2$s
                                    %1$s
                                    %3$s
                                </span>',
            esc_html($button_text),
            $button_icon_placement === 'left' ? $icon : '',
            $button_icon_placement === 'right' ? $icon : ''
        );
        $only_icon_html   = sprintf(
            '<span class="search_bar_button">
                            %1$s
                        </span>',
            $icon
        );

        return sprintf(
            '<div class="search_bar">
                        <input type="text" name="df_search_filter" placeholder="%2$s" class="df_search_filter_input"/>
                        %1$s
                    </div>',
            $only_icon === 'off' ? $button_icon_html : $only_icon_html,
            esc_html($placeholder_text)
        );
    }

    public function df_author_filter_html($post_type, $post_display, $show_filter_label, $prefix_multi_filter_label, $field_type = "select")
    {
        $query = new \WP_Query([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
        ]);

        $author_ids = [];

        ob_start();
        if ("multiple_filter" !== $post_display) {
        ?>
            <div class="df_author_filter">
                <?php
                if ("on" === $show_filter_label) {
                ?>
                    <span class="df_author_label">Author</span>
                <?php
                }
                ?>
                <ul>
                    <li class="df_author_active" data-author_id="0" data-author="all">All</li>
                    <?php
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                            $author_id = get_the_author_meta('ID');
                            if (! in_array($author_id, $author_ids)) {
                                $author_ids[] = $author_id;
                                $author_name  = get_the_author_meta('display_name');
                    ?>
                                <li class="" data-author_id="<?php echo esc_html($author_id); ?>"
                                    data-author="<?php echo esc_html($author_name); ?>"><?php echo esc_html($author_name); ?></li>
                    <?php
                            }
                        }
                        wp_reset_postdata();
                    }
                    ?>
                </ul>
            </div>
        <?php
        }
        if ("multiple_filter" === $post_display) {
            $html  = "";
            $label = "";
            if ("on" === $show_filter_label) {
                $label = sprintf('<span class="multi_filter_label">%1$s Author</span>', $prefix_multi_filter_label);
            }
        ?>
            <li class="df_author_filter">
                <?php
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $author_id = get_the_author_meta('ID');
                        if (! in_array($author_id, $author_ids)) {
                            $author_ids[] = $author_id;
                            $author_name  = get_the_author_meta('display_name');
                            if ('checkbox' === $field_type) {
                                $html .= sprintf(
                                    '
                                    <label class="checkbox_content">%1$s
                                      <input type="checkbox" data-value="%2$s">
                                      <span class="checkmark"></span>
                                    </label>',
                                    esc_html($author_name),
                                    esc_html($author_id)
                                );
                            }
                            if ('select' === $field_type) {
                                $html .= "<option value='$author_id'> $author_name </option>";
                            }
                        }
                    }
                    wp_reset_postdata();
                }
                if ('checkbox' === $field_type) {
                    echo sprintf('<div class="checkbox_container"><span class="multi_filter_label">Author</span>%1$s</div>', esc_attr($html));
                }
                if ('select' === $field_type) {
                    echo sprintf(
                        '
                        <div class="dropdown-container">
                        %2$s
                        <select id="author_data" name="author_data" title="Author" multiple data-multi-select-plugin>%1$s</select>
                        </div>',
                        et_core_esc_previously($html),
                        et_core_esc_previously($label)
                    );
                }
                ?>
            </li>
        <?php

        }

        return ob_get_clean();
    }

    public function multi_filter_item_in_row_data($multi_filter_item_in_row)
    {
        $width = '25%';
        $flex = '24%';
        switch ($multi_filter_item_in_row) {
            case '1':
                $flex = '100%';
                $width = '100%';
                break;
            case '2':
                $flex = '50%';
                $width = '50%';
                break;
            case '3':
                $flex = '33%';
                $width = '33.33%';
                break;
            case '5':
                $flex = '19%';
                $width = '20%';
                break;
            case '6':
                $flex = '16%';
                $width = '16.66%';
                break;
            default:
                $flex = '24%';
                $width = '25%';
                break;
        }

        return [
            'flex' => $flex,
            'width' => $width
        ];
    }

    public function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    public function get_common_styles($args)
    {
        $attrs      = $this->get_merged_attrs($args);
        $attrs      = self::build_attr_for_styles($attrs);
        $attrs      = $this->get_default_value_for_styles($attrs);
        $orderClass = $args['orderClass'] ?? '';

        $styles = [];

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .pagination .older:before, {$orderClass} .pagination .prev:before",
            'attr'                => $attrs['next_prev_icon']['innerContent'] ?? null,
            'declarationFunction' => function ($value) {
                $attrValue = $value['attrValue'] ?? 'set_1';

                return "content: '" . $this->arrow_icon($attrValue, 'prev') . "'";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .pagination .newer:after, {$orderClass} .pagination .next:after",
            'attr'                => $attrs['next_prev_icon']['innerContent'] ?? null,
            'declarationFunction' => function ($value) {
                $attrValue = $value['attrValue'] ?? 'set_1';

                return "content: '" . $this->arrow_icon($attrValue, 'next') . "'";
            },
        ]);

        if( $this->props['alignment'] ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df-cpt-inner-wrap",
                'attr'     => $attrs['alignment']['innerContent'] ?? null,
                'property' => 'text-align',
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df-cpt-filter-nav, {$orderClass}.difl_cptfilter .filter_section .df_author_filter ul",
            'attr'      => $attrs['filter_button_align']['innerContent'] ?? null,
            'property'  => 'justify-content',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass} .df_filter_sidebar .df-cpts-wrap",
            'attr'  => $attrs['multi_filter_container_width']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                // For group-item attributes, attrValue is an array with the sub-attribute as key
                $width = $props['attrValue'] ?? $props['attrValue'] ?? '';
                $width = 100 - (int) $width;
                return "width: {$width}% !important;";
            }
        ] );

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df_filter_sidebar .filter_section",
            'attr'      => $attrs['multi_filter_container_width']['innerContent'] ?? null,
            'property'  => 'width'
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass}.difl_cptfilter .df-cpt-item",
            'attr'                => $attrs['column']['innerContent'] ?? null,
            'declarationFunction' => function ($value) {
                $attrValue = $value['attrValue'] ?? '';
                $width     = ! empty( $attrValue ) ? round( 100 / (int) $attrValue, 2 ) . '%' : '';

                return "width: {$width};";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass}.difl_cptfilter .df-cpt-item",
            'attr'                => $attrs['gutter']['innerContent'] ?? null,
            'declarationFunction' => function ($value) {
                $attrValue   = $value['attrValue'] ?? '';
                $gutterValue = ! empty($attrValue) ? (int) $attrValue : 0;
                $halfGutter  = $gutterValue / 2;

                return "padding-left: {$halfGutter}px; padding-right: {$halfGutter}px; padding-bottom: {$gutterValue}px;";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_cptfilter_container .df-cpts-inner-wrap",
            'attr'                => $attrs['gutter']['innerContent'] ?? null,
            'declarationFunction' => function ($value) {
                $attrValue   = $value['attrValue'] ?? '';
                $gutterValue = ! empty($attrValue) ? (int) $attrValue : 0;
                $halfGutter  = $gutterValue / 2;

                return "margin-left: -{$halfGutter}px; margin-right: -{$halfGutter}px;";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df-cpt-outer-wrap",
            'attr'     => $attrs['outer_wrpper_visibility']['innerContent'] ?? null,
            'property' => 'overflow',
        ]);

        //		if ( ! empty( $this->props['outer_wrpper_visibility'] ) && $this->props['outer_wrpper_visibility'] !== 'default' ) {
        //			$common_styles[] = CommonStyle::style( [
        //				'selector'            => "{$orderClass} .df-cpt-outer-wrap",
        //				'attr'                => $attrs['outer_wrpper_visibility']['innerContent'] ?? null,
        //				'declarationFunction' => function ( $value ) {
        //					$attrValue  = $value['attrValue'] ?? '';
        //					$visibility = ( $attrValue === 'visible' ) ? 'visible' : 'hidden';
        //
        //					return "visibility: {$visibility};";
        //				},
        //			] );
        //		}

        if (! empty($this->props['inner_wrpper_visibility']) && $this->props['inner_wrpper_visibility'] !== 'default') {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df-cpt-inner-wrap",
                'attr'     => $attrs['inner_wrpper_visibility']['innerContent'] ?? null,
                'property' => 'overflow',
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df-cpt-filter-nav, {$orderClass}.difl_cptfilter .filter_section .df_author_filter ul",
            'attr'      => $attrs['filter_nav_gap']['innerContent'] ?? null,
            'property'  => 'gap',
            'important' => true,
        ]);

        if (! empty($this->props['multi_filter_label_position']) && $this->props['multi_filter_label_position'] === 'on_top') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .filter_section ul li",
                'attr'                => $attrs['multi_filter_label_position']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'display: flex; flex-direction:column;';
                },
            ]);
        }

        if (($this->props['post_display'] ?? '') === 'multiple_filter') {
            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .df_cptfilter_container.df_filter_sidebar",
                'attr'      => $attrs['multi_filter_item_align']['innerContent'] ?? null,
                'property'  => 'align-items',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptfilter_container.df_filter_sidebar",
                'attr'                => $attrs['multi_filter_placement']['innerContent'] ?? null,
                'declarationFunction' => function ($value) {
                    $attrValue   = $value['attrValue'] ?? '';

                    if ($attrValue === 'right') {
                        return "flex-direction: row-reverse;";
                    }
                    return "";
                },
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .df_cptfilter_container.df_filter_sidebar .filter_section ul.multi_filter_container > li:not(:last-child)",
                'attr'      => $attrs['multi_filter_input_gap']['innerContent'] ?? null,
                'property'  => 'margin-bottom',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .df_cptfilter_container:not(.df_filter_sidebar) .filter_section ul.multi_filter_container > li:not(:last-child)",
                'attr'      => $attrs['multi_filter_input_gap']['innerContent'] ?? null,
                'property'  => 'padding-right',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .df_cptfilter_container:not(.df_filter_sidebar) .filter_section ul.multi_filter_container",
                'attr'      => $attrs['multi_filter_input_gap']['innerContent'] ?? null,
                'property'  => 'row-gap',
            ]);

            if (($this->props['enable_mobile_responsive'] ?? '') === 'on') {
                $common_styles[] = CommonStyle::style([
                    'selector'  => "{$orderClass} .df_cptfilter_container .filter_section ul.multi_filter_container> li:not(:last-child)",
                    'attr'      => $attrs['multi_filter_input_gap_for_mobile']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "@media only screen and (max-width:767px){margin-bottom: {$this->props['multi_filter_input_gap_for_mobile']};}";
                    }
                ]);

                $common_styles[] = CommonStyle::style([
                    'selector'  => "{$orderClass} .df_cptfilter_container , {$orderClass} .df_cptfilter_container.df_filter_sidebar",
                    'attr'      => $attrs['enable_mobile_responsive']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "@media only screen and (max-width:767px){flex-direction:column;}";
                    }
                ]);

                $common_styles[] = CommonStyle::style([
                    'selector'  => "{$orderClass} .df_cptfilter_container .filter_section, {$orderClass} .df_cptfilter_container .filter_section ul",
                    'attr'      => $attrs['enable_mobile_responsive']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "@media only screen and (max-width:767px){display:block;width:100%;}";
                    }
                ]);

                $common_styles[] = CommonStyle::style([
                    'selector'  => "{$orderClass} .df_cptfilter_container .filter_section li, {$orderClass} .df_cptfilter_container .filter_section li select, {$orderClass} .df_cptfilter_container .filter_section li> span",
                    'attr'      => $attrs['enable_mobile_responsive']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "@media only screen and (max-width:767px){width:100% !important;max-width:100% !important}";
                    }
                ]);

                $common_styles[] = CommonStyle::style([
                    'selector'            => "{$orderClass} .df_filter_sidebar .df-cpts-wrap",
                    'attr'                => $attrs['multi_filter_container_width']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "@media only screen and (max-width:767px){width:100% !important;}";
                    },
                ]);
            }

            $common_styles[] = CommonStyle::style([
                'selector'             => "{$orderClass} .df_cptfilter_container:not(.df_filter_sidebar) .filter_section ul.multi_filter_container > li",
                'attr'                 => $attrs['multi_filter_item_in_row']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    $value = $this->multi_filter_item_in_row_data($props['attrValue']);
                    return "width: {$value['flex']}; max-width: {$value['width']};";
                },
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .search_bar",
            'attr'      => $attrs['search_bar_align']['innerContent'] ?? null,
            'property'  => 'justify-content',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df_search_filter_input",
            'attr'      => $attrs['search_input_width']['innerContent'] ?? null,
            'property'  => 'width',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .search_bar .et-pb-icon.search_icon",
            'attr'      => $attrs['search_button_icon_size']['innerContent'] ?? null,
            'property'  => 'font-size',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .search_bar .search_bar_button .et-pb-icon.search_icon",
            'attr'     => $attrs['search_button_icon_color']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                    return 'color: ' . $props['attrValue'];
                }
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .search_bar .search_bar_button:hover .et-pb-icon.search_icon",
            'attr'     => $attrs['search_button_icon_color']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                    return 'color: ' . $props['attrValue'];
                }
            },
        ]);

        if ('on' === ($this->props['full_width_load_more'])) {
            $common_styles[] = CommonStyle::style([
                'selector'             => "{$orderClass} .df-cptfilter-load-more",
                'attr'                 => $attrs['full_width_load_more']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'width: 100% !important;';
                },
            ]);
        }

        if ('on' === ($this->props['use_load_more'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .load-more-pagintaion-container",
                'attr'      => $attrs['load_more_align']['innerContent'] ?? null,
                'property'  => 'text-align',
                'important' => true,
            ]);
        }

        if ('tablet_phone' === ($this->props['turn_off_cpt_sticky'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'             => "{$orderClass} .filter_section.difl_cpt_sticky_filter_on, {$orderClass} .df-cptfilter .filter_section.difl_cpt_sticky_filter_on + ul.df-cpt-filter-nav",
                'attr'                 => $attrs['turn_off_cpt_sticky']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "@media only screen and (max-width:980px){position: unset;}";
                },
            ]);
        } elseif ('phone' === ($this->props['turn_off_cpt_sticky'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'             => "{$orderClass} .filter_section.difl_cpt_sticky_filter_on, {$orderClass} .df-cptfilter .filter_section.difl_cpt_sticky_filter_on + ul.df-cpt-filter-nav",
                'attr'                 => $attrs['turn_off_cpt_sticky']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "@media only screen and (max-width:767px){position: unset;}";
                },
            ]);
        }

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-line",
			'attr'     => $attrs['multi_filter_range_bg']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-bar",
			'attr'     => $attrs['multi_filter_range_active_color']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-handle>i:first-child",
			'attr'     => $attrs['multi_filter_range_active_border_color']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-from, {$orderClass} .filter_section li .irs .irs-to",
			'attr'     => $attrs['multi_filter_range_tooltip_bg']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-from:before, {$orderClass} .filter_section li .irs .irs-to:before",
			'attr'     => $attrs['multi_filter_range_tooltip_bg']['innerContent'] ?? null,
            'property'  => 'border-top-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .irs .irs-min, {$orderClass} .filter_section li .irs .irs-max",
			'attr'     => $attrs['multi_filter_range_min_max_bg']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .search_bar_button .et-pb-icon",
			'attr'     => $attrs['search_bar_font_icon']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) {
                return self::get_font_style( $this->props['search_bar_font_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cptfilter-load-more .et-pb-icon",
			'attr'     => $attrs['load_more_font_icon']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) {
                return self::get_font_style( $this->props['load_more_font_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-filter-nav-item, {$orderClass}.difl_cptfilter .filter_section .df_author_filter:not(:has(.dropdown-container)) ul li",
			'attr'     => $attrs['filter_buttons']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-filter-nav-item.df-active, {$orderClass} .filter_section .filter_elements .filter_element_card, {$orderClass}.difl_cptfilter .filter_section .df_author_filter:not(:has(.dropdown-container)) ul li.df_author_active",
			'attr'     => $attrs['filter_button_active']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_phn_resp_cls .difl_filter_short_desc_card a.difl_filter_show_btn",
			'attr'     => $attrs['filter_button_active_container_show']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_phn_resp_cls .difl_filter_short_desc_card a.difl_filter_cls_btn",
			'attr'     => $attrs['filter_button_active_container_cancel']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_phn_resp_cls .difl_filter_short_desc_card_header",
			'attr'     => $attrs['filter_button_active_container_header_title']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_phn_resp_cls .difl_filter_short_desc_card",
			'attr'     => $attrs['filter_button_active_container_footer']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .search_bar .search_bar_button",
			'attr'     => $attrs['search_button_styles']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-outer-wrap",
			'attr'     => $attrs['item_outer']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-inner-wrap",
			'attr'     => $attrs['item_inner']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section ul li .checkbox_container",
			'attr'     => $attrs['multi_filter_checkbox_field']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cptfilter-load-more",
			'attr'     => $attrs['load_more']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section ul li span.multi_filter_label",
			'attr'     => $attrs['multi_filter_text']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .search_bar input.df_search_filter_input, {$orderClass} .search_bar input.df_search_filter_input::placeholder",
			'attr'     => $attrs['search_bar']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li span.multi_filter_range_label",
			'attr'     => $attrs['multi_filter_range_label']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .checkbox_container .checkbox_content",
			'attr'     => $attrs['multi_filter_checkbox_field_checkbox_item_gap']['innerContent'] ?? null,
            'property'  => 'margin-bottom'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .checkbox_container .checkbox_content",
			'attr'     => $attrs['multi_filter_checkbox_field_checkbox_item_space_left']['innerContent'] ?? null,
            'property'  => 'margin-left'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .checkbox_container .checkbox_content",
			'attr'     => $attrs['multi_filter_checkbox_field_checkbox_text_space_left']['innerContent'] ?? null,
            'property'  => 'padding-left'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .checkbox_container .checkmark",
			'attr'     => $attrs['multi_filter_checkbox_field_checkbox_size']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) {
                return "width: {$this->props['multi_filter_checkbox_field_checkbox_size']}; height: {$this->props['multi_filter_checkbox_field_checkbox_size']};";
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .filter_section li .checkbox_container input:checked ~ .checkmark",
			'attr'     => $attrs['multi_filter_checkbox_field_checkbox_checked_color']['innerContent'] ?? null,
            'property'  => 'background-color'
		] );


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

    
    public function arrow_icon($set = 'set_1', $type = 'next')
    {
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

        return $icons[$set][$type];
    }

    /**
     * Module render method.
     *
     * @return String
     */
    public function render($attrs, $content, $render_slug)
    {
        $this->init_cpt_tax(
            'general',
            'settings',
            [],
            [],
            [
                'terms' => 'Filter by terms:',
            ]
        );
        $this->df_acf_init();
        $this->df_pod_init();


        global $df_cpt_items, $df_cpt_items_outside;

        wp_enqueue_script('imageload');
        wp_enqueue_script('df-imagegallery-lib');
        wp_enqueue_script('df_cpt_filter');
        wp_localize_script(
            'df_cpt_filter',
            'dfCptFilterArgs',
            [
                'hungarianSort' => apply_filters('df_cpt_is_hungarian_sort', false),
            ]
        );
        wp_enqueue_style('df-rangeSlider-styles');
        wp_enqueue_script('df-rangeSlider');
        $order_class = $this->order_class;

        $this->d5_get_taxonomy_values();

        /* ACF Filter */
        $multiACFKey = "";
        if ('on' === $this->acf_extension && class_exists('ACF')) {
            $this->d5_get_acf_filter_values();
            $multiACFKey = 'acf_filter_' . $this->props['post_type'];
        }
        /* ACF Filter */
        /* POD Filter Start */
        $multiPODKey = "";
        if ('on' === $this->pod_extension && class_exists('Pods')) {
            $this->d5_get_pod_filter_values();
            $multiPODKey = 'pod_filter_' . $this->props['post_type'];
        }
        /* POD Filter The End */

        $loader_data = [
            'status'          => isset($this->props['loader_spining']) ? $this->props['loader_spining'] : 'off',
            'color'           => isset($this->props['loader_spinning_color']) ? $this->props['loader_spinning_color'] : '#284cc1',
            'size'            => isset($this->props['loader_spining_size']) ? $this->props['loader_spining_size'] : '10px',
            'type'            => isset($this->props['loader_spinning_type']) ? $this->props['loader_spinning_type'] : 'classic',
            'background'      => isset($this->props['loader_spinning_bg_color']) ? $this->props['loader_spinning_bg_color'] : '#ffffff',
            'alignment'       => isset($this->props['loader_spinning_alignment']) ? $this->props['loader_spinning_alignment'] : 'center',
            'margin_from_top' => isset($this->props['loader_spining_top_position']) && ! empty($this->props['loader_spining_top_position']) ? $this->props['loader_spining_top_position'] : '30%',
            'margin'          => isset($this->props['loader_spinning_margin']) ? array_slice(explode("|", $this->props['loader_spinning_margin']), 0, 4) : [
                '0px',
                '0px',
                '0px',
                '0px',
            ],
        ];

        if (! class_exists('DF_Localize_Vars')) {
            require_once(DIFL_MAIN_DIR . '/includes/classes/df-class-localize-vars.php');
        }

        \DF_Localize_Vars::enqueue('df_cpt_filter', [
            'class'                                    => $order_class,
            'layout'                                   => $this->props['layout'],
            'post_type'                                => $this->props['post_type'],
            'post_display'                             => $this->props['post_display'],
            'posts_number'                             => $this->props['posts_number'],
            'offset_number'                            => $this->props['offset_number'],
            'equal_height'                             => $this->props['equal_height'],
            'use_image_as_background'                  => $this->props['use_image_as_background'],
            'use_background_scale'                     => $this->props['use_background_scale'],
            'cpt_item_inner'                           => wp_json_encode($df_cpt_items),
            'cpt_item_outer'                           => wp_json_encode($df_cpt_items_outside),
            'load_more'                                => $this->props['use_load_more'],
            'use_load_more_icon'                       => $this->props['use_load_more_icon'],
            'load_more_font_icon'                      => $this->props['load_more_font_icon'],
            'load_more_icon_pos'                       => $this->props['load_more_icon_pos'],
            'use_load_more_text'                       => $this->props['use_load_more_text'],
            'use_search_bar'                           => isset($this->props['use_search_bar']) ? $this->props['use_search_bar'] : 'off',
            'use_search_bar_icon'                      => $this->props['use_search_bar_icon'],
            'search_bar_font_icon'                     => $this->props['search_bar_font_icon'],
            'search_bar_button_text'                   => $this->props['search_bar_button_text'],
            'search_bar_placeholder_text'              => $this->props['search_bar_placeholder_text'],
            'selected_tax'                             => $this->selected_taxonomy,
            'selected_taxonomy_list'                   => $this->selected_taxonomy_list,
            'multi_filter_type'                        => isset($this->props['multi_filter_type']) ? $this->props['multi_filter_type'] : 'AND',
            'multi_filter_dropdown_placeholder_prefix' => isset($this->props['multi_filter_dropdown_placeholder_prefix']) ? $this->props['multi_filter_dropdown_placeholder_prefix'] : '',
            'orderby'                                  => $this->props['orderby'],
            'gutter'                                   => $this->props['gutter'],
            'all_items'                                => $this->props['all_items'],
            'column'                                   => $this->props['column'],
            'column_tablet'                            => $this->props['column_tablet'],
            'column_phone'                             => $this->props['column_phone'],
            'loader_spining'                           => isset($this->props['loader_spining']) ? $this->props['loader_spining'] : 'off',
            'loader_overlay'                           => isset($this->props['loader_overlay']) ? $this->props['loader_overlay'] : 'off',
            'use_empty_post_message'                   => isset($this->props['use_empty_post_message']) ? $this->props['use_empty_post_message'] : 'off',
            'empty_post_message'                       => isset($this->props['empty_post_message']) ? $this->props['empty_post_message'] : '',
            'loader'                                   => $loader_data,
            'enable_acf_filter'                        => isset($this->props[$multiACFKey]) ? $this->props[$multiACFKey] : 'off',
            'enable_pod_filter'                        => isset($this->props[$multiPODKey]) ? $this->props[$multiPODKey] : 'off',
            'entire_item_clickable'                    => isset($this->props['entire_item_clickable']) ? $this->props['entire_item_clickable'] : 'off',
        ]);

        $data = ['layout' => $this->props['layout']];

        $posts = $this->get_posts($df_cpt_items, $df_cpt_items_outside);

        $multi_filter_placement_class = 'multiple_filter' === $this->props['post_display'] && $this->props['multi_filter_placement'] !== 'default' ? 'df_filter_sidebar' : '';
        $df_cpt_items                 = [];
        $df_cpt_items_outside         = [];

        $multiFilterFields = '';
        if ('multiple_filter' === $this->props['post_display']) {

            // Generate ACF filter fields if enabled
            if (isset($this->props[$multiACFKey]) && 'on' === $this->props[$multiACFKey]) {
                $acfFilterFields = $this->generate_acf_filter_fields(
                    $this->props['post_type'],
                    $this->selected_acf_filter_options,
                    $this->df_acf_field_details_for_filter,
                    $this->props
                );
            } else {
                $acfFilterFields = '';
            }

            // Generate Pods filter fields if enabled
            if (isset($this->props[$multiPODKey]) && 'on' === $this->props[$multiPODKey]) {
                $podFilterFields = $this->generate_pod_filter_fields(
                    $this->props['post_type'],
                    $this->selected_pod_filter_options,
                    // $this->selected_pod_filter_fields,
                    $this->df_pod_field_details_for_filter,
                    $this->props
                );
            } else {
                $podFilterFields = '';
            }

            $multiFilterFields = $acfFilterFields . $podFilterFields;
        }

        return sprintf(
            '<div class="df_cptfilter_container %4$s" data-settings=\'%2$s\'>
                            <div class="filter_section %6$s %7$s" data-top=\'%8$s\'><div class="filter_wrapper"><div class="filter_elements"></div>
                            <div class="filter_field_wrapper">%5$s %3$s</div>
                            </div></div>
                            %1$s
                        </div>',

            $posts,
            wp_json_encode($data),
            $this->props['post_display'] === 'multiple_filter' ? sprintf(
                '<ul class="multi_filter_container">%1$s %3$s %2$s</ul>',
                $this->generate_taxonomy_dropdown($this->props['post_type'], $this->selected_taxonomy_list, $this->props),
                $multiFilterFields,
                ("on" === $this->props['use_author_filter'] && 'multiple_filter' === $this->props['post_display']) ? $this->df_author_filter_html(
                    $this->props['post_type'],
                    $this->props['post_display'],
                    $this->props['use_multi_filter_label'],
                    $this->props['prefix_multi_filter_label'],
                    $this->props['author_filter_field_type']
                ) : ""
            ) : '',
            $multi_filter_placement_class,
            $this->props['use_search_bar'] === 'on' ?
                $this->df_search_filter_html(
                    $this->props['use_search_bar_icon'],
                    (isset($this->props['search_bar_font_icon']) && !empty($this->props['search_bar_font_icon'])) ? $this->props['search_bar_font_icon'] : '&#x55;',
                    $this->props['search_bar_placeholder_text'],
                    $this->props['search_bar_button_text'],
                    $this->props['use_only_search_bar_icon'],
                    $this->props['search_button_icon_placement']
                )
                : '',

            "on" === $this->props['enable_mobile_responsive'] && 'multiple_filter' === $this->props['post_display'] ? "df_phn_resp" : "",
            "on" === $this->props['cpt_sticky_filter'] ? "difl_cpt_sticky_filter_on" : "",
            $this->props['cpt_sticky_offset'] ? substr($this->props['cpt_sticky_offset'], 0, -2) : 0

        );
    }
}

