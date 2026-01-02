<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

use Bitrix\Main\Authentication\Internal\ModuleGroupTable;
use Bitrix\Main\Authentication\Internal\GroupSubordinateTable;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Fields\ExpressionField;

class CAllGroup
{
	public $LAST_ERROR;

	public function Add($arFields)
	{
		global $APPLICATION;

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		foreach (GetModuleEvents("main", "OnBeforeGroupAdd", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, [&$arFields]);
			if ($bEventRes === false)
			{
				if ($err = $APPLICATION->GetException())
				{
					$this->LAST_ERROR .= $err->GetString() . "<br>";
				}
				else
				{
					$this->LAST_ERROR .= "Unknown error in OnBeforeGroupAdd handler." . "<br>";
				}
				return false;
			}
		}

		if (isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] != "Y")
		{
			$arFields["ACTIVE"] = "N";
		}

		$fields = $arFields;

		unset($fields['~ID']);
		unset($fields['USER_ID']);

		if (isset($arFields['~ID']))
		{
			$fields['ID'] = $arFields['~ID'];
		}

		$result = GroupTable::add($fields);
		$ID = $result->getId();

		if (!empty($arFields["USER_ID"]) && is_array($arFields["USER_ID"]))
		{
			static::AddUsersToGroup($ID, $arFields["USER_ID"]);
		}

		$arFields["ID"] = $ID;

		foreach (GetModuleEvents("main", "OnAfterGroupAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$arFields]);
		}

		if (COption::GetOptionString("main", "event_log_group_edit", "N") === "Y")
		{
			CEventLog::Log(CEventLog::SEVERITY_SECURITY, "GROUP_ADDED", "main", $ID);
		}

		return $ID;
	}

	protected static function AddUsersToGroup(int $groupId, array $users): array
	{
		$added = [];
		foreach ($users as $user)
		{
			if (!is_array($user))
			{
				$user = ["USER_ID" => $user];
			}
			$userId = (int)($user["USER_ID"] ?? 0);

			if ($userId > 0 && !isset($added[$userId]))
			{
				CUser::AppendUserGroup(
					$userId,
					[[
						'GROUP_ID' => $groupId,
						'DATE_ACTIVE_FROM' => $user['DATE_ACTIVE_FROM'] ?? null,
						'DATE_ACTIVE_TO' => $user['DATE_ACTIVE_TO'] ?? null,
					]]
				);
				$added[$userId] = $userId;
			}
		}

		return $added;
	}

	public static function GetDropDownList($strSqlSearch = "and ACTIVE='Y'", $strSqlOrder = "ORDER BY C_SORT, NAME, ID")
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				" . $helper->getConcatFunction("NAME", "' ['", "ID", "']'") . " as REFERENCE
			FROM
				b_group
			WHERE
				1=1
			$strSqlSearch
			$strSqlOrder
			";
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetList($by = 'c_sort', $order = 'asc', $arFilter = [], $SHOW_USERS_AMOUNT = "N")
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$arSqlSearch = $arSqlSearch_h = [];
		$strSqlSearch_h = "";
		if (is_array($arFilter))
		{
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
					if ((string)$val == '' || $val == "NOT_REF")
					{
						continue;
					}
				}
				$key = strtoupper($key);
				$match_value_set = array_key_exists($key . "_EXACT_MATCH", $arFilter);
				switch ($key)
				{
					case "ID":
						$match = ($match_value_set && $arFilter[$key . "_EXACT_MATCH"] == "N") ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.ID", $val, $match);
						break;
					case "TIMESTAMP_1":
						$arSqlSearch[] = "G.TIMESTAMP_X >= FROM_UNIXTIME('" . MkDateTime(FmtDate($val, "D.M.Y"), "d.m.Y") . "')";
						break;
					case "TIMESTAMP_2":
						$arSqlSearch[] = "G.TIMESTAMP_X <= FROM_UNIXTIME('" . MkDateTime(FmtDate($val, "D.M.Y") . " 23:59:59", "d.m.Y") . "')";
						break;
					case "ACTIVE":
						$arSqlSearch[] = ($val == "Y") ? "G.ACTIVE='Y'" : "G.ACTIVE='N'";
						break;
					case "ADMIN":
						if (COption::GetOptionString("main", "controller_member", "N") == "Y" && COption::GetOptionString("main", "~controller_limited_admin", "N") == "Y")
						{
							if ($val == "Y")
							{
								$arSqlSearch[] = "G.ID=0";
							}
							break;
						}
						else
						{
							$arSqlSearch[] = ($val == "Y") ? "G.ID=1" : "G.ID>1";
						}
						break;
					case "NAME":
						$match = ($match_value_set && $arFilter[$key . "_EXACT_MATCH"] == "Y") ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.NAME", $val, $match);
						break;
					case "STRING_ID":
						$match = ($match_value_set && $arFilter[$key . "_EXACT_MATCH"] == "N") ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.STRING_ID", $val, $match);
						break;
					case "DESCRIPTION":
						$match = ($match_value_set && $arFilter[$key . "_EXACT_MATCH"] == "Y") ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.DESCRIPTION", $val, $match);
						break;
					case "USERS_1":
						$SHOW_USERS_AMOUNT = "Y";
						$arSqlSearch_h[] = "USERS>=" . intval($val);
						break;
					case "USERS_2":
						$SHOW_USERS_AMOUNT = "Y";
						$arSqlSearch_h[] = "USERS<=" . intval($val);
						break;
					case "ANONYMOUS":
						if ($val == 'Y' || $val == 'N')
						{
							$arSqlSearch[] = "G.ANONYMOUS='" . $val . "'";
						}
						break;
				}
			}
			foreach ($arSqlSearch_h as $condition)
			{
				$strSqlSearch_h .= " and (" . $condition . ") ";
			}
		}

		$by = strtolower($by);

		if ($by == "id")
		{
			$strSqlOrder = " ORDER BY G.ID ";
		}
		elseif ($by == "active")
		{
			$strSqlOrder = " ORDER BY G.ACTIVE ";
		}
		elseif ($by == "timestamp_x")
		{
			$strSqlOrder = " ORDER BY G.TIMESTAMP_X ";
		}
		elseif ($by == "c_sort")
		{
			$strSqlOrder = " ORDER BY G.C_SORT ";
		}
		elseif ($by == "sort")
		{
			$strSqlOrder = " ORDER BY G.C_SORT, G.NAME, G.ID ";
		}
		elseif ($by == "name")
		{
			$strSqlOrder = " ORDER BY G.NAME ";
		}
		elseif ($by == "string_id")
		{
			$strSqlOrder = " ORDER BY G.STRING_ID ";
		}
		elseif ($by == "description")
		{
			$strSqlOrder = " ORDER BY G.DESCRIPTION ";
		}
		elseif ($by == "anonymous")
		{
			$strSqlOrder = " ORDER BY G.ANONYMOUS ";
		}
		elseif ($by == "dropdown")
		{
			$strSqlOrder = " ORDER BY C_SORT, NAME ";
		}
		elseif ($by == "users")
		{
			$strSqlOrder = " ORDER BY USERS ";
			$SHOW_USERS_AMOUNT = "Y";
		}
		else
		{
			$strSqlOrder = " ORDER BY G.C_SORT ";
		}

		if (strtolower($order) == "desc")
		{
			$strSqlOrder .= " desc ";
		}
		else
		{
			$strSqlOrder .= " asc ";
		}

		$str_USERS = $str_TABLE = "";
		if ($SHOW_USERS_AMOUNT == "Y")
		{
			$str_USERS = "count(distinct U.USER_ID)						USERS,";
			$str_TABLE = "LEFT JOIN b_user_group U ON (U.GROUP_ID=G.ID AND ((U.DATE_ACTIVE_FROM IS NULL) OR (U.DATE_ACTIVE_FROM <= " . $DB->CurrentTimeFunction() . ")) AND ((U.DATE_ACTIVE_TO IS NULL) OR (U.DATE_ACTIVE_TO >= " . $DB->CurrentTimeFunction() . ")))";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				G.ID, G.ACTIVE, G.C_SORT, G.ANONYMOUS, G.NAME, G.DESCRIPTION, G.STRING_ID,
				" . $str_USERS . "
				G.ID REFERENCE_ID,
				" . $helper->getConcatFunction("G.NAME", "' ['", "G.ID", "']'") . " REFERENCE,
				" . $DB->DateToCharFunction("G.TIMESTAMP_X") . " TIMESTAMP_X
			FROM
				b_group G
			" . $str_TABLE . "
			WHERE
			" . $strSqlSearch . "
			GROUP BY
				G.ID, G.ACTIVE, G.C_SORT, G.TIMESTAMP_X, G.ANONYMOUS, G.NAME, G.STRING_ID, G.DESCRIPTION
			HAVING
				1=1
				" . $strSqlSearch_h . "
			" . $strSqlOrder;

		$res = $DB->Query($strSql);
		$res->is_filtered = (IsFiltered($strSqlSearch) || $strSqlSearch_h <> '');
		return $res;
	}

	//*************** COMMON UTILS *********************/
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
		}
		elseif (str_starts_with($key, ">"))
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (str_starts_with($key, "<="))
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (str_starts_with($key, "<"))
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (str_starts_with($key, "@"))
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (str_starts_with($key, "~"))
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (str_starts_with($key, "%"))
		{
			$key = substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return ["FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull];
	}

	public static function PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";

		$arGroupByFunct = ["COUNT", "AVG", "MIN", "MAX", "SUM"];

		$arAlreadyJoined = [];

		// GROUP BY -->
		if (is_array($arGroupBy) && !empty($arGroupBy))
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = strtoupper($val);
				$key = strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if ($strSqlGroupBy <> '')
					{
						$strSqlGroupBy .= ", ";
					}
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& $arFields[$val]["FROM"] <> ''
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
						{
							$strSqlFrom .= " ";
						}
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && empty($arGroupBy))
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% " . $arFields[$arFieldsKeys[0]]["FIELD"] . ") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && is_string($arSelectFields) && $arSelectFields <> '' && array_key_exists($arSelectFields, $arFields))
			{
				$arSelectFields = [$arSelectFields];
			}

			if (empty($arSelectFields)
				|| !is_array($arSelectFields)
				|| in_array("*", $arSelectFields)
			)
			{
				foreach ($arFields as $FIELD_ID => $arField)
				{
					if (isset($arField["WHERE_ONLY"])
						&& $arField["WHERE_ONLY"] == "Y")
					{
						continue;
					}

					if ($strSqlSelect <> '')
					{
						$strSqlSelect .= ", ";
					}

					if ($arField["TYPE"] == "datetime")
					{
						$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"]) . " as " . $FIELD_ID;
					}
					elseif ($arField["TYPE"] == "date")
					{
						$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"], "SHORT") . " as " . $FIELD_ID;
					}
					else
					{
						$strSqlSelect .= $arField["FIELD"] . " as " . $FIELD_ID;
					}

					if (isset($arField["FROM"])
						&& $arField["FROM"] <> ''
						&& !in_array($arField["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
						{
							$strSqlFrom .= " ";
						}
						$strSqlFrom .= $arField["FROM"];
						$arAlreadyJoined[] = $arField["FROM"];
					}
				}
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					$val = strtoupper($val);
					$key = strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if ($strSqlSelect <> '')
						{
							$strSqlSelect .= ", ";
						}

						if (in_array($key, $arGroupByFunct))
						{
							$strSqlSelect .= $key . "(" . $arFields[$val]["FIELD"] . ") as " . $val;
						}
						else
						{
							if ($arFields[$val]["TYPE"] == "datetime")
							{
								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"]) . " as " . $val;
							}
							elseif ($arFields[$val]["TYPE"] == "date")
							{
								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT") . " as " . $val;
							}
							else
							{
								$strSqlSelect .= $arFields[$val]["FIELD"] . " as " . $val;
							}
						}

						if (isset($arFields[$val]["FROM"])
							&& $arFields[$val]["FROM"] <> ''
							&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
						{
							if ($strSqlFrom <> '')
							{
								$strSqlFrom .= " ";
							}
							$strSqlFrom .= $arFields[$val]["FROM"];
							$arAlreadyJoined[] = $arFields[$val]["FROM"];
						}
					}
				}
			}

			if ($strSqlGroupBy <> '')
			{
				if ($strSqlSelect <> '')
				{
					$strSqlSelect .= ", ";
				}
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% " . $arFields[$arFieldsKeys[0]]["FIELD"] . ") as CNT";
			}
			else
			{
				$strSqlSelect = "%%_DISTINCT_%% " . $strSqlSelect;
			}
		}
		// <-- SELECT

		// WHERE -->
		$arSqlSearch = [];

		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $vals)
			{
				if (!is_array($vals))
				{
					$vals = [$vals];
				}

				$key_res = static::GetFilterOperation($key);
				$key = $key_res["FIELD"];
				$strNegative = $key_res["NEGATIVE"];
				$strOperation = $key_res["OPERATION"];
				$strOrNull = $key_res["OR_NULL"];

				if (array_key_exists($key, $arFields))
				{
					$arSqlSearch_tmp = [];
					foreach ($vals as $val)
					{
						if (isset($arFields[$key]["WHERE"]))
						{
							$arSqlSearch_tmp1 = call_user_func_array(
								$arFields[$key]["WHERE"],
								[$val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter]
							);
							if ($arSqlSearch_tmp1 !== false)
							{
								$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
							}
						}
						else
						{
							if ($arFields[$key]["TYPE"] == "int")
							{
								if (intval($val) <= 0)
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? "NOT" : "") . "(" . $arFields[$key]["FIELD"] . " IS NULL OR " . $arFields[$key]["FIELD"] . " <= 0)";
								}
								else
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? " " . $arFields[$key]["FIELD"] . " IS NULL OR NOT " : "") . "(" . $arFields[$key]["FIELD"] . " " . $strOperation . " " . intval($val) . " )";
								}
							}
							elseif ($arFields[$key]["TYPE"] == "double")
							{
								$val = str_replace(",", ".", $val);
								if (DoubleVal($val) <= 0)
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? "NOT" : "") . "(" . $arFields[$key]["FIELD"] . " IS NULL OR " . $arFields[$key]["FIELD"] . " <= 0)";
								}
								else
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? " " . $arFields[$key]["FIELD"] . " IS NULL OR NOT " : "") . "(" . $arFields[$key]["FIELD"] . " " . $strOperation . " " . DoubleVal($val) . " )";
								}
							}
							elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
							{
								if ($strOperation == "QUERY")
								{
									$arSqlSearch_tmp[] = GetFilterQuery($arFields[$key]["FIELD"], $val);
								}
								else
								{
									if ((string)$val == '')
									{
										$arSqlSearch_tmp[] = ($strNegative == "Y" ? "NOT" : "") . "(" . $arFields[$key]["FIELD"] . " IS NULL OR LENGTH(" . $arFields[$key]["FIELD"] . ")<=0)";
									}
									else
									{
										$arSqlSearch_tmp[] = ($strNegative == "Y" ? " " . $arFields[$key]["FIELD"] . " IS NULL OR NOT " : "") . "(" . $arFields[$key]["FIELD"] . " " . $strOperation . " '" . $DB->ForSql($val) . "' )";
									}
								}
							}
							elseif ($arFields[$key]["TYPE"] == "datetime")
							{
								if ((string)$val == '')
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? "NOT" : "") . "(" . $arFields[$key]["FIELD"] . " IS NULL)";
								}
								else
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? " " . $arFields[$key]["FIELD"] . " IS NULL OR NOT " : "") . "(" . $arFields[$key]["FIELD"] . " " . $strOperation . " " . $DB->CharToDateFunction($DB->ForSql($val)) . ")";
								}
							}
							elseif ($arFields[$key]["TYPE"] == "date")
							{
								if ((string)$val == '')
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? "NOT" : "") . "(" . $arFields[$key]["FIELD"] . " IS NULL)";
								}
								else
								{
									$arSqlSearch_tmp[] = ($strNegative == "Y" ? " " . $arFields[$key]["FIELD"] . " IS NULL OR NOT " : "") . "(" . $arFields[$key]["FIELD"] . " " . $strOperation . " " . $DB->CharToDateFunction($DB->ForSql($val), "SHORT") . ")";
								}
							}
						}
					}

					if (isset($arFields[$key]["FROM"])
						&& $arFields[$key]["FROM"] <> ''
						&& !in_array($arFields[$key]["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
						{
							$strSqlFrom .= " ";
						}
						$strSqlFrom .= $arFields[$key]["FROM"];
						$arAlreadyJoined[] = $arFields[$key]["FROM"];
					}

					$strSqlSearch_tmp = "";
					foreach ($arSqlSearch_tmp as $condition)
					{
						if ($strSqlSearch_tmp != "")
						{
							$strSqlSearch_tmp .= ($strNegative == "Y" ? " AND " : " OR ");
						}
						$strSqlSearch_tmp .= "(" . $condition . ")";
					}
					if ($strOrNull == "Y")
					{
						if ($strSqlSearch_tmp != "")
						{
							$strSqlSearch_tmp .= ($strNegative == "Y" ? " AND " : " OR ");
						}
						$strSqlSearch_tmp .= "(" . $arFields[$key]["FIELD"] . " IS " . ($strNegative == "Y" ? "NOT " : "") . "NULL)";
					}

					if ($strSqlSearch_tmp != "")
					{
						$arSqlSearch[] = "(" . $strSqlSearch_tmp . ")";
					}
				}
			}
		}

		foreach ($arSqlSearch as $condition)
		{
			if ($strSqlWhere != "")
			{
				$strSqlWhere .= " AND ";
			}
			$strSqlWhere .= "(" . $condition . ")";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = [];
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if ($order != "ASC")
			{
				$order = "DESC";
			}

			if (array_key_exists($by, $arFields))
			{
				$arSqlOrder[] = " " . $arFields[$by]["FIELD"] . " " . $order . " ";

				if (isset($arFields[$by]["FROM"])
					&& $arFields[$by]["FROM"] <> ''
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if ($strSqlFrom <> '')
					{
						$strSqlFrom .= " ";
					}
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
		}

		$strSqlOrderBy = implode(", ", $arSqlOrder);
		// <-- ORDER BY

		return [
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy,
		];
	}

	public static function GetListEx($arOrder = ["ID" => "DESC"], $arFilter = [], $arGroupBy = false, $arNavStartParams = false, $arSelectFields = [])
	{
		global $DB;

		if (empty($arSelectFields))
		{
			$arSelectFields = ["ID", "TIMESTAMP_X", "ACTIVE", "C_SORT", "ANONYMOUS", "NAME", "DESCRIPTION"];
		}

		// FIELDS -->
		$arFields = [
			"ID" => ["FIELD" => "G.ID", "TYPE" => "int"],
			"TIMESTAMP_X" => ["FIELD" => "G.TIMESTAMP_X", "TYPE" => "datetime"],
			"ACTIVE" => ["FIELD" => "G.ACTIVE", "TYPE" => "char"],
			"C_SORT" => ["FIELD" => "G.C_SORT", "TYPE" => "int"],
			"ANONYMOUS" => ["FIELD" => "G.ANONYMOUS", "TYPE" => "char"],
			"NAME" => ["FIELD" => "G.NAME", "TYPE" => "string"],
			"STRING_ID" => ["FIELD" => "G.STRING_ID", "TYPE" => "string"],
			"DESCRIPTION" => ["FIELD" => "G.DESCRIPTION", "TYPE" => "string"],
			"USER_USER_ID" => ["FIELD" => "UG.USER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"],
			"USER_GROUP_ID" => ["FIELD" => "UG.GROUP_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"],
			"USER_DATE_ACTIVE_FROM" => ["FIELD" => "UG.DATE_ACTIVE_FROM", "TYPE" => "datetime", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"],
			"USER_DATE_ACTIVE_TO" => ["FIELD" => "UG.DATE_ACTIVE_TO", "TYPE" => "datetime", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"],
		];
		// <-- FIELDS

		$arSqls = static::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && empty($arGroupBy))
		{
			$strSql =
				"SELECT " . $arSqls["SELECT"] . " " .
				"FROM b_group G " .
				"	" . $arSqls["FROM"] . " ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql .= "WHERE " . $arSqls["WHERE"] . " ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql .= "GROUP BY " . $arSqls["GROUPBY"] . " ";
			}

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
			{
				return $arRes["CNT"];
			}
			else
			{
				return false;
			}
		}

		$strSql =
			"SELECT " . $arSqls["SELECT"] . " " .
			"FROM b_group G " .
			"	" . $arSqls["FROM"] . " ";
		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE " . $arSqls["WHERE"] . " ";
		}
		if ($arSqls["GROUPBY"] <> '')
		{
			$strSql .= "GROUP BY " . $arSqls["GROUPBY"] . " ";
		}
		if ($arSqls["ORDERBY"] <> '')
		{
			$strSql .= "ORDER BY " . $arSqls["ORDERBY"] . " ";
		}

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT " .
				"FROM b_group G " .
				"	" . $arSqls["FROM"] . " ";
			if ($arSqls["WHERE"] <> '')
			{
				$strSql_tmp .= "WHERE " . $arSqls["WHERE"] . " ";
			}
			if ($arSqls["GROUPBY"] <> '')
			{
				$strSql_tmp .= "GROUP BY " . $arSqls["GROUPBY"] . " ";
			}

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
				{
					$cnt = $arRes["CNT"];
				}
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
			{
				$strSql .= "LIMIT " . intval($arNavStartParams["nTopCount"]);
			}
			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}

	public static function GetByID($ID, $SHOW_USERS_AMOUNT = "N")
	{
		$params = [
			'select' => ['ID', 'ACTIVE', 'C_SORT', 'ANONYMOUS', 'NAME', 'STRING_ID', 'DESCRIPTION', 'TIMESTAMP_X'],
		];

		if ($SHOW_USERS_AMOUNT == "Y")
		{
			$params['select'][] = 'USERS';
			$params['runtime'] = [new ExpressionField('USERS', 'COUNT(%s)', 'USER_GROUP.USER_ID')];
		}
		else
		{
			$params['select'][] = 'SECURITY_POLICY';
		}

		$result = GroupTable::getByPrimary($ID, $params);

		return new CDBResult($result);
	}

	public function CheckFields($arFields, $ID = false)
	{
		$this->LAST_ERROR = "";

		if (isset($arFields["NAME"]) && $arFields["NAME"] == '')
		{
			$this->LAST_ERROR .= GetMessage("BAD_GROUP_NAME") . "<br>";
		}

		if (is_array($arFields["USER_ID"]) && !empty($arFields["USER_ID"]))
		{
			if (is_array($arFields["USER_ID"][0]) && !empty($arFields["USER_ID"][0]))
			{
				foreach ($arFields["USER_ID"] as $arUser)
				{
					if ($arUser["DATE_ACTIVE_FROM"] <> '' && !CheckDateTime($arUser["DATE_ACTIVE_FROM"]))
					{
						$this->LAST_ERROR .= GetMessage("WRONG_USER_DATE_ACTIVE_FROM", ["#USER_ID#" => $arUser["USER_ID"]]) . "<br>";
					}

					if ($arUser["DATE_ACTIVE_TO"] <> '' && !CheckDateTime($arUser["DATE_ACTIVE_TO"]))
					{
						$this->LAST_ERROR .= GetMessage("WRONG_USER_DATE_ACTIVE_TO", ["#USER_ID#" => $arUser["USER_ID"]]) . "<br>";
					}
				}
			}
		}

		if (isset($arFields['STRING_ID']) && $arFields['STRING_ID'] <> '')
		{
			$res = GroupTable::getRow([
				'select' => ['ID'],
				'filter' => ['=STRING_ID' => $arFields['STRING_ID']],
			]);
			if ($res)
			{
				if ($ID === false || $ID != $res['ID'])
				{
					$this->LAST_ERROR .= GetMessage('MAIN_ERROR_STRING_ID') . "<br>";
				}
			}
		}

		if (isset($arFields["TIMESTAMP_X"]) && !CheckDateTime($arFields["TIMESTAMP_X"]))
		{
			$this->LAST_ERROR .= GetMessage('MAIN_GROUP_INCORRECT_TIMESTAMP') . "<br>";
		}

		if ($this->LAST_ERROR <> '')
		{
			return false;
		}

		return true;
	}

	public function Update($ID, $arFields)
	{
		global $APPLICATION;

		$ID = intval($ID);

		if (!$this->CheckFields($arFields, $ID))
		{
			return false;
		}

		foreach (GetModuleEvents("main", "OnBeforeGroupUpdate", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, [$ID, &$arFields]);
			if ($bEventRes === false)
			{
				if ($err = $APPLICATION->GetException())
				{
					$this->LAST_ERROR .= $err->GetString() . "<br>";
				}
				else
				{
					$this->LAST_ERROR .= "Unknown error in OnBeforeGroupUpdate handler." . "<br>";
				}
				return false;
			}
		}

		if ($ID <= 2)
		{
			unset($arFields["ACTIVE"]);
		}

		$activeChanged = false;
		if (isset($arFields["ACTIVE"]))
		{
			if ($arFields["ACTIVE"] != "Y")
			{
				$arFields["ACTIVE"] = "N";
			}

			// check whether the group was activated or deactivated
			$res = GroupTable::getRowById($ID, ['select' => ['ACTIVE']]);
			if ($res && $res["ACTIVE"] != $arFields["ACTIVE"])
			{
				CGroupAuthProvider::RecalculateForGroup($ID, ($arFields["ACTIVE"] == "Y"));
				$activeChanged = true;
			}
		}

		$arFields["TIMESTAMP_X"] = new DateTime($arFields["TIMESTAMP_X"] ?? null);

		if (isset($arFields["SECURITY_POLICY"]))
		{
			if (COption::GetOptionString("main", "event_log_group_policy", "N") === "Y")
			{
				//get old security policy
				$aPrevPolicy = [];
				$res = GroupTable::getRowById($ID, ['select' => ['SECURITY_POLICY']]);
				if ($res && $res["SECURITY_POLICY"] != '')
				{
					$aPrevPolicy = unserialize($res["SECURITY_POLICY"], ['allowed_classes' => false]);
				}
				//compare with new one
				$aNewPolicy = [];
				if ($arFields["SECURITY_POLICY"] <> '')
				{
					$aNewPolicy = unserialize($arFields["SECURITY_POLICY"], ['allowed_classes' => false]);
				}
				$aDiff = array_diff_assoc($aNewPolicy, $aPrevPolicy);
				if (empty($aDiff))
				{
					$aDiff = array_diff_assoc($aPrevPolicy, $aNewPolicy);
				}
				if (!empty($aDiff))
				{
					CEventLog::Log(CEventLog::SEVERITY_SECURITY, "GROUP_POLICY_CHANGED", "main", $ID, ['before' => $aPrevPolicy, 'after' => $aNewPolicy]);
				}
			}
		}

		$fields = $arFields;
		unset($fields["USER_ID"]);

		GroupTable::update($ID, $fields);

		if (isset($arFields["USER_ID"]) && is_array($arFields["USER_ID"]) && $ID != 2)
		{
			$prevUsers = UserGroupTable::getList([
				'select' => ['USER_ID'],
				'filter' => ['=GROUP_ID' => $ID],
			])->fetchAll();

			$added = static::AddUsersToGroup($ID, $arFields["USER_ID"]);

			foreach ($prevUsers as $user)
			{
				if (!isset($added[(int)$user['USER_ID']]))
				{
					if (!($ID == 1 && $user['USER_ID'] == 1))
					{
						CUser::RemoveUserGroup((int)$user['USER_ID'], [$ID]);
					}
				}
			}
		}

		foreach (GetModuleEvents("main", "OnAfterGroupUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID, $arFields]);
		}

		if ($activeChanged)
		{
			// cached join with groups and GROUP.ACTIVE='Y'
			ModuleGroupTable::cleanCache();
		}

		if (COption::GetOptionString("main", "event_log_group_edit", "N") === "Y")
		{
			CEventLog::Log(CEventLog::SEVERITY_SECURITY, "GROUP_UPDATED", "main", $ID);
		}

		return true;
	}

	public static function Delete($ID)
	{
		global $APPLICATION;

		$ID = intval($ID);
		if ($ID <= 2)
		{
			return false;
		}

		foreach (GetModuleEvents("main", "OnBeforeGroupDelete", true) as $arEvent)
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

		foreach (GetModuleEvents("main", "OnGroupDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID]);
		}

		CMain::DelGroupRight("", [$ID]);

		CGroupAuthProvider::RecalculateForGroup($ID, false);

		UserGroupTable::deleteByFilter(['=GROUP_ID' => $ID]);

		CUser::clearUserGroupCache();

		GroupTable::delete($ID);

		if (COption::GetOptionString("main", "event_log_group_edit", "N") === "Y")
		{
			CEventLog::Log(CEventLog::SEVERITY_SECURITY, "GROUP_DELETED", "main", $ID);
		}

		return true;
	}

	public static function GetGroupUser($ID)
	{
		$res = static::GetGroupUserEx($ID);
		$arr = [];
		while ($r = $res->Fetch())
		{
			$arr[] = $r["USER_ID"];
		}

		return $arr;
	}

	public static function GetGroupUserEx($ID)
	{
		$ID = intval($ID);

		if ($ID == 2)
		{
			trigger_error("Group 2 contains all the users, the call was ignored.", E_USER_WARNING);

			$result = new CDBResult();
			$result->InitFromArray([]);
			return $result;
		}

		$users = UserGroupTable::getList([
			'filter' => ['=GROUP_ID' => $ID],
			'cache' => ['ttl' => 3600],
		]);

		$userList = [];
		$now = time();
		while ($user = $users->fetch())
		{
			/** @var DateTime $dateFrom */
			$dateFrom = $user['DATE_ACTIVE_FROM'];
			/** @var DateTime $dateTo */
			$dateTo = $user['DATE_ACTIVE_TO'];

			if (
				(empty($dateFrom) || $dateFrom->getTimestamp() <= $now)
				&& (empty($dateTo) || $dateTo->getTimestamp() >= $now)
			)
			{
				// TZ convertion to string
				$userList[] = [
					'USER_ID' => $user['USER_ID'],
					'DATE_ACTIVE_FROM' => (string)$dateFrom,
					'DATE_ACTIVE_TO' => (string)$dateTo,
				];
			}
		}

		$result = new CDBResult();
		$result->InitFromArray($userList);
		return $result;
	}

	public static function GetSubordinateGroups($grId)
	{
		if (!is_array($grId))
		{
			$grId = [$grId];
		}

		Collection::normalizeArrayValuesByInt($grId, false);

		$result = ['2'];
		if (!empty($grId))
		{
			$groups = GroupSubordinateTable::query()
				->setSelect(['ID', 'AR_SUBGROUP_ID'])
				->setCacheTtl(86400)
				->exec()
			;

			$cache = [];
			while ($group = $groups->fetch())
			{
				$cache[$group['ID']] = explode(',', $group['AR_SUBGROUP_ID']);
			}

			foreach ($cache as $groupId => $subordinateGroups)
			{
				if (in_array($groupId, $grId))
				{
					$result = array_merge($result, $subordinateGroups);
				}
			}
		}

		Collection::normalizeArrayValuesByInt($result, false);

		return $result;
	}

	public static function SetSubordinateGroups($grId, $arSubGroups = false)
	{
		$grId = (int)$grId;

		GroupSubordinateTable::delete($grId);

		if (is_array($arSubGroups))
		{
			GroupSubordinateTable::add([
				'ID' => $grId,
				'AR_SUBGROUP_ID' => implode(',', $arSubGroups),
			]);
		}
	}

	public static function GetTasks($ID, $onlyMainTasks = true, $module_id = false)
	{
		global $DB;

		$sql_str = 'SELECT GT.TASK_ID,T.MODULE_ID,GT.EXTERNAL_ID
			FROM b_group_task GT
			INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
			WHERE GT.GROUP_ID=' . intval($ID);
		if ($module_id !== false)
		{
			$sql_str .= ' AND T.MODULE_ID="' . $DB->ForSQL($module_id) . '"';
		}

		$z = $DB->Query($sql_str);
		$arr = [];
		$ex_arr = [];
		while ($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
			{
				$arr[$r['MODULE_ID']] = $r['TASK_ID'];
			}
			else
			{
				$ex_arr[] = $r;
			}
		}
		if ($onlyMainTasks)
		{
			return $arr;
		}
		else
		{
			return [$arr, $ex_arr];
		}
	}

	public static function SetTasks($ID, $arr)
	{
		global $DB;
		$ID = intval($ID);

		if (COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = [];
			$rsTask = $DB->Query("SELECT TASK_ID FROM b_group_task WHERE GROUP_ID=" . $ID);
			while ($arTask = $rsTask->Fetch())
			{
				$arOldTasks[] = $arTask["TASK_ID"];
			}
			//compare with new ones
			$aNewTasks = [];
			foreach ($arr as $task_id)
			{
				if ($task_id > 0)
				{
					$aNewTasks[] = $task_id;
				}
			}
			$aDiff = array_diff($arOldTasks, $aNewTasks);
			if (empty($aDiff))
			{
				$aDiff = array_diff($aNewTasks, $arOldTasks);
			}
			if (!empty($aDiff))
			{
				CEventLog::Log(CEventLog::SEVERITY_SECURITY, "MODULE_RIGHTS_CHANGED", "main", $ID, ['before' => $arOldTasks, 'after' => $aNewTasks]);
			}
		}

		$sql_str = "DELETE FROM b_group_task WHERE GROUP_ID=" . $ID .
			" AND (EXTERNAL_ID IS NULL OR EXTERNAL_ID = '')";
		$DB->Query($sql_str);

		$sID = "0";
		if (is_array($arr))
		{
			foreach ($arr as $task_id)
			{
				$sID .= "," . intval($task_id);
			}
		}

		$DB->Query(
			"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) " .
			"SELECT '" . $ID . "', ID, '' " .
			"FROM b_task " .
			"WHERE ID IN (" . $sID . ") "
		);
	}

	public static function GetTasksForModule($module_id, $onlyMainTasks = true)
	{
		global $DB;

		$sql_str = "SELECT GT.TASK_ID,GT.GROUP_ID,GT.EXTERNAL_ID,T.NAME
			FROM b_group_task GT
			INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
			WHERE T.MODULE_ID='" . $DB->ForSQL($module_id) . "'";

		$z = $DB->Query($sql_str);

		$main_arr = [];
		$ext_arr = [];
		while ($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
			{
				$main_arr[$r['GROUP_ID']] = ['ID' => $r['TASK_ID'], 'NAME' => $r['NAME']];
			}
			elseif (!$onlyMainTasks)
			{
				if (!isset($ext_arr[$r['GROUP_ID']]))
				{
					$ext_arr[$r['GROUP_ID']] = [];
				}
				$ext_arr[$r['GROUP_ID']][] = ['ID' => $r['TASK_ID'], 'NAME' => $r['NAME'], 'EXTERNAL_ID' => $r['EXTERNAL_ID']];
			}
		}
		if ($onlyMainTasks)
		{
			return $main_arr;
		}
		else
		{
			return [$main_arr, $ext_arr];
		}
	}

	public static function SetTasksForModule($module_id, $arGroupTask)
	{
		global $DB;

		$module_id = $DB->ForSql($module_id);
		$sql_str = "SELECT T.ID
			FROM b_task T
			WHERE T.MODULE_ID='" . $module_id . "'";
		$r = $DB->Query($sql_str);
		$arIds = [];
		while ($arR = $r->Fetch())
		{
			$arIds[] = $arR['ID'];
		}

		if (COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = [];
			if (!empty($arIds))
			{
				$rsTask = $DB->Query("SELECT GROUP_ID, TASK_ID FROM b_group_task WHERE TASK_ID IN (" . implode(",", $arIds) . ")");
				while ($arTask = $rsTask->Fetch())
				{
					$arOldTasks[$arTask["GROUP_ID"]] = $arTask["TASK_ID"];
				}
			}
			//compare with new ones
			foreach ($arOldTasks as $gr_id => $task_id)
			{
				if ($task_id <> $arGroupTask[$gr_id]['ID'])
				{
					CEventLog::Log(CEventLog::SEVERITY_SECURITY, "MODULE_RIGHTS_CHANGED", "main", $gr_id, ['before' => [$module_id => $task_id], 'after' => [$module_id => $arGroupTask[$gr_id]['ID']]]);
				}
			}
			foreach ($arGroupTask as $gr_id => $oTask)
			{
				if (intval($oTask['ID']) > 0 && !array_key_exists($gr_id, $arOldTasks))
				{
					CEventLog::Log(CEventLog::SEVERITY_SECURITY, "MODULE_RIGHTS_CHANGED", "main", $gr_id, ['before' => [$module_id => ''], 'after' => [$module_id => $oTask['ID']]]);
				}
			}
		}

		if (!empty($arIds))
		{
			$sql_str = "DELETE FROM b_group_task WHERE TASK_ID IN (" . implode(",", $arIds) . ")";
			$DB->Query($sql_str);
		}

		foreach ($arGroupTask as $gr_id => $oTask)
		{
			if (intval($oTask['ID']) > 0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) " .
					"SELECT G.ID, T.ID, '' " .
					"FROM b_group G, b_task T " .
					"WHERE G.ID = " . intval($gr_id) . " AND
					T.ID = " . intval($oTask['ID'])
				);
			}
		}
	}

	public static function GetModulePermission($group_id, $module_id)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		// check module permissions mode
		$strSql = "SELECT T.ID, GT.TASK_ID FROM b_task T LEFT JOIN b_group_task GT ON T.ID=GT.TASK_ID AND GT.GROUP_ID=" . intval($group_id) . " WHERE T.MODULE_ID='" . $DB->ForSql($module_id) . "'";
		$dbr_tasks = $DB->Query($strSql);
		if ($ar_task = $dbr_tasks->Fetch())
		{
			do
			{
				if ($ar_task["TASK_ID"] > 0)
				{
					return $ar_task["TASK_ID"];
				}
			}
			while ($ar_task = $dbr_tasks->Fetch());

			return false;
		}

		return $APPLICATION->GetGroupRight($module_id, [$group_id], "N", "N");
	}

	public static function SetModulePermission($group_id, $module_id, $permission)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		if (intval($permission) <= 0 && $permission !== false)
		{
			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='" . $DB->ForSql($module_id) . "' AND NAME='" . $DB->ForSql($permission) . "'";
			$db_task = $DB->Query($strSql);
			if ($ar_task = $db_task->Fetch())
			{
				$permission = $ar_task['ID'];
			}
		}

		$permission_letter = '';
		if (intval($permission) > 0 || $permission === false)
		{
			$DB->StartTransaction();

			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='" . $DB->ForSql($module_id) . "'";
			$dbr_tasks = $DB->Query($strSql);
			$arIds = [];
			while ($arTask = $dbr_tasks->Fetch())
			{
				$arIds[] = $arTask['ID'];
			}

			if (!empty($arIds))
			{
				$strSql = "DELETE FROM b_group_task WHERE GROUP_ID=" . intval($group_id) . " AND TASK_ID IN (" . implode(",", $arIds) . ")";
				$DB->Query($strSql);
			}

			if (intval($permission) > 0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) " .
					"SELECT G.ID, T.ID, '' " .
					"FROM b_group G, b_task T " .
					"WHERE G.ID = " . intval($group_id) . " AND T.ID = " . intval($permission)
				);

				$permission_letter = CTask::GetLetter($permission);
			}

			$DB->Commit();
		}
		else
		{
			$permission_letter = $permission;
		}

		if ($permission_letter <> '')
		{
			$APPLICATION->SetGroupRight($module_id, $group_id, $permission_letter);
		}
		else
		{
			$APPLICATION->DelGroupRight($module_id, [$group_id]);
		}
	}

	/**
	 * Returns group ID by its STRING_ID, cached query.
	 * @param string $code
	 * @return false|int
	 */
	public static function GetIDByCode($code)
	{
		if (strval(intval($code)) == $code && $code > 0)
		{
			return (int)$code;
		}

		if (strtolower($code) == 'administrators')
		{
			return 1;
		}

		if (strtolower($code) == 'everyone')
		{
			return 2;
		}

		$res = GroupTable::getRow([
			'select' => ['ID'],
			'filter' => ['=STRING_ID' => $code],
			'cache' => ['ttl' => 86400],
		]);

		if ($res)
		{
			return (int)$res['ID'];
		}

		return false;
	}
}

class CGroup extends CAllGroup
{
}
