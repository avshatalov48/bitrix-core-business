<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!empty($arResult['Group']))
{
	$arResult['groupTypeCode'] = \Bitrix\Socialnetwork\Helper\Workgroup::getTypeCodeByParams([
		'fields' => [
			'VISIBLE' => (isset($arResult['Group']['VISIBLE']) && $arResult['Group']['VISIBLE'] === 'Y' ? 'Y' : 'N'),
			'OPENED' => (isset($arResult['Group']['OPENED']) && $arResult['Group']['OPENED'] === 'Y' ? 'Y' : 'N'),
			'PROJECT' => (isset($arResult['Group']['PROJECT']) && $arResult['Group']['PROJECT'] === 'Y' ? 'Y' : 'N'),
			'EXTERNAL' => (isset($arResult['Group']['IS_EXTRANET_GROUP']) && $arResult['Group']['IS_EXTRANET_GROUP'] === 'Y' ? 'Y' : 'N'),
		],
	]);
}
?>