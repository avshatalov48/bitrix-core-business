<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NAME'),
		'section' => array('countdowns'),
		'version' => '18.5.0',
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--TITLE'),
			'type' => 'text',
		),
		
		'.landing-block-node-number-text-days' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-hours' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-minutes' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text-seconds' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-number-number' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_NUMBER'),
			'type' => array('color', 'font-family'),
		),
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_TEXT'),
			'type' => array('color', 'font-family'),
		),
		'.landing-block-node-number-delimiter' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--NUMBER_DELIMETER'),
			'type' => array('color'),
		),
	),
	'attrs' => array(
		'.landing-block-node-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_1_COUNTDOWN_01--DATE'),
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