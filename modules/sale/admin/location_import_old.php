<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$strWarning = "";
$strOK = "";
if ($_SERVER["REQUEST_METHOD"]=="POST" && check_bitrix_sessid())
{
	if (isset($_REQUEST["delete_all"]))
	{
		$strWarning = "";
		CSaleLocation::DeleteAll();
		$strOK = GetMessage("LOCATION_CLEAR_OK");
		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
	}
}

$APPLICATION->SetTitle(GetMessage("location_admin_import"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

echo ShowError($strWarning);
echo ShowNote($strOK, "oktext");

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("location_admin_import_tab"), "ICON" => "sale", "TITLE" => GetMessage("LOCA_LOADING")),
	array("DIV" => "edit3", "TAB" => GetMessage("LOCATION_CLEAR"), "ICON" => "sale", "TITLE" => GetMessage("LOCATION_CLEAR_DESC"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><input type="submit" onclick="WizardWindow.Open('bitrix:sale.locations', '<?=bitrix_sessid()?>');" class="adm-btn-save" value="<?=GetMessage('LOCA_LOADING_WIZARD')?>"></td>
	</tr>
<?
$tabControl->EndTab();
?>
<?
$tabControl->BeginNextTab();
?>
<form method="POST" action="sale_location_import.php" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
	<tr>
		<td colspan="2"><input onclick="return confirm('<?=GetMessage('LOCATION_CLEAR_CONFIRM');?>');" type="submit" name="delete_all" value="<?=GetMessage('LOCATION_CLEAR_BTN')?>"></td>
	</tr>
</form>
<?
$tabControl->EndTab();

$tabControl->End();

echo BeginNote();
echo GetMessage("LOCA_LOCATIONS_STATS").': <ul style="font-size: 100%">';

$rsLocations = CSaleLocation::GetList(array(), array(), array("COUNTRY_ID", "COUNT" => "CITY_ID"));

$numLocations = 0;
$numCountries = 0;
$numCities = 0;
$numRegion = 0;

while ($arStat = $rsLocations->Fetch())
{
	$numCountries++;
	$numCities += $arStat["CITY_ID"];
	$numLocations += $arStat['CNT'];
}

$rsRegion = CSaleLocation::GetRegionList(array(), array(), LANG);
$numRegion = $rsRegion->SelectedRowsCount();

echo '<li>'.GetMessage('LOCA_LOCATIONS_COUNTRY_STATS').': '.$numCountries.'</li>';
echo '<li>'.GetMessage('LOCA_LOCATIONS_REGION_STATS').': '.$numRegion.'</li>';
echo '<li>'.GetMessage('LOCA_LOCATIONS_CITY_STATS').': '.$numCities.'</li>';
echo '<li>'.GetMessage('LOCA_LOCATIONS_LOC_STATS').': '.$numLocations.'</li>';

$rsLocationGroups = CSaleLocationGroup::GetList();
$numGroups = 0;
while ($arGroup = $rsLocationGroups->Fetch()) $numGroups++;

echo '<li>'.GetMessage('LOCA_LOCATIONS_GROUP_STATS').': '.$numGroups.'</li>';
echo '</ul>';
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>