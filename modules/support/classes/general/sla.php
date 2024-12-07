<?php

IncludeModuleLangFile(__FILE__);

class CAllTicketSLA
{
	const SLA_SITE = 1;
	const SITE_SLA = 2;

	// add new or modify exist SLA
	public static function Set($arFields, $id, $checkRights=true)
	{
		global $DB, $USER, $APPLICATION;
		$id = intval($id);
		$table = "b_ticket_sla";
		$isDemo = $isSupportClient = $isSupportTeam = $isAdmin = $isAccess = $userID = null;
		CTicket::GetRoles($isDemo, $isSupportClient, $isSupportTeam, $isAdmin, $isAccess, $userID, $checkRights);
		if ($isAdmin)
		{
			$validDeadlineSource = !isset($arFields['DEADLINE_SOURCE']) || in_array($arFields['DEADLINE_SOURCE'], array('', 'DATE_CREATE'), true);

			if (CTicket::CheckFields($arFields, $id, array("NAME","TIMETABLE_ID")) && $validDeadlineSource)
			{
				$arFields_i = CTicket::PrepareFields($arFields, $table, $id);
				if (intval($id)>0)
				{
					$DB->Update($table, $arFields_i, "WHERE ID=".intval($id));
				}
				else
				{
					$id = $DB->Insert($table, $arFields_i);
				}

				if (intval($id)>0)
				{
					if (is_set($arFields, "arGROUPS"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_user_group WHERE SLA_ID = $id");
						if (is_array($arFields["arGROUPS"]) && count($arFields["arGROUPS"])>0)
						{
							foreach($arFields["arGROUPS"] as $groupID)
							{
								$groupID = intval($groupID);
								if ($groupID>0)
								{
									$strSql = "INSERT INTO b_ticket_sla_2_user_group (SLA_ID, GROUP_ID) VALUES ($id, $groupID)";
									$DB->Query($strSql);
								}
							}
						}
					}

					if (is_set($arFields, "arSITES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_site WHERE SLA_ID = $id");
						if (is_array($arFields["arSITES"]) && count($arFields["arSITES"])>0)
						{
							foreach($arFields["arSITES"] as $siteID)
							{
								//if (strlen($FIRST_SITE_ID)<=0) $FIRST_SITE_ID = $siteID;
								$FIRST_SITE_ID = $siteID;
								$siteID = $DB->ForSql($siteID);
								$strSql = "INSERT INTO b_ticket_sla_2_site (SLA_ID, SITE_ID) VALUES ($id, '$siteID')";
								$DB->Query($strSql);
							}
						}
					}

					if (is_set($arFields, "arCATEGORIES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_category WHERE SLA_ID = $id");
						if (is_array($arFields["arCATEGORIES"]) && count($arFields["arCATEGORIES"])>0)
						{
							foreach($arFields["arCATEGORIES"] as $categoryID)
							{
								$categoryID = intval($categoryID);
								$strSql = "INSERT INTO b_ticket_sla_2_category (SLA_ID, CATEGORY_ID) VALUES ($id, $categoryID)";
								$DB->Query($strSql);
							}
						}
					}

					if (is_set($arFields, "arCRITICALITIES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_criticality WHERE SLA_ID = $id");
						if (is_array($arFields["arCRITICALITIES"]) && count($arFields["arCRITICALITIES"])>0)
						{
							foreach($arFields["arCRITICALITIES"] as $criticalityID)
							{
								$criticalityID = intval($criticalityID);
								$strSql = "INSERT INTO b_ticket_sla_2_criticality (SLA_ID, CRITICALITY_ID) VALUES ($id, $criticalityID)";
								$DB->Query($strSql);
							}
						}
					}

					if (is_set($arFields, "arMARKS"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_mark WHERE SLA_ID = $id");
						if (is_array($arFields["arMARKS"]) && count($arFields["arMARKS"])>0)
						{
							foreach($arFields["arMARKS"] as $markID)
							{
								$markID = intval($markID);
								$strSql = "INSERT INTO b_ticket_sla_2_mark (SLA_ID, MARK_ID) VALUES ($id, $markID)";
								$DB->Query($strSql);
							}
						}
					}

					/*
					if (is_set($arFields, "arSHEDULE"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_shedule WHERE SLA_ID = $id");
						if (is_array($arFields["arSHEDULE"]) && count($arFields["arSHEDULE"])>0)
						{
							while(list($weekday, $arSHEDULE) = each($arFields["arSHEDULE"]))
							{
								$arF = array(
									"SLA_ID"			=> $id,
									"WEEKDAY_NUMBER"	=> intval($weekday),
									"OPEN_TIME"			=> "'".$DB->ForSql($arSHEDULE["OPEN_TIME"], 10)."'",
									);
								if ($arSHEDULE["OPEN_TIME"]=="CUSTOM" && is_array($arSHEDULE["CUSTOM_TIME"]) && count($arSHEDULE["CUSTOM_TIME"])>0)
								{
									foreach($arSHEDULE["CUSTOM_TIME"] as $ar)
									{
										if (strlen(trim($ar["MINUTE_FROM"]))>0 || strlen(trim($ar["MINUTE_TILL"]))>0)
										{
											$minute_from = strlen($ar["MINUTE_FROM"])>0 ? $ar["MINUTE_FROM"] : "00:00";
											$a = explode(":",$minute_from);
											$minute_from = intval($a[0]*60 + $a[1]);
											$arF["MINUTE_FROM"] = $minute_from;

											$minute_till = strlen($ar["MINUTE_TILL"])>0 ? $ar["MINUTE_TILL"] : "23:59";
											$a = explode(":",$minute_till);
											$minute_till = intval($a[0]*60 + $a[1]);
											$arF["MINUTE_TILL"] = $minute_till;

											$DB->Insert("b_ticket_sla_shedule", $arF);
										}
									}
								}
								else $DB->Insert("b_ticket_sla_shedule", $arF);
							}
						}
					}
					*/

					$FIRST_SITE_ID = $FIRST_SITE_ID <> '' ? "'".$DB->ForSql($FIRST_SITE_ID)."'" : "null";
					$DB->Update($table, array("FIRST_SITE_ID" => $FIRST_SITE_ID), "WHERE ID=".intval($id));
				}
			}
		}
		else
		{
			//$APPLICATION->ThrowException(GetMessage("SUP_ERROR_ACCESS_DENIED"));
			$arMsg = Array();
			$arMsg[] = array("id"=>"PERMISSION", "text"=> GetMessage("SUP_ERROR_ACCESS_DENIED"));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
		}
		CSupportTimetableCache::toCache( array( "SLA_ID"=> $id ) );
		return $id;
	}

	// delete SLA
	public static function Delete($id, $checkRights=true)
	{
		global $DB, $USER, $APPLICATION;
		$id = intval($id);
		if ($id < 1)
		{
			return false;
		}
		if ($id == 1)
		{
			$APPLICATION->ThrowException(GetMessage("SUP_ERROR_SLA_1"));
			return false;
		}
		$isDemo = $isSupportClient = $isSupportTeam = $isAdmin = $isAccess = $userID = null;
		CTicket::GetRoles($isDemo, $isSupportClient, $isSupportTeam, $isAdmin, $isAccess, $userID, $checkRights);
		if ($isAdmin)
		{
			$strSql = "SELECT DISTINCT 'x' FROM b_ticket WHERE SLA_ID = $id";
			$rs = $DB->Query($strSql);
			if (!$rs->Fetch())
			{
				$DB->Query("DELETE FROM b_ticket_sla_2_site WHERE SLA_ID = $id");
				$DB->Query("DELETE FROM b_ticket_sla_2_category WHERE SLA_ID = $id");
				$DB->Query("DELETE FROM b_ticket_sla_2_criticality WHERE SLA_ID = $id");
				$DB->Query("DELETE FROM b_ticket_sla_2_mark WHERE SLA_ID = $id");
				$DB->Query("DELETE FROM b_ticket_sla_2_user_group WHERE SLA_ID = $id");
				//$DB->Query("DELETE FROM b_ticket_sla_shedule WHERE SLA_ID = $id");
				$DB->Query("DELETE FROM b_ticket_sla_2_holidays WHERE SLA_ID = $id");
				
				$DB->Query("DELETE FROM b_ticket_sla WHERE ID = $id");
				$DB->Query("DELETE FROM b_ticket_timetable_cache WHERE SLA_ID = $id");
				return true;
			}
			else
			{
				$APPLICATION->ThrowException(str_replace("#ID#", "$id", GetMessage("SUP_ERROR_SLA_HAS_TICKET")));
			}	
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage("SUP_ERROR_ACCESS_DENIED"));
		}
		return false;
	}

	// get SLA by ID
	public static function GetByID($id)
	{
		$id = intval($id);
		if ($id<=0) return false;
		$arFilter = array("ID" => $id, "ID_EXACT_MATCH" => "Y");
		$arSort = $is_filtered = null;
		$rs = CTicketSLA::GetList($arSort, $arFilter, $is_filtered);
		return $rs;
	}

	// get shedule array by SLA ID
	public static function GetSheduleArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT * FROM b_ticket_sla_shedule WHERE SLA_ID = $slaID ORDER BY WEEKDAY_NUMBER, MINUTE_FROM, MINUTE_TILL";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				if ($ar["OPEN_TIME"]=="CUSTOM")
				{
					if (intval($ar["MINUTE_FROM"])>0)
					{
						$h_from = floor($ar["MINUTE_FROM"]/60);
						$m_from = $ar["MINUTE_FROM"] - $h_from*60;
					}
					if (intval($ar["MINUTE_TILL"])>0)
					{
						$h_till = floor($ar["MINUTE_TILL"]/60);
						$m_till = $ar["MINUTE_TILL"] - $h_till*60;
					}
					$arResult[$ar["WEEKDAY_NUMBER"]]["OPEN_TIME"] = $ar["OPEN_TIME"];
					$arResult[$ar["WEEKDAY_NUMBER"]]["CUSTOM_TIME"][] = array(
						"MINUTE_FROM"	=> $ar["MINUTE_FROM"],
						"SECOND_FROM"	=> $ar["MINUTE_FROM"]*60,
						"MINUTE_TILL"	=> $ar["MINUTE_TILL"],
						"SECOND_TILL"	=> $ar["MINUTE_TILL"]*60,
						"FROM"			=> $h_from.":".str_pad($m_from, 2, 0),
						"TILL"			=> $h_till.":".str_pad($m_till, 2, 0)
						);
				}
				else
				{
					$arResult[$ar["WEEKDAY_NUMBER"]] = array("OPEN_TIME" => $ar["OPEN_TIME"]);
				}
				$arResult[$ar["WEEKDAY_NUMBER"]]["WEEKDAY_TITLE"] = GetMessage("SUP_WEEKDAY_".$ar["WEEKDAY_NUMBER"]);
			}
		}
		return $arResult;
	}

	public static function GetGroupArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT GROUP_ID FROM b_ticket_sla_2_user_group WHERE SLA_ID = $slaID";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch()) $arResult[] = $ar["GROUP_ID"];
		}
		return $arResult;
	}

	public static function GetGroupArrayForAllSLA()
	{
		global $DB;

		$arResult = array();
		$strSql = "SELECT SLA_ID, GROUP_ID FROM b_ticket_sla_2_user_group";
		$rs = $DB->Query($strSql);

		while($ar = $rs->Fetch())
		{
			$arResult[$ar['SLA_ID']][] = $ar["GROUP_ID"];
		}

		return $arResult;
	}

	public static function GetSiteArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT SITE_ID FROM b_ticket_sla_2_site WHERE SLA_ID = $slaID";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch()) $arResult[] = $ar["SITE_ID"];
		}
		return $arResult;
	}

	public static function GetSiteArrayForAllSLA($p = self::SLA_SITE) //self::SITE_SLA
	{
		static $GetSiteArrayForAllSLACache;
		if($p !== self::SITE_SLA)
		{
			$p = self::SLA_SITE;
		}
		if(!is_array($GetSiteArrayForAllSLACache))
		{
			global $DB;
			$GetSiteArrayForAllSLACache = array();
			$strSql = "
				SELECT
					SS.SITE_ID,
					SS.SLA_ID
				FROM
					b_ticket_sla_2_site SS
				";
			$rs = $DB->Query($strSql);
			while ($ar = $rs->Fetch())
			{
				$GetSiteArrayForAllSLACache[self::SLA_SITE][$ar["SLA_ID"]][] = $ar["SITE_ID"];
				$GetSiteArrayForAllSLACache[self::SITE_SLA][$ar["SITE_ID"]][] = $ar["SLA_ID"];
			}
		}
		return $GetSiteArrayForAllSLACache[$p];
	}

	public static function GetCategoryArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT CATEGORY_ID FROM b_ticket_sla_2_category WHERE SLA_ID = $slaID";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch()) $arResult[] = $ar["CATEGORY_ID"];
		}
		return $arResult;
	}

	public static function GetCriticalityArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT CRITICALITY_ID FROM b_ticket_sla_2_criticality WHERE SLA_ID = $slaID";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch()) $arResult[] = $ar["CRITICALITY_ID"];
		}
		return $arResult;
	}

	public static function GetMarkArray($slaID)
	{
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$slaID = intval($slaID);
		if ($slaID>0)
		{
			$strSql = "SELECT MARK_ID FROM b_ticket_sla_2_mark WHERE SLA_ID = $slaID";
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch()) $arResult[] = $ar["MARK_ID"];
		}
		return $arResult;
	}

	public static function GetDropDown($siteID="")
	{
		if ($siteID <> '' && mb_strtoupper($siteID) != "ALL")
		{
			$arFilter = array("SITE" => $siteID);
		}
		$arSort = array("FIRST_SITE_ID" => "ASC", "PRIORITY" => "ASC");
		$is_filtered = null;
		$rs = CTicketSLA::GetList($arSort, $arFilter, $is_filtered);
		return $rs;
	}

	public static function GetForUser($siteID=false, $userID=false)
	{
		global $DB, $USER, $APPLICATION;

		$slaID = 1; // default SLA

		$arrGroups = array();
		if (!is_object($USER)) $USER = new CUser;
		if ($userID===false && is_object($USER)) $userID = $USER->GetID();
		if ($siteID==false) $siteID = SITE_ID;

		$userID = intval($userID);
		if ($userID>0) $arrGroups = CUser::GetUserGroup($userID);
		if (count($arrGroups)<=0) $arrGroups[] = 2;

		$arSla2Site = array();
		$rs = $DB->Query("SELECT SLA_ID, SITE_ID FROM b_ticket_sla_2_site");
		while ($ar = $rs->Fetch()) $arSla2Site[$ar["SLA_ID"]][] = $ar["SITE_ID"];

		$strSql = "
			SELECT
				SG.SLA_ID
			FROM
				b_ticket_sla_2_user_group SG
			INNER JOIN b_ticket_sla S ON (S.ID = SG.SLA_ID)
			WHERE
				SG.GROUP_ID in (".implode(",",$arrGroups).")
			GROUP BY
				SG.SLA_ID, S.PRIORITY
			ORDER BY
				S.PRIORITY DESC
			";
		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			if (is_array($arSla2Site[$ar["SLA_ID"]]) && (in_array($siteID, $arSla2Site[$ar["SLA_ID"]]) || in_array("ALL", $arSla2Site[$ar["SLA_ID"]])))
			{
				$slaID = $ar["SLA_ID"];
				break;
			}
		}
		return $slaID;
	}
	
	public static function GetSLA( $siteID, $userID, $categoryID = null, $coupon = ""  )
	{
		global $DB;

		$userID = intval($userID);
			
		if( $coupon <> '' )
		{
			$rsCoupon = CSupportSuperCoupon::GetList( false, array( 'COUPON' => $coupon ) );
			if($arCoupon = $rsCoupon->Fetch())
			{
				if(intval($arCoupon['SLA_ID']) > 0)
				{
					return intval($arCoupon['SLA_ID']);
				}
			}
		}
		
		$slaID = COption::GetOptionString( "support", "SUPPORT_DEFAULT_SLA_ID" );

		$OLD_FUNCTIONALITY = COption::GetOptionString( "support", "SUPPORT_OLD_FUNCTIONALITY", "Y" );
		if( $OLD_FUNCTIONALITY == "Y" )
		{
			$categoryID = null;
		}
		
		$JOIN = "";
		$fields = "1";
		if( $categoryID != null )
		{
			$categoryID = intval($categoryID);
			$fields = "CASE
						WHEN SC.SLA_ID IS NOT NULL THEN 1
						ELSE 0
					END";
			$JOIN .= "	
					LEFT JOIN b_ticket_sla_2_category SC
						ON S.ID = SC.SLA_ID
							AND ( SC.CATEGORY_ID = 0 OR SC.CATEGORY_ID = $categoryID )";
		}

		$strSql = "
			SELECT
				PZ.SLA_ID
			FROM
			(
				SELECT
					SG.SLA_ID SLA_ID,
					$fields PRIORITY1,
					S.PRIORITY PRIORITY2
				FROM
					b_ticket_sla S
					INNER JOIN b_ticket_sla_2_site SS
						ON S.ID = SS.SLA_ID
							AND ( SS.SITE_ID = 'ALL' OR SS.SITE_ID = '$siteID' ) 
					INNER JOIN b_ticket_sla_2_user_group SG
						ON S.ID = SG.SLA_ID

					$JOIN

					WHERE SG.GROUP_ID IN (
						SELECT UG.GROUP_ID FROM b_user_group UG
						WHERE 
							UG.USER_ID = $userID
							AND (UG.DATE_ACTIVE_FROM IS NULL OR UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")  
							AND (UG.DATE_ACTIVE_TO IS NULL OR UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().") 
						) 
					OR SG.GROUP_ID = 2
			) PZ
			GROUP BY
				PZ.SLA_ID, PZ.PRIORITY1, PZ.PRIORITY2
			ORDER BY
				PZ.PRIORITY1 DESC, PZ.PRIORITY2 DESC
							
		";
		
		$rs = $DB->Query($strSql);
		if( $ar = $rs->Fetch() )
		{
			if( is_array( $ar ) && array_key_exists( "SLA_ID", $ar ) )
			{
				$slaID = $ar["SLA_ID"];
			}
		}

		return $slaID;
	}
}
