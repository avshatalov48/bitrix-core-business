<?php

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
$entity_type = (isset($_REQUEST["et"]) && is_string($_REQUEST["et"])) ? trim($_REQUEST["et"]): "";
$entity_id = ($_REQUEST['eid'] ?? '');
$cb_id = ($_REQUEST['cb_id'] ?? '');
$event_id = (isset($_REQUEST["evid"]) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]): "";
$transport = (isset($_REQUEST["transport"]) && is_string($_REQUEST["transport"])) ? trim($_REQUEST["transport"]): "";
$entity_xml_id = (isset($_REQUEST["exmlid"]) && is_string($_REQUEST["exmlid"])) ? trim($_REQUEST["exmlid"]): "";
$entity_xml_id = (!empty($entity_xml_id)) ? $entity_xml_id : ((isset($_REQUEST["ENTITY_XML_ID"]) && is_string($_REQUEST["ENTITY_XML_ID"])) ? trim($_REQUEST["ENTITY_XML_ID"]): "");

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && !is_array($_REQUEST["ls"])? trim($_REQUEST["ls"]): "";
$ls_arr = ($_REQUEST['ls_arr'] ?? '');

$st_id = (isset($_REQUEST["st_id"]) && is_string($_REQUEST["st_id"])) ? trim($_REQUEST["st_id"]): "";
$st_id = preg_replace("/[^a-z0-9_]/i", "", $st_id);

define("SITE_TEMPLATE_ID", $st_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $DB, $USER, $USER_FIELD_MANAGER, $CACHE_MANAGER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Component\LogEntry;
use Bitrix\Socialnetwork\ComponentHelper;

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

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = [];

	if (in_array($action, array("add_comment", "get_comment", "get_comments", "get_more_destination")))
	{
		CSocNetTools::InitGlobalExtranetArrays();
	}

	$currentUserId = 0;
	$currentUserExternalAuthId = '';
	$bCurrentUserUserAuthorized = $USER->IsAuthorized();

	if ($bCurrentUserUserAuthorized)
	{
		$currentUserId = (int)$USER->GetId();
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
	elseif (in_array($action, [ 'delete_comment', 'get_comments', 'add_comment' ]))
	{
		$logId = 0;
		$entityXmlId = '';

		switch ($action)
		{
			case 'delete_comment':
				$logId = (int)$_REQUEST["post_id"];
				break;
			case 'get_comments':
				$logId = (int)$_REQUEST["logid"];
				break;
			case 'add_comment':
				$logId = (int)$_REQUEST["log_id"];
				break;
			default:
		}

		if ($logId > 0)
		{
			$res = \Bitrix\Socialnetwork\LogTable::getList([
				'filter' => [
					'=ID' => $logId,
				],
				'select' => [ 'ID', 'EVENT_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'SOURCE_ID', 'PARAMS' ],
			]);
			if ($logFields = $res->fetch())
			{
				$liveFeedCommentsParams = ComponentHelper::getLFCommentsParams($logFields);
				$entityXmlId = ($liveFeedCommentsParams['ENTITY_XML_ID'] ?? '');
			}
		}

		if ($action === "delete_comment")
		{
			$errorMessage = '';
			$deleteResult = false;

			try
			{
				$deleteResult = LogEntry::deleteComment([
					'logId' => (int)$_REQUEST['post_id'],
					'commentId' => (int)$_REQUEST['delete_comment_id'],
				]);
			}
			catch (Exception $e)
			{
				$errorMessage = $e->getMessage();
			}

			$APPLICATION->IncludeComponent(
				'bitrix:main.post.list',
				'',
				[
					'ENTITY_XML_ID' => $entityXmlId,
					'PUSH&PULL' => [
						"ID" => $deleteResult,
						"ACTION" => "DELETE"
					],
					'OK_MESSAGE' => ($deleteResult ? Loc::getMessage('SONET_LOG_COMMENT_DELETED', false, $lng) : ''),
					'ERROR_MESSAGE' => $errorMessage,
				]
			);
		}
		elseif ($action === "get_comments")
		{
			$arResult["arComments"] = array();

			$log_tmp_id = $_REQUEST["logid"];
			$log_entity_type = $entity_type;
			$follow = (isset($_REQUEST["follow"]) && $_REQUEST["follow"] === "Y" ? "Y" : "N");
			$counterType = ($_REQUEST["ct"] ?? false);

			$arListParams = (
				mb_strpos($log_entity_type, "CRM") === 0
				&& $currentUserExternalAuthId !== 'email'
				&& ModuleManager::isModuleInstalled("crm")
					? array("IS_CRM" => "Y", "CHECK_CRM_RIGHTS" => "Y")
					: array("CHECK_RIGHTS" => "Y", "USE_SUBSCRIBE" => "N")
			);

			$arLog = [];
			if ((int)$log_tmp_id > 0)
			{
				$rsLog = CSocNetLog::getList(
					[],
					[ 'ID' => $log_tmp_id ],
					false,
					false,
					[ 'ID', 'EVENT_ID', 'SOURCE_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID' ],
					$arListParams
				);
				if ($rsLog)
				{
					$arLog = $rsLog->Fetch();
				}

				if (
					empty($arLog)
					&& !empty($arListParams['IS_CRM'])
					&& $arListParams['IS_CRM'] === 'Y'
				)
				{
					$arListParams = [
						'CHECK_RIGHTS' => 'Y',
						'USE_SUBSCRIBE' => 'N'
					];
					$rsLog = CSocNetLog::GetList(array(), array("ID" => $log_tmp_id), false, false, array("ID", "EVENT_ID", "SOURCE_ID", "RATING_TYPE_ID", "RATING_ENTITY_ID"), $arListParams);
					if ($rsLog)
					{
						$arLog = $rsLog->Fetch();
					}
				}
			}

			if (!empty($arLog))
			{
				$postContentTypeId = $commentContentTypeId = $commentEntitySuffix = '';
				$contentId = \Bitrix\Socialnetwork\Livefeed\Provider::getContentId($arLog);
				if (
					!empty($contentId['ENTITY_TYPE'])
					&& ($postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($contentId['ENTITY_TYPE']))
					&& ($commentProvider = $postProvider->getCommentProvider())
				)
				{
					$postContentTypeId = $postProvider->getContentTypeId();
					$commentProviderClassName = get_class($commentProvider);
					$reflectionClass = new ReflectionClass($commentProviderClassName);

					$canGetCommentContent = ($reflectionClass->getMethod('initSourceFields')->class === $commentProviderClassName);
					if ($canGetCommentContent)
					{
						$commentContentTypeId = $commentProvider->getContentTypeId();
					}

					$commentProvider->setLogEventId($arLog['EVENT_ID']);
					$commentEntitySuffix = $commentProvider->getSuffix();
				}

				$arParams = array(
					"PATH_TO_USER" => $_REQUEST["p_user"],
					"PATH_TO_GROUP" => $_REQUEST["p_group"],
					"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
					"PATH_TO_LOG_ENTRY" => $_REQUEST["p_le"],
					"NAME_TEMPLATE" => $_REQUEST["nt"],
					"NAME_TEMPLATE_WO_NOBR" => str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $_REQUEST["nt"]),
					"SHOW_LOGIN" => $_REQUEST["sl"],
					"DATE_TIME_FORMAT" => (isset($_REQUEST["dtf"]) ? $_REQUEST["dtf"] : CSite::GetDateFormat()),
					"DATE_TIME_FORMAT_WITHOUT_YEAR" => ($_REQUEST["dtfwoy"] ?? CSite::GetDateFormat()),
					"TIME_FORMAT" => ($_REQUEST["tf"] ?? CSite::GetTimeFormat()),
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
						$CACHE_MANAGER->StartTagCache($cache_path);
					}

					$arFilter = array("LOG_ID" => $log_tmp_id);
					$arListParams = array("USE_SUBSCRIBE" => "N");

					$arSelect = array(
						"ID", "LOG_ID", "SOURCE_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID",
						"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
						"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
						"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER", "CREATED_BY_EXTERNAL_AUTH_ID",
						"LOG_SITE_ID", "LOG_SOURCE_ID",
						"RATING_TYPE_ID", "RATING_ENTITY_ID",
						"SHARE_DEST",
						"UF_*"
					);

					$arUFMeta = LogEntry::getUserFieldsFMetaData();

					$arAssets = [
						"CSS" => [],
						"JS" => []
					];

					$dbComments = CSocNetLogComments::GetList(
						[
							'LOG_DATE' => 'ASC',
							'ID' => 'ASC'
						],
						$arFilter,
						false,
						false,
						$arSelect,
						$arListParams
					);

					$commentsList = $commentSourceIdList = array();
					while($arComments = $dbComments->GetNext())
					{
						if (!empty($arComments['SHARE_DEST']))
						{
							$arComments['SHARE_DEST'] = htmlspecialcharsback($arComments['SHARE_DEST']);
						}

						if (defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->RegisterTag('USER_NAME_' . (int)$arComments['USER_ID']);
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

						$commentsList[] = $arComments;
						if ((int)$arComments['SOURCE_ID'] > 0)
						{
							$commentSourceIdList[] = (int)$arComments['SOURCE_ID'];
						}
					}

					if (
						!empty($commentSourceIdList)
						&& !empty($commentProvider)
					)
					{
						$sourceAdditonalData = $commentProvider->getAdditionalData(array(
							'id' => $commentSourceIdList
						));

						if (!empty($sourceAdditonalData))
						{
							foreach($commentsList as $key => $comment)
							{
								if (
									!empty($comment['SOURCE_ID'])
									&& isset($sourceAdditonalData[$comment['SOURCE_ID']])
								)
								{
									$commentsList[$key]['ADDITIONAL_DATA'] = $sourceAdditonalData[$comment['SOURCE_ID']];
								}
							}
						}
					}

					foreach($commentsList as $arComment)
					{
						$arResult["arComments"][$arComment["ID"]] = LogEntry::getLogCommentRecord($arComment, $arParams, $arAssets);
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
							$CACHE_MANAGER->EndTagCache();
						}
					}
				}

				if (
					(int)$_REQUEST["commentID"] > 0
					|| (int)$_REQUEST["commentTS"] > 0
				)
				{
					foreach($arResult["arComments"] as $key => $res)
					{
						if (
							(
								(int)$_REQUEST["commentTS"] > 0
								&& (int)$res["LOG_DATE_TS"] > (int)$_REQUEST["commentTS"]
							)
							|| (
								(int)$_REQUEST["commentTS"] > 0
								&& (int)$res["LOG_DATE_TS"] === (int)$_REQUEST["commentTS"]
								&& (int)$key >= (int)$_REQUEST["commentID"]
							)
							|| (
								(int)$_REQUEST["commentTS"] <= 0
								&& (int)$key >= (int)$_REQUEST["commentID"]
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

				$records = [];
				$arEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arLog["EVENT_ID"]);
				$offset = CTimeZone::GetOffset();

				$count = 0;
				while (
					($arComment = $db_res->fetch())
					&& $arComment
				)
				{
					if ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
						[
							'POST_TEXT' => $arComment['EVENT_FORMATTED']['MESSAGE'],
							'SHARE_DEST' => $arComment['EVENT']['SHARE_DEST'],
							'SOURCE_ID' => (int)$arComment['EVENT']['SOURCE_ID'],
							'EVENT_ID' => $arComment['EVENT']['EVENT_ID'],
							'RATING_TYPE_ID' => $arComment['EVENT']['RATING_TYPE_ID']
						],
						[
							'eventId' => $arComment['EVENT']['EVENT_ID']
						]
					))
					{
						$commentAuxProvider->setOptions([
							'suffix' => $commentEntitySuffix,
							'logId' => $log_tmp_id,
							'cache' => false,
							'entityId' => $arLog['SOURCE_ID'],
							'entityType' => $arLog['RATING_TYPE_ID'],
						]);

						$arComment['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] = $commentAuxProvider->getText();
						$arComment['AUX'] = $commentAuxProvider->getType();
					}

					$commentId = ($arComment["EVENT"]["SOURCE_ID"] ? $arComment["EVENT"]["SOURCE_ID"] : $arComment["EVENT"]["ID"]);
					$timestamp = ($arComment["LOG_DATE_TS"]);

					$datetime_formatted = CComponentUtil::getDateTimeFormatted(array(
						'TIMESTAMP' => $timestamp,
						'DATETIME_FORMAT' => $arParams["DATE_TIME_FORMAT"],
						'DATETIME_FORMAT_WITHOUT_YEAR' => (isset($arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"]) ? $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"] : false),
						'TZ_OFFSET' => $offset
					));

					ob_start();
					?><script>
						top.arLogCom<?=$arLog["ID"]?><?=$commentId?> = '<?=$arComment["EVENT"]["ID"]?>';<?php
					?></script><?php
					$t = ob_get_clean();

					$records[$commentId] = array(
						"ID" => $commentId,
						"NEW" => (
							$lastLogTs > 0
							&& $arComment["LOG_DATE_TS"] > ($lastLogTs + $offset)
							&& $follow === "Y"
							&& (int)$arComment["EVENT"]["USER_ID"] !== $currentUserId
							&& (
								$counterType === "**"
								|| $counterType === "CRM_**"
								|| $counterType === "blog_post"
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
							"AVATAR" => $arComment["AVATAR_SRC"],
							"EXTERNAL_AUTH_ID" => ($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["EXTERNAL_AUTH_ID"] ?? false),
							"UF_USER_CRM_ENTITY" => ($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["UF_USER_CRM_ENTITY"] ?? false)
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
						"AFTER_RECORD" => "",
						"RATING_VOTE_ID" => $rating_entity_type.'_'.$commentId.'-'.(time() + random_int(0, 1000)),
						"AUX" => (!empty($arComment["AUX"]) ? $arComment["AUX"] : ''),
						"AUX_LIVE_PARAMS" => (!empty($arComment["AUX_LIVE_PARAMS"]) ? $arComment["AUX_LIVE_PARAMS"] : array())
					);
					$count++;
				}

				$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
				$rights = CSocNetLogComponent::getCommentRights([
					"EVENT_ID" => $arLog["EVENT_ID"],
					"SOURCE_ID" => $arLog["SOURCE_ID"],
					"USER_ID" => $USER->getId(),
				]);
				$navComponentObject = false;
				$res = $APPLICATION->IncludeComponent(
					"bitrix:main.post.list",
					"",
					array(
						"TEMPLATE_ID" => '',
						"RATING_TYPE_ID" => $rating_entity_type,
						"ENTITY_XML_ID" => $entityXmlId,
						"POST_CONTENT_TYPE_ID" => $postContentTypeId,
						"COMMENT_CONTENT_TYPE_ID" => $commentContentTypeId,
						"RECORDS" => $records,
						"NAV_STRING" => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?'.http_build_query(array(
								"action" => 'get_comments',
								"logid" => $arLog["ID"],
								"commentID" => $_REQUEST["commentID"] ?? 0,
								"commentTS" => $_REQUEST["commentTS"] ?? 0,
								"lastLogTs" => $_REQUEST["lastLogTs"] ?? 0,
								"et" => $_REQUEST["et"] ?? '',
								"exmlid" => $entityXmlId,
								"p_user" => $_REQUEST["p_user"],
								"p_le" => $_REQUEST["p_le"],
								"p_group" => $_REQUEST["p_group"] ?? '',
								"p_dep" => $_REQUEST["p_dep"] ?? '',
								"nt" => $_REQUEST["nt"],
								"sl" => $_REQUEST["sl"],
								"dtf" => $_REQUEST["dtf"],
								"dtfwoy" => $_REQUEST["dtfwoy"],
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
							"DELETE" => $rights["COMMENT_RIGHTS_DELETE"],
							"CREATETASK" => (ModuleManager::isModuleInstalled('tasks') && $canGetCommentContent ? "Y" : "N"),
							"CREATESUBTASK" => (
								ModuleManager::isModuleInstalled('tasks')
								&& $canGetCommentContent
								&& preg_match('/^TASK_(\d+)$/i', $entityXmlId)
									? 'Y'
									: 'N'
							)
						),
						"VISIBLE_RECORDS_COUNT" => $count,
						"ERROR_MESSAGE" => "",
						"OK_MESSAGE" => "",
						"VIEW_URL" => (
							isset($arComment["EVENT"]["URL"])
							&& $arComment["EVENT"]["URL"] <> ''
								? $arComment["EVENT"]["URL"]
								: (
									isset($arParams["PATH_TO_LOG_ENTRY"])
									&& $arParams["PATH_TO_LOG_ENTRY"] <> ''
										? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG_ENTRY"], array("log_id" => $arLog["ID"]))."?commentId=#ID#"
										: ""
								)
						),
						"EDIT_URL" => "__logEditComment('".$entityXmlId."', '#ID#', '".$log_tmp_id."');",
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
		elseif ($action === "add_comment")
		{
			$cuid = (isset($_REQUEST["cuid"]) && is_string($_REQUEST["cuid"])) ? trim($_REQUEST["cuid"]): "";
			$cuid = preg_replace("/[^a-z0-9]/i", "", $cuid);

			$arResult = array_merge($arResult, LogEntry::addComment([
				'logId' => $_REQUEST['log_id'],
				'currentUserId' => $currentUserId,
				'currentUserExternalAuthId' => $currentUserExternalAuthId,
				'crm' => ($_REQUEST['crm'] ?? 'N'),
				'languageId' => $lng,
				'commentParams' => $_REQUEST['id'],
				'pathToSmile' => $_REQUEST['p_smile'],
				'pathToLogEntry' => $_REQUEST['p_le'],
				'pathToUser' => $_REQUEST['p_user'],
				'pathToUserBlogPost' => $_REQUEST['p_ubp'],
				'pathToGroupBlogPost' => $_REQUEST['p_gbp'],
				'pathToUserMicroBlogPost' => $_REQUEST['p_umbp'],
				'pathToGroupMicroBlogPost' => $_REQUEST['p_gmbp'],
				'dateTimeFormat' => ($_REQUEST['dtf'] ?? \CSite::getTimeFormat()),
				'blogAllowPostCode' => $_REQUEST['bapc'],
				'message' => $_REQUEST['message'],
				'forumId' => $_REQUEST['f_id'],
				'siteId' => $site_id,
				'commentUid' => $cuid,
				'nameTemplate' => $_REQUEST['nt'],
				'showLogin' => $_REQUEST['sl'],
				'avatarSize' => $_REQUEST['as'],
				'pull' => $_REQUEST['pull'],
				'entityXmlId' => $entityXmlId,
				'decode' => true,
			]));
		}
	}
	elseif ($action === "change_favorites")
	{
		$log_id = (int)$_REQUEST["log_id"];
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			$strRes = CSocNetLogFavorites::Change($currentUserId, $log_id);

			if ($strRes)
			{
				if ($strRes === "Y")
				{
					ComponentHelper::userLogSubscribe(array(
						'logId' => $log_id,
						'userId' => $currentUserId,
						'typeList' => array(
							'FOLLOW',
							'COUNTER_COMMENT_PUSH'
						),
						'followDate' => $arLog["LOG_UPDATE"]
					));
				}
				$arResult["bResult"] = $strRes;
			}
			else
			{
				if($e = $APPLICATION->GetException())
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
		$action === "delete"
		&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
	)
	{
		$log_id = (int)$_REQUEST["log_id"];
		if ($log_id > 0)
		{
			$arResult["bResult"] = (CSocNetLog::Delete($log_id) ? "Y" : "N");
		}
	}
	elseif ($action === "get_more_destination") // deprecated, todo: create method from \Bitrix\Socialnetwork\Controller\Livefeed\LogEntry::getHiddenDestinationsAction()
	{
		$isExtranetInstalled = (CModule::IncludeModule("extranet") ? "Y" : "N");
		$isExtranetSite = ($isExtranetInstalled === "Y" && CExtranet::IsExtranetSite() ? "Y" : "N");
		$isExtranetUser = ($isExtranetInstalled === "Y" && !CExtranet::IsIntranetUser() ? "Y" : "N");
		$isExtranetAdmin = ($isExtranetInstalled === "Y" && CExtranet::IsExtranetAdmin() ? "Y" : "N");

		if ($isExtranetUser === "Y")
		{
			$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
		}
		elseif (
			$isExtranetInstalled === "Y"
			&& $isExtranetUser !== "Y"
			&& $isExtranetAdmin !== "Y"
		)
		{
			if (
				$isExtranetAdmin === "Y"
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
		$log_id = (int)$_REQUEST["log_id"];
		$created_by_id = (int)$_REQUEST["created_by_id"];
		$iDestinationLimit = (int)$_REQUEST["dlim"];

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
								$arDestination["TYPE"] === "SG"
								&& !in_array((int)$arDestination["ID"], $arGroupID)
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
								&& $isExtranetUser === "Y"
							)
							|| (
								$arDestination["TYPE"] === "U"
								&& isset($arUserIdVisible)
								&& is_array($arUserIdVisible)
								&& !in_array((int)$arDestination["ID"], $arUserIdVisible)
							)
							|| (
								isset($arDestination["IS_EXTRANET"], $arAvailableExtranetUserID)
								&& $arDestination["TYPE"] === "U"
								&& $arDestination["IS_EXTRANET"] === "Y"
								&& is_array($arAvailableExtranetUserID)
								&& !in_array((int)$arDestination["ID"], $arAvailableExtranetUserID)
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
	elseif ($action === "get_comment_src")
	{
		$arResult = false;
		$comment_id = intval($_REQUEST["comment_id"]);
		$post_id = (int)$_REQUEST["post_id"];

		if (
			$comment_id > 0
			&& $post_id > 0
		)
		{
			$arRes = CSocNetLogComponent::getCommentByRequest($comment_id, $post_id, "edit");
			if ($arRes)
			{
				$arResult["id"] = (int)$arRes["ID"];
				$arResult["message"] = str_replace("<br />", "\n", $arRes["MESSAGE"]);
				$arResult["sourceId"] = ((int)$arRes["SOURCE_ID"] > 0 ? (int)$arRes["SOURCE_ID"] : (int)$arRes["ID"]);
				$arResult["UF"] = (!empty($arRes["UF"]) ? $arRes["UF"] : array());
			}
		}
	}
	elseif ($action === "get_comment")
	{
		$comment_id = $_REQUEST["cid"];

		if ($arComment = CSocNetLogComments::GetByID($comment_id))
		{
			if (
				mb_strpos($arComment["ENTITY_TYPE"], "CRM") === 0
				&& $currentUserExternalAuthId !== 'email'
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
				(int)$arComment["LOG_ID"] > 0
				&& ($rsLog = CSocNetLog::GetList(array(), array("ID" => $arComment["LOG_ID"]), false, false, array("ID", "EVENT_ID"), $arListParams))
				&& ($arLog = $rsLog->Fetch())
			)
			{
				$arResult["arComment"] = $arComment;

				$dateFormated = FormatDate(
					CDatabase::DateFormatToPHP(FORMAT_DATE),
					MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComment) ? $arComment["LOG_DATE_FORMAT"] : $arComment["LOG_DATE"])
				);

				$timeFormat = ($_REQUEST["dtf"] ?? CSite::GetTimeFormat());

				$timeFormated = FormatDateFromDB(
					(
					array_key_exists("LOG_DATE_FORMAT", $arComment)
						? $arComment["LOG_DATE_FORMAT"]
						: $arComment["LOG_DATE"]
					),
					(
					mb_stripos($timeFormat, 'a')
					|| (
						$timeFormat === 'FULL'
						&& (mb_strpos(FORMAT_DATETIME, 'T') !== false || mb_strpos(FORMAT_DATETIME, 'TT') !== false)
					) !== false
						? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'H:MI TT' : 'H:MI T')
						: 'HH:MI'
					)
				);

				if ((int)$arComment["USER_ID"] > 0)
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
					$bUseLogin = $arParams["SHOW_LOGIN"] !== "N" ? true : false;
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

	$APPLICATION->RestartBuffer();

	header('Content-Type:application/json; charset=UTF-8');
	?><?=\Bitrix\Main\Web\Json::encode($arResult)?><?php
	/** @noinspection PhpUndefinedClassInspection */
	\CMain::finalActions();
	die;

}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
