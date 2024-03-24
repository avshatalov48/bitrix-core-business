<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("form");

ClearVars();

$strError = '';
$strNote = '';

$sTableID = "tbl_status_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$WEB_FORM_ID = intval($WEB_FORM_ID);
$arForm = CForm::GetByID_admin($WEB_FORM_ID);
if (false === $arForm)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo "<a href='form_list.php?lang=".LANGUAGE_ID."' >".GetMessage("FORM_FORM_LIST")."</a>";
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

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_active",
	"find_title",
	"find_title_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_results_1",
	"find_results_2"
	);

$lAdmin->InitFilter($arFilterFields);

InitSorting();
$old_module_version = CForm::IsOldVersion();

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$cp_id = intval($cp_id);
if ($cp_id>0 && check_bitrix_sessid() && $F_RIGHT >= 30)
{
	CFormStatus::Copy($cp_id);
	LocalRedirect("form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID");
}

InitBVar($find_id_exact_match);
InitBVar($find_title_exact_match);
InitBVar($find_description_exact_match);
$arFilter = Array(
	"ID"						=> $find_id,
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"ACTIVE"					=> $find_active,
	"TITLE"						=> $find_title,
	"TITLE_EXACT_MATCH"			=> $find_title_exact_match,
	"DESCRIPTION"				=> $find_description,
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
	"RESULTS_1"					=> $find_results_1,
	"RESULTS_2"					=> $find_results_2
);

// "Save changes" button processing
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
			"ACTIVE"		=> "'".$DB->ForSql($arFields['ACTIVE'])."'",
			"C_SORT"		=> "'".intval($arFields['C_SORT'])."'",
		);

		if (!$DB->Update("b_form_status",$arFieldsStore,"WHERE ID='".$ID."'",$err_mess.__LINE__))
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

// single and group action processing
if(($arID = $lAdmin->GroupAction()) && $FORM_RIGHT=="W" && $F_RIGHT>=30 && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CFormStatus::GetList($WEB_FORM_ID, '', '', $arFilter);
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
				if(!CFormStatus::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("FORM_STATUS_DELETE_ERROR").' '.$ID, $ID);
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
				if (!$DB->Update("b_form_status",$arFieldsStore,"WHERE ID='".$ID."'",$err_mess.__LINE__))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("FORM_STATUS_ACTIVE_ERROR").' '.$ID, $ID);
				}
				else
				{
					$DB->Commit();
				}
			break;
		}
	}

	if (!$_REQUEST["mode"])
		LocalRedirect("form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID);
}



//////////////////////////////////////////////////////////////////////
// initialize list - preparing data
global $by, $order;

$rsData = CFormStatus::GetList($WEB_FORM_ID, $by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// set navigation
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FORM_PAGES")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("FORM_TIMESTAMP"), "sort"=>"s_timestamp", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("FORM_ACTIVE"), "sort"=>"s_active", "default"=>true),
	array("id"=>"C_SORT", "content"=>GetMessage("FORM_C_SORT"), "sort"=>"s_sort", "default"=>true),
	array("id"=>"DEFAULT_VALUE", "content"=>GetMessage("FORM_DEFAULT"), "sort"=>"s_default", "default"=>true),
	array("id"=>"TITLE", "content"=>GetMessage("FORM_TITLE"), "sort"=>"s_title", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("FORM_DESCRIPTION"), "sort"=>"s_description", "default"=>true),
	array("id"=>"RESULTS", "content"=>GetMessage("FORM_RESULTS"), "sort"=>"s_results", "default"=>true),
));

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("DEFAULT_VALUE", ($f_DEFAULT_VALUE=="Y")?GetMessage("FORM_YES"):GetMessage("FORM_NO"));
	$row->AddViewField("RESULTS", "<a href='form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&find_status=$f_ID&set_filter=Y' title='".GetMessage("FORM_RESULT_TITLE")."'>$f_RESULTS</a>");
	$row->AddViewField("TITLE", "<a href='form_status_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID&ID=$f_ID' title='".GetMessage("FORM_EDIT")."'>$f_TITLE</a>");
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("C_SORT");

	$arActions = Array();

	$arActions[] = array(
		"DEFAULT"=>"Y",
		"ICON"=>"edit",
		"TITLE"=>GetMessage("FORM_EDIT_ALT"),
		"ACTION"=>$lAdmin->ActionRedirect("form_status_edit.php?lang=".LANGUAGE_ID."&ID=$f_ID&WEB_FORM_ID=$WEB_FORM_ID"),
		"TEXT"=>GetMessage("FORM_EDIT")
	);
	if ($F_RIGHT>=30)
	{
		$arActions[] = array(
			"ICON"=>"copy",
			"TITLE"=>GetMessage("FORM_COPY"),
			"ACTION"=>$lAdmin->ActionRedirect("form_status_list.php?lang=".LANGUAGE_ID."&cp_id=$f_ID&WEB_FORM_ID=$WEB_FORM_ID&".bitrix_sessid_get()),
			"TEXT"=>GetMessage("FORM_CP")
		);
		$arActions[] = array(
			"ICON"=>"delete",
			"TITLE"=>GetMessage("FORM_STATUS_DELETE_ALT"),
			"ACTION"=>"javascript:if(confirm('".GetMessage("FORM_DELETE_STATUS_CONFIRM")."')) ".$lAdmin->ActionRedirect("?lang=".LANGUAGE_ID."&action=delete&ID=$f_ID&WEB_FORM_ID=$WEB_FORM_ID&".bitrix_sessid_get()),
			"TEXT"=>GetMessage("FORM_STATUS_DELETE")
		);
	}
	$row->AddActions($arActions);
}




// list "footer"
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

// add group actions
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("FORM_DELETE_L"),
	"activate"=>GetMessage("FORM_ACTIVATE_L"),
	"deactivate"=>GetMessage("FORM_DEACTIVATE_L"),
	));


$aMenu = array(
	array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("FORM_ADD"),
		"TITLE"	=> GetMessage("FORM_ADD_STATUS"),
		"LINK"	=>	"/bitrix/admin/form_status_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
	),
);

$lAdmin->AddAdminContextMenu($aMenu);
$lAdmin->CheckListMode();

$sDocTitle = GetMessage("FORM_PAGE_TITLE");
$APPLICATION->SetTitle(str_replace("#ID#","$WEB_FORM_ID",$sDocTitle));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?echo EndNote();

echo ShowError($strError);
echo ShowNote($strNote);

// Filter
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FORM_FL_ID"),
			GetMessage("FORM_FL_ACTIVE"),
			GetMessage("FORM_FL_DESCR"),
			GetMessage("FORM_FL_RESULTS"),
		)
	);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?=GetMessage("FORM_F_TITLE")?></b></td>
	<td nowrap><input type="text" name="find_title" value="<?echo htmlspecialcharsbx($find_title)?>" size="47"><?=InputType("checkbox", "find_title_exact_match", "Y", $find_title_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("FORM_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?=GetMessage("FORM_F_ACTIVE")?></td>
	<td nowrap><?
	$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
	echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("FORM_ALL"));
	?></td>
</tr>
<tr>
	<td nowrap><?=GetMessage("FORM_F_DESCRIPTION")?></td>
	<td nowrap><input type="text" name="find_description" value="<?echo htmlspecialcharsbx($find_description)?>" size="47"><?=InputType("checkbox", "find_description_exact_match", "Y", $find_description_exact_match, false, "", "title='".GetMessage("FORM_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("FORM_F_RESULTS")?></td>
	<td nowrap><input type="text" name="find_results_1" value="<?=htmlspecialcharsbx($find_results_1)?>" size="5">&nbsp;<?=GetMessage("FORM_TILL")?>&nbsp;<input type="text" name="find_results_2" value="<?=htmlspecialcharsbx($find_results_2)?>" size="5"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>"form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=$WEB_FORM_ID"));
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");