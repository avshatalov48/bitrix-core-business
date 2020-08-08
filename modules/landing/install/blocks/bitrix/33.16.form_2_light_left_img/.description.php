<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16'),
		'section' => array('forms'),
		'dynamic' => false,
		'subtype' => 'form',
	),
	'cards' => array(),
	'groups' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16_NODE_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 570),
		),
	),
	'style' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16_NODE_IMG'),
			'type' => 'background-size',
		),
	),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);