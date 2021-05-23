<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$orderIndex = InitSortingEx();
global $by, $order;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["FID"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());

/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
	$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
	$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
}
/***************** ADDITIONAL **************************************/
$arParams["COMMENTS_PER_PAGE"] = intval($arParams["COMMENTS_PER_PAGE"] > 0 ? $arParams["COMMENTS_PER_PAGE"] :
	COption::GetOptionString("forum", "COMMENTS_PER_PAGE", "10"));
$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) :
	$arParams["DATE_TIME_FORMAT"]);
$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "Y" ? "Y" : "N");
// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/***************** ANOTHER *****************************************/
$arParams["PAGER_DESC_NUMBERING"] = ($arParams["PAGER_DESC_NUMBERING"] == "N" ? "N" : "Y");
$arParams["PAGER_TITLE"] = (empty($arParams["PAGER_TITLE"]) ? GetMessage("FCL_TITLE_NAV") : $arParams["PAGER_TITLE"]);
$arParams["PAGER_TEMPLATE"] = (empty($arParams["PAGER_TEMPLATE"]) ? false : $arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_SHOW_ALWAYS"] = ($arParams["PAGER_SHOW_ALWAYS"] == "Y" ? true : false);
/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
$arNavParams = array("nPageSize"=>$arParams["COMMENTS_PER_PAGE"], "bDescPageNumbering"=>($arParams["PAGER_DESC_NUMBERING"] == "Y"));
if ($arParams['SET_NAVIGATION'] == 'N')
	$arNavParams['nTopCount'] = $arParams["COMMENTS_PER_PAGE"];
$arNavigation = CDBResult::GetNavParams($arNavParams);

$arSort = array('ID' => 'DESC');
$arFilter = array();
if (!CForumUser::IsAdmin())
	$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N")
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID"]))
	$arFilter["@ID"] = $arParams["FID"];

// PARSER
$parser = new forumTextParser(LANGUAGE_ID);
$parser->imageWidth = $arParams["IMAGE_SIZE"];
$parser->imageHtmlWidth = $arParams["IMAGE_HTML_SIZE"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arResult["PARSER"] = $parser;
$arAllow = array(
	"HTML" => $arParams["ALLOW_HTML"],
	"ANCHOR" => $arParams["ALLOW_ANCHOR"],
	"BIU" => $arParams["ALLOW_BIU"],
	"IMG" => $arParams["ALLOW_IMG"],
	"VIDEO" => $arParams["ALLOW_VIDEO"],
	"LIST" => $arParams["ALLOW_LIST"],
	"QUOTE" => $arParams["ALLOW_QUOTE"],
	"CODE" => $arParams["ALLOW_CODE"],
	"FONT" => $arParams["ALLOW_FONT"],
	"SMILES" => $arParams["ALLOW_SMILES"],
	"NL2BR" => $arParams["ALLOW_NL2BR"],
	"TABLE" => $arParams["ALLOW_TABLE"],
	"UPLOAD" => $arParams["ALLOW_UPLOAD"],
	"ALIGN" => $arParams["ALLOW_ALIGN"],
	"USER" => "N"
);

if($this->StartResultCache(false, array($arNavigation, $GLOBALS["USER"]->GetGroups(), $arSort, $arFilter, $orderIndex)))
{
/********************************************************************
				Default values
********************************************************************/
$arResult["TOPIC"] = array();
$arResult["FORUM"] = array();
$arResult["FORUMS"] = array();
$arResult["TOPICS"] = array();
$arForums = array();
$arTopics = array();
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
	$db_res = CForumNew::GetListEx(array(), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$res["URL"] = array(
				"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])),
				"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"])));
			$res["list"] = $res["URL"]["list"]; // for custom
			$arForums[$res["ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}

	if (!empty($arForums))
	{
		$arTopicsId = Array();
		$arMessages = Array();
		$db_res_message = CForumMessage::GetListEx(
				$arSort,
				array("@FORUM_ID" => array_keys($arForums), 'APPROVED' => 'Y', 'NEW_TOPIC' => 'N'),
				false, false, $arNavParams
		);
		if ($db_res)
		{
			$db_res_message->NavStart($arParams["COMMENTS_PER_PAGE"], false);
			$arResult["NAV_STRING"] = $db_res_message->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
			//$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
			$arResult["NAV_RESULT"] = $db_res_message;

			while ($res = $db_res_message->GetNext())
			{
				$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arParams["ALLOW_SMILES"] : "N")));

				$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);

				$parser->arFiles = $arResult["FILES"];
				$res["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);

				$res["POST_MESSAGE_TEXT"] = htmlspecialcharsback($res["POST_MESSAGE_TEXT"]);
				$res["POST_MESSAGE_TEXT"] = preg_replace("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER, "<b>\\2</b>", $res["POST_MESSAGE_TEXT"]);

				if ($arParams['TOPIC_POST_MESSAGE_LENGTH'] > 0)
				{
					$symbols_len = mb_strlen(strip_tags($res["POST_MESSAGE_TEXT"]));
					if ($symbols_len > $arParams['TOPIC_POST_MESSAGE_LENGTH'])
					{
						$strip_text = $parser->strip_words($res["POST_MESSAGE_TEXT"], $arParams['TOPIC_POST_MESSAGE_LENGTH']);
						if ($symbols_len > $arParams['TOPIC_POST_MESSAGE_LENGTH'])
							$strip_text = $strip_text."...";

						$res["POST_MESSAGE_TEXT"] = $parser->closetags($strip_text);
					}
				}

				if (!empty($arParams["NAME_TEMPLATE"]) && $res["SHOW_NAME"] != "Y")
				{
					$name = CUser::FormatName(
							$arParams["NAME_TEMPLATE"],
							array(
									"NAME"        => $res["NAME"],
									"LAST_NAME"   => $res["LAST_NAME"],
									"SECOND_NAME" => $res["SECOND_NAME"],
									"LOGIN"       => $res["LOGIN"]
							),
							true,
							false
					);
					if (!!$name)
					{
						$res["~AUTHOR_NAME"] = $name;
						$res["AUTHOR_NAME"] = htmlspecialcharsbx($name);
					}
				}
				$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
				$res["AUTHOR_URL"] = "";
				if (!empty($arParams["URL_TEMPLATES_PROFILE_VIEW"]))
				{
					$res["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("user_id" => $res["AUTHOR_ID"]));
				}

				$res["POST_TIMESTAMP"] = MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat());
				$res["POST_TIME"] = FormatDate($FormatDate, $res["POST_TIMESTAMP"]);
				$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], $res["POST_TIMESTAMP"]);

				$arMessages[] = Array(
						'TOPIC_ID'           => $res["TOPIC_ID"],
						'FORUM_ID'           => $res["FORUM_ID"],
						'~POST_MESSAGE_TEXT' => $res["~POST_MESSAGE_TEXT"],
						'POST_MESSAGE_TEXT'  => $res["POST_MESSAGE_TEXT"],
						'NEW_TOPIC'          => $res["NEW_TOPIC"],
						'POST_DATE'          => $res["POST_DATE"],
						'POST_TIME'          => $res["POST_TIME"],
						'POST_TIMESTAMP'     => $res["POST_TIMESTAMP"],
						'AUTHOR_NAME'        => $res["AUTHOR_NAME"],
						'AUTHOR_ID'          => $res["AUTHOR_ID"],
						'AUTHOR_URL'         => str_replace('#UID#', $res["AUTHOR_ID"], $res["AUTHOR_URL"]),
						'AUTHOR_AVATAR_ID'   => $res["PERSONAL_PHOTO"] > 0 ? $res["PERSONAL_PHOTO"] : $res["AVATAR"],
				);
				$arTopicsId[] = $res['TOPIC_ID'];
			}

			foreach (array("TITLE", "USER_START_NAME", "POSTS", "VIEWS", "LAST_POST_DATE") as $res):
				$arResult["SortingEx"][$res] = SortingEx($res, false, "by".$orderIndex, "order".$orderIndex);
			endforeach;

			$db_res = CForumTopic::GetListEx(Array('ID' => 'ASC'),
					array("@ID" => $arTopicsId, "APPROVED" => "Y"),
					false, false);
			if ($db_res)
			{
				while ($res = $db_res->GetNext())
				{
					if (trim($res["LAST_POST_DATE"]) <> '')
					{
						$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"],
								MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
					}
					$res["URL"] = array(
							"AUTHOR"  => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
									array("UID" => $res["USER_START_ID"])),
							"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
									array("UID" => $res["USER_START_ID"])),
							"READ"    => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
											array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))).
									"#message".intval($res["LAST_MESSAGE_ID"]),
							"~READ"   => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
											array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))).
									"#message".intval($res["LAST_MESSAGE_ID"]));
					$res["user_start_id_profile"] = $res["URL"]["AUTHOR"]; // For custom
					$res["read"] = $res["URL"]["READ"]; // For custom
					$arTopics[$res["ID"]] = $res;
				}
			}
		}
	}
	$arResult['MESSAGES'] = CForumCacheManager::Compress($arMessages);
	$arResult['TOPICS'] = CForumCacheManager::Compress($arTopics);
	$arResult['FORUMS'] = CForumCacheManager::Compress($arForums);
	$this->EndResultCache();
	$arResult['MESSAGES'] = $arMessages;
	$arResult['TOPICS'] = $arTopics;
	$arResult['FORUMS'] = $arForums;
}
else
{
	$arResult['MESSAGES'] = CForumCacheManager::Expand($arResult['MESSAGES']);
	$arResult['TOPICS'] = CForumCacheManager::Expand($arResult['TOPICS']);
	$arResult['FORUMS'] = CForumCacheManager::Expand($arResult['FORUMS']);
}
$arResult["MESSAGE"] = $arResult["MESSAGES"]; // For custom
$arResult["TOPIC"] = $arResult["TOPICS"]; // For custom
$arResult["FORUM"] = $arResult["FORUMS"]; // For custom
$this->IncludeComponentTemplate();

/********************************************************************
			Data
********************************************************************/

if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("FCL_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FCL_TITLE"));
// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel(0, 0, false);
?>
