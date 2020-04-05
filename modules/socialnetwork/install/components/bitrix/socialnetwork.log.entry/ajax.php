<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
$entity_type = (isset($_REQUEST["et"]) && is_string($_REQUEST["et"])) ? trim($_REQUEST["et"]): "";
$entity_id = isset($_REQUEST["eid"])? $_REQUEST["eid"]: "";
$cb_id = isset($_REQUEST["cb_id"])? $_REQUEST["cb_id"]: "";
$event_id = (isset($_REQUEST["evid"]) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]): "";
$transport = (isset($_REQUEST["transport"]) && is_string($_REQUEST["transport"])) ? trim($_REQUEST["transport"]): "";
$entity_xml_id = (isset($_REQUEST["exmlid"]) && is_string($_REQUEST["exmlid"])) ? trim($_REQUEST["exmlid"]): "";
$entity_xml_id = (!empty($entity_xml_id)) ? $entity_xml_id : ((isset($_REQUEST["ENTITY_XML_ID"]) && is_string($_REQUEST["ENTITY_XML_ID"])) ? trim($_REQUEST["ENTITY_XML_ID"]): "");

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && !is_array($_REQUEST["ls"])? trim($_REQUEST["ls"]): "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

$st_id = (isset($_REQUEST["st_id"]) && is_string($_REQUEST["st_id"])) ? trim($_REQUEST["st_id"]): "";
$st_id = preg_replace("/[^a-z0-9_]/i", "", $st_id);

define("SITE_TEMPLATE_ID", $st_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
{
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
}
else
{
	define("LANGUAGE_ID", "en");
}

if (empty($lng))
{
	$lng = LANGUAGE_ID;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.log.entry/include.php");

Loc::loadLanguageFile(__FILE__, $lng);

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (in_array($action, array("add_comment", "get_comment", "get_comments", "get_more_destination")))
	{
		CSocNetTools::InitGlobalExtranetArrays();
	}

	$currentUserId = 0;
	$currentUserExternalAuthId = '';
	$bCurrentUserUserAuthorized = $GLOBALS["USER"]->IsAuthorized();

	if ($bCurrentUserUserAuthorized)
	{
		$currentUserId = $GLOBALS["USER"]->GetId();
		$rsCurrentUser = CUser::GetByID($currentUserId);
		if ($arCurrentUser = $rsCurrentUser->Fetch())
		{
			$currentUserExternalAuthId = $arCurrentUser['EXTERNAL_AUTH_ID'];
		}
	}

	if (!$bCurrentUserUserAuthorized)
	{
		$arResult[0] = "*";
	}
	elseif (!check_bitrix_sessid())
	{
		$arResult[0] = "*";
	}
	elseif ($action == "add_comment")
	{
		$log_id = $_REQUEST["log_id"];

		$cuid = (isset($_REQUEST["cuid"]) && is_string($_REQUEST["cuid"])) ? trim($_REQUEST["cuid"]): "";
		$cuid = preg_replace("/[^a-z0-9]/i", "", $cuid);

		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			if (
				strpos($arLog["ENTITY_TYPE"], "CRM") === 0
				&& 
				(
					!in_array($arLog["EVENT_ID"], array("crm_lead_message", "crm_deal_message", "crm_company_message", "crm_contact_message", "crm_activity_add"))
					|| (isset($_REQUEST["crm"]) && $_REQUEST["crm"] == "Y")
				)
				&& $currentUserExternalAuthId != 'email'
				&& IsModuleInstalled("crm")
			)
			{
				$arListParams = array("IS_CRM" => "Y", "CHECK_CRM_RIGHTS" => "Y");
			}
			else
			{
				$arListParams = array("CHECK_RIGHTS" => "Y", "USE_SUBSCRIBE" => "N");
			}
		}
		else
		{
			$log_id = 0;
		}

		if (
			intval($log_id) > 0
			&& ($rsLog = CSocNetLog::GetList(array(), array("ID" => $log_id), false, false, array(), $arListParams))
			&& ($arLog = $rsLog->Fetch())
		)
		{
			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arLog["EVENT_ID"]);
			if ($arCommentEvent)
			{
				$bCanAddComments = \Bitrix\Socialnetwork\ComponentHelper::canAddComment($arLog, $arCommentEvent);

				if ($bCanAddComments)
				{
					$arCommentParams = $_REQUEST["id"];
					if (is_array($arCommentParams) && isset($arCommentParams[1]) && intval($arCommentParams[1]) > 0)
					{
						$editCommentSourceID = intval($arCommentParams[1]);
					}
				}

				if ($bCanAddComments)
				{
					// add source object and get source_id, $source_url
					$arParams = array(
						"PATH_TO_SMILE" => $_REQUEST["p_smile"],
						"PATH_TO_LOG_ENTRY" => $_REQUEST["p_le"],
						"PATH_TO_USER_BLOG_POST" => $_REQUEST["p_ubp"],
						"PATH_TO_GROUP_BLOG_POST" => $_REQUEST["p_gbp"],
						"PATH_TO_USER_MICROBLOG_POST" => $_REQUEST["p_umbp"],
						"PATH_TO_GROUP_MICROBLOG_POST" => $_REQUEST["p_gmbp"],
						"BLOG_ALLOW_POST_CODE" => $_REQUEST["bapc"]
					);
//					$parser = new logTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

					$comment_text = $_REQUEST["message"];
					CUtil::decodeURIComponent($comment_text);
					$comment_text = Trim($comment_text);
					if (strlen($comment_text) > 0)
					{
						$arSearchParams = array();

						if($arCommentEvent["EVENT_ID"] == "forum")
						{
							$arSearchParams["FORUM_ID"] = intval($_REQUEST["f_id"]);
							$arSearchParams["PATH_TO_GROUP_FORUM_MESSAGE"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#", 
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_FORUM_MESSAGE"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"] 
									: ""
							);
						}
						elseif ($arCommentEvent["EVENT_ID"] == "files_comment")
						{
							if (strlen($arLog["PARAMS"]) > 0)
							{
								$files_forum_id = 0;
								$arLogParams = explode("&", htmlspecialcharsback($arLog["PARAMS"]));
								foreach($arLogParams as $prm)
								{
									list($k, $v) = explode("=", $prm);
									if ($k == "forum_id")
									{
										$files_forum_id = $v;
										break;
									}
								}
							}
							$arSearchParams["FILES_FORUM_ID"] = $files_forum_id;
							$arSearchParams["PATH_TO_GROUP_FILES_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#", 
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_FILES_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"] 
									: ""
							);
						}
						elseif($arCommentEvent["EVENT_ID"] == "photo_comment")
						{
							if (strlen($arLog["PARAMS"]) > 0)
							{
								$photo_forum_id = 0;
								$arLogParams = unserialize(htmlspecialcharsback($arLog["PARAMS"]));
								if (
									isset($arLogParams["FORUM_ID"])
									&& intval($arLogParams["FORUM_ID"]) > 0
								)
								{
									$photo_forum_id = $arLogParams["FORUM_ID"];
								}
							}
							$arSearchParams["PHOTO_FORUM_ID"] = $photo_forum_id;
							$arSearchParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#",
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_PHOTO_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"]
									: ""
							);
						}
						elseif($arCommentEvent["EVENT_ID"] == "wiki_comment")
						{
							$arSearchParams["PATH_TO_GROUP_WIKI_POST_COMMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id)."group/#group_id#/wiki/#wiki_name#/?MID=#message_id##message#message_id#"
									: ""
							);
						}
						elseif($arCommentEvent["EVENT_ID"] == "tasks_comment")
						{
							if (CModule::IncludeModule('tasks'))
							{
								$tasksForumId = 0;
								try
								{
									$tasksForumId = intval(CTasksTools::getForumIdForIntranet());
								}
								catch(Exception $e)
								{
								}
								if ($tasksForumId > 0)
								{
									$arSearchParams["TASK_FORUM_ID"] = $tasksForumId;
									$arSearchParams["PATH_TO_GROUP_TASK_ELEMENT"] = (
										$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
											? COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id)."group/#group_id#/tasks/task/view/#task_id#/"
											: ""
									);
									$arSearchParams["PATH_TO_USER_TASK_ELEMENT"] = (
										$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
											? COption::GetOptionString("socialnetwork", "user_page", false, $site_id)."user/#user_id#/tasks/task/view/#task_id#/"
											: ""
									);
								}
							}
						}
						elseif($arCommentEvent["EVENT_ID"] == "calendar_comment")
						{
							$arSearchParams["PATH_TO_GROUP_CALENDAR_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id)."group/#group_id#/calendar/?EVENT_ID=#element_id#"
									: ""
							);
						}
						elseif($arCommentEvent["EVENT_ID"] == "lists_new_element_comment")
						{
							$arSearchParams["PATH_TO_WORKFLOW"] = "/services/processes/#list_id#/bp_log/#workflow_id#/";
						}

						global $bxSocNetSearch;
						if (
							!empty($arSearchParams)
							&& !is_object($bxSocNetSearch)
						)
						{
							$bxSocNetSearch = new CSocNetSearch(
								($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER ? $arLog["ENTITY_ID"] : false), 
								($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? $arLog["ENTITY_ID"] : false),
								$arSearchParams
							);
							AddEventHandler("search", "BeforeIndex", Array($bxSocNetSearch, "BeforeIndex"));
						}

						$arAllow = array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"LOG_ANCHOR" => "N",
							"BIU" => "N",
							"IMG" => "N",
							"LIST" => "N",
							"QUOTE" => "N",
							"CODE" => "N",
							"FONT" => "N",
							"UPLOAD" => $arForum["ALLOW_UPLOAD"],
							"NL2BR" => "N",
							"SMILES" => "N",
							"VIDEO" => "N",
							"USER" => "N",
							"ALIGN" => "N"
						);

						if ($editCommentSourceID > 0)
						{
							$arFields = array(
								"EVENT_ID" => $arCommentEvent["EVENT_ID"],
								"MESSAGE" => $comment_text,
								"TEXT_MESSAGE" => $comment_text,
								"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"]
							);
						}
						else
						{
							$arFields = array(
								"ENTITY_TYPE" => $arLog["ENTITY_TYPE"],
								"ENTITY_ID" => $arLog["ENTITY_ID"],
								"EVENT_ID" => $arCommentEvent["EVENT_ID"],
								"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
								"MESSAGE" => $comment_text,
								"TEXT_MESSAGE" => $comment_text,
								"MODULE_ID" => false,
								"LOG_ID" => $arLog["ID"],
								"USER_ID" => $currentUserId,
								"PATH_TO_USER_BLOG_POST" => $arParams["PATH_TO_USER_BLOG_POST"],
								"PATH_TO_GROUP_BLOG_POST" => $arParams["PATH_TO_GROUP_BLOG_POST"],
								"PATH_TO_USER_MICROBLOG_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
								"PATH_TO_GROUP_MICROBLOG_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
								"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"]
							);
						}

						$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("SONET_COMMENT", $arFields);

						if (
							array_key_exists("UF_SONET_COM_FILE", $arFields)
							&& !empty($arFields["UF_SONET_COM_FILE"])
						)
						{

							if (is_array($arFields["UF_SONET_COM_FILE"]))
							{
								foreach($arFields["UF_SONET_COM_FILE"] as $key => $fileID)
								{
									if (
										!$cuid
										|| !array_key_exists("MFI_UPLOADED_FILES_".$cuid, $_SESSION)
										|| !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_".$cuid])
									)
									{
										unset($arFields["UF_SONET_COM_FILE"][$key]);
									}
								}
							}
							else
							{
								if (
									!$cuid
									|| !array_key_exists("MFI_UPLOADED_FILES_".$cuid, $_SESSION)
									|| !in_array($arFields["UF_SONET_COM_FILE"], $_SESSION["MFI_UPLOADED_FILES_".$cuid])
								)
								{
									unset($arFields["UF_SONET_COM_FILE"]);
								}
							}
						}

						$inlineTagList = \Bitrix\Socialnetwork\Util::detectTags($arFields, array("MESSAGE"));

						if (!empty($inlineTagList))
						{
							$arFields["TAG"] = $inlineTagList;
						}

						if ($editCommentSourceID > 0)
						{
							if (
								isset($arCommentEvent["ADD_CALLBACK"])
								&& is_callable($arCommentEvent["ADD_CALLBACK"])
							)
							{
								$rsRes = CSocNetLogComments::GetList(
									array(),
									array(
										"EVENT_ID" => $arCommentEvent["EVENT_ID"],
										"SOURCE_ID" => $editCommentSourceID
									),
									false,
									false,
									array("ID", "USER_ID", "LOG_ID", "SOURCE_ID")
								);
								if ($arRes = $rsRes->Fetch())
								{
									$update_id = $arRes["ID"];
									$update_log_id = $arRes["LOG_ID"];
									$update_user_id = $arRes["USER_ID"];
									$update_source_id = $arRes["SOURCE_ID"];
								}
							}

							if (intval($update_id) <= 0)
							{
								$rsRes = CSocNetLogComments::GetList(
									array(),
									array(
										"ID" => $editCommentSourceID
									),
									false,
									false,
									array("ID", "USER_ID", "LOG_ID", "SOURCE_ID")
								);
								if ($arRes = $rsRes->Fetch())
								{
									$update_id = $arRes["ID"];
									$update_log_id = $arRes["LOG_ID"];
									$update_user_id = $arRes["USER_ID"];
									$update_source_id = $arRes["SOURCE_ID"];
								}
							}

							if (intval($update_id) > 0)
							{
								$bAllowUpdate = CSocNetLogComponent::canUserChangeComment(array(
									"ACTION" => "EDIT",
									"LOG_ID" => $update_log_id,
									"LOG_EVENT_ID" => $arLog["EVENT_ID"],
									"LOG_SOURCE_ID" => $arLog["SOURCE_ID"],
									"COMMENT_ID" => $update_id,
									"COMMENT_USER_ID" => $update_user_id
								));
							}

							if ($bAllowUpdate)
							{
								$commentIdres = CSocNetLogComments::Update($update_id, $arFields, true);
							}
							else
							{
								$commentIdres = array(
									"MESSAGE" => Loc::getMessage("SONET_LOG_COMMENT_NO_PERMISSIONS_UPDATE", false, $lng)
								);
							}
						}
						else
						{
							$commentIdres = CSocNetLogComments::Add($arFields, true, false);
						}

						if (
							!is_array($commentIdres)
							&& intval($commentIdres) > 0
						)
						{
							if ($editCommentSourceID <= 0)
							{
								$db_events = GetModuleEvents("socialnetwork", "OnAfterSocNetLogEntryCommentAdd");
								while ($arEvent = $db_events->Fetch())
								{
									ExecuteModuleEventEx($arEvent, array($arLog, array(
										"SITE_ID" => SITE_ID,
										"COMMENT_ID" => $commentIdres,
									)));
								}

								$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetLogCommentCounterIncrement");
								while ($arEvent = $db_events->Fetch())
								{
									if (ExecuteModuleEventEx($arEvent, array($arLog))===false)
									{
										$bSkipCounterIncrement = true;
										break;
									}
								}
							}
							else
							{
								$bSkipCounterIncrement = true;
							}

							if (!$bSkipCounterIncrement)
							{
								CSocNetLog::CounterIncrement(
									$commentIdres, 
									false, 
									false, 
									"LC",
									CSocNetLogRights::CheckForUserAll($arLog["ID"])
								);
							}
							$arResult["commentID"] = $commentIdres;

							if ($arComment = CSocNetLogComments::GetByID($arResult["commentID"]))
							{
								$res = \Bitrix\Socialnetwork\ComponentHelper::addLiveComment(
									$arComment,
									$arLog,
									$arCommentEvent,
									array(
										"ACTION" => (intval($update_id) <= 0 ? 'ADD' : "UPDATE"),
										"SOURCE_ID" => $editCommentSourceID,
										"TIME_FORMAT" => (
											isset($_REQUEST["dtf"])
												? $_REQUEST["dtf"]
												: CSite::GetTimeFormat()
										),
										"PATH_TO_USER" => $_REQUEST["p_user"],
										"PATH_TO_LOG_ENTRY" => (isset($_REQUEST["p_le"]) ? $_REQUEST["p_le"] : ''),
										"NAME_TEMPLATE" => $_REQUEST["nt"],
										"SHOW_LOGIN" => $_REQUEST["sl"],
										"AVATAR_SIZE" => $_REQUEST["as"],
										"PATH_TO_SMILE" => $_REQUEST["p_smile"],
										"LANGUAGE_ID" => $lng,
										"SITE_ID" => SITE_ID,
										"PULL" => $_REQUEST["pull"],
										"ENTITY_XML_ID" => $entity_xml_id
									)
								);

								$arResult = array_merge($arResult, $res);
							}
						}
						elseif (
							isset($commentIdres["MESSAGE"])
							&& strlen($commentIdres["MESSAGE"]) > 0
						)
						{
							$arResult["strMessage"] = $commentIdres["MESSAGE"];
							$arResult["commentText"] = $comment_text;
						}
					}
					else
					{
						$arResult["strMessage"] = Loc::getMessage("SONET_LOG_COMMENT_EMPTY", false, $lng);
					}
				}
				else
				{
					$arResult["strMessage"] = Loc::getMessage("SONET_LOG_COMMENT_NO_PERMISSIONS", false, $lng);
				}
			}
		}
	}
	elseif ($action == "get_comment")
	{
		$comment_id = $_REQUEST["cid"];

		if ($arComment = CSocNetLogComments::GetByID($comment_id))
		{
			if (
				strpos($arComment["ENTITY_TYPE"], "CRM") === 0
				&& $currentUserExternalAuthId != 'email'
				&& IsModuleInstalled("crm")
			)
			{
				$arListParams = array("IS_CRM" => "Y", "CHECK_CRM_RIGHTS" => "Y");
			}
			else
			{
				$arListParams = array("CHECK_RIGHTS" => "Y", "USE_SUBSCRIBE" => "N");
			}

			if (
				intval($arComment["LOG_ID"]) > 0
				&& ($rsLog = CSocNetLog::GetList(array(), array("ID" => $arComment["LOG_ID"]), false, false, array("ID", "EVENT_ID"), $arListParams))
				&& ($arLog = $rsLog->Fetch())
			)
			{
				$arResult["arComment"] = $arComment;

				$dateFormated = FormatDate(
					$GLOBALS['DB']->DateFormatToPHP(FORMAT_DATE),
					MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComment) ? $arComment["LOG_DATE_FORMAT"] : $arComment["LOG_DATE"])
				);

				$timeFormat = (isset($_REQUEST["dtf"]) ? $_REQUEST["dtf"] : CSite::GetTimeFormat());

				$timeFormated = FormatDateFromDB(
					(
						array_key_exists("LOG_DATE_FORMAT", $arComment) 
							? $arComment["LOG_DATE_FORMAT"] 
							: $arComment["LOG_DATE"]
					),
					(
						stripos($timeFormat, 'a') 
						|| (
							$timeFormat == 'FULL' 
							&& (strpos(FORMAT_DATETIME, 'T')!==false || strpos(FORMAT_DATETIME, 'TT')!==false)
						) !== false 
							? (strpos(FORMAT_DATETIME, 'TT')!==false ? 'H:MI TT' : 'H:MI T')
							: 'HH:MI'
					)
				);

				if (intval($arComment["USER_ID"]) > 0)
				{
					$arParams = array(
						"PATH_TO_USER" => $_REQUEST["p_user"],
						"NAME_TEMPLATE" => $_REQUEST["nt"],
						"SHOW_LOGIN" => $_REQUEST["sl"],
						"AVATAR_SIZE" => $_REQUEST["as"],
						"PATH_TO_SMILE" => $_REQUEST["p_smile"]
					);

					$arUser = array(
						"ID" => $arComment["USER_ID"],
						"NAME" => $arComment["~CREATED_BY_NAME"],
						"LAST_NAME" => $arComment["~CREATED_BY_LAST_NAME"],
						"SECOND_NAME" => $arComment["~CREATED_BY_SECOND_NAME"],
						"LOGIN" => $arComment["~CREATED_BY_LOGIN"],
						"PERSONAL_PHOTO" => $arComment["~CREATED_BY_PERSONAL_PHOTO"],
						"PERSONAL_GENDER" => $arComment["~CREATED_BY_PERSONAL_GENDER"],
					);
					$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
					$arCreatedBy = array(
						"FORMATTED" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, $bUseLogin),
						"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["USER_ID"], "id" => $arComment["USER_ID"]))
					);

				}
				else
					$arCreatedBy = array("FORMATTED" => Loc::getMessage("SONET_C73_CREATED_BY_ANONYMOUS", false, $lng));

				$arTmpCommentEvent = array(
					"LOG_DATE" => $arComment["LOG_DATE"],
					"LOG_DATE_FORMAT" => $arComment["LOG_DATE_FORMAT"],
					"LOG_DATE_DAY" => ConvertTimeStamp(MakeTimeStamp($arComment["LOG_DATE"]), "SHORT"),
					"LOG_TIME_FORMAT" => $timeFormated,
					"MESSAGE" => $arComment["MESSAGE"],
					"MESSAGE_FORMAT" => $arComment["~MESSAGE"],
					"CREATED_BY" => $arCreatedBy,
					"AVATAR_SRC" => CSocNetLogTools::FormatEvent_CreateAvatar($arUser, $arParams, ""),
					"USER_ID" => $arComment["USER_ID"]
				);

				$arEventTmp = CSocNetLogTools::FindLogCommentEventByID($arComment["EVENT_ID"]);
				if (
					$arEventTmp
					&& array_key_exists("CLASS_FORMAT", $arEventTmp)
					&& array_key_exists("METHOD_FORMAT", $arEventTmp)
				)
				{
					$arFIELDS_FORMATTED = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arComment, $arParams);
					$arTmpCommentEvent["MESSAGE_FORMAT"] = htmlspecialcharsback($arFIELDS_FORMATTED["EVENT_FORMATTED"]["MESSAGE"]);
				}

				$arResult["arCommentFormatted"] = $arTmpCommentEvent;
			}
		}
	}
	elseif ($action == "get_comments")
	{

		$arResult["arComments"] = array();

		$log_tmp_id = $_REQUEST["logid"];
		$log_entity_type = $entity_type;
		$follow = (isset($_REQUEST["follow"]) && $_REQUEST["follow"] == "Y" ? "Y" : "N");
		$counterType = (isset($_REQUEST["ct"]) ? $_REQUEST["ct"] : false);

		$arListParams = (
			strpos($log_entity_type, "CRM") === 0
			&& $currentUserExternalAuthId != 'email'
			&& IsModuleInstalled("crm")
				? array("IS_CRM" => "Y", "CHECK_CRM_RIGHTS" => "Y")
				: array("CHECK_RIGHTS" => "Y", "USE_SUBSCRIBE" => "N")
		);

		if (
			intval($log_tmp_id) > 0
			&& ($rsLog = CSocNetLog::GetList(array(), array("ID" => $log_tmp_id), false, false, array("ID", "EVENT_ID", "SOURCE_ID"), $arListParams))
			&& ($arLog = $rsLog->Fetch())
		)
		{
			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"PATH_TO_GROUP" => $_REQUEST["p_group"],
				"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
				"PATH_TO_LOG_ENTRY" => $_REQUEST["p_le"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"NAME_TEMPLATE_WO_NOBR" => str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $_REQUEST["nt"]),
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"DATE_TIME_FORMAT" => (isset($_REQUEST["dtf"]) ? $_REQUEST["dtf"] : CSite::GetDateFormat()),
				"TIME_FORMAT" => (isset($_REQUEST["tf"]) ? $_REQUEST["tf"] : CSite::GetTimeFormat()),
				"AVATAR_SIZE" => $_REQUEST["as"]
			);

			$cache_time = 31536000;

			$cache = new CPHPCache;

			$arCacheID = array();
			$arKeys = array(
				"AVATAR_SIZE",
				"NAME_TEMPLATE",
				"NAME_TEMPLATE_WO_NOBR",
				"SHOW_LOGIN",
				"DATE_TIME_FORMAT",
				"PATH_TO_USER",
				"PATH_TO_GROUP",
				"PATH_TO_CONPANY_DEPARTMENT"
			);
			foreach($arKeys as $param_key)
			{
				$arCacheID[$param_key] = (array_key_exists($param_key, $arParams) ? $arParams[$param_key] : false) ;
			}
			$cache_id = "log_comments_".$log_tmp_id."_".md5(serialize($arCacheID))."_".SITE_TEMPLATE_ID."_".SITE_ID."_".LANGUAGE_ID."_".FORMAT_DATETIME."_".CTimeZone::GetOffset();
			$cache_path = "/sonet/log/".intval(intval($log_tmp_id) / 1000)."/".$log_tmp_id."/comments/";

			if (
				is_object($cache)
				&& $cache->InitCache($cache_time, $cache_id, $cache_path)
			)
			{
				$arCacheVars = $cache->GetVars();
				$arResult["arComments"] = $arCacheVars["COMMENTS_FULL_LIST"];

				if (!empty($arCacheVars["Assets"]))
				{
					if (!empty($arCacheVars["Assets"]["CSS"]))
					{
						foreach($arCacheVars["Assets"]["CSS"] as $cssFile)
						{
							\Bitrix\Main\Page\Asset::getInstance()->addCss($cssFile);
						}
					}

					if (!empty($arCacheVars["Assets"]["JS"]))
					{
						foreach($arCacheVars["Assets"]["JS"] as $jsFile)
						{
							\Bitrix\Main\Page\Asset::getInstance()->addJs($jsFile);
						}
					}
				}
			}
			else
			{
				if (is_object($cache))
				{
					$cache->StartDataCache($cache_time, $cache_id, $cache_path);
				}

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
				}

				$arFilter = array("LOG_ID" => $log_tmp_id);
				$arListParams = array("USE_SUBSCRIBE" => "N");

				$arSelect = array(
					"ID", "LOG_ID", "SOURCE_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID",
					"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
					"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
					"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
					"LOG_SITE_ID", "LOG_SOURCE_ID",
					"RATING_TYPE_ID", "RATING_ENTITY_ID",
					"UF_*"
				);

				$arUFMeta = __SLGetUFMeta();

				$arAssets = array(
					"CSS" => array(),
					"JS" => array()
				);

				$dbComments = CSocNetLogComments::GetList(
					array("LOG_DATE" => "ASC"),
					$arFilter,
					false,
					false,
					$arSelect,
					$arListParams
				);

				while($arComments = $dbComments->GetNext())
				{
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_NAME_".intval($arComments["USER_ID"]));
					}

					$arComments["UF"] = $arUFMeta;
					foreach($arUFMeta as $field_name => $arUF)
					{
						if (array_key_exists($field_name, $arComments))
						{
							$arComments["UF"][$field_name]["VALUE"] = $arComments[$field_name];
							$arComments["UF"][$field_name]["ENTITY_VALUE_ID"] = $arComments["ID"];
						}
					}

					$arResult["arComments"][$arComments["ID"]] = __SLEGetLogCommentRecord($arComments, $arParams, $arAssets);
				}

				if (is_object($cache))
				{
					$arCacheData = Array(
						"COMMENTS_FULL_LIST" => $arResult["arComments"],
						"Assets" => $arAssets
					);
					$cache->EndDataCache($arCacheData);
					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$GLOBALS["CACHE_MANAGER"]->EndTagCache();
					}
				}
			}

			if (
				intval($_REQUEST["commentID"]) > 0
				|| intval($_REQUEST["commentTS"]) > 0
			)
			{
				foreach($arResult["arComments"] as $key => $res)
				{
					if (
						(
							intval($_REQUEST["commentTS"]) > 0
							&& $res["LOG_DATE_TS"] >= $_REQUEST["commentTS"]
						)
						|| (
							intval($_REQUEST["commentTS"]) <= 0
							&& $key >= $_REQUEST["commentID"]
						)
					)
					{
						unset($arResult["arComments"][$key]);
					}
				}
			}
			$tmp = reset($arResult["arComments"]);
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();

			$rating_entity_type = ($tmp["EVENT"]["RATING_TYPE_ID"] ?: false);
			$lastLogTs = (int) $request->getQuery("lastLogTs");

			$db_res = new CDBResult();
			$db_res->InitFromArray(array_reverse($arResult["arComments"], true));
			$db_res->NavNum = 1;
			$db_res->NavStart(20, false);

			$records = array();
			$arEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arLog["EVENT_ID"]);
			$offset = CTimeZone::GetOffset();

			$count = 0;
			while (($arComment = $db_res->fetch()) && $arComment)
			{
				$commentId = ($arComment["EVENT"]["SOURCE_ID"] ? $arComment["EVENT"]["SOURCE_ID"] : $arComment["EVENT"]["ID"]);
				$timestamp = ($arComment["LOG_DATE_TS"]);
				$datetime_formatted = CSocNetLogComponent::getDateTimeFormatted($timestamp, array(
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
					"TIME_FORMAT" => $arParams["TIME_FORMAT"]
				));
				ob_start();
				?><script>
					top.arLogCom<?=$arLog["ID"]?><?=$commentId?> = '<?=$arComment["EVENT"]["ID"]?>';<?
				?></script><?
				$t = ob_get_clean();

				$records[$commentId] = array(
					"ID" => $commentId,
					"NEW" => (
						$lastLogTs > 0
						&& $arComment["LOG_DATE_TS"] > ($lastLogTs + $offset)
						&& $follow == "Y"
						&& $arComment["EVENT"]["USER_ID"] != $currentUserId
						&& (
							$counterType == "**"
							|| $counterType == "CRM_**"
							|| $counterType == "blog_post"
						)
							? "Y"
							: "N"
					),
					"APPROVED" => "Y",
					"POST_TIMESTAMP" => $arComment["LOG_DATE_TS"],
					"AUTHOR" => array(
						"ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
						"NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["NAME"],
						"LAST_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LAST_NAME"],
						"SECOND_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["SECOND_NAME"],
						"LOGIN" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LOGIN"],
						"PERSONAL_GENDER" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"],
						"AVATAR" => $arComment["AVATAR_SRC"]
					),
					"FILES" => false,
					"UF" => $arComment["UF"],
					"~POST_MESSAGE_TEXT" => $arComment["EVENT_FORMATTED"]["MESSAGE"],
					"POST_MESSAGE_TEXT" => $arComment["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"],
					"CLASSNAME" => $t ? "" : "",
					"BEFORE_HEADER" => "",
					"BEFORE_ACTIONS" => "",
					"AFTER_ACTIONS" => "",
					"AFTER_HEADER" => "",
					"BEFORE" => "",
					"AFTER" => $t,
					"BEFORE_RECORD" => "",
					"AFTER_RECORD" => ""
				);
				$count++;
			}

			$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
			$rights = CSocNetLogComponent::getCommentRights(array(
				"EVENT_ID" => $arLog["EVENT_ID"],
				"SOURCE_ID" => $arLog["SOURCE_ID"],
				"USER_ID" => $USER->getId()
			));
			$navComponentObject = false;
			$res = $APPLICATION->IncludeComponent(
				"bitrix:main.post.list",
				"",
				array(
					"TEMPLATE_ID" => '',
					"RATING_TYPE_ID" => $rating_entity_type,
					"ENTITY_XML_ID" => $entity_xml_id,
					"RECORDS" => $records,
					"NAV_STRING" => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?'.http_build_query(array(
							"action" => 'get_comments',
							"logid" => $arLog["ID"],
							"commentID" => isset($_REQUEST["commentID"]) ? $_REQUEST["commentID"] : 0,
							"commentTS" => isset($_REQUEST["commentTS"]) ? $_REQUEST["commentTS"] : 0,
							"lastLogTs" => isset($_REQUEST["lastLogTs"]) ? $_REQUEST["lastLogTs"] : 0,
							"et" => isset($_REQUEST["et"]) ? $_REQUEST["et"] : '',
							"exmlid" => $entity_xml_id,
							"p_user" => $_REQUEST["p_user"],
							"p_le" => $_REQUEST["p_le"],
							"p_group" => isset($_REQUEST["p_group"]) ? $_REQUEST["p_group"] : '',
							"p_dep" => isset($_REQUEST["p_dep"]) ? $_REQUEST["p_dep"] : '',
							"nt" => $_REQUEST["nt"],
							"sl" => $_REQUEST["sl"],
							"dtf" => $_REQUEST["dtf"],
							"tf" => $_REQUEST["tf"],
							"as" => $_REQUEST["as"],
							"lang" => LANGUAGE_ID,
							"site" => SITE_ID,
							"follow" => $follow,
							"ct" => $_REQUEST["ct"]
						)),
					"NAV_RESULT" => $db_res,
					"PREORDER" => "N",
					"RIGHTS" => array(
						"MODERATE" => "N",
						"EDIT" => $rights["COMMENT_RIGHTS_EDIT"],
						"DELETE" => $rights["COMMENT_RIGHTS_DELETE"]
					),
					"VISIBLE_RECORDS_COUNT" => $count,
					"ERROR_MESSAGE" => "",
					"OK_MESSAGE" => "",
					"VIEW_URL" => (
						isset($arComment["EVENT"]["URL"])
						&& strlen($arComment["EVENT"]["URL"]) > 0
							? $arComment["EVENT"]["URL"]
							: (
								isset($arParams["PATH_TO_LOG_ENTRY"])
								&& strlen($arParams["PATH_TO_LOG_ENTRY"]) > 0
									? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG_ENTRY"], array("log_id" => $arLog["ID"]))."?commentId=#ID#"
									: ""
							)
					),
					"EDIT_URL" => "__logEditComment('".$entity_xml_id."', '#ID#', '".$log_tmp_id."');",
					"MODERATE_URL" => "",
					"DELETE_URL" => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?lang='.LANGUAGE_ID.'&action=delete_comment&delete_comment_id=#ID#&post_id='.$arLog["ID"].'&site='.SITE_ID,
					"AUTHOR_URL" => $arParams["PATH_TO_USER"],

					"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],

					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"LAZYLOAD" => "Y",

					"NOTIFY_TAG" => "",
					"NOTIFY_TEXT" => "",
					"SHOW_MINIMIZED" => "Y",
					"SHOW_POST_FORM" => "Y",

					"IMAGE_SIZE" => "",
					"mfi" => ""
				),
				array(),
				null
			);
			RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
		}
	}
	elseif ($action == "change_favorites")
	{
		$log_id = intval($_REQUEST["log_id"]);
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			$strRes = CSocNetLogFavorites::Change($currentUserId, $log_id);

			if ($strRes)
			{
				if ($strRes == "Y")
					CSocNetLogFollow::Set(
						$currentUserId,
						"L".$log_id, 
						"Y",
						$arLog["LOG_UPDATE"]
					);
				$arResult["bResult"] = $strRes;
			}
			else
			{
				if($e = $GLOBALS["APPLICATION"]->GetException())
				{
					$arResult["strMessage"] = $e->GetString();
				}
				else
				{
					$arResult["strMessage"] = Loc::getMessage("SONET_LOG_FAVORITES_CANNOT_CHANGE", false, $lng);
				}
				$arResult["bResult"] = "E";
			}
		}
		else
		{
			$arResult["strMessage"] = Loc::getMessage("SONET_LOG_FAVORITES_INCORRECT_LOG_ID", false, $lng);
			$arResult["bResult"] = "E";
		}
	}
	elseif (
		$action == "delete" 
		&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
	)
	{
		$log_id = intval($_REQUEST["log_id"]);
		if ($log_id > 0)
		{
			$arResult["bResult"] = (CSocNetLog::Delete($log_id) ? "Y" : "N");
		}
	}
	elseif ($action == "get_more_destination")
	{
		$isExtranetInstalled = (CModule::IncludeModule("extranet") ? "Y" : "N");
		$isExtranetSite = ($isExtranetInstalled == "Y" && CExtranet::IsExtranetSite() ? "Y" : "N");
		$isExtranetUser = ($isExtranetInstalled == "Y" && !CExtranet::IsIntranetUser() ? "Y" : "N");
		$isExtranetAdmin = ($isExtranetInstalled == "Y" && CExtranet::IsExtranetAdmin() ? "Y" : "N");

		if ($isExtranetUser == "Y")
		{
			$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
		}
		elseif (
			$isExtranetInstalled == "Y"
			&& $isExtranetUser != "Y"
			&& $isExtranetAdmin != "Y"
		)
		{
			if (
				$isExtranetAdmin == "Y"
				&& $bCurrentUserIsAdmin
			)
			{
				$arAvailableExtranetUserID = CExtranet::GetMyGroupsUsers(SITE_ID);				
			}
			else
			{
				$arAvailableExtranetUserID = CExtranet::GetMyGroupsUsersSimple(CExtranet::GetExtranetSiteID());
			}
		}

		$arResult["arDestinations"] = false;
		$log_id = intval($_REQUEST["log_id"]);
		$created_by_id = intval($_REQUEST["created_by_id"]);
		$iDestinationLimit = intval($_REQUEST["dlim"]);

		if ($log_id > 0)
		{
			$arRights = array();
			$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetLogEntryGetRights");
			while ($arEvent = $db_events->Fetch())
			{
				if (ExecuteModuleEventEx(
						$arEvent, 
						array(
							array("LOG_ID" => $log_id),
							&$arRights
						)
					) === false
				)
				{
					$bSkipGetRights = true;
					break;
				}
			}
			if (!$bSkipGetRights)
			{
				$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $log_id));
				while ($arRight = $dbRight->Fetch())
				{
					$arRights[] = $arRight["GROUP_CODE"];
				}
			}

			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"PATH_TO_GROUP" => $_REQUEST["p_group"],
				"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"DESTINATION_LIMIT" => 100,
				"CHECK_PERMISSIONS_DEST" => "N"
			);

			if ($created_by_id > 0)
				$arParams["CREATED_BY"] = $created_by_id;

			$arDestinations = CSocNetLogTools::FormatDestinationFromRights($arRights, $arParams, $iMoreCount);

			if (is_array($arDestinations))
			{
				$iDestinationsHidden = 0;

				$arGroupID = CSocNetLogTools::GetAvailableGroups();

				foreach($arDestinations as $key => $arDestination)
				{
					if (
						array_key_exists("TYPE", $arDestination)
						&& array_key_exists("ID", $arDestination)
						&& (
							(
								$arDestination["TYPE"] == "SG"
								&& !in_array(intval($arDestination["ID"]), $arGroupID)
							)
							|| (
								in_array($arDestination["TYPE"], array("CRMCOMPANY", "CRMLEAD", "CRMCONTACT", "CRMDEAL"))
								&& CModule::IncludeModule("crm")
								&& !\Bitrix\Crm\Security\EntityAuthorization::checkReadPermission(
									CCrmLiveFeedEntity::ResolveEntityTypeID($arDestination["TYPE"]),
									$arDestination["ID"]
								)
							)
							|| (
								in_array($arDestination["TYPE"], array("DR", "D"))
								&& $isExtranetUser == "Y"
							)
							|| (
								$arDestination["TYPE"] == "U"
								&& isset($arUserIdVisible)
								&& is_array($arUserIdVisible)
								&& !in_array(intval($arDestination["ID"]), $arUserIdVisible)
							)
							|| (
								$arDestination["TYPE"] == "U"
								&& isset($arDestination["IS_EXTRANET"])
								&& $arDestination["IS_EXTRANET"] == "Y"
								&& isset($arAvailableExtranetUserID)
								&& is_array($arAvailableExtranetUserID)
								&& !in_array(intval($arDestination["ID"]), $arAvailableExtranetUserID)
							)
						)
					)
					{
						unset($arDestinations[$key]);
						$iDestinationsHidden++;
					}
				}

				$arResult["arDestinations"] = array_slice($arDestinations, $iDestinationLimit);
				$arResult["iDestinationsHidden"] = $iDestinationsHidden;
			}
		}
	}
	elseif ($action == "get_comment_src")
	{
		$arResult = false;
		$comment_id = intval($_REQUEST["comment_id"]);
		$post_id = intval($_REQUEST["post_id"]);

		if (
			$comment_id > 0 
			&& $post_id > 0
		)
		{
			$arRes = CSocNetLogComponent::getCommentByRequest($comment_id, $post_id, "edit");
			if ($arRes)
			{
				$arResult["id"] = intval($arRes["ID"]);
				$arResult["message"] = str_replace("<br />", "\n", $arRes["MESSAGE"]);
				$arResult["sourceId"] = (intval($arRes["SOURCE_ID"]) > 0 ? intval($arRes["SOURCE_ID"]) : intval($arRes["ID"]));
				$arResult["UF"] = (!empty($arRes["UF"]) ? $arRes["UF"] : array());
			}
		}
	}	
	elseif ($action == "delete_comment")
	{
		$arResult = false;
		$comment_id = intval($_REQUEST["delete_comment_id"]);
		$post_id = intval($_REQUEST["post_id"]);

		if (
			$comment_id > 0 
			&& $post_id > 0
		)
		{
			$arRes = CSocNetLogComponent::getCommentByRequest($comment_id, $post_id, "delete");
			if ($arRes)
			{
				$bSuccess = CSocNetLogComments::Delete($arRes["ID"], true);

				if (
					!$bSuccess
					&& ($e = $GLOBALS["APPLICATION"]->GetException())
				)
				{
					$errorMessage = $e->GetString();
				}

				$APPLICATION->IncludeComponent(
					"bitrix:main.post.list",
					"",
					array(
						"ENTITY_XML_ID" => $_REQUEST["ENTITY_XML_ID"],
						"PUSH&PULL" => array(
							"ID" => (
								$bSuccess
									? (
										$arRes["SOURCE_ID"] > 0
											? $arRes["SOURCE_ID"]
											: $arRes["ID"]
									)
									: false
							),
							"ACTION" => "DELETE"
						),
						"OK_MESSAGE" => ($bSuccess ? Loc::getMessage('SONET_LOG_COMMENT_DELETED', false, $lng) : ''),
						"ERROR_MESSAGE" => (!$bSuccess ? $errorMessage : '')
					)
				);
			}
		}
	}

	header('Content-Type:application/json; charset=UTF-8');
	?><?=\Bitrix\Main\Web\Json::encode($arResult)?><?
	/** @noinspection PhpUndefinedClassInspection */
	\CMain::finalActions();
	die;

}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>