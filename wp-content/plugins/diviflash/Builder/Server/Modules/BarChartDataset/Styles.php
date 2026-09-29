<?php

namespace DIFL\Server\Modules\BarChartDataset;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;

trait Styles
{
	public static function styles($args)
	{
		$attrs        = $args['attrs'] ?? [];
		$elements     = $args['elements'];
		$settings     = $args['settings'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),
				],
			]
		);
	}
}
