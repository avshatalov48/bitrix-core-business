<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */

\Bitrix\Main\Page\Asset::getInstance()->addString(
	'<meta property="Bitrix24SiteType" content="' . mb_strtolower($arParams['TYPE']) . '" />'
);