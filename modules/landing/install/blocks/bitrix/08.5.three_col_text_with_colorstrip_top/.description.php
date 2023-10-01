<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_08_5_NAME'),
		 'section' => array('columns', 'news'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_CARD'),
			'label' => array('.landing-block-node-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-info' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_INFO'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_CARD'),
			'type' => array('columns',  'margin-bottom', 'animation'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_TITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_INNER'),
			'type' => 'row-align',
		),
		'.landing-block-node-info' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_INFO'),
			'type' => 'typo',
		),
		'.landing-block-node-card-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_08_5_CARD'),
			'type' => array('border-color',  'border-top-color', 'border-radius'),
		),
	),
);