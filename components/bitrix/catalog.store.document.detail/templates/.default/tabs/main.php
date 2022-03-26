<?php

/**
 * @var $component \CatalogProductDetailsComponent
 * @var $arResult array
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;
?>
<div class="catalog-document-card-wrapper">
	<div class="catalog-document-card-left">

	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:ui.form',
		'.default',
		[
			'GUID' => $arResult['FORM']['GUID'],

			'ENTITY_ID' => $arResult['FORM']['ENTITY_ID'],
			'ENTITY_TYPE_NAME' => $arResult['FORM']['ENTITY_TYPE_NAME'],

			'ENTITY_FIELDS' => $arResult['FORM']['ENTITY_FIELDS'],
			'ENTITY_CONFIG' => $arResult['FORM']['ENTITY_CONFIG'],
			'ENTITY_DATA' => $arResult['FORM']['ENTITY_DATA'],
			'ENTITY_CONTROLLERS' => $arResult['FORM']['ENTITY_CONTROLLERS'],

			'READ_ONLY' => $arResult['FORM']['READ_ONLY'],

			'ENABLE_COMMON_CONFIGURATION_UPDATE' => true,
			'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => true,
			'ENABLE_SETTINGS_FOR_ALL' => true,
			'ENABLE_PAGE_TITLE_CONTROLS' => true,

			'ENABLE_SECTION_EDIT' => true,
			'ENABLE_SECTION_DRAG_DROP' => true,

			'IS_TOOL_PANEL_ALWAYS_VISIBLE' => true,
			'ENABLE_BOTTOM_PANEL' => true,
			'ENABLE_CONFIG_CONTROL' => true,

			'SERVICE_URL' => '/bitrix/components/bitrix/catalog.store.document.detail/ajax.php?'.bitrix_sessid_get(),
			'COMPONENT_AJAX_DATA' => [
				'COMPONENT_NAME' => $component->getName(),
				'ACTION_NAME' => 'save',
				'SIGNED_PARAMETERS' => $component->getSignedParameters(),

				'ADDITIONAL_ACTIONS' => $arResult['FORM']['ADDITIONAL_ACTIONS'],
			],

			'CUSTOM_TOOL_PANEL_BUTTONS' => $arResult['FORM']['CUSTOM_TOOL_PANEL_BUTTONS'],
			'TOOL_PANEL_BUTTONS_ORDER' => $arResult['FORM']['TOOL_PANEL_BUTTONS_ORDER'],
		],
		$component
	);
	?>
	</div>
	<div class="catalog-document-card-right">
	<?php
		if (isset($arResult['RIGHT_COLUMN']))
		{
			$APPLICATION->IncludeComponent(
				$arResult['RIGHT_COLUMN']['COMPONENT_NAME'],
				$arResult['RIGHT_COLUMN']['COMPONENT_TEMPLATE'],
				$arResult['RIGHT_COLUMN']['COMPONENT_PARAMS'],
				$component
			);
		}
	?>
	</div>
</div>