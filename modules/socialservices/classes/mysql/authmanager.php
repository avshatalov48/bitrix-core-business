<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/authmanager.php");

/**
 * Class CSocServAuthDB
 * @deprecated Use \Bitrix\Socialservices\UserTable
 */
class CSocServAuthDB extends CSocServAuth
{
	public static function Add($arFields)
	{
		global $DB;
		if(!self::CheckFields('ADD', $arFields))
			return false;

		$arDbFields = $arFields;
		if (static::hasEncryptedFields(array_keys($arDbFields)))
			static::encryptFields($arDbFields);

		$arInsert = $DB->PrepareInsert("b_socialservices_user", $arDbFields);
		$strSql =
			"INSERT INTO b_socialservices_user (".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";

		$res = $DB->Query($strSql, true, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!$res)
		{
			$_SESSION["LAST_ERROR"] = GetMessage("SC_ADD_ERROR");
			return false;
		}
		$lastId = intval($DB->LastID());
		$cache_id = 'socserv_ar_user';
		$obCache = new CPHPCache;
		$cache_dir = '/bx/socserv_ar_user';
		$obCache->Clean($cache_id, $cache_dir);

		$arFields['ID'] = $lastId;
		foreach(GetModuleEvents("socialservices", "OnAfterSocServUserAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $lastId;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;
		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "LOGIN", "NAME", "LAST_NAME", "EMAIL", "PERSONAL_PHOTO",
				"EXTERNAL_AUTH_ID", "USER_ID", "XML_ID", "CAN_DELETE", "PERSONAL_WWW", "PERMISSIONS", "OATOKEN", "OASECRET", "REFRESH_TOKEN", "ACTIVE", "SEND_ACTIVITY", "OATOKEN_EXPIRES", "INITIALIZED");

		$arFields = array(
			"ID" => array("FIELD" => "SU.ID", "TYPE" => "int"),
			"LOGIN" => array("FIELD" => "SU.LOGIN", "TYPE" => "string"),
			"NAME" => array("FIELD" => "SU.NAME", "TYPE" => "string"),
			"LAST_NAME" => array("FIELD" => "SU.LAST_NAME", "TYPE" => "string"),
			"EMAIL" => array("FIELD" => "SU.EMAIL", "TYPE" => "string"),
			"PERSONAL_PHOTO" => array("FIELD" => "SU.PERSONAL_PHOTO", "TYPE" => "int"),
			"EXTERNAL_AUTH_ID" => array("FIELD" => "SU.EXTERNAL_AUTH_ID", "TYPE" => "string"),
			"USER_ID" => array("FIELD" => "SU.USER_ID", "TYPE" => "int"),
			"XML_ID" => array("FIELD" => "SU.XML_ID", "TYPE" => "string"),
			"CAN_DELETE" => array("FIELD" => "SU.CAN_DELETE", "TYPE" => "char"),
			"PERSONAL_WWW" => array("FIELD" => "SU.PERSONAL_WWW", "TYPE" => "string"),
			"PERMISSIONS" => array("FIELD" => "SU.PERMISSIONS", "TYPE" => "string"),
			"OATOKEN" => array("FIELD" => "SU.OATOKEN", "TYPE" => "string"),
			"OASECRET" => array("FIELD" => "SU.OASECRET", "TYPE" => "string"),
			"REFRESH_TOKEN" => array("FIELD" => "SU.REFRESH_TOKEN", "TYPE" => "string"),
			"SEND_ACTIVITY" => array("FIELD" => "SU.SEND_ACTIVITY", "TYPE" => "char"),
			"SITE_ID" => array("FIELD" => "SU.SITE_ID", "TYPE" => "string"),
			"OATOKEN_EXPIRES" => array("FIELD" => "SU.OATOKEN_EXPIRES", "TYPE" => "int"),
			"INITIALIZED" => array("FIELD" => "SU.INITIALIZED", "TYPE" => "char"),
			"ACTIVE" => array("FIELD" => "BU.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user BU ON (SU.USER_ID = BU.ID)"),
		);
		$arSqls = CGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
					"FROM b_socialservices_user SU ".
					"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_socialservices_user SU ".
				"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
					"FROM b_socialservices_user SU ".
					"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
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
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])>0)
			{
				$strSql = $DB->TopSql($strSql, $arNavStartParams["nTopCount"]);
			}

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		if (static::hasEncryptedFields($arSelectFields))
			static::decryptDbRes($dbRes);

		return $dbRes;
	}

	public static function decryptDbRes(\CDBResult $dbRes)
	{
		$cryptoField = new \Bitrix\Socialservices\EncryptedToken\CryptoField('OATOKEN');
		$result = [];
		while ($data = $dbRes->Fetch())
		{
			if (array_key_exists('OATOKEN', $data))
				$data['OATOKEN'] = $cryptoField->decrypt($data['OATOKEN']);

			if (array_key_exists('OASECRET', $data))
				$data['OASECRET'] = $cryptoField->decrypt($data['OASECRET']);

			if (array_key_exists('REFRESH_TOKEN', $data))
				$data['REFRESH_TOKEN'] = $cryptoField->decrypt($data['REFRESH_TOKEN']);

			$result[] = $data;
		}
		$dbRes->InitFromArray($result);
	}

}

class CSocServMessage extends CSocServAllMessage
{
	public static function CleanUp()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sql = 'DELETE FROM b_socialservices_message WHERE INSERT_DATE < ' . $helper->addDaysToDateTime(-4);
		$connection->query($sql);

		return "CSocServMessage::CleanUp();";
	}

	public static function Add($arFields)
	{
		global $DB;
		if (!self::CheckFields('ADD',$arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_socialservices_message", $arFields);
		$strSql =
			"INSERT INTO b_socialservices_message (".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";

		$res=$DB->Query($strSql, true, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!$res)
		{
			return false;
		}
		$lastId = intval($DB->LastID());
		$cache_id = 'socserv_mes_user';
		$obCache = new CPHPCache;
		$cache_dir = '/bx/socserv_mes_user';
		$obCache->Clean($cache_id, $cache_dir);

		return $lastId;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;
		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "USER_ID", "SOCSERV_USER_ID", "PROVIDER", "MESSAGE", "INSERT_DATE", "SUCCES_SENT");
		$arFields = array(
			"ID" => array("FIELD" => "SM.ID", "TYPE" => "int"),
			"USER_ID" => array("FIELD" => "SM.USER_ID", "TYPE" => "int"),
			"SOCSERV_USER_ID" => array("FIELD" => "SM.SOCSERV_USER_ID", "TYPE" => "int"),
			"PROVIDER" => array("FIELD" => "SM.PROVIDER", "TYPE" => "string"),
			"MESSAGE" => array("FIELD" => "SM.MESSAGE", "TYPE" => "string"),
			"INSERT_DATE" => array("FIELD" => "SM.INSERT_DATE", "TYPE" => "datetime"),
			"SUCCES_SENT" => array("FIELD" => "SM.SUCCES_SENT", "TYPE" => "char"),
		);
		$arSqls = CGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
					"FROM b_socialservices_message SM ".
					"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_socialservices_message SM ".
				"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
					"FROM b_socialservices_message SM ".
					"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
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
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])>0)
			{
				$strSql = $DB->TopSql($strSql, $arNavStartParams["nTopCount"]);
			}

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
