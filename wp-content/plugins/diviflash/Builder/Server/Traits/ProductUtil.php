<?php
namespace DIFL\Server\Traits;

trait ProductUtil {
	public static function get_no_results_template( $heading_tag = 'h1' ) {
		global $et_no_results_heading_tag;
		$et_no_results_heading_tag = $heading_tag;
		ob_start();

		if ( et_is_builder_plugin_active() ) {
			include ET_BUILDER_PLUGIN_DIR . 'includes/no-results.php';
		} else {
			get_template_part( 'includes/no-results', 'index' );
		}

		return ob_get_clean();
	}

	/**
	 * Filters out invalid term ids from an array.
	 *
	 * @since 3.0.106
	 *
	 * @param integer[] $term_ids
	 * @param string    $taxonomy
	 *
	 * @return integer[]
	 */
	public static function filter_invalid_term_ids( $term_ids, $taxonomy ) {
		$valid_term_ids = array();

		foreach ( $term_ids as $term_id ) {
			$term_id = intval( $term_id );
			$term    = term_exists( $term_id, $taxonomy );
			if ( ! empty( $term ) ) {
				$valid_term_ids[] = $term_id;
			}
		}

		return $valid_term_ids;
	}

	/**
	 * Convert an array or comma-separated list of term ids and special keywords to an array of term ids.
	 *
	 * @since 3.17.2
	 *
	 * @param string|array $terms Comma-separated list of term ids and special keywords.
	 * @param integer      $post_id Optional post id to resolve "current" categories.
	 * @param string       $taxonomy
	 *
	 * @return integer[]
	 */
	protected static function filter_meta_categories( $categories, $post_id = 0, $taxonomy = 'category' ) {
		$raw_term_ids = is_array( $categories ) ? $categories : explode( ',', $categories );

		if ( in_array( 'all', $raw_term_ids, true ) ) {
			// If "All Categories" is selected return an empty array so it works for all terms
			// even ones created after the module was last updated.
			return array();
		}

		$term_ids = array();

		foreach ( $raw_term_ids as $value ) {
			if ( 'current' === $value ) {
				if ( $post_id > 0 ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy );

					if ( is_wp_error( $post_terms ) ) {
						continue;
					}

					$term_ids = array_merge( $term_ids, wp_list_pluck( $post_terms, 'term_id' ) );
				} else {
					$is_category = 'category' === $taxonomy && is_category();
					$is_tag      = ! $is_category && 'post_tag' === $taxonomy && is_tag();
					$is_tax      = ! $is_category && ! $is_tag && is_tax( $taxonomy );

					if ( $is_category || $is_tag || $is_tax ) {
						$term_ids[] = get_queried_object()->term_id;
					}
				}

				continue;
			}
			$term_ids[] = (int) $value;
		}

		$term_ids = self::filter_invalid_term_ids( array_unique( array_filter( $term_ids ) ), $taxonomy );

		return $term_ids;
	}

	/**
	 * Handle common filtering of included categories, including handling meta categories.
	 *
	 * @since 4.0
	 *
	 * @param string|array $include_categories Comma-separated list of term ids and special keywords.
	 * @param integer      $post_id
	 * @param string       $taxonomy
	 *
	 * @return array
	 */
	protected static function filter_include_categories( $include_categories, $post_id = 0, $taxonomy = 'category' ) {
		$categories = array();

		if ( ! empty( $include_categories ) ) {
			// wp_doing_ajax() covers VB usage when fetching computed values where we always have a post.
			if ( is_singular() || wp_doing_ajax() ) {
				$post_id    = $post_id > 0 ? $post_id : self::get_current_post_id_reverse();
				$categories = self::filter_meta_categories( $include_categories, $post_id, $taxonomy );
			} else {
				$categories = self::filter_meta_categories( $include_categories, 0, $taxonomy );
			}
		}

		return $categories;
	}

	public static function get_current_post_id_reverse() {
		// phpcs:disable WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
		$post_id = self::_get_main_post_id();

		// try to get post id from get_post_ID().
		if ( false !== $post_id ) {
			return $post_id;
		}

		if ( wp_doing_ajax() ) {
			// get the post ID if loading data for VB.
			return isset( $_POST['et_post_id'] ) ? absint( $_POST['et_post_id'] ) : false;
		}

		// fallback to $_GET['post'] to cover the BB data loading.
		return isset( $_GET['post'] ) ? absint( $_GET['post'] ) : false;
		// phpcs:enable
	}

	protected static function _get_main_post_id() {
		if ( et_core_should_respect_post_interference() ) {
			return get_the_ID();
		}

		return \ET_Post_Stack::get_main_post_id();
	}

    /**
	 * next and prev icons
	 */
	public function arrow_icon( $set = 'set_1', $type = 'next' ) {
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

		return $icons[ $set ][ $type ];
	}

    /**
	 * function for 'woocommerce_pagination_args' hook overwrite
	 *
	 * @param $args default pagination
	 *
	 * @return  Array $args
	 */
	public function pagination_args( $args ) {
        $next_prev_icon_set = $this->props['next_prev_icon'] ?? 'set_1';

        $args['prev_text'] = '<span class="et-pb-icon">' . esc_attr( et_pb_process_font_icon( $this->arrow_icon( $next_prev_icon_set, 'prev' ) ) ) . '</span>';
        $args['next_text'] = '<span class="et-pb-icon">' . esc_attr( et_pb_process_font_icon( $this->arrow_icon( $next_prev_icon_set, 'next' ) ) ) . '</span>';

		return $args;
	}

}

