<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NAME'),
		'section' => array('countdowns', 'cover'),
		'version' => '18.5.0',
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--EVENT'),
			'additional' => array(
				'attrs' => array(
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--DATE'),
						'time' => true,
						'type' => 'date',
						'format' => 'ms',
						'attribute' => 'data-end-date',
					)
					//						"placeholder" => "2018/08/25 19:19:19",
					//			"value" => "default_value",
				),
			),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 588),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TEXT'),
			'type' => 'text',
		),
		
		'.landing-block-node-number-text-days' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-hours' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-minutes' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-seconds' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay'),
		),
		'nodes' => array(
			'.landing-block-node-title-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
				'type' => 'border-color',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-number-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_NUMBER'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-number-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_NUMBER'),
				'type' => array('border-color'),
			),
			'.landing-block-node-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--IMG'),
				'type' => array('background-attachment'),
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_countdown'),
	),
);