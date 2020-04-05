<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['USE_REVIEW']))
	$arParams['USE_REVIEW'] = 'Y';

if ($arParams['IN_COMPLEX'] == 'Y' && strpos($this->GetParent()->GetName(), 'socialnetwork') === false)
	$arParams['USE_REVIEW'] = $this->GetParent()->arResult['USE_REVIEW'];

if($arParams['USE_REVIEW'] == 'N')
	return;

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

if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}

if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if(strlen($arParams['PATH_TO_POST_EDIT'])<=0)
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if(strlen($arParams['PATH_TO_HISTORY'])<=0)
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if(strlen($arParams['PATH_TO_HISTORY_DIFF'])<=0)
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if(strlen($arParams['PATH_TO_DISCUSSION'])<=0)
{
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_DISCUSSION'] = $this->GetParent()->arResult['PATH_TO_DISCUSSION'];
}

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

$GLOBALS['arParams'] = $arParams;

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('forum'))
{
	ShowError(GetMessage('FORUM_MODULE_NOT_INSTALLED'));
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

	CWikiUtils::SetCommentPath(COption::GetOptionString('wiki', 'socnet_forum_id'),"wiki/comment/#MESSAGE_ID#/","/workgroups/index.php"); 		//http://jabber.bx/view.php?id=25340	todo: /workgroups/index.php => settings
}
else
	CWikiUtils::SetCommentPath($arParams['FORUM_ID'],"comment/#MESSAGE_ID#/","/services/wiki.php"); 	//todo: /workgroups/index.php => settings


if (!CWikiUtils::IsReadable())
{
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;
}

$arParams['ELEMENT_NAME'] = rawurldecode($arParams['ELEMENT_NAME']);
$arFilter = array(
//	'IBLOCK_LID' => SITE_ID,
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'CHECK_PERMISSIONS' => 'N',
//	'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE']
);

if (!CWikiSocnet::isEnabledSocnet() || empty($arParams['SOCNET_GROUP_ID']))
{
	$arFilter['IBLOCK_LID'] = SITE_ID;
	$arFilter['IBLOCK_TYPE'] = $arParams['IBLOCK_TYPE'];
}



if (empty($arParams['ELEMENT_NAME']))
	$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);

$arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter);

if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] != false))
{
	$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
	if (
		CWikiSocnet::isEnabledSocnet()
		&& !empty($arParams['SOCNET_GROUP_ID'])
		&& array_key_exists('FORUM_TOPIC_ID', $arResult['ELEMENT'])
		&& intval($arResult['ELEMENT']['FORUM_TOPIC_ID']) > 0
		&& CModule::IncludeModule("forum")
	)
	{
		$arForumTopic = CForumTopic::GetByID($arResult['ELEMENT']['FORUM_TOPIC_ID']);
		if ($arForumTopic)
			$arParams['FORUM_ID'] = $arForumTopic['FORUM_ID'];
	}



}
else
	return ;

//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks('discussion', $arParams);
$arResult['CACHE_TYPE'] = $arParams['CACHE_TYPE'];
$arResult['CACHE_TIME'] = $arParams['CACHE_TIME'];
$arResult['MESSAGES_PER_PAGE'] = $arParams['MESSAGES_PER_PAGE'];
$arResult['USE_CAPTCHA'] = $arParams['USE_CAPTCHA'];
$arResult['PATH_TO_SMILE'] = '/bitrix/images/forum/smile/'; //$arParams['PATH_TO_SMILE'];
$arResult['URL_TEMPLATES_READ'] = $arParams['URL_TEMPLATES_READ'];
$arResult['SHOW_LINK_TO_FORUM'] = $arParams['SHOW_LINK_TO_FORUM'] == 'Y' ? 'Y' : 'N';
$arResult['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
$arResult['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
$arResult['FORUM_ID'] = $arParams['FORUM_ID'];
$arResult['POST_FIRST_MESSAGE'] = $arParams['POST_FIRST_MESSAGE'];
$arResult['URL_TEMPLATES_DETAIL'] = $arParams['URL_TEMPLATES_DETAIL'];
$arResult['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'];

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	class CSocNetWikiForumEvent
	{
		var $arPath;
		var $ForumID = null;

		function SetVars($arParams, $arResult)
		{
			$this->arPath['PATH_TO_SMILE'] = $arParams['PATH_TO_SMILE'];
			$this->arPath['PATH_TO_USER'] = $arParams['PATH_TO_USER'];
			$this->arPath['PATH_TO_POST'] = $arParams['~PATH_TO_POST'];
			$this->ForumID = $arParams['FORUM_ID'];
			$this->SonetGroupID = $arParams['SOCNET_GROUP_ID'];
		}

		private function createLogEntry($iblockElementID)
		{
			$rsElement = CIBlockElement::GetByID($iblockElementID);
			if ($arElement = $rsElement->Fetch())
			{
				$arWikiElement = CWiki::GetElementById($arElement['ID'], array('IBLOCK_ID' => $arElement['IBLOCK_ID']));

				$CWikiParser = new CWikiParser();
				$parserLog = new logTextParser();
				$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N", "TABLE" => "N");

				$text4message = $CWikiParser->Parse($arElement['DETAIL_TEXT'], $arElement['DETAIL_TEXT_TYPE'], $arWikiElement['IMAGES']);
				$text4message = preg_replace("#<br[\s]*\/>#is", "#BR#", $text4message);
				$text4message = htmlspecialcharsback($parserLog->convert($text4message, array(), $arAllow));
				$text4message = preg_replace("#\#BR\##is", "\n", $text4message);
				$text4message = $CWikiParser->Clear($text4message);

				$url = str_replace(
					array('#group_id#', '#wiki_name#'),
					array(intval($this->SonetGroupID), rawurlencode($arElement['NAME'])),
					$this->arPath['PATH_TO_POST']
				);

				$arSoFields = Array(
					'ENTITY_TYPE' => SONET_SUBSCRIBE_ENTITY_GROUP,
					'IS_CUSTOM_ET' => 'N',
					'ENTITY_ID' => intval($this->SonetGroupID),
					'EVENT_ID' => 'wiki',
					'USER_ID' => $arElement['CREATED_BY'],
					'LOG_DATE' => $arElement['DATE_CREATE'],
					'LOG_UPDATE' => $arElement['DATE_CREATE'],
					'TITLE_TEMPLATE' => GetMessage('WIKI_SONET_LOG_TITLE_TEMPLATE'),
					'TITLE' => $arElement['NAME'],
					'MESSAGE' => $text4message,
					'TEXT_MESSAGE' => '',
					'MODULE_ID' => 'wiki',
					'URL' => $url,
					'CALLBACK_FUNC' => false,
					'SOURCE_ID' => intval($iblockElementID),
					'PARAMS' => 'forum_id='.$this->ForumID,
					'RATING_TYPE_ID' => 'IBLOCK_ELEMENT',
					'RATING_ENTITY_ID' => intval($iblockElementID)
				);

				$log_id = CSocNetLog::Add($arSoFields, false);
				$log_user_id = $arElement['CREATED_BY'];
				$log_title = $arElement["NAME"];

				if (intval($log_id) > 0)
				{
					CSocNetLog::Update($log_id, array("TMP_ID" => $log_id));
					CSocNetLogRights::SetForSonet($log_id, SONET_SUBSCRIBE_ENTITY_GROUP, intval($this->SonetGroupID), "wiki", "view", true);

					return array(
						"LOG_ID" => $log_id,
						"LOG_USER_ID" => $log_user_id,
						"LOG_TITLE" => $log_title,
						"URL" => $url
					);
				}
			}

			return false;
		}

		private function createLogComment($messageID, $bSocNetLogRecordExists, $iblockElementID, $topicID, $arLogParams)
		{
			$dbComment = CSocNetLogComments::GetList(array(), array('EVENT_ID' => 'wiki_comment', 'SOURCE_ID' => $messageID), false, false, array('ID'));
			if (
				($arComment = $dbComment->Fetch())
				&& intval($arComment['ID']) > 0
			)
			{
				return false;
			}

			$arForum = CForumNew::GetByID($this->ForumID);

			$parser = new forumTextParser(LANGUAGE_ID, $this->arPath['PATH_TO_SMILE']);
			$parser->imageWidth = false;
			$parser->imageHeight = false;

			$arAllow = array(
				'HTML' => "N",
				'ANCHOR' => "N",
				'BIU' => "N",
				'IMG' => "N",
				'LIST' => "N",
				'QUOTE' => "N",
				'CODE' => "N",
				'FONT' => "N",
				'UPLOAD' => $arForum['ALLOW_UPLOAD'],
				'NL2BR' => "N",
				'SMILES' => "N"
			);

			if ($bSocNetLogRecordExists) //if existing record- add only newly added comment
			{
				$arMessage = CForumMessage::GetByIDEx($messageID);
				$sAuthorForMail = str_replace('#TITLE#', $arMessage['AUTHOR_NAME'], GetMessage('SONET_FORUM_LOG_TEMPLATE_GUEST'));

				$parser = new forumTextParser(LANGUAGE_ID, $this->arPath['PATH_TO_SMILE']);
				$parser->imageWidth = false;
				$parser->imageHeight = false;

				if (intVal($arMessage['AUTHOR_ID']) > 0)
					$sAuthorForMail = str_replace(array('#URL#', '#TITLE#'), array('http://'.SITE_SERVER_NAME.CComponentEngine::MakePathFromTemplate(
						$this->arPath['PATH_TO_USER'], array('user_id' => $arMessage['AUTHOR_ID'])), $arMessage['AUTHOR_NAME']),
						GetMessage('SONET_FORUM_LOG_TEMPLATE_AUTHOR'));

				$arFieldsForSocnet = array(
					'ENTITY_TYPE' => SONET_SUBSCRIBE_ENTITY_GROUP,
					'ENTITY_ID' => intval($this->SonetGroupID),
					'EVENT_ID' => 'wiki_comment',
					'=LOG_DATE' => $GLOBALS['DB']->CharToDateFunction($arMessage['POST_DATE'], "FULL", SITE_ID),
					'MESSAGE' => $arMessage['POST_MESSAGE'],
					'TEXT_MESSAGE' => $parser->convert4mail($arMessage['POST_MESSAGE'].$sAuthorForMail),
					'URL' => $arLogParams["URL"],
					'MODULE_ID' => false,
					'SOURCE_ID' => $messageID,
					'LOG_ID' => $arLogParams["LOG_ID"],
					'RATING_TYPE_ID' => 'FORUM_POST',
					'RATING_ENTITY_ID' => intval($arMessage['ID'])
				);

				if (intVal($arMessage['AUTHOR_ID']) > 0)
					$arFieldsForSocnet['USER_ID'] = $arMessage['AUTHOR_ID'];

				$log_comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
				if ($log_comment_id)
				{
					CSocNetLog::CounterIncrement($log_comment_id, false, false, "LC");

					if (
						CModule::IncludeModule("im")
						&& intval($arMessage["AUTHOR_ID"]) > 0
						&& $arMessage["AUTHOR_ID"] != $arLogParams["LOG_USER_ID"]
					)
					{
						$rsUnFollower = CSocNetLogFollow::GetList(
							array(
								"USER_ID" => $arLogParams["LOG_USER_ID"],
								"CODE" => "L".$arLogParams["LOG_ID"],
								"TYPE" => "N"
							),
							array("USER_ID")
						);

						$arUnFollower = $rsUnFollower->Fetch();
						if (!$arUnFollower)
						{
							$arMessageFields = array(
								"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
								"TO_USER_ID" => $arLogParams["LOG_USER_ID"],
								"FROM_USER_ID" => $arMessage["AUTHOR_ID"],
								"NOTIFY_TYPE" => IM_NOTIFY_FROM,
								"NOTIFY_MODULE" => "wiki",
								"NOTIFY_EVENT" => "comment",
							);

							$arParams["TITLE"] = str_replace(Array("\r\n", "\n"), " ", $arLogParams["LOG_TITLE"]);
							$arParams["TITLE"] = TruncateText($arParams["TITLE"], 100);
							$arParams["TITLE_OUT"] = TruncateText($arParams["TITLE"], 255);

							$arTmp = CSocNetLogTools::ProcessPath(array("ELEMENT_URL" => $arLogParams["URL"]), $arLogParams["LOG_USER_ID"]);
							$serverName = $arTmp["SERVER_NAME"];
							$url = $arTmp["URLS"]["ELEMENT_URL"];

							$arMessageFields["NOTIFY_TAG"] = "WIKI|COMMENT|".$iblockElementID;
							$arMessageFields["NOTIFY_MESSAGE"] = GetMessage("WIKI_SONET_IM_COMMENT", Array(
								"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
							));
							$arMessageFields["NOTIFY_MESSAGE_OUT"] = GetMessage("WIKI_SONET_IM_COMMENT", Array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
							))." (".$serverName.$url.")#BR##BR#".$arFieldsForSocnet["MESSAGE"];

							CIMNotify::Add($arMessageFields);
						}
					}
				}
			}
			else //if new socnetlog record - add all comments
			{
				$dbMessage = CForumMessage::GetListEx(
					array(),
					array(
						"TOPIC_ID" => $topicID, 
						"NEW_TOPIC" => "N"
					)
				);

				while ($arMessage = $dbMessage->GetNext())
				{
					if (intVal($arMessage['AUTHOR_ID']) > 0)
						$sAuthorForMail = str_replace(array('#URL#', '#TITLE#'), array('http://'.SITE_SERVER_NAME.CComponentEngine::MakePathFromTemplate(
							$this->arPath['PATH_TO_USER'], array('user_id' => $arMessage['AUTHOR_ID'])), $arMessage['AUTHOR_NAME']),
							GetMessage('SONET_FORUM_LOG_TEMPLATE_AUTHOR'));

					$arFieldsForSocnet = array(
						'ENTITY_TYPE' => SONET_SUBSCRIBE_ENTITY_GROUP,
						'ENTITY_ID' => intval($this->SonetGroupID),
						'EVENT_ID' => 'wiki_comment',
						'=LOG_DATE' => $GLOBALS['DB']->CharToDateFunction($arMessage['POST_DATE'], "FULL", SITE_ID),
						'MESSAGE' => $parser->convert($arMessage['POST_MESSAGE'], $arAllow),
						'TEXT_MESSAGE' => $parser->convert4mail($arMessage['POST_MESSAGE'].$sAuthorForMail),
						'URL' => $arLogParams["URL"],
						'MODULE_ID' => false,
						'SOURCE_ID' => $arMessage['ID'],
						'LOG_ID' => $arLogParams["LOG_ID"],
						'RATING_TYPE_ID' => 'FORUM_POST',
						'RATING_ENTITY_ID' => intval($arMessage['ID'])
					);

					if (intVal($arMessage['AUTHOR_ID']) > 0)
						$arFieldsForSocnet['USER_ID'] = $arMessage['AUTHOR_ID'];

					$log_comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
					CSocNetLog::CounterIncrement($log_comment_id, false, false, "LC");
				}
			}
		}

		private function deleteLogComment($messageID)
		{
			$dbRes = CSocNetLogComments::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "wiki_comment",
					"SOURCE_ID" => $messageID
				),
				false,
				false,
				array("ID")
			);

			while ($arRes = $dbRes->Fetch())
			{
				CSocNetLogComments::Delete($arRes["ID"]);
			}
		}

		function onMessageModerate($ID, $TYPE, $arMessage)
		{
			$bSocNetLogRecordExists = false;

			if ($TYPE == "SHOW")
			{
				if (
					(
						!array_key_exists('PARAM1', $arMessage)
						|| $arMessage['PARAM1'] != 'IB'
					)
					&& array_key_exists('PARAM2', $arMessage)
					&& intval($arMessage['PARAM2']) > 0
				)
				{
					$dbRes = CSocNetLog::GetList(
						array('ID' => 'DESC'),
						array(
							"EVENT_ID" => "wiki",
							"SOURCE_ID" => $arMessage["PARAM2"] // wiki element id
						),
						false,
						false,
						array('ID', 'TITLE', 'USER_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'TMP_ID', 'URL')
					);

					if ($arRes = $dbRes->Fetch())
					{
						$log_id = $arRes["ID"];
						$log_user_id = $arRes["USER_ID"];
						$log_title = $arRes["TITLE"];
						$url = $arRes["URL"];
						$bSocNetLogRecordExists = true;
					}
					else
					{
						$tmpRes = $this->createLogEntry($arMessage["PARAM2"]);
						if (
							is_array($tmpRes)
							&& $tmpRes["LOG_ID"]
						)
						{
							$log_id = $tmpRes["LOG_ID"];
							$log_user_id = $tmpRes["LOG_USER_ID"];
							$log_title = $tmpRes["LOG_TITLE"];
							$url = $tmpRes["URL"];
						}
					}

					if (intval($log_id) > 0)
					{
						$this->createLogComment($ID, $bSocNetLogRecordExists, $arMessage["PARAM2"], $arMessage["TOPIC_ID"], array(
							"LOG_ID" => $log_id,
							"LOG_USER_ID" => $log_user_id,
							"LOG_TITLE" => $log_title,
							"URL" => $url
						));
					}
				}
			}
			elseif (
				(
					!array_key_exists('PARAM1', $arMessage)
					|| $arMessage['PARAM1'] != 'IB'
				)
				&& array_key_exists('PARAM2', $arMessage)
				&& intval($arMessage['PARAM2']) > 0
			)
			{
				$this->deleteLogComment($ID);
			}
		}

		function onAfterMessageAdd($ID, $arFields)
		{
			$bSocNetLogRecordExists = false;

			// add log comment
			if (
				(
					!array_key_exists('PARAM1', $arFields)
					|| $arFields['PARAM1'] != 'IB'
				)
				&& array_key_exists('PARAM2', $arFields)
				&& intval($arFields['PARAM2']) > 0
			)
			{
				$dbRes = CSocNetLog::GetList(
					array('ID' => 'DESC'),
					array(
						"EVENT_ID" => "wiki",
						"SOURCE_ID" => $arFields["PARAM2"] // wiki element id
					),
					false,
					false,
					array('ID', 'TITLE', 'USER_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'TMP_ID', 'URL')
				);

				if ($arRes = $dbRes->Fetch())
				{
					$log_id = $arRes["ID"];
					$log_user_id = $arRes["USER_ID"];
					$log_title = $arRes["TITLE"];
					$url = $arRes["URL"];
					$bSocNetLogRecordExists = true;
				}
				else
				{
					$tmpRes = $this->createLogEntry($arFields["PARAM2"]);
					if (
						is_array($tmpRes)
						&& $tmpRes["LOG_ID"]
					)
					{
						$log_id = $tmpRes["LOG_ID"];
						$log_user_id = $tmpRes["LOG_USER_ID"];
						$log_title = $tmpRes["LOG_TITLE"];
						$url = $tmpRes["URL"];
					}
				}

				if (intval($log_id) > 0)
				{
					$this->createLogComment($ID, $bSocNetLogRecordExists, $arFields["PARAM2"], $arFields["TOPIC_ID"], array(
						"LOG_ID" => $log_id,
						"LOG_USER_ID" => $log_user_id,
						"LOG_TITLE" => $log_title,
						"URL" => $url
					));
				}
			}
		}

		function onAfterMessageDelete($ID, $arFields)
		{
			if (
				(
					!array_key_exists('PARAM1', $arFields)
					|| $arFields['PARAM1'] != 'IB'
				)
				&& array_key_exists('PARAM2', $arFields)
				&& intval($arFields['PARAM2']) > 0
			)
			{
				$this->deleteLogComment($ID);
			}
		}

		function onBeforeTopicAdd(&$arFields)
		{
			if (intval($this->SonetGroupID) > 0)
			{
				$arFields["SOCNET_GROUP_ID"] = intval($this->SonetGroupID);
			}
		}
	}

	$obWikiForumEventHandler = new CSocNetWikiForumEvent;
	$obWikiForumEventHandler->SetVars($arParams, $arResult);

	AddEventHandler('forum', 'onAfterMessageAdd', array($obWikiForumEventHandler, 'onAfterMessageAdd'));
	AddEventHandler('forum', 'onMessageModerate', array($obWikiForumEventHandler, 'onMessageModerate'));
	AddEventHandler('forum', 'onAfterMessageDelete', array($obWikiForumEventHandler, 'onAfterMessageDelete'));
	if (intval($arParams['SOCNET_GROUP_ID']) > 0)
	{
		AddEventHandler('forum', 'onBeforeTopicAdd', array($obWikiForumEventHandler, 'onBeforeTopicAdd'));
	}
}

$this->IncludeComponentTemplate();
unset($GLOBALS['arParams']);

?>