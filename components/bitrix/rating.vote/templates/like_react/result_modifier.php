<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

$arResult["LIKE_TEMPLATE"] = (
	!empty($arParams["LIKE_TEMPLATE"])
		? $arParams["LIKE_TEMPLATE"]
		: 'light'
);

$arResult['COMMENT'] = (
	!empty($arParams["COMMENT"])
		? $arParams["COMMENT"]
		: 'N'
);

$arParams['MOBILE'] = (
	!empty($arParams["MOBILE"])
		? $arParams["MOBILE"]
		: 'N'
);

if (
	$arParams['MOBILE'] === 'Y'
	&& $arResult['COMMENT'] === 'Y'
)
{
	$arParams['REACTIONS_LIST'] = (
		isset($arParams['REACTIONS_LIST'])
		&& is_array($arParams['REACTIONS_LIST'])
			? array_reverse($arParams['REACTIONS_LIST'], true)
			: array()
	);
}
