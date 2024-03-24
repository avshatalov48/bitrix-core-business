<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$return = array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_STORE.SHOP_CART_NAME'),
		'section' => array('store'),
		'type' => 'null',
		'html' => false,
		'namespace' => 'bitrix'
	),
	'nodes' => array(
		"bitrix:sale.basket.basket" => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(
					// visual
					'HIDE_COUPON' => array(
					),
					'SHOW_FILTER' => array(
						'style' => true,
					),
					'TOTAL_BLOCK_DISPLAY' => array(
						'style' => true,
					),
				),
			),
		),
	),
);


$params =& $return['nodes']['bitrix:sale.basket.basket']['extra']['editable'];

// remove extended fields in simple mode
$extendedFields = \Bitrix\Landing\Hook\Page\Settings::getCodes(true);
foreach ($params as $key => $item)
{
	if (in_array($key, $extendedFields))
	{
		$params[$key]['hidden'] = true;
	}
}

return $return;