<?php

IncludeModuleLangFile(__FILE__);

class CAllTicketDictionary
{
	public static function GetDefault($type, $siteID=SITE_ID)
	{
		if ($siteID=="all")
		{
			$siteID = "";
		}
		$arFilter = array("DEFAULT" => "Y", "TYPE" => $type, "SITE" => $siteID);
		$rs = CTicketDictionary::GetList("s_dropdown", '', $arFilter);
		$ar = $rs->Fetch();
		return $ar["ID"];
	}

	public static function GetNextSort($typeID)
	{
		global $DB;
		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_ticket_dictionary WHERE C_TYPE='".$DB->ForSql($typeID,5)."'";
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	public static function GetDropDown($type="C", $siteID=false, $sla_id=false)
	{
		global $DB;
		if ($siteID==false || $siteID=="all")
		{
			$siteID = "";
		}
		$arFilter = array("TYPE" => $type, "SITE" => $siteID);
		$rs = CTicketDictionary::GetList("s_dropdown", '', $arFilter);
		
		$oldFunctionality = COption::GetOptionString( "support", "SUPPORT_OLD_FUNCTIONALITY", "Y" );
		if( intval($sla_id) <= 0 || $oldFunctionality != "Y" || ( $type != "C" && $type!="K" && $type!="M" ) ) return $rs;
		
		switch($type)
		{
			case "C": $strSql = "SELECT CATEGORY_ID as DID FROM b_ticket_sla_2_category WHERE SLA_ID=" . intval($sla_id); break;
			case "K": $strSql = "SELECT CRITICALITY_ID as DID FROM b_ticket_sla_2_criticality WHERE SLA_ID=" . intval($sla_id); break;
			case "M": $strSql = "SELECT MARK_ID as DID FROM b_ticket_sla_2_mark WHERE SLA_ID=" . intval($sla_id); break;
		}
		$r = $DB->Query($strSql);
		while( $a = $r->Fetch() ) $arDID[] = $a["DID"];
		$arRecords = array();
		while( $ar = $rs->Fetch() ) if( is_array( $arDID ) && ( in_array( $ar["ID"], $arDID ) || in_array( 0,$arDID ) ) ) $arRecords[] = $ar;
		
		$rs = new CDBResult;
		$rs->InitFromArray($arRecords);
		
		return $rs;
	}

	public static function GetDropDownArray($siteID = false, $SLA_ID = false, $arUnsetType = Array("F"))
	{
		//M, C, K, S, SR, D, F
		global $DB;

		if ($siteID == false || $siteID == "all")
			$siteID = "";

		$arFilter = Array("SITE" => $siteID);

		$arReturn = Array();
		$rs = CTicketDictionary::GetList("s_dropdown", '', $arFilter);
		while ($ar = $rs->Fetch())
		{
			if (in_array($ar["C_TYPE"], $arUnsetType))
				continue;

			$arReturn[$ar["C_TYPE"]][$ar["ID"]] = $ar;
		}
		
		$oldFunctionality = COption::GetOptionString( "support", "SUPPORT_OLD_FUNCTIONALITY", "Y" );
		if( intval($SLA_ID) > 0 && $oldFunctionality == "Y" )
		{
			$SLA_ID = intval($SLA_ID);

			$strSql = "SELECT 'M' as C_TYPE, SLA_ID, MARK_ID as DIC_ID FROM b_ticket_sla_2_mark WHERE SLA_ID = ".$SLA_ID."
						UNION ALL
						SELECT 'K' as C_TYPE, SLA_ID, CRITICALITY_ID as DIC_ID FROM b_ticket_sla_2_criticality WHERE SLA_ID = ".$SLA_ID."
						UNION ALL
						SELECT 'C' as C_TYPE, SLA_ID, CATEGORY_ID as DIC_ID FROM b_ticket_sla_2_category WHERE SLA_ID = ".$SLA_ID;

			$r = $DB->Query($strSql);

			$arUnset = Array();
			while ($ar = $r->Fetch())
			{
				if ($ar["DIC_ID"] == 0)
					continue;
				else
					$arUnset[$ar["C_TYPE"]][] = $ar["DIC_ID"];
			}

			if (!empty($arUnset) && !empty($arReturn))
			{
				foreach ($arReturn as $type => $arID)
				{
					if (!array_key_exists($type, $arUnset))
						continue;

					$arID = array_keys($arID);
					$arID = array_diff($arID, $arUnset[$type]);
					foreach ($arID as $val)
						unset($arReturn[$type][$val]);
				}
			}
		}
		
		return $arReturn;
	}

	// get array of languages related to contract
	public static function GetSiteArray($DICTIONARY_ID)
	{
		global $DB;
		$DICTIONARY_ID = intval($DICTIONARY_ID);
		if ($DICTIONARY_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT
				DS.SITE_ID
			FROM
				b_ticket_dictionary_2_site DS
			WHERE
				DS.DICTIONARY_ID = $DICTIONARY_ID
			";

		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["SITE_ID"];
		return $arrRes;
	}

	public static function GetSiteArrayForAllDictionaries()
	{
		static $GetSiteArrayForAllDictCache;
		if(is_array($GetSiteArrayForAllDictCache))
		{
			return $GetSiteArrayForAllDictCache;
		}

		global $DB;
		$GetSiteArrayForAllDictCache = array();
		$strSql = "
			SELECT
				DS.SITE_ID,
				DS.DICTIONARY_ID
			FROM
				b_ticket_dictionary_2_site DS
			";
		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			$GetSiteArrayForAllDictCache[$ar["DICTIONARY_ID"]][] = $ar["SITE_ID"];
		}
		return $GetSiteArrayForAllDictCache;
	}

	public static function GetTypeList()
	{
		$arr = array(
			"reference"=>array(
				GetMessage("SUP_CATEGORY"),
				GetMessage("SUP_CRITICALITY"),
				GetMessage("SUP_STATUS"),
				GetMessage("SUP_MARK"),
				GetMessage("SUP_FUA"),
				GetMessage("SUP_SOURCE"),
				GetMessage("SUP_DIFFICULTY")
				),
			"reference_id"=>array(
				"C",
				"K",
				"S",
				"M",
				"F",
				"SR",
				"D")
			);
		return $arr;
	}

	public static function GetTypeNameByID($id)
	{
		$arr = CTicketDictionary::GetTypeList();
		$KEY = array_search($id, $arr["reference_id"]);
		return $arr["reference"][$KEY];
	}

	public static function GetByID($id)
	{
		global $DB;
		$id = intval($id);
		if ($id<=0)
		{
			return;
		}
		$res = CTicketDictionary::GetList('', '', array("ID" => $id));
		return $res;
	}

	public static function GetBySID($sid, $type, $siteID=SITE_ID)
	{
		$rs = CTicketDictionary::GetList('', '', array("SITE_ID"=>$siteID, "TYPE"=>$type, "SID"=>$sid));
		return $rs;
	}

	public static function Delete($id, $CHECK_RIGHTS="Y")
	{
		global $DB, $APPLICATION;
		$id = intval($id);
		if ($id<=0)
		{
			return;
		}
		$bAdmin = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
		}
		if ($bAdmin=="Y")
		{
			$DB->Query("DELETE FROM b_ticket_dictionary WHERE ID='$id'");
			$DB->Query('DELETE FROM b_ticket_dictionary_2_site WHERE DICTIONARY_ID=' . $id);
		}
	}

	public static function CheckFields($arFields, $id = false)
	{
		$arMsg = Array();

		if ( $id ===false && !(array_key_exists('NAME', $arFields) && $arFields['NAME'] <> '') )
		{
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("SUP_FORGOT_NAME"));
		}

		if ($id !== false)
		{
			$rs = CTicketDictionary::GetByID($id);
			if (!$rs->Fetch())
			{
				$arMsg[] = array("id"=>"ID", "text"=> GetMessage("SUP_UNKNOWN_ID", array('#ID#' => $id)));
			}
		}

		if ( array_key_exists('SID', $arFields) && preg_match("#[^A-Za-z_0-9]#", $arFields['SID']) )
		{
			$arMsg[] = array("id"=>"SID", "text"=> GetMessage("SUP_INCORRECT_SID"));
		}
		elseif (
				$arFields['SID'] <> '' && array_key_exists('arrSITE', $arFields) &&
				is_array($arFields['arrSITE']) && count($arFields['arrSITE']) > 0
			)
		{
			$arFilter = array(
				"TYPE"	=> $arFields['C_TYPE'],
				"SID"	=> $arFields['SID'],
				"SITE"	=> $arFields['arrSITE'],
			);
			if (intval($id) > 0)
			{
				$arFilter['ID'] = '~'.intval($id);
			}

			$z = CTicketDictionary::GetList('', '', $arFilter);
			if ($zr = $z->Fetch())
			{
				$arMsg[] = array(
							"id"=>"SID",
							"text"=> GetMessage(
									'SUP_SID_ALREADY_IN_USE',
									array(
										'#TYPE#' => CTicketDictionary::GetTypeNameByID($arFields['C_TYPE']),
										'#LANG#' => $zr['LID'] <> ''? $zr['LID']: $zr['SITE_ID'],
										'#RECORD_ID#' => $zr['ID'],
									)
							)
					);
			}
		}

		if (count($arMsg) > 0)
		{
			$e = new CAdminException($arMsg);
			$GLOBALS['APPLICATION']->ThrowException($e);
			return false;
		}

		return true;
	}

	public static function Add($arFields)
	{
		global $DB;
		$DB->StartTransaction();
		if (!CTicketDictionary::CheckFields($arFields))
		{
			$DB->Rollback();
			return false;
		}

		CTicketDictionary::__CleanDefault($arFields);

		$id = intval($DB->Add('b_ticket_dictionary', $arFields));
		if ($id > 0)
		{
			CTicketDictionary::__SetSites($id, $arFields);
			$DB->Commit();
			return $id;
		}

		$DB->Rollback();
		$GLOBALS['APPLICATION']->ThrowException(GetMessage('SUP_ERROR_ADD_DICTONARY'));
		return false;
	}

	public static function Update($id, $arFields)
	{
		global $DB;
		$DB->StartTransaction();
		$id = intval($id);
		if (!CTicketDictionary::CheckFields($arFields, $id))
		{
			$DB->Rollback();
			return false;
		}

		CTicketDictionary::__CleanDefault($arFields);

		$strUpdate = $DB->PrepareUpdate('b_ticket_dictionary', $arFields);
		$rs = $DB->Query('UPDATE b_ticket_dictionary SET ' . $strUpdate . ' WHERE ID=' . $id);
		if ($rs->AffectedRowsCount() > 0)
		{
			CTicketDictionary::__SetSites($id, $arFields);
			$DB->Commit();
			return true;
		}

		$DB->Rollback();
		$GLOBALS['APPLICATION']->ThrowException(GetMessage('SUP_ERROR_UPDATE_DICTONARY'));
		return false;
	}

	public static function __CleanDefault(&$arFields)
	{
		if (
				array_key_exists('SET_AS_DEFAULT', $arFields) && $arFields['SET_AS_DEFAULT'] == 'Y' &&
				array_key_exists('arrSITE', $arFields) && array_key_exists('C_TYPE',  $arFields)
			)
		{
			global $DB;
			$arFilter = array(
				'TYPE'	=> $arFields['C_TYPE'],
				'SITE'	=> $arFields['arrSITE']
				);
			$z = CTicketDictionary::GetList('', '', $arFilter);
			while ($zr = $z->Fetch())
			{
				$DB->Update('b_ticket_dictionary', array('SET_AS_DEFAULT' => "'N'"), 'WHERE ID=' . $zr['ID'], '', false, false, false);
			}
		}
		elseif (array_key_exists('SET_AS_DEFAULT', $arFields))
		{
			$arFields['SET_AS_DEFAULT'] = 'N';
		}
	}

	public static function __SetSites($id, $arFields)
	{
		global $DB;
		if (!array_key_exists('arrSITE', $arFields))
		{
			return ;
		}
		$id = intval($id);
		$DB->Query('DELETE FROM b_ticket_dictionary_2_site WHERE DICTIONARY_ID=' . $id);
		if (is_array($arFields['arrSITE']) && count($arFields['arrSITE']) > 0)
		{
			foreach($arFields['arrSITE'] as $sid)
			{
				$strSql = "INSERT INTO b_ticket_dictionary_2_site (DICTIONARY_ID, SITE_ID) VALUES ($id, '".$DB->ForSql($sid, 2)."')";
				$DB->Query($strSql);
			}
		}
	}
}
