<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	'bitrix:wiki.category',
	'',
	Array(
		'PATH_TO_POST' => $arResult['PATH_TO_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_POST_EDIT'],
		'PATH_TO_CATEGORY' => $arResult['PATH_TO_CATEGORY'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_HISTORY_DIFF'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],	
		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
		'OPER_VAR' => $arResult['ALIASES']['oper'],
		'IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'ELEMENT_NAME' => $arResult['VARIABLES']['wiki_name'],
		'PAGES_COUNT' => '100',
		'COLUMNS_COUNT' => '3'	
	),
	$component
);



?>
