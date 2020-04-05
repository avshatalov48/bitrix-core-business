<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_SEPARATOR_NAME'),
			'section' => array('separator'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(),
	'style' =>
		array(
			'.landing-block-line' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_9_SEPARATOR_LINE'),
					'type' => array('border-color'),
				),
		),
);