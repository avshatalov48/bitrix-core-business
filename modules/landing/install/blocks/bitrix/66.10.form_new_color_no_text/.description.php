<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDNGBLCK_66_10_NAME'),
		// 'section' => array('forms'),
		'dynamic' => false,
		'subtype' => 'embedform',
	],
	// 'nodes' => [],
	// 'style' => [
	// 	'block' => [
	// 		'type' => ['block-default', 'block-border'],
	// 	],
	// 	'nodes' => [],
	// ],
	'assets' => [
		'ext' => ['landing_form_embed'],
	],
];