<?php

use Bitrix\Socialnetwork\Livefeed\Context\Context;

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
			'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.features',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'CONTEXT' => Context::SPACES,
				'GROUP_ID' => $arResult['groupId'],
				'USER_ID' => $arResult['userId'],
				'PAGE_ID' => 'group_features',
				'PATH_TO_USER' => $arParams['PATH_TO_USER'],
				'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
				'SET_NAV_CHAIN' => 'Y',
				'SET_TITLE' => 'Y',
			],
		]
	);
?>
</div>
