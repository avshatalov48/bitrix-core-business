<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_form_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

ClearVars();

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("form");

$bSimple = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_name",
	"find_name_exact_match",
	"find_sid",
	"find_sid_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_site"
	);

$lAdmin->InitFilter($arFilterFields);

$old_module_version = CForm::IsOldVersion();

$reset_id = intval($reset_id);
if ($FORM_RIGHT=="W" && $reset_id>0 && check_bitrix_sessid()) CForm::Reset($reset_id);

$copy_id = intval($makecopy_id);
if ($FORM_RIGHT=="W" && $copy_id>0 && check_bitrix_sessid())
{
	CForm::Copy($copy_id);
	LocalRedirect("form_list.php?lang=".LANGUAGE_ID);
}

InitBVar($find_id_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_sid_exact_match);
InitBVar($find_description_exact_match);
$arFilter = Array(
	"ID"						=> $find_id,
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"NAME"						=> $find_name,
	"NAME_EXACT_MATCH"			=> $find_name_exact_match,
	"SID"						=> $find_sid,
	"SID_EXACT_MATCH"			=> $find_sid_exact_match,
	"DESCRIPTION"				=> $find_description,
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
	"SITE"						=> $find_site
	);

// "Save" button was pressed
if ($lAdmin->EditAction() && $FORM_RIGHT>="W" && check_bitrix_sessid())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$ID = intval($ID);
		$F_RIGHT = CForm::GetPermission($ID);
		if ($F_RIGHT>=30)
		{
			$arFieldsStore = Array(
				"TIMESTAMP_X"	=> $DB->GetNowFunction(),
				"C_SORT"		=> "'".intval($arFields['C_SORT'])."'"
			);

			$DB->StartTransaction();

			if (!$DB->Update("b_form",$arFieldsStore,"WHERE ID='".$ID."'",$err_mess.__LINE__))
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".GetMessage("FORM_SAVE_ERROR"), $ID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
		}
	}
}

// simgle and group actions processing
if(($arID = $lAdmin->GroupAction()) && $FORM_RIGHT=="W" && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CForm::GetList('', '', $arFilter);
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
			if(!CForm::Delete($ID))
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
}
//////////////////////////////////////////////////////////////////////
// list initialization - get data
global $by, $order;

$rsData = CForm::GetList($by, $order, $arFilter);
$arData = array();
while ($arForm = $rsData->Fetch())
{
	$F_RIGHT = CForm::GetPermission($arForm["ID"]);
	if ($F_RIGHT >= 20)
	{
		$arForm["F_RIGHT"] = $F_RIGHT;
		$arData[] = $arForm;
	}
}

$rsData->InitFromArray($arData);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// set navigation bar
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FORM_PAGES")));

$headers = array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
		array("id"=>"SITE", "content"=>GetMessage("FORM_SITE"), "default"=>true),
		array("id"=>"C_SORT", "content"=>GetMessage("FORM_C_SORT"), "sort"=>"s_c_sort", "default"=>true)
		);
	if (!$bSimple)
		$headers[] = array("id"=>"SID", "content"=>GetMessage("FORM_SID"), "sort"=>"s_sid", "default"=>true);
	$headers[] = array("id"=>"NAME", "content"=>GetMessage("FORM_NAME"), "sort"=>"s_name", "default"=>true);
	$headers[] = array("id"=>"QUESTIONS", "content"=>GetMessage("FORM_QUESTIONS"), "default"=>true);
if (COption::GetOptionString("form", "SIMPLE")!="Y")
{
	$headers[]=array("id"=>"C_FIELDS", "content"=>GetMessage("FORM_FIELDS"), "default"=>true);
	$headers[]=array("id"=>"STATUSES", "content"=>GetMessage("FORM_STATUSES"), "default"=>true);
}
$headers[]=array("id"=>"RESULTS", "content"=>GetMessage("FORM_RESULTS"), "default"=>true);

$lAdmin->AddHeaders($headers);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	//$F_RIGHT = CForm::GetPermission($f_ID);
	$F_RIGHT = $f_F_RIGHT;

	unset($txt);
	$arrSITE = CForm::GetSiteArray($f_ID);
	reset($arrSITE);
	if (is_array($arrSITE))
	{
		foreach($arrSITE as $sid)
			$txt.= "<a href='/bitrix/admin/site_edit.php?LID=".htmlspecialcharsbx($sid, ENT_QUOTES)."&lang=".LANGUAGE_ID."'>".htmlspecialcharsbx($sid)."</a>,";
	}
	else
		$txt="&nbsp;";
		$txt=trim($txt,",");
	$row->AddViewField("SITE",$txt);

	if ($bSimple)
	{
		$f_QUESTIONS+=$f_C_FIELDS;
		$txt="<a title=\"".GetMessage("FORM_QUESTIONS_ALT")."\" href=\"form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">$f_QUESTIONS</a>&nbsp;[<a title=\"".GetMessage("FORM_ADD_QUESTION")."\" href=\"form_field_edit_simple.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">+</a>]";
	}
	else
		$txt="<a title=\"".GetMessage("FORM_QUESTIONS_ALT")."\" href=\"form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">$f_QUESTIONS</a>&nbsp;[<a title=\"".GetMessage("FORM_ADD_QUESTION")."\" href=\"form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">+</a>]";
	$row->AddViewField("QUESTIONS",$txt);

	$txt="<a title=\"".GetMessage("FORM_FIELDS_ALT")."\" href=\"form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID&additional=Y\">$f_C_FIELDS</a>&nbsp;[<a title=\"".GetMessage("FORM_ADD_FIELD")."\" href=\"form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID&additional=Y\">+</a>]";
	$row->AddViewField("C_FIELDS",$txt);

	$txt="<a title=\"".GetMessage("FORM_STATUSES_ALT")."\" href=\"form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">$f_STATUSES</a>&nbsp;[<a title=\"".GetMessage("FORM_ADD_STATUS")."\" href=\"form_status_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">+</a>]";
	$row->AddViewField("STATUSES",$txt);

	$txt="<a title=\"".str_replace("\"#NAME#\"", "", GetMessage("FORM_RESULTS_ALT"))."\" href=\"form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">".(CFormResult::GetCount($f_ID))."</a>&nbsp;[<a title=\"".GetMessage("FORM_ADD_RESULT")."\" href=\"form_result_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$f_ID\">+</a>]";
	$row->AddViewField("RESULTS",$txt);

	if ($FORM_RIGHT=="W") $row->AddInputField("C_SORT");



	$arActions = Array();

	if ($F_RIGHT>=25)
		$arActions[] = array("DEFAULT"=>"Y", "ICON"=>"edit", "TITLE"=>GetMessage("FORM_EDIT_ALT"), "ACTION"=>$lAdmin->ActionRedirect("form_edit.php?lang=".LANGUAGE_ID."&ID=$f_ID"), "TEXT"=>GetMessage("FORM_EDIT"));
	if (CForm::IsAdmin())
		$arActions[] = array("ICON"=>"copy", "TITLE"=>GetMessage("FORM_COPY_ALT"),"ACTION"=>$lAdmin->ActionRedirect("form_list.php?lang=".LANGUAGE_ID."&amp;makecopy_id=$f_ID&".bitrix_sessid_get()),"TEXT"=>GetMessage("FORM_COPY"));
	if ($F_RIGHT>=30)
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("TITLE"=>GetMessage("FORM_DELETE_RESULTS_ALT"),"ACTION"=>"javascript:if(confirm('".CUtil::JSEscape(GetMessage("FORM_CONFIRM_DELETE_RESULTS"))."')) window.location='?lang=".LANGUAGE_ID."&reset_id=".$f_ID."&".bitrix_sessid_get()."'", "TEXT"=>GetMessage("FORM_DELETE_RESULTS"));
	}
	if (CForm::IsAdmin())
		$arActions[] = array("ICON"=>"delete", "TITLE"=>GetMessage("FORM_DELETE_ALT"),"ACTION"=>"javascript:if(confirm('".CUtil::JSEscape(GetMessage("FORM_CONFIRM_DELETE"))."')) window.location='?lang=".LANGUAGE_ID."&action=delete&ID=$f_ID&".bitrix_sessid_get()."'","TEXT"=>GetMessage("FORM_DELETE"));

	$row->AddActions($arActions);


}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($FORM_RIGHT=="W")
	// add list buttons
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("FORM_DELETE_L"),
		));

// context menu
if ($FORM_RIGHT=="W")
{
	$aMenu = array();
	$aMenu[] = array(
		"TEXT"	=> GetMessage("FORM_CREATE"),
		"TITLE"=>GetMessage("FORM_CREATE_TITLE"),
		"LINK"=>"form_edit.php?lang=".LANG,
		"ICON" => "btn_new"
	);

	$aContext = $aMenu;
	$lAdmin->AddAdminContextMenu($aContext);
}

// check list output mode
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("FORM_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("FORM_FL_ID"),
		GetMessage("FORM_FL_SITE"),
		GetMessage("FORM_FL_SID"),
		GetMessage("FORM_FL_DESCRIPTION"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("FORM_F_NAME")?></b></td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("FORM_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("FORM_F_SITE")?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
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
<tr>
	<td><?echo GetMessage("FORM_F_SID")?></td>
	<td><input type="text" name="find_sid" size="47" value="<?echo htmlspecialcharsbx($find_sid)?>"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("FORM_F_DESCRIPTION")?></td>
	<td><input type="text" name="find_description" size="47" value="<?echo htmlspecialcharsbx($find_description)?>"><?=InputType("checkbox", "find_description_exact_match", "Y", $find_description_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");