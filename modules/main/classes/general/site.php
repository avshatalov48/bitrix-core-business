<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Localization\CultureTable;
use Bitrix\Main\SiteTable;

IncludeModuleLangFile(__FILE__);

class CAllSite
{
	public static $MAIN_LANGS_CACHE = [];
	public static $MAIN_LANGS_ADMIN_CACHE = [];

	public $LAST_ERROR;

	public static function InDir($strDir)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		return (mb_substr($APPLICATION->GetCurPage(true), 0, mb_strlen($strDir)) == $strDir);
	}

	public static function InPeriod($iUnixTimestampFrom, $iUnixTimestampTo)
	{
		if ($iUnixTimestampFrom > 0 && time() < $iUnixTimestampFrom)
		{
			return false;
		}
		if ($iUnixTimestampTo > 0 && time() > $iUnixTimestampTo)
		{
			return false;
		}

		return true;
	}

	public static function InGroup($arGroups)
	{
		global $USER;
		$arUserGroups = $USER->GetUserGroupArray();
		if (!empty(array_intersect($arUserGroups, $arGroups)))
		{
			return true;
		}
		return false;
	}

	/**
	 * @deprecated Use Context culture.
	 */
	public static function GetWeekStart()
	{
		return Main\Context::getCurrent()->getCulture()->getWeekStart();
	}

	public static function GetDateFormat($type = "FULL", $lang = false, $bSearchInSitesOnly = false)
	{
		$fullFormat = (strtoupper($type) == 'FULL');

		if ($lang === false && defined("LANG"))
		{
			$lang = LANG;
		}

		$format = '';
		if ($lang !== false)
		{
			if (defined("SITE_ID") && $lang == SITE_ID)
			{
				if ($fullFormat && defined("FORMAT_DATETIME"))
				{
					return FORMAT_DATETIME;
				}
				if (!$fullFormat && defined("FORMAT_DATE"))
				{
					return FORMAT_DATE;
				}
			}

			$formatKey = ($fullFormat ? 'FORMAT_DATETIME' : 'FORMAT_DATE');

			if (!$bSearchInSitesOnly && defined("ADMIN_SECTION") && ADMIN_SECTION === true)
			{
				if (!isset(static::$MAIN_LANGS_ADMIN_CACHE[$lang]))
				{
					$res = CLanguage::GetByID($lang);
					if ($res = $res->Fetch())
					{
						static::$MAIN_LANGS_ADMIN_CACHE[$res["LID"]] = $res;
					}
				}

				if (isset(static::$MAIN_LANGS_ADMIN_CACHE[$lang]))
				{
					$format = mb_strtoupper(static::$MAIN_LANGS_ADMIN_CACHE[$lang][$formatKey]);
				}
			}

			// if LANG is not found in LangAdmin:
			if ($format == '')
			{
				if (!isset(static::$MAIN_LANGS_CACHE[$lang]))
				{
					$res = CLang::GetByID($lang);
					if ($res = $res->Fetch())
					{
						static::$MAIN_LANGS_CACHE[$res["LID"]] = $res;

						if (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
						{
							static::$MAIN_LANGS_ADMIN_CACHE[$res["LID"]] = $res;
						}
					}
				}

				if (isset(static::$MAIN_LANGS_ADMIN_CACHE[$lang]))
				{
					$format = mb_strtoupper(static::$MAIN_LANGS_CACHE[$lang][$formatKey]);
				}
			}
		}

		if ($format == '')
		{
			$format = ($fullFormat ? "DD.MM.YYYY HH:MI:SS" : "DD.MM.YYYY");
		}

		return $format;
	}

	public static function GetTimeFormat($lang = false, $bSearchInSitesOnly = false)
	{
		$dateTimeFormat = self::GetDateFormat('FULL', $lang, $bSearchInSitesOnly);
		preg_match('~[HG]~', $dateTimeFormat, $chars, PREG_OFFSET_CAPTURE);
		return trim(mb_substr($dateTimeFormat, $chars[0][1]));
	}

	public function CheckFields($arFields, $ID = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		$this->LAST_ERROR = "";
		$arMsg = [];

		if (isset($arFields["NAME"]) && mb_strlen($arFields["NAME"]) < 2)
		{
			$this->LAST_ERROR .= GetMessage("BAD_SITE_NAME") . " ";
			$arMsg[] = ["id" => "NAME", "text" => GetMessage("BAD_SITE_NAME")];
		}
		if (($ID === false || isset($arFields["LID"])) && mb_strlen($arFields["LID"]) <> 2)
		{
			$this->LAST_ERROR .= GetMessage("BAD_SITE_LID") . " ";
			$arMsg[] = ["id" => "LID", "text" => GetMessage("BAD_SITE_LID")];
		}
		if (isset($arFields["LID"]) && preg_match("/[^a-z0-9_]/i", $arFields["LID"]))
		{
			$this->LAST_ERROR .= GetMessage("MAIN_SITE_LATIN") . " ";
			$arMsg[] = ["id" => "LID", "text" => GetMessage("MAIN_SITE_LATIN")];
		}
		if (isset($arFields["DIR"]) && $arFields["DIR"] == '')
		{
			$this->LAST_ERROR .= GetMessage("BAD_LANG_DIR") . " ";
			$arMsg[] = ["id" => "DIR", "text" => GetMessage("BAD_LANG_DIR")];
		}
		if ($ID === false && !isset($arFields["LANGUAGE_ID"]))
		{
			$this->LAST_ERROR .= GetMessage("MAIN_BAD_LANGUAGE_ID") . " ";
			$arMsg[] = ["id" => "LANGUAGE_ID", "text" => GetMessage("MAIN_BAD_LANGUAGE_ID")];
		}
		if (isset($arFields["LANGUAGE_ID"]))
		{
			$dbl_check = CLanguage::GetByID($arFields["LANGUAGE_ID"]);
			if (!$dbl_check->Fetch())
			{
				$this->LAST_ERROR .= GetMessage("MAIN_BAD_LANGUAGE_ID_BAD") . " ";
				$arMsg[] = ["id" => "LANGUAGE_ID", "text" => GetMessage("MAIN_BAD_LANGUAGE_ID_BAD")];
			}
		}
		if ($ID === false && !isset($arFields["CULTURE_ID"]))
		{
			$this->LAST_ERROR .= GetMessage("lang_check_culture_not_set") . " ";
			$arMsg[] = ["id" => "CULTURE_ID", "text" => GetMessage("lang_check_culture_not_set")];
		}
		if (isset($arFields["CULTURE_ID"]))
		{
			if (CultureTable::getRowById($arFields["CULTURE_ID"]) === null)
			{
				$this->LAST_ERROR .= GetMessage("lang_check_culture_incorrect") . " ";
				$arMsg[] = ["id" => "CULTURE_ID", "text" => GetMessage("lang_check_culture_incorrect")];
			}
		}
		if (isset($arFields["SORT"]) && $arFields["SORT"] == '')
		{
			$this->LAST_ERROR .= GetMessage("BAD_SORT") . " ";
			$arMsg[] = ["id" => "SORT", "text" => GetMessage("BAD_SORT")];
		}
		if (isset($arFields["TEMPLATE"]))
		{
			$isOK = false;
			$check_templ = [];
			$dupError = "";
			foreach ($arFields["TEMPLATE"] as $val)
			{
				if ($val["TEMPLATE"] <> '' && getLocalPath("templates/" . $val["TEMPLATE"], BX_PERSONAL_ROOT) !== false)
				{
					if (in_array($val["TEMPLATE"] . ", " . $val["CONDITION"], $check_templ))
					{
						$dupError = " " . GetMessage("MAIN_BAD_TEMPLATE_DUP");
						$isOK = false;
						break;
					}
					$check_templ[] = $val["TEMPLATE"] . ", " . $val["CONDITION"];
					$isOK = true;
				}
			}
			if (!$isOK)
			{
				$this->LAST_ERROR .= GetMessage("MAIN_BAD_TEMPLATE") . $dupError;
				$arMsg[] = ["id" => "SITE_TEMPLATE", "text" => GetMessage("MAIN_BAD_TEMPLATE") . $dupError];
			}
		}

		if ($ID === false)
		{
			$events = GetModuleEvents("main", "OnBeforeSiteAdd", true);
		}
		else
		{
			$events = GetModuleEvents("main", "OnBeforeSiteUpdate", true);
		}
		foreach ($events as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, [&$arFields]);
			if ($bEventRes === false)
			{
				if ($err = $APPLICATION->GetException())
				{
					$this->LAST_ERROR .= $err->GetString() . " ";
					$arMsg[] = ["id" => "EVENT_ERROR", "text" => $err->GetString()];
				}
				else
				{
					$this->LAST_ERROR .= "Unknown error. ";
					$arMsg[] = ["id" => "EVENT_ERROR", "text" => "Unknown error. "];
				}
				break;
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

		if ($ID === false)
		{
			$r = $DB->Query("SELECT 'x' FROM b_lang WHERE LID='" . $DB->ForSQL($arFields["LID"], 2) . "'");
			if ($r->Fetch())
			{
				$this->LAST_ERROR .= GetMessage("BAD_SITE_DUP") . " ";
				$e = new CAdminException([["id" => "LID", "text" => GetMessage("BAD_SITE_DUP")]]);
				$APPLICATION->ThrowException($e);
				return false;
			}
		}

		return true;
	}

	public static function SaveDomains($LID, $domains)
	{
		global $DB, $CACHE_MANAGER;

		if (CACHED_b_lang_domain !== false)
		{
			$CACHE_MANAGER->CleanDir("b_lang_domain");
		}

		$DB->Query("DELETE FROM b_lang_domain WHERE LID='" . $DB->ForSQL($LID) . "'");

		$domains = str_replace("\r", "\n", $domains);
		$arDomains = explode("\n", $domains);
		foreach ($arDomains as $i => $domain)
		{
			$domain = preg_replace("#^(http://|https://)#", "", rtrim(trim(mb_strtolower($domain)), "/"));

			$arErrors = [];
			if ($domainTmp = CBXPunycode::ToASCII($domain, $arErrors))
			{
				$domain = $domainTmp;
			}

			$arDomains[$i] = $domain;
		}
		$arDomains = array_unique($arDomains);

		$bIsDomain = false;
		foreach ($arDomains as $domain)
		{
			if ($domain <> '')
			{
				$DB->Query("INSERT INTO b_lang_domain(LID, DOMAIN) VALUES('" . $DB->ForSQL($LID, 2) . "', '" . $DB->ForSQL($domain, 255) . "')");
				$bIsDomain = true;
			}
		}
		$DB->Query("UPDATE b_lang SET DOMAIN_LIMITED='" . ($bIsDomain ? "Y" : "N") . "' WHERE LID='" . $DB->ForSql($LID) . "'");

		Main\SiteDomainTable::cleanCache();
	}

	public function Add($arFields)
	{
		global $DB, $DOCUMENT_ROOT, $CACHE_MANAGER;

		if (!$this->CheckFields($arFields))
		{
			return false;
		}

		if (isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] != "Y")
		{
			$arFields["ACTIVE"] = "N";
		}

		if (isset($arFields["DEF"]))
		{
			if ($arFields["DEF"] == "Y")
			{
				$DB->Query("UPDATE b_lang SET DEF='N' WHERE DEF='Y'");
			}
			else
			{
				$arFields["DEF"] = "N";
			}
		}

		$arInsert = $DB->PrepareInsert("b_lang", $arFields);

		$strSql =
			"INSERT INTO b_lang(" . $arInsert[0] . ") " .
			"VALUES(" . $arInsert[1] . ")";

		$DB->Query($strSql);

		if (CACHED_b_lang !== false)
		{
			$CACHE_MANAGER->CleanDir("b_lang");
		}

		if (isset($arFields["DIR"]))
		{
			CheckDirPath($DOCUMENT_ROOT . $arFields["DIR"]);
		}

		if (isset($arFields["DOMAINS"]))
		{
			self::SaveDomains($arFields["LID"], $arFields["DOMAINS"]);
		}

		if (isset($arFields["TEMPLATE"]))
		{
			foreach ($arFields["TEMPLATE"] as $arTemplate)
			{
				if (trim($arTemplate["TEMPLATE"]) <> '')
				{
					$arInsert = $DB->PrepareInsert("b_site_template", [
						'SITE_ID' => $arFields["LID"],
						'CONDITION' => trim($arTemplate["CONDITION"]),
						'SORT' => $arTemplate["SORT"],
						'TEMPLATE' => trim($arTemplate["TEMPLATE"]),
					]);
					$strSql = "INSERT INTO b_site_template(" . $arInsert[0] . ") VALUES (" . $arInsert[1] . ")";
					$DB->Query($strSql);
				}
			}

			if (CACHED_b_site_template !== false)
			{
				$CACHE_MANAGER->Clean("b_site_template");
			}
		}

		SiteTable::cleanCache();

		return $arFields["LID"];
	}

	public function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		unset(static::$MAIN_LANGS_CACHE[$ID]);
		unset(static::$MAIN_LANGS_ADMIN_CACHE[$ID]);

		if (!$this->CheckFields($arFields, $ID))
		{
			return false;
		}

		if (isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] != "Y")
		{
			$arFields["ACTIVE"] = "N";
		}

		if (isset($arFields["DEF"]))
		{
			if ($arFields["DEF"] == "Y")
			{
				$DB->Query("UPDATE b_lang SET DEF='N' WHERE DEF='Y'");
			}
			else
			{
				$arFields["DEF"] = "N";
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_lang", $arFields);
		if ($strUpdate <> '')
		{
			$strSql = "UPDATE b_lang SET " . $strUpdate . " WHERE LID='" . $DB->ForSql($ID, 2) . "'";
			$DB->Query($strSql);
		}

		if (CACHED_b_lang !== false)
		{
			$CACHE_MANAGER->CleanDir("b_lang");
		}

		if (isset($arFields["DIR"]))
		{
			CheckDirPath($_SERVER["DOCUMENT_ROOT"] . $arFields["DIR"]);
		}

		if (isset($arFields["DOMAINS"]))
		{
			self::SaveDomains($ID, $arFields["DOMAINS"]);
		}

		if (isset($arFields["TEMPLATE"]))
		{
			$DB->Query("DELETE FROM b_site_template WHERE SITE_ID='" . $DB->ForSQL($ID) . "'");

			foreach ($arFields["TEMPLATE"] as $arTemplate)
			{
				if (trim($arTemplate["TEMPLATE"]) <> '')
				{
					$arInsert = $DB->PrepareInsert("b_site_template", [
						'SITE_ID' => $ID,
						'CONDITION' => trim($arTemplate["CONDITION"]),
						'SORT' => $arTemplate["SORT"],
						'TEMPLATE' => trim($arTemplate["TEMPLATE"]),
					]);
					$strSql = "INSERT INTO b_site_template(" . $arInsert[0] . ") VALUES (" . $arInsert[1] . ")";
					$DB->Query($strSql);
				}
			}

			if (CACHED_b_site_template !== false)
			{
				$CACHE_MANAGER->Clean("b_site_template");
			}
		}

		SiteTable::cleanCache();

		return true;
	}

	public static function Delete($ID)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION, $CACHE_MANAGER;

		$APPLICATION->ResetException();

		foreach (GetModuleEvents("main", "OnBeforeLangDelete", true) as $arEvent)
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

		foreach (GetModuleEvents("main", "OnBeforeSiteDelete", true) as $arEvent)
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

		foreach (GetModuleEvents("main", "OnLangDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID]);
		}

		foreach (GetModuleEvents("main", "OnSiteDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID]);
		}

		if (!$DB->Query("DELETE FROM b_event_message_site WHERE SITE_ID='" . $DB->ForSQL($ID, 2) . "'"))
		{
			return false;
		}

		if (!$DB->Query("DELETE FROM b_lang_domain WHERE LID='" . $DB->ForSQL($ID, 2) . "'"))
		{
			return false;
		}

		if (CACHED_b_lang_domain !== false)
		{
			$CACHE_MANAGER->CleanDir("b_lang_domain");
		}

		if (!$DB->Query("UPDATE b_event_message SET LID=NULL WHERE LID='" . $DB->ForSQL($ID, 2) . "'"))
		{
			return false;
		}

		if (!$DB->Query("DELETE FROM b_site_template WHERE SITE_ID='" . $DB->ForSQL($ID, 2) . "'"))
		{
			return false;
		}

		if (CACHED_b_site_template !== false)
		{
			$CACHE_MANAGER->Clean("b_site_template");
		}

		$result = $DB->Query("DELETE FROM b_lang WHERE LID='" . $DB->ForSQL($ID, 2) . "'", true);

		if (CACHED_b_lang !== false)
		{
			$CACHE_MANAGER->CleanDir("b_lang");
		}

		SiteTable::cleanCache();
		Main\SiteDomainTable::cleanCache();

		return $result;
	}

	public static function GetTemplateList($site_id)
	{
		global $DB;
		$strSql =
			"SELECT * " .
			"FROM b_site_template " .
			"WHERE SITE_ID='" . $DB->ForSQL($site_id, 2) . "' " .
			"ORDER BY SORT";

		$dbr = $DB->Query($strSql);
		return $dbr;
	}

	public static function GetDefList()
	{
		return static::GetList('def_list', 'asc', ['ACTIVE' => 'Y']);
	}

	/**
	 * @deprecated Use SiteTable::getDocumentRoot()
	 */
	public static function GetSiteDocRoot($site)
	{
		return SiteTable::getDocumentRoot($site === false ? null : $site);
	}

	public static function GetSiteByFullPath($path, $bOneResult = true)
	{
		$res = [];

		if (($p = realpath($path)))
		{
			$path = $p;
		}
		$path = str_replace("\\", "/", $path);
		$path = mb_strtolower($path) . "/";

		$db_res = CSite::GetList("lendir", "desc");
		while ($ar_res = $db_res->Fetch())
		{
			$abspath = $ar_res["ABS_DOC_ROOT"] . $ar_res["DIR"];
			if (($p = realpath($abspath)))
			{
				$abspath = $p;
			}
			$abspath = str_replace("\\", "/", $abspath);
			$abspath = mb_strtolower($abspath);
			if (mb_substr($abspath, -1) <> "/")
			{
				$abspath .= "/";
			}
			if (mb_strpos($path, $abspath) === 0)
			{
				if ($bOneResult)
				{
					return $ar_res["ID"];
				}
				$res[] = $ar_res["ID"];
			}
		}

		if (!empty($res))
		{
			return $res;
		}

		return false;
	}

	public static function GetList($by = "sort", $order = "asc", $arFilter = [])
	{
		global $DB, $CACHE_MANAGER;

		if (CACHED_b_lang !== false)
		{
			$cacheId = "b_lang" . md5($by . "." . $order . "." . serialize($arFilter));
			if ($CACHE_MANAGER->Read(CACHED_b_lang, $cacheId, "b_lang"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);

				$res = new CDBResult;
				$res->InitFromArray($arResult);
				$res = new _CLangDBResult($res);
				return $res;
			}
		}

		$strSqlSearch = "";
		$bIncDomain = false;
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '')
				{
					continue;
				}
				$val = $DB->ForSql($val);
				switch (strtoupper($key))
				{
					case "ACTIVE":
						if ($val == "Y" || $val == "N")
						{
							$strSqlSearch .= " AND L.ACTIVE='" . $val . "'\n";
						}
						break;
					case "DEFAULT":
						if ($val == "Y" || $val == "N")
						{
							$strSqlSearch .= " AND L.DEF='" . $val . "'\n";
						}
						break;
					case "NAME":
						$strSqlSearch .= " AND UPPER(L.NAME) LIKE UPPER('" . $val . "')\n";
						break;
					case "DOMAIN":
						$bIncDomain = true;
						$strSqlSearch .= " AND UPPER(D.DOMAIN) LIKE UPPER('" . $val . "')\n";
						break;
					case "IN_DIR":
						$strSqlSearch .= " AND UPPER('" . $val . "') LIKE " . $DB->Concat("UPPER(L.DIR)", "'%'") . "\n";
						break;
					case "ID":
					case "LID":
						$strSqlSearch .= " AND L.LID='" . $val . "'\n";
						break;
					case "LANGUAGE_ID":
						$strSqlSearch .= " AND L.LANGUAGE_ID='" . $val . "'\n";
						break;
				}
			}
		}

		$strSql = "
			SELECT " . ($bIncDomain ? " DISTINCT " : "") . "
				L.*,
				L.LID ID,
				L.LID SITE_ID,
				" . $DB->Length("L.DIR") . ",
				" . $DB->IsNull($DB->Length("L.DOC_ROOT"), "0") . ",
				C.FORMAT_DATE, C.FORMAT_DATETIME, C.FORMAT_NAME, C.WEEK_START, C.CHARSET, C.DIRECTION
			FROM
				b_culture C,
				b_lang L " . ($bIncDomain ? "LEFT JOIN b_lang_domain D ON D.LID=L.LID " : "") . "
			WHERE
				C.ID=L.CULTURE_ID
				" . $strSqlSearch . "
			";

		$by = strtolower($by);
		$order = strtolower($order);

		if ($by == "lid" || $by == "id")
		{
			$strSqlOrder = " ORDER BY L.LID ";
		}
		elseif ($by == "active")
		{
			$strSqlOrder = " ORDER BY L.ACTIVE ";
		}
		elseif ($by == "name")
		{
			$strSqlOrder = " ORDER BY L.NAME ";
		}
		elseif ($by == "dir")
		{
			$strSqlOrder = " ORDER BY L.DIR ";
		}
		elseif ($by == "lendir")
		{
			$strSqlOrder = " ORDER BY " . $DB->IsNull($DB->Length("L.DOC_ROOT"), "0") . ($order == "desc" ? " desc" : "") . ", " . $DB->Length("L.DIR");
		}
		elseif ($by == "def")
		{
			$strSqlOrder = " ORDER BY L.DEF ";
		}
		elseif ($by == "def_list")
		{
			$strSqlOrder = " ORDER BY L.DEF desc, L.SORT ";
		}
		else
		{
			$strSqlOrder = " ORDER BY L.SORT ";
		}

		if ($order == "desc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql .= $strSqlOrder;
		if (CACHED_b_lang === false)
		{
			$res = $DB->Query($strSql);
		}
		else
		{
			$arResult = [];
			$res = $DB->Query($strSql);
			while ($ar = $res->Fetch())
			{
				$arResult[] = $ar;
			}

			$CACHE_MANAGER->Set($cacheId, $arResult);

			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}
		$res = new _CLangDBResult($res);
		return $res;
	}

	public static function GetByID($ID)
	{
		return CSite::GetList('', '', ["LID" => $ID]);
	}

	public static function GetArrayByID($ID)
	{
		$res = self::GetByID($ID);
		return $res->Fetch();
	}

	public static function GetDefSite($LID = false)
	{
		if ($LID <> '')
		{
			$dbSite = CSite::GetByID($LID);
			if ($dbSite->Fetch())
			{
				return $LID;
			}
		}

		$dbDefSites = CSite::GetDefList();
		if ($arDefSite = $dbDefSites->Fetch())
		{
			return $arDefSite["LID"];
		}

		return false;
	}

	public static function IsDistinctDocRoots($arFilter = [])
	{
		$s = false;
		$res = CSite::GetList('', '', $arFilter);
		while ($ar = $res->Fetch())
		{
			if ($s !== false && $s != $ar["ABS_DOC_ROOT"])
			{
				return true;
			}
			$s = $ar["ABS_DOC_ROOT"];
		}
		return false;
	}

	///////////////////////////////////////////////////////////////////
	// Returns drop down list with langs
	///////////////////////////////////////////////////////////////////
	public static function SelectBox($sFieldName, $sValue, $sDefaultValue = "", $sFuncName = "", $field = "class=\"typeselect\"")
	{
		$l = CLang::GetList();
		$s = '<select name="' . $sFieldName . '" ' . $field;
		$s1 = '';
		if ($sFuncName <> '')
		{
			$s .= ' OnChange="' . $sFuncName . '"';
		}
		$s .= '>' . "\n";
		$found = false;
		while (($l_arr = $l->Fetch()))
		{
			$found = ($l_arr["LID"] == $sValue);
			$s1 .= '<option value="' . $l_arr["LID"] . '"' . ($found ? ' selected' : '') . '>[' . htmlspecialcharsex($l_arr["LID"]) . ']&nbsp;' . htmlspecialcharsex($l_arr["NAME"]) . '</option>' . "\n";
		}
		if ($sDefaultValue <> '')
		{
			$s .= "<option value='NOT_REF' " . ($found ? "" : "selected") . ">" . htmlspecialcharsex($sDefaultValue) . "</option>";
		}
		return $s . $s1 . '</select>';
	}

	public static function SelectBoxMulti($sFieldName, $Value)
	{
		$l = CLang::GetList();
		if (is_array($Value))
		{
			$arValue = $Value;
		}
		else
		{
			$arValue = [$Value];
		}

		$s = '<div class="adm-list">';
		while ($l_arr = $l->Fetch())
		{
			$s .=
				'<div class="adm-list-item">' .
				'<div class="adm-list-control"><input type="checkbox" name="' . $sFieldName . '[]" value="' . htmlspecialcharsex($l_arr["LID"]) . '" id="' . htmlspecialcharsex($l_arr["LID"]) . '" class="typecheckbox"' . (in_array($l_arr["LID"], $arValue) ? ' checked' : '') . '></div>' .
				'<div class="adm-list-label"><label for="' . htmlspecialcharsex($l_arr["LID"]) . '">[' . htmlspecialcharsex($l_arr["LID"]) . ']&nbsp;' . htmlspecialcharsex($l_arr["NAME"]) . '</label></div>' .
				'</div>';
		}

		$s .= '</div>';

		return $s;
	}

	public static function GetNameTemplates()
	{
		return [
			'#NAME# #LAST_NAME#' => GetMessage('MAIN_NAME_JOHN_SMITH'),
			'#LAST_NAME# #NAME#' => GetMessage('MAIN_NAME_SMITH_JOHN'),
			'#TITLE# #LAST_NAME#' => GetMessage("MAIN_NAME_MR_SMITH"),
			'#NAME# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_NAME_JOHN_L_SMITH'),
			'#LAST_NAME# #NAME# #SECOND_NAME#' => GetMessage('MAIN_NAME_SMITH_JOHN_LLOYD'),
			'#LAST_NAME#, #NAME# #SECOND_NAME#' => GetMessage('MAIN_NAME_SMITH_COMMA_JOHN_LLOYD'),
			'#NAME# #SECOND_NAME# #LAST_NAME#' => GetMessage('MAIN_NAME_JOHN_LLOYD_SMITH'),
			'#NAME_SHORT# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_NAME_J_L_SMITH'),
			'#NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_NAME_J_SMITH'),
			'#LAST_NAME# #NAME_SHORT#' => GetMessage('MAIN_NAME_SMITH_J'),
			'#LAST_NAME# #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('MAIN_NAME_SMITH_J_L'),
			'#LAST_NAME#, #NAME_SHORT#' => GetMessage('MAIN_NAME_SMITH_COMMA_J'),
			'#LAST_NAME#, #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('MAIN_NAME_SMITH_COMMA_J_L'),
		];
	}

	/**
	 * Returns current name template
	 *
	 * If site is not defined - will look for name template for current language.
	 * If there is no value for language - returns pre-defined value @param null $dummy Unused
	 * @param string $site_id - use to get value for the specific site
	 * @return string ex: #LAST_NAME# #NAME#
	 * @see CSite::GetDefaultNameFormat
	 * FORMAT_NAME constant can be set in dbconn.php
	 *
	 */
	public static function GetNameFormat($dummy = null, $site_id = "")
	{
		static $siteFormat = [];
		$format = '';

		$context = Main\Context::getCurrent();

		if ($site_id == "" || $context->getSite() == $site_id)
		{
			// from current site or language
			$format = $context->getCulture()->getFormatName();
		}

		//site value
		if ($format == "")
		{
			if (!isset($siteFormat[$site_id]))
			{
				$db_res = CSite::GetByID($site_id);
				if ($res = $db_res->Fetch())
				{
					$format = $res["FORMAT_NAME"];
					$siteFormat[$site_id] = $format;
				}
			}
			else
			{
				$format = $siteFormat[$site_id];
			}
		}

		//if not found - trying to get value for the language
		if ($format == "")
		{
			$format = $context->getCulture()->getFormatName();
		}

		//if not found - trying to get default values
		if ($format == "")
		{
			$format = self::GetDefaultNameFormat();
		}

		$format = str_replace(["#NOBR#", "#/NOBR#"], "", $format);

		return $format;
	}

	/**
	 * Returns default name template
	 * By default: Russian #LAST_NAME# #NAME#, English #NAME# #LAST_NAME#
	 *
	 * @return string - one of two possible default values
	 */
	public static function GetDefaultNameFormat()
	{
		return '#NAME# #LAST_NAME#';
	}

	public static function GetCurTemplate()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $APPLICATION, $USER, $CACHE_MANAGER;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$conditionQuoted = $helper->quote("CONDITION");

		$siteTemplate = "";
		if (CACHED_b_site_template === false)
		{
			$strSql = "
				SELECT
					" . $conditionQuoted . ",
					TEMPLATE
				FROM
					b_site_template
				WHERE
					SITE_ID='" . SITE_ID . "'
				ORDER BY
					CASE
						WHEN " . $helper->getIsNullFunction($helper->getLengthFunction($conditionQuoted), 0) . "=0 THEN 2
						ELSE 1
					END,
					SORT
				";
			$dbr = $connection->query($strSql);
			while ($ar = $dbr->fetch())
			{
				$strCondition = trim($ar["CONDITION"]);
				if ($strCondition <> '' && (!@eval("return " . $strCondition . ";")))
				{
					continue;
				}
				if (($path = getLocalPath("templates/" . $ar["TEMPLATE"], BX_PERSONAL_ROOT)) !== false && is_dir($_SERVER["DOCUMENT_ROOT"] . $path))
				{
					$siteTemplate = $ar["TEMPLATE"];
					break;
				}
			}
		}
		else
		{
			if ($CACHE_MANAGER->Read(CACHED_b_site_template, "b_site_template"))
			{
				$arSiteTemplateBySite = $CACHE_MANAGER->Get("b_site_template");
			}
			else
			{
				$dbr = $connection->query("
					SELECT
						" . $conditionQuoted . ",
						TEMPLATE,
						SITE_ID
					FROM
						b_site_template
					ORDER BY
						SITE_ID,
						CASE
							WHEN " . $helper->getIsNullFunction($helper->getLengthFunction($conditionQuoted), 0) . "=0 THEN 2
							ELSE 1
						END,
						SORT
				");
				$arSiteTemplateBySite = [];
				while ($ar = $dbr->fetch())
				{
					$arSiteTemplateBySite[$ar['SITE_ID']][] = $ar;
				}
				$CACHE_MANAGER->Set("b_site_template", $arSiteTemplateBySite);
			}

			if (isset($arSiteTemplateBySite[SITE_ID]) && is_array($arSiteTemplateBySite[SITE_ID]))
			{
				foreach ($arSiteTemplateBySite[SITE_ID] as $ar)
				{
					$strCondition = trim($ar["CONDITION"]);
					if ($strCondition <> '' && (!@eval("return " . $strCondition . ";")))
					{
						continue;
					}
					if (($path = getLocalPath("templates/" . $ar["TEMPLATE"], BX_PERSONAL_ROOT)) !== false && is_dir($_SERVER["DOCUMENT_ROOT"] . $path))
					{
						$siteTemplate = $ar["TEMPLATE"];
						break;
					}
				}
			}
		}

		if ($siteTemplate == "")
		{
			$siteTemplate = ".default";
		}

		$event = new Main\Event("main", "OnGetCurrentSiteTemplate", ["template" => $siteTemplate]);
		$event->send();

		foreach ($event->getResults() as $evenResult)
		{
			if (($result = $evenResult->getParameters()) <> '')
			{
				//only the first result matters
				$siteTemplate = $result;
				break;
			}
		}

		return $siteTemplate;
	}
}

class CAllLang extends CAllSite
{
}

class CSite extends CAllSite
{
}

class CLang extends CSite
{
}
