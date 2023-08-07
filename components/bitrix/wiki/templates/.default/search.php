<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	'bitrix:search.page',
	'',		
	array(
		'RESTART' => 'N',
		'CHECK_DATES' => 'N',
		'USE_TITLE_RANK' => 'Y',
		'DEFAULT_SORT' => 'rank',
		'arrFILTER' => array(
			0 => 'iblock_'.$arResult['IBLOCK_TYPE'],
		),
		'arrFILTER_iblock_'.$arResult['IBLOCK_TYPE'] => array(
			0 => $arResult['IBLOCK_ID'],
		),
		'SHOW_WHERE' => 'N',
		'SHOW_WHEN' => 'N',
		'PAGE_RESULT_COUNT' => '20',
		'AJAX_MODE' => 'N',
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'CACHE_TIME_LONG' => $arResult['CACHE_TIME_LONG'],
		'DISPLAY_TOP_PAGER' => 'Y',
		'DISPLAY_BOTTOM_PAGER' => 'Y',
		'PAGER_TITLE' => GetMessage('WIKI_SEARCH_RESULT'),
		'PAGER_SHOW_ALWAYS' => 'Y',
		'PAGER_TEMPLATE' => '',
		'USE_SUGGEST' => 'N',
		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
		'OPER_VAR' => $arResult['ALIASES']['oper']		
		),		
	$component
);
?>