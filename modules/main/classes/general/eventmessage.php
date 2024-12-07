<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main\Mail;
use Bitrix\Main\Mail\Internal\EventTypeTable;

IncludeModuleLangFile(__FILE__);

/**
 * @deprecated
 */
class CAllEventMessage
{
	var $LAST_ERROR;

	public function CheckFields($arFields, $ID = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$this->LAST_ERROR = "";
		$arMsg = [];

		if (is_set($arFields, "EMAIL_FROM") && mb_strlen($arFields["EMAIL_FROM"]) < 3)
		{
			$this->LAST_ERROR .= GetMessage("BAD_EMAIL_FROM") . "<br>";
			$arMsg[] = ["id" => "EMAIL_FROM", "text" => GetMessage("BAD_EMAIL_FROM")];
		}
		if (is_set($arFields, "EMAIL_TO") && mb_strlen($arFields["EMAIL_TO"]) < 3)
		{
			$this->LAST_ERROR .= GetMessage("BAD_EMAIL_TO") . "<br>";
			$arMsg[] = ["id" => "EMAIL_TO", "text" => GetMessage("BAD_EMAIL_TO")];
		}

		if ($ID === false && !is_set($arFields, "EVENT_NAME"))
		{
			$this->LAST_ERROR .= GetMessage("MAIN_BAD_EVENT_NAME_NA") . "<br>";
			$arMsg[] = ["id" => "EVENT_NAME", "text" => GetMessage("MAIN_BAD_EVENT_NAME_NA")];
		}
		if (is_set($arFields, "EVENT_NAME"))
		{
			$r = CEventType::GetListEx([], ["EVENT_NAME" => $arFields["EVENT_NAME"]], ["type" => "none"]);
			if (!$r->Fetch())
			{
				$this->LAST_ERROR .= GetMessage("BAD_EVENT_TYPE") . "<br>";
				$arMsg[] = ["id" => "EVENT_NAME", "text" => GetMessage("BAD_EVENT_TYPE")];
			}
		}

		if (
			($ID === false && !is_set($arFields, "LID")) ||
			(is_set($arFields, "LID")
				&& (
					(is_array($arFields["LID"]) && empty($arFields["LID"]))
					||
					(!is_array($arFields["LID"]) && $arFields["LID"] == '')
				)
			)
		)
		{
			$this->LAST_ERROR .= GetMessage("MAIN_BAD_SITE_NA") . "<br>";
			$arMsg[] = ["id" => "LID", "text" => GetMessage("MAIN_BAD_SITE_NA")];
		}
		elseif (is_set($arFields, "LID"))
		{
			if (!is_array($arFields["LID"]))
			{
				$arFields["LID"] = [$arFields["LID"]];
			}

			foreach ($arFields["LID"] as $v)
			{
				$r = CSite::GetByID($v);
				if (!$r->Fetch())
				{
					$this->LAST_ERROR .= "'" . $v . "' - " . GetMessage("MAIN_EVENT_BAD_SITE") . "<br>";
					$arMsg[] = ["id" => "LID", "text" => GetMessage("MAIN_EVENT_BAD_SITE")];
				}
			}
		}

		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
		}

		if ($this->LAST_ERROR <> '')
		{
			return false;
		}

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// New event message template
	///////////////////////////////////////////////////////////////////
	public function Add($arFields)
	{
		unset($arFields["ID"]);

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
		{
			$arFields["ACTIVE"] = "N";
		}

		$arLID = [];
		if (is_set($arFields, "LID"))
		{
			if (is_array($arFields["LID"]))
			{
				$arLID = $arFields["LID"];
			}
			else
			{
				$arLID[] = $arFields["LID"];
			}

			$arFields["LID"] = false;
			foreach ($arLID as $v)
			{
				$arFields["LID"] = $v;
			}
		}

		$arATTACHMENT_FILE = [];
		if (is_set($arFields, "ATTACHMENT_FILE"))
		{
			if (is_array($arFields["ATTACHMENT_FILE"]))
			{
				$arATTACHMENT_FILE = $arFields["ATTACHMENT_FILE"];
			}
			else
			{
				$arATTACHMENT_FILE[] = $arFields["ATTACHMENT_FILE"];
			}

			$arATTACHMENT_FILE_tmp = [];
			foreach ($arATTACHMENT_FILE as $v)
			{
				$v = intval($v);
				$arATTACHMENT_FILE_tmp[] = $v;
			}
			$arATTACHMENT_FILE = $arATTACHMENT_FILE_tmp;

			unset($arFields['ATTACHMENT_FILE']);
		}

		$arDeleteFields = [
			'EVENT_MESSAGE_TYPE_ID', 'EVENT_MESSAGE_TYPE_ID',
			'EVENT_MESSAGE_TYPE_NAME', 'EVENT_MESSAGE_TYPE_EVENT_NAME',
			'SITE_ID', 'EVENT_TYPE',
		];
		foreach ($arDeleteFields as $deleteField)
		{
			if (array_key_exists($deleteField, $arFields))
			{
				unset($arFields[$deleteField]);
			}
		}

		$ID = false;

		$result = Mail\Internal\EventMessageTable::add($arFields);

		if ($result->isSuccess())
		{
			$ID = $result->getId();

			if (!empty($arLID))
			{
				static::UpdateSites($ID, $arLID);
			}

			if (!empty($arATTACHMENT_FILE))
			{
				foreach ($arATTACHMENT_FILE as $file_id)
				{
					Mail\Internal\EventMessageAttachmentTable::add([
						'EVENT_MESSAGE_ID' => $ID,
						'FILE_ID' => $file_id,
					]);
				}
			}
		}

		return $ID;
	}

	public function Update($ID, $arFields)
	{
		if (!$this->CheckFields($arFields, $ID))
		{
			return false;
		}

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
		{
			$arFields["ACTIVE"] = "N";
		}

		$arLID = [];
		if (is_set($arFields, "LID"))
		{
			if (is_array($arFields["LID"]))
			{
				$arLID = $arFields["LID"];
			}
			else
			{
				$arLID[] = $arFields["LID"];
			}

			$arFields["LID"] = false;
			foreach ($arLID as $v)
			{
				$arFields["LID"] = $v;
			}
		}

		$arATTACHMENT_FILE = [];
		if (is_set($arFields, "ATTACHMENT_FILE"))
		{
			if (is_array($arFields["ATTACHMENT_FILE"]))
			{
				$arATTACHMENT_FILE = $arFields["ATTACHMENT_FILE"];
			}
			else
			{
				$arATTACHMENT_FILE[] = $arFields["ATTACHMENT_FILE"];
			}

			$arATTACHMENT_FILE_tmp = [];
			foreach ($arATTACHMENT_FILE as $v)
			{
				$v = intval($v);
				$arATTACHMENT_FILE_tmp[] = $v;
			}
			$arATTACHMENT_FILE = $arATTACHMENT_FILE_tmp;

			unset($arFields['ATTACHMENT_FILE']);
		}

		if (array_key_exists('NAME', $arFields))
		{
			unset($arFields['NAME']);
		}

		$ID = intval($ID);

		Mail\Internal\EventMessageTable::update($ID, $arFields);

		if (!empty($arLID))
		{
			static::UpdateSites($ID, $arLID);
		}

		if (!empty($arATTACHMENT_FILE))
		{
			foreach ($arATTACHMENT_FILE as $file_id)
			{
				Mail\Internal\EventMessageAttachmentTable::add([
					'EVENT_MESSAGE_ID' => $ID,
					'FILE_ID' => $file_id,
				]);
			}
		}

		return true;
	}

	protected static function UpdateSites(int $ID, array $arLID)
	{
		Mail\Internal\EventMessageSiteTable::deleteByFilter(['=EVENT_MESSAGE_ID' => $ID]);

		$resultDb = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID'],
			'filter' => ['=LID' => $arLID],
		]);
		while ($arResultSite = $resultDb->fetch())
		{
			Mail\Internal\EventMessageSiteTable::add([
				'EVENT_MESSAGE_ID' => $ID,
				'SITE_ID' => $arResultSite['LID'],
			]);
		}
	}

	///////////////////////////////////////////////////////////////////
	// Query
	///////////////////////////////////////////////////////////////////
	public static function GetByID($ID)
	{
		return CEventMessage::GetList('', '', ["ID" => $ID]);
	}

	public static function GetSite($event_message_id)
	{
		$event_message_id = intval($event_message_id);

		$resultDb = Mail\Internal\EventMessageSiteTable::getList([
			'select' => ['*', '' => 'SITE.*'],
			'filter' => ['=EVENT_MESSAGE_ID' => $event_message_id],
			'runtime' => [
				'SITE' => [
					'data_type' => 'Bitrix\Main\Site',
					'reference' => ['=this.SITE_ID' => 'ref.LID'],
				],
			],
		]);

		return new CDBResult($resultDb);
	}

	public static function GetLang($event_message_id)
	{
		return CEventMessage::GetSite($event_message_id);
	}

	public static function Delete($ID)
	{
		/**
		 * @global CMain $APPLICATION
		 * @global CDatabase $DB
		 */
		global $APPLICATION;
		$ID = intval($ID);

		foreach (GetModuleEvents("main", "OnBeforeEventMessageDelete", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [$ID]) === false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR1") . ' ' . $arEvent['TO_NAME'];
				if ($ex = $APPLICATION->GetException())
				{
					$err .= ': ' . $ex->GetString();
				}
				$APPLICATION->throwException($err);
				return false;
			}
		}

		//check module event for OnDelete
		foreach (GetModuleEvents("main", "OnEventMessageDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID]);
		}

		Mail\Internal\EventMessageSiteTable::deleteByFilter(['=EVENT_MESSAGE_ID' => $ID]);
		$result = Mail\Internal\EventMessageTable::delete($ID);

		if ($result->isSuccess())
		{
			$res = new CDBResultEventMultiResult();
			$res->affectedRowsCount = 1;
		}
		else
		{
			$res = false;
		}

		return $res;
	}

	public static function GetListDataModifier($data)
	{
		if (!isset($data['EVENT_MESSAGE_TYPE_ID']) || intval($data['EVENT_MESSAGE_TYPE_ID']) <= 0)
		{
			$data['EVENT_TYPE'] = $data['EVENT_NAME'];
		}
		else
		{
			$data['EVENT_TYPE'] = '[ ' . $data['EVENT_MESSAGE_TYPE_EVENT_NAME'] . ' ] ' . $data['EVENT_MESSAGE_TYPE_NAME'];

			unset($data['EVENT_MESSAGE_TYPE_ID']);
			unset($data['EVENT_MESSAGE_TYPE_NAME']);
			unset($data['EVENT_MESSAGE_TYPE_EVENT_NAME']);
		}

		if (!empty($data['ADDITIONAL_FIELD']) && is_array($data['ADDITIONAL_FIELD']))
		{
			foreach ($data['ADDITIONAL_FIELD'] as $index => $aField)
			{
				$index++;
				$oldKeyName = "FIELD{$index}_NAME";
				$oldKeyValue = "FIELD{$index}_VALUE";
				if (!array_key_exists($oldKeyName, $data))
				{
					continue;
				}

				if (!empty($data[$oldKeyName]))
				{
					continue;
				}

				$data[$oldKeyName] = $aField['NAME'];
				$data[$oldKeyValue] = $aField['VALUE'];
			}
		}

		return $data;
	}

	public static function GetList($by = 'id', $order = 'desc', $arFilter = [])
	{
		$arSearch = [];
		$arSqlSearch = [];
		$bIsLang = false;
		if (is_array($arFilter))
		{
			static $map = [
				'TYPE_ID' => 'EVENT_NAME',
				'FROM' => 'EMAIL_FROM',
				'TO' => 'EMAIL_TO',
				'BODY' => 'MESSAGE',
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
				else
				{
					if ((string)$val == '' || $val === "NOT_REF")
					{
						continue;
					}
				}
				$key = strtoupper($key);
				switch ($key)
				{
					case "ID":
					case "EVENT_NAME":
					case "TYPE_ID":
						$operation = (isset($arFilter[$key . "_EXACT_MATCH"]) && $arFilter[$key . "_EXACT_MATCH"] == "N" ? '%' : '=');
						$field = $map[$key] ?? $key;
						$arSearch[$operation . $field] = $val;
						break;
					case "FROM":
					case "TO":
					case "BCC":
					case "SUBJECT":
					case "BODY":
						$operation = (isset($arFilter[$key . "_EXACT_MATCH"]) && $arFilter[$key . "_EXACT_MATCH"] == "Y" ? '=' : '%');
						$field = $map[$key] ?? $key;
						$arSearch[$operation . $field] = $val;
						break;
					case "TYPE":
						$operation = (isset($arFilter[$key . "_EXACT_MATCH"]) && $arFilter[$key . "_EXACT_MATCH"] == "Y" ? '=' : '%');
						$arSearch[] = [
							'LOGIC' => 'OR',
							$operation . 'EVENT_NAME' => $val,
							$operation . 'EVENT_MESSAGE_TYPE.NAME' => $val
						];
						break;
					case "TIMESTAMP_1":
						$arSqlSearch[] = "M.TIMESTAMP_X>=TO_DATE('" . FmtDate($val, "D.M.Y") . " 00:00:00','dd.mm.yyyy hh24:mi:ss')";
						$arSearch['>=TIMESTAMP_X'] = $val . " 00:00:00";
						break;
					case "TIMESTAMP_2":
						$arSqlSearch[] = "M.TIMESTAMP_X<=TO_DATE('" . FmtDate($val, "D.M.Y") . " 23:59:59','dd.mm.yyyy hh24:mi:ss')";
						$arSearch['<=TIMESTAMP_X'] = $val . " 23:59:59";
						break;
					case "LID":
					case "LANG":
					case "SITE_ID":
						$bIsLang = true;
						$arSearch["=SITE_ID"] = $val;
						break;
					case "LANGUAGE_ID":
					case "ACTIVE":
						$arSearch['=' . $key] = $val;
						break;
					case "BODY_TYPE":
						$arSearch['=' . $key] = ($val == "text") ? 'text' : 'html';
						break;
				}
			}
		}

		if ($by == "id")
		{
			$strSqlOrder = "ID";
		}
		elseif ($by == "active")
		{
			$strSqlOrder = "ACTIVE";
		}
		elseif ($by == "event_name")
		{
			$strSqlOrder = "EVENT_NAME";
		}
		elseif ($by == "from")
		{
			$strSqlOrder = "EMAIL_FROM";
		}
		elseif ($by == "to")
		{
			$strSqlOrder = "EMAIL_TO";
		}
		elseif ($by == "bcc")
		{
			$strSqlOrder = "BCC";
		}
		elseif ($by == "body_type")
		{
			$strSqlOrder = "BODY_TYPE";
		}
		elseif ($by == "subject")
		{
			$strSqlOrder = "SUBJECT";
		}
		elseif ($by == "language_id")
		{
			$strSqlOrder = "LANGUAGE_ID";
		}
		else
		{
			$strSqlOrder = "ID";
		}

		if ($order != "asc")
		{
			$strSqlOrderBy = "DESC";
		}
		else
		{
			$strSqlOrderBy = "ASC";
		}

		$arSelect = [
			'*',
			'EVENT_MESSAGE_TYPE_ID' => 'EVENT_MESSAGE_TYPE.ID',
			'EVENT_MESSAGE_TYPE_NAME' => 'EVENT_MESSAGE_TYPE.NAME',
			'EVENT_MESSAGE_TYPE_EVENT_NAME' => 'EVENT_MESSAGE_TYPE.EVENT_NAME',
		];

		if ($bIsLang)
		{
			$arSelect['SITE_ID'] = 'EVENT_MESSAGE_SITE.SITE_ID';
		}
		else
		{
			$arSelect['SITE_ID'] = 'LID';
		}

		$resultDb = Mail\Internal\EventMessageTable::getList([
			'select' => $arSelect,
			'filter' => $arSearch,
			'order' => [$strSqlOrder => $strSqlOrderBy],
			'runtime' => [
				'EVENT_MESSAGE_TYPE' => [
					'data_type' => 'Bitrix\Main\Mail\Internal\EventType',
					'reference' => ['=this.EVENT_NAME' => 'ref.EVENT_NAME', '=ref.LID' => new \Bitrix\Main\DB\SqlExpression('?', LANGUAGE_ID)],
				],
			],
		]);
		$resultDb->addFetchDataModifier(['CEventMessage', 'GetListDataModifier']);
		$res = new CDBResult($resultDb);

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$res->is_filtered = (IsFiltered($strSqlSearch));

		return $res;
	}
}

class CEventMessage extends CAllEventMessage
{
}

class CEventType
{
	public static function CheckFields($arFields = [], $action = "ADD", $ID = [])
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$arFilter = [];
		$aMsg = [];
		//ID, LID, EVENT_NAME, NAME, DESCRIPTION, SORT
		if ($action == "ADD")
		{
			if (empty($arFields["EVENT_NAME"]))
			{
				$aMsg[] = ["id" => "EVENT_NAME_EMPTY", "text" => GetMessage("EVENT_NAME_EMPTY")];
			}

			if (!is_set($arFields, "LID") && is_set($arFields, "SITE_ID"))
			{
				$arFields["LID"] = $arFields["SITE_ID"];
			}
			if (is_set($arFields, "LID") && empty($arFields["LID"]))
			{
				$aMsg[] = ["id" => "LID_EMPTY", "text" => GetMessage("LID_EMPTY")];
			}

			if (empty($aMsg))
			{
				$db_res = CEventType::GetList(["LID" => $arFields["LID"], "EVENT_NAME" => $arFields["EVENT_NAME"]]);
				if ($db_res && $db_res->Fetch())
				{
					$aMsg[] = ["id" => "EVENT_NAME_EXIST", "text" => str_replace(
						["#SITE_ID#", "#EVENT_NAME#"],
						[$arFields["LID"], $arFields["EVENT_NAME"]],
						GetMessage("EVENT_NAME_EXIST"))];
				}
			}
		}
		elseif ($action == "UPDATE")
		{
			if (empty($ID))
			{
				$aMsg[] = ["id" => "EVENT_ID_EMPTY", "text" => GetMessage("EVENT_ID_EMPTY")];
			}

			if (isset($arFields["EVENT_TYPE"]) && $arFields["EVENT_TYPE"] == '')
			{
				$aMsg[] = ["id" => "EVENT_TYPE_EMPTY", "text" => GetMessage('EVENT_TYPE_EMPTY')];
			}

			if (empty($aMsg) && is_set($arFields, "EVENT_NAME") && (is_set($arFields, "LID")))
			{
				if (is_set($arFields, "EVENT_NAME"))
				{
					$arFilter["EVENT_NAME"] = $arFields["EVENT_NAME"];
				}
				if (is_set($arFields, "LID"))
				{
					$arFilter["LID"] = $arFields["LID"];
				}

				if (!empty($arFilter) && (count($arFilter) < 2) && is_set($arFilter, "LID"))
				{
					unset($arFields["LID"]);
				}
				else
				{
					$db_res = CEventType::GetList($arFilter);

					if ($db_res && ($res = $db_res->Fetch()))
					{
						if ((is_set($ID, "EVENT_NAME") && is_set($ID, "LID") &&
								(($res["EVENT_NAME"] != $ID["EVENT_NAME"]) || ($res["LID"] != $ID["LID"]))) ||
							(is_set($ID, "ID") && $res["ID"] != $ID["ID"]) ||
							(is_set($ID, "EVENT_NAME") && ($res["EVENT_NAME"] != $ID["EVENT_NAME"])))
						{
							$aMsg[] = ["id" => "EVENT_NAME_EXIST", "text" => str_replace(
								["#SITE_ID#", "#EVENT_NAME#"],
								[$arFields["LID"], $arFields["EVENT_NAME"]],
								GetMessage("EVENT_NAME_EXIST"))];
						}
					}
				}
			}
		}
		else
		{
			$aMsg[] = ["id" => "ACTION_EMPTY", "text" => GetMessage("ACTION_EMPTY")];
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	public static function Add($arFields)
	{
		if (!is_set($arFields, "LID") && is_set($arFields, "SITE_ID"))
		{
			$arFields["LID"] = $arFields["SITE_ID"];
		}

		if (!isset($arFields["EVENT_TYPE"]))
		{
			//compatibility
			$arFields["EVENT_TYPE"] = EventTypeTable::TYPE_EMAIL;
		}

		unset($arFields["ID"]);

		if (CEventType::CheckFields($arFields))
		{
			$result = Mail\Internal\EventTypeTable::add($arFields);

			return $result->getId();
		}
		return false;
	}

	public static function Update($arID = [], $arFields = [])
	{
		$ID = [];

		// update event type by ID, or (LID+EVENT_NAME)
		if (is_array($arID) && !empty($arID))
		{
			foreach ($arID as $key => $val)
			{
				if (in_array($key, ["ID", "LID", "EVENT_NAME"]))
				{
					$ID[$key] = $val;
				}
			}
		}
		if (!empty($ID) && CEventType::CheckFields($arFields, "UPDATE", $ID))
		{
			unset($arFields["ID"]);

			$affectedRowsCount = 0;
			$listDb = Mail\Internal\EventTypeTable::getList([
				'select' => ['ID'],
				'filter' => $ID,
			]);
			while ($arListId = $listDb->fetch())
			{
				$result = Mail\Internal\EventTypeTable::update($arListId['ID'], $arFields);
				$affectedRowsCount += $result->getAffectedRowsCount();
			}

			$res = new CDBResultEventMultiResult();
			$res->affectedRowsCount = $affectedRowsCount;

			return $res;
		}
		return false;
	}

	public static function Delete($arID)
	{
		$ID = [];
		if (!is_array($arID))
		{
			$arID = ["EVENT_NAME" => $arID];
		}
		foreach ($arID as $k => $v)
		{
			if (!in_array(mb_strtoupper($k), ["ID", "LID", "EVENT_NAME", "NAME", "SORT"]))
			{
				continue;
			}
			$ID[$k] = $v;
		}

		if (!empty($ID))
		{
			$res = null;
			$affectedRowsCount = 0;
			$listDb = Mail\Internal\EventTypeTable::getList([
				'select' => ['ID'],
				'filter' => $ID,
			]);
			while ($arListId = $listDb->fetch())
			{
				$result = Mail\Internal\EventTypeTable::delete($arListId['ID']);
				if ($result->isSuccess())
				{
					$affectedRowsCount++;
				}
				else
				{
					$res = false;
					break;
				}
			}

			if ($res === null)
			{
				$res = new CDBResultEventMultiResult();
				$res->affectedRowsCount = $affectedRowsCount;
			}

			return $res;
		}
		return false;
	}

	public static function GetList($arFilter = [], $arOrder = [])
	{
		$arSqlSearch = $arSqlOrder = [];

		foreach ($arFilter as $key => $val)
		{
			if ((string)$val == '')
			{
				continue;
			}

			$key = strtoupper($key);
			switch ($key)
			{
				case "EVENT_NAME":
				case "TYPE_ID":
					$arSqlSearch["=EVENT_NAME"] = (string)$val;
					break;
				case "EVENT_TYPE":
					$arSqlSearch["=EVENT_TYPE"] = (string)$val;
					break;
				case "LID":
					$arSqlSearch["=LID"] = (string)$val;
					break;
				case "ID":
					$arSqlSearch["=ID"] = (int)$val;
					break;
			}
		}

		if (is_array($arOrder))
		{
			static $arFields = ["ID" => 1, "LID" => 1, "EVENT_NAME" => 1, "NAME" => 1, "SORT" => 1];
			foreach ($arOrder as $by => $ord)
			{
				$by = strtoupper($by);
				$ord = strtoupper($ord);
				if (array_key_exists($by, $arFields))
				{
					$arSqlOrder[$by] = ($ord == "DESC" ? "DESC" : "ASC");
				}
			}
		}
		if (empty($arSqlOrder))
		{
			$arSqlOrder['ID'] = 'ASC';
		}

		$result = Mail\Internal\EventTypeTable::getList([
			'select' => ['ID', 'LID', 'EVENT_NAME', 'EVENT_TYPE', 'NAME', 'DESCRIPTION', 'SORT'],
			'filter' => $arSqlSearch,
			'order' => $arSqlOrder,
		]);

		$res = new CDBResult($result);

		return $res;
	}

	public static function GetListExFetchDataModifier($data)
	{
		if (isset($data['ID1']) && !isset($data['ID']))
		{
			$data['ID'] = $data['ID1'];
			unset($data['ID1']);
		}

		if (isset($data['EVENT_NAME1']) && !isset($data['EVENT_NAME']))
		{
			$data['EVENT_NAME'] = $data['EVENT_NAME1'];
			unset($data['EVENT_NAME1']);
		}

		return $data;
	}

	public static function GetListEx($arOrder = [], $arFilter = [], $arParams = [])
	{
		global $DB;

		$arSearch = $arSearch1 = $arSearch2 = [];

		$arSqlOrder = [];
		foreach ($arFilter as $key => $val)
		{
			if ((string)$val == '')
			{
				continue;
			}
			$val = $DB->ForSql($val);
			$key_res = CEventType::GetFilterOperation($key);
			$key = mb_strtoupper($key_res["FIELD"]);
			$strOperation = $key_res["OPERATION"];
			$strNOperation = $key_res["NOPERATION"];

			switch ($key)
			{
				case "EVENT_NAME":
				case "TYPE_ID":
					if ($strOperation == "LIKE")
					{
						$val = "%" . $val . "%";
					}
					$arSearch[] = [$strNOperation . 'EVENT_NAME' => $val];
					break;
				case "DESCRIPTION":
				case "NAME":
					if ($strOperation == "LIKE")
					{
						$val = "%" . $val . "%";
					}
					$arSearch1[] = [$strNOperation . 'EVENT_MESSAGE_TYPE.' . $key => $val];
					$arSearch2[] = [$strNOperation . $key => $val];
					break;
				case "LID":
					$arSearch1[] = [$strNOperation . 'EVENT_MESSAGE_TYPE.' . $key => $val];
					$arSearch2[] = [$strNOperation . $key => $val];
					break;
				case "ID":
					$val = intval($val);
					$arSearch1[] = [$strNOperation . 'EVENT_MESSAGE_TYPE.' . $key => $val];
					$arSearch2[] = [$strNOperation . $key => $val];
					break;
				case "MESSAGE_ID":
					$val = intval($val);
					$arSearch1[] = [$strNOperation . "ID" => $val];
					$arSearch2[] = [$strNOperation . 'EVENT_MESSAGE.ID' => $val];
					break;
			}
		}

		if (is_array($arOrder))
		{
			foreach ($arOrder as $by => $order)
			{
				$by = mb_strtoupper($by);
				$order = mb_strtoupper($order);
				$order = ($order <> "DESC" ? "ASC" : "DESC");
				if ($by == "EVENT_NAME" || $by == "ID")
				{
					$arSqlOrder["EVENT_NAME"] = "EVENT_NAME1 " . $order;
				}
			}
		}
		if (empty($arSqlOrder))
		{
			$arSqlOrder["EVENT_NAME"] = "EVENT_NAME1 ASC";
		}
		$strSqlOrder = " ORDER BY " . implode(", ", $arSqlOrder);

		$arSearch['!EVENT_NAME'] = null;
		$arQuerySelect = ['ID1' => 'EVENT_NAME', 'EVENT_NAME1' => 'EVENT_NAME'];
		$query1 = new \Bitrix\Main\Entity\Query(Mail\Internal\EventMessageTable::getEntity());
		$query1->setSelect($arQuerySelect);
		$query1->setFilter(array_merge($arSearch, $arSearch1));
		$query1->registerRuntimeField('EVENT_MESSAGE_TYPE', [
			'data_type' => 'Bitrix\Main\Mail\Internal\EventType',
			'reference' => ['=this.EVENT_NAME' => 'ref.EVENT_NAME'],
		]);

		$query2 = new \Bitrix\Main\Entity\Query(Mail\Internal\EventTypeTable::getEntity());
		$query2->setSelect($arQuerySelect);
		$query2->setFilter(array_merge($arSearch, $arSearch2));
		$query2->registerRuntimeField('EVENT_MESSAGE', [
			'data_type' => 'Bitrix\Main\Mail\Internal\EventMessage',
			'reference' => ['=this.EVENT_NAME' => 'ref.EVENT_NAME'],
		]);

		$connection = \Bitrix\Main\Application::getConnection();
		$strSql = $query1->getQuery() . " UNION " . $query2->getQuery() . " " . $strSqlOrder;
		$db_res = $connection->query($strSql);
		$db_res->addFetchDataModifier(['CEventType', 'GetListExFetchDataModifier']);

		$db_res = new _CEventTypeResult($db_res, $arParams);
		return $db_res;
	}

	///////////////////////////////////////////////////////////////////
	// selecting type
	///////////////////////////////////////////////////////////////////
	public static function GetByID($ID, $LID)
	{
		$result = Mail\Internal\EventTypeTable::getList([
			'filter' => ['=LID' => $LID, '=EVENT_NAME' => $ID],
		]);

		return new CDBResult($result);
	}

	public static function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (str_starts_with($key, "!"))
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (str_starts_with($key, "+"))
		{
			$key = substr($key, 1);
			$strOrNull = "Y";
		}

		if (str_starts_with($key, ">="))
		{
			$key = substr($key, 2);
			$strOperation = ">=";
			$strNOperation = ($strNegative == "Y" ? '<' : $strOperation);
		}
		elseif (str_starts_with($key, ">"))
		{
			$key = substr($key, 1);
			$strOperation = ">";
			$strNOperation = ($strNegative == "Y" ? '<=' : $strOperation);
		}
		elseif (str_starts_with($key, "<="))
		{
			$key = substr($key, 2);
			$strOperation = "<=";
			$strNOperation = ($strNegative == "Y" ? '>' : $strOperation);
		}
		elseif (str_starts_with($key, "<"))
		{
			$key = substr($key, 1);
			$strOperation = "<";
			$strNOperation = ($strNegative == "Y" ? '>=' : $strOperation);
		}
		elseif (str_starts_with($key, "@"))
		{
			$key = substr($key, 1);
			$strOperation = "IN";
			$strNOperation = '';
		}
		elseif (str_starts_with($key, "~"))
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
			$strNOperation = ($strNegative == "Y" ? '!=%' : '=%');
		}
		elseif (str_starts_with($key, "%"))
		{
			$key = substr($key, 1);
			$strOperation = "QUERY";
			$strNOperation = '';
		}
		else
		{
			$strOperation = "=";
			$strNOperation = ($strNegative == "Y" ? '!=' : '=');
		}

		return ["FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "NOPERATION" => $strNOperation, "OR_NULL" => $strOrNull];
	}
}

class _CEventTypeResult extends CDBResult
{
	var $type;
	var $LID;
	var $SITE_ID;

	public function __construct($res, $arParams = [])
	{
		$language = (defined("LANGUAGE_ID") ? LANGUAGE_ID : 'en');
		$site = (defined("SITE_ID") ? SITE_ID : 's1');

		$this->type = empty($arParams["type"]) ? "type" : $arParams["type"];
		$this->LID = empty($arParams["LID"]) ? $language : $arParams["LID"];
		$this->SITE_ID = empty($arParams["SITE_ID"]) ? $site : $arParams["SITE_ID"];

		parent::__construct($res);
	}

	function Fetch()
	{
		$arr = [];
		$arr_lid = [];
		$arr_lids = [];

		if ($res = parent::Fetch())
		{
			if ($this->type != "none")
			{
				$eventType = EventTypeTable::TYPE_EMAIL;
				$db_res_ = CEventType::GetList(["EVENT_NAME" => $res["EVENT_NAME"]]);
				if ($db_res_ && $res_ = $db_res_->Fetch())
				{
					do
					{
						$arr[$res_["ID"]] = $res_;
						$arr_lid[] = $res_["LID"];
						$arr_lids[$res_["LID"]] = $res_;
						$eventType = $res_["EVENT_TYPE"];
					}
					while ($res_ = $db_res_->Fetch());
				}
				$res["ID"] = array_keys($arr);
				$res["LID"] = $arr_lid;
				$res["EVENT_TYPE"] = $eventType;

				$res["NAME"] = empty($arr_lids[$this->LID]["NAME"]) ? $arr_lids["en"]["NAME"] : $arr_lids[$this->LID]["NAME"];
				$res["SORT"] = empty($arr_lids[$this->LID]["SORT"]) ? $arr_lids["en"]["SORT"] : $arr_lids[$this->LID]["SORT"];
				$res["DESCRIPTION"] = empty($arr_lids[$this->LID]["DESCRIPTION"]) ? $arr_lids["en"]["DESCRIPTION"] : $arr_lids[$this->LID]["DESCRIPTION"];
				$res["TYPE"] = $arr;
				if ($this->type != "type")
				{
					$arr = [];
					$db_res_ = CEventMessage::GetList('', '', ["EVENT_NAME" => $res["EVENT_NAME"]]);
					if ($db_res_ && $res_ = $db_res_->Fetch())
					{
						do
						{
							$arr[$res_["ID"]] = $res_;
						}
						while ($res_ = $db_res_->Fetch());
					}
					$res["TEMPLATES"] = $arr;
				}
			}
		}
		return $res;
	}
}

class CDBResultEventMultiResult extends CDBResult
{
	public $affectedRowsCount;

	public function AffectedRowsCount()
	{
		if ($this->affectedRowsCount !== false)
		{
			return $this->affectedRowsCount;
		}
		else
		{
			return parent::AffectedRowsCount();
		}
	}
}
