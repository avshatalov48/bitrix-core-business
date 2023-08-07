<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent(
	'bitrix:wiki.menu',
	'',
	Array(
		'IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
		'ELEMENT_NAME' => $arResult['VARIABLES']['wiki_name'],
		'MENU_TYPE' => 'page',
		'PATH_TO_POST' => $arResult['PATH_TO_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_HISTORY_DIFF'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],	
		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
		'OPER_VAR' => $arResult['ALIASES']['oper'],	
		'USE_REVIEW' => $arResult['USE_REVIEW'],
	),
	$component
);?>

<?$APPLICATION->IncludeComponent(
	'bitrix:wiki.history',
	'',
	Array(
		'PATH_TO_POST' => $arResult['PATH_TO_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_HISTORY_DIFF'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],		
		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
		'OPER_VAR' => $arResult['ALIASES']['oper'],
		'IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
		'ELEMENT_NAME' => $arResult['VARIABLES']['wiki_name'],
		'HISTORY_COUNT' => '20',
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'SET_TITLE' => $arResult['SET_TITLE'],
		'SET_STATUS_404' => $arResult['SET_STATUS_404'],
		'INCLUDE_IBLOCK_INTO_CHAIN' => $arResult['INCLUDE_IBLOCK_INTO_CHAIN'],
		'ADD_SECTIONS_CHAIN' => $arResult['ADD_SECTIONS_CHAIN']
	),
$component
);?>