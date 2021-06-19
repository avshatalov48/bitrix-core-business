<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
define("HELP_FILE","ticket_list.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

if( isset( $_REQUEST["tabControl_active_tab"] ) )
{
	unset( $_REQUEST["tabControl_active_tab"] );
}
ClearVars();

$arFILES = array();
$arFiles = array();
$PROPERTY_ID = "SUPPORT";

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$message = null;
$messageA = array();

if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y" && $bSupportClient!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);


$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","ticket_list.php");

global $USER_FIELD_MANAGER;

/***************************************************************************
									Functions
***************************************************************************/

function CheckFields()
{
	global $ID, $TITLE, $MESSAGE, $arFILES, $bSupportTeam, $bAdmin;
	$max_size = 0;

	$arMsg = Array();
	if (trim($TITLE) == '' && intval($ID)<=0)
		//$str .= GetMessage("SUP_FORGOT_TITLE")."<br>";
		$arMsg[] = array("id"=>"TITLE", "text"=> GetMessage("SUP_FORGOT_TITLE"));


	if (trim($MESSAGE) == '' && intval($ID)<=0)
		//$str .= GetMessage("SUP_FORGOT_MESSAGE")."<br>";
		$arMsg[] = array("id"=>"MESSAGE", "text"=> GetMessage("SUP_FORGOT_MESSAGE"));

	if ($bSupportTeam!="Y" && $bAdmin!="Y")
	{
		$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
		$max_size = intval($max_size)*1024;
	}

	if ($max_size>0 && is_array($arFILES) && count($arFILES)>0)
	{
		$i = -1;
		foreach ($arFILES as $key => $arFILE)
		{
			$i++;
			if (intval($arFILE["size"])>$max_size)
				$arMsg[] = array("id"=>"FILE_".$i, "text"=> str_replace("#FILE_NAME#", $arFILE["name"], GetMessage("SUP_MAX_FILE_SIZE_EXCEEDING")));
		}
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;

}

function Support_GetUserInfo($USER_ID, &$login, &$name, $safe_for_html=true)
{
	static $arrUsers;
	$login = "";
	$name = "";
	if (intval($USER_ID)>0)
	{
		if (is_array($arrUsers) && in_array($USER_ID, array_keys($arrUsers)))
		{
			$login = $arrUsers[$USER_ID]["LOGIN"];
			$name = $arrUsers[$USER_ID]["NAME"];
		}
		else
		{
			$rsUser = CUser::GetByID($USER_ID);
			$arUser = $rsUser->Fetch();
			$login = $arUser["LOGIN"];
			$name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
			$arrUsers[$USER_ID] = array("LOGIN" => $login, "NAME" => $name);
		}
		if ($safe_for_html)
		{
			$login = htmlspecialcharsbx($login);
			$name = htmlspecialcharsbx($name);
		}
	}
}

function Support_GetDictionaryInfo($DID, &$name, &$desc, &$sid, $safe_for_html=true)
{
	static $arrDic;
	$name = "";
	$desc = "";
	$sid = "";
	if (intval($DID)>0)
	{
		if (is_array($arrDic) && in_array($DID, array_keys($arrDic)))
		{
			$name = $arrDic[$DID]["NAME"];
			$desc = $arrDic[$DID]["DESC"];
			$sid = $arrDic[$DID]["SID"];
		}
		else
		{
			$rsD = CTicketDictionary::GetByID($DID);
			$arD = $rsD->Fetch();
			$name = $arD["NAME"];
			$desc = $arD["DESCR"];
			$sid = $arD["SID"];
			$arrDic[$DID] = array("NAME" => $name, "DESC" => $desc, "SID" => $sid);
		}
		if ($safe_for_html)
		{
			$name = htmlspecialcharsbx($name);
			$desc = htmlspecialcharsbx($desc);
			$sid = htmlspecialcharsbx($sid);
		}
	}
}

function _Support_GetDictionaryInfoEx($arDictionary = Array())
{
	//$arID = array_values($arDictionary);

	$arID = Array();
	foreach ($arDictionary as $dic => $value)
	{
		if (intval($value) > 0)
			$arID[] = $value;
		else
			$GLOBALS["str_".$dic."_NAME"] = $GLOBALS["str_".$dic."_DESC"] = $GLOBALS["str_".$dic."_SID"] = "";
	}

	if (!empty($arID))
	{
		$arTypes = Array(
				"C" => "CATEGORY",
				"K" => "CRITICALITY",
				"S" => "STATUS",
				"M" => "MARK",
				"F" => "FUA",
				"SR" => "SOURCE",
				"D" => "DIFFICULTY"
		);

		$rs = CTicketDictionary::GetList('', '', array("ID"=> $arID));
		while ($ar = $rs->Fetch())
		{
			$dic = $ar["C_TYPE"];
			$GLOBALS["str_".$arTypes[$dic]."_NAME"] = $ar["NAME"];
			$GLOBALS["str_".$arTypes[$dic]."_DESC"] = $ar["DESCR"];
			$GLOBALS["str_".$arTypes[$dic]."_SID"] = $ar["SID"];
		}
	}
}

function  __GetDropDown($TYPE, &$TICKET_DICTIONARY)
{
	$arReturn = Array();

	if (array_key_exists($TYPE, $TICKET_DICTIONARY))
	{
		foreach ($TICKET_DICTIONARY[$TYPE] as $key => $value)
		{
			$arReturn["REFERENCE"][] = $value["REFERENCE"];
			$arReturn["REFERENCE_ID"][] = $key;
		}
	}

	return $arReturn;
}

function Support_GetSLAInfo($ID, &$name, &$description, $safe_for_html=true)
{
	static $arrSLA;
	$name = "";
	$description = "";
	if (intval($ID)>0)
	{
		if (is_array($arrSLA) && in_array($ID, array_keys($arrSLA)))
		{
			$name = $arrSLA[$ID]["NAME"];
			$description = $arrSLA[$ID]["DESCRIPTION"];
		}
		else
		{
			$rs = CTicketSLA::GetByID($ID);
			$ar = $rs->Fetch();
			$name = $ar["NAME"];
			$description = $ar["DESCRIPTION"];
			$arrSLA[$ar["ID"]] = array("NAME" => $ar["NAME"], "DESCRIPTION" => $ar["DESCRIPTION"]);
		}
		if ($safe_for_html)
		{
			$name = htmlspecialcharsbx($name);
			$description = htmlspecialcharsbx($description);
		}
	}
}

/***************************************************************************
							Work with GET | POST
***************************************************************************/
$ID = intval($ID);
if ($ID<=0)	$bOwner = "Y"; else $bOwner = CTicket::IsOwner($ID) ? "Y" : "N";

if ($bDemo=="Y" && $bOwner=="Y")
{
	$bSupportClient = "Y";
	$bDemo = "N";
}

$TICKET_LIST_URL = $TICKET_LIST_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_LIST_URL, 0, 4) == 'http'?'':'/').$TICKET_LIST_URL)) : "ticket_list.php";
$TICKET_EDIT_URL = $TICKET_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_EDIT_URL)) : "ticket_edit.php";
$TICKET_MESSAGE_EDIT_URL = $TICKET_MESSAGE_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_MESSAGE_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_MESSAGE_EDIT_URL)) : "ticket_message_edit.php";

if (intval($mdel_id)>0 && check_bitrix_sessid())
{
	CTicket::DeleteMessage($mdel_id, "Y");
	LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
}

if ($action <> '' && check_bitrix_sessid())
{
	switch ($action)
	{
		case "close":
			CTicket::SetTicket(array("CLOSE" => "Y"), $ID);
			LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
			break;
		case "open":
			CTicket::SetTicket(array("CLOSE" => "N"), $ID);
			LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
			break;
		case "unmark_spam":
			CTicket::UnMarkAsSpam($ID);
			LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
			break;
		case "maybe_spam":
			CTicket::MarkAsSpam($ID, "N");
			LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
			break;
		case "mark_spam":
			CTicket::MarkAsSpam($ID);
			LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
			break;
	}
}

// if button "Save" pressed
if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && check_bitrix_sessid())
{
	$arFILES = array();
	if (is_array($_FILES) && count($_FILES)>0)
	{
		foreach ($_FILES as $key => $arFILE)
		{
			if ($arFILE["name"] <> '')
			{
				$arFILE["MODULE_ID"] = "support";
				$arFILES[] = $arFILE;
			}
		}
	}
	if (CheckFields())
	{
		if ($OPEN=="Y") $CLOSE="N";
		if ($CLOSE=="Y") $OPEN="N";
		if ($bAdmin!="Y" && $bSupportTeam!="Y")
		{
			$HIDDEN = "N";
			$NOT_CHANGE_STATUS = "N";
		}
		$arFields = array(
			"SITE_ID"					=> $SITE_ID,
			"CLOSE"						=> $CLOSE,
			"AUTO_CLOSE_DAYS"			=> $AUTO_CLOSE_DAYS,
			"TITLE"						=> $TITLE,
			"SLA_ID"					=> $SLA_ID,
			"CATEGORY_ID"				=> $CATEGORY_ID,
			"CRITICALITY_ID"			=> $CRITICALITY_ID,
			"STATUS_ID"					=> $STATUS_ID,
			"DIFFICULTY_ID"				=> $DIFFICULTY_ID,
			"MARK_ID"					=> $MARK_ID,
			"TASK_TIME" 				=> $TASK_TIME,
			"HOLD_ON" 					=> $HOLD_ON,
			"SOURCE_ID"					=> $SOURCE_ID,
			"OWNER_SID"					=> $OWNER_SID,
			"OWNER_USER_ID"				=> $OWNER_USER_ID,
			"MESSAGE_SOURCE_ID"			=> $MESSAGE_SOURCE_ID,
			"MESSAGE_AUTHOR_SID"		=> $MESSAGE_AUTHOR_SID,
			"MESSAGE_AUTHOR_USER_ID"	=> $MESSAGE_AUTHOR_USER_ID,
			"RESPONSIBLE_USER_ID"		=> $RESPONSIBLE_USER_ID,
			"MESSAGE"					=> $MESSAGE,
			"HIDDEN"					=> $HIDDEN,
			"NOT_CHANGE_STATUS" => $NOT_CHANGE_STATUS,
			"FILES"						=> $arFILES,
			"SUPPORT_COMMENTS"			=> $SUPPORT_COMMENTS,
			"COUPON"					=> $COUPON,
			);

		if ($ID > 0)
		{
			$arFields['CHANGE_TITLE'] = $TITLE;
		}
		
		$NOTIFY = ($HIDDEN=="Y") ? "N" : "Y";

		$bSetTicket = false;
		if (CTicket::IsAdmin())
			$bSetTicket = true;
		else
		{
			$rsTicket = CTicket::GetByID($ID, SITE_ID, $check_rights = "Y", $get_user_name = "N", $get_extra_names = "N");
			if ($arTicket = $rsTicket->GetNext())
				$bSetTicket = true;
		}

		if ($bDemo!="Y" && $bAdmin!="Y" && ($bSupportTeam=="Y" && intval($ID) > 0 && !$bSetTicket))
		{
			// send to ticket list
			if ($OWNER_USER_ID!=$USER->GetID())
				LocalRedirect($TICKET_LIST_URL."?lang=".LANGUAGE_ID);
		}
		
		$USER_FIELD_MANAGER->EditFormAddFields($PROPERTY_ID, $arFields);
		$ID = CTicket::SetTicket($arFields, $ID, "Y", $NOTIFY);
		if (intval($ID)>0)
		{
			CTicket::UpdateOnline($ID);
			
			if (isset($_SESSION['TICKET_ID']) && isset($_SESSION['MESSAGE_ID']))
			{
				$intLastTicketID = $_SESSION['TICKET_ID'];
				
				$arParam = Array(
					'SPLIT_TICKET_ID'		=> $ID,
					'SPLIT_TICKET_TITLE' 	=> $TITLE,
					'SPLIT_MESSAGE_USER_ID'	=> $USER->GetID(),
					'SPLIT_ATTACH_FILE'		=> isset($_POST['ATTACH_FILE']) ? $_POST['ATTACH_FILE'] : Array(),
					'SOURCE_TICKET_ID' 		=> $intLastTicketID,
					'SOURCE_TICKET_TITLE'	=> $_SESSION['TICKET_TITLE'],
					'SOURCE_MESSAGE_ID' 	=> $MESSAGE_SOURCE_ID,
					'SOURCE_MESSAGE_NUM'	=> $_SESSION['MESSAGE_NUM'],
					'SOURCE_MESSAGE_DATE'	=> $_SESSION['MESSAGE_DATE']
				);
			
				CTicket::SplitTicket($arParam);
						
				unset($_SESSION['TICKET_ID']);
				unset($_SESSION['TICKET_TITLE']);
				unset($_SESSION['MESSAGE_ID']);
				unset($_SESSION['MESSAGE_NUM']);
				unset($_SESSION['MESSAGE_DATE']);
				
				if ($save <> '') // save -> new ticket				
					LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
				elseif ($apply <> '') // apply -> original ticket
					LocalRedirect($TICKET_EDIT_URL."?ID=".$intLastTicketID."&lang=".LANGUAGE_ID);
			} 
			else 
			{
				if ($save <> '') LocalRedirect($TICKET_LIST_URL."?lang=".LANGUAGE_ID);
				elseif ($apply <> '')
				{
					// change responsible
					if ($bDemo!="Y" && $bAdmin!="Y" && ($bSupportTeam=="Y" && $RESPONSIBLE_USER_ID!=$arTicket['RESPONSIBLE_USER_ID']))
					{
						// send to ticket list
						LocalRedirect($TICKET_LIST_URL."?lang=".LANGUAGE_ID);
					}
					else
					{
						// else refresh page
						LocalRedirect($TICKET_EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID);
					}
				}
			}
		}
		else
		{
			$e = new CAdminException(array());
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$ID = intval($_REQUEST['ID']);
		}
	}
}

$arrSiteRef = array();
$arrSiteID = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$arrSiteRef[] = "[".$ar["ID"]."] ".$ar["NAME"];
	$arrSiteID[] = $ar["ID"];
}

$get_user_name = "N";
$get_extra_names = "N";
$ALL_TICKET_FILES = Array();
$arStrUsers = array();

$site_id = (defined("ADMIN_SECTION") && ADMIN_SECTION==true) ? "" : SITE_ID;
$ticket = CTicket::GetByID($ID, $site_id, "Y", $get_user_name, $get_extra_names);
if (!($ticket && $ticket->ExtractFields()))
{
	$ID=0;
	$str_lang = $TICKET_SITE = (defined("ADMIN_SECTION") && ADMIN_SECTION==true) ? reset($arrSiteID) : SITE_ID;
	$str_RESPONSIBLE_USER_ID = intval(COption::GetOptionString('support', 'DEFAULT_RESPONSIBLE_ID', '0'));
}
else
{
	$str_lang = $TICKET_SITE = $str_SITE_ID;
	
	if ($str_DATE_CLOSE <> '') $str_CLOSE = "Y";
	CTicket::UpdateOnline($ID);

	$rsFiles = CTicket::GetFileList("s_id", "asc", array("TICKET_ID" => $ID));
	{
		while ($arFile = $rsFiles->Fetch())
		{
			$name = $arFile["ORIGINAL_NAME"] <> '' ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"];
			if ($arFile["EXTENSION_SUFFIX"] <> '')
			{
				$suffix_length = mb_strlen($arFile["EXTENSION_SUFFIX"]);
				$name = mb_substr($name, 0, mb_strlen($name) - $suffix_length);
			}
			$ALL_TICKET_FILES[$arFile["MESSAGE_ID"]][] = array("HASH" => $arFile["HASH"], "NAME" => $name, "FILE_SIZE" => $arFile["FILE_SIZE"]);
		}
	}

	if ($get_user_name=="N")
	{
		//Support_GetUserInfo($str_RESPONSIBLE_USER_ID, $str_RESPONSIBLE_LOGIN, $str_RESPONSIBLE_NAME);
		//Support_GetUserInfo($str_OWNER_USER_ID, $str_OWNER_LOGIN, $str_OWNER_NAME);
		//Support_GetUserInfo($str_CREATED_USER_ID, $str_CREATED_LOGIN, $str_CREATED_NAME);
		//Support_GetUserInfo($str_MODIFIED_USER_ID, $str_MODIFIED_BY_LOGIN, $str_MODIFIED_BY_NAME);
	}
	$arUserIDs = array($str_RESPONSIBLE_USER_ID, $str_OWNER_USER_ID, $str_CREATED_USER_ID, $str_MODIFIED_USER_ID);
	$arGuestIDs = array($str_OWNER_GUEST_ID, $str_CREATED_GUEST_ID, $str_MODIFIED_GUEST_ID);
	$arStrUsers =CTicket::GetUsersPropertiesArray($arUserIDs, $arGuestIDs);

	if ($get_extra_names=="N")
	{
		/*Support_GetDictionaryInfo($str_CATEGORY_ID, $str_CATEGORY_NAME, $str_CATEGORY_DESC, $str_CATEGORY_SID);
		Support_GetDictionaryInfo($str_CRITICALITY_ID, $str_CRITICALITY_NAME, $str_CRITICALITY_DESC, $str_CRITICALITY_SID);
		Support_GetDictionaryInfo($str_STATUS_ID, $str_STATUS_NAME, $str_STATUS_DESC, $str_STATUS_SID);
		Support_GetDictionaryInfo($str_MARK_ID, $str_MARK_NAME, $str_MARK_DESC, $str_MARK_SID);
		Support_GetDictionaryInfo($str_SOURCE_ID, $str_SOURCE_NAME, $str_SOURCE_DESC, $str_SOURCE_SID);*/

		$arDictionary = Array(
				"CATEGORY" => $GLOBALS["str_CATEGORY_ID"],
				"CRITICALITY" => $GLOBALS["str_CRITICALITY_ID"],
				"STATUS" => $GLOBALS["str_STATUS_ID"],
				"MARK" => $GLOBALS["str_MARK_ID"],
				"SOURCE" => $GLOBALS["str_SOURCE_ID"]
			);

		_Support_GetDictionaryInfoEx($arDictionary);


		Support_GetSLAInfo($str_SLA_ID, $str_SLA_NAME, $str_SLA_DESCRIPTION);
	}
}
$str_HIDDEN = COption::GetOptionString("support","DEFAULT_VALUE_HIDDEN");
$str_NOTIFY = COption::GetOptionString("support","DEFAULT_VALUE_NOTIFY");
$srt_NOT_CHANGE_STATUS = "";

if($e = $APPLICATION->GetException())
{
	$message = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
	$DB->InitTableVarsForEdit("b_ticket", "", "str_");
}
if ($ID>0) $sDocTitle = GetMessage("SUP_EDIT_RECORD", array("#ID#" => $ID, "#TITLE#" => htmlspecialcharsback($str_TITLE)));
else $sDocTitle = GetMessage("SUP_NEW_RECORD");

if (($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y") && $str_IS_SPAM <> '')
{
	if ($str_IS_SPAM=="Y")
		$sDocTitle .= " [".GetMessage("SUP_SPAM")."!]";
	else
		$sDocTitle .= " [".GetMessage("SUP_SPAM")."?]";
}

$APPLICATION->SetTitle($sDocTitle);

if ($ADD_PUBLIC_CHAIN=="Y" || !isset($ADD_PUBLIC_CHAIN))
{
	$APPLICATION->AddChainItem(GetMessage("SUP_RECORDS_LIST"), $TICKET_LIST_URL);
}

$VIEW_TICKET_DEFAULT_MODE = COption::GetOptionString("support", "VIEW_TICKET_DEFAULT_MODE");
$DEFAULT_AUTO_CLOSE_DAYS = COption::GetOptionString("support", "DEFAULT_AUTO_CLOSE_DAYS");
$ONLINE_AUTO_REFRESH = COption::GetOptionString("support", "ONLINE_AUTO_REFRESH");

$str_AUTO_CLOSE_DAYS = $str_AUTO_CLOSE_DAYS <> '' ? $str_AUTO_CLOSE_DAYS : $DEFAULT_AUTO_CLOSE_DAYS;

$bResponsible = $bSupportTeam;

$can_select_message_owner = "N";
$can_select_owner = "N";
$can_select_site = "N";
$can_select_sla = "N";
$can_select_category = "N";
$can_select_status = "N";
$can_select_difficulty = "N";
$can_select_responsible = "N";
$can_select_criticality = "N";
$can_select_mark = "N";
$can_select_mode = "N";

$default_mode = "edit";
if ($VIEW_TICKET_DEFAULT_MODE <> '') $default_mode = $VIEW_TICKET_DEFAULT_MODE;

if ($ID>0)
{
	if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
	{
		$can_select_message_owner = "Y";
		$can_select_owner = "Y";
		$can_select_sla = "Y";
		$can_select_site = "Y";
		$can_select_category = "Y";
		$can_select_status = "Y";
		$can_select_difficulty = "Y";
		$can_select_responsible = "Y";
		$can_select_criticality = "Y";

		if ($str_DATE_CLOSE == '' && $VIEW_TICKET_DEFAULT_MODE <> '')
			$can_select_mode = "Y";
	}
	if ($bOwner=="Y")
	{
		$can_select_criticality = "Y";
		$can_select_mark = "Y";
	}
}

if ($ID<=0)
{
	if (defined("ADMIN_SECTION") && ADMIN_SECTION==true) $can_select_site = "Y";
	if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
	{
		$can_select_owner = "Y";
		$can_select_sla = "Y";
		$can_select_category = "Y";
		$can_select_status = "Y";
		$can_select_difficulty = "Y";
		$can_select_responsible = "Y";
		$can_select_criticality = "Y";
	}
	if ($bOwner=="Y")
	{
		$can_select_category = "Y";
		$can_select_criticality = "Y";
	}
}
if ($can_select_sla=="Y")
{
	$TICKET_SLA = (intval($str_SLA_ID)>0) ? $str_SLA_ID : 1;
	$arrSlaRef = array();
	$arrSlaID = array();
	$rs = CTicketSLA::GetDropDown();
	while ($ar = $rs->Fetch())
	{
		$arrSlaRef[] = $ar["REFERENCE"];
		$arrSlaID[] = $ar["ID"];
	}
}
else $TICKET_SLA = (intval($str_SLA_ID)>0) ? $str_SLA_ID : CTicketSLA::GetForUser($TICKET_SITE);
$arrSUPPORT_TEAM = array();

$TICKET_DICTIONARY = CTicketDictionary::GetDropDownArray($TICKET_SITE, $TICKET_SLA);
$TICKET_DICTIONARY_ALL = CTicketDictionary::GetDropDownArray();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<?
$aMenu = array(
	array(

		"TEXT" => GetMessage("MAIN_ADMIN_MENU_LIST"),
		"TITLE" => GetMessage("SUP_RECORDS_LIST"),
		"LINK" => "/bitrix/admin/ticket_list.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list",
	),
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"TITLE" => GetMessage("SUP_CREATE_NEW_TICKET"),
		"ICON" => "btn_new",
		"LINK"	=> "/bitrix/admin/ticket_edit.php?lang=".LANGUAGE_ID,
		);

	if ($str_DATE_CLOSE == '')
	{
		$aMenu[] = array(
			//"ICON" => "btn_close",
			"TEXT"	=> GetMessage("MAIN_ADMIN_MENU_CLOSE"),
			"TITLE"	=> GetMessage("SUP_CLOSE_TICKET"),
			"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=close&lang=".LANGUAGE_ID."&".bitrix_sessid_get()
			);
	}
	else
	{
		$aMenu[] = array(
			//"ICON" => "btn_open",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_OPEN"),
			"TITLE"	=> GetMessage("SUP_OPEN_TICKET"),
			"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=open&lang=".LANGUAGE_ID."&".bitrix_sessid_get()
			);
	}

	if ($bSupportTeam=="Y" || $bAdmin=="Y")
	{
		//$aMenu[] = array("NEWBAR"=>"Y");
		$arSpamMenu = Array();

		if ($str_IS_SPAM <> '')
		{
			$arSpamMenu[] = array(
				"TEXT"	=> GetMessage("SUP_UNMARK_TICKET"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_edit.php?ID=".$ID."&action=unmark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';"
				);
		}

		if ($str_IS_SPAM!="N")
		{
			$arSpamMenu[] = array(
				"TEXT"	=> GetMessage("SUP_MARK_AS_POSSIBLE_SPAM"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_edit.php?ID=".$ID."&action=maybe_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';"
				);
		}

		if ($str_IS_SPAM!="Y" && $bAdmin=="Y")
		{
			$arSpamMenu[] = array(
				"TEXT"	=> GetMessage("SUP_MARK_AS_SPAM"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_edit.php?ID=".$ID."&action=mark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';"
				);
		}

		if ($bAdmin=="Y" || $bDemo=="Y")
		{
			$arSpamMenu[] = array(
				"TEXT"	=> GetMessage("SUP_MARK_AS_SPAM_DELETE"),
				"ACTION"	=> "javascript:if(confirm('".GetMessage("SUP_MARK_AS_SPAM_DELETE_CONFIRM")."')) window.location='/bitrix/admin/ticket_list.php?ID=".$ID."&action=mark_spam_delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
				);

			$aMenu[] = array(
				"TEXT"	=> GetMessage("MAIN_ADMIN_MENU_DELETE"),
				"TITLE"	=> GetMessage("SUP_DELETE_TICKET"),
				"ICON" => "btn_delete",
				"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_DELETE_TICKET_CONFIRM")."')) window.location='/bitrix/admin/ticket_list.php?ID=".$ID."&action=delete&redirectafter=Y&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
				);
		}

		if (!empty($arSpamMenu))
		{
			$aMenu[] = array(
				"TEXT" => GetMessage("SUP_MENU_SPAM"),
				"TITLE" => GetMessage("SUP_MENU_SPAM_TITLE"),
				"MENU" => $arSpamMenu,
			);
		}

	}
}
//echo ShowSubMenu($aMenu);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
{
	echo $message->Show();
	$messageA = $message->GetMessages();
}


/***************************************************************************
									HTML form
****************************************************************************/

$hkInst=CHotKeys::getInstance();

$arHK = array("B", "I", "U", "QUOTE", "CODE", "TRANSLIT");
foreach($arHK as $n => $s)
{		
	$arExecs = $hkInst->GetCodeByClassName("TICKET_EDIT_$s");
	echo $hkInst->PrintJSExecs($arExecs);
}

?>

<script language="JavaScript">
<!--
function htmlspecialcharsback(str)
{
	str = str.replace(/&quot;/g, "\"");
	str = str.replace(/&lt;/g, "<");
	str = str.replace(/&gt;/g, ">");
	str = str.replace(/&amp;/g, "&");
	return str;
}

function in_array(needle, haystack)
{
	for(k=0; k<haystack.length; k++) if (needle==haystack[k][0]) return true;
	return false;
}
//-->
</script>
<?
/***************************************************************************
								SPLIT MESSAGE
****************************************************************************/
if (isset($_GET['TICKET_ID']) && isset($_GET['MESSAGE_ID'])) 
{
	$_SESSION["TICKET_ID"] = intval($_GET['TICKET_ID']);
	$_SESSION["MESSAGE_ID"] = intval($_GET['MESSAGE_ID']);
	
	$ticket = CTicket::GetByID($_SESSION['TICKET_ID'], $site_id, "Y", $get_user_name, $get_extra_names);
	if ($ticket && $ticket->ExtractFields())
	{
		$obUserTiket = $USER->GetByID($str_OWNER_USER_ID);
		$arUserTiket = $obUserTiket->Fetch();
		$str_OWNER_LOGIN = htmlspecialcharsbx($arUserTiket['LOGIN']);
		$str_OWNER_NAME = htmlspecialcharsbx($arUserTiket['NAME']).' '.htmlspecialcharsbx($arUserTiket['LAST_NAME']);
		$str_lang = $TICKET_SITE = $str_SITE_ID;
		$TICKET_SLA = $str_SLA_ID = CTicketSLA::GetForUser($str_SITE_ID, $str_OWNER_USER_ID); 
		$str_DIFFICULTY_ID = '';
		$str_CRITICALITY_ID = '';
		$obTicketMessage = CTicket::GetMessageByID($_SESSION['MESSAGE_ID']);
		$arTicketMessage = $obTicketMessage->Fetch();
		$MESSAGE = $arTicketMessage['MESSAGE'];
		$_SESSION['MESSAGE_NUM']  = intval($arTicketMessage['C_NUMBER']);
		$_SESSION['MESSAGE_DATE'] = $arTicketMessage['DATE_CREATE'];
		$_SESSION["TICKET_TITLE"] = $str_TITLE;
		$str_TITLE = '';
		$str_DATE_CLOSE = null;
		$arFiles = array();
		if ($rsFiles = CTicket::GetFileList("s_id", "asc", array("MESSAGE_ID" => $_SESSION['MESSAGE_ID'])))
		{
			while ($arFile = $rsFiles->Fetch())
			{
				$name = $arFile["ORIGINAL_NAME"];
				if ($arFile["EXTENSION_SUFFIX"] <> '')
				{
					$suffix_length = mb_strlen($arFile["EXTENSION_SUFFIX"]);
					$name = mb_substr($name, 0, mb_strlen($name) - $suffix_length);
				}
				$arFile["NAME"] = $name;
				$arFiles[] = $arFile;
			}
		}
	}
}

?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<!-- <input type="hidden" name="set_default" value="Y"> -->
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
	
	$aTabs = array();
	$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SUP_RECORD"), "ICON"=>"ticket_edit",
	"TITLE"=>($ID>0 && trim($str_TITLE) <> '' ? $str_TITLE : $APPLICATION->GetTitle())
	);
	//$aTabs[] = $USER_FIELD_MANAGER->EditFormTab($PROPERTY_ID);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();

?>
	<?if ($can_select_site=="Y"):?>
	<tr valign="middle">
		<td align="right" width="20%" nowrap><?=GetMessage("SUP_SITE")?></td>
		<td width="80%" nowrap><?echo SelectBoxFromArray("SITE_ID", array("reference" => $arrSiteRef, "reference_id" => $arrSiteID), htmlspecialcharsbx($TICKET_SITE), "", "onChange=\"OnSiteChange(this[this.selectedIndex].value)\" id=\"SITE_ID\"");?></td>
	</tr>
	<script language="JavaScript">
	<!--
	var arSLA = Array();
	var arStatus = Array();
	var arCriticality = Array();
	var arCategory = Array();
	var arMark = Array();
	var arSource = Array();
	var arDifficulty = Array();
	<?
	if (is_array($arrSiteID))
	{
		$arrSiteID = array_unique($arrSiteID);
		if ($can_select_sla=="Y")
		{
			$arSLA = array();
			$strSite = implode("|", $arrSiteID);
			$arSort = array("FIRST_SITE_ID" => "ASC", "PRIORITY" => "ASC");
			$is_filtered = null;
			$rs = CTicketSLA::GetList($arSort, array("SITE" => $strSite), $is_filtered);
			while($ar=$rs->Fetch())
			{
				$arSLA[$ar["ID"]] = $ar;
			}
			$arSiteSLA = CTicketSLA::GetSiteArrayForAllSLA(CTicketSLA::SITE_SLA);
		}

		$allDictionary = CTicketDictionary::GetDropDownArray($sid);

		foreach($arrSiteID as $sid)
		{

			?>
	arSLA["<? echo $sid; ?>"]=Array(<?
			if(isset($arSiteSLA[$sid]))
			{
				$c0 = "";
				foreach($arSiteSLA[$sid] as $key=>$cSlaID)
				{
					echo $c0 . "Array('". addslashes(htmlspecialcharsbx($arSLA[$cSlaID]["REFERENCE_ID"])) . "', '" . addslashes(htmlspecialcharsbx($arSLA[$cSlaID]["REFERENCE"])) . "')";
					$c0 = ", ";
				}
			}
			?>);

			<?

			/*?>
				arSLA["<?=$sid?>"]=Array(<?
			$rs = CTicketSLA::GetDropDown($sid);
			$i=0;
			while($ar=$rs->Fetch())
			{
				$i++;
				if ($i>1) echo ", ";
				echo "Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
			}
			?>);
			<?*/

			if ($can_select_status=="Y")
			{
				?>
				arStatus["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($allDictionary['S']))
					{
						foreach ($allDictionary['S'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
			if ($can_select_difficulty=="Y")
			{
				?>
				arDifficulty["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($allDictionary['D']))
					{
						foreach ($allDictionary['D'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
			if ($can_select_category=="Y")
			{
				?>
				arCategory["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($allDictionary['C']))
					{
						foreach ($allDictionary['C'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
			if ($can_select_mark=="Y")
			{
				?>
				arMark["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($allDictionary['M']))
					{
						foreach ($allDictionary['M'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
			if ($can_select_criticality=="Y")
			{
				?>
				arCriticality["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($allDictionary['K']))
					{
						foreach ($allDictionary['K'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
			if ($can_select_message_owner=="Y" || $can_select_owner=="Y")
			{
				?>
				arSource["<?=$sid?>"]=Array(<?

					echo "Array('NOT_REF', '< web >')";

					if (isset($allDictionary['SR']))
					{
						foreach ($allDictionary['SR'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			}
		}
	}
	?>

	function OnSiteChange(site_id)
	{
		var select_index;
		var arrList = Array();
		var arrValues = Array();
		var arrInit = Array();

		<?if ($can_select_sla=="Y") : ?>
			arrList[arrList.length] = document.form1.SLA_ID;
			arrValues[arrValues.length] = arSLA;
			arrInit[arrInit.length] = parseInt('<?=$str_SLA_ID?>');
		<?endif;?>

		<?if ($can_select_status=="Y") : ?>
			arrList[arrList.length] = document.form1.STATUS_ID;
			arrValues[arrValues.length] = arStatus;
			arrInit[arrInit.length] = parseInt('<?=$str_STATUS_ID?>');
		<?endif;?>

		<?if ($can_select_difficulty=="Y") : ?>
			arrList[arrList.length] = document.form1.DIFFICULTY_ID;
			arrValues[arrValues.length] = arDifficulty;
			arrInit[arrInit.length] = parseInt('<?=$str_DIFFICULTY_ID?>');
		<?endif;?>

		<?if ($can_select_category=="Y") :?>
			arrList[arrList.length] = document.form1.CATEGORY_ID;
			arrValues[arrValues.length] = arCategory;
			arrInit[arrInit.length] = parseInt('<?=$str_CATEGORY_ID?>');
		<?endif;?>

		<?if ($can_select_mark=="Y") :?>
			arrList[arrList.length] = document.form1.MARK_ID;
			arrValues[arrValues.length] = arMark;
			arrInit[arrInit.length] = parseInt('<?=$str_MARK_ID?>');
		<?endif;?>

		<?if ($can_select_criticality=="Y") :?>
			arrList[arrList.length] = document.form1.CRITICALITY_ID;
			arrValues[arrValues.length] = arCriticality;
			arrInit[arrInit.length] = parseInt('<?=$str_CRITICALITY_ID?>');
		<?endif;?>

		<?if ($can_select_owner=="Y") :?>
			arrList[arrList.length] = document.form1.SOURCE_ID;
			arrValues[arrValues.length] = arSource;
			arrInit[arrInit.length] = parseInt('<?=$str_SOURCE_ID?>');
		<?endif;?>

		<?if ($can_select_message_owner=="Y") :?>
			arrList[arrList.length] = document.form1.MESSAGE_SOURCE_ID;
			arrValues[arrValues.length] = arSource;
			arrInit[arrInit.length] = parseInt('<?=$str_MESSAGE_SOURCE_ID?>');
		<?endif;?>

		for(i=0; i<arrList.length; i++)
		{
			arList = arrList[i];
			arValues = arrValues[i][site_id];
			select_index = 0;
			while(arList.length>0) arList.options[0]=null;
			for(j=0; j<arValues.length; j++)
			{
				newoption = new Option(htmlspecialcharsback(arValues[j][1]), arValues[j][0], false, false);
				arList.options[j] = newoption;
				if (newoption.value==arrInit[i]) select_index = j;
			}
			if (parseInt(select_index)>0) arList.selectedIndex = parseInt(select_index);
		}

		<?if ($can_select_sla=="Y"):?>
		var obSLASelect, sla_id;
		obSLASelect = document.form1.SLA_ID;
		if(obSLASelect.selectedIndex >= 0)
		{
			sla_id = obSLASelect[obSLASelect.selectedIndex].value;
			OnSLAChange(sla_id);
		}
		<?endif;?>
	}
	//-->
	</script>
	<?endif;?>

	<?
	
	$arAuthorFilter = $tmp = array();
	if (intval($str_OWNER_USER_ID)>0)
	{
		//$tmp[] = htmlspecialcharsback($str_OWNER_LOGIN);
		$tmp[] = $arStrUsers["arUsers"][intval($str_OWNER_USER_ID)]["LOGIN"];
	}
	if ($str_OWNER_SID <> '')
	{
		$tmp[] = htmlspecialcharsback($str_OWNER_SID);
	}
	else
	{
		$arAuthorFilter[] = "find_owner_exact_match=Y";
	}
	$arAuthorFilter[] = "find_owner=".urlencode(implode(" | ",$tmp));

	if ($can_select_owner=="Y"):
	?>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	function SelectSource()
	{
		var objSourceSelect, strSourceValue;
		objSourceSelect = document.form1.SOURCE_ID;

		strSourceValue = objSourceSelect[objSourceSelect.selectedIndex].value;
		document.getElementById("OWNER_SID").style.display = "none";
		document.getElementById("OWNER_SID").disabled = true;
		if (strSourceValue!="")
		{
			document.getElementById("OWNER_SID").disabled = false;
			document.getElementById("OWNER_SID").style.display = "inline";
		}
	}
	//-->
	</SCRIPT>
	<tr>
		<td align="right" width="20%" nowrap><?=GetMessage("SUP_AUTHOR")?></td>
		<td width="80%" nowrap><?
			//echo SelectBox("SOURCE_ID", CTicketDictionary::GetDropDown("SR", $TICKET_SITE), "< web >", $str_SOURCE_ID, "OnChange=SelectSource() class='typeselect'");

			echo SelectBoxFromArray("SOURCE_ID", __GetDropDown("SR", $TICKET_DICTIONARY), $str_SOURCE_ID, "< web >", "OnChange=SelectSource() class='inputselect'");


			?>&nbsp;<input type="text" size="20" name="OWNER_SID" id="OWNER_SID" value="<?=$str_OWNER_SID?>">
			<?
			/*if (intval($str_OWNER_USER_ID)>0)
			{
				$owner_name = "[<a title=\"".GetMessage("SUP_USER_PROFILE")."\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$str_OWNER_USER_ID."\">".$str_OWNER_USER_ID."</a>] (".$str_OWNER_LOGIN.") ".$str_OWNER_NAME;
			}
			*/
			if(intval($str_OWNER_USER_ID)>0)
			{
				$owner_name = $arStrUsers["arUsers"][intval($str_OWNER_USER_ID)]["HTML_NAME"];
			}
			echo FindUserID("OWNER_USER_ID", $str_OWNER_USER_ID, $owner_name);
			if ($ID>0):
				?><br>[&nbsp;<a href="/bitrix/admin/ticket_list.php?set_filter=Y&lang=<?=LANGUAGE_ID?>&<?=implode("&",$arAuthorFilter)?>"><?=GetMessage("SUP_AUTHOR_TICKETS")?></a>&nbsp;]<?
			endif;
			?>
		</td>
	</tr>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	SelectSource();
	//-->
	</SCRIPT>
	<?elseif ($ID>0) :?>
	<tr>
		<td valign="top" align="right" width="20%" nowrap><?=GetMessage("SUP_AUTHOR")?></td>
		<td width="80%" nowrap><?

		echo ($str_SOURCE_NAME <> '') ? "[".$str_SOURCE_NAME."]&nbsp;" : "[web]&nbsp;";

		if ($str_OWNER_SID <> '')
		{
			echo TxtToHtml($str_OWNER_SID)."&nbsp;";
			if (intval($str_OWNER_USER_ID)>0) echo "/&nbsp;";
		}

		$uid = $str_OWNER_USER_ID;
		if ($uid>0 && !in_array($uid, array_keys($arrSUPPORT_TEAM)))
		{
			$arrSUPPORT_TEAM[$uid] = (CTicket::IsSupportTeam($uid) || CTicket::IsAdmin($uid)) ? "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)" : "";
		}

		if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y")
		{
			if (intval($str_OWNER_USER_ID)>0)
			{
				/*?>[<a title="<?echo GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?echo LANG?>&ID=<?=$str_OWNER_USER_ID?>"><?echo $str_OWNER_USER_ID?></a>]  (<?=$str_OWNER_LOGIN?>) <?=$str_OWNER_NAME?> */
				echo $arStrUsers["arUsers"][intval($str_OWNER_USER_ID)]["HTML_NAME"] . " " . $arrSUPPORT_TEAM[$str_OWNER_USER_ID];
			}

			if (intval($str_OWNER_GUEST_ID)>0 && CModule::IncludeModule("statistic"))
			{
				/*echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=".$str_OWNER_GUEST_ID."&find_id_exact_match=Y&set_filter=Y'>".$str_OWNER_GUEST_ID."</a>]";*/
				echo $arStrUsers["arGuests"][intval($str_OWNER_GUEST_ID)]["HTML_NAME"];
			}
		}
		else
		{
			if (intval($str_OWNER_USER_ID)>0)
			{
				/*echo "[".$str_OWNER_USER_ID."] (".$str_OWNER_LOGIN.") ".$str_OWNER_NAME." ".$arrSUPPORT_TEAM[$str_OWNER_USER_ID]."";*/
				echo $arStrUsers["arUsers"][intval($str_OWNER_USER_ID)]["HTML_NAME_S"];
			}

		}

		?></td>
	</tr>
	<?endif;?>

<? if ($ID>0): ?>

	<tr valign="middle">
		<td align="right" width="20%"><?=GetMessage("SUP_CREATE")?></td>
		<td align="left" width="80%"><?=$str_DATE_CREATE?>&nbsp;&nbsp;&nbsp;<?
		if ($str_CREATED_MODULE_NAME == '' || $str_CREATED_MODULE_NAME=="support")
		{
			$uid = intval($str_CREATED_USER_ID);
			if ($uid>0 && !in_array($uid, array_keys($arrSUPPORT_TEAM)))
			{
				$arrSUPPORT_TEAM[$uid] = (CTicket::IsSupportTeam($uid) || CTicket::IsAdmin($uid)) ? "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)" : "";
			}

			if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y")
			{

				/*?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$str_CREATED_USER_ID?>"><?echo $str_CREATED_USER_ID?></a>] (<?=$str_CREATED_LOGIN?>) <?=$str_CREATED_NAME?> <?=$arrSUPPORT_TEAM[$str_CREATED_USER_ID]?><?*/

				if ($uid <= 0 && intval($str_CREATED_GUEST_ID) > 0 && CModule::IncludeModule("statistic"))
				{
					/*echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=". $str_CREATED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y' class='tablebodylink'>".$str_CREATED_GUEST_ID."</a>]";*/
					echo $arStrUsers["arGuests"][intval($str_CREATED_GUEST_ID)]["HTML_NAME"];
				}
				elseif($uid > 0)
				{
					echo $arStrUsers["arUsers"][$uid]["HTML_NAME"] . " " . $arrSUPPORT_TEAM[$uid];
				}
			}
			else
			{
				echo $arStrUsers["arUsers"][$uid]["HTML_NAME_S"];
				/*echo "[".$str_CREATED_USER_ID."] (".$str_CREATED_LOGIN.") ".$str_CREATED_NAME." ".$arrSUPPORT_TEAM[$str_CREATED_USER_ID]."";*/
			}
		}else{
			echo $str_CREATED_MODULE_NAME;
		}
		?></td>
	</tr>

	<?
	if ($str_DATE_CREATE!=$str_TIMESTAMP_X)
	{
	?>
	<tr valign="middle">
		<td align="right" width="20%"><?=GetMessage("SUP_TIMESTAMP")?></td>
		<td align="left" width="80%"><?=$str_TIMESTAMP_X?>&nbsp;&nbsp;&nbsp;<?

		if ($str_MODIFIED_MODULE_NAME == '' || $str_MODIFIED_MODULE_NAME=="support")
		{
			$uid = intval($str_MODIFIED_USER_ID);
			if ($uid>0 && !in_array($uid, array_keys($arrSUPPORT_TEAM)))
			{
				$arrSUPPORT_TEAM[$uid] = (CTicket::IsSupportTeam($uid) || CTicket::IsAdmin($uid)) ? "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)" : "";
			}

			if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y")
			{

				/*?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?echo $str_MODIFIED_USER_ID?>"><?=$str_MODIFIED_USER_ID?></a>] (<?=$str_MODIFIED_BY_LOGIN?>) <?=$str_MODIFIED_BY_NAME?> <?=$arrSUPPORT_TEAM[$str_MODIFIED_USER_ID]?><?*/

				if($uid <= 0 && intval($str_MODIFIED_GUEST_ID)>0 && CModule::IncludeModule("statistic"))
				{
					echo $arStrUsers["arGuests"][intval($str_MODIFIED_GUEST_ID)]["HTML_NAME"];
					/*echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=".$str_MODIFIED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y' >".$str_MODIFIED_GUEST_ID."</a>]";*/
				}
				elseif($uid > 0)
				{
					echo $arStrUsers["arUsers"][$uid]["HTML_NAME"] . " " . $arrSUPPORT_TEAM[$uid];
				}

			}
			else
			{
				echo $arStrUsers["arUsers"][$uid]["HTML_NAME_S"];
				/*echo "[".$str_MODIFIED_USER_ID."] (".$str_MODIFIED_BY_LOGIN.") ".$str_MODIFIED_BY_NAME." ".$arrSUPPORT_TEAM[$str_MODIFIED_USER_ID]."";*/
			}
		}else{
			echo $str_MODIFIED_MODULE_NAME;
		}
		?></td>
	</tr>
	<?
	}

	if ($str_DATE_CLOSE <> '')
	{
	?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_CLOSE")?></td>
		<td><?=$str_DATE_CLOSE?></td>
	</tr>
	<?
	}
	elseif($str_AUTO_CLOSE_DAYS_LEFT <> '' && !empty($str_AUTO_CLOSE_DATE))
	{
	?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_DATE_AUTO_CLOSE")?></td>
		<td><?=$str_AUTO_CLOSE_DATE?>&nbsp;&nbsp;&nbsp;(<?=str_replace("#DAYS#", "<span class=\"supportrequired\">$str_AUTO_CLOSE_DAYS_LEFT</span>", GetMessage("SUP_LEFT"))?>)</td>
	</tr>
	<?
	}
	?>


<?if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y"){?>

	<?if (intval($str_PROBLEM_TIME)>0){?>
		<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_PROBLEM_TIME")?>:</td>
		<td>
		<?
		$str = "";
		$days = intval($str_PROBLEM_TIME/1440);
		if ($days>0)
		{
			$str .= $days."&nbsp;".GetMessage("SUP_DAYS")." ";
			$str_PROBLEM_TIME = $str_PROBLEM_TIME - $days*1440;
		}

		$hours = intval($str_PROBLEM_TIME/60);
		if ($hours>0)
		{
			$str .= $hours."&nbsp;".GetMessage("SUP_HOURS")." ";
			$str_PROBLEM_TIME = $str_PROBLEM_TIME - $hours*60;
		}

		$str .= ($str_PROBLEM_TIME%60)."&nbsp;".GetMessage("SUP_MINUTES");
		echo $str;
		?>
		</td>
	</tr>
	<?}?>

	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_LAST_MESSAGE_DATE")?>:</td>
		<td><?=$str_LAST_MESSAGE_DATE?></td>
	</tr>
<?}?>

<? endif;?>
	<?if ($ID>0 && IsModuleInstalled("sale")): 
		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
		if ($saleModulePermissions > "D"):?>
			<tr>
				<td valign="top" align="right" width="20%" nowrap><?=GetMessage("SUP_SALE_ORDER")?></td>
				<td width="80%" nowrap>[ <a href="/bitrix/admin/sale_order.php?lang=<?=LANGUAGE_ID?>&set_filter=Y&filter_user_id=<?=$str_CREATED_USER_ID?>" target="_blank"><?=GetMessage("SUP_SALE_ORDER_LIST")?></a> ]</td>
			</tr>
	<?  endif;
	endif;?>
	<?if($can_select_sla=="N" && $str_SLA_NAME <> ''){?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_SLA")?>:</td>
		<td><font title="<?=$str_SLA_DESCRIPTION?>"><?=$str_SLA_NAME?></td>
	</tr>
	<?}?>

	<?if ($can_select_category=="N" && $str_CATEGORY_NAME <> ''){?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_CATEGORY")?></td>
		<td><font title="<?=$str_CATEGORY_DESC?>"><?=$str_CATEGORY_NAME?></td>
	</tr>
	<?}?>

	<?if($can_select_criticality=="N" && $str_CRITICALITY_NAME <> ''){?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_CRITICALITY")?></td>
		<td><?=$str_CRITICALITY_NAME?></td>
	</tr>
	<?}?>

	<?if ($can_select_difficulty=="N" && $str_DIFFICULTY_NAME <> ''){?>
	<tr valign="middle">
		<td align="right" nowrap><?=GetMessage("SUP_DIFFICULTY_COLNAME")?></td>
		<td nowrap><font title="<?=$str_DIFFICULTY_DESC?>"><?=$str_DIFFICULTY_NAME?></td>
	</tr>
	<?}?>

	<?if ($can_select_status=="N" && $str_STATUS_NAME <> ''){?>
	<tr valign="middle">
		<td align="right" nowrap><?=GetMessage("SUP_STATUS")?></td>
		<td nowrap><font title="<?=$str_STATUS_DESC?>"><?=$str_STATUS_NAME?></td>
	</tr>
	<?}?>

	<?
	if ($can_select_responsible=="N" && intval($str_RESPONSIBLE_USER_ID)>0) {
		$uid = $str_RESPONSIBLE_USER_ID;
		if ($uid>0 && !in_array($uid, array_keys($arrSUPPORT_TEAM)))
		{
			$arrSUPPORT_TEAM[$uid] = (CTicket::IsSupportTeam($uid) || CTicket::IsAdmin($uid)) ? "(<span class='supportrequired'>".GetMessage("SUP_TECHSUPPORT").")</span>" : "";
		}
	?>
	<tr valign="middle">
		<td align="right" nowrap><?=GetMessage("SUP_RESPONSIBLE")?></td>
		<? /*<td nowrap><?echo "[".$str_RESPONSIBLE_USER_ID."] (".$str_RESPONSIBLE_LOGIN.") ".$str_RESPONSIBLE_NAME." ".$arrSUPPORT_TEAM[$str_RESPONSIBLE_USER_ID]?></td>*/ ?>
		<td nowrap><?
			echo $arStrUsers["arUsers"][intval($str_RESPONSIBLE_USER_ID)]["HTML_NAME"] . " " . $arrSUPPORT_TEAM[$str_RESPONSIBLE_USER_ID];
		?></td>
	</tr>
	<?}?>

	<?if($can_select_mark=="N" && $str_MARK_NAME <> ''){?>
	<tr valign="middle">
		<td align="right" nowrap><?=GetMessage("SUP_MARK")?></td>
		<td nowrap><font title="<?=htmlspecialcharsbx($str_MARK_DESC)?>"><?=htmlspecialcharsbx($str_MARK_NAME)?></td>
	</tr>
	<?}?>

	<?if ($ID>0 && intval($str_OVERDUE_MESSAGES)>0 && ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")){?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_OVERDUE_MESSAGES")?></td>
		<td><?=$str_OVERDUE_MESSAGES?></td>
	</tr>
	<?}?>

	<?if ($ID<=0){?>
	<tr class="adm-detail-required-field">
		<td align="right"><?=GetMessage("SUP_TITLE")?></td>
		<td><input type="text" name="TITLE" value="<?=$str_TITLE?>" size="80" maxlength="255"></td>
	</tr>
	<?}?>

	<?if ($ID > 0 && $str_COUPON <> ''){?>
	<tr valign="middle">
		<td align="right"><?=GetMessage("SUP_COUPON")?></td>
		<td><?=$str_COUPON?></td>
	</tr>
	<?}?>

	<?
	if ($ID>0) :

	if ($bDemo=="Y") $CHECK_RIGHTS = "N"; else $CHECK_RIGHTS = "Y";
	$mess = CTicket::GetMessageList('', '', array("TICKET_ID" => $ID, "TICKET_ID_EXACT_MATCH" => "Y"), null, $CHECK_RIGHTS, $get_user_name);
	$mess->NavStart(COption::GetOptionString("support", "MESSAGES_PER_PAGE", 50));
	//$mess->NavStart(5);
	$messages = $mess->SelectedRowsCount();
	if (intval($messages)>0) :
	?>

	<?if ($ID>0){?>
	<tr class="heading"><td colspan="2"><?=GetMessage("SUP_DISCUSSION")?></td></tr>
	<?}?>

	<tr valign="top">
		<td colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td></td>
					<td><?echo $mess->NavPrint(GetMessage("SUP_PAGES"))?></td>
				</tr>
				<?
				//while ($mess->NavNext(true, "f_", false))
				$arRespUserIDs = array();
				$arGuestIDs = array();
				$arM0 = array();
				while($arCM = $mess->Fetch())
				{
					$arRespUserIDs[] = intval($arCM["OWNER_USER_ID"]);
					$arRespUserIDs[] = intval($arCM["CREATED_USER_ID"]);
					$arGuestIDs[] = intval($arCM["OWNER_GUEST_ID"]);
					$arGuestIDs[] = intval($arCM["CREATED_GUEST_ID"]);
					$arM0[] = $arCM;
				}

				$arStrUsersM = CTicket::GetUsersPropertiesArray($arRespUserIDs, $arGuestIDs);

				foreach($arM0 as $key => $arM)
				{
					$backcolor = "background-color:#F1F1F1;";
					$table_class = "class=\"defaultborder\"";
					$headerbackcolor = "background-color:#FFF;";
					$bottomline = "background:#ACAAA7;";

					if($arM["IS_OVERDUE"] == "Y")
					{
						$backcolor = "background-color:#FFECEC;";
						$table_class = "class=\"overdueborder\"";
						$headerbackcolor = "background-color:#FFECEC;";
						$bottomline = "";
					}
					elseif($arM["IS_HIDDEN"] == "Y")
					{
						$backcolor = "background-color:#F1F8F5;";
						$table_class = "class=\"hiddenborder\"";
						$headerbackcolor = "background-color:#F1F8F5";
						$bottomline = "";
					}
					elseif($arM["IS_LOG"] == "Y")
					{
						$backcolor = "background-color:#FDF9E1;";
						$table_class = "class=\"logborder\"";
						$headerbackcolor = "background-color:#FDF9E1;";
						$bottomline = "";
					}

					?>
				<tr>
					<td align="center" colspan="2" width="100%" style="padding:6px 3px;">
						<table border="0" cellspacing="0" cellpadding="0" <? echo $table_class; ?> width="100%" >
							<tr>
								<td style="<? echo $backcolor; ?>" width="100%">
									<table border="0" width="100%" cellspacing="0" cellpadding="0" class="wd-ticket-message">

										<tr>
											<td style="padding:4px;text-align:left;<? echo $backcolor; ?>"><b>#&nbsp;<? echo intval($arM["C_NUMBER"]); ?></b>&nbsp;&nbsp;&nbsp;<? echo $arM["DATE_CREATE"]; ?></td>
											<td align="right"  style="padding:4px;<?=$backcolor?>"><?

												$bSep = true;
												//if($str_OWNER_USER_ID == $arM["OWNER_USER_ID"])
												if($arM["IS_LOG"] != "Y")
												{
													?>
													<a title="<? echo GetMessage("SUP_SPLIT_ALT"); ?>" href="<? echo $TICKET_EDIT_URL; ?>?lang=<? echo LANGUAGE_ID; ?>&TICKET_ID=<?=$ID?>&MESSAGE_ID=<? echo $arM["ID"]; ?>" onclick="return (confirm('<? echo AddSlashes(GetMessage("SUP_SPLIT_CONFIRM")); ?>') ? true : false)"><? echo GetMessage("SUP_SPLIT"); ?></a>
													<?
													if($bSep)
														echo " | ";
												}
												$bSep = false;
												if ($str_DATE_CLOSE == ''):
													echo '<a href="#postform" OnMouseDown="javascript:SupQuoteMessage(\'quotetd' . $arM["ID"] . '\')" title="' . GetMessage("SUP_QUOTE_LINK_DESCR") . '">' . GetMessage("SUP_QUOTE_LINK") . '</a>';
													$bSep = true;
												endif;

												if ($bAdmin=="Y" || $bDemo=="Y") :

													if (intval($arM["SOURCE_ID"])>0 && IsModuleInstalled("mail") && intval($arM["EXTERNAL_ID"])>0)
													{

														Support_GetDictionaryInfo($arM["SOURCE_ID"], $arM["SOURCE_NAME"], $arM["SOURCE_DESC"], $arM["SOURCE_SID"]);

														if (mb_strtolower($arM["SOURCE_SID"]) == "email")
														{
															if($bSep)
															{
																echo " | ";
															}
															echo '<a title="' . GetMessage("SUP_VIEW_SOURCE_ALT") . '" href="/bitrix/admin/mail_message_view.php?lang=' . LANGUAGE_ID . '&ID=' . $arM["EXTERNAL_ID"] . '">' . GetMessage("SUP_VIEW_SOURCE") . '</a>';
															$bSep = true;
														}

													}
													if($bSep)
													{
														echo " | ";
													}


													echo '<a title="' . GetMessage("SUP_EDIT_ALT") . '" href="' . $TICKET_MESSAGE_EDIT_URL . '?lang=' . LANGUAGE_ID . '&TICKET_ID=' . $ID . '&ID=' . $arM["ID"] . '">' . GetMessage("SUP_EDIT") . '</a>';

													if ($messages>1)
													{
														echo ' | ' . '<a title="' . GetMessage("SUP_DELETE_ALT") . '" ' .
															'href="javascript:if(confirm(\'' . GetMessage("SUP_CONF") . '\')) ' .
															'window.location=\'' . urlencode(urlencode($TICKET_EDIT_URL)) . '?lang=' . LANGUAGE_ID . '&ID=' . $ID . '&mdel_id=' . $arM["ID"] . '&' . bitrix_sessid_get() . '\'">' . GetMessage("SUP_DELETE") . '</a>';
													}

												endif;
												?></td>
										</tr>
										<tr>
											<td  style="padding:4px;text-align:left;<? echo $backcolor; ?>"><?

												if($arM["IS_LOG"] != "Y")
												{
													echo ($arM["SOURCE_NAME"] <> '') ? "[".htmlspecialcharsbx($arM["SOURCE_NAME"])."]&nbsp;" : "";

													$oUID = isset($arM["OWNER_USER_ID"]) ? intval($arM["OWNER_USER_ID"]) : 0;
													$oGID = isset($arM["OWNER_GUEST_ID"]) ? intval($arM["OWNER_GUEST_ID"]) : 0;

													if($arM["OWNER_SID"] <> '')
													{
														echo TxtToHtml($arM["OWNER_SID"]) . "&nbsp;";
														if($oUID > 0)
														{
															echo "/&nbsp;";
														}
													}

													if($oUID > 0)
													{
														//Support_GetUserInfo($arM["OWNER_USER_ID"], $arM["OWNER_LOGIN"], $arM["OWNER_USER_NAME"]);

														if(!in_array($oUID, array_keys($arrSUPPORT_TEAM)))
														{
															if($arM["MESSAGE_BY_SUPPORT_TEAM"] == "Y")
															{
																$arrSUPPORT_TEAM[$oUID] = "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)";

															}
															elseif($arM["MESSAGE_BY_SUPPORT_TEAM"] == "N")
															{
																$arrSUPPORT_TEAM[$oUID] = "";
															}
															else
															{
																$arrSUPPORT_TEAM[$oUID] = (CTicket::IsSupportTeam($uid) || CTicket::IsAdmin($uid)) ? "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)" : "";
															}
														}
													}

													if($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y")
													{
														/*if($oUID > 0)
														{
															?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?echo LANG?>&ID=<?echo $arM["OWNER_USER_ID"]; ?>"><?echo $arM["OWNER_USER_ID"]; ?></a>] (<? echo $arM["OWNER_LOGIN"]; ?>) <? echo $arM["OWNER_USER_NAME"]; ?>
															<?=$arrSUPPORT_TEAM[$arM["OWNER_USER_ID"]]?>
															<?
														}*/

														if($oUID <= 0 && $oGID > 0 && CModule::IncludeModule("statistic"))
														{
															echo $arStrUsersM["arGuests"][$oGID]["HTML_NAME"];
															?>
															<? echo ($oUID > 0 ? $arrSUPPORT_TEAM[$oUID] : ""); ?>
															<?
															//echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=" . LANG . "&find_id=" . $arM["OWNER_GUEST_ID"] . "&find_id_exact_match=Y&set_filter=Y'>" . $arM["OWNER_GUEST_ID"] . "</a>]";
														}
														elseif($oUID > 0)
														{
															echo $arStrUsersM["arUsers"][$oUID]["HTML_NAME"]; ?>
															<? echo $arrSUPPORT_TEAM[$oUID]; ?>
															<?
														}
													}
													else
													{
														if($oUID > 0)
														{
															echo $arStrUsersM["arUsers"][$oUID]["HTML_NAME_S"];
															//echo "[" . $arM["OWNER_USER_ID"] . "] (" . $arM["OWNER_LOGIN"] . ") " . $arM["OWNER_USER_NAME"] . " " . $arrSUPPORT_TEAM[$arM["OWNER_USER_ID"]] . "";
														}
													}
												}
												else
												{
													$cUID = isset($arM["CREATED_USER_ID"]) ? intval($arM["CREATED_USER_ID"]) : 0;
													$cGID = isset($arM["CREATED_GUEST_ID"]) ? intval($arM["CREATED_GUEST_ID"]) : 0;

													if($cUID > 0)
													{
														//Support_GetUserInfo($arM["CREATED_USER_ID"], $arM["CREATED_LOGIN"], $arM["CREATED_USER_NAME"]);

														if (!in_array($cUID, array_keys($arrSUPPORT_TEAM)))
														{
															if($arM["MESSAGE_BY_SUPPORT_TEAM"] == "Y")
															{
																$arrSUPPORT_TEAM[$cUID] = "(<span class=\"supportrequired\">".GetMessage("SUP_TECHSUPPORT")."</span>)";
															}
															elseif ($arM["MESSAGE_BY_SUPPORT_TEAM"] == "N")
															{
																$arrSUPPORT_TEAM[$cUID] = "";
															}
															else
															{
																$arrSUPPORT_TEAM[$cUID] = (CTicket::IsSupportTeam($cUID) || CTicket::IsAdmin($cUID)) ? "(<span class=\"supportrequired\">" . GetMessage("SUP_TECHSUPPORT") . ")</span>" : "";
															}
														}

													}

													if ($arM["CREATED_MODULE_NAME"] == '' || $arM["CREATED_MODULE_NAME"] == "support")
													{
														/*?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<? echo $arM["CREATED_USER_ID"]; ?>"><? echo $arM["CREATED_USER_ID"]; ?></a>] (<? echo $arM["CREATED_LOGIN"]; ?>) <? echo $arM["CREATED_USER_NAME"]; ?> <? echo $arrSUPPORT_TEAM[$arM["CREATED_USER_ID"]]; ?><?*/

														if ($cUID <= 0 && $cGID > 0 && CModule::IncludeModule("statistic"))
														{
															echo $arStrUsersM["arGuests"][$cGID]["HTML_NAME"];
															echo ($cUID > 0 ? " " . $arrSUPPORT_TEAM[$cUID] : "");
															/*echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=". $arM["CREATED_GUEST_ID"]."&find_id_exact_match=Y&set_filter=Y'>".$arM["CREATED_GUEST_ID"]."</a>]";*/
														}
														elseif($cUID > 0)
														{
															echo $arStrUsersM["arUsers"][$cUID]["HTML_NAME"];
															echo " " . $arrSUPPORT_TEAM[$cUID];
														}
													}
													else
													{
														echo $arM["CREATED_MODULE_NAME"];
													}

												}
												?></td>
											<td align="right"  style="padding:4px;<?=$backcolor?>">&nbsp;
												<?
												if (($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") && $arM["IS_SPAM"] <> ''):
													?>&nbsp;&nbsp;&nbsp;[<?=GetMessage("SUP_SPAM")?><?echo ($arM["IS_SPAM"]=="Y") ? "!" : "?"?>]<?

												elseif ($arM["IS_LOG"]=="Y") :
													?>&nbsp;&nbsp;&nbsp;<span style="color:#939300">[<?=GetMessage("SUP_LOG")?>]</span><?
												else:

													if (intval($arM["TASK_TIME"])>0):
														$str = "";
														$days = intval($arM["TASK_TIME"]/1440);
														if ($days>0)
														{
															$str .= $days."&nbsp;".GetMessage("SUP_DAYS")." ";
															$arM["TASK_TIME"] = $arM["TASK_TIME"] - $days*1440;
														}

														$hours = intval($arM["TASK_TIME"]/60);
														if ($hours>0)
														{
															$str .= $hours."&nbsp;".GetMessage("SUP_HOURS")." ";
															$arM["TASK_TIME"] = $arM["TASK_TIME"] - $hours*60;
														}

														$str .= ($arM["TASK_TIME"]%60)."&nbsp;".GetMessage("SUP_MINUTES");

														echo $str;


													endif;

													if ($arM["IS_HIDDEN"] == "Y"):?>&nbsp;&nbsp;&nbsp;<span style="color:#2F9567">[<?=GetMessage("SUP_HIDDEN")?>]</span><?endif?>

													<?endif;?></td>
										</tr>

											<?
											if($arM["IS_LOG"] != "Y")
											{
												/*$arFiles = array();
												if ($rsFiles = CTicket::GetFileList($v1="s_id", $v2="asc", array("MESSAGE_ID" => $f_ID))) :
													while ($arFile = $rsFiles->Fetch()) :
														$name = strlen($arFile["ORIGINAL_NAME"])>0 ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"];
														if (strlen($arFile["EXTENSION_SUFFIX"])>0) :
															$suffix_length = strlen($arFile["EXTENSION_SUFFIX"]);
															$name = substr($name, 0, strlen($name)-$suffix_length);
														endif;
														$arFiles[] = array("HASH" => $arFile["HASH"], "NAME" => $name, "FILE_SIZE" => $arFile["FILE_SIZE"]);
													endwhile;
												endif;*/
												if (array_key_exists($arM["ID"], $ALL_TICKET_FILES) && is_array($ALL_TICKET_FILES[$arM["ID"]]))
												{
											?>

												<tr><td colspan="2" height="1" style="padding:4px;<?=$headerbackcolor?>"></td></tr>
												<tr>
													<td colspan="2" style="padding:8px;<?=$headerbackcolor?>">
														<table cellspacing=0 cellpadding=0 style="width:0%;">
															<tr>
																<td <?=$headerbackcolor?> valign="top" style="width:0%;" nowrap>
																	<img src="/bitrix/images/support/paperclip.gif" width="16" height="16" border="0" alt="">
																</td>
																<td <?=$headerbackcolor?> style="width:0%;text-align:left;" nowrap><?
																	$aImg = array("gif", "png", "jpg", "jpeg", "bmp");
																	foreach ($ALL_TICKET_FILES[$arM["ID"]] as $arFile)
																	{
																		if(in_array(mb_strtolower(GetFileExtension($arFile["NAME"])), $aImg)):
																			?><a title="<?=GetMessage("SUP_VIEW_ALT")?>" target="_blank" href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANGUAGE_ID?>"><?echo htmlspecialcharsbx($arFile["NAME"])?></a>
																			<?else:?>
																			<?echo htmlspecialcharsbx($arFile["NAME"])?>
																			<?endif?>
																		(<?
																		/*$a = array("b", "kb", "mb", "gb");
																		$pos = 0;
																		$size = $arFile["FILE_SIZE"];
																		while($size >= 1024)
																		{
																			$size /= 1024;
																			$pos++;
																		}
																		echo round($size,2)." ".$a[$pos];*/
																		echo CFile::FormatSize($arFile["FILE_SIZE"]);
																		?>)<br><?
																	}
																	?></td>
																<td <?=$headerbackcolor?> style="width:0%;" nowrap><?
																	foreach ($ALL_TICKET_FILES[$arM["ID"]] as $arFile)
																	{
																		$alt = str_replace( "#FILE_NAME#", htmlspecialcharsbx($arFile["NAME"]), GetMessage( "SUP_DOWNLOAD_ALT" ) );
																		?>&nbsp;[<a title="<?=$alt?>" href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANGUAGE_ID?>&action=download"><?echo GetMessage("SUP_DOWNLOAD")?></a>]<br><?
																	}
																	?></td>
															</tr>
														</table>
													</td>
												</tr>
											<?
												}
											}
											?>

										<tr>
											<td colspan="2" height="1" style="<?=$headerbackcolor?>"></td></tr>
										<tr>

											<td colspan="2" id="quotetd<? echo $arM["ID"]; ?>"  style="padding:8px;text-align:left;<? echo $headerbackcolor; ?>"><?
												if($arM["IS_LOG"] == "Y")
												{
													echo "" . $arM["MESSAGE"] . "";
												}
												else
												{
													$quote_table_class = "quotetable";
													$code_table_class = "codetable";
													if($arM["IS_HIDDEN"] == "Y")
													{
														$quote_head_class =  "tdhiddenquotehead";
														$quote_body_class = "tdhiddenquote";
														$code_head_class = "tdhiddencodehead";
														$code_body_class = "tdhiddencodebody";
														$code_textarea_class = "codehiddentextarea";
													}
													else
													{
														$quote_head_class =  "tdquotehead";
														$quote_body_class = "tdquote";
														$code_head_class = "tdcodehead";
														$code_body_class = "tdcodebody";
														$code_textarea_class = "codetextarea";
													}
													echo TxtToHTML(
														$arM["MESSAGE"],
														true,
														70,
														"Y",
														"N",
														"Y",
														"Y",
														$quote_table_class,
														$quote_head_class,
														$quote_body_class,
														$code_table_class,
														$code_head_class,
														$code_body_class,
														$code_textarea_class
													);
												}
												?></td>
										</tr>

									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?
				}
				?>
				<tr>
					<td></td>
					<td><?echo $mess->NavPrint(GetMessage("SUP_PAGES"))?></td>
				</tr>
			</table>
		</td>
	</tr>
	<?endif;?>
	<?endif;?>

<?if ($str_DATE_CLOSE == ''):?>

	<?if ($ID>0):?>
	<tr class="heading"><td id="edit_27" colspan="2"><?=GetMessage("SUP_ANSWER")?><a name="postform"></a></td></tr>
	<?endif;?>
<?endif;?>


<?if ($can_select_mode=="Y"):?>
<script type="text/javascript">
<!--
var timeCounterID = null;

function TaskTime(){document.getElementById("TASK_TIME").value++;timeCounterID = setTimeout('TaskTime()',60000);}

function OnModeClick(mode, btn1, btn2)
{
	var disabled = (mode=='view') ? true : false;
	var back_color = disabled ? "F8F8F8" : "FFFFFF";
	var obTD;

	if (disabled)
	{
		if (timeCounterID)
			clearTimeout(timeCounterID);
	}
	else
	{
		if (document.getElementById("TASK_TIME").value >= 1)
			document.getElementById("TASK_TIME").value--;
		TaskTime();
	}

	for (i=0; i<=39; i++)
	{
		obTD = document.getElementById("edit_"+i);
		if (obTD) obTD.disabled = disabled;
	}

	document.getElementById("TITLE").disabled = disabled;
	document.getElementById("HIDDEN").disabled = disabled;
	document.getElementById("NOT_CHANGE_STATUS").disabled = disabled;


	document.getElementById("MESSAGE_AUTHOR_SID").disabled = disabled;
	document.getElementById("MESSAGE_AUTHOR_SID").style.backgroundColor = back_color;

	document.getElementById("MESSAGE_SOURCE_ID").disabled = disabled;
	document.getElementById("MESSAGE_SOURCE_ID").style.backgroundColor = back_color;

	document.getElementById("MESSAGE_AUTHOR_USER_ID").disabled = disabled;
	document.getElementById("MESSAGE_AUTHOR_USER_ID").style.backgroundColor = back_color;

	//document.getElementById("FindUser").disabled = disabled;
	//document.getElementById("FindUser").style.backgroundColor = back_color;

	document.getElementById("FUA_ID").disabled = disabled;
	document.getElementById("FUA_ID").style.backgroundColor = back_color;

	document.getElementById("B").disabled = disabled;
	document.getElementById("I").disabled = disabled;
	document.getElementById("U").disabled = disabled;
	document.getElementById("QUOTE").disabled = disabled;
	document.getElementById("CODE").disabled = disabled;
	document.getElementById("TRANSLIT").disabled = disabled;

	document.getElementById("MESSAGE").disabled = disabled;
	document.getElementById("MESSAGE").style.backgroundColor = back_color;
<?
	if ( $ID>0 && ( $bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y" ))
	{
?>
	document.getElementById("SUPPORT_COMMENTS").disabled = disabled;
	document.getElementById("SUPPORT_COMMENTS").style.backgroundColor = back_color;
<?
	}
?>

	document.getElementById("TASK_TIME").disabled = disabled;
	document.getElementById("TASK_TIME").style.backgroundColor = back_color;

	document.getElementById("CLOSE").disabled = disabled;
	document.getElementById("CLOSE").style.backgroundColor = back_color;

	document.getElementById("HOLD_ON").disabled = disabled;
	document.getElementById("HOLD_ON").style.backgroundColor = back_color;

	document.getElementById("AUTO_CLOSE_DAYS").disabled = disabled;
	document.getElementById("AUTO_CLOSE_DAYS").style.backgroundColor = back_color;

	objFilesCounter = document.getElementById("files_counter");
	files_counter = parseInt(objFilesCounter.value);
	for (i=0; i<files_counter; i++)
	{
		document.getElementById("FILE_"+i).disabled = disabled;
		document.getElementById("FILE_"+i).style.backgroundColor = back_color;
	}

	document.getElementById("AddFile").disabled = disabled;
	document.getElementById("AddFile").style.backgroundColor = back_color;


	<?if ($can_select_category=="Y"):?>
	document.getElementById("CATEGORY_ID").disabled = disabled;
	document.getElementById("CATEGORY_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_status=="Y"):?>
	document.getElementById("STATUS_ID").disabled = disabled;
	document.getElementById("STATUS_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_difficulty=="Y"):?>
	document.getElementById("DIFFICULTY_ID").disabled = disabled;
	document.getElementById("DIFFICULTY_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_responsible=="Y"):?>
	document.getElementById("RESPONSIBLE_USER_ID").disabled = disabled;
	document.getElementById("RESPONSIBLE_USER_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_criticality=="Y"):?>
	document.getElementById("CRITICALITY_ID").disabled = disabled;
	document.getElementById("CRITICALITY_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_mark=="Y"):?>
	document.getElementById("MARK_ID").disabled = disabled;
	document.getElementById("MARK_ID").style.backgroundColor = back_color;
	<?endif;?>

	<?if ($can_select_sla=="Y"):?>
	document.getElementById("SLA_ID").disabled = disabled;
	document.getElementById("SLA_ID").style.backgroundColor = back_color;
	<?endif;?>
	
	//document.getElementById("save").disabled = disabled;
	//document.getElementById("apply").disabled = disabled;
	//document.getElementById("reset").disabled = disabled;
	document.forms['form1'].elements['save'].disabled = disabled;
	document.forms['form1'].elements['apply'].disabled = disabled;
	//document.forms['form1'].elements['cancel'].disabled = disabled;

	document.getElementById(btn2).style.backgroundColor = document.getElementById(btn1).style.backgroundColor;
	document.getElementById(btn1).style.backgroundColor = "FFF8A8";
	document.getElementById(btn1).disabled = true;
	document.getElementById(btn2).disabled = false;

	if (disabled)
	{
		document.getElementById("icon_1").style.display = "none";
		document.getElementById("icon_2").style.display = "none";
	}
	else
	{
		document.getElementById("icon_1").style.display = "inline";
		document.getElementById("icon_2").style.display = "inline";
	}

	document.getElementById("online_frame").src = "/bitrix/admin/ticket_online.php?TICKET_ID=<?=$ID?>&OWNER_USER_ID=<?=intval($str_OWNER_USER_ID)?>&lang=<?=LANGUAGE_ID?>&mode="+mode+"&ONLINE_AUTO_REFRESH=<?=$ONLINE_AUTO_REFRESH?>";
	
	EnDisUserFields( mode );
}
//-->
</script>

	<tr valign="top">
		<td align="right" valign="middle"><?=GetMessage("SUP_MODE")?></td>
		<td><input OnClick="javascript:OnModeClick('edit', 'mode_edit', 'mode_view')" type="button" name="mode_edit" value="<?=GetMessage("SUP_ANSWER_MODE")?>" id="mode_edit">&nbsp;<input OnClick="javascript:OnModeClick('view', 'mode_view', 'mode_edit')" type="button" id="mode_view" name="mode_view" value="<?=GetMessage("SUP_VIEW_MODE")?>"></td>
	</tr>
<?endif;?>

<?if ($can_select_message_owner=="Y"):?>

	<script type="text/javascript">
	<!--
	function HiddenClick()
	{
		var objPrivate, color, color2, objFilesCounter, files_counter;
		objPrivate = document.getElementById("HIDDEN");
		if (objPrivate.checked)
		{
			color_backgroud = "#F1F8F5";
			color_checkbox = "#3CB97D";
		}
		else
		{
			color_backgroud = "";
			color_checkbox = "";
		}
		<?if ($str_DATE_CLOSE == ''):?>
		document.getElementById("MESSAGE_AUTHOR_SID").style.backgroundColor = color_backgroud;
		document.getElementById("MESSAGE_SOURCE_ID").style.backgroundColor = color_backgroud;
		document.getElementById("MESSAGE_AUTHOR_USER_ID").style.backgroundColor = color_backgroud;
		document.getElementById("FUA_ID").style.backgroundColor = color_backgroud;
		document.getElementById("MESSAGE").style.backgroundColor = color_backgroud;
		document.getElementById("TASK_TIME").style.backgroundColor = color_backgroud;
		document.getElementById("HOLD_ON").style.backgroundColor = color_backgroud;
		document.getElementById("AUTO_CLOSE_DAYS").style.backgroundColor = color_backgroud;
		objFilesCounter = document.getElementById("files_counter");
		files_counter = parseInt(objFilesCounter.value);
		for (i=0; i<files_counter; i++)
		{
			document.getElementById("FILE_"+i).style.backgroundColor = color_backgroud;
		}
		<?endif;?>
		<?if ($can_select_category=="Y"):?>
		document.getElementById("CATEGORY_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_status=="Y"):?>
		document.getElementById("STATUS_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_difficulty=="Y"):?>
		document.getElementById("DIFFICULTY_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_responsible=="Y"):?>
		document.getElementById("RESPONSIBLE_USER_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_criticality=="Y"):?>
		document.getElementById("CRITICALITY_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_mark=="Y"):?>
		document.getElementById("MARK_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		<?if ($can_select_sla=="Y"):?>
		document.getElementById("SLA_ID").style.backgroundColor = color_backgroud;
		<?endif;?>
		objPrivate.style.backgroundColor = color_checkbox;

	}
	//-->
	</SCRIPT>

	<tr valign="top">
		<td align="right" id="edit_38"><?=GetMessage('SUP_TITLE')?></td>
		<td valign="center" id="edit_39"><?echo InputType("input", "TITLE", $str_TITLE, '', false, "", "id=\"TITLE\"")?></td>
	</tr>

	<tr valign="top">
		<td align="right" id="edit_1"><?
		echo ($str_DATE_CLOSE == '') ? GetMessage("SUP_HIDDEN_MESSAGE") : GetMessage("SUP_DO_NOT_NOTIFY_AUTHOR")?></td>
		<td valign="center" id="edit_2"><?echo InputType("checkbox", "HIDDEN", "Y", $str_HIDDEN, false, "", "OnClick=\"HiddenClick()\" id=\"HIDDEN\"")?><br><?=GetMessage("SUP_HIDDEN_MESSAGE_ALT")?></td>
	</tr>

	<?if($str_DATE_CLOSE == ''): ?>
	<tr valign="top">
		<td align="right" id="edit_28"><?=GetMessage("CHANGE_STATUS")?>:</td>
		<td valign="center" id="edit_29"><?echo InputType("checkbox", "NOT_CHANGE_STATUS", "Y", $str_NOT_CHANGE_STATUS, false, "", "id=\"NOT_CHANGE_STATUS\"")?></td>
	</tr>
	<?endif?>

	<?if ($str_DATE_CLOSE == ''):?>

	<script type="text/javascript">
	<!--
	function SelectMessageSource()
	{
		var objSourceSelect, strSourceValue;
		objSourceSelect = document.form1.MESSAGE_SOURCE_ID;
		strSourceValue = objSourceSelect[objSourceSelect.selectedIndex].value;
		document.getElementById("MESSAGE_AUTHOR_SID").style.display = "none";
		document.getElementById("MESSAGE_AUTHOR_SID").disabled = true;
		if (strSourceValue!="")
		{
			document.getElementById("MESSAGE_AUTHOR_SID").disabled = false;
			document.getElementById("MESSAGE_AUTHOR_SID").style.display = "inline";
		}
	}
	//-->
	</SCRIPT>

	<tr valign="middle">
		<td id="edit_3" align="right" width="20%" nowrap><?=GetMessage("SUP_SOURCE")." / ".GetMessage("SUP_FROM")?></td>
		<td id="edit_4" width="80%" nowrap><?
			//echo SelectBox("MESSAGE_SOURCE_ID", CTicketDictionary::GetDropDown("SR", $TICKET_SITE), "< web >", $str_MESSAGE_SOURCE_ID, "OnChange=SelectMessageSource() id=\"MESSAGE_SOURCE_ID\"");

			echo SelectBoxFromArray("MESSAGE_SOURCE_ID", __GetDropDown("SR", $TICKET_DICTIONARY), $str_MESSAGE_SOURCE_ID, "< web >", "OnChange=SelectMessageSource() id=\"MESSAGE_SOURCE_ID\"");

			?>&nbsp;<input type="text" size="12" name="MESSAGE_AUTHOR_SID" id="MESSAGE_AUTHOR_SID" value="<?=$str_MESSAGE_AUTHOR_SID?>">

<input type="text" name="MESSAGE_AUTHOR_USER_ID" id="MESSAGE_AUTHOR_USER_ID" value="" size="3" >
<input type="button" name="FindUser" id="FindUser" OnClick="window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=form1&FC=MESSAGE_AUTHOR_USER_ID', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" value="..."-->


			</td>
	</tr>

	<script type="text/javascript">
	<!--
	SelectMessageSource();
	//-->
	</SCRIPT>

	<?endif;?>

<?endif;?>

<?if ($str_DATE_CLOSE == ''):?>

	<?if (($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") && $ID>0) :?>
	<script type="text/javascript">
	var answers= new Array();
	function OnChangeFUA_ID()
	{
		var value;
		value = answers[document.form1.FUA_ID[document.form1.FUA_ID.selectedIndex].value];
		if (value && value.length>0) document.form1.MESSAGE.value += value;

	}
	<?
	$z = CTicket::GetFUA($TICKET_SITE);
	while ($zr=$z->Fetch()) :
		$src = $zr["DESCR"];
		$src=preg_replace('#<SCRIPT#i', '<S"+"CRIPT',
				preg_replace('#</SCRIPT#i', '</S"+"CRIPT',
					addcslashes(
						str_replace('"', '\"',
							str_replace("\\", "\\\\",
								$src
							)
						),
					"\0..\37")
				)
			);
	?>
	answers[<?=$zr["REFERENCE_ID"]?>]="<?=$src?>";
	<?endwhile;?>
	</script>

	<tr valign="middle">
		<td id="edit_5" align="right"><?=GetMessage("SUP_FUA")?></td>
		<td id="edit_6"><?
			echo SelectBox("FUA_ID", CTicket::GetFUA($TICKET_SITE), GetMessage("SUP_NO"), "", "OnChange=\"OnChangeFUA_ID()\" id=\"FUA_ID\"");
			?></td>
	</tr>
	<?endif;?>

	<SCRIPT type="text/javascript">
	<!--
	function SupQuoteMessage(id)
	{
		var selection;
		if (document.getSelection)
		{
			selection = document.getSelection().toString();
			selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_");
			selection = selection.replace(/\r\n/gi, " ");
			selection = selection.replace(/  /gi, "");
			selection = selection.replace(/_newstringhere_/gi, "\r\n\r\n");
		}
		else
		{
			selection = document.selection.createRange().text;
		}
		if (selection!="")
		{
			document.form1.MESSAGE.value += "<QUOTE>"+selection+"</QUOTE>\n";
		}
		else
		{
			var el = document.getElementById(id);
			var textData = (el.innerText) ? el.innerText : el.textContent;
			if(el)
			{
				var str = textData
				str = str.replace(/\r\n\r\n/gi, "_newstringhere_");
				str = str.replace(/\r\n/gi, " ");
				str = str.replace(/<br[^>]*>/gi, "");
				str = str.replace(/<\/p[^>]*>/gi, "\r\n");
				str = str.replace(/<li[^>]*>/gi, "\r\n");
				str = str.replace(/<[^>]*>/gi, " ");
				str = str.replace(/  /gi, "");
				str = str.replace(/_newstringhere_/gi, "\r\n");
				document.form1.MESSAGE.value += "<QUOTE>"+str+"</QUOTE>\n";
			}
		}
		
	}

	<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/admin/ticket_message_js.php");?>

	//-->
	</SCRIPT>

	<tr valign="top" class="adm-detail-required-field">
		<?if ($ID<=0) :?>
		<td align="right"><?=GetMessage("SUP_MESSAGE")?></td>
		<?else:?>
		<td align="right">
			<table cellspacing=0 cellpadding=0 width="100%" border=0>
				<tr>
					<td style="padding-top:13px; padding-right: 10px" nowrap><?=GetMessage("SUP_ONLINE_TITLE")?></td>
					<td style="padding-top:13px;" width="100%" align="left"><a target="online_frame" href="/bitrix/admin/ticket_online.php?TICKET_ID=<?=$ID?>&OWNER_USER_ID=<?=intval($str_OWNER_USER_ID)?>&lang=<?=LANGUAGE_ID?>&ONLINE_AUTO_REFRESH=<?=$ONLINE_AUTO_REFRESH?>"><img src="/bitrix/images/support/refresh.gif" width="14" height="14" border="0" alt="<?=GetMessage("SUP_REFRESH_ALT")?>"></a></td>
				</tr>
				<tr>
					<td colspan="2" width="100%" nowrap><iframe name="online_frame" id="online_frame" frameborder="0" style="width:100%; border:0; height:300px;" src="/bitrix/admin/ticket_online.php?TICKET_ID=<?=$ID?>&OWNER_USER_ID=<?=intval($str_OWNER_USER_ID)?>&lang=<?=LANGUAGE_ID?>&mode=<?=$default_mode?>&ONLINE_AUTO_REFRESH=<?=$ONLINE_AUTO_REFRESH?>"></iframe></td>
				</tr>
			</table></td>
		<?endif;?>
		<td style="padding: 0px"><table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td valign="bottom" id="edit_7" style="padding-bottom:3px;">
					<input type="button" value="<?=GetMessage("SUP_B")?>" onClick="insert_tag('B', document.form1.MESSAGE)" style="font-weight:bold" name="B" id="B" title="<? echo GetMessage("SUP_B_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_B_T"); ?>">
					<input type="button" value="<?=GetMessage("SUP_I")?>" onClick="insert_tag('I', document.form1.MESSAGE)" style="font-style:italic" name="I" id="I" title="<? echo GetMessage("SUP_I_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_I_T"); ?>">
					<input type="button" value="<?=GetMessage("SUP_U")?>" onClick="insert_tag('U', document.form1.MESSAGE)" style="text-decoration:underline" name="U" id="U" title="<? echo GetMessage("SUP_U_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_U_T"); ?>">
					<input type="button" value="<?=GetMessage("SUP_QUOTE").$hkInst->GetTitle("TICKET_EDIT_QUOTE_T")?>" onClick="insert_tag('QUOTE', document.form1.MESSAGE)" style="vertical-align: middle; width: 130px" name="QUOTE" id="QUOTE" title="<? echo GetMessage("SUP_QUOTE_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_QUOTE_T"); ?>">
					<input type="button" value="<?=GetMessage("SUP_CODE").$hkInst->GetTitle("TICKET_EDIT_CODE_T")?>" onClick="insert_tag('CODE', document.form1.MESSAGE)" style="vertical-align: middle; width: 100px" name="CODE" id="CODE" title="<? echo GetMessage("SUP_CODE_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_CODE_T"); ?>">
					<input type="button" value="<?=GetMessage("SUP_TRANSLIT").$hkInst->GetTitle("TICKET_EDIT_TRANSLIT_T")?>" onClick="translit(document.form1.MESSAGE)" style="vertical-align: middle; width: 135px" name="TRANSLIT" id="TRANSLIT" title="<? echo GetMessage("SUP_TRANSLIT_ALT"); echo $hkInst->GetTitle("TICKET_EDIT_TRANSLIT_T");?>"></td>
				</tr>
				<tr>
					<td><textarea name="MESSAGE" id="MESSAGE" style="width:100%;height:300px;"  wrap="virtual"><?=htmlspecialcharsbx($MESSAGE)?></textarea></td>
				</tr>
			</table></td>
	</tr>

	<script type="text/javascript">
	<!--
	function AddFileInput()
	{
		var counter = document.form1.files_counter.value;
		counter++;
		inputNom = counter-1;
		var tb = document.getElementById("files_table");
		var oRow = tb.insertRow(0);
		var oCell = oRow.insertCell(0);
		oCell.style.paddingBottom="5px";
		oCell.innerHTML = '&nbsp;<input name="FILE_'+inputNom+'" id="FILE_'+inputNom+'" size="30" type="file">';

		document.form1.files_counter.value = counter;
		/*document.getElementById("FILE_"+counter).style.backgroundColor = document.getElementById("FILE_"+(counter-1)).style.backgroundColor;*/
		BX.adminPanel.modifyFormElements(oCell);
	}
	//-->
	</script>

	<tr valign="top">
		<td align="right" id="edit_8"><?
		$max_size = 0;
		if ($bSupportTeam!="Y" && $bAdmin!="Y" && $bDemo!="Y")
		{
			$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
		}
		if (intval($max_size)>0)
		{
			echo GetMessage("SUP_ATTACH")."<br>(max - ". intval($max_size)." ".GetMessage("SUP_KB")."):";
			$ms = intval($max_size)*1024;
			echo "<input type='hidden' name='MAX_FILE_SIZE' value='".$ms."'>";
		}
		else
		{
			echo GetMessage("SUP_ATTACH").":";
		}
		?></td>
		<td style="padding:0px" nowrap id="edit_9">
		
			
			<table cellspacing=0 cellpadding=0 border=0 id="files_table">
				<?
				if (isset($arFiles))
				{
					foreach($arFiles as $arFile)
					{
					?>
				<tr>
					<td><?
						?><a title="<?=GetMessage("SUP_VIEW_ALT")?>" target="_blank" href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANG?>"><?echo htmlspecialcharsbx($arFile["NAME"])?></a> (<?
						/*$a = array("b", "kb", "mb", "gb");
						$pos = 0;
						$size = $arFile["FILE_SIZE"];
						while($size >= 1024) {$size /= 1024; $pos++;}
						echo round($size,2)." ".$a[$pos];*/
						echo CFile::FormatSize($arFile["FILE_SIZE"]);
						?>)&nbsp;&nbsp;[&nbsp;<a href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANG?>&action=download"><?echo GetMessage("SUP_DOWNLOAD")?></a>&nbsp;]&nbsp;&nbsp;<input type="checkbox" name="ATTACH_FILE[]" value="<?=$arFile["ID"]?>" checked>
					</td>
				</tr>
					<?
					}
				}
				?>
				<tr>
					<td valign="top" style="padding-bottom:5px;">&nbsp;<input name="FILE_0" id="FILE_0" size="30" type="file"></td>
				</tr>
			<input type="hidden" name="files_counter" id="files_counter" value="1">
			</table>&nbsp;<input type="button" id="AddFile" value="<?=GetMessage("SUP_MORE")?>" OnClick="AddFileInput()"></td>
	</tr>

<?endif;?>

	<?if ($can_select_status=="Y") :?>
	<tr valign="middle">
		<td id="edit_10" align="right" nowrap><?=GetMessage("SUP_STATUS")?></td>
		<td id="edit_11" nowrap><?
			//echo SelectBox("STATUS_ID", CTicketDictionary::GetDropDown("S", $TICKET_SITE), " ", $str_STATUS_ID," id=\"STATUS_ID\"");
			echo SelectBoxFromArray("STATUS_ID", __GetDropDown("S", $TICKET_DICTIONARY), $str_STATUS_ID, " ", " id=\"STATUS_ID\"");

			?></td>
	</tr>
	<?endif;?>

	<?if ($can_select_difficulty=="Y") :?>
	<tr valign="middle">
		<td id="edit_30" align="right" nowrap><?=GetMessage("SUP_DIFFICULTY")?></td>
		<td id="edit_31" nowrap><?
			//echo SelectBox("DIFFICULTY_ID", CTicketDictionary::GetDropDown("D", $TICKET_SITE), " ", $str_DIFFICULTY_ID," id=\"DIFFICULTY_ID\"");
			echo SelectBoxFromArray("DIFFICULTY_ID", __GetDropDown("D", $TICKET_DICTIONARY), $str_DIFFICULTY_ID, " ", "  id=\"DIFFICULTY_ID\"");
			?></td>
	</tr>
	<?endif;?>

	<?if ($can_select_responsible=="Y") :?>
	<script type="text/javascript">
	<!--
	var arCategory_RESP = Array();
	<?
	if ($can_select_category=="Y") :
		$rs = CTicketDictionary::GetDropDown("C");
		while($ar = $rs->Fetch()):
			if (intval($ar["RESPONSIBLE_USER_ID"])>0):
			?>arCategory_RESP[<?=$ar["ID"]?>] = <?=$ar["RESPONSIBLE_USER_ID"]?>;
			<?
			endif;
		endwhile;
	endif;
	?>
	//-->
	</script>
	<?if ($can_select_sla=="Y" && $can_select_site=="Y") :?>
	<script type="text/javascript">
	<!--
	var arSla_RESP = Array();
	<?
		$rs = CTicketSLA::GetDropDown();
		while($ar = $rs->Fetch()):
			if (intval($ar["RESPONSIBLE_USER_ID"])>0):
			?>arSla_RESP[<?=$ar["ID"]?>] = <?=$ar["RESPONSIBLE_USER_ID"]?>;
			<?
			endif;
		endwhile;
	endif;
	?>
	//-->
	</script>

	<?if ($can_select_responsible=="Y" || ($can_select_sla=="Y" && $can_select_site=="Y")) :?>
	<script type="text/javascript">
	<!--
	function SetResponsible(select_name)
	{
		var obResponsible = document.getElementById("RESPONSIBLE_USER_ID");
		if (!obResponsible.disabled)
		{
			var obSelect = document.getElementById(select_name);
			var iValue, iResponsible, mess, i;
			if (obSelect)
			{
				iValue = obSelect[obSelect.selectedIndex].value;
				switch (select_name)
				{
					case "SLA_ID":
						if (arSla_RESP[iValue]) iResponsible = arSla_RESP[iValue];
						mess = '<?=GetMessage("SUP_SLA_RESPONSIBLE_UNDEFINED")?>';
						break;
					case "CATEGORY_ID":
						if (arCategory_RESP[iValue]) iResponsible = arCategory_RESP[iValue];
						mess = '<?=GetMessage("SUP_CATEGORY_RESPONSIBLE_UNDEFINED")?>';
						break;
				}
				if (parseInt(iResponsible)>0)
				{
					for(i=0; i<obResponsible.options.length; i++)
						if (obResponsible.options[i].value==iResponsible)
							obResponsible.selectedIndex = i;
				}
				else alert(mess);
			}
		}
	}
	//-->
	</script>
	<?endif;?>

	<tr valign="middle">
		<td id="edit_12" align="right" nowrap><?=GetMessage("SUP_RESPONSIBLE")?></td>
		<td id="edit_13" nowrap>
			<select id="RESPONSIBLE_USER_ID" name="RESPONSIBLE_USER_ID">";
				<option value="NOT_REF"> </option>
<?
$dbTeam = CTicket::GetSupportTeamList();
while ($arTeam = $dbTeam->Fetch())
{
	$reference_id = $arTeam["REFERENCE_ID"];
	$reference = $arTeam["REFERENCE"];

	if($arTeam["ACTIVE"] == "Y" || $reference_id == $str_RESPONSIBLE_USER_ID)
	{
		echo "<option ";
		if($reference_id == $str_RESPONSIBLE_USER_ID)
			echo " selected ";
		echo "value=\"".htmlspecialcharsbx($reference_id). "\">". htmlspecialcharsbx($reference)."</option>";
	}
}
?>
			</select>
		</td>
	</tr>
	<?endif;?>

	<?if ($can_select_criticality=="Y") :?>
	<tr valign="middle">
		<td id="edit_14" align="right"><?=GetMessage("SUP_CRITICALITY")?></td>
		<td id="edit_15"><?
		
			//if ($ID<=0 && strlen($strError)<=0) $str_CRITICALITY_ID = CTicketDictionary::GetDefault("K", $TICKET_SITE);
			//echo SelectBox("CRITICALITY_ID", CTicketDictionary::GetDropDown("K", $TICKET_SITE, $TICKET_SLA), " ", $str_CRITICALITY_ID, " id=\"CRITICALITY_ID\"");
			echo SelectBoxFromArray("CRITICALITY_ID", __GetDropDown("K", $TICKET_DICTIONARY), $str_CRITICALITY_ID, " ", " id=\"CRITICALITY_ID\"");
			?></td>
	</tr>
	<?endif;?>

	<?if ($can_select_mark=="Y") :?>
	<tr valign="middle">
		<td id="edit_16" align="right" nowrap><?=GetMessage("SUP_MARK")?></td>
		<td id="edit_17" nowrap><?
			//echo SelectBox("MARK_ID", CTicketDictionary::GetDropDown("M", $TICKET_SITE, $TICKET_SLA), " ", $str_MARK_ID, " id=\"MARK_ID\"");
			echo SelectBoxFromArray("MARK_ID", __GetDropDown("M", $TICKET_DICTIONARY), $str_MARK_ID, " ", " id=\"MARK_ID\"");
			?></td>
	</tr>
	<?endif;?>

	<?if ($can_select_category=="Y") :?>
	<tr valign="middle">
		<td id="edit_18" align="right"><?=GetMessage("SUP_CATEGORY")?></td>
		<td id="edit_19"><?
			//if ($ID<=0 && strlen($strError)<=0) $str_CATEGORY_ID = CTicketDictionary::GetDefault("C", $TICKET_SITE);
			//echo SelectBox("CATEGORY_ID", CTicketDictionary::GetDropDown("C", $TICKET_SITE, $TICKET_SLA), " ", $str_CATEGORY_ID, " id =\"CATEGORY_ID\"");

			echo SelectBoxFromArray("CATEGORY_ID", __GetDropDown("C", $TICKET_DICTIONARY), $str_CATEGORY_ID, " ", " id =\"CATEGORY_ID\"");

			?><?if ($can_select_responsible=="Y"):?>&nbsp;&nbsp;<a title="<?=GetMessage("SUP_RESPONSIBLE_SELECT_BY_CATEGORY_ALT")?>" id="icon_1" href="javascript:SetResponsible('CATEGORY_ID')"><img src="/bitrix/images/support/resp.gif" width="16" height="16" border="0" alt="<?=GetMessage("SUP_RESPONSIBLE_SELECT_BY_CATEGORY_ALT")?>"></a><?endif;?></td>
	</tr>
	<?endif;?>

	<?if ($can_select_sla=="Y" && $can_select_site=="Y"):?>
	<tr valign="middle">
		<td id="edit_20" align="right" width="20%" nowrap><?=GetMessage("SUP_SLA")?>:</td>
		<td id="edit_21" width="80%" nowrap><?
			$rsSLA = CTicketSLA::GetDropDown($TICKET_SITE);
			echo SelectBox("SLA_ID", $rsSLA, "", $TICKET_SLA, "onChange=\"OnSLAChange(this[this.selectedIndex].value)\" id=\"SLA_ID\"");
			?><?if ($can_select_responsible=="Y"):?>&nbsp;&nbsp;<a id="icon_2" title="<?=GetMessage("SUP_RESPONSIBLE_SELECT_BY_SLA_ALT")?>" href="javascript:SetResponsible('SLA_ID')"><img src="/bitrix/images/support/resp.gif" width="16" height="16" border="0" alt="<?=GetMessage("SUP_RESPONSIBLE_SELECT_BY_SLA_ALT")?>"></a><?endif;?></td>
	</tr>

	<script type="text/javascript">
	<!--
	var arCriticality_SLA = Array();
	var arCategory_SLA = Array();
	var arMark_SLA = Array();
	<?
	if (is_array($arrSlaID)):

		foreach($arrSlaID as $sid):

			$slaDictionary = CTicketDictionary::GetDropDownArray(false, $sid);

			if ($can_select_category=="Y") :
				?>
				arCategory_SLA[<?=$sid?>]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($slaDictionary['C']))
					{
						foreach ($slaDictionary['C'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			endif;

			if ($can_select_mark=="Y") :
				?>
				arMark_SLA[<?=$sid?>]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($slaDictionary['M']))
					{
						foreach ($slaDictionary['M'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			endif;

			if ($can_select_criticality=="Y") :
				?>
				arCriticality_SLA[<?=$sid?>]=Array(<?

					echo "Array('NOT_REF', ' ')";

					if (isset($slaDictionary['K']))
					{
						foreach ($slaDictionary['K'] as $ar)
						{
							echo ", Array('".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]))."', '".addslashes(htmlspecialcharsbx($ar["REFERENCE"]))."')";
						}
					}
					?>);
				<?
			endif;

		endforeach;
	endif;
	?>

	function OnSLAChange(sla_id)
	{
		var obSiteSelect, site_id, select_index, k;
		var arrList = Array();
		var arrValues = Array();
		var arrInit = Array();
		var arrCheck = Array();

		obSiteSelect = document.form1.SITE_ID;
		site_id = obSiteSelect[obSiteSelect.selectedIndex].value;

		<?if ($can_select_category=="Y") :?>
			arrList[arrList.length] = document.form1.CATEGORY_ID;
			arrValues[arrValues.length] = arCategory_SLA;
			arrCheck[arrCheck.length] = arCategory[site_id];
			arrInit[arrInit.length] = parseInt('<?=$str_CATEGORY_ID?>');
		<?endif;?>

		<?if ($can_select_mark=="Y") :?>
			arrList[arrList.length] = document.form1.MARK_ID;
			arrValues[arrValues.length] = arMark_SLA;
			arrCheck[arrCheck.length] = arMark[site_id];
			arrInit[arrInit.length] = parseInt('<?=$str_MARK_ID?>');
		<?endif;?>

		<?if ($can_select_criticality=="Y") :?>
			arrList[arrList.length] = document.form1.CRITICALITY_ID;
			arrValues[arrValues.length] = arCriticality_SLA;
			arrCheck[arrCheck.length] = arCriticality[site_id];
			arrInit[arrInit.length] = parseInt('<?=$str_CRITICALITY_ID?>');
		<?endif;?>

		for(i=0; i<arrList.length; i++)
		{
			arList = arrList[i];
			arValues = arrValues[i][sla_id];
			select_index = 0;
			while(arList.length>0) arList.options[0]=null;
			k = 0;
			for(j=0; j<arValues.length; j++)
			{
				if(in_array(arValues[j][0], arrCheck[i]))
				{
					newoption = new Option(htmlspecialcharsback(arValues[j][1]), arValues[j][0], false, false);
					arList.options[k] = newoption;
					k++;
					if (newoption.value==arrInit[i]) select_index = j;
				}
			}
			if (parseInt(select_index)>0) arList.selectedIndex = parseInt(select_index);
		}
	}
	//-->
	</script>


	<?endif;?>

<?if ($ID>0):?>

	<?if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y"):?>
	<tr>
		<td id="edit_32"><?=GetMessage("SUP_TASK_TIME")?></td>
		<td id="edit_33"><input type="text" name="TASK_TIME" id="TASK_TIME" size="7" maxlength="10" value=""></td>
	</tr>


	<tr valign="middle">
		<td id="edit_34" align="right" nowrap><?echo GetMessage("SUP_HOLD_ON")?>:</td>
		<td id="edit_35" nowrap><?echo InputType("checkbox","HOLD_ON","Y",$str_HOLD_ON, false, "", "id=\"HOLD_ON\"")?></td>
	</tr>

	<?endif;?>




	<?if (($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") && $str_DATE_CLOSE == '') :?>
	<tr valign="middle">
		<td id="edit_22" align="right" nowrap><?=GetMessage("SUP_AUTO_CLOSE_TICKET")?></td>
		<td id="edit_23" nowrap><?
			$ref_id = array("-1", "0");
			$ref = array(GetMessage("SUP_NOT_CHANGE"), GetMessage("SUP_SET_NULL"));
			for ($i=1;$i<=90;$i++)
			{
				$ref[] = $i." ".GetMessage("SUP_DAY");
				$ref_id[] = $i;
			}
			$arr = Array("reference" => $ref, "reference_id" => $ref_id);
			echo SelectBoxFromArray("AUTO_CLOSE_DAYS", $arr, $str_AUTO_CLOSE_DAYS, "", " id=\"AUTO_CLOSE_DAYS\"");
		?></td>
	</tr>
	<?endif;?>

	<?if ($str_DATE_CLOSE == ''):?>

	<script type="text/javascript">
	<!--
	function CloseClick()
	{
		var objClose, color;
		objClose = document.getElementById("CLOSE");
		if (objClose.checked) color = "#FF0000"; else color = "";
		objClose.style.backgroundColor = color;
	}
	//-->
	</SCRIPT>

	<tr valign="middle">
		<td id="edit_24" align="right" nowrap><?echo GetMessage("SUP_CLOSE_TICKET")?>:</td>
		<td id="edit_25" nowrap><?echo InputType("checkbox","CLOSE","Y",$str_CLOSE, false, "", "OnClick=\"CloseClick()\" id=\"CLOSE\"")?></td>
	</tr>
	<script type="text/javascript">
	<!--
	CloseClick();
	//-->
	</SCRIPT>

	<?else:?>

	<script type="text/javascript">
	<!--
	function OpenClick()
	{
		var objOpen, color;
		objOpen = document.getElementById("OPEN");
		if (objOpen.checked) color = "#0000FF"; else color = "";
		objOpen.style.backgroundColor = color;
	}
	//-->
	</SCRIPT>
	<tr valign="middle">
		<td align="right" nowrap><?echo GetMessage("SUP_OPEN_TICKET")?>:</td>
		<td nowrap><?echo InputType("checkbox","OPEN","Y","",false,"","OnClick=\"OpenClick()\" id=\"OPEN\"")?></td>
	</tr>
	<script type="text/javascript">
	<!--
	OpenClick();
	//-->
	</SCRIPT>

	<?endif;?>

<?endif;?>

<?
if ($ID <= 0/* && !($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y")*/)
{
	?>
	<tr>
		<td class="heading" id="edit_36"><?=GetMessage('SUP_COUPON')?></td>
		<td valign="top" id="edit_37"><input type="text" name="COUPON" value="" size="80" maxlength="255"></td>
	</tr>
	<?
}
?>

<?
	//$tabControl->BeginNextTab();
if( $bAdmin == "Y" || $bSupportTeam == "Y" || $bDemo == "Y" )
{
	$USER_FIELD_MANAGER->EditFormShowTab($PROPERTY_ID, ( count( $messageA ) > 0 ? true : false ), $ID);
	if ( $ID > 0 )
	{
	?>
	<script type="text/javascript">

	function EnDisUserFields( mode )
	{
		<? 
			$arrUF = $USER_FIELD_MANAGER->GetUserFields( "SUPPORT", 0, LANGUAGE_ID );
			echo "UFArr = Array('" . implode("','", array_keys( $arrUF ) ) . "');";
		?>
		HtmArr = Array();
		i = 0;
		tab = document.getElementById( "edit1_edit_table" );
		var disabled = (mode=='view') ? true : false;
		var back_color = disabled ? "F8F8F8" : "FFFFFF";
		for( var k in UFArr )
		{
			var tabs = BX.findChildren( tab, { "tag" : "input", attr: { "name" : UFArr[k] } }, true );
			if( tabs == null ) continue;
			for (var i = 0; i < tabs.length; i++)
			{
				tabs[i].disabled = disabled;
				tabs[i].style.backgroundColor = back_color;
			}
		}
		
	}
	EnDisUserFields( 'view' );

	</script>
	<?
	}
}
?>

<?
if ( $ID>0 && ( $bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y" ))
{
	?>
		
	<tr class="heading"><td id="edit_26" colspan="2"><?=GetMessage("SUP_SUPPORT_COMMENTS")?></td></tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td nowrap><textarea name="SUPPORT_COMMENTS" id="SUPPORT_COMMENTS" style="width:100%;height:100px;"  wrap="virtual"><?=$str_SUPPORT_COMMENTS?></textarea></td>
	</tr>
	<?
}
?>

<?
$tabControl->Buttons(array("back_url"=>$TICKET_LIST_URL."?lang=".LANGUAGE_ID));
$tabControl->End();
?>


<?if (($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") && $ID>0):?>

<?if ($can_select_mode && $default_mode=="view"):?>
<?echo BeginNote();?>
<?=GetMessage("SUP_MODE_LEGEND")?>
<?echo EndNote();?>
<?endif;?>

<script language="javascript">

HiddenClick();

<?if ($can_select_mode=="Y"):?>
<?if ($default_mode=="view"):?>
	OnModeClick('view', 'mode_view', 'mode_edit');
<?else:?>
	OnModeClick('edit', 'mode_edit', 'mode_view');
<?endif;?>
<?endif;?>

</script>
<?endif;?>

</form>
<?/*$tabControl->ShowWarnings("form1", $message);*/?>

<?echo BeginNote();?>
<table border="0" cellspacing="6" cellpadding="0">
	<tr>
		<td valign="center" colspan="2" nowrap><?=GetMessage("SUP_TICKET_STATUS")?>:</td>
	</tr>
	<tr>
		<td valign="center" nowrap><div class="lamp-red"></div></td>
		<td valign="center" nowrap><?echo ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") ? GetMessage("SUP_RED_ALT") : GetMessage("SUP_RED_ALT_2")?></td>
	</tr>
	<?if ($bAdmin=="Y" || $bDemo=="Y") :?>
	<tr>
		<td valign="center" nowrap><div class="lamp-yellow"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_YELLOW_ALT")?></td>
	</tr>
	<?endif;?>
	<tr>
		<td valign="center" nowrap><div class="lamp-green"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREEN_ALT")?></td>
	</tr>
	<?if ($bAdmin=="Y" || $bDemo=="Y" || $bSupportTeam=="Y") :?>
	<tr>
		<td valign="center" nowrap><div class="lamp-green-s"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREEN_S_ALT")?></td>
	</tr>
	<?endif;?>
	<tr>
		<td valign="center" nowrap><div class="lamp-grey"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREY_ALT")?></td>
	</tr>
</table>
<?echo EndNote();?>


<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
