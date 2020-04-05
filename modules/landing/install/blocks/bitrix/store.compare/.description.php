<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$return = array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_STORE.COMPARE_NAME'),
		'section' => array('store'),
		'type' => 'null',
		'html' => false,
		'namespace' => 'bitrix',
	),
	'nodes' => array(
		'bitrix:catalog.compare.result' => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(
					'PRICE_CODE' => array(),
					'USE_PRICE_COUNT' => array(),
					'SHOW_PRICE_COUNT' => array(),
					'PRICE_VAT_INCLUDE' => array(),
				),
			),
		),
	),
);

$params =& $return['nodes']['bitrix:catalog.compare.result']['extra']['editable'];

// remove extended fields in simple mode
$extendedFields = \Bitrix\Landing\Hook\Page\Settings::getCodes(true);
if (!isset($extended) || $extended !== true) //@todo make extended version
{
	foreach ($params as $key => $item)
	{
		if (in_array($key, $extendedFields))
		{
			unset($params[$key]);
		}
	}
}

return $return;