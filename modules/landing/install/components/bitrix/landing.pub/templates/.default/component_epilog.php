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

$landing = $arResult['LANDING'];

/** @var array $arParams */
/** @var \LandingPubComponent $component */
/** @var \Bitrix\Landing\Landing $landing */

// set meta og:image
$metaOG = Manager::getPageView('MetaOG');
if (mb_strpos($metaOG, '"og:image"') === false)
{
	$preview = $landing->getPreview();
	Manager::setPageView(
		'MetaOG',
		'<meta property="og:image" content="' . $preview . '" />' .
		'<meta property="twitter:image" content="' . $preview . '" />'
	);
}

Manager::setPageView(
	'MetaOG',
	'<meta property="Bitrix24SiteType" content="' . mb_strtolower($arParams['TYPE']) . '" />'
);

Manager::setPageView(
	'BeforeHeadClose',
	'<link rel="icon" type="image/x-icon" href="' . ($arResult['SITE_RELATIVE_URL'] ?: '/').'favicon.ico">'
);

// we set canonical, only if user no setup it before
$headBlock = \Bitrix\Landing\Hook\Page\HeadBlock::getLastInsertedCode();
if (mb_strpos($headBlock, '"canonical"') === false)
{
	$component->setCanonical($landing);
}
