<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Bitrix\Main\Diag\Logger;
use Bitrix\Main\Diag\SysLogger;
use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main\Diag\JsonLinesFormatter;

IncludeModuleLangFile(__FILE__);

class CEventLog
{
	public const SEVERITY_SECURITY = 'SECURITY';
	public const SEVERITY_EMERGENCY = 'EMERGENCY';
	public const SEVERITY_ALERT = 'ALERT';
	public const SEVERITY_CRITICAL = 'CRITICAL';
	public const SEVERITY_ERROR = 'ERROR';
	public const SEVERITY_WARNING = 'WARNING';
	public const SEVERITY_NOTICE = 'NOTICE';
	public const SEVERITY_INFO = 'INFO';
	public const SEVERITY_DEBUG = 'DEBUG';

	public static function Log($SEVERITY, $AUDIT_TYPE_ID, $MODULE_ID, $ITEM_ID, $DESCRIPTION = false, $SITE_ID = false)
	{
		return CEventLog::Add([
			"SEVERITY" => $SEVERITY,
			"AUDIT_TYPE_ID" => $AUDIT_TYPE_ID,
			"MODULE_ID" => $MODULE_ID,
			"ITEM_ID" => $ITEM_ID,
			"DESCRIPTION" => $DESCRIPTION,
			"SITE_ID" => $SITE_ID,
		]);
	}

	public static function Add($arFields)
	{
		global $USER, $DB;

		static $arSeverity = [
			self::SEVERITY_SECURITY => LogLevel::INFO,
			self::SEVERITY_EMERGENCY => LogLevel::EMERGENCY,
			self::SEVERITY_ALERT => LogLevel::ALERT,
			self::SEVERITY_CRITICAL => LogLevel::CRITICAL,
			self::SEVERITY_ERROR => LogLevel::ERROR,
			self::SEVERITY_WARNING => LogLevel::WARNING,
			self::SEVERITY_NOTICE => LogLevel::NOTICE,
			self::SEVERITY_INFO => LogLevel::INFO,
			self::SEVERITY_DEBUG => LogLevel::DEBUG,
		];

		$url = preg_replace("/(&?sessid=[0-9a-z]+)/", "", $_SERVER["REQUEST_URI"]);

		$SITE_ID = (defined("ADMIN_SECTION") && ADMIN_SECTION == true) || !defined('SITE_ID')
			? false
			: SITE_ID;

		$session = \Bitrix\Main\Application::getInstance()->isInitialized()
			? \Bitrix\Main\Application::getInstance()->getSession()
			: null;

		if ($arFields["DESCRIPTION"] !== false && !is_string($arFields["DESCRIPTION"]))
		{
			$arFields["DESCRIPTION"] = json_encode($arFields["DESCRIPTION"], JSON_UNESCAPED_UNICODE);
		}

		$arFields = [
			"SEVERITY" => isset($arSeverity[$arFields["SEVERITY"] ?? null]) ? $arFields["SEVERITY"] : "UNKNOWN",
			"AUDIT_TYPE_ID" => $arFields["AUDIT_TYPE_ID"] == '' ? "UNKNOWN" : $arFields["AUDIT_TYPE_ID"],
			"MODULE_ID" => $arFields["MODULE_ID"] == '' ? "UNKNOWN" : $arFields["MODULE_ID"],
			"ITEM_ID" => empty($arFields["ITEM_ID"]) ? "UNKNOWN" : $arFields["ITEM_ID"],
			"REMOTE_ADDR" => $_SERVER["REMOTE_ADDR"],
			"USER_AGENT" => $_SERVER["HTTP_USER_AGENT"] ?? false,
			"REQUEST_URI" => $url,
			"SITE_ID" => empty($arFields["SITE_ID"]) ? $SITE_ID : $arFields["SITE_ID"],
			"USER_ID" => is_object($USER) && ($USER->GetID() > 0) ? $USER->GetID() : false,
			"GUEST_ID" => (isset($session) && $session->isStarted() && $session->has("SESS_GUEST_ID") && $session["SESS_GUEST_ID"] > 0 ? $session["SESS_GUEST_ID"] : false),
			"DESCRIPTION" => $arFields["DESCRIPTION"],
			"~TIMESTAMP_X" => $DB->GetNowFunction(),
		];

		$level = $arSeverity[$arFields["SEVERITY"]] ?? LogLevel::INFO;
		static::writeLogs($level, $arFields);

		return $DB->Add("b_event_log", $arFields, ["DESCRIPTION"], "", false, "", ["ignore_dml" => true]);
	}

	protected static function writeLogs(string $level, array $fields)
	{
		$sysLogger = static::getSysLogger();
		$fileLogger = static::getFileLogger();

		if ($sysLogger || $fileLogger)
		{
			$logFields = [
				"severity" => $fields["SEVERITY"],
				"auditTypeId" => $fields["AUDIT_TYPE_ID"],
				"moduleId" => $fields["MODULE_ID"],
				"itemId" => $fields["ITEM_ID"],
				"remoteAddr" => $fields["REMOTE_ADDR"],
				"userAgent" => $fields["USER_AGENT"] !== false ? $fields["USER_AGENT"] : null,
				"requestUri" => $fields["REQUEST_URI"],
				"siteId" => $fields["SITE_ID"] !== false ? $fields["SITE_ID"] : null,
				"userId" => $fields["USER_ID"] !== false ? $fields["USER_ID"] : null,
				"guestId" => $fields["GUEST_ID"] !== false ? $fields["GUEST_ID"] : null,
				"description" => $fields["DESCRIPTION"],
				"timestamp" => date('Y-m-d H:i:s'),
				"host" => $_SERVER['HTTP_HOST'] ?? null,
			];

			$sysLogger?->log($level, '', $logFields);
			$fileLogger?->log($level, '', $logFields);
		}
	}

	protected static function getSysLogger(): ?LoggerInterface
	{
		static $logger = null;

		if ($logger === null)
		{
			if (COption::GetOptionString('main', 'event_log_syslog') === 'Y')
			{
				$logger = Logger::create('main.EventLog.SysLogger');
				if ($logger === null)
				{
					$logger = new SysLogger();
					$formatter = new JsonLinesFormatter(false);
					$logger->setFormatter($formatter);
				}
			}
			else
			{
				$logger = new NullLogger();
			}
		}

		return ($logger instanceof NullLogger ? null : $logger);
	}

	protected static function getFileLogger(): ?LoggerInterface
	{
		static $logger = null;

		if ($logger === null)
		{
			if (COption::GetOptionString('main', 'event_log_filelog') === 'Y')
			{
				$path = COption::GetOptionString('main', 'event_log_filelog_path');
				if (!empty($path))
				{
					$logger = Logger::create('main.EventLog.FileLogger', [$path, 0]);
					if ($logger === null)
					{
						$logger = new FileLogger($path, 0);
						$formatter = new JsonLinesFormatter();
						$logger->setFormatter($formatter);
					}
				}
			}
			if ($logger === null)
			{
				$logger = new NullLogger();
			}
		}

		return ($logger instanceof NullLogger ? null : $logger);
	}

	//Agent
	public static function CleanUpAgent()
	{
		global $DB;

		$cleanup_days = COption::GetOptionInt("main", "event_log_cleanup_days", 7);
		if ($cleanup_days > 0)
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4] + 1, $arDate[3] - $cleanup_days, 1900 + $arDate[5]);
			$DB->Query("DELETE FROM b_event_log WHERE TIMESTAMP_X <= " . $DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")));
		}

		return "CEventLog::CleanUpAgent();";
	}

	public static function GetList($arOrder = ["ID" => "DESC"], $arFilter = [], $arNavParams = false)
	{
		global $DB;

		$arSqlSearch = [];
		$arSqlOrder = [];

		$arFields = ["ID", "TIMESTAMP_X", "AUDIT_TYPE_ID", "MODULE_ID", "SEVERITY", "ITEM_ID", "SITE_ID", "REMOTE_ADDR", "USER_AGENT", "REQUEST_URI", "USER_ID", "GUEST_ID"];
		$arOFields = [
			"ID" => "L.ID",
			"TIMESTAMP_X" => "L.TIMESTAMP_X",
		];

		foreach ($arFilter as $key => $val)
		{
			if (is_array($val))
			{
				if (empty($val))
				{
					continue;
				}
			}
			elseif ((string)$val == '')
			{
				continue;
			}
			$key = mb_strtoupper($key);
			switch ($key)
			{
				case "ID":
					$arSqlSearch[] = "L.ID=" . intval($val);
					break;
				case "TIMESTAMP_X_1":
					$arSqlSearch[] = "L.TIMESTAMP_X >= " . $DB->CharToDateFunction($DB->ForSql($val));
					break;
				case "TIMESTAMP_X_2":
					$arSqlSearch[] = "L.TIMESTAMP_X <= " . $DB->CharToDateFunction($DB->ForSql($val));
					break;
				case "=AUDIT_TYPE_ID":
					$arValues = [];
					if (is_array($val))
					{
						foreach ($val as $value)
						{
							$value = trim($value);
							if ($value <> '')
							{
								$arValues[$value] = $DB->ForSQL($value);
							}
						}
					}
					elseif (is_string($val))
					{
						$value = trim($val);
						if ($value <> '')
						{
							$arValues[$value] = $DB->ForSQL($value);
						}
					}
					if (!empty($arValues))
					{
						$arSqlSearch[] = "L.AUDIT_TYPE_ID in ('" . implode("', '", $arValues) . "')";
					}
					break;
				case "=MODULE_ITEM":
					if (is_array($val))
					{
						$arSqlSearch2 = [];
						foreach ($val as $value)
						{
							$arSqlSearchTmp = [];
							foreach ($value as $item2 => $value2)
							{
								if (in_array($item2, $arFields))
								{
									$arSqlSearchTmp[] = "L." . $item2 . " = '" . $DB->ForSQL($value2) . "'";
								}
							}
							if (!empty($arSqlSearchTmp))
							{
								$arSqlSearch2[] = implode(" AND ", $arSqlSearchTmp);
							}
						}
						if (!empty($arSqlSearch2))
						{
							$arSqlSearch[] = "(" . implode(" OR ", $arSqlSearch2) . ")";
						}
					}
					break;
				case "SEVERITY":
				case "AUDIT_TYPE_ID":
				case "MODULE_ID":
				case "ITEM_ID":
				case "SITE_ID":
				case "REMOTE_ADDR":
				case "USER_AGENT":
				case "REQUEST_URI":
					$arSqlSearch[] = GetFilterQuery("L." . $key, $val);
					break;
				case "USER_ID":
				case "GUEST_ID":
					$arSqlSearch[] = "L." . $key . " = " . intval($val);
					break;
			}
		}

		foreach ($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if (array_key_exists($by, $arOFields))
			{
				if ($order != "ASC")
				{
					$order = "DESC";
				}
				$arSqlOrder[$by] = $arOFields[$by] . " " . $order;
			}
		}

		$strSql = "
			FROM
				b_event_log L
		";

		if (!empty($arSqlSearch))
		{
			$strSql .= " WHERE " . implode(" AND ", $arSqlSearch);
		}

		if (is_array($arNavParams))
		{
			$res_cnt = $DB->Query("SELECT count(1) C" . $strSql);
			$res_cnt = $res_cnt->Fetch();
			$cnt = $res_cnt["C"];

			if (!empty($arSqlOrder))
			{
				$strSql .= " ORDER BY " . implode(", ", $arSqlOrder);
			}

			$res = new CDBResult();
			$res->NavQuery("
				SELECT
					ID
					," . $DB->DateToCharFunction("L.TIMESTAMP_X") . " as TIMESTAMP_X
					,SEVERITY
					,AUDIT_TYPE_ID
					,MODULE_ID
					,ITEM_ID
					,REMOTE_ADDR
					,USER_AGENT
					,REQUEST_URI
					,SITE_ID
					,USER_ID
					,GUEST_ID
					,DESCRIPTION
			" . $strSql, $cnt, $arNavParams);

			return $res;
		}
		else
		{
			if (!empty($arSqlOrder))
			{
				$strSql .= " ORDER BY " . implode(", ", $arSqlOrder);
			}

			return $DB->Query("SELECT L.*, " . $DB->DateToCharFunction("L.TIMESTAMP_X") . " as TIMESTAMP_X" . $strSql);
		}
	}

	public static function GetEventTypes()
	{
		$arAuditTypes = [
			"USER_AUTHORIZE" => "[USER_AUTHORIZE] " . GetMessage("MAIN_EVENTLOG_USER_AUTHORIZE"),
			"USER_DELETE" => "[USER_DELETE] " . GetMessage("MAIN_EVENTLOG_USER_DELETE"),
			"USER_INFO" => "[USER_INFO] " . GetMessage("MAIN_EVENTLOG_USER_INFO"),
			"USER_LOGIN" => "[USER_LOGIN] " . GetMessage("MAIN_EVENTLOG_USER_LOGIN"),
			"USER_LOGIN_INCORRECT_CAPTCHA" => "[USER_LOGIN_INCORRECT_CAPTCHA] " . GetMessage("MAIN_EVENTLOG_USER_LOGIN_INCORRECT_CAPTCHA"),
			"USER_LOGIN_BLOCKED" => "[USER_LOGIN_BLOCKED] " . GetMessage("MAIN_EVENTLOG_USER_LOGIN_BLOCKED"),
			"USER_LOGIN_NOT_FOUND" => "[USER_LOGIN_NOT_FOUND] " . GetMessage("MAIN_EVENTLOG_USER_LOGIN_NOT_FOUND"),
			"USER_LOGINBYHASH" => "[USER_LOGINBYHASH] " . GetMessage("MAIN_EVENTLOG_USER_LOGINBYHASH_FAILED"),
			"USER_LOGOUT" => "[USER_LOGOUT] " . GetMessage("MAIN_EVENTLOG_USER_LOGOUT"),
			"USER_PASSWORD_CHANGED" => "[USER_PASSWORD_CHANGED] " . GetMessage("MAIN_EVENTLOG_USER_PASSWORD_CHANGED"),
			"USER_BLOCKED" => "[USER_BLOCKED] " . GetMessage("MAIN_EVENTLOG_USER_BLOCKED"),
			"USER_PERMISSIONS_FAIL" => "[USER_PERMISSIONS_FAIL] " . GetMessage("MAIN_EVENTLOG_USER_PERMISSIONS_FAIL"),
			"USER_REGISTER" => "[USER_REGISTER] " . GetMessage("MAIN_EVENTLOG_USER_REGISTER"),
			"USER_REGISTER_FAIL" => "[USER_REGISTER_FAIL] " . GetMessage("MAIN_EVENTLOG_USER_REGISTER_FAIL"),
			"USER_GROUP_CHANGED" => "[USER_GROUP_CHANGED] " . GetMessage("MAIN_EVENTLOG_GROUP"),
			"GROUP_ADDED" => "[GROUP_ADDED] " . GetMessage('MAIN_EVENTLOG_GROUP_ADDED'),
			"GROUP_UPDATED" => "[GROUP_UPDATED] " . GetMessage('MAIN_EVENTLOG_GROUP_UPDATED'),
			"GROUP_DELETED" => "[GROUP_DELETED] " . GetMessage('MAIN_EVENTLOG_GROUP_DELETED'),
			"GROUP_POLICY_CHANGED" => "[GROUP_POLICY_CHANGED] " . GetMessage("MAIN_EVENTLOG_GROUP_POLICY"),
			"MODULE_RIGHTS_CHANGED" => "[MODULE_RIGHTS_CHANGED] " . GetMessage("MAIN_EVENTLOG_MODULE"),
			"FILE_PERMISSION_CHANGED" => "[FILE_PERMISSION_CHANGED] " . GetMessage("MAIN_EVENTLOG_FILE"),
			"TASK_CHANGED" => "[TASK_CHANGED] " . GetMessage("MAIN_EVENTLOG_TASK"),
			"MP_MODULE_INSTALLED" => "[MP_MODULE_INSTALLED] " . GetMessage("MAIN_EVENTLOG_MP_MODULE_INSTALLED"),
			"MP_MODULE_UNINSTALLED" => "[MP_MODULE_UNINSTALLED] " . GetMessage("MAIN_EVENTLOG_MP_MODULE_UNINSTALLED"),
			"MP_MODULE_DELETED" => "[MP_MODULE_DELETED] " . GetMessage("MAIN_EVENTLOG_MP_MODULE_DELETED"),
			"MP_MODULE_DOWNLOADED" => "[MP_MODULE_DOWNLOADED] " . GetMessage("MAIN_EVENTLOG_MP_MODULE_DOWNLOADED"),
		];

		foreach (GetModuleEvents("main", "OnEventLogGetAuditTypes", true) as $arEvent)
		{
			$ar = ExecuteModuleEventEx($arEvent);
			if (is_array($ar))
			{
				$arAuditTypes = array_merge($ar, $arAuditTypes);
			}
		}

		ksort($arAuditTypes);

		return $arAuditTypes;
	}
}

class CEventMain
{
	public static function MakeMainObject()
	{
		$obj = new CEventMain;
		return $obj;
	}

	public static function GetFilter()
	{
		$arFilter = [];
		if (COption::GetOptionString("main", "event_log_register", "N") === "Y" || COption::GetOptionString("main", "event_log_user_delete", "N") === "Y" || COption::GetOptionString("main", "event_log_user_edit", "N") === "Y" || COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
		{
			$arFilter["USERS"] = GetMessage("LOG_TYPE_USERS");
		}
		return $arFilter;
	}

	public static function GetAuditTypes()
	{
		return [
			"USER_REGISTER" => "[USER_REGISTER] " . GetMessage("LOG_TYPE_NEW_USERS"),
			"USER_DELETE" => "[USER_DELETE] " . GetMessage("LOG_TYPE_USER_DELETE"),
			"USER_EDIT" => "[USER_EDIT] " . GetMessage("LOG_TYPE_USER_EDIT"),
			"USER_GROUP_CHANGED" => "[USER_GROUP_CHANGED] " . GetMessage("LOG_TYPE_USER_GROUP_CHANGED"),
			"BACKUP_ERROR" => "[BACKUP_ERROR] " . GetMessage("LOG_TYPE_BACKUP_ERROR"),
			"BACKUP_SUCCESS" => "[BACKUP_SUCCESS] " . GetMessage("LOG_TYPE_BACKUP_SUCCESS"),
			"SITE_CHECKER_SUCCESS" => "[SITE_CHECKER_SUCCESS] " . GetMessage("LOG_TYPE_SITE_CHECK_SUCCESS"),
			"SITE_CHECKER_ERROR" => "[SITE_CHECKER_ERROR] " . GetMessage("LOG_TYPE_SITE_CHECK_ERROR"),
		];
	}

	public static function GetEventInfo($row, $arParams)
	{
		$DESCRIPTION = unserialize($row["DESCRIPTION"], ['allowed_classes' => false]);
		$userURL = $EventPrint = "";
		$rsUser = CUser::GetByID($row['ITEM_ID']);
		if ($rsUser->GetNext())
		{
			$userURL = SITE_DIR . CComponentEngine::MakePathFromTemplate($arParams['USER_PATH'], ["user_id" => $row['ITEM_ID'], "SITE_ID" => ""]);
		}
		$EventName = is_array($DESCRIPTION) ? $DESCRIPTION["user"] : null;
		switch ($row['AUDIT_TYPE_ID'])
		{
			case "USER_REGISTER":
				$EventPrint = GetMessage("LOG_USER_REGISTER");
				break;
			case "USER_DELETE":
				$EventPrint = GetMessage("LOG_USER_DELETE");
				break;
			case "USER_EDIT":
				$EventPrint = GetMessage("LOG_USER_EDIT");
				break;
			case "USER_GROUP_CHANGED":
				$EventPrint = GetMessage("LOG_USER_GROUP_CHANGED");
				break;
		}

		return [
			"eventType" => $EventPrint,
			"eventName" => $EventName,
			"eventURL" => $userURL,
		];
	}

	public static function GetFilterSQL()
	{
		$ar[] = ["AUDIT_TYPE_ID" => "USER_REGISTER"];
		$ar[] = ["AUDIT_TYPE_ID" => "USER_DELETE"];
		$ar[] = ["AUDIT_TYPE_ID" => "USER_EDIT"];
		$ar[] = ["AUDIT_TYPE_ID" => "USER_GROUP_CHANGED"];
		return $ar;
	}
}
