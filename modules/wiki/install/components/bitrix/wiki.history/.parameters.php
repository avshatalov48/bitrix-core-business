<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule('wiki'))
	return false;

if(!CModule::IncludeModule('iblock'))
	return false;

$dbIBlockType = CIBlockType::GetList(
	array('sort' => 'asc'),
	array('ACTIVE' => 'Y')
);
$arIblockType = array();
while ($arIBlockType = $dbIBlockType->Fetch())
{
	if ($arIBlockTypeLang = CIBlockType::GetByIDLang($arIBlockType['ID'], LANGUAGE_ID))
		$arIblockType[$arIBlockType['ID']] = '['.$arIBlockType['ID'].'] '.$arIBlockTypeLang['NAME'];
}	
	

$arTypes = CIBlockParameters::GetIBlockTypes();

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array('SORT'=>'ASC'), Array('SITE_ID'=>$_REQUEST['site'], 'TYPE' => (!empty($arCurrentValues['IBLOCK_TYPE'])?$arCurrentValues['IBLOCK_TYPE']:'wiki')));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes['ID']] = $arRes['NAME'];


$arComponentParameters = Array(
	'GROUPS' => array(
		'VARIABLE_ALIASES' => array(
			'NAME' => GetMessage('WIKI_VARIABLE_ALIASES'),
		),
	),
	'PARAMETERS' => array(
		'PATH_TO_POST' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_POST'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),		
		'PATH_TO_POST_EDIT' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_POST_EDIT'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),
		'PATH_TO_CATEGORIES' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_CATEGORIES'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),			
		'PATH_TO_DISCUSSION' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_DISCUSSION'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),
		'PATH_TO_HISTORY_DIFF' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_HISTORY_DIFF'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),
		'PATH_TO_USER' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_USER'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
		),							
		'PAGE_VAR' => Array(
			'NAME' => GetMessage('WIKI_PAGE_VAR'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'title',
			'COLS' => 25,
			'PARENT' => 'VARIABLE_ALIASES',
		),	
		'OPER_VAR' => Array(
			'NAME' => GetMessage('WIKI_OPER_VAR'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'oper',
			'COLS' => 25,
			'PARENT' => 'VARIABLE_ALIASES',
		),				
		'IBLOCK_TYPE' => Array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('T_IBLOCK_DESC_LIST_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arTypes,
			'DEFAULT' => 'wiki',
			'REFRESH' => 'Y',
		),
		'IBLOCK_ID' => Array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('T_IBLOCK_DESC_LIST_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
			'DEFAULT' => '',
			'ADDITIONAL_VALUES' => 'Y',
			'REFRESH' => 'Y',
		),
		'ELEMENT_NAME' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BND_ELEMENT_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => '={$_REQUEST["title"]}',
		),	
		'HISTORY_COUNT' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('WIKI_HISTORY_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '20',
		),					
		'CACHE_TIME'	=>	array('DEFAULT'=>'36000000'),
		'SET_TITLE' =>Array(),
		'SET_STATUS_404' => Array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('CP_BND_SET_STATUS_404'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'INCLUDE_IBLOCK_INTO_CHAIN' => Array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('T_IBLOCK_DESC_INCLUDE_IBLOCK_INTO_CHAIN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'ADD_SECTIONS_CHAIN' => Array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('T_IBLOCK_DESC_ADD_SECTIONS_CHAIN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),		

	)
);
?>