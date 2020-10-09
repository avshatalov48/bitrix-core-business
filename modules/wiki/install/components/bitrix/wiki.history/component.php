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
	if (mb_strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}
if (!empty($arParams['SOCNET_GROUP_ID']))
	$arCache = array('SOCNET_GROUP_ID' => $arParams['SOCNET_GROUP_ID']);

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if($arParams['PATH_TO_HISTORY'] == '')
{
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_HISTORY'] = $this->GetParent()->arResult['PATH_TO_HISTORY'];
}

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if($arParams['PATH_TO_USER'] == '')
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == "Y")
		$arParams["PATH_TO_USER"] = $this->GetParent()->arParams['PATH_TO_USER'];
}

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if($arParams['PATH_TO_HISTORY_DIFF'] == '')
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_CATEGORY']);
if($arParams['PATH_TO_CATEGORY'] == '')
	$arParams['PATH_TO_CATEGORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if($arParams['PATH_TO_DISCUSSION'] == '')
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

if(empty($arParams['USE_REVIEW']))
	$arParams['USE_REVIEW'] = 'Y';

if (empty($arParams['HISTORY_COUNT']))
	$arParams['HISTORY_COUNT'] = 20;

$arNavParams = array(
	'nPageSize' => $arParams['HISTORY_COUNT']
);
$arNavigation = CDBResult::GetNavParams($arNavParams);

$historyId = 0;
if (isset($_REQUEST['oldid']))
{
	$historyId = intval($_REQUEST['oldid']);
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

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	if(!CModule::IncludeModule('socialnetwork'))
	{
		ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
		return;
	}
}

if (array_key_exists('SOCNET_GROUP_ID', $arParams) && empty($arParams['SOCNET_GROUP_ID']))
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
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

$res = CIBlock::GetList(Array(), Array("ID"=>$arParams['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N'));
$arIBLOCK = $res->GetNext();
if (empty($arIBLOCK['BIZPROC']) || $arIBLOCK['BIZPROC'] != 'Y')
{
	ShowError(GetMessage('WIKI_NOT_CHANGE_BIZPROC'));
	return;
}

//converts the old type of document to a new
if (COption::GetOptionString('wiki', 'convert_history_'.$arParams['IBLOCK_ID'], 'N') == 'N')
{
	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y'
	);
	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
	while($arElement = $rsElement->GetNext())
	{
		CBPHistoryService::MergeHistory(array('iblock', 'CWikiDocument', $arElement['ID']), array('iblock', 'CIBlockDocument', $arElement['ID']));
	}
	COption::SetOptionString('wiki', 'convert_history_'.$arParams['IBLOCK_ID'], 'Y');

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

CUtil::InitJSCore(array("ajax", "tooltip")); //http://jabber.bx/view.php?id=30695

if($this->StartResultCache(false, array($USER->GetGroups(), $arNavigation, $arCache), false))
{
	$arResult['USE_REVIEW'] = $arParams['USE_REVIEW'];
	$arResult['ELEMENT'] = array();
	$arParams['ELEMENT_NAME'] = rawurldecode($arParams['ELEMENT_NAME']);
	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y'
	);

	if (empty($arParams['ELEMENT_NAME']))
		$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);

	if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
		$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
	else
	{
		$this->AbortResultCache();
		return;
	}

	//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks(array('article', 'history'), $arParams);

	$documentId = array('iblock', 'CWikiDocument', $arParams['ELEMENT_ID']);

	$bCanUserWrite = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::WriteDocument,
		$GLOBALS['USER']->GetID(),
		$documentId,
		array('UserGroups' => $GLOBALS['USER']->GetUserGroupArray())
	);

	if (!$bCanUserWrite)
	{
		$this->AbortResultCache();
		ShowError(GetMessage('WIKI_ACCESS_DENIED'));
		return;
	}

	if(!empty($historyId) && check_bitrix_sessid())
	{
		$this->AbortResultCache();
		if (isset($_REQUEST['delete']))
		{
			if (CWikiUtils::IsDeleteable())
			{
				$historyService = new CBPHistoryService();
				$historyService->DeleteHistory($historyId, array('iblock', 'CWikiDocument', $arParams['ELEMENT_ID']));
				$CACHE_MANAGER->ClearByTag('wiki_'.$arParams['ELEMENT_ID']);

				LocalRedirect(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_HISTORY'],
						array(
							'wiki_name' => $arParams['ELEMENT_NAME'],
							'group_id' => CWikiSocnet::$iSocNetId
						)
					)
				);
			}
			else
			{
				$this->AbortResultCache();
				ShowError(GetMessage('WIKI_ACCESS_DENIED'));
				return;
			}
		}
		else
		{
			try
			{
				$CWIKI = new CWiki();
				if ($CWIKI->Recover($historyId, $arParams['ELEMENT_ID'], $arParams['IBLOCK_ID']))
				{
					//   $arResult["MESSAGE"] = GetMessage('WIKI_PAGE_RECOVER');
					// so how could it change the name
					$arResult['ELEMENT_NEW'] = CWiki::GetElementById($arParams['ELEMENT_ID'], $arFilter);
					if (CWiki::GetDefaultPage($arParams['IBLOCK_ID']) == $arResult['ELEMENT']['NAME'] &&
						$arResult['ELEMENT']['NAME'] != $arResult['ELEMENT_NEW']['NAME'])
						CWiki::SetDefaultPage($arParams['IBLOCK_ID'], $arResult['ELEMENT_NEW']['NAME']);
					$CACHE_MANAGER->ClearByTag('wiki_'.$arParams['ELEMENT_ID']);

					LocalRedirect(
						CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'],
							array(
								'wiki_name' => rawurlencode($arResult['ELEMENT_NEW']['NAME']),
								'group_id' => CWikiSocnet::$iSocNetId
							)
						)
					);
				}
				else
					$arResult['FATAL_MESSAGE'] = GetMessage('WIKI_PAGE_RECOVER_ERROR');
			}
			catch (Exception $e)
			{
				$arResult['FATAL_MESSAGE'] = $e->getMessage();
			}
		}
	}

	$history = new CBPHistoryService();
	$arFilter = array('DOCUMENT_ID' => $documentId);
	$dbResultList = $history->GetHistoryList(
		array('ID' => 'DESC'),
		$arFilter,
		false,
		false,
		array('ID', 'DOCUMENT_ID', 'NAME', 'MODIFIED', 'USER_ID', 'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN')
	);

	$dbResultListFirst = $history->GetHistoryList(
		array('ID' => 'DESC'),
		$arFilter,
		false,
		false,
		array('ID')
	);

	$arResult['PAGE_VAR'] = $arParams['PAGE_VAR'];
	$arResult['OPER_VAR'] = $arParams['OPER_VAR'];
	$arResult['PATH_TO_HISTORY_DIFF'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate(
			$arParams['PATH_TO_HISTORY_DIFF'],
			array(
				'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'history_diff') : array()
	);

	$sTableID = 'tbl_bizproc_document_history';
	$rsHistoryFirst= new CDBResult($dbResultListFirst, $sTableID);
	$arHistoryFirst = $rsHistoryFirst->GetNext();

	$rsHistory= new CDBResult($dbResultList, $sTableID);
	$rsHistory->NavStart($arParams['HISTORY_COUNT'], false);
	$arResult['DB_LIST'] = &$rsHistory;
	$arResult['HISTORY'] = array();
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

	$arErrorsTmp = array();

	while($arHistory = $rsHistory->GetNext())
	{
		$arHistory['USER_LINK'] = '';
		$arHistoryResult = CBPDocument::GetDocumentFromHistory($arHistory['ID'], $arErrorsTmp);
		$arHistory["MODIFY_COMMENT"] = $arHistoryResult["DOCUMENT"]["MODIFY_COMMENT"];

		if (!empty($arParams['PATH_TO_USER']))
		{
			$arHistory['USER_LINK'] = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_USER'],
					array(
						'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
						'group_id' => CWikiSocnet::$iSocNetId,
						'user_id' => $arHistory['USER_ID']
					)
				),
				array()
			);
		}

		/*CBPHistoryService::GetHistoryList() returns specialchared values
		and CWikiUtils::GetUserLogin also do htmlspecialchars*/
		foreach (array('USER_LOGIN','USER_NAME','USER_LAST_NAME','USER_SECOND_NAME') as $key)
			$arHistory[$key] = CWikiUtils::htmlspecialchars_decode($arHistory[$key]);

		$arHistory['USER_LOGIN'] = CWikiUtils::GetUserLogin($arHistory, $arParams["NAME_TEMPLATE"]);
		$arHistory['DISCUSSION_LINK'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams['PATH_TO_DISCUSSION'],
				array(
					'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
					'group_id' => CWikiSocnet::$iSocNetId
				)
			),
			$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'discussion') : array()
		);

		$arHistory['SHOW_LINK'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams['PATH_TO_POST'],
				array(
					'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
					'group_id' => CWikiSocnet::$iSocNetId
				)
			),
			array('oldid' => $arHistory['ID'])
		);
		$arHp = array('oldid' => $arHistory['ID'], 'sessid' => bitrix_sessid());
		if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')
			$arHp[$arParams['OPER_VAR']] = 'history';
		$arHistory['CANCEL_LINK'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams['PATH_TO_HISTORY'],
				array(
					'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
					'group_id' => CWikiSocnet::$iSocNetId
				)
			),
			$arHp
		);

		if ($arHistoryFirst['ID'] != $arHistory['ID'])
		{
			$arHp = array('diffid' => $arHistoryFirst['ID'], 'oldid' => $arHistory['ID']);
			if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')
				$arHp[$arParams['OPER_VAR']] = 'history_diff';
			$arHistory['CUR_LINK'] = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_HISTORY_DIFF'],
					array(
						'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				$arHp
			);
		}

		if (CWikiUtils::IsDeleteable())
		{
			$arHp = array('oldid' => $arHistory['ID'], 'sessid' => bitrix_sessid(), 'delete' => 1);
			if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')
				$arHp[$arParams['OPER_VAR']] = 'history';
			$arHistory['DELETE_LINK'] = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_HISTORY'],
					array(
						'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				$arHp
			);
		}

		$arHistory['MODIFIED'] = FormatDateFromDB($arHistory['MODIFIED']);

		$arResult['HISTORY'][] = $arHistory;

		$iPrev = count($arResult['HISTORY']) - 2;

		if (isset($arResult['HISTORY'][$iPrev]))
		{
			$arHp = array('diffid' => $arResult['HISTORY'][$iPrev]['ID'], 'oldid' => $arHistory['ID']);
			if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')
				$arHp[$arParams['OPER_VAR']] = 'history_diff';
			$arResult['HISTORY'][$iPrev]['PREV_LINK'] = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_HISTORY_DIFF'],
					array(
						'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				$arHp
			);
		}
	}

	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag('wiki_'.$arParams['ELEMENT_ID']);
	$CACHE_MANAGER->EndTagCache();

	$this->IncludeComponentTemplate();
}

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

unset($GLOBALS['arParams']);

?>