<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NAME'),
		'section' => array('countdowns'),
		'version' => '18.5.5',
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--TITLE'),
			'type' => 'text',
		),
		
		'.landing-block-node-number-text-days' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-hours' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-minutes' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-seconds' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default'),
		),
		'nodes' => array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-number-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_NUMBER'),
				'type' => array('color', 'font-family', 'border-color', 'border-width'),
			),
			'.landing-block-node-number-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--NUMBER_TEXT'),
				'type' => array('color', 'font-family'),
			),
		),
	),
	'attrs' => array(
		'.landing-block-node-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_7_COUNTDOWN_13--DATE'),
			'time' => true,
			'type' => 'date',
			'format' => 'ms',
			'attribute' => 'data-end-date',
		),
	),
	'assets' => array(
		'ext' => array('landing_countdown'),
	),
);