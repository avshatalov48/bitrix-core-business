<?php
/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div class="ui-entity-card-container">
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:ui.form',
		'.default',
		[
			'GUID' => 'CATALOG_VARIATION_CARD',

			'ENTITY_TYPE_NAME' => 'VARIATION',
			'ENTITY_ID' => $arResult['VARIATION_FIELDS']['ID'],

			'ENTITY_FIELDS' => $arResult['UI_ENTITY_FIELDS'],
			'ENTITY_CONFIG' => $arResult['UI_ENTITY_CONFIG'],
			'ENTITY_DATA' => $arResult['UI_ENTITY_DATA'],
			'ENTITY_CONTROLLERS' => $arResult['UI_ENTITY_CONTROLLERS'],

			'ENABLE_CONFIGURATION_UPDATE' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_COMMON_CONFIGURATION_UPDATE' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'READ_ONLY' => $arResult['UI_ENTITY_READ_ONLY'],

			'ENABLE_BOTTOM_PANEL' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_FIELDS_CONTEXT_MENU' => !$arResult['UI_ENTITY_READ_ONLY'],
			'ENABLE_PAGE_TITLE_CONTROLS' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_SECTION_EDIT' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_SECTION_CREATION' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_SETTINGS_FOR_ALL' => $arResult['UI_ENTITY_ENABLE_SETTINGS_FOR_ALL'],

			'ENABLE_SECTION_DRAG_DROP' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],

			'ENABLE_CONFIG_CONTROL' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'ENABLE_CONFIG_SCOPE_TOGGLE' => $arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'],
			'COMPONENT_AJAX_DATA' => [
				'COMPONENT_NAME' => $component->getName(),
				'ACTION_NAME' => 'save',
				'SIGNED_PARAMETERS' => $component->getSignedParameters(),
			],
		],
		$component
	);
	?>
</div>
<div style="clear: both;"></div>