<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;

if (!isset($arResult['LANDING']))
{
	return;
}

// set meta og:image
$metaOG = Manager::getPageView('MetaOG');
if (strpos($metaOG, '"og:image"') === false)
{
	Manager::setPageView(
		'MetaOG',
		'<meta property="og:image" content="' . $arResult['LANDING']->getPreview() . '" />'
	);
}

// we set canonical, only if user no setup it before
$headBlock = \Bitrix\Landing\Hook\Page\HeadBlock::getLastInsertedCode();
if (strpos($headBlock, '"canonical"') === false)
{
	$component->setCanonical($arResult['LANDING']);
}