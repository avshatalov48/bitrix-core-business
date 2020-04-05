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
		'CATEGORY_COUNT' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('WIKI_CATEGORY_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '20',
		),					
	)
);
?>