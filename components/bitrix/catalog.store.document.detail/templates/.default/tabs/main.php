<?php

/**
 * @var $component \CatalogProductDetailsComponent
 * @var $arResult array
 */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;
?>
<div class="catalog-document-card-wrapper">
	<div class="catalog-document-card-left">
		<?php
		$editorParams = [
			'GUID' => $arResult['FORM']['GUID'],
			'ENTITY_ID' => $arResult['FORM']['ENTITY_ID'],
			'ENTITY_TYPE_NAME' => $arResult['FORM']['ENTITY_TYPE_NAME'],
			'ENTITY_TYPE_TITLE' => Loc::getMessage('DOC_TYPE_SHORT_' . $arResult['DOCUMENT_TYPE']),
			'ENTITY_FIELDS' => $arResult['FORM']['ENTITY_FIELDS'],
			'ENTITY_CONFIG' => $arResult['FORM']['ENTITY_CONFIG'],
			'ENTITY_DATA' => $arResult['FORM']['ENTITY_DATA'],
			'ENTITY_CONTROLLERS' => $arResult['FORM']['ENTITY_CONTROLLERS'],
			'READ_ONLY' => $arResult['FORM']['READ_ONLY'],
			'ENABLE_COMMON_CONFIGURATION_UPDATE' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_SETTINGS_FOR_ALL' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_PAGE_TITLE_CONTROLS' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_SECTION_EDIT' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_SECTION_DRAG_DROP' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'IS_TOOL_PANEL_ALWAYS_VISIBLE' => true,
			'ENABLE_BOTTOM_PANEL' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_CONFIG_CONTROL' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'ENABLE_CONFIG_SCOPE_TOGGLE' => $arResult['FORM']['ENTITY_CONFIG_EDITABLE'],
			'SERVICE_URL' => '/bitrix/components/bitrix/catalog.store.document.detail/ajax.php?'.bitrix_sessid_get(),
			'COMPONENT_AJAX_DATA' => [
				'COMPONENT_NAME' => $component->getName(),
				'ACTION_NAME' => 'save',
				'SIGNED_PARAMETERS' => $component->getSignedParameters(),

				'ADDITIONAL_ACTIONS' => $arResult['FORM']['ADDITIONAL_ACTIONS'],
			],
			'CUSTOM_TOOL_PANEL_BUTTONS' => $arResult['FORM']['CUSTOM_TOOL_PANEL_BUTTONS'],
			'TOOL_PANEL_BUTTONS_ORDER' => $arResult['FORM']['TOOL_PANEL_BUTTONS_ORDER'],
			'ENABLE_TOOL_PANEL' => $arResult['FORM']['ENABLE_TOOL_PANEL'],

			// uf
			'ENABLE_USER_FIELD_CREATION' => $arResult['FORM']['ENABLE_USER_FIELD_CREATION'],
			'ENABLE_USER_FIELD_MANDATORY_CONTROL' => $arResult['FORM']['ENABLE_USER_FIELD_MANDATORY_CONTROL'],
			'USER_FIELD_ENTITY_ID' => $arResult['FORM']['USER_FIELD_ENTITY_ID'],
			'USER_FIELD_PREFIX' => $arResult['FORM']['USER_FIELD_PREFIX'],
			'USER_FIELD_CREATE_SIGNATURE' => $arResult['FORM']['USER_FIELD_CREATE_SIGNATURE'],
			'USER_FIELD_CREATE_PAGE_URL' => $arResult['FORM']['USER_FIELD_CREATE_PAGE_URL'],
		];

		if ($arResult['INCLUDE_CRM_ENTITY_EDITOR']):
			$componentName = 'bitrix:crm.entity.editor';
			$editorParams = array_merge(
				$editorParams,
				[
					'MODULE_ID' => 'crm',
					'ENTITY_TYPE_ID' => CCrmOwnerType::StoreDocument,
					'CONFIG_ID' => 'store_document_details',
				]
			);
			?>
			<script>
				BX.Catalog.DocumentCard.DocumentCard.registerDocumentControllersFactory(
					'BX.Crm.EntityEditorControllerFactory:onInitialize'
				);
			</script>
		<?php else:
		$componentName = 'bitrix:ui.form';
		?>
			<script>
				BX.Catalog.DocumentCard.DocumentCard.registerFieldFactory();
				BX.Catalog.DocumentCard.DocumentCard.registerModelFactory();
				BX.Catalog.DocumentCard.DocumentCard.registerDocumentControllersFactory(
					'BX.UI.EntityEditorControllerFactory:onInitialize'
				);
			</script>
		<?php
		endif;
		$APPLICATION->IncludeComponent(
			$componentName,
			'',
			$editorParams,
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