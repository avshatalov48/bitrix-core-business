<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Iblock;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog;

IncludeModuleLangFile(__FILE__);

global $IBLOCK_ACTIVE_DATE_FORMAT;
$IBLOCK_ACTIVE_DATE_FORMAT = Array();
global $BX_IBLOCK_PROP_CACHE;
$BX_IBLOCK_PROP_CACHE = Array();
global $ar_IBLOCK_SITE_FILTER_CACHE;
$ar_IBLOCK_SITE_FILTER_CACHE = Array();

class CAllIBlockElement
{
	public const WORKFLOW_STATUS_UNLOCK = 'green';
	public const WORKFLOW_STATUS_CURRENT_LOCK = 'yellow';
	public const WORKFLOW_STATUS_LOCK = 'red';

	private const PROPERTY_LINK_ELEMENT_MASK = '/^([^.]+)\\.([^.]+)$/';

	private const JOIN_TYPE_SELECT = 0x01;
	private const JOIN_TYPE_WHERE = 0x02;
	private const JOIN_TYPE_GROUP = 0x04;
	private const JOIN_TYPE_ORDER = 0x08;

	public string $LAST_ERROR = '';
	protected $bWF_SetMove = true;

	public $strField;
	protected $subQueryProp;
	public $arFilter;

	public $bOnlyCount;
	public $bDistinct;
	public $bCatalogSort;

	public $arFilterIBlocks = array();
	public $arIBlockMultProps = array();
	public $arIBlockConvProps = array();
	public $arIBlockAllProps = array();
	public $arIBlockNumProps = array();
	public $arIBlockLongProps = array();

	public $sSelect;
	public $sFrom;
	public $sWhere;
	public $sGroupBy;
	public $sOrderBy;
	public ?string $countFrom = null;

	protected static $elementIblock = array();

	protected $catalogIncluded;
	protected $workflowIncluded;
	protected $bizprocInstalled;
	protected $searchIncluded;

	protected $userExists;
	protected $userId;
	protected bool $isAdmin;

	protected $iblock;
	protected $iblockLanguage;

	protected $indexedProperties = array();

	protected $offerProperties = array();

	private static array $propertyIdentifierMasks = [
		'/^[0-9]+([A-Z_]*)$/',
		'/^[A-Z_][A-Z0-9_]*$/',
	];

	private static array $propertyLinkFieldIdentifierMasks = [
		'/^[A-Z][A-Z_]*$/',
		'/^PROPERTY_[0-9]+$/',
		'/^PROPERTY_[A-Z_][A-Z0-9_]*$/',
	];

	public function __construct()
	{
		global $USER;

		$this->catalogIncluded = Loader::includeModule('catalog');
		$this->workflowIncluded = Loader::includeModule('workflow');
		$this->bizprocInstalled = ModuleManager::isModuleInstalled('bizproc');
		$this->searchIncluded = Loader::includeModule('search');
		$this->userExists = isset($USER) && $USER instanceof \CUser;
		$this->userId = ($this->userExists ? (int)$USER->GetID() : 0);
		$this->isAdmin = $this->userExists && $USER->IsAdmin();
		$this->iblock = null;
		$this->iblockLanguage = null;
	}

	public function setIblock(?int $iblockId): void
	{
		$iblock = null;
		$language = null;
		if ($iblockId !== null)
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
			if (!is_array($iblock))
			{
				$iblock = null;
			}
			else
			{
				$iblock['ID'] = (int)$iblock['ID'];
				$language = static::getIblockLanguage($iblock['ID']);
			}
		}
		$this->iblock = $iblock;
		$this->iblockLanguage = $language;
	}

	/**
	 * @param $strField
	 * @param $arFilter
	 * @return CIBlockElement|null
	 */
	public static function SubQuery($strField, $arFilter)
	{
		if(mb_substr($strField, 0, 9) == "PROPERTY_")
		{
			if (!is_array($arFilter))
			{
				return null;
			}
			$iblockId = null;
			if (isset($arFilter['IBLOCK_ID']))
			{
				$iblockId = $arFilter['IBLOCK_ID'];
			}
			elseif (isset($arFilter['=IBLOCK_ID']))
			{
				$iblockId = $arFilter['=IBLOCK_ID'];
			}
			if ($iblockId === null)
			{
				return null;
			}

			$db_prop = CIBlockProperty::GetPropertyArray(
				mb_substr($strField, 9),
				CIBlock::_MergeIBArrays(
					$iblockId,
					$arFilter["IBLOCK_CODE"] ?? false,
					$arFilter["~IBLOCK_ID"] ?? false,
					$arFilter["~IBLOCK_CODE"] ?? false
				)
			);
			if ($db_prop && $db_prop["PROPERTY_TYPE"] === Iblock\PropertyTable::TYPE_ELEMENT)
			{
				$ob = new CIBlockElement;
				$ob->subQueryProp = $db_prop;
				$ob->strField = $strField;
				$ob->arFilter = $arFilter;
				return $ob;
			}
		}
		elseif($strField == "ID")
		{
			$ob = new CIBlockElement;
			$ob->strField = $strField;
			$ob->arFilter = $arFilter;
			return $ob;
		}

		return null;
	}

	public function CancelWFSetMove()
	{
		$this->bWF_SetMove = false;
	}

	public static function WF_Restore($ID)
	{
		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetByID($ID);
		if($arElement = $rsElement->Fetch())
		{
			if($arElement["WF_PARENT_ELEMENT_ID"] <> '')
			{
				$arElement["PROPERTY_VALUES"] = array();
				$rsProperties = $obElement->GetProperty($arElement["IBLOCK_ID"], $arElement["WF_PARENT_ELEMENT_ID"], "sort", "asc", array("PROPERTY_TYPE"=>"F"));
				while($arProperty = $rsProperties->Fetch())
				{
					if(!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"]))
					{
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]] = array();
					}
					$arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty["PROPERTY_VALUE_ID"]] = array(
						"del" => "Y",
					);
				}
				$n = 1;
				$rsProperties = $obElement->GetProperty($arElement["IBLOCK_ID"], $arElement["ID"]);
				while($arProperty = $rsProperties->Fetch())
				{
					if(!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"]))
					{
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]] = array();
					}
					if($arProperty["PROPERTY_TYPE"] == "F")
					{
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]]["n".$n] = array(
							"VALUE" => $arProperty["VALUE"],
							"DESCRIPTION" => $arProperty["DESCRIPTION"],
						);
						$n++;
					}
					else
					{
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty["PROPERTY_VALUE_ID"]] = array(
							"VALUE" => $arProperty["VALUE"],
							"DESCRIPTION" => $arProperty["DESCRIPTION"],
						);
					}
				}

				return $obElement->Update($arElement["WF_PARENT_ELEMENT_ID"], $arElement, true);
			}
		}
		return false;
	}

	///////////////////////////////////////////////////////////////////
	// Clear history
	///////////////////////////////////////////////////////////////////
	public static function WF_CleanUpHistory()
	{
		if (CModule::IncludeModule("workflow"))
		{
			global $DB;

			$HISTORY_DAYS = COption::GetOptionInt("workflow", "HISTORY_DAYS", -1);
			if($HISTORY_DAYS >= 0)
			{
				$arDate = localtime(time());
				$date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$HISTORY_DAYS, 1900+$arDate[5]);

				CTimeZone::Disable();
				$strSql = "
					SELECT ID, WF_PARENT_ELEMENT_ID
					FROM b_iblock_element
					WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL"))."
					AND WF_PARENT_ELEMENT_ID is not null
					ORDER BY ID DESC
				";
				$rsElements = $DB->Query($strSql);
				CTimeZone::Enable();

				//This Fetch will keep at least one history copy
				//in order to prevent files being deleted
				//before they copied into working copy
				if($rsElements->Fetch())
				{
					while($arElement = $rsElements->Fetch())
					{
						$LAST_ID = CIBlockElement::WF_GetLast($arElement["WF_PARENT_ELEMENT_ID"]);
						if($LAST_ID != $arElement["ID"])
						{
							CIBlockElement::Delete($arElement["ID"]);
						}
					}
				}
			}
		}
	}
	///////////////////////////////////////////////////////////////////
	// Send changing status message
	///////////////////////////////////////////////////////////////////
	public function WF_SetMove($NEW_ID, $OLD_ID = 0)
	{
		if(CModule::IncludeModule("workflow"))
		{
			global $DB, $USER;

			$USER_ID = is_object($USER)? (int)$USER->GetID(): 0;
			$NEW = "Y";
			$OLD_ID = (int)$OLD_ID;
			$NEW_ID = (int)$NEW_ID;
			$oldStatusId = 0;
			if($OLD_ID>0)
			{
				$old = $DB->Query("SELECT WF_STATUS_ID FROM b_iblock_element WHERE ID = ".$OLD_ID);
				if($old_r=$old->Fetch())
				{
					$NEW = "N";
					$oldStatusId = (int)$old_r["WF_STATUS_ID"];
				}
			}
			CTimeZone::Disable();
			$new = CIBlockElement::GetByID($NEW_ID);
			CTimeZone::Enable();

			if($new_r=$new->Fetch())
			{
				$NEW_STATUS_ID = (int)$new_r["WF_STATUS_ID"];
				$OLD_STATUS_ID = $oldStatusId;
				$PARENT_ID = (int)$new_r["WF_PARENT_ELEMENT_ID"];

				CTimeZone::Disable();
				$parent = CIBlockElement::GetByID($PARENT_ID);
				CTimeZone::Enable();

				if($parent_r = $parent->Fetch())
				{
					$arFields = array(
						"TIMESTAMP_X"		=> $DB->GetNowFunction(),
						"IBLOCK_ELEMENT_ID"	=> $PARENT_ID,
						"OLD_STATUS_ID"		=> $OLD_STATUS_ID,
						"STATUS_ID"		=> $NEW_STATUS_ID,
						"USER_ID"		=> $USER_ID,
						);
					$DB->Insert("b_workflow_move", $arFields);
					if($NEW_STATUS_ID != $OLD_STATUS_ID)
					{
						// Get creator Email
						$strSql = "SELECT EMAIL FROM b_user WHERE ID = ".(int)$parent_r["CREATED_BY"];
						$rs = $DB->Query($strSql);
						if($ar = $rs->Fetch())
							$parent_r["CREATED_BY_EMAIL"] = $ar["EMAIL"];
						else
							$parent_r["CREATED_BY_EMAIL"] = "";

						// gather email of the workflow admins
						$WORKFLOW_ADMIN_GROUP_ID = (int)Main\Config\Option::get("workflow", "WORKFLOW_ADMIN_GROUP_ID");
						$strSql = "
							SELECT U.ID, U.EMAIL
							FROM b_user U, b_user_group UG
							WHERE
								UG.GROUP_ID=".$WORKFLOW_ADMIN_GROUP_ID."
								AND U.ID = UG.USER_ID
								AND U.ACTIVE='Y'
						";
						$rs = $DB->Query($strSql);
						$arAdmin = Array();
						while($ar = $rs->Fetch())
						{
							$arAdmin[$ar["ID"]] = $ar["EMAIL"];
						}

						// gather email for BCC
						$arBCC = array();

						// gather all who changed doc in its current status
						$strSql = "
							SELECT U.EMAIL
							FROM
								b_workflow_move WM
								INNER JOIN b_user U on U.ID = WM.USER_ID
							WHERE
								IBLOCK_ELEMENT_ID = ".$PARENT_ID."
								AND OLD_STATUS_ID = ".$NEW_STATUS_ID."
						";
						$rs = $DB->Query($strSql);
						while($ar = $rs->Fetch())
						{
							$arBCC[$ar["EMAIL"]] = $ar["EMAIL"];
						}

						// gather all editors
						// in case status have notifier flag

						//First those who have write permissions on iblock
						$strSql = "
							SELECT U.EMAIL
							FROM
								b_workflow_status S
								INNER JOIN b_workflow_status2group SG on SG.STATUS_ID = S.ID
								INNER JOIN b_iblock_group IG on IG.GROUP_ID = SG.GROUP_ID
								INNER JOIN b_user_group UG on UG.GROUP_ID = IG.GROUP_ID
								INNER JOIN b_user U on U.ID = UG.USER_ID
							WHERE
								S.ID = ".$NEW_STATUS_ID."
								AND S.NOTIFY = 'Y'
								AND IG.IBLOCK_ID = ".intval($new_r["IBLOCK_ID"])."
								AND IG.PERMISSION >= 'U'
								AND SG.PERMISSION_TYPE = '2'
								AND U.ACTIVE = 'Y'
						";
						$rs = $DB->Query($strSql);
						while($ar = $rs->Fetch())
						{
							$arBCC[$ar["EMAIL"]] = $ar["EMAIL"];
						}

						//Second admins if they in PERMISSION_TYPE = 2 list
						//because they have all the rights
						$strSql = "
							SELECT U.EMAIL
							FROM
								b_workflow_status S
								INNER JOIN b_workflow_status2group SG on SG.STATUS_ID = S.ID
								INNER JOIN b_user_group UG on UG.GROUP_ID = SG.GROUP_ID
								INNER JOIN b_user U on U.ID = UG.USER_ID
							WHERE
								S.ID = ".$NEW_STATUS_ID."
								AND S.NOTIFY = 'Y'
								AND SG.GROUP_ID = 1
								AND SG.PERMISSION_TYPE = '2'
								AND U.ACTIVE = 'Y'
						";
						$rs = $DB->Query($strSql);
						while($ar = $rs->Fetch())
						{
							$arBCC[$ar["EMAIL"]] = $ar["EMAIL"];
						}

						$iblock_r = CIBlock::GetArrayByID($new_r["IBLOCK_ID"]);
						$iblock_r["LID"] = array();
						$rsIBlockSite = $DB->Query("SELECT SITE_ID FROM b_iblock_site WHERE IBLOCK_ID= ".(int)$new_r["IBLOCK_ID"]);
						while($arIBlockSite = $rsIBlockSite->Fetch())
							$iblock_r["LID"][] = $arIBlockSite["SITE_ID"];

						if(array_key_exists($new_r["MODIFIED_BY"], $arAdmin))
							$new_r["USER_NAME"] .= " (Admin)";
						// it is not new doc
						if($NEW!="Y")
						{
							if(array_key_exists($parent_r["CREATED_BY"], $arAdmin))
								$parent_r["CREATED_USER_NAME"] .= " (Admin)";

							// send change notification
							$arEventFields = array(
								"ID"			=> $PARENT_ID,
								"IBLOCK_ID"		=> $new_r["IBLOCK_ID"],
								"IBLOCK_TYPE"		=> $iblock_r["IBLOCK_TYPE_ID"],
								"ADMIN_EMAIL"		=> implode(",", $arAdmin),
								"BCC"			=> implode(",", $arBCC),
								"PREV_STATUS_ID"	=> $OLD_STATUS_ID,
								"PREV_STATUS_TITLE"	=> CIblockElement::WF_GetStatusTitle($OLD_STATUS_ID),
								"STATUS_ID"		=> $NEW_STATUS_ID,
								"STATUS_TITLE"		=> CIblockElement::WF_GetStatusTitle($NEW_STATUS_ID),
								"DATE_CREATE"		=> $parent_r["DATE_CREATE"],
								"CREATED_BY_ID"		=> $parent_r["CREATED_BY"],
								"CREATED_BY_NAME"	=> $parent_r["CREATED_USER_NAME"],
								"CREATED_BY_EMAIL"	=> $parent_r["CREATED_BY_EMAIL"],
								"DATE_MODIFY"		=> $new_r["TIMESTAMP_X"],
								"MODIFIED_BY_ID"	=> $new_r["MODIFIED_BY"],
								"MODIFIED_BY_NAME"	=> $new_r["USER_NAME"],
								"NAME"			=> $new_r["NAME"],
								"SECTION_ID"		=> $new_r["IBLOCK_SECTION_ID"],
								"PREVIEW_HTML"		=> ($new_r["PREVIEW_TEXT_TYPE"]=="html" ?$new_r["PREVIEW_TEXT"]:TxtToHtml($new_r["PREVIEW_TEXT"])),
								"PREVIEW_TEXT"		=> ($new_r["PREVIEW_TEXT_TYPE"]=="text"? $new_r["PREVIEW_TEXT"]:HtmlToTxt($new_r["PREVIEW_TEXT"])),
								"PREVIEW"		=> $new_r["PREVIEW_TEXT"],
								"PREVIEW_TYPE"		=> $new_r["PREVIEW_TEXT_TYPE"],
								"DETAIL_HTML"		=> ($new_r["DETAIL_TEXT_TYPE"]=="html" ?$new_r["DETAIL_TEXT"]:TxtToHtml($new_r["DETAIL_TEXT"])),
								"DETAIL_TEXT"		=> ($new_r["DETAIL_TEXT_TYPE"]=="text"? $new_r["DETAIL_TEXT"]:HtmlToTxt($new_r["DETAIL_TEXT"])),
								"DETAIL"		=> $new_r["DETAIL_TEXT"],
								"DETAIL_TYPE"		=> $new_r["DETAIL_TEXT_TYPE"],
								"COMMENTS"		=> $new_r["WF_COMMENTS"]
							);
							CEvent::Send("WF_IBLOCK_STATUS_CHANGE", $iblock_r["LID"], $arEventFields);
						}
						else // otherwise
						{
							// it was new one

							$arEventFields = array(
								"ID"			=> $PARENT_ID,
								"IBLOCK_ID"		=> $new_r["IBLOCK_ID"],
								"IBLOCK_TYPE"		=> $iblock_r["IBLOCK_TYPE_ID"],
								"ADMIN_EMAIL"		=> implode(",", $arAdmin),
								"BCC"			=> implode(",", $arBCC),
								"STATUS_ID"		=> $NEW_STATUS_ID,
								"STATUS_TITLE"		=> CIblockElement::WF_GetStatusTitle($NEW_STATUS_ID),
								"DATE_CREATE"		=> $parent_r["DATE_CREATE"],
								"CREATED_BY_ID"		=> $parent_r["CREATED_BY"],
								"CREATED_BY_NAME"	=> $parent_r["CREATED_USER_NAME"],
								"CREATED_BY_EMAIL"	=> $parent_r["CREATED_BY_EMAIL"],
								"NAME"			=> $new_r["NAME"],
								"PREVIEW_HTML"		=> ($new_r["PREVIEW_TEXT_TYPE"]=="html" ?$new_r["PREVIEW_TEXT"]:TxtToHtml($new_r["PREVIEW_TEXT"])),
								"PREVIEW_TEXT"		=> ($new_r["PREVIEW_TEXT_TYPE"]=="text"? $new_r["PREVIEW_TEXT"]:HtmlToTxt($new_r["PREVIEW_TEXT"])),
								"PREVIEW"		=> $new_r["PREVIEW_TEXT"],
								"PREVIEW_TYPE"		=> $new_r["PREVIEW_TEXT_TYPE"],
								"SECTION_ID"		=> $new_r["IBLOCK_SECTION_ID"],
								"DETAIL_HTML"		=> ($new_r["DETAIL_TEXT_TYPE"]=="html" ?$new_r["DETAIL_TEXT"]:TxtToHtml($new_r["DETAIL_TEXT"])),
								"DETAIL_TEXT"		=> ($new_r["DETAIL_TEXT_TYPE"]=="text"? $new_r["DETAIL_TEXT"]:HtmlToTxt($new_r["DETAIL_TEXT"])),
								"DETAIL"		=> $new_r["DETAIL_TEXT"],
								"DETAIL_TYPE"		=> $new_r["DETAIL_TEXT_TYPE"],
								"COMMENTS"		=> $new_r["WF_COMMENTS"]
							);
							CEvent::Send("WF_NEW_IBLOCK_ELEMENT",$iblock_r["LID"], $arEventFields);
						}
					}
				}
			}
		}
	}

	///////////////////////////////////////////////////////////////////
	// Clears the last or old records in history using parameters from workflow module
	///////////////////////////////////////////////////////////////////
	public static function WF_CleanUpHistoryCopies($ELEMENT_ID=false, $HISTORY_COPIES=false)
	{
		if(CModule::IncludeModule("workflow"))
		{
			global $DB;
			if($HISTORY_COPIES===false)
				$HISTORY_COPIES = intval(COption::GetOptionString("workflow","HISTORY_COPIES","10"));

			$strSqlSearch = '';
			$ELEMENT_ID = (int)$ELEMENT_ID;
			if($ELEMENT_ID>0)
				$strSqlSearch = " AND ID = $ELEMENT_ID ";
			$strSql = "SELECT ID FROM b_iblock_element ".
					"WHERE (ID=WF_PARENT_ELEMENT_ID or (WF_PARENT_ELEMENT_ID IS NULL AND WF_STATUS_ID=1)) ".
					$strSqlSearch;
			$z = $DB->Query($strSql);
			while ($zr=$z->Fetch())
			{
				$DID = $zr["ID"];
				$strSql =
					"SELECT ID, WF_NEW, WF_PARENT_ELEMENT_ID ".
					"FROM b_iblock_element ".
					"WHERE WF_PARENT_ELEMENT_ID = ".$DID." ".
					"	AND WF_PARENT_ELEMENT_ID<>ID ".
					"	AND (WF_NEW<>'Y' or WF_NEW is null) ".
					"ORDER BY ID desc";
				$t = $DB->Query($strSql);
				$i = 0;
				while ($tr = $t->Fetch())
				{
					$i++;
					if($i>$HISTORY_COPIES)
					{
						$LAST_ID = CIBlockElement::WF_GetLast($DID);
						if($LAST_ID!=$tr["ID"])
						{
							CIBlockElement::Delete($tr["ID"]);
						}
					}
				}
			}
		}
	}

	public static function WF_GetSqlLimit($PS="BE.", $SHOW_NEW="N")
	{
		if(CModule::IncludeModule("workflow"))
		{
			$limit = " and ((".$PS."WF_STATUS_ID=1 and ".$PS."WF_PARENT_ELEMENT_ID is null)";
			if($SHOW_NEW=="Y") $limit .= " or ".$PS."WF_NEW='Y' ";
			$limit .= " ) ";
		}
		else
		{
			$limit = " AND ".$PS."WF_STATUS_ID=1 and ".$PS."WF_PARENT_ELEMENT_ID is null ";
		}
		return $limit;
	}

	///////////////////////////////////////////////////////////////////
	// Returns last ID of element in the history
	///////////////////////////////////////////////////////////////////
	public static function WF_GetLast($ID)
	{
		global $DB;
		$ID = (int)$ID;

		$z = $DB->Query("SELECT ID, WF_PARENT_ELEMENT_ID FROM b_iblock_element WHERE ID = ".$ID);
		$zr = $z->Fetch();
		$WF_PARENT_ELEMENT_ID = (int)$zr["WF_PARENT_ELEMENT_ID"];
		if ($WF_PARENT_ELEMENT_ID > 0)
		{
			$strSql = "SELECT ID FROM b_iblock_element WHERE WF_PARENT_ELEMENT_ID='".$WF_PARENT_ELEMENT_ID."' ORDER BY ID desc";
			$s = $DB->Query($strSql);
			$sr = $s->Fetch();
			if ($sr && $sr["ID"] > 0)
			{
				$ID = $sr["ID"];
			}
		}
		else
		{
			$strSql = "SELECT ID, WF_STATUS_ID FROM b_iblock_element WHERE WF_PARENT_ELEMENT_ID='$ID' ORDER BY ID desc";
			$s = $DB->Query($strSql);
			$sr = $s->Fetch();
			if ($sr && $sr['WF_STATUS_ID'] > 1 && $sr["ID"] > 0)
			{
				$ID = $sr["ID"];
			}
		}
		return $ID;
	}

	/**
	 * @param int $ID
	 * @return int
	 */
	public static function GetRealElement($ID): int
	{
		global $DB;
		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return $ID;
		}

		$PARENT_ID = 0;
		$z = $DB->Query("SELECT WF_PARENT_ELEMENT_ID FROM b_iblock_element WHERE ID='$ID'");
		$zr = $z->Fetch();
		unset($z);
		if (!empty($zr))
		{
			$PARENT_ID = (int)$zr["WF_PARENT_ELEMENT_ID"];
		}
		unset($zr);

		return ($PARENT_ID > 0 ? $PARENT_ID : $ID);
	}

	public static function WF_GetStatusTitle($STATUS_ID)
	{
		global $DB;

		$zr = array(
			'TITLE' => null
		);
		if(CModule::IncludeModule("workflow"))
		{
			$STATUS_ID = (int)$STATUS_ID;
			if($STATUS_ID>0)
			{
				$strSql = "SELECT * FROM b_workflow_status WHERE ID='$STATUS_ID'";
				$z = $DB->Query($strSql);
				$zr = $z->Fetch();
			}
		}
		return $zr["TITLE"];
	}

	public static function WF_GetCurrentStatus($ELEMENT_ID, &$STATUS_TITLE)
	{
		global $DB;
		$STATUS_ID = 0;

		if(CModule::IncludeModule("workflow"))
		{
			$ELEMENT_ID = (int)$ELEMENT_ID;

			$WF_ID = (int)CIBlockElement::WF_GetLast($ELEMENT_ID);
			if ($WF_ID <= 0)
				$WF_ID = $ELEMENT_ID;

			if ($WF_ID > 0)
			{
				$strSql =
					"SELECT E.WF_STATUS_ID, S.TITLE ".
					"FROM b_iblock_element E, b_workflow_status S ".
					"WHERE E.ID = ".$WF_ID." ".
					"	AND	S.ID = E.WF_STATUS_ID";
				$z = $DB->Query($strSql);
				$zr = $z->Fetch();
				$STATUS_ID = $zr["WF_STATUS_ID"];
				$STATUS_TITLE = $zr["TITLE"];
			}
		}
		return (int)$STATUS_ID;
	}

	///////////////////////////////////////////////////////////////////
	// Returns permission status
	///////////////////////////////////////////////////////////////////
	public static function WF_GetStatusPermission($STATUS_ID, $ID = false)
	{
		global $DB, $USER;
		$result = false;
		if(CModule::IncludeModule("workflow"))
		{
			if(CWorkflow::IsAdmin())
				return 2;
			else
			{
				$ID = (int)$ID;
				if($ID)
				{
					$arStatus = array();
					$arSql = Array("ID='".$ID."'", "WF_PARENT_ELEMENT_ID='".$ID."'");
					foreach($arSql as $where)
					{
						$strSql = "SELECT ID, WF_STATUS_ID FROM b_iblock_element WHERE ".$where;
						$rs = $DB->Query($strSql);
						while($ar = $rs->Fetch())
							$arStatus[$ar["WF_STATUS_ID"]] = $ar["WF_STATUS_ID"];
					}
				}
				else
				{
					$arStatus = array((int)($STATUS_ID)=>(int)($STATUS_ID));
				}
				$arGroups = $USER->GetUserGroupArray();
				if (!is_array($arGroups))
				{
					$arGroups = [2];
				}
				$groups = implode(",",$arGroups);
				foreach($arStatus as $STATUS_ID)
				{
					$strSql =
							"SELECT max(G.PERMISSION_TYPE) as MAX_PERMISSION ".
							"FROM b_workflow_status2group G ".
							"WHERE G.STATUS_ID = ".$STATUS_ID." ".
							"	AND G.GROUP_ID in (".$groups.") ";
					$rs = $DB->Query($strSql);
					$ar = $rs->Fetch();
					$ar["MAX_PERMISSION"] = (int)$ar["MAX_PERMISSION"];
					if($result===false || ($result > $ar["MAX_PERMISSION"]))
						$result = $ar["MAX_PERMISSION"];
				}
			}
		}
		return $result;
	}

	public static function WF_IsLocked($ID, &$locked_by, &$date_lock)
	{
		return static::WF_GetLockStatus($ID, $locked_by, $date_lock) === self::WORKFLOW_STATUS_LOCK;
	}

	/**
	 * Returns lock status of element (red, yellow, green)
	 *
	 * @param $ID
	 * @param &$locked_by
	 * @param &$date_lock
	 * @return string
	 */
	public static function WF_GetLockStatus($ID, &$locked_by, &$date_lock)
	{
		global $USER;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return self::WORKFLOW_STATUS_UNLOCK;
		}

		$MAX_LOCK = (int)Main\Config\Option::get('workflow','MAX_LOCK_TIME','60');
		$uid = is_object($USER)? (int)$USER->GetID(): 0;

		$strSql = "
			SELECT WF_LOCKED_BY,
				WF_DATE_LOCK,
				case
					when WF_DATE_LOCK is null then '" . self::WORKFLOW_STATUS_UNLOCK . "'
					when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'WF_DATE_LOCK') . " < " . $helper->getCurrentDateTimeFunction() . " then '" . self::WORKFLOW_STATUS_UNLOCK . "'
					when WF_LOCKED_BY = " . $uid . " then '" . self::WORKFLOW_STATUS_CURRENT_LOCK . "'
					else '" . self::WORKFLOW_STATUS_LOCK . "'
				end LOCK_STATUS
			FROM b_iblock_element
			WHERE ID = " . $ID . "
		";
		unset($helper);

		$iterator = $connection->query($strSql);
		unset($connection);
		$row = $iterator->fetch();
		unset($iterator);

		if (empty($row))
		{
			return self::WORKFLOW_STATUS_UNLOCK;
		}

		$locked_by = $row['WF_LOCKED_BY'];
		$date_lock = $row['WF_DATE_LOCK'] instanceof Main\Type\DateTime ? $row['WF_DATE_LOCK']->toString() : $row['WF_DATE_LOCK'];

		return $row['LOCK_STATUS'];
	}

	/**
	 * Locking element.
	 *
	 * @param $lastId
	 * @param $bWorkFlow
	 * @return void
	 */
	public static function WF_Lock($lastId, $bWorkFlow = true)
	{
		global $USER;
		$lastId = (int)$lastId;
		if ($lastId <= 0)
		{
			return;
		}
		$userId = $USER instanceof CUser ? (int)$USER->GetID(): 0;

		$updateIds = [];
		if ($bWorkFlow === true)
		{
			$row = ElementTable::getRow([
				'select' => [
					'WF_PARENT_ELEMENT_ID',
				],
				'filter' => [
					'=ID' => $lastId,
				],
			]);
			if ($row)
			{
				$updateIds[] = $lastId;
				$parentId = (int)$row['WF_PARENT_ELEMENT_ID'];
				if ($parentId > 0)
				{
					$updateIds[] = $parentId;
				}
			}
		}
		else
		{
			$updateIds[] = $lastId;
		}
		if (!empty($updateIds))
		{
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$query =
				'update ' . $helper->quote(ElementTable::getTableName()) .
				' set'
				. ' ' . $helper->quote('WF_DATE_LOCK') . ' = ' . $helper->getCurrentDateTimeFunction() . ','
				. ' ' . $helper->quote('WF_LOCKED_BY') . ' = ' . $userId
				. ' where ' . $helper->quote('ID') . ' in (' . implode(',', $updateIds)  . ')'
			;
			$connection->queryExecute($query);

			unset($helper, $connection);
		}
	}

	/**
	 * Unlock element.
	 *
	 * @param $lastId
	 * @param $bWorkFlow
	 * @return void
	 */
	public static function WF_UnLock($lastId, $bWorkFlow = true)
	{
		global $USER;
		$lastId = (int)$lastId;
		if ($lastId <= 0)
		{
			return;
		}
		$userId = $USER instanceof CUser ? (int)$USER->GetID(): 0;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if ($bWorkFlow === true)
		{
			$row = ElementTable::getRow([
				'select' => [
					'WF_PARENT_ELEMENT_ID',
					'WF_LOCKED_BY',
				],
				'filter' => [
					'=ID' => $lastId,
				],
			]);
			if ($row)
			{
				if (
					(int)$row['WF_LOCKED_BY'] === $userId
					|| (
						Loader::includeModule('workflow') && CWorkflow::IsAdmin()
					)
				)
				{
					$updateIds = [
						$lastId,
					];
					$parentId = (int)$row['WF_PARENT_ELEMENT_ID'];
					if ($parentId > 0)
					{
						$updateIds[] = $parentId;
					}
					$query =
						'update ' . $helper->quote(ElementTable::getTableName())
						. ' set'
						. ' ' . $helper->quote('WF_DATE_LOCK') . ' = null, '
						. ' ' . $helper->quote('WF_LOCKED_BY') . ' = null'
						. ' where'
						. ' ' . $helper->quote('ID' ). ' in (' . implode(',', $updateIds) . ')'
					;
					if ($parentId > 0)
					{
						$query .= ' or ' . $helper->quote('WF_PARENT_ELEMENT_ID')  . ' = ' . $parentId;
					}
					$connection->queryExecute($query);
				}
			}
		}
		else
		{
			$query =
				'update ' . $helper->quote(ElementTable::getTableName())
				. ' set'
				. ' ' . $helper->quote('WF_DATE_LOCK') . ' = null, '
				. ' ' . $helper->quote('WF_LOCKED_BY') . ' = null'
				. ' where'
				. ' ' . $helper->quote('ID') . ' = ' . $lastId
			;
			$connection->queryExecute($query);
		}

		unset($helper, $connection);
	}

	/**
	 * List the workflow history items.
	 *
	 * @param $ELEMENT_ID
	 * @param $by
	 * @param $order
	 * @param $arFilter
	 * @return CDBResult|false
	 */
	public static function WF_GetHistoryList($ELEMENT_ID, $by = 's_id', $order = 'desc', $arFilter = [])
	{
		global $DB;

		$ELEMENT_ID = (int)$ELEMENT_ID;
		$strSqlSearch = "";
		if(is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '' || $val == "NOT_REF")
					continue;
				$val = $DB->ForSql($val);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$arr = explode(",", $val);
						if (!empty($arr))
						{
							$arr = array_map("intval", $arr);
							$str = implode(", ", $arr);
							$strSqlSearch .= " and E.ID in (".$str.")";
						}
						break;
					case "TIMESTAMP_FROM":
						$strSqlSearch .= " and E.TIMESTAMP_X>=FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
						break;
					case "TIMESTAMP_TO":
						$strSqlSearch .= " and E.TIMESTAMP_X<=FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y H:i:s")."')";
						break;
					case "MODIFIED_BY":
					case "MODIFIED_USER_ID":
						$strSqlSearch .= " and E.MODIFIED_BY='" . (int)$val . "'";
						break;
					case "IBLOCK_ID":
						$strSqlSearch .= " and E.IBLOCK_ID='" . (int)$val . "'";
						break;
					case "NAME":
						if($val!="%%")
							$strSqlSearch .= " and upper(E.NAME) like upper('".$DB->ForSQL($val,255)."')";
						break;
					case "STATUS":
					case "STATUS_ID":
						$strSqlSearch .= " and E.WF_STATUS_ID='" . (int)$val . "'";
						break;
				}
			}
		}

		if($by == "s_id")
			$strSqlOrder = "ORDER BY E.ID";
		elseif($by == "s_timestamp")
			$strSqlOrder = "ORDER BY E.TIMESTAMP_X";
		elseif($by == "s_modified_by")
			$strSqlOrder = "ORDER BY E.MODIFIED_BY";
		elseif($by == "s_name")
			$strSqlOrder = "ORDER BY E.NAME";
		elseif($by == "s_status")
			$strSqlOrder = "ORDER BY E.WF_STATUS_ID";
		else
		{
			$strSqlOrder = "ORDER BY E.ID";
		}

		if($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql = "
			SELECT
				E.*,
				".$DB->DateToCharFunction("E.TIMESTAMP_X")." TIMESTAMP_X,
				" . self::getUserNameSql('U') . " USER_NAME,
				S.TITLE STATUS_TITLE
			FROM
				b_iblock_element E
				INNER JOIN b_workflow_status S on S.ID = E.WF_STATUS_ID
				LEFT JOIN b_user U ON U.ID = E.MODIFIED_BY
			WHERE
				E.WF_PARENT_ELEMENT_ID = ".$ELEMENT_ID."
				".$strSqlSearch."
			".$strSqlOrder."
		";

		return $DB->Query($strSql);
	}

	protected static function getSingleIblockIdFromFilter(array $filter): ?int
	{
		$iblockId = null;
		$list = [
			'IBLOCK_ID',
			'=IBLOCK_ID',
		];
		foreach ($list as $index)
		{
			if (isset($filter[$index]) && is_numeric($filter[$index]))
			{
				$iblockId = (int)$filter[$index];
				if ($iblockId > 0)
				{
					break;
				}
				else
				{
					$iblockId = null;
				}
			}
		}

		if ($iblockId === null)
		{
			return null;
		}

		return
			CIBlock::GetArrayByID($iblockId, 'ID') === null
				? null
				: $iblockId
		;
	}

	public function MkFilter($arFilter, &$arJoinProps, &$arAddWhereFields, $level = 0, $bPropertyLeftJoin = false)
	{
		global $DB, $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$catalogIncluded = Loader::includeModule('catalog');
		$catalogFields = [];

		$arSqlSearch = [];
		$permSQL = '';

		$arSectionFilter = [
			'LOGIC' => '',
			'BE' => [],
			'BS' => [],
		];

		if (!is_array($arFilter))
		{
			$arFilter = [];
		}

		foreach ($arFilter as $key => $val)
		{
			$origKey = $key;
			$key = mb_strtoupper($key);
			if (str_ends_with($key, 'PROPERTY') && is_array($val))
			{
				unset($arFilter[$origKey]);
				$arFilter[$key] = array_change_key_case($val, CASE_UPPER);
			}
		}

		foreach ($arFilter as $key => $val)
		{
			$origKey = $key;
			$key = mb_strtoupper($key);
			$p = mb_strpos($key, 'PROPERTY_');
			if ($p !== false && $p < 4)
			{
				$newIndex = mb_substr($key, 0, $p) . 'PROPERTY';
				$arFilter[$newIndex] ??= [];
				$arFilter[$newIndex][mb_substr($key, $p + 9)] = $val;
				unset($newIndex);
				unset($arFilter[$origKey]);
			}
			else
			{
				$p = strpos($key, 'SUBQUERY');
				if ($p !== false && $p < 4)
				{
					if (!empty($val) && is_array($val))
					{
						$arFilter[substr($key, 0, $p).'ID'] = static::SubQuery(
							$val['FIELD'],
							$val['FILTER']
						);
					}
					unset($arFilter[$origKey]);
				}
			}
		}

		if (isset($arFilter['LOGIC']) && $arFilter['LOGIC'] === 'OR')
		{
			$Logic = 'OR';
			unset($arFilter['LOGIC']);
			$bPropertyLeftJoin = true;
		}
		else
		{
			$Logic = 'AND';
		}

		if ($Logic === "AND" && $level === 0 && isset($arFilter['IBLOCK_ID']))
		{
			$f = new \Bitrix\Iblock\PropertyIndex\QueryBuilder($arFilter['IBLOCK_ID']);
			if ($f->isValid())
			{
				$arJoinProps['FC'] = $f->getFilterSql($arFilter, $arSqlSearch);
				$arJoinProps['FC_DISTINCT'] = $f->getDistinct();
			}
			unset($f);
		}

		foreach ($arFilter as $orig_key => $val)
		{
			$res = CIBlock::MkOperationFilter($orig_key);
			$key = $res['FIELD'];
			$cOperationType = $res['OPERATION'];

			//it was done before $key = strtoupper($key);

			switch ($key)
			{
			case "ACTIVE":
			case "DETAIL_TEXT_TYPE":
			case "PREVIEW_TEXT_TYPE":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "string_equal", $bFullJoinTmp, $cOperationType);
				break;
			case "NAME":
			case "XML_ID":
			case "TMP_ID":
			case "DETAIL_TEXT":
			case "PREVIEW_TEXT":
			case "CODE":
			case "TAGS":
			case "WF_COMMENTS":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "string", $bFullJoinTmp, $cOperationType);
				break;
			case "SEARCHABLE_CONTENT":
				if ($DB->IndexExists("b_iblock_element", array("SEARCHABLE_CONTENT")))
				{
					$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "fulltext", $bFullJoinTmp, $cOperationType);
				}
				else
				{
					if ($cOperationType == "FT")
						$cOperationType = "FTL";
					elseif ($cOperationType == "FTI")
						$cOperationType = "E";
					$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "string", $bFullJoinTmp, $cOperationType);
				}
				break;
			case "ID":
				if(is_object($val))
				{
					/** @var CIBlockElement $val */
					$val->prepareSql(array($val->strField), $val->arFilter, false, false);
					$arSqlSearch[] = 'BE.'.$key.(mb_substr($cOperationType, 0, 1) == "N"? ' NOT': '').' IN  (
						SELECT '.$val->sSelect.'
						FROM '.$val->sFrom.'
						WHERE 1=1
							'.$val->sWhere.'
						)'
					;
				}
				else
				{
					$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "number", $bFullJoinTmp, $cOperationType);
				}
				break;
			case "SHOW_COUNTER":
			case "WF_PARENT_ELEMENT_ID":
			case "WF_STATUS_ID":
			case "SORT":
			case "CREATED_BY":
			case "MODIFIED_BY":
			case "PREVIEW_PICTURE":
			case "DETAIL_PICTURE":
			case "IBLOCK_ID":
			case "IBLOCK_SECTION_ID":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.".$key, $val, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "TIMESTAMP_X":
			case "DATE_CREATE":
			case "SHOW_COUNTER_START":
				$sqlSearch = CIBlock::FilterCreateEx("BE.".$key, $val, "date", $bFullJoinTmp, $cOperationType);
				if($sqlSearch <> '')
				{
					$arSqlSearch[] = $sqlSearch;
				}
				break;
			case "EXTERNAL_ID":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.XML_ID", $val, "string", $bFullJoinTmp, $cOperationType);
				break;
			case "IBLOCK_TYPE":
				$arSqlSearch[] = CIBlock::FilterCreateEx("B.IBLOCK_TYPE_ID", $val, "string", $bFullJoinTmp, $cOperationType);
				break;
			case "CHECK_PERMISSIONS":
				if ($val === 'Y')
				{
					$minPermission = (string)($arFilter['MIN_PERMISSION'] ?? CIBlockRights::PUBLIC_READ);
					if (strlen($minPermission) !== 1)
					{
						$minPermission = CIBlockRights::PUBLIC_READ;
					}

					$permissionsBy = null;
					if (isset($arFilter['PERMISSIONS_BY']))
					{
						$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
						if ($permissionsBy < 0)
						{
							$permissionsBy = null;
						}
					}

					$iblockId = static::getSingleIblockIdFromFilter($arFilter);

					if ($permissionsBy !== null)
					{
						$permSQL = self::getCheckRightsSql($iblockId, $minPermission, $permissionsBy);
					}
					elseif (!$this->isAdmin)
					{
						$permSQL = self::getCheckRightsSql($iblockId, $minPermission);
					}

					unset(
						$iblockId,
						$permissionsBy,
						$minPermission,
					);
				}
				break;
			case "CHECK_BP_PERMISSIONS":
				if (IsModuleInstalled('bizproc') && !$this->isAdmin)
				{
					if(is_array($val))
					{
						$MODULE_ID = $DB->ForSQL($val["MODULE_ID"]);
						$ENTITY = $DB->ForSQL($val["ENTITY"]);
						$PERMISSION = $DB->ForSQL($val["PERMISSION"]);
						$arUserGroups = array();
						if(is_array($val["GROUPS"]))
						{
							$USER_ID = intval($val["USER_ID"]);
							foreach($val["GROUPS"] as $GROUP_ID)
							{
								$GROUP_ID = intval($GROUP_ID);
								if($GROUP_ID)
									$arUserGroups[$GROUP_ID] = $GROUP_ID;
							}
						}
						else
						{
							$USER_ID = 0;
						}
					}
					else
					{
						$MODULE_ID = "iblock";
						$ENTITY = "CIBlockDocument";
						$PERMISSION = $val;
						$arUserGroups = false;
						$USER_ID = 0;
					}

					if($PERMISSION == "read" || $PERMISSION == "write")
					{
						if(!is_array($arUserGroups) && is_object($USER))
						{
							$USER_ID = intval($USER->GetID());
							$arUserGroups = $USER->GetUserGroupArray();
						}

						if(!is_array($arUserGroups) || count($arUserGroups) <= 0)
							$arUserGroups = array(2);

						$arSqlSearch[] = "EXISTS (
							SELECT S.DOCUMENT_ID_INT
							FROM
							b_bp_workflow_state S
							INNER JOIN b_bp_workflow_permissions P ON S.ID = P.WORKFLOW_ID
							WHERE
								S.DOCUMENT_ID_INT = BE.ID
								AND S.MODULE_ID = '$MODULE_ID'
								AND S.ENTITY = '$ENTITY'
								AND P.PERMISSION = '$PERMISSION'
								AND (
									P.OBJECT_ID IN ('".implode("', '", $arUserGroups)."')
									OR (P.OBJECT_ID = 'Author' AND BE.CREATED_BY = $USER_ID)
									OR (P.OBJECT_ID = ".$DB->Concat("'USER_'", "'$USER_ID'").")
								)
						)";
					}
				}
				break;
			case "CHECK_BP_VIRTUAL_PERMISSIONS":
				if (
					IsModuleInstalled('bizproc')
					&& !$this->isAdmin
				)
				{
					if (!in_array($val, array("read", "create", "admin")))
						$val = "admin";

					$userId = is_object($USER)? (int)$USER->GetID(): 0;
					$arUserGroups = is_object($USER)? $USER->GetUserGroupArray(): false;

					if (!is_array($arUserGroups) || empty($arUserGroups))
						$arUserGroups = array(2);

					$arSqlSearch[] = "EXISTS (
						SELECT S.DOCUMENT_ID_INT
						FROM b_bp_workflow_state S
							INNER JOIN b_bp_workflow_permissions P ON S.ID = P.WORKFLOW_ID
						WHERE S.DOCUMENT_ID_INT = BE.ID
							AND S.MODULE_ID = 'bizproc'
							AND S.ENTITY = 'CBPVirtualDocument'
							AND
								(P.PERMISSION = '".$val."'
								AND (
									P.OBJECT_ID IN ('".implode("', '", $arUserGroups)."')
									OR (P.OBJECT_ID = 'Author' AND BE.CREATED_BY = ".$userId.")
									OR (P.OBJECT_ID = ".$DB->Concat("'USER_'", "'".$userId."'").")
								)
							)
					)";
				}
				break;
			case "LID":
			case "SITE_ID":
			case "IBLOCK_LID":
			case "IBLOCK_SITE_ID":
				$flt = CIBlock::FilterCreateEx("SITE_ID", $val, "string_equal", $bFullJoinTmp, $cOperationType);
				if ($flt !== '')
					$arSqlSearch[] = ($cOperationType == "N" ? "NOT " : "")."EXISTS (
						SELECT IBLOCK_ID FROM b_iblock_site WHERE IBLOCK_ID = B.ID
						AND ".$flt."
					)";
				break;
			case "DATE_ACTIVE_FROM":
				$sqlSearch = CIBlock::FilterCreateEx("BE.ACTIVE_FROM", $val, "date", $bFullJoinTmp, $cOperationType);
				if ($sqlSearch <> '')
				{
					$arSqlSearch[] = $sqlSearch;
				}
				break;
			case "DATE_ACTIVE_TO":
				$sqlSearch = CIBlock::FilterCreateEx("BE.ACTIVE_TO", $val, "date", $bFullJoinTmp, $cOperationType);
				if ($sqlSearch <> '')
				{
					$arSqlSearch[] = $sqlSearch;
				}
				break;
			case "IBLOCK_ACTIVE":
				$arSqlSearch[] = CIBlock::FilterCreateEx("B.ACTIVE", $val, "string_equal", $bFullJoinTmp, $cOperationType);
				break;
			case "IBLOCK_CODE":
				$arSqlSearch[] = CIBlock::FilterCreateEx("B.CODE", $val, "string", $bFullJoinTmp, $cOperationType);
				break;
			case "ID_ABOVE":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.ID", $val, "number_above", $bFullJoinTmp, $cOperationType);
				break;
			case "ID_LESS":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.ID", $val, "number_less", $bFullJoinTmp, $cOperationType);
				break;
			case "ACTIVE_FROM":
				$val = (string)$val;
				if ($val !== '')
				{
					$isCorrect = \CIBlock::isCorrectFullFormatDate($DB->ForSql($val));
					if ($isCorrect)
					{
						$arSqlSearch[] = "(BE.ACTIVE_FROM "
							. ($cOperationType == "N" ? "<" : ">=")
							. $DB->CharToDateFunction($DB->ForSql($val), "FULL")
							. ($cOperationType == "N" ? "" : " OR BE.ACTIVE_FROM IS NULL")
							. ")";
					}
				}
				break;
			case "ACTIVE_TO":
				$val = (string)$val;
				if ($val !== '')
				{
					$isCorrect = \CIBlock::isCorrectFullFormatDate($DB->ForSql($val));
					if ($isCorrect)
					{
						$arSqlSearch[] = "(BE.ACTIVE_TO "
							. ($cOperationType == "N" ? ">" : "<=")
							. $DB->CharToDateFunction($DB->ForSql($val), "FULL")
							. ($cOperationType == "N" ? "" : " OR BE.ACTIVE_TO IS NULL")
							. ")";
					}
				}
				break;
			case "ACTIVE_DATE":
				$val = (string)$val;
				if($val !== '')
					$arSqlSearch[] = ($cOperationType=="N"?" NOT":"")."((BE.ACTIVE_TO >= ".$DB->GetNowFunction()." OR BE.ACTIVE_TO IS NULL) AND (BE.ACTIVE_FROM <= ".$DB->GetNowFunction()." OR BE.ACTIVE_FROM IS NULL))";
				break;
			case "DATE_MODIFY_FROM":
				$val = (string)$val;
				if ($val !== '')
				{
					$isCorrect = \CIBlock::isCorrectFullFormatDate($DB->ForSql($val));
					if ($isCorrect)
					{
						$arSqlSearch[] = "(BE.TIMESTAMP_X " .
							($cOperationType == "N" ? "<" : ">=") . $DB->CharToDateFunction($DB->ForSql($val), "FULL") .
							($cOperationType == "N" ? "" : " OR BE.TIMESTAMP_X IS NULL") . ")";
					}
				}
				break;
			case "DATE_MODIFY_TO":
				$val = (string)$val;
				if ($val !== '')
				{
					$isCorrect = \CIBlock::isCorrectFullFormatDate($DB->ForSql($val));
					if ($isCorrect)
					{
						$arSqlSearch[] = "(BE.TIMESTAMP_X " .
							($cOperationType == "N" ? ">" : "<=") . $DB->CharToDateFunction($DB->ForSql($val), "FULL") .
							($cOperationType == "N" ? "" : " OR BE.TIMESTAMP_X IS NULL") . ")";
					}
				}
				break;
			case "WF_NEW":
				if($val=="Y" || $val=="N")
					$arSqlSearch[] = CIBlock::FilterCreateEx("BE.WF_NEW", "Y", "string_equal", $bFullJoinTmp, ($val=="Y"?false:true), false);
				break;
			case "MODIFIED_USER_ID":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.MODIFIED_BY", $val, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "CREATED_USER_ID":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.CREATED_BY", $val, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "RATING_USER_ID":
				$arSqlSearch[] = CIBlock::FilterCreateEx("RVV.USER_ID", $val, "number", $bFullJoinTmp, $cOperationType);
				$arJoinProps["RVV"] = self::addJoinTypeWhere([
					"bFullJoin" => $bFullJoinTmp,
				]);
				break;
			case "WF_STATUS":
				$arSqlSearch[] = CIBlock::FilterCreateEx("BE.WF_STATUS_ID", $val, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "WF_LOCK_STATUS":
				$val = (string)$val;
				if ($val !== '')
				{
					$lockInterval = (int)Main\Config\Option::get('workflow', 'MAX_LOCK_TIME', 60) * 60;
					$arSqlSearch[] =
						"(case when BE.WF_DATE_LOCK is null then 'green' when "
						. $helper->addSecondsToDateTime($lockInterval, 'BE.WF_DATE_LOCK')
						. " < " . $helper->getCurrentDateTimeFunction()
						. " then 'green' when BE.WF_LOCKED_BY = " . $this->userId
						. " then 'yellow' else 'red' end) = '" . $DB->ForSql($val) . "'"
					;
					unset($lockInterval);
				}
				break;
			case "WF_LAST_STATUS_ID":
				$arSqlSearch[] = "exists (
					select
						history.ID
					from
						b_iblock_element history
					where
						history.WF_PARENT_ELEMENT_ID = BE.ID
						and history.WF_STATUS_ID = ".intval($val)."
						and history.ID = (
							select max(history0.ID) LAST_ID
							from b_iblock_element history0
							where history0.WF_PARENT_ELEMENT_ID = BE.ID
						)
				)
				";
				break;
			case "SECTION_ACTIVE":
				if($arFilter["INCLUDE_SUBSECTIONS"]==="Y")
					$arSectionFilter["BS"][] = "BSubS.ACTIVE = 'Y'";
				else
					$arSectionFilter["BS"][] = "BS.ACTIVE = 'Y'";
				break;
			case "SECTION_GLOBAL_ACTIVE":
				if($arFilter["INCLUDE_SUBSECTIONS"]==="Y")
					$arSectionFilter["BS"][] = "BSubS.GLOBAL_ACTIVE = 'Y'";
				else
					$arSectionFilter["BS"][] = "BS.GLOBAL_ACTIVE = 'Y'";
				break;
			case "SUBSECTION":
				if (!is_array($val))
					$val=Array($val);
				//Find out margins of sections
				$arUnknownMargins = array();
				foreach($val as $i=>$section)
				{
					if(!is_array($section))
						$arUnknownMargins[intval($section)] = intval($section);
				}
				if (!empty($arUnknownMargins))
				{
					$rs = $DB->Query("SELECT ID, LEFT_MARGIN, RIGHT_MARGIN FROM b_iblock_section WHERE ID in (".implode(", ", $arUnknownMargins).")");
					while($ar = $rs->Fetch())
					{
						$arUnknownMargins[intval($ar["ID"])] = array(
							intval($ar["LEFT_MARGIN"]),
							intval($ar["RIGHT_MARGIN"]),
						);
					}
					foreach($val as $i=>$section)
					{
						if(!is_array($section))
							$val[$i] = $arUnknownMargins[intval($section)];
					}
				}
				//Now sort them out
				$arMargins = array();
				foreach($val as $i=>$section)
				{
					if(is_array($section) && (count($section) == 2))
					{
						$left = intval($section[0]);
						$right = intval($section[1]);
						if($left > 0 && $right > 0)
							$arMargins[$left] = $right;
					}
				}
				ksort($arMargins);
				//Remove subsubsections of the sections
				$prev_right = 0;
				foreach($arMargins as $left => $right)
				{
					if($right <= $prev_right)
						unset($arMargins[$left]);
					else
						$prev_right = $right;
				}

				if(isset($arFilter["INCLUDE_SUBSECTIONS"]) && $arFilter["INCLUDE_SUBSECTIONS"] === "Y")
					$bsAlias = "BSubS";
				else
					$bsAlias = "BS";

				$subsectionWhere = '';
				foreach ($arMargins as $left => $right)
				{
					if ($subsectionWhere !== '')
					{
						$subsectionWhere .= ($cOperationType === 'N' ? ' AND ' : ' OR ');
					}
					$subsectionWhere .= ($cOperationType == "N"? " NOT ": " ")."($bsAlias.LEFT_MARGIN >= ".$left." AND $bsAlias.RIGHT_MARGIN <= ".$right.")\n";;
				}

				if ($subsectionWhere !== '')
				{
					$arSectionFilter["BS"][] = "(".$subsectionWhere.")";
				}
				unset($subsectionWhere);
				break;
			case "SECTION_ID":
				if(!is_array($val))
					$val = array($val);

				$arSections = array();
				foreach($val as $section_id)
				{
					$section_id = (int)$section_id;
					$arSections[$section_id] = $section_id;
				}

				if($cOperationType=="N")
				{
					if (isset($arSections[0]))
					{
						$arSectionFilter["BE"][] = "BE.IN_SECTIONS<>'N'";
						$arSectionFilter["LOGIC"] = "AND";
						unset($arSections[0]);
						if(!empty($arSections))
							$arSectionFilter["BS"][] = "BS.ID NOT IN (".implode(", ", $arSections).")";
					}
					elseif(!empty($arSections))
					{
						$arSectionFilter["BE"][] = "BE.IN_SECTIONS='N'";
						$arSectionFilter["LOGIC"] = "OR";
						$arSectionFilter["BS"][] = "BS.ID NOT IN (".implode(", ", $arSections).")";
					}
				}
				else
				{
					if (isset($arSections[0]))
					{
						$allSections = (
							isset($arFilter["INCLUDE_SUBSECTIONS"])
							&& $arFilter["INCLUDE_SUBSECTIONS"] === "Y"
							&& count($arSections) == 1
						);
						if (!$allSections)
						{
							$arSectionFilter["BE"][] = "BE.IN_SECTIONS='N'";
							$arSectionFilter["LOGIC"] = "OR";
						}
						unset($arSections[0]);
					}
					if(!empty($arSections))
						$arSectionFilter["BS"][] = "BS.ID IN (".implode(", ", $arSections).")";
				}
				break;
			case "SECTION_CODE":
				if(!is_array($val))
					$val = array($val);

				$arSections = array();
				foreach($val as $section_code)
				{
					$section_code = $DB->ForSql($section_code);
					$arSections[$section_code] = $section_code;
				}

				if($cOperationType=="N")
				{
					if(array_key_exists("", $arSections))
					{
						$arSectionFilter["BE"][] = "BE.IN_SECTIONS<>'N'";
						$arSectionFilter["LOGIC"] = "AND";
						unset($arSections[""]);
						if(!empty($arSections))
							$arSectionFilter["BS"][] = "BS.CODE NOT IN ('".implode("', '", $arSections)."')";
					}
					elseif(!empty($arSections))
					{
						$arSectionFilter["BE"][] = "BE.IN_SECTIONS='N'";
						$arSectionFilter["LOGIC"] = "OR";
						$arSectionFilter["BS"][] = "BS.CODE NOT IN ('".implode("', '", $arSections)."')";
					}
				}
				else
				{
					if(array_key_exists("", $arSections))
					{
						$arSectionFilter["BE"][] = "BE.IN_SECTIONS='N'";
						$arSectionFilter["LOGIC"] = "OR";
						unset($arSections[""]);
					}
					if(!empty($arSections))
						$arSectionFilter["BS"][] = "BS.CODE IN ('".implode("', '", $arSections)."')";
				}
				break;
			case "PROPERTY":
				$iblockIds = CIBlock::_MergeIBArrays(
					$arFilter["IBLOCK_ID"] ?? false,
					$arFilter["IBLOCK_CODE"] ?? false,
					$arFilter["~IBLOCK_ID"] ?? false,
					$arFilter["~IBLOCK_CODE"] ?? false
				);
				foreach($val as $propID=>$propVAL)
				{
					$propertyFilter = CIBlock::MkOperationFilter($propID);
					$propertyFilter["LOGIC"] = $Logic;
					$propertyFilter["LEFT_JOIN"] = $bPropertyLeftJoin;

					if(preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $propertyFilter["FIELD"], $arMatch))
					{
						if (self::checkPropertyLinkIdentifier($propertyFilter['FIELD']))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($arMatch[1], $iblockIds);
							if (is_array($db_prop) && $db_prop["PROPERTY_TYPE"] == "E")
							{
								$propertyFilter["FIELD"] = $arMatch;
								CIBlockElement::MkPropertyFilter(
									$propertyFilter,
									$cOperationType,
									$propVAL,
									$db_prop,
									$arJoinProps,
									$arSqlSearch
								);
							}
						}
					}
					else
					{
						if (self::checkPropertyIdentifier($propertyFilter['FIELD']))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($propertyFilter["FIELD"], $iblockIds);
							if ($db_prop)
							{
								CIBlockElement::MkPropertyFilter(
									$propertyFilter,
									$cOperationType,
									$propVAL,
									$db_prop,
									$arJoinProps,
									$arSqlSearch
								);
							}
						}
					}
					unset($propertyFilter);
				}
				unset($iblockIds);
				break;
			default:
				if(is_numeric($orig_key))
				{
					if (is_array($val))
					{
						//Here is hint for better property resolution:
						if (!isset($val["~IBLOCK_ID"]))
						{
							if (isset($arFilter["IBLOCK_ID"]))
								$val["~IBLOCK_ID"] = $arFilter["IBLOCK_ID"];
							elseif (isset($arFilter["~IBLOCK_ID"]))
								$val["~IBLOCK_ID"] = $arFilter["~IBLOCK_ID"];
						}
						if (!isset($val["~IBLOCK_CODE"]))
						{
							if (isset($arFilter["IBLOCK_CODE"]))
								$val["~IBLOCK_CODE"] = $arFilter["IBLOCK_CODE"];
							elseif (isset($arFilter["~IBLOCK_CODE"]))
								$val["~IBLOCK_CODE"] = $arFilter["~IBLOCK_CODE"];
						}
						//Subfilter process
						$arSubSqlSearch = CIBlockElement::MkFilter($val, $arJoinProps, $arAddWhereFields, $level + 1, $bPropertyLeftJoin);
						if(trim($arSubSqlSearch[0], "\n\t") <> '')
						{
							$arSqlSearch[] = str_replace("\n\t\t\t", "\n\t\t\t\t", $arSubSqlSearch[0]);
						}
					}
				}
				elseif ($catalogIncluded && \CProductQueryBuilder::isValidField($key))
				{
					$catalogFields[$orig_key] = $val;
					$arAddWhereFields[$orig_key] = $val;
				}
				break;
			}
		}
		if ($catalogIncluded && !empty($catalogFields))
		{
			$catalogQueryResult = \CProductQueryBuilder::makeFilter($catalogFields);
			// catalog join set in \CIBlockElement::prepareSql
			if (!empty($catalogQueryResult) && !empty($catalogQueryResult['filter']))
			{
				$arSqlSearch = array_merge($arSqlSearch, $catalogQueryResult['filter']);
			}
			unset($catalogQueryResult);
		}
		unset($catalogFields);

		//SECTION sub filter
		$sWhere = "";
		foreach($arSectionFilter["BS"] as $strFilter)
		{
			if ($strFilter !== '')
			{
				if ($sWhere !== '')
					$sWhere .= " ".$Logic." ";
				$sWhere .= "(".$strFilter.")";
			}
		}

		$bINCLUDE_SUBSECTIONS = isset($arFilter["INCLUDE_SUBSECTIONS"]) && $arFilter["INCLUDE_SUBSECTIONS"] === "Y";

		if($sWhere !== '')
		{
			$sectionScope = "";
			if (isset($arFilter["SECTION_SCOPE"]))
			{
				if ($arFilter["SECTION_SCOPE"] == "IBLOCK")
					$sectionScope = "AND BSE.ADDITIONAL_PROPERTY_ID IS NULL";
				elseif ($arFilter["SECTION_SCOPE"] == "PROPERTY")
					$sectionScope = "AND BSE.ADDITIONAL_PROPERTY_ID IS NOT NULL";
				elseif (preg_match("/^PROPERTY_(\\d+)\$/", $arFilter["SECTION_SCOPE"], $match))
					$sectionScope = "AND BSE.ADDITIONAL_PROPERTY_ID = ".$match[1];
			}

			//Try to convert correlated subquery to join subquery
			if($level == 0 && $Logic == "AND" && !count($arSectionFilter["BE"]))
			{
				$arJoinProps["BES"] .= " INNER JOIN (
					SELECT DISTINCT BSE.IBLOCK_ELEMENT_ID
					FROM b_iblock_section_element BSE
					".($bINCLUDE_SUBSECTIONS? "
					INNER JOIN b_iblock_section BSubS ON BSE.IBLOCK_SECTION_ID = BSubS.ID
					INNER JOIN b_iblock_section BS ON (BSubS.IBLOCK_ID=BS.IBLOCK_ID
						AND BSubS.LEFT_MARGIN>=BS.LEFT_MARGIN
						AND BSubS.RIGHT_MARGIN<=BS.RIGHT_MARGIN)
					" : "
					INNER JOIN b_iblock_section BS ON BSE.IBLOCK_SECTION_ID = BS.ID
					")."
					WHERE (".$sWhere.")$sectionScope
					) BES ON BES.IBLOCK_ELEMENT_ID = BE.ID\n";
			}
			else
			{
				$arSqlSearch[] = "(".(count($arSectionFilter["BE"])? implode(" ".$arSectionFilter["LOGIC"]." ", $arSectionFilter["BE"])." ".$arSectionFilter["LOGIC"]: "")." EXISTS (
					SELECT BSE.IBLOCK_ELEMENT_ID
					FROM b_iblock_section_element BSE
					".($bINCLUDE_SUBSECTIONS? "
					INNER JOIN b_iblock_section BSubS ON BSE.IBLOCK_SECTION_ID = BSubS.ID
					INNER JOIN b_iblock_section BS ON (BSubS.IBLOCK_ID=BS.IBLOCK_ID
						AND BSubS.LEFT_MARGIN>=BS.LEFT_MARGIN
						AND BSubS.RIGHT_MARGIN<=BS.RIGHT_MARGIN)
					" : "
					INNER JOIN b_iblock_section BS ON BSE.IBLOCK_SECTION_ID = BS.ID
					")."
					WHERE BSE.IBLOCK_ELEMENT_ID = BE.ID
					AND (".$sWhere.")$sectionScope
					))";
			}
		}
		elseif(!empty($arSectionFilter["BE"]))
		{
			foreach($arSectionFilter["BE"] as $strFilter)
				$arSqlSearch[] = $strFilter;
		}

		$sWhere = "";
		foreach($arSqlSearch as $strFilter)
		{
			if(trim($strFilter, "\n\t") !== '')
			{
				if ($sWhere !== '')
					$sWhere .= "\n\t\t\t\t".$Logic." ";
				else
					$sWhere .= "\n\t\t\t\t";
				$sWhere .= "(".$strFilter.")";
			}
		}

		$arSqlSearch = array("\n\t\t\t".$sWhere."\n\t\t\t");

		$SHOW_BP_NEW = "";
		$SHOW_NEW = isset($arFilter["SHOW_NEW"]) && $arFilter["SHOW_NEW"]=="Y"? "Y": "N";

		if(
			$SHOW_NEW == "Y"
			&& isset($arFilter["SHOW_BP_NEW"])
			&& is_array($arFilter["SHOW_BP_NEW"])
			&& IsModuleInstalled('bizproc')
			&& !$this->isAdmin
		)
		{

			$MODULE_ID = $DB->ForSQL($arFilter["SHOW_BP_NEW"]["MODULE_ID"]);
			$ENTITY = $DB->ForSQL($arFilter["SHOW_BP_NEW"]["ENTITY"]);
			$PERMISSION = $DB->ForSQL($arFilter["SHOW_BP_NEW"]["PERMISSION"]);
			$arUserGroups = array();
			if(is_array($arFilter["SHOW_BP_NEW"]["GROUPS"]))
			{
				$USER_ID = intval($arFilter["SHOW_BP_NEW"]["USER_ID"]);
				foreach($arFilter["SHOW_BP_NEW"]["GROUPS"] as $GROUP_ID)
				{
					$GROUP_ID = intval($GROUP_ID);
					if($GROUP_ID)
						$arUserGroups[$GROUP_ID] = $GROUP_ID;
				}
			}
			else
			{
				$USER_ID = false;
				$arUserGroups = false;
			}

			if($PERMISSION == "read" || $PERMISSION == "write")
			{
				if(!is_array($arUserGroups))
				{
					$USER_ID = is_object($USER)? (int)$USER->GetID(): 0;
					if(is_object($USER))
						$arUserGroups = $USER->GetUserGroupArray();
				}
				if(!is_array($arUserGroups) || empty($arUserGroups))
					$arUserGroups = array(2);

				$SHOW_BP_NEW = " AND EXISTS (
					SELECT S.DOCUMENT_ID_INT
					FROM
					b_bp_workflow_state S
					INNER JOIN b_bp_workflow_permissions P ON S.ID = P.WORKFLOW_ID
					WHERE
						S.DOCUMENT_ID_INT = BE.ID
						AND S.MODULE_ID = '$MODULE_ID'
						AND S.ENTITY = '$ENTITY'
						AND P.PERMISSION = '$PERMISSION'
						AND (
							P.OBJECT_ID IN ('".implode("', '", $arUserGroups)."')
							OR (P.OBJECT_ID = 'Author' AND BE.CREATED_BY = $USER_ID)
							OR (P.OBJECT_ID = ".$DB->Concat("'USER_'", "'$USER_ID'").")
						)
				)";
			}
		}

		if(!isset($arFilter["SHOW_HISTORY"]) || $arFilter["SHOW_HISTORY"] != "Y")
			$arSqlSearch[] = "((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL)".($SHOW_NEW == "Y"? " OR (BE.WF_NEW='Y'".$SHOW_BP_NEW.")": "").")";

		if($permSQL)
			$arSqlSearch[] = $permSQL;

		if(isset($this) && is_object($this) && isset($this->subQueryProp))
		{
			//Subquery list value should not be null
			$this->MkPropertyFilter(
				CIBlock::MkOperationFilter("!".mb_substr($this->strField, 9))
				,"NE"
				,false
				,$this->subQueryProp, $arJoinProps, $arSqlSearch
			);
		}

		return $arSqlSearch;
	}

	public function MkPropertyFilter($res, $cOperationType, $propVAL, $db_prop, &$arJoinProps, &$arSqlSearch)
	{
		global $DB;

		$propertyId = $db_prop['ID'];

		if($res["OPERATION"]!="E")
			$cOperationType = $res["OPERATION"];

		//Tables counters
		if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"]=="N")
		{
			if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
				$iPropCnt = count($arJoinProps["FPS"]);
			else
				$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
		}
		else
		{
			if(!array_key_exists($db_prop["ID"], $arJoinProps["FPV"]))
				$iPropCnt = count($arJoinProps["FPV"]);
			else
				$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];
		}

		if(!is_array($res["FIELD"]) && (mb_substr(mb_strtoupper($res["FIELD"]), -6) == '_VALUE'))
			$bValueEnum = true;
		else
			$bValueEnum = false;

		if($db_prop["PROPERTY_TYPE"] == "L" && $bValueEnum)
		{
			if(!array_key_exists($db_prop["ID"], $arJoinProps["FPEN"]))
				$iFpenCnt = count($arJoinProps["FPEN"]);
			else
				$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];
		}
		else
		{
			$iFpenCnt = false;
		}

		if(is_array($res["FIELD"]))
		{
			if(!array_key_exists($db_prop["ID"], $arJoinProps["BE"]))
				$iElCnt = count($arJoinProps["BE"]);
			else
				$iElCnt = $arJoinProps["BE"][$db_prop["ID"]]["CNT"];
		}
		else
		{
			$iElCnt = false;
		}

		$bFullJoin = false;
		$r = "";

		if(is_array($res["FIELD"]))
		{
			switch($res["FIELD"][2]."")
			{
			case "ACTIVE":
			case "DETAIL_TEXT_TYPE":
			case "PREVIEW_TEXT_TYPE":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".".$res["FIELD"][2], $propVAL, "string_equal", $bFullJoinTmp, $cOperationType);
				break;
			case "EXTERNAL_ID":
				$res["FIELD"][2] = "XML_ID";
			case "NAME":
			case "XML_ID":
			case "TMP_ID":
			case "DETAIL_TEXT":
			case "SEARCHABLE_CONTENT":
			case "PREVIEW_TEXT":
			case "CODE":
			case "TAGS":
			case "WF_COMMENTS":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".".$res["FIELD"][2], $propVAL, "string", $bFullJoinTmp, $cOperationType);
				break;
			case "ID":
			case "SHOW_COUNTER":
			case "WF_PARENT_ELEMENT_ID":
			case "WF_STATUS_ID":
			case "SORT":
			case "CREATED_BY":
			case "MODIFIED_BY":
			case "PREVIEW_PICTURE":
			case "DETAIL_PICTURE":
			case "IBLOCK_ID":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".".$res["FIELD"][2], $propVAL, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "TIMESTAMP_X":
			case "DATE_CREATE":
			case "SHOW_COUNTER_START":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".".$res["FIELD"][2], $propVAL, "date", $bFullJoinTmp, $cOperationType);
				break;
			case "DATE_ACTIVE_FROM":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".ACTIVE_FROM", $propVAL, "date", $bFullJoinTmp, $cOperationType);
				break;
			case "DATE_ACTIVE_TO":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".ACTIVE_TO", $propVAL, "date", $bFullJoinTmp, $cOperationType);
				break;
			case "ACTIVE_FROM":
				if($propVAL <> '')
					$r = "(BE".$iElCnt.".ACTIVE_FROM ".($cOperationType=="N"?"<":">=").$DB->CharToDateFunction($DB->ForSql($propVAL), "FULL").($cOperationType=="N"?"":" OR BE".$iElCnt.".ACTIVE_FROM IS NULL").")";
				break;
			case "ACTIVE_TO":
				if($propVAL <> '')
					$r = "(BE".$iElCnt.".ACTIVE_TO ".($cOperationType=="N"?">":"<=").$DB->CharToDateFunction($DB->ForSql($propVAL), "FULL").($cOperationType=="N"?"":" OR BE".$iElCnt.".ACTIVE_TO IS NULL").")";
				break;
			case "ACTIVE_DATE":
				if($propVAL <> '')
					$r = ($cOperationType=="N"?" NOT":"")."((BE".$iElCnt.".ACTIVE_TO >= ".$DB->GetNowFunction()." OR BE".$iElCnt.".ACTIVE_TO IS NULL) AND (BE".$iElCnt.".ACTIVE_FROM <= ".$DB->GetNowFunction()." OR BE".$iElCnt.".ACTIVE_FROM IS NULL))";
				break;
			case "DATE_MODIFY_FROM":
				if($propVAL <> '')
					$r = "(BE".$iElCnt.".TIMESTAMP_X ".
						( $cOperationType=="N" ? "<" : ">=" ).$DB->CharToDateFunction($DB->ForSql($propVAL), "FULL").
						( $cOperationType=="N" ? ""  : " OR BE".$iElCnt.".TIMESTAMP_X IS NULL").")";
				break;
			case "DATE_MODIFY_TO":
				if($propVAL <> '')
					$r = "(BE".$iElCnt.".TIMESTAMP_X ".
						( $cOperationType=="N" ? ">" : "<=" ).$DB->CharToDateFunction($DB->ForSql($propVAL), "FULL").
						( $cOperationType=="N" ? ""  : " OR BE".$iElCnt.".TIMESTAMP_X IS NULL").")";
				break;
			case "MODIFIED_USER_ID":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".MODIFIED_BY", $propVAL, "number", $bFullJoinTmp, $cOperationType);
				break;
			case "CREATED_USER_ID":
				$r = CIBlock::FilterCreateEx("BE".$iElCnt.".CREATED_BY", $propVAL, "number", $bFullJoinTmp, $cOperationType);
				break;
			}
		}
		else
		{
			if(!is_array($propVAL))
				$propVAL = array($propVAL);

			if($db_prop["PROPERTY_TYPE"]=="L")
			{
				if($bValueEnum)
					$r = CIBlock::FilterCreateEx("FPEN".$iFpenCnt.".VALUE", $propVAL, "string", $bFullJoin, $cOperationType);
				elseif($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
					$r = CIBlock::FilterCreateEx("FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $bFullJoin, $cOperationType);
				else
					$r = CIBlock::FilterCreateEx("FPV".$iPropCnt.".VALUE_ENUM", $propVAL, "number", $bFullJoin, $cOperationType);
			}
			elseif($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
			{
				if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
					$r = CIBlock::FilterCreateEx("FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $bFullJoin, $cOperationType);
				else
					$r = CIBlock::FilterCreateEx("FPV".$iPropCnt.".VALUE_NUM", $propVAL, "number", $bFullJoin, $cOperationType);
			}
			else
			{
				if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
					$r = CIBlock::FilterCreateEx("FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $bFullJoin, $cOperationType);
				else
					$r = CIBlock::FilterCreateEx("FPV".$iPropCnt.".VALUE", $propVAL, "string", $bFullJoin, $cOperationType);
			}
		}

		if($r <> '')
		{
			if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"]=="N")
			{
				if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
					$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = $iPropCnt;
			}
			else
			{
				$arJoinProps['FP'][$propertyId] ??= [
					'CNT' => count($arJoinProps['FP']),
					'bFullJoin' => false,
				];
				$arJoinProps['FP'][$propertyId] = self::addJoinTypeWhere($arJoinProps['FP'][$propertyId]);

				if (isset($res["LEFT_JOIN"]) && $res["LEFT_JOIN"])
				{
					$arJoinProps["FP"][$db_prop["ID"]]["bFullJoin"] &= $bFullJoin;
				}
				else
				{
					$arJoinProps["FP"][$db_prop["ID"]]["bFullJoin"] |= $bFullJoin;
				}

				$arJoinProps['FPV'][$propertyId] ??= [
					'CNT' => $iPropCnt,
					'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
					'MULTIPLE' => $db_prop['MULTIPLE'],
					'VERSION' => $db_prop['VERSION'],
					'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
					'bFullJoin' => false,
				];
				$arJoinProps['FPV'][$propertyId] = self::addJoinTypeWhere($arJoinProps['FPV'][$propertyId]);

				if(isset($res["LEFT_JOIN"]) && $res["LEFT_JOIN"])
				{
					$arJoinProps["FPV"][$db_prop["ID"]]["bFullJoin"] &= $bFullJoin;
				}
				else
				{
					$arJoinProps["FPV"][$db_prop["ID"]]["bFullJoin"] |= $bFullJoin;
				}
			}

			if($db_prop["PROPERTY_TYPE"]=="L" && $bValueEnum)
			{
				$arJoinProps['FPEN'][$propertyId] ??= [
					'CNT' => $iFpenCnt,
					'MULTIPLE' => $db_prop['MULTIPLE'],
					'VERSION' => $db_prop['VERSION'],
					'ORIG_ID' => $db_prop['ORIG_ID'],
					'JOIN' => $iPropCnt,
					'bFullJoin' => false,
				];
				$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeWhere($arJoinProps['FPEN'][$propertyId]);

				if(isset($res["LEFT_JOIN"]) && $res["LEFT_JOIN"])
				{
					$arJoinProps["FPEN"][$db_prop["ID"]]["bFullJoin"] &= $bFullJoin;
				}
				else
				{
					$arJoinProps["FPEN"][$db_prop["ID"]]["bFullJoin"] |= $bFullJoin;
				}
			}

			if(is_array($res["FIELD"]))
			{
				$arJoinProps['BE'][$propertyId] ??= [
					'CNT' => $iElCnt,
					'MULTIPLE' => $db_prop['MULTIPLE'],
					'VERSION' => $db_prop['VERSION'],
					'ORIG_ID' => $db_prop['ORIG_ID'],
					'JOIN' => $iPropCnt,
					'bJoinIBlock' => false,
					'bJoinSection' => false,
				];
				$arJoinProps['BE'][$propertyId] = self::addJoinTypeWhere($arJoinProps['BE'][$propertyId]);
			}

			$arSqlSearch[] = $r;
		}
	}

	public function MkPropertyOrder($by, $order, $bSort, $db_prop, &$arJoinProps, &$arSqlOrder)
	{
		global $DB;
		static $arJoinEFields = false;

		$propertyId = $db_prop['ID'];

		//Tables counters
		if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"]=="N")
		{
			if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
				$iPropCnt = count($arJoinProps["FPS"]);
			else
				$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
		}
		else
		{
			if(!array_key_exists($db_prop["ID"], $arJoinProps["FPV"]))
				$iPropCnt = count($arJoinProps["FPV"]);
			else
				$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];
		}

		if($db_prop["PROPERTY_TYPE"] == "L")
		{
			if(!array_key_exists($db_prop["ID"], $arJoinProps["FPEN"]))
				$iFpenCnt = count($arJoinProps["FPEN"]);
			else
				$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];
		}
		else
		{
			$iFpenCnt = -1;
		}

		$iElCnt = -1;
		$db_jprop = false;
		$ijPropCnt = -1;
		$ijFpenCnt = -1;

		if(is_array($by))
		{
			if(!$arJoinEFields) $arJoinEFields = array(
				"ID" => "BE#i#.ID",
				"TIMESTAMP_X" => "BE#i#.TIMESTAMP_X",
				"MODIFIED_BY" => "BE#i#.MODIFIED_BY",
				"CREATED" => "BE#i#.DATE_CREATE",
				"CREATED_DATE" => $DB->DateFormatToDB("YYYY.MM.DD", "BE#i#.DATE_CREATE"),
				"CREATED_BY" => "BE#i#.CREATED_BY",
				"IBLOCK_ID" => "BE#i#.IBLOCK_ID",
				"ACTIVE" => "BE#i#.ACTIVE",
				"ACTIVE_FROM" => "BE#i#.ACTIVE_FROM",
				"ACTIVE_TO" => "BE#i#.ACTIVE_TO",
				"SORT" => "BE#i#.SORT",
				"NAME" => "BE#i#.NAME",
				"SHOW_COUNTER" => "BE#i#.SHOW_COUNTER",
				"SHOW_COUNTER_START" => "BE#i#.SHOW_COUNTER_START",
				"CODE" => "BE#i#.CODE",
				"TAGS" => "BE#i#.TAGS",
				"XML_ID" => "BE#i#.XML_ID",
				"STATUS" => "BE#i#.WF_STATUS_ID",
			);

			$jProp_ID = mb_substr($by[2], 9);
			$db_jprop = CIBlockProperty::GetPropertyArray($jProp_ID, CIBlock::_MergeIBArrays($db_prop["LINK_IBLOCK_ID"]));
			if ($bSort && $db_jprop["PROPERTY_TYPE"] != "L")
				return;

			//Joined Elements Field
			if(array_key_exists($by[2], $arJoinEFields))
			{
				//Then join elements
				if(!array_key_exists($db_prop["ID"], $arJoinProps["BE"]))
					$iElCnt = count($arJoinProps["BE"]);
				else
					$iElCnt = $arJoinProps["BE"][$db_prop["ID"]]["CNT"];

				$arSqlOrder[$by[0]] = CIBlock::_Order(str_replace("#i#", $iElCnt, $arJoinEFields[$by[2]]), $order, "desc");
			}
			elseif(mb_substr($by[2], 0, 9) == "PROPERTY_")
			{
				if(is_array($db_jprop))
				{
					//join elements
					if(!array_key_exists($db_prop["ID"], $arJoinProps["BE"]))
						$iElCnt = count($arJoinProps["BE"]);
					else
						$iElCnt = $arJoinProps["BE"][$db_prop["ID"]]["CNT"];

					if($db_jprop["VERSION"] == 2 && $db_jprop["MULTIPLE"]=="N")
					{
						if(!array_key_exists($db_jprop["IBLOCK_ID"], $arJoinProps["BE_FPS"]))
							$ijPropCnt = count($arJoinProps["BE_FPS"]);
						else
							$ijPropCnt = $arJoinProps["BE_FPS"][$db_jprop["IBLOCK_ID"]]["CNT"];
					}
					else
					{
						if(!array_key_exists($db_jprop["ID"], $arJoinProps["BE_FPV"]))
							$ijPropCnt = count($arJoinProps["BE_FPV"]);
						else
							$ijPropCnt = $arJoinProps["BE_FPV"][$db_jprop["ID"]]["CNT"];
					}

					if($db_jprop["PROPERTY_TYPE"] == "L")
					{
						if(!array_key_exists($db_jprop["ID"], $arJoinProps["BE_FPEN"]))
							$ijFpenCnt = count($arJoinProps["BE_FPEN"]);
						else
							$ijFpenCnt = $arJoinProps["BE_FPEN"][$db_jprop["ID"]]["CNT"];
					}

					if($db_jprop["PROPERTY_TYPE"]=="L" && $bSort)
						$arSqlOrder["PROPERTY_".$by[1]."_".$by[2]] = CIBlock::_Order("JFPEN".$ijFpenCnt.".SORT", $order, "desc");
					elseif($db_jprop["PROPERTY_TYPE"]=="L")
						$arSqlOrder["PROPERTY_".$by[1]."_".$by[2]] = CIBlock::_Order("JFPEN".$ijFpenCnt.".VALUE", $order, "desc");
					elseif($db_jprop["VERSION"]==2 && $db_jprop["MULTIPLE"]=="N")
						$arSqlOrder["PROPERTY_".$by[1]."_".$by[2]] = CIBlock::_Order("JFPS".$ijPropCnt.".PROPERTY_".$db_jprop["ORIG_ID"], $order, "desc");
					elseif($db_jprop["PROPERTY_TYPE"]=="N")
						$arSqlOrder["PROPERTY_".$by[1]."_".$by[2]] = CIBlock::_Order("JFPV".$ijPropCnt.".VALUE_NUM", $order, "desc");
					else
						$arSqlOrder["PROPERTY_".$by[1]."_".$by[2]] = CIBlock::_Order("JFPV".$ijPropCnt.".VALUE", $order, "desc");

				}
			}
		}
		else
		{
			if ($bSort && $db_prop["PROPERTY_TYPE"] != "L")
				return;

			if($db_prop["PROPERTY_TYPE"]=="L" && $bSort)
				$arSqlOrder[$by] = CIBlock::_Order("FPEN".$iFpenCnt.".SORT", $order, "desc");
			elseif($db_prop["PROPERTY_TYPE"]=="L")
				$arSqlOrder[$by] = CIBlock::_Order("FPEN".$iFpenCnt.".VALUE", $order, "desc");
			elseif($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
				$arSqlOrder[$by] = CIBlock::_Order("FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"], $order, "desc");
			elseif($db_prop["PROPERTY_TYPE"]=="N")
				$arSqlOrder[$by] = CIBlock::_Order("FPV".$iPropCnt.".VALUE_NUM", $order, "desc");
			else
				$arSqlOrder[$by] = CIBlock::_Order("FPV".$iPropCnt.".VALUE", $order, "desc");
		}

		//Pass join "commands" up there
		if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"]=="N")
		{
			if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
				$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = $iPropCnt;
		}
		else
		{
			$arJoinProps['FP'][$propertyId] ??= [
				'CNT' => count($arJoinProps['FP']),
				'bFullJoin' => false,
			];
			$arJoinProps['FP'][$propertyId] = self::addJoinTypeOrder($arJoinProps['FP'][$propertyId]);

			$arJoinProps['FPV'][$propertyId] ??= [
				'CNT' => $iPropCnt,
				'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
				'MULTIPLE' => $db_prop['MULTIPLE'],
				'VERSION' => $db_prop['VERSION'],
				'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
				'bFullJoin' => false,
			];
			$arJoinProps['FPV'][$propertyId] = self::addJoinTypeOrder($arJoinProps['FPV'][$propertyId]);
		}

		if ($iFpenCnt >= 0)
		{
			$arJoinProps['FPEN'][$propertyId] ??= [
				'CNT' => $iFpenCnt,
				'MULTIPLE' => $db_prop['MULTIPLE'],
				'VERSION' => $db_prop['VERSION'],
				'ORIG_ID' => $db_prop['ORIG_ID'],
				'JOIN' => $iPropCnt,
				'bFullJoin' => false,
			];
			$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeOrder($arJoinProps['FPEN'][$propertyId]);
		}

		if($iElCnt >= 0)
		{
			$arJoinProps['BE'][$propertyId] ??= [
				'CNT' => $iElCnt,
				'MULTIPLE' => $db_prop['MULTIPLE'],
				'VERSION' => $db_prop['VERSION'],
				'ORIG_ID' => $db_prop['ORIG_ID'],
				'JOIN' => $iPropCnt,
				'bJoinIBlock' => false,
				'bJoinSection' => false,
			];
			$arJoinProps['BE'][$propertyId] = self::addJoinTypeOrder($arJoinProps['BE'][$propertyId]);

			if(is_array($db_jprop))
			{
				if($db_jprop["VERSION"] == 2 && $db_jprop["MULTIPLE"]=="N")
				{
					$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']] ??= [
						'CNT' => $ijPropCnt,
						'JOIN' => $iElCnt,
					];
					$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']] = self::addJoinTypeOrder(
						$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']]
					);
				}
				else
				{
					$arJoinProps['BE_FP'][$db_jprop['ID']] ??= [
						"CNT" => count($arJoinProps["BE_FP"]),
						"JOIN" => $iElCnt,
						"bFullJoin" => false,
					];
					$arJoinProps['BE_FP'][$db_jprop['ID']] = self::addJoinTypeOrder($arJoinProps['BE_FP'][$db_jprop['ID']]);

					$arJoinProps['BE_FPV'][$db_jprop['ID']] ??= [
						'CNT' => $ijPropCnt,
						'IBLOCK_ID' => $db_jprop['IBLOCK_ID'],
						'MULTIPLE' => $db_jprop['MULTIPLE'],
						'VERSION' => $db_jprop['VERSION'],
						'JOIN' => $arJoinProps['BE_FP'][$db_jprop['ID']]['CNT'],
						'BE_JOIN' => $iElCnt,
						'bFullJoin' => false,
					];
					$arJoinProps['BE_FPV'][$db_jprop['ID']] = self::addJoinTypeOrder(
						$arJoinProps['BE_FPV'][$db_jprop['ID']]
					);
				}

				if ($ijFpenCnt >= 0)
				{
					$arJoinProps['BE_FPEN'][$db_jprop['ID']] ??= [
						'CNT' => $ijFpenCnt,
						'MULTIPLE' => $db_jprop['MULTIPLE'],
						'VERSION' => $db_jprop['VERSION'],
						'ORIG_ID' => $db_jprop['ORIG_ID'],
						'JOIN' => $ijPropCnt,
						'bFullJoin' => false,
					];
					$arJoinProps['BE_FPEN'][$db_jprop['ID']] = self::addJoinTypeOrder(
						$arJoinProps['BE_FPEN'][$db_jprop['ID']]
					);
				}
			}
		}
	}

	public function MkPropertyGroup($db_prop, &$arJoinProps, $bSort = false)
	{
		$propertyId = $db_prop['ID'];

		if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"]=="N")
		{
			if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
				$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
			$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
		}
		else
		{
			//Join property metadata table
			$arJoinProps['FP'][$propertyId] ??= [
				'CNT' => count($arJoinProps['FP']),
				'bFullJoin' => false,
			];
			$arJoinProps['FP'][$propertyId] = self::addJoinTypeGroup($arJoinProps['FP'][$propertyId]);

			$arJoinProps['FPV'][$propertyId] ??= [
				'CNT' => count($arJoinProps['FPV']),
				'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
				'MULTIPLE' => $db_prop['MULTIPLE'],
				'VERSION' => $db_prop['VERSION'],
				'ORIG_ID' => $db_prop['ORIG_ID'],
				'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
				'bFullJoin' => false,
			];
			$arJoinProps['FPV'][$propertyId] = self::addJoinTypeGroup($arJoinProps['FPV'][$propertyId]);

			$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];
		}

		if($db_prop["PROPERTY_TYPE"]=="L")
		{
			$arJoinProps['FPEN'][$propertyId] ??= [
				'CNT' => count($arJoinProps['FPEN']),
				'MULTIPLE' => $db_prop['MULTIPLE'],
				'VERSION' => $db_prop['VERSION'],
				'ORIG_ID' => $db_prop['ORIG_ID'],
				'JOIN' => $iPropCnt,
				'bFullJoin' => false,
			];
			$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeGroup($arJoinProps['FPEN'][$propertyId]);

			$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];

			return ($bSort? ", FPEN".$iFpenCnt.".SORT": ", FPEN".$iFpenCnt.".VALUE, FPEN".$iFpenCnt.".ID");
		}
		elseif($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
		{
			return ", FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"];
		}
		elseif($db_prop["PROPERTY_TYPE"]=="N")
		{
			return ", FPV".$iPropCnt.".VALUE, FPV".$iPropCnt.".VALUE_NUM";
		}
		else
		{
			return ", FPV".$iPropCnt.".VALUE";
		}
	}

	public function MkPropertySelect($PR_ID, $db_prop, &$arJoinProps, $bWasGroup, $sGroupBy, &$sSelect, $bSort = false)
	{
		global $DB;

		if($bSort && $db_prop["PROPERTY_TYPE"] != "L")
			return;

		static $arJoinEFields = false;

		$propertyId = $db_prop['ID'];

		//define maximum alias length
		$mal = false;

		$bSubQuery = isset($this) && is_object($this) && isset($this->subQueryProp);

		$arSelect = array();

		//Joined elements
		if(is_array($PR_ID))
		{
			if(!$arJoinEFields) $arJoinEFields = array(
				"ID" => "BE#i#.ID",
				"TIMESTAMP_X" => $DB->DateToCharFunction("BE#i#.TIMESTAMP_X"),
				"MODIFIED_BY" => "BE#i#.MODIFIED_BY",
				"DATE_CREATE" => $DB->DateToCharFunction("BE#i#.DATE_CREATE"),
				"CREATED_BY" => "BE#i#.CREATED_BY",
				"IBLOCK_ID" => "BE#i#.IBLOCK_ID",
				"ACTIVE" => "BE#i#.ACTIVE",
				"ACTIVE_FROM" => $DB->DateToCharFunction("BE#i#.ACTIVE_FROM"),
				"ACTIVE_TO" => $DB->DateToCharFunction("BE#i#.ACTIVE_TO"),
				"SORT" => "BE#i#.SORT",
				"NAME" => "BE#i#.NAME",
				"PREVIEW_PICTURE" => "BE#i#.PREVIEW_PICTURE",
				"PREVIEW_TEXT" => "BE#i#.PREVIEW_TEXT",
				"PREVIEW_TEXT_TYPE" => "BE#i#.PREVIEW_TEXT_TYPE",
				"DETAIL_PICTURE" => "BE#i#.DETAIL_PICTURE",
				"DETAIL_TEXT" => "BE#i#.DETAIL_TEXT",
				"DETAIL_TEXT_TYPE" => "BE#i#.DETAIL_TEXT_TYPE",
				"SHOW_COUNTER" => "BE#i#.SHOW_COUNTER",
				"SHOW_COUNTER_START" => $DB->DateToCharFunction("BE#i#.SHOW_COUNTER_START"),
				"CODE" => "BE#i#.CODE",
				"TAGS" => "BE#i#.TAGS",
				"XML_ID" => "BE#i#.XML_ID",
				"IBLOCK_SECTION_ID" => "BE#i#.IBLOCK_SECTION_ID",
				"IBLOCK_TYPE_ID"=>"B#i#.IBLOCK_TYPE_ID",
				"IBLOCK_CODE"=>"B#i#.CODE",
				"IBLOCK_NAME"=>"B#i#.NAME",
				"IBLOCK_EXTERNAL_ID"=>"B#i#.XML_ID",
				"DETAIL_PAGE_URL" => "
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(B#i#.DETAIL_PAGE_URL, '#ID#', BE#i#.ID)
					, '#ELEMENT_ID#', BE#i#.ID)
					, '#CODE#', ".$DB->IsNull("BE#i#.CODE", "''").")
					, '#ELEMENT_CODE#', ".$DB->IsNull("BE#i#.CODE", "''").")
					, '#EXTERNAL_ID#', ".$DB->IsNull("BE#i#.XML_ID", "''").")
					, '#IBLOCK_TYPE_ID#', B#i#.IBLOCK_TYPE_ID)
					, '#IBLOCK_ID#', BE#i#.IBLOCK_ID)
					, '#IBLOCK_CODE#', ".$DB->IsNull("B#i#.CODE", "''").")
					, '#IBLOCK_EXTERNAL_ID#', ".$DB->IsNull("B#i#.XML_ID", "''").")
					, '#SITE_DIR#', '".$DB->ForSQL(SITE_DIR)."')
					, '#SERVER_NAME#', '".$DB->ForSQL(SITE_SERVER_NAME)."')
					, '#SECTION_ID#', ".$DB->IsNull("BE#i#.IBLOCK_SECTION_ID", "''").")
					, '#SECTION_CODE#', ".$DB->IsNull("BS#i#.CODE", "''").")
				",
				"LIST_PAGE_URL" => "
					replace(
					replace(
					replace(
					replace(
					replace(
					replace(B#i#.LIST_PAGE_URL, '#IBLOCK_TYPE_ID#', B#i#.IBLOCK_TYPE_ID)
					, '#IBLOCK_ID#', BE#i#.IBLOCK_ID)
					, '#IBLOCK_CODE#', ".$DB->IsNull("B#i#.CODE", "''").")
					, '#IBLOCK_EXTERNAL_ID#', ".$DB->IsNull("B#i#.XML_ID", "''").")
					, '#SITE_DIR#', '".$DB->ForSQL(SITE_DIR)."')
					, '#SERVER_NAME#', '".$DB->ForSQL(SITE_SERVER_NAME)."')
				",
			);

			//Joined Elements Fields
			if(array_key_exists($PR_ID[2], $arJoinEFields))
			{

				if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"] == "N")
				{
					//Join properties table if needed
					if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
						$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
					$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
				}
				else
				{
					//Join property metadata table
					$arJoinProps['FP'][$propertyId] ??= [
						'CNT' => count($arJoinProps['FP']),
						'bFullJoin' => false,
					];
					$arJoinProps['FP'][$propertyId] = self::addJoinTypeSelect($arJoinProps['FP'][$propertyId]);

					//Join multiple values properties table if needed
					$arJoinProps['FPV'][$propertyId] ??= [
						'CNT' => count($arJoinProps['FPV']),
						'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
						'MULTIPLE' => $db_prop['MULTIPLE'],
						'VERSION' => $db_prop['VERSION'],
						'ORIG_ID' => $db_prop['ORIG_ID'],
						'JOIN' => $arJoinProps['FP'][$db_prop['ID']]['CNT'],
						'bFullJoin' => false,
					];
					$arJoinProps['FPV'][$propertyId] = self::addJoinTypeSelect($arJoinProps['FPV'][$propertyId]);

					$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];
				}
				//Then join elements
				$arJoinProps['BE'][$propertyId] ??= [
					'CNT' => count($arJoinProps['BE']),
					'MULTIPLE' => $db_prop['MULTIPLE'],
					'VERSION' => $db_prop['VERSION'],
					'ORIG_ID' => $db_prop['ORIG_ID'],
					'JOIN' => $iPropCnt,
					'bJoinIBlock' => false,
					'bJoinSection' => false,
				];
				$arJoinProps['BE'][$propertyId] = self::addJoinTypeSelect($arJoinProps['BE'][$propertyId]);

				$iElCnt = $arJoinProps["BE"][$db_prop["ID"]]["CNT"];

				//Check if b_iblock have to be joined also
				if(
					$PR_ID[2] == "LIST_PAGE_URL"
					|| $PR_ID[2] == "IBLOCK_TYPE_ID"
					|| $PR_ID[2] == "IBLOCK_CODE"
					|| $PR_ID[2] == "IBLOCK_NAME"
					|| $PR_ID[2] == "IBLOCK_EXTERNAL_ID"
				)
					$arJoinProps["BE"][$db_prop["ID"]]["bJoinIBlock"] = true;

				//Check if b_iblock_section have to be joined also
				if($PR_ID[2] == "DETAIL_PAGE_URL")
				{
					$arJoinProps["BE"][$db_prop["ID"]]["bJoinIBlock"] = true;
					$arJoinProps["BE"][$db_prop["ID"]]["bJoinSection"] = true;
				}

				$arSelect[str_replace("#i#", $iElCnt, $arJoinEFields[$PR_ID[2]])] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2], $this->arIBlockLongProps);
			}
			//Joined elements properties
			elseif(mb_substr($PR_ID[2], 0, 9) == "PROPERTY_")
			{
				$jProp_ID = mb_substr($PR_ID[2], 9);
				$db_jprop = CIBlockProperty::GetPropertyArray($jProp_ID, CIBlock::_MergeIBArrays($db_prop["LINK_IBLOCK_ID"]));
				if(is_array($db_jprop))
				{
					if($db_prop["VERSION"] == 2 && $db_prop["MULTIPLE"] == "N")
					{
						//Join properties table if needed
						if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
							$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
						$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
					}
					else
					{
						//Join property metadata table
						$arJoinProps['FP'][$propertyId] ??= [
							'CNT' => count($arJoinProps['FP']),
							'bFullJoin' => false,
						];
						$arJoinProps['FP'][$propertyId] = self::addJoinTypeSelect($arJoinProps['FP'][$propertyId]);

						//Join multiple values properties table if needed
						$arJoinProps['FPV'][$propertyId] ??= [
							'CNT' => count($arJoinProps['FPV']),
							'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
							'MULTIPLE' => $db_prop['MULTIPLE'],
							'VERSION' => $db_prop['VERSION'],
							'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
							'bFullJoin' => false,
						];
						$arJoinProps['FPV'][$propertyId] = self::addJoinTypeSelect($arJoinProps['FPV'][$propertyId]);

						$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];
					}
					//Then join elements
					$arJoinProps['BE'][$propertyId] ??= [
						'CNT' => count($arJoinProps['BE']),
						'MULTIPLE' => $db_prop['MULTIPLE'],
						'VERSION' => $db_prop['VERSION'],
						'ORIG_ID' => $db_prop['ORIG_ID'],
						'JOIN' => $iPropCnt,
						'bJoinIBlock' => false,
						'bJoinSection' => false,
					];
					$arJoinProps['BE'][$propertyId] = self::addJoinTypeSelect($arJoinProps['BE'][$propertyId]);

					$iElCnt = $arJoinProps["BE"][$db_prop["ID"]]["CNT"];

					if($db_jprop["USER_TYPE"]!="")
					{
						$arUserType = CIBlockProperty::GetUserType($db_jprop["USER_TYPE"]);
						if(array_key_exists("ConvertFromDB", $arUserType))
							$this->arIBlockConvProps["PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE"] = array(
								"ConvertFromDB" => $arUserType["ConvertFromDB"],
								"PROPERTY" => $db_jprop,
							);
					}

					$comp_prop_id = $db_jprop["ID"]."~".$db_prop["ID"];

					//Infoblock+ (property stored in separate table)
					if($db_jprop["VERSION"] == 2)
					{
						//This is single value property
						if($db_jprop["MULTIPLE"] == "N")
						{
							//For numbers we will need special processing in CIBlockResult::Fetch
							if($db_jprop["PROPERTY_TYPE"]=="N")
								$this->arIBlockNumProps["PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE"] = $db_prop;

							//Enum single property
							if($db_jprop["PROPERTY_TYPE"]=="L")
							{
								//Join properties table if needed
								$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']] ??= [
									'CNT' => count($arJoinProps['BE_FPS']),
									'JOIN' => $iElCnt,
								];
								$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']] = self::addJoinTypeSelect(
									$arJoinProps['BE_FPS'][$db_jprop['IBLOCK_ID']]
								);

								$ijPropCnt = $arJoinProps["BE_FPS"][$db_jprop["IBLOCK_ID"]]["CNT"];
								//Then join list values table
								$arJoinProps['BE_FPEN'][$comp_prop_id] ??= [
									"CNT" => count($arJoinProps["BE_FPEN"]),
									"MULTIPLE" => "N",
									"VERSION" => 2,
									"ORIG_ID" => $db_jprop["ORIG_ID"],
									"JOIN" => $ijPropCnt,
									"bFullJoin" => false,
								];
								$arJoinProps['BE_FPEN'][$comp_prop_id] = self::addJoinTypeSelect(
									$arJoinProps['BE_FPEN'][$comp_prop_id]
								);

								$ijFpenCnt = $arJoinProps["BE_FPEN"][$comp_prop_id]["CNT"];

								$arSelect["JFPEN".$ijFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
								$arSelect["JFPEN".$ijFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_ENUM_ID", $this->arIBlockLongProps);
							}
							else //Just single value property for Infoblock+
							{
								//Join properties table if needed
								$singleValueKey = $db_jprop['IBLOCK_ID'] . '~' . $db_prop['ID'];

								$arJoinProps['BE_FPS'][$singleValueKey] ??= [
									'CNT' => count($arJoinProps['BE_FPS']),
									'JOIN' => $iElCnt,
								];
								$arJoinProps['BE_FPS'][$singleValueKey] = self::addJoinTypeSelect(
									$arJoinProps['BE_FPS'][$singleValueKey]
								);

								$ijPropCnt = $arJoinProps["BE_FPS"][$singleValueKey]["CNT"];
								unset($singleValueKey);

								$arSelect["JFPS".$ijPropCnt.".PROPERTY_".$db_jprop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
								if($sGroupBy=="" && $db_jprop["WITH_DESCRIPTION"] == "Y")
									$arSelect["JFPS".$ijPropCnt.".DESCRIPTION_".$db_jprop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_DESCRIPTION", $this->arIBlockLongProps);
							}

							//When there is no grouping and this is single value property for Infoblock+
							if($sGroupBy == "")
							{
								$arSelect[$DB->Concat("BE".$iElCnt.".ID","':'",$db_jprop["ORIG_ID"])] = CIBlockElement::MkAlias($mal, $PR_ID[2]."_".$PR_ID[1]."_VALUE_ID", $this->arIBlockLongProps);
							}
						}
						else //This is multiple value property for Infoblock+
						{
							//Join property metadata table
							$arJoinProps['BE_FP'][$comp_prop_id] ??= [
								'CNT' => count($arJoinProps['BE_FP']),
								'JOIN' => $iElCnt,
								'bFullJoin' => false,
							];
							$arJoinProps['BE_FP'][$comp_prop_id] = self::addJoinTypeSelect(
								$arJoinProps['BE_FP'][$comp_prop_id]
							);

							//Join multiple values properties table if needed
							$arJoinProps['BE_FPV'][$comp_prop_id] ??= [
								'CNT' => count($arJoinProps['BE_FPV']),
								'MULTIPLE' => 'Y',
								'VERSION' => 2,
								'IBLOCK_ID' => $db_jprop['IBLOCK_ID'],
								'JOIN' => $arJoinProps['BE_FP'][$comp_prop_id]['CNT'],
								'BE_JOIN' => $iElCnt,
								'bFullJoin' => false,
							];
							$arJoinProps['BE_FPV'][$comp_prop_id] = self::addJoinTypeSelect(
								$arJoinProps['BE_FPV'][$comp_prop_id]
							);

							$ijPropCnt = $arJoinProps["BE_FPV"][$comp_prop_id]["CNT"];

							//For enum properties
							if($db_jprop["PROPERTY_TYPE"]=="L")
							{
								//Then join list values table
								$arJoinProps['BE_FPEN'][$comp_prop_id] ??= [
									'CNT' => count($arJoinProps['BE_FPEN']),
									'MULTIPLE' => 'Y',
									'VERSION' => 2,
									'ORIG_ID' => $db_jprop['ORIG_ID'],
									'JOIN' => $ijPropCnt,
									'bFullJoin' => false,
								];
								$arJoinProps['BE_FPEN'][$comp_prop_id] = self::addJoinTypeSelect(
									$arJoinProps['BE_FPEN'][$comp_prop_id]
								);

								$ijFpenCnt = $arJoinProps["BE_FPEN"][$comp_prop_id]["CNT"];

								$arSelect["JFPEN".$ijFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
								$arSelect["JFPEN".$ijFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_ENUM_ID", $this->arIBlockLongProps);
							}
							else
							{
								$arSelect["JFPV".$ijPropCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
							}
						}
					}//Infoblocks with common values table (VERSION==1)
					else
					{
						//Join property metadata table
						$arJoinProps['BE_FP'][$comp_prop_id] ??= [
							'CNT' => count($arJoinProps['BE_FP']),
							'JOIN' => $iElCnt,
							'bFullJoin' => false,
						];
						$arJoinProps['BE_FP'][$comp_prop_id] = self::addJoinTypeSelect(
							$arJoinProps['BE_FP'][$comp_prop_id]
						);

						//Join multiple values properties table if needed
						$arJoinProps['BE_FPV'][$comp_prop_id] ??= [
							'CNT' => count($arJoinProps['BE_FPV']),
							'MULTIPLE' => $db_jprop['MULTIPLE'],
							'VERSION' => 1,
							'IBLOCK_ID' => $db_jprop['IBLOCK_ID'],
							'JOIN' => $arJoinProps['BE_FP'][$comp_prop_id]['CNT'],
							'BE_JOIN' => $iElCnt,
							'bFullJoin' => false,
						];
						$arJoinProps['BE_FPV'][$comp_prop_id] = self::addJoinTypeSelect(
							$arJoinProps['BE_FPV'][$comp_prop_id]
						);

						$ijPropCnt = $arJoinProps["BE_FPV"][$comp_prop_id]["CNT"];

						//For enum properties
						if($db_jprop["PROPERTY_TYPE"]=="L")
						{
							//Then join list values table
							$arJoinProps['BE_FPEN'][$comp_prop_id] ??= [
								'CNT' => count($arJoinProps['BE_FPEN']),
								'MULTIPLE' => $db_jprop['MULTIPLE'],
								'VERSION' => 1,
								'ORIG_ID' => $db_jprop['ORIG_ID'],
								'JOIN' => $ijPropCnt,
								'bFullJoin' => false,
							];
							$arJoinProps['BE_FPEN'][$comp_prop_id] = self::addJoinTypeSelect(
								$arJoinProps['BE_FPEN'][$comp_prop_id]
							);

							$ijFpenCnt = $arJoinProps["BE_FPEN"][$comp_prop_id]["CNT"];

							$arSelect["JFPEN".$ijFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
							$arSelect["JFPEN".$ijFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_ENUM_ID", $this->arIBlockLongProps);
						}
						else
						{
							$arSelect["JFPV".$ijPropCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE", $this->arIBlockLongProps);
						}

						//When there is no grouping select property value id also
						if($sGroupBy == "")
							$arSelect["JFPV".$ijPropCnt.".ID"] =  CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID[1]."_".$PR_ID[2]."_VALUE_ID", $this->arIBlockLongProps);
					}
				}

			}
		}
		else
		{
			//Define special processing for CIBlockResult::Fetch
			if($db_prop["USER_TYPE"]!="")
			{
				$arUserType = CIBlockProperty::GetUserType($db_prop["USER_TYPE"]);
				if(array_key_exists("ConvertFromDB", $arUserType))
					$this->arIBlockConvProps["PROPERTY_".$PR_ID."_VALUE"] = array(
						"ConvertFromDB" => $arUserType["ConvertFromDB"],
						"PROPERTY" => $db_prop,
					);
			}

			//Infoblock+ (property stored in separate table)
			if($db_prop["VERSION"] == 2)
			{
				//This is single value property
				if($db_prop["MULTIPLE"] == "N")
				{
					//For numbers we will need special processing in CIBlockResult::Fetch
					if($db_prop["PROPERTY_TYPE"]=="N")
						$this->arIBlockNumProps["PROPERTY_".$PR_ID."_VALUE"] = $db_prop;

					//Enum single property
					if($db_prop["PROPERTY_TYPE"]=="L")
					{
						//Join properties table if needed
						if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
							$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
						$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];
						//Then join list values table
						if(!array_key_exists($db_prop["ID"], $arJoinProps["FPEN"]))
						{
							$arJoinProps['FPEN'][$propertyId] ??= [
								'CNT' => count($arJoinProps['FPEN']),
								'MULTIPLE' => 'N',
								'VERSION' => 2,
								'ORIG_ID' => $db_prop['ORIG_ID'],
								'JOIN' => $iPropCnt,
								'bFullJoin' => false,
							];
							$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeSelect(
								$arJoinProps['FPEN'][$propertyId]
							);
						}
						$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];

						if($bSort)
							$arSelect["FPEN".$iFpenCnt.".SORT"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_SORT", $this->arIBlockLongProps);
						$arSelect["FPEN".$iFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
						$arSelect["FPEN".$iFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_ENUM_ID", $this->arIBlockLongProps);
					}
					else //Just single value property for Infoblock+
					{
						//Join properties table if needed
						if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
							$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
						$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];

						$arSelect["FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
						if($sGroupBy=="" && $db_prop["WITH_DESCRIPTION"] == "Y")
							$arSelect["FPS".$iPropCnt.".DESCRIPTION_".$db_prop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_DESCRIPTION", $arIBlockLongProps);
					}

					//When there is no grouping and this is single value property for Infoblock+
					if($sGroupBy == "")
					{
						$arSelect[$DB->Concat("BE.ID","':'",$db_prop["ORIG_ID"])] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE_ID", $this->arIBlockLongProps);
					}
				}
				else //This is multiple value property for Infoblock+
				{
					//There was no grouping so we can join FPS and constuct an array on CIBlockPropertyResult::Fetch
					if(!$bWasGroup && !$bSubQuery)
					{
						//Join single value properties table if needed
						if(!array_key_exists($db_prop["IBLOCK_ID"], $arJoinProps["FPS"]))
							$arJoinProps["FPS"][$db_prop["IBLOCK_ID"]] = count($arJoinProps["FPS"]);
						$iPropCnt = $arJoinProps["FPS"][$db_prop["IBLOCK_ID"]];

						$arSelect["FPS".$iPropCnt.".PROPERTY_".$db_prop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
						if($sGroupBy=="" && $db_prop["WITH_DESCRIPTION"] == "Y")
							$arSelect["FPS".$iPropCnt.".DESCRIPTION_".$db_prop["ORIG_ID"]] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_DESCRIPTION", $this->arIBlockLongProps);

						//And we will need extra processing in CIBlockPropertyResult::Fetch
						$this->arIBlockMultProps["PROPERTY_".$PR_ID."_VALUE"] = $db_prop;
					}
					//This is multiple value property for Infoblock+ with gouping used
					else
					{
						//Join property metadata table
						$arJoinProps['FP'][$propertyId] ??= [
							'CNT' => count($arJoinProps['FP']),
							'bFullJoin' => false,
						];
						$arJoinProps['FP'][$propertyId] = self::addJoinTypeSelect(
							$arJoinProps['FP'][$propertyId]
						);

						//Join multiple values properties table if needed
						$arJoinProps['FPV'][$propertyId] ??= [
							'CNT' => count($arJoinProps['FPV']),
							'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
							'MULTIPLE' => 'Y',
							'VERSION' => 2,
							'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
							'bFullJoin' => false,
						];
						$arJoinProps['FPV'][$propertyId] = self::addJoinTypeSelect(
							$arJoinProps['FPV'][$propertyId]
						);

						$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];

						//For enum properties
						if($db_prop["PROPERTY_TYPE"]=="L")
						{
							//Then join list values table
							$arJoinProps['FPEN'][$propertyId] ??= [
								'CNT' => count($arJoinProps['FPEN']),
								'MULTIPLE' => 'Y',
								'VERSION' => 2,
								'ORIG_ID' => $db_prop['ORIG_ID'],
								'JOIN' => $iPropCnt,
								'bFullJoin' => false,
							];
							$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeSelect(
								$arJoinProps['FPEN'][$propertyId]
							);

							$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];

							if($bSort)
								$arSelect["FPEN".$iFpenCnt.".SORT"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_SORT", $this->arIBlockLongProps);
							$arSelect["FPEN".$iFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
							$arSelect["FPEN".$iFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_ENUM_ID", $this->arIBlockLongProps);
						}
						else
						{
							$arSelect["FPV".$iPropCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
						}
					}
				}
			}//Infoblocks with common values table (VERSION==1)
			else
			{
				//Join property metadata table
				$arJoinProps['FP'][$propertyId] ??= [
					'CNT' => count($arJoinProps['FP']),
					'bFullJoin' => false,
				];
				$arJoinProps['FP'][$propertyId] = self::addJoinTypeSelect(
					$arJoinProps['FP'][$propertyId]
				);

				//Join multiple values properties table if needed
				$arJoinProps['FPV'][$propertyId] ??= [
					'CNT' => count($arJoinProps['FPV']),
					'IBLOCK_ID' => $db_prop['IBLOCK_ID'],
					'MULTIPLE' => $db_prop['MULTIPLE'],
					'VERSION' => 1,
					'JOIN' => $arJoinProps['FP'][$propertyId]['CNT'],
					'bFullJoin' => false,
				];
				$arJoinProps['FPV'][$propertyId] = self::addJoinTypeSelect(
					$arJoinProps['FPV'][$propertyId]
				);
				$iPropCnt = $arJoinProps["FPV"][$db_prop["ID"]]["CNT"];

				//For enum properties
				if($db_prop["PROPERTY_TYPE"]=="L")
				{
					//Then join list values table
					$arJoinProps['FPEN'][$propertyId] ??= [
						'CNT' => count($arJoinProps['FPEN']),
						'MULTIPLE' => $db_prop['MULTIPLE'],
						'VERSION' => 1,
						'ORIG_ID' => $db_prop['ORIG_ID'],
						'JOIN' => $iPropCnt,
						'bFullJoin' => false,
					];
					$arJoinProps['FPEN'][$propertyId] = self::addJoinTypeSelect(
						$arJoinProps['FPEN'][$propertyId]
					);

					$iFpenCnt = $arJoinProps["FPEN"][$db_prop["ID"]]["CNT"];

					if($bSort)
						$arSelect["FPEN".$iFpenCnt.".SORT"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_SORT", $this->arIBlockLongProps);
					$arSelect["FPEN".$iFpenCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
					$arSelect["FPEN".$iFpenCnt.".ID"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_ENUM_ID", $this->arIBlockLongProps);
				}
				else
				{
					if ($db_prop['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_NUMBER)
					{
						$arSelect['FPV' . $iPropCnt . '.VALUE_NUM'] = CIBlockElement::MkAlias(
							$mal,
							'PROPERTY_' . $PR_ID . '_VALUE_NUM',
							$this->arIBlockLongProps
						);
					}
					$arSelect["FPV".$iPropCnt.".VALUE"] = CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE", $this->arIBlockLongProps);
				}

				//When there is no grouping select property value id also
				if($sGroupBy == "")
					$arSelect["FPV".$iPropCnt.".ID"] =  CIBlockElement::MkAlias($mal, "PROPERTY_".$PR_ID."_VALUE_ID", $this->arIBlockLongProps);
			}
		}

		//If this is subquery we do not need alias and any othe columns
		if($bSubQuery)
		{
			foreach($arSelect as $column => $alias)
			{
				if(preg_match('/^(FPV\\d+)\\.VALUE/', $column, $match))
				{
					$sSelect .= ", ".$match[1].".VALUE_NUM";
					break;
				}
				elseif(preg_match('/^(FPS\\d+)\\.PROPERTY_/', $column))
				{
					$sSelect .= ", ".$column;
					break;
				}
			}
		}
		else
		{
			foreach($arSelect as $column => $alias)
				$sSelect .= ", ".$column." as ".$alias;
		}
	}

	public function MkAlias($max_alias_len, $alias, &$arIBlockLongProps)
	{
		if($max_alias_len && mb_strlen($alias) > $max_alias_len)
		{
			$alias_index = count($arIBlockLongProps);
			$arIBlockLongProps[$alias_index] = $alias;
			$alias = "ALIAS_".$alias_index."_";
		}
		return $alias;
	}

	/**
	 * Returns true, if identifier is valid property id or property symbolic code.
	 *
	 * @param int|string $identifier
	 * @return bool
	 */
	private static function checkPropertyIdentifier(int|string $identifier): bool
	{
		$identifier = (string)$identifier;
		if ($identifier === '')
		{
			return false;
		}

		$prepared = [];
		$result = false;
		foreach (self::$propertyIdentifierMasks as $mask)
		{
			if (preg_match($mask, $identifier, $prepared))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	/**
	 * Returns true, if identifier is valid field or property identifier (id or symbolic code) in linked iblock.
	 *
	 * @param string $identifier
	 * @return bool
	 */
	private static function checkPropertyLinkIdentifier(string $identifier): bool
	{
		if ($identifier === '')
		{
			return false;
		}

		$prepared = [];
		if (!preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $identifier, $prepared))
		{
			return false;
		}

		if (!static::checkPropertyIdentifier($prepared[1]))
		{
			return false;
		}

		$result = false;
		$subprepared = [];
		foreach (self::$propertyLinkFieldIdentifierMasks as $mask)
		{
			if (preg_match($mask, $prepared[2], $subprepared))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	public function PrepareGetList(
		&$arIblockElementFields,
		&$arJoinProps,

		&$arSelectFields,
		&$sSelect,
		&$arAddSelectFields,

		&$arFilter,
		&$sWhere,
		&$sSectionWhere,
		&$arAddWhereFields,

		&$arGroupBy,
		&$sGroupBy,

		&$arOrder,
		&$arSqlOrder,
		&$arAddOrderByFields
	)
	{
		if(
			is_array($arSelectFields)
			&& (in_array("DETAIL_PAGE_URL", $arSelectFields) || in_array("CANONICAL_PAGE_URL", $arSelectFields))
			&& !in_array("LANG_DIR", $arSelectFields)
		)
		{
			$arSelectFields[] = "LANG_DIR";
		}

		global $DB;

		if (empty($arSelectFields))
		{
			$arSelectFields = ['*'];
		}

		if ($arGroupBy === true)
		{
			$arGroupBy = [];
		}

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$this->bOnlyCount = true;
		}

		$iPropCnt = 0;
		$arJoinProps = [
			'FP' => [
				//CNT
				//bFullJoin
			],
			'FPV' => [
				//CNT
				//IBLOCK_ID
				//MULTIPLE
				//VERSION
				//JOIN
				//bFullJoin
			],
			'FPS' => [ // property values for iblock 2.0
				//
			],
			'FPEN' => [
				//CNT
				//MULTIPLE
				//VERSION
				//ORIG_ID
				//JOIN
				//bFullJoin
			],
			'BE' => [
				//CNT
				//MULTIPLE
				//VERSION
				//ORIG_ID
				//JOIN
				//bJoinIBlock
				//bJoinSection
			],
			'BE_FP' => [
				//CNT
				//JOIN
				//bFullJoin
			],
			'BE_FPV' => [
				//CNT
				//IBLOCK_ID
				//MULTIPLE
				//VERSION
				//JOIN
				//BE_JOIN
				//bFullJoin
			],
			'BE_FPS' => [
				//CNT
				//JOIN
			],
			'BE_FPEN' => [
				//CNT
				//MULTIPLE
				//VERSION
				//ORIG_ID
				//JOIN
				//bFullJoin
			],
			'BES' => '',
			'RV' => false, // order by RATING_TOTAL_VALUE
			'RVU' => false,
			'RVV' => false,
			'FC' => '',
		];

		$this->arIBlockMultProps = [];
		$this->arIBlockAllProps = [];
		$this->arIBlockNumProps = [];
		$bWasGroup = false;

		//********************************ORDER BY PART***********************************************

		$iblockIds = CIBlock::_MergeIBArrays(
			$arFilter["IBLOCK_ID"] ?? false,
			$arFilter["IBLOCK_CODE"] ?? false,
		);
		$orderAlias = array(
			'EXTERNAL_ID' => 'XML_ID',
			'DATE_ACTIVE_FROM' => 'ACTIVE_FROM',
			'DATE_ACTIVE_TO' => 'ACTIVE_TO'
		);
		$arSqlOrder = [];
		$arAddOrderByFields = [];
		$iOrdNum = -1;
		if (!is_array($arOrder))
		{
			$arOrder = [];
		}
		foreach($arOrder as $by=>$order)
		{
			$by_orig = $by;
			$by = mb_strtoupper($by);
			//Remove aliases
			if (isset($orderAlias[$by]))
				$by = $orderAlias[$by];

			if (isset($arSqlOrder[$by]))
				continue;

			if ($this->catalogIncluded && \CProductQueryBuilder::isValidField($by))
			{
				$iOrdNum++;
				$arAddOrderByFields[$iOrdNum] = Array($by=>$order);
				//Reserve for future fill
				$arSqlOrder[$iOrdNum] = false;
			}
			else
			{
				if($by == "ID") $arSqlOrder[$by] = $this->getIdOrder($order);
				elseif($by == "NAME") $arSqlOrder[$by] = CIBlock::_Order("BE.NAME", $order, "desc", false);
				elseif($by == "STATUS") $arSqlOrder[$by] = CIBlock::_Order("BE.WF_STATUS_ID", $order, "desc");
				elseif($by == "XML_ID") $arSqlOrder[$by] = CIBlock::_Order("BE.XML_ID", $order, "desc");
				elseif($by == "CODE") $arSqlOrder[$by] = CIBlock::_Order("BE.CODE", $order, "desc");
				elseif($by == "TAGS") $arSqlOrder[$by] = CIBlock::_Order("BE.TAGS", $order, "desc");
				elseif($by == "TIMESTAMP_X") $arSqlOrder[$by] = CIBlock::_Order("BE.TIMESTAMP_X", $order, "desc");
				elseif($by == "CREATED") $arSqlOrder[$by] = CIBlock::_Order("BE.DATE_CREATE", $order, "desc");
				elseif($by == "CREATED_DATE") $arSqlOrder[$by] = CIBlock::_Order($DB->DateFormatToDB("YYYY.MM.DD", "BE.DATE_CREATE"), $order, "desc");
				elseif($by == "IBLOCK_ID") $arSqlOrder[$by] = CIBlock::_Order("BE.IBLOCK_ID", $order, "desc");
				elseif($by == "MODIFIED_BY") $arSqlOrder[$by] = CIBlock::_Order("BE.MODIFIED_BY", $order, "desc");
				elseif($by == "CREATED_BY") $arSqlOrder[$by] = CIBlock::_Order("BE.CREATED_BY", $order, "desc");
				elseif($by == "ACTIVE") $arSqlOrder[$by] = CIBlock::_Order("BE.ACTIVE", $order, "desc");
				elseif($by == "ACTIVE_FROM") $arSqlOrder[$by] = CIBlock::_Order("BE.ACTIVE_FROM", $order, "desc");
				elseif($by == "ACTIVE_TO") $arSqlOrder[$by] = CIBlock::_Order("BE.ACTIVE_TO", $order, "desc");
				elseif($by == "SORT") $arSqlOrder[$by] = CIBlock::_Order("BE.SORT", $order, "desc");
				elseif($by == "IBLOCK_SECTION_ID") $arSqlOrder[$by] = CIBlock::_Order("BE.IBLOCK_SECTION_ID", $order, "desc");
				elseif($by == "SHOW_COUNTER") $arSqlOrder[$by] = CIBlock::_Order("BE.SHOW_COUNTER", $order, "desc");
				elseif($by == "SHOW_COUNTER_START") $arSqlOrder[$by] = CIBlock::_Order("BE.SHOW_COUNTER_START", $order, "desc");
				elseif($by == "RAND") $arSqlOrder[$by] = CIBlockElement::GetRandFunction();
				elseif($by == "SHOWS") $arSqlOrder[$by] = CIBlock::_Order(CIBlockElement::GetShowedFunction(), $order, "desc", false);
				elseif($by == "HAS_PREVIEW_PICTURE") $arSqlOrder[$by] = CIBlock::_Order(CIBlock::_NotEmpty("BE.PREVIEW_PICTURE"), $order, "desc", false);
				elseif($by == "HAS_DETAIL_PICTURE") $arSqlOrder[$by] = CIBlock::_Order(CIBlock::_NotEmpty("BE.DETAIL_PICTURE"), $order, "desc", false);
				elseif($by == "RATING_TOTAL_VALUE")
				{
					$arSqlOrder[$by] = CIBlock::_Order("RV.TOTAL_VALUE", $order, "desc");
					$arJoinProps["RV"] = true;
				}
				elseif($by == "CNT")
				{
					if (!empty($arGroupBy) && is_array($arGroupBy))
					{
						$arSqlOrder[$by] = ' '.CIBlock::_Order('CNT', $order, 'desc', false).' ';
					}
				}
				elseif(mb_substr($by, 0, 9) == "PROPERTY_")
				{
					$propID = mb_strtoupper(mb_substr($by_orig, 9));
					if (preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $propID, $arMatch))
					{
						if (self::checkPropertyLinkIdentifier($propID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($arMatch[1], $iblockIds);
							if (is_array($db_prop) && $db_prop["PROPERTY_TYPE"] === Iblock\PropertyTable::TYPE_ELEMENT)
							{
								CIBlockElement::MkPropertyOrder($arMatch, $order, false, $db_prop, $arJoinProps, $arSqlOrder);
							}
						}
					}
					else
					{
						if (self::checkPropertyIdentifier($propID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($propID, $iblockIds);
							if ($db_prop)
							{
								CIBlockElement::MkPropertyOrder($by, $order, false, $db_prop, $arJoinProps, $arSqlOrder);
							}
						}
					}
				}
				elseif(mb_substr($by, 0, 13) == "PROPERTYSORT_")
				{
					$propID = mb_strtoupper(mb_substr($by_orig, 13));
					if (preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $propID, $arMatch))
					{
						if (self::checkPropertyLinkIdentifier($propID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($arMatch[1], $iblockIds);
							if (is_array($db_prop) && $db_prop["PROPERTY_TYPE"] === Iblock\PropertyTable::TYPE_ELEMENT)
							{
								CIBlockElement::MkPropertyOrder($arMatch, $order, true, $db_prop, $arJoinProps, $arSqlOrder);
							}
						}
					}
					else
					{
						if (self::checkPropertyIdentifier($propID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($propID, $iblockIds);
							if ($db_prop)
							{
								CIBlockElement::MkPropertyOrder($by, $order, true, $db_prop, $arJoinProps, $arSqlOrder);
							}
						}
					}
				}
				else
				{
					$by = "ID";
					if (!isset($arSqlOrder[$by]))
					{
						$arSqlOrder[$by] = CIBlock::_Order("BE.ID", $order, "desc");
					}
				}

				//Check if have to add select field in order to correctly sort
				if (isset($arSqlOrder[$by]) && is_array($arSqlOrder[$by]))
				{
					if (!empty($arGroupBy) && is_array($arGroupBy))
						$arGroupBy[] = $arSqlOrder[$by][1];
					else
						$arSelectFields[] = $arSqlOrder[$by][1];
					//                        COLUMN ALIAS         COLUMN EXPRESSION
					$arIblockElementFields[$arSqlOrder[$by][1]] = $arSqlOrder[$by][0];
					//                  ORDER EXPRESSION
					$arSqlOrder[$by] = $arSqlOrder[$by][2];
				}
			}

			//Add order by fields to the select list
			//in order to avoid sql errors
			if (!empty($arGroupBy) && is_array($arGroupBy))
			{
				if ($by == "STATUS")
				{
					$arGroupBy[] = "WF_STATUS_ID";
				}
				elseif ($by == "CREATED")
				{
					$arGroupBy[] = "DATE_CREATE";
				}
				elseif ($by == "SHOWS")
				{
					$arGroupBy[] = "SHOW_COUNTER";
					$arGroupBy[] = "SHOW_COUNTER_START_X";
				}
				else
				{
					$arGroupBy[] = $by;
				}
			}
			else
			{
				if ($by == "STATUS")
				{
					$arSelectFields[] = "WF_STATUS_ID";
				}
				elseif ($by == "CREATED")
				{
					$arSelectFields[] = "DATE_CREATE";
				}
				elseif ($by == "SHOWS")
				{
					$arSelectFields[] = "SHOW_COUNTER";
					$arSelectFields[] = "SHOW_COUNTER_START_X";
				}
				else
				{
					$arSelectFields[] = $by;
				}
			}
		}

		//*************************GROUP BY PART****************************
		$sGroupBy = "";
		if(!empty($arGroupBy) && is_array($arGroupBy))
		{
			$arSelectFields = $arGroupBy;
			$bWasGroup = true;
			foreach($arSelectFields as $key=>$val)
			{
				$val = mb_strtoupper($val);
				if(array_key_exists($val, $arIblockElementFields))
				{
					$sGroupBy.=",".preg_replace("/(\s+AS\s+[A-Z_]+)/i", "", $arIblockElementFields[$val]);
				}
				elseif(mb_substr($val, 0, 9) == "PROPERTY_")
				{
					$PR_ID = mb_strtoupper(mb_substr($val, 9));
					if($db_prop = CIBlockProperty::GetPropertyArray($PR_ID, $iblockIds))
						$sGroupBy .= CIBlockElement::MkPropertyGroup($db_prop, $arJoinProps);
				}
				elseif(mb_substr($val, 0, 13) == "PROPERTYSORT_")
				{
					$PR_ID = mb_strtoupper(mb_substr($val, 13));
					if($db_prop = CIBlockProperty::GetPropertyArray($PR_ID, $iblockIds))
						$sGroupBy .= CIBlockElement::MkPropertyGroup($db_prop, $arJoinProps, true);
				}
			}
			if ($sGroupBy !== '')
			{
				$sGroupBy = " GROUP BY " . mb_substr($sGroupBy, 1 ). " ";
			}
		}

		//*************************SELECT PART****************************
		$arAddSelectFields = [];
		if($this->bOnlyCount)
		{
			$sSelect = "COUNT(%%_DISTINCT_%% BE.ID) as CNT ";
		}
		else
		{
			$sSelect = "";
			$arDisplayedColumns = array();
			$bStar = false;
			foreach($arSelectFields as $key=>$val)
			{
				$val = mb_strtoupper($val);
				if(array_key_exists($val, $arIblockElementFields))
				{
					if(isset($arDisplayedColumns[$val]))
						continue;
					$arDisplayedColumns[$val] = true;
					$arSelectFields[$key] = $val;
					$sSelect.=",".$arIblockElementFields[$val]." as ".$val;
				}
				elseif($val == "PROPERTY_*" && !$bWasGroup)
				{
					//We have to analyze arFilter IBLOCK_ID and IBLOCK_CODE
					//in a way to be shure we will get properties of the ONE IBLOCK ONLY!
					$arPropertyFilter = array(
						"ACTIVE"=>"Y",
						"VERSION"=>2,
					);
					if(array_key_exists("IBLOCK_ID", $arFilter))
					{
						if(is_array($arFilter["IBLOCK_ID"]) && count($arFilter["IBLOCK_ID"])==1)
							$arPropertyFilter["IBLOCK_ID"] = $arFilter["IBLOCK_ID"][0];
						elseif(!is_array($arFilter["IBLOCK_ID"]) && intval($arFilter["IBLOCK_ID"])>0)
							$arPropertyFilter["IBLOCK_ID"] = $arFilter["IBLOCK_ID"];
					}
					if(!array_key_exists("IBLOCK_ID", $arPropertyFilter))
					{
						if(array_key_exists("IBLOCK_CODE", $arFilter))
						{
							if(is_array($arFilter["IBLOCK_CODE"]) && count($arFilter["IBLOCK_CODE"])==1)
								$arPropertyFilter["IBLOCK_CODE"] = $arFilter["IBLOCK_CODE"][0];
							elseif(!is_array($arFilter["IBLOCK_CODE"]) && $arFilter["IBLOCK_CODE"] <> '')
								$arPropertyFilter["IBLOCK_CODE"] = $arFilter["IBLOCK_CODE"];
							else
								continue;
						}
						else
							continue;
					}

					$rs_prop = CIBlockProperty::GetList(array("sort"=>"asc"), $arPropertyFilter);
					while($db_prop = $rs_prop->Fetch())
						$this->arIBlockAllProps[]=$db_prop;
					$iblock_id = false;
					foreach($this->arIBlockAllProps as $db_prop)
					{
						if($db_prop["USER_TYPE"]!="")
						{
							$arUserType = CIBlockProperty::GetUserType($db_prop["USER_TYPE"]);
							if(array_key_exists("ConvertFromDB", $arUserType))
								$this->arIBlockConvProps["PROPERTY_".$db_prop["ID"]] = array(
									"ConvertFromDB"=>$arUserType["ConvertFromDB"],
									"PROPERTY"=>$db_prop,
								);
						}
						$db_prop["ORIG_ID"] = $db_prop["ID"];
						if($db_prop["MULTIPLE"]=="Y")
							$this->arIBlockMultProps["PROPERTY_".$db_prop["ID"]] = $db_prop;
						$iblock_id = $db_prop["IBLOCK_ID"];
					}
					if($iblock_id!==false)
					{
						if(!array_key_exists($iblock_id, $arJoinProps["FPS"]))
							$arJoinProps["FPS"][$iblock_id] = count($arJoinProps["FPS"]);
						$iPropCnt = $arJoinProps["FPS"][$iblock_id];

						$sSelect .= ", FPS".$iPropCnt.".*";
					}
				}
				elseif(mb_substr($val, 0, 9) == "PROPERTY_")
				{
					$PR_ID = mb_strtoupper($val);
					if(isset($arDisplayedColumns[$PR_ID]))
						continue;
					$arDisplayedColumns[$PR_ID] = true;
					$PR_ID = mb_substr($PR_ID, 9);

					$iblockIds = CIBlock::_MergeIBArrays(
						$arFilter["IBLOCK_ID"] ?? false,
						$arFilter["IBLOCK_CODE"] ?? false
					);

					if(preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $PR_ID, $arMatch))
					{
						if (self::checkPropertyLinkIdentifier($PR_ID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($arMatch[1], $iblockIds);
							if (is_array($db_prop) && $db_prop["PROPERTY_TYPE"] == "E")
							{
								$this->MkPropertySelect($arMatch, $db_prop, $arJoinProps, $bWasGroup, $sGroupBy, $sSelect);
							}
						}
					}
					else
					{
						if (self::checkPropertyIdentifier($PR_ID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($PR_ID, $iblockIds);
							if ($db_prop)
							{
								$this->MkPropertySelect($PR_ID, $db_prop, $arJoinProps, $bWasGroup, $sGroupBy, $sSelect);
							}
						}
					}
				}
				elseif(mb_substr($val, 0, 13) == "PROPERTYSORT_")
				{
					$PR_ID = mb_strtoupper($val);
					if(isset($arDisplayedColumns[$PR_ID]))
						continue;
					$arDisplayedColumns[$PR_ID] = true;
					$PR_ID = mb_substr($PR_ID, 13);

					if(preg_match(self::PROPERTY_LINK_ELEMENT_MASK, $PR_ID, $arMatch))
					{
						if (self::checkPropertyLinkIdentifier($PR_ID))
						{
							$db_prop = CIBlockProperty::GetPropertyArray($arMatch[1], $iblockIds);
							if (is_array($db_prop) && $db_prop["PROPERTY_TYPE"] == "E")
								$this->MkPropertySelect($arMatch, $db_prop, $arJoinProps, $bWasGroup, $sGroupBy, $sSelect, true);
						}
					}
					else
					{
						if (self::checkPropertyIdentifier($PR_ID))
						{
							if ($db_prop = CIBlockProperty::GetPropertyArray($PR_ID, $iblockIds))
								$this->MkPropertySelect($PR_ID, $db_prop, $arJoinProps, $bWasGroup, $sGroupBy, $sSelect, true);
						}
					}
				}
				elseif($val == "*")
				{
					$bStar = true;
				}
				elseif ($this->catalogIncluded && \CProductQueryBuilder::isValidField($val))
				{
					$arAddSelectFields[] = $val;
				}
				elseif(
					$val == "RATING_TOTAL_VALUE"
					|| $val == "RATING_TOTAL_VOTES"
					|| $val == "RATING_TOTAL_POSITIVE_VOTES"
					|| $val == "RATING_TOTAL_NEGATIVE_VOTES"
				)
				{
					if(isset($arDisplayedColumns[$val]))
						continue;
					$arDisplayedColumns[$val] = true;
					$arSelectFields[$key] = $val;
					$sSelect.=",".preg_replace("/^RATING_/", "RV.", $val)." as ".$val;
					$arJoinProps["RV"] = true;
				}
				elseif($val == "RATING_USER_VOTE_VALUE")
				{
					if(isset($arDisplayedColumns[$val]))
						continue;
					$arDisplayedColumns[$val] = true;
					$arSelectFields[$key] = $val;

					//if(isset($USER) && is_object($USER))
					if ($this->userExists)
					{
						$sSelect.=",".$DB->IsNull('RVU.VALUE', '0')." as ".$val;
						$arJoinProps["RVU"] = true;
					}
					else
					{
						$sSelect.=",0 as ".$val;
					}
				}
			}

			if($bStar)
			{
				foreach($arIblockElementFields as $key=>$val)
				{
					if(isset($arDisplayedColumns[$key]))
						continue;
					$arDisplayedColumns[$key] = true;
					$arSelectFields[]=$key;
					$sSelect.=",".$val." as ".$key;
				}
			}
			elseif ($sGroupBy === '')
			{
				//Try to add missing fields for correct URL translation (only then no grouping)
				if(isset($arDisplayedColumns["DETAIL_PAGE_URL"]))
					$arAddFields = array("LANG_DIR", "ID", "CODE", "EXTERNAL_ID", "IBLOCK_SECTION_ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_CODE", "IBLOCK_EXTERNAL_ID", "LID");
				elseif(isset($arDisplayedColumns["CANONICAL_PAGE_URL"]))
					$arAddFields = array("LANG_DIR", "ID", "CODE", "EXTERNAL_ID", "IBLOCK_SECTION_ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_CODE", "IBLOCK_EXTERNAL_ID", "LID");
				elseif(isset($arDisplayedColumns["SECTION_PAGE_URL"]))
					$arAddFields = array("LANG_DIR", "ID", "CODE", "EXTERNAL_ID", "IBLOCK_SECTION_ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_CODE", "IBLOCK_EXTERNAL_ID", "LID");
				elseif(isset($arDisplayedColumns["LIST_PAGE_URL"]))
					$arAddFields = array("LANG_DIR", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_CODE", "IBLOCK_EXTERNAL_ID", "LID");
				else
					$arAddFields = array();

				//Try to add missing fields for correct PREVIEW and DETAIL text formatting
				if(isset($arDisplayedColumns["DETAIL_TEXT"]))
					$arAddFields[] = "DETAIL_TEXT_TYPE";
				if(isset($arDisplayedColumns["PREVIEW_TEXT"]))
					$arAddFields[] = "PREVIEW_TEXT_TYPE";

				foreach($arAddFields as $key)
				{
					if(isset($arDisplayedColumns[$key]))
						continue;
					$arDisplayedColumns[$key] = true;
					$arSelectFields[]=$key;
					$sSelect.=",".$arIblockElementFields[$key]." as ".$key;
				}
			}

			if ($sGroupBy !== '')
			{
				$sSelect = mb_substr($sSelect, 1). ", COUNT(%%_DISTINCT_%% BE.ID) as CNT ";
			}
			elseif ($sSelect !== '')
			{
				$sSelect = "%%_DISTINCT_%% " . mb_substr($sSelect, 1) . " ";
			}
		}

		//*********************WHERE PART*********************
		$arAddWhereFields = [];
		if (is_array($arFilter) && isset($arFilter["CATALOG"]))
		{
			$arAddWhereFields = $arFilter["CATALOG"];
			unset($arFilter["CATALOG"]);
		}

		$arSqlSearch = CIBlockElement::MkFilter($arFilter, $arJoinProps, $arAddWhereFields);
		$this->bDistinct = false;
		$sSectionWhere = "";

		$sWhere = "";
		foreach ($arSqlSearch as $condition)
		{
			if (trim($condition, "\n\t") !== '')
			{
				$sWhere .= "\n\t\t\tAND (" . $condition . ")";
			}
		}
	}

	///////////////////////////////////////////////////////////////////
	// Add function
	///////////////////////////////////////////////////////////////////
	public function Add($arFields, $bWorkFlow=false, $bUpdateSearch=true, $bResizePictures=false)
	{
		global $DB;

		if ($this->iblock !== null && $this->iblock['ID'] === (int)$arFields["IBLOCK_ID"])
		{
			$arIBlock = $this->iblock;
		}
		else
		{
			$arIBlock = CIBlock::GetArrayByID($arFields["IBLOCK_ID"]);
		}

		$existIblock = !empty($arIBlock) && is_array($arIBlock);
		$bWorkFlow = $bWorkFlow && $existIblock && ($arIBlock["WORKFLOW"] != "N") && $this->workflowIncluded;
		$bBizProc = $existIblock && ($arIBlock["BIZPROC"] == "Y") && $this->bizprocInstalled;

		if(isset($arFields["BP_PUBLISHED"]))
		{
			if($bBizProc)
			{
				if($arFields["BP_PUBLISHED"] == "Y")
				{
					$arFields["WF_STATUS_ID"] = 1;
					$arFields["WF_NEW"] = false;
				}
				else
				{
					$arFields["WF_STATUS_ID"] = 2;
					$arFields["WF_NEW"] = "Y";
					$arFields["BP_PUBLISHED"] = "N";
				}
			}
			else
			{
				unset($arFields["BP_PUBLISHED"]);
			}
		}

		if(array_key_exists("IBLOCK_SECTION_ID", $arFields))
		{
			if (!isset($arFields["IBLOCK_SECTION"]))
			{
				$arFields["IBLOCK_SECTION"] = array($arFields["IBLOCK_SECTION_ID"]);
			}
			elseif (is_array($arFields["IBLOCK_SECTION"]) && !in_array($arFields["IBLOCK_SECTION_ID"], $arFields["IBLOCK_SECTION"]))
			{
				unset($arFields["IBLOCK_SECTION_ID"]);
			}
		}

		$strWarning = "";
		if($bResizePictures)
		{
			$arDef = $arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"];

			if(
				$arDef["FROM_DETAIL"] === "Y"
				&& isset($arFields["DETAIL_PICTURE"])
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arFields["DETAIL_PICTURE"]["size"] > 0
				&& (
					$arDef["UPDATE_WITH_DETAIL"] === "Y"
					|| $arFields["PREVIEW_PICTURE"]["size"] <= 0
				)
			)
			{
				$arNewPreview = $arFields["DETAIL_PICTURE"];
				$arNewPreview["COPY_FILE"] = "Y";
				if (
					isset($arFields["PREVIEW_PICTURE"])
					&& is_array($arFields["PREVIEW_PICTURE"])
					&& isset($arFields["PREVIEW_PICTURE"]["description"])
				)
				{
					$arNewPreview["description"] = $arFields["PREVIEW_PICTURE"]["description"];
				}

				$arFields["PREVIEW_PICTURE"] = $arNewPreview;
			}

			if(
				isset($arFields["PREVIEW_PICTURE"])
				&& is_array($arFields["PREVIEW_PICTURE"])
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["PREVIEW_PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["PREVIEW_PICTURE"]["description"];
					$arFields["PREVIEW_PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["PREVIEW_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				isset($arFields["PREVIEW_PICTURE"])
				&& is_array($arFields["PREVIEW_PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				$arFields["PREVIEW_PICTURE"]["copy"] ??= null;
				if(
					$arFields["PREVIEW_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PREVIEW_PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PREVIEW_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PREVIEW_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PREVIEW_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PREVIEW_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PREVIEW_PICTURE"]["copy"] = true;
					$arFields["PREVIEW_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PREVIEW_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
				));
			}

			if(
				isset($arFields["PREVIEW_PICTURE"])
				&& is_array($arFields["PREVIEW_PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				$arFields["PREVIEW_PICTURE"]["copy"] ??= null;
				if(
					$arFields["PREVIEW_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PREVIEW_PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PREVIEW_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PREVIEW_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PREVIEW_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PREVIEW_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PREVIEW_PICTURE"]["copy"] = true;
					$arFields["PREVIEW_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PREVIEW_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}

			$arDef = $arIBlock["FIELDS"]["DETAIL_PICTURE"]["DEFAULT_VALUE"];

			if(
				isset($arFields["DETAIL_PICTURE"])
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["DETAIL_PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["DETAIL_PICTURE"]["description"];
					$arFields["DETAIL_PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["DETAIL_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				isset($arFields["DETAIL_PICTURE"])
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				$arFields["DETAIL_PICTURE"]["copy"] ??= null;
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PREVIEW_PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
				));
			}

			if(
				isset($arFields["DETAIL_PICTURE"])
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				$arFields["DETAIL_PICTURE"]["copy"] ??= null;
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PREVIEW_PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}
		}

		$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($arFields["IBLOCK_ID"], 0);
		if(array_key_exists("PREVIEW_PICTURE", $arFields))
		{
			if (is_array($arFields["PREVIEW_PICTURE"]))
			{
				if (
					($arFields["PREVIEW_PICTURE"]["name"] ?? '') === ''
					&& ($arFields["PREVIEW_PICTURE"]["del"] ?? '') === ''
				)
				{
					unset($arFields["PREVIEW_PICTURE"]);
				}
				else
				{
					$arFields["PREVIEW_PICTURE"]["MODULE_ID"] = "iblock";
					$arFields["PREVIEW_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
						$ipropTemplates
						,"ELEMENT_PREVIEW_PICTURE_FILE_NAME"
						,$arFields
						,$arFields["PREVIEW_PICTURE"]
					);
				}
			}
			else
			{
				if((int)$arFields["PREVIEW_PICTURE"] <= 0)
					unset($arFields["PREVIEW_PICTURE"]);
			}
		}

		if(array_key_exists("DETAIL_PICTURE", $arFields))
		{
			if (is_array($arFields["DETAIL_PICTURE"]))
			{
				if (
					($arFields["DETAIL_PICTURE"]["name"] ?? '') === ''
					&& ($arFields["DETAIL_PICTURE"]["del"] ?? '') === ''
				)
				{
					unset($arFields["DETAIL_PICTURE"]);
				}
				else
				{
					$arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
					$arFields["DETAIL_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
						$ipropTemplates
						,"ELEMENT_DETAIL_PICTURE_FILE_NAME"
						,$arFields
						,$arFields["DETAIL_PICTURE"]
					);
				}
			}
			else
			{
				if((int)$arFields["DETAIL_PICTURE"] <= 0)
					unset($arFields["DETAIL_PICTURE"]);
			}
		}

		if(isset($arFields["ACTIVE"]) && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(isset($arFields["PREVIEW_TEXT_TYPE"]) && $arFields["PREVIEW_TEXT_TYPE"]!="html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if(isset($arFields["DETAIL_TEXT_TYPE"]) && $arFields["DETAIL_TEXT_TYPE"]!="html")
			$arFields["DETAIL_TEXT_TYPE"]="text";

		if(is_set($arFields, "DATE_ACTIVE_FROM"))
			$arFields["ACTIVE_FROM"] = $arFields["DATE_ACTIVE_FROM"];
		if(is_set($arFields, "DATE_ACTIVE_TO"))
			$arFields["ACTIVE_TO"] = $arFields["DATE_ACTIVE_TO"];
		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if($bWorkFlow)
		{
			$arFields["WF"] = "Y";
			if($arFields["WF_STATUS_ID"] != 1)
				$arFields["WF_NEW"] = "Y";
			else
				$arFields["WF_NEW"] = "";
		}

		$arFields["NAME"] = (string)$arFields["NAME"];
		$arFields["SEARCHABLE_CONTENT"] = false;
		if ($this->searchIncluded)
		{
			$arFields["SEARCHABLE_CONTENT"] = $arFields["NAME"];
			if (isset($arFields["PREVIEW_TEXT"]))
			{
				if (isset($arFields["PREVIEW_TEXT_TYPE"]) && $arFields["PREVIEW_TEXT_TYPE"] == "html")
					$arFields["SEARCHABLE_CONTENT"] .= "\r\n".HTMLToTxt($arFields["PREVIEW_TEXT"]);
				else
					$arFields["SEARCHABLE_CONTENT"] .= "\r\n".$arFields["PREVIEW_TEXT"];
			}
			if (isset($arFields["DETAIL_TEXT"]))
			{
				if (isset($arFields["DETAIL_TEXT_TYPE"]) && $arFields["DETAIL_TEXT_TYPE"] == "html")
					$arFields["SEARCHABLE_CONTENT"] .= "\r\n".HTMLToTxt($arFields["DETAIL_TEXT"]);
				else
					$arFields["SEARCHABLE_CONTENT"] .= "\r\n".$arFields["DETAIL_TEXT"];
			}
			$arFields["SEARCHABLE_CONTENT"] = mb_strtoupper($arFields["SEARCHABLE_CONTENT"]);
		}

		if(!$this->CheckFields($arFields) || $strWarning != '')
		{
			$this->LAST_ERROR .= $strWarning;
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if(array_key_exists("PREVIEW_PICTURE", $arFields))
			{
				$SAVED_PREVIEW_PICTURE = $arFields["PREVIEW_PICTURE"];
				if(is_array($arFields["PREVIEW_PICTURE"]))
					CFile::SaveForDB($arFields, "PREVIEW_PICTURE", "iblock");
				if($bWorkFlow)
					$COPY_PREVIEW_PICTURE = $arFields["PREVIEW_PICTURE"];
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$SAVED_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
				if(is_array($arFields["DETAIL_PICTURE"]))
					CFile::SaveForDB($arFields, "DETAIL_PICTURE", "iblock");
				if($bWorkFlow)
					$COPY_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
			}

			unset($arFields["ID"]);
			if ($this->userExists)
			{
				if(!isset($arFields["CREATED_BY"]) || (int)$arFields["CREATED_BY"] <= 0)
					$arFields["CREATED_BY"] = $this->userId;
				if(!isset($arFields["MODIFIED_BY"]) || (int)$arFields["MODIFIED_BY"] <= 0)
					$arFields["MODIFIED_BY"] = $this->userId;
			}
			$arFields["~TIMESTAMP_X"] = $arFields["~DATE_CREATE"] = $DB->CurrentTimeFunction();

			foreach (GetModuleEvents("iblock", "OnIBlockElementAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields));

			$IBLOCK_SECTION_ID = $arFields["IBLOCK_SECTION_ID"] ?? null;
			unset($arFields["IBLOCK_SECTION_ID"]);

			$ID = $DB->Add("b_iblock_element", $arFields, array("DETAIL_TEXT", "SEARCHABLE_CONTENT"), "iblock");

			if(array_key_exists("PREVIEW_PICTURE", $arFields))
			{
				$arFields["PREVIEW_PICTURE_ID"] = $arFields["PREVIEW_PICTURE"];
				$arFields["PREVIEW_PICTURE"] = $SAVED_PREVIEW_PICTURE;
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$arFields["DETAIL_PICTURE_ID"] = $arFields["DETAIL_PICTURE"];
				$arFields["DETAIL_PICTURE"] = $SAVED_DETAIL_PICTURE;
			}

			if ($arIBlock['VERSION'] == Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE)
			{
				$DB->Query("INSERT INTO b_iblock_element_prop_s".$arFields["IBLOCK_ID"]."(IBLOCK_ELEMENT_ID)VALUES(".$ID.")");
			}

			if (!empty($arFields["PROPERTY_VALUES"]) && is_array($arFields["PROPERTY_VALUES"]))
			{
				CIBlockElement::SetPropertyValues($ID, $arFields["IBLOCK_ID"], $arFields["PROPERTY_VALUES"]);
			}

			$updateFields = array();
			if(!isset($arFields["XML_ID"]) || $arFields["XML_ID"] == '')
			{
				$arFields["XML_ID"] = $ID;
				$updateFields["XML_ID"] = $ID;
			}
			if (!$this->searchIncluded)
			{
				$arFields['SEARCHABLE_CONTENT'] = $this->getSearchableContent($ID, $arFields, $arIBlock);
				$updateFields['SEARCHABLE_CONTENT'] = $arFields['SEARCHABLE_CONTENT'];
			}

			if (!empty($updateFields))
			{
				$updateQuery = $DB->PrepareUpdate("b_iblock_element", $updateFields, "iblock");
				if ($updateQuery != "")
				{
					$updateQuery .= ', TIMESTAMP_X = TIMESTAMP_X';
					$DB->Query("UPDATE b_iblock_element SET ".$updateQuery." WHERE ID = ".$ID);
				}
			}
			unset($updateFields);

			if(is_set($arFields, "IBLOCK_SECTION"))
				CIBlockElement::SetElementSection($ID, $arFields["IBLOCK_SECTION"], true, $arIBlock["RIGHTS_MODE"] === "E"? $arIBlock["ID"]: 0, $IBLOCK_SECTION_ID);

			if ($arIBlock["RIGHTS_MODE"] === Iblock\IblockTable::RIGHTS_EXTENDED)
			{
				$obElementRights = new CIBlockElementRights($arIBlock["ID"], $ID);
				if(!is_set($arFields, "IBLOCK_SECTION") || empty($arFields["IBLOCK_SECTION"]))
					$obElementRights->ChangeParents(array(), array(0));
				if(array_key_exists("RIGHTS", $arFields) && is_array($arFields["RIGHTS"]))
					$obElementRights->SetRights($arFields["RIGHTS"]);
			}

			if (array_key_exists("IPROPERTY_TEMPLATES", $arFields))
			{
				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($arIBlock["ID"], $ID);
				$ipropTemplates->set($arFields["IPROPERTY_TEMPLATES"]);
			}

			if ($bUpdateSearch && $this->searchIncluded)
			{
				CIBlockElement::UpdateSearch($ID);
			}

			\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($arIBlock["ID"], $ID);

			if(
				!isset($arFields["WF_PARENT_ELEMENT_ID"])
				&& $arIBlock["FIELDS"]["LOG_ELEMENT_ADD"]["IS_REQUIRED"] == "Y"
			)
			{
				$arEvents = GetModuleEvents("main", "OnBeforeEventLog", true);
				if(
					empty($arEvents)
					|| ExecuteModuleEventEx($arEvents[0], array($this->userId))===false
				)
				{
					$rsElement = CIBlockElement::GetList(array(), array("=ID"=>$ID), false, false, array("LIST_PAGE_URL", "NAME", "CODE"));
					$arElement = $rsElement->GetNext();
					$res = array(
						"ID" => $ID,
						"CODE" => $arElement["CODE"],
						"NAME" => $arElement["NAME"],
						"ELEMENT_NAME" => $arIBlock["ELEMENT_NAME"],
						"USER_ID" => $this->userId,
						"IBLOCK_PAGE_URL" => $arElement["LIST_PAGE_URL"],
					);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_ELEMENT_ADD",
						"iblock",
						$arIBlock["ID"],
						serialize($res)
					);
				}
			}
			if($bWorkFlow && (int)($arFields["WF_PARENT_ELEMENT_ID"] ?? null) <= 0)
			{
				// It is completly new element - so make it copy
				unset($arFields["WF_NEW"]);
				$arFields["WF_PARENT_ELEMENT_ID"] = $ID;
				$arNewFields = $arFields;
				$arNewFields["PREVIEW_PICTURE"] = $COPY_PREVIEW_PICTURE ?? null;
				$arNewFields["DETAIL_PICTURE"] = $COPY_DETAIL_PICTURE ?? null;

				if (isset($arNewFields['PROPERTY_VALUES']) && is_array($arNewFields['PROPERTY_VALUES']))
				{
					$i = 0;
					$db_prop = CIBlockProperty::GetList(array(), array(
						"IBLOCK_ID" => $arFields["IBLOCK_ID"],
						"CHECK_PERMISSIONS" => "N",
						"PROPERTY_TYPE" => "F",
					));
					while($arProp = $db_prop->Fetch())
					{
						$i++;
						unset($arNewFields["PROPERTY_VALUES"][$arProp["CODE"]]);
						unset($arNewFields["PROPERTY_VALUES"][$arProp["ID"]]);
						$arNewFields["PROPERTY_VALUES"][$arProp["ID"]] = array();
					}

					if($i > 0)
					{
						$props = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $ID, "sort", "asc", array("PROPERTY_TYPE" => "F", "EMPTY" => "N"));
						while($arProp = $props->Fetch())
						{
							$arNewFields["PROPERTY_VALUES"][$arProp["ID"]][$arProp['PROPERTY_VALUE_ID']] = array(
								"VALUE" => $arProp["VALUE"],
								"DESCRIPTION" => $arProp["DESCRIPTION"],
							);
						}
					}
				}

				$WF_ID = $this->Add($arNewFields);
				if($this->bWF_SetMove)
					CIBlockElement::WF_SetMove($WF_ID);
			}

			$Result = $ID;
			$arFields["ID"] = &$ID;
			CDiskQuota::recalculateDb();
			self::$elementIblock[$ID] = $arIBlock['ID'];
		}

		if(
			isset($arFields["PREVIEW_PICTURE"])
			&& is_array($arFields["PREVIEW_PICTURE"])
			&& ($arFields["PREVIEW_PICTURE"]["COPY_FILE"] ?? '') === "Y"
			&& ($arFields["PREVIEW_PICTURE"]["copy"] ?? null)
		)
		{
			@unlink($arFields["PREVIEW_PICTURE"]["tmp_name"]);
			@rmdir(dirname($arFields["PREVIEW_PICTURE"]["tmp_name"]));
		}

		if(
			isset($arFields["DETAIL_PICTURE"])
			&& is_array($arFields["DETAIL_PICTURE"])
			&& ($arFields["DETAIL_PICTURE"]["COPY_FILE"] ?? '') === "Y"
			&& ($arFields["DETAIL_PICTURE"]["copy"] ?? null)
		)
		{
			@unlink($arFields["DETAIL_PICTURE"]["tmp_name"]);
			@rmdir(dirname($arFields["DETAIL_PICTURE"]["tmp_name"]));
		}

		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockElementAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		CIBlock::clearIblockTagCache($arIBlock['ID']);

		ElementTable::cleanCache();

		return $Result;
	}

	public static function DeleteFile($FILE_ID, $ELEMENT_ID, $TYPE = false, $PARENT_ID = -1, $IBLOCK_ID = false, $bCheckOnly = false)
	{
		global $DB;

		$FILE_ID = (int)$FILE_ID;
		if($FILE_ID <= 0)
			return;

		if($ELEMENT_ID !== false)
		{//ELEMENT_ID may be false when we are going to check for a valid file from CheckFields
			$ELEMENT_ID = (int)$ELEMENT_ID;
			if($ELEMENT_ID <= 0)
				return;
		}

		$IBLOCK_ID = (int)$IBLOCK_ID;
		if($IBLOCK_ID <= 0 || $PARENT_ID===-1)
		{
			if($ELEMENT_ID===false)
				return; //This is an error in API call
			$rsElement = $DB->Query("SELECT IBLOCK_ID, WF_PARENT_ELEMENT_ID from b_iblock_element WHERE ID = ".$ELEMENT_ID);
			$arElement = $rsElement->Fetch();
			if(!$arElement)
				return;
			$IBLOCK_ID = $arElement["IBLOCK_ID"];
			$PARENT_ID = $arElement["WF_PARENT_ELEMENT_ID"];
		}

		if($TYPE === false)
		{
			$CNT = CIBlockElement::DeleteFile($FILE_ID, $ELEMENT_ID, "PREVIEW", $PARENT_ID, $IBLOCK_ID);
			$CNT += CIBlockElement::DeleteFile($FILE_ID, $ELEMENT_ID, "DETAIL", $PARENT_ID, $IBLOCK_ID);
			$CNT += CIBlockElement::DeleteFile($FILE_ID, $ELEMENT_ID, "PROPERTY", $PARENT_ID, $IBLOCK_ID);
			return $CNT;
		}

		$VERSION = CIBlockElement::GetIBVersion($IBLOCK_ID);

		$arProps = array();
		if($TYPE === "PROPERTY" && $VERSION==2)
		{
			$strSQL = "
				SELECT P.ID
				FROM
				b_iblock_property P
				WHERE P.IBLOCK_ID = ".$IBLOCK_ID."
				AND P.PROPERTY_TYPE = 'F'
				AND P.MULTIPLE = 'N'
			";
			$rs = $DB->Query($strSQL);
			while($ar = $rs->Fetch())
				$arProps[] = " V.PROPERTY_".(int)$ar["ID"]." = ".$FILE_ID;
		}

		if($ELEMENT_ID === false)
		{
			//It is new historical record so we'' check original
			//and all over history already there
			$arWhere = array(
				"E.ID=".(int)$PARENT_ID,
				"E.WF_PARENT_ELEMENT_ID=".(int)$PARENT_ID
			);
		}
		elseif((int)$PARENT_ID)
		{
			//It's an historical record so we will check original
			// and all history except deleted one
			$arWhere = array(
				"E.ID=".(int)$PARENT_ID,
				"E.WF_PARENT_ELEMENT_ID=".(int)$PARENT_ID." AND E.ID <> ".$ELEMENT_ID
			);
		}
		else
		{
			//It is an original so we have to check only history
			//all history copies
			$arWhere = array(
				"E.WF_PARENT_ELEMENT_ID=".$ELEMENT_ID
			);
		}

		$CNT = 0;
		foreach($arWhere as $strWhere)
		{
			if($TYPE === "PREVIEW")
			{
				$strSQL = "
					SELECT COUNT(1) CNT
					from b_iblock_element E
					WHERE ".$strWhere."
					AND PREVIEW_PICTURE = ".$FILE_ID."
				";

			}
			elseif($TYPE === "DETAIL")
			{
				$strSQL = "
					SELECT COUNT(1) CNT
					from b_iblock_element E
					WHERE ".$strWhere."
					AND DETAIL_PICTURE = ".$FILE_ID."
				";
			}
			elseif($TYPE === "PROPERTY")
			{
				if($VERSION==2)
				{
					$strSQL = "
						SELECT COUNT(1) CNT
						FROM
							b_iblock_element E
							,b_iblock_property P
							,b_iblock_element_prop_m".$IBLOCK_ID." V
						WHERE ".$strWhere."
						AND E.IBLOCK_ID = ".$IBLOCK_ID."
						AND P.IBLOCK_ID = E.IBLOCK_ID
						AND P.PROPERTY_TYPE = 'F'
						AND V.IBLOCK_ELEMENT_ID = E.ID
						AND V.IBLOCK_PROPERTY_ID = P.ID
						AND V.VALUE_NUM = ".$FILE_ID."
					";
				}
				else
				{
					$strSQL = "
						SELECT COUNT(1) CNT
						FROM
							b_iblock_element E
							,b_iblock_property P
							,b_iblock_element_property V
						WHERE ".$strWhere."
						AND E.IBLOCK_ID = ".$IBLOCK_ID."
						AND P.IBLOCK_ID = E.IBLOCK_ID
						AND P.PROPERTY_TYPE = 'F'
						AND V.IBLOCK_ELEMENT_ID = E.ID
						AND V.IBLOCK_PROPERTY_ID = P.ID
						AND V.VALUE_NUM = ".$FILE_ID."
					";
				}
			}

			$rs = $DB->Query($strSQL);
			$ar = $rs->Fetch();

			$CNT += (int)$ar["CNT"];
			if($CNT > 0)
				return $CNT;

			//Check VERSION 2 SINGLE PROPERTIES
			if(!empty($arProps))
			{
				//This SQL potentially wrong
				//in case when file may be saved in
				//different properties
				$strSQL = "
					SELECT COUNT(1) CNT
					FROM
						b_iblock_element E
						,b_iblock_property P
						,b_iblock_element_prop_s".$IBLOCK_ID." V
					WHERE ".$strWhere."
					AND E.IBLOCK_ID = ".$IBLOCK_ID."
					AND P.IBLOCK_ID = E.IBLOCK_ID
					AND P.PROPERTY_TYPE = 'F'
					AND V.IBLOCK_ELEMENT_ID = E.ID
					AND (".implode(" OR ", $arProps).")
				";
				$rs = $DB->Query($strSQL);
				$ar = $rs->Fetch();
				$CNT += (int)$ar["CNT"];
				if($CNT > 0)
					return $CNT;
			}
		}

		if($bCheckOnly)
			return $CNT;
		elseif($CNT === 0)
			CFile::Delete($FILE_ID);
	}

	///////////////////////////////////////////////////////////////////
	// Removes element
	///////////////////////////////////////////////////////////////////
	public static function Delete($ID)
	{
		global $DB, $APPLICATION, $USER;
		$USER_ID = is_object($USER)? (int)$USER->GetID() : 0;
		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}

		$APPLICATION->ResetException();
		foreach (GetModuleEvents("iblock", "OnBeforeIBlockElementDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = "";
				$err_id = false;
				$ex = $APPLICATION->GetException();
				if (is_object($ex))
				{
					$err = $ex->GetString();
					$err_id = $ex->GetID();
				}
				$APPLICATION->throwException($err, $err_id);
				return false;
			}
		}

		$arSql = array(
			"ID='".$ID."'",
			"WF_PARENT_ELEMENT_ID='".$ID."'",
		);
		foreach($arSql as $strWhere)
		{
			$strSql = "
				SELECT
					ID
					,IBLOCK_ID
					,WF_PARENT_ELEMENT_ID
					,WF_STATUS_ID
					,PREVIEW_PICTURE
					,DETAIL_PICTURE
					,XML_ID as EXTERNAL_ID
					,CODE
					,NAME
				FROM b_iblock_element
				WHERE ".$strWhere."
				ORDER BY ID DESC
			";
			$z = $DB->Query($strSql);
			while ($zr = $z->Fetch())
			{
				$elementId = (int)$zr["ID"];
				$VERSION = CIBlockElement::GetIBVersion($zr["IBLOCK_ID"]);
				$db_res = CIBlockElement::GetProperty($zr["IBLOCK_ID"], $zr["ID"], "sort", "asc", array("PROPERTY_TYPE"=>"F"));

				$arIBlockFields = CIBlock::GetArrayByID($zr["IBLOCK_ID"], "FIELDS");
				if(
					(int)$zr["WF_PARENT_ELEMENT_ID"]<=0
					&& $arIBlockFields["LOG_ELEMENT_DELETE"]["IS_REQUIRED"] == "Y"
				)
				{
					$arEvents = GetModuleEvents("main", "OnBeforeEventLog", true);

					if(empty($arEvents) || ExecuteModuleEventEx($arEvents[0], array($USER_ID))===false)
					{
						$rsElement = CIBlockElement::GetList(array(), array("=ID"=>$ID), false, false, array("LIST_PAGE_URL", "NAME", "CODE"));
						$arElement = $rsElement->GetNext();
						$arIblock = CIBlock::GetArrayByID($zr['IBLOCK_ID']);
						$res_log = array(
							"ID" => $ID,
							"CODE" => $arElement["CODE"],
							"NAME" => $arElement["NAME"],
							"ELEMENT_NAME" => $arIblock["ELEMENT_NAME"],
							"USER_ID" => $USER_ID,
							"IBLOCK_PAGE_URL" => $arElement["LIST_PAGE_URL"],
						);
						CEventLog::Log(
							"IBLOCK",
							"IBLOCK_ELEMENT_DELETE",
							"iblock",
							$zr["IBLOCK_ID"],
							serialize($res_log)
						);
					}
				}

				$piId = \Bitrix\Iblock\PropertyIndex\Manager::resolveElement($zr["IBLOCK_ID"], $zr["ID"]);

				foreach (GetModuleEvents("iblock", "OnIBlockElementDelete", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array($elementId, $zr));

				while($res = $db_res->Fetch())
					CIBlockElement::DeleteFile($res["VALUE"], $zr["ID"], "PROPERTY", $zr["WF_PARENT_ELEMENT_ID"], $zr["IBLOCK_ID"]);

				if($VERSION==2)
				{
					if(!$DB->Query("DELETE FROM b_iblock_element_prop_m".$zr["IBLOCK_ID"]." WHERE IBLOCK_ELEMENT_ID = ".$elementId))
						return false;
					if(!$DB->Query("DELETE FROM b_iblock_element_prop_s".$zr["IBLOCK_ID"]." WHERE IBLOCK_ELEMENT_ID = ".$elementId))
						return false;
				}
				else
				{
					if(!$DB->Query("DELETE FROM b_iblock_element_property WHERE IBLOCK_ELEMENT_ID = ".$elementId))
						return false;
				}

				static $arDelCache = array();
				if(!is_set($arDelCache, $zr["IBLOCK_ID"]))
				{
					$arDelCache[$zr["IBLOCK_ID"]] = [];
					$db_ps = $DB->Query("SELECT ID,IBLOCK_ID,VERSION,MULTIPLE FROM b_iblock_property WHERE PROPERTY_TYPE='E' AND (LINK_IBLOCK_ID=".$zr["IBLOCK_ID"]." OR LINK_IBLOCK_ID=0 OR LINK_IBLOCK_ID IS NULL)");
					while($ar_ps = $db_ps->Fetch())
					{
						if($ar_ps["VERSION"]==2)
						{
							if($ar_ps["MULTIPLE"]=="Y")
								$strTable = "b_iblock_element_prop_m".$ar_ps["IBLOCK_ID"];
							else
								$strTable = "b_iblock_element_prop_s".$ar_ps["IBLOCK_ID"];
						}
						else
						{
							$strTable = "b_iblock_element_property";
						}
						$arDelCache[$zr["IBLOCK_ID"]][$strTable][] = $ar_ps["ID"];
					}
				}

				if($arDelCache[$zr["IBLOCK_ID"]])
				{
					foreach($arDelCache[$zr["IBLOCK_ID"]] as $strTable=>$arProps)
					{
						if(strncmp("b_iblock_element_prop_s", $strTable, 23)==0)
						{
							$tableFields = $DB->GetTableFields($strTable);
							foreach($arProps as $prop_id)
							{
								$strSql = "UPDATE ".$strTable." SET PROPERTY_".$prop_id."=null";
								if (isset($tableFields["DESCRIPTION_".$prop_id]))
									$strSql .= ",DESCRIPTION_".$prop_id."=null";
								$strSql .= " WHERE PROPERTY_".$prop_id."=".$zr["ID"];
								if(!$DB->Query($strSql))
									return false;
							}
						}
						elseif(strncmp("b_iblock_element_prop_m", $strTable, 23)==0)
						{
							$tableFields = $DB->GetTableFields(str_replace("prop_m", "prop_s", $strTable));
							$strSql = "SELECT IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$zr["ID"];
							$rs = $DB->Query($strSql);
							while($ar = $rs->Fetch())
							{
								$strSql = "
									UPDATE ".str_replace("prop_m", "prop_s", $strTable)."
									SET PROPERTY_".$ar["IBLOCK_PROPERTY_ID"]."=null
									".(isset($tableFields["DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]])? ",DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]."=null": "")."
									WHERE IBLOCK_ELEMENT_ID = ".$ar["IBLOCK_ELEMENT_ID"]."
								";
								if(!$DB->Query($strSql))
									return false;
							}
							$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$zr["ID"];
							if(!$DB->Query($strSql))
								return false;
						}
						else
						{
							$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$zr["ID"];
							if(!$DB->Query($strSql))
								return false;
						}
					}
				}

				if(!$DB->Query("DELETE FROM b_iblock_section_element WHERE IBLOCK_ELEMENT_ID = ".$elementId))
					return false;

				$obIBlockElementRights = new CIBlockElementRights($zr["IBLOCK_ID"], $zr["ID"]);
				$obIBlockElementRights->DeleteAllRights();

				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($zr["IBLOCK_ID"], $zr["ID"]);
				$ipropTemplates->delete();

				if((int)$zr["WF_PARENT_ELEMENT_ID"]<=0 && $zr["WF_STATUS_ID"]==1 && CModule::IncludeModule("search"))
				{
					CSearch::DeleteIndex("iblock", $elementId);
				}

				CIBlockElement::DeleteFile($zr["PREVIEW_PICTURE"], $zr["ID"], "PREVIEW", $zr["WF_PARENT_ELEMENT_ID"], $zr["IBLOCK_ID"]);
				CIBlockElement::DeleteFile($zr["DETAIL_PICTURE"], $zr["ID"], "DETAIL", $zr["WF_PARENT_ELEMENT_ID"], $zr["IBLOCK_ID"]);

				if(CModule::IncludeModule("workflow"))
					$DB->Query("DELETE FROM b_workflow_move WHERE IBLOCK_ELEMENT_ID=".$elementId);

				$DB->Query("DELETE FROM b_iblock_element_lock WHERE IBLOCK_ELEMENT_ID=".$elementId);
				$DB->Query("DELETE FROM b_rating_vote WHERE ENTITY_TYPE_ID = 'IBLOCK_ELEMENT' AND ENTITY_ID = ".$elementId);
				$DB->Query("DELETE FROM b_rating_voting WHERE ENTITY_TYPE_ID = 'IBLOCK_ELEMENT' AND ENTITY_ID = ".$elementId);

				if(!$DB->Query("DELETE FROM b_iblock_element WHERE ID=".$elementId))
					return false;

				if (isset(self::$elementIblock[$elementId]))
					unset(self::$elementIblock[$elementId]);

				\Bitrix\Iblock\PropertyIndex\Manager::deleteElementIndex($zr["IBLOCK_ID"], $piId);

				if(CModule::IncludeModule("bizproc"))
				{
					$arErrorsTmp = [];
					CBPDocument::OnDocumentDelete(["iblock", "CIBlockDocument", $zr["ID"]], $arErrorsTmp);
				}

				foreach (GetModuleEvents("iblock", "OnAfterIBlockElementDelete", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array($zr));

				CIBlock::clearIblockTagCache($zr['IBLOCK_ID']);

				ElementTable::cleanCache();

				unset($elementId);
			}
		}
		/************* QUOTA *************/
		CDiskQuota::recalculateDb();
		/************* QUOTA *************/
		return true;
	}

	public static function GetByID($ID)
	{
		return CIBlockElement::GetList(array(), array("ID"=>(int)$ID, "SHOW_HISTORY"=>"Y"));
	}

	/**
	 * Return IBLOCK_ID for element.
	 *
	 * @param int $ID				Element id.
	 * @return bool|int
	 */
	public static function GetIBlockByID($ID)
	{
		global $DB;
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		if (!isset(self::$elementIblock[$ID]))
		{
			self::$elementIblock[$ID] = false;
			$strSql = "select IBLOCK_ID from b_iblock_element where ID=".$ID;
			$rsItems = $DB->Query($strSql);
			if ($arItem = $rsItems->Fetch())
				self::$elementIblock[$ID] = (int)$arItem['IBLOCK_ID'];
			unset($arItem, $rsItems);
		}
		return self::$elementIblock[$ID];
	}

	/**
	 * Return IBLOCK_ID for element.
	 *
	 * @param array $list
	 * @return array
	 */
	public static function GetIBlockByIDList(array $list)
	{
		global $DB;
		$output = array();
		Main\Type\Collection::normalizeArrayValuesByInt($list);
		if (empty($list))
			return $output;
		foreach ($list as $index => $id)
		{
			if (!empty(self::$elementIblock[$id]))
			{
				$output[$id] = self::$elementIblock[$id];
				unset($list[$index]);
			}
		}

		if (!empty($list))
		{
			foreach (array_chunk($list, 500) as $pageIds)
			{
				$strSql = "select IBLOCK_ID,ID from b_iblock_element where ID in (".join(', ', $pageIds).")";
				$rsItems = $DB->Query($strSql);
				while ($itemData = $rsItems->Fetch())
				{
					$output[$itemData['ID']] = self::$elementIblock[$itemData['ID']] = (int)$itemData['IBLOCK_ID'];
				}
				unset($itemData, $rsItems);
			}
			unset($pageIds);
		}

		return $output;
	}

	///////////////////////////////////////////////////////////////////
	// Checks fields before update or insert
	///////////////////////////////////////////////////////////////////
	public function CheckFields(&$arFields, $ID=false, $bCheckDiskQuota=true)
	{
		global $DB, $APPLICATION, $USER;
		$this->LAST_ERROR = "";

		$APPLICATION->ResetException();
		if($ID===false)
		{
			$db_events = GetModuleEvents("iblock", "OnStartIBlockElementAdd", true);
		}
		else
		{
			$arFields["ID"] = $ID;
			$db_events = GetModuleEvents("iblock", "OnStartIBlockElementUpdate", true);
		}

		foreach ($db_events as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
				break;
		}

		if(($ID===false || array_key_exists("NAME", $arFields)) && (string)$arFields["NAME"] === '')
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_ELEMENT_NAME")."<br>";

		if(
			isset($arFields["ACTIVE_FROM"])
			&& $arFields["ACTIVE_FROM"] != ''
			&& !$DB->IsDate($arFields["ACTIVE_FROM"], false, LANG, "FULL")
		)
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_ACTIVE_FROM")."<br>";

		if(
			isset($arFields["ACTIVE_TO"])
			&& $arFields["ACTIVE_TO"] != ''
			&& !$DB->IsDate($arFields["ACTIVE_TO"], false, LANG, "FULL")
		)
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_ACTIVE_TO")."<br>";

		if(is_set($arFields, "PREVIEW_PICTURE"))
		{
			if(
				is_array($arFields["PREVIEW_PICTURE"])
				&& array_key_exists("bucket", $arFields["PREVIEW_PICTURE"])
				&& is_object($arFields["PREVIEW_PICTURE"]["bucket"])
			)
			{
				//This is trusted image from xml import
			}
			elseif(is_array($arFields["PREVIEW_PICTURE"]))
			{
				$error = CFile::CheckImageFile($arFields["PREVIEW_PICTURE"]);
				if($error <> '')
					$this->LAST_ERROR .= $error."<br>";
				elseif(($error = CFile::checkForDb($arFields, "PREVIEW_PICTURE")) !== "")
					$this->LAST_ERROR .= GetMessage("IBLOCK_ERR_PREVIEW_PICTURE")."<br>".$error."<br>";
			}
			elseif((int)$arFields["PREVIEW_PICTURE"] > 0)
			{
				if(
					(int)$arFields["WF_PARENT_ELEMENT_ID"] <= 0
					|| CIBlockElement::DeleteFile(
							$arFields["PREVIEW_PICTURE"],
							$ID,
							"PREVIEW",
							(int)$arFields["WF_PARENT_ELEMENT_ID"],
							$arFields["IBLOCK_ID"],
							true
					) <= 0
				)
				{
					$this->LAST_ERROR .= GetMessage("IBLOCK_ERR_PREVIEW_PICTURE")."<br>";
				}
			}
		}

		if(is_set($arFields, "DETAIL_PICTURE"))
		{
			if(
				is_array($arFields["DETAIL_PICTURE"])
				&& array_key_exists("bucket", $arFields["DETAIL_PICTURE"])
				&& is_object($arFields["DETAIL_PICTURE"]["bucket"])
			)
			{
				//This is trusted image from xml import
			}
			elseif(is_array($arFields["DETAIL_PICTURE"]))
			{
				$error = CFile::CheckImageFile($arFields["DETAIL_PICTURE"]);
				if($error <> '')
					$this->LAST_ERROR .= $error."<br>";
				elseif(($error = CFile::checkForDb($arFields, "DETAIL_PICTURE")) !== "")
					$this->LAST_ERROR .= GetMessage("IBLOCK_ERR_DETAIL_PICTURE")."<br>".$error."<br>";
			}
			elseif((int)$arFields["DETAIL_PICTURE"] > 0)
			{
				if(
					(int)($arFields["WF_PARENT_ELEMENT_ID"]) <= 0
					|| CIBlockElement::DeleteFile(
							$arFields["DETAIL_PICTURE"],
							$ID,
							"DETAIL",
							(int)$arFields["WF_PARENT_ELEMENT_ID"],
							$arFields["IBLOCK_ID"],
							true
					) <= 0
				)
				{
					$this->LAST_ERROR .= GetMessage("IBLOCK_ERR_DETAIL_PICTURE")."<br>";
				}
			}
		}

		if(array_key_exists("TAGS", $arFields) && CModule::IncludeModule('search'))
		{
			$arFields["TAGS"] = implode(", ", tags_prepare($arFields["TAGS"]));
		}

		if($ID===false && !is_set($arFields, "IBLOCK_ID"))
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";

		//Find out IBLOCK_ID from fields or from element
		$IBLOCK_ID = (int)($arFields["IBLOCK_ID"] ?? 0);
		if($IBLOCK_ID <= 0)
		{
			$IBLOCK_ID = 0;
			$res = $DB->Query("SELECT IBLOCK_ID FROM b_iblock_element WHERE ID=".intval($ID));
			if($ar = $res->Fetch())
				$IBLOCK_ID = (int)$ar["IBLOCK_ID"];
		}

		//Read iblock metadata
		static $IBLOCK_CACHE = array();
		if(!isset($IBLOCK_CACHE[$IBLOCK_ID]))
		{
			if($IBLOCK_ID > 0)
				$IBLOCK_CACHE[$IBLOCK_ID] = CIBlock::GetArrayByID($IBLOCK_ID);
			else
				$IBLOCK_CACHE[$IBLOCK_ID] = false;
		}

		if($IBLOCK_CACHE[$IBLOCK_ID])
			$arFields["IBLOCK_ID"] = $IBLOCK_ID;
		else
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";

		if (is_set($arFields,'IBLOCK_SECTION') && !empty($arFields['IBLOCK_SECTION']))
		{
			if (!is_array($arFields['IBLOCK_SECTION']))
				$arFields['IBLOCK_SECTION'] = array($arFields['IBLOCK_SECTION']);
			$arFields['IBLOCK_SECTION'] = array_filter($arFields['IBLOCK_SECTION']);
		}

		if($IBLOCK_CACHE[$IBLOCK_ID])
		{
			$ar = $IBLOCK_CACHE[$IBLOCK_ID]["FIELDS"];
			if(is_array($ar))
			{
				$WF_PARENT_ELEMENT_ID = (int)($arFields["WF_PARENT_ELEMENT_ID"] ?? 0);
				if(
					(
						$WF_PARENT_ELEMENT_ID == 0
						|| $WF_PARENT_ELEMENT_ID == (int)$ID
					)
					&& array_key_exists("CODE", $arFields)
					&& $arFields["CODE"] <> ''
					&& is_array($ar["CODE"]["DEFAULT_VALUE"])
					&& $ar["CODE"]["DEFAULT_VALUE"]["UNIQUE"] == "Y"
				)
				{
					$res = $DB->Query("
						SELECT ID
						FROM b_iblock_element
						WHERE IBLOCK_ID = ".$IBLOCK_ID."
						AND CODE = '".$DB->ForSQL($arFields["CODE"])."'
						AND WF_PARENT_ELEMENT_ID IS NULL
						AND ID <> ".(int)$ID
					);
					if($res->Fetch())
						$this->LAST_ERROR .= GetMessage("IBLOCK_DUP_ELEMENT_CODE")."<br>";
				}


				$arOldElement = false;
				foreach($ar as $FIELD_ID => $field)
				{
					if(preg_match("/^(SECTION_|LOG_)/", $FIELD_ID))
						continue;

					if($field["IS_REQUIRED"] === "Y")
					{
						switch($FIELD_ID)
						{
						case "NAME":
						case "ACTIVE":
						case "PREVIEW_TEXT_TYPE":
						case "DETAIL_TEXT_TYPE":
						case "SORT":
							//We should never check for this fields
							break;
						case "IBLOCK_SECTION":
							if($ID===false || array_key_exists($FIELD_ID, $arFields))
							{
								$sum = 0;
								if(is_array($arFields[$FIELD_ID]))
								{
									foreach($arFields[$FIELD_ID] as $k => $v)
										if(intval($v) > 0)
											$sum += intval($v);
								}
								else
								{
									$sum = intval($arFields[$FIELD_ID]);
								}
								if($sum <= 0)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							break;
						case "PREVIEW_PICTURE":
						case "DETAIL_PICTURE":
							if($ID !== false && !$arOldElement)
							{
								$rs = $DB->Query("SELECT PREVIEW_PICTURE, DETAIL_PICTURE from b_iblock_element WHERE ID = ".intval($ID));
								$arOldElement = $rs->Fetch();
							}
							if($arOldElement && $arOldElement[$FIELD_ID] > 0)
							{//There was an picture so just check that it is not deleted
								if(
									array_key_exists($FIELD_ID, $arFields)
									&& is_array($arFields[$FIELD_ID])
									&& $arFields[$FIELD_ID]["del"] === "Y"
								)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							else
							{//There was NO picture so it MUST be present
								if(!array_key_exists($FIELD_ID, $arFields))
								{
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
								}
								elseif(is_array($arFields[$FIELD_ID]))
								{
									if(
										$arFields[$FIELD_ID]["del"] === "Y"
										|| (array_key_exists("error", $arFields[$FIELD_ID]) && $arFields[$FIELD_ID]["error"] !== 0)
										|| $arFields[$FIELD_ID]["size"] <= 0
									)
										$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
								}
								else
								{
									if(intval($arFields[$FIELD_ID]) <= 0)
										$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
								}
							}
							break;
						case "XML_ID":
							if ($ID !== false && array_key_exists($FIELD_ID, $arFields))
							{
								$val = $arFields[$FIELD_ID];
								if($val == '')
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							break;
						default:
							if($ID===false || array_key_exists($FIELD_ID, $arFields))
							{
								if(is_array($arFields[$FIELD_ID]))
									$val = implode("", $arFields[$FIELD_ID]);
								else
									$val = $arFields[$FIELD_ID];
								if($val == '')
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							break;
						}
					}
				}
			}
		}

		if(
			array_key_exists("PROPERTY_VALUES", $arFields)
			&& is_array($arFields["PROPERTY_VALUES"])
			//&& intval($arFields["WF_PARENT_ELEMENT_ID"]) <= 0 //
		)
		{
			//First "normalize" properties to form:
			//$arFields["PROPERTY_VALUES"][<PROPERTY_ID>][<PROPERTY_VALUE_ID>] => $value
			$arProperties = array();
			foreach($arFields["PROPERTY_VALUES"] as $key => $property_values)
			{
				$arProperties[$key] = array();
				if(is_array($property_values)) //This is multiple values
				{
					if(array_key_exists("VALUE", $property_values)) //Or single "complex" value
					{
						$arProperties[$key][] = $property_values["VALUE"];
					}
					elseif(array_key_exists("tmp_name", $property_values)) //Or single file value
					{
						$arProperties[$key][] = $property_values;
					}
					else //true multiple
					{
						foreach($property_values as $key2 => $property_value)
						{
							if(is_array($property_value) && array_key_exists("VALUE", $property_value)) //each of these may be "complex"
								$arProperties[$key][] = $property_value["VALUE"];
							else //or simple
								$arProperties[$key][] = $property_value;
						}
					}
				}
				else //just one simple value
				{
					$arProperties[$key][] = $property_values;
				}
			}

			foreach($arProperties as $key => $property_values)
			{
				$arProperty = CIBlockProperty::GetPropertyArray($key, $IBLOCK_ID);

				if($arProperty["USER_TYPE"] != "")
					$arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
				else
					$arUserType = array();

				if (isset($arUserType['CheckFields']))
				{
					foreach ($property_values as $singleValue)
					{
						$arError = call_user_func_array(
							$arUserType['CheckFields'],
							[
								$arProperty,
								['VALUE' => $singleValue],
							]
						);
						if (is_array($arError))
						{
							foreach ($arError as $err_mess)
							{
								$this->LAST_ERROR .= $err_mess . '<br>';
							}
						}
					}
					unset($singleValue);
				}

				//Files check
				$bError = false;

				if(
					$arProperty["IS_REQUIRED"] == "Y"
					&& $arProperty['PROPERTY_TYPE'] == 'F'
				)
				{
					//New element
					if($ID===false)
					{
						$bError = true;
						foreach($property_values as $key2 => $property_value)
						{
							if(
								is_array($property_value)
								&& array_key_exists("tmp_name", $property_value)
								&& array_key_exists("size", $property_value)
							)
							{
								if($property_value['size'] > 0)
								{
									$bError = false;
									break;
								}
							}
							elseif(intval($property_value) > 0)
							{//This is history copy of the file
								$bError = false;
								break;
							}
						}
					}
					else
					{
						$dbProperty = CIBlockElement::GetProperty(
							$arProperty["IBLOCK_ID"],
							$ID,
							"sort", "asc",
							array(
								"ID" => $arProperty["ORIG_ID"],
								"EMPTY" => "N",
							)
						);

						$bCount = 0;
						while ($a=$dbProperty->Fetch())
						{
							if ($a["VALUE"] > 0)
								$bCount++;
						}

						foreach ($property_values as $key2 => $property_value)
						{
							if (is_array($property_value))
							{
								if ((int)($property_value['size'] ?? null) > 0)
								{
									$bCount++;
									break;
								}
								elseif (($property_value['del'] ?? null) === 'Y')
								{
									$bCount--;
								}
							}
							elseif ((int)$property_value > 0)
							{//This is history copy of the file
								$bCount++;
								break;
							}
						}
						$bError = $bCount <= 0;
					}
				}

				$correctValue = true;
				if (
					$arProperty["IS_REQUIRED"] == "Y"
					&& $arProperty['PROPERTY_TYPE'] != 'F'
				)
				{
					$len = 0;
					foreach ($property_values as $singleValue)
					{
						if (isset($arUserType['GetLength']))
						{
							$len += call_user_func_array(
								$arUserType['GetLength'],
								[
									$arProperty,
									['VALUE' => $singleValue],
								]
							);
						}
						else
						{
							if ($singleValue === null)
							{
								continue;
							}
							if (is_scalar($singleValue))
							{
								if ((string)$singleValue !== '')
								{
									$len++;
								}
							}
							else
							{
								$correctValue = false;
							}
						}
						if ($len > 0)
						{
							break;
						}
					}
					unset($singleValue);

					$bError = $len <= 0;
				}

				if ($bError)
				{
					$this->LAST_ERROR .=
						GetMessage(
							'IBLOCK_BAD_PROPERTY',
							array('#PROPERTY#' => $arProperty['NAME'])
						)
						. '<br>'
					;
				}
				if (!$correctValue)
				{
					$this->LAST_ERROR .=
						GetMessage(
							'IBLOCK_BAD_REQUIRED_PROPERTY_VALUE',
							array('#PROPERTY#' => $arProperty['NAME'])
						)
						. '<br>'
					;
				}

				// check file properties for correctness
				if($arProperty['PROPERTY_TYPE'] == 'F')
				{
					$bImageOnly = False;
					$arImageExtentions = explode(",", mb_strtoupper(CFile::GetImageExtensions()));
					if($arProperty["FILE_TYPE"] <> '')
					{
						$bImageOnly = True;
						$arAvailTypes = explode(",", mb_strtoupper($arProperty["FILE_TYPE"]));
						foreach($arAvailTypes as $avail_type)
						{
							if(!in_array(trim($avail_type), $arImageExtentions))
							{
								$bImageOnly = False;
								break;
							}
						}
					}

					foreach($property_values as $key2 => $property_value)
					{
						if(
							!is_array($property_value)
							&& (int)$property_value > 0
							&& isset($arFields['WF_PARENT_ELEMENT_ID'])
							&& (int)$arFields['WF_PARENT_ELEMENT_ID'] > 0
						)
						{
							if (CIBlockElement::DeleteFile($property_value, $ID, "PROPERTY",
									(int)$arFields["WF_PARENT_ELEMENT_ID"], $arFields["IBLOCK_ID"], true) <= 0)
							{
								$this->LAST_ERROR .= GetMessage("IBLOCK_ERR_FILE_PROPERTY")."<br>";
							}
						}
						elseif(is_array($property_value))
						{
							if (
								array_key_exists('bucket', $property_value)
								&& is_object($property_value["bucket"])
							)
							{
								//This is trusted image from xml import
								$error = "";
							}
							else
							{
								if($bImageOnly)
									$error = CFile::CheckImageFile($property_value);
								else
									$error = CFile::CheckFile($property_value, 0, false, $arProperty["FILE_TYPE"]);
							}

							//For user without edit php permissions
							//we allow only pictures upload
							if (!$this->isAdmin)
							{
								if(HasScriptExtension($property_value["name"]))
								{
									$error = GetMessage("FILE_BAD_TYPE")." (".$property_value["name"].").";
								}
							}

							if ($error <> '')
								$this->LAST_ERROR .= $error."<br>";
						}
					}
				}
			}
		}

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockElementAdd", true);
		else
		{
			$arFields["ID"] = $ID;
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockElementUpdate", true);
		}

		foreach($db_events as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		/****************************** QUOTA ******************************/
		if(
			$bCheckDiskQuota
			&& empty($this->LAST_ERROR)
			&& (COption::GetOptionInt("main", "disk_space") > 0)
		)
		{
			$quota = new CDiskQuota();
			if(!$quota->checkDiskQuota($arFields))
				$this->LAST_ERROR = $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/

		if(!empty($this->LAST_ERROR))
			return false;

		return true;
	}

	/**
	 * @param int $ELEMENT_ID
	 * @param string|int $PROPERTY_CODE
	 * @param mixed $PROPERTY_VALUE
	 * @return bool
	 */
	public static function SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $PROPERTY_VALUE)
	{
		$IBLOCK_ID = CIBlockElement::GetIBlockByID($ELEMENT_ID);
		if (!$IBLOCK_ID)
			return false;

		CIBlockElement::SetPropertyValues($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUE, $PROPERTY_CODE);

		return true;
	}

	public static function GetElementGroups($ID, $bElementOnly = false, $arSelect = array())
	{
		global $DB;

		$arFields = array(
			"ID" => "BS.ID",
			"TIMESTAMP_X" => "BS.TIMESTAMP_X",
			"MODIFIED_BY" => "BS.MODIFIED_BY",
			"DATE_CREATE" => "BS.DATE_CREATE",
			"CREATED_BY" => "BS.CREATED_BY",
			"IBLOCK_ID" => "BS.IBLOCK_ID",
			"IBLOCK_SECTION_ID" => "BS.IBLOCK_SECTION_ID",
			"ACTIVE" => "BS.ACTIVE",
			"GLOBAL_ACTIVE" => "BS.GLOBAL_ACTIVE",
			"SORT" => "BS.SORT",
			"NAME" => "BS.NAME",
			"PICTURE" => "BS.PICTURE",
			"LEFT_MARGIN" => "BS.LEFT_MARGIN",
			"RIGHT_MARGIN" => "BS.RIGHT_MARGIN",
			"DEPTH_LEVEL" => "BS.DEPTH_LEVEL",
			"DESCRIPTION" => "BS.DESCRIPTION",
			"DESCRIPTION_TYPE" => "BS.DESCRIPTION_TYPE",
			"SEARCHABLE_CONTENT" => "BS.SEARCHABLE_CONTENT",
			"CODE" => "BS.CODE",
			"XML_ID" => "BS.XML_ID",
			"EXTERNAL_ID" => "BS.XML_ID",
			"TMP_ID" => "BS.TMP_ID",
			"DETAIL_PICTURE" => "BS.DETAIL_PICTURE",
			"SOCNET_GROUP_ID" => "BS.SOCNET_GROUP_ID",

			"LIST_PAGE_URL" => "B.LIST_PAGE_URL",
			"SECTION_PAGE_URL" => "B.SECTION_PAGE_URL",
			"IBLOCK_TYPE_ID" => "B.IBLOCK_TYPE_ID",
			"IBLOCK_CODE" => "B.CODE",
			"IBLOCK_EXTERNAL_ID" => "B.XML_ID",

			"IBLOCK_ELEMENT_ID" => "SE.IBLOCK_ELEMENT_ID",
			"ADDITIONAL_PROPERTY_ID" => "SE.ADDITIONAL_PROPERTY_ID",
		);

		if(is_array($ID))
		{
			if (!empty($ID))
			{
				Main\Type\Collection::normalizeArrayValuesByInt($ID);
			}
			$sqlID = !empty($ID) ? $ID : array(0);
		}
		else
		{
			$sqlID = array((int)$ID);
		}

		$arSqlSelect = array();
		foreach($arSelect as &$field)
		{
			$field = mb_strtoupper($field);
			if(array_key_exists($field, $arFields))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}
		if (isset($field))
			unset($field);

		if (array_key_exists("DESCRIPTION", $arSqlSelect))
			$arSqlSelect["DESCRIPTION_TYPE"] = $arFields["DESCRIPTION_TYPE"]." AS DESCRIPTION_TYPE";

		if(array_key_exists("LIST_PAGE_URL", $arSqlSelect) || array_key_exists("SECTION_PAGE_URL", $arSqlSelect))
		{
			$arSqlSelect["ID"] = $arFields["ID"]." AS ID";
			$arSqlSelect["CODE"] = $arFields["CODE"]." AS CODE";
			$arSqlSelect["EXTERNAL_ID"] = $arFields["EXTERNAL_ID"]." AS EXTERNAL_ID";
			$arSqlSelect["IBLOCK_TYPE_ID"] = $arFields["IBLOCK_TYPE_ID"]." AS IBLOCK_TYPE_ID";
			$arSqlSelect["IBLOCK_ID"] = $arFields["IBLOCK_ID"]." AS IBLOCK_ID";
			$arSqlSelect["IBLOCK_CODE"] = $arFields["IBLOCK_CODE"]." AS IBLOCK_CODE";
			$arSqlSelect["IBLOCK_EXTERNAL_ID"] = $arFields["IBLOCK_EXTERNAL_ID"]." AS IBLOCK_EXTERNAL_ID";
			$arSqlSelect["GLOBAL_ACTIVE"] = $arFields["GLOBAL_ACTIVE"]." AS GLOBAL_ACTIVE";
		}

		if (!empty($arSelect))
		{
			$strSelect = implode(", ", $arSqlSelect);
		}
		else
		{
			$strSelect = "
				BS.*
				,B.LIST_PAGE_URL
				,B.SECTION_PAGE_URL
				,B.IBLOCK_TYPE_ID
				,B.CODE as IBLOCK_CODE
				,B.XML_ID as IBLOCK_EXTERNAL_ID
				,BS.XML_ID as EXTERNAL_ID
				,SE.IBLOCK_ELEMENT_ID
			";
		}

		$dbr = new CIBlockResult($DB->Query("
			SELECT
				".$strSelect."
			FROM
				b_iblock_section_element SE
				INNER JOIN b_iblock_section BS ON SE.IBLOCK_SECTION_ID = BS.ID
				INNER JOIN b_iblock B ON B.ID = BS.IBLOCK_ID
			WHERE
				SE.IBLOCK_ELEMENT_ID in (".implode(", ", $sqlID).")
				".($bElementOnly?"AND SE.ADDITIONAL_PROPERTY_ID IS NULL ":"")."
		"));
		return $dbr;
	}

	//////////////////////////////////////////////////////////////////////////
	//
	//////////////////////////////////////////////////////////////////////////
	public static function RecalcSections($ID, $sectionId = null)
	{
		global $DB;
		$ID = (int)$ID;

		$sectionId = (int)$sectionId;
		if ($sectionId > 0)
		{
			$res = $DB->Query("
				SELECT
					SE.IBLOCK_SECTION_ID as IBLOCK_SECTION_ID_NEW
					,E.IBLOCK_SECTION_ID
					,E.IN_SECTIONS
					,E.IBLOCK_ID
				FROM
					b_iblock_section_element SE
					INNER JOIN b_iblock_element E ON E.ID = SE.IBLOCK_ELEMENT_ID
				WHERE
					SE.IBLOCK_ELEMENT_ID = ".$ID."
					AND SE.IBLOCK_SECTION_ID = ".$sectionId."
					AND SE.ADDITIONAL_PROPERTY_ID IS NULL
			");
			$res = $res->Fetch();
			if ($res)
			{
				$oldInSections = $res["IN_SECTIONS"];
				$newInSections = "Y";
				$oldSectionId = (int)$res["IBLOCK_SECTION_ID"];
				$newSectionId = (int)$res["IBLOCK_SECTION_ID_NEW"];
			}
			else
			{
				//No such section linked to the element
				return;
			}
		}
		else
		{
			$res = $DB->Query("
				SELECT
					COUNT('x') as C
					,MIN(SE.IBLOCK_SECTION_ID) as MIN_IBLOCK_SECTION_ID
					,E.IBLOCK_SECTION_ID
					,E.IN_SECTIONS
					,E.IBLOCK_ID
				FROM
					b_iblock_section_element SE
					INNER JOIN b_iblock_element E ON E.ID = SE.IBLOCK_ELEMENT_ID
				WHERE
					SE.IBLOCK_ELEMENT_ID = ".$ID."
					AND SE.ADDITIONAL_PROPERTY_ID IS NULL
				GROUP BY
					E.IBLOCK_SECTION_ID
					,E.IN_SECTIONS
					,E.IBLOCK_ID
			");
			$res = $res->Fetch();
			if ($res)
			{
				$oldInSections = $res["IN_SECTIONS"];
				$newInSections = ($res["C"] > 0? "Y": "N");
				$oldSectionId = (int)$res["IBLOCK_SECTION_ID"];
				$newSectionId = (int)$res["MIN_IBLOCK_SECTION_ID"];

				$arIBlock = CIBlock::GetArrayByID($res["IBLOCK_ID"]);
				if (
					$arIBlock["FIELDS"]["IBLOCK_SECTION"]["DEFAULT_VALUE"]["KEEP_IBLOCK_SECTION_ID"] === "Y"
					&& $oldInSections === $newInSections
				)
				{
					$res2 = $DB->Query("
						SELECT
							SE.IBLOCK_SECTION_ID
						FROM
							b_iblock_section_element SE
						WHERE
							SE.IBLOCK_ELEMENT_ID = ".$ID."
							AND SE.IBLOCK_SECTION_ID = ".$oldSectionId."
							AND SE.ADDITIONAL_PROPERTY_ID IS NULL
					");
					$res2 = $res2->Fetch();
					if ($res2)
					{
						//We'll keep IBLOCK_SECTION_ID
						return;
					}
				}
			}
			else
			{
				//No such element
				$oldInSections = "";
				$newInSections = "N";
				$oldSectionId = 0;
				$newSectionId = 0;
			}
		}

		if (
			$oldInSections != $newInSections
			|| ($oldSectionId != $newSectionId)
		)
		{
			$DB->Query("
				UPDATE b_iblock_element SET
					IN_SECTIONS = '".$newInSections."',
					IBLOCK_SECTION_ID= ".($newSectionId > 0? $newSectionId: "NULL")."
				WHERE
					ID = ".$ID."
			");
		}
	}

	//////////////////////////////////////////////////////////////////////////
	//
	//////////////////////////////////////////////////////////////////////////
	public static function SetElementSection($ID, $arSections, $bNew = false, $bRightsIBlock = 0, $sectionId = null)
	{
		global $DB;
		$ID = intval($ID);

		$min_old_id = null;
		$min_new_id = null;

		$arToDelete = array();
		$arToInsert = array();
		if(is_array($arSections))
		{
			foreach($arSections as $section_id)
			{
				$section_id = intval($section_id);
				if($section_id > 0)
				{
					if(!isset($min_new_id) || $section_id < $min_new_id)
						$min_new_id = $section_id;

					$arToInsert[$section_id] = $section_id;
				}
			}
		}
		else
		{
			$section_id = intval($arSections);
			if($section_id > 0)
			{
				$arToInsert[$section_id] = $section_id;
				$min_new_id = $section_id;
			}
		}

		$originalInsert = $arToInsert;
		$additionalInsert = array();

		$arOldParents = array();
		$arNewParents = $arToInsert;
		$bParentsChanged = false;

		//Read database
		if(!$bNew)
		{
			$rs = $DB->Query("
				SELECT * FROM b_iblock_section_element
				WHERE IBLOCK_ELEMENT_ID = ".$ID."
				AND ADDITIONAL_PROPERTY_ID IS NULL
			");

			while($ar = $rs->Fetch())
			{
				$section_id = intval($ar["IBLOCK_SECTION_ID"]);
				$arOldParents[] = $section_id;

				if(!isset($min_old_id) || $section_id < $min_old_id)
					$min_old_id = $section_id;

				if(isset($arToInsert[$section_id]))
				{
					unset($arToInsert[$section_id]); //This already in DB
				}
				else
				{
					if (isset($originalInsert[$section_id]))
						$additionalInsert[$section_id] = $section_id;
					$arToDelete[] = $section_id;
				}
			}

			if(!empty($arToDelete))
			{
				$bParentsChanged = true;
				$DB->Query($s="
					DELETE FROM b_iblock_section_element
					WHERE IBLOCK_ELEMENT_ID = ".$ID."
					AND ADDITIONAL_PROPERTY_ID IS NULL
					AND IBLOCK_SECTION_ID in (".implode(", ", $arToDelete).")
				"); //And this should be deleted
			}

			if (!empty($additionalInsert))
			{
				foreach ($additionalInsert as $index)
					$arToInsert[$index] = $index;
			}
		}

		if(!empty($arToInsert))
		{
			$bParentsChanged = true;
			$DB->Query("
				INSERT INTO b_iblock_section_element(IBLOCK_SECTION_ID, IBLOCK_ELEMENT_ID)
				SELECT S.ID, E.ID
				FROM b_iblock_section S, b_iblock_element E
				WHERE S.IBLOCK_ID = E.IBLOCK_ID
				AND S.ID IN (".implode(", ", $arToInsert).")
				AND E.ID = ".$ID."
			");
		}

		if($bParentsChanged && $bRightsIBlock)
		{
			$obElementRights = new CIBlockElementRights($bRightsIBlock, $ID);
			if(empty($arOldParents))
				$arOldParents[] = 0;
			if(empty($arNewParents))
				$arNewParents[] = 0;

			$obElementRights->ChangeParents($arOldParents, $arNewParents);
		}

		if($sectionId !== null || ($min_old_id !== $min_new_id))
		{
			CIBlockElement::RecalcSections($ID, $sectionId);
		}

		return !empty($arToDelete) || !empty($arToInsert);
	}

	function __InitFile($old_id, &$arFields, $fname)
	{
		if($old_id>0
			&&
			(
				!is_set($arFields, $fname)
				||
				(
					$arFields[$fname]['name'] == ''
					&&
					$arFields[$fname]['del']!="Y"
				)
			)
			&&
			($p = CFile::MakeFileArray($old_id))
		)
		{
			if(is_set($arFields[$fname], 'description'))
				$p['description'] = $arFields[$fname]['description'];
			$p["OLD_VALUE"] = true;
			$arFields[$fname] = $p;
		}
	}

	static function __GetFileContent($FILE_ID)
	{
		static $max_file_size = null;

		$arFile = CFile::MakeFileArray($FILE_ID);
		if($arFile && $arFile["tmp_name"])
		{
			if(!isset($max_file_size))
				$max_file_size = COption::GetOptionInt("search", "max_file_size", 0)*1024;

			if($max_file_size > 0 && $arFile["size"] > $max_file_size)
				return "";

			$io = CBXVirtualIo::GetInstance();
			$file_abs_path = $io->GetLogicalName($arFile["tmp_name"]);

			$arrFile = false;
			foreach(GetModuleEvents("search", "OnSearchGetFileContent", true) as $arEvent)
			{
				if($arrFile = ExecuteModuleEventEx($arEvent, array($file_abs_path)))
					break;
			}

			return $arrFile;
		}

		return "";
	}

	public static function UpdateSearch($ID, $bOverWrite=false)
	{
		if(!Loader::includeModule("search"))
			return;

		global $DB;
		$ID = (int)$ID;

		static $strElementSql = false;
		if(!$strElementSql)
		{
			$strElementSql = "
				SELECT BE.ID, BE.NAME, BE.XML_ID as EXTERNAL_ID,
					BE.PREVIEW_TEXT_TYPE, BE.PREVIEW_TEXT, BE.CODE,
					BE.TAGS,
					BE.DETAIL_TEXT_TYPE, BE.DETAIL_TEXT, BE.IBLOCK_ID, B.IBLOCK_TYPE_ID,
					".$DB->DateToCharFunction("BE.TIMESTAMP_X")." as LAST_MODIFIED,
					".$DB->DateToCharFunction("BE.ACTIVE_FROM")." as DATE_FROM,
					".$DB->DateToCharFunction("BE.ACTIVE_TO")." as DATE_TO,
					BE.IBLOCK_SECTION_ID,
					B.CODE as IBLOCK_CODE, B.XML_ID as IBLOCK_EXTERNAL_ID, B.DETAIL_PAGE_URL,
					B.VERSION, B.RIGHTS_MODE, B.SOCNET_GROUP_ID
				FROM b_iblock_element BE, b_iblock B
				WHERE BE.IBLOCK_ID=B.ID
					AND B.ACTIVE='Y'
					AND BE.ACTIVE='Y'
					AND B.INDEX_ELEMENT='Y'
					".CIBlockElement::WF_GetSqlLimit("BE.", "N")."
					AND BE.ID=";
		}

		$dbrIBlockElement = $DB->Query($strElementSql.$ID);

		if($arIBlockElement = $dbrIBlockElement->Fetch())
		{
			$IBLOCK_ID = $arIBlockElement["IBLOCK_ID"];
			$DETAIL_URL =
				'=ID=' . urlencode($arIBlockElement['ID'])
				. '&EXTERNAL_ID=' . urlencode((string)$arIBlockElement['EXTERNAL_ID'])
				. '&IBLOCK_SECTION_ID=' . urlencode((string)$arIBlockElement['IBLOCK_SECTION_ID'])
				. '&IBLOCK_TYPE_ID=' . urlencode($arIBlockElement['IBLOCK_TYPE_ID'])
				. '&IBLOCK_ID=' . urlencode($arIBlockElement['IBLOCK_ID'])
				. '&IBLOCK_CODE=' . urlencode((string)$arIBlockElement['IBLOCK_CODE'])
				. '&IBLOCK_EXTERNAL_ID=' . urlencode((string)$arIBlockElement['IBLOCK_EXTERNAL_ID'])
				. '&CODE=' . urlencode((string)$arIBlockElement['CODE'])
			;

			static $arGroups = array();
			if(!isset($arGroups[$IBLOCK_ID]))
			{
				$arGroups[$IBLOCK_ID] = array();
				$strSql =
					"SELECT GROUP_ID ".
					"FROM b_iblock_group ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID." ".
					"	AND PERMISSION>='R' ".
					"ORDER BY GROUP_ID";

				$dbrIBlockGroup = $DB->Query($strSql);
				while($arIBlockGroup = $dbrIBlockGroup->Fetch())
				{
					$arGroups[$IBLOCK_ID][] = $arIBlockGroup["GROUP_ID"];
					if($arIBlockGroup["GROUP_ID"]==2) break;
				}
				unset($arIBlockGroup);
				unset($dbrIBlockGroup);
			}

			static $arSITE = array();
			if(!isset($arSITE[$IBLOCK_ID]))
			{
				$arSITE[$IBLOCK_ID] = array();
				$strSql =
					"SELECT SITE_ID ".
					"FROM b_iblock_site ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID;

				$dbrIBlockSite = $DB->Query($strSql);
				while($arIBlockSite = $dbrIBlockSite->Fetch())
					$arSITE[$IBLOCK_ID][] = $arIBlockSite["SITE_ID"];
				unset($arIBlockSite);
				unset($dbrIBlockSite);
			}

			$BODY =
				($arIBlockElement["PREVIEW_TEXT_TYPE"]=="html" ?
					CSearch::KillTags($arIBlockElement["PREVIEW_TEXT"]) :
					$arIBlockElement["PREVIEW_TEXT"]
				)."\r\n".
				($arIBlockElement["DETAIL_TEXT_TYPE"]=="html" ?
					CSearch::KillTags($arIBlockElement["DETAIL_TEXT"]) :
					$arIBlockElement["DETAIL_TEXT"]
				);

			static $arProperties = array();
			if(!isset($arProperties[$IBLOCK_ID]))
			{
				$arProperties[$IBLOCK_ID] = array();
				$rsProperties = CIBlockProperty::GetList(
					array("sort"=>"asc","id"=>"asc"),
					array(
						"IBLOCK_ID"=>$IBLOCK_ID,
						"ACTIVE"=>"Y",
						"SEARCHABLE"=>"Y",
						"CHECK_PERMISSIONS"=>"N",
					)
				);
				while($ar = $rsProperties->Fetch())
				{
					if($ar["USER_TYPE"] <> '')
					{
						$arUT = CIBlockProperty::GetUserType($ar["USER_TYPE"]);
						if(isset($arUT["GetSearchContent"]))
							$ar["GetSearchContent"] = $arUT["GetSearchContent"];
						elseif(isset($arUT["GetPublicViewHTML"]))
							$ar["GetSearchContent"] = $arUT["GetPublicViewHTML"];
					}
					$arProperties[$IBLOCK_ID][$ar["ID"]] = $ar;
				}
				unset($ar);
				unset($rsProperties);
			}

			//Read current property values from database
			$strProperties = "";
			if(!empty($arProperties[$IBLOCK_ID]))
			{
				if($arIBlockElement["VERSION"]==1)
				{
					$rs = $DB->Query("
						select *
						from b_iblock_element_property
						where IBLOCK_ELEMENT_ID=".$arIBlockElement["ID"]."
						AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProperties[$IBLOCK_ID])).")
					");
					while($ar=$rs->Fetch())
					{
						$strProperties .= "\r\n";
						$arProperty = $arProperties[$IBLOCK_ID][$ar["IBLOCK_PROPERTY_ID"]];
						if (isset($arProperty["GetSearchContent"]) && $arProperty["GetSearchContent"])
						{
							$strProperties .= CSearch::KillTags(
								call_user_func_array($arProperty["GetSearchContent"],
									array(
										$arProperty,
										array("VALUE" => $ar["VALUE"]),
										array(),
									)
								)
							);
						}
						elseif ($arProperty["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_LIST)
						{
							$arEnum = CIBlockPropertyEnum::GetByID($ar["VALUE"]);
							if($arEnum!==false)
								$strProperties .= $arEnum["VALUE"];
						}
						elseif ($arProperty["PROPERTY_TYPE"] === Iblock\PropertyTable::TYPE_FILE)
						{
							$arFile = CIBlockElement::__GetFileContent($ar["VALUE"]);
							if(is_array($arFile))
							{
								$strProperties .= $arFile["CONTENT"];
								$arIBlockElement["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
							}
						}
						else
						{
							$strProperties .= $ar["VALUE"];
						}
					}
				}
				else
				{
					$rs = $DB->Query("
						select *
						from b_iblock_element_prop_m".$IBLOCK_ID."
						where IBLOCK_ELEMENT_ID=".$arIBlockElement["ID"]."
						AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProperties[$IBLOCK_ID])).")
					");
					while($ar=$rs->Fetch())
					{
						$strProperties .= "\r\n";
						$arProperty = $arProperties[$IBLOCK_ID][$ar["IBLOCK_PROPERTY_ID"]];
						if($arProperty["GetSearchContent"])
						{
							$strProperties .= CSearch::KillTags(
								call_user_func_array($arProperty["GetSearchContent"],
									array(
										$arProperty,
										array("VALUE" => $ar["VALUE"]),
										array(),
									)
								)
							);
						}
						elseif($arProperty["PROPERTY_TYPE"]=='L')
						{
							$arEnum = CIBlockPropertyEnum::GetByID($ar["VALUE"]);
							if($arEnum!==false)
								$strProperties .= $arEnum["VALUE"];
						}
						elseif($arProperty["PROPERTY_TYPE"]=='F')
						{
							$arFile = CIBlockElement::__GetFileContent($ar["VALUE"]);
							if(is_array($arFile))
							{
								$strProperties .= $arFile["CONTENT"];
								$arIBlockElement["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
							}
						}
						else
						{
							$strProperties .= $ar["VALUE"];
						}
					}
					$rs = $DB->Query("
						select *
						from b_iblock_element_prop_s".$IBLOCK_ID."
						where IBLOCK_ELEMENT_ID=".$arIBlockElement["ID"]."
					");
					if($ar=$rs->Fetch())
					{
						foreach($arProperties[$IBLOCK_ID] as $property_id=>$property)
						{
							if( array_key_exists("PROPERTY_".$property_id, $ar)
								&& $property["MULTIPLE"]=="N"
								&& $ar["PROPERTY_".$property_id] <> '')
							{
								$strProperties .= "\r\n";
								if($property["GetSearchContent"])
								{
									$strProperties .= CSearch::KillTags(
										call_user_func_array($property["GetSearchContent"],
											array(
												$property,
												array("VALUE" => $ar["PROPERTY_".$property_id]),
												array(),
											)
										)
									);
								}
								elseif($property["PROPERTY_TYPE"]=='L')
								{
									$arEnum = CIBlockPropertyEnum::GetByID($ar["PROPERTY_".$property_id]);
									if($arEnum!==false)
										$strProperties .= $arEnum["VALUE"];
								}
								elseif($property["PROPERTY_TYPE"]=='F')
								{
									$arFile = CIBlockElement::__GetFileContent($ar["PROPERTY_".$property_id]);
									if(is_array($arFile))
									{
										$strProperties .= $arFile["CONTENT"];
										$arIBlockElement["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
									}
								}
								else
								{
									$strProperties .= $ar["PROPERTY_".$property_id];
								}
							}
						}
					}
				}
			}
			$BODY .= $strProperties;

			if($arIBlockElement["RIGHTS_MODE"] !== "E")
			{
				$arPermissions = $arGroups[$IBLOCK_ID];
			}
			else
			{
				$obElementRights = new CIBlockElementRights($IBLOCK_ID, $arIBlockElement["ID"]);
				$arPermissions = $obElementRights->GetGroups(array("element_read"));
			}

			$arFields = array(
				"LAST_MODIFIED" => ($arIBlockElement["DATE_FROM"] <> ''?$arIBlockElement["DATE_FROM"]:$arIBlockElement["LAST_MODIFIED"]),
				"DATE_FROM" => ($arIBlockElement["DATE_FROM"] <> ''? $arIBlockElement["DATE_FROM"] : false),
				"DATE_TO" => ($arIBlockElement["DATE_TO"] <> ''? $arIBlockElement["DATE_TO"] : false),
				"TITLE" => $arIBlockElement["NAME"],
				"PARAM1" => $arIBlockElement["IBLOCK_TYPE_ID"],
				"PARAM2" => $IBLOCK_ID,
				"SITE_ID" => $arSITE[$IBLOCK_ID],
				"PERMISSIONS" => $arPermissions,
				"URL" => $DETAIL_URL,
				"BODY" => $BODY,
				"TAGS" => $arIBlockElement["TAGS"],
			);

			if ($arIBlockElement["SOCNET_GROUP_ID"] > 0)
				$arFields["PARAMS"] = array(
					"socnet_group" => $arIBlockElement["SOCNET_GROUP_ID"],
				);

			CSearch::Index("iblock", $ID, $arFields, $bOverWrite);
		}
		else
		{
			CSearch::DeleteIndex("iblock", $ID);
		}
	}

	public static function GetPropertyValues($IBLOCK_ID, $arElementFilter, $extMode = false, $propertyFilter = array())
	{
		global $DB;
		$IBLOCK_ID = (int)$IBLOCK_ID;
		$VERSION = CIBlockElement::GetIBVersion($IBLOCK_ID);

		$propertyID = array();
		if (isset($propertyFilter['ID']))
		{
			$propertyID = (is_array($propertyFilter['ID']) ? $propertyFilter['ID'] : array($propertyFilter['ID']));
			Main\Type\Collection::normalizeArrayValuesByInt($propertyID);
		}

		$arElementFilter["IBLOCK_ID"] = $IBLOCK_ID;

		$element = new CIBlockElement;
		$element->strField = "ID";
		$element->prepareSql(array("ID"), $arElementFilter, false, false);

		if ($VERSION == 2)
			$strSql = "
				SELECT
					BEP.*
				FROM
					".$element->sFrom."
					INNER JOIN b_iblock_element_prop_s".$IBLOCK_ID." BEP ON BEP.IBLOCK_ELEMENT_ID = BE.ID
				WHERE 1=1 ".$element->sWhere."
				ORDER BY
					BEP.IBLOCK_ELEMENT_ID
			";
		else
			$strSql = "
				SELECT
					BE.ID IBLOCK_ELEMENT_ID
					,BEP.IBLOCK_PROPERTY_ID
					,BEP.VALUE
					,BEP.VALUE_NUM
					".($extMode ?
						",BEP.ID PROPERTY_VALUE_ID
						,BEP.DESCRIPTION
						" :
						""
					)."
				FROM
					".$element->sFrom."
					LEFT JOIN b_iblock_element_property BEP ON BEP.IBLOCK_ELEMENT_ID = BE.ID ".
						(!empty($propertyID) ? "AND BEP.IBLOCK_PROPERTY_ID IN (" . implode(', ', $propertyID) . ")" : "") .
				"WHERE 1=1 " . $element->sWhere . "
				ORDER BY
					BEP.IBLOCK_ELEMENT_ID, BEP.IBLOCK_PROPERTY_ID, BEP.ID
			";
		$rs = new CIBlockPropertyResult($DB->Query($strSql));
		$rs->setIBlock($IBLOCK_ID, $propertyID);
		$rs->setMode($extMode);

		return $rs;
	}

	public static function GetPropertyValuesArray(&$result, $iblockID, $filter, $propertyFilter = array(), $options = array())
	{
		$iblockExtVersion = (CIBlockElement::GetIBVersion($iblockID) == 2);
		$propertiesList = array();
		$shortProperties = array();
		$userTypesList = array();

		$selectListMultiply = array('SORT' => SORT_ASC, 'VALUE' => SORT_STRING);
		$selectAllMultiply = array('PROPERTY_VALUE_ID' => SORT_ASC);

		$selectFields = array(
			'ID', 'IBLOCK_ID', 'NAME', 'ACTIVE', 'SORT', 'CODE', 'DEFAULT_VALUE', 'PROPERTY_TYPE', 'ROW_COUNT', 'COL_COUNT', 'LIST_TYPE',
			'MULTIPLE', 'XML_ID', 'FILE_TYPE', 'MULTIPLE_CNT', 'LINK_IBLOCK_ID', 'WITH_DESCRIPTION', 'SEARCHABLE', 'FILTRABLE',
			'IS_REQUIRED', 'VERSION', 'USER_TYPE', 'USER_TYPE_SETTINGS', 'HINT'
		);

		if (!is_array($options))
			$options = array();
		$usePropertyId = (isset($options['USE_PROPERTY_ID']) && $options['USE_PROPERTY_ID'] == 'Y');
		$getRawData = (isset($options['GET_RAW_DATA']) && $options['GET_RAW_DATA'] == 'Y');
		$propertyFieldList = array();
		if (!empty($options['PROPERTY_FIELDS']) && is_array($options['PROPERTY_FIELDS']))
			$propertyFieldList = array_intersect($options['PROPERTY_FIELDS'], $selectFields);
		if (!empty($propertyFieldList))
		{
			if (!in_array('ID', $propertyFieldList))
				$propertyFieldList[] = 'ID';
			if (!$getRawData)
			{
				if (in_array('NAME', $propertyFieldList))
					$propertyFieldList[] = '~NAME';
				if (in_array('DEFAULT_VALUE', $propertyFieldList))
					$propertyFieldList[] = '~DEFAULT_VALUE';
			}
			$propertyFieldList = array_fill_keys($propertyFieldList, true);
		}

		$propertyListFilter = array(
			'IBLOCK_ID' => $iblockID
		);
		$propertyID = array();
		if (isset($propertyFilter['ID']))
		{
			$propertyID = (is_array($propertyFilter['ID']) ? $propertyFilter['ID'] : array($propertyFilter['ID']));
			Main\Type\Collection::normalizeArrayValuesByInt($propertyID, true);
		}
		if (!empty($propertyID))
		{
			$propertyListFilter['@ID'] = $propertyID;
		}
		elseif (isset($propertyFilter['CODE']))
		{
			if (!is_array($propertyFilter['CODE']))
				$propertyFilter['CODE'] = array($propertyFilter['CODE']);
			$propertyCodes = array();
			if (!empty($propertyFilter['CODE']))
			{
				foreach ($propertyFilter['CODE'] as &$code)
				{
					$code = (string)$code;
					if ($code !== '')
						$propertyCodes[] = $code;
				}
				unset($code);
			}
			if (!empty($propertyCodes))
				$propertyListFilter['@CODE'] = $propertyCodes;
			unset($propertyCodes);
		}
		$propertyListFilter['=ACTIVE'] = (
			isset($propertyFilter['ACTIVE']) && ($propertyFilter['ACTIVE'] == 'Y' || $propertyFilter['ACTIVE'] == 'N')
			? $propertyFilter['ACTIVE']
			: 'Y'
		);

		$propertyID = array();
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => $selectFields,
			'filter' => $propertyListFilter,
			'order' => array('SORT'=>'ASC', 'ID'=>'ASC'),
			'cache' => array(
				'ttl' => 86400,
			),
		));
		while ($property = $propertyIterator->fetch())
		{
			$propertyID[] = (int)$property['ID'];
			$property['CODE'] = trim((string)$property['CODE']);
			if ($property['CODE'] === '')
				$property['CODE'] = $property['ID'];
			$code = ($usePropertyId ? $property['ID'] : $property['CODE']);
			if (!$getRawData)
			{
				$property['~NAME'] = $property['NAME'];
				if (preg_match("/[;&<>\"]/", $property['NAME']))
					$property['NAME'] = htmlspecialcharsEx($property['NAME']);
			}

			if ($property['USER_TYPE'])
			{
				$userType = CIBlockProperty::GetUserType($property['USER_TYPE']);
				if (isset($userType['ConvertFromDB']))
				{
					$userTypesList[$property['ID']] = $userType;
					if (array_key_exists('DEFAULT_VALUE', $property))
					{
						$value = [
							'VALUE' => $property['DEFAULT_VALUE'],
							'DESCRIPTION' => '',
						];
						$value = call_user_func_array(
							$userType['ConvertFromDB'],
							[$property, $value]
						);
						$property['DEFAULT_VALUE'] = $value['VALUE'] ?? '';
					}
				}
			}

			if (!empty($property['USER_TYPE_SETTINGS']))
			{
				$property['USER_TYPE_SETTINGS'] = unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
			}

			if (!$getRawData)
			{
				$property['~DEFAULT_VALUE'] = $property['DEFAULT_VALUE'] ?? null;
				if (
					isset($property['DEFAULT_VALUE'])
					&& (
						is_array($property['DEFAULT_VALUE'])
						|| preg_match("/[;&<>\"]/", $property['DEFAULT_VALUE'])
					)
				)
				{
					$property['DEFAULT_VALUE'] = htmlspecialcharsEx($property['DEFAULT_VALUE']);
				}
			}

			$propertiesList[$code] = $property;
			$shortProperties[$code] = (!empty($propertyFieldList)
				? array_intersect_key($property, $propertyFieldList)
				: $property
			);
		}
		unset($property, $propertyIterator);

		if (empty($propertiesList))
			return;

		$enumList = array();

		$emptyResult = empty($result);

		$valuesRes = (
			!empty($propertyID)
			? CIBlockElement::GetPropertyValues($iblockID, $filter, true, array('ID' => $propertyID))
			: CIBlockElement::GetPropertyValues($iblockID, $filter, true)
		);
		while ($value = $valuesRes->Fetch())
		{
			$elementID = (int)$value['IBLOCK_ELEMENT_ID'];
			if (!isset($result[$elementID]))
			{
				if ($emptyResult)
				{
					$result[$elementID] = [];
				}
				else
				{
					continue;
				}
			}
			$elementValues = array();
			$existDescription = isset($value['DESCRIPTION']);
			foreach ($propertiesList as $code => $property)
			{
				$existElementDescription = isset($value['DESCRIPTION']) && array_key_exists($property['ID'], $value['DESCRIPTION']);
				$existElementPropertyID = isset($value['PROPERTY_VALUE_ID']) && array_key_exists($property['ID'], $value['PROPERTY_VALUE_ID']);
				$elementValues[$code] = $shortProperties[$code];

				$elementValues[$code]['VALUE_ENUM'] = null;
				$elementValues[$code]['VALUE_XML_ID'] = null;
				$elementValues[$code]['VALUE_SORT'] = null;
				$elementValues[$code]['VALUE'] = null;

				if ('Y' === $property['MULTIPLE'])
				{
					$elementValues[$code]['PROPERTY_VALUE_ID'] = false;
					if (!isset($value[$property['ID']]) || empty($value[$property['ID']]))
					{
						$elementValues[$code]['DESCRIPTION'] = false;
						$elementValues[$code]['VALUE'] = false;
						if (!$getRawData)
						{
							$elementValues[$code]['~DESCRIPTION'] = false;
							$elementValues[$code]['~VALUE'] = false;
						}
						if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
						{
							$elementValues[$code]['VALUE_ENUM_ID'] = false;
							$elementValues[$code]['VALUE_ENUM'] = false;
							$elementValues[$code]['VALUE_XML_ID'] = false;
							$elementValues[$code]['VALUE_SORT'] = false;
						}
					}
					else
					{
						if ($existElementPropertyID)
						{
							$elementValues[$code]['PROPERTY_VALUE_ID'] = $value['PROPERTY_VALUE_ID'][$property['ID']];
						}
						if (isset($userTypesList[$property['ID']]))
						{
							foreach ($value[$property['ID']] as $valueKey => $oneValue)
							{
								$raw = call_user_func_array(
									$userTypesList[$property['ID']]['ConvertFromDB'],
									array(
										$property,
										array(
											'VALUE' => $oneValue,
											'DESCRIPTION' => ($existElementDescription ? $value['DESCRIPTION'][$property['ID']][$valueKey] : null),
										)
									)
								);
								$value[$property['ID']][$valueKey] = $raw['VALUE'] ?? null;
								if (!$existDescription)
								{
									$value['DESCRIPTION'] = array();
									$existDescription = true;
								}
								if (!$existElementDescription)
								{
									$value['DESCRIPTION'][$property['ID']] = array();
									$existElementDescription = true;
								}
								$value['DESCRIPTION'][$property['ID']][$valueKey] = (string)$raw['DESCRIPTION'];
							}
							if (isset($oneValue))
								unset($oneValue);
						}
						if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
						{
							if (empty($value[$property['ID']]))
							{
								$elementValues[$code]['VALUE_ENUM_ID'] = $value[$property['ID']];
								$elementValues[$code]['DESCRIPTION'] = ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : array());
							}
							else
							{
								$selectedValues = array();
								foreach ($value[$property['ID']] as $listKey => $listValue)
								{
									if (!isset($enumList[$property['ID']][$listValue]))
									{
										if (!isset($enumList[$property['ID']]))
											$enumList[$property['ID']] = [];
										$enumList[$property['ID']][$listValue] = false;
										$enumIterator = Iblock\PropertyEnumerationTable::getList(array(
											'select' => array('ID', 'VALUE', 'SORT', 'XML_ID'),
											'filter' => array('=ID' => $listValue, '=PROPERTY_ID' => $property['ID'])
										));
										$row = $enumIterator->fetch();
										unset($enumIterator);
										if (!empty($row))
										{
											$enumList[$property['ID']][$listValue] = array(
												'ID' => $row['ID'],
												'VALUE' => $row['VALUE'],
												'SORT' => $row['SORT'],
												'XML_ID' => $row['XML_ID']
											);
										}
										unset($row);
									}
									if (!empty($enumList[$property['ID']][$listValue]))
									{
										$selectedValues[$listKey] = $enumList[$property['ID']][$listValue];
										$selectedValues[$listKey]['DESCRIPTION'] = (
											$existElementDescription && array_key_exists($listKey, $value['DESCRIPTION'][$property['ID']])
												? $value['DESCRIPTION'][$property['ID']][$listKey]
												: null
										);
										$selectedValues[$listKey]['PROPERTY_VALUE_ID'] = (
											$existElementPropertyID && array_key_exists($listKey, $value['PROPERTY_VALUE_ID'][$property['ID']])
												? $value['PROPERTY_VALUE_ID'][$property['ID']][$listKey]
												: null
										);
									}
								}
								if (empty($selectedValues))
								{
									$elementValues[$code]['VALUE_ENUM_ID'] = $value[$property['ID']];
									$elementValues[$code]['DESCRIPTION'] = ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : array());
								}
								else
								{
									Main\Type\Collection::sortByColumn($selectedValues, $selectListMultiply);
									$elementValues[$code]['VALUE_SORT'] = array();
									$elementValues[$code]['VALUE_ENUM_ID'] = array();
									$elementValues[$code]['VALUE'] = array();
									$elementValues[$code]['VALUE_ENUM'] = array();
									$elementValues[$code]['VALUE_XML_ID'] = array();
									$elementValues[$code]['DESCRIPTION'] = array();
									$elementValues[$code]['PROPERTY_VALUE_ID'] = array();
									foreach ($selectedValues as $listValue)
									{
										$elementValues[$code]['VALUE_SORT'][] = $listValue['SORT'];
										$elementValues[$code]['VALUE_ENUM_ID'][] = $listValue['ID'];
										$elementValues[$code]['VALUE'][] = $listValue['VALUE'];
										$elementValues[$code]['VALUE_ENUM'][] = $listValue['VALUE'];
										$elementValues[$code]['VALUE_XML_ID'][] = $listValue['XML_ID'];
										$elementValues[$code]['PROPERTY_VALUE_ID'][] = $listValue['PROPERTY_VALUE_ID'];
										$elementValues[$code]['DESCRIPTION'][] = $listValue['DESCRIPTION'];
									}
									unset($selectedValues);
								}
							}
						}
						else
						{
							if (empty($value[$property['ID']]) || !$existElementPropertyID || isset($userTypesList[$property['ID']]))
							{
								$elementValues[$code]['VALUE'] = $value[$property['ID']];
								$elementValues[$code]['DESCRIPTION'] = ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : array());
							}
							else
							{
								$selectedValues = array();
								foreach ($value['PROPERTY_VALUE_ID'][$property['ID']] as $propKey => $propValueID)
								{
									$selectedValues[$propKey] = array(
										'PROPERTY_VALUE_ID' => $propValueID,
										'VALUE' => $value[$property['ID']][$propKey],
									);
									if ($existElementDescription)
									{
										$selectedValues[$propKey]['DESCRIPTION'] = $value['DESCRIPTION'][$property['ID']][$propKey];
									}
								}
								unset($propValueID, $propKey);

								Main\Type\Collection::sortByColumn($selectedValues, $selectAllMultiply);
								$elementValues[$code]['PROPERTY_VALUE_ID'] = array();
								$elementValues[$code]['VALUE'] = array();
								$elementValues[$code]['DESCRIPTION'] = array();
								foreach ($selectedValues as &$propValue)
								{
									$elementValues[$code]['PROPERTY_VALUE_ID'][] = $propValue['PROPERTY_VALUE_ID'];
									$elementValues[$code]['VALUE'][] = $propValue['VALUE'];
									if ($existElementDescription)
									{
										$elementValues[$code]['DESCRIPTION'][] = $propValue['DESCRIPTION'];
									}
								}
								unset($propValue, $selectedValues);
							}
						}
					}

					if (!$getRawData)
					{
						$elementValues[$code]['~VALUE'] = $elementValues[$code]['VALUE'];
						if (is_array($elementValues[$code]['VALUE']))
						{
							foreach ($elementValues[$code]['VALUE'] as &$oneValue)
							{
								$isArr = is_array($oneValue);
								if ($isArr || ('' !== $oneValue && null !== $oneValue))
								{
									if ($isArr || preg_match("/[;&<>\"]/", $oneValue))
									{
										$oneValue = htmlspecialcharsEx($oneValue);
									}
								}
							}
							if (isset($oneValue))
								unset($oneValue);
						}
						else
						{
							if ('' !== $elementValues[$code]['VALUE'] && null !== $elementValues[$code]['VALUE'])
							{
								if (preg_match("/[;&<>\"]/", $elementValues[$code]['VALUE']))
								{
									$elementValues[$code]['VALUE'] = htmlspecialcharsEx($elementValues[$code]['VALUE']);
								}
							}
						}

						$elementValues[$code]['~DESCRIPTION'] = $elementValues[$code]['DESCRIPTION'];
						if (is_array($elementValues[$code]['DESCRIPTION']))
						{
							foreach ($elementValues[$code]['DESCRIPTION'] as &$oneDescr)
							{
								$isArr = is_array($oneDescr);
								if ($isArr || (!$isArr && '' !== $oneDescr && null !== $oneDescr))
								{
									if ($isArr || preg_match("/[;&<>\"]/", $oneDescr))
									{
										$oneDescr = htmlspecialcharsEx($oneDescr);
									}
								}
							}
							if (isset($oneDescr))
								unset($oneDescr);
						}
						else
						{
							if ('' !== $elementValues[$code]['DESCRIPTION'] && null !== $elementValues[$code]['DESCRIPTION'])
							{
								if (preg_match("/[;&<>\"]/", $elementValues[$code]['DESCRIPTION']))
								{
									$elementValues[$code]['DESCRIPTION'] = htmlspecialcharsEx($elementValues[$code]['DESCRIPTION']);
								}
							}
						}
					}
				}
				else
				{
					$elementValues[$code]['VALUE_ENUM'] = ($iblockExtVersion ? '' : null);
					$elementValues[$code]['PROPERTY_VALUE_ID'] = ($iblockExtVersion ? $elementID.':'.$property['ID'] : null);

					if (!isset($value[$property['ID']]) || false === $value[$property['ID']])
					{
						$elementValues[$code]['DESCRIPTION'] = '';
						$elementValues[$code]['VALUE'] = '';
						if (!$getRawData)
						{
							$elementValues[$code]['~DESCRIPTION'] = '';
							$elementValues[$code]['~VALUE'] = '';
						}
						if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
						{
							$elementValues[$code]['VALUE_ENUM_ID'] = null;
						}
					}
					else
					{
						if ($existElementPropertyID)
						{
							$elementValues[$code]['PROPERTY_VALUE_ID'] = $value['PROPERTY_VALUE_ID'][$property['ID']];
						}
						if (isset($userTypesList[$property['ID']]))
						{
							$raw = call_user_func_array(
								$userTypesList[$property['ID']]['ConvertFromDB'],
								array(
									$property,
									array(
										'VALUE' => $value[$property['ID']],
										'DESCRIPTION' => ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : null)
									)
								)
							);
							$value[$property['ID']] = $raw['VALUE'] ?? null;
							if (!$existDescription)
							{
								$value['DESCRIPTION'] = array();
								$existDescription = true;
							}
							$value['DESCRIPTION'][$property['ID']] = (string)($raw['DESCRIPTION'] ?? null);
							$existElementDescription = true;
						}
						if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
						{
							$elementValues[$code]['VALUE_ENUM_ID'] = $value[$property['ID']];
							if (!isset($enumList[$property['ID']][$value[$property['ID']]]))
							{
								if (!isset($enumList[$property['ID']]))
									$enumList[$property['ID']] = [];
								$enumList[$property['ID']][$value[$property['ID']]] = false;
								$enumIterator = Iblock\PropertyEnumerationTable::getList(array(
									'select' => array('ID', 'VALUE', 'SORT', 'XML_ID'),
									'filter' => array('=ID' => $value[$property['ID']], '=PROPERTY_ID' => $property['ID'])
								));
								$row = $enumIterator->fetch();
								unset($enumIterator);
								if (!empty($row))
								{
									$enumList[$property['ID']][$value[$property['ID']]] = array(
										'ID' => $row['ID'],
										'VALUE' => $row['VALUE'],
										'SORT' => $row['SORT'],
										'XML_ID' => $row['XML_ID']
									);
								}
								unset($row);
							}
							if (!empty($enumList[$property['ID']][$value[$property['ID']]]))
							{
								$elementValues[$code]['VALUE'] = $enumList[$property['ID']][$value[$property['ID']]]['VALUE'];
								$elementValues[$code]['VALUE_ENUM'] = $elementValues[$code]['VALUE'];
								$elementValues[$code]['VALUE_XML_ID'] = $enumList[$property['ID']][$value[$property['ID']]]['XML_ID'];
								$elementValues[$code]['VALUE_SORT'] = $enumList[$property['ID']][$value[$property['ID']]]['SORT'];
							}
							$elementValues[$code]['DESCRIPTION'] = ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : null);
						}
						else
						{
							$elementValues[$code]['VALUE'] = $value[$property['ID']];
							$elementValues[$code]['DESCRIPTION'] = ($existElementDescription ? $value['DESCRIPTION'][$property['ID']] : null);
						}
					}

					if (!$getRawData)
					{
						$elementValues[$code]['~VALUE'] = $elementValues[$code]['VALUE'];
						$isArr = is_array($elementValues[$code]['VALUE']);
						if ($isArr || ('' !== $elementValues[$code]['VALUE'] && null !== $elementValues[$code]['VALUE']))
						{
							if ($isArr || preg_match("/[;&<>\"]/", $elementValues[$code]['VALUE']))
							{
								$elementValues[$code]['VALUE'] = htmlspecialcharsEx($elementValues[$code]['VALUE']);
							}
						}

						$elementValues[$code]['~DESCRIPTION'] = $elementValues[$code]['DESCRIPTION'];
						$isArr = is_array($elementValues[$code]['DESCRIPTION']);
						if ($isArr || ('' !== $elementValues[$code]['DESCRIPTION'] && null !== $elementValues[$code]['DESCRIPTION']))
						{
							if ($isArr || preg_match("/[;&<>\"]/", $elementValues[$code]['DESCRIPTION']))
								$elementValues[$code]['DESCRIPTION'] = htmlspecialcharsEx($elementValues[$code]['DESCRIPTION']);
						}
					}
				}
			}
			if (isset($result[$elementID]['PROPERTIES']))
			{
				$result[$elementID]['PROPERTIES'] = $elementValues;
			}
			else
			{
				$result[$elementID] = $elementValues;
			}
			unset($elementValues);
		}
	}

	public static function GetProperty($IBLOCK_ID, $ELEMENT_ID, $by="sort", $order="asc", $arFilter = Array())
	{
		global $DB;
		if(is_array($by))
		{
			if($order!="asc")
				$arFilter = $order;
			$arOrder = $by;
		}
		else
		{
			$arOrder = false;
		}

		$IBLOCK_ID = (int)$IBLOCK_ID;
		$ELEMENT_ID = (int)$ELEMENT_ID;
		$VERSION = CIBlockElement::GetIBVersion($IBLOCK_ID);

		$strSqlSearch = "";
		foreach($arFilter as $key=>$val)
		{
			switch(mb_strtoupper($key))
			{
				case "ACTIVE":
					if($val == "Y" || $val == "N")
					{
						$strSqlSearch .= "AND BP.ACTIVE='".$val."'\n";
					}
					break;
				case "SEARCHABLE":
					if($val == "Y" || $val == "N")
					{
						$strSqlSearch .= "AND BP.SEARCHABLE='".$val."'\n";
					}
					break;
				case "NAME":
					if($val <> '')
					{
						$strSqlSearch .= "AND ".CIBlock::_Upper("BP.NAME")." LIKE ".CIBlock::_Upper("'".$DB->ForSql($val)."'")."\n";
					}
					break;
				case "ID":
					if(is_array($val))
					{
						if(!empty($val))
						{
							$strSqlSearch .= "AND BP.ID in (".implode(", ", array_map("intval", $val)).")\n";
						}
					}
					elseif($val <> '')
					{
						$strSqlSearch .= "AND BP.ID=".intval($val)."\n";
					}
					break;
				case "PROPERTY_TYPE":
					if($val <> '')
					{
						$strSqlSearch .= "AND BP.PROPERTY_TYPE='".$DB->ForSql($val)."'\n";
					}
					break;
				case "CODE":
					if($val <> '')
					{
						$strSqlSearch .= "AND ".CIBlock::_Upper("BP.CODE")." LIKE ".CIBlock::_Upper("'".$DB->ForSql($val)."'")."\n";
					}
					break;
				case "EMPTY":
					if($val <> '')
					{
						if($val == "Y")
						{
							$strSqlSearch .= "AND BEP.ID IS NULL\n";
						}
						elseif($VERSION != 2)
						{
							$strSqlSearch .= "AND BEP.ID IS NOT NULL\n";
						}
					}
					break;
			}
		}

		$arSqlOrder = array();
		if($arOrder)
		{
			foreach($arOrder as $by=>$order)
			{
				$order = mb_strtolower($order);
				if($order!="desc")
					$order = "asc";

				$by = mb_strtolower($by);
				if($by == "sort")		$arSqlOrder["BP.SORT"]=$order;
				elseif($by == "id")		$arSqlOrder["BP.ID"]=$order;
				elseif($by == "name")		$arSqlOrder["BP.NAME"]=$order;
				elseif($by == "active")		$arSqlOrder["BP.ACTIVE"]=$order;
				elseif($by == "value_id")	$arSqlOrder["BEP.ID"]=$order;
				elseif($by == "enum_sort")	$arSqlOrder["BEPE.SORT"]=$order;
				else
					$arSqlOrder["BP.SORT"]=$order;
			}
		}
		else
		{
			if($by == "id")			$arSqlOrder["BP.ID"]="asc";
			elseif($by == "name")		$arSqlOrder["BP.NAME"]="asc";
			elseif($by == "active")		$arSqlOrder["BP.ACTIVE"]="asc";
			elseif($by == "value_id")	$arSqlOrder["BEP.ID"]=$order;
			elseif($by == "enum_sort")	$arSqlOrder["BEPE.SORT"]=$order;
			else
			{
				$arSqlOrder["BP.SORT"]="asc";
				$by = "sort";
			}

			if ($order!="desc")
			{
				$arSqlOrder["BP.SORT"]="asc";
				$arSqlOrder["BP.ID"]="asc";
				$arSqlOrder["BEPE.SORT"]="asc";
				$arSqlOrder["BEP.ID"]="asc";
				$order = "asc";
			}
			else
			{
				$arSqlOrder["BP.SORT"]="desc";
				$arSqlOrder["BP.ID"]="desc";
				$arSqlOrder["BEPE.SORT"]="desc";
				$arSqlOrder["BEP.ID"]="desc";
			}
		}

		$strSqlOrder = "";
		foreach($arSqlOrder as $key=>$val)
			$strSqlOrder.=", ".$key." ".$val;

		if($strSqlOrder!="")
			$strSqlOrder = ' ORDER BY '.mb_substr($strSqlOrder, 1);

		if($VERSION==2)
			$strTable = "b_iblock_element_prop_m".$IBLOCK_ID;
		else
			$strTable = "b_iblock_element_property";

		$strSql = "
			SELECT BP.*, BEP.ID as PROPERTY_VALUE_ID, BEP.VALUE, BEP.DESCRIPTION, BEPE.VALUE VALUE_ENUM, BEPE.XML_ID VALUE_XML_ID, BEPE.SORT VALUE_SORT
			FROM b_iblock B
				INNER JOIN b_iblock_property BP ON B.ID=BP.IBLOCK_ID
				LEFT JOIN ".$strTable." BEP ON (BP.ID = BEP.IBLOCK_PROPERTY_ID AND BEP.IBLOCK_ELEMENT_ID = ".$ELEMENT_ID.")
				LEFT JOIN b_iblock_property_enum BEPE ON (BP.PROPERTY_TYPE = 'L' AND BEPE.ID=BEP.VALUE_ENUM AND BEPE.PROPERTY_ID=BP.ID)
			WHERE B.ID = ".$IBLOCK_ID."
				".$strSqlSearch."
			".$strSqlOrder;

		if($VERSION==2)
		{
			$emptyFilter = !isset($arFilter['EMPTY']) || $arFilter['EMPTY'] === 'Y';
			$notEmptyFilter = isset($arFilter['EMPTY']) && $arFilter['EMPTY'] === 'N';
			$result = array();
			$arElements = array();
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				if($ar["VERSION"]==2 && $ar["MULTIPLE"]=="N")
				{
					if (!isset($arElements[$ELEMENT_ID]))
					{
						$strSql = "
							SELECT *
							FROM b_iblock_element_prop_s".$ar["IBLOCK_ID"]."
							WHERE IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
						";
						$rs2 = $DB->Query($strSql);
						$arElements[$ELEMENT_ID] = $rs2->Fetch();
						unset($rs2);
					}
					$notEmptyValue =
						is_array($arElements[$ELEMENT_ID])
						&& isset($arElements[$ELEMENT_ID]["PROPERTY_".$ar["ID"]])
						&& $arElements[$ELEMENT_ID]["PROPERTY_".$ar["ID"]] !== ''
					;
					if ($emptyFilter || $notEmptyValue)
					{
						$val = $arElements[$ELEMENT_ID]["PROPERTY_".$ar["ID"]] ?? null;
						$ar["PROPERTY_VALUE_ID"] = $ELEMENT_ID . ":" . $ar["ID"];
						if ($ar["PROPERTY_TYPE"]=="L" && (int)$val > 0)
						{
							$arEnum = CIBlockPropertyEnum::GetByID($val);
							if($arEnum!==false)
							{
								$ar["VALUE_ENUM"] = $arEnum["VALUE"];
								$ar["VALUE_XML_ID"] = $arEnum["XML_ID"];
								$ar["VALUE_SORT"] = $arEnum["SORT"];
							}
						}
						else
						{
							$ar["VALUE_ENUM"] = "";
						}
						if($ar["PROPERTY_TYPE"]=="N" && $val <> '')
						{
							$val = CIBlock::NumberFormat($val);
						}
						$ar["DESCRIPTION"] = $arElements[$ELEMENT_ID]["DESCRIPTION_".$ar["ID"]] ?? null;
						$ar["VALUE"] = $val;
					}
					else
					{
						continue;
					}
				}
				if ($notEmptyFilter && $ar["PROPERTY_VALUE_ID"] == "")
					continue;
				$result[]=$ar;
			}
			$rs = new CIBlockPropertyResult;
			$rs->InitFromArray($result);
		}
		else
		{
			$rs = new CIBlockPropertyResult($DB->Query($strSql));
		}
		return $rs;
	}

	public static function CounterInc($ID)
	{
		global $DB;
		$ID = (int)$ID;
		if ($ID <= 0)
			return;
		if(!isset($_SESSION["IBLOCK_COUNTER"]) || !is_array($_SESSION["IBLOCK_COUNTER"]))
			$_SESSION["IBLOCK_COUNTER"] = array();
		if(in_array($ID, $_SESSION["IBLOCK_COUNTER"]))
			return;
		$_SESSION["IBLOCK_COUNTER"][] = $ID;

		$DB->StartUsingMasterOnly();
		$strSql =
			"UPDATE b_iblock_element SET ".
			"	TIMESTAMP_X = ".($DB->type=="ORACLE"?" NULL":"TIMESTAMP_X").", ".
			"	SHOW_COUNTER_START = ".$DB->IsNull("SHOW_COUNTER_START", $DB->CurrentTimeFunction()).", ".
			"	SHOW_COUNTER =  ".$DB->IsNull("SHOW_COUNTER", 0)." + 1 ".
			"WHERE ID=".$ID;
		$DB->Query($strSql, false, "", array("ignore_dml"=>true));
		$DB->StopUsingMasterOnly();
	}

	public static function GetIBVersion($iblock_id)
	{
		return
			(int)CIBlock::GetArrayByID($iblock_id, 'VERSION') === Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE
				? Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE
				: Iblock\IblockTable::PROPERTY_STORAGE_COMMON
		;
	}

	public static function DeletePropertySQL($property, $iblock_element_id)
	{
		global $DB;

		if($property["VERSION"]==2)
		{
			if($property["MULTIPLE"]=="Y")
				return "
					DELETE
					FROM b_iblock_element_prop_m".intval($property["IBLOCK_ID"])."
					WHERE
						IBLOCK_ELEMENT_ID=".intval($iblock_element_id)."
						AND IBLOCK_PROPERTY_ID=".intval($property["ID"])."
				";
			else
			{
				return "
					UPDATE
						b_iblock_element_prop_s".intval($property["IBLOCK_ID"])."
					SET
						PROPERTY_".intval($property["ID"])."=null
						".self::__GetDescriptionUpdateSql($property["IBLOCK_ID"], $property["ID"])."
					WHERE
						IBLOCK_ELEMENT_ID=".intval($iblock_element_id)."
				";
			}
		}
		else
		{
			return "
				DELETE FROM
					b_iblock_element_property
				WHERE
					IBLOCK_ELEMENT_ID=".intval($iblock_element_id)."
					AND IBLOCK_PROPERTY_ID=".intval($property["ID"])."
			";
		}
	}

	public static function SetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS = [])
	{
		//Check input parameters
		if (!is_array($PROPERTY_VALUES))
		{
			return;
		}

		if (!is_array($FLAGS))
		{
			$FLAGS = [];
		}
		//FLAGS - modify function behavior
		//NewElement - if present no db values select will be issued
		//DoNotValidateLists - if present list values do not validates against metadata tables

		global $DB;
		global $BX_IBLOCK_PROP_CACHE;

		$ELEMENT_ID = (int)$ELEMENT_ID;
		if ($ELEMENT_ID <= 0)
		{
			return;
		}

		$IBLOCK_ID = (int)$IBLOCK_ID;
		if ($IBLOCK_ID <= 0)
		{
			$ar = static::GetIBlockByID($ELEMENT_ID);
			if (empty($ar))
			{
				return;
			}
			$IBLOCK_ID = $ar;
		}

		//Get property metadata
		$uniq_flt = $IBLOCK_ID."|SetPropertyValuesEx";

		if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID]))
		{
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID] = array();
		}

		if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt]))
		{
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt] = array(0=>array());
			$rs = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID"=>$IBLOCK_ID,
				"CHECK_PERMISSIONS"=>"N",
				"ACTIVE"=>"Y",
			));
			while($ar = $rs->Fetch())
			{
				$ar["ConvertToDB"] = false;
				if($ar["USER_TYPE"]!="")
				{
					$arUserType = CIBlockProperty::GetUserType($ar["USER_TYPE"]);
					if(array_key_exists("ConvertToDB", $arUserType))
						$ar["ConvertToDB"] = $arUserType["ConvertToDB"];
				}

				$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt][$ar["ID"]] = $ar;
				//For CODE2ID conversion
				$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt][0][$ar["CODE"]] = $ar["ID"];
				//VERSION
				$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt]["VERSION"] = $ar["VERSION"];
			}
		}

		$PROPS_CACHE = $BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt];
		//Unify properties values arProps[$property_id]=>array($id=>array("VALUE", "DESCRIPTION"),....)
		$arProps = array();
		$propertyList = [];
		foreach($PROPERTY_VALUES as $key=>$value)
		{
			//Code2ID
			if(array_key_exists($key, $PROPS_CACHE[0]))
			{
				$key = $PROPS_CACHE[0][$key];
			}
			//It's not CODE so check if such ID exists
			else
			{
				$key = intval($key);
				if($key <= 0 || !array_key_exists($key, $PROPS_CACHE))
					continue;
			}

			$propertyList[$key] = $PROPS_CACHE[$key];
			if($PROPS_CACHE[$key]["PROPERTY_TYPE"]=="F")
			{
				if(is_array($value))
				{
					$ar = array_keys($value);
					if(array_key_exists("tmp_name", $value) || array_key_exists("del", $value))
					{
						$uni_value = array(array("ID"=>0,"VALUE"=>$value,"DESCRIPTION"=>""));
					}
					elseif($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
					{
						$uni_value = array(array("ID"=>0,"VALUE"=>$value["VALUE"],"DESCRIPTION"=>$value["DESCRIPTION"]));
					}
					elseif(count($ar)===1 && $ar[0]==="VALUE")
					{
						$uni_value = array(array("ID"=>0,"VALUE"=>$value["VALUE"],"DESCRIPTION"=>""));
					}
					else //multiple values
					{
						$uni_value = array();
						foreach($value as $id=>$val)
						{
							if(is_array($val))
							{
								if(array_key_exists("tmp_name", $val) || array_key_exists("del", $val))
								{
									$uni_value[] = array("ID"=>$id,"VALUE"=>$val,"DESCRIPTION"=>"");
								}
								else
								{
									$ar = array_keys($val);
									if($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
										$uni_value[] = array("ID"=>$id,"VALUE"=>$val["VALUE"],"DESCRIPTION"=>$val["DESCRIPTION"]);
									elseif(count($ar)===1 && $ar[0]==="VALUE")
										$uni_value[] = array("ID"=>$id,"VALUE"=>$val["VALUE"],"DESCRIPTION"=>"");
								}
							}
						}
					}
				}
				else
				{
					//There was no valid file array found so we'll skip this property
					$uni_value = array();
				}
			}
			elseif(!is_array($value))
			{
				$uni_value = array(array("VALUE"=>$value,"DESCRIPTION"=>""));
			}
			else
			{
				$ar = array_keys($value);
				if(count($ar)===2 && $ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
				{
					$uni_value = array(array("VALUE"=>$value["VALUE"],"DESCRIPTION"=>$value["DESCRIPTION"]));
				}
				elseif(count($ar)===1 && $ar[0]==="VALUE")
				{
					$uni_value = array(array("VALUE"=>$value["VALUE"],"DESCRIPTION"=>""));
				}
				else // multiple values
				{
					$uni_value = array();
					foreach($value as $id=>$val)
					{
						if(!is_array($val))
							$uni_value[] = array("VALUE"=>$val,"DESCRIPTION"=>"");
						else
						{
							$ar = array_keys($val);
							if($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
								$uni_value[] = array("VALUE"=>$val["VALUE"],"DESCRIPTION"=>$val["DESCRIPTION"]);
							elseif(count($ar)===1 && $ar[0]==="VALUE")
								$uni_value[] = array("VALUE"=>$val["VALUE"],"DESCRIPTION"=>"");
						}
					}
				}
			}

			$arValueCounters = array();
			foreach($uni_value as $val)
			{
				if(!array_key_exists($key, $arProps))
				{
					$arProps[$key] = array();
					$arValueCounters[$key] = 0;
				}

				if($PROPS_CACHE[$key]["ConvertToDB"]!==false)
				{
					$arProperty = $PROPS_CACHE[$key];
					$arProperty["ELEMENT_ID"] = $ELEMENT_ID;
					$val = call_user_func_array($PROPS_CACHE[$key]["ConvertToDB"], array($arProperty, $val));
				}

				if(
					(!is_array($val["VALUE"]) && (string)$val["VALUE"] <> '')
					|| (is_array($val["VALUE"]) && !empty($val["VALUE"]))
				)
				{
					if(
						$arValueCounters[$key] == 0
						|| $PROPS_CACHE[$key]["MULTIPLE"]=="Y"
					)
					{
						if(!is_array($val["VALUE"]) || !isset($val["VALUE"]["del"]))
							$arValueCounters[$key]++;

						$arProps[$key][] = $val;
					}
				}
			}
		}

		if (empty($arProps))
		{
			return;
		}

		//Read current property values from database
		$arDBProps = array();
		if(!array_key_exists("NewElement", $FLAGS))
		{
			if($PROPS_CACHE["VERSION"]==1)
			{
				$rs = $DB->Query("
					select *
					from b_iblock_element_property
					where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
					AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProps)).")
				");
				while($ar=$rs->Fetch())
				{
					if(!array_key_exists($ar["IBLOCK_PROPERTY_ID"], $arDBProps))
						$arDBProps[$ar["IBLOCK_PROPERTY_ID"]] = array();
					$arDBProps[$ar["IBLOCK_PROPERTY_ID"]][$ar["ID"]] = $ar;
				}
			}
			else
			{
				$rs = $DB->Query("
					select *
					from b_iblock_element_prop_m".$IBLOCK_ID."
					where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
					AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProps)).")
				");
				while($ar=$rs->Fetch())
				{
					if(!array_key_exists($ar["IBLOCK_PROPERTY_ID"], $arDBProps))
						$arDBProps[$ar["IBLOCK_PROPERTY_ID"]] = array();
					$arDBProps[$ar["IBLOCK_PROPERTY_ID"]][$ar["ID"]] = $ar;
				}
				$rs = $DB->Query("
					select *
					from b_iblock_element_prop_s".$IBLOCK_ID."
					where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
				");
				if($ar=$rs->Fetch())
				{
					foreach($PROPS_CACHE as $property_id=>$property)
					{
						if(	array_key_exists($property_id, $arProps)
							&& array_key_exists("PROPERTY_".$property_id, $ar)
							&& $property["MULTIPLE"]=="N"
							&& $ar["PROPERTY_".$property_id] <> '')
						{
							$pr=array(
								"IBLOCK_PROPERTY_ID" => $property_id,
								"VALUE" => $ar["PROPERTY_".$property_id],
								"DESCRIPTION" => $ar["DESCRIPTION_".$property_id],
							);
							if(!array_key_exists($pr["IBLOCK_PROPERTY_ID"], $arDBProps))
								$arDBProps[$pr["IBLOCK_PROPERTY_ID"]] = array();
							$arDBProps[$pr["IBLOCK_PROPERTY_ID"]][$ELEMENT_ID.":".$property_id] = $pr;
						}
					}
				}
				else
				{
					$DB->Query("
					insert into b_iblock_element_prop_s".$IBLOCK_ID."
					(IBLOCK_ELEMENT_ID) values (".$ELEMENT_ID.")
				");
				}
			}
		}

		foreach (GetModuleEvents("iblock", "OnIBlockElementSetPropertyValuesEx", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $propertyList, $arDBProps));
		if (isset($arEvent))
			unset($arEvent);

		$arFilesToDelete = array();
		//Handle file properties
		foreach($arProps as $property_id=>$values)
		{
			if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]=="F")
			{
				foreach($values as $i=>$value)
				{
					$val = $value["VALUE"];
					if($val["del"] <> '')
					{
						$val = "NULL";
					}
					else
					{
						$val["MODULE_ID"] = "iblock";
						unset($val["old_file"]);

						if($value["DESCRIPTION"] <> '')
							$val["description"] = $value["DESCRIPTION"];

						$val = CFile::SaveFile($val, "iblock");
					}

					if($val=="NULL")
					{//Delete it! Actually it will not add an value
						unset($arProps[$property_id][$i]);
					}
					elseif(intval($val)>0)
					{
						$arProps[$property_id][$i]["VALUE"] = intval($val);
						if($value["DESCRIPTION"] == '')
							$arProps[$property_id][$i]["DESCRIPTION"]=$arDBProps[$property_id][$value["ID"]]["DESCRIPTION"];
					}
					elseif($value["DESCRIPTION"] <> '')
					{
						$arProps[$property_id][$i]["VALUE"] = $arDBProps[$property_id][$value["ID"]]["VALUE"];
						//Only needs to update description so CFile::Delete will not called
						unset($arDBProps[$property_id][$value["ID"]]);
					}
					else
					{
						$arProps[$property_id][$i]["VALUE"] = $arDBProps[$property_id][$value["ID"]]["VALUE"];
						//CFile::Delete will not called
						unset($arDBProps[$property_id][$value["ID"]]);
					}
				}

				if(array_key_exists($property_id, $arDBProps))
				{
					foreach($arDBProps[$property_id] as $id=>$value)
						$arFilesToDelete[] = array($value["VALUE"], $ELEMENT_ID, "PROPERTY", -1, $IBLOCK_ID);
				}
			}
		}

		foreach($arFilesToDelete as $ar)
			call_user_func_array(array("CIBlockElement", "DeleteFile"), $ar);

		//Now we'll try to find out properties which do not require any update
		if(!array_key_exists("NewElement", $FLAGS))
		{
			foreach($arProps as $property_id=>$values)
			{
				if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]!="F")
				{
					if(array_key_exists($property_id, $arDBProps))
					{
						$db_values = $arDBProps[$property_id];
						if(count($values) == count($db_values))
						{
							$bEqual = true;
							foreach($values as $id=>$value)
							{
								$bDBFound = false;
								foreach($db_values as $db_id=>$db_row)
								{
									if (
										strcmp($value['VALUE'], $db_row['VALUE']) === 0
										&& strcmp(
											(string)($value['DESCRIPTION'] ?? ''),
											(string)($db_row['DESCRIPTION'] ?? '')
										) === 0
									)
									{
										unset($db_values[$db_id]);
										$bDBFound = true;
										break;
									}
								}
								if(!$bDBFound)
								{
									$bEqual = false;
									break;
								}
							}
							if($bEqual)
							{
								unset($arProps[$property_id]);
								unset($arDBProps[$property_id]);
							}
						}
					}
					elseif(count($values)==0)
					{
						//Values was not found in database neither no values input was given
						unset($arProps[$property_id]);
					}
				}
			}
		}

		//Init "commands" arrays
		$ar2Delete = array(
			"b_iblock_element_property" => array(/*property_id=>true, property_id=>true, ...*/),
			"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>true, property_id=>true, ...*/),
			"b_iblock_section_element" => array(/*property_id=>true, property_id=>true, ...*/),
		);
		$ar2Insert = array(
			"values" => array(
				"b_iblock_element_property" => array(/*property_id=>value, property_id=>value, ...*/),
				"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
			),
			"sqls"=>array(
				"b_iblock_element_property" => array(/*property_id=>sql, property_id=>sql, ...*/),
				"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>sql, property_id=>sql, ...*/),
				"b_iblock_section_element" => array(/*property_id=>sql, property_id=>sql, ...*/),
			),
		);
		$ar2Update = array(
			//"b_iblock_element_property" => array(/*property_id=>value, property_id=>value, ...*/),
			//"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
			//"b_iblock_element_prop_s".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
		);

		foreach($arDBProps as $property_id=>$values)
		{
			if($PROPS_CACHE[$property_id]["VERSION"]==1)
			{
				$ar2Delete["b_iblock_element_property"][$property_id]=true;
			}
			elseif($PROPS_CACHE[$property_id]["MULTIPLE"]=="Y")
			{
				$ar2Delete["b_iblock_element_prop_m".$IBLOCK_ID][$property_id]=true;
				$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
			}
			else
			{
				$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
			}
			if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]=="G")
				$ar2Delete["b_iblock_section_element"][$property_id]=true;
		}

		foreach($arProps as $property_id=>$values)
		{
			$db_prop = $PROPS_CACHE[$property_id];
			if($db_prop["PROPERTY_TYPE"]=="L" && !array_key_exists("DoNotValidateLists",$FLAGS))
			{
				$arID=array();
				foreach($values as $value)
				{
					$value["VALUE"] = intval($value["VALUE"]);
					if($value["VALUE"]>0)
						$arID[]=$value["VALUE"];
				}
				if(count($arID)>0)
				{
					if($db_prop["VERSION"]==1)
					{
						$ar2Insert["sqls"]["b_iblock_element_property"][$property_id] = "
								INSERT INTO b_iblock_element_property
								(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM)
								SELECT ".$ELEMENT_ID.", P.ID, PEN.ID, PEN.ID
								FROM
									b_iblock_property P
									,b_iblock_property_enum PEN
								WHERE
									P.ID=".$property_id."
									AND P.ID=PEN.PROPERTY_ID
									AND PEN.ID IN (".implode(", ",$arID).")
						";
					}
					elseif($db_prop["MULTIPLE"]=="Y")
					{
						$ar2Insert["sqls"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id] = "
								INSERT INTO b_iblock_element_prop_m".$IBLOCK_ID."
								(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM)
								SELECT ".$ELEMENT_ID.", P.ID, PEN.ID, PEN.ID
								FROM
									b_iblock_property P
									,b_iblock_property_enum PEN
								WHERE
									P.ID=".$property_id."
									AND P.ID=PEN.PROPERTY_ID
									AND PEN.ID IN (".implode(", ",$arID).")
						";
						$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
					}
					else
					{
						$rs = $DB->Query("
								SELECT PEN.ID
								FROM
									b_iblock_property P
									,b_iblock_property_enum PEN
								WHERE
									P.ID=".$property_id."
									AND P.ID=PEN.PROPERTY_ID
									AND PEN.ID IN (".implode(", ",$arID).")
						");
						if($ar = $rs->Fetch())
							$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=array("VALUE"=>$ar["ID"],"DESCRIPTION"=>"");
					}
				}
				continue;
			}
			if($db_prop["PROPERTY_TYPE"]=="G")
			{
				$arID=array();
				foreach($values as $value)
				{
					$value["VALUE"] = intval($value["VALUE"]);
					if($value["VALUE"]>0)
						$arID[]=$value["VALUE"];
				}
				if(count($arID)>0)
				{
					if($db_prop["VERSION"]==1)
					{
						$ar2Insert["sqls"]["b_iblock_element_property"][$property_id] = "
								INSERT INTO b_iblock_element_property
								(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM)
								SELECT ".$ELEMENT_ID.", P.ID, S.ID, S.ID
								FROM
									b_iblock_property P
									,b_iblock_section S
								WHERE
									P.ID=".$property_id."
									AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
									AND S.ID IN (".implode(", ",$arID).")
						";
					}
					elseif($db_prop["MULTIPLE"]=="Y")
					{
						$ar2Insert["sqls"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id] = "
								INSERT INTO b_iblock_element_prop_m".$IBLOCK_ID."
								(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM)
								SELECT ".$ELEMENT_ID.", P.ID, S.ID, S.ID
								FROM
									b_iblock_property P
									,b_iblock_section S
								WHERE
									P.ID=".$property_id."
									AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
									AND S.ID IN (".implode(", ",$arID).")
						";
						$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
					}
					else
					{
						$rs = $DB->Query("
								SELECT S.ID
								FROM
									b_iblock_property P
									,b_iblock_section S
								WHERE
									P.ID=".$property_id."
									AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
									AND S.ID IN (".implode(", ",$arID).")
						");
						if($ar = $rs->Fetch())
							$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=array("VALUE"=>$ar["ID"],"DESCRIPTION"=>"");
					}
					$ar2Insert["sqls"]["b_iblock_section_element"][$property_id] = "
						INSERT INTO b_iblock_section_element
						(IBLOCK_ELEMENT_ID, IBLOCK_SECTION_ID, ADDITIONAL_PROPERTY_ID)
						SELECT ".$ELEMENT_ID.", S.ID, P.ID
						FROM b_iblock_property P, b_iblock_section S
						WHERE P.ID=".$property_id."
							AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
							AND S.ID IN (".implode(", ",$arID).")
					";
				}
				continue;
			}
			foreach($values as $value)
			{
				if($db_prop["VERSION"]==1)
				{
					$ar2Insert["values"]["b_iblock_element_property"][$property_id][]=$value;
				}
				elseif($db_prop["MULTIPLE"]=="Y")
				{
					$ar2Insert["values"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id][]=$value;
					$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
				}
				else
				{
					$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=$value;
				}
			}
		}

		foreach($ar2Delete as $table=>$arID)
		{
			if(count($arID)>0)
			{
				if($table=="b_iblock_section_element")
					$DB->Query("
						delete from ".$table."
						where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
						and  ADDITIONAL_PROPERTY_ID in (".implode(", ", array_keys($arID)).")
					");
				else
					$DB->Query("
						delete from ".$table."
						where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
						and IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arID)).")
					");
			}
		}

		foreach($ar2Insert["values"] as $table=>$properties)
		{
			$strSqlPrefix = "
					insert into ".$table."
					(IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID, VALUE, VALUE_ENUM, VALUE_NUM, DESCRIPTION)
					values
			";

			$maxValuesLen = $DB->type=="MYSQL"?1024:0;
			$strSqlValues = "";
			foreach ($properties as $property_id=>$values)
			{
				foreach ($values as $value)
				{
					if ((string)$value["VALUE"] <> '')
					{
						$description = (string)($value['DESCRIPTION'] ?? '');
						$strSqlValues .= ",\n(".
							$property_id.", ".
							$ELEMENT_ID.", ".
							"'".$DB->ForSQL($value["VALUE"])."', ".
							intval($value["VALUE"]).", ".
							CIBlock::roundDB($value["VALUE"]).", ".
							($description !== ''? "'".$DB->ForSQL($description)."'" : "null")." ".
						")";
					}
					if (mb_strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
						$strSqlValues = "";
					}
				}
			}
			if($strSqlValues <> '')
			{
				$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
				$strSqlValues = "";
			}
		}

		foreach($ar2Insert["sqls"] as $table=>$properties)
		{
			foreach($properties as $property_id=>$sql)
			{
				$DB->Query($sql);
			}
		}

		foreach($ar2Update as $table=>$properties)
		{
			$tableFields = $DB->GetTableFields($table);
			if(count($properties)>0)
			{
				$arFields = array();
				foreach($properties as $property_id=>$value)
				{
					if($value===false || (string)$value["VALUE"] === '')
					{
						$arFields[] = "PROPERTY_".$property_id." = null";
						if (isset($tableFields["DESCRIPTION_".$property_id]))
						{
							$arFields[] = "DESCRIPTION_".$property_id." = null";
						}
					}
					else
					{
						$arFields[] = "PROPERTY_".$property_id." = '".$DB->ForSQL($value["VALUE"])."'";
						if (isset($tableFields["DESCRIPTION_".$property_id]))
						{
							if($value["DESCRIPTION"] <> '')
							{
								$arFields[] = "DESCRIPTION_".$property_id." = '".$DB->ForSQL($value["DESCRIPTION"])."'";
							}
							else
							{
								$arFields[] = "DESCRIPTION_".$property_id." = null";
							}
						}
					}
				}
				$DB->Query("
					update ".$table."
					set ".implode(",\n", $arFields)."
					where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
				");
			}
		}
		/****************************** QUOTA ******************************/
		CDiskQuota::recalculateDb();
		/****************************** QUOTA ******************************/

		foreach (GetModuleEvents("iblock", "OnAfterIBlockElementSetPropertyValuesEx", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS));
	}

	protected static function getCheckRightsSql(?int $iblockId, string $minPermission, ?int $permissionsBy = null): string
	{
		global $USER;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		unset($connection);

		$minPermission = (strlen($minPermission) === 1) ? $minPermission : CIBlockRights::PUBLIC_READ;

		if ($permissionsBy !== null && $permissionsBy < 0)
		{
			$permissionsBy = null;
		}

		if ($permissionsBy !== null)
		{
			$userId = $permissionsBy;
			$groupList = CUser::GetUserGroup($permissionsBy);
			$authorized = false;
		}
		else
		{
			if (isset($USER) && $USER instanceof CUser)
			{
				$userId = (int)$USER->GetID();
				$groupList = $USER->GetUserGroupArray();
				$authorized = $USER->IsAuthorized();
			}
			else
			{
				$userId = 0;
				$groupList = ['2'];
				$authorized = false;
			}
		}

		$simplePermissions = "
			SELECT IBLOCK_ID
			FROM b_iblock_group IBG
			WHERE IBG.GROUP_ID IN (" . implode(',', $groupList) . ")
			AND IBG.PERMISSION >= '" . $helper->forSql($minPermission) . "'
		";
		if (!defined('ADMIN_SECTION'))
		{
			$simplePermissions .= "
				AND (IBG.PERMISSION='" . CIBlockRights::FULL_ACCESS . "' OR B.ACTIVE='Y')
			";
		}
		$simplePermissions = "B.ID IN (" . $simplePermissions . ")";

		if ($minPermission >= CIBlockRights::FULL_ACCESS)
		{
			$operation = CIBlockElementRights::OPERATION_RIGHTS_EDIT;
		}
		elseif ($minPermission >= CIBlockRights::EDIT_ACCESS)
		{
			$operation = CIBlockElementRights::OPERATION_EDIT;
		}
		elseif ($minPermission >= CIBlockRights::PUBLIC_READ)
		{
			$operation = CIBlockElementRights::OPERATION_READ;
		}
		else
		{
			$operation = '';
		}

		if ($operation)
		{
			$acc = new CAccess;
			$acc->UpdateCodes($permissionsBy !== null ? ['USER_ID' => $permissionsBy] : false);
			unset($acc);
		}

		if ($operation === CIBlockElementRights::OPERATION_READ)
		{
			$extendedPermissions = "
				SELECT ER.ELEMENT_ID
				FROM b_iblock_element_right ER
				INNER JOIN b_iblock_right IBR ON IBR.ID = ER.RIGHT_ID
				" . ($userId > 0 ? 'LEFT': 'INNER')
				. " JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = " . $userId . "
				WHERE ER.ELEMENT_ID = BE.ID
				AND IBR.OP_EREAD = 'Y'
				" . ($authorized || $userId > 0
					? "
					AND (UA.USER_ID IS NOT NULL
					" . ($authorized ? "OR IBR.GROUP_CODE = 'AU'": '') . "
					" . ($userId > 0 ? "OR (IBR.GROUP_CODE = 'CR' AND BE.CREATED_BY = " . $userId . ")": '') . "
				)"
					: ''
				) . "
			";

			$full = "(
				" . $simplePermissions . "
				OR (B.RIGHTS_MODE = '" . Iblock\IblockTable::RIGHTS_EXTENDED . "' AND EXISTS (" . $extendedPermissions . "))
			)";
		}
		elseif ($operation)
		{
			$extendedPermissions = "
				SELECT ER.ELEMENT_ID
				FROM b_iblock_element_right ER
				INNER JOIN b_iblock_right IBR ON IBR.ID = ER.RIGHT_ID
				INNER JOIN b_task_operation T ON T.TASK_ID = IBR.TASK_ID
				INNER JOIN b_operation O ON O.ID = T.OPERATION_ID
				" . ($userId > 0 ? 'LEFT': 'INNER')
				. " JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = " . $userId . "
				WHERE ER.ELEMENT_ID = BE.ID
				AND O.NAME = '" . $operation . "'
				" . ($authorized || $userId > 0
					? "
					AND (UA.USER_ID IS NOT NULL
					" . ($authorized ? "OR IBR.GROUP_CODE = 'AU'": '') . "
					" . ($userId > 0 ? "OR (IBR.GROUP_CODE = 'CR' AND BE.CREATED_BY = " . $userId . ")": '') . "
				)"
					: ''
				) . "
			";

			$full = "(
				" . $simplePermissions . "
				OR (B.RIGHTS_MODE = '" . Iblock\IblockTable::RIGHTS_EXTENDED . "' AND EXISTS (" . $extendedPermissions . "))
			)";
		}
		else
		{
			$extendedPermissions = '';
			$full = "(
				" . $simplePermissions . "
			)";
		}

		$result = [
			'SIMPLE' => $simplePermissions,
			'EXTENDED' => 'EXISTS (' . $extendedPermissions .')',
			'FULL' => $full,
		];

		$mode = 'FULL';
		if ($iblockId !== null)
		{
			$mode = match (CIBlock::GetArrayByID($iblockId, 'RIGHTS_MODE'))
			{
				Iblock\IblockTable::RIGHTS_SIMPLE => 'SIMPLE',
				Iblock\IblockTable::RIGHTS_EXTENDED => 'EXTENDED',
				default => 'FULL',
			};
		}

		return $result[$mode];
	}

	/** @deprecated */
	protected static function _check_rights_sql($min_permission, $permissionsBy = null)
	{
		if ($permissionsBy !== null)
		{
			$permissionsBy = (int)$permissionsBy;
		}

		return static::getCheckRightsSql(null, (string)$min_permission, $permissionsBy);
	}

	protected static function __GetDescriptionUpdateSql($iblock_id, $property_id, $description = false)
	{
		global $DB;
		$tableFields = $DB->GetTableFields("b_iblock_element_prop_s".$iblock_id);
		if (isset($tableFields["DESCRIPTION_".$property_id]))
		{
			if ($description !== false)
				$sqlValue = "'".$DB->ForSQL($description, 255)."'";
			else
				$sqlValue = "null";
			return ", DESCRIPTION_".$property_id."=".$sqlValue;
		}
		else
		{
			return "";
		}
	}

	/**
	 * @param mixed $order
	 * @return string
	 */
	protected function getIdOrder($order): string
	{
		if (is_array($order))
		{
			Main\Type\Collection::normalizeArrayValuesByInt($order, false);
			if (!empty($order))
			{
				$connection = Main\Application::getConnection();
				$helper = $connection->getSqlHelper();

				return $helper->getOrderByIntField('BE.ID', $order, false);
			}
			else
			{
				$order = '';
			}
		}
		if (!is_string($order))
		{
			$order = '';
		}

		return CIBlock::_Order('BE.ID', $order, 'desc', false);
	}

	protected function getSearchableContent(int $id, array $fields, array $iblock): ?string
	{
		if ($id <= 0 || empty($fields))
		{
			return null;
		}

		$result = $fields['NAME'];

		$properties = array();
		$list = $this->getIndexedProperties($iblock['ID']);
		if (!empty($list))
		{
			$properties = $list;
		}
		$list = $this->getOfferProperties($iblock['ID']);
		if (!empty($list))
		{
			foreach (array_keys($list) as $index)
			{
				$properties[$index] = $list[$index];
			}
		}

		if (!empty($properties))
		{
			$connection = Main\Application::getConnection();
			if ($iblock["VERSION"] == Iblock\IblockTable::PROPERTY_STORAGE_COMMON)
			{
				$iterator = $connection->query("
					select *
					from b_iblock_element_property
					where IBLOCK_ELEMENT_ID=".$id."
					and IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($properties)).")
				");
			}
			else
			{
				$iterator = $connection->query("
					select *
					from b_iblock_element_prop_m".$iblock['ID']."
					where IBLOCK_ELEMENT_ID=".$id."
					and IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($properties)).")
				");
			}
			while ($row = $iterator->fetch())
			{
				$result .= "\r\n";
				$property = $properties[$row["IBLOCK_PROPERTY_ID"]];
				if (isset($property["GetSearchContent"]))
				{
					$result .= HTMLToTxt(
						call_user_func_array($property["GetSearchContent"],
							array(
								$property,
								array("VALUE" => $row["VALUE"]),
								array(),
							)
						)
					);
				}
				elseif ($property["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_LIST)
				{
					$arEnum = CIBlockPropertyEnum::GetByID($row["VALUE"]);
					if ($arEnum !== false)
						$result .= $arEnum["VALUE"];
				}
				elseif($property["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_FILE)
				{
					$arFile = CIBlockElement::__GetFileContent($row["VALUE"]);
					if(is_array($arFile))
					{
						$result .= $arFile["CONTENT"];
					}
				}
				else
				{
					$result .= $row["VALUE"];
				}
			}
			if ($iblock["VERSION"] == Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE)
			{
				$iterator = $connection->query("
						select *
						from b_iblock_element_prop_s".$iblock['ID']."
						where IBLOCK_ELEMENT_ID=".$id."
					");
				if ($row = $iterator->fetch())
				{
					foreach($properties as $propertyId => $item)
					{
						$fieldId = 'PROPERTY_'.$propertyId;
						if (isset($row[$fieldId])
							&& $item["MULTIPLE"] == "N"
							&& $row[$fieldId] != '')
						{
							$result .= "\r\n";
							if (isset($item["GetSearchContent"]))
							{
								$result .= HTMLToTxt(
									call_user_func_array($item["GetSearchContent"],
										array(
											$item,
											array("VALUE" => $row[$fieldId]),
											array(),
										)
									)
								);
							}
							elseif ($item["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_LIST)
							{
								$arEnum = CIBlockPropertyEnum::GetByID($row[$fieldId]);
								if($arEnum!==false)
									$result .= $arEnum["VALUE"];
							}
							elseif($item["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_FILE)
							{
								$arFile = CIBlockElement::__GetFileContent($row[$fieldId]);
								if(is_array($arFile))
								{
									$result .= $arFile["CONTENT"];
								}
							}
							else
							{
								$result .= $row[$fieldId];
							}
						}
					}
				}
			}
		}
		if (isset($fields["PREVIEW_TEXT"]))
		{
			if (isset($fields["PREVIEW_TEXT_TYPE"]) && $fields["PREVIEW_TEXT_TYPE"] == "html")
				$result .= "\r\n".HTMLToTxt($fields["PREVIEW_TEXT"]);
			else
				$result .= "\r\n".$fields["PREVIEW_TEXT"];
		}
		if (isset($fields["DETAIL_TEXT"]))
		{
			if (isset($fields["DETAIL_TEXT_TYPE"]) && $fields["DETAIL_TEXT_TYPE"] == "html")
				$result .= "\r\n".HTMLToTxt($fields["DETAIL_TEXT"]);
			else
				$result .= "\r\n".$fields["DETAIL_TEXT"];
		}

		return $result;
	}

	protected function getPropertiesByFilter(array $filter): ?array
	{
		$result = array();
		$iterator = Iblock\PropertyTable::getList(array(
			'select' => array(
				'ID', 'IBLOCK_ID', 'NAME', 'SORT', 'MULTIPLE', 'WITH_DESCRIPTION',
				'PROPERTY_TYPE', 'USER_TYPE', 'USER_TYPE_SETTINGS_LIST', 'LINK_IBLOCK_ID'
			),
			'filter' => $filter,
			'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
			'cache' => array('ttl' => 86400)
		));
		while ($row = $iterator->fetch())
		{
			$row['USER_TYPE'] = (string)$row['USER_TYPE'];
			if (!is_array($row['USER_TYPE_SETTINGS_LIST']))
			{
				$row['USER_TYPE_SETTINGS_LIST'] = null;
			}
			$row['USER_TYPE_SETTINGS'] = $row['USER_TYPE_SETTINGS_LIST'];
			unset($row['USER_TYPE_SETTINGS_LIST']);
			if ($row['USER_TYPE'] != '')
			{
				$userType = CIBlockProperty::GetUserType($row['USER_TYPE']);
				if (isset($userType['GetSearchContent']))
				{
					$row['GetSearchContent'] = $userType['GetSearchContent'];
				}
				elseif (isset($userType['GetPublicViewHTML']))
				{
					$row['GetSearchContent'] = $userType['GetPublicViewHTML'];
				}
			}
			$result[$row['ID']] = $row;
		}
		return (!empty($result) ? $result : null);
	}

	protected function getIndexedProperties(int $iblockId): ?array
	{
		if ($iblockId <= 0)
		{
			return null;
		}
		if (!isset($this->indexedProperties[$iblockId]))
		{
			$this->indexedProperties[$iblockId] = $this->getPropertiesByFilter(array(
				'=IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
				'=SEARCHABLE' => 'Y'
			));
			if (empty($this->indexedProperties[$iblockId]))
			{
				$this->indexedProperties[$iblockId] = array();
			}
		}
		return (!empty($this->indexedProperties[$iblockId]) ? $this->indexedProperties[$iblockId] : null);
	}

	protected function getOfferProperties(int $iblockId): ?array
	{
		if ($iblockId <= 0)
		{
			return null;
		}
		if (!$this->catalogIncluded)
		{
			return null;
		}

		if (!isset($this->offerProperties[$iblockId]))
		{
			$this->offerProperties[$iblockId] = array();
			$catalog = CCatalogSku::GetInfoByOfferIBlock($iblockId);
			if (!empty($catalog))
			{
				$ids = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes($catalog['IBLOCK_ID']);
				if (!empty($ids))
				{
					$this->offerProperties[$iblockId] = $this->getPropertiesByFilter(array(
						'=IBLOCK_ID' => $catalog['IBLOCK_ID'],
						'=ACTIVE' => 'Y',
						'@ID' => $ids
					));
					if (empty($this->offerProperties[$iblockId]))
					{
						$this->offerProperties[$iblockId] = array();
					}
				}
			}
		}
		return (!empty($this->offerProperties[$iblockId]) ? $this->offerProperties[$iblockId] : null);
	}

	public function generateMnemonicCode(string $name, int $iblockId, array $options = []): ?string
	{
		if ($name === '' || $iblockId <= 0)
		{
			return null;
		}

		if ($this->iblock !== null && $this->iblock['ID'] === $iblockId)
		{
			$iblock = $this->iblock;
			$language = $this->iblockLanguage;
		}
		else
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
			if (empty($iblock))
			{
				$iblock = null;
				$language = null;
			}
			else
			{
				$iblock['ID'] = (int)$iblock['ID'];
				$language = static::getIblockLanguage($iblock['ID']);
			}
		}

		if (empty($iblock))
		{
			return null;
		}

		$result = null;
		if (isset($iblock['FIELDS']['CODE']['DEFAULT_VALUE']))
		{
			if ($iblock['FIELDS']['CODE']['DEFAULT_VALUE']['TRANSLITERATION'] === 'Y'
				&& $iblock['FIELDS']['CODE']['DEFAULT_VALUE']['USE_GOOGLE'] === 'N'
			)
			{
				$config = $iblock['FIELDS']['CODE']['DEFAULT_VALUE'];
				$config['LANGUAGE_ID'] = $language;
				$config = array_merge($config, $options);

				if ($config['LANGUAGE_ID'] !== null)
				{
					$settings = [
						'max_len' => $config['TRANS_LEN'],
						'change_case' => $config['TRANS_CASE'],
						'replace_space' => $config['TRANS_SPACE'],
						'replace_other' => $config['TRANS_OTHER'],
						'delete_repeat_replace' => ($config['TRANS_EAT'] == 'Y'),
					];

					$result = CUtil::translit($name, $config['LANGUAGE_ID'], $settings);
				}
			}
		}

		return $result;
	}

	public function isExistsMnemonicCode(string $code, ?int $elementId, int $iblockId): bool
	{
		if ($code === '')
		{
			return false;
		}

		$filter = static::getPublicElementsOrmFilter([
			'=IBLOCK_ID' => $iblockId,
			'=CODE' => $code,
		]);
		if ($elementId !== null)
		{
			$filter['!=ID'] = $elementId;
		}

		return ElementTable::getRow([
			'select' => ['ID'],
			'filter' => $filter,
		]) !== null;
	}

	public function getUniqueMnemonicCode(string $code, ?int $elementId, int $iblockId, array $options = []): ?string
	{
		if ($code === '')
		{
			return false;
		}
		if ($iblockId <= 0)
		{
			return null;
		}

		if (!$this->isExistsMnemonicCode($code, $elementId, $iblockId))
		{
			return $code;
		}

		$checkSimilar = ($options['CHECK_SIMILAR'] ?? 'N') === 'Y';

		$list = [];
		$iterator = ElementTable::getList([
			'select' => [
				'ID',
				'CODE',
			],
			'filter' => static::getPublicElementsOrmFilter([
				'=IBLOCK_ID' => $iblockId,
				'%=CODE' => $code . '%',
			]),
		]);
		while ($row = $iterator->fetch())
		{
			if ($checkSimilar && $elementId === (int)$row['ID'])
			{
				return null;
			}
			$list[$row['CODE']] = true;
		}
		unset($iterator, $row);

		if (isset($list[$code]))
		{
			$code .= '_';
			$i = 1;
			while (isset($list[$code . $i]))
			{
				$i++;
			}

			$code .= $i;
		}
		unset($list);

		return $code;
	}

	public function createMnemonicCode(array $element, array $options = []): ?string
	{
		if (!isset($element['NAME']) || $element['NAME'] === '')
		{
			return null;
		}
		$iblockId = $element['IBLOCK_ID'] ?? 0;
		if ($iblockId !== null)
		{
			$iblockId = (int)$iblockId;
		}
		if ($iblockId <= 0)
		{
			return null;
		}

		if ($this->iblock !== null && $this->iblock['ID'] === $iblockId)
		{
			$iblock = $this->iblock;
		}
		else
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
		}

		if (empty($iblock))
		{
			return null;
		}

		$code = null;
		if (isset($iblock['FIELDS']['CODE']['DEFAULT_VALUE']))
		{
			$code = $this->generateMnemonicCode($element['NAME'], $iblockId, $options);
			if ($code === null)
			{
				return null;
			}

			if ($iblock['FIELDS']['CODE']['DEFAULT_VALUE']['TRANSLITERATION'] === 'Y'
				&& (
					$iblock['FIELDS']['CODE']['DEFAULT_VALUE']['UNIQUE'] === 'Y'
					|| ($options['CHECK_UNIQUE'] ?? 'N') === 'Y'
				)
			)
			{
				$id = (int)($element['ID'] ?? null);
				if ($id <= 0)
				{
					$id = null;
				}

				$code = $this->getUniqueMnemonicCode(
					$code,
					$id,
					$iblockId,
					$options
				);
			}
		}

		return $code;
	}

	protected static function getIblockLanguage(int $iblockId): ?string
	{
		$result = [];
		$iterator = Iblock\IblockSiteTable::getList([
			'select' => ['LANGUAGE_ID' => 'SITE.LANGUAGE_ID'],
			'filter' => ['=IBLOCK_ID' => $iblockId],
		]);
		while ($row = $iterator->fetch())
		{
			$result[$row['LANGUAGE_ID']] = true;
		}
		unset($iterator, $row);

		return count($result) === 1 ? key($result) : null;
	}

	public static function getPublicElementsOrmFilter(array $filter): array
	{
		$filter['=WF_STATUS_ID'] = 1;
		$filter['==WF_PARENT_ELEMENT_ID'] = null;

		return $filter;
	}

	public function getLastError(): string
	{
		return $this->LAST_ERROR;
	}

	private static function getUserNameSql(string $tableAlias): string
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return $helper->getConcatFunction(
			"'('",
			$tableAlias . '.LOGIN',
			"') '",
			$helper->getIsNullFunction($tableAlias . '.NAME', "''"),
			"' '",
			$helper->getIsNullFunction($tableAlias . '.LAST_NAME', "''")
		);
	}

	private static function isJoinForCount(int $joinType): bool
	{
		return ($joinType & self::JOIN_TYPE_WHERE) !== 0 || ($joinType & self::JOIN_TYPE_GROUP) !== 0;
	}

	protected static function useCountJoin(array $row): bool
	{
		return
			(int)($row['bFullJoin'] ?? false) === 1
			|| self::isJoinForCount($row['JOIN_TYPE'] ?? 0)
		;
	}

	private static function addJoinType(array $row, int $joinType): array
	{
		$row['JOIN_TYPE'] ??= 0;
		$row['JOIN_TYPE'] |= $joinType;

		return $row;
	}

	private static function addJoinTypeSelect(array $row): array
	{
		return self::addJoinType($row, self::JOIN_TYPE_SELECT);
	}

	private static function addJoinTypeWhere(array $row): array
	{
		return self::addJoinType($row, self::JOIN_TYPE_WHERE);
	}

	private static function addJoinTypeGroup(array $row): array
	{
		return self::addJoinType($row, self::JOIN_TYPE_GROUP);
	}

	private static function addJoinTypeOrder(array $row): array
	{
		return self::addJoinType($row, self::JOIN_TYPE_ORDER);
	}
}
