<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 * @var string $componentName
 * @var CBitrixComponent $this
 * @global CDataBase $DB
 */
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$orderIndex = InitSortingEx();
global $by, $order, $CACHE_MANAGER;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["FID"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
$arParams["SORT_BY"] = (empty($arParams["SORT_BY"]) ? false : $arParams["SORT_BY"]);
$arParams["SORT_BY"] = ($by ? $by : $arParams["SORT_BY"]);
$arParams["SORT_BY"] = ($arParams["SORT_BY"] ? $arParams["SORT_BY"] : "LAST_POST_DATE");
$arParams["SORT_ORDER"] = mb_strtoupper($arParams["SORT_ORDER"] == "ASC"? "ASC" : "DESC");
$arParams["SORT_ORDER"] = mb_strtoupper($order? $order : $arParams["SORT_ORDER"]);
$by = $arParams["SORT_BY"];
$order = $arParams["SORT_ORDER"];
$arParams["SORT_BY_SORT_FIRST"] = ($arParams["SORT_BY_SORT_FIRST"] == "Y" ? "Y" : "N");
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
$arParams["TOPICS_PER_PAGE"] = intval($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] :
	COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) :
	$arParams["DATE_TIME_FORMAT"]);
$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "Y" ? "Y" : "N");
$arParams["SHOW_TOPIC_POST_MESSAGE"] = ($arParams["SHOW_TOPIC_POST_MESSAGE"] == "FIRST_POST" || $arParams["SHOW_TOPIC_POST_MESSAGE"] == "LAST_POST" ? $arParams["SHOW_TOPIC_POST_MESSAGE"] : "NONE");
$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : (empty($arParams["USER_FIELDS"]) ? array() : array($arParams["USER_FIELDS"])));
if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]) && IsModuleInstalled("disk"))
	$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";

// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/***************** ANOTHER *****************************************/
$arParams["PAGER_DESC_NUMBERING"] = ($arParams["PAGER_DESC_NUMBERING"] == "N" ? "N" : "Y");
$arParams["PAGER_TITLE"] = (empty($arParams["PAGER_TITLE"]) ? GetMessage("FTP_TITLE_NAV") : $arParams["PAGER_TITLE"]);
$arParams["PAGER_TEMPLATE"] = (empty($arParams["PAGER_TEMPLATE"]) ? false : $arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_SHOW_ALWAYS"] = ($arParams["PAGER_SHOW_ALWAYS"] == "Y" ? true : false);
/***************** STANDART ****************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["CACHE_TAG"] = ($arParams["CACHE_TAG"] == "N" ? "N" : "Y");
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
$arNavParams = array("nPageSize"=>$arParams["TOPICS_PER_PAGE"], "bDescPageNumbering"=>($arParams["PAGER_DESC_NUMBERING"] == "Y"));
if ($arParams['SET_NAVIGATION'] == 'N')
	$arNavParams['nTopCount'] = $arParams["TOPICS_PER_PAGE"];
$arNavigation = CDBResult::GetNavParams($arNavParams);

$arSort = ($arParams["SORT_BY_SORT_FIRST"] == "Y" ? array("SORT" => "ASC") : array());
$arSort[$arParams["SORT_BY"]] = $arParams["SORT_ORDER"];

$arFilter = array();
if (!CForumUser::IsAdmin())
	$arFilter = array("LID" => SITE_ID, "PERMS" => array($USER->GetGroups(), 'A'), "ACTIVE" => "Y");
elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N")
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID"]))
	$arFilter["@ID"] = $arParams["FID"];

if($this->StartResultCache(false, array($arNavigation, $USER->GetGroups(), $arSort, $arFilter, $orderIndex)))
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
// it needs for custom components
		foreach (array("TITLE", "USER_START_NAME", "POSTS", "VIEWS", "LAST_POST_DATE", "START_DATE") as $res):
			$arResult["SortingEx"][$res] = SortingEx($res, false, "by".$orderIndex, "order".$orderIndex);
		endforeach;
// /it needs for custom components
		$db_res = CForumTopic::GetListEx($arSort, array("@FORUM_ID" => array_keys($arForums), "APPROVED" => "Y", "!STATE" => "L"), false, false, $arNavParams);
		if ($db_res)
		{
			$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
			//$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
			$arResult["NAV_RESULT"] = $db_res;

			$ids = array();
			while ($res = $db_res->GetNext())
			{
				if ($res["LAST_POST_DATE"] <> '')
				{
					$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"],
						MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
				}
				$res["URL"] = array(
					"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
						array("UID" => $res["USER_START_ID"])),
					"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
						array("UID" => $res["USER_START_ID"])),
					"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))).
							"#message".intval($res["LAST_MESSAGE_ID"]),
					"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))).
							"#message".intval($res["LAST_MESSAGE_ID"]));
				$res["user_start_id_profile"] = $res["URL"]["AUTHOR"]; // For custom
				$res["read"] = $res["URL"]["READ"]; // For custom

				$arTopics[$res["ID"]] = $res;
				$ids[$res['ID']] = $res['LAST_MESSAGE_ID'];
			}
		}

		if ($arParams['SHOW_TOPIC_POST_MESSAGE'] != 'NONE' && !empty($arTopics))
		{
			$db_res = CForumMessage::GetListEx(
				array("ID" => "ASC"),
				($arParams['SHOW_TOPIC_POST_MESSAGE'] == 'FIRST_POST' ?
					array("@TOPIC_ID" => array_keys($ids), 'APPROVED' => 'Y', 'NEW_TOPIC' => 'Y') :
					array("@ID" => array_values($ids), 'APPROVED' => 'Y'))
			);

			$ids = array();
			while ($res = $db_res->GetNext())
			{
				$ids[] = $res["ID"];
				$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
				if (!empty($arParams["NAME_TEMPLATE"]) && $res["SHOW_NAME"] != "Y")
				{
					$res["~AUTHOR_NAME"] =  CUser::FormatName(
						$arParams["NAME_TEMPLATE"],
						array(
							"NAME"			=> $res["NAME"],
							"LAST_NAME"		=> $res["LAST_NAME"],
							"SECOND_NAME"	=> $res["SECOND_NAME"],
							"LOGIN"			=> $res["LOGIN"]
						),
						true,
						false
					);
					$res["AUTHOR_NAME"] =  CUser::FormatName(
						$arParams["NAME_TEMPLATE"],
						array(
							"NAME"			=> $res["NAME"],
							"LAST_NAME"		=> $res["LAST_NAME"],
							"SECOND_NAME"	=> $res["SECOND_NAME"],
							"LOGIN"			=> $res["LOGIN"]
						),
						true,
						true
					);
				}
				$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
				$res["AUTHOR_URL"] = "";
				if (!empty($arParams["URL_TEMPLATES_PROFILE_VIEW"]))
				{
					$res["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("user_id" => $res["AUTHOR_ID"]));
				}

				$res["POST_TIMESTAMP"] = MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat());

				$arTopics[$res['TOPIC_ID']]['MESSAGE'] = array(
					'ID' => $res["ID"],
					'~POST_MESSAGE_TEXT' => $res["~POST_MESSAGE_TEXT"],
					'POST_MESSAGE_TEXT' => $res["~POST_MESSAGE_TEXT"],
					'NEW_TOPIC' => $res["NEW_TOPIC"],
					'POST_DATE' => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], $res["POST_TIMESTAMP"]),
					'USE_SMILES' => $res["USE_SMILES"],
					'POST_TIMESTAMP' => $res["POST_TIMESTAMP"],
					'AUTHOR_NAME' => $res["AUTHOR_NAME"],
					'AUTHOR_ID' => $res["AUTHOR_ID"],
					'AUTHOR_URL' => str_replace('#UID#', $res["AUTHOR_ID"], $res["AUTHOR_URL"]),
					'AUTHOR_AVATAR_ID' => $res["AVATAR"] > 0 ? $res["AVATAR"] : $res["PERSONAL_PHOTO"]
				);

				if ($arParams["CACHE_TAG"] == "Y" && $arParams["CACHE_TIME"] > 0 && defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->RegisterTag('forum_topic_'.$res['TOPIC_ID']);
				}
			}

			if (!empty($ids))
			{
				$parser = new forumTextParser(LANGUAGE_ID);
				$parser->imageWidth = $parser->imageHeight = (array_key_exists("IMAGE_SIZE", $arParams) ? $arParams["IMAGE_SIZE"] : 200);
				$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
				$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

				$files = array();
				$props = array();

				$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), array("@MESSAGE_ID" => $ids));
				while ($res = $db_files->Fetch())
				{
					$files[$res["MESSAGE_ID"]] = ($files[$res["MESSAGE_ID"]] ?: array());
					$files[$res["MESSAGE_ID"]][$res["FILE_ID"]] = $res;
				}
				if (!empty($arParams["USER_FIELDS"]))
				{
					$db_props = CForumMessage::GetList(array("ID" => "ASC"), array("@ID" => $ids), false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
					while ($res = $db_props->Fetch())
					{
						$props[$res["ID"]] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
					}
				}

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
					"ALIGN" => $arParams["ALLOW_ALIGN"]
				);

				foreach ($arTopics as &$topic)
				{
					$topic['MESSAGE']["POST_MESSAGE_TEXT"] = $parser->convert(
						$topic['MESSAGE']["~POST_MESSAGE_TEXT"],
						array_merge($arAllow, array(
							"SMILES" => ($topic["MESSAGE"]["USE_SMILES"] == "Y" ? $arParams["ALLOW_SMILES"] : "N"),
							"USERFIELDS" => $props[$topic["MESSAGE"]["ID"]])),
						"html",
						$files[$topic["MESSAGE"]["ID"]]
					);
					if ($arParams['TOPIC_POST_MESSAGE_LENGTH'] > 0)
					{
						$symbols_len = mb_strlen(strip_tags($topic['MESSAGE']["POST_MESSAGE_TEXT"]));
						if($symbols_len > $arParams['TOPIC_POST_MESSAGE_LENGTH'])
						{
							$strip_text = $parser->strip_words($topic['MESSAGE']["POST_MESSAGE_TEXT"], $arParams['TOPIC_POST_MESSAGE_LENGTH']);
							if($symbols_len > $arParams['TOPIC_POST_MESSAGE_LENGTH'])
								$strip_text = $strip_text."...";

							$topic['MESSAGE']["POST_MESSAGE_TEXT"] = $parser->closetags($strip_text);
						}
					}
					$topic['MESSAGE']["FILES"] = $files[$topic["MESSAGE"]["ID"]];
					$topic['MESSAGE']["FILES_PARSED"] = $parser->arFilesIDParsed;
					unset($topic["MESSAGE"]["USE_SMILES"]);
				}
			}
		}
	}

	$arResult['TOPICS'] = CForumCacheManager::Compress($arTopics);
	$arResult['FORUMS'] = CForumCacheManager::Compress($arForums);
	$this->EndResultCache();
	$arResult['TOPICS'] = $arTopics;
	$arResult['FORUMS'] = $arForums;
}
else
{
	$arResult['TOPICS'] = CForumCacheManager::Expand($arResult['TOPICS']);
	$arResult['FORUMS'] = CForumCacheManager::Expand($arResult['FORUMS']);
}
$arResult["TOPIC"] = $arResult["TOPICS"]; // For custom
$arResult["FORUM"] = $arResult["FORUMS"]; // For custom
$this->IncludeComponentTemplate();

/********************************************************************
			Data
********************************************************************/

if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("FTP_INDEX"), CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FTP_TITLE"));
// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel(0, 0, false);
?>
