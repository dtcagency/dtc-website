<?php

namespace DIFL\Server;

class Localizer {
	private static string $handler = "difl-divi5-new";

	public function __construct() {
		// Same hook as Main::enqueue_scripts; registered after it so the script exists before localize.
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', [ $this, 'enqueue_localize_data' ] );
	}

	public function enqueue_localize_data() {
		global $post;
		wp_localize_script(
			self::$handler,
			'diflVBLocalData',
			apply_filters( 'difl_divi5_localize_data', [
				'nonce'                => wp_create_nonce( 'difl_ajax_nonce' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'acf_settings'         => $this->get_acf_data(),
				'acf_gallery'          => [
					"acf_gallery_fields"    => $this->get_acf_gallery_fields(),
					"registered_image_size" => $this->get_registered_image_size(),
				],
				'library_item'         => $this->df_library_items(),
				'wpforms'              => $this->get_all_wpforms(),
				'wpcf7'                => $this->get_all_wpcf7(),
				'gravity_forms'        => $this->get_all_gravity_forms(),
				'database_tables_list' => $this->get_database_tables_list(),
				'table_press_list'     => $this->get_table_press_list(),
				'active_modules'       => json_decode( get_option( 'df_active_modules' ) ),
				'product_categories'   => $this->get_product_categories(),
				'post_data'            => $this->get_postdata(),
				'postMeta'             => $post,
				// 'page_link'            => get_page_link(),
				'page_link'            => $this->get_safe_page_link_for_vb(),
			] ) );
	}

	/**
	 * Theme Builder / layout screens often have no global $post; get_page_link() then errors in core.
	 */
	private function get_safe_page_link_for_vb(): string {
		global $post;
		if ( $post instanceof \WP_Post ) {
			return get_page_link( $post );
		}

		return home_url( '/' );
	}

	private function get_table_press_list() {
		$tables_list = [];
		foreach ( \df_get_table_press_list() as $key => $table ) {
			$tables_list[ $key ]['label'] = $table;
		}

		return $tables_list;
	}

	private function get_database_tables_list() {
		$tables_list = [];
		foreach ( \df_db_tables_list() as $key => $table ) {
			$tables_list[ $key ]['label'] = $table;
		}

		return $tables_list;
	}

	private function get_all_gravity_forms() {
		$option_array                  = [];
		$option_array['none']['label'] = 'Select a form';
		if ( class_exists( 'GFAPI' ) ) {
			$forms = \GFAPI::get_forms();
			if ( ! empty( $forms ) ) {
				foreach ( $forms as $form ) {
					$option_array[ $form['id'] ]['label'] = $form['title'];
				}

				return $option_array;
			}
		}

		return $option_array;
	}

	private function get_all_wpcf7() {
		$cf7                     = [];
		$args                    = [
			'post_type'   => 'wpcf7_contact_form',
			'numberposts' => - 1,
		];
		$contact_forms           = get_posts( $args );
		$cf7['default']['label'] = 'Select an item';
		foreach ( $contact_forms as $contact_form ) {
			$cf7[ $contact_form->ID ]['label'] = $contact_form->post_title;
		}

		return $cf7;
	}

	private function get_all_wpforms() {
		$wpforms                     = [];
		$args                        = [
			'post_type'   => 'wpforms',
			'numberposts' => - 1,
		];
		$forms                       = get_posts( $args );
		$wpforms['default']['label'] = 'Select an item';
		foreach ( $forms as $form ) {
			$wpforms[ $form->ID ]['label'] = $form->post_title;
		}

		return $wpforms;
	}

	private function get_acf_gallery_fields() {
		if ( ! function_exists( 'acf_get_fields' ) && ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}
		$field_groups                           = acf_get_field_groups();
		$field_labels                           = [];
		$field_labels['select_option']['label'] = "Select Gallery";

		if ( $field_groups ) {
			foreach ( $field_groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				if ( $fields ) {
					foreach ( $fields as $field ) {
						if ( ! is_array( $field ) ) {
							continue;
						}
						if ( $field['type'] === 'gallery' ) {
							$field_labels[ $field['name'] ]['label'] = $field['label'];
						}
					}
				}
			}
		}

		return $field_labels;
	}

	public function get_registered_image_size() {
		$options  = [];
		$sub_size = wp_get_registered_image_subsizes();
		foreach ( $sub_size as $key => $value ) {
			$options[ $key ]['label'] = $key;
		}

		return $options;
	}

	private function df_library_items() {
		$library_item = [];

		foreach ( df_load_library() as $key => $value ) {
			$library_item[ $key ]['label'] = $value;
		}

		return $library_item;
	}

	private function get_product_categories() {
		$terms = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'id=>name',
			'exclude'    => [ get_option( 'default_product_cat' ) ],
		] );

		$options = [
			[ 'label' => esc_html__( 'All Categories', 'et_builder' ), 'value' => 'all' ],
			[ 'label' => esc_html__( 'Current Category', 'et_builder' ), 'value' => 'current' ],
		];

		foreach ( $terms as $key_term => $term_value ) {
			$options[] = [ 'label' => $term_value, 'value' => (string) $key_term ];
		}

		return $options;
	}

	private function get_postdata() {
		//HANDLE REGISTERED CATEGORIES
		$categories    = get_categories( [
			'hide_empty' => false,
			'number'     => 100,
		] );
		$category_list = [
			[
				'label' => __( 'Current Category', 'divi_flash' ),
				'value' => 'current',
			],
		];
		foreach ( $categories as $category ) {
			$category_list[] = [
				'label' => $category->name,
				'value' => $category->term_id,
				'slug'  => $category->slug,
				'count' => $category->count,
			];
		}

		//HANDLE REGISTERED TAGS
		$tags = get_tags( [
			'hide_empty' => false,
			'number'     => 100,
		] );

		$tag_list = [];

		foreach ( $tags as $tag ) {
			$tag_list[] = [
				'label' => $tag->name,
				'value' => $tag->term_id,
				'slug'  => $tag->slug,
				'count' => $tag->count,
			];
		}

		//HANDLE STICKY POST LIST
		$sticky           = get_option( 'sticky_posts' );
		$sticky_post_list = [];
		foreach ( $sticky as $postid ) {
			$sticky_post_list[] = [
				'label' => get_the_title( $postid ),
				'value' => $postid,
			];
		}

		//HANDLE CUSTOM FIELD LIST
		$custom_fields     = [];

		$custom_fieldsArry = $this->get_most_used_post_meta_keys();
		// if ( empty( $custom_fields ) ) {
			foreach ( $custom_fieldsArry as $field ) {
				$custom_fields[ $field ] = [
					'label' => $field,
					'value' => $field,
				];
			}
		// }


		return [
			'categories'       => $category_list,
			'tags'             => $tag_list,
			'sticky_post_list' => $sticky_post_list,
			'custom_fields'    => $custom_fields,
			'post_types'       => $this->df_get_post_types(),
		];
	}

	function df_get_post_types( $usort = false, $require_editor = true ) {
		$require_editor_key = $require_editor ? '1' : '0';
		$key                = "df_get_registered_post_type_options:{$require_editor_key}";

		$blocklist = et_builder_get_blocklisted_post_types();
		$allowlist = et_builder_get_third_party_post_types();

		// Extra and Library layouts shouldn't appear in Theme Options as configurable post types.
		$blocklist = array_merge(
			$blocklist,
			[
				'et_pb_layout',
				'layout',
				'attachment',
				'page',
			]
		);

		$blocklist = apply_filters( 'et_builder_post_type_options_blocklist', $blocklist );

		$raw_post_types = get_post_types(
			[ 'show_ui' => true ],
			'objects'
		);

		$post_types = [];

		foreach ( $raw_post_types as $post_type ) {
			$is_allowlisted  = in_array( $post_type->name, $allowlist, true );
			$is_blocklisted  = in_array( $post_type->name, $blocklist, true );
			$supports_editor = $require_editor ? post_type_supports( $post_type->name, 'editor' ) : true;
			$is_public       = et_builder_is_post_type_public( $post_type->name );

			if ( ! $is_allowlisted && ( $is_blocklisted || ! $supports_editor || ! $is_public ) ) {
				continue;
			}

			$post_types[] = $post_type;
		}

		if ( $usort && is_callable( $usort ) ) {
			usort( $post_types, $usort );
		}

		$post_type_options = [];

		foreach ( $post_types as $post_type ) {
			$post_type_options[ $post_type->name ] = [
				'label' => $post_type->label,
			];
		}

		if ( empty( $post_type_options ) ) {
			$post_type_options = [
				'not_found' => [ 'label' => 'No Custom Post Type Found' ],
			];
		}

		return $post_type_options;
	}

	protected function get_most_used_post_meta_keys() {
		global $wpdb;

		$most_used_meta_keys = get_transient( 'et_builder_most_used_meta_keys' );
		if ( false !== $most_used_meta_keys ) {
			return $most_used_meta_keys;
		}

		$public_post_types = array_keys( et_builder_get_public_post_types() );
		$post_types        = "'" . implode( "','", esc_sql( $public_post_types ) ) . "'";

		$sql = "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON ( p.ID = pm.post_id AND p.post_type IN ({$post_types}) )
		WHERE pm.meta_key NOT LIKE '\_%'
		GROUP BY pm.meta_key
		ORDER BY COUNT(pm.meta_key) DESC
		LIMIT 50";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql query does not use users/visitor input
		$most_used_meta_keys = $wpdb->get_col( $sql );

		set_transient( 'et_builder_most_used_meta_keys', $most_used_meta_keys, 5 * MINUTE_IN_SECONDS );

		return $most_used_meta_keys;
	}

    private function get_acf_data() {
        $fieldsObj = [];
        if(class_exists('ACF') && get_option( 'df_general_acf_field_support' )==='1'){
            $groups = acf_get_field_groups(array('post_type' => 'post'));
            foreach($groups as $group) {
                $fieldsArr = acf_get_fields($group['key']);
                foreach ($fieldsArr as $field) {
                    $fieldsObj[$field['name']] = [
                        'label' => $field['label'],
                    ];
                }

            }
        }

        return array(
            'status'=> (get_option( 'df_general_acf_field_support' ) === '1' && class_exists('ACF'))?"active":"inactive",
            'post_releated_acf_fields'=>$fieldsObj
        );
    }

	/**
	 * PHP consumer helpers for specific datasets (not part of JS localization).
	 */
	public static function get_acf_list() {
		$instance = new self();
		return $instance->get_acf_fields_by_post_type();
	}

	public static function get_pod_list() {
		$instance = new self();
		return $instance->get_pod_fields_by_post_type();
	}

	public static function get_taxonomies_list() {
		$instance = new self();
		return $instance->get_taxonomies_for_post_types();
	}

	private function get_taxonomies_for_post_types() {
		$post_types = $this->df_get_post_types();
		$taxonomies_list = [];

		foreach ( array_keys( $post_types ) as $post_type ) {
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$tax_array = [];
			if ( ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
					$tax_array[] = [
						'label' => $taxonomy->label,
						'value' => $taxonomy->name,
					];
				}
			}
			$taxonomies_list[ $post_type ] = $tax_array;
		}

		return $taxonomies_list;
	}

	private function get_acf_fields_by_post_type() {
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}

		$post_types = array_keys( $this->df_get_post_types() );
		$acf_fields_list = [];

		foreach ( $post_types as $post_type ) {
			$field_groups = acf_get_field_groups( [ 'post_type' => $post_type ] );
			$fields_array = [];

			foreach ( $field_groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				if ( $fields ) {
					foreach ( $fields as $field ) {
						if ( is_array( $field ) ) {
							$fields_array[] = [
								'label' => $field['label'],
								'value' => $field['name'],
							];
						}
					}
				}
			}

			$acf_fields_list[ $post_type ] = $fields_array;
		}

		return $acf_fields_list;
	}

	private function get_pod_fields_by_post_type() {
		if ( ! defined( 'PODS_VERSION' ) ) {
			return [];
		}

		$post_types = array_keys( $this->df_get_post_types() );
		$pod_fields_list = [];

		foreach ( $post_types as $post_type ) {
			$pod_fields_array = [];
			try {
				$pod = pods( $post_type );
				if ( $pod && is_object( $pod ) && isset( $pod->pod ) && is_object( $pod->pod ) && method_exists( $pod->pod, 'get_fields' ) ) {
					$fields = $pod->pod->get_fields();
					if ( is_array( $fields ) ) {
						foreach ( $fields as $field_name => $field_data ) {
							if ( isset( $field_data['label'] ) ) {
								$pod_fields_array[] = [
									'label' => $field_data['label'],
									'value' => $field_name,
								];
							}
						}
					}
				}
			} catch ( \Exception $e ) {
				// Pod doesn't exist for this post type
			}

			$pod_fields_list[ $post_type ] = $pod_fields_array;
		}

		return $pod_fields_list;
	}


}

new Localizer();
