<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--NAME'),
		'section' => array('countdowns'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'type' => ['page', 'store', 'smn'],
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--TITLE'),
			'type' => 'text',
		),
		
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--NUMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-number-number' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--NUMBER_NUMBER'),
			'type' => array('color', 'font-family'),
		),
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--NUMBER_TEXT'),
			'type' => array('color', 'font-family'),
		),
		'.landing-block-node-number' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--NUMBER_NUMBER'),
			'type' => array('bg', 'border-color'),
		),
	),
	'attrs' => array(
		'.landing-block-node-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08_WO_BG--DATE'),
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