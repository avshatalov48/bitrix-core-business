<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Main\Localization\Loc;
use Bitrix\Landing\Manager;

Loc::loadMessages(
	\Bitrix\Landing\Manager::getDocRoot() .
	'/bitrix/modules/landing/blocks/.components.php'
);

$return = [
	'block' => [
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_DETAIL_STORE_V3_NAME'),
		'section' => ['store'],
		'type' => 'store',
		'html' => false,
		'subtype' => 'component',
		'subtype_params' => [
			'required' => 'catalog',
		],
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:catalog.element' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					// filter
					'ELEMENT_ID' => [
						'type' => 'url',
						'entityType' => 'element',
						'disableCustomURL' => true,
						'disallowType' => true,
						'allowedTypes' => [
							'catalog',
						],
						'allowedCatalogEntityTypes' => [
							'element',
						],
					],
					'ALLOW_SEO_DATA' => [],
					'HIDE_NOT_AVAILABLE' => [],
					'HIDE_NOT_AVAILABLE_OFFERS' => [],
					// price
					'PRICE_CODE' => [],
					'USE_PRICE_COUNT' => [],
					'SHOW_PRICE_COUNT' => [],
					'PRICE_VAT_INCLUDE' => [],
					// actions
					'USE_PRODUCT_QUANTITY' => [],
					'SHOW_DISCOUNT_PERCENT' => [],
					'SHOW_OLD_PRICE' => [],
					'ADD_TO_BASKET_ACTION' => [],
					'ADD_TO_BASKET_ACTION_PRIMARY' => [],
					// texts
					'MESS_BTN_BUY' => [],
					'MESS_BTN_ADD_TO_BASKET' => [],
					'MESS_BTN_SUBSCRIBE' => [],
					'MESS_NOT_AVAILABLE' => [],
					'USE_ENHANCED_ECOMMERCE' => [],
					'DATA_LAYER_NAME' => [],
					'FB_APP_ID' => [
						'name' => Loc::getMessage('LD_COMP_FB_APP_ID'),
					],
					'VK_API_ID' => [
						'name' => Loc::getMessage('LD_COMP_VK_API_ID'),
					],
					// visual
					'PROPERTY_CODE' => [
						'style' => true,
					],
					'LABEL_PROP_POSITION' => [
						'style' => true,
					],
					'DISCOUNT_PERCENT_POSITION' => [
						'style' => true,
					],
					'PRODUCT_INFO_BLOCK_ORDER' => [
						'style' => true,
					],
				],
			],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_DETAIL_STORE_V3_TEXT_1'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [],
	]
];

$params =& $return['nodes']['bitrix:catalog.element']['extra']['editable'];

// vk only for ru
if (!Manager::availableOnlyForZone('ru'))
{
	unset($params['VK_API_ID']);
}

// remove extended fields in simple mode
$extendedFields = Settings::getCodes(true);
foreach ($params as $key => $item)
{
	if (in_array($key, $extendedFields, true))
	{
		$params[$key]['hidden'] = true;
	}
}

return $return;