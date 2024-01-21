<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

?>

<div class="sn-spaces__group">
<?php
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group_create.ex',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'PATH_TO_USER' => $arParams['PATH_TO_USER'],
				'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
				'USER_ID' => $arResult['userId'],
				'GROUP_ID' => $arResult['groupId'],
				'TAB' => 'INVITE',
				'THEME_ENTITY_TYPE' => 'SONET_GROUP',
				'USE_KEYWORDS' => 'Y',
				'USE_AUTOSUBSCRIBE' => 'N',
				'SET_NAV_CHAIN' => 'Y',
				'SET_TITLE' => 'Y',
			],
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['groupId'],
		]
	);
?>
</div>
