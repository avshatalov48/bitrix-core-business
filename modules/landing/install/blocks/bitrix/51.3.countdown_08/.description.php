<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--NAME'),
		'section' => array('countdowns'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'type' => ['page', 'store', 'smn'],
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--IMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
			'isWrapper' => true,
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--NUMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['block-default-background'],
		),
		'nodes' => array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-number-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--NUMBER_NUMBER'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-number-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--NUMBER_TEXT'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--NUMBER_NUMBER'),
				'type' => array('bg', 'border-color'),
			),
		),
	),
	'attrs' => array(
		'.landing-block-node-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_3_COUNTDOWN_08--DATW'),
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