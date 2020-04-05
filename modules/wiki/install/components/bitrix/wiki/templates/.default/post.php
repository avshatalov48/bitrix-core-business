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
		'PATH_TO_SEARCH' => $arResult['PATH_TO_SEARCH'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_HISTORY_DIFF'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],
		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
		'OPER_VAR' => $arResult['ALIASES']['oper'],
		'USE_REVIEW' => $arResult['USE_REVIEW'],
	),
	$component
);?>

<?$ID = $APPLICATION->IncludeComponent(
	'bitrix:wiki.show',
	'',
	Array(
		'PATH_TO_POST' => $arResult['PATH_TO_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_DISCUSSION'],
		'PATH_TO_SEARCH' => $arResult['PATH_TO_SEARCH'],
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
		'SHOW_RATING' => $arResult['SHOW_RATING'],
		'RATING_TYPE' => $arResult['RATING_TYPE'],
		'SET_TITLE' => $arResult['SET_TITLE'],
		'SET_STATUS_404' => $arResult['SET_STATUS_404'],
		'INCLUDE_IBLOCK_INTO_CHAIN' => $arResult['INCLUDE_IBLOCK_INTO_CHAIN'],
		'ADD_SECTIONS_CHAIN' => $arResult['ADD_SECTIONS_CHAIN']
	),
	$component
);?>
<br/>
<?
if (!empty($ID))
{
    $APPLICATION->IncludeComponent(
    	'bitrix:wiki.discussion',
    	'',
    	Array(
    		'PATH_TO_POST' => $arResult['PATH_TO_POST'],
    		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_POST_EDIT'],
    		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_DISCUSSION'],
    		'PATH_TO_HISTORY' => $arResult['PATH_TO_HISTORY'],
    		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_HISTORY_DIFF'],
    		'PAGE_VAR' => $arResult['ALIASES']['wiki_name'],
    		'OPER_VAR' => $arResult['ALIASES']['oper'],
    		'IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
    		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    		'USE_REVIEW' => $arResult['USE_REVIEW'],
    		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
    		'CACHE_TIME' => $arResult['CACHE_TIME'],
    		'ELEMENT_NAME' => $arResult['VARIABLES']['wiki_name'],
    		'SET_TITLE' => $arResult['SET_TITLE'],
    		'SET_STATUS_404' => $arResult['SET_STATUS_404'],
    		'INCLUDE_IBLOCK_INTO_CHAIN' => $arResult['INCLUDE_IBLOCK_INTO_CHAIN'],
    		'ADD_SECTIONS_CHAIN' => $arResult['ADD_SECTIONS_CHAIN'],
    		'MESSAGES_PER_PAGE' => $arResult['MESSAGES_PER_PAGE'],
    		'USE_CAPTCHA' => $arResult['USE_CAPTCHA'],
    		'FORUM_ID' => $arResult['FORUM_ID'],
    		'PATH_TO_SMILE' => $arResult['PATH_TO_SMILE'],
    		'URL_TEMPLATES_READ' => $arResult['URL_TEMPLATES_READ'],
    		'SHOW_LINK_TO_FORUM' => $arResult['SHOW_LINK_TO_FORUM'],
    		'POST_FIRST_MESSAGE' => $arResult['POST_FIRST_MESSAGE'],
			'SHOW_RATING' => $arResult['SHOW_RATING'],
			'RATING_TYPE' => $arResult['RATING_TYPE'],
			'PATH_TO_USER' => $arResult['~PATH_TO_USER'],				
    	),
    	$component
    );
}?>