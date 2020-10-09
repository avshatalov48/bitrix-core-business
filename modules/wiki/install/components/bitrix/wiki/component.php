<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arDefaultUrlTemplates404 = array(
	'index' => 'index.php',
	'post' => '#wiki_name#/',
	'category' => '#wiki_name#/',
	'discussion' => '#wiki_name#/discussion/',
	'categories' => 'categories/',
	'search' => 'search/',
	'post_edit' => '#wiki_name#/edit/',
	'history' => '#wiki_name#/history/',
	'history_diff' => '#wiki_name#/history/diff/',
	'comment' => 'comment/#message_id#/'
);

$arDefaultVariableAliases404 = array(

);

$arDefaultVariableAliases = array();
$componentPage = '';
$arComponentVariables = array('wiki_name', 'oper', 'message_id','title');

if ($arParams['SEF_MODE'] == 'Y')
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams['SEF_URL_TEMPLATES']);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams['VARIABLE_ALIASES']);
	$componentPage = CComponentEngine::ParseComponentPath($arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);

	//message on like on comment lead here
	if($componentPage == "comment")
	{
		CModule::IncludeModule('forum');
		$dbMessage = CForumMessage::GetList(array(), array('ID' => intval($arVariables["message_id"])));

		if ($arMessage = $dbMessage->Fetch())
		{
			$elementID = intval($arMessage['PARAM2']);

			if ($elementID > 0)
				$dbElement = CIBlockElement::GetList(array(), array('ID' => $elementID));


			if ($dbElement && ($arElement = $dbElement->Fetch()))
				if($arElement['IBLOCK_ID'] == $arParams['IBLOCK_ID'])
				{
					$redirectPath = $arParams['SEF_FOLDER'];
					$redirectPath .= CComponentEngine::MakePathFromTemplate($arParams["SEF_URL_TEMPLATES"]['post'], array("wiki_name" => rawurlencode($arElement['NAME'])));
					$redirectPath .= "?MID=".$arVariables["message_id"]."#message".$arVariables["message_id"];
					LocalRedirect($redirectPath);
				}

		}
	}

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
		$componentPage = 'index';

	if (!isset($_REQUEST['oper']) && $componentPage == 'post_edit')
		$_REQUEST['oper'] = 'edit';
	else if (!isset($_REQUEST['oper']) && ($componentPage != 'index' && $componentPage != 'post'))
		$_REQUEST['oper'] = $componentPage;

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
	{
		if($arParams['PATH_TO_'.mb_strtoupper($url)] == '')
			$arResult['PATH_TO_'.mb_strtoupper($url)] = $arParams['SEF_FOLDER'].$value;
		else
			$arResult['PATH_TO_'.mb_strtoupper($url)] = $arParams['PATH_TO_'.mb_strtoupper($url)];

		$arResult['PATH_TO_CATEGORY'] = $arResult['PATH_TO_POST'];
	}

	$bDesignMode = $GLOBALS['APPLICATION']->GetShowIncludeAreas() && is_object($GLOBALS['USER']) && $GLOBALS['USER']->IsAdmin();

	if ($bDesignMode)
	{
		if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN' && is_dir($_SERVER['DOCUMENT_ROOT'].$arParams['SEF_FOLDER']))
		{
			ShowError(GetMessage('WIKI_SEF_FOLDER_INCORRECT'));
			return ;
		}
	}

}
else
{
	$arComponentVariables[] = $arParams['VARIABLE_ALIASES']['wiki_name'];
	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams['VARIABLE_ALIASES']);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if(!$arVariables['wiki_name']) //http://jabber.bx/view.php?id=28100 http://jabber.bx/view.php?id=28099
		$arVariables['wiki_name'] = $arVariables['title'];

	$arVariables['wiki_name'] = CUtil::ConvertToLangCharset($arVariables['wiki_name']); //http://jabber.bx/view.php?id=14756

	$componentPage = $arVariables['oper'];
	if ($componentPage == 'add' || $componentPage == 'edit'
		|| $componentPage == 'rename' || $componentPage == 'rename_it'
		|| $componentPage == 'delete' || $componentPage == 'access')
	{
		$componentPage = 'post_edit';
	}

	if ($componentPage == 'comment')
			$componentPage = 'post';

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
		$componentPage = 'index';

}

$arResult = array_merge(
	array(
		'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'SEF_MODE' => $arParams['SEF_MODE'],
		'SEF_FOLDER' => $arParams['SEF_FOLDER'],
		'USE_REVIEW' => $arParams['USE_REVIEW'],
		'VARIABLES' => $arVariables,
		'ALIASES' => $arParams['SEF_MODE'] == 'Y'? array(): $arVariableAliases,
		'SHOW_RATING' => $arParams['SHOW_RATING'],
		'RATING_TYPE' => $arParams['RATING_TYPE'],
		'SET_TITLE' => $arParams['SET_TITLE'],
		'CACHE_TYPE' => $arParams['CACHE_TYPE'],
		'CACHE_TIME' => $arParams['CACHE_TIME'],
		'CACHE_TIME_LONG' => $arParams['CACHE_TIME_LONG'],
		'SET_NAV_CHAIN' => $arParams['SET_NAV_CHAIN'],
		'INCLUDE_IBLOCK_INTO_CHAIN' => $arParams['INCLUDE_IBLOCK_INTO_CHAIN'],
		'ADD_SECTIONS_CHAIN' => $arParams['ADD_SECTIONS_CHAIN'],
		'MESSAGES_PER_PAGE' => $arParams['MESSAGES_PER_PAGE'],
		'USE_CAPTCHA' => $arParams['USE_CAPTCHA'],
		'FORUM_ID' => $arParams['FORUM_ID'],
		'PATH_TO_USER' => $arParams['PATH_TO_USER'],
		'PATH_TO_SMILE' => $arParams['PATH_TO_SMILE'],
		'URL_TEMPLATES_READ' => $arParams['URL_TEMPLATES_READ'],
		'SHOW_LINK_TO_FORUM' => $arParams['SHOW_LINK_TO_FORUM'],
		'POST_FIRST_MESSAGE' => $arParams['POST_FIRST_MESSAGE'],
		'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"]
	),
	$arResult
);

$this->IncludeComponentTemplate($componentPage);

?>
