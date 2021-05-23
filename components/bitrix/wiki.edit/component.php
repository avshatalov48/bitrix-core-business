<?
use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';
if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';

if(empty($arParams['SEF_MODE']))
{
	$arParams['SEF_MODE'] = 'N';
	if ($arParams['IN_COMPLEX'] == 'Y')
		$arParams['SEF_MODE'] = $this->GetParent()->arResult['SEF_MODE'];
}

if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (mb_strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if($arParams['PATH_TO_POST_EDIT'] == '')
{
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_POST_EDIT'] = $this->GetParent()->arResult['PATH_TO_POST_EDIT'];
}

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if($arParams['PATH_TO_HISTORY'] == '')
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if($arParams['PATH_TO_HISTORY_DIFF'] == '')
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if($arParams['PATH_TO_DISCUSSION'] == '')
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if($arParams['PATH_TO_CATEGORIES'] == '')
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if($arParams['PATH_TO_USER'] == '')
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

$arResult['PREVIEW'] = !empty($_POST['preview']) && $_POST['preview'] == 'Y' ? 'Y' : 'N';
$arResult['IMAGE_UPLOAD'] = isset($_GET['image_upload']) || ($_POST['do_upload']) ? 'Y' : 'N';

$arResult['DEL_DIALOG'] = isset($_GET['del_dialog']) ? 'Y' : 'N';

if (isset($_REQUEST['post_to_feed']))
{
	$arResult['POST_TO_FEED'] = ($_REQUEST['post_to_feed'] === 'Y' ? 'Y' : 'N');
	CUserOptions::SetOption("wiki", "POST_TO_FEED", $arResult['POST_TO_FEED']);
}
else
	$arResult['POST_TO_FEED'] = CUserOptions::GetOption("wiki", "POST_TO_FEED", "N");

$arResult['WIKI_oper'] = 'edit';
if (isset($_REQUEST[$arParams['OPER_VAR']]))
	$arResult['WIKI_oper'] = $_REQUEST[$arParams['OPER_VAR']];

$GLOBALS['arParams'] = $arParams;

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

$arResult['ALLOW_HTML'] = CWikiUtils::isAllowHTML() ? 'Y' : 'N';

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (IsModuleInstalled('search'))
	AddEventHandler('search', 'BeforeIndex', array('CWikiUtils', 'OnBeforeIndex'));

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

if (!CWikiUtils::IsWriteable() || ($arResult['WIKI_oper'] == 'delete' && !CWikiUtils::IsDeleteable()))
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}
$CWiki = new CWiki();
$arParams['ELEMENT_NAME'] = CWikiUtils::htmlspecialcharsback(($arParams['ELEMENT_NAME']));
$arFilter = array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'CHECK_PERMISSIONS' => 'N'
);

if (empty($arParams['ELEMENT_NAME']))
	$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);
$arResult['ELEMENT'] = array();

if ($arResult['WIKI_oper'] == 'delete')
{
	$arResult['ELEMENT'] = CWiki::GetElementById($arParams['ELEMENT_NAME'], $arFilter);

	if(!$arResult['ELEMENT'])
	{
		$arResult['ERROR_MESSAGE'] = GetMessage("WIKI_DELETE_ERROR");
		$arParams['ELEMENT_NAME'] = "";

	}
	else
		$arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME'];
}

$bNotPage = true;
// localize the name of the stream
$sPageName = CWikiUtils::htmlspecialcharsback($arParams['ELEMENT_NAME']);
$sCatName = '';
if (CWikiUtils::IsCategoryPage($sPageName, $sCatName))
{
	$sPageName = preg_replace('/^category:/i'.BX_UTF_PCRE_MODIFIER, GetMessage('CATEGORY_NAME').':', $sPageName);
	$arParams['ELEMENT_NAME'] = CWikiUtils::UnlocalizeCategoryName($arParams['ELEMENT_NAME']);
}

if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter, $arParams)) != false)
{
	$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
	if ($arResult['WIKI_oper'] != 'delete')
	{
		if ($arResult['ELEMENT']['ACTIVE'] == 'N')
			$arResult['WIKI_oper'] = 'add';
		else if ($arResult['WIKI_oper'] == 'add')
			$bNotPage = false;
	}
}
elseif($arResult['WIKI_oper'] != 'delete')
	$arResult['WIKI_oper'] = 'add';

CUtil::InitJSCore(array('window', 'ajax'));

if ((empty($arResult['ELEMENT']) || !$bNotPage ) && $arResult['WIKI_oper']!="delete" && $arResult['WIKI_oper']!="rename" && $arResult['WIKI_oper']!="rename_it")
{
	if ($arResult['WIKI_oper'] == 'add')
	{
		// Check name
		if (!$bNotPage)
		{
			$i = 2;
			$strName = $arParams['ELEMENT_NAME']." ($i)";
			while(CWiki::GetElementByName($strName, $arFilter) !== false)
			{
				$i++;
				$strName = $arParams['ELEMENT_NAME']." ($i)";
			}
			$arParams['ELEMENT_NAME'] = $strName;
			$sPageName .= " ($i)";
		}

		// Create a temporary item
		$sPageDecoded = CWikiUtils::htmlspecialcharsback(htmlspecialcharsbx($sPageName), false);
		$arFields=array(
			'NAME'		=> CWikiUtils::htmlspecialcharsback($arParams['ELEMENT_NAME']),
			'IBLOCK_ID'	=> $arParams['IBLOCK_ID'],
			'IBLOCK_TYPE'	=> $arParams['IBLOCK_TYPE'],
			'DETAIL_TEXT_TYPE' => $arResult['ALLOW_HTML'] == 'Y' ? 'html' : 'text',
			'DETAIL_TEXT' => GetMessage('WIKI_DEFAULT_DETAIL_TEXT', array(
				'%HEAD%' => $arResult['ALLOW_HTML'] == 'Y' ? '<h1>'.$sPageDecoded.'</h1>' : '= '.$sPageDecoded.' =',
				'%NEWLINE%' => $arResult['ALLOW_HTML'] == 'Y' ? '<br />' : "\n"
			)),
			'~DETAIL_TEXT' => GetMessage('WIKI_DEFAULT_DETAIL_TEXT', array(
				'%HEAD%' => $arResult['ALLOW_HTML'] == 'Y' ? '<h1>'.$sPageDecoded.'</h1>' : '= '.$sPageDecoded.' =',
				'%NEWLINE%' => $arResult['ALLOW_HTML'] == 'Y' ? '<br />' : "\n"
			)),
			'ACTIVE' => 'N',
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE']
		);

		$arParams['ELEMENT_ID'] = $CWiki->Add($arFields);

		$arResult['ELEMENT'] = 	$arFields;
		$arResult['ELEMENT']['ID'] = $arParams['ELEMENT_ID'];
		$sPageName = $arResult['ELEMENT']['NAME'];
		if (CWikiUtils::IsCategoryPage($sPageName, $sCatName))
			$sPageName = preg_replace('/^category:/i'.BX_UTF_PCRE_MODIFIER, GetMessage('CATEGORY_NAME').':', $sPageName);
	}
	else
	{
		$arResult['ELEMENT']['NAME'] = $arParams['ELEMENT_NAME'];
		$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_PAGE_NOT_FIND');
	}

	$arResult['WIKI_oper'] = 'edit';
}

$arResult['ELEMENT']['NAME_LOCALIZE'] = CWikiUtils::htmlspecialcharsback($sPageName);
$arResult['PAGE_VAR'] = $arParams['PAGE_VAR'];
$arResult['OPER_VAR'] = $arParams['OPER_VAR'];

$arResult['PATH_TO_POST_EDIT'] = CHTTP::urlAddParams(
	CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'],
		array(
			'wiki_name' => $arResult['ELEMENT']['NAME'],
			'group_id' => CWikiSocnet::$iSocNetId
		)
	),
	$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'rename_it') : array()
);

$arResult['PATH_TO_DELETE'] = CHTTP::urlAddParams(
	CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'],
		array(
			'wiki_name' => $arResult['ELEMENT']['ID'],
			'group_id' => CWikiSocnet::$iSocNetId
		)
	),
	$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'delete') : array()
);

if (CWikiSocnet::IsSocNet())
{
	$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
	$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
}

$rsTree = CIBlockSection::GetList(Array('left_margin' => 'asc'), $arFilter);
$arTree = array('-1' => GetMessage('WIKI_CHOISE_CATEGORY'));
$_iLevel = 0;
while($arElement = $rsTree->GetNext())
{
	$_iLevel = (int)$arElement['DEPTH_LEVEL'] - (CWikiSocnet::IsSocNet() ? 2 : 1);
	$_sSeparator = '';
	if ($_iLevel > 0)
		$_sSeparator  = str_pad('', $_iLevel, '--');
	$arTree[$arElement['NAME']] = $_sSeparator.CWikiUtils::htmlspecialcharsback($arElement['NAME'], false);
}
$arResult['TREE'] = $arTree;

if (($arResult['DEL_DIALOG'] != 'Y'  && $arResult['WIKI_oper'] == 'delete'))
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	$this->IncludeComponentTemplate('dialog_delete');
	die();
}
else if ($arResult['WIKI_oper'] == 'rename')
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	$this->IncludeComponentTemplate('dialog_rename');
	die();
}
else if ($arResult['IMAGE_UPLOAD'] == 'Y' && isset($_POST['do_upload']))
{
	$APPLICATION->RestartBuffer();
	header("Content-Type: application/json");
	header("Pragma: no-cache");
	$uploadResult = array();
	if (!empty($_FILES['FILE_ID']) && $_FILES['FILE_ID']['size'] > 0)
	{
		$file = $_FILES['FILE_ID'];
		$file['name'] =  Encoding::convertEncoding($file['name'], 'UTF-8', Context::getCurrent()->getCulture()->getCharset());
		$iCheckResult = CFile::CheckImageFile($file);
		if ($iCheckResult == '')
		{
			$_imgID = $CWiki->addImage($arParams['ELEMENT_ID'], $arParams['IBLOCK_ID'], $file);
			if($_imgID !== false)
			{
				$rsFile = CFile::GetByID($_imgID);
				$arFile = $rsFile->Fetch();
				$uploadResult['IMAGE'] = array(
						'ID' => $_imgID,
						'ORIGINAL_NAME' => $arFile['ORIGINAL_NAME'],
						'FILE_SHOW' => CFile::ShowImage($_imgID, 100, 100, "id=\"$_imgID\" border=\"0\" style=\"cursor:pointer;\" onclick=\"wikiMainEditor.doInsert('[File:".CUtil::JSEscape($arFile['ORIGINAL_NAME'])."]', '' ,false, '$_imgID')\" title=\"".GetMessage('WIKI_IMAGE_INSERT')."\"")
				);
			}
			else
			{
				$lastError = array_pop($CWiki->getErrors()->toArray());
				$uploadResult['ERROR_MESSAGE'] = $lastError->getMessage();
			}
		}
		else
			$uploadResult['ERROR_MESSAGE'] = GetMessage('WIKI_IMAGE_UPLOAD_ERROR');
	}
	else
		$uploadResult['ERROR_MESSAGE'] = GetMessage('WIKI_IMAGE_UPLOAD_ERROR');


	echo Bitrix\Main\Web\Json::encode($uploadResult);
	die();
}
else
{
	//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks(array('article', $arResult['WIKI_oper']), $arParams);
	$CWikiParser = new CWikiParser();
	if (( $_SERVER['REQUEST_METHOD'] == 'POST' || $arResult['DEL_DIALOG'] == 'Y') && ((isset($_POST['apply']) || isset($_REQUEST['save']) && !isset($_POST['preview'])))) //$_SERVER['REQUEST_METHOD'] == 'POST'
	{
		if (check_bitrix_sessid())
		{
			// checking the data entered
			$arFields = Array();

			switch ($arResult['WIKI_oper'])
			{
				case 'edit':
				case 'add':
				default: //add

					$arFields = array(
						'DETAIL_TEXT'		=> $arResult['ALLOW_HTML'] == 'Y' && $_POST['POST_MESSAGE_TYPE'] == 'html' ? $_POST['POST_MESSAGE_HTML'] : CWikiUtils::htmlspecialchars_decode($_POST['POST_MESSAGE']),
						'DETAIL_TEXT_TYPE'	=> $arResult['ALLOW_HTML'] == 'Y' ? $_POST['POST_MESSAGE_TYPE'] : 'text',
						'TAGS' => $_POST['TAGS'],
						'MODIFY_COMMENT' => $_POST['MODIFY_COMMENT'],
						'IBLOCK_ID'		=> $arParams['IBLOCK_ID'],
						'IBLOCK_TYPE'		=> $arParams['IBLOCK_TYPE'],
						'ACTIVE' => 'Y',
						'NAME_TEMPLATE'		=> $arParams['NAME_TEMPLATE']
					);

					if (isset($_POST['POST_TITLE']))
					{
						$arFields['NAME'] = CWikiUtils::htmlspecialchars_decode($_POST['POST_TITLE']);
						$sCatName = '';
						if (CWikiUtils::IsCategoryPage($arFields['NAME'] , $sCatName))
							$arFields['NAME'] = CWikiUtils::UnlocalizeCategoryName($sPageName);

						if (empty($_POST['POST_TITLE']))
						{
							$arFields['NAME'] = $arParams['ELEMENT_NAME'] = $_POST['POST_TITLE'] = $arResult['ELEMENT']['NAME_LOCALIZE'];
							$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_NAME_EMPTY');
						}
						else if (mb_strpos($_POST['POST_TITLE'], '/') !== false)
						{
							$arFields['NAME'] = $arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME_LOCALIZE'];
							$arResult['ELEMENT']['NAME_LOCALIZE'] = $_POST["POST_TITLE"];

							$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_NAME_BAD_SYMBOL');
						}
						else
						{
							if (!$CWiki->Rename($arParams['ELEMENT_ID'], array('NAME' => $arFields['NAME'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']),false))
								$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_RENAME');
							else
								$arParams['ELEMENT_NAME'] = $arFields['NAME'];
						}
					}

					if (empty($arFields['DETAIL_TEXT']))
						$arResult['ERROR_MESSAGE'] .= (!empty($arResult['ERROR_MESSAGE']) ? '<br />' : '').GetMessage('WIKI_ERROR_TEXT_EMPTY');

					$arResult['ELEMENT']['NAME'] = CWikiUtils::htmlspecialcharsback($_POST['POST_TITLE']);
					$arResult['ELEMENT']['DETAIL_TEXT'] = $arResult['ELEMENT']['~DETAIL_TEXT'] = $arFields['DETAIL_TEXT'];
					$arResult['ELEMENT']['DETAIL_TEXT_TYPE'] = $arFields['DETAIL_TEXT_TYPE'];

					if (empty($arResult['ERROR_MESSAGE']))
					{
						if(is_array($_POST['IMAGE_ID_del']))
						{
							foreach($_POST['IMAGE_ID_del'] as $_imgID => $_)
							{
								if (in_array($_imgID, $arResult['ELEMENT']['IMAGES']))
								{
									$rsFile = CFile::GetByID($_imgID);
									$arFile = $rsFile->Fetch();
									$CWiki->deleteImage($_imgID, $arResult['ELEMENT']['ID'], $arParams['IBLOCK_ID']);
									$arFields['DETAIL_TEXT'] = preg_replace('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):('.$_imgID.'|'.preg_quote($arFile['ORIGINAL_NAME'], '/').')\]\]?/iU'.BX_UTF_PCRE_MODIFIER, '', $arFields['DETAIL_TEXT']);
								}
							}
						}

						$CWikiParser = new CWikiParser();

						$CWiki->Update($arParams['ELEMENT_ID'], $arFields);

						//we should not post content of wiki page to feed if it redirects to another page
						if(preg_match("/^\#(REDIRECT|".GetMessage('WIKI_REDIRECT').")\s*\[\[(.*)\]\]/iU".BX_UTF_PCRE_MODIFIER, $arFields['DETAIL_TEXT']))
							$bPageRedirect = true;
						else
							$bPageRedirect = false;

						if(CWikiSocnet::IsSocNet() && $arResult['POST_TO_FEED'] == "Y" && !$bPageRedirect)
						{
							if ($arParams['SOCNET_GROUP_ID'] <> '')
								CSocNetGroup::SetLastActivity(intval($arParams['SOCNET_GROUP_ID']));

							$postUrl = str_replace(
											array('#group_id#', '#wiki_name#'),
											array(intval($arParams['SOCNET_GROUP_ID']), rawurlencode($arFields['NAME'])),
											$arParams['~PATH_TO_POST']
										);

							$arCurImages = array();
							$rsProperties = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arParams['ELEMENT_ID'], 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
							while($arProperty = $rsProperties->Fetch())
							{
								if($arProperty['CODE'] == 'IMAGES')
								{
									$arCurImages[] = $arProperty['VALUE'];
								}
							}

							$arCat = array();
							$text4message = $CWikiParser->parseBeforeSave($arFields['DETAIL_TEXT'], $arCat);
							$text4message =  $CWikiParser->Parse($text4message, $arFields['DETAIL_TEXT_TYPE'], $arCurImages);
							$text4message = CWikiSocnet::PrepareTextForFeed($text4message);
							$text4message = $CWikiParser->Clear($text4message);

							//while CSocNetTextParser::closetags closes <br> by </br> must be corrected soon. Then remove this.
							$text4message = preg_replace("/<\s*br\s*>/ismU", "<br />", $text4message);

							$bNew = true;

							$notify_title_tmp = str_replace(Array("\r\n", "\n"), " ", $arFields["NAME"]);
							$notify_title = TruncateText($notify_title_tmp, 100);
							$notify_title_out = TruncateText($notify_title_tmp, 255);

							if ($arResult['WIKI_oper'] == 'edit')
							{
								$dbLog = CSocNetLog::GetList(
									array('ID' => 'DESC'),
									array(
										'SOURCE_ID' => $arParams['ELEMENT_ID'],
										'EVENT_ID'=> 'wiki'
								));

								if ($arLog = $dbLog->Fetch())
								{
									$bNew = false;
									$arSoFields = Array(
										'=LOG_DATE' => $GLOBALS['DB']->CurrentTimeFunction(),
										'=LOG_UPDATE' => $GLOBALS['DB']->CurrentTimeFunction(),
										'USER_ID' => $GLOBALS['USER']->GetID(),
										'TITLE' => $arFields['NAME'],
										'MESSAGE' => $text4message,
										'TEXT_MESSAGE' => "\n".GetMessage('WIKI_MODIFY_COMMENT').": ".($_POST['MODIFY_COMMENT'] ? $_POST['MODIFY_COMMENT'] : ' '.GetMessage('WIKI_MODIFY_COMMENT_ABSENT'))."\n",
										'URL' => $postUrl
									);

									if ($arFields['TAGS'] !== '')
									{
										$arSoFields['TAG'] = [];
										$tagsList = explode(',', $arFields['TAGS']);
										foreach($tagsList as $tag)
										{
											$tag = trim($tag);
											if ($tag === '')
											{
												continue;
											}
											$arSoFields['TAG'][] = $tag;
										}
									}

									$logID = CSocNetLog::Update($arLog['ID'], $arSoFields);
									if (intval($logID) > 0)
									{
										CSocNetLogRights::SetForSonet($arLog['ID'], SONET_SUBSCRIBE_ENTITY_GROUP, intval($arParams['SOCNET_GROUP_ID']), "wiki", "view");
										CSocNetLog::CounterIncrement($logID);

										$arNotifyParams = array(
											"LOG_ID" => $logID,
											"GROUP_ID" => intval($arParams['SOCNET_GROUP_ID']),
											"NOTIFY_MESSAGE" => "",
											"FROM_USER_ID" => $arSoFields["USER_ID"],
											"URL" => $arSoFields["URL"],
											"MESSAGE" => GetMessage("WIKI_SONET_IM_EDIT", Array(
												"#title#" => "<a href=\"#URL#\" class=\"bx-notifier-item-action\">".$notify_title."</a>",
											)),
											"MESSAGE_OUT" => GetMessage("WIKI_SONET_IM_EDIT", Array(
												"#title#" => $notify_title_out
											))." (#URL#)",
											"EXCLUDE_USERS" => array($arSoFields["USER_ID"])
										);

										CSocNetSubscription::NotifyGroup($arNotifyParams);
									}
								}
							}

							if ($bNew)
							{
								$arSoFields = Array(
									'ENTITY_TYPE' => SONET_SUBSCRIBE_ENTITY_GROUP,
									'IS_CUSTOM_ET' => 'N',
									'ENTITY_ID' => intval($arParams['SOCNET_GROUP_ID']),
									'EVENT_ID' => 'wiki',
									'USER_ID' => $GLOBALS['USER']->GetID(),
									'=LOG_DATE' => $GLOBALS['DB']->CurrentTimeFunction(),
									'TITLE_TEMPLATE' => GetMessage('WIKI_SONET_LOG_TITLE_TEMPLATE'),
									'TITLE' => $arFields['NAME'],
									'MESSAGE' => $text4message,
									'TEXT_MESSAGE' => '',
									'SITE_ID' =>$arGroupSite['SITE_ID'],
									'MODULE_ID' => 'wiki',
									'URL' => str_replace(
										array('#group_id#', '#wiki_name#'),
										array(intval($arParams['SOCNET_GROUP_ID']), rawurlencode($arFields['NAME'])),
										$arParams['~PATH_TO_POST']
									),
									'CALLBACK_FUNC' => false,
									'SOURCE_ID' => $arParams['ELEMENT_ID'],
									'PARAMS' => 'forum_id='.intval(COption::GetOptionString('wiki', 'socnet_forum_id')),
									'RATING_TYPE_ID' => 'IBLOCK_ELEMENT',
									'RATING_ENTITY_ID' => intval($arParams['ELEMENT_ID'])
								);

								if ($arFields['TAGS'] !== '')
								{
									$arSoFields['TAG'] = [];
									$tagsList = explode(',', $arFields['TAGS']);
									foreach($tagsList as $tag)
									{
										$tag = trim($tag);
										if ($tag === '')
										{
											continue;
										}
										$arSoFields['TAG'][] = $tag;
									}
								}

								$logID = CSocNetLog::Add($arSoFields, false);

								if (intval($logID) > 0)
								{
									CSocNetLog::Update($logID, array('TMP_ID' => $logID));
									CSocNetLogRights::SetForSonet($logID, SONET_SUBSCRIBE_ENTITY_GROUP, intval($arParams['SOCNET_GROUP_ID']), "wiki", "view", true);
									CSocNetLog::CounterIncrement($logID);

									$arNotifyParams = array(
										"LOG_ID" => $logID,
										"GROUP_ID" => intval($arParams['SOCNET_GROUP_ID']),
										"NOTIFY_MESSAGE" => "",
										"FROM_USER_ID" => $arSoFields["USER_ID"],
										"URL" => $arSoFields["URL"],
										"MESSAGE" => GetMessage("WIKI_SONET_IM_ADD", Array(
											"#title#" => "<a href=\"#URL#\" class=\"bx-notifier-item-action\">".$notify_title."</a>",
										)),
										"MESSAGE_OUT" => GetMessage("WIKI_SONET_IM_ADD", Array(
											"#title#" => $notify_title_out
										))." (#URL#)",
										"EXCLUDE_USERS" => array($arSoFields["USER_ID"])
									);

									CSocNetSubscription::NotifyGroup($arNotifyParams);
								}
							}

						}
					}
					$arResult['ELEMENT'] = $arFields + $arResult['ELEMENT'];
					break;

				case 'edit_title':
					break;

				case 'delete':

					if(CWikiSocnet::IsSocNet())
					{
						$dbResTmp = CIBlockElement::GetByID($arParams['ELEMENT_ID']);

						if($arResTmp = $dbResTmp->GetNext())
							$strTitleTmp = $arResTmp['NAME'];

						$dbLog = CSocNetLog::GetList(
										array('ID' => 'DESC'),
										array(
											'EVENT_ID' => 'wiki',
											'SOURCE_ID' => $arParams['ELEMENT_ID']
										)
						);
						$arLog = $dbLog->Fetch();
					}

					$CWiki->Delete($arParams['ELEMENT_ID'], $arParams['IBLOCK_ID']);

					if($strTitleTmp <> '' && isset($arLog['ID']) && CWikiSocnet::IsSocNet())
					{
						$arSoFields = Array(
							'ENTITY_TYPE' => SONET_SUBSCRIBE_ENTITY_GROUP,
							'IS_CUSTOM_ET' => 'N',
							'ENTITY_ID' => intval($arParams['SOCNET_GROUP_ID']),
							'EVENT_ID' => 'wiki_del',
							'USER_ID' => $GLOBALS['USER']->GetID(),
							'=LOG_DATE' => $GLOBALS['DB']->CurrentTimeFunction(),
							'TITLE_TEMPLATE' => GetMessage('WIKI_DEL_SONET_LOG_TITLE_TEMPLATE'),
							'TITLE' => $strTitleTmp,
							'MESSAGE' => '',
							'TEXT_MESSAGE' => '',
							'MODULE_ID' => 'wiki',
							'URL' => '',
							'CALLBACK_FUNC' => false,
							'SOURCE_ID' => $arParams['ELEMENT_ID'],
							'RATING_TYPE_ID' => 'WIKI_'.$arParams['IBLOCK_ID'].'_PAGE',
							'RATING_ENTITY_ID' => intval($arParams['ELEMENT_ID'])
						);

						$logID = CSocNetLog::Update($arLog['ID'], $arSoFields);
						if (intval($logID) > 0)
						{
							CSocNetLogRights::SetForSonet($arLog['ID'], SONET_SUBSCRIBE_ENTITY_GROUP, intval($arParams['SOCNET_GROUP_ID']), "wiki", "view");
							CSocNetLog::CounterIncrement($logID);

							$notify_title_tmp = str_replace(Array("\r\n", "\n"), " ", $strTitleTmp);
							$notify_title = TruncateText($notify_title_tmp, 100);
							$notify_title_out = TruncateText($notify_title_tmp, 255);

							$arNotifyParams = array(
								"LOG_ID" => $logID,
								"GROUP_ID" => intval($arParams['SOCNET_GROUP_ID']),
								"NOTIFY_MESSAGE" => "",
								"FROM_USER_ID" => $arSoFields["USER_ID"],
								"URL" => "",
								"MESSAGE" => GetMessage("WIKI_SONET_IM_DELETE", Array(
									"#title#" => $notify_title,
								)),
								"MESSAGE_OUT" => GetMessage("WIKI_SONET_IM_DELETE", Array(
									"#title#" => $notify_title_out
								)),
								"EXCLUDE_USERS" => array($arSoFields["USER_ID"])
							);

							CSocNetSubscription::NotifyGroup($arNotifyParams);	
						}
					}
					break;

					case 'rename_it':
					/*rename element, and renew all links directed on this page and/or category*/
						$newName = $_POST["NEW_NAME"];

						if(!isset($newName)
							|| $newName ==""
							|| $newName == $arResult['ELEMENT']['NAME_LOCALIZE']
						)
							break;

						$sCatName = '';
						if (CWikiUtils::IsCategoryPage($arParams['ELEMENT_NAME'] , $sCatName))
							$newName = 'category:'.$newName;

						if (!$CWiki->Rename($arParams['ELEMENT_ID'], array('NAME' => $newName, 'IBLOCK_ID' => $arParams['IBLOCK_ID']),true))
						{
							$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_RENAME');
							break;
						}

						$iBlockSectId = CWikiSocnet::$iCatId ? CWikiSocnet::$iCatId : false;
						$CWiki->RenameLinkOnPages($arParams['IBLOCK_ID'], $arParams['ELEMENT_NAME'], $newName, $iBlockSectId);

						//post to feed
						if(CWikiSocnet::IsSocNet())
						{
							$postUrl = str_replace(
											array('#group_id#', '#wiki_name#'),
											array(intval($arParams['SOCNET_GROUP_ID']), rawurlencode($newName)),
											$arParams['~PATH_TO_POST']
										);

							$dbLog = CSocNetLog::GetList(
								array('ID' => 'DESC'),
								array(
									'EVENT_ID' => 'wiki',
									'SOURCE_ID' => $arParams['ELEMENT_ID']
								)
							);
							if ($arLog = $dbLog->Fetch())
							{
								$arSoFields = Array(
									'=LOG_DATE' => $GLOBALS['DB']->CurrentTimeFunction(),
									'=LOG_UPDATE' => $GLOBALS['DB']->CurrentTimeFunction(),
									'USER_ID' => $GLOBALS['USER']->GetID(),
									'TITLE' => $newName,
									'TEXT_MESSAGE' => "\n".GetMessage('WIKI_MODIFY_COMMENT').": ".GetMessage('WIKI_PAGE_RENAMED',array("%OLD_NAME%"=>$arParams['ELEMENT_NAME'], "%NEW_NAME%"=>$newName))."\n",
									'URL' => $postUrl
								);

								$logID = CSocNetLog::Update($arLog['ID'], $arSoFields);
								if (intval($logID) > 0)
								{
									CSocNetLogRights::SetForSonet($arLog['ID'], SONET_SUBSCRIBE_ENTITY_GROUP, intval($arParams['SOCNET_GROUP_ID']), "wiki", "view");
									CSocNetLog::CounterIncrement($logID);

									$notify_title_tmp = str_replace(Array("\r\n", "\n"), " ", $arLog["TITLE"]);
									$notify_title_old = TruncateText($notify_title_tmp, 100);
									$notify_title_old_out = TruncateText($notify_title_tmp, 255);

									$notify_title_tmp = str_replace(Array("\r\n", "\n"), " ", $newName);
									$notify_title_new = TruncateText($notify_title_tmp, 100);
									$notify_title_new_out = TruncateText($notify_title_tmp, 255);

									$arNotifyParams = array(
										"LOG_ID" => $logID,
										"GROUP_ID" => intval($arParams['SOCNET_GROUP_ID']),
										"NOTIFY_MESSAGE" => "",
										"FROM_USER_ID" => $arSoFields["USER_ID"],
										"URL" => $arSoFields["URL"],
										"MESSAGE" => GetMessage("WIKI_SONET_IM_RENAME", Array(
											"#title_old#" => $notify_title_old,
											"#title_new#" => "<a href=\"#URL#\" class=\"bx-notifier-item-action\">".$notify_title_new."</a>"
										)),
										"MESSAGE_OUT" => GetMessage("WIKI_SONET_IM_RENAME", Array(
											"#title_old#" => $notify_title_old_out,
											"#title_new#" => $notify_title_new_out
										))." (#URL#)",
										"EXCLUDE_USERS" => array($arSoFields["USER_ID"])
									);

									CSocNetSubscription::NotifyGroup($arNotifyParams);	
								}
							}
						}

						$arParams['ELEMENT_NAME'] = $newName;

					break;
			}
			if (empty($arResult['ERROR_MESSAGE']))
			{
				if (!isset($_POST['apply']))
					LocalRedirect(CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'],
						array(
							'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
							'group_id' => CWikiSocnet::$iSocNetId,
						)),
						array('wiki_page_cache_clear' => 'Y'))
					);
				else
					LocalRedirect(CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'],
						array(
							'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
							'group_id' => CWikiSocnet::$iSocNetId
						)),
						$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => $arResult['WIKI_oper']) : array())
					);
			}
			else
				$arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME'];
		}
		else
		{
			$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_SESS_TIMEOUT');
			$arResult['ELEMENT']['DETAIL_TEXT']  =
			$arResult['ELEMENT']['~DETAIL_TEXT'] = $arResult['ALLOW_HTML'] == 'Y' && $_POST['POST_MESSAGE_TYPE'] == 'html' ? $_POST['POST_MESSAGE_HTML'] : $_POST['POST_MESSAGE'];
		}
	}
	else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['preview']))
	{
		if (check_bitrix_sessid())
		{
			if (isset($_POST['POST_TITLE']))
			{
				$arResult['ELEMENT']['NAME_LOCALIZE'] = CWikiUtils::htmlspecialchars_decode($_POST['POST_TITLE']);
				$sCatName = '';
				if (CWikiUtils::IsCategoryPage($arFields['NAME'] , $sCatName))
					$arResult['ELEMENT']['NAME_LOCALIZE'] = CWikiUtils::UnlocalizeCategoryName($sPageName);

				if (empty($_POST['POST_TITLE']))
				{
					$arFields['NAME'] = $arParams['ELEMENT_NAME'] = $_POST['POST_TITLE'] = $arResult['ELEMENT']['NAME_LOCALIZE'];
					$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_NAME_EMPTY');
				}
			}


			$arResult['ELEMENT']['~DETAIL_TEXT'] = $arResult['ALLOW_HTML'] == 'Y' && $_POST['POST_MESSAGE_TYPE'] == 'html' ? $_POST['POST_MESSAGE_HTML'] : $_POST['POST_MESSAGE'];
			$arResult['ELEMENT']['DETAIL_TEXT_TYPE'] = $arResult['ALLOW_HTML'] == 'Y' ? $_POST['POST_MESSAGE_TYPE'] : 'text';

			$arResult['PREVIEW'] = 'Y';
			$arResult['ELEMENT_PREVIEW'] = array();
			$arCat = array();

			$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'] = $CWikiParser->parseBeforeSave($arResult['ELEMENT']['~DETAIL_TEXT'], $arCat);
			$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'] = $CWikiParser->Parse($arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'], $arResult['ELEMENT']['DETAIL_TEXT_TYPE'], $arResult['ELEMENT']['IMAGES']);
			$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'] = $CWikiParser->Clear($arResult['ELEMENT_PREVIEW']['DETAIL_TEXT']);

			$arResult['ELEMENT']['TAGS'] = htmlspecialcharsbx($_POST['TAGS']);
			$arResult['ELEMENT']['~TAGS'] = htmlspecialcharsbx($_POST['TAGS']);
		}
	}

	// obtain a list of pictures page
	$arResult['IMAGES'] = array();
	if (!empty($arResult['ELEMENT']['IMAGES']))
	{
		foreach ($arResult['ELEMENT']['IMAGES'] as $_imgID)
		{
			$rsFile = CFile::GetByID($_imgID);
			$arFile = $rsFile->Fetch();
			$aImg = array();
			$aImg['ID'] = $_imgID;
			$aImg['ORIGINAL_NAME'] = $arFile['ORIGINAL_NAME'];
			$aImg['FILE_SHOW'] = CFile::ShowImage($_imgID, 100, 100, "id=\"$_imgID\" border=\"0\" style=\"cursor:pointer;\" onclick=\"wikiMainEditor.doInsert('[File:".CUtil::JSEscape(htmlspecialcharsbx($arFile['ORIGINAL_NAME']))."]','',false, '$_imgID')\" title='".GetMessage('WIKI_IMAGE_INSERT')."'");
			$arResult['IMAGES'][] = $aImg;
		}
	}

	include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

	$arResult['PATH_TO_POST_EDIT'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'],
			array(
				'wiki_name' => rawurlencode($arParams['ELEMENT_NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => $arResult['WIKI_oper']) : array()
	);
	$arResult['~PATH_TO_POST_EDIT'] = rawurldecode($arResult['PATH_TO_POST_EDIT']);

	//because it can change the page name, and hence the path for the parameter "Action" in tag "Form"
	if (mb_strpos(POST_FORM_ACTION_URI, 'SEF_APPLICATION_CUR_PAGE_URL=') !== false)
	{
		$arResult['PATH_TO_POST_EDIT_SUBMIT'] = CHTTP::urlAddParams(
			CHTTP::urlDeleteParams(POST_FORM_ACTION_URI, array('SEF_APPLICATION_CUR_PAGE_URL')),
			array('SEF_APPLICATION_CUR_PAGE_URL' => rawurlencode($arResult['~PATH_TO_POST_EDIT']))
		);
	}
	else
		$arResult['PATH_TO_POST_EDIT_SUBMIT'] = $arResult['PATH_TO_POST_EDIT'];

	$sCatName = '';
	$arResult["IS_CATEGORY_PAGE"] = CWikiUtils::IsCategoryPage($arResult['ELEMENT']['NAME_LOCALIZE'], $sCatName);
}

$this->IncludeComponentTemplate();
unset($GLOBALS['arParams']);
