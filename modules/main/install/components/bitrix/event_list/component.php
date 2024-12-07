<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * @deprecated Use bitrix:intranet.event.log
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 */

if(!$USER->CanDoOperation('view_event_log'))
{
	ShowError(GetMessage("ACCESS_DENIED"));
	return;
}

/** @var CEventMain[] $arModuleObjects */
$arModuleObjects = array();
$arAllFilter = array();

$currentTemplateName = $this->getTemplateName();
if ($currentTemplateName == "grid")
{
	$arResult["GRID_ID"] = "event_list_grid";
	$arResult["ELEMENTS_ROWS"] = array();

	$grid = new CGridOptions($arResult["GRID_ID"]);
	$nav = $grid->GetNavParams();
	$arParams["PAGE_NUM"] = $nav["nPageSize"];
}

foreach(GetModuleEvents("main", "OnEventLogGetAuditHandlers", true) as $arEvent)
{
	$ModuleEvent = ExecuteModuleEventEx($arEvent);
	$arModuleObjects[] = $ModuleEvent;
	$arAllFilter = $arAllFilter + $ModuleEvent->GetFilter();
}
if (isset($arParams["FILTER"]) && is_array($arParams["FILTER"]))
{
	foreach($arParams["FILTER"] as $key => $val)
	{
		$arResult["ActiveFeatures"][$val] = $arAllFilter[$val] ?? null;
	}
}
if (isset($arResult["ActiveFeatures"]) && is_array($arResult["ActiveFeatures"]) && !empty($arResult["ActiveFeatures"]))
{
	$arResult["NO_ACTIVE_FEATURES"] = false;
	if (!isset($_REQUEST["flt_event_id"]))
	{
		$arParams["EVENT_ID"] = CUserOptions::GetOption("main", "~event_list");
		$flt_event_id = (empty($arParams["EVENT_ID"])) ? $arParams["FILTER"] : $arParams["EVENT_ID"];
	}
	else
	{
		$arResult["flt_event_id"] = $_REQUEST["flt_event_id"];
		if (array_key_exists("flt_event_id_all", $_REQUEST) && $_REQUEST["flt_event_id_all"] == "Y")
		{
			$arParams["EVENT_ID"] = "";
			$flt_event_id = $arParams["FILTER"];
			if($USER->IsAuthorized() && check_bitrix_sessid())
				CUserOptions::DeleteOption("main", "~event_list");
		}
		else
		{
			$flt_event_id = $_REQUEST["flt_event_id"];    // checked events
			foreach($flt_event_id as $key => $val)
				$flt_event_id[$key] = htmlspecialcharsbx($val);
			$arParams["EVENT_ID"] = $flt_event_id;
			if($USER->IsAuthorized() && check_bitrix_sessid())
				CUserOptions::SetOption("main", "~event_list", $arParams["EVENT_ID"]);
		}
	}

	$arObjectTypes = array(
		"USER_AUTHORIZE" => "[USER_AUTHORIZE] ".GetMessage("MAIN_EVENTLOG_USER_AUTHORIZE"),
		"USER_DELETE" => "[USER_DELETE] ".GetMessage("MAIN_EVENTLOG_USER_DELETE"),
		"USER_INFO" => "[USER_INFO] ".GetMessage("MAIN_EVENTLOG_USER_INFO"),
		"USER_LOGIN" => "[USER_LOGIN] ".GetMessage("MAIN_EVENTLOG_USER_LOGIN"),
		"USER_LOGINBYHASH" => "[USER_LOGINBYHASH] ".GetMessage("MAIN_EVENTLOG_USER_LOGINBYHASH_FAILED"),
		"USER_LOGOUT" => "[USER_LOGOUT] ".GetMessage("MAIN_EVENTLOG_USER_LOGOUT"),
		"USER_PASSWORD_CHANGED" => "[USER_PASSWORD_CHANGED] ".GetMessage("MAIN_EVENTLOG_USER_PASSWORD_CHANGED"),
		"USER_REGISTER" => "[USER_REGISTER] ".GetMessage("MAIN_EVENTLOG_USER_REGISTER"),
		"USER_REGISTER_FAIL" => "[USER_REGISTER_FAIL] ".GetMessage("MAIN_EVENTLOG_USER_REGISTER_FAIL"),
		"USER_GROUP_CHANGED" => "[USER_GROUP_CHANGED] ".GetMessage("MAIN_EVENTLOG_GROUP"),
		"GROUP_POLICY_CHANGED" => "[GROUP_POLICY_CHANGED] ".GetMessage("MAIN_EVENTLOG_GROUP_POLICY"),
		"MODULE_RIGHTS_CHANGED" => "[MODULE_RIGHTS_CHANGED] ".GetMessage("MAIN_EVENTLOG_MODULE"),
		"FILE_PERMISSION_CHANGED" => "[FILE_PERMISSION_CHANGED] ".GetMessage("MAIN_EVENTLOG_FILE"),
		"TASK_CHANGED" => "[TASK_CHANGED] ".GetMessage("MAIN_EVENTLOG_TASK"),
		"MP_MODULE_INSTALLED" => "[MP_MODULE_INSTALLED] ".GetMessage("MAIN_EVENTLOG_MP_MODULE_INSTALLED"),
		"MP_MODULE_UNINSTALLED" => "[MP_MODULE_UNINSTALLED] ".GetMessage("MAIN_EVENTLOG_MP_MODULE_UNINSTALLED"),
		"MP_MODULE_DELETED" => "[MP_MODULE_DELETED] ".GetMessage("MAIN_EVENTLOG_MP_MODULE_DELETED"),
		"MP_MODULE_DOWNLOADED" => "[MP_MODULE_DOWNLOADED] ".GetMessage("MAIN_EVENTLOG_MP_MODULE_DOWNLOADED"),
	);

	$arFilter["MODULE_ITEM"] = array();           //filter for GetList
	if (\Bitrix\Main\ModuleManager::isModuleInstalled("bitrix24"))
	{
		$arFilter["MODULE_ITEM"][] = array(
			"AUDIT_TYPE_ID" => "USER_AUTHORIZE"
		);
	}
	else
	{
		foreach($arModuleObjects as $key => $val)
		{
			$arObjectTypes = array_merge($arObjectTypes, $val->GetAuditTypes());

			$ar = $val->GetFilter();
			$filters = array_keys($ar);
			$var = array_intersect($filters, $flt_event_id);
			if ($var)
			{
				if(isset($ar["IBLOCK"]))
				{
					//iblock has more complex structure because logs are kept for individual blocks
					$var = $filters;
				}
				$arFilter["MODULE_ITEM"] = array_merge($arFilter["MODULE_ITEM"], $val->GetFilterSQL($var));
			}
		}
	}

	//USER
	if (isset($_REQUEST["flt_created_by_id"]) && is_array($_REQUEST["flt_created_by_id"]))
		$_REQUEST["flt_created_by_id"] = $_REQUEST["flt_created_by_id"][0];

	$find_user_id = "";
	if (isset($_REQUEST["flt_created_by_id"]) && intval($_REQUEST["flt_created_by_id"]) > 0)
	{
		$find_user_id = $_REQUEST["flt_created_by_id"];
	}
	else
	{
		if (CModule::IncludeModule("socialnetwork"))
		{
			$arFoundUsers = CSocNetUser::SearchUser($_REQUEST["flt_created_by_id"] ?? '', false);
			if (is_array($arFoundUsers) && !empty($arFoundUsers))
				$find_user_id = key($arFoundUsers);
		}
	}

	// for date
	if (
		array_key_exists("flt_date_datesel", $_REQUEST)
		&& $_REQUEST["flt_date_datesel"] <> ''
	)
	{
		$_REQUEST["flt_date_datesel"] = htmlspecialcharsbx($_REQUEST["flt_date_datesel"]);
		switch($_REQUEST["flt_date_datesel"])
		{
			case "today":
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(MakeTimeStamp(date("d.m.Y 00:00:00"), "DD.MM.YYYY HH:MI:SS"), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(time(), "FULL");

				break;
			case "yesterday":
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(MakeTimeStamp(date("d.m.Y 00:00:00", time() - 86400), "DD.MM.YYYY HH:MI:SS"), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(MakeTimeStamp(date("d.m.Y 23:59:59", time() - 86400), "DD.MM.YYYY HH:MI:SS"), "FULL");
				break;
			case "week":
				$day = date("w");
				if($day == 0)
					$day = 7;
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time()-($day-1)*86400, "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(time()+(7-$day)*86400, "FULL");
				break;
			case "week_ago":
				$day = date("w");
				if($day == 0)
					$day = 7;
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time()-($day-1+7)*86400, "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(time()-($day)*86400, "FULL");
				break;
			case "month":
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")+1, 0), "FULL");
				break;
			case "month_ago":
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")-1, 1), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 0), "FULL");
				break;
			case "days":
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time() - intval($_REQUEST["flt_date_days"])*86400, "FULL");
				$arParams["LOG_DATE_TO"] = "";
				break;
			case "exact":
				$day = ConvertDateTime($_REQUEST["flt_date_from"], "DD.MM.YYYY");
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(MakeTimeStamp($day." 00:00:00", "DD.MM.YYYY HH:MI:SS"), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(MakeTimeStamp($day." 23:59:59", "DD.MM.YYYY HH:MI:SS"), "FULL");

				break;
			case "after":
				$arParams["LOG_DATE_FROM"] = $_REQUEST["flt_date_from"];
				$arParams["LOG_DATE_TO"] = "";
				break;
			case "before":
				$arParams["LOG_DATE_FROM"] = "";
				$arParams["LOG_DATE_TO"] = $_REQUEST["flt_date_to"];
				break;
			case "interval":
				$dayFrom = ConvertDateTime($_REQUEST["flt_date_from"], "DD.MM.YYYY");
				$dayTo = ConvertDateTime($_REQUEST["flt_date_to"], "DD.MM.YYYY");
				$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(MakeTimeStamp($dayFrom." 00:00:00", "DD.MM.YYYY HH:MI:SS"), "FULL");
				$arParams["LOG_DATE_TO"] = ConvertTimeStamp(MakeTimeStamp($dayTo." 23:59:59", "DD.MM.YYYY HH:MI:SS"), "FULL");
				break;
		}
	}
	elseif (array_key_exists("flt_date_datesel", $_REQUEST))
	{
		$arParams["LOG_DATE_FROM"] = "";
		$arParams["LOG_DATE_TO"] = "";
	}
	else
	{
		if (array_key_exists("flt_date_from", $_REQUEST))
		{
			$_REQUEST["flt_date_from"] = htmlspecialcharsbx($_REQUEST["flt_date_from"]);
			$arParams["LOG_DATE_FROM"] = trim($_REQUEST["flt_date_from"]);
		}
		if (array_key_exists("flt_date_to", $_REQUEST))
		{
			$_REQUEST["flt_date_to"] = htmlspecialcharsbx($_REQUEST["flt_date_to"]);
			$arParams["LOG_DATE_TO"] = trim($_REQUEST["flt_date_to"]);
		}
	}
	//=============End date

	if (
		array_key_exists("flt_ip", $_REQUEST)
		&& $_REQUEST["flt_ip"] <> ''
	)
	{
		$ip = htmlspecialcharsbx($_REQUEST["flt_ip"]);
		$arParams["IP"] = trim($ip);
	}

	function CheckFilter()
	{
		if(!empty($_REQUEST["flt_date_from"]))
		{
			if(!CheckDateTime($_REQUEST["flt_date_from"], CSite::GetDateFormat("FULL")))
				return false;
		}
		if(!empty($_REQUEST["flt_date_to"]))
		{
			if(!CheckDateTime($_REQUEST["flt_date_to"], CSite::GetDateFormat("FULL")))
				return false;
		}
		return true;
	}

	if(CheckFilter())
	{
		if (!empty($arFilter["MODULE_ITEM"]))
			$arEventFilter["=MODULE_ITEM"] = $arFilter["MODULE_ITEM"];
		if (!empty($arParams["LOG_DATE_FROM"]))
			$arEventFilter["TIMESTAMP_X_1"] = $arParams["LOG_DATE_FROM"];
		if (!empty($arParams["LOG_DATE_TO"]))
			$arEventFilter["TIMESTAMP_X_2"] = $arParams["LOG_DATE_TO"];
		if (!empty($arParams["IP"]))
			$arEventFilter["REMOTE_ADDR"] = $arParams["IP"];
		$arEventFilter["USER_ID"] = !empty($find) && isset($find_type) && $find_type == "user_id" ? $find : ($find_user_id ?? null);

		$nameFormat = CSite::GetNameFormat(false);

		$arUsersTmp = array();
		$arNavParams = array("nPageSize"=>$arParams["PAGE_NUM"], "bShowAll"=>false);

		$arSort = array('TIMESTAMP_X' => 'DESC');
		if ($currentTemplateName == "grid")
		{
			$arSort = $grid->GetSorting(array('sort' => array('TIMESTAMP_X' => 'DESC')));
			$arSort = $arSort['sort'];
		}
		$arResult['SORT'] = $arSort;

		$results = CEventLog::GetList($arResult['SORT'], $arEventFilter, $arNavParams);
		$results->NavStart($arNavParams);  //page navigation
		$arResult["NAV"] = $results;
		while($row = $results->NavNext())
		{
			if (!isset($arUsersTmp[$row['USER_ID']]))
			{
				$arUserInfo = array();
				$rsUser = CUser::GetList("", "",
					array("ID_EQUAL_EXACT" => intval($row['USER_ID'])),
					array("FIELDS" => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'PERSONAL_PHOTO', 'EXTERNAL_AUTH_ID'))
				);
				if($arUser = $rsUser->GetNext())
				{
					if (in_array($arUser["EXTERNAL_AUTH_ID"], \Bitrix\Main\UserTable::getExternalUserTypes()))
					{
						continue;
					}

					$arUserInfo["ID"] = $row['USER_ID'];
					$arUserInfo["FULL_NAME"] = CUser::FormatName($nameFormat, $arUser, true, false);
					$arUserInfo['avatar'] = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO"],
						array("width"=>30, "height"=>30),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$arUsersTmp[$row['USER_ID']] = $arUserInfo;
				}
			}
			else
			{
				$arUserInfo = $arUsersTmp[$row['USER_ID']];
			}

			$dateFormated = FormatDateFromDB($row["TIMESTAMP_X"], CSite::GetDateFormat('SHORT'));
			$time = FormatDateFromDB($row["TIMESTAMP_X"], CSite::GetTimeFormat());

			if (isset($arObjectTypes[$row['AUDIT_TYPE_ID']]))
			{
				$eventName = preg_replace("/^\\[.*?]\\s+/", "", $arObjectTypes[$row['AUDIT_TYPE_ID']]);

				if (in_array($row['AUDIT_TYPE_ID'], array("PAGE_EDIT", "PAGE_ADD", "PAGE_DELETE")))
				{
					$path = unserialize($row["DESCRIPTION"], ['allowed_classes' => false]);
					$path = $path["path"];
					if ($path)
					{
						$eventName.= ": ".$path;
					}
				}

				//for grid template
				if ($currentTemplateName == "grid")
				{
					$userPath = CComponentEngine::MakePathFromTemplate($arParams["USER_PATH"], array("user_id" => $arUserInfo["ID"], "SITE_ID" => SITE_DIR));
					$userVal = "<a href=\"".$userPath."\">".$arUserInfo["FULL_NAME"]."</a>";

					$arResult["ELEMENTS_ROWS"][] = array("data" => array(
						"ID" => $row["ID"],
						"IP" => $row["REMOTE_ADDR"],
						"DATE_TIME" => FormatDateFromDB($row["TIMESTAMP_X"], CSite::GetDateFormat()),
						"USER_NAME" => $userVal,
						"EVENT_NAME" => $eventName,
					));
				}
				else //for default template
				{
					$res['user'] = array(
						"name" => $arUserInfo["FULL_NAME"],
						"id" => $arUserInfo["ID"],
						"avatar" => $arUserInfo["avatar"]["src"]
					);
					$res["eventType"] = $eventName;

					$arResult['EVENT'][$dateFormated][] = $res;
				}
			}
		}
	}
}
else
{
	$arResult["NO_ACTIVE_FEATURES"] = true;
}

$this->IncludeComponentTemplate();
