<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule("workflow")) die();

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE==false) die();

$IBLOCK_ID = intval($IBLOCK_ID);
$ELEMENT_ID = intval($ELEMENT_ID);
$find_section_section = intval($find_section_section);

$iblock = CIBlock::GetByID($IBLOCK_ID);
if($arIBlock=$iblock->Fetch())
{
	if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display"))
	{
		$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_HISTORY_TITLE", array("#ID#" => $ELEMENT_ID)));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(GetMessage("IBLOCK_ADM_HISTORY_BAD_IBLOCK"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$LAST_ID = CIBlockElement::WF_GetLast($ELEMENT_ID);
$z = CIblockElement::GetByID($LAST_ID);
if(!$zr=$z->Fetch())
{
	$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_HISTORY_TITLE", array("#ID#" => $ELEMENT_ID)));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><a href="/bitrix/admin/iblock_admin.php?type=<?echo htmlspecialcharsbx($type)?>&amp;lang=<?echo LANG?>"><?echo htmlspecialcharsex($arIBTYPE["NAME"])?></a> - <a href="<?echo htmlspecialcharsbx(CIBlock::GetAdminElementListLink($IBLOCK_ID, array()))?>"><?echo htmlspecialcharsbx($arIBlock["NAME"])?></a><?
	echo ShowError(GetMessage("IBLOCK_ADM_HISTORY_BAD_ELEMENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

// this is uniqu ajax id
$sTableID = "tbl_iblock_history";
// sort init
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
// list init
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_timestamp_from",
	"find_timestamp_to",
	"find_modified_by",
	"find_modified_user_id",
	"find_name",
	"find_status",
	"find_status_id"
	);

$lAdmin->InitFilter($arFilterFields);

$arFilter = Array(
	"ID"				=> $find_id,
	"TIMESTAMP_FROM"	=> $find_timestamp_from,
	"TIMESTAMP_TO"		=> $find_timestamp_to,
	"MODIFIED_BY"		=> $find_modified_by,
	"MODIFIED_USER_ID"	=> $find_modified_user_id,
	"NAME"				=> $find_name,
	"STATUS"			=> $find_status,
	"STATUS_ID"			=> $find_status_id
	);

// action handlers
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CIBlockElement::WF_GetHistoryList($ELEMENT_ID, $by, $order, $arFilter, $is_filtered);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = intval($ID);
		$arRes = CIBlockElement::GetByID($ID);
		$arRes = $arRes->Fetch();
		if(!$arRes)
			continue;

		$bPermissions = false;
		//delete and modify can:
		if(CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_any_wf_status ")) // only writers
		{
			$bPermissions = true;
		}
		else
		{
			//For delete action we have to check all statuses in element history
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"], $_REQUEST['action']=="delete"? $ID: false);
			if($STATUS_PERMISSION >= 2)
				$bPermissions = true;
		}

		if(!$bPermissions)
		{
			$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_HISTORY_UPDERR3")." (ID:".$ID.")", $ID);
			continue;
		}

		switch($_REQUEST['action'])
		{
		case "delete":
			$d = CIBlockElement::GetByID($ID);
			if($dr = $d->Fetch())
			{
				if(strlen($dr["WF_PARENT_ELEMENT_ID"])>0)
				{
					$DB->StartTransaction();
					if(!CIBlockElement::Delete(intval($ID)))
					{
						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_HISTORY_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
						else
							$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_HISTORY_DELETE_ERROR"), $ID);
						$DB->Rollback();
					}
					else
						$DB->Commit();
				}
			}
			break;
		case "restore":
			if(!CIBlockElement::WF_Restore($ID))
			{
				$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_HISTORY_RESTORE_ERROR"), $ID);
			}
			break;
		}
	}
}

// dataset
$rsData = CIBlockElement::WF_GetHistoryList($ELEMENT_ID, $by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IBLOCK_ADM_HISTORY_PAGER")));


// list headers
$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => GetMessage("IBLOCK_FIELD_ID"),
		"sort" => "s_id",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("IBLOCK_FIELD_NAME"),
		"sort" => "s_name",
		"default" => true,
	),
	array(
		"id" => "WF_STATUS_ID",
		"content" => GetMessage("IBLOCK_FIELD_STATUS"),
		"sort" => "s_status",
		"default" => true,
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("IBLOCK_FIELD_USER_NAME"),
		"sort" => "s_modified_by",
		"default" => true,
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
		"sort" => "s_timestamp_x",
		"default" => true,
	),
));

// list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($f_MODIFIED_BY>0)
		$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_BY.'">'.$f_MODIFIED_BY.'</a>] '.$f_USER_NAME.'</a>');

	$row->AddViewField("WF_STATUS_ID", '[<a href="workflow_status_edit.php?ID='.$f_WF_STATUS_ID.'&lang='.LANG.'">'.$f_WF_STATUS_ID.'</a>] '.htmlspecialcharsex(CIBlockElement::WF_GetStatusTitle($f_WF_STATUS_ID)));

	$arActions = Array();
	$arActions[] = array(
		"ICON"=>"view",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("IBLOCK_ADM_HISTORY_VIEW"),
		"TITLE"=>GetMessage("IBLOCK_ADM_HISTORY_VIEW_ALT"),
		"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?type='.$type.'&ID='.$f_ID.'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&view=Y&find_section_section='.$find_section_section)
		);

	$arActions[] = array("SEPARATOR"=>true);
	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage('IBLOCK_ADM_HISTORY_DELETE'),
		"TITLE"=>GetMessage("IBLOCK_ADM_HISTORY_DELETE_ALT"),
		"ACTION"=>"if(confirm('".GetMessageJS("IBLOCK_ADM_HISTORY_CONFIRM_DEL")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", 'type='.htmlspecialcharsbx($type).'&ELEMENT_ID='.$ELEMENT_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section_section)
		);
	$arActions[] = array(
		"ICON"=>"restore",
		"TEXT"=>GetMessage('IBLOCK_ADM_HISTORY_RESTORE'),
		"TITLE"=>GetMessage("IBLOCK_ADM_HISTORY_RESTORE_ALT"),
		"ACTION"=>"if(confirm('".GetMessageJS("IBLOCK_ADM_HISTORY_RESTORE_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "restore", 'type='.htmlspecialcharsbx($type).'&ELEMENT_ID='.$ELEMENT_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section_section)
		);

	$row->AddActions($arActions);
}

// footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);


// actions buttins
$lAdmin->AddGroupActionTable(array(
	"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"compare" => array(
		"action" => "Diff()",
		"value" => "compare",
		"type" => "button",
		"name" => GetMessage("IBLOCK_ADM_HISTORY_COMPARE"),
	),
));


// context menu
$aContext = array(
	array(
		"TEXT"=>GetMessage("IBLOCK_ADM_HISTORY_ORIGINAL"),
		"LINK"=>"iblock_element_edit.php?WF=Y&ID=".$ELEMENT_ID."&type=".htmlspecialcharsbx($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".$find_section_section,
		"TITLE"=>GetMessage("IBLOCK_ADM_HISTORY_ORIGINAL_TITLE")
	),
);

$lAdmin->AddAdminContextMenu($aContext);

//Chain
$chain = $lAdmin->CreateChain();

$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0));
$chain->AddItem(array(
	"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
	"LINK" => htmlspecialcharsbx($sSectionUrl),
));

if($find_section_section > 0)
{
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
	while($ar_nav = $nav->GetNext())
	{
		$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"]));
		$chain->AddItem(array(
			"TEXT" => $ar_nav["NAME"],
			"LINK" => htmlspecialcharsbx($sSectionUrl),
		));
	}
}

$chain->AddItem(array(
	"TEXT" => htmlspecialcharsex($zr["NAME"]),
	"LINK" => "iblock_element_edit.php?WF=Y&ID=".$ELEMENT_ID."&type=".htmlspecialcharsbx($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID,
));

$lAdmin->ShowChain($chain);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_HISTORY_TITLE", array("#ID#" => $ELEMENT_ID)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<script language="JavaScript">
<!--
function Diff()
{
	var selection = new Array();
	var j = 0;

	var l = document.form_tbl_iblock_history.elements['ID[]'].length;
	for(var i=0; i<l; i++)
	{
		var a = document.form_tbl_iblock_history.elements['ID[]'][i].checked;
		if (a == true)
		{
			selection[j] = document.form_tbl_iblock_history.elements['ID[]'][i].value;
			j++;
		}
	}
	if(j < 2 || j > 2)
	{
		alert('<?echo GetMessage("IBLOCK_ADM_HISTORY_COMPARE_ALERT")?>');
	}
	else
	{
		window.location='iblock_element_edit.php?type=<?echo urlencode($type)?>&lang=<?echo urlencode(LANG)?>&IBLOCK_ID=<?echo urlencode($IBLOCK_ID)?>&view=Y&find_section_section=<?echo $find_section_section?>&ID='+selection[0]+'&PREV_ID='+selection[1];
	}
}
//-->
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("IBLOCK_FIELD_ID"),
		GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
		GetMessage("IBLOCK_FIELD_MODIFIED_BY"),
		GetMessage("IBLOCK_FIELD_STATUS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><font class="tableheadtext"><b><?=GetMessage("IBLOCK_FIELD_NAME")?>:</b></td>
	<td><input type="text" name="find_name" value="<?echo (strlen($find_name)>0) ? htmlspecialcharsbx($find_name) : ""?>" size="38"></td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_FIELD_ID")?>:</td>
	<td><input type="text" name="find_id" size="38" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("IBLOCK_FIELD_TIMESTAMP_X").":"?></td>
	<td><?echo CalendarPeriod("find_timestamp_from", htmlspecialcharsbx($find_timestamp_from), "find_timestamp_to", htmlspecialcharsbx($find_timestamp_to), "find_form")?></font></td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_FIELD_MODIFIED_BY")?>:</td>
	<td>
		<?echo FindUserID(
			/*$tag_name=*/"find_modified_user_id",
			/*$tag_value=*/$find_modified_user_id,
			/*$user_name=*/"",
			/*$form_name=*/"find_form",
			/*$tag_size=*/"5",
			/*$tag_maxlength=*/"",
			/*$button_value=*/" ... ",
			/*$tag_class=*/"",
			/*$button_class=*/""
		);?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("IBLOCK_FIELD_STATUS")?>:</td>
	<td><input type="text" name="find_status_id" value="<?echo htmlspecialcharsbx($find_status_id)?>" size="3">&nbsp;<?
	echo SelectBox("find_status", CWorkflowStatus::GetDropDownList("Y"), GetMessage("IBLOCK_ALL"), htmlspecialcharsbx($find_status));
	?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?type=".htmlspecialcharsbx($type)."&IBLOCK_ID=".intval($IBLOCK_ID)."&ELEMENT_ID=".$ELEMENT_ID, "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
