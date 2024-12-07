<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

$arResult['size'] = ($arResult['userField']['SETTINGS']['LIST_HEIGHT'] > 1 ?
	(int)$arResult['userField']['SETTINGS']['LIST_HEIGHT'] : ''
);

if (!is_array($arResult['additionalParameters']['VALUE']))
{
	$arResult['additionalParameters']['VALUE'] = [$arResult['additionalParameters']['VALUE']];
}
