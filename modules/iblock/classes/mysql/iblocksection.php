<?php
use Bitrix\Iblock;

class CIBlockSection extends CAllIBlockSection
{
	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool $bIncCnt
	 * @param array $arSelect
	 * @param array|false $arNavStartParams
	 * @return CIBlockResult
	 */
	public static function GetList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $bIncCnt = false, $arSelect = array(), $arNavStartParams=false)
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		if (!is_array($arOrder))
			$arOrder = array();
		if (!is_array($arFilter))
			$arFilter = array();
		if (!is_array($arSelect))
			$arSelect = array();
		foreach (array_keys($arSelect) as $index)
		{
			if (!is_string($arSelect[$index]))
			{
				unset($arSelect[$index]);
			}
		}

		$iblockId = null;
		if (isset($arFilter['IBLOCK_ID']) && is_numeric($arFilter['IBLOCK_ID']))
		{
			$iblockId = (int)$arFilter['IBLOCK_ID'];
			if ($iblockId <= 0)
			{
				$iblockId = null;
			}
		}
		$iblockFilterExist = $iblockId !== null;

		$needUfManager = self::checkUfFields($arOrder, $arFilter, $arSelect);

		if ($needUfManager && !$iblockFilterExist)
		{
			trigger_error("Parameters of the CIBlockSection::GetList contains user fields, but arFilter has no IBLOCK_ID field.", E_USER_WARNING);
		}
		if ($needUfManager)
		{
			$userFieldsSelect = $arSelect;
			if (!in_array("UF_*", $userFieldsSelect))
			{
				if (!empty($arOrder) && is_array($arOrder))
				{
					foreach (array_keys($arOrder) as $userFieldName)
					{
						if (!in_array($userFieldName, $userFieldsSelect))
							$userFieldsSelect[] = $userFieldName;
					}
					unset($userFieldName);
				}
			}

			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION", "BS.ID");
			$obUserFieldsSql->SetSelect($userFieldsSelect);
			$obUserFieldsSql->SetFilter($arFilter);
			$obUserFieldsSql->SetOrder($arOrder);

			unset($userFieldsSelect);
		}

		$useSubsections = !(isset($arFilter["ELEMENT_SUBSECTIONS"]) && $arFilter["ELEMENT_SUBSECTIONS"]=="N");
		$allElements = (isset($arFilter['CNT_ALL']) && $arFilter['CNT_ALL'] == 'Y');
		$activeElements = (isset($arFilter['CNT_ACTIVE']) && $arFilter['CNT_ACTIVE'] == 'Y');

		$arJoinProps = array();
		$bJoinFlatProp = false;

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);

		$bCheckPermissions = !(isset($arFilter["CHECK_PERMISSIONS"]) && $arFilter["CHECK_PERMISSIONS"] === "N");
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
		{
			$arFilter["MIN_PERMISSION"] ??= CIBlockRights::PUBLIC_READ;
			$arSqlSearch[] = self::_check_rights_sql($arFilter["MIN_PERMISSION"], $permissionsBy);
		}
		unset($permissionsBy);

		if (!empty($arFilter["PROPERTY"]) && is_array($arFilter["PROPERTY"]))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, CIBlock::_MergeIBArrays($arFilter["IBLOCK_ID"], $arFilter["IBLOCK_CODE"])))
				{
					$bSave = false;
					$separateSingle = (
						$db_prop["VERSION"] == Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE
						&& $db_prop["MULTIPLE"] == "N"
					);

					$iPropCnt = -1;
					if (isset($arJoinProps[$db_prop["ID"]]))
					{
						$iPropCnt = $arJoinProps[$db_prop["ID"]]["iPropCnt"];
					}
					elseif(!$separateSingle)
					{
						$bSave = true;
						$iPropCnt = count($arJoinProps);
					}

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($separateSingle)
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
						{
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE_NUM", $propVAL, "number", $cOperationType);
						}
					}
					else
					{
						if($separateSingle)
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
						{
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE", $propVAL, "string", $cOperationType);
						}
					}

					if($r != '')
					{
						if($bSave)
						{
							$db_prop["iPropCnt"] = $iPropCnt;
							$arJoinProps[$db_prop["ID"]] = $db_prop;
						}
						$arSqlSearch[] = $r;
					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if($r <> '')
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		if(isset($obUserFieldsSql))
		{
			$r = $obUserFieldsSql->GetFilter();
			if($r <> '')
				$strSqlSearch .= "\n\t\t\t\tAND (".$r.") ";
		}

		$strProp1 = "";
		foreach($arJoinProps as $propID=>$db_prop)
		{
			if($db_prop["VERSION"]==2)
				$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
			else
				$strTable = "b_iblock_element_property";
			$i = $db_prop["iPropCnt"];
			$strProp1 .= "
				LEFT JOIN b_iblock_property FP".$i." ON FP".$i.".IBLOCK_ID=B.ID AND
				".((int)$propID>0?" FP".$i.".ID=".(int)$propID." ":" FP".$i.".CODE='".$DB->ForSQL($propID, 200)."' ")."
				LEFT JOIN ".$strTable." FPV".$i." ON FP".$i.".ID=FPV".$i.".IBLOCK_PROPERTY_ID AND FPV".$i.".IBLOCK_ELEMENT_ID=BE.ID ";
		}
		if($bJoinFlatProp)
			$strProp1 .= "
				LEFT JOIN b_iblock_element_prop_s".$bJoinFlatProp." FPS ON FPS.IBLOCK_ELEMENT_ID = BE.ID
			";

		$arFields = array(
			"ID" => "BS.ID",
			"CODE" => "BS.CODE",
			"XML_ID" => "BS.XML_ID",
			"EXTERNAL_ID" => "BS.XML_ID",
			"IBLOCK_ID" => "BS.IBLOCK_ID",
			"IBLOCK_SECTION_ID" => "BS.IBLOCK_SECTION_ID",
			"TIMESTAMP_X" => $DB->DateToCharFunction("BS.TIMESTAMP_X"),
			"TIMESTAMP_X_UNIX" => 'UNIX_TIMESTAMP(BS.TIMESTAMP_X)',
			"SORT" => "BS.SORT",
			"NAME" => "BS.NAME",
			"ACTIVE" => "BS.ACTIVE",
			"GLOBAL_ACTIVE" => "BS.GLOBAL_ACTIVE",
			"PICTURE" => "BS.PICTURE",
			"DESCRIPTION" => "BS.DESCRIPTION",
			"DESCRIPTION_TYPE" => "BS.DESCRIPTION_TYPE",
			"LEFT_MARGIN" => "BS.LEFT_MARGIN",
			"RIGHT_MARGIN" => "BS.RIGHT_MARGIN",
			"DEPTH_LEVEL" => "BS.DEPTH_LEVEL",
			"SEARCHABLE_CONTENT" => "BS.SEARCHABLE_CONTENT",
			"MODIFIED_BY" => "BS.MODIFIED_BY",
			"DATE_CREATE" => $DB->DateToCharFunction("BS.DATE_CREATE"),
			"DATE_CREATE_UNIX" => 'UNIX_TIMESTAMP(BS.DATE_CREATE)',
			"CREATED_BY" => "BS.CREATED_BY",
			"DETAIL_PICTURE" => "BS.DETAIL_PICTURE",
			"TMP_ID" => "BS.TMP_ID",

			"LIST_PAGE_URL" => "B.LIST_PAGE_URL",
			"SECTION_PAGE_URL" => "B.SECTION_PAGE_URL",
			"IBLOCK_TYPE_ID" => "B.IBLOCK_TYPE_ID",
			"IBLOCK_CODE" => "B.CODE",
			"IBLOCK_EXTERNAL_ID" => "B.XML_ID",
			"SOCNET_GROUP_ID" => "BS.SOCNET_GROUP_ID",
		);

		$arSqlSelect = array();
		foreach($arSelect as $field)
		{
			$field = mb_strtoupper($field);
			if(isset($arFields[$field]))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}

		if(isset($arSqlSelect['DESCRIPTION']))
			$arSqlSelect["DESCRIPTION_TYPE"] = $arFields["DESCRIPTION_TYPE"]." AS DESCRIPTION_TYPE";

		if(isset($arSqlSelect['LIST_PAGE_URL']) || isset($arSqlSelect['SECTION_PAGE_URL']))
		{
			$arSqlSelect["ID"] = $arFields["ID"]." AS ID";
			$arSqlSelect["CODE"] = $arFields["CODE"]." AS CODE";
			$arSqlSelect["EXTERNAL_ID"] = $arFields["EXTERNAL_ID"]." AS EXTERNAL_ID";
			$arSqlSelect["IBLOCK_TYPE_ID"] = $arFields["IBLOCK_TYPE_ID"]." AS IBLOCK_TYPE_ID";
			$arSqlSelect["IBLOCK_ID"] = $arFields["IBLOCK_ID"]." AS IBLOCK_ID";
			$arSqlSelect["IBLOCK_CODE"] = $arFields["IBLOCK_CODE"]." AS IBLOCK_CODE";
			$arSqlSelect["IBLOCK_EXTERNAL_ID"] = $arFields["IBLOCK_EXTERNAL_ID"]." AS IBLOCK_EXTERNAL_ID";
			$arSqlSelect["GLOBAL_ACTIVE"] = $arFields["GLOBAL_ACTIVE"]." AS GLOBAL_ACTIVE";
			//$arr["LANG_DIR"],
		}

		$additionalSelect = array();
		$arSqlOrder = array();
		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			if(isset($arSqlOrder[$by]))
				continue;
			$order = mb_strtolower($order);
			if($order!="asc")
				$order = "desc";

			switch ($by)
			{
				case "id":
					$arSqlOrder[$by] = " BS.ID ".$order." ";
					$additionalSelect["ID"] = $arFields["ID"]." AS ID";
					break;
				case "section":
					$arSqlOrder[$by] = " BS.IBLOCK_SECTION_ID ".$order." ";
					$additionalSelect["IBLOCK_SECTION_ID"] = $arFields["IBLOCK_SECTION_ID"]." AS IBLOCK_SECTION_ID";
					break;
				case "name":
					$arSqlOrder[$by] = " BS.NAME ".$order." ";
					$additionalSelect["NAME"] = $arFields["NAME"]." AS NAME";
					break;
				case "code":
					$arSqlOrder[$by] = " BS.CODE ".$order." ";
					$additionalSelect["CODE"] = $arFields["CODE"]." AS CODE";
					break;
				case "external_id":
				case "xml_id":
					$arSqlOrder[$by] = " BS.XML_ID ".$order." ";
					$additionalSelect["XML_ID"] = $arFields["XML_ID"]." AS XML_ID";
					break;
				case "active":
					$arSqlOrder[$by] = " BS.ACTIVE ".$order." ";
					$additionalSelect["ACTIVE"] = $arFields["ACTIVE"]." AS ACTIVE";
					break;
				case "left_margin":
					$arSqlOrder[$by] = " BS.LEFT_MARGIN ".$order." ";
					$additionalSelect["LEFT_MARGIN"] = $arFields["LEFT_MARGIN"]." AS LEFT_MARGIN";
					break;
				case "depth_level":
					$arSqlOrder[$by] = " BS.DEPTH_LEVEL ".$order." ";
					$additionalSelect["DEPTH_LEVEL"] = $arFields["DEPTH_LEVEL"]." AS DEPTH_LEVEL";
					break;
				case "sort":
					$arSqlOrder[$by] = " BS.SORT ".$order." ";
					$additionalSelect["SORT"] = $arFields["SORT"]." AS SORT";
					break;
				case "created":
					$arSqlOrder[$by] = " BS.DATE_CREATE ".$order." ";
					$additionalSelect["DATE_CREATE"] = $arFields["DATE_CREATE"]." AS DATE_CREATE";
					break;
				case "created_by":
					$arSqlOrder[$by] = " BS.CREATED_BY ".$order." ";
					$additionalSelect["CREATED_BY"] = $arFields["CREATED_BY"]." AS CREATED_BY";
					break;
				case "modified_by":
					$arSqlOrder[$by] = " BS.MODIFIED_BY ".$order." ";
					$additionalSelect["MODIFIED_BY"] = $arFields["MODIFIED_BY"]." AS MODIFIED_BY";
					break;
				default:
					if ($bIncCnt && $by == "element_cnt")
					{
						$arSqlOrder[$by] = " ELEMENT_CNT ".$order." ";
					}
					elseif (isset($obUserFieldsSql) && $s = $obUserFieldsSql->GetOrder($by))
					{
						$arSqlOrder[$by] = " ".$s." ".$order." ";
					}
					else
					{
						$by = "timestamp_x";
						$arSqlOrder[$by] = " BS.TIMESTAMP_X ".$order." ";
						$additionalSelect["TIMESTAMP_X_SORT"] = "BS.TIMESTAMP_X AS TSX_TMP";
					}
			}
		}
		if (!empty($additionalSelect) && !empty($arSqlSelect))
		{
			foreach ($additionalSelect as $key => $value)
				$arSqlSelect[$key] = $value;
		}

		if(!empty($arSqlSelect))
			$sSelect = implode(",\n", $arSqlSelect);
		else
			$sSelect = "
				BS.*,
				B.LIST_PAGE_URL,
				B.SECTION_PAGE_URL,
				B.IBLOCK_TYPE_ID,
				B.CODE as IBLOCK_CODE,
				B.XML_ID as IBLOCK_EXTERNAL_ID,
				BS.XML_ID as EXTERNAL_ID,
				".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as TIMESTAMP_X,
				".$DB->DateToCharFunction("BS.DATE_CREATE")." as DATE_CREATE
			";

		if(!$bIncCnt)
		{
			$strSelect = $sSelect.(isset($obUserFieldsSql)? $obUserFieldsSql->GetSelect(): "");
			$strSql = "
				FROM b_iblock_section BS
					INNER JOIN b_iblock B ON BS.IBLOCK_ID = B.ID
					".(isset($obUserFieldsSql)? $obUserFieldsSql->GetJoin("BS.ID"): "")."
				".($strProp1 <> ''?
					"	INNER JOIN b_iblock_section BSTEMP ON BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
						LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID
						LEFT JOIN b_iblock_element BE ON (BSE.IBLOCK_ELEMENT_ID=BE.ID
							AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
							AND BE.IBLOCK_ID = BS.IBLOCK_ID
					".($allElements ?" OR BE.WF_NEW='Y' ":"").")
					".($activeElements ?
						" AND BE.ACTIVE='Y'
						AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL)
						AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
					:"").")
						".$strProp1." "
				:"")."
				WHERE 1=1
				".($strProp1 <> ''?
					"	AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
						AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN "
				:""
				)."
				".$strSqlSearch."
			";
			$strGroupBy = "";
		}
		else
		{
			$strSelect = $sSelect.",COUNT(DISTINCT BE.ID) as ELEMENT_CNT".(isset($obUserFieldsSql)? $obUserFieldsSql->GetSelect(): "");
			$strSql = "
				FROM b_iblock_section BS
					INNER JOIN b_iblock B ON BS.IBLOCK_ID = B.ID
					".(isset($obUserFieldsSql)? $obUserFieldsSql->GetJoin("BS.ID"): "")."
				".(!$useSubsections ?
					"	LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BS.ID "
				:
					"	INNER JOIN b_iblock_section BSTEMP ON BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
						LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID "
				)."
					LEFT JOIN b_iblock_element BE ON (BSE.IBLOCK_ELEMENT_ID=BE.ID
						AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
						AND BE.IBLOCK_ID = BS.IBLOCK_ID
				".($allElements ?" OR BE.WF_NEW='Y' ":"").")
				".($activeElements?
					" AND BE.ACTIVE='Y'
					AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL)
					AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
				:"").")
					".$strProp1."
				WHERE 1=1
				".(!$useSubsections
				?
					"	"
				:
					"	AND BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
						AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
						AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN
						".($activeElements ? "AND BSTEMP.GLOBAL_ACTIVE = 'Y'": "")."
					"
				)."
				".$strSqlSearch."
			";
			$strGroupBy = "GROUP BY BS.ID, B.ID";
		}

		if(!empty($arSqlOrder))
			$strSqlOrder = "\n\t\t\t\tORDER BY ".implode(", ", $arSqlOrder);
		else
			$strSqlOrder = "";

		if(is_array($arNavStartParams))
		{
			$nTopCount = (int)($arNavStartParams['nTopCount'] ?? 0);
			if($nTopCount > 0)
			{
				$res = $DB->Query($DB->TopSql(
					"SELECT DISTINCT ".$strSelect.$strSql.$strGroupBy.$strSqlOrder,
					$nTopCount
				));
				if($iblockFilterExist)
				{
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
				}
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(DISTINCT BS.ID) as C ".$strSql);
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				if($iblockFilterExist)
				{
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
				}
				$res->NavQuery("SELECT DISTINCT ".$strSelect.$strSql.$strGroupBy.$strSqlOrder, $res_cnt["C"], $arNavStartParams);
			}
		}
		else
		{
			$res = $DB->Query("SELECT DISTINCT ".$strSelect.$strSql.$strGroupBy.$strSqlOrder);
			if($iblockFilterExist)
			{
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
			}
		}

		$res = new CIBlockResult($res);
		if($iblockFilterExist)
		{
			$res->SetIBlockTag($arFilter["IBLOCK_ID"]);
		}

		return $res;
	}
	///////////////////////////////////////////////////////////////////
	// Update list of sections w/o any events
	///////////////////////////////////////////////////////////////////
	protected function UpdateList($arFields, $arFilter = array())
	{
		global $DB, $USER;

		$strUpdate = $DB->PrepareUpdate("b_iblock_section", $arFields, "iblock", false, "BS");
		if ($strUpdate == "")
			return false;

		if(isset($arFilter["IBLOCK_ID"]) && $arFilter["IBLOCK_ID"] > 0)
		{
			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION", "BS.ID");
			$obUserFieldsSql->SetFilter($arFilter);
		}
		else
		{
			foreach($arFilter as $key => $val)
			{
				$res = CIBlock::MkOperationFilter($key);
				if(preg_match("/^UF_/", $res["FIELD"]))
				{
					trigger_error("arFilter parameter of the CIBlockSection::GetList contains user fields, but has no IBLOCK_ID field.", E_USER_WARNING);
					break;
				}
			}
		}

		$arJoinProps = array();
		$bJoinFlatProp = false;

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
		{
			$arFilter["MIN_PERMISSION"] ??= CIBlockRights::PUBLIC_READ;
			$arSqlSearch[] = self::_check_rights_sql($arFilter["MIN_PERMISSION"], $permissionsBy);
		}
		unset($permissionsBy);

		if(array_key_exists("PROPERTY", $arFilter))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, CIBlock::_MergeIBArrays($arFilter["IBLOCK_ID"], $arFilter["IBLOCK_CODE"])))
				{

					$bSave = false;
					if(array_key_exists($db_prop["ID"], $arJoinProps))
						$iPropCnt = $arJoinProps[$db_prop["ID"]];
					elseif($db_prop["VERSION"]!=2 || $db_prop["MULTIPLE"]=="Y")
					{
						$bSave = true;
						$iPropCnt=count($arJoinProps);
					}

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE_NUM", $propVAL, "number", $cOperationType);
					}
					else
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE", $propVAL, "string", $cOperationType);
					}

					if($r <> '')
					{
						if($bSave)
						{
							$db_prop["iPropCnt"] = $iPropCnt;
							$arJoinProps[$db_prop["ID"]] = $db_prop;
						}
						$arSqlSearch[] = $r;
					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if($r <> '')
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		if(isset($obUserFieldsSql))
		{
			$r = $obUserFieldsSql->GetFilter();
			if($r <> '')
				$strSqlSearch .= "\n\t\t\t\tAND (".$r.") ";
		}

		$strProp1 = "";
		foreach($arJoinProps as $propID=>$db_prop)
		{
			if($db_prop["VERSION"]==2)
				$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
			else
				$strTable = "b_iblock_element_property";
			$i = $db_prop["iPropCnt"];
			$strProp1 .= "
				LEFT JOIN b_iblock_property FP".$i." ON FP".$i.".IBLOCK_ID=B.ID AND
				".((int)$propID>0?" FP".$i.".ID=".(int)$propID." ":" FP".$i.".CODE='".$DB->ForSQL($propID, 200)."' ")."
				LEFT JOIN ".$strTable." FPV".$i." ON FP".$i.".ID=FPV".$i.".IBLOCK_PROPERTY_ID AND FPV".$i.".IBLOCK_ELEMENT_ID=BE.ID ";
		}
		if($bJoinFlatProp)
			$strProp1 .= "
				LEFT JOIN b_iblock_element_prop_s".$bJoinFlatProp." FPS ON FPS.IBLOCK_ELEMENT_ID = BE.ID
			";

		$strSql = "
			UPDATE
			b_iblock_section BS
				INNER JOIN b_iblock B ON BS.IBLOCK_ID = B.ID
				".(isset($obUserFieldsSql)? $obUserFieldsSql->GetJoin("BS.ID"): "")."
			".($strProp1 <> ''?
				"	INNER JOIN b_iblock_section BSTEMP ON BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
					LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID
					LEFT JOIN b_iblock_element BE ON (BSE.IBLOCK_ELEMENT_ID=BE.ID
						AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
						AND BE.IBLOCK_ID = BS.IBLOCK_ID
				".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW='Y' ":"").")
				".($arFilter["CNT_ACTIVE"]=="Y"?
					" AND BE.ACTIVE='Y'
					AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL)
					AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
				:"").")
					".$strProp1." "
			:"")."
			SET ".$strUpdate."
			WHERE 1=1
			".($strProp1 <> ''?
				"	AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
					AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN "
			:""
			)."
			".$strSqlSearch."
		";

		return $DB->Query($strSql);
	}

	/**
	 * @param array $order
	 * @param array $filter
	 * @param array $select
	 * @return bool
	 */
	private static function checkUfFields(array $order, array $filter, array $select): bool
	{
		$result = false;

		$parsed = array();
		if (!empty($select))
		{
			if (in_array('UF_*', $select))
			{
				$result = true;
			}
			else
			{
				foreach ($select as $field)
				{
					if (preg_match('/^UF_/', $field, $parsed))
					{
						$result = true;
						break;
					}
				}
				unset($field);
			}
		}
		if (!$result && !empty($filter))
		{
			$filter = array_keys($filter);
			foreach ($filter as $key)
			{
				$field = CIBlock::MkOperationFilter($key);
				if (preg_match('/^UF_/', $field['FIELD'], $parsed))
				{
					$result = true;
					break;
				}
			}
			unset($field, $key);
		}
		if (!$result && !empty($order))
		{
			$order = array_keys($order);
			foreach ($order as $field)
			{
				if (preg_match('/^UF_/', $field, $parsed))
				{
					$result = true;
					break;
				}
			}
			unset($field);
		}
		unset($parced);

		return $result;
	}
}
