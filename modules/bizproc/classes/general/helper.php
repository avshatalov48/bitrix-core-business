<?php

use Bitrix\Main;
use Bitrix\Bitrix24;
use Bitrix\Bizproc;

class CBPHelper
{
	const DISTR_B24 = 'b24';
	const DISTR_BOX = 'box';

	private static $serverName;
	protected static $cAccess;
	protected static $groupsCache = array();

	protected static function getAccessProvider()
	{
		if (self::$cAccess === null)
		{
			self::$cAccess = new CAccess;
		}
		return self::$cAccess;
	}

	private static function usersArrayToStringInternal($arUsers, $arWorkflowTemplate, $arAllowableUserGroups, $appendId = true)
	{
		if (is_array($arUsers))
		{
			$r = [];

			$keys = array_keys($arUsers);
			foreach ($keys as $key)
			{
				$r[$key] = self::UsersArrayToStringInternal($arUsers[$key], $arWorkflowTemplate, $arAllowableUserGroups, $appendId);
			}

			if (count($r) == 2)
			{
				$keys = array_keys($r);
				if ($keys[0] == 0 && $keys[1] == 1 && is_string($r[0]) && is_string($r[1]))
				{
					if (in_array($r[0], array("Document", "Template", "Variable", "User"))
						|| preg_match('#^A\d+_\d+_\d+_\d+$#i', $r[0])
						|| is_array($arWorkflowTemplate) && CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $r[0]) != null
						)
					{
						return "{=".$r[0].":".$r[1]."}";
					}
				}
			}

			return implode(", ", $r);
		}
		else
		{
			if (array_key_exists(mb_strtolower($arUsers), $arAllowableUserGroups))
			{
				return $arAllowableUserGroups[mb_strtolower($arUsers)];
			}

			if (CBPActivity::isExpression($arUsers))
			{
				return $arUsers;
			}

			$userId = 0;
			if (mb_substr($arUsers, 0, mb_strlen("user_")) == "user_")
			{
				$userId = intval(mb_substr($arUsers, mb_strlen("user_")));
			}

			if ($userId > 0)
			{
				$db = CUser::GetList(
					"LAST_NAME",
					"asc",
					["ID_EQUAL_EXACT" => $userId],
					[
						"NAV_PARAMS" => false,
						'FIELDS'=> [
							'ID',
							'LOGIN',
							'EMAIL',
							'NAME',
							'LAST_NAME',
							'SECOND_NAME'
						],
					]
				);

				if ($ar = $db->Fetch())
				{
					$str = CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $ar, true, false);
					if ($appendId)
					{
						$str = $str." [".$ar["ID"]."]";
					}
					return str_replace(",", " ", $str);
				}
			}
			else if (mb_strpos($arUsers, 'group_') === 0)
			{
				$str = self::getExtendedGroupName($arUsers, $appendId);
				return str_replace(array(',', ';'), array(' ', ' '), $str);
			}

			return str_replace(",", " ", $arUsers);
		}
	}

	public static function usersArrayToString($users, $arWorkflowTemplate, $documentType, $appendId = true)
	{
		if (static::isEmptyValue($users))
		{
			return "";
		}

		$uniqueUsers = is_array($users) ? [] : $users;
		if (is_array($users))
		{
			foreach ($users as $user)
			{
				if (is_string($user))
				{
					$uniqueUsers[$user] = $user;
				}
				else
				{
					$uniqueUsers[] = $user;
				}
			}

			$uniqueUsers = array_values($uniqueUsers);
		}

		$arAllowableUserGroups = [];
		$arAllowableUserGroupsTmp = CBPDocument::GetAllowableUserGroups($documentType);
		foreach ($arAllowableUserGroupsTmp as $k1 => $v1)
		{
			$arAllowableUserGroups[mb_strtolower($k1)] = str_replace(",", " ", $v1);
		}

		return self::UsersArrayToStringInternal($uniqueUsers, $arWorkflowTemplate, $arAllowableUserGroups, $appendId);
	}

	public static function usersStringToArray($strUsers, $documentType, &$arErrors, $callbackFunction = null)
	{
		$arErrors = [];

		$strUsers = trim($strUsers);
		if ($strUsers == '')
		{
			return ($callbackFunction != null) ? [[], []] : [];
		}

		if (CBPActivity::isExpression($strUsers))
		{
			return ($callbackFunction != null) ? [[$strUsers], []] : [$strUsers];
		}

		$arUsers = [];
		$strUsers = str_replace(";", ",", $strUsers);
		$arUsersTmp = explode(",", $strUsers);
		foreach ($arUsersTmp as $user)
		{
			$user = trim($user);
			if ($user <> '')
			{
				$arUsers[] = $user;
			}
		}

		$arAllowableUserGroups = null;

		$arResult = $arResultAlt = [];
		foreach ($arUsers as $user)
		{
			$bCorrectUser = false;
			$bNotFoundUser = true;
			if (CBPActivity::isExpression($user))
			{
				$bCorrectUser = true;
				$arResult[] = $user;
			}
			else
			{
				if ($arAllowableUserGroups == null)
				{
					$arAllowableUserGroups = [];
					$arAllowableUserGroupsTmp = CBPDocument::GetAllowableUserGroups($documentType);
					foreach ($arAllowableUserGroupsTmp as $k1 => $v1)
					{
						$arAllowableUserGroups[mb_strtolower($k1)] = mb_strtolower($v1);
					}
				}

				if (array_key_exists(mb_strtolower($user), $arAllowableUserGroups))
				{
					$bCorrectUser = true;
					$arResult[] = $user;
				}
				elseif (($k1 = array_search(mb_strtolower($user), $arAllowableUserGroups)) !== false)
				{
					$bCorrectUser = true;
					$arResult[] = $k1;
				}
				elseif (preg_match('#\[([A-Z]{1,}[0-9A-Z_]+)\]$#i', $user, $arMatches))
				{
					$bCorrectUser = true;
					$arResult[] = 'group_' . mb_strtolower($arMatches[1]);
				}
				else
				{
					$ar = self::SearchUserByName($user);
					$cnt = count($ar);
					if ($cnt == 1)
					{
						$bCorrectUser = true;
						$arResult[] = 'user_' . $ar[0];
					}
					elseif ($cnt > 1)
					{
						$bNotFoundUser = false;
						$arErrors[] = [
							'code' => 'Ambiguous',
							'message' => str_replace(
								'#USER#',
								htmlspecialcharsbx($user),
								GetMessage('BPCGHLP_AMBIGUOUS_USER')
							),
						];
					}
					elseif ($callbackFunction != null)
					{
						$s = call_user_func_array($callbackFunction, [$user]);
						if ($s != null)
						{
							$arResultAlt[] = $s;
							$bCorrectUser = true;
						}
					}
				}
			}

			if (!$bCorrectUser)
			{
				if ($bNotFoundUser)
				{
					$arErrors[] = [
						'code' => 'NotFound',
						'message' => str_replace(
							'#USER#',
							htmlspecialcharsbx($user),
							GetMessage('BPCGHLP_INVALID_USER')
						),
					];
				}
			}
		}

		return ($callbackFunction != null) ? [$arResult, $arResultAlt] : $arResult;
	}

	private static function searchUserByName($user)
	{
		$user = trim($user);
		if ($user == '')
		{
			return [];
		}

		$userId = 0;
		if ($user."|" == intval($user)."|")
		{
			$userId = intval($user);
		}

		if ($userId <= 0)
		{
			$arMatches = [];
			if (preg_match('#\[(\d+)\]#i', $user, $arMatches))
			{
				$userId = intval($arMatches[1]);
			}
		}

		$arResult = [];

		$dbUsers = false;
		if ($userId > 0)
		{
			$arFilter = array("ID_EQUAL_EXACT" => $userId);

			$dbUsers = CUser::GetList(
				"LAST_NAME",
				"asc",
				$arFilter,
				[
					'FIELDS' => ['ID'],
					'NAV_PARAMS' => false
				]
			);
		}
		else
		{
			$userLogin = "";
			$arMatches = [];
			if (preg_match('#\((.+?)\)#i', $user, $arMatches))
			{
				$userLogin = $arMatches[1];
				$user = trim(str_replace("(".$userLogin.")", "", $user));
			}

			$userEmail = "";
			$arMatches = [];
			if (preg_match("#<(.+?)>#i", $user, $arMatches))
			{
				if (check_email($arMatches[1]))
				{
					$userEmail = $arMatches[1];
					$user = trim(Str_Replace("<".$userEmail.">", "", $user));
				}
			}

			$arUser = [];
			$arUserTmp = explode(" ", $user);
			foreach ($arUserTmp as $s)
			{
				$s = trim($s);
				if ($s <> '')
				{
					$arUser[] = $s;
				}
			}
			if ($userLogin <> '')
			{
				$arUser[] = $userLogin;
			}

			$dbUsers = CUser::SearchUserByName($arUser, $userEmail, true);
		}

		if ($dbUsers)
		{
			while ($arUsers = $dbUsers->GetNext())
			{
				$arResult[] = $arUsers["ID"];
			}
		}

		return $arResult;
	}

	public static function formatTimePeriod($period)
	{
		$period = intval($period);

		$days = intval($period / 86400);
		$period = $period - $days * 86400;

		$hours = intval($period / 3600);
		$period = $period - $hours * 3600;

		$minutes = intval($period / 60);
		$period = $period - $minutes * 60;

		$seconds = intval($period);

		$s = "";
		if ($days > 0)
		{
			$s .= str_replace(
				array("#VAL#", "#UNIT#"),
				array($days, self::MakeWord($days, array(GetMessage("BPCGHLP_DAY1"), GetMessage("BPCGHLP_DAY2"), GetMessage("BPCGHLP_DAY3")))),
				"#VAL# #UNIT# "
			);
		}
		if ($hours > 0)
		{
			$s .= str_replace(
				array("#VAL#", "#UNIT#"),
				array($hours, self::MakeWord($hours, array(GetMessage("BPCGHLP_HOUR1"), GetMessage("BPCGHLP_HOUR2"), GetMessage("BPCGHLP_HOUR3")))),
				"#VAL# #UNIT# "
			);
		}
		if ($minutes > 0)
		{
			$s .= str_replace(
				array("#VAL#", "#UNIT#"),
				array($minutes, self::MakeWord($minutes, array(GetMessage("BPCGHLP_MIN1"), GetMessage("BPCGHLP_MIN2"), GetMessage("BPCGHLP_MIN3")))),
				"#VAL# #UNIT# "
			);
		}
		if ($seconds > 0)
		{
			$s .= str_replace(
				array("#VAL#", "#UNIT#"),
				array($seconds, self::MakeWord($seconds, array(GetMessage("BPCGHLP_SEC1"), GetMessage("BPCGHLP_SEC2"), GetMessage("BPCGHLP_SEC3")))),
				"#VAL# #UNIT# "
			);
		}

		return $s;
	}

	private static function makeWord($val, $arWords)
	{
		if ($val > 20)
		{
			$val = ($val % 10);
		}

		if ($val == 1)
		{
			return $arWords[0];
		}
		elseif ($val > 1 && $val < 5)
		{
			return $arWords[1];
		}
		else
		{
			return $arWords[2];
		}
	}

	public static function getFilterOperation($key)
	{
		$strNegative = "N";
		if (mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (mb_substr($key, 0, 1) == "+")
		{
			$key = mb_substr($key, 1);
			$strOrNull = "Y";
		}

		if (mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$strOperation = ">=";
		}
		elseif (mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$strOperation = ">";
		}
		elseif (mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "<=";
		}
		elseif (mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$strOperation = "<";
		}
		elseif (mb_substr($key, 0, 1) == "@")
		{
			$key = mb_substr($key, 1);
			$strOperation = "=";
			$strNegative = 'N';
		}
		elseif (mb_substr($key, 0, 1) == "~")
		{
			$key = mb_substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (mb_substr($key, 0, 1) == "%")
		{
			$key = mb_substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	public static function prepareSql(&$arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";
		$strSqlOrderBy = "";

		$arOrder = array_change_key_case($arOrder, CASE_UPPER);

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = [];

		// GROUP BY -->
		if (is_array($arGroupBy) && count($arGroupBy)>0)
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = mb_strtoupper($val);
				$key = mb_strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if ($strSqlGroupBy <> '')
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (!empty($arFields[$val]["FROM"]))
					{
						$toJoin = (array)$arFields[$val]["FROM"];
						foreach ($toJoin as $join)
						{
							if (in_array($join, $arAlreadyJoined))
							{
								continue;
							}
							if ($strSqlFrom <> '')
							{
								$strSqlFrom .= " ";
							}
							$strSqlFrom .= $join;
							$arAlreadyJoined[] = $join;
						}
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && $arSelectFields <> '' && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields)<=0
				|| in_array("*", $arSelectFields))
			{
				for ($i = 0, $cnt = count($arFieldsKeys); $i < $cnt; $i++)
				{
					if (isset($arFields[$arFieldsKeys[$i]]["WHERE_ONLY"])
						&& $arFields[$arFieldsKeys[$i]]["WHERE_ONLY"] == "Y")
					{
						continue;
					}

					if ($strSqlSelect <> '')
						$strSqlSelect .= ", ";

					if ($arFields[$arFieldsKeys[$i]]["TYPE"] == "datetime")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "FULL")." as ".$arFieldsKeys[$i];
					}
					elseif ($arFields[$arFieldsKeys[$i]]["TYPE"] == "date")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "SHORT")." as ".$arFieldsKeys[$i];
					}
					else
						$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i];

					if (!empty($arFields[$arFieldsKeys[$i]]["FROM"]))
					{
						$toJoin = (array)$arFields[$arFieldsKeys[$i]]["FROM"];
						foreach ($toJoin as $join)
						{
							if (in_array($join, $arAlreadyJoined))
								continue;
							if ($strSqlFrom <> '')
								$strSqlFrom .= " ";
							$strSqlFrom .= $join;
							$arAlreadyJoined[] = $join;
						}
					}
				}
			}
			else
			{
				foreach ($arOrder as $by => $order)
				{
					if (
						isset($arFields[$by])
						&& !in_array($by, $arSelectFields)
						&& ($arFields[$by]["TYPE"] == "date" || $arFields[$by]["TYPE"] == "datetime")
					)
						$arSelectFields[] = $by;
				}

				foreach ($arSelectFields as $key => $val)
				{
					$val = mb_strtoupper($val);
					$key = mb_strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if ($strSqlSelect <> '')
							$strSqlSelect .= ", ";

						if (in_array($key, $arGroupByFunct))
						{
							$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
						}
						else
						{
							if ($arFields[$val]["TYPE"] == "datetime")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "FULL")." as ".$val;
							}
							elseif ($arFields[$val]["TYPE"] == "date")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT")." as ".$val;
							}
							else
								$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val;
						}
						if (!empty($arFields[$val]["FROM"]))
						{
							$toJoin = (array)$arFields[$val]["FROM"];
							foreach ($toJoin as $join)
							{
								if (in_array($join, $arAlreadyJoined))
									continue;
								if ($strSqlFrom <> '')
									$strSqlFrom .= " ";
								$strSqlFrom .= $join;
								$arAlreadyJoined[] = $join;
							}
						}
					}
				}
			}

			if ($strSqlGroupBy <> '')
			{
				if ($strSqlSelect <> '')
					$strSqlSelect .= ", ";
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
			else
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
		}
		// <-- SELECT

		// WHERE -->
		$arSqlSearch = [];

		if (!is_array($arFilter))
			$filter_keys = [];
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0, $cnt = count($filter_keys); $i < $cnt; $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$key = $filter_keys[$i];
			$key_res = CBPHelper::GetFilterOperation($key);
			$key = $key_res["FIELD"];
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			$strOrNull = $key_res["OR_NULL"];

			if (array_key_exists($key, $arFields))
			{
				$arSqlSearch_tmp = array();
				for ($j = 0, $cntj = count($vals); $j < $cntj; $j++)
				{
					$val = $vals[$j];

					if (isset($arFields[$key]["WHERE"]))
					{
						$arSqlSearch_tmp1 = call_user_func_array(
								$arFields[$key]["WHERE"],
								array($val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter)
							);
						if ($arSqlSearch_tmp1 !== false)
							$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
					}
					else
					{
						if ($arFields[$key]["TYPE"] == "int")
						{
							if ((intval($val) == 0) && (mb_strpos($strOperation, "=") !== False))
								$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
							else
								$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".intval($val)." )";
						}
						elseif ($arFields[$key]["TYPE"] == "double")
						{
							$val = str_replace(",", ".", $val);

							if ((DoubleVal($val) == 0) && (mb_strpos($strOperation, "=") !== False))
								$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
							else
								$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".DoubleVal($val)." )";
						}
						elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
						{
							if ($strOperation == "QUERY")
							{
								$arSqlSearch_tmp[] = GetFilterQuery($arFields[$key]["FIELD"], $val, "Y");
							}
							else
							{
								if (($val == '') && (mb_strpos($strOperation, "=") !== False))
									$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$DB->Length($arFields[$key]["FIELD"])." <= 0) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
							}
						}
						elseif ($arFields[$key]["TYPE"] == "datetime")
						{
							if ($val == '')
								$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
							else
								$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
						}
						elseif ($arFields[$key]["TYPE"] == "date")
						{
							if ($val == '')
								$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
							else
								$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
						}
					}
				}

				if (!empty($arFields[$key]["FROM"]))
				{
					$toJoin = (array)$arFields[$key]["FROM"];
					foreach ($toJoin as $join)
					{
						if (in_array($join, $arAlreadyJoined))
							continue;
						if ($strSqlFrom <> '')
							$strSqlFrom .= " ";
						$strSqlFrom .= $join;
						$arAlreadyJoined[] = $join;
					}
				}

				$strSqlSearch_tmp = "";
				for ($j = 0, $cntj = count($arSqlSearch_tmp); $j < $cntj; $j++)
				{
					if ($j > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arSqlSearch_tmp[$j].")";
				}
				if ($strOrNull == "Y")
				{
					if ($strSqlSearch_tmp <> '')
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." IS ".($strNegative=="Y" ? "NOT " : "")."NULL)";

					if ($strSqlSearch_tmp <> '')
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					if ($arFields[$key]["TYPE"] == "int" || $arFields[$key]["TYPE"] == "double")
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." 0)";
					elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." '')";
				}

				if ($strSqlSearch_tmp != "")
					$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
			}
		}

		for ($i = 0, $cnt = count($arSqlSearch); $i < $cnt; $i++)
		{
			if ($strSqlWhere <> '')
				$strSqlWhere .= " AND ";
			$strSqlWhere .= "(".$arSqlSearch[$i].")";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = Array();
		foreach ($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			$order = $order ? mb_strtoupper($order) : '';

			if ($order != "ASC")
				$order = "DESC";
			else
				$order = "ASC";

			if (array_key_exists($by, $arFields))
			{
				if ($arFields[$by]["TYPE"] == "datetime" || $arFields[$by]["TYPE"] == "date")
					$arSqlOrder[] = " ".$by."_X1 ".$order." ";
				else
					$arSqlOrder[] = " ".$arFields[$by]["FIELD"]." ".$order." ";

				if (!empty($arFields[$by]["FROM"]))
				{
					$toJoin = (array)$arFields[$by]["FROM"];
					foreach ($toJoin as $join)
					{
						if (in_array($join, $arAlreadyJoined))
							continue;
						if ($strSqlFrom <> '')
							$strSqlFrom .= " ";
						$strSqlFrom .= $join;
						$arAlreadyJoined[] = $join;
					}
				}
			}
		}

		$strSqlOrderBy = "";
		DelDuplicateSort($arSqlOrder);
		for ($i = 0, $cnt = count($arSqlOrder); $i < $cnt; $i++)
		{
			if ($strSqlOrderBy <> '')
				$strSqlOrderBy .= ", ";

			$strSqlOrderBy .= $arSqlOrder[$i];
		}
		// <-- ORDER BY

		return array(
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy
		);
	}

	public static function parseDocumentId($parameterDocumentId)
	{
		if (!is_array($parameterDocumentId))
		{
			$parameterDocumentId = array($parameterDocumentId);
		}

		$moduleId = "";
		$entity = "";
		$documentId = "";

		$cnt = count($parameterDocumentId);
		if ($cnt > 2)
		{
			$documentId = $parameterDocumentId[2];
			$entity = $parameterDocumentId[1];
			$moduleId = $parameterDocumentId[0];
		}
		elseif ($cnt == 2)
		{
			$documentId = $parameterDocumentId[1];
			$entity = $parameterDocumentId[0];
		}

		$moduleId = is_scalar($moduleId) ? trim($moduleId) : '';
		$entity = is_scalar($entity) ? trim($entity) : '';
		$documentId = is_scalar($documentId) ? trim($documentId) : '';

		if ($documentId === '')
		{
			throw new CBPArgumentNullException("documentId");
		}

		if ($entity === '')
		{
			throw new CBPArgumentNullException("entity");
		}

		return [$moduleId, $entity, $documentId];
	}

	public static function parseDocumentIdArray($parameterDocumentId)
	{
		if (!is_array($parameterDocumentId))
		{
			$parameterDocumentId = array($parameterDocumentId);
		}

		$moduleId = "";
		$entity = "";
		$documentId = "";

		$cnt = count($parameterDocumentId);
		if ($cnt > 2)
		{
			$documentId = $parameterDocumentId[2];
			$entity = $parameterDocumentId[1];
			$moduleId = $parameterDocumentId[0];
		}
		elseif ($cnt == 2)
		{
			$documentId = $parameterDocumentId[1];
			$entity = $parameterDocumentId[0];
		}

		$moduleId = trim($moduleId);

		$entity = trim($entity);
		if ($entity == '')
		{
			throw new CBPArgumentNullException("entity");
		}

		if (is_array($documentId))
		{
			$a = [];
			foreach ($documentId as $v)
			{
				$v = trim($v);
				if ($v <> '')
				{
					$a[] = $v;
				}
			}
			$documentId = $a;
			if (count($documentId) <= 0)
			{
				throw new CBPArgumentNullException("documentId");
			}
		}
		else
		{
			$documentId = trim($documentId);
			if ($documentId == '')
			{
				throw new CBPArgumentNullException("documentId");
			}
			$documentId = array($documentId);
		}

		return [$moduleId, $entity, $documentId];
	}

	public static function getFieldValuePrintable($fieldName, $fieldType, $result)
	{
		$newResult = null;

		switch ($fieldType)
		{
			case "user":
				if (is_array($result))
				{
					$newResult = [];
					foreach ($result as $r)
					{
						$newResult[] = CBPHelper::ConvertUserToPrintableForm($r);
					}
				}
				else
				{
					$newResult = CBPHelper::ConvertUserToPrintableForm($result);
				}
				break;

			case "file":
				if (is_array($result))
				{
					$newResult = array();
					foreach ($result as $r)
					{
						$r = intval($r);
						$dbImg = CFile::GetByID($r);
						if ($arImg = $dbImg->Fetch())
						{
							$newResult[] = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($arImg["FILE_NAME"])."&i=".$r."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
						}
					}
				}
				else
				{
					$result = intval($result);
					$dbImg = CFile::GetByID($result);
					if ($arImg = $dbImg->Fetch())
					{
						$newResult = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($arImg["FILE_NAME"])."&i=".$result."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
					}
				}
				break;

			default:
				$newResult = $result;
		}

		return $newResult;
	}

	public static function convertUserToPrintableForm($userId, $nameTemplate = "", $htmlSpecialChars = true)
	{
		if (mb_substr($userId, 0, mb_strlen("user_")) == "user_")
		{
			$userId = mb_substr($userId, mb_strlen("user_"));
		}

		if (empty($nameTemplate))
		{
			$nameTemplate = COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID);
		}

		$userId = intval($userId);

		$db = CUser::GetList(
			"LAST_NAME",
			"asc",
			["ID_EQUAL_EXACT" => $userId],
			[
				"NAV_PARAMS" => false,
				'FIELDS'=> [
					'ID',
					'LOGIN',
					'EMAIL',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME'
				]
			]
		);

		$str = "";
		if ($ar = $db->Fetch())
		{
			$str = CUser::FormatName($nameTemplate, $ar, true, $htmlSpecialChars);
			$str = $str." [".$ar["ID"]."]";
			$str = str_replace(",", " ", $str);
		}

		return $str;
	}

	/**
	 * @deprecated
	 * @param $objectName
	 * @param $arDocumentFields
	 * @param $arDocumentFieldTypes
	 * @return false|string
	 */
	public static function getJSFunctionsForFields($objectName, $arDocumentFields, $arDocumentFieldTypes)
	{
		ob_start();
		CAdminCalendar::ShowScript();

		return ob_get_clean();
	}

	public static function getDocumentFieldTypes()
	{
		$arResult = array(
			"string" => array("Name" => GetMessage("BPCGHLP_PROP_STRING"), "BaseType" => "string"),
			"text" => array("Name" => GetMessage("BPCGHLP_PROP_TEXT"), "BaseType" => "text"),
			"int" => array("Name" => GetMessage("BPCGHLP_PROP_INT"), "BaseType" => "int"),
			"double" => array("Name" => GetMessage("BPCGHLP_PROP_DOUBLE"), "BaseType" => "double"),
			"select" => array("Name" => GetMessage("BPCGHLP_PROP_SELECT"), "BaseType" => "select"),
			"internalselect" => array("Name" => GetMessage("BPCGHLP_PROP_INTERNALSELECT_1"), "BaseType" => "internalselect"),
			"bool" => array("Name" => GetMessage("BPCGHLP_PROP_BOOL"), "BaseType" => "bool"),
			"date" => array("Name" => GetMessage("BPCGHLP_PROP_DATA"), "BaseType" => "date"),
			"datetime" => array("Name" => GetMessage("BPCGHLP_PROP_DATETIME"), "BaseType" => "datetime"),
			"user" => array("Name" => GetMessage("BPCGHLP_PROP_USER"), "BaseType" => "user"),
			"file" => array("Name" => GetMessage("BPCGHLP_PROP_FILE"), "BaseType" => "file"),
		);

		return $arResult;
	}

	/**
	 * @deprecated
	 */
	public static function getGUIFieldEdit($documentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection)
	{
		return self::GetFieldInputControl(
			$documentType,
			$arDocumentField,
			array("Form" => $formName, "Field" => $fieldName),
			$fieldValue,
			$bAllowSelection
		);
	}

	public static function getFieldInputControl($documentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection = false)
	{
		if (!is_array($fieldValue) || is_array($fieldValue) && CBPHelper::IsAssociativeArray($fieldValue))
		{
			$fieldValue = array($fieldValue);
		}

		ob_start();

		if ($arFieldType["Type"] == "select")
		{
			$fieldValueTmp = $fieldValue;
			?>
			<select id="id_<?= $arFieldName["Field"] ?>" name="<?= $arFieldName["Field"].($arFieldType["Multiple"] ? "[]" : "") ?>"<?= ($arFieldType["Multiple"] ? ' size="5" multiple' : '') ?>>
				<?
				if (!$arFieldType["Required"])
					echo '<option value="">['.GetMessage("BPCGHLP_NOT_SET").']</option>';
				foreach ($arFieldType["Options"] as $k => $v)
				{
					$ind = array_search($k, $fieldValueTmp);
					echo '<option value="'.htmlspecialcharsbx($k).'"'.($ind !== false ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
					if ($ind !== false)
						unset($fieldValueTmp[$ind]);
				}
				?>
			</select>
			<?
			if ($bAllowSelection)
			{
				?>
				<br /><input type="text" id="id_<?= $arFieldName["Field"] ?>_text" name="<?= $arFieldName["Field"] ?>_text" value="<?
				if (count($fieldValueTmp) > 0)
				{
					$a = array_values($fieldValueTmp);
					echo htmlspecialcharsbx($a[0]);
				}
				?>"><?
				echo CBPHelper::renderControlSelectorButton('id_'.$arFieldName["Field"].'_text', 'select');
			}
		}
		elseif ($arFieldType["Type"] == "user")
		{
			$fieldValue = CBPHelper::UsersArrayToString($fieldValue, null, $documentType);
			?><input type="text" size="40" id="id_<?= $arFieldName["Field"] ?>" name="<?= $arFieldName["Field"] ?>" value="<?= htmlspecialcharsbx($fieldValue) ?>"><? echo CBPHelper::renderControlSelectorButton('id_'.$arFieldName["Field"], 'user');
		}
		else
		{
			if (!array_key_exists("CBPVirtualDocumentCloneRowPrinted", $GLOBALS) && $arFieldType["Multiple"])
			{
				$GLOBALS["CBPVirtualDocumentCloneRowPrinted"] = 1;
				?>
				<script>
				<!--
				function CBPVirtualDocumentCloneRow(tableID)
				{
					var tbl = document.getElementById(tableID);
					var cnt = tbl.rows.length;
					var oRow = tbl.insertRow(cnt);
					var oCell = oRow.insertCell(0);
					var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('[n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf(']', s);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 2, e - s));
						sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
						p = s + 1;
					}
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('__n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf('_', s + 2);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 3, e - s));
						sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
						p = e + 1;
					}
					oCell.innerHTML = sHTML;
					var patt = new RegExp('<' + 'script' + '>[^\000]*?<' + '\/' + 'script' + '>', 'ig');
					var code = sHTML.match(patt);
					if (code)
					{
						for (var i = 0; i < code.length; i++)
						{
							if (code[i] != '')
							{
								var s = code[i].substring(8, code[i].length - 9);
								jsUtils.EvalGlobal(s);
							}
						}
					}
				}
				//-->
				</script>
				<?
			}

			if ($arFieldType["Multiple"])
				echo '<table width="100%" border="0" cellpadding="2" cellspacing="2" id="CBPVirtualDocument_'.$arFieldName["Field"].'_Table">';

			if ($bAllowSelection)
			{
				$arFieldType["BaseType"] = "string";

				static $arDocumentTypes = null;
				if (is_null($arDocumentTypes))
					$arDocumentTypes = self::GetDocumentFieldTypes($documentType);

				if (array_key_exists($arFieldType["Type"], $arDocumentTypes))
					$arFieldType["BaseType"] = $arDocumentTypes[$arFieldType["Type"]]["BaseType"];
			}

			$fieldValueTmp = $fieldValue;

			$ind = -1;
			foreach ($fieldValue as $key => $value)
			{
				$ind++;
				$fieldNameId = 'id_'.$arFieldName["Field"].'__n'.$ind.'_';
				$fieldNameName = $arFieldName["Field"].($arFieldType["Multiple"] ? "[n".$ind."]" : "");

				if ($arFieldType["Multiple"])
					echo '<tr><td>';

				switch ($arFieldType["Type"])
				{
					case "int":
					case "double":
						unset($fieldValueTmp[$key]);
						?><input type="text" size="10" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialcharsbx($value) ?>"><?
						break;
					case "file":
						unset($fieldValueTmp[$key]);
						?><input type="file" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?
						break;
					case "bool":
						if (in_array($value, array("Y", "N")))
							unset($fieldValueTmp[$key]);
						?>
						<select id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>">
							<?
							if (!$arFieldType["Required"])
								echo '<option value="">['.GetMessage("BPCGHLP_NOT_SET").']</option>';
							?>
							<option value="Y"<?= (in_array("Y", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
							<option value="N"<?= (in_array("N", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
						</select>
						<?
						break;
					case "text":
						unset($fieldValueTmp[$key]);
						?><textarea rows="5" cols="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?= htmlspecialcharsbx($value) ?></textarea><?
						break;
					case "date":
					case "datetime":
						$v = "";
						if (!CBPActivity::isExpression($value))
						{
							$v = $value;
							unset($fieldValueTmp[$key]);
						}
						echo CAdminCalendar::CalendarDate($fieldNameName, $v, 19, ($arFieldType["Type"] == "date"));
						break;
					default:
						unset($fieldValueTmp[$key]);
						?><input type="text" size="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialcharsbx($value) ?>"><?
				}

				if ($bAllowSelection)
				{
					if (!in_array($arFieldType["Type"], array("file", "bool", "date", "datetime")))
					{
						echo CBPHelper::renderControlSelectorButton($fieldNameId, $arFieldType["BaseType"]);
					}
				}

				if ($arFieldType["Multiple"])
					echo '</td></tr>';
			}

			if ($arFieldType["Multiple"])
				echo "</table>";

			if ($arFieldType["Multiple"])
				echo '<input type="button" value="'.GetMessage("BPCGHLP_ADD").'" onclick="CBPVirtualDocumentCloneRow(\'CBPVirtualDocument_'.$arFieldName["Field"].'_Table\')"/><br />';

			if ($bAllowSelection)
			{
				if (in_array($arFieldType["Type"], array("file", "bool", "date", "datetime")))
				{
					?>
					<input type="text" id="id_<?= $arFieldName["Field"] ?>_text" name="<?= $arFieldName["Field"] ?>_text" value="<?
					if (count($fieldValueTmp) > 0)
					{
						$a = array_values($fieldValueTmp);
						echo htmlspecialcharsbx($a[0]);
					}
					?>"><?
					echo CBPHelper::renderControlSelectorButton('id_'.$arFieldName["Field"].'_text', $arFieldType["BaseType"]);
				}
			}
		}

		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	public static function getFieldInputValue($documentType, $arFieldType, $arFieldName, $arRequest, &$arErrors)
	{
		$result = [];

		if ($arFieldType["Type"] == "user")
		{
			$value = $arRequest[$arFieldName["Field"]];
			if ($value <> '')
			{
				$result = CBPHelper::UsersStringToArray($value, $documentType, $arErrors);
				if (count($arErrors) > 0)
				{
					foreach ($arErrors as $e)
					{
						$arErrors[] = $e;
					}
				}
			}
		}
		elseif (array_key_exists($arFieldName["Field"], $arRequest) || array_key_exists($arFieldName["Field"]."_text", $arRequest))
		{
			$arValue = [];
			if (array_key_exists($arFieldName["Field"], $arRequest))
			{
				$arValue = $arRequest[$arFieldName["Field"]];
				if (!is_array($arValue) || is_array($arValue) && CBPHelper::IsAssociativeArray($arValue))
				{
					$arValue = array($arValue);
				}
			}
			if (array_key_exists($arFieldName["Field"]."_text", $arRequest))
			{
				$arValue[] = $arRequest[$arFieldName["Field"]."_text"];
			}

			foreach ($arValue as $value)
			{
				if (!CBPActivity::isExpression($value))
				{
					if ($arFieldType["Type"] == "int")
					{
						if ($value <> '')
						{
							$value = str_replace(" ", "", $value);
							if ($value."|" == intval($value)."|")
							{
								$value = intval($value);
							}
							else
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID1"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($arFieldType["Type"] == "double")
					{
						if ($value <> '')
						{
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							if ($value."|" == doubleval($value)."|")
							{
								$value = doubleval($value);
							}
							else
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID11"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($arFieldType["Type"] == "select")
					{
						if (!is_array($arFieldType["Options"]) || count($arFieldType["Options"]) <= 0 || $value == '')
						{
							$value = null;
						}
						elseif (!array_key_exists($value, $arFieldType["Options"]))
						{
							$value = null;
							$arErrors[] = array(
								"code" => "ErrorValue",
								"message" => GetMessage("BPCGWTL_INVALID35"),
								"parameter" => $arFieldName["Field"],
							);
						}
					}
					elseif ($arFieldType["Type"] == "bool")
					{
						if ($value !== "Y" && $value !== "N")
						{
							if ($value === true)
							{
								$value = "Y";
							}
							elseif ($value === false)
							{
								$value = "N";
							}
							elseif ($value <> '')
							{
								$value = mb_strtolower($value);
								if (in_array($value, array("y", "yes", "true", "1")))
								{
									$value = "Y";
								}
								elseif (in_array($value, array("n", "no", "false", "0")))
								{
									$value = "N";
								}
								else
								{
									$value = null;
									$arErrors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("BPCGWTL_INVALID45"),
										"parameter" => $arFieldName["Field"],
									);
								}
							}
							else
							{
								$value = null;
							}
						}
					}
					elseif ($arFieldType["Type"] == "file")
					{
						if (array_key_exists("name", $value) && $value["name"] <> '')
						{
							if (!array_key_exists("MODULE_ID", $value) || $value["MODULE_ID"] == '')
								$value["MODULE_ID"] = "bizproc";

							$value = CFile::SaveFile($value, "bizproc_wf");
							if (!$value)
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID915"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					else
					{
						if (!is_array($value) && $value == '')
							$value = null;
					}
				}

				if ($value != null)
					$result[] = $value;
			}
		}

		if (!$arFieldType["Multiple"])
		{
			if (count($result) > 0)
				$result = $result[0];
			else
				$result = null;
		}

		return $result;
	}

	public static function getFieldInputValuePrintable($documentType, $arFieldType, $fieldValue)
	{
		$result = $fieldValue;

		switch ($arFieldType['Type'])
		{
			case "user":
				$result = CBPHelper::UsersArrayToString($fieldValue, null, $documentType);
				break;

			case "bool":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
						$result[] = ((mb_strtoupper($r) == "Y") ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				else
				{
					$result = ((mb_strtoupper($fieldValue) == "Y") ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				break;

			case "file":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
					{
						$r = intval($r);
						$dbImg = CFile::GetByID($r);
						if ($arImg = $dbImg->Fetch())
							$result[] = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($arImg["FILE_NAME"])."&i=".$r."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
					}
				}
				else
				{
					$fieldValue = intval($fieldValue);
					$dbImg = CFile::GetByID($fieldValue);
					if ($arImg = $dbImg->Fetch())
						$result = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($arImg["FILE_NAME"])."&i=".$fieldValue."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
				}
				break;
			case "select":
				if (isset($arFieldType["Options"][$fieldValue]))
					$result = $arFieldType["Options"][$fieldValue];

				break;
		}

		return $result;
	}

	public static function setGUIFieldEdit($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = null)
	{
		return self::GetFieldInputValue($documentType, $arDocumentField, array("Field" => $fieldName), $arRequest, $arErrors);
	}

	/**
	 * @deprecated
	 * @see \CBPHelper::convertBBtoText
	 * @param $text
	 * @param false $siteId
	 * @return array|string|string[]|null
	 */
	public static function convertTextForMail($text, $siteId = false)
	{
		if (is_array($text))
		{
			$text = implode(', ', $text);
		}

		$text = trim($text);
		if ($text == '')
		{
			return "";
		}

		if (!$siteId)
		{
			$siteId = SITE_ID;
		}

		$arPattern = $arReplace = [];

		$arPattern[] = "/\[(code|quote)(.*?)\]/isu";
		$arReplace[] = "\n>================== \\1 ===================\n";

		$arPattern[] = "/\[\/(code|quote)(.*?)\]/isu";
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\<WBR[\s\/]?\>/isu";
		$arReplace[] = "";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\[b\](.+?)\[\/b\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\[i\](.+?)\[\/i\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\[u\](.+?)\[\/u\]/isu";
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\[s\](.+?)\[\/s\]/isu";
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\[(\/?)(color|font|size)([^\]]*)\]/isu";
		$arReplace[] = "";

		//$arPattern[] = "/\[url\](\S+?)\[\/url\]/isu";
		//$arReplace[] = "(URL: \\1)";

		//$arPattern[] = "/\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]/isu";
		//$arReplace[] = "\\2 (URL: \\1)";

		$arPattern[] = "/\[img\](.+?)\[\/img\]/isu";
		$arReplace[] = "(IMAGE: \\1)";

		$arPattern[] = "/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/isu";
		$arReplace[] = "(VIDEO: \\2)";

		$arPattern[] = "/\[(\/?)list\]/isu";
		$arReplace[] = "\n";

		$text = preg_replace($arPattern, $arReplace, $text);


		$dbSite = CSite::GetByID($siteId);
		$arSite = $dbSite->Fetch();
		static::$serverName = $arSite["SERVER_NAME"];
		if (static::$serverName == '')
		{
			if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
			{
				static::$serverName = SITE_SERVER_NAME;
			}
			else
			{
				static::$serverName = COption::GetOptionString("main", "server_name", "");
			}
		}

		$text = preg_replace_callback(
			"/\[url\]([^\]]+?)\[\/url\]/iu",
			array("CBPHelper", "__ConvertAnchorTag"),
			$text
		);
		$text = preg_replace_callback(
			"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/isu",
			array("CBPHelper", "__ConvertAnchorTag"),
			$text
		);

		return $text;
	}

	public static function convertBBtoText(string $text): string
	{
		$textParser = new CTextParser();
		$textParser->allow = [
			'HTML' => 'N',
			'USER' => 'N',
			'ANCHOR' => 'Y',
			'BIU' => 'Y',
			'IMG' => 'Y',
			'QUOTE' => 'N',
			'CODE' => 'N',
			'FONT' => 'Y',
			'LIST' => 'Y',
			'SMILES' => 'N',
			'NL2BR' => 'Y',
			'VIDEO' => 'N',
			'TABLE' => 'N',
			'CUT_ANCHOR' => 'N',
			'ALIGN' => 'N'
		];

		return $textParser->convertText($text);
	}

	public static function __ConvertAnchorTag($url, $text = '', $serverName = '')
	{
		if (is_array($url))
		{
			$text = isset($url[2]) ? $url[2] : $url[1];
			$url = $url[1];
			$serverName = static::$serverName;
		}

		$scheme = \CMain::IsHTTPS() ? 'https' : 'http';

		if (mb_substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//iu", $url))
			$url = $scheme.'://'.$url;
		if (!preg_match("/^(http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+/iu", $url))
			$url = $serverName.$url;
		if (!preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//iu", $url))
			$url = $scheme.'://'.$url;

		$url = str_replace(' ', '%20', $url);

		if ($text <> '' && $text !== $url)
		{
			return $text." ( ".$url." )";
		}

		return $url;
	}

	public static function isAssociativeArray($ar)
	{
		if (!is_array($ar))
		{
			return false;
		}

		$fl = false;

		$arKeys = array_keys($ar);
		$ind = -1;
		$indn = -1;
		foreach ($arKeys as $key)
		{
			$ind++;
			if ($key."!" !== $ind."!")
			{
				if (mb_substr($key, 0, 1) === 'n')
				{
					$indn++;
					if (($indn === 0) && ("".$key === "n1"))
						$indn++;

					if ("".$key !== "n".$indn)
					{
						$fl = true;
						break;
					}
				}
				else
				{
					$fl = true;
					break;
				}
			}
		}

		return $fl;
	}

	public static function extractUsersFromUserGroups($value, $activity)
	{
		$result = [];

		if (!is_array($value))
		{
			$value = array($value);
		}

		$l = mb_strlen("user_");
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		foreach ($value as $v)
		{
			if (mb_substr($v, 0, $l) == "user_")
			{
				$result[] = $v;
			}
			else
			{
				$arDSUsers = self::extractUsersFromExtendedGroup($v);
				if ($arDSUsers === false)
				{
					$arDSUsers = $documentService->GetUsersFromUserGroup($v, $activity->GetDocumentId());
				}
				foreach ($arDSUsers as $v1)
				{
					$result[] = "user_".$v1;
				}
			}
		}

		return $result;
	}

	/**
	 * Method return array of user ids, extracting from special codes. Supported: user (U), group (G),
	 * intranet (IU, D, DR, Dextranet, UA), socnet (SU, SG1_A, SG1_E, SG1_K)
	 *
	 * @param string $code - group code, ex. group_D1
	 * @return bool|array
	 */
	public static function extractUsersFromExtendedGroup($code)
	{
		static $cache = [];

		if (isset($cache[$code]))
		{
			return $cache[$code];
		}

		if (mb_strpos($code, 'group_') !== 0)
		{
			return false;
		}
		$code = mb_strtoupper(mb_substr($code, mb_strlen('group_')));

		if (mb_strpos($code, 'G') === 0)
		{
			$group = (int)mb_substr($code, 1);
			if ($group <= 0)
			{
				return [];
			}
			$result = [];

			$iterator = CUser::GetList(
				"ID",
				"ASC",
				[
					"GROUPS_ID" => $group,
					"ACTIVE" => "Y",
				],
				['FIELDS' => ['ID']]
			);
			while ($user = $iterator->fetch())
			{
				$result[] = $user['ID'];
			}
			$cache[$code] = $result;

			return $result;
		}

		if (preg_match('/^(U|IU|SU)([0-9]+)$/i', $code, $match))
		{
			return array($match[2]);
		}

		if ($code == 'UA' && CModule::IncludeModule('intranet'))
		{
			$result = [];
			$iterator = CUser::GetList("id", "asc",
				array('ACTIVE' => 'Y', '>UF_DEPARTMENT' => 0),
				array('FIELDS' => array('ID'))
			);
			while($user = $iterator->fetch())
			{
				$result[] = $user['ID'];
			}
			$cache[$code] = $result;

			return $result;
		}

		if (preg_match('/^(D|DR)([0-9]+)$/', $code, $match))
		{
			$userService = CBPRuntime::getRuntime()->getUserService();
			$cache[$code] = $userService->extractUsersFromDepartment($match[2], $match[1] === 'DR');

			return $cache[$code];
		}
		if ($code == 'Dextranet' && CModule::IncludeModule('extranet'))
		{
			$result = array();
			$iterator = CUser::GetList("id", "asc",
				array(COption::GetOptionString("extranet", "extranet_public_uf_code", "UF_PUBLIC") => "1",
					"!UF_DEPARTMENT" => false,
					"GROUPS_ID" => array(CExtranet::GetExtranetUserGroupID())
				),
				array('FIELDS' => array('ID'))
			);
			while($user = $iterator->fetch())
			{
				$result[] = $user['ID'];
			}
			$cache[$code] = $result;

			return $result;
		}
		if (preg_match('/^SG([0-9]+)_?([AEK])?$/', $code, $match) && CModule::IncludeModule('socialnetwork'))
		{
			$groupId = (int)$match[1];
			$role = isset($match[2])? $match[2] : 'K';

			$iterator = CSocNetUserToGroup::GetList(
				array("USER_ID" => "ASC"),
				array(
					"=GROUP_ID" => $groupId,
					"<=ROLE" => $role,
					"USER_ACTIVE" => "Y"
				),
				false,
				false,
				array("USER_ID")
			);
			$result = array();
			while($user = $iterator->fetch())
			{
				$result[] = $user['USER_ID'];
			}
			$cache[$code] = $result;

			return $result;
		}

		return false;
	}

	public static function extractUsers($arUsersDraft, $documentId, $bFirst = false)
	{
		$result = [];

		if (!is_array($arUsersDraft))
		{
			$arUsersDraft = array($arUsersDraft);
		}

		$l = mb_strlen("user_");
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();

		foreach ($arUsersDraft as $user)
		{
			if (!is_scalar($user))
			{
				continue;
			}

			if (mb_substr($user, 0, $l) === "user_")
			{
				$user = intval(mb_substr($user, $l));
				if (($user > 0) && !in_array($user, $result))
				{
					if ($bFirst)
					{
						return $user;
					}
					$result[] = $user;
				}
			}
			elseif (\CBPActivity::isExpression($user))
			{
				$parsed = \CBPActivity::parseExpression($user);
				if ($parsed && $parsed['object'] === 'Document')
				{
					$document = $documentService->GetDocument($documentId);
					if ($document && $document[$parsed['field']])
					{
						foreach ((array) $document[$parsed['field']] as $docUser)
						{
							if (mb_substr($docUser, 0, $l) === "user_")
							{
								$user = intval(mb_substr($docUser, $l));
								if (($user > 0) && !in_array($user, $result))
								{
									if ($bFirst)
									{
										return $user;
									}
									$result[] = $user;
								}
							}
						}
					}
				}
			}
			else
			{
				$users = self::extractUsersFromExtendedGroup($user);
				if ($users === false)
				{
					$users = $documentService->GetUsersFromUserGroup($user, $documentId);
				}
				foreach ($users as $u)
				{
					$u = (int)$u;
					if (($u > 0) && !in_array($u, $result))
					{
						if ($bFirst)
						{
							return $u;
						}
						$result[] = $u;
					}
				}
			}
		}

		if (!$bFirst)
		{
			return $result;
		}

		if (count($result) > 0)
		{
			return $result[0];
		}

		return null;
	}

	public static function extractFirstUser($userGroups, $documentId): ?int
	{
		return static::extractUsers($userGroups, $documentId, true);
	}

	public static function makeArrayFlat($ar)
	{
		if (!is_array($ar))
		{
			return array($ar);
		}

		$result = [];

		if (
			!CBPHelper::isAssociativeArray($ar)
			&& (count($ar) === 2)
			&& isset($ar[0], $ar[1])
			&& in_array($ar[0], ["Variable", "Document", "Template", "Workflow", "User", "System"])
			&& is_string($ar[1])
		)
		{
			$result[] = $ar;
			return $result;
		}

		foreach ($ar as $val)
		{
			if (!is_array($val))
			{
				if (trim($val) !== "")
					$result[] = $val;
			}
			else
			{
				foreach (self::MakeArrayFlat($val) as $val1)
					$result[] = $val1;
			}
		}

		return $result;
	}

	public static function flatten($array): array
	{
		if (!is_array($array))
		{
			return [$array];
		}

		$result = [];
		array_walk_recursive($array, function($a) use (&$result) { $result[] = $a; });

		return $result;
	}

	public static function stringify($mixed): string
	{
		if (is_array($mixed))
		{
			return implode(', ', static::flatten($mixed));
		}

		return (string)$mixed;
	}

	public static function getBool($value)
	{
		if (
			empty($value)
			|| $value === 'false'
			|| (is_int($value) && ($value == 0))
			|| (is_scalar($value) && mb_strtoupper($value) == 'N')
		)
		{
			return false;
		}

		return (bool)$value;
	}

	public static function isEmptyValue($value)
	{
		$filter = function ($value)
		{
			return ($value !== null && $value !== '' && $value !== false);
		};

		return (
			$value === null
			||
			$value === ''
			||
			$value === false
			||
			is_array($value) && count(array_filter($value, $filter)) === 0
		);
	}

	public static function convertParameterValues($val)
	{
		$result = $val;

		if (is_string($val) && preg_match(CBPActivity::ValuePattern, $val, $arMatches))
		{
			$result = null;
			if ($arMatches['object'] == "User")
			{
				if ($GLOBALS["USER"]->IsAuthorized())
					$result = "user_".$GLOBALS["USER"]->GetID();
			}
			elseif ($arMatches['object'] == "System")
			{
				if (mb_strtolower($arMatches['field']) === "now")
					$result = date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")));
				elseif (mb_strtolower($arMatches['field']) == "date")
					$result = date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
			}
		}

		return $result;
	}

	public static function stripUserPrefix($value)
	{
		if (is_array($value) && !CBPHelper::IsAssociativeArray($value))
		{
			foreach ($value as &$v)
			{
				if (mb_substr($v, 0, 5) == "user_")
					$v = mb_substr($v, 5);
			}
		}
		elseif (is_string($value))
		{
			if (mb_substr($value, 0, 5) == "user_")
				$value = mb_substr($value, 5);
		}

		return $value;
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public static function getUserExtendedGroups($userId)
	{
		if (!isset(self::$groupsCache[$userId]))
		{
			self::$groupsCache[$userId] = array();
			$access = self::getAccessProvider();
			$userCodes = $access->GetUserCodesArray($userId);
			foreach ($userCodes AS $code)
			{
				self::$groupsCache[$userId][] = 'group_'.mb_strtolower($code);
			}
		}
		return self::$groupsCache[$userId];
	}

	/**
	 * @param string $group - Extended group code (ex. group_g1)
	 * @param bool $appendId - Append id to group name
	 * @return string
	 */
	public static function getExtendedGroupName($group, $appendId = true)
	{
		if (mb_strpos($group, 'group_') === 0)
			$group = mb_substr($group, mb_strlen('group_'));
		$group = mb_strtoupper($group);
		$access = self::getAccessProvider();
		$arNames = $access->GetNames(array($group));
		$groupName = $arNames[$group]['name'] ?? null;

		return $groupName . ($appendId ? ' ['.$group.']' : '');
	}

	/**
	 * @param $users
	 * @return array
	 */

	public static function convertToExtendedGroups($users)
	{
		$users = (array)$users;
		foreach ($users as &$user)
		{
			if (!is_scalar($user))
				continue;
			$user = (string) $user;
			if (mb_strpos($user, 'user_') === 0)
			{
				$user = 'group_u'.mb_substr($user, mb_strlen('user_'));
			}
			elseif (preg_match('#^[0-9]+$#', $user))
			{
				$user = 'group_g'.$user;
			}
			else
				$user = mb_strtolower($user);
		}
		return $users;
	}

	/**
	 * @param $users
	 * @param bool $extractUsers
	 * @return array
	 */

	public static function convertToSimpleGroups($users, $extractUsers = false)
	{
		$users = (array)$users;
		$converted = [];

		foreach ($users as $user)
		{
			if (!is_scalar($user))
				continue;
			$user = mb_strtolower((string)$user);
			if (mb_strpos($user, 'group_u') === 0)
			{
				$converted[] = 'user_'.mb_substr($user, mb_strlen('group_u'));
			}
			elseif (mb_strpos($user, 'group_g') === 0)
			{
				$converted[] = mb_substr($user, mb_strlen('group_g'));
			}
			elseif (mb_strpos($user, 'group_') === 0)
			{
				if ($extractUsers)
				{
					$extracted = self::extractUsersFromExtendedGroup($user);
					if ($extracted !== false)
					{
						foreach ($extracted as $exUser)
						{
							$converted[] = 'user_'.$exUser;
						}
					}
				}
			}
			else
				$converted[] = $user;
		}
		return $converted;
	}

	public static function getForumId()
	{
		$forumId = COption::GetOptionString('bizproc', 'forum_id', 0);
		if (!$forumId && CModule::includeModule('forum'))
		{
			$defaultSiteId = CSite::GetDefSite();
			$forumId = CForumNew::Add(array(
				'NAME' => 'Bizproc Workflow',
				'XML_ID' => 'bizproc_workflow',
				'SITES' => array($defaultSiteId => '/'),
				'ACTIVE' => 'Y',
				'DEDUPLICATION' => 'N'
			));
			COption::SetOptionString("bizproc", "forum_id", $forumId);
		}

		return (int)$forumId;
	}

	public static function getDistrName()
	{
		return CModule::IncludeModule('bitrix24') ? static::DISTR_B24 : static::DISTR_BOX;
	}

	/**
	 * @param int $headUserId
	 * @param int $subUserId
	 * @return bool
	 */
	public static function checkUserSubordination($headUserId, $subUserId)
	{
		if (CModule::IncludeModule('intranet'))
		{
			$headUserId = (int)$headUserId;
			$subUserId = (int)$subUserId;

			if ($headUserId && $subUserId)
			{
				$headDepts = (array) CIntranetUtils::GetSubordinateDepartments($headUserId, true);
				if (!empty($headDepts))
				{
					$subDepts = (array) CIntranetUtils::GetUserDepartments($subUserId);
					return (sizeof(array_intersect($headDepts, $subDepts)) > 0);
				}
			}
		}
		return false;
	}

	public static function renderControlSelectorButton($controlId, $baseType = 'string', array $options = null)
	{
		$selectorProps = \Bitrix\Main\Web\Json::encode(array(
			'controlId' => $controlId,
			'baseType' => $baseType
		));

		$mode = isset($options['mode']) ? $options['mode'] : '';
		$additional = array();

		if (isset($options['style']))
			$additional[] = 'style="'.htmlspecialcharsbx($options['style']).'"';

		if (isset($options['title']))
			$additional[] = 'title="'.htmlspecialcharsbx($options['title']).'"';

		return '<input type="button" value="..." onclick="BPAShowSelector(\''
		.Cutil::JSEscape(htmlspecialcharsbx($controlId))
		.'\', \''.Cutil::JSEscape(htmlspecialcharsbx($baseType))
		.'\', \''.Cutil::JSEscape(htmlspecialcharsbx($mode)).'\');"'
		.' data-role="bp-selector-button" data-bp-selector-props="'.htmlspecialcharsbx($selectorProps).'" '.implode(' ', $additional).'>';
	}

	public static function decodeTemplatePostData(&$data)
	{
		$jsonParams = ['arWorkflowTemplate', 'arWorkflowParameters', 'arWorkflowGlobalVariables', 'arWorkflowVariables', 'arWorkflowGlobalConstants', 'arWorkflowConstants', 'USER_PARAMS'];

		foreach ($jsonParams as $k)
		{
			if (!isset($data[$k]) || !is_array($data[$k]))
			{
				$data[$k] = isset($data[$k]) ? (array) CUtil::JsObjectToPhp($data[$k]) : array();
			}
		}
	}

	public static function makeTimestamp($date, bool $appendOffset = false)
	{
		if (!$date)
		{
			return 0;
		}

		if (is_array($date))
		{
			$date = current(static::flatten($date));
		}

		//serialized date string
		if (is_string($date) && Bizproc\BaseType\Value\DateTime::isSerialized($date))
		{
			$date = new Bizproc\BaseType\Value\DateTime($date);
		}

		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return $date->getTimestamp() + ($appendOffset ? $date->getOffset() : 0);
		}

		if ($date instanceof Main\Type\Date)
		{
			return $date->getTimestamp();
		}

		if (intval($date) . '!' === $date . '!')
		{
			return $date;
		}

		if (($result = MakeTimeStamp($date, FORMAT_DATETIME)) === false)
		{
			if (($result = MakeTimeStamp($date, FORMAT_DATE)) === false)
			{
				if (($result = MakeTimeStamp($date, 'YYYY-MM-DD HH:MI:SS')) === false)
				{
					$result = MakeTimeStamp($date, 'YYYY-MM-DD');
				}
			}
		}

		return (int) $result;
	}

	public static function isWorkTimeAvailable(): bool
	{
		if (
			Main\Loader::includeModule('bitrix24')
			&& !Bitrix24\Feature::isFeatureEnabled('bizproc_timeman')
		)
		{
			return false;
		}

		if (Main\Loader::includeModule('intranet'))
		{
			$workTime = \Bitrix\Intranet\Site\Sections\TimemanSection::getWorkTime();

			return $workTime['available'] && \Bitrix\Main\Loader::includeModule('timeman');
		}

		return false;
	}

	public static function hasStringRepresentation($value): bool
	{
		return (is_scalar($value) || (is_object($value) && method_exists($value, '__toString')));
	}

	public static function isEqualDocument(array $documentA, array $documentB): bool
	{
		return (
			(string)$documentA[0] === (string)$documentB[0]
			&& (string)$documentA[1] === (string)$documentB[1]
			&& (string)$documentA[2] === (string)$documentB[2]
		);
	}
}
