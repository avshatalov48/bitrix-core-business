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
$entity_id = isset($_REQUEST["eid"])? $_REQUEST["eid"]: "";
$cb_id = isset($_REQUEST["cb_id"])? $_REQUEST["cb_id"]: "";
$event_id = (isset($_REQUEST["evid"]) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]): "";
$transport = (isset($_REQUEST["transport"]) && is_string($_REQUEST["transport"])) ? trim($_REQUEST["transport"]): "";

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && !is_array($_REQUEST["ls"])? trim($_REQUEST["ls"]): "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper\ServiceComment;
use Bitrix\Socialnetwork\Livefeed;

global $USER;

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

Loc::loadLanguageFile(__FILE__, $lng);

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
	$arSocNetAllowedSubscribeEntityTypesDesc = CSocNetAllowed::GetAllowedEntityTypesDesc();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (in_array($action, array("get_comment", "get_comments")))
	{
		CSocNetTools::InitGlobalExtranetArrays();
	}

	if (!$USER->IsAuthorized())
	{
		$arResult[0] = "*";
	}
	elseif (!check_bitrix_sessid())
	{
		$arResult[0] = "*";
	}
	elseif ($action == "get_raw_data") // deprecated, see socialnetwork.api.livefeed.getRawEntryData
	{
		$provider = \Bitrix\Socialnetwork\Livefeed\Provider::init(array(
			'ENTITY_TYPE' => (isset($_REQUEST['ENTITY_TYPE']) ? preg_replace("/[^a-z0-9_]/i", "", $_REQUEST['ENTITY_TYPE']) : false),
			'ENTITY_ID' => (isset($_REQUEST['ENTITY_ID']) ? intval($_REQUEST['ENTITY_ID']) : false),
			'LOG_ID' => (isset($_REQUEST['LOG_ID']) ? intval($_REQUEST['LOG_ID']) : false),
			'CLONE_DISK_OBJECTS' => true
		));

		if ($provider)
		{
			$arResult = array(
				'TITLE' => $provider->getSourceTitle(),
				'DESCRIPTION' => $provider->getSourceDescription(),
				'DISK_OBJECTS' => $provider->getSourceDiskObjects()
			);
			if (isset($_REQUEST["params"]))
			{
				if (
					isset($_REQUEST["params"]["getSonetGroupAvailableList"])
					&& !!$_REQUEST["params"]["getSonetGroupAvailableList"]
				)
				{
					$feature = $operation = false;
					if (
						isset($_REQUEST["params"]["checkParams"])
						&& isset($_REQUEST["params"]["checkParams"]["feature"])
						&& isset($_REQUEST["params"]["checkParams"]["operation"])
					)
					{
						$feature = $_REQUEST["params"]["checkParams"]["feature"];
						$operation = $_REQUEST["params"]["checkParams"]["operation"];
					}
					$arResult['GROUPS_AVAILABLE'] = $provider->getSonetGroupsAvailable($feature, $operation);
				}

				if (
					isset($_REQUEST["params"]["getLivefeedUrl"])
					&& !!$_REQUEST["params"]["getLivefeedUrl"]
				)
				{
					$arResult['LIVEFEED_URL'] = $provider->getLiveFeedUrl();
				}
			}

			if ($provider->getType() == Livefeed\Provider::TYPE_COMMENT)
			{
				$arResult['SUFFIX'] = $provider->getSuffix();
			}

			$logId = $provider->getLogId();
			if (intval($logId) > 0)
			{
				$arResult['LOG_ID'] =$logId;
			}
		}
	}
	elseif ($action == "create_task_comment") // deprecated, see socialnetwork.api.livefeed.createTaskComment
	{
		if (
			isset($_REQUEST['ENTITY_TYPE'])
			&& isset($_REQUEST['ENTITY_ID'])
			&& isset($_REQUEST['TASK_ID'])
		)
		{
			if (in_array($_REQUEST['ENTITY_TYPE'], array('BLOG_POST', 'BLOG_COMMENT')))
			{
				ServiceComment::processBlogCreateEntity([
					'ENTITY_TYPE' => \Bitrix\Socialnetwork\CommentAux\CreateEntity::ENTITY_TYPE_TASK,
					'ENTITY_ID' => (int)$_REQUEST['TASK_ID'],
					'SOURCE_ENTITY_TYPE' => preg_replace("/[^a-z0-9_]/i", '', $_REQUEST['ENTITY_TYPE']),
					'SOURCE_ENTITY_ID' => (int)$_REQUEST['ENTITY_ID'],
					'LIVE' => 'Y'
				]);
			}
			else
			{
				\Bitrix\Socialnetwork\Helper\ServiceComment::processLogEntryCreateEntity([
					'LOG_ID' => (!empty($_REQUEST['LOG_ID']) ? (int)$_REQUEST['LOG_ID'] : false),
					'ENTITY_TYPE' => \Bitrix\Socialnetwork\CommentAux\CreateEntity::ENTITY_TYPE_TASK,
					'ENTITY_ID' => (int)$_REQUEST['TASK_ID'],
					'POST_ENTITY_TYPE' => preg_replace("/[^a-z0-9_]/i", '', $_REQUEST['POST_ENTITY_TYPE']),
					'SOURCE_ENTITY_TYPE' => preg_replace("/[^a-z0-9_]/i", '', $_REQUEST['ENTITY_TYPE']),
					'SOURCE_ENTITY_ID' => (int)$_REQUEST['ENTITY_ID'],
					'LIVE' => 'Y',
				]);
			}
		}
	}
	elseif ($action == "set")
	{
		$arFields = false;

		if (in_array($ls, array("EVENT", "ALL")))
		{
			$arFields = array(
				"USER_ID" => $USER->getId(),
				"ENTITY_TYPE" => $entity_type,
				"ENTITY_ID" => $entity_id,
				"ENTITY_CB" => "N"
			);

			if($ls == "EVENT")
				$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
			else
				$arEventID = array("all");

		}
		elseif (in_array($ls, array("CB_ALL")))
		{
			$arFields = array(
				"USER_ID" => $USER->getId(),
				"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" => $cb_id,
				"ENTITY_CB" => "Y"
			);

			$arEventID = array("all");
		}

		if ($arFields && $transport <> '')
		{
			if (
				$arFields["ENTITY_CB"] != "Y"
				&& array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
				&& array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
				&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_SITE_ID"] == "Y"
				&& $site_id <> ''
			)
				$arFieldsVal["SITE_ID"] = $site_id;
			else
				$arFieldsVal["SITE_ID"] = false;

			if ($transport <> '')
				$arFieldsVal["TRANSPORT"] = $transport;

			foreach($arEventID as $event_id)
			{
				$arFields["EVENT_ID"] = $event_id;

				$dbResultTmp = CSocNetLogEvents::GetList(
					array(),
					$arFields,
					false,
					false,
					array("ID", "TRANSPORT")
				);

				$arFieldsSet = array_merge($arFields, $arFieldsVal);

				if ($arResultTmp = $dbResultTmp->Fetch())
				{
					if ($arFieldsVal["TRANSPORT"] == "I")
						CSocNetLogEvents::Delete($arResultTmp["ID"]);
					else
						$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
				}
				elseif($arFieldsVal["TRANSPORT"] != "I")
				{
					if (!array_key_exists("TRANSPORT", $arFieldsSet))
						$arFieldsSet["TRANSPORT"] = "I";

					$idTmp = CSocNetLogEvents::Add($arFieldsSet);
				}
			}
		}
	}
	elseif ($action == "set_transport_arr")
	{
		$arFields = false;

		if (is_array($ls_arr))
		{
			foreach($ls_arr as $ls => $transport)
			{
				$ls = trim($ls);

				if (in_array($ls, array("EVENT", "ALL")))
				{
					$arFields = array(
						"USER_ID" => $USER->getId(),
						"ENTITY_TYPE" => $entity_type,
						"ENTITY_ID" => $entity_id,
						"ENTITY_CB" => "N"
					);

					if($ls == "EVENT")
						$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
					else
						$arEventID = array("all");

				}
				elseif (in_array($ls, array("CB_ALL")))
				{
					$arFields = array(
						"USER_ID" => $USER->getId(),
						"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
						"ENTITY_ID" => $cb_id,
						"ENTITY_CB" => "Y"
					);

					$arEventID = array("all");
				}

				if ($arFields && $transport <> '')
				{
					if (
						$arFields["ENTITY_CB"] != "Y"
						&& array_key_exists($entity_type, $arSocNetAllowedSubscribeEntityTypesDesc)
						&& array_key_exists("HAS_SITE_ID", $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type])
						&& $arSocNetAllowedSubscribeEntityTypesDesc[$entity_type]["HAS_SITE_ID"] == "Y"
						&& $site_id <> ''
					)
						$arFieldsVal["SITE_ID"] = $site_id;
					else
						$arFieldsVal["SITE_ID"] = false;

					if ($transport <> '')
						$arFieldsVal["TRANSPORT"] = $transport;

					foreach($arEventID as $event_id)
					{
						$arFields["EVENT_ID"] = $event_id;

						$dbResultTmp = CSocNetLogEvents::GetList(
							array(),
							$arFields,
							false,
							false,
							array("ID", "TRANSPORT")
						);

						$arFieldsSet = array_merge($arFields, $arFieldsVal);

						if ($arResultTmp = $dbResultTmp->Fetch())
						{
							if ($arFieldsVal["TRANSPORT"] == "I")
								CSocNetLogEvents::Delete($arResultTmp["ID"]);
							else
								$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
						}
						elseif($arFieldsVal["TRANSPORT"] != "I")
						{
							if (!array_key_exists("TRANSPORT", $arFieldsSet))
								$arFieldsSet["TRANSPORT"] = "I";

							$idTmp = CSocNetLogEvents::Add($arFieldsSet);
						}
					}
				}
			}
		}
	}
	elseif (
		$action == "change_follow"
		&& $USER->isAuthorized()
	)
	{
		$arResult["SUCCESS"] = (
			($strRes = CSocNetLogFollow::Set($USER->getId(), "L".intval($_REQUEST["log_id"]), ($_REQUEST["follow"] == "Y" ? "Y" : "N")))
				? "Y"
				: "N"
		);

		if (isset($_REQUEST["follow"]) && $_REQUEST["follow"] == "Y")
		{
			\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
				'logId' => $_REQUEST["log_id"],
				'userId' => $USER->getId(),
				'typeList' => array(
					'COUNTER_COMMENT_PUSH'
				)
			));
		}
	}

	if (empty($_REQUEST['mobile_action']))
	{
		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	}
	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
