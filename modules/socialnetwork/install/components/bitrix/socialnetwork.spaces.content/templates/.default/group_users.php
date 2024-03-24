<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Main\Context;

$isSidePanel = (bool) Context::getCurrent()->getRequest()->get('IFRAME_TYPE');
$includeToolbar = $isSidePanel;
?>

<div class="sn-spaces__group">
<?php
	if ($isSidePanel)
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group.user.list',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => [
					'INCLUDE_TOOLBAR' => $includeToolbar,
					'INCLUDE_COUNTERS_BELOW_TITLE' => $includeToolbar,
					'FILTER_ID' => 'SOCIALNETWORK_WORKGROUP_USER_LIST',
					'GROUP_ID' => $arResult['groupId'],
					'MODE' => $arResult['mode'],
					'PATH_TO_USER' => $arParams['PATH_TO_USER'],
					'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
					'PATH_TO_GROUP_INVITE' => $arParams['PATH_TO_GROUP_INVITE'],
					'USE_AUTO_MEMBERS' => 'Y',
					'SET_NAV_CHAIN' => 'Y',
					'SET_TITLE' => 'Y',
				],
				'USE_UI_TOOLBAR' => 'Y',
			]
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			'bitrix:socialnetwork.group.user.list',
			'',
			[
				'INCLUDE_TOOLBAR' => $includeToolbar,
				'INCLUDE_COUNTERS_BELOW_TITLE' => $includeToolbar,
				'FILTER_ID' => 'SOCIALNETWORK_WORKGROUP_USER_LIST',
				'GROUP_ID' => $arResult['groupId'],
				'MODE' => $arResult['mode'],
				'PATH_TO_USER' => $arParams['PATH_TO_USER'],
				'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
				'PATH_TO_GROUP_INVITE' => $arParams['PATH_TO_GROUP_INVITE'],
				'USE_AUTO_MEMBERS' => 'Y',
				'SET_NAV_CHAIN' => 'Y',
				'SET_TITLE' => 'Y',
			]
		);
	}
?>
</div>
