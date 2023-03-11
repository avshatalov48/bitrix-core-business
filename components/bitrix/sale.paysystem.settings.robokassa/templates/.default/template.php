<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

?>
<div class="sale-paysystem-settings-wrapper">
<?php
	global $APPLICATION;
	$APPLICATION->IncludeComponent(
		'bitrix:ui.form',
		'.default',
		[
			'GUID' => 'SALE_PAYSYSTEM_SETTINGS',
			'INITIAL_MODE' => 'edit',
			'IS_IDENTIFIABLE_ENTITY' => false,
			'ENTITY_FIELDS' => $arResult['ENTITY_FIELDS'],
			'ENTITY_CONFIG' => $arResult['ENTITY_CONFIG'],
			'ENTITY_DATA' => $arResult['ENTITY_DATA'],
			'ENABLE_COMMON_CONFIGURATION_UPDATE' => false,
			'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => false,
			'ENABLE_SECTION_DRAG_DROP' => false,
			'ENABLE_CONFIG_CONTROL' => false,
			'ENABLE_FIELD_DRAG_DROP' => false,
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