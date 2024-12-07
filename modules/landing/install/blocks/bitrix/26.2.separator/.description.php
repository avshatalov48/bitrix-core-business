<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_26_2_SEPARATOR_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['separator', 'widgets_separators'],
	),
	'cards' => array(),
	'nodes' => array(),
	'style' => array(
		'block' => [
			'type' => ['display', 'fill-first', 'fill-second', 'height-increased--md'],
		],
		'nodes' => [
		],
	),
);