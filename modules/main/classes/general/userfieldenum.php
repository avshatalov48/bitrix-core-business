<?php

IncludeModuleLangFile(__FILE__);

class CUserFieldEnum
{
	function SetEnumValues($FIELD_ID, $values)
	{
		global $DB, $CACHE_MANAGER, $APPLICATION;
		$aMsg = array();
		$originalValues = $values;

		foreach($values as $i => $row)
		{
			foreach($row as $key => $val)
			{
				if(strncmp($key, "~", 1) === 0)
				{
					unset($values[$i][$key]);
				}
			}
		}

		/*check unique XML_ID*/
		$arAdded = array();
		$salt = RandString(8);
		foreach($values as $key => $value)
		{
			if(strncmp($key, "n", 1) === 0 && (!isset($value["DEL"]) || $value["DEL"] != "Y") && (string)$value["VALUE"] <> '')
			{
				if(!isset($value["XML_ID"]) || $value["XML_ID"] == '')
				{
					$values[$key]["XML_ID"] = $value["XML_ID"] = md5($salt . $value["VALUE"]);
				}

				if(array_key_exists($value["XML_ID"], $arAdded))
				{
					$aMsg[] = array("text" => GetMessage("USER_TYPE_XML_ID_UNIQ", array("#XML_ID#" => $value["XML_ID"])));
				}
				else
				{
					$rsEnum = $this->GetList(array(), array("USER_FIELD_ID" => $FIELD_ID, "XML_ID" => $value["XML_ID"]));
					if($arEnum = $rsEnum->Fetch())
					{
						$aMsg[] = array("text" => GetMessage("USER_TYPE_XML_ID_UNIQ", array("#XML_ID#" => $value["XML_ID"])));
					}
					else
					{
						if (!isset($arAdded[$value["XML_ID"]]))
						{
							$arAdded[$value["XML_ID"]] = 0;
						}

						$arAdded[$value["XML_ID"]]++;
					}
				}
			}
		}

		$previousValues = array();

		$rsEnum = $this->GetList(array(), array("USER_FIELD_ID" => $FIELD_ID));
		while($arEnum = $rsEnum->Fetch())
		{
			$previousValues[$arEnum["ID"]] = $arEnum;

			if(array_key_exists($arEnum["ID"], $values))
			{
				$value = $values[$arEnum["ID"]];
				if((string)$value["VALUE"] == '' || $value["DEL"] == "Y")
				{
				}
				elseif(
					$arEnum["VALUE"] != $value["VALUE"] ||
					$arEnum["DEF"] != $value["DEF"] ||
					$arEnum["SORT"] != $value["SORT"] ||
					$arEnum["XML_ID"] != $value["XML_ID"]
				)
				{
					if(!isset($value["XML_ID"]) || $value["XML_ID"] == '')
						$value["XML_ID"] = md5($value["VALUE"]);

					$bUnique = true;
					if($arEnum["XML_ID"] != $value["XML_ID"])
					{
						if(array_key_exists($value["XML_ID"], $arAdded))
						{
							$aMsg[] = array("text" => GetMessage("USER_TYPE_XML_ID_UNIQ", array("#XML_ID#" => $value["XML_ID"])));
							$bUnique = false;
						}
						else
						{
							$rsEnumXmlId = $this->GetList(array(), array("USER_FIELD_ID" => $FIELD_ID, "XML_ID" => $value["XML_ID"]));
							if($arEnumXmlId = $rsEnumXmlId->Fetch())
							{
								$aMsg[] = array("text" => GetMessage("USER_TYPE_XML_ID_UNIQ", array("#XML_ID#" => $value["XML_ID"])));
								$bUnique = false;
							}
						}
					}
					if($bUnique)
					{
						$arAdded[$value["XML_ID"]]++;
					}
				}
			}
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		if(CACHED_b_user_field_enum !== false)
			$CACHE_MANAGER->CleanDir("b_user_field_enum");

		foreach($values as $key => $value)
		{
			if(strncmp($key, "n", 1) === 0 && (!isset($value["DEL"]) || $value["DEL"] != "Y") && (string)$value["VALUE"] <> '')
			{
				if(!isset($value["XML_ID"]) || $value["XML_ID"] == '')
					$value["XML_ID"] = md5($value["VALUE"]);

				if($value["DEF"] != "Y")
					$value["DEF"] = "N";

				$value["USER_FIELD_ID"] = $FIELD_ID;
				$id = $DB->Add("b_user_field_enum", $value);

				$originalValues[$id] = $originalValues[$key];
				unset($originalValues[$key], $values[$key]);
			}
		}
		$rsEnum = $this->GetList(array(), array("USER_FIELD_ID" => $FIELD_ID));
		while($arEnum = $rsEnum->Fetch())
		{
			if(array_key_exists($arEnum["ID"], $values))
			{
				$value = $values[$arEnum["ID"]];
				if((string)$value["VALUE"] == '' || $value["DEL"] == "Y")
				{
					$DB->Query("DELETE FROM b_user_field_enum WHERE ID = " . $arEnum["ID"]);
				}
				elseif($arEnum["VALUE"] != $value["VALUE"] ||
					$arEnum["DEF"] != $value["DEF"] ||
					$arEnum["SORT"] != $value["SORT"] ||
					$arEnum["XML_ID"] != $value["XML_ID"])
				{
					if(!isset($value["XML_ID"]) || $value["XML_ID"] == '')
						$value["XML_ID"] = md5($value["VALUE"]);

					unset($value["ID"]);
					$strUpdate = $DB->PrepareUpdate("b_user_field_enum", $value);
					if($strUpdate <> '')
						$DB->Query("UPDATE b_user_field_enum SET " . $strUpdate . " WHERE ID = " . $arEnum["ID"]);
				}
			}
		}
		if(CACHED_b_user_field_enum !== false)
			$CACHE_MANAGER->CleanDir("b_user_field_enum");

		$event = new \Bitrix\Main\Event('main', 'onAfterSetEnumValues', [$FIELD_ID, $originalValues, $previousValues]);
		$event->send();

		return true;
	}

	public static function GetList($aSort = array(), $aFilter = array())
	{
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_user_field_enum !== false)
		{
			$cacheId = "b_user_field_enum" . md5(serialize($aSort) . "." . serialize($aFilter));
			if($CACHE_MANAGER->Read(CACHED_b_user_field_enum, $cacheId, "b_user_field_enum"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				return $res;
			}
		}
		else
		{
			$cacheId = '';
		}

		$bJoinUFTable = false;
		$arFilter = array();
		foreach($aFilter as $key => $val)
		{
			if(is_array($val))
			{
				if(empty($val))
					continue;
				$val = array_map(array($DB, "ForSQL"), $val);
				$val = "('" . implode("', '", $val) . "')";
			}
			else
			{
				if((string)$val == '')
					continue;
				$val = "('" . $DB->ForSql($val) . "')";
			}

			$key = mb_strtoupper($key);
			switch($key)
			{
				case "ID":
				case "USER_FIELD_ID":
				case "VALUE":
				case "DEF":
				case "SORT":
				case "XML_ID":
					$arFilter[] = "UFE." . $key . " in " . $val;
					break;
				case "USER_FIELD_NAME":
					$bJoinUFTable = true;
					$arFilter[] = "UF.FIELD_NAME in " . $val;
					break;
			}
		}

		$arOrder = array();
		foreach($aSort as $key => $val)
		{
			$key = mb_strtoupper($key);
			$ord = (mb_strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			switch($key)
			{
				case "ID":
				case "USER_FIELD_ID":
				case "VALUE":
				case "DEF":
				case "SORT":
				case "XML_ID":
					$arOrder[] = "UFE." . $key . " " . $ord;
					break;
			}
		}
		if(empty($arOrder))
		{
			$arOrder[] = "UFE.SORT asc";
			$arOrder[] = "UFE.ID asc";
		}
		DelDuplicateSort($arOrder);
		$sOrder = "\nORDER BY " . implode(", ", $arOrder);

		if(empty($arFilter))
			$sFilter = "";
		else
			$sFilter = "\nWHERE " . implode("\nAND ", $arFilter);

		$strSql = "
			SELECT
				UFE.ID
				,UFE.USER_FIELD_ID
				,UFE.VALUE
				,UFE.DEF
				,UFE.SORT
				,UFE.XML_ID
			FROM
				b_user_field_enum UFE
				" . ($bJoinUFTable ? "INNER JOIN b_user_field UF ON UF.ID = UFE.USER_FIELD_ID" : "") . "
			" . $sFilter . $sOrder;

		if($cacheId == '')
		{
			$res = $DB->Query($strSql);
		}
		else
		{
			$arResult = array();
			$res = $DB->Query($strSql);
			while($ar = $res->Fetch())
				$arResult[] = $ar;

			$CACHE_MANAGER->Set($cacheId, $arResult);

			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}

		return $res;
	}

	function DeleteFieldEnum($FIELD_ID)
	{
		global $DB, $CACHE_MANAGER;
		$DB->Query("DELETE FROM b_user_field_enum WHERE USER_FIELD_ID = " . intval($FIELD_ID));
		if(CACHED_b_user_field_enum !== false) $CACHE_MANAGER->CleanDir("b_user_field_enum");
	}
}
