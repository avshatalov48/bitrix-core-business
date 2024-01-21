<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

class CAllOperation
{
	public static function GetList($arOrder = ['MODULE_ID' => 'asc'], $arFilter = [])
	{
		global $DB;

		static $arFields = [
			"ID" => ["FIELD_NAME" => "O.ID", "FIELD_TYPE" => "int"],
			"NAME" => ["FIELD_NAME" => "O.NAME", "FIELD_TYPE" => "string"],
			"MODULE_ID" => ["FIELD_NAME" => "O.MODULE_ID", "FIELD_TYPE" => "string"],
			"BINDING" => ["FIELD_NAME" => "O.BINDING", "FIELD_TYPE" => "string"],
		];

		$arSqlSearch = [];
		if (is_array($arFilter))
		{
			foreach ($arFilter as $n => $val)
			{
				$n = strtoupper($n);
				if ((string)$val == '' || strval($val) == "NOT_REF")
				{
					continue;
				}
				if ($n == 'ID' || $n == 'MODULE_ID' || $n == 'BINDING')
				{
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, 'N');
				}
				elseif (isset($arFields[$n]))
				{
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
				}
			}
		}

		$strOrderBy = '';
		foreach ($arOrder as $by => $order)
		{
			if (isset($arFields[strtoupper($by)]))
			{
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"] . ' ' . (strtolower($order) == 'desc' ? 'desc' : 'asc') . ',';
			}
		}

		if ($strOrderBy <> '')
		{
			$strOrderBy = "ORDER BY " . rtrim($strOrderBy, ",");
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT *
			FROM
				b_operation O
			WHERE
			" . $strSqlSearch . "
			" . $strOrderBy;

		$res = $DB->Query($strSql);
		return $res;
	}

	public static function GetAllowedModules()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID FROM b_operation O';
		$z = $DB->Query($sql_str);
		$arr = [];
		while ($r = $z->Fetch())
		{
			$arr[] = $r['MODULE_ID'];
		}
		return $arr;
	}

	public static function GetBindingList()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID, O.BINDING FROM b_operation O';
		$z = $DB->Query($sql_str);
		$arr = [];
		while ($r = $z->Fetch())
		{
			$arr[] = $r;
		}
		return $arr;
	}

	public static function GetIDByName($name)
	{
		$z = static::GetList(['MODULE_ID' => 'asc'], ["NAME" => $name]);
		if ($r = $z->Fetch())
		{
			return $r['ID'];
		}
		return false;
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = [];

		if (preg_match("/[^a-z0-9._]/i", $module))
		{
			return [];
		}

		if (!isset($descriptions[$module]))
		{
			if (($path = getLocalPath("modules/" . $module . "/admin/operation_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"] . $path);
			}
			else
			{
				$descriptions[$module] = [];
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if (isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if (isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}
}

class COperation extends CAllOperation
{
}
