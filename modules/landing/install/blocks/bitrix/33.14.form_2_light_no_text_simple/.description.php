<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.14'),
			'section' => array('forms'),
			'subtype' => 'form',
		),
	'nodes' => array(
	),
	'style' => array(
	),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);