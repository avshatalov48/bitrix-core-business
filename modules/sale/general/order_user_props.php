<?php

IncludeModuleLangFile(__FILE__);

class CAllSaleOrderUserProps
{
	/*
	public static function TranslateLocationPropertyValues($personTypeId, &$orderProps)
	{
		if(CSaleLocation::isLocationProMigrated())
		{
			// location ID to CODE
			$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
					'PERSON_TYPE_ID' => $personTypeId
				),
				false,
				false,
				array("ID", "NAME", "TYPE", "IS_LOCATION", "IS_LOCATION4TAX", "IS_PROFILE_NAME", "IS_PAYER", "IS_EMAIL", "REQUIED", "SORT", "IS_ZIP", "CODE", "MULTIPLE")
			);
			while($item = $dbOrderProps->fetch())
			{
				if($item['TYPE'] == 'LOCATION' && strlen($orderProps[$item['ID']]))
					$orderProps[$item['ID']] = CSaleLocation::getLocationCodeByID($orderProps[$item['ID']]);
			}
		}
	}
	*/

	/**
	 * @param $userId
	 * @param $profileId
	 * @param $profileName
	 * @param $personTypeId
	 * @param $orderProps
	 * @param $arErrors
	 *
	 * @return bool|int
	 */
	public static function DoSaveUserProfile($userId, $profileId, $profileName, $personTypeId, $orderProps, &$arErrors)
	{
		$profileId = intval($profileId);

		$arIDs = array();
		if ($profileId > 0)
		{
			$dbProfile = CSaleOrderUserProps::GetList(
				array(),
				array("ID" => $profileId),
				false,
				false,
				array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID")
			);
			$arProfile = $dbProfile->Fetch();
			if (!$arProfile)
			{
				$arErrors[] = array("CODE" => "PROFILE_NOT_FOUND", "TEXT" => GetMessage('SKGOUP_PROFILE_NOT_FOUND'));
				return false;
			}
			if ($arProfile["USER_ID"] != $userId || $arProfile["PERSON_TYPE_ID"] != $personTypeId)
			{
				$arErrors[] = array("CODE" => "PARAM", "TEXT" => GetMessage('SKGOUP_PARRAMS_ERROR'));
				return false;
			}

			//if (strlen($profileName) > 0 && $profileName != $arProfile["NAME"])
			if ($profileName <> '')
			{
				$arFields = array("NAME" => $profileName, "USER_ID" => $userId);
				CSaleOrderUserProps::Update($profileId, $arFields);
			}

			$dbUserPropsValues = CSaleOrderUserPropsValue::GetList(
				array(),
				array("USER_PROPS_ID" => $profileId),
				false,
				false,
				array("ID", "ORDER_PROPS_ID")
			);
			while ($arUserPropsValue = $dbUserPropsValues->Fetch())
				$arIDs[$arUserPropsValue["ORDER_PROPS_ID"]] = $arUserPropsValue["ID"];
		}

		if (!is_array($orderProps) && (int)$orderProps > 0)
		{
			$dbOrderPropsValues = CSaleOrderPropsValue::GetList(
				array(),
				array("ORDER_ID" => intval($orderProps)),
				false,
				false,
				array("ORDER_PROPS_ID", "VALUE")
			);
			$orderProps = array();
			while ($arOrderPropsValue = $dbOrderPropsValues->Fetch())
				$orderProps[$arOrderPropsValue["ORDER_PROPS_ID"]] = $arOrderPropsValue["VALUE"];
		}

		if (empty($orderProps))
		{
			/**
			 * Returned profile ID instead of error for compatibility
			 */
			return $profileId;
		}

		$dbOrderProperties = CSaleOrderProps::GetList(
			array(),
			array("PERSON_TYPE_ID" => $personTypeId, "ACTIVE" => "Y", "USER_PROPS" => "Y"),
			false,
			false,
			array("ID", "TYPE", "NAME", "CODE", "UTIL", "MULTIPLE")
		);
		while ($arOrderProperty = $dbOrderProperties->Fetch())
		{
			if (isset($orderProps[$arOrderProperty["ID"]]))
			{
				$curVal = $orderProps[$arOrderProperty["ID"]];
			}

			if (
				(
					$arOrderProperty["TYPE"] === "MULTISELECT"
					|| $arOrderProperty["TYPE"] === "SELECT"
				)
				&& is_array($curVal)
			)
			{
				$curVal = implode(",", $curVal);
			}

			if (($arOrderProperty["TYPE"] === "FILE") && is_array($curVal))
			{
				$fileList = array();

				foreach ($curVal as $fileDat)
				{
					if (!empty($fileDat['ID']))
					{
						$fileList[] = $fileDat['ID'];
					}
				}

				$curVal = serialize($fileList);
			}

			if ($arOrderProperty["MULTIPLE"] === "Y" && is_array($curVal))
			{
				$curVal = serialize($curVal);
			}

			if (isset($curVal))
			{
				if ($profileId <= 0)
				{
					if ($profileName == '')
						$profileName = GetMessage("SOA_PROFILE")." ".Date("Y-m-d");

					$arFields = array(
						"NAME" => $profileName,
						"USER_ID" => $userId,
						"PERSON_TYPE_ID" => $personTypeId
					);
					$profileId = CSaleOrderUserProps::Add($arFields);
					if (!$profileId)
					{
						$arErrors[] = array("CODE" => "PROFILE_CREATE_ERROR", "TEXT" => GetMessage('SKGOUP_PROFILE_CREATE_ERROR'));
						return false;
					}
				}

				if (array_key_exists($arOrderProperty["ID"], $arIDs))
				{
					$arFields = Array(
						"NAME" => $arOrderProperty["NAME"],
						"VALUE" => $curVal
					);
					CSaleOrderUserPropsValue::Update($arIDs[$arOrderProperty["ID"]], $arFields);
					unset($arIDs[$arOrderProperty["ID"]]);
				}
				else
				{
					$arFields = array(
						"USER_PROPS_ID" => $profileId,
						"ORDER_PROPS_ID" => $arOrderProperty["ID"],
						"NAME" => $arOrderProperty["NAME"],
						"VALUE" => $curVal
					);
					CSaleOrderUserPropsValue::Add($arFields);
				}
			}
			unset($curVal);
		}

		return $profileId;
	}

	public static function DoLoadProfiles($userId, $personTypeId = null)
	{
		$userId = intval($userId);

		if ($userId <= 0)
			return null;

		$arResult = array();
		$arFilter = array("USER_ID" => $userId);

		if ($personTypeId != null)
			$arFilter["PERSON_TYPE_ID"] = $personTypeId;

		$dbProfile = CSaleOrderUserProps::GetList(
			array("DATE_UPDATE" => "DESC", "NAME" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "PERSON_TYPE_ID", "DATE_UPDATE")
		);

		while ($arProfile = $dbProfile->GetNext())
		{
			if (!array_key_exists($arProfile["PERSON_TYPE_ID"], $arResult))
				$arResult[$arProfile["PERSON_TYPE_ID"]] = array();

			$arResult[$arProfile["PERSON_TYPE_ID"]][$arProfile["ID"]] = array("NAME" => $arProfile["NAME"], "VALUES" => array(), "VALUES_ORIG" => array());

			$dbProps = CSaleOrderUserPropsValue::GetList(
				array(),
				array("USER_PROPS_ID" => $arProfile["ID"]),
				false,
				false,
				array("ORDER_PROPS_ID", "NAME", "VALUE", "VALUE_ORIG")
			);
			while ($arProps = $dbProps->GetNext())
			{
				$arResult[$arProfile["PERSON_TYPE_ID"]][$arProfile["ID"]]["VALUES"][$arProps["ORDER_PROPS_ID"]] = $arProps["VALUE"];

				if(isset($arProps["VALUE_ORIG"]))
					$arResult[$arProfile["PERSON_TYPE_ID"]][$arProfile["ID"]]["VALUES_ORIG"][$arProps["ORDER_PROPS_ID"]] = $arProps["VALUE_ORIG"];
			}
		}

		if (count($arResult) > 0)
		{
			if ($personTypeId != null)
				$arResult = $arResult[$personTypeId];
		}

		return $arResult;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_user_props ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $USER;

		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && intval($arFields["PERSON_TYPE_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_EMPTY_PERS_TYPE"), "ERROR_NO_PERSON_TYPE_ID");
			return false;
		}

		if (false && !$USER->IsAuthorized())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_UNAUTH"), "ERROR_NO_AUTH");
			return false;
		}

		if (!is_set($arFields, "USER_ID"))
			$arFields["USER_ID"] = intval($USER->GetID());

		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_NO_USER_ID"), "ERROR_NO_PERSON_TYPE_ID");
			return false;
		}

		return True;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if (!CSaleOrderUserProps::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_user_props", $arFields);

		$strSql =
			"UPDATE b_sale_user_props SET ".
			"	".$strUpdate.", ".
			"	DATE_UPDATE = ".$DB->GetNowFunction()." ".
			"WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		return $ID;
	}

	public static function ClearEmpty()
	{
		global $DB;
		$strSql =
			"SELECT UP.ID ".
			"FROM b_sale_user_props UP ".
			"	LEFT JOIN b_sale_user_props_value UPV ON (UP.ID = UPV.USER_PROPS_ID) ".
			"WHERE UPV.ID IS NULL ";
		$db_res = $DB->Query($strSql);
		while ($res = $db_res->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_user_props WHERE ID = ".$res["ID"]."");
		}
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$DB->Query("DELETE FROM b_sale_user_props_value WHERE USER_PROPS_ID = ".$ID."", true);
		return $DB->Query("DELETE FROM b_sale_user_props WHERE ID = ".$ID."", true);
	}

	public static function OnUserDelete($ID)
	{
		$ID = intval($ID);
		$db_res = CSaleOrderUserProps::GetList(($b="ID"), ($o="ASC"), Array("USER_ID"=>$ID));
		while ($ar_res = $db_res->Fetch())
		{
			CSaleOrderUserProps::Delete(intval($ar_res["ID"]));
		}
		return True;
	}
}
