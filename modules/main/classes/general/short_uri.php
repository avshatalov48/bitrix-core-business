<?php

IncludeModuleLangFile(__FILE__);

class CBXShortUri
{
	private static $httpStatusCodes = array(
		301 => "301 Moved Permanently",
		302 => "302 Found",
		/*303 => "303 See Other",
		307 => "307 Temporary Redirect"*/
	);

	protected static $arErrors = array();

	public static function GetErrors()
	{
		return self::$arErrors;
	}

	protected static function AddError($error)
	{
		self::$arErrors[] = $error;
	}

	protected static function ClearErrors()
	{
		self::$arErrors = array();
	}

	public static function Update($id, $arFields)
	{
		global $DB;

		self::ClearErrors();

		$id = intval($id);
		if ($id <= 0)
		{
			self::AddError(GetMessage("MN_SU_NO_ID"));
			return false;
		}

		if (!self::ParseFields($arFields, $id))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_short_uri", $arFields);

		$strSql =
			"UPDATE b_short_uri SET ".
			"	".$strUpdate.", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = ".$id;
		$DB->Query($strSql);

		return $id;
	}

	public static function GetShortUri($uri)
	{
		$uriCrc32 = self::Crc32($uri);

		$dbResult = CBXShortUri::GetList(array(), array("URI_CRC" => $uriCrc32));
		while ($arResult = $dbResult->Fetch())
		{
			if ($arResult["URI"] == $uri)
				return "/".$arResult["SHORT_URI"];
		}

		$arFields = array(
			"URI" => $uri,
			"SHORT_URI" => self::GenerateShortUri(),
			"STATUS" => 301,
		);

		$id = CBXShortUri::Add($arFields);

		if ($id)
			return "/".$arFields["SHORT_URI"];

		return "";
	}

	public static function GetUri($shortUri)
	{
		$shortUri = trim($shortUri);

		$ar = @parse_url($shortUri);
		if (isset($ar["path"]))
			$shortUri = $ar["path"];

		$shortUri = trim($shortUri, "/");

		$uriCrc32 = self::Crc32($shortUri);

		$dbResult = CBXShortUri::GetList(array(), array("SHORT_URI_CRC" => $uriCrc32));
		while ($arResult = $dbResult->Fetch())
		{
			if ($arResult["SHORT_URI"] == $shortUri)
				return array("URI" => $arResult["URI"], "STATUS" => $arResult["STATUS"], "ID" => $arResult["ID"]);
		}

		return null;
	}

	public static function SetLastUsed($id)
	{
		global $DB;

		$strSql =
			"UPDATE b_short_uri SET ".
			"	NUMBER_USED = NUMBER_USED + 1, ".
			"	LAST_USED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = ".intval($id);
		$DB->Query($strSql);
	}

	public static function Delete($id)
	{
		global $DB, $APPLICATION;

		self::ClearErrors();

		$id = intval($id);
		if ($id <= 0)
		{
			self::AddError(GetMessage("MN_SU_NO_ID"));
			return false;
		}

		foreach(GetModuleEvents("main", "OnBeforeShortUriDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($id)) === false)
			{
				if(($ex = $APPLICATION->GetException()))
					$err = $ex->GetString();
				else
					$err = GetMessage("MN_SU_DELETE_ERROR");
				self::AddError($err);
				return false;
			}
		}

		$fl = $DB->Query("DELETE FROM b_short_uri WHERE ID = ".$id, true);

		if (!$fl)
		{
			self::AddError(GetMessage("MN_SU_DELETE_ERROR"));
			return false;
		}

		return true;
	}

	public static function Crc32($str)
	{
		$c = crc32($str);
		if ($c > 0x7FFFFFFF)
			$c = -(0xFFFFFFFF - $c + 1);
		return $c;
	}

	protected static function ParseFields(&$arFields, $id = 0)
	{
		$id = intval($id);
		$updateMode = ($id > 0 ? true : false);
		$addMode = !$updateMode;

		if (is_set($arFields, "URI") || $addMode)
		{
			$arFields["URI"] = trim($arFields["URI"]);
			if ($arFields["URI"] == '')
			{
				self::AddError(GetMessage("MN_SU_NO_URI"));
				return false;
			}

			$arFields["URI_CRC"] = self::Crc32($arFields["URI"]);
		}

		if (is_set($arFields, "SHORT_URI") || $addMode)
		{
			$arFields["SHORT_URI"] = trim($arFields["SHORT_URI"]);
			if ($arFields["SHORT_URI"] == '')
			{
				self::AddError(GetMessage("MN_SU_NO_SHORT_URI"));
				return false;
			}

			$ar = @parse_url($arFields["SHORT_URI"]);
			if (isset($ar["path"]))
				$arFields["SHORT_URI"] = $ar["path"];

			//$arFields["SHORT_URI"] = @parse_url($arFields["SHORT_URI"], PHP_URL_PATH);
			$arFields["SHORT_URI"] = trim($arFields["SHORT_URI"], "/");
			if ($arFields["SHORT_URI"] == '')
			{
				self::AddError(GetMessage("MN_SU_WRONG_SHORT_URI"));
				return false;
			}

			$arFields["SHORT_URI_CRC"] = self::Crc32($arFields["SHORT_URI"]);
		}

		if (is_set($arFields, "STATUS") || $addMode)
		{
			$arFields["STATUS"] = intval($arFields["STATUS"]);
			if ($arFields["STATUS"] <= 0)
			{
				self::AddError(GetMessage("MN_SU_NO_STATUS"));
				return false;
			}
			elseif (!array_key_exists($arFields["STATUS"], self::$httpStatusCodes))
			{
				self::AddError(GetMessage("MN_SU_WRONG_STATUS"));
				return false;
			}
		}

		if (is_set($arFields, "NUMBER_USED") || $addMode)
		{
			$arFields["NUMBER_USED"] = intval($arFields["NUMBER_USED"] ?? 0);
			if ($arFields["NUMBER_USED"] <= 0)
				$arFields["NUMBER_USED"] = 0;
		}

		return true;
	}

	public static function GetHttpStatusCodeText($code)
	{
		$code = intval($code);

		if (array_key_exists($code, self::$httpStatusCodes))
			return self::$httpStatusCodes[$code];

		return "";
	}

	public static function SelectBox($fieldName, $value, $defaultValue = "", $field = "class=\"typeselect\"")
	{
		$s = '<select name="'.$fieldName.'" '.$field.'>'."\n";
		$s1 = "";
		$found = false;
		foreach (self::$httpStatusCodes as $code => $codeText)
		{
			$found = ($code == $value);
			$m = GetMessage("MN_SU_HTTP_STATUS_".$code);
			$s1 .= '<option value="'.$code.'"'.($found ? ' selected':'').'>'.(empty($m) ? htmlspecialcharsex($codeText) : htmlspecialcharsex($m)).'</option>'."\n";
		}
		if ($defaultValue <> '')
			$s .= "<option value='' ".($found ? "" : "selected").">".htmlspecialcharsex($defaultValue)."</option>";
		return $s.$s1.'</select>';
	}

	public static function GenerateShortUri()
	{
		do
		{
			$uri = "~".randString(5);
			$bNew = true;
			$uriCrc32 = self::Crc32($uri);

			$dbResult = CBXShortUri::GetList(array(), array("SHORT_URI_CRC" => $uriCrc32));
			while ($arResult = $dbResult->Fetch())
			{
				if ($arResult["SHORT_URI"] == $uri)
				{
					$bNew = false;
					break;
				}
			}
		}
		while (!$bNew);

		return $uri;
	}

	public static function CheckUri()
	{
		if ($arUri = static::GetUri(Bitrix\Main\Context::getCurrent()->getRequest()->getDecodedUri()))
		{
			static::SetLastUsed($arUri["ID"]);
			if (CModule::IncludeModule("statistic"))
			{
				CStatEvent::AddCurrent("short_uri_redirect", "", "", "", "", $arUri["URI"], "N", SITE_ID);
			}
			LocalRedirect($arUri["URI"], true, static::GetHttpStatusCodeText($arUri["STATUS"]));
			return true;
		}
		return false;
	}

	public static function Add($arFields)
	{
		global $DB;

		self::ClearErrors();

		if (!self::ParseFields($arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_short_uri", $arFields);

		$strSql =
			"INSERT INTO b_short_uri (".$arInsert[0].", MODIFIED) ".
			"VALUES(".$arInsert[1].", ".$DB->CurrentTimeFunction().")";
		$DB->Query($strSql);

		$taskId = intval($DB->LastID());

		$arFields["ID"] = $taskId;

		foreach (GetModuleEvents("main", "OnAfterShortUriAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($arFields));

		return $taskId;
	}

	public static function GetList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arNavStartParams = false)
	{
		global $DB;

		self::ClearErrors();

		$arWherePart = array();
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				$key = mb_strtoupper($key);
				switch($key)
				{
					case "ID":
						$arWherePart[] = "U.ID=".intval($val);
						break;
					case "URI":
						$q = GetFilterQuery("U.URI", $val);
						if (!empty($q) && ($q != "0"))
							$arWherePart[] = $q;
						break;
					case "URI_EXACT":
						$arWherePart[] = "U.URI='".$DB->ForSQL($val)."'";
						break;
					case "URI_CRC":
						$arWherePart[] = "U.URI_CRC=".intval($val);
						break;
					case "SHORT_URI":
						$arWherePart[] = "U.SHORT_URI='".$DB->ForSQL($val)."'";
						break;
					case "SHORT_URI_CRC":
						$arWherePart[] = "U.SHORT_URI_CRC=".intval($val);
						break;
					case "STATUS":
						$arWherePart[] = "U.STATUS=".intval($val);
						break;
					case "MODIFIED_1":
						$arWherePart[] = "U.MODIFIED >= FROM_UNIXTIME('".MkDateTime(FmtDate($val, "D.M.Y"), "d.m.Y")."')";
						break;
					case "MODIFIED_2":
						$arWherePart[] = "U.MODIFIED <= FROM_UNIXTIME('".MkDateTime(FmtDate($val, "D.M.Y")." 23:59:59", "d.m.Y")."')";
						break;
					case "LAST_USED_1":
						$arWherePart[] = "U.LAST_USED >= FROM_UNIXTIME('".MkDateTime(FmtDate($val, "D.M.Y"), "d.m.Y")."')";
						break;
					case "LAST_USED_2":
						$arWherePart[] = "U.LAST_USED <= FROM_UNIXTIME('".MkDateTime(FmtDate($val, "D.M.Y")." 23:59:59", "d.m.Y")."')";
						break;
					case "NUMBER_USED":
						$arWherePart[] = "U.NUMBER_USED=".intval($val);
						break;
				}
			}
		}

		$strWherePart = "";
		if (!empty($arWherePart))
		{
			foreach ($arWherePart as $val)
			{
				if ($strWherePart !== "")
					$strWherePart .= " AND ";
				$strWherePart .= "(".$val.")";
			}
		}
		if ($strWherePart !== "")
			$strWherePart = "WHERE ".$strWherePart;

		$arOrderByPart = array();
		if (is_array($arOrder))
		{
			foreach ($arOrder as $key => $val)
			{
				$key = mb_strtoupper($key);
				if (!in_array($key, array("ID", "URI", "URI_CRC", "SHORT_URI", "SHORT_URI_CRC", "STATUS", "MODIFIED", "LAST_USED", "NUMBER_USED")))
					continue;
				$val = mb_strtoupper($val);
				if (!in_array($val, array("ASC", "DESC")))
					$val = "ASC";
				if ($key == "MODIFIED")
					$key = "MODIFIED1";
				if ($key == "LAST_USED")
					$key = "LAST_USED1";
				$arOrderByPart[] = $key." ".$val;
			}
		}

		$strOrderByPart = "";
		if (!empty($arOrderByPart))
		{
			foreach ($arOrderByPart as $val)
			{
				if ($strOrderByPart !== "")
					$strOrderByPart .= ", ";
				$strOrderByPart .= $val;
			}
		}
		if ($strOrderByPart !== "")
			$strOrderByPart = "ORDER BY ".$strOrderByPart;

		$strSql = "FROM b_short_uri U ".$strWherePart;

		if ($arNavStartParams)
		{
			$dbResultCount = $DB->Query("SELECT COUNT(U.ID) as C ".$strSql);
			$arResultCount = $dbResultCount->Fetch();
			$strSql = "SELECT ID, URI, URI_CRC, SHORT_URI, SHORT_URI_CRC, STATUS, ".$DB->DateToCharFunction("MODIFIED")." MODIFIED, MODIFIED MODIFIED1, ".$DB->DateToCharFunction("LAST_USED")." LAST_USED, LAST_USED LAST_USED1, NUMBER_USED ".$strSql.$strOrderByPart;
			$dbResult = new CDBResult();
			$dbResult->NavQuery($strSql, $arResultCount["C"], $arNavStartParams);
		}
		else
		{
			$strSql = "SELECT ID, URI, URI_CRC, SHORT_URI, SHORT_URI_CRC, STATUS, ".$DB->DateToCharFunction("MODIFIED")." MODIFIED, MODIFIED MODIFIED1, ".$DB->DateToCharFunction("LAST_USED")." LAST_USED, LAST_USED LAST_USED1, NUMBER_USED ".$strSql.$strOrderByPart;
			$dbResult = $DB->Query($strSql);
		}

		return $dbResult;
	}
}

/*
 * create table b_short_uri
 * (
 *      ID int(18) not null auto_increment,
 *      URI varchar(250) not null,
 *      URI_CRC int(18) not null,
 *      SHORT_URI varbinary(250) not null,
 *      SHORT_URI_CRC int(18) not null,
 *      STATUS int(18) not null default 301,
 *      MODIFIED timestamp not null,
 *      LAST_USED timestamp null,
 *      NUMBER_USED int(18) not null default 0,
 *      primary key (ID),
 *      index ux_b_short_uri_1 (SHORT_URI_CRC),
 *      index ux_b_short_uri_2 (URI_CRC)
 * )
 * */
