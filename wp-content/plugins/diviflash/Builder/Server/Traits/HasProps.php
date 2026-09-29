<?php

namespace DIFL\Server\Traits;

use DIFL\Server\Utils\Props;

trait HasProps {
	protected Props $props;

	protected function set_props( array $attrs ) {
		$props = [];

		if ( empty( $attrs ) ) {
			return;
		}
		foreach ( $attrs as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}
			if ( is_string( $value ) ) {
				$props[ $key ] = $value;
				continue;
			}

			if ( is_array( $value ) && ! array_key_exists( 'innerContent', $value ) ) {
				continue;
			}

			// Use isset + null/'' check only: empty() wrongly drops 0, '0', and false (valid editor values).
			if ( isset( $value['innerContent']['desktop']['value'] ) ) {
				$content = $value['innerContent']['desktop']['value'];
				if ( $content === null || $content === '' ) {
					continue;
				}
				if ( is_array( $content ) ) {
					$is_checkboxes = count( array_filter( array_keys( $content ), 'is_numeric' ) ) === count( $content );
					if ( $is_checkboxes ) {
						$props[ $key ] = $content;
						continue;
					}
					if ( array_key_exists( 'type', $content ) && ( $content['type'] === 'divi' || $content['type'] === 'fa' ) ) {
						$props[ $key ] = $content;
						continue;
					}
					foreach ( $content as $key => $value ) {
						$props[ $key ] = $value;
					}
				} else {
					$props[ $key ] = $content;
				}
			}
		}

		if ( isset( $this->attributes ) ) {
			foreach ( $this->attributes as $key => $value ) {
				if ( ! isset( $value['default_value'] ) ) {
					continue;
				}

				if ( isset( $value['default_value']['innerContent']['desktop']['value'] ) ) {
					$desktopValue = $value['default_value']['innerContent']['desktop']['value'];

					if ( ! is_array( $desktopValue ) ) {
						if ( ! isset( $props[ $key ] ) ) {
							$props[ $key ] = $desktopValue;
						}
					} else {
						$is_checkboxes = count( array_filter( array_keys( $desktopValue ), 'is_numeric' ) ) === count( $desktopValue );
						if ( $is_checkboxes && ! isset( $props[ $key ] ) ) {
							$props[ $key ] = $desktopValue;
							continue;
						}

						foreach ( $desktopValue as $inner_key => $inner_value ) {
							if ( ! isset( $props[ $inner_key ] ) ) {
								$props[ $inner_key ] = $inner_value;
							}
						}
					}
				}
			}
		}

		$this->props = new Props( $props );
	}
}
