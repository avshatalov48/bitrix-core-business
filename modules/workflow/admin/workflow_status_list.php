<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");
$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";


$sTableID = "t_wf_status_list";
$oSort = new CAdminSorting($sTableID, "s_timestamp", "desc");// sort init
$lAdmin = new CAdminList($sTableID, $oSort);// list init


$arFilterFields = Array(
	"find",
	"find_type",
	"find_id",
	"find_id_exact_match",
	"find_active",
	"find_title",
	"find_title_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_documents_1",
	"find_documents_2",
	"FILTER_logic",
);

$lAdmin->InitFilter($arFilterFields);//filter init

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		"ID",
		GetMessage("FLOW_F_ACTIVE"),
		GetMessage("FLOW_TITLE"),
		GetMessage("FLOW_F_DESCRIPTION"),
		GetMessage("FLOW_F_DOCUMENTS"),
		GetMessage('FLOW_F_LOGIC'),
	)
);

InitBVar($find_id_exact_match);
InitBVar($find_title_exact_match);
InitBVar($find_description_exact_match);


$arFilter = Array(
	"ID"			=> ($find!="" && $find_type == "id"? $find: $find_id),
	"ACTIVE"		=> $find_active,
	"TITLE"			=> ($find!="" && $find_type == "title"? $find: $find_title),
	"DESCRIPTION"	=> ($find!="" && $find_type == "description"? $find: $find_description),
	"DOCUMENTS_1"	=> $find_documents_1,
	"DOCUMENTS_2"	=> $find_documents_2,
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"TITLE_EXACT_MATCH"			=> $find_title_exact_match,
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
);

if($lAdmin->EditAction() && ($WORKFLOW_RIGHT == "W"))
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if (trim($arFields["TITLE"]) <> '')
		{
			$DB->StartTransaction();

			$obWorkflowStatus = new CWorkflowStatus;
			if($obWorkflowStatus->Update($ID, $arFields))
				$DB->Commit();
			else
				$DB->Rollback();
		}
		else
		{
			$lAdmin->AddUpdateError(GetMessage("FLOW_FORGOT_NAME", array("#ID#" => $ID)), $ID);
		}
	}
}


// actions handlers
if($WORKFLOW_RIGHT=="W" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CWorkflowStatus::GetList('', '', $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if($ID <= 1)
			continue;

		switch($_REQUEST['action'])
		{
			case "delete":

				@set_time_limit(0);

				$strSql = "SELECT 'x' FROM b_workflow_document WHERE STATUS_ID='".$ID."'";
				$z = $DB->Query($strSql, false);
				if ($zr = $z->Fetch())
				{
					$lAdmin->AddGroupError(GetMessage("FLOW_CANNOT_DELETE_STATUS"), $ID);
				}
				else
				{
					if(CModule::IncludeModule("iblock"))
					{
						$res = CIBlockElement::GetList(Array(), Array("WF_STATUS_ID" =>$ID, "SHOW_HISTORY" => "Y"));
						if ($res->Fetch())
						{
							$lAdmin->AddGroupError(GetMessage("FLOW_CANNOT_DELETE_STATUS_IBLOCK"), $ID);
						}
						else
						{
							$DB->StartTransaction();
							$DB->Query("DELETE FROM b_workflow_status WHERE ID='".$ID."'", false, $err_mess.__LINE__);
							$DB->Query("DELETE FROM b_workflow_status2group WHERE STATUS_ID='".$ID."'", false, $err_mess.__LINE__);
							$DB->Commit();
						}
					}

				}
			break;

			case "activate":
			case "deactivate":
				$obWorkflowStatus = new CWorkflowStatus;
				$arFields = array(
					"~TIMESTAMP_X" => $DB->GetNowFunction(),
					"ACTIVE" => ($_REQUEST['action'] == "activate"? "Y": "N"),
				);
				$obWorkflowStatus->Update($ID, $arFields);

			break;
		}
	}
}

global $by, $order;

$rsData = CWorkflowStatus::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLOW_PAGES")));

$arHeaders = Array();
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "s_id");

$arHeaders[] = Array("id"=>"TIMESTAMP_X", "content"=>GetMessage("FLOW_TIMESTAMP"), "default"=>true, "sort" => "s_timestamp");
$arHeaders[] = Array("id"=>"ACTIVE", "content"=>GetMessage("FLOW_ACTIVE"), "default"=>true, "sort" => "s_active");
$arHeaders[] = Array("id"=>"C_SORT", "content"=>GetMessage("FLOW_C_SORT"), "default"=>true, "sort" => "s_c_sort");
$arHeaders[] = Array("id"=>"TITLE", "content"=>GetMessage("FLOW_TITLE"), "default"=>true, "sort" => "s_title");
$arHeaders[] = Array("id"=>"DESCRIPTION", "content"=>GetMessage("FLOW_DESCRIPTION"), "default"=>false, "sort" => "s_description");
$arHeaders[] = Array("id"=>"DOCUMENTS", "content"=>GetMessage("FLOW_DOCUMENTS"), "default"=>true, "sort" => "s_documents");


$lAdmin->AddHeaders($arHeaders);

// list fill
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("TITLE",Array("size"=>"35"));
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("C_SORT", Array("size"=>"3"));

	$row->AddViewField("DOCUMENTS", '<a href="workflow_list.php?lang='.LANG.'&find_status='.$f_ID.'&set_filter=Y">'.$f_DOCUMENTS.'</a>');


	$arActions = Array();


	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("FLOW_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("workflow_status_edit.php?lang=".LANG."&ID=".$f_ID)
	);

	if ($WORKFLOW_RIGHT=="W" && $f_ID>1)
	{

		$arActions[] = Array("SEPARATOR" => true);

		$arActions[] = array(
			"ICON" => "delete",
			"TEXT"=>GetMessage("FLOW_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage('FLOW_DELETE_STATUS_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);

	}

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($WORKFLOW_RIGHT=="W")
{
	// action buttons
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}



$aContext = array(
	array(
			"ICON" => "btn_new",
			"TEXT" => GetMessage("FLOW_ADD"),
			"TITLE" => GetMessage("FLOW_ADD"),
			"LINK" => "workflow_status_edit.php?lang=".LANG
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("FLOW_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">

<?$filter->Begin();?>
<tr valign="top">
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage('FLOW_F_DESCRIPTION'),
				"ID",
				GetMessage('FLOW_TITLE'),
			),
			"reference_id" => array(
				"description",
				"id",
				"title",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>


<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top">
	<td nowrap><?=GetMessage("FLOW_F_ACTIVE")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_TITLE")?>:</td>
	<td nowrap><input type="text" name="find_title" value="<?echo htmlspecialcharsbx($find_title)?>" size="47"><?=ShowExactMatchCheckbox("find_title")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_F_DESCRIPTION")?>:</td>
	<td nowrap><input type="text" name="find_description" value="<?echo htmlspecialcharsbx($find_description)?>" size="47"><?=ShowExactMatchCheckbox("find_description")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?echo GetMessage("FLOW_F_DOCUMENTS")?>:</td>
	<td nowrap><input type="text" name="find_documents_1" value="<?=htmlspecialcharsbx($find_documents_1)?>" size="5"><?echo "&nbsp;".GetMessage("FLOW_TILL")."&nbsp;"?><input type="text" name="find_documents_2" value="<?=htmlspecialcharsbx($find_documents_2)?>" size="5"></td>
</tr>
<?=ShowLogicRadioBtn()?>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form" => "form1"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>