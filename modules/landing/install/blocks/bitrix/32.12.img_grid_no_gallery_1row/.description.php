<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32_12-NAME'),
		'section' => array('image'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-IMG'),
			'label' => ['.landing-block-node-img'],
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1110),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-TITLE'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-IMG'),
			'type' => ['columns', 'margin-bottom', 'animation'],
		),
		'.landing-block-node-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-IMG'),
			'type' => ['border-radius'],
		),
		'.landing-block-node-row' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-ROW'),
			'type' => 'row-align',
		),
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_12-TITLE'),
			'type' => 'typo',
		),
	),
);