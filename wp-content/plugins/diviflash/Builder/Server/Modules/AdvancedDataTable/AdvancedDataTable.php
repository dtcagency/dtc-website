<?php

namespace DIFL\Server\Modules\AdvancedDataTable;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AdvancedDataTable implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'AdvancedDataTable';

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/advanced-data-table' )->customCssFields;
	}

    function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';
		$state      = Breakpoint::get_style_breakpoint_settings();


		$styles = [];

		$common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );


		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_adt_container .df_adt_content",
			'attr'                => $attrs['table_type']['innerContent'] ?? null,
			'declarationFunction' => function () use ( $state ) {
				return ( $state === 'tablet' || $state === 'phone' ) ? 'overflow-x:scroll;' : '';
			},
		] );

		if ( 'on' === $this->props['make_head_cell_equal_border'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr.df-advanced-table__head-column .df-advanced-table__head-column-cell:not(:last-child)",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $state ) {
					return 'border-right:0px !important;';
				},
			] );
		}

		if ( 'on' === $this->props['make_row_cell_equal_border'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr.df-advanced-table__body-row:not(:first-child) .df-advanced-table__body-row-cell",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-top:0px !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr > td.df-advanced-table__body-row-cell:not(:last-child)",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-right:0px !important;';
				},
			] );
		}

		if ( 'on' === $this->props['make_row_last_cell_border_right_0'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr > td.df-advanced-table__body-row-cell:last-child, {$orderClass} table.df-advanced-table tr.df-advanced-table__head-column .df-advanced-table__head-column-cell:last-child",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-right:0px !important;';
				},
			] );
		}

		if ( 'on' === $this->props['make_row_first_cell_border_left_0'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr > td.df-advanced-table__body-row-cell:first-child, {$orderClass} table.df-advanced-table tr.df-advanced-table__head-column .df-advanced-table__head-column-cell:first-child",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-left:0px !important;';
				},
			] );
		}

		if ( 'on' === $this->props['make_first_row_all_cell_border_top_0'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr.df-advanced-table__head-column .df-advanced-table__head-column-cell",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-top:0px !important;';
				},
			] );
		}

		if ( 'on' === $this->props['make_last_row_all_cell_border_bottom_0'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} table.df-advanced-table tr:last-child td.df-advanced-table__body-row-cell",
				'attr'                => $attrs['border_settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'border-bottom:0px !important;';
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} table.dataTable",
			'attr'      => $attrs['pagination_spacing']['innerContent'] ?? null,
			'property'  => 'margin-bottom',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .dataTables_paginate a:not(.next)",
			'attr'      => $attrs['between_button_spacing']['innerContent'] ?? null,
			'property'  => 'margin-right',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .dataTables_paginate a:not(.previous)",
			'attr'      => $attrs['between_button_spacing']['innerContent'] ?? null,
			'property'  => 'margin-left',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .dataTables_wrapper .dataTables_paginate .ellipsis",
			'attr'      => $attrs['pagination_dot_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} tr td.df-advanced-table__body-row-cell img, {$orderClass} tr th.df-advanced-table__head-column-cell img",
			'attr'      => $attrs['image_size']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .tr td.df-advanced-table__body-row-cell span",
			'attr'      => $attrs['icon_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		if ( 'on' === $this->props['adt_order'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df-advanced-table .df-advanced-table__head-column-cell.sorting:before",
				'attr'      => $attrs['sorting_icon_color']['innerContent'] ?? null,
				'property'  => 'border-bottom-color',
				'important' => true,
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df-advanced-table .df-advanced-table__head-column-cell.sorting_asc:before",
				'attr'      => $attrs['sorting_icon_color']['innerContent'] ?? null,
				'property'  => 'border-bottom-color',
				'important' => true,
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df-advanced-table .df-advanced-table__head-column-cell.sorting:after",
				'attr'      => $attrs['sorting_icon_color']['innerContent'] ?? null,
				'property'  => 'border-top-color',
				'important' => true,
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df-advanced-table .df-advanced-table__head-column-cell.sorting_desc:after",
				'attr'      => $attrs['sorting_icon_color']['innerContent'] ?? null,
				'property'  => 'border-top-color',
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} tr td.df-advanced-table__body-row-cell span",
			'attr'      => $attrs['icon_settings']['innerContent'] ?? null,
			'declarationFunction' => function( $props ) {
				$color = $props['attrValue']['icon_color'] ?? '';
				$size  = $props['attrValue']['icon_size'] ?? '';
				return "color: {$color} !important; font-size: {$size} !important;";   
            }
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .dataTables_wrapper .dataTables_paginate .ellipsis",
			'attr'      => $attrs['pagination_dot_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

        $common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .dataTables_wrapper .dataTables_filter input",
			'attr'      => $attrs['search_lebel_spacing']['innerContent'] ?? null,
			'property'  => 'margin-left',
			'important' => true,
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} table.dataTable",
			'attr'     => $attrs['table_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-advanced-table__head-column-cell",
			'attr'     => $attrs['head_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-advanced-table__body-row-cell",
			'attr'     => $attrs['row_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-advanced-table__body-row-cell img",
			'attr'     => $attrs['image_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-advanced-table__body-row-cell span",
			'attr'     => $attrs['icon_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-advanced-table__body-row-cell a",
			'attr'     => $attrs['link_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .dataTables_wrapper .dataTables_filter input",
			'attr'     => $attrs['search_input_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .dataTables_wrapper .dataTables_length select",
			'attr'     => $attrs['info_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .dataTables_wrapper .dataTables_paginate a.paginate_button,{$orderClass} .dataTables_wrapper .dataTables_paginate a.paginate_button.current,{$orderClass} .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled",
			'attr'     => $attrs['pagination_settings']['decoration']['border'] ?? null,
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

	public function google_sheet_head_function( $columns ) {
		$data = '';
		foreach ( $columns as $column ) :
			$data .= sprintf( '<th class="df-advanced-table__head-column-cell">%1$s</th>', et_core_esc_previously( $column ) );
		endforeach;

		return $data;
	}

	public function get_tds_escape( $num_list ) {
		static $escape_tds = [];
		$new_arr = range( $num_list[0], max( $num_list ) );
		array_push( $escape_tds, array_diff( $new_arr, $num_list ) );
	}

	public function get_merges_cols() {
		$api_key       = esc_html( $this->props['google_api_key'] );
		$sheet_id      = esc_html( $this->props['google_sheet_id'] );
		$url           = "https://sheets.googleapis.com/v4/spreadsheets/$sheet_id?key=$api_key";
		$id            = $this->order_class;
		$transient_key = $id . '_data_table_merges_cache';
		$table_data    = get_transient( $transient_key );

		if ( false === $table_data ) {
			$data       = wp_remote_get( $url );
			$table_data = json_decode( wp_remote_retrieve_body( $data ), true );
			set_transient( $transient_key, $table_data, 0 );
		}
		if ( $this->props['google_cache_remove'] == 'on' ) {
			delete_transient( $transient_key );
		}

		if ( empty( $data ) ) {
			return '';
		}

		return json_decode( wp_remote_retrieve_body( $data ) );
	}

	public function google_sheet_body_function( $table_rows, $table_columns ) {
		$tr_html = '';
		for ( $i = 0; $i < count( $table_rows ); $i ++ ) :
			if ( count( $table_columns ) > count( $table_rows[ $i ] ) ) {
				$diference = count( $table_columns ) - count( $table_rows[ $i ] );

				for ( $j = 0; $j < $diference; $j ++ ) {
					array_push( $table_rows[ $i ], null );
				}
			}

			$tr_html .= '<tr class="df-advanced-table__body-row">';

			foreach ( $table_rows[ $i ] as $key => $row ) :
				$cell = $row == null ? '' : $row;

				$tr_html .= sprintf( '<td class="df-advanced-table__body-row-cell">%1$s</td>', et_core_esc_previously( $cell ) ); //et_core_esc_previously
			endforeach;
			$tr_html .= '</tr>';
		endfor;

		return $tr_html;
	}

	public function google_sheet_render() {
		$error_message = [];
		$sheet_id      = esc_html( $this->props['google_sheet_id'] );
		$range         = $this->props['google_sheet_range'] ? str_replace( ':', '%3A', esc_html( trim( $this->props['google_sheet_range'] ) ) ) : '';
		$api_key       = esc_html( $this->props['google_api_key'] );
		$base_url      = 'https://sheets.googleapis.com/v4/spreadsheets/';
		$parameters    = '?dateTimeRenderOption=FORMATTED_STRING&majorDimension=ROWS&valueRenderOption=FORMATTED_VALUE&key=';
		$url           = $base_url . $sheet_id . '/values/' . $range . $parameters . $api_key;

		// error handling for editor fields.
		if ( empty( $api_key ) ) {
			$error_message[] = __( 'Add API key', 'divi_flash' );
		} elseif ( empty( $sheet_id ) ) {
			$error_message[] = __( 'Add Google Sheets ID', 'divi_flash' );
		} elseif ( empty( $range ) ) {
			$error_message[] = __( 'Add Sheets Range', 'divi_flash' );
		}
		if ( ! empty( $error_message ) ) {
			return sprintf( '<div class="df-data-table-error">%1$s</div>', $error_message[0] );
		}

		$transient_key = '_data_table_cash';
		$table_data    = get_transient( $transient_key );

		if ( false === $table_data ) {
			$data       = wp_remote_get( $url );
			$table_data = json_decode( wp_remote_retrieve_body( $data ), true );
			set_transient( $transient_key, $table_data, 0 );
		}
		if ( $this->props['google_cache_remove'] == 'on' ) {
			delete_transient( $transient_key );
		}

		// error handling for google sheet
		if ( empty( $table_data ) ) {
			$error_message['sheet_empty'] = __( 'Google Sheet is Empty', 'divi_flash' );

			return sprintf( '<div class="df-data-table-error">%1$s</div>', $error_message['sheet_empty'] );
		} elseif ( ! empty( $table_data ) && ! empty( $table_data['error'] ) ) {
			$error_message['sheet_error'] = $table_data['error']['message'];

			if ( ! empty( $error_message['sheet_error'] ) ) {
				return sprintf( '<div class="df-data-table-error">%1$s</div>', $error_message['sheet_error'] );
			}
		}

		$table_columns = $table_data['values'][0];
		$table_rows    = array_splice( $table_data['values'], 1, count( $table_data['values'] ) );
		// Handle span if any
		$table_rows = $this->google_sheet_span_handler( $table_rows );
		$table_html = '';
		$table_html .= sprintf( '<table class="df-advanced-table">
                            <thead class="df-advanced-table__head">
                                <tr class="df-advanced-table__head-column">
                                %1$s
                                </tr>
                            </thead>', $this->google_sheet_head_function( $table_columns ) );
		$table_html .= sprintf( '<tbody class="df-advanced-table__body">
                                  %1$s
                                </tbody>
                        </table>', $this->google_sheet_body_function( $table_rows, $table_columns ) );

		return $table_html;

	}

	public function database_table_head_function( $columns ) {
		$data = '';
		foreach ( array_keys( $columns ) as $key => $column ) :
			$data .= sprintf( '<th class="df-advanced-table__head-column-cell">%1$s</th>', esc_html( $column ) );
		endforeach;

		return $data;
	}

	public function database_table_body_function( $table_rows ) {
		$tr_html = '';
		for ( $i = 0; $i < count( $table_rows ); $i ++ ) :

			$tr_html .= '<tr class="df-advanced-table__body-row">';
			foreach ( $table_rows[ $i ] as $row ) :
				$tr_html .= sprintf( '<td class="df-advanced-table__body-row-cell">%1$s</td>', 'on' === $this->props['keep_line_break'] ? nl2br( esc_html( $row ) ) : esc_html( $row ) );
			endforeach;
			$tr_html .= '</tr>';
		endfor;

		return $tr_html;
	}

	public function database_table_render() {
		global $wpdb;

		$table_name = $this->props['database_tables_list'];
		if ( empty( $table_name ) || $table_name === 'select' ) {
			return sprintf( '<div class="df-data-table-error"> %1$s </div>', __( 'Must select a table', 'divi_flash' ) );
		}
		$query = "SELECT * FROM {$table_name}";
		// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$table = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table_name ), ARRAY_A ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
		// phpcs:enable WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( is_wp_error( $table ) ) {
			return sprintf( '<div class="df-data-table-error">%1$s</div>', $table->get_error_message() );
		}

		$table_html = '';
		$table_html .= sprintf( '<table class="df-advanced-table">
                                <thead class="df-advanced-table__head">
                                    <tr class="df-advanced-table__head-column">
                                     %1$s
                                    </tr>
                                </thead>', $this->database_table_head_function( $table[0] ) );
		$table_html .= sprintf( '<tbody class="df-advanced-table__body">
                                    %1$s
                                </tbody>
            </table>', $this->database_table_body_function( $table ) );

		return $table_html;
	}

	/**
	 *Table Type Tablepress : Data Table Head Creator.
	 *
	 * @param Array $columns
	 *
	 * @return String Table Rows
	 */
	public function tablepress_table_head_function( $columns ) {
		$data = '';
		foreach ( $columns as $key => $column ) :
			$data .= sprintf( '<th class="df-advanced-table__head-column-cell">%1$s</th>', esc_html( $column ) );
		endforeach;

		return $data;
	}

	/**
	 * Table Type Tablepress : Data Table Body Creator.
	 *
	 * @param Array $table_rows
	 *
	 * @return String Table Rows
	 */
	public function tablepress_table_body_function( $table_rows ) {
		$tr_html = '';
		for ( $i = 1; $i < count( $table_rows ); $i ++ ) :

			$tr_html .= '<tr class="df-advanced-table__body-row">';
			foreach ( $table_rows[ $i ] as $row ) :
				$tr_html .= sprintf( '<td class="df-advanced-table__body-row-cell">%1$s</td>', $row );
			endforeach;
			$tr_html .= '</tr>';
		endfor;

		return $tr_html;
	}

	/**
	 * Table Type Tablepress:  Data table render main function
	 * Process the Data Table and Error handaling
	 *
	 * @return String Data Table
	 */
	public function tablepress_render() {


		if ( ! df_is_table_press_activated() ) {
			return sprintf( '<div class="df-data-table-error"> %1$s </div>', __( 'Install TablePress', 'divi_flash' ) );
		}
		if ( empty( df_get_table_press_list() ) ) {
			return sprintf( '<div class="df-data-table-error"> %1$s </div>', __( 'Create Table', 'divi_flash' ) );
		}
		if ( empty( $this->props['table_press_list'] ) ) {
			return sprintf( '<div class="df-data-table-error"> %1$s </div>', __( 'Select Table', 'divi_flash' ) );
		}

		if ( $this->props['table_press_list'] === 'select' ) {
			return sprintf( '<div class="df-data-table-error"> %1$s </div>', __( 'Select Table', 'divi_flash' ) );
		}

		$tables        = [];
		$tables_option = get_option( 'tablepress_tables', '{}' );
		$tables_opt    = json_decode( $tables_option, true );
		$tables        = $tables_opt['table_post'];
		$table_id      = $tables[ $this->props['table_press_list'] ];
		$table_data    = get_post_field( 'post_content', $table_id );
		$tables        = json_decode( $table_data, true );

		// $this->attributes();
		$table_html = '';
		$table_html .= sprintf( '<table class="df-advanced-table">
                            <thead class="df-advanced-table__head">
                                <tr class="df-advanced-table__head-column">
                                    %1$s
                                </tr>
                            </thead>', $this->tablepress_table_head_function( $tables[0] ) );
		$table_html .= sprintf( '<tbody class="df-advanced-table__body">
                                    %1$s
                                </tbody>
        </table>', $this->tablepress_table_body_function( $tables ) );

		return $table_html;

	}

	/**
	 * Table Type CSV upload :  Data Table Head Creator.
	 *
	 * @param Array $columns
	 *
	 * @return String Table Rows
	 */
	public function csv_upload_table_head_function( $columns ) {
		$data = '';
		foreach ( $columns as $key => $column ) :
			$data .= sprintf( '<th class="df-advanced-table__head-column-cell"> %1$s </th>',
				str_replace( '`', ',', mb_convert_encoding( $column, 'UTF-8', mb_detect_encoding( $column, 'UTF-8, ISO-8859-1, ISO-8859-15', true ) ) )
			);
		endforeach;

		return $data;
	}

	/**
	 * Table Type CSV upload : Data Table Body Creator.
	 *
	 * @param Array $table_rows
	 *
	 * @return String Table Rows
	 */

	public function csv_upload_table_body_function( $table_rows ) {
		$tr_html           = '';
		$table_rows_length = count( $table_rows );
		for ( $i = 0; $i < $table_rows_length; $i ++ ) :
			$expr    = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
			$result  = preg_split( $expr, trim( $table_rows[ $i ] ) );
			$rows    = preg_replace( "/^\"(.*)\"$/", '$1', $result );
			$tr_html .= '<tr class="df-advanced-table__body-row">';
			foreach ( $rows as $row ) :
				$tr_html .= sprintf( '<td class="df-advanced-table__body-row-cell">%1$s</td>',
					str_replace( '`', ',', mb_convert_encoding( $row, 'UTF-8', mb_detect_encoding( $row, 'UTF-8, ISO-8859-1, ISO-8859-15', true ) ) ) // change from utf8_encode() for Danish and other language special charecter.
				);
			endforeach;
			$tr_html .= '</tr>';
		endfor;

		return $tr_html;
	}

	/**
	 * Table Type CSV upload:  Data table render main function
	 * Process the Data Table and Error handaling
	 *
	 * @return String Data Table
	 */

	public function csv_upload_table_render() {

		if ( empty( $this->props['csv_upload_data'] ) ) {
			return sprintf( '<div class="df-data-table-error">%1$s</div>', __( 'Only CSV file will Suport. So Upload CSV file.', 'divi_flash' ) );
		}
		$file_url = @fopen( $this->props['csv_upload_data'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $file_url ) {
			return sprintf( '<div class="df-data-table-error">%1$s</div>', __( 'File isn\'t available', 'divi_flash' ) );
		}
		$file_parts = pathinfo( $this->props['csv_upload_data'] );
		if ( $file_parts['extension'] !== 'csv' ) {
			$error_message = sprintf( 'Oops! %1$s file can\'t be uploaded, So you have to Upload CSV file.', $file_parts['extension'] );

			return sprintf( '<div class="df-data-table-error">%1$s</div>', $error_message );
		}
		$file = $this->props['csv_upload_data'];

		$delimiter = '';
		if ( $this->df_detectCSVFileDelimiter( $file ) ) {
			$delimiter = $this->df_detectCSVFileDelimiter( $file );
		} else {
			$delimiter .= ',';
		}
		$csv = [];
        //		$fileData = file( $file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
        //		$csv      = array_map(
        //			function ( $v ) use ( $delimiter ) {
        //				return str_getcsv( $v, $delimiter );
        //			},
        //			$fileData
        //		);
		$fileData = fopen( $file, 'rb' );
		$row      = fgetcsv( $fileData );
		while ( ! empty( $row ) ) {
			$csv[] = $row;
			$row   = fgetcsv( $fileData );
		}

		$csv_data = [];
		foreach ( $csv as $key => $value ) {
			$change_value = str_replace( [ ',', '"' ], [ '`', '""' ], $value );
			$new_data     = implode( ',', $change_value );
			$csv_data[]   = $new_data;
		}

		$columns = explode( ',', $csv_data[0] );

		$table_rows = array_splice( $csv_data, 1, count( $csv_data ) );
		$table_html = '';
		$table_html .= sprintf( '<table class="df-advanced-table">
                            <thead class="df-advanced-table__head">
                                <tr class="df-advanced-table__head-column">
                                    %1$s
                                 </tr>
                            </thead>', $this->csv_upload_table_head_function( $columns ) );
		$table_html .= sprintf( '<tbody class="df-advanced-table__body">
                             %1$s
                        </tbody>
		</table>', $this->csv_upload_table_body_function( $table_rows ) );

		return $table_html;

	}

	/**
	 * Detact delimiter from CSV file.
	 * Parse the CSV file and find out the delimiter.
	 *
	 * @param String $csvFile
	 *
	 * @return String the delimiter
	 */
	public function df_detectCSVFileDelimiter( $csvFile ) {
		$delimiters = [ ',' => 0, ';' => 0, "\t" => 0, '|' => 0, ];
		$firstLine  = '';
		$handle     = fopen( $csvFile, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( $handle ) {
			$firstLine = fgets( $handle );
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		if ( $firstLine ) {
			foreach ( $delimiters as $delimiter => &$count ) {
				$count = count( str_getcsv( $firstLine, $delimiter, '"', '\\' ) );
			}

			return array_search( max( $delimiters ), $delimiters );
		} else {
			return key( $delimiters );
		}
	}

	public function import_table_head_function( $columns ) {
		$data = '';
		foreach ( $columns as $key => $column ) :
			$data .= sprintf( '<th class="df-advanced-table__head-column-cell"> %1$s </th>', $column );
		endforeach;

		return $data;
	}

	public function import_table_body_function( $table_rows ) {
		$tr_html = '';
		for ( $i = 0; $i < count( $table_rows ); $i ++ ) :
			$rows    = explode( ',', $table_rows[ $i ] );
			$expr    = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
			$result  = preg_split( $expr, trim( $table_rows[ $i ] ) );
			$rows    = preg_replace( "/^\"(.*)\"$/", '$1', $result );
			$tr_html .= '<tr class="df-advanced-table__body-row">';
			foreach ( $rows as $row ) :
				$tr_html .= sprintf( '<td class="df-advanced-table__body-row-cell">%1$s</td>', preg_replace( "/\"/", '', $row ) );
			endforeach;
			$tr_html .= '</tr>';
		endfor;

		return $tr_html;
	}

	public function import_table_render() {

		if ( empty( $this->props['import_table_data'] ) ) {
			return sprintf( '<div class="df-data-table-error">%1$s</div>', __( 'Paste Data in CSV format.', 'divi_flash' ) );
		}

		$table_data = explode( "\n", $this->props['import_table_data'] );
		$columns    = explode( ',', $table_data[0] );
		$table_rows = array_splice( $table_data, 1, count( $table_data ) );
		for ( $i = 0; $i < count( $table_rows ); $i ++ ) :
			$rows   = explode( ',', $table_rows[ $i ] );
			$expr   = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
			$result = preg_split( $expr, trim( $table_rows[ $i ] ) );
			$rows   = preg_replace( "/^\"(.*)\"$/", '$1', $result );
			if ( count( $rows ) ) {
				if ( count( $rows ) !== count( $columns ) ) {
					return sprintf( '<div class="df-data-table-error">%1$s</div>', __( 'Datatable Format does\'t match', 'divi_flash' ) );
				}
			}
		endfor;
		$table_html = '';
		$table_html .= sprintf( '<table class="df-advanced-table">
                            <thead class="df-advanced-table__head">
                                <tr class="df-advanced-table__head-column">
                                    %1$s
                                 </tr>
                            </thead>', $this->import_table_head_function( $columns ) );
		$table_html .= sprintf( '<tbody class="df-advanced-table__body">
                             %1$s
                        </tbody>
		</table>', $this->import_table_body_function( $table_rows ) );

		return $table_html;

	}

	public function render( $attrs, $content, $render_slug ) {
		wp_enqueue_script( 'data-table-script' );
		wp_enqueue_script( 'df-advanced-data-table' );

		$data_options = [
			'adt_search'        => isset( $this->props['adt_search'] ) && $this->props['adt_search'] === 'on' ? true : false,
			'adt_paging'        => isset( $this->props['adt_paging'] ) && $this->props['adt_paging'] === 'on' ? true : false,
			'show_entries'      => ! empty( $this->props['show_entries'] ) ? $this->props['show_entries'] : '10',
			'adt_order'         => isset( $this->props['adt_order'] ) && $this->props['adt_order'] === 'on' ? true : false,
			'adt_info'          => isset( $this->props['adt_info'] ) && $this->props['adt_info'] === 'on' ? true : false,
			'multi_lang_enable' => isset( $this->props['multi_lang_enable'] ) && $this->props['multi_lang_enable'] === 'on' ? true : false,
			'multi_lang_name'   => isset( $this->props['multi_lang_name'] ) && $this->props['multi_lang_enable'] === 'on' ? $this->props['multi_lang_name'] : 'English',
			// 'adt_scroll_x'         => isset($this->props['adt_scroll_x']) && $this->props['adt_scroll_x'] === 'on' ? true : false,
		];
		$table_html   = '';
		if ( $this->props['table_type'] == 'google_sheet' ) {
			$table_html .= $this->google_sheet_render();
		} elseif ( $this->props['table_type'] == 'database_table' ) {
			$table_html .= $this->database_table_render();
		} elseif ( $this->props['table_type'] == 'table_press' ) {
			$table_html .= $this->tablepress_render();
		} elseif ( $this->props['table_type'] == 'import_table' ) {
			$table_html .= $this->import_table_render();
		} elseif ( $this->props['table_type'] == 'csv_upload' ) {
			$table_html .= $this->csv_upload_table_render();
		}

		return sprintf( '<div class="df_adt_container" data-options=\'%2$s\'>
                                <div class="df_adt_content">
                                    %1$s
                                </div>
                        </div>', $table_html, wp_json_encode( $data_options ) );
	}

	protected function google_sheet_span_handler( $rows ) {
		$sheet_data = reset( $this->get_merges_cols()->sheets );
		$merges     = [];

		if ( property_exists( $sheet_data, 'merges' ) && ! empty( $sheet_data->merges ) ) {
			$merges = $sheet_data->merges;
		}

		if ( empty( $merges ) ) {
			return $rows;
		}

		$span_mapper = $this->get_google_sheet_span_mapper( $merges );

		foreach ( $rows as $row_key => $row_data ) {
			foreach ( $row_data as $col_key => $col_data ) {
				if ( $col_data !== '' ) {
					continue;
				}
				$row = $row_key + 2;
				$col = $col_key + 1;

				if ( array_key_exists( $row, $span_mapper['rowspan'] ) ) {
					foreach ( $span_mapper['rowspan'][ $row ] as $item ) {
						$rows[ $row_key ][ $col_key ] = '#rowspan#';
					}
				}
				if ( array_key_exists( $row, $span_mapper['colspan'] ) ) {
					foreach ( $span_mapper['colspan'][ $row ] as $item ) {
						$rows[ $row_key ][ $col_key ] = '#colspan#';
					}
				}
			}
		}

		return $rows;

	}

	protected function get_google_sheet_span_mapper( $merges ) {
		$rowspan = [];
		$colspan = [];

		foreach ( $merges as $cells ) {
			if ( $cells->endRowIndex - $cells->startRowIndex > 1 && $cells->endColumnIndex - $cells->startColumnIndex === 1 ) {
				$rows = [];
				for ( $i = $cells->startRowIndex + 1; $i < $cells->endRowIndex; $i ++ ) {
					$rows[] = $i + 2;
				}
				$rowspan[ $cells->endRowIndex ] = $rows;
			}

			if ( $cells->endColumnIndex - $cells->startColumnIndex > 1 && $cells->endRowIndex - $cells->startRowIndex === 1 ) {
				$cols = [];
				for ( $i = $cells->startColumnIndex + 1; $i < $cells->endColumnIndex; $i ++ ) {
					$cols[] = $i + 1;
				}

				$colspan[ $cells->startRowIndex + 1 ] = $cols;
			}

		}

		return [ 'rowspan' => $rowspan, 'colspan' => $colspan ];

	}
}
