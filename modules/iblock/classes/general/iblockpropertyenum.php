<?php

class CIBlockPropertyEnumResult extends CDBResult
{
	function Fetch()
	{
		$a = parent::Fetch();
		if($a && defined("BX_COMP_MANAGED_CACHE"))
		{
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_property_enum_".$a["PROPERTY_ID"]);
		}
		return $a;
	}
}

class CIBlockPropertyEnum
{
	public static function GetList($arOrder = array("SORT"=>"ASC", "VALUE"=>"ASC"), $arFilter = array())
	{
		global $DB;

		$arSqlSearch = array();
		foreach ($arFilter as $key => $val)
		{
			if ($key[0] == "!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
			{
				$bInvert = false;
			}

			$key = mb_strtoupper($key);
			switch ($key)
			{
			case "CODE":
				$arSqlSearch[] = CIBlock::FilterCreate("P.CODE", $val, "string", $bInvert);
				break;
			case "IBLOCK_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("P.IBLOCK_ID", $val, "number", $bInvert);
				break;
			case "DEF":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.DEF", $val, "string_equal", $bInvert);
				break;
			case "EXTERNAL_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.XML_ID", $val, "string_equal", $bInvert);
				break;
			case "VALUE":
			case "XML_ID":
			case "TMP_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.".$key, $val, "string", $bInvert);
				break;
			case "PROPERTY_ID":
				if(is_numeric(mb_substr($val, 0, 1)))
					$arSqlSearch[] = CIBlock::FilterCreate("P.ID", $val, "number", $bInvert);
				else
					$arSqlSearch[] = CIBlock::FilterCreate("P.CODE", $val, "string", $bInvert);
				break;
			case "PROPERTY_ACTIVE":
				$arSqlSearch[] = CIBlock::FilterCreate("P.ACTIVE", $val, "string_equal", $bInvert);
				break;
			case "ID":
			case "SORT":
				$arSqlSearch[] = CIBlock::FilterCreate("BEN.".$key, $val, "number", $bInvert);
				break;
			}
		}

		$strSqlSearch = "";
		foreach(array_filter($arSqlSearch) as $sqlCondition)
			$strSqlSearch .= " AND  (".$sqlCondition.") ";

		$arSqlOrder = array();
		foreach ($arOrder as $by => $order)
		{
			$order = mb_strtolower($order) != "asc"? "desc": "asc";
			$by = mb_strtoupper($by);
			switch ($by)
			{
			case "ID":
			case "PROPERTY_ID":
			case "VALUE":
			case "XML_ID":
			case "EXTERNAL_ID":
			case "DEF":
				$arSqlOrder[$by] = "BEN.".$by." ".$order;
				break;
			case "PROPERTY_SORT":
				$arSqlOrder[$by] = "P.SORT ".$order;
				break;
			case "PROPERTY_CODE":
				$arSqlOrder[$by] = "P.CODE ".$order;
				break;
			default:
				$arSqlOrder["SORT"] = " BEN.SORT ".$order;
				break;
			}
		}

		if (!empty($arSqlOrder))
			$strSqlOrder = "ORDER BY ".implode(", ", $arSqlOrder);
		else
			$strSqlOrder = "";

		$strSql = "
			SELECT
				BEN.*,
				BEN.XML_ID as EXTERNAL_ID,
				P.NAME as PROPERTY_NAME,
				P.CODE as PROPERTY_CODE,
				P.SORT as PROPERTY_SORT
			FROM
				b_iblock_property_enum BEN,
				b_iblock_property P
			WHERE
				BEN.PROPERTY_ID=P.ID
			$strSqlSearch
			$strSqlOrder
		";

		$rs = $DB->Query($strSql);
		return new CIBlockPropertyEnumResult($rs);
	}

	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		if($arFields["VALUE"] == '')
			return false;

		if(CACHED_b_iblock_property_enum !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_iblock_property_enum");

		$allowedKeys = ["PROPERTY_ID", "VALUE", "DEF", "SORT", "XML_ID", "TMP_ID"];
		$arFields = array_intersect_key($arFields, array_flip($allowedKeys));

		if(is_set($arFields, "DEF") && $arFields["DEF"]!="Y")
			$arFields["DEF"]="N";

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if(!is_set($arFields, "XML_ID"))
			$arFields["XML_ID"] = md5(uniqid("", true));

		$ID = $DB->Add("b_iblock_property_enum", $arFields);

		if (defined("BX_COMP_MANAGED_CACHE"))
			$CACHE_MANAGER->ClearByTag("iblock_property_enum_".$arFields["PROPERTY_ID"]);

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intval($ID);

		if(is_set($arFields, "VALUE") && $arFields["VALUE"] == '')
			return false;

		if(CACHED_b_iblock_property_enum !== false)
			$CACHE_MANAGER->CleanDir("b_iblock_property_enum");

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if(is_set($arFields, "DEF") && $arFields["DEF"]!="Y")
			$arFields["DEF"]="N";

		$strUpdate = $DB->PrepareUpdate("b_iblock_property_enum", $arFields);
		if($strUpdate <> '')
			$DB->Query("UPDATE b_iblock_property_enum SET ".$strUpdate." WHERE ID=".$ID);

		if (defined("BX_COMP_MANAGED_CACHE") && intval($arFields["PROPERTY_ID"]) > 0)
			$CACHE_MANAGER->ClearByTag("iblock_property_enum_".$arFields["PROPERTY_ID"]);

		return true;
	}

	public static function DeleteByPropertyID($PROPERTY_ID, $bIgnoreError=false)
	{
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_iblock_property_enum !== false)
			$CACHE_MANAGER->CleanDir("b_iblock_property_enum");

		if (defined("BX_COMP_MANAGED_CACHE"))
			$CACHE_MANAGER->ClearByTag("iblock_property_enum_".$PROPERTY_ID);

		return $DB->Query("
			DELETE FROM b_iblock_property_enum
			WHERE PROPERTY_ID=".intval($PROPERTY_ID)."
			", $bIgnoreError
		);
	}

	public static function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_iblock_property_enum !== false)
			$CACHE_MANAGER->CleanDir("b_iblock_property_enum");

		$DB->Query("
			DELETE FROM b_iblock_property_enum
			WHERE ID=".intval($ID)."
			"
		);

		return true;
	}

	public static function GetByID($ID)
	{
		global $DB, $CACHE_MANAGER;
		static $BX_IBLOCK_ENUM_CACHE = [];
		static $bucket_size = null;

		if ($bucket_size === null)
		{
			$bucket_size = (int)CACHED_b_iblock_property_enum_bucket_size;
			if ($bucket_size <= 0)
			{
				$bucket_size = 10;
			}
		}

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}
		$bucket = (int)($ID/$bucket_size);

		if (
			!isset($BX_IBLOCK_ENUM_CACHE[$bucket])
			|| !isset($BX_IBLOCK_ENUM_CACHE[$bucket][$ID])
		)
		{
			$BX_IBLOCK_ENUM_CACHE[$bucket] ??= [];
			if (CACHED_b_iblock_property_enum === false)
			{
				$rs = $DB->Query("SELECT * from b_iblock_property_enum WHERE ID=".$ID);
				$BX_IBLOCK_ENUM_CACHE[$bucket][$ID] = $rs->Fetch();
				unset($rs);
			}
			elseif (empty($BX_IBLOCK_ENUM_CACHE[$bucket]))
			{
				$cache_id = 'b_iblock_property_enum' . $bucket;
				if ($CACHE_MANAGER->Read(CACHED_b_iblock_property_enum, $cache_id, "b_iblock_property_enum"))
				{
					$arEnums = $CACHE_MANAGER->Get($cache_id);
				}
				else
				{
					$arEnums = [];
					$rs = $DB->Query("
						SELECT *
						FROM b_iblock_property_enum
						WHERE ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)
					);
					while ($ar = $rs->Fetch())
					{
						$arEnums[$ar["ID"]] = $ar;
					}
					unset($rs);
					$CACHE_MANAGER->Set($cache_id, $arEnums);
				}
				$BX_IBLOCK_ENUM_CACHE[$bucket] = $arEnums;
			}
		}

		return $BX_IBLOCK_ENUM_CACHE[$bucket][$ID] ?? false;
	}
}
