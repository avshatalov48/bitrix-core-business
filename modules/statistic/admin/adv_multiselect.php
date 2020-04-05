<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$sTableID = "t_adv_multiselect";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);


$filter = new CAdminFilter(
	$sTableID."_filter_id",
	Array(
		"ID",
		"referer1",
		"referer2",
		GetMessage("STAT_DESCRIPTION"),
	)
);

$FilterArr = Array(
	"find", "find_type",
	"find_id","find_id_exact_match",
	"find_referer1","find_referer1_exact_match",
	"find_referer2","find_referer2_exact_match",
	"find_description","find_description_exact_match",
	);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID"						=> ($find!="" && $find_type == "id"? $find: $find_id),
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"REFERER1"					=> ($find!="" && $find_type == "referer1"? $find: $find_referer1),
	"REFERER1_EXACT_MATCH"		=> $find_referer1_exact_match,
	"REFERER2"					=> ($find!="" && $find_type == "referer2"? $find: $find_referer2),
	"REFERER2_EXACT_MATCH"		=> $find_referer2_exact_match,
	"DESCRIPTION"				=> ($find!="" && $find_type == "description"? $find: $find_description),
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
	);


$rsData = CAdv::GetSimpleList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_ADV_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,);
$arHeaders[] = array("id"=>"REFERER1", "content"=>"referer1", "sort"=>"s_referer1", "default"=>true,);
$arHeaders[] = array("id"=>"REFERER2", "content"=>"referer2", "sort"=>"s_referer2", "default"=>true,);
$arHeaders[] = array("id"=>"DESCRIPTION", "content"=>GetMessage("STAT_DESCRIPTION"), "sort"=>"s_description", "default"=>true,);

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("REFERER1",$f_REFERER1.'<input type="hidden" name="ADV_NAME['.$f_ID.']" id="ADV_NAME['.$f_ID.']" value="'.$f_REFERER1." [".$f_ID.']">');
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"select" => array(
			"action" => "setTargetValue(0, '".addslashes($field)."')",
			"value" => "select",
			"type" => "button",
			"name" => GetMessage("STAT_SELECT"),
	),
),array("disable_action_target"=>true));

$lAdmin->CheckListMode();
//$rs = CAdv::GetSimpleList($by, $order, $arFilter, $is_filtered);


$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">

<?$filter->Begin();?>
<tr>
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">

		<?
		$arr = array(
			"reference" => array(
				"ID",
				"referer1",
				"referer2",
				GetMessage("STAT_DESCRIPTION"),
			),
			"reference_id" => array(
				"id",
				"referer1",
				"referer2",
				"description",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>


<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="35" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer1:</td>
	<td><input type="text" name="find_referer1" size="35" value="<?echo htmlspecialcharsbx($find_referer1)?>"><?=ShowExactMatchCheckbox("find_referer1")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer2:</td>
	<td><input type="text" name="find_referer2" size="35" value="<?echo htmlspecialcharsbx($find_referer2)?>"><?=ShowExactMatchCheckbox("find_referer2")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_DESCRIPTION")?>:</td>
	<td><input type="text" name="find_description" size="35" value="<?echo htmlspecialcharsbx($find_description)?>"><?=ShowExactMatchCheckbox("find_description")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>


<?$filter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>


<script language="JavaScript">
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
					var opt_name=document.getElementById('ADV_NAME['+oForm.elements[i].value+']').value;
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
