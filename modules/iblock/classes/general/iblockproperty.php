<?php

use Bitrix\Main;
use Bitrix\Iblock;

global $IBLOCK_CACHE_PROPERTY;
$IBLOCK_CACHE_PROPERTY = [];
IncludeModuleLangFile(__FILE__);

class CAllIBlockProperty
{
	public string $LAST_ERROR = '';

	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB;

		$strSql = "
			SELECT BP.*
			FROM b_iblock_property BP
		";

		$bJoinIBlock = false;
		$arSqlSearch = array();
		foreach($arFilter as $key => $val)
		{
			$val = $DB->ForSql($val);
			$key = mb_strtoupper($key);

			switch($key)
			{
			case "ACTIVE":
			case "SEARCHABLE":
			case "FILTRABLE":
			case "IS_REQUIRED":
			case "MULTIPLE":
				if($val=="Y" || $val=="N")
					$arSqlSearch[] = "BP.".$key." = '".$val."'";
				break;
			case "?CODE":
			case "?NAME":
				$arSqlSearch[] = CIBlock::FilterCreate("BP.".mb_substr($key, 1), $val, "string", "E");
				break;
			case "CODE":
			case "NAME":
				$arSqlSearch[] = "UPPER(BP.".$key.") LIKE UPPER('".$val."')";
				break;
			case "XML_ID":
			case "EXTERNAL_ID":
				$arSqlSearch[] = "BP.XML_ID LIKE '".$val."'";
				break;
			case "!XML_ID":
			case "!EXTERNAL_ID":
				$arSqlSearch[] = "(BP.XML_ID IS NULL OR NOT (BP.XML_ID LIKE '".$val."'))";
				break;
			case "TMP_ID":
				$arSqlSearch[] = "BP.TMP_ID LIKE '".$val."'";
				break;
			case "!TMP_ID":
				$arSqlSearch[] = "(BP.TMP_ID IS NULL OR NOT (BP.TMP_ID LIKE '".$val."'))";
				break;
			case "PROPERTY_TYPE":
				$ar = explode(":", $val);
				if (count($ar) == 2)
				{
					$val = $ar[0];
					$arSqlSearch[] = "BP.USER_TYPE = '".$ar[1]."'";
				}
				$arSqlSearch[] = "BP.".$key." = '".$val."'";
				break;
			case "USER_TYPE":
				$arSqlSearch[] = "BP.".$key." = '".$val."'";
				break;
			case "ID":
			case "IBLOCK_ID":
			case "LINK_IBLOCK_ID":
			case "VERSION":
				$arSqlSearch[] = "BP.".$key." = ".(int)$val;
				break;
			case "IBLOCK_CODE":
				$arSqlSearch[] = "UPPER(B.CODE) = UPPER('".$val."')";
				$bJoinIBlock = true;
				break;
			}
		}

		if($bJoinIBlock)
			$strSql .= "
				INNER JOIN b_iblock B ON B.ID = BP.IBLOCK_ID
			";

		if(!empty($arSqlSearch))
			$strSql .= "
				WHERE ".implode("\n\t\t\t\tAND ", $arSqlSearch)."
			";

		$allowKeys = array(
			"ID" => true,
			"IBLOCK_ID" => true,
			"NAME" => true,
			"ACTIVE" => true,
			"SORT" => true,
			"FILTRABLE" => true,
			"SEARCHABLE" => true
		);
		$orderKeys = array();
		$arSqlOrder = array();
		foreach($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			if (!isset($allowKeys[$by]))
				$by = "TIMESTAMP_X";
			if (isset($orderKeys[$by]))
				continue;
			$orderKeys[$by] = true;
			$order = mb_strtoupper($order) == "ASC"? "ASC": "DESC";

			$arSqlOrder[] = "BP.".$by." ".$order;
		}

		if(!empty($arSqlOrder))
			$strSql .= "
				ORDER BY ".implode(", ", $arSqlOrder)."
			";

		$res = $DB->Query($strSql);

		return new CIBlockPropertyResult($res);
	}

	///////////////////////////////////////////////////////////////////
	// Delete by property ID
	///////////////////////////////////////////////////////////////////
	public static function Delete($ID)
	{
		global $DB, $APPLICATION;
		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}

		$APPLICATION->ResetException();
		foreach (GetModuleEvents("iblock", "OnBeforeIBlockPropertyDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				if($ex = $APPLICATION->GetException())
				{
					$APPLICATION->ThrowException($ex->GetString());
				}
				else
				{
					$APPLICATION->ThrowException(GetMessage("MAIN_BEFORE_DEL_ERR1"));
				}

				return false;
			}
		}

		foreach (GetModuleEvents("iblock", "OnIBlockPropertyDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		if(!CIBlockPropertyEnum::DeleteByPropertyID($ID, true))
			return false;

		CIBlockSectionPropertyLink::DeleteByProperty($ID);
		Iblock\PropertyFeatureTable::deleteByProperty($ID);

		$rsProperty = CIBlockProperty::GetByID($ID);
		$arProperty = $rsProperty->Fetch();
		if($arProperty["VERSION"] == 2)
		{
			if($arProperty["PROPERTY_TYPE"]=="F")
			{
				if($arProperty["MULTIPLE"]=="Y")
				{
					$strSql = "
						SELECT	VALUE
						FROM	b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
						WHERE	IBLOCK_PROPERTY_ID=".$ID."
					";
				}
				else
				{
					$strSql = "
						SELECT	PROPERTY_".$ID." VALUE
						FROM	b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
						WHERE	PROPERTY_".$ID." is not null
					";
				}
				$res = $DB->Query($strSql);
				while($arr = $res->Fetch())
					CFile::Delete($arr["VALUE"]);
			}
			if(!$DB->Query("DELETE FROM b_iblock_section_element WHERE ADDITIONAL_PROPERTY_ID=".$ID, true))
				return false;
			$strSql = "
				DELETE
				FROM b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
				WHERE IBLOCK_PROPERTY_ID=".$ID."
			";
			if(!$DB->Query($strSql))
				return false;
			$arSql = CIBlockProperty::DropColumnSQL("b_iblock_element_prop_s".$arProperty["IBLOCK_ID"], array("PROPERTY_".$ID,"DESCRIPTION_".$ID));
			foreach($arSql as $strSql)
			{
				if(!$DB->DDL($strSql))
					return false;
			}
		}
		else
		{
			$res = $DB->Query("SELECT EP.VALUE FROM b_iblock_property P, b_iblock_element_property EP WHERE P.ID=".$ID." AND P.ID=EP.IBLOCK_PROPERTY_ID AND P.PROPERTY_TYPE='F'");
			while($arr = $res->Fetch())
				CFile::Delete($arr["VALUE"]);
			if(!$DB->Query("DELETE FROM b_iblock_section_element WHERE ADDITIONAL_PROPERTY_ID=".$ID, true))
				return false;
			if(!$DB->Query("DELETE FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID=".$ID, true))
				return false;
		}

		$seq = new CIBlockSequence($arProperty["IBLOCK_ID"], $ID);
		$seq->Drop();

		CIBlock::clearIblockTagCache($arProperty["IBLOCK_ID"]);

		Iblock\PropertyTable::cleanCache();

		$res = $DB->Query("DELETE FROM b_iblock_property WHERE ID=".$ID, true);

		foreach (GetModuleEvents("iblock", "OnAfterIBlockPropertyDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($arProperty));

		return $res;
	}
	///////////////////////////////////////////////////////////////////
	// Add
	///////////////////////////////////////////////////////////////////
	public function Add($arFields)
	{
		global $DB;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if(!isset($arFields["SEARCHABLE"]) || $arFields["SEARCHABLE"] != "Y")
			$arFields["SEARCHABLE"]="N";
		if(!isset($arFields["FILTRABLE"]) || $arFields["FILTRABLE"] != "Y")
			$arFields["FILTRABLE"]="N";
		if(is_set($arFields, "MULTIPLE") && $arFields["MULTIPLE"]!="Y")
			$arFields["MULTIPLE"]="N";
		if(is_set($arFields, "LIST_TYPE") && $arFields["LIST_TYPE"]!="C")
			$arFields["LIST_TYPE"]="L";
		$arFields['IS_REQUIRED'] = ($arFields['IS_REQUIRED'] ?? 'N') === 'Y' ? 'Y' : 'N';

		if(!$this->CheckFields($arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			$arFields["VERSION"] = CIBlockElement::GetIBVersion($arFields["IBLOCK_ID"]);
			unset($arFields["ID"]);
			if (isset($arFields['USER_TYPE']))
			{
				$arUserType = [];
				$userTypeId = (string)$arFields['USER_TYPE'];
				if ($userTypeId !== '')
				{
					$arUserType = CIBlockProperty::GetUserType($userTypeId);
				}
				if (isset($arUserType['ConvertToDB']))
				{
					$arValue = [
						'VALUE' => $arFields['DEFAULT_VALUE'],
						'DEFAULT_VALUE' => true,
					];
					$arValue = call_user_func_array(
						$arUserType['ConvertToDB'],
						[$arFields, $arValue]
					);
					$arFields['DEFAULT_VALUE'] = $this->prepareDefaultValue($arValue);
					unset($arValue);
				}
				if (isset($arUserType['PrepareSettings']))
				{
					$arFieldsResult = call_user_func_array(
						$arUserType['PrepareSettings'],
						[$arFields]
					);
					if (is_array($arFieldsResult) && array_key_exists('USER_TYPE_SETTINGS', $arFieldsResult))
					{
						$arFields = array_merge($arFields, $arFieldsResult);
						$arFields['USER_TYPE_SETTINGS'] = serialize($arFields['USER_TYPE_SETTINGS']);
					}
					else
					{
						$arFields['USER_TYPE_SETTINGS'] = serialize($arFieldsResult);
					}
				}
				else
				{
					$arFields['USER_TYPE_SETTINGS'] = false;
				}
			}
			else
			{
				$arFields['USER_TYPE_SETTINGS'] = false;
			}

			unset($arFields['TIMESTAMP_X']);
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);

			$ID = $DB->Add("b_iblock_property", $arFields, array('USER_TYPE_SETTINGS'), "iblock");

			if($arFields["VERSION"]==2)
			{
				if($this->_Add($ID, $arFields))
				{
					$Result = $ID;
					$arFields["ID"] = &$ID;
				}
				else
				{
					$DB->Query("DELETE FROM b_iblock_property WHERE ID = ".(int)$ID);
					$this->LAST_ERROR = GetMessage("IBLOCK_PROPERTY_ADD_ERROR",array(
						"#ID#"=>$ID,
						"#CODE#"=>"[14]".$DB->GetErrorSQL(),
					));
					$Result = false;
					$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
				}
			}
			else
			{
				$Result = $ID;
				$arFields["ID"] = &$ID;
			}

			if($Result)
			{
				if(array_key_exists("VALUES", $arFields))
					$this->UpdateEnum($ID, $arFields["VALUES"]);

				if(CIBlock::GetArrayByID($arFields["IBLOCK_ID"], "SECTION_PROPERTY") === "Y")
				{
					if(
						!array_key_exists("SECTION_PROPERTY", $arFields)
						|| $arFields["SECTION_PROPERTY"] !== "N"
					)
					{
						$arLink = array(
							"SMART_FILTER" => $arFields["SMART_FILTER"] ?? null,
						);
						if (array_key_exists("DISPLAY_TYPE", $arFields))
							$arLink["DISPLAY_TYPE"] = $arFields["DISPLAY_TYPE"];
						if (array_key_exists("DISPLAY_EXPANDED", $arFields))
							$arLink["DISPLAY_EXPANDED"] = $arFields["DISPLAY_EXPANDED"];
						if (array_key_exists("FILTER_HINT", $arFields))
							$arLink["FILTER_HINT"] = $arFields["FILTER_HINT"];
						CIBlockSectionPropertyLink::Add(0, $ID, $arLink);
					}
				}

				if (!empty($arFields['FEATURES']) && is_array($arFields['FEATURES']))
				{
					$featureResult = Iblock\Model\PropertyFeature::addFeatures(
						$ID,
						$arFields['FEATURES']
					);
					//TODO: add error handling
					unset($featureResult);
				}

				Iblock\PropertyTable::cleanCache();
			}
		}

		global $BX_IBLOCK_PROP_CACHE;
		if (isset($arFields["IBLOCK_ID"]))
		{
			unset($BX_IBLOCK_PROP_CACHE[$arFields["IBLOCK_ID"]]);
			CIBlock::clearIblockTagCache($arFields["IBLOCK_ID"]);
		}

		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockPropertyAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $Result;
	}
	///////////////////////////////////////////////////////////////////
	// This one called before any Update or Add
	///////////////////////////////////////////////////////////////////
	public function CheckFields(&$arFields, $ID=false, $bFormValidate=false)
	{
		global $APPLICATION;
		$this->LAST_ERROR = "";
		if ($ID===false || array_key_exists("NAME", $arFields))
		{
			if ($arFields["NAME"] == '')
				$this->LAST_ERROR .= GetMessage("IBLOCK_PROPERTY_BAD_NAME")."<br>";
		}

		if(array_key_exists("CODE", $arFields) && mb_strlen($arFields["CODE"]))
		{
			if(mb_strpos("0123456789", mb_substr($arFields["CODE"], 0, 1)) !== false)
				$this->LAST_ERROR .= GetMessage("IBLOCK_PROPERTY_CODE_FIRST_LETTER").": ".htmlspecialcharsbx($arFields["CODE"])."<br>";
			if(preg_match("/[^A-Za-z0-9_]/",  $arFields["CODE"]))
				$this->LAST_ERROR .= GetMessage("IBLOCK_PROPERTY_WRONG_CODE").": ".htmlspecialcharsbx($arFields["CODE"])."<br>";
		}

		if(!$bFormValidate)
		{
			if($ID===false && !is_set($arFields, "IBLOCK_ID"))
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";

			if(is_set($arFields, "IBLOCK_ID"))
			{
				$arFields["IBLOCK_ID"] = (int)$arFields["IBLOCK_ID"];
				$r = CIBlock::GetList(array(), array("ID"=>$arFields["IBLOCK_ID"], "CHECK_PERMISSIONS" => "N"));
				if(!$r->Fetch())
					$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
		}

		if (isset($arFields['USER_TYPE']))
		{
			if ($ID === false)
			{
				$arFields['DEFAULT_VALUE'] ??= false;
			}
			$arUserType = CIBlockProperty::GetUserType($arFields['USER_TYPE']);
			if (
				isset($arUserType['CheckFields'])
				&& ($ID === false || array_key_exists('DEFAULT_VALUE', $arFields))
			)
			{
				$value = [
					'VALUE' => $arFields['DEFAULT_VALUE'],
				];
				$arError = call_user_func_array(
					$arUserType['CheckFields'],
					[
						$arFields,
						$value
					]
				);
				if (!empty($arError) && is_array($arError))
				{
					$this->LAST_ERROR .= implode('<br>', $arError) . '<br>';
				}
			}
		}

		if(!$bFormValidate)
		{
			$APPLICATION->ResetException();
			if($ID===false)
			{
				$db_events = GetModuleEvents("iblock", "OnBeforeIBlockPropertyAdd", true);
			}
			else
			{
				$arFields["ID"] = $ID;
				$db_events = GetModuleEvents("iblock", "OnBeforeIBlockPropertyUpdate", true);
			}

			foreach($db_events as $arEvent)
			{
				$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
				if($bEventRes===false)
				{
					if($err = $APPLICATION->GetException())
					{
						$this->LAST_ERROR .= $err->GetString()."<br>";
					}
					else
					{
						$APPLICATION->ThrowException("Unknown error");
						$this->LAST_ERROR .= "Unknown error.<br>";
					}
					break;
				}
			}
		}

		return $this->LAST_ERROR === '';
	}

	///////////////////////////////////////////////////////////////////
	// Update method
	///////////////////////////////////////////////////////////////////
	public function Update($ID, $arFields, $bCheckDescription = false)
	{
		global $DB;
		$ID = (int)$ID;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if(is_set($arFields, "SEARCHABLE") && $arFields["SEARCHABLE"]!="Y")
			$arFields["SEARCHABLE"]="N";
		if(is_set($arFields, "FILTRABLE") && $arFields["FILTRABLE"]!="Y")
			$arFields["FILTRABLE"]="N";
		if(is_set($arFields, "MULTIPLE") && $arFields["MULTIPLE"]!="Y")
			$arFields["MULTIPLE"]="N";
		if(is_set($arFields, "LIST_TYPE") && $arFields["LIST_TYPE"]!="C")
			$arFields["LIST_TYPE"]="L";

		if(!$this->CheckFields($arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif(!$this->_Update($ID, $arFields, $bCheckDescription))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			$arUserType = [];
			$userTypeId = (string)($arFields['USER_TYPE'] ?? '');
			if ($userTypeId !== '')
			{
				$arUserType = CIBlockProperty::GetUserType($userTypeId);
			}
			if (!empty($arUserType))
			{
				if (array_key_exists('DEFAULT_VALUE', $arFields))
				{
					if (isset($arUserType['ConvertToDB']))
					{
						$arValue = [
							'VALUE' => $arFields['DEFAULT_VALUE'],
							'DEFAULT_VALUE' => true,
						];
						$arValue = call_user_func_array(
							$arUserType['ConvertToDB'],
							[$arFields, $arValue]
						);
						$arFields['DEFAULT_VALUE'] = $this->prepareDefaultValue($arValue);
						unset($arValue);
					}
					else
					{
						if (!is_scalar($arFields['DEFAULT_VALUE']))
						{
							$arFields['DEFAULT_VALUE'] = false;
						}
					}
				}

				if (isset($arUserType['PrepareSettings']))
				{
					if (!isset($arFields["USER_TYPE_SETTINGS"]))
					{
						$oldData = Iblock\PropertyTable::getRow([
							'select' => [
								'ID',
								'PROPERTY_TYPE',
								'USER_TYPE',
								'USER_TYPE_SETTINGS',
							],
							'filter' => [
								'=ID' => $ID,
							],
						]);
						if (!empty($oldData))
						{
							if ($arFields["USER_TYPE"] == $oldData["USER_TYPE"] && !empty($oldData["USER_TYPE_SETTINGS"]))
							{
								$arFields["USER_TYPE_SETTINGS"] = (
									is_array($oldData["USER_TYPE_SETTINGS"])
										? $oldData["USER_TYPE_SETTINGS"]
										: unserialize($oldData["USER_TYPE_SETTINGS"], ['allowed_classes' => false])
								);
							}
						}
						unset($oldData);
					}
					$arFieldsResult = call_user_func_array($arUserType["PrepareSettings"], array($arFields));
					if (is_array($arFieldsResult) && array_key_exists('USER_TYPE_SETTINGS', $arFieldsResult))
					{
						$arFields = array_merge($arFields, $arFieldsResult);
						$arFields["USER_TYPE_SETTINGS"] = serialize($arFields["USER_TYPE_SETTINGS"]);
					}
					else
					{
						$arFields["USER_TYPE_SETTINGS"] = serialize($arFieldsResult);
					}
					unset($arFieldsResult);
				}
				else
				{
					$arFields["USER_TYPE_SETTINGS"] = false;
				}
			}
			else
			{
				if (isset($arFields['DEFAULT_VALUE']) && !is_scalar($arFields['DEFAULT_VALUE']))
				{
					$arFields['DEFAULT_VALUE'] = false;
				}
				if (isset($arFields["USER_TYPE_SETTINGS"]))
				{
					if (is_array($arFields["USER_TYPE_SETTINGS"]))
					{
						$arFields["USER_TYPE_SETTINGS"] = serialize($arFields["USER_TYPE_SETTINGS"]);
					}
					if (!is_scalar($arFields["USER_TYPE_SETTINGS"]))
					{
						$arFields["USER_TYPE_SETTINGS"] = false;
					}
				}
			}
			unset($arUserType);

			unset($arFields["ID"]);
			unset($arFields["VERSION"]);
			unset($arFields["TIMESTAMP_X"]);
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
			unset($helper, $connection);

			$strUpdate = $DB->PrepareUpdate("b_iblock_property", $arFields);
			if($strUpdate <> '')
			{
				$strSql = "UPDATE b_iblock_property SET ".$strUpdate." WHERE ID=".$ID;
				$bindList = [];
				if (isset($arFields['USER_TYPE_SETTINGS']))
				{
					$bindList['USER_TYPE_SETTINGS'] = $arFields['USER_TYPE_SETTINGS'];
				}
				$DB->QueryBind($strSql, $bindList);
			}

			if(is_set($arFields, "VALUES"))
				$this->UpdateEnum($ID, $arFields["VALUES"]);

			if(
				array_key_exists("IBLOCK_ID", $arFields)
				&& CIBlock::GetArrayByID($arFields["IBLOCK_ID"], "SECTION_PROPERTY") === "Y"
			)
			{
				if(
					!array_key_exists("SECTION_PROPERTY", $arFields)
					|| $arFields["SECTION_PROPERTY"] !== "N"
				)
				{
					$arLink = [];
					if (array_key_exists("SMART_FILTER", $arFields))
					{
						$arLink["SMART_FILTER"] = $arFields["SMART_FILTER"];
					}
					if (array_key_exists("DISPLAY_TYPE", $arFields))
						$arLink["DISPLAY_TYPE"] = $arFields["DISPLAY_TYPE"];
					if (array_key_exists("DISPLAY_EXPANDED", $arFields))
						$arLink["DISPLAY_EXPANDED"] = $arFields["DISPLAY_EXPANDED"];
					if (array_key_exists("FILTER_HINT", $arFields))
						$arLink["FILTER_HINT"] = $arFields["FILTER_HINT"];
					CIBlockSectionPropertyLink::Set(0, $ID, $arLink);
				}
				else
				{
					CIBlockSectionPropertyLink::Delete(0, $ID);
				}
			}

			if (!empty($arFields['FEATURES']) && is_array($arFields['FEATURES']))
			{
				$featureResult = Iblock\Model\PropertyFeature::setFeatures(
					$ID,
					$arFields['FEATURES']
				);
				//TODO: add error handling
				unset($featureResult);
			}

			Iblock\PropertyTable::cleanCache();

			global $BX_IBLOCK_PROP_CACHE;
			if (isset($arFields["IBLOCK_ID"]))
			{
				unset($BX_IBLOCK_PROP_CACHE[$arFields["IBLOCK_ID"]]);
				CIBlock::clearIblockTagCache($arFields["IBLOCK_ID"]);
			}

			$Result = true;
		}

		$arFields["ID"] = $ID;
		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockPropertyUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Get property information by ID
	///////////////////////////////////////////////////////////////////
	public static function GetByID($ID, $IBLOCK_ID=false, $IBLOCK_CODE=false)
	{
		$iblockId = null;
		$iblockCode = null;
		if (is_numeric($IBLOCK_ID))
		{
			$IBLOCK_ID = (int)$IBLOCK_ID;
			if ($IBLOCK_ID > 0)
			{
				$iblockId = $IBLOCK_ID;
			}
		}
		if (is_string($IBLOCK_CODE))
		{
			$IBLOCK_CODE = trim($IBLOCK_CODE);
			if ($IBLOCK_CODE !== '')
			{
				$iblockCode = $IBLOCK_CODE;
			}
		}

		$runtime = [];
		$filter = [];
		if ($iblockCode && $iblockId)
		{
			$filter[] = [
				'LOGIC' => 'OR',
				'=IBLOCK.ID' => $iblockId,
				'=IBLOCK.CODE' => $iblockCode,
			];
		}
		elseif ($iblockCode)
		{
			$filter['=IBLOCK.CODE'] = $iblockCode;
		}
		elseif ($iblockId)
		{
			$filter['=IBLOCK.ID'] = $iblockId;
		}
		if (!is_int($ID))
		{
			$ID = (string)$ID;
			if (is_numeric($ID))
			{
				$ID = (int)$ID;
			}
		}
		if (is_int($ID))
		{
			$filter['=ID'] = $ID;
		}
		else
		{
			$ID = mb_strtoupper($ID);
			$connection = Main\Application::getConnection();
			if ($connection instanceof Main\DB\MysqlCommonConnection)
			{
				$filter['=CODE'] = $ID;
			}
			else
			{
				$filter['=UPPER_PROPERTY_CODE'] = $ID;
				$runtime[] = self::getUpperExpressionFields();
			}
			unset($connection);
		}

		$params = [
			'select' => ['*'],
			'filter' => $filter,
			'limit' => 1,
		];
		if (!empty($runtime))
		{
			$params['runtime'] = $runtime;
		}

		return new CIBlockPropertyResult(Iblock\PropertyTable::getList($params));
	}

	public static function GetPropertyArray($ID, $IBLOCK_ID, $bCached=true)
	{
		if (!is_int($ID) && !is_string($ID))
		{
			return false;
		}

		$iblockIdList = [];
		$iblockCodeList = [];

		if (is_array($IBLOCK_ID))
		{
			foreach ($IBLOCK_ID as $value)
			{
				if (is_numeric($value))
				{
					$value = (int)$value;
					if ($value > 0)
					{
						$iblockIdList[$value] = $value;
					}
				}
				elseif (is_string($value))
				{
					$value = trim($value);
					if ($value !== '')
					{
						$iblockCodeList[$value] = $value;
					}
				}
			}
		}
		elseif (is_numeric($IBLOCK_ID))
		{
			$iblockId = (int)$IBLOCK_ID;
			if ($iblockId > 0)
			{
				$iblockIdList[$iblockId] = $iblockId;
			}
			unset($iblockId);
		}
		elseif (is_string($IBLOCK_ID))
		{
			$iblockCode = trim($IBLOCK_ID);
			if ($iblockCode !== '')
			{
				$iblockCodeList[$iblockCode] = $iblockCode;
			}
			unset($iblockCode);
		}
		$iblockIdList = array_values($iblockIdList);
		$iblockCodeList = array_values($iblockCodeList);

		$cacheId = $ID . '|' . implode(', ', $iblockIdList) . '|' . implode(', ', $iblockCodeList);

		global $IBLOCK_CACHE_PROPERTY;
		if ($bCached && isset($IBLOCK_CACHE_PROPERTY[$cacheId]))
		{
			return $IBLOCK_CACHE_PROPERTY[$cacheId];
		}

		$runtime = [];
		$filter = [];

		$iblockFilter = [];
		if (!empty($iblockIdList) && !empty($iblockCode))
		{
			$iblockFilter[] = [
				'LOGIC' => 'OR',
				'@ID' => $iblockIdList,
				'@CODE' => $iblockCodeList
			];
		}
		elseif (!empty($iblockIdList))
		{
			$iblockFilter['@ID'] = $iblockIdList;
		}
		elseif (!empty($iblockCodeList))
		{
			$iblockFilter['@CODE'] = $iblockCodeList;
		}
		if (!empty($iblockFilter))
		{
			$iblockIds = [];
			$iterator = Iblock\IblockTable::getList([
				'select' => [
					'ID',
				],
				'filter' => $iblockFilter,
				'cache' => [
					'ttl' => 86400,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$iblockId = (int)$row['ID'];
				$iblockIds[$iblockId] = $iblockId;
			}
			unset(
				$iblockId,
				$row,
				$iterator,
			);
			if (empty($iblockIds))
			{
				return false;
			}
			$filter['@IBLOCK_ID'] = $iblockIds;
			unset($iblockIds);
		}
		unset($iblockFilter);

		$propertyId = null;
		$propertyCode = null;
		$propertyFullCode = null;
		$existsValuePostfix = false;
		if (is_int($ID))
		{
			$propertyId = $ID;
		}
		else
		{
			$upperId = mb_strtoupper($ID);
			$preparedId = [];
			if (preg_match('/^([A-Za-z0-9_]+)(_VALUE)$/', $upperId, $preparedId))
			{
				$existsValuePostfix = true;
				$value = (int)$preparedId[1];
				if ($value > 0)
				{
					$propertyId = $value;
				}
				else
				{
					$propertyCode = $preparedId[1];
					$propertyFullCode = $preparedId[0];
				}
			}
			else
			{
				$value = (int)$upperId;
				if ($value > 0)
				{
					$propertyId = $value;
				}
				else
				{
					$propertyCode = $upperId;
				}
			}
			unset(
				$value,
				$preparedId,
				$upperId,
			);
		}

		if ($propertyId !== null)
		{
			$filter['=ID'] = $propertyId;
		}
		else
		{
			$connection = Main\Application::getConnection();
			if ($connection instanceof Main\DB\MysqlCommonConnection)
			{
				$fieldName = '=CODE';
			}
			else
			{
				$fieldName = '=UPPER_PROPERTY_CODE';
				$runtime[] = self::getUpperExpressionFields();
			}
			unset($connection);
			if ($existsValuePostfix)
			{
				$filter[] = [
					'LOGIC' => 'OR',
					[
						$fieldName => $propertyFullCode,
						'!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_LIST,
					],
					[
						$fieldName => $propertyCode,
						'=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_LIST,
					]
				];
			}
			else
			{
				$filter[$fieldName] = $propertyCode;
			}
		}

		$params = [
			'select' => ['*'],
			'filter' => $filter,
			'cache' => [
				'ttl' => 86400,
			],
		];
		if (!empty($runtime))
		{
			$params['runtime'] = $runtime;
		}

		$iterator = Iblock\PropertyTable::getList($params);
		$propertyRow = $iterator->fetch();
		if (!empty($propertyRow))
		{
			unset($propertyRow['USER_TYPE_SETTINGS_LIST']);
			if ($propertyRow['TIMESTAMP_X'] instanceof Main\Type\DateTime)
			{
				$propertyRow['TIMESTAMP_X'] = $propertyRow['TIMESTAMP_X']->format('Y-m-d H:i:s');
			}
			$propertyRow['ORIG_ID'] = $propertyRow['ID']; //it saves original (digital) id
			$propertyRow['IS_CODE_UNIQUE'] = true; //boolean check for global code uniquess
			$propertyRow['IS_VERSION_MIXED'] = false; //boolean check if varios versions of ibformation block properties

			while ($row = $iterator->fetch())
			{
				$propertyRow['IS_CODE_UNIQUE'] = false;
				if ($propertyRow['VERSION'] !== $row['VERSION'])
				{
					$propertyRow['IS_VERSION_MIXED'] = true;
				}
			}
			unset($row);

			if (
				$existsValuePostfix
				&& $propertyRow['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_LIST
				&& mb_strtoupper((string)$propertyRow['CODE']) === $propertyCode
			)
			{
				$propertyRow['ID'] = mb_substr($ID, 0, -6);
			}
			else
			{
				$propertyRow['ID'] = $ID;
			}
		}
		unset($iterator);

		$IBLOCK_CACHE_PROPERTY[$cacheId] = $propertyRow;

		return $propertyRow;
	}

	private static function getUpperExpressionFields(): Main\ORM\Fields\ExpressionField
	{
		return new Main\ORM\Fields\ExpressionField(
			'UPPER_PROPERTY_CODE',
			'UPPER(%s)',
			'CODE'
		);
	}

	public static function GetPropertyEnum($PROP_ID, $arOrder = array("SORT"=>"asc"), $arFilter = array())
	{
		global $DB;

		$strSqlSearch = "";
		if(is_array($arFilter))
		{
			foreach($arFilter as $key => $val)
			{
				$key = mb_strtoupper($key);
				switch($key)
				{
				case "ID":
					$strSqlSearch .= "AND (BPE.ID=".intval($val).")\n";
					break;
				case "IBLOCK_ID":
					$strSqlSearch .= "AND (BP.IBLOCK_ID=".intval($val).")\n";
					break;
				case "VALUE":
					$strSqlSearch .= "AND (BPE.VALUE LIKE '".$DB->ForSql($val)."')\n";
					break;
				case "EXTERNAL_ID":
				case "XML_ID":
					$strSqlSearch .= "AND (BPE.XML_ID LIKE '".$DB->ForSql($val)."')\n";
					break;
				}
			}
		}

		$arSqlOrder = array();
		if(is_array($arOrder))
		{
			foreach($arOrder as $by => $order)
			{
				$by = mb_strtolower($by);
				$order = mb_strtolower($order);
				if ($order!="asc")
					$order = "desc";

				if ($by == "value")
					$arSqlOrder["BPE.VALUE"] = "BPE.VALUE ".$order;
				elseif ($by == "id")
					$arSqlOrder["BPE.ID"] = "BPE.ID ".$order;
				elseif ($by == "external_id")
					$arSqlOrder["BPE.XML_ID"] = "BPE.XML_ID ".$order;
				elseif ($by == "xml_id")
					$arSqlOrder["BPE.XML_ID"] = "BPE.XML_ID ".$order;
				else
					$arSqlOrder["BPE.SORT"] = "BPE.SORT ".$order;
			}
		}

		if(empty($arSqlOrder))
			$strSqlOrder = "";
		else
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		return $DB->Query("
			SELECT BPE.*, BPE.XML_ID as EXTERNAL_ID
			FROM
				b_iblock_property_enum BPE
				INNER JOIN b_iblock_property BP ON BP.ID = BPE.PROPERTY_ID
			WHERE
			".(
				is_numeric(mb_substr($PROP_ID, 0, 1))?
				"BP.ID = ".(int)$PROP_ID:
				"BP.CODE = '".$DB->ForSql($PROP_ID)."'"
			)."
			".$strSqlSearch."
			".$strSqlOrder."
		");
	}

	function UpdateEnum($ID, $arVALUES, $bForceDelete = true)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intval($ID);

		if(!is_array($arVALUES) || (empty($arVALUES) && $bForceDelete))
		{
			CIBlockPropertyEnum::DeleteByPropertyID($ID);
			return true;
		}

		$ar_XML_ID = array();
		$db_res = $this->GetPropertyEnum($ID);
		while($res = $db_res->Fetch())
		{
			$ar_XML_ID[rtrim($res["XML_ID"], " ")] = $res["ID"];
		}

		$sqlWhere = "";
		if(!$bForceDelete)
		{
			$rsProp = CIBlockProperty::GetByID($ID);
			if($arProp = $rsProp->Fetch())
			{
				if($arProp["VERSION"] == 1)
					$sqlWhere = "AND NOT EXISTS (
						SELECT *
						FROM b_iblock_element_property
						WHERE b_iblock_element_property.IBLOCK_PROPERTY_ID = b_iblock_property_enum.PROPERTY_ID
						AND b_iblock_element_property.VALUE_ENUM = b_iblock_property_enum.ID
					)";
				elseif($arProp["MULTIPLE"] == "N")
					$sqlWhere = "AND NOT EXISTS (
						SELECT *
						FROM b_iblock_element_prop_s".$arProp["IBLOCK_ID"]."
						WHERE b_iblock_element_prop_s".$arProp["IBLOCK_ID"].".PROPERTY_".$arProp["ID"]." = b_iblock_property_enum.ID
					)";
				else
					$sqlWhere = "AND NOT EXISTS (
						SELECT *
						FROM b_iblock_element_prop_m".$arProp["IBLOCK_ID"]."
						WHERE b_iblock_element_prop_m".$arProp["IBLOCK_ID"].".IBLOCK_PROPERTY_ID = b_iblock_property_enum.PROPERTY_ID
						AND b_iblock_element_prop_m".$arProp["IBLOCK_ID"].".VALUE_ENUM = b_iblock_property_enum.ID
					)";
			}
		}

		$db_res = $this->GetPropertyEnum($ID);
		while($res = $db_res->Fetch())
		{
			$VALUE = $arVALUES[$res["ID"]];
			$VAL = is_array($VALUE)? $VALUE["VALUE"]: $VALUE;
			UnSet($arVALUES[$res["ID"]]);

			if((string)$VAL == '')
			{
				unset($ar_XML_ID[rtrim($res["XML_ID"], " ")]);

				$strSql = "
					DELETE FROM b_iblock_property_enum
					WHERE ID=".$res["ID"]."
					".$sqlWhere."
				";

				$DB->Query($strSql);
			}
			else
			{
				$DEF = "";
				$SORT = 0;
				$XML_ID = "";
				if(is_array($VALUE))
				{
					if(array_key_exists("DEF", $VALUE))
						$DEF = $VALUE["DEF"]=="Y"? "Y": "N";

					if(array_key_exists("SORT", $VALUE))
						$SORT = intval($VALUE["SORT"]);
					if($SORT < 0)
						$SORT = 0;

					if(array_key_exists("XML_ID", $VALUE) && mb_strlen($VALUE["XML_ID"]))
						$XML_ID = mb_substr(rtrim($VALUE["XML_ID"], " "), 0, 200);
					elseif(array_key_exists("EXTERNAL_ID", $VALUE) && mb_strlen($VALUE["EXTERNAL_ID"]))
						$XML_ID = mb_substr(rtrim($VALUE["EXTERNAL_ID"], " "), 0, 200);
				}

				if($XML_ID)
				{
					unset($ar_XML_ID[mb_strtolower(rtrim($res["XML_ID"], " "))]);
					if (isset($ar_XML_ID[mb_strtolower($XML_ID)]))
						$XML_ID = md5(uniqid(""));
					$ar_XML_ID[mb_strtolower($XML_ID)] = $res["ID"];
				}

				$strSql = "
					UPDATE b_iblock_property_enum
					SET
						".($DEF? " DEF = '".$DEF."', ":"")."
						".($SORT? " SORT = ".$SORT.", ":"")."
						".($XML_ID? " XML_ID = '".$DB->ForSQL($XML_ID, 200)."', ":"")."
						VALUE = '".$DB->ForSQL($VAL, 255)."'
					WHERE
						ID = ".$res["ID"]."
				";

				$DB->Query($strSql);
			}
		}

		foreach($arVALUES as $id => $VALUE)
		{
			$VAL = is_array($VALUE)? $VALUE["VALUE"]: $VALUE;
			if((string)$id <> '' && (string)$VAL <> '')
			{
				$DEF = "";
				$SORT = 0;
				$XML_ID = "";
				if(is_array($VALUE))
				{
					if(array_key_exists("DEF", $VALUE))
						$DEF = $VALUE["DEF"]=="Y"? "Y": "N";

					if(array_key_exists("SORT", $VALUE))
						$SORT = intval($VALUE["SORT"]);
					if($SORT < 0)
						$SORT = 0;

					if(array_key_exists("XML_ID", $VALUE) && mb_strlen($VALUE["XML_ID"]))
						$XML_ID = mb_substr(rtrim($VALUE["XML_ID"], " "), 0, 200);
					elseif(array_key_exists("EXTERNAL_ID", $VALUE) && mb_strlen($VALUE["EXTERNAL_ID"]))
						$XML_ID = mb_substr(rtrim($VALUE["EXTERNAL_ID"], " "), 0, 200);
				}

				if($XML_ID)
				{
					if (isset($ar_XML_ID[mb_strtolower($XML_ID)]))
						$XML_ID = md5(uniqid("", true));
				}
				else
				{
					$XML_ID = md5(uniqid("", true));
				}
				$ar_XML_ID[mb_strtolower($XML_ID)] = 0;

				$strSql = "
					INSERT INTO b_iblock_property_enum
					(
						PROPERTY_ID
						".($DEF? ",DEF": "")."
						".($SORT? ",SORT": "")."
						,VALUE
						,XML_ID
					) VALUES (
						".$ID."
						".($DEF? ",'".$DEF."'": "")."
						".($SORT? ",".$SORT."": "")."
						,'".$DB->ForSQL($VAL, 255)."'
						,'".$DB->ForSQL($XML_ID, 200)."'
					)
				";
				$DB->Query($strSql);
			}
		}

		if(CACHED_b_iblock_property_enum !== false)
			$CACHE_MANAGER->CleanDir("b_iblock_property_enum");

		if (defined("BX_COMP_MANAGED_CACHE"))
			$CACHE_MANAGER->ClearByTag("iblock_property_enum_".$ID);

		Iblock\PropertyEnumerationTable::cleanCache();

		return true;
	}

	public static function GetUserType($USER_TYPE = false)
	{
		static $CACHE = null;

		if(!isset($CACHE))
		{
			$CACHE = array();
			foreach(GetModuleEvents("iblock", "OnIBlockPropertyBuildList", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if (is_array($res) && array_key_exists("USER_TYPE", $res))
				{
					$CACHE[$res["USER_TYPE"]] = $res;
				}
			}
		}

		if($USER_TYPE !== false)
		{
			if(array_key_exists($USER_TYPE, $CACHE))
				return $CACHE[$USER_TYPE];
			else
				return array();
		}
		else
		{
			return $CACHE;
		}
	}

	function FormatUpdateError($ID, $CODE)
	{
		return GetMessage("IBLOCK_PROPERTY_CHANGE_ERROR",array("#ID#"=>$ID,"#CODE#"=>$CODE));
	}

	function FormatNotFoundError($ID)
	{
		return GetMessage("IBLOCK_PROPERTY_NOT_FOUND",array("#ID#"=>$ID));
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyDateTime::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _DateTime_GetUserTypeDescription()
	{
		return CIBlockPropertyDateTime::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyDate::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _Date_GetUserTypeDescription()
	{
		return CIBlockPropertyDate::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyXmlID::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _XmlID_GetUserTypeDescription()
	{
		return CIBlockPropertyXmlID::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyFileMan::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _FileMan_GetUserTypeDescription()
	{
		return CIBlockPropertyFileMan::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyHTML::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _HTML_GetUserTypeDescription()
	{
		return CIBlockPropertyHTML::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyElementList::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _ElementList_GetUserTypeDescription()
	{
		return CIBlockPropertyElementList::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertySequence::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _Sequence_GetUserTypeDescription()
	{
		return CIBlockPropertySequence::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertyElementAutoComplete::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _ElementAutoComplete_GetUserTypeDescription()
	{
		return CIBlockPropertyElementAutoComplete::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertySKU::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _SKU_GetUserTypeDescription()
	{
		return CIBlockPropertySKU::GetUserTypeDescription();
	}

	/**
	 * @deprecated deprecated since iblock 17.0.9
	 * @see \CIBlockPropertySectionAutoComplete::GetUserTypeDescription()
	 *
	 * @return array
	 */
	public static function _SectionAutoComplete_GetUserTypeDescription()
	{
		return CIBlockPropertySectionAutoComplete::GetUserTypeDescription();
	}

	function _Update($ID, $arFields, $bCheckDescription = false)
	{
		return false;
	}

	public static function DropColumnSQL($strTable, $arColumns)
	{
		return array();
	}

	function _Add($ID, $arFields)
	{
		return false;
	}

	public function getLastError(): string
	{
		return $this->LAST_ERROR;
	}

	/**
	 * Prepare default value for custom property type.
	 *
	 * @param mixed $value ConvertToDB method result for custom property type.
	 * @return string|bool|int|float
	 */
	protected function prepareDefaultValue(mixed $value): string|bool|int|float
	{
		$result = false;
		if (
			is_array($value)
			&& isset($value['VALUE'])
		)
		{
			$defaultValue = $value['VALUE'];
			if (
				(is_string($defaultValue) && $defaultValue !== '')
				|| is_int($defaultValue)
				|| is_float($defaultValue)
				|| ($defaultValue === true)
			)
			{
				$result = $defaultValue;
			}
		}

		return $result;
	}
}
