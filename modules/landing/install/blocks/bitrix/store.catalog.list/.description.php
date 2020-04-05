<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(
	\Bitrix\Landing\Manager::getDocRoot() .
	'/bitrix/modules/landing/blocks/.components.php'
);

\CBitrixComponent::includeComponentClass('bitrix:landing.blocks.cmpfilter');

$return = array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_LIST_NAME'),
		'section' => array('store'),
		'type' => 'store',
		'html' => false,
		'subtype' => 'component',
		'subtype_params' => array(
			'required' => 'catalog',
		),
		'namespace' => 'bitrix',
	),
	'nodes' => array(
		'bitrix:landing.blocks.cmpfilter' => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(/*'FILTER' => array(
							'name' => Loc::getMessage('LD_COMP_FILTER'),
							'type' => 'filter',
							'fields' => LandingUtilsCmpFilterComponent::getFilterFields()
						),*/
				),
			),
		),
		'bitrix:catalog.section' => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(
					'SECTION_ID' => array(
						'title' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_SECTION_ID'),
						'type' => 'url',
						'entityType' => 'section',
						'disableCustomURL' => true,
						'disallowType' => true,
						'allowedTypes' => array(
							'catalog',
						),
						'allowedCatalogEntityTypes' => array(
							'section',
						),
					),
					'ALLOW_SEO_DATA' => array(),
					'HIDE_NOT_AVAILABLE' => array(),
					'HIDE_NOT_AVAILABLE_OFFERS' => array(),
					// sort
					'ELEMENT_SORT_FIELD' => array(),
					'ELEMENT_SORT_ORDER' => array(),
					// price
					'CURRENCY_ID' => array(),
					'PRICE_CODE' => array(),
					'USE_PRICE_COUNT' => array(),
					'SHOW_PRICE_COUNT' => array(),
					'PRICE_VAT_INCLUDE' => array(),
					// actions
					'DISPLAY_COMPARE' => array(),
					'USE_PRODUCT_QUANTITY' => array(),
					'PRODUCT_SUBSCRIPTION' => array(),
					'SHOW_DISCOUNT_PERCENT' => array(),
					'SHOW_OLD_PRICE' => array(),
					'ADD_TO_BASKET_ACTION' => array(),
					// texts
					'MESS_BTN_BUY' => array(),
					'MESS_BTN_ADD_TO_BASKET' => array(),
					'MESS_BTN_SUBSCRIBE' => array(),
					'MESS_NOT_AVAILABLE' => array(),
					'USE_ENHANCED_ECOMMERCE' => array(),
					'DATA_LAYER_NAME' => array(),
					'BRAND_PROPERTY' => array(),
					// visual
					'PRODUCT_ROW_VARIANTS' => array(
						'style' => true,
					),
					/*'PROPERTY_CODE' => array(
						'style' => true
					),*/
					'LABEL_PROP_POSITION' => array(
						'style' => true,
					),
					'DISCOUNT_PERCENT_POSITION' => array(
						'style' => true,
					),
					'PRODUCT_BLOCKS_ORDER' => array(
						'style' => true,
					),
				),
			),
		),
	),
);

$params =& $return['nodes']['bitrix:catalog.section']['extra']['editable'];

// remove extended fields in simple mode
$extendedFields = \Bitrix\Landing\Hook\Page\Settings::getCodes(true);
//if (!isset($extended) || $extended !== true)
foreach ($params as $key => $item)
{
	if (
		in_array($key, $extendedFields) &&
		!in_array($key, ['SECTION_ID'])
	)
	{
		$params[$key]['hidden'] = true;
	}
}

return $return;
