<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LNDNG_BLCK_35_10_HEADER_NAME'),
		'section' => array('menu'),
		'dynamic' => false,
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_10_HEADER_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_10_HEADER_SLOGAN'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_10_HEADER_TITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_10_HEADER_SLOGAN'),
			'type' => 'typo',
		),
	),
);