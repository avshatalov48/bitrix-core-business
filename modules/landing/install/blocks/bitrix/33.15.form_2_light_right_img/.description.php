<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.15'),
			'section' => array('forms'),
			'subtype' => 'form',
		),
	'cards' => array(),
	'groups' => array(),
	'nodes' => array(
		'.landing-block-node-img' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.15_NODE_IMG'),
				'type' => 'img',
				'dimensions' => array('width' => 570, 'height' => 510),
			),
	),
	'style' => array(),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);