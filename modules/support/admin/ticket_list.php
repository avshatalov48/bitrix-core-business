<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$message = null;

$entity_id = $PROPERTY_ID = "SUPPORT";

$bADS = $bDemo == 'Y' || $bAdmin == 'Y' || $bSupportTeam == 'Y';

if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y" && $bSupportClient!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/admin/ticket_list.php");

$err_mess = "File: ".__FILE__."<br>Line: ";
/***************************************************************************
									Функции
***************************************************************************/

function CheckFilter() // проверка введенных полей
{
	global $strError, $arFilterFields;
	reset($arFilterFields); foreach ($arFilterFields as $f) global $$f;
	$str = "";
	$arMsg = Array();

	if (trim($find_date_create1) <> '' || trim($find_date_create2) <> '')
	{
		// Дата создания
		$date_1_ok = false;
		$date1_stm = MkDateTime(ConvertDateTime($find_date_create1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($find_date_create2,"D.M.Y")." 23:59","d.m.Y H:i");

		if (!$date1_stm && trim($find_date_create1) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_CREATE_FROM")."<br>";
			$arMsg[] = array("id"=>"find_date_create1", "text"=> GetMessage("SUP_WRONG_DATE_CREATE_FROM"));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && trim($find_date_create2) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_CREATE_TILL")."<br>";
			$arMsg[] = array("id"=>"find_date_create2", "text"=> GetMessage("SUP_WRONG_DATE_CREATE_TILL"));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			//$str.= GetMessage("SUP_FROM_TILL_DATE_CREATE")."<br>";
			$arMsg[] = array("id"=>"find_date_create2", "text"=> GetMessage("SUP_FROM_TILL_DATE_CREATE"));
		}
	}

	if (trim($find_date_timestamp1) <> '' || trim($find_date_timestamp2) <> '')
	{
		// Дата изменения
		$date_1_ok = false;
		$date1_stm = MkDateTime(ConvertDateTime($find_date_timestamp1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($find_date_timestamp2,"D.M.Y")." 23:59","d.m.Y H:i");

		if (!$date1_stm && trim($find_date_timestamp1) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_TIMESTAMP_FROM")."<br>";
			$arMsg[] = array("id"=>"find_date_timestamp1", "text"=> GetMessage("SUP_WRONG_DATE_TIMESTAMP_FROM"));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && trim($find_date_timestamp2) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_TIMESTAMP_TILL")."<br>";
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("SUP_WRONG_DATE_TIMESTAMP_TILL"));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			//$str.= GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP")."<br>";
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP"));
		}
	}

	if (trim($find_date_close1) <> '' || trim($find_date_close2) <> '')
	{
		// Дата закрытия
		$date_1_ok = false;
		$date1_stm = MkDateTime(ConvertDateTime($find_date_close1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($find_date_close2,"D.M.Y")." 23:59","d.m.Y H:i");

		if (!$date1_stm && trim($find_date_close1) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_CLOSE_FROM")."<br>";
			$arMsg[] = array("id"=>"find_date_close1", "text"=> GetMessage("SUP_WRONG_DATE_CLOSE_FROM"));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && trim($find_date_close2) <> '')
		{
			//$str.= GetMessage("SUP_WRONG_DATE_CLOSE_TILL")."<br>";
			$arMsg[] = array("id"=>"find_date_close2", "text"=> GetMessage("SUP_WRONG_DATE_CLOSE_TILL"));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			//$str.= GetMessage("SUP_FROM_TILL_DATE_CLOSE")."<br>";
			$arMsg[] = array("id"=>"find_date_close2", "text"=> GetMessage("SUP_FROM_TILL_DATE_CLOSE"));
		}
	}

	// сообщений
	if (intval($find_messages1)>0 and intval($find_messages2)>0 and $find_messages1>$find_messages2)
	{
		//$str .= GetMessage("SUP_MESSAGES1_MESSAGES2")."<br>";
		$arMsg[] = array("id"=>"find_messages2", "text"=> GetMessage("SUP_MESSAGES1_MESSAGES2"));
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

/*function Support_GetUserInfo($USER_ID, &$login, &$name)
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
			$login = htmlspecialcharsbx($arUser["LOGIN"]);
			$name = htmlspecialcharsbx($arUser["NAME"]." ".$arUser["LAST_NAME"]);
			$arrUsers[$USER_ID] = array("LOGIN" => $login, "NAME" => $name);
		}
	}
}*/

function Support_GetDictionaryInfo($DID, $TYPE, &$TICKET_DICTIONARY, &$name, &$desc, &$sid)
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
		elseif(array_key_exists($TYPE, $TICKET_DICTIONARY) && array_key_exists($DID, $TICKET_DICTIONARY[$TYPE]))
		{
			$name = htmlspecialcharsbx($TICKET_DICTIONARY[$TYPE][$DID]["NAME"]);
			$desc = htmlspecialcharsbx($TICKET_DICTIONARY[$TYPE][$DID]["DESCR"]);
			$sid = $TICKET_DICTIONARY[$TYPE][$DID]["SID"];
			$arrDic[$DID] = array("NAME" => $name, "DESC" => $desc, "SID" => $sid);
		}
		else
		{
			$rsD = CTicketDictionary::GetByID($DID);
			$arD = $rsD->Fetch();
			$name = htmlspecialcharsbx($arD["NAME"]);
			$desc = htmlspecialcharsbx($arD["DESCR"]);
			$sid = $arD["SID"];
			$arrDic[$DID] = array("NAME" => $name, "DESC" => $desc, "SID" => $sid);
		}
	}
}

function Support_GetSiteInfo($SITE_ID)
{
	static $arrSites;

	$stSiteFullName = $SITE_ID;
	if (is_array($arrSites) && in_array($SITE_ID, array_keys($arrSites)))
	{
		$stSiteFullName = $arrSites[$SITE_ID];
	}
	else
	{
		$rs = CSite::GetList();
		while ($ar = $rs->Fetch())
		{
			$arrSites[$ar["ID"]] = "[".$ar["ID"]."] ".$ar["NAME"];
		}
		if (in_array($SITE_ID, array_keys($arrSites)))
		{
			$stSiteFullName = $arrSites[$SITE_ID];
		}
	}

	return htmlspecialcharsEx($stSiteFullName);
}

function  __GetDropDown($TYPE, &$TICKET_DICTIONARY)
{
	$arReturn = Array();

	if ($TYPE == "SR")
	{
		$arReturn["REFERENCE"][] = "web";
	}
	else
	{
		$arReturn["REFERENCE"][] = GetMessage("SUP_NO");
	}
	$arReturn["REFERENCE_ID"][] = "0";

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
							Обработка GET | POST
****************************************************************************/
$arrUsers = array();
$TICKET_LIST_URL = $TICKET_LIST_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_LIST_URL, 0, 4) == 'http'?'':'/').$TICKET_LIST_URL)) : "ticket_list.php";
$TICKET_EDIT_URL = $TICKET_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_EDIT_URL)) : "ticket_edit.php";
$TICKET_MESSAGE_EDIT_URL = $TICKET_MESSAGE_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_MESSAGE_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_MESSAGE_EDIT_URL)) : "ticket_message_edit.php";

if ($tf == '')
{
	$tf = ${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_TICKET_FILTER"};
}
if ($tf == '')
{
	$tf = "none";
}



$sTableID = "t_ticket_list";

if ($bADS)
	$oSort = new CAdminSorting($sTableID, "s_default", "asc");
else
	$oSort = new CAdminSorting($sTableID, "s_timestamp", "desc");

$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка

$arFilterHeads = array();

$arFilterHeads[] = GetMessage("SUP_F_TITLE_MESSAGE");
$arFilterHeads[] = GetMessage("SUP_F_ID");
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_SITE");
}
$arFilterHeads[] = GetMessage("SUP_F_LAMP");
$arFilterHeads[] = GetMessage("SUP_F_CLOSE");
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_HOLD_ON");
	$arFilterHeads[] = GetMessage("SUP_F_SPAM");
	$arFilterHeads[] = GetMessage("SUP_F_SPAM_MAYBE");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_DATE_CREATE");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_TIMESTAMP");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_OWNER");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_MODIFIED_BY");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_RESPONSIBLE");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_TICKET_TIME");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_MESSAGES_1_2");
	$arFilterHeads[] = GetMessage("SUP_F_PROBLEM_TIME_1_2");
	$arFilterHeads[] = GetMessage("SUP_F_OVERDUE_MESSAGES_1_2");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_AUTO_CLOSE_DAYS_LEFT");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_SLA");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_CATEGORY");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_CRITICALITY");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_STATUS");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_DIFFICULTY");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_MARK");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_SOURCE");
}
//$arFilterHeads[] = GetMessage("SUP_F_TITLE");
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_SUPPORT_COMMENTS");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_SUPPORTTEAM_GROUP");
}
if ($bADS || $bSupportClient = 'Y')
{
	$arFilterHeads[] = GetMessage("SUP_F_CLIENT_GROUP");
}
if ($bADS)
{
	$arFilterHeads[] = GetMessage("SUP_F_COUPON");
}

$USER_FIELD_MANAGER->AddFindFields( $entity_id, $arFilterHeads );
$filter = new CAdminFilter(
	$sTableID."_filter_id",
	$arFilterHeads
);

if ($lAdmin->IsDefaultFilter())
{
	if ($bADS)
	{

		$find_lamp = Array("red","yellow");
		$by = "s_default";
		$find_hold_on="N";
		$sort = "asc";
	}
	else
	{
		$find_lamp = Array("red","green");
		$by = "s_timestamp";
		$sort = "desc";
	}


	$find_close="N";
	$set_filter = "Y";
}


$arFilterFields = Array(
	"find",
	"find_type",
	"find_message_exact_match",
	"find_id",
	"find_id_exact_match",
	"find_site",
	"find_lamp",
	"find_date_create1",
	"find_date_create2",
	"find_date_timestamp1",
	"find_date_timestamp2",
	"find_date_close1",
	"find_date_close2",
	"find_close",
	"find_ticket_time_1",
	"find_ticket_time_2",
	"find_title",
	"find_title_exact_match",
	"find_support_comments",
	"find_support_comments_exact_match",
	"find_messages1",
	"find_messages2",
	"find_problem_time1",
	"find_problem_time2",
	"find_overdue_messages1",
	"find_overdue_messages2",
	"find_auto_close_days_left1",
	"find_auto_close_days_left2",
	"find_owner",
	"find_owner_exact_match",
	"find_created_by",
	"find_created_by_exact_match",
	"find_responsible",
	"find_responsible_exact_match",
	"find_responsible_id",
	"find_sla_id",
	"find_category_id",
	"find_criticality_id",
	"find_status_id",
	"find_difficulty_id",
	"find_mark_id",
	"find_source_id",
	"find_modified_by",
	"find_modified_by_exact_match",
	"find_message",
	"find_is_spam",
	"find_is_spam_maybe",
	"find_hold_on",
	"find_supportteam_group_id",
	"find_supportteam_group_id",
	"find_client_group_id",
	'find_coupon',
	'find_coupon_exact_match',
);

$USER_FIELD_MANAGER->AdminListAddFilterFields( $entity_id, $arFilterFields );
$lAdmin->InitFilter($arFilterFields);//инициализация фильтра




InitBVar($find_id_exact_match);
InitBVar($find_title_exact_match);
InitBVar($find_support_comments_exact_match);
InitBVar($find_owner_exact_match);
InitBVar($find_created_by_exact_match);
InitBVar($find_responsible_exact_match);
InitBVar($find_modified_by_exact_match);
InitBVar($find_message_exact_match);

if (CheckFilter())
{
	$arFilter = Array(
		"MESSAGE_EXACT_MATCH"			=> $find_message_exact_match,
		"ID"							=> ($find!="" && $find_type == "id"? $find: $find_id),
		"ID_EXACT_MATCH"				=> $find_id_exact_match,
		"SITE"							=> $find_site,
		"LAMP"							=> $find_lamp,
		"DATE_CREATE_1"					=> $find_date_create1,
		"DATE_CREATE_2"					=> $find_date_create2,
		"DATE_TIMESTAMP_1"				=> $find_date_timestamp1,
		"DATE_TIMESTAMP_2"				=> $find_date_timestamp2,
		"DATE_CLOSE_1"					=> $find_date_close1,
		"DATE_CLOSE_2"					=> $find_date_close2,
		"CLOSE"							=> $find_close,
		"PROBLEM_TIME1"							=> $find_problem_time1,
		"PROBLEM_TIME2"							=> $find_problem_time2,
		"TICKET_TIME_1"					=> $find_ticket_time_1,
		"TICKET_TIME_2"					=> $find_ticket_time_2,
		"TITLE"							=> ($find!="" && $find_type == "name"? $find: $find_title),
		"TITLE_EXACT_MATCH"				=> $find_title_exact_match,
		"MESSAGES1"						=> $find_messages1,
		"MESSAGES2"						=> $find_messages2,
		"OVERDUE_MESSAGES1"				=> $find_overdue_messages1,
		"OVERDUE_MESSAGES2"				=> $find_overdue_messages2,
		"AUTO_CLOSE_DAYS_LEFT1"			=> $find_auto_close_days_left1,
		"AUTO_CLOSE_DAYS_LEFT2"			=> $find_auto_close_days_left2,
		"OWNER"							=> ($find!="" && $find_type == "owner"? $find: $find_owner),
		"OWNER_EXACT_MATCH"				=> $find_owner_exact_match,
		"CREATED_BY"					=> $find_created_by,
		"CREATED_BY_EXACT_MATCH"		=> $find_created_by_exact_match,
		"RESPONSIBLE"					=> $find_responsible,
		"RESPONSIBLE_EXACT_MATCH"		=> $find_responsible_exact_match,
		"RESPONSIBLE_ID"				=> $find_responsible_id,
		"SLA_ID"						=> $find_sla_id,
		"CATEGORY_ID"					=> $find_category_id,
		"CRITICALITY_ID"				=> $find_criticality_id,
		"STATUS_ID"						=> $find_status_id,
		"DIFFICULTY_ID"						=> $find_difficulty_id,
		"MARK_ID"						=> $find_mark_id,
		"SOURCE_ID"						=> $find_source_id,
		"MODIFIED_BY"					=> $find_modified_by,
		"MODIFIED_BY_EXACT_MATCH"		=> $find_modified_by_exact_match,
		"MESSAGE"						=> ($find!="" && $find_type == "message"? $find: $find_message),
		"SUPPORT_COMMENTS"				=> $find_support_comments,
		"SUPPORT_COMMENTS_EXACT_MATCH"	=> $find_support_comments_exact_match,
		"IS_SPAM"						=> $find_is_spam,
		"IS_SPAM_MAYBE"					=> $find_is_spam_maybe,
		"HOLD_ON" => $find_hold_on,
		"SUPPORTTEAM_GROUP_ID"			=> $find_supportteam_group_id,
		"CLIENT_GROUP_ID"				=> $find_client_group_id,
		'COUPON'						=> $find_coupon,
		'COUPON_EXACT_MATCH'			=> $find_coupon_exact_match,
		);

	$USER_FIELD_MANAGER->AdminListAddFilter( $entity_id, $arFilter );
}
else
{
	if($e = $APPLICATION->GetException())
	{
		$GLOBALS["lAdmin"]->AddFilterError(GetMessage("SUP_FILTER_ERROR").": ".$e->GetString());
		//$message = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
	}
}

// обработка действий групповых и одиночных
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CTicket::GetList(
			'',
			'',
			$arFilter,
			null,
			"Y",
			"Y",
			"Y",
			false,
			array( "SELECT" => $lAdmin->GetVisibleHeaderColumns() )
		);

		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}


	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case "close":
				CTicket::SetTicket(array("CLOSE" => "Y"), $ID, "Y", "N");
				break;

			case "close_notify":
				CTicket::SetTicket(array("CLOSE" => "Y"), $ID, "Y", "Y");
				break;

			case "open":
				CTicket::SetTicket(array("CLOSE" => "N"), $ID, "Y", "N");
				break;

			case "open_notify":
				CTicket::SetTicket(array("CLOSE" => "N"), $ID, "Y", "Y");
				break;

			case "unmark_spam":
				CTicket::UnMarkAsSpam($ID);
				break;
			case "maybe_spam":
				CTicket::MarkAsSpam($ID, "N");
				break;
			case "mark_spam":
				CTicket::MarkAsSpam($ID);
				break;
			case "mark_spam_delete":
				CTicket::MarkAsSpam($ID);
				CTicket::Delete($ID);
				break;
			case "delete":
				CTicket::Delete($ID);
				if(isset($_REQUEST['redirectafter']) && $_REQUEST['redirectafter'] === "Y")
				{
					LocalRedirect($TICKET_LIST_URL."?lang=".LANGUAGE_ID);
				}
				break;
		}
	}
}

$get_extra_names = "N";

// инициализация списка - выборка данных
$TICKET_DICTIONARY = CTicketDictionary::GetDropDownArray();

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,"align" => "center");

$arHeaders[] = array("id"=>"LAMP", "content"=>GetMessage("SUP_F_LAMP"), "sort"=>"s_lamp", "default"=>true,"align" => "center", "valign" => "middle");

$arHeaders[] = array("id"=>"TITLE", "content"=>GetMessage('SUP_TITLE'),	"sort"=>"s_title", "default"=>true);

if ($bADS)
	$arHeaders[] = array("id"=>"DATE_CREATE","content"=>GetMessage("SUP_DATE_CREATE"), "default"=>true, "sort"=>"s_date_create" );


$arHeaders[] = array("id"=>"TIMESTAMP_X", "content"=>GetMessage('SUP_TIMESTAMP'),"sort"=> "s_timestamp", "default"=>($bADS ? false : true ));

if ($bADS)
{
	$arHeaders[] = array("id"=>"LAST_MESSAGE_DATE", "content"=>GetMessage('SUP_LAST_MESSAGE_DATE_EX'),"sort"=> "s_default", "default"=>true);
	$arHeaders[] = array("id"=>"LAST_MESSAGE_DATE_EX", "content"=>GetMessage('SUP_LAST_MESSAGE_DATE'),"sort"=> "s_last_message_date");
}

$arHeaders[] = array("id"=>"MESSAGES", "content"=>GetMessage('SUP_MESSAGES'), "default"=>true, "sort" => "s_messages");

$arHeaders[] = array("id"=>"SLA_ID", "content"=>GetMessage("SUP_SLA"), "default"=>true, "sort"=>"s_sla");
$arHeaders[] = array("id"=>"CATEGORY_ID", "content"=>GetMessage("SUP_CATEGORY"), "default"=>false, "sort" => "s_category");
$arHeaders[] = array("id"=>"CRITICALITY_ID", "content"=>GetMessage("SUP_CRITICALITY"), "default"=>false, "sort" => "s_criticality");
$arHeaders[] = array("id"=>"SITE_ID", "content"=>GetMessage("SUP_SITE_ID"), "default"=>false, "sort" => "s_site_id");

$arHeaders[] = array("id"=>"RESPONSIBLE_USER_ID", "content"=>GetMessage("SUP_RESPONSIBLE"), "default"=>true, "sort" => "s_responsible");
$arHeaders[] = array("id"=>"STATUS_ID", "content"=>GetMessage("SUP_STATUS"), "default"=>false, "sort" => "s_status");

$arHeaders[] = array("id"=>"AUTO_CLOSE_DAYS_LEFT", "content"=>GetMessage("SUP_F_AUTO_CLOSE_DAYS_LEFT"), "sort" => "s_auto_close_days_left", "default"=>false);

if ($bADS)
	$arHeaders[] = array("id"=>"DIFFICULTY_ID", "content"=>GetMessage("SUP_DIFFICULTY_1"), "default"=>false, "sort" => "s_difficulty");

$arHeaders[] = array("id"=>"MARK_ID", "content"=>GetMessage("SUP_MARK"), "default"=>false, "sort" => "s_mark");

if ($bADS)
	$arHeaders[] = array("id"=>"PROBLEM_TIME", "content"=>GetMessage("SUP_PROBLEM_TIME"), "default"=>false, "sort" => "s_problem_time");

if ($bADS)
{
	$arHeaders[] = array("id"=>"COUPON", "content"=>GetMessage("SUP_COUPON"), "default"=>false, "sort" => "s_coupon");
}

if ($bADS)
{
	$arHeaders[] = array("id"=>"SUPPORT_DEADLINE", "content"=>GetMessage("SUP_DEADLINE"), "default"=>true, "sort" => "s_deadline");
}

$USER_FIELD_MANAGER->AdminListAddHeaders( $entity_id, $arHeaders );

// заголовок списка
$lAdmin->AddHeaders($arHeaders);

$get_user_name = "N";

global $by, $order;

$rsData = CTicket::GetList(
	$by,
	$order,
	$arFilter,
	null,
	"Y",
	$get_user_name,
	$get_extra_names,
	false,
	array( "SELECT" => $lAdmin->GetVisibleHeaderColumns(), 'NAV_PARAMS' => array('nPageSize' => CAdminResult::GetNavSize($sTableID), 'bShowAll' => false) )
);

$rsData = new CAdminResult($rsData, $sTableID);

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SUP_PAGES")));

// построение списка
$arRows = array();

$aUserIDs = array();
$arGuestIDs = array();
$arUsersPref = array("RESPONSIBLE", "OWNER", "CREATED", "MODIFIED");
$arGuestsPref = array("OWNER", "CREATED", "MODIFIED");

while($arRes = $rsData->NavNext(true, "f_"))
{
	$lamp = "/bitrix/images/support/$f_LAMP.gif";
	$lamp_alt = GetMessage("SUP_".mb_strtoupper($f_LAMP)."_ALT");

	/*if ($get_user_name=="N")
	{
		Support_GetUserInfo($f_RESPONSIBLE_USER_ID, $f_RESPONSIBLE_LOGIN, $f_RESPONSIBLE_NAME);
		Support_GetUserInfo($f_OWNER_USER_ID, $f_OWNER_LOGIN, $f_OWNER_NAME);
		Support_GetUserInfo($f_MODIFIED_USER_ID, $f_MODIFIED_LOGIN, $f_MODIFIED_NAME);
	}*/

	foreach($arUsersPref as $cup)
	{
		$aUserIDs[] = $arRes[$cup . "_USER_ID"];
	}
	foreach($arGuestsPref as $cgp)
	{
		$arGuestIDs[] = $arRes[$cgp . "_GUEST_ID"];
	}

	if ($get_extra_names=="N")
	{
		Support_GetDictionaryInfo($f_CATEGORY_ID, "C" , $TICKET_DICTIONARY, $f_CATEGORY_NAME, $f_CATEGORY_DESC, $f_CATEGORY_SID);
		Support_GetDictionaryInfo($f_CRITICALITY_ID, "K" , $TICKET_DICTIONARY, $f_CRITICALITY_NAME, $f_CRITICALITY_DESC, $f_CRITICALITY_SID);
		Support_GetDictionaryInfo($f_STATUS_ID, "S" , $TICKET_DICTIONARY,$f_STATUS_NAME, $f_STATUS_DESC, $f_STATUS_SID);
		Support_GetDictionaryInfo($f_DIFFICULTY_ID, "D" , $TICKET_DICTIONARY, $f_DIFFICULTY_NAME, $f_DIFFICULTY_DESC, $f_DIFFICULTY_SID);
		Support_GetDictionaryInfo($f_MARK_ID, "M" , $TICKET_DICTIONARY, $f_MARK_NAME, $f_MARK_DESC, $f_MARK_SID);
		Support_GetDictionaryInfo($f_SOURCE_ID, "SR" , $TICKET_DICTIONARY, $f_SOURCE_NAME, $f_SOURCE_DESC, $f_SOURCE_SID);

		Support_GetSLAInfo($f_SLA_ID, $f_SLA_NAME, $f_SLA_DESCRIPTION);
	}

	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$arRow = array("objRow" => $row, "arFields" => $arRes);
	$USER_FIELD_MANAGER->AddUserFields( $entity_id, $arRes, $row );

	$row->AddViewField("ID", '<a title="'.GetMessage("SUP_EDIT_TICKET").'" href="'.$TICKET_EDIT_URL.'?ID='.$f_ID.'&lang='.LANG.'">'.$f_ID.'</a>');

	$ID_HTML = '';

	if ($bADS)
	{
		if ($f_IS_SPAM <> '')
			$ID_HTML .= '<br><span style="color:#428857">'.GetMessage("SUP_SPAM").($f_IS_SPAM=="Y" ? "!" : "?").'</span>';

		if ($f_DATE_CLOSE == '')
		{
			if ($f_IS_OVERDUE=="Y")
				$ID_HTML .= '<br><span class="required">'.GetMessage("SUP_OVERDUE").'</span>';
			elseif($f_IS_NOTIFIED=="Y")
				$ID_HTML .= '<br><span style="color:#FF3300">'.GetMessage("SUP_EXPIRING").'</span>';
		}
	}


	if (intval($f_USERS_ONLINE)>0)
	{
		$ID_HTML .= '<img src="/bitrix/images/1.gif" width="1" height="3"><br>
			<nobr>('.GetMessage("SUP_ONLINE").' - <span class="supportrequired">'.intval($f_USERS_ONLINE).'</span>)</nobr>';
	}


	$row->AddViewField("LAMP", '<div class="lamp-'.str_replace("_","-",$f_LAMP).'" title="'.$lamp_alt.'"></div>'.$ID_HTML);

	$TITLE_HTML = (trim($f_TITLE) == '') ? "&nbsp;" : TxtToHTML($f_TITLE, true, 30);
	$TITLE_HTML .= "<br>";

	if (COption::GetOptionString('support', "SHOW_COMMENTS_IN_TICKET_LIST") == 'Y')
	{
		if (($bADS) && trim($f_SUPPORT_COMMENTS) <> '')
			$TITLE_HTML .= '<br><img src="/bitrix/images/1.gif" width="1" height="5" border="0" alt=""><br>[&nbsp;'.TxtToHTML($f_SUPPORT_COMMENTS, true, 30).'&nbsp;]';
	}

	$row->AddViewField("TITLE", $TITLE_HTML);

	$row->AddViewField("SITE_ID", Support_GetSiteInfo($f_SITE_ID));

	$DATE_CREATE_HTML = "";
	if ($bADS)
	{
		if (intval($f_PROBLEM_TIME)>0)
		{
			$str = "";
			$days = intval($f_PROBLEM_TIME/1440);
			if ($days>0)
			{
				$str .= $days."&nbsp;".GetMessage("SUP_DAYS")." ";
				$f_PROBLEM_TIME = $f_PROBLEM_TIME - $days*1440;
			}

			$hours = intval($f_PROBLEM_TIME/60);
			if ($hours>0)
			{
				$str .= $hours."&nbsp;".GetMessage("SUP_HOURS")." ";
				$f_PROBLEM_TIME = $f_PROBLEM_TIME - $hours*60;
			}

			$str .= ($f_PROBLEM_TIME%60)."&nbsp;".GetMessage("SUP_MINUTES");

			$row->AddViewField("PROBLEM_TIME", $str);
		}
		else
		{
			$row->AddViewField("PROBLEM_TIME", "&nbsp;");
		}


		$arr = explode(" ",$f_DATE_CREATE);
		$DATE_CREATE_HTML = $arr[0]."&nbsp;".$arr[1]."<br>";

		if ($f_SOURCE_NAME <> '')
			$DATE_CREATE_HTML .= "<nobr>[".$f_SOURCE_NAME."]&nbsp;</nobr><br>";

		if ($f_OWNER_SID <> '')
			$DATE_CREATE_HTML .= TxtToHtml($f_OWNER_SID)."&nbsp;&nbsp;<br>";

		/*
		if (intval($f_OWNER_USER_ID)>0)
			$DATE_CREATE_HTML .= '[<a title="'.GetMessage("SUP_USER_PROFILE").'" href="/bitrix/admin/user_edit.php?lang='.LANG.'&ID='.$f_OWNER_USER_ID.'">'.$f_OWNER_USER_ID.'</a>]  ('.$f_OWNER_LOGIN.') '.$f_OWNER_NAME;

		$row->AddViewField("DATE_CREATE", $DATE_CREATE_HTML);
		*/
		$row->AddViewField("LAST_MESSAGE_DATE_EX", $f_LAST_MESSAGE_DATE);
	}

	$arRow["arFields"]["t_DATE_CREATE_HTML"] = $DATE_CREATE_HTML;

	/*
	$TIMESTAMP_X_HTML = $f_TIMESTAMP_X."<br>";

	if (strlen($f_MODIFIED_MODULE_NAME)<=0 || $f_MODIFIED_MODULE_NAME=="support")
	{
		if ($bADS)
			$TIMESTAMP_X_HTML .= '[<a title="'.GetMessage("SUP_USER_PROFILE").'" href="/bitrix/admin/user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_USER_ID.'">'.$f_MODIFIED_USER_ID.'</a>] ('.$f_MODIFIED_LOGIN.') '.$f_MODIFIED_NAME;
		else
			$TIMESTAMP_X_HTML .= "[".$f_MODIFIED_USER_ID."] (".$f_MODIFIED_LOGIN.") ".$f_MODIFIED_NAME;
	}
	else
	{
		$TIMESTAMP_X_HTML .= $f_MODIFIED_MODULE_NAME;
	}

	$row->AddViewField("TIMESTAMP_X", $TIMESTAMP_X_HTML);
	*/
	$row->AddViewField("MESSAGES", '<a title="'.GetMessage("SUP_EDIT_TICKET").'" href="'.$TICKET_EDIT_URL.'?ID='.$f_ID.'&lang='.LANG.'">'.$f_MESSAGES.'</a>');

	if ($f_SLA_NAME <> '')
		$row->AddViewField("SLA_ID", $f_SLA_NAME);
	else
		$row->AddViewField("SLA_ID", '&nbsp;');

	if ($f_CATEGORY_NAME <> '')
		$row->AddViewField("CATEGORY_ID", $f_CATEGORY_NAME);
	else
		$row->AddViewField("CATEGORY_ID", '&nbsp;');


	if ($f_CRITICALITY_NAME <> '')
		$row->AddViewField("CRITICALITY_ID", $f_CRITICALITY_NAME);
	else
		$row->AddViewField("CRITICALITY_ID", '&nbsp;');

	/*
	$INFO2_HTML = '';
	if ($bADS)
	{
		if (intval($f_RESPONSIBLE_USER_ID)>0)
		{
			$INFO2_HTML .= '('.$f_RESPONSIBLE_LOGIN.') '.$f_RESPONSIBLE_NAME;
			$INFO2_HTML .= '<br>';
		}
	}

	$row->AddViewField("RESPONSIBLE_USER_ID", $INFO2_HTML);
	*/

	if( $f_SUPPORT_DEADLINE <> '' )
		$row->AddViewField("SUPPORT_DEADLINE", $f_SUPPORT_DEADLINE);
	else
		$row->AddViewField("SUPPORT_DEADLINE", '&nbsp;');


	if ($f_AUTO_CLOSE_DAYS_LEFT < 0)
	{
		$f_AUTO_CLOSE_DAYS_LEFT = '-';
	}

	$row->AddViewField("AUTO_CLOSE_DAYS_LEFT", $f_AUTO_CLOSE_DAYS_LEFT);

	if ($bADS)
	{
		if ($f_DIFFICULTY_NAME <> '')
			$row->AddViewField("DIFFICULTY_ID",$f_DIFFICULTY_NAME);
		else
			$row->AddViewField("DIFFICULTY_ID", '&nbsp;');
	};

	if ($f_STATUS_NAME <> '')
		$row->AddViewField("STATUS_ID",$f_STATUS_NAME);
	else
		$row->AddViewField("STATUS_ID", '&nbsp;');

	if ($f_MARK_NAME <> '')
		$row->AddViewField("MARK_ID", $f_MARK_NAME);
	else
		$row->AddViewField("MARK_ID", '&nbsp;');


	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("SUP_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect($TICKET_EDIT_URL.'?ID='.$f_ID.'&lang='.LANG)
	);

	$arActions[] = array("SEPARATOR" => true);

	if ($f_DATE_CLOSE == '')
	{
		$arActions[] = array(
			"TEXT"	=> GetMessage("SUP_CLOSE"),
			"ACTION"=>$lAdmin->ActionAjaxReload("/bitrix/admin/ticket_list.php?ID=".$f_ID."&action=close&lang=".LANGUAGE_ID."&".bitrix_sessid_get()),
			);
	}
	else
	{
		$arActions[] = array(
			"TEXT"	=> GetMessage("SUP_OPEN"),
			//"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=open&lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
			"ACTION"=>$lAdmin->ActionAjaxReload("/bitrix/admin/ticket_list.php?ID=".$f_ID."&action=open&lang=".LANGUAGE_ID."&".bitrix_sessid_get())
			);
	}

	if ($bSupportTeam=="Y" || $bAdmin=="Y")
	{
		if ($f_IS_SPAM <> '')
		{
			$arActions[] = array(
				"TEXT"	=> GetMessage("SUP_UNMARK_SPAM"),
				//"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=unmark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
				"ACTION"=>$lAdmin->ActionAjaxReload("/bitrix/admin/ticket_list.php?ID=".$f_ID."&action=unmark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get())
				);
		}

		if ($f_IS_SPAM!="N")
		{
			$arActions[] = array(
				"TEXT"	=> GetMessage("SUP_MAYBE_SPAM"),
				//"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=maybe_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get()
				"ACTION"=>$lAdmin->ActionAjaxReload("/bitrix/admin/ticket_list.php?ID=".$f_ID."&action=maybe_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get())
				);
		}

		if ($f_IS_SPAM!="Y" && $bAdmin=="Y")
		{
			$arActions[] = array(
				"TEXT"	=> GetMessage("SUP_MARK_SPAM"),
				//"LINK"	=> "/bitrix/admin/ticket_edit.php?ID=".$ID."&action=mark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get()
				"ACTION"=>$lAdmin->ActionAjaxReload("/bitrix/admin/ticket_list.php?ID=".$f_ID."&action=mark_spam&lang=".LANGUAGE_ID."&".bitrix_sessid_get())
				);
		}

		if ($bAdmin=="Y" || $bDemo=="Y")
		{
			$arActions[] = array(
				"TEXT"	=> GetMessage("SUP_MARK_SPAM_DELETE"),
				//"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_MARK_AS_SPAM_DELETE_CONFIRM")."')) window.location='/bitrix/admin/ticket_list.php?ARR_TICKET[]=".$ID."&action=mark_spam_delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&set_default=Y';",
				//"WARNING"=>"Y",
				"ACTION"=>"if(confirm('".GetMessage('SUP_CONF_ACTION_MARK_AS_SPAM_DELETE')."')) ".$lAdmin->ActionDoGroup($f_ID, "mark_spam_delete"),
			);

			$arActions[] = array("SEPARATOR" => true);

			$arActions[] = array(
				"ICON" => "delete",
				"TEXT"	=> GetMessage("SUP_DELETE"),
				//"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_DELETE_TICKET_CONFIRM")."')) window.location='/bitrix/admin/ticket_list.php?ARR_TICKET[]=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&set_default=Y';",
				//"WARNING"=>"Y",
				"ACTION"=>"if(confirm('".GetMessage('SUP_DELETE_TICKET_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
				);
		}
	}


	$row->AddActions($arActions);

	$arRows[] = $arRow;
}

//$arUsersPref = array("RESPONSIBLE", "OWNER", "CREATED", "MODIFIED");
//$arGuestsPref = array("OWNER", "CREATED", "MODIFIED");

$arStrUsersM = CTicket::GetUsersPropertiesArray($aUserIDs,$arGuestIDs);
foreach($arRows as $k => $v)
{
	$rUserID = intval($v["arFields"]["RESPONSIBLE_USER_ID"]);
	$info2Html = '';
	$postFixS = ($bADS ? "" : "_S");
	if($bADS && $rUserID > 0)
	{
		$info2Html .= $arStrUsersM["arUsers"][$rUserID]["HTML_NAME"] . "<br>";
	}
	$v["objRow"]->AddViewField("RESPONSIBLE_USER_ID", $info2Html);


	$dateCreateHtml = $v["arFields"]["t_DATE_CREATE_HTML"];
	$oUserID = intval($v["arFields"]["OWNER_USER_ID"]);
	$oGuestID = intval($v["arFields"]["OWNER_GUEST_ID"]);
	if($oUserID > 0)
	{
		$dateCreateHtml .= " " . $arStrUsersM["arUsers"][$oUserID]["HTML_NAME" . $postFixS];
	}
	elseif($oGuestID > 0)
	{
		$dateCreateHtml .= " " . $arStrUsersM["arGuests"][$oGuestID]["HTML_NAME" . $postFixS];
	}
	$v["objRow"]->AddViewField("DATE_CREATE", $dateCreateHtml);


	$mUserID = intval($v["arFields"]["MODIFIED_USER_ID"]);
	$mGuestID = intval($v["arFields"]["MODIFIED_GUEST_ID"]);
	$timeStampXHtml = isset($v["arFields"]["TIMESTAMP_X"]) ? htmlspecialcharsbx($v["arFields"]["TIMESTAMP_X"]) . "<br>" : "";
	$modifiedModuleName = isset($v["arFields"]["MODIFIED_MODULE_NAME"]) ? htmlspecialcharsbx($v["arFields"]["MODIFIED_MODULE_NAME"]) :  "";
	if($modifiedModuleName == '' || $modifiedModuleName=="support")
	{
		if($mUserID > 0)
		{
			$timeStampXHtml .= " " . $arStrUsersM["arUsers"][$mUserID]["HTML_NAME" . $postFixS];
		}
		elseif($mGuestID > 0)
		{
			$timeStampXHtml .= " " . $arStrUsersM["arGuests"][$mGuestID]["HTML_NAME" . $postFixS];
		}
	}
	else
	{
		$timeStampXHtml .= $modifiedModuleName;
	}
	$v["objRow"]->AddViewField("TIMESTAMP_X", $timeStampXHtml);

}


// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$arr = array();
if ($bAdmin=="Y" || $bDemo=="Y")
{
	$arr["close"] = GetMessage("SUP_CLOSE");
	$arr["close_notify"] = GetMessage("SUP_CLOSE_NOTIFY");
	$arr["open"] = GetMessage("SUP_OPEN");
	$arr["open_notify"] = GetMessage("SUP_OPEN_NOTIFY");

	if (CModule::IncludeModule("mail"))
	{
		$arr["unmark_spam"] = GetMessage("SUP_UNMARK_SPAM");
		$arr["maybe_spam"] = GetMessage("SUP_MAYBE_SPAM");
		$arr["mark_spam"] = GetMessage("SUP_MARK_SPAM");
		$arr["mark_spam_delete"] = GetMessage("SUP_MARK_SPAM_DELETE");
	}
	$arr["delete"] = GetMessage("SUP_DELETE");
}
elseif ($bSupportTeam=="Y")
{
	$arr["close"] = GetMessage("SUP_CLOSE");
	$arr["open"] = GetMessage("SUP_OPEN");

	if (CModule::IncludeModule("mail"))
	{
		$arr["unmark_spam"] = GetMessage("SUP_UNMARK_SPAM");
		$arr["maybe_spam"] = GetMessage("SUP_MAYBE_SPAM");
	}

}


$lAdmin->AddGroupActionTable($arr);

$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"ticket_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("MAIN_ADD")
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SUP_TICKETS_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>


<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">

		<?
		$arr = array(
			"reference" => array(
				GetMessage("SUP_F_ID"),
				//GetMessage('SUP_F_MESSAGE'),
				//GetMessage('SUP_F_TITLE'),
				GetMessage('SUP_F_OWNER'),
			),
			"reference_id" => array(
				"id",
				//"message",
				//"name",
				"owner",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_TITLE_MESSAGE")?>:</td>
	<td><input type="text" name="find_message" size="47" value="<?=htmlspecialcharsbx($find_message)?>"><?=InputType("checkbox", "find_message_exact_match", "Y", $find_message_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?=htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?if ($bADS):?>
<tr valign="top">
	<td valign="top"><?=GetMessage("SUP_F_SITE")?>:</td>
	<td><?
		$ref = array();
		$ref_id = array();
		$rs = CSite::GetList();
		while ($ar = $rs->Fetch())
		{
			$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
			$ref_id[] = $ar["ID"];
		}
		echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
		?></td>
</tr>
<?endif;?>
<tr valign="top">
	<td valign="top"><?=GetMessage("SUP_F_LAMP")?>:</td>
	<td><?
		if ($bADS)
		{
			$size=5;
			$arr = array(
				"reference" => array(
					GetMessage("SUP_RED"),
					GetMessage("SUP_YELLOW"),
					GetMessage("SUP_GREEN"),
					GetMessage("SUP_GREEN_S"),
					GetMessage("SUP_GREY")
				),
				"reference_id" => array(
					"red",
					"yellow",
					"green",
					"green_s",
					"grey"
				));
		}
		else
		{
			$size=3;
			$arr = array(
				"reference" => array(
					GetMessage("SUP_RED"),
					GetMessage("SUP_GREEN"),
					GetMessage("SUP_GREY")
				),
				"reference_id" => array(
					"red",
					"green",
					"grey"));
		}
		echo SelectBoxMFromArray("find_lamp[]", $arr, $find_lamp, "", false, $size);
		?></td>
</tr>
<tr>
	<td><?=GetMessage("SUP_F_CLOSE")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("SUP_CLOSED"), GetMessage("SUP_OPENED")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_close", $arr, htmlspecialcharsbx($find_close), GetMessage("SUP_ALL"));
		?></td>
</tr>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_HOLD_ON")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("SUP_YES"), GetMessage("SUP_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_hold_on", $arr, htmlspecialcharsbx($find_hold_on), GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr>
	<td><?=GetMessage("SUP_F_SPAM")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("SUP_YES"), GetMessage("SUP_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_is_spam", $arr, htmlspecialcharsbx($find_is_spam), GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr>
	<td>
		<?=GetMessage("SUP_F_SPAM_MAYBE")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("SUP_YES"), GetMessage("SUP_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_is_spam_maybe", $arr, htmlspecialcharsbx($find_is_spam_maybe), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr valign="center">
	<td><?echo GetMessage("SUP_F_DATE_CREATE").":"?></td>
	<td><?echo CalendarPeriod("find_date_create1", $find_date_create1, "find_date_create2", $find_date_create2, "form1","Y")?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr valign="center">
	<td><?echo GetMessage("SUP_F_TIMESTAMP").":"?></td>
	<td><?echo CalendarPeriod("find_date_timestamp1", $find_date_timestamp1, "find_date_timestamp2", $find_date_timestamp2, "form1","Y")?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_OWNER")?>:</td>
	<td><input type="text" name="find_owner" size="47" value="<?=htmlspecialcharsbx($find_owner)?>"><?=InputType("checkbox", "find_owner_exact_match", "Y", $find_owner_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?}?>

<?if ($bADS):?>
<!--
<tr>
	<td><?=GetMessage("SUP_F_CREATED_BY")?></td>
	<td><input type="text" name="find_created_by" size="47" value="<?=htmlspecialcharsbx($find_created_by)?>"><?=InputType("checkbox", "find_created_by_exact_match", "Y", $find_created_by_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
-->
<?endif;?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_MODIFIED_BY")?>:</td>
	<td><input type="text" name="find_modified_by" size="47" value="<?=htmlspecialcharsbx($find_modified_by)?>"><?=InputType("checkbox", "find_modified_by_exact_match", "Y", $find_modified_by_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td valign="top"><?=GetMessage("SUP_F_RESPONSIBLE")?>:</td>
	<td><?
		$ref = $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicket::GetSupportTeamList();
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_responsible_id", $arr, htmlspecialcharsbx($find_responsible_id), GetMessage("SUP_ALL"));
		?><br><input type="text" name="find_responsible" size="47" value="<?=htmlspecialcharsbx($find_responsible)?>"><?=InputType("checkbox", "find_responsible_exact_match", "Y", $find_responsible_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_TICKET_TIME")?>:</td>
	<td>
		<input type="text" name="find_ticket_time_1" size="10" value="<?=htmlspecialcharsbx($find_ticket_time_1)?>"><?echo "&nbsp;".GetMessage("SUP_TILL")."&nbsp;"?><input type="text" name="find_ticket_time_2" size="10" value="<?=htmlspecialcharsbx($find_ticket_time_2)?>"></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_MESSAGES_1_2")?>:</td>
	<td>
		<input type="text" name="find_messages1" size="10" value="<?=htmlspecialcharsbx($find_messages1)?>"><?echo "&nbsp;".GetMessage("SUP_TILL")."&nbsp;"?><input type="text" name="find_messages2" size="10" value="<?=htmlspecialcharsbx($find_messages2)?>"></td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_PROBLEM_TIME_1_2")?>:</td>
	<td>
		<input type="text" name="find_problem_time1" size="10" value="<?=htmlspecialcharsbx($find_problem_time1)?>"><?echo "&nbsp;".GetMessage("SUP_TILL")."&nbsp;"?><input type="text" name="find_problem_time2" size="10" value="<?=htmlspecialcharsbx($find_problem_time2)?>"></td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_OVERDUE_MESSAGES_1_2")?>:</td>
	<td>
		<input type="text" name="find_overdue_messages1" size="10" value="<?=htmlspecialcharsbx($find_overdue_messages1)?>"><?echo "&nbsp;".GetMessage("SUP_TILL")."&nbsp;"?><input type="text" name="find_overdue_messages2" size="10" value="<?=htmlspecialcharsbx($find_overdue_messages2)?>"></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_AUTO_CLOSE_DAYS_LEFT")?>:</td>
	<td>
		<input type="text" name="find_auto_close_days_left1" size="10" value="<?=htmlspecialcharsbx($find_auto_close_days_left1)?>"><?echo "&nbsp;".GetMessage("SUP_TILL")."&nbsp;"?><input type="text" name="find_auto_close_days_left2" size="10" value="<?=htmlspecialcharsbx($find_auto_close_days_left2)?>"></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_SLA")?>:</td>
	<td><?
		$ref = $ref_id = array();
		$z = CTicketSLA::GetDropDown("ALL");
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_sla_id", $arr, htmlspecialcharsbx($find_sla_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_CATEGORY")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_category_id", __GetDropDown("C", $TICKET_DICTIONARY), htmlspecialcharsbx($find_category_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_CRITICALITY")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_criticality_id", __GetDropDown("K", $TICKET_DICTIONARY), htmlspecialcharsbx($find_criticality_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_STATUS")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_status_id", __GetDropDown("S", $TICKET_DICTIONARY), htmlspecialcharsbx($find_status_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_DIFFICULTY")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_difficulty_id", __GetDropDown("D", $TICKET_DICTIONARY), htmlspecialcharsbx($find_difficulty_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_MARK")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_mark_id", __GetDropDown("M", $TICKET_DICTIONARY), htmlspecialcharsbx($find_mark_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?}?>

<?if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_SOURCE")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_source_id", __GetDropDown("SR", $TICKET_DICTIONARY), htmlspecialcharsbx($find_source_id), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?} /*?>
<tr>
	<td><?=GetMessage("SUP_F_TITLE")?>:</td>
	<td><input type="text" name="find_title" size="47" value="<?=htmlspecialcharsbx($find_title)?>"><?=InputType("checkbox", "find_title_exact_match", "Y", $find_title_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?*/
if ($bADS) {?>
<tr>
	<td><?=GetMessage("SUP_F_SUPPORT_COMMENTS")?>:</td>
	<td><input type="text" name="find_support_comments" size="47" value="<?=htmlspecialcharsbx($find_support_comments)?>"><?=InputType("checkbox", "find_support_comments_exact_match", "Y", $find_support_comments_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?}?>

<?
if ($bADS)
{
	$arr = array('REFERENCE' => array(), 'REFERENCE_ID' => array());
	if ($bAdmin=='Y' || $bDemo=='Y')
	{
		$rsGroups = CSupportUserGroup::GetList(array('NAME' => 'ASC'), array('=IS_TEAM_GROUP' => 'Y'));
		while ($arGroup = $rsGroups->Fetch())
		{
			$arr['REFERENCE'][] = $arGroup['NAME'];
			$arr['REFERENCE_ID'][] = $arGroup['ID'];
		}
	}
	else
	{
		$rsGroups = CSupportUserGroup::GetUserGroupList(array('GROUP_NAME' => 'ASC'), array('USER_ID' => $USER->GetID(), '=IS_TEAM_GROUP' => 'Y'));
		while ($arGroup = $rsGroups->Fetch())
		{
			$arr['REFERENCE'][] = $arGroup['GROUP_NAME'];
			$arr['REFERENCE_ID'][] = $arGroup['GROUP_ID'];
		}
	}
?>
<tr>
	<td><?=GetMessage('SUP_F_SUPPORTTEAM_GROUP')?>:</td>
	<td><?=SelectBoxMFromArray('find_supportteam_group_id[]', $arr, $find_supportteam_group_id, GetMessage('SUP_ALL'), false, ((count($arr['REFERENCE']) < 7) ? (count($arr['REFERENCE']) + 1) : 7))?></td>
</tr>
<?
}

if ($bADS || $bSupportClient = 'Y')
{

	$arr = array('REFERENCE' => array(), 'REFERENCE_ID' => array());
	if ($bAdmin=='Y' || $bDemo=='Y')
	{
		$rsGroups = CSupportUserGroup::GetList(array('NAME' => 'ASC'), array('!=IS_TEAM_GROUP' => 'Y'));
		while ($arGroup = $rsGroups->Fetch())
		{
			$arr['REFERENCE'][] = $arGroup['NAME'];
			$arr['REFERENCE_ID'][] = $arGroup['ID'];
		}
	}
	else
	{
		$arGroupFilter = array('!=IS_TEAM_GROUP' => 'Y');
		if($bSupportTeam != 'Y')
		{
			$arGroupFilter['USER_ID'] = $USER->GetID();
		}
		$rsGroups = CSupportUserGroup::GetUserGroupList(array('GROUP_NAME' => 'ASC'), $arGroupFilter);
		while ($arGroup = $rsGroups->Fetch())
		{
			$arr['REFERENCE'][] = $arGroup['GROUP_NAME'];
			$arr['REFERENCE_ID'][] = $arGroup['GROUP_ID'];
		}
	}
?>
<tr>
	<td><?=GetMessage('SUP_F_CLIENT_GROUP')?>:</td>
	<td><?=SelectBoxMFromArray('find_client_group_id[]', $arr, $find_client_group_id, GetMessage('SUP_ALL'), false, ((count($arr['REFERENCE'])) < 7 ? (count($arr['REFERENCE']) + 1) : 7))?></td>
</tr>
<?
}

if ($bADS)
{
?>
<tr>
	<td><?=GetMessage("SUP_F_COUPON")?>:</td>
	<td><input type="text" name="find_coupon" size="47" value="<?=htmlspecialcharsbx($find_coupon)?>"><?=InputType("checkbox", "find_coupon_exact_match", "Y", $find_coupon_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
}
$USER_FIELD_MANAGER->AdminListShowFilter( $entity_id );
$filter->Buttons(array("table_id"=>$sTableID, "url"=>"ticket_list.php?lang=".LANG, "form"=>"form1"));
$filter->End();
?>
</form>
<?
$aContext = array(
	array(
		"TEXT" => GetMessage("SUP_ADD"),
		"LINK"	=> $TICKET_EDIT_URL."?lang=".LANG,
		"TITLE"	=> GetMessage("SUP_ADD")
	),
);

?>
<?$lAdmin->DisplayList();?>

<?echo BeginNote();?>
<table border="0" cellspacing="6" cellpadding="0">
	<tr>
		<td valign="center" colspan="2" nowrap><?=GetMessage("SUP_TICKET_STATUS")?>:</td>
	</tr>
	<tr>
		<td valign="center" nowrap><div class="lamp-red"></div></td>
		<td valign="center" nowrap><?echo ($bADS) ? GetMessage("SUP_RED_ALT") : GetMessage("SUP_RED_ALT_2")?></td>
	</tr>
	<?if ($bAdmin=="Y" || $bDemo=="Y") {?>
	<tr>
		<td valign="center" nowrap><div class="lamp-yellow"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_YELLOW_ALT")?></td>
	</tr>
	<?}?>
	<tr>
		<td valign="center" nowrap><div class="lamp-green"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREEN_ALT")?></td>
	</tr>
	<?if ($bADS) {?>
	<tr>
		<td valign="center" nowrap><div class="lamp-green-s"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREEN_S_ALT")?></td>
	</tr>
	<?}?>
	<tr>
		<td valign="center" nowrap><div class="lamp-grey"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREY_ALT")?></td>
	</tr>
</table>
<?echo EndNote();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>