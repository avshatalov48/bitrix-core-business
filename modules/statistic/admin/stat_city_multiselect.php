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

$sTableID = "tbl_stat_city_multiselect";
$oSort = new CAdminSorting($sTableID, "CITY", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_country_id",
	"find_country_id_exact_match",
	"find_country_short_name",
	"find_country_short_name_exact_match",
	"find_country_name",
	"find_country_name_exact_match",
	"find_region_name",
	"find_region_name_exact_match",
	"find_city_name",
	"find_city_name_exact_match",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	($find_country_id_exact_match=="Y"? "=": "%")."COUNTRY_ID" => ($find!="" && $find_type == "county_id"? $find: $find_country_id),
	($find_country_short_name_exact_match=="Y"? "=": "%")."COUNTRY_SHORT_NAME" => ($find!="" && $find_type == "county_short_name"? $find: $find_country_short_name),
	($find_country_name_exact_match=="Y"? "=": "%")."COUNTRY_NAME" => ($find!="" && $find_type == "county_name"? $find: $find_country_name),
	($find_region_name_exact_match=="Y"? "=": "%")."REGION_NAME" => ($find!="" && $find_type == "region_name"? $find: $find_region_name),
	($find_city_name_exact_match=="Y"? "=": "%")."CITY_NAME" => ($find!="" && $find_type == "city_name"? $find: $find_city_name),
);
foreach($arFilter as $i=>$flt)
	if(trim($flt) == "")
		unset($arFilter[$i]);

$cData = new CCity;
$rsData = $cData->GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_CITY_MSEL_PAGES")));

$arHeaders = array(
	array(
		"id" => "COUNTRY_ID",
		"content" => GetMessage("STAT_CITY_MSEL_COUNTRY_ID"),
		"sort" => "CITY",
		"default" => true,
	),
	array(
		"id" => "COUNTRY_SHORT_NAME",
		"content" => GetMessage("STAT_CITY_MSEL_COUNTRY_SHORT_NAME"),
		"sort" => "COUNTRY_SHORT_NAME",
		"default" => true,
	),
	array(
		"id" => "COUNTRY_NAME",
		"content" => GetMessage("STAT_CITY_MSEL_COUNTRY_NAME"),
		"sort" => "COUNTRY_NAME",
		"default" => true,
	),
	array(
		"id" => "REGION_NAME",
		"content" => GetMessage("STAT_CITY_MSEL_REGION_NAME"),
		"sort" => "REGION_NAME",
		"default" => true,
	),
	array(
		"id" => "CITY_NAME",
		"content" => GetMessage("STAT_CITY_MSEL_CITY_NAME"),
		"sort" => "CITY_NAME",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_CITY_ID, $arRes);
	$row->AddViewField("COUNTRY_ID", $f_COUNTRY_ID.'<input type="hidden" name="CITY_NAME['.$f_CITY_ID.']" id="CITY_NAME['.$f_CITY_ID.']" value="['.$f_COUNTRY_ID.'] ['.$f_REGION_NAME.'] '.$f_CITY_NAME.'">');
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
		"title" => GetMessage("STAT_CITY_MSEL_SELECT_TITLE"),
		"name" => GetMessage("STAT_CITY_MSEL_SELECT"),
	)
), array("disable_action_target"=>true));

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_CITY_MSEL_TITLE"));

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
				var opt_name=document.getElementById('CITY_NAME['+opt_value+']').value;
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
	GetMessage('STAT_CITY_MSEL_COUNTRY_ID'),
	GetMessage('STAT_CITY_MSEL_COUNTRY_SHORT_NAME'),
	GetMessage('STAT_CITY_MSEL_COUNTRY_NAME'),
	GetMessage('STAT_CITY_MSEL_REGION_NAME'),
	GetMessage('STAT_CITY_MSEL_CITY_NAME'),
);

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<input type="hidden" name="field" value="<?=htmlspecialcharsbx($_REQUEST["field"])?>">
<?
$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("STAT_CITY_MSEL_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage('STAT_CITY_MSEL_COUNTRY_ID'),
				GetMessage('STAT_CITY_MSEL_COUNTRY_SHORT_NAME'),
				GetMessage('STAT_CITY_MSEL_COUNTRY_NAME'),
				GetMessage('STAT_CITY_MSEL_REGION_NAME'),
				GetMessage('STAT_CITY_MSEL_CITY_NAME'),
			),
			"reference_id" => array(
				"county_id",
				"county_name",
				"county_short_name",
				"region_name",
				"city_name",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_CITY_MSEL_COUNTRY_ID")?></td>
	<td><input type="text" name="find_country_id" size="47" value="<?echo htmlspecialcharsbx($find_country_id)?>"><?=ShowExactMatchCheckbox("find_country_id")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_CITY_MSEL_COUNTRY_SHORT_NAME")?></td>
	<td><input type="text" name="find_country_short_name" size="47" value="<?echo htmlspecialcharsbx($find_country_short_name)?>"><?=ShowExactMatchCheckbox("find_country_short_name")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_CITY_MSEL_COUNTRY_NAME")?></td>
	<td><input type="text" name="find_country_name" size="47" value="<?echo htmlspecialcharsbx($find_country_name)?>"><?=ShowExactMatchCheckbox("find_country_name")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_CITY_MSEL_REGION_NAME")?></td>
	<td><input type="text" name="find_region_name" size="47" value="<?echo htmlspecialcharsbx($find_region_name)?>"><?=ShowExactMatchCheckbox("find_region_name")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_CITY_MSEL_CITY_NAME")?></td>
	<td><input type="text" name="find_city_name" size="47" value="<?echo htmlspecialcharsbx($find_city_name)?>"><?=ShowExactMatchCheckbox("find_city_name")?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
