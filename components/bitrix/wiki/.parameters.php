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
		'REVIEW_SETTINGS' => array(
			'NAME' => GetMessage('WIKI_REVIEW_SETTINGS'),
		)
	),
	'PARAMETERS' => array(
		'VARIABLE_ALIASES' => Array(
			'wiki_name' => Array(
					'NAME' => GetMessage('WIKI_PAGE_VAR'),
					'DEFAULT' => 'title',
					),
			'oper' => Array(
					'NAME' => GetMessage('WIKI_OPER_VAR'),
					'DEFAULT' => '',
					)
			),
		'SEF_MODE' => Array(
			'index' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_INDEX'),
				'DEFAULT' => 'index.php',
				'VARIABLES' => array(),
			),
			'post' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_POST'),
				'DEFAULT' => '#wiki_name#/',
				'VARIABLES' => array('title'),
			),
			'post_edit' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_POST_EDIT'),
				'DEFAULT' => '#wiki_name#/edit/',
				'VARIABLES' => array('title'),
			),
			'categories' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_CATEGORIES'),
				'DEFAULT' => 'categories/',
				'VARIABLES' => array('title'),
			),
			'discussion' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_DISCUSSION'),
				'DEFAULT' => '#wiki_name#/discussion/',
				'VARIABLES' => array('title'),
			),
			'history' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_HISTORY'),
				'DEFAULT' => '#wiki_name#/history/',
				'VARIABLES' => array('title'),
			),
			'history_diff' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_HISTORY_DIFF'),
				'DEFAULT' => '#wiki_name#/history/diff/',
				'VARIABLES' => array('title'),
			),
			'search' => array(
				'NAME' => GetMessage('WIKI_SEF_PATH_TO_SEARCH'),
				'DEFAULT' => 'search/',
				'VARIABLES' => array('title'),
			),
		),
		'PATH_TO_USER' => Array(
			'NAME' => GetMessage('WIKI_PATH_TO_USER'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'URL_TEMPLATES',
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
		'SHOW_RATING' => Array(
			'NAME' => GetMessage('SHOW_RATING'),
			'TYPE' => 'LIST',
			'VALUES' => Array(
				'' => GetMessage('SHOW_RATING_CONFIG'),
				'Y' => GetMessage('MAIN_YES'),
				'N' => GetMessage('MAIN_NO'),
			),
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
		'RATING_TYPE' => Array(
			'NAME' => GetMessage('RATING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => Array(
				'' => GetMessage('RATING_TYPE_CONFIG'),
				'like' => GetMessage('RATING_TYPE_LIKE_TEXT'),
				'like_graphic' => GetMessage('RATING_TYPE_LIKE_GRAPHIC'),
				'standart_text' => GetMessage('RATING_TYPE_STANDART_TEXT'),
				'standart' => GetMessage('RATING_TYPE_STANDART_GRAPHIC'),
			),
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
		'USE_REVIEW' => Array(
			'PARENT' => 'REVIEW_SETTINGS',
			'NAME' => GetMessage('T_IBLOCK_DESC_USE_REVIEW'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if(!IsModuleInstalled('forum'))
{
	unset($arComponentParameters['PARAMETERS']['USE_REVIEW']);
	unset($arComponentParameters['GROUPS']['REVIEW_SETTINGS']);
}
elseif($arCurrentValues['USE_REVIEW']=='Y')
{
	$arForumList = array();
	if(CModule::IncludeModule('forum'))
	{
		$rsForum = CForumNew::GetList();
		$arForumList[] = '';
		while($arForum=$rsForum->Fetch())
			$arForumList[$arForum['ID']]=$arForum['NAME'];
	}
	$arComponentParameters['PARAMETERS']['MESSAGES_PER_PAGE'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_MESSAGES_PER_PAGE'),
		'TYPE' => 'STRING',
		'DEFAULT' => intval(COption::GetOptionString('forum', 'MESSAGES_PER_PAGE', '10'))
	);
	$arComponentParameters['PARAMETERS']['USE_CAPTCHA'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_USE_CAPTCHA'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	);
	$arComponentParameters['PARAMETERS']['PATH_TO_SMILE'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_PATH_TO_SMILE'),
		'TYPE' => 'STRING',
		'DEFAULT' => '/bitrix/images/forum/smile/',
	);
	$arComponentParameters['PARAMETERS']['FORUM_ID'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_FORUM_ID'),
		'TYPE' => 'LIST',
		'VALUES' => $arForumList,
		'DEFAULT' => '',
	);
	$arComponentParameters['PARAMETERS']['URL_TEMPLATES_READ'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_READ_TEMPLATE'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	);
	$arComponentParameters['PARAMETERS']['SHOW_LINK_TO_FORUM'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_SHOW_LINK_TO_FORUM'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
	$arComponentParameters['PARAMETERS']['POST_FIRST_MESSAGE'] = Array(
		'PARENT' => 'REVIEW_SETTINGS',
		'NAME' => GetMessage('F_POST_FIRST_MESSAGE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
}

$arComponentParameters['PARAMETERS'] += Array(
	'NAV_ITEM' => Array(
		'NAME' => GetMessage('WIKI_NAV_ITEM'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '',
		'COLS' => 25,
		'PARENT' => 'ADDITIONAL_SETTINGS',
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
	'NAME_TEMPLATE' => array(
		"TYPE" => "LIST",
		"NAME" => GetMessage("WIKI_NAME_TEMPLATE"),
		"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
		"MULTIPLE" => "N",
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	),
);

?>