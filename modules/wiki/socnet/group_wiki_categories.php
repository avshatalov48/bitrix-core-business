<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_wiki";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");
?>
<?$APPLICATION->IncludeComponent(
	'bitrix:wiki.menu',
	'',
	Array(
		'IBLOCK_TYPE' => COption::GetOptionString('wiki', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('wiki', 'socnet_iblock_id'),
		'ELEMENT_NAME' => isset($arResult['VARIABLES']['title']) ? $arResult['VARIABLES']['title'] : $arResult['VARIABLES']['wiki_name'],
		'MENU_TYPE' => 'page',
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_WIKI_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_WIKI_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_WIKI_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_WIKI_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'USE_REVIEW' => COption::GetOptionString('wiki', 'socnet_use_review'),
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id']
	),
	$component
);?>
<?$APPLICATION->IncludeComponent(
	'bitrix:wiki.categories',
	'',
	Array(
		'IBLOCK_TYPE' => COption::GetOptionString('wiki', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('wiki', 'socnet_iblock_id'),
		'CATEGORY_COUNT' => 20,
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_WIKI_POST'],
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE']
	),
	$component
);
?>

