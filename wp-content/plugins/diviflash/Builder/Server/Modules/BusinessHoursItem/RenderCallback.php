<?php
namespace DIFL\Server\Modules\BusinessHoursItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback {
	use Classnames;
	use Styles;

	public static function render_callback( $attrs, $content, $block, $elements ) {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		$parent_attrs = $parent->attrs ?? [];
    
		// Title.
		$day_name = $elements->render(
			[
				'attrName'   => 'day_name',
				'attributes' => [
					'class' => 'df_bh_day',
				],
			]
		);

		$dayTimeSepHas = isset($attrs['on_separator_day_time']['innerContent']['desktop']['value']) ? $attrs['on_separator_day_time']['innerContent']['desktop']['value'] : 'off';
		$timeStructure = isset($attrs['time_structure_type']['innerContent']['desktop']['value']) ? $attrs['time_structure_type']['innerContent']['desktop']['value'] : '';
		$off_day_enable = $attrs['off_day_enable']['innerContent']['desktop']['value'] ?? 'off';
		$timeTextDf = isset($attrs['time']['innerContent']['desktop']['value']) ? $attrs['time']['innerContent']['desktop']['value'] : '';
		$startTimeDf = isset($attrs['start_time']['innerContent']['desktop']['value']) ? $attrs['start_time']['innerContent']['desktop']['value'] : '';
		$endTimeDf = isset($attrs['end_time']['innerContent']['desktop']['value']) ? $attrs['end_time']['innerContent']['desktop']['value'] : '';
		$timeSepDf = isset($attrs['time_separetor']['innerContent']['desktop']['value']) ? $attrs['time_separetor']['innerContent']['desktop']['value'] : '';
		$offDayText = isset($attrs['off_day_text']['innerContent']['desktop']['value']) ? $attrs['off_day_text']['innerContent']['desktop']['value'] : '';

		$timeText = $timeTextDf ? '<span class="df_bh_time_text">' . esc_html($timeTextDf) . '</span>' : '';
		$startTime = $startTimeDf ? '<span class="df_bh_start_time">' . esc_html($startTimeDf) . '</span>' : '';
		$endTime = $endTimeDf ? '<span class="df_bh_end_time">' . esc_html($endTimeDf) . '</span>' : '';
		$separatorTime = $timeSepDf ? '<span class="df_bh_time_separetor">' . esc_html($timeSepDf) . '</span>' : '';
		$offDay = ('on' === $off_day_enable && $offDayText) ? '<span class="df_bh_off_day">' . esc_html($offDayText) . '</span>' : '';

		$timeHtml = ('advanced' === $timeStructure) ? '<div class="df_bh_time">' . $startTime . $separatorTime . $endTime . '</div>' : '<div class="df_bh_time">' . $timeText . '</div>';

		$timeContainnerHtml = ('on' !== $off_day_enable) ? $timeHtml : '<div class="df_bh_time">' . $offDay . '</div>';

    $day_tiem_separator_on = ( $dayTimeSepHas === 'on' ) ? 'day_tiem_separator_on' : '';
    $day_tiem_separator = ( $dayTimeSepHas === 'on' ) ? '<div class="df_bh_day_time_separator"><hr></div>' : '';
    $offDayClass = $off_day_enable === 'on' ? 'off_day_true' : '';
    
		$difl_bh_item = '
			<div class="'.$day_tiem_separator_on.' df_bh_item '.$offDayClass.'">
				'.$day_name.'
				'.$day_tiem_separator.'
				'.$timeContainnerHtml.'
			</div>
		';

		return Module::render(
			[
				// FE only.
				'orderIndex'         => $block->parsed_block['orderIndex'],
				'storeInstance'      => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                 => $block->parsed_block['id'],
				'name'               => $block->block_type->name,
				'moduleCategory'     => $block->block_type->category,
				'attrs'              => $attrs,
				'elements'           => $elements,
				'classnamesFunction'  => [ self::class, 'classnames' ],
				'stylesComponent'    => [ self::class, 'styles' ],
				'parentAttrs'        => $parent_attrs,
				'parentId'           => $parent->id ?? '',
				'parentName'         => $parent->blockName ?? '',
				'children'           => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) .$difl_bh_item,
			]
		);
	}
}