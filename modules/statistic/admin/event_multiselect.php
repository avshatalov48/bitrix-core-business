<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_event_multiselect";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_id_exact_match",
	"find_event1",
	"find_event1_exact_match",
	"find_event2",
	"find_event2_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_name",
	"find_name_exact_match",
	);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID"				=> ($find!="" && $find_type == "id"? $find:$find_id),
	"ID_EXACT_MATCH"		=> $find_id_exact_match,
	"EVENT1"			=> ($find!="" && $find_type == "event1"? $find:$find_event1),
	"EVENT1_EXACT_MATCH"		=> $find_event1_exact_match,
	"EVENT2"			=> ($find!="" && $find_type == "event2"? $find:$find_event2),
	"EVENT2_EXACT_MATCH"		=> $find_event2_exact_match,
	"DESCRIPTION"			=> $find_description,
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
	"NAME"				=> $find_name,
	"NAME_EXACT_MATCH"		=> $find_name_exact_match,
	);

$cData = new CStatEventType;
$rsData = $cData->GetSimpleList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_EVENT_TYPE_PAGES")));

$arHeaders = array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"s_id",
		"default"	=>true,
	),
	array(	"id"		=>"EVENT1",
		"content"	=>"event1",
		"sort"		=>"s_event1",
		"default"	=>true,
	),
	array(	"id"		=>"EVENT2",
		"content"	=>"event2",
		"sort"		=>"s_event2",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("STAT_NAME"),
		"sort"		=>"s_name",
		"default"	=>true,
	),
	array(	"id"		=>"DESCRIPTION",
		"content"	=>GetMessage("STAT_DESCRIPTION"),
		"sort"		=>"s_description",
		"default"	=>true,
	),
);
if($target_control=="text")
	$arHeaders[] =
		array(	"id"		=>"SELECT_BUTTON",
			"content"	=>"&nbsp;",
			"sort"		=>"",
			"default"	=>true,
		);

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$row->AddViewField("EVENT1",$f_EVENT1.'<input type="hidden" name="EVENT_NAME['.$f_ID.']" id="EVENT_NAME['.$f_ID.']" value="'.$f_EVENT." [".$f_ID.']">');

	$id = CUtil::JSEscape($f_ID.($full_name=="Y"?" (".$f_EVENT1." / ".$f_EVENT2.")": ""));
	$fld = CUtil::JSEscape($field);
	$row->AddViewField("SELECT_BUTTON","<a href=\"".htmlspecialcharsbx("javascript:setTargetValue('".$id."', '".$fld."');")."\" title=\"".GetMessage("STAT_CHOOSE_TITLE")."\">".GetMessage("STAT_CHOOSE")."</a>");

	if($target_control=="text")
	{
		$arActions = array(
			array(
				"ICON"=>"",
				"DEFAULT"=>true,
				"TEXT"=>GetMessage("STAT_CHOOSE"),
				"ACTION"=>htmlspecialcharsbx("javascript:setTargetValue('".$id."', '".$fld."');"),
			),
		);
		$row->AddActions($arActions);
	}
endwhile;

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
$arFooter[] = array(
	"counter"=>true,
	"title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
	"value"=>"0",
	);
$lAdmin->AddFooter($arFooter);

if($target_control!="text")
{
	$lAdmin->AddGroupActionTable(array(
		array(
			"action" => "setTargetValue(0, '".CUtil::JSEscape($field)."')",
			"value" => "select",
			"type" => "button",
			"title" => GetMessage("STAT_SELECT_TITLE"),
			"name" => GetMessage("STAT_SELECT"),
		)
	), array("disable_action_target"=>true));
}
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));

/***************************************************************************
					HTML
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");?>

<script type="text/javascript">
<!--
function setTargetValue(id, field)
{
	var arSelect = window.opener.document.getElementById(field);
	if(!arSelect)
	{
		arSelect = window.opener.document.getElementsByName(field);
		if(arSelect && arSelect.length>0)
			arSelect=arSelect[0];
	}

	if(id!=0)
	{
		if(arSelect.type.toLowerCase()=='text')
			arSelect.value=id;
	}
	else
	{
		var oForm = document.form_<?=$sTableID?>;

		for (var i = 0; i < oForm.elements.length; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				if(window.opener.selectEventType)
				{
					var opt_value=oForm.elements[i].value;
					var opt_name=document.getElementById('EVENT_NAME['+oForm.elements[i].value+']').value;
					window.opener.jsSelectUtils.addNewOption(field, opt_value, opt_name);
				}
				else
				{
					for(var j=0; j<arSelect.length; j++)
						if (arSelect.options[j].value==oForm.elements[i].value)
							arSelect.options[j].selected = true;
				}
			}
		}
	}
	window.opener.jsSelectUtils.selectAllOptions(field);
	window.close();
}
//-->
</script>

<?
$arFilterDropDown = array(
	GetMessage('STAT_F_ID'),
	"event1",
	"event2",
	GetMessage("STAT_NAME"),
	GetMessage("STAT_DESCRIPTION"),
);

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?
$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("STAT_F_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_F_FIND_ENTER")?>">
		<?
		$arr = array(
			"reference" => array(
				"event1",
				"event2",
				GetMessage('STAT_F_ID'),
			),
			"reference_id" => array(
				"event1",
				"event2",
				"id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event1</td>
	<td><input type="text" name="find_event1" size="47" value="<?echo htmlspecialcharsbx($find_event1)?>"><?=ShowExactMatchCheckbox("find_event1")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event2</td>
	<td><input type="text" name="find_event2" size="47" value="<?echo htmlspecialcharsbx($find_event2)?>"><?=ShowExactMatchCheckbox("find_event2")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_NAME")?></td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=ShowExactMatchCheckbox("find_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_DESCRIPTION")?></td>
	<td><input type="text" name="find_description" size="47" value="<?echo htmlspecialcharsbx($find_description)?>"><?=ShowExactMatchCheckbox("find_description")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
