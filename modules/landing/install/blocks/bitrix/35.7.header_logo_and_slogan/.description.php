<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_35_7_HEADER--NAME'),
		'section' => array('menu'),
		'dynamic' => false,
	),
	'nodes' => array(
		'.landing-block-node-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_7_HEADER--LOGO'),
			'type' => 'img',
			'dimensions' => array('maxWidth' => 304, 'maxHeight' => 304)
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_7_HEADER--SLOGAN'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_7_HEADER--SLOGAN'),
			'type' => ['typo'],
		),
		'.landing-block-node-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_7_HEADER--CONTAINER'),
			'type' => ['text-align', 'container-max-width'],
		),
	),
);