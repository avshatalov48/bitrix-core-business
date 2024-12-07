<?php

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

/**
 * Class CIBlockType
 *
 * Fields:
 * <ul>
 * <li> ID string(50) mandatory
 * <li> SECTIONS bool optional default 'Y'
 * <li> EDIT_FILE_BEFORE string(255) optional
 * <li> EDIT_FILE_AFTER string(255) optional
 * <li> IN_RSS bool optional default 'N'
 * <li> SORT int optional default 500
 * </ul>
 */
class CIBlockType
{
	/**
	 * @var string Contains an error message in case of error in last Update or Add functions.
	 */
	public string $LAST_ERROR = '';
	/**
	 * Returns list of iblock types.
	 * @example iblocktype.php
	 *
	 * @param array $arOrder Order of the list.<br>
	 * 	keys are case insensitive:
	 * 		<ul>
	 * 		<li>SORT - by SORT field.
	 * 		<li>ID - by ID field.
	 * 		<li>NAME - by language depended NAME field (must be used with LANGUAGE_ID in the filter).
	 * 		</ul>
	 * 	values are case insensitive:
	 * 		<ul>
	 * 		<li>DESC - in descending order.
	 * 		<li>ASC - in ascending order.
	 * 		</ul>
	 * @param array $arFilter Filter criteria.<br>
	 * 	keys are case insensitive:
	 * 		<ul>
	 * 		<li>ID - uses <i>like</i> operator and is <i>case insensitive</i>.
	 * 		<li>=ID - when contains string uses <i>strict equal</i> operator.
	 * 		<li>=ID - when contains array[]string uses <i>in</i> operator.
	 * 		<li>NAME - uses <i>like</i> operator and is <i>case insensitive</i>.
	 * 		<li>LANGUAGE_ID - uses <i>strict equal</i> operator and is <i>case sensitive</i>.
	 * 		</ul>
	 * 	values with zero string length are ignored.
	 * @return CDBResult
	 */
	public static function GetList($arOrder = array("SORT" => "ASC"), $arFilter = array())
	{
		/** @global CDatabase $DB */
		global $DB;
		/** @global CCacheManager $CACHE_MANAGER */
		global $CACHE_MANAGER;
		$bLang = false;
		$bNameSort = false;
		$strSqlSearch = "1=1\n";

		foreach ($arFilter as $key => $val)
		{
			if (!is_array($val) && $val == '')
				continue;

			switch(mb_strtoupper($key))
			{
				case "ID":
					$strSqlSearch .= "AND UPPER(T.ID) LIKE UPPER('".$DB->ForSql($val)."')\n";
					break;

				case "=ID":
					if(is_array($val))
					{
						if(!empty($val))
						{
							$sqlVal = array_map(array($DB, 'ForSQL'), $val);
							$strSqlSearch .= "AND T.ID in ('".implode("', '", $sqlVal)."')\n";
						}
					}
					else
					{
						$strSqlSearch .= "AND T.ID = '".$DB->ForSql($val)."'\n";
					}
					break;

				case "NAME":
					$strSqlSearch .= "AND UPPER(TL.NAME) LIKE UPPER('%".$DB->ForSql($val)."%')\n";
					$bLang = true;
					break;
				case "LANGUAGE_ID":
					$strSqlSearch .= "AND TL.LID = '".$DB->ForSql($val)."'\n";
					$bLang = true;
					break;
			}
		}

		$strSqlOrder = '';
		foreach ($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			if ($by == "ID")
				$by = "T.ID";
			elseif ($by == "NAME")
			{
				$by = "TL.NAME";
				$bLang = true;
				$bNameSort = true;
			}
			else
				$by = "T.SORT";

			$order = mb_strtolower($order);
			if ($order != "desc")
				$order = "asc";

			if ($strSqlOrder == '')
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ', ';

			$strSqlOrder .= $by." ".$order;
		}

		$strSql = "
			SELECT ".($bLang ? "DISTINCT" : "")." T.*".($bNameSort ? ",TL.NAME" : "")."
			FROM b_iblock_type T
			".($bLang ? " LEFT JOIN b_iblock_type_lang TL ON TL.IBLOCK_TYPE_ID = T.ID " : "")."
			WHERE ".$strSqlSearch.$strSqlOrder;

		if (CACHED_b_iblock_type === false)
		{
			$res = $DB->Query($strSql);
		}
		else
		{
			if ($CACHE_MANAGER->Read(CACHED_b_iblock_type, $cache_id = "b_iblock_type".md5($strSql), "b_iblock_type"))
			{
				$arResult = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arResult = array();
				$res = $DB->Query($strSql);
				while ($ar = $res->Fetch())
					$arResult[] = $ar;

				$CACHE_MANAGER->Set($cache_id, $arResult);
			}
			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}
		return $res;
	}

	/**
	 * Returns cached version of the iblock type information.
	 *
	 * @param string $ID
	 * @return bool|array
	 */
	protected static function _GetCache($ID)
	{
		/** @global CDatabase $DB */
		global $DB;
		/** @global CCacheManager $CACHE_MANAGER */
		global $CACHE_MANAGER;

		$ID = trim((string)$ID);
		if ($ID === '')
		{
			return false;
		}

		if ($CACHE_MANAGER->Read(CACHED_b_iblock_type, "b_iblock_type", Iblock\TypeTable::getTableName()))
		{
			$arIBlocks = $CACHE_MANAGER->Get("b_iblock_type");
		}
		else
		{
			$arIBlocks = [];
			$rs = $DB->Query("SELECT * FROM b_iblock_type");
			while ($ar = $rs->GetNext())
			{
				$ar["_lang"] = [];
				$arIBlocks[$ar['ID']] = $ar;
			}
			$rs = $DB->Query("SELECT * FROM b_iblock_type_lang");
			while ($ar = $rs->GetNext())
			{
				if (array_key_exists($ar['IBLOCK_TYPE_ID'], $arIBlocks))
				{
					$arIBlocks[$ar['IBLOCK_TYPE_ID']]["_lang"][$ar["LID"]] = $ar;
				}
			}
			$CACHE_MANAGER->Set("b_iblock_type", $arIBlocks);
		}

		return $arIBlocks[$ID] ?? false;
	}
	/**
	 * Returns iblock type information by ID.
	 * @see CIBlockType
	 * <code>
	 * if (CModule::IncludeModule('iblock'))
	 * &#123;
	 * 	$rsType = CIBlockType::GetByID('test');
	 * 	$arType = $rsType->GetNext();
	 * 	if ($arType)
	 * 	&#123;
	 * 		echo '&lt;pre&gt;', htmlspecialcharsEx(print_r($arType, true)), '&lt;/pre&gt;';
	 * 	&#125;
	 * &#125;
	 * </code>
	 * @param string $ID iblock type ID
	 * @return CDBResult
	 */
	public static function GetByID($ID)
	{
		if (CACHED_b_iblock_type === false)
		{
			return CIBlockType::GetList(
				[],
				[
					'=ID' => $ID,
				]
			);
		}
		else
		{
			$arResult = CIBlockType::_GetCache($ID);
			$res = new CDBResult;
			if ($arResult !== false && isset($arResult["ID"]))
			{
				unset($arResult["_lang"]);
				$res->InitFromArray([$arResult]);
			}
			else
			{
				$res->InitFromArray([]);
			}

			return $res;
		}
	}
	/**
	 * Returns iblock type information with additional language depended on messages.<br>
	 *
	 * Additional to {@link CIBlockType} language depended on fields:
	 * <ul>
	 * <li>NAME - Name of the type
	 * <li>SECTION_NAME - How sections are called
	 * <li>ELEMENT_NAME - How elements are called
	 * </ul>
	 *
	 * <code>
	 * if (CModule::IncludeModule('iblock'))
	 * &#123;
	 * 	$rsTypeLang = CIBlockType::GetByIDLang('test', 'en');
	 * 	$arTypeLang = $rsTypeLang->GetNext();
	 * 	if ($arTypeLang)
	 * 	&#123;
	 * 		echo '&lt;pre&gt;', htmlspecialcharsEx(print_r($arTypeLang, true)), '&lt;/pre&gt;';
	 * 	&#125;
	 * &#125;
	 * </code>
	 * @param string $ID iblock type ID
	 * @param string $LID language ID
	 * @param bool $bFindAny Forces strict search
	 * @return array|bool
	 */
	public static function GetByIDLang($ID, $LID, $bFindAny = true)
	{
		/** @global CDatabase $DB */
		global $DB;
		$LID = $DB->ForSQL($LID, 2);

		if (CACHED_b_iblock_type === false)
		{
			$strSql = "
				SELECT BTL.*, BT.*
				FROM b_iblock_type BT, b_iblock_type_lang BTL
				WHERE BTL.IBLOCK_TYPE_ID = '".$DB->ForSQL($ID)."'
				AND BTL.LID='".$LID."'
				AND BT.ID=BTL.IBLOCK_TYPE_ID
			";
			$res = $DB->Query($strSql);
			if ($r = $res->GetNext())
				return $r;
		}
		else
		{
			$arResult = CIBlockType::_GetCache($ID);
			if ($arResult !== false && array_key_exists($LID, $arResult["_lang"]))
			{
				$res = $arResult["_lang"][$LID];
				unset($arResult["_lang"]);
				return array_merge($res, $arResult);
			}
		}

		if (!$bFindAny)
			return false;

		$strSql = "
			SELECT BTL.*, BT.*
			FROM b_iblock_type BT, b_iblock_type_lang BTL, b_language L
			WHERE BTL.IBLOCK_TYPE_ID = '".$DB->ForSQL($ID)."'
			AND BTL.LID = L.LID
			AND BT.ID=BTL.IBLOCK_TYPE_ID
			ORDER BY L.DEF DESC, L.SORT
		";
		$res = $DB->Query($strSql);
		if ($r = $res->GetNext())
			return $r;

		return false;
	}
	/**
	 * Deletes iblock type including all iblocks.<br>
	 * When there is an error occured on iblock deletion
	 * it stops and returns false.
	 *
	 * @param string $ID iblock type ID.
	 * @return bool|CDBResult
	 */
	public static function Delete($ID)
	{
		/** @global CDatabase $DB */
		global $DB;

		Iblock\TypeTable::cleanCache();

		$iblocks = CIBlock::GetList(array(), array(
			"=TYPE" => $ID,
		));
		while ($iblock = $iblocks->Fetch())
		{
			if (!CIBlock::Delete($iblock["ID"]))
			{
				return false;
			}
		}

		if (!$DB->Query("DELETE FROM b_iblock_type_lang WHERE IBLOCK_TYPE_ID='".$DB->ForSql($ID)."'", true))
		{
			return false;
		}

		return $DB->Query("DELETE FROM b_iblock_type WHERE ID='".$DB->ForSql($ID)."'", true);
	}
	/**
	 * Helper internal function.<br>
	 * Checks correctness of the information. Called by Add and Update methods.
	 * List of errors returned by LAST_ERROR member variable.
	 *
	 * @param array $arFields
	 * @param bool $ID iblock type ID. false - if new one.
	 * @return bool
	 */
	public function CheckFields($arFields, $ID = false)
	{
		/** @global CDatabase $DB */
		global $DB;
		$this->LAST_ERROR = '';

		if ($ID === false)
		{
			if (!isset($arFields["ID"]) || $arFields["ID"] == '')
			{
				$this->LAST_ERROR .= Loc::getMessage("IBLOCK_TYPE_BAD_ID")."<br>";
			}
			elseif (preg_match("/[^A-Za-z0-9_]/", $arFields["ID"]))
			{
				$this->LAST_ERROR .= Loc::getMessage("IBLOCK_TYPE_ID_HAS_WRONG_CHARS")."<br>";
			}
			else
			{
				$chk = $DB->Query("SELECT 'x' FROM b_iblock_type WHERE ID='".$DB->ForSQL($arFields["ID"])."'");
				if ($chk->Fetch())
				{
					$this->LAST_ERROR .= Loc::getMessage("IBLOCK_TYPE_DUBL_ID")."<br>";
					return false;
				}
			}
			if (empty($arFields["LANG"]) || !is_array($arFields["LANG"]))
			{
				$this->LAST_ERROR .= Loc::getMessage("IBLOCK_TYPE_EMPTY_NAMES")."<br>";
				return false;
			}
		}

		if (!empty($arFields['LANG']) && is_array($arFields['LANG']))
		{
			foreach ($arFields["LANG"] as $lid => $arFieldsLang)
			{
				if ($arFieldsLang["NAME"] == '')
				{
					$this->LAST_ERROR .= Loc::getMessage("IBLOCK_TYPE_BAD_NAME")." ".$lid.".<br>";
				}
			}
		}

		return $this->LAST_ERROR === '';
	}
	/**
	 * Creates new iblock type in the database.
	 * For arFields see {@link CIBlockType} class description.<br>
	 * In addition it may contain key "LANG" with and array of language depended on parameters.<br>
	 * For example:
	 * <code>
	 * $arFields = array(
	 * 	"ID" =&gt; "test",
	 * 	"LANG" =&gt; array(
	 * 		"en" =&gt; array(
	 * 			"NAME" => "Test",
	 * 			"ELEMENT_NAME" =&gt; "Test element",
	 * 			"SECTION_NAME" =&gt; "Test section",
	 * 		),
	 * 	),
	 * );
	 * </code>
	 *
	 * @param array $arFields
	 * @return bool
	 */
	public function Add($arFields)
	{
		/** @global CDatabase $DB */
		global $DB;

		$arFields["SECTIONS"] = isset($arFields["SECTIONS"]) && $arFields["SECTIONS"] === "Y" ? "Y" : "N";
		$arFields["IN_RSS"] = isset($arFields["IN_RSS"]) && $arFields["IN_RSS"] === "Y" ? "Y" : "N";

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		$arInsert = $DB->PrepareInsert("b_iblock_type", $arFields);
		$DB->Query("INSERT INTO b_iblock_type(".$arInsert[0].") VALUES(".$arInsert[1].")");

		if (isset($arFields["LANG"]))
		{
			$this->SetLang($arFields["ID"], $arFields["LANG"]);
		}

		Iblock\TypeTable::cleanCache();

		return $arFields["ID"];
	}
	/**
	 * Updates iblock type in the database.
	 *
	 * $arFields is the same as for {@link CIBlockType::Add} method.
	 * @see CIBlockType::Add
	 *
	 * @param string $ID
	 * @param array $arFields
	 * @return bool
	 */
	public function Update($ID, $arFields)
	{
		/** @global CDatabase $DB */
		global $DB;

		$arFields["SECTIONS"] = $arFields["SECTIONS"] == "Y" ? "Y" : "N";
		$arFields["IN_RSS"] = $arFields["IN_RSS"] == "Y" ? "Y" : "N";

		if (!$this->CheckFields($arFields, $ID))
		{
			return false;
		}

		$str_update = $DB->PrepareUpdate("b_iblock_type", $arFields);
		$DB->Query("UPDATE b_iblock_type SET ".$str_update." WHERE ID='".$DB->ForSQL($ID)."'");

		if (isset($arFields["LANG"]))
		{
			$this->SetLang($ID, $arFields["LANG"]);
		}

		Iblock\TypeTable::cleanCache();

		return true;
	}
	/**
	 * Internal helper function which helps to store language depended on fields into database.
	 *
	 * @param string $ID iblock type ID
	 * @param array $arLang language depended fields
	 */
	protected static function SetLang($ID, $arLang)
	{
		/** @global CDatabase $DB */
		global $DB;

		if (is_array($arLang))
		{
			$DB->Query("DELETE FROM b_iblock_type_lang WHERE IBLOCK_TYPE_ID='".$DB->ForSQL($ID)."'");
			foreach ($arLang as $lid => $arFieldsLang)
			{
				$name = (string)($arFieldsLang['NAME'] ?? '');
				$elementName = (string)($arFieldsLang['ELEMENT_NAME'] ?? '');
				$sectionName = (string)($arFieldsLang['SECTION_NAME'] ?? '');
				if ($name !== '' || $elementName !== '')
				{
					$DB->Query("
						INSERT INTO b_iblock_type_lang(IBLOCK_TYPE_ID, LID, NAME, SECTION_NAME, ELEMENT_NAME)
						SELECT
							BT.ID,
							L.LID,
							'".$DB->ForSql($name, 100)."',
							'".$DB->ForSql($sectionName, 100)."',
							'".$DB->ForSql($elementName, 100)."'
						FROM
							b_iblock_type BT,
							b_language L
						WHERE
							BT.ID = '".$DB->ForSQL($ID)."'
							AND L.LID = '".$DB->ForSQL($lid)."'
					");
				}
			}
		}
	}

	/**
	 * Returns last errors.
	 *
	 * @return string
	 */
	public function getLastError(): string
	{
		return $this->LAST_ERROR;
	}
}
