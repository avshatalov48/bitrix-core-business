<?php
/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.form',
	'.default',
	[
		'GUID' => 'CATALOG_PRODUCT_CARD',

		'ENTITY_TYPE_NAME' => 'PRODUCT',
		'ENTITY_ID' => $arResult['PRODUCT_FIELDS']['ID'],

		'ENTITY_FIELDS' => $arResult['UI_ENTITY_FIELDS'],
		'ENTITY_CONFIG' => $arResult['UI_ENTITY_CONFIG'],
		'ENTITY_DATA' => $arResult['UI_ENTITY_DATA'],
		'ENTITY_CONTROLLERS' => $arResult['UI_ENTITY_CONTROLLERS'],

		'ENABLE_COMMON_CONFIGURATION_UPDATE' => true,
		'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => true,
		'ENABLE_SETTINGS_FOR_ALL' => true,

		'ENABLE_SECTION_EDIT' => true,
		'ENABLE_SECTION_CREATION' => true,
		'ENABLE_SECTION_DRAG_DROP' => true,

		'ENABLE_BOTTOM_PANEL' => true,
		'ENABLE_CONFIG_CONTROL' => true,

		'COMPONENT_AJAX_DATA' => [
			'COMPONENT_NAME' => $component->getName(),
			'ACTION_NAME' => 'save',
			'SIGNED_PARAMETERS' => $component->getSignedParameters(),
		],
	],
	$component
);
?>
<div style="clear: both;"></div>