<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_35_8_HEADER--NAME'),
		'section' => array('menu'),
		'dynamic' => false,
	),
	'nodes' => array(
		'.landing-block-node-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_8_HEADER--LOGO'),
			'type' => 'img',
			'dimensions' => array('maxWidth' => 180, 'maxHeight' => 60),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_8_HEADER--SLOGAN'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-row' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_8_HEADER--ELEMENTS'),
			'type' => ['align-items'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_8_HEADER--SLOGAN'),
			'type' => ['typo-link'],
		),
	),
);