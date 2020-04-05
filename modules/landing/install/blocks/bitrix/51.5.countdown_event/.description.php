<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--NAME'),
		'section' => array('countdowns', 'cover'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'type' => ['page', 'store', 'smn'],
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
			'type' => 'text',
		),
		
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--NUMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-date-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-date-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-date-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--ICON'),
			'type' => 'icon',
		),
		
		'.landing-block-node-place-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-place-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-place-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--ICON'),
			'type' => 'icon',
		),
		
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--BUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay'),
		),
		'nodes' => array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-number-number' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--NUMBER_NUMBER'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-number-border' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--NUMBER_NUMBER'),
				'type' => array('border-color'),
			),
			'.landing-block-node-number-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--NUMBER_TEXT'),
				'type' => array('color', 'font-family'),
			),
			'.landing-block-node-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--ICON'),
				'type' => 'color',
			),
			
			'.landing-block-node-date-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-date-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TEXT'),
				'type' => 'typo',
			),
			
			'.landing-block-node-place-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-place-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--TEXT'),
				'type' => 'typo',
			),
			
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--BUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--IMG'),
				'type' => 'background-attachment',
			),
		),
	),
	'attrs' => array(
		'.landing-block-node-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_51_5_COUNTDOWN_EVENT--DATE'),
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