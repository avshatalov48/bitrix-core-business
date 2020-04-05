<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $CACHE_MANAGER;
$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';
$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['SEF_MODE']))
{
	$arParams['SEF_MODE'] = 'N';
	if ($arParams['IN_COMPLEX'] == 'Y')
		$arParams['SEF_MODE'] = $this->GetParent()->arResult['SEF_MODE'];
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arCache = array();
if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}
if (!empty($arParams['SOCNET_GROUP_ID']))
	$arCache = array('SOCNET_GROUP_ID' => $arParams['SOCNET_GROUP_ID']);

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == "Y")
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if(strlen($arParams['PATH_TO_HISTORY_DIFF'])<=0)
{
	$arParams["PATH_TO_HISTORY_DIFF"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_HISTORY_DIFF'] = $this->GetParent()->arResult['PATH_TO_HISTORY_DIFF'];
}

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if(strlen($arParams['PATH_TO_DISCUSSION'])<=0)
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$DiffId = 0;
if (isset($_REQUEST['diffid']))
{
	$DiffId = intval($_REQUEST['diffid']);
	$arCache['diffid'] = $DiffId;
}

$historyId = 0;
if (isset($_REQUEST['oldid']))
{
	$historyId = intval($_REQUEST['oldid']);
	$arCache['oldid'] = $historyId;
}

if (isset($_REQUEST['ID']) && is_array($_REQUEST['ID']))
{
	$DiffId = intval($_REQUEST['ID'][0]);
	$arCache['diffid'] = $DiffId;
	$historyId = intval($_REQUEST['ID'][1]);
	$arCache['oldid'] = $historyId;
}

$GLOBALS['arParams'] = $arParams;

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

if(!CModule::IncludeModule('bizproc'))
{
	ShowError(GetMessage('BIZPROC_MODULE_NOT_INSTALLED'));
	return;
}

if (empty($arParams['IBLOCK_ID']))
{
	ShowError(GetMessage('IBLOCK_NOT_ASSIGNED'));
	return;
}

if (array_key_exists('SOCNET_GROUP_ID', $arParams) && empty($arParams['SOCNET_GROUP_ID']))
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	if(!CModule::IncludeModule('socialnetwork'))
	{
		ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
		return;
	}
}

$arResult['SOCNET'] = false;
if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	$iblock_id_tmp = CWikiSocnet::RecalcIBlockID($arParams["SOCNET_GROUP_ID"]);
	if ($iblock_id_tmp)
		$arParams['IBLOCK_ID'] = $iblock_id_tmp;

	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID']))
	{
		ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
		return;
	}
	$arResult['SOCNET'] = true;
}

if (!CWikiUtils::IsReadable())
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}

if (empty($historyId) || empty($DiffId))
{
	LocalRedirect(CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_HISTORY'],
			array(
				'wiki_name' => $arParams['ELEMENT_NAME'],
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'history') : array()
	));
	return ;
}

$res = CIBlock::GetList(Array(), Array("ID"=>$arParams['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N'));
$arIBLOCK = $res->GetNext();
if (empty($arIBLOCK['BIZPROC']) || $arIBLOCK['BIZPROC'] != 'Y')
{
	ShowError(GetMessage('WIKI_NOT_CHANGE_BIZPROC'));
	return;
}

CUtil::InitJSCore(array("ajax", "tooltip")); //http://jabber.bx/view.php?id=30695

if($this->StartResultCache(false, array($USER->GetGroups(), $arCache), false))
{
	$arParams['ELEMENT_NAME'] = rawurldecode($arParams['ELEMENT_NAME']);
	$arResult['ELEMENT'] = array();
	if (empty($arParams['ELEMENT_NAME']))
		$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);

	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y'
	);

	if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
		$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];

	$documentId = array('iblock', 'CWikiDocument', $arParams['ELEMENT_ID']);
	$arErrorsTmp = array();
	$arHistoryResult = CBPDocument::GetDocumentFromHistory($historyId, $arErrorsTmp);
	if (count($arErrorsTmp) > 0)
	{
		foreach ($arErrorsTmp as $e)
			$arResult['FATAL_MESSAGE'] .= $e['message'];
	}
	$arDiffResult = CBPDocument::GetDocumentFromHistory($DiffId, $arErrorsTmp);
	if (count($arErrorsTmp) > 0)
	{
		foreach ($arErrorsTmp as $e)
			$arResult['FATAL_MESSAGE'] .= $e['message'];

		$this->AbortResultCache();
		ShowError($arResult['FATAL_MESSAGE']);
		return;
	}

	if (!($arHistoryResult['DOCUMENT_ID'] == $documentId && $arDiffResult['DOCUMENT_ID'] == $documentId))
	{
		$this->AbortResultCache();
		ShowError(GetMessage('WIKI_ACCESS_DENIED'));
		return;
	}


	$canRead = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ReadDocument,
		$GLOBALS['USER']->GetID(),
		$documentId,
		array('UserGroups' => $GLOBALS['USER']->GetUserGroupArray())
	);
	if (!$canRead)
	{
		$this->AbortResultCache();
			ShowError(GetMessage('WIKI_ACCESS_DENIED'));
			return;
	}

	$rsUser = CUser::GetByID($arDiffResult['USER_ID']);
	$arUser = $rsUser->Fetch();

	if ($arResult['SOCNET'])
	{
		$arResult['AJAX_PAGE'] = $APPLICATION->GetCurPageParam('', array('bxajaxid', 'logout'));
		$arResult['PATH_TO_USER'] = $arParams['PATH_TO_USER'];
		$arResult['SHOW_LOGIN'] = $this->GetParent()->arParams['SHOW_LOGIN'];
		$arResult['NAME_TEMPLATE'] = $this->GetParent()->arParams['NAME_TEMPLATE'];
		$arResult['PATH_TO_CONPANY_DEPARTMENT'] = $this->GetParent()->arParams['PATH_TO_CONPANY_DEPARTMENT'];
		$arResult['PATH_TO_VIDEO_CALL'] = $this->GetParent()->arParams['PATH_TO_VIDEO_CALL'];
		$arResult['PATH_TO_SONET_MESSAGES_CHAT'] = $this->GetParent()->arParams['PATH_TO_MESSAGES_CHAT'];
	}

	$arResult['VERSION_DIFF'] = array();
	$arResult['VERSION_DIFF']['USER_ID'] = $arDiffResult['USER_ID'];
	$arResult['VERSION_DIFF']['USER_LOGIN'] = CWikiUtils::GetUserLogin($arUser, $arParams["NAME_TEMPLATE"]);
	$arResult['VERSION_DIFF']['MODIFIED'] = FormatDateFromDB($arDiffResult['MODIFIED']);
	$arResult['VERSION_DIFF']['ID'] = $arDiffResult['ID'];
	$arResult['VERSION_DIFF']['SHOW_LINK'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"],
			array(
				'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		array('oldid' => $arDiffResult['ID'])
	);
	$arResult['VERSION_DIFF']['USER_LINK'] = '';
	if (!empty($arParams["PATH_TO_USER"]))
	{
		$arResult['VERSION_DIFF']['USER_LINK'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"],
				array(
					'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
					'group_id' => CWikiSocnet::$iSocNetId,
					'user_id' => $arDiffResult['USER_ID']
				)
			),
			array()
		);
	}

	$rsUser = CUser::GetByID($arHistoryResult['USER_ID']);
	$arUser = $rsUser->Fetch();

	$arResult['VERSION_OLD'] = array();
	$arResult['VERSION_OLD']['USER_ID'] = $arHistoryResult['USER_ID'];
	$arResult['VERSION_OLD']['USER_LOGIN'] = CWikiUtils::GetUserLogin($arUser, $arParams["NAME_TEMPLATE"]);
	$arResult['VERSION_OLD']['MODIFIED'] = FormatDateFromDB($arHistoryResult['MODIFIED']);
	$arResult['VERSION_OLD']['ID'] = $arHistoryResult['ID'];
	$arResult['VERSION_OLD']['SHOW_LINK'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'],
			array(
				'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		array('oldid' => $arHistoryResult['ID'])
	);
	$arResult['VERSION_OLD']['USER_LINK'] = '';
	if (!empty($arParams['PATH_TO_USER']))
	{
		$arResult['VERSION_OLD']['USER_LINK'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'],
				array(
					'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
					'group_id' => CWikiSocnet::$iSocNetId,
					'user_id' => $arHistoryResult['USER_ID']
				)
			),
			array()
		);
	}

	$arHp = array('oldid' => $arHistoryResult['ID'], 'sessid' => bitrix_sessid());
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')
		$arHp[$arParams["OPER_VAR"]] = 'history';
	$arResult['CANCEL_LINK'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_HISTORY"],
			array(
				'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		$arHp
	);

	$wikiDiff = new Bitrix\Wiki\Diff();

	$arResult['DIFF_NAME'] = $wikiDiff->getDiffHtml(
		$arHistoryResult['DOCUMENT']['FIELDS']['NAME'],
		$arDiffResult['DOCUMENT']['FIELDS']['NAME']
	);

	$arCat = array();
	$CWikiParser = new CWikiParser();

	$arResult['DIFF'] = $wikiDiff->getDiffHtml(
		$arHistoryResult['DOCUMENT']['FIELDS']['DETAIL_TEXT'],
		$arDiffResult['DOCUMENT']['FIELDS']['DETAIL_TEXT']
	);

	$arResult['DIFF'] = $CWikiParser->parseBeforeSave($arResult['DIFF'], $arCat, $arParams["NAME_TEMPLATE"]);
	$arResult['DIFF'] = $CWikiParser->Parse($arResult['DIFF'], $arHistoryResult['DOCUMENT']['FIELDS']['DETAIL_TEXT_TYPE']);
	$arResult['DIFF'] = $CWikiParser->Clear($arResult['DIFF']);

	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag('wiki_'.$arParams['ELEMENT_ID']);
	$CACHE_MANAGER->EndTagCache();

	$this->IncludeComponentTemplate();
}

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

unset($GLOBALS['arParams']);

?>
