<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(
	\Bitrix\Landing\Manager::getDocRoot() .
	'/bitrix/modules/landing/blocks/.attrs.php'
);

return [
	'attrs' => [
		'autoplay' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_AUTOPLAY'),
			'attribute' => 'data-slider-autoplay',
			'type' => 'list',
			'items' => [
				[
					'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_OFF'),
					'value' => 0,
					'hide' => [
						'autoplay-speed',
						'pause-hover',
					],
				],
				[
					'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_ON'),
					'value' => 1,
					'show' => [
						'autoplay-speed',
						'pause-hover',
					]
				],
			],
		],
		'autoplay-speed' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_AUTOPLAY_SPEED'),
			'attribute' => 'data-slider-autoplay-speed',
			'type' => 'slider',
			'items' => [
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_AUTO'), 'value' => '3001'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_1'), 'value' => '1000'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_3'), 'value' => '3000'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_5'), 'value' => '5000'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_10'), 'value' => '10000'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_15'), 'value' => '15000'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_TIME_30'), 'value' => '30000'],
			],
		],
		'pause-hover' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_PAUSE_HOVER'),
			'attribute' => 'data-slider-pause-hover',
			'type' => 'list',
			'items' => [
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_NO'), 'value' => false],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_YES'), 'value' => true],
			],
		],
		'slides-show' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_SLIDES_SHOW'),
			'attribute' => 'data-slider-slides-show',
			'type' => 'list',
			'items' => [
				['name' => '1', 'value' => '1'],
				['name' => '2', 'value' => '2'],
				['name' => '3', 'value' => '3'],
			],
			'dependency' => [
				[
					'attribute' => 'data-slider-animation',
					'action' => 'changeValue',
					'conditions' => ['2', '3'],
					'attributeCurrentValues' => ['3'],
					'attributeNewValue' => '1',
				],
			],
			'hint' => Loc::getMessage('LANDING_BLOCK_ATTRS_SLIDES_SHOW_HINT'),
		],
		'slides-show-extended' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_SLIDES_SHOW'),
			'attribute' => 'data-slider-slides-show',
			'type' => 'list',
			'items' => [
				['name' => '1', 'value' => '1'],
				['name' => '2', 'value' => '2'],
				['name' => '3', 'value' => '3'],
				['name' => '4', 'value' => '4'],
				['name' => '5', 'value' => '5'],
				['name' => '6', 'value' => '6'],
			],
			'dependency' => [
				[
					'attribute' => 'data-slider-animation',
					'action' => 'changeValue',
					'conditions' => ['2', '3', '4', '5', '6'],
					'attributeCurrentValues' => ['3'],
					'attributeNewValue' => '1',
				],
			],
			'hint' => Loc::getMessage('LANDING_BLOCK_ATTRS_SLIDES_SHOW_HINT'),
		],
		'animation' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_ANIMATION'),
			'attribute' => 'data-slider-animation',
			'type' => 'list',
			'items' => [
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_ANIMATION_OFF'), 'value' => '0', 'show' => ['slides-show']],
				['name' => 'Slide', 'value' => '1', 'show' => ['slides-show']],
				['name' => 'Advanced slide', 'value' => '2', 'show' => ['slides-show']],
				['name' => 'Fade in', 'value' => '3', 'hide' => ['slides-show']],
			],
			'dependency' => [
				[
					'attribute' => 'data-slider-slides-show',
					'action' => 'changeValue',
					'conditions' => ['3'],
					'attributeCurrentValues' => ['2', '3', '4', '5', '6'],
					'attributeNewValue' => '1',
				],
			],
			'hint' => Loc::getMessage('LANDING_BLOCK_ATTRS_ANIMATION_HINT'),
		],
		'arrows' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_ARROWS'),
			'attribute' => 'data-slider-arrows',
			'type' => 'slider',
			'items' => [
				['name' => '0', 'value' => '0'],
				['name' => '1', 'value' => '1'],
				['name' => '2', 'value' => '2'],
				['name' => '3', 'value' => '3'],
				['name' => '4', 'value' => '4'],
				['name' => '5', 'value' => '5'],
				['name' => '6', 'value' => '6'],
				['name' => '7', 'value' => '7'],
			],
		],
		'dots' => [
			'name' => Loc::getMessage('LANDING_BLOCK_ATTRS_DOTS'),
			'attribute' => 'data-slider-dots',
			'type' => 'list',
			'items' => [
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_YES'), 'value' => '1'],
				['name' => Loc::getMessage('LANDING_BLOCK_ATTRS_NO'), 'value' => '0'],
			],
		],
	]
];