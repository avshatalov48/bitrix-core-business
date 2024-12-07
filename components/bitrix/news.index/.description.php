<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
	'NAME' => Loc::getMessage('T_IBLOCK_DESC_ALLNEWS'),
	'DESCRIPTION' => Loc::getMessage('T_IBLOCK_DESC_ALLNEWS_DESC'),
	'ICON' => '/images/news_all.gif',
	'SORT' => 50,
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'content',
		'CHILD' => [
			'ID' => 'news',
			'NAME' => Loc::getMessage('T_IBLOCK_DESC_NEWS'),
			'SORT' => 10,
		],
	],
];
