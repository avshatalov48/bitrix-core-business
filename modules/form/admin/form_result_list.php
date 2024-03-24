<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2006 Bitrix           #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_form_result_list_".md5($_REQUEST['WEB_FORM_ID']);
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

CModule::IncludeModule("form");

ClearVars();

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$old_module_version = CForm::IsOldVersion();
$bSimple = (COption::GetOptionString("form", "SIMPLE", "N") == "Y") ? true : false;

$WEB_FORM_ID = intval($_REQUEST['WEB_FORM_ID']);
$arForm = CForm::GetByID_admin($WEB_FORM_ID);

$aMenu = array();

if (false === $arForm)
{
	define('BX_ADMIN_FORM_MENU_OPEN', 1);
	if($_REQUEST["mode"] == "list")
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	}
	else
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	}

	$adminPage->ShowSectionIndex("menu_webforms_list", "form");

	if($_REQUEST["mode"] == "list")
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	}
	else
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}

	die();
}
else
{
	$dbRes = CFormCrm::GetByFormID($WEB_FORM_ID);
	$arFormCrmLink = $dbRes->Fetch();
}

$HELP_FILE_ACCESS = $APPLICATION->GetFileAccessPermission("/bitrix/modules/form/help/".LANGUAGE_ID."/index.php");
$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");
$WEB_FORM_NAME = $arForm["SID"];

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_result_list.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
								Utility
***************************************************************************/

function CheckFilter()
{
	global $strError, $MESS, $arrFORM_FILTER;
	global $find_date_create_1, $find_date_create_2, $lAdmin;
	$str = "";
	CheckFilterDates($find_date_create_1, $find_date_create_2, $date1_wrong, $date2_wrong, $date2_less);
	if ($date1_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_FROM")."<br>";
	if ($date2_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_TO")."<br>";
	if ($date2_less=="Y") $str.= GetMessage("FORM_FROM_TILL_DATE_CREATE")."<br>";

	if (is_array($arrFORM_FILTER))
	{
		foreach ($arrFORM_FILTER as $arrF)
		{
			if (is_array($arrF))
			{
				foreach ($arrF as $arr)
				{
					$title = ($arr["TITLE_TYPE"]=="html") ? strip_tags(htmlspecialcharsback($arr["TITLE"])) : $arr["TITLE"];
					if ($arr["FILTER_TYPE"]=="date")
					{
						$date1 = $_GET["find_".$arr["FID"]."_1"];
						$date2 = $_GET["find_".$arr["FID"]."_2"];
						CheckFilterDates($date1, $date2, $date1_wrong, $date2_wrong, $date2_less);
						if ($date1_wrong=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE1"))."<br>";
						if ($date2_wrong=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE2"))."<br>";
						if ($date2_less=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_DATE2_LESS"))."<br>";
					}
					if ($arr["FILTER_TYPE"]=="integer")
					{
						$int1 = intval($_GET["find_".$arr["FID"]."_1"]);
						$int2 = intval($_GET["find_".$arr["FID"]."_2"]);
						if ($int1>0 && $int2>0 && $int2<$int1)
						{
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_INT2_LESS"))."<br>";
						}
					}
				}
			}
		}
	}
	$strError .= $str;
	if ($str <> '')
	{
		$lAdmin->AddFilterError($str);
		return false;
	}
	else return true;
}

/***************************************************************************
							GET | POST processing
****************************************************************************/

$FORM_ID = $WEB_FORM_ID;
$USER_ID = $USER->GetID();
$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);

if($F_RIGHT<15)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($F_RIGHT >= 20)
{
	$arFilterFields = Array(
		"find_id",
		"find_id_exact_match",
		"find_status",
		"find_status_id",
		"find_status_id_exact_match",
		"find_timestamp_1",
		"find_timestamp_2",
		"find_date_create_1",
		"find_date_create_2",
		"find_registered",
		"find_user_auth",
		"find_user_id",
		"find_user_id_exact_match",
		"find_guest_id",
		"find_guest_id_exact_match",
		"find_session_id",
		"find_session_id_exact_match"
	);
	if (is_array($arFormCrmLink))
		$arFilterFields[] = "find_sent_to_crm";
}
else
	$arFilterFields = array(
		"find_id",
		"find_id_exact_match",
		"find_timestamp_1",
		"find_timestamp_2",
		"find_date_create_1",
		"find_date_create_2",
	);

$z = CFormField::GetFilterList($WEB_FORM_ID, array("ACTIVE" => "Y"));
while ($zr = $z->Fetch())
{
	$FID = $WEB_FORM_NAME."_".$zr["SID"]."_".$zr["PARAMETER_NAME"]."_".$zr["FILTER_TYPE"];
	$zr["FID"] = $FID;

	$arrFORM_FILTER[$zr["SID"]][] = $zr;
	$fname = "find_".$FID;
	if ($zr["FILTER_TYPE"]=="date" || $zr["FILTER_TYPE"]=="integer")
	{
		$arFilterFields[] = $fname."_1";
		$arFilterFields[] = $fname."_2";
		$arFilterFields[] = $fname."_0";
	}
	elseif ($zr["FILTER_TYPE"]=="text")
	{
		$arFilterFields[] = $fname;
		$arFilterFields[] = $fname."_exact_match";
	}
	else $arFilterFields[] = $fname;
}

$sess_filter = "FORM_RESULT_LIST_".$WEB_FORM_NAME;

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_status_id_exact_match);
InitBVar($find_user_id_exact_match);
InitBVar($find_guest_id_exact_match);
InitBVar($find_session_id_exact_match);

if (CheckFilter())
{
	if ($F_RIGHT >= 20)
	{
		$arFilter = Array(
			"ID"						=> $find_id,
			"ID_EXACT_MATCH"			=> $find_id_exact_match,
			"STATUS"					=> $find_status,
			"STATUS_ID"					=> $find_status_id,
			"STATUS_ID_EXACT_MATCH"		=> $find_status_id_exact_match,
			"TIMESTAMP_1"				=> $find_timestamp_1,
			"TIMESTAMP_2"				=> $find_timestamp_2,
			"DATE_CREATE_1"				=> $find_date_create_1,
			"DATE_CREATE_2"				=> $find_date_create_2,
			"REGISTERED"				=> $find_registered,
			"USER_AUTH"					=> $find_user_auth,
			"USER_ID"					=> $find_user_id,
			"USER_ID_EXACT_MATCH"		=> $find_user_id_exact_match,
			"GUEST_ID"					=> $find_guest_id,
			"GUEST_ID_EXACT_MATCH"		=> $find_guest_id_exact_match,
			"SESSION_ID"				=> $find_session_id,
			"SESSION_ID_EXACT_MATCH"	=> $find_session_id_exact_match
		);
		if (is_array($arFormCrmLink))
			$arFilter["SENT_TO_CRM"] = $find_sent_to_crm;

	}
	else
		$arFilter = Array(
			"ID"						=> $find_id,
			"ID_EXACT_MATCH"			=> $find_id_exact_match,
			"TIMESTAMP_1"				=> $find_timestamp_1,
			"TIMESTAMP_2"				=> $find_timestamp_2,
			"DATE_CREATE_1"				=> $find_date_create_1,
			"DATE_CREATE_2"				=> $find_date_create_2,
		);


	if (is_array($arrFORM_FILTER))
	{
		foreach ($arrFORM_FILTER as $arrF)
		{
			foreach ($arrF as $arr)
			{
				if ($arr["FILTER_TYPE"]=="date" || $arr["FILTER_TYPE"]=="integer")
				{
					$arFilter[$arr["FID"]."_1"] = ${"find_".$arr["FID"]."_1"};
					$arFilter[$arr["FID"]."_2"] = ${"find_".$arr["FID"]."_2"};
					$arFilter[$arr["FID"]."_0"] = ${"find_".$arr["FID"]."_0"};
				}
				elseif ($arr["FILTER_TYPE"]=="text")
				{
					$arFilter[$arr["FID"]] = ${"find_".$arr["FID"]};
					$exact_match = (${"find_".$arr["FID"]."_exact_match"}=="Y") ? "Y" : "N";
					$arFilter[$arr["FID"]."_exact_match"] = $exact_match;
				}
				else $arFilter[$arr["FID"]] = ${"find_".$arr["FID"]};
			}
		}
	}
}

if ($lAdmin->EditAction() && /*$FORM_RIGHT>="W"*/ $F_RIGHT >= 20 && check_bitrix_sessid())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);

		if (!CFormResult::SetStatus($ID, $arFields['STATUS_ID']))
		{
			if ($ex = $APPLICATION->GetException())
				$error_text = $ex->GetString();
			else
				$error_text = GetMessage("FORM_SAVE_STATUS_ERROR");

			$lAdmin->AddUpdateError(GetMessage("FORM_SAVE_ERROR").$ID.": ".$error_text, $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && /*$FORM_RIGHT>="W"*/ $F_RIGHT >= 20 && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
			$arID = Array();
			$result = CFormResult::GetList($WEB_FORM_ID, '', '', $arFilter);
			while($arRes = $result->Fetch())
			{
				$arID[] = $arRes['ID'];
			}
	}

	foreach($arID as $ID)
	{
		if($ID == '')
				continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				$GLOBALS['strError'] = '';
				if(!CFormResult::Delete($ID))
				{
					$DB->Rollback();
					if ($GLOBALS['strError'])
					{
						$lAdmin->AddGroupError($GLOBALS['strError'], $ID);
					}
					else
						$lAdmin->AddGroupError(GetMessage("FORM_DELETE_ERROR").$ID, $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
		}

	}

	if (!$_REQUEST["mode"])
		LocalRedirect("form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID);
}

//////////////////////////////////////////////////////////////////////
// initialize list
global $by, $order;

$result = CFormResult::GetList($WEB_FORM_ID, $by, $order, $arFilter);
$result = new CAdminResult($result, $sTableID);
$result->NavStart();

$custom_table=false;
$TABLE_RESULT_TEMPLATE = $arForm["TABLE_RESULT_TEMPLATE"];
if ($TABLE_RESULT_TEMPLATE <> '' && file_exists($_SERVER["DOCUMENT_ROOT"].$TABLE_RESULT_TEMPLATE)) // use custom tpl
{
	$custom_table=true;
}
else
{
	// set nav string
	$lAdmin->NavText($result->GetNavPrint(GetMessage("FORM_PAGES")));

	$headers = array(
			array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
			);

	if (!$bSimple)
		$headers[] = array("id"=>"STATUS_ID", "content"=>GetMessage("FORM_STATUS"), "sort"=>"s_status", "default"=>true);

	if (is_array($arFormCrmLink))
		$headers[] = array("id"=>"SENT_TO_CRM", "content"=>GetMessage("FORM_SENT_TO_CRM"), "sort"=>"s_sent_to_crm", "default"=>true);

	$headers[] = array("id"=>"DATE_CREATE", "content"=>GetMessage("FORM_DATE_CREATE"), "sort"=>"s_date_create", "default"=>false);
	$headers[] = array("id"=>"TIMESTAMP_X", "content"=>GetMessage("FORM_TIMESTAMP"), "sort"=>"s_timestamp", "default"=>true);
	$headers[] = array("id"=>"USER_ID", "content"=>GetMessage("FORM_USER"), "sort"=>"s_user_id", "default"=>true);

	if (CModule::IncludeModule("statistic"))
	{
		$headers[] = array("id"=>"STAT_GUEST_ID", "content"=>GetMessage("FORM_GUEST_ID"), "sort"=>"s_guest_id", "default"=>true);
		$headers[] = array("id"=>"STAT_SESSION_ID", "content"=>GetMessage("FORM_SESSION_ID"), "sort"=>"s_session_id", "default"=>true);
	}

	if ($_GET['mode']=='excel')
		$arFilter = array("IN_EXCEL_TABLE" => "Y");
	else
		$arFilter = array("IN_RESULTS_TABLE" => "Y");

	$arFilter['ACTIVE'] = 'Y';

	$rsFields = CFormField::GetList($WEB_FORM_ID, "ALL", "s_c_sort", "asc", $arFilter);
	while ($arField = $rsFields->Fetch())
	{
		if ($arField['RESULTS_TABLE_TITLE'] <> '')
			$r=$arField['RESULTS_TABLE_TITLE'];
		elseif ($arField['TITLE'] <> '')
			$r=$arField['TITLE'];
		else
			$r=$arField['SID'];

		$headers[] = array("id"=>$arField['SID'], "content"=>strip_tags($r), "default"=>true);
	}

	$lAdmin->AddHeaders($headers);

	$arValues=array();
	$obj=CFormStatus::GetDropdown($WEB_FORM_ID, array("MOVE"));
	while ($ar=$obj->Fetch())
		$arValues[$ar['REFERENCE_ID']]=$ar['REFERENCE'];
	$fullStatusList = [];
	$iterator = CFormStatus::GetList($WEB_FORM_ID, 's_id');
	while ($row = $iterator->Fetch())
	{
		$fullStatusList[$row['ID']] = htmlspecialcharsbx('['.$row['ID'].'] '.$row['TITLE']);
	}
	unset($row, $iterator);

	$arrUsers = array();
	while($arRes = $result->NavNext(true, "f_"))
	{
		$row =& $lAdmin->AddRow($f_ID, $arRes);

		if (isset($arValues[$arRes['STATUS_ID']]))
		{
			$row->AddSelectField("STATUS_ID", $arValues);
		}
		else
		{
			$row->AddViewField('STATUS_ID', $fullStatusList[$arRes['STATUS_ID']]);
		}

		$arFilter = array("RESULT_ID" => $f_ID);
		$arrAnswers = array();
		$arrColumns = array();
		$arrAnswersSID = array();

		CForm::GetResultAnswerArray($WEB_FORM_ID, $arrColumns, $arrAnswers, $arrAnswersSID, $arFilter);
		if (!is_array($arrAnswers[$f_ID]))
			$arrAnswers[$f_ID] = array();

		foreach ($arrAnswers[$f_ID] as $arFieldValues)
		{
			if ($f_USER_ID>0)
			{
				if (!is_array($arrUsers[$f_USER_ID]))
				{
					$rsUser = CUser::GetByID($f_USER_ID);
					if ($arUser = $rsUser->Fetch())
					{
						$f_LOGIN = $arUser['LOGIN'];
						$f_USER_NAME = $arUser['NAME']." ".$arUser['LAST_NAME'];

						$arrUsers[$f_USER_ID]["USER_NAME"] = $f_USER_NAME;
						$arrUsers[$f_USER_ID]["LOGIN"] = $f_LOGIN;
					}
					else
					{
						$arrUsers[$f_USER_ID] = array();
					}
				}
				else
				{
					$f_USER_NAME = $arrUsers[$f_USER_ID]["USER_NAME"];
					$f_LOGIN = $arrUsers[$f_USER_ID]["LOGIN"];
				}

				$txt = "[<a title='".GetMessage("FORM_EDIT_USER")."' href=\"user_edit.php?lang=".LANGUAGE_ID."&ID=".$f_USER_ID."\">".$f_USER_ID."</a>] (".htmlspecialcharsbx($f_LOGIN).") ".htmlspecialcharsbx($f_USER_NAME);
				$txt.= ($f_USER_AUTH=="N") ? GetMessage("FORM_NOT_AUTH") : "";
			}
			else
			{
				$txt = GetMessage("FORM_NOT_REGISTERED");
			}

			$row->AddViewField("USER_ID",$txt); unset($txt);

			if (CModule::IncludeModule("statistic")):
				if (intval($f_STAT_GUEST_ID)>0) :
					$row->AddViewField("STAT_GUEST_ID", " [<a title='".GetMessage("FORM_GUEST_TITLE")."' href='/bitrix/admin/guest_list.php?lang=".LANGUAGE_ID."&find_id=". $f_STAT_GUEST_ID."&set_filter=Y'>".$f_STAT_GUEST_ID."</a>]");
				endif;
				if (intval($f_STAT_SESSION_ID)>0) :
					$row->AddViewField("STAT_SESSION_ID", " (<a title='".GetMessage("FORM_SESSION_TITLE")."' href='/bitrix/admin/session_list.php?lang=".LANGUAGE_ID."&find_id=". $f_STAT_SESSION_ID."&set_filter=Y'>".$f_STAT_SESSION_ID."</a>)");
				endif;
			endif;

			if($_REQUEST["mode"] == "excel")
			{
				$row->AddViewField('SENT_TO_CRM', $f_SENT_TO_CRM == 'N' ? GetMessage('MAIN_NO') : GetMessage('MAIN_YES'));
			}
			else
			{
				$row->AddViewField('SENT_TO_CRM', $f_SENT_TO_CRM == 'N' ? '<img src="/bitrix/images/form/red.gif" height="14" width="14" border="0" id="crm_sent_'.$f_ID.'" />' : '<img src="/bitrix/images/form/green.gif" height="14" width="14" border="0" id="crm_sent_'.$f_ID.'" />');
			}

			foreach ($arFieldValues as $arrA)
			{
				if (trim($arrA["USER_DATE"]) <> '')
				{
					// hack
					$txt.= ((MakeTimeStamp($arrA["USER_TEXT"])+date('Z'))%86400 == 0 ? $arrA['USER_TEXT'] : $arrA['USER_DATE'])."<br>";
				}
				elseif (trim($arrA["USER_TEXT"]) <> '')
				{
					if (intval($arrA["USER_FILE_ID"])>0)
					{
						if ($arrA["USER_FILE_IS_IMAGE"]=="Y" && $USER->IsAdmin())
							$txt.= htmlspecialcharsbx($arrA["USER_TEXT"])."<br>";
					}
					else $txt.= TxtToHTML($arrA["USER_TEXT"],true,100)."<br>";
				}

				if (trim($arrA["ANSWER_TEXT"]) <> '')
				{
					$answer = "[".TxtToHTML($arrA["ANSWER_TEXT"],true,100)."]";
					if (trim($arrA["ANSWER_VALUE"]) <> '') $answer .= "&nbsp;"; else $answer .= "<br>";
					$txt.= $answer;
				}

				if (trim($arrA["ANSWER_VALUE"]) <> '')
					$txt.= "(".TxtToHTML($arrA["ANSWER_VALUE"],true,100).")<br>";

				if (intval($arrA["USER_FILE_ID"])>0)
				{
					if ($arrA["USER_FILE_IS_IMAGE"]=="Y") :
						$txt.= CFile::ShowImage($arrA["USER_FILE_ID"], 0, 0, "border=0", "", true);
					else :
						$txt.="<a title=\"".GetMessage("FORM_VIEW_FILE")."\" href=\"/bitrix/tools/form_show_file.php?rid=$f_ID&hash=$arrA[USER_FILE_HASH]&lang=".LANGUAGE_ID."\">".htmlspecialcharsbx($arrA["USER_FILE_NAME"])."</a><br>(";
						$txt.= CFile::FormatSize($arrA["USER_FILE_SIZE"]);
						$txt.=")<br>[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arrA["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" href=\"/bitrix/tools/form_show_file.php?rid=$f_ID&hash=$arrA[USER_FILE_HASH]&lang=".LANGUAGE_ID."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]";
					endif;
				}


				$row->AddViewField($arrA['SID'], $txt);
			}
		}

		$arrRESULT_PERMISSION = CFormResult::GetPermissions($f_ID);

		$arActions = Array();
		if ($F_RIGHT>=20 || ($F_RIGHT>=15 && $USER_ID==$f_USER_ID))
		{
			if (in_array("EDIT",$arrRESULT_PERMISSION) || in_array("VIEW",$arrRESULT_PERMISSION))
				$arActions[] = array("ICON"=>"edit", "TITLE"=>GetMessage("FORM_EDIT_ALT"), "TEXT"=>GetMessage("FORM_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("form_result_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&RESULT_ID=$f_ID&WEB_FORM_NAME=$WEB_FORM_NAME"), 'DEFAULT' => 'Y');
			if (is_array($arFormCrmLink))
			{
				$arActions[] = array("ICON" => "copy", "TITLE" => GetMessage('FORM_SEND_TO_CRM_TITLE'), "TEXT" => GetMessage('FORM_SEND_TO_CRM'), "ACTION" => "sendToCrm('".$WEB_FORM_ID."', '".$f_ID."')");
			}
			/*if (in_array("VIEW",$arrRESULT_PERMISSION))
				$arActions[] = array("ICON"=>"view", "TITLE"=>GetMessage("FORM_VIEW_ALT"), "TEXT"=>GetMessage("FORM_VIEW"), "ACTION"=>$lAdmin->ActionRedirect("form_result_view.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&RESULT_ID=$f_ID&WEB_FORM_NAME=$WEB_FORM_NAME"));*/
			if (in_array("DELETE",$arrRESULT_PERMISSION))
				$arActions[] = array("ICON"=>"delete", "TITLE"=>GetMessage("FORM_DELETE_ALT"), "TEXT"=>GetMessage("FORM_DELETE"), "ACTION"=>"if(confirm('".GetMessage("FORM_CONFIRM_DELETE")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "&WEB_FORM_ID=".$WEB_FORM_ID."&WEB_FORM_NAME=".$WEB_FORM_NAME));
		}
		$row->AddActions($arActions);
	}

	// list "footer"
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$result->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);

	// show form with btns
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("FORM_DELETE_L"),
		));
}

// context menu
$aMenu[] = array(
	"TEXT" => GetMessage("FORM_ADD"),
	"TITLE" => GetMessage("FORM_CREATE_TITLE"),
	"ICON"	=> "btn_new",
	"LINK"	=> "/bitrix/admin/form_result_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
	);

$lAdmin->AddAdminContextMenu($aMenu);

$lAdmin->CheckListMode();
/***************************************************************************
							HTML form
****************************************************************************/
$APPLICATION->SetTitle(str_replace("#ID#","$WEB_FORM_ID",GetMessage("FORM_PAGE_TITLE")));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_result_list.php");

if(!is_set($_REQUEST, "mode"))
{
	if ($F_RIGHT >= 25)
	{
		$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
		$context->Show();
	}

	echo BeginNote('width="100%"');
?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?
	echo EndNote();
}

$FILTER_RESULT_TEMPLATE = $arForm["FILTER_RESULT_TEMPLATE"];
if($FILTER_RESULT_TEMPLATE <> '' && file_exists($_SERVER["DOCUMENT_ROOT"].$FILTER_RESULT_TEMPLATE))
{
	require_once($_SERVER["DOCUMENT_ROOT"].$FILTER_RESULT_TEMPLATE);
}
else
{

	?>
	<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
	<?

	if ($F_RIGHT >= 20)
	{
		$arFieldsTitle=array(
			GetMessage("FORM_FL_DATE_CREATED"),
			GetMessage("FORM_FL_DATE_CHANGED"),
			GetMessage("FORM_FL_REGISTERED"),
			GetMessage("FORM_FL_AUTORIZED"),
			GetMessage("FORM_FL_UID"),
			(CModule::IncludeModule("statistic") ? GetMessage("FORM_FL_VID") : null),
			(CModule::IncludeModule("statistic") ? GetMessage("FORM_FL_SID") : null),
		);

		if (!$bSimple)
		{
			$arFieldsTitle[]=GetMessage("FORM_FL_STATUS");
			$arFieldsTitle[]=GetMessage("FORM_FL_STATUS_ID");
		}

		if (is_array($arFormCrmLink))
			$arFieldsTitle[] = GetMessage('FORM_FL_SENT_TO_CRM');

	}
	else
	{
		$arFieldsTitle=array(
			GetMessage("FORM_FL_DATE_CREATED"),
			GetMessage("FORM_FL_DATE_CHANGED"),
		);
	}

	$arrFORM_FILTER = (is_array($arrFORM_FILTER)) ? $arrFORM_FILTER : array();

	foreach ($arrFORM_FILTER as $key => $arrFILTER)
	{
		$arrF = current($arrFILTER);

		if ($arrF["FILTER_TITLE"] <> '')
			$arFieldsTitle[] = htmlspecialcharsbx($arrF["FILTER_TITLE"]);
		elseif ($arrF["TITLE"] <> '')
			$arFieldsTitle[] = htmlspecialcharsbx($arrF["TITLE"]);
		else
			$arFieldsTitle[] = $arrF["SID"];
	}

	$oFilter = new CAdminFilter($sTableID."_filter", $arFieldsTitle);
	$oFilter->Begin();
	?>

	<tr>
		<td><?=GetMessage("FORM_F_ID")?></td>
		<td><?=CForm::GetTextFilter("id")?></td>
	</tr>
	<tr>
		<td width="0%" nowrap><?echo GetMessage("FORM_F_DATE_CREATE")." (".CSite::GetDateFormat("SHORT")."):"?></td>
		<td width="0%" nowrap><?=CForm::GetDateFilter("date_create", "form1", "Y", "class=\"typeselect\"", "class=\"inputtype\"")?></td>
	</tr>
	<tr>
		<td width="0%" nowrap><?echo GetMessage("FORM_F_TIMESTAMP")." (".CSite::GetDateFormat("SHORT")."):"?></td>
		<td width="0%" nowrap><?=CForm::GetDateFilter("timestamp", "form1", "Y", "class=\"typeselect\"", "class=\"inputtype\"")?></td>
	</tr>
<?if ($F_RIGHT>=20):?>
	<tr>
		<td>
			<?echo GetMessage("FORM_F_REGISTERED")?></td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("find_registered", $arr, htmlspecialcharsbx($find_registered), GetMessage("FORM_ALL"));
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_F_AUTH")?></td>
		<td><?
			$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("find_user_auth", $arr, htmlspecialcharsbx($find_user_auth), GetMessage("FORM_ALL"));
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_F_USER")?></td>
		<td><?=CForm::GetTextFilter("user_id")?></td>
	</tr>

	<?if (CModule::IncludeModule("statistic")) :?>
	<tr>
		<td><?echo GetMessage("FORM_F_GUEST")?></td>
		<td><?=CForm::GetTextFilter("guest_id")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_F_SESSION")?></td>
		<td><?=CForm::GetTextFilter("session_id")?></td>
	</tr>
	<?endif;?>

	<?if (!$bSimple):?>
		<tr>
			<td><?echo GetMessage("FORM_F_STATUS")?></td>
			<td><?
				echo SelectBox("find_status", CFormStatus::GetDropdown($WEB_FORM_ID, array("VIEW")), GetMessage("FORM_ALL"), htmlspecialcharsbx($find_status));
				?></td>
		</tr>
		<tr>
			<td>
				<?echo GetMessage("FORM_F_STATUS_ID")?></td>
			<td><?
				echo CForm::GetTextFilter("status_id");
				?></td>
		</tr>
	<?endif;?>
	<?if (is_array($arFormCrmLink)):?>
	<tr>
		<td><?echo GetMessage("FORM_FL_SENT_TO_CRM")?></td>
		<td><?=CForm::GetCrmFlagFilter("sent_to_crm")?></td>
	</tr>
	<?endif;?>
<?endif;?>
	<?
	$arrFORM_FILTER = (is_array($arrFORM_FILTER)) ? $arrFORM_FILTER : array();

	foreach ($arrFORM_FILTER as $arrFILTER)
	{
		foreach ($arrFILTER as $key => $arrF)
		{

			$fname = $arrF["SID"];

			if (!is_array($arrNOT_SHOW_FILTER) || !in_array($fname,$arrNOT_SHOW_FILTER))
			{

				if ($arrF["ADDITIONAL"]=="Y" || $arrF["ADDITIONAL"]!="Y")
				{
					$i++;
					if ($fname != $prev_fname)
					{

						echo $i > 1 ? '</td></tr>' : '';
?>
	<tr>
		<td width="40%">
<?
						if ($arrF["FILTER_TITLE"] <> '')
							echo htmlspecialcharsbx($arrF["FILTER_TITLE"]);
						elseif ($arrF["TITLE"] <> '')
							echo htmlspecialcharsbx($arrF["TITLE"]);
						else
							echo $arrF["SID"];

						if ($arrF["FILTER_TYPE"]=="date")
							echo " (".CSite::GetDateFormat("SHORT").")";
?>
		</td>
		<td nowrap width="60%">
<?
					} //endif;

					switch($arrF["FILTER_TYPE"])
					{
						case "text":
							echo CForm::GetTextFilter($arrF["FID"], 45, "class=\"typeinput\"", "");
							break;

						case "date":
							echo CForm::GetDateFilter($arrF["FID"], "form1", "Y", "class=\"typeselect\"", "class=\"typeinput\"");
							break;

						case "integer":
							echo CForm::GetNumberFilter($arrF["FID"], 10, "class=\"typeinput\"");
							break;

						case "dropdown":
							echo CForm::GetDropDownFilter($arrF["ID"], $arrF["PARAMETER_NAME"], $arrF["FID"], "class=\"typeselect\"");
							break;

						case "exist":
							echo CForm::GetExistFlagFilter($arrF["FID"], "");
							break;

					} //endswitch;

					if ($arrF["PARAMETER_NAME"]=="ANSWER_TEXT")
					{
						echo "&nbsp;<sup>[...]</sup>";
						$f_anstext = "Y";
					}
					elseif ($arrF["PARAMETER_NAME"]=="ANSWER_VALUE")
					{
						echo "&nbsp;<sup>(...)</sup>";
						$f_ansvalue = "Y";
					}

					echo "<br />";

					$prev_fname = $fname;
				} //endif;
			} //endif;

		} //endforeach;

	} //endforeach;
	?></td>
	</tr>
	<?
	$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID"));
	$oFilter->End();
	?>
	</form>
	<?

#############
}

if (is_array($arFormCrmLink)):
?>
<script type="text/javascript">
function sendToCrm(FORM_ID, RESULT_ID)
{
	BX.ajax.loadJSON('/bitrix/admin/form_crm.php?action=add_lead&FORM_ID=<?=$WEB_FORM_ID?>&RESULT_ID=' + parseInt(RESULT_ID) + '&<?=bitrix_sessid_get()?>', function(result){
		if (result.result == 'ok')
		{
			var img = BX('crm_sent_' + RESULT_ID);
			if (img)
				img.src = '/bitrix/images/form/green.gif';
			alert('<?=CUtil::JSEscape(GetMessage('FORM_SEND_TO_CRM_OK'))?>'.replace('#ID#', result.ID));
		}
		else
		{
			var img = BX('crm_sent_' + RESULT_ID);
			if (img)
				img.src = '/bitrix/images/form/red.gif';
			alert('<?=CUtil::JSEscape(GetMessage('FORM_SEND_TO_CRM_ERROR'))?>'.replace('#ERROR#', result.error));
		}
	});
}
</script>
<?
endif;

$lAdmin->DisplayList();

if ($custom_table) require_once($_SERVER["DOCUMENT_ROOT"].$TABLE_RESULT_TEMPLATE);

echo BeginNote();
?>
<table border="0" cellspacing="6" cellpadding="0">
	<tr>
		<td nowrap><sup>[...]</sup></td>
		<td nowrap> - </td>
		<td nowrap><?echo str_replace("#FORM_ID#",$WEB_FORM_ID,GetMessage("FORM_FILTER_ANSWER_TEXT"))?></td>
	</tr>
	<tr>
		<td nowrap><sup>(...)</sup></td>
		<td nowrap> - </td>
		<td nowrap><?echo str_replace("#FORM_ID#",$WEB_FORM_ID,GetMessage("FORM_FILTER_ANSWER_VALUE"))?></td>
	</tr>
</table>
<?
echo EndNote();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
