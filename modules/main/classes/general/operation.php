<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

/**
 * @deprecated Use COperation
 */
class CAllOperation
{
	public static function err_mess()
	{
		return "<br>Class: COperation<br>File: ".__FILE__;
	}

	public static function GetList($arOrder = array('MODULE_ID'=>'asc'),$arFilter=array())
	{
		global $DB;

		static $arFields = array(
			"ID" => array("FIELD_NAME" => "O.ID", "FIELD_TYPE" => "int"),
			"NAME" => array("FIELD_NAME" => "O.NAME", "FIELD_TYPE" => "string"),
			"MODULE_ID" => array("FIELD_NAME" => "O.MODULE_ID", "FIELD_TYPE" => "string"),
			"BINDING" => array("FIELD_NAME" => "O.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (static::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		if(is_array($arFilter))
		{
			foreach($arFilter as $n => $val)
			{
				$n = strtoupper($n);
				if((string)$val == '' || strval($val)=="NOT_REF")
					continue;
				if ($n == 'ID' || $n == 'MODULE_ID' || $n == 'BINDING')
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, 'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order) == 'desc'?'desc':'asc').',';

		if($strOrderBy <> '')
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT *
			FROM
				b_operation O
			WHERE
				".$strSqlSearch."
			".$strOrderBy;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetAllowedModules()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[] = $r['MODULE_ID'];
		return $arr;
	}

	public static function GetBindingList()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID, O.BINDING FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[] = $r;
		return $arr;
	}

	public static function GetIDByName($name)
	{
		$z = static::GetList(array('MODULE_ID' => 'asc'), array("NAME" => $name));
		if ($r = $z->Fetch())
			return $r['ID'];
		return false;
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = array();

		if(preg_match("/[^a-z0-9._]/i", $module))
		{
			return array();
		}

		if(!isset($descriptions[$module]))
		{
			if(($path = getLocalPath("modules/".$module."/admin/operation_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"].$path);
			}
			else
			{
				$descriptions[$module] = array();
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}
}

class COperation extends CAllOperation
{
}
