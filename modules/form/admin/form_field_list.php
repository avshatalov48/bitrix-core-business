<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2006 Bitrix           #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_field_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

CModule::IncludeModule("form");

ClearVars();

$WEB_FORM_ID = intval($WEB_FORM_ID);
$arForm = CForm::GetByID_admin($WEB_FORM_ID);

IncludeModuleLangFile(__FILE__);

$strError = '';

if (false === $arForm)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo "<a href='form_list.php?lang=".LANGUAGE_ID."' class='navchain'>".GetMessage("FORM_FORM_LIST")."</a>";
	ShowError(GetMessage("FORM_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$txt = "(".htmlspecialcharsbx($arForm['SID']).")&nbsp;".htmlspecialcharsbx($arForm['NAME']);
$link = "form_edit.php?lang=".LANGUAGE_ID."&ID=".$WEB_FORM_ID;
$adminChain->AddItem(array("TEXT"=>$txt, "LINK"=>$link));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bSimple = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;

$err_mess = "File: ".__FILE__."<br>Line: ";
$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_title",
	"find_title_exact_match",
	"find_sid",
	"find_sid_exact_match",
	"find_active",
	"find_in_table",
	"find_in_excel",
	"find_in_filter",
	"find_required",
	"find_comments",
	"find_comments_exact_match"
	);
$lAdmin->InitFilter($arFilterFields);

InitSorting();
$old_module_version = CForm::IsOldVersion();
InitBVar($additional);
if ($additional!="Y") define("HELP_FILE","form_question_list.php");
else define("HELP_FILE","form_field_list.php");

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$copy_id = intval($_REQUEST['copy_id'] ?? 0);
if ($copy_id > 0 && $F_RIGHT >= 30 && check_bitrix_sessid())
{
	$new_id = CFormField::Copy($copy_id);
	LocalRedirect("form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&additional=$additional");
}

if ($additional=="Y") $sess_filter = "FORM_ADDITIONAL_FIELD_LIST"; else $sess_filter = "FORM_FIELD_LIST";

if ($bSimple)
	$additional="ALL";
else
	InitBVar($additional);

InitBVar($find_id_exact_match);
InitBVar($find_title_exact_match);
InitBVar($find_sid_exact_match);
InitBVar($find_comments_exact_match);
$arFilter = Array(
	"ID"					=> $find_id,
	"ID_EXACT_MATCH"		=> $find_id_exact_match,
	"TITLE"					=> $find_title,
	"TITLE_EXACT_MATCH"		=> $find_title_exact_match,
	"SID"					=> $find_sid,
	"SID_EXACT_MATCH"		=> $find_sid_exact_match,
	"ACTIVE"				=> $find_active,
	"IN_RESULTS_TABLE"		=> $find_in_table,
	"IN_EXCEL_TABLE"		=> $find_in_excel,
	"IN_FILTER"				=> $find_in_filter,
	"REQUIRED"				=> $find_required,
	"COMMENTS"				=> $find_comments,
	"COMMENTS_EXACT_MATCH"	=> $find_comments_exact_match
	);

if ($lAdmin->EditAction() && $FORM_RIGHT>="W" && $F_RIGHT>=30 && check_bitrix_sessid())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);

		$arFieldsStore = Array(
			"TIMESTAMP_X"	=> $DB->GetNowFunction(),
		);

		$arFlds = array('C_SORT', 'ACTIVE', 'REQUIRED', 'IN_RESULTS_TABLE', 'IN_EXCEL_TABLE');
		foreach ($arFlds as $key)
		{
			if (is_set($arFields, $key))
			{
				$arFieldsStore[$key] = "'".($key == "C_SORT" ? intval($arFields[$key]) : $DB->ForSql($arFields[$key]))."'";
			}
		}

		if (!$DB->Update("b_form_field",$arFieldsStore,"WHERE ID='".$ID."'",$err_mess.__LINE__))
		{
			$lAdmin->AddUpdateError(GetMessage("FORM_ERROR").$ID.": ".GetMessage("FORM_ERROR_SAVE"), $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && $FORM_RIGHT=="W" && $F_RIGHT>=30 && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CFormField::GetList($WEB_FORM_ID, $additional, '', '', $arFilter);
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
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CFormField::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
				}
				else
				{
					$DB->Commit();
				}
			break;
			case "activate":
			case "deactivate":
				@set_time_limit(0);
				$DB->StartTransaction();
				$arFieldsStore=array("ACTIVE"=>($_REQUEST['action']=="activate")?"'Y'":"'N'");
				if (!$DB->Update("b_form_field",$arFieldsStore,"WHERE ID='".$ID."'",$err_mess.__LINE__))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
				}
				else
				{
					$DB->Commit();
				}
			break;
		}
	}

	if (!$_REQUEST["mode"])
		LocalRedirect("form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=".$additional);

}


$sDocTitle = ($additional=="Y") ? GetMessage("FORM_PAGE_ADDITIONAL_TITLE") : GetMessage("FORM_PAGE_TITLE");
$APPLICATION->SetTitle(str_replace("#ID#","$WEB_FORM_ID",$sDocTitle));

//////////////////////////////////////////////////////////////////////
global $by, $order;

$rsData = CFormField::GetList($WEB_FORM_ID, $additional, $by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint($additional=="Y"?GetMessage("FORM_PAGES"):GetMessage("FORM_PAGES_Q")));

$headers=array();
$headers[]=array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true);
$headers[]=array("id"=>"ACTIVE", "content"=>GetMessage("FORM_ACTIVE"), "sort"=>"s_active", "default"=>true);
$headers[]=array("id"=>"C_SORT", "content"=>GetMessage("FORM_C_SORT"), "sort"=>"s_sort", "default"=>true);
if ($additional != 'Y')
	$headers[]=array("id"=>"REQUIRED", "content"=>GetMessage("FORM_REQUIRED"), "sort"=>"s_required", "default"=>true);

if (!$bSimple)
	$headers[]=array("id"=>"SID", "content"=>GetMessage("FORM_SID"), "sort"=>"s_sid", "default"=>true);


if ($additional=="Y")
{
	$headers[]=array("id"=>"TITLE", "content"=>GetMessage("FORM_ADDITIONAL_TITLE"), "sort"=>"s_title", "default"=>true);
	$headers[]=array("id"=>"FIELD_TYPE", "content"=>GetMessage("FORM_FIELD_TYPE"), "sort"=>"s_field_type", "default"=>true);
}
else
{
	$headers[]=array("id"=>"TITLE", "content"=>GetMessage("FORM_TITLE"), "sort"=>"s_title", "default"=>true);
}
if ($bSimple)
	$headers[]=array("id"=>"A_FIELD_TYPE", "content"=>GetMessage("FIELD_TYPE"), "default"=>true);
$headers[]=array("id"=>"COMMENTS", "content"=>GetMessage("FORM_COMMENTS"), "sort"=>"s_comments", "default"=>true);

if (!$bSimple)
{
	$headers[]=array("id"=>"IN_RESULTS_TABLE", "content"=>GetMessage("FORM_IN_RESULTS_TABLE"), "sort"=>"s_in_results_table", "default"=>true);
	$headers[]=array("id"=>"IN_EXCEL_TABLE", "content"=>GetMessage("FORM_IN_EXCEL_TABLE"), "sort"=>"s_in_excel_table", "default"=>true);
}


$lAdmin->AddHeaders($headers);
if ($additional=="Y") $msg=GetMessage("FORM_EDIT_ALT"); else $msg=GetMessage("FORM_EDIT_ALT_Q");
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($bSimple)
	{
		if ($arRes['ADDITIONAL']=="Y")
			$p_FIELD_TYPE='hidden';
		else
		{
			$rAnswer = CFormAnswer::GetList($f_ID);
			$rAnswer->ExtractFields("p_");
		}
		$row->AddViewField("A_FIELD_TYPE",$p_FIELD_TYPE);
	}

	$row->AddCheckField("ACTIVE");
	if ($arRes['ADDITIONAL'] != 'Y')
		$row->AddCheckField("REQUIRED");
	else
		$row->AddViewField('REQUIRED', '');

	$row->AddCheckField("IN_RESULTS_TABLE");
	$row->AddCheckField("IN_EXCEL_TABLE");
	$row->AddInputField("C_SORT");
	$row->AddViewField("SID","<a href=\"form_field_edit.php?lang=".LANGUAGE_ID."&ID=".$f_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=".$additional."\" title=\"".$msg."\">".htmlspecialcharsbx($f_SID)."</a>");

	$arActions = Array();

	if ($bSimple)
		$arActions[] = array(
			"DEFAULT"=>"Y",
			"ICON"=>"edit",
			"TITLE"=>GetMessage("FORM_EDIT_ALT"),
			"ACTION"=>$lAdmin->ActionRedirect("form_field_edit_simple.php?lang=".LANGUAGE_ID."&ID=$f_ID&WEB_FORM_ID=$WEB_FORM_ID&additional=$additional"),
			"TEXT"=>GetMessage("FORM_FIELD_EDIT")
		);
	else
		$arActions[] = array(
			"DEFAULT"=>"Y",
			"ICON"=>"edit",
			"TITLE"=>GetMessage("FORM_EDIT_ALT"),
			"ACTION"=>$lAdmin->ActionRedirect("form_field_edit.php?lang=".LANGUAGE_ID."&ID=$f_ID&WEB_FORM_ID=$WEB_FORM_ID&additional=$additional"),
			"TEXT"=>GetMessage("FORM_FIELD_EDIT")
		);

	if ($F_RIGHT>=30)
	{
		$arActions[] = array(
			"ICON"=>"copy",
			"TITLE"=>GetMessage("FORM_COPY"),
			"ACTION"=>$lAdmin->ActionRedirect("form_field_list.php?copy_id=$f_ID&lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&additional=$additional&".bitrix_sessid_get()),
			"TEXT"=>GetMessage("FORM_CP")
		);
		$arActions[] = array(
			"ICON"=>"delete",
			"TITLE"=>GetMessage("FORM_DELETE_FIELD"),
			"ACTION"=>"if(confirm('".GetMessageJS("FORM_CONFIRM_DELETE")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "&WEB_FORM_ID=".$WEB_FORM_ID."&additional=$additional"),
			"TEXT"=>GetMessage("FORM_DELETE_FIELD")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$arActions = array();
$arActions=array("delete"=>GetMessage("FORM_DELETE_L"));
if (!$bSimple)
{
	$arActions["activate"] = GetMessage("FORM_ACTIVATE_L");
	$arActions["deactivate"] = GetMessage("FORM_DEACTIVATE_L");
}

$lAdmin->AddGroupActionTable($arActions);

if ($bSimple)
	$aMenu = array(
		array(
			"ICON"	=> "btn_new",
			"TEXT"	=> GetMessage("FORM_ADD"),
			"LINK" => "form_field_edit_simple.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID",
			"TITLE"	=> GetMessage("FORM_ADD_QUESTION"),
		)
	);
else
	$aMenu = array(
		array(
			"ICON"	=> "btn_new",
			"TEXT"	=> GetMessage("FORM_ADD"),
			"LINK" => "form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&additional=$additional",
			"TITLE"	=> ($additional=="Y") ? GetMessage("FORM_ADD_FIELD") : GetMessage("FORM_ADD_QUESTION"),
		)
	);

$lAdmin->AddAdminContextMenu($aMenu);

$lAdmin->CheckListMode();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?echo EndNote();

if ($strError)
	$lAdmin->AddFilterError($strError);
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
if ($additional=="Y")
{
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FORM_FL_ID"),
			GetMessage("FORM_FL_ACTIVE"),
			GetMessage("FORM_FL_SID"),
			GetMessage("FORM_FL_COMMENT"),
			GetMessage("FORM_FL_HTML_INC"),
			GetMessage("FORM_FL_EXCEL_INC"),
			GetMessage("FORM_FL_FILTER_INC"),
			GetMessage("FORM_FL_LOGIC"),
		)
	);
}
else
{
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FORM_FL_ID"),
			GetMessage("FORM_FL_ACTIVE"),
			GetMessage("FORM_FL_SID"),
			GetMessage("FORM_FL_COMMENT"),
			GetMessage("FORM_FL_REQUIRED"),
			GetMessage("FORM_FL_HTML_INC"),
			GetMessage("FORM_FL_EXCEL_INC"),
			GetMessage("FORM_FL_FILTER_INC"),
			GetMessage("FORM_FL_LOGIC"),
		)
	);
}
$oFilter->Begin();

?>

<tr>
	<td><b><?echo ($additional=="Y") ? GetMessage("FORM_ADDITIONAL_TITLE") : GetMessage("FORM_TITLE")?>:</b></td>
	<td><input type="text" name="find_title" size="47" value="<?echo htmlspecialcharsbx($find_title)?>"><?=InputType("checkbox", "find_title_exact_match", "Y", $find_title_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("FORM_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_ACTIVE")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("FORM_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("FORM_F_SID")?></td>
	<td><input type="text" name="find_sid" size="47" value="<?echo htmlspecialcharsbx($find_sid)?>"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("FORM_F_COMMENTS")?></td>
	<td><input type="text" name="find_comments" size="47" value="<?echo htmlspecialcharsbx($find_comments)?>"><?=InputType("checkbox", "find_comments_exact_match", "Y", $find_comments_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?if ($additional!="Y"):?>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_REQUIRED")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_required", $arr, htmlspecialcharsbx($find_required), GetMessage("FORM_ALL"));
		?></td>
</tr>
<?endif;?>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_IN_TABLE")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_in_table", $arr, htmlspecialcharsbx($find_in_table), GetMessage("FORM_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_IN_EXCEL")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_in_excel", $arr, htmlspecialcharsbx($find_in_excel), GetMessage("FORM_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_IN_FILTER")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_in_filter", $arr, htmlspecialcharsbx($find_in_filter), GetMessage("FORM_ALL"));
		?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>($APPLICATION->GetCurPage())."?lang=".LANGUAGE_ID."&additional=$additional&WEB_FORM_ID=$WEB_FORM_ID"));
$oFilter->End();
#############################################################
?>
</form>
<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");