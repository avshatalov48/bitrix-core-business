<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(
	\Bitrix\Landing\Manager::getDocRoot() .
	'/bitrix/modules/landing/blocks/.components.php'
);

$return = array(
	'block' =>
		array(
			'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_DETAIL_NAME'),
			'section' => array('store'),
			'type' => 'store',
			'html' => false,
			'subtype' => 'component',
			'subtype_params' => array(
				'required' => 'catalog'
			)
		),
	'assets' => array(
		'css' => array(
			'/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/style.css'
		),
		'js' => array(
			'/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/script.js'
		),
		'ext' => array(
			'currency'
		)
	),
	'nodes' =>
		array(
			'bitrix:catalog.element' =>
				array(
					'type' => 'component',
					'extra' => array(
						'editable' => array(
							// filter
							'ELEMENT_ID' => array(
							),
							'HIDE_NOT_AVAILABLE' => array(
							),
							'HIDE_NOT_AVAILABLE_OFFERS' => array(
							),
							// price
							'PRICE_CODE' => array(
							),
							'USE_PRICE_COUNT' => array(
							),
							'SHOW_PRICE_COUNT' => array(
							),
							'PRICE_VAT_INCLUDE' => array(
							),
							// actions
							'USE_PRODUCT_QUANTITY' => array(
							),
							'PRODUCT_SUBSCRIPTION' => array(
							),
							'SHOW_DISCOUNT_PERCENT' => array(
							),
							'SHOW_OLD_PRICE' => array(
							),
							// texts
							'MESS_BTN_BUY' => array(
							),
							'MESS_BTN_ADD_TO_BASKET' => array(
							),
							'MESS_BTN_SUBSCRIBE' => array(
							),
							'MESS_NOT_AVAILABLE' => array(
							),
							'USE_ENHANCED_ECOMMERCE' => array(
							),
							'DATA_LAYER_NAME' => array(
							),
							'FB_APP_ID' => array(
								'name' => Loc::getMessage('LD_COMP_FB_APP_ID')
							),
							'VK_API_ID' => array(
								'name' => Loc::getMessage('LD_COMP_VK_API_ID')
							),
							// visual
							'PROPERTY_CODE' => array(
								'style' => true
							),
							'LABEL_PROP_POSITION' => array(
								'style' => true
							),
							'DISCOUNT_PERCENT_POSITION' => array(
								'style' => true
							),
							'PRODUCT_INFO_BLOCK_ORDER' => array(
								'style' => true
							)
						)
					)
				),
		),
);

$params =& $return['nodes']['bitrix:catalog.element']['extra']['editable'];

// vk only for ru
if (!in_array(Manager::getZone(), array('ru', 'by', 'kz')))
{
	unset($params['VK_API_ID']);
}

// remove extended fields in simple mode
$extendedFields = \Bitrix\Landing\Hook\Page\Settings::getCodes(true);
//if (!isset($extended) || $extended !== true)
foreach ($params as $key => $item)
{
	if (in_array($key, $extendedFields))
	{
		$params[$key]['hidden'] = true;
	}
}

return $return;