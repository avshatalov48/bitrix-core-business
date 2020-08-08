<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LNDNG_BLCK_35_9_HEADER_NAME'),
		'section' => array('menu'),
		'dynamic' => false,
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_9_HEADER_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-phone' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_9_HEADER_PHONE'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_9_HEADER_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-phone' => array(
			'name' => Loc::getMessage('LNDNG_BLCK_35_9_HEADER_PHONE'),
			'type' => ['typo-link'],
		),
	),
);