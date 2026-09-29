<?php

namespace DIFL\Server\Traits;

use ET\Builder\Packages\GlobalData\GlobalPreset;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

/**
 * Trait for applying preset values to STYLE attr groups only.
 *
 * Sister trait to ProcessAttrWithPreset, intended for DiviFlash modules whose
 * preset values would otherwise leak into content fields. The original trait
 * applies the preset across every attr group; this one takes an explicit list
 * of style attr groups and leaves everything else untouched.
 *
 * Why this exists:
 * - DiviFlash custom innerContent fields aren't categorized into preset.renderAttrs
 *   at save time (Divi's get_preset_attrs_from_group returns [] for primitive
 *   components like divi/select, divi/range, divi/color-picker), so the values
 *   only end up in preset.attrs.
 * - Divi's render_callback wrapper auto-merges only preset.renderAttrs into the
 *   $attrs argument it passes downstream, so values stuck in preset.attrs are
 *   invisible to your render path until you merge them yourself.
 * - That merge needs to be scoped to STYLE groups — presets in DiviFlash are
 *   styles only and must not replace content (labels, target dates, expiration
 *   messages, source URLs, etc.).
 *
 * See docs/MODULE_PRESETS.md for the full design rationale and patch recipe.
 */
trait ProcessStylePreset
{
    /**
     * Apply preset values to a whitelist of style attr groups.
     *
     * Merge layering inside each whitelisted group (lowest → highest priority):
     *
     *   $attrs[$group]                – Divi's pre-merge for the group
     *                                   (defaults + preset.renderAttrs + user_attrs).
     *                                   Used as the lower bound so a field the
     *                                   preset doesn't have keeps the default.
     *   $preset_only[$group]          – preset.attrs (full bucket) for the group.
     *                                   Wins over Divi defaults pre-filled in $attrs.
     *   $user_raw_attrs[$group]       – user's truly-saved overrides from
     *                                   $block->parsed_block['attrs']. Wins last
     *                                   so panel edits override the preset.
     *
     * Attr groups outside the whitelist are returned unchanged (i.e. exactly
     * as Divi delivered them), so content defaults and user-entered content
     * are both preserved.
     *
     * @param array         $attrs              Module attributes (the post-merge
     *                                          version Divi passed to render_callback).
     * @param string        $module_slug        Module slug, e.g. 'difl/countdowntimer'.
     * @param array         $style_attr_groups  Whitelist of top-level attr keys on
     *                                          which to apply the preset (e.g.
     *                                          ['module', 'style_settings', 'unit']).
     *                                          An empty array is a no-op merge.
     * @param array|null    $user_raw_attrs     Optional. The user's truly-saved
     *                                          overrides, typically
     *                                          $block->parsed_block['attrs'].
     *                                          Required for render_callback if you
     *                                          want panel edits to win over the
     *                                          preset; safe to omit in Styles.php
     *                                          (Divi's preset CSS path covers user
     *                                          overrides on the CSS side).
     * @return array Merged attributes.
     */
    public static function processStylePreset($attrs, $module_slug, array $style_attr_groups, $user_raw_attrs = null)
    {
        try {
            if (empty($attrs['modulePreset']) || empty($style_attr_groups)) {
                return $attrs;
            }

            // presetOnly=true returns the preset+groupPreset attrs WITHOUT $attrs
            // (which carries Divi's pre-filled defaults) mixed in. We control the
            // priority order ourselves below.
            $preset_only = GlobalPreset::get_merged_attrs([
                'moduleName'  => $module_slug,
                'moduleAttrs' => $attrs,
                'presetOnly'  => true,
            ]);

            if (empty($preset_only) || !is_array($preset_only)) {
                return $attrs;
            }

            $user_raw_attrs = is_array($user_raw_attrs) ? $user_raw_attrs : [];

            $result = $attrs;
            foreach ($style_attr_groups as $group) {
                $preset_group = $preset_only[$group]    ?? null;
                $user_group   = $user_raw_attrs[$group] ?? null;

                // Nothing to do if neither preset nor user has anything for this
                // group — the Divi-delivered default is already in $result.
                if ($preset_group === null && $user_group === null) {
                    continue;
                }

                $merged = is_array($attrs[$group] ?? null) ? $attrs[$group] : [];
                if (is_array($preset_group)) {
                    $merged = array_replace_recursive($merged, $preset_group);
                }
                if (is_array($user_group)) {
                    $merged = array_replace_recursive($merged, $user_group);
                }
                $result[$group] = $merged;
            }
            return $result;

        } catch (\Exception $e) {
            return $attrs;
        }
    }
}
