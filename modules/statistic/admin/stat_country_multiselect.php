<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
{
	if(CModule::IncludeModule("advertising"))
	{
		$isDemo = CAdvContract::IsDemo();
		$isManager = CAdvContract::IsManager();
		$isAdvertiser = CAdvContract::IsAdvertiser();
		$isAdmin = CAdvContract::IsAdmin();
		if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser)
			$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	else
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_stat_country_multiselect";
$oSort = new CAdminSorting($sTableID, "s_id", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_id_exact_match",
	"find_short_name",
	"find_short_name_exact_match",
	"find_name",
	"find_name_exact_match",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID" => ($find!="" && $find_type == "id"? $find: $find_id),
	"ID_EXACT_MATCH" => $find_id_exact_match,
	"SHORT_NAME" => ($find!="" && $find_type == "short_name"? $find: $find_short_name),
	"SHORT_NAME_EXACT_MATCH" => $find_short_name_exact_match,
	"NAME" => ($find!="" && $find_type == "name"? $find: $find_name),
	"NAME_EXACT_MATCH" => $find_name_exact_match,
);

$cData = new CCountry;
$rsData = $cData->GetList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_COUNTRY_MSEL_PAGES")));

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("STAT_COUNTRY_MSEL_ID"),
		"sort" => "s_id",
		"default" => true,
	),
	array(
		"id" => "SHORT_NAME",
		"content" => GetMessage("STAT_COUNTRY_MSEL_SHORT_NAME"),
		"sort" => "s_short_name",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("STAT_COUNTRY_MSEL_NAME"),
		"sort" => "s_name",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$row->AddViewField("ID", $f_ID.'<input type="hidden" name="NAME['.$f_ID.']" id="NAME['.$f_ID.']" value="['.$f_ID.'] '.$f_NAME.'">');
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

$lAdmin->AddGroupActionTable(array(
	array(
		"action" => "setTargetValue(0, 'form_".$sTableID."', '".CUtil::JSEscape($_REQUEST["field"])."')",
		"value" => "select",
		"type" => "button",
		"title" => GetMessage("STAT_COUNTRY_MSEL_SELECT_TITLE"),
		"name" => GetMessage("STAT_COUNTRY_MSEL_SELECT"),
	)
), array("disable_action_target"=>true));

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_COUNTRY_MSEL_TITLE"));

/***************************************************************************
				HTML
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");?>

<script type="text/javascript">
<!--
function setTargetValue(id, form_name, field)
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
		var oForm = document[form_name];

		for (var i = 0; i < oForm.elements.length; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				var opt_value=oForm.elements[i].value;
				var opt_name=document.getElementById('NAME['+opt_value+']').value;
				window.opener.jsSelectUtils.addNewOption(field, opt_value, opt_name);
			}
		}
	}

	if(arSelect.onchange)
		arSelect.onchange();
	window.close();
}
//-->
</script>

<?
$arFilterDropDown = array(
	GetMessage('STAT_COUNTRY_MSEL_ID'),
	GetMessage('STAT_COUNTRY_MSEL_SHORT_NAME'),
	GetMessage('STAT_COUNTRY_MSEL_NAME'),
);

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<input type="hidden" name="field" value="<?=htmlspecialcharsbx($_REQUEST["field"])?>">
<?
$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("STAT_COUNTRY_MSEL_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage('STAT_COUNTRY_MSEL_ID'),
				GetMessage('STAT_COUNTRY_MSEL_SHORT_NAME'),
				GetMessage('STAT_COUNTRY_MSEL_NAME'),
			),
			"reference_id" => array(
				"id",
				"name",
				"short_name",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_COUNTRY_MSEL_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_COUNTRY_MSEL_SHORT_NAME")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_short_name)?>"><?=ShowExactMatchCheckbox("find_short_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_COUNTRY_MSEL_NAME")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=ShowExactMatchCheckbox("find_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
