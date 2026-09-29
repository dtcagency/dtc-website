<?php
namespace DIFL\Server\Modules\CountdownTimer;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use DIFL\Server\Traits\ProcessStylePreset;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback {
  use Classnames;
  use Styles;
  use ScriptData;
  use ProcessStylePreset;

  /**
   * Resolve a "YYYY-MM-DD[ T]HH:MM" string against an IANA timezone (or the
   * WP-configured zone, or visitor-local) to a Unix timestamp. Shared by the
   * Fixed Date and Count Up branches so both compute the same instant from
   * the same wall-clock input regardless of PHP's default timezone.
   */
  protected static function resolve_local_datetime( $date_string, $tz ) {
    if ( empty( $date_string ) || $tz === 'visitor' ) {
      return 0;
    }
    $datetime_str = str_replace( 'T', ' ', $date_string );
    try {
      if ( $tz === 'wp' ) {
        $zone = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );
      } else {
        $zone = new \DateTimeZone( $tz );
      }
      $dt = new \DateTime( $datetime_str, $zone );
      return $dt->getTimestamp();
    } catch ( \Exception $e ) {
      return strtotime( $datetime_str );
    }
  }

  public static function render_callback($attrs, $content, $block, $elements) {
    wp_enqueue_script('df-countdown-timer');

    // Include timer_settings, label_settings, and expiration_settings in the
    // preset merge so "Save as Preset" can carry the target date, label text,
    // and expiration message into other instances. The trait layers
    // user_raw_attrs LAST, so a receiver that has explicitly saved its own
    // date/labels/expiration message still wins over the preset — the
    // original concern about presets clobbering per-instance content is
    // handled by that ordering. See docs/MODULE_PRESETS.md.
    $attrs = self::processStylePreset(
      $attrs,
      $block->block_type->name,
      ['module', 'timer_settings', 'label_settings', 'expiration_settings',
       'style_settings', 'unit', 'number_font', 'label_font', 'separator_font'],
      $block->parsed_block['attrs'] ?? []
    );

    // Divi's auto-resolve of global color variables ($variable(...)$ → hex)
    // runs on the divi_module_library_register_module_attrs filter, which
    // fires BEFORE the preset merge above — so any color from the preset
    // arrives unresolved. Re-run the resolver on the merged attrs so the
    // FE chart_config receives concrete hex values for SVG fill/stroke.
    if (function_exists('difl_resolve_color_values_in_attrs')) {
      $attrs = difl_resolve_color_values_in_attrs($attrs);
    }

    $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
    $parent_attrs = $parent->attrs ?? [];

    // Extract settings from attribute groups
    $timer      = $attrs['timer_settings']['innerContent']['desktop']['value'] ?? [];
    $labels     = $attrs['label_settings']['innerContent']['desktop']['value'] ?? [];
    $expiration = $attrs['expiration_settings']['innerContent']['desktop']['value'] ?? [];
    $style      = $attrs['style_settings']['innerContent']['desktop']['value'] ?? [];

    // Compute end timestamp for fixed countdown
    $countdown_type = $timer['countdown_type'] ?? 'fixed';
    $end_timestamp  = 0;
    $target_tz      = $timer['target_timezone'] ?? 'wp';

    if ($countdown_type === 'fixed') {
      $end_timestamp = self::resolve_local_datetime(
        $timer['target_date'] ?? '2026-12-31 23:59',
        $target_tz
      );
    }

    // Count Up — anchor in the past (or future). Frontend JS computes
    // `now − anchor` (or `anchor − now` when future, then auto-flips at zero).
    $count_up_anchor_ts = 0;
    if ($countdown_type === 'count_up') {
      $count_up_anchor_ts = self::resolve_local_datetime(
        $timer['count_up_anchor_date'] ?? '2025-01-01 00:00',
        $timer['count_up_timezone']    ?? 'wp'
      );
    }

    // Compute evergreen duration from days/hours/minutes
    $eg_days    = intval($timer['evergreen_days'] ?? 0);
    $eg_hours   = intval($timer['evergreen_hours'] ?? 1);
    $eg_minutes = intval($timer['evergreen_minutes'] ?? 0);
    $eg_duration = ($eg_days * 86400) + ($eg_hours * 3600) + ($eg_minutes * 60);

    // Build wrapper CSS classes (layout/style/effect classes from PHP, not JS)
    $display_style = $style['display_style'] ?? 'flip';
    $timer_layout  = $style['timer_layout'] ?? 'inline';
    $wrapper_classes = [
      'df_countdown_wrapper',
      'df_countdown_style_' . $display_style,
      'df_countdown_layout_' . $timer_layout,
      'df_countdown_type_' . $countdown_type,
    ];
    // Build JSON config — only values needed by JS for DOM/logic (not styling)
    $countdown_config = wp_json_encode([
      // Timer settings
      'countdownType'       => $countdown_type,
      'endTimestamp'        => $end_timestamp,
      'targetDate'          => str_replace(' ', 'T', $timer['target_date'] ?? '2026-12-31 23:59'),
      'targetTimezone'      => $target_tz,
      'evergreenDays'       => $timer['evergreen_days'] ?? '0',
      'evergreenHours'      => $timer['evergreen_hours'] ?? '1',
      'evergreenMinutes'    => $timer['evergreen_minutes'] ?? '0',
      'evergreenDuration'   => $eg_duration,
      // Pass through as-is (empty when the user hasn't set one) so the JS
      // can fall back to a module-unique default — otherwise multiple
      // evergreen timers on the same page share a cookie and tick in lockstep.
      'evergreenCookieId'   => $timer['evergreen_cookie_id'] ?? '',
      'recurringType'       => $timer['recurring_type'] ?? 'daily',
      'recurringTime'       => $timer['recurring_time'] ?? '23:59',
      'recurringWeekday'    => $timer['recurring_weekday'] ?? '1',
      'recurringMonth'      => $timer['recurring_month'] ?? '1',
      'recurringDay'        => $timer['recurring_day'] ?? '1',
      'showDays'            => $timer['show_days'] ?? 'on',
      'showHours'           => $timer['show_hours'] ?? 'on',
      'showMinutes'         => $timer['show_minutes'] ?? 'on',
      'showSeconds'         => $timer['show_seconds'] ?? 'on',

      // Label settings
      'labelPosition'       => $labels['label_position'] ?? 'below',
      'dayLabel'            => $labels['days_label'] ?? '',
      'hourLabel'           => $labels['hours_label'] ?? '',
      'minuteLabel'         => $labels['minutes_label'] ?? '',
      'secondLabel'         => $labels['seconds_label'] ?? '',

      // Expiration settings
      'expirationAction'    => $expiration['expiration_action'] ?? 'show_message',
      'expirationMessage'   => $expiration['expiration_message'] ?? 'Countdown finished!',
      'redirectUrl'         => $expiration['redirect_url'] ?? '',
      'redirectDelay'       => $expiration['redirect_delay'] ?? '0',
      'restartDelay'        => $expiration['restart_delay'] ?? '0',

      // Style settings (only values needed by JS for DOM structure/logic)
      'timerStyle'          => $display_style,
      'separatorCharacter'  => $style['separator_character'] ?? ':',
      // enable_separator (default "on") is the canonical control; legacy
      // hide_separator data is preserved for FE-side back-compat.
      'enableSeparator'     => $style['enable_separator'] ?? (
        ($style['hide_separator'] ?? 'off') === 'on' ? 'off' : 'on'
      ),

      // Flip animation timing (JS-controlled)
      'flipDuration'        => $style['flip_duration'] ?? '600',
      'splitFlipDuration'   => $style['split_flip_duration'] ?? '600',

      // Count Up — past-anchor instant; the FE computes elapsed time from it.
      // anchorDate + timezone passed alongside the resolved seconds so the FE
      // can parse the wall-clock locally when the timezone is "visitor"
      // (resolve_local_datetime returns 0 for visitor — same pattern as
      // Fixed Date's targetDate fallback).
      'countUpAnchor'       => $count_up_anchor_ts,
      'countUpAnchorDate'   => str_replace(' ', 'T', $timer['count_up_anchor_date'] ?? '2025-01-01 00:00'),
      'countUpTimezone'     => $timer['count_up_timezone'] ?? 'wp',

      // Circle/ring SVG attributes (must be JS — not CSS)
      'circleSize'          => $style['circle_size'] ?? '120',
      'circleStrokeWidth'   => $style['circle_stroke_width'] ?? '4',
      'circleTrackColor'    => $style['circle_track_color'] ?? '#e0e0e0',
      'circleProgressColor' => $style['circle_progress_color'] ?? '#4285F4',
      'circleFillColor'     => $style['circle_fill_color'] ?? '',
      'circleCap'           => $style['circle_cap'] ?? 'round',
      'circleLabelInside'   => $style['circle_label_inside'] ?? 'off',
      // Unified individual-unit-colors toggle now drives every per-style
      // override (ring color in Circle, card bg+text in Flip, etc.) — JS
      // reads enableIndividualColors plus the per-style/per-unit field.
      'enableIndividualColors' => $style['enable_individual_colors'] ?? 'off',
      // Per-unit ring color defaults match the Material palette in module.json's
      // default_value; falling back to '' would leak to circleProgressColor and
      // hide the distinct ring colors users expect with the toggle on.
      'daysRingColor'       => $style['days_ring_color']    ?? '#4285F4',
      'hoursRingColor'      => $style['hours_ring_color']   ?? '#34A853',
      'minutesRingColor'    => $style['minutes_ring_color'] ?? '#EA4335',
      'secondsRingColor'    => $style['seconds_ring_color'] ?? '#FBBC05',
      // Per-unit circle inner fill (SVG <circle fill> attribute, set by FE JS).
      // Empty default — falls back to the global circleFillColor.
      'daysCircleFillColor'    => $style['days_circle_fill_color']    ?? '',
      'hoursCircleFillColor'   => $style['hours_circle_fill_color']   ?? '',
      'minutesCircleFillColor' => $style['minutes_circle_fill_color'] ?? '',
      'secondsCircleFillColor' => $style['seconds_circle_fill_color'] ?? '',

      // Tick animation (JS class toggling)
      'tickAnimation'       => $style['tick_animation'] ?? 'none',
      'tickTarget'          => $style['tick_target'] ?? 'all',
    ]);

    $countdown_html = sprintf(
      '<div class="%s" data-countdown-config=\'%s\'>
        <div class="df_countdown_display"></div>
        <div class="df_countdown_message"></div>
      </div>',
      esc_attr(implode(' ', $wrapper_classes)),
      esc_attr($countdown_config)
    );

    return Module::render(
      [
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],
        'id'                  => $block->parsed_block['id'],
        'name'                => $block->block_type->name,
        'moduleCategory'      => $block->block_type->category,
        'attrs'               => $attrs,
        'elements'            => $elements,
        'classnamesFunction'  => [self::class, 'classnames'],
        'scriptDataComponent' => [self::class, 'script_data'],
        'stylesComponent'     => [self::class, 'styles'],
        'parentAttrs'         => $parent_attrs,
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => ElementComponents::component(
          [
            'attrs'         => $attrs['module']['decoration'] ?? [],
            'id'            => $block->parsed_block['id'],
            'orderIndex'    => $block->parsed_block['orderIndex'],
            'storeInstance' => $block->parsed_block['storeInstance'],
          ]
        ) . $countdown_html,
      ]
    );
  }
}
