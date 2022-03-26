<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(\Bitrix\Landing\Manager::getDocRoot() . '/bitrix/modules/landing/blocks/.components.php');

\CBitrixComponent::includeComponentClass('bitrix:landing.blocks.cmpfilter');

$return = [
	'block' => [
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_LIST_STORE_V3_NAME2'),
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
		'bitrix:landing.blocks.cmpfilter' => [
			'type' => 'component',
			'extra' => [
				'editable' => [],
			],
		],
		'bitrix:catalog.section' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'SECTION_ID' => [
						'title' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_STORE_V3_SECTION_ID'),
						'type' => 'url',
						'entityType' => 'section',
						'disableCustomURL' => true,
						'disallowType' => true,
						'allowedTypes' => [
							'catalog',
						],
						'allowedCatalogEntityTypes' => [
							'section',
						],
					],
					'ALLOW_SEO_DATA' => [],
					'HIDE_NOT_AVAILABLE' => [],
					'HIDE_NOT_AVAILABLE_OFFERS' => [],
					// sort
					'ELEMENT_SORT_FIELD' => [],
					'ELEMENT_SORT_ORDER' => [],
					// price
					'CURRENCY_ID' => [],
					'PRICE_CODE' => [],
					'USE_PRICE_COUNT' => [],
					'SHOW_PRICE_COUNT' => [],
					'PRICE_VAT_INCLUDE' => [],
					// actions
					'DISPLAY_COMPARE' => [],
					'USE_PRODUCT_QUANTITY' => [],
					'SHOW_DISCOUNT_PERCENT' => [],
					'SHOW_OLD_PRICE' => [],
					'ADD_TO_BASKET_ACTION' => [],
					// texts
					'MESS_BTN_BUY' => [],
					'MESS_BTN_ADD_TO_BASKET' => [],
					'MESS_BTN_SUBSCRIBE' => [],
					'MESS_NOT_AVAILABLE' => [],
					'USE_ENHANCED_ECOMMERCE' => [],
					'DATA_LAYER_NAME' => [],
					'BRAND_PROPERTY' => [],
					// visual
					'LABEL_PROP_POSITION' => [
						'style' => true,
					],
					'DISCOUNT_PERCENT_POSITION' => [
						'style' => true,
					],
					'DEFERRED_LOAD' => [
						'hidden' => true
					],
					'CYCLIC_LOADING' => [
						'hidden' => true
					],
					'SECTIONS_OFFSET_MODE' => [
						'hidden' => true
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [],
	]
];

$params =& $return['nodes']['bitrix:catalog.section']['extra']['editable'];

// remove extended fields in simple mode
$extendedFields = \Bitrix\Landing\Hook\Page\Settings::getCodes(true);
//if (!isset($extended) || $extended !== true)
foreach ($params as $key => $item) {
	if (
		in_array($key, $extendedFields) &&
		!in_array($key, ['SECTION_ID'])
	) {
		$params[$key]['hidden'] = true;
	}
}

return $return;
