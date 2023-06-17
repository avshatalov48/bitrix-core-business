<?
@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile("/bitrix/modules/main/admin/update_system_market.php");

if(!$USER->CanDoOperation('install_updates'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_updates_marketplace_list";

$oSort = new CAdminSorting($sTableID, "NAME", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

if ($_REQUEST["action"] == "load" && $_REQUEST["id"] <> '' && check_bitrix_sessid())
{
	$errorMessage = "";
	if (!CUpdateClientPartner::LoadModuleNoDemand($_REQUEST["id"], $errorMessage, "Y", false))
	{
		$lAdmin->AddGroupError($errorMessage, $_REQUEST["id"]);
	}
	else
	{
		LocalRedirect("/bitrix/admin/module_admin.php?lang=".LANG."&id=".urlencode($_REQUEST["id"])."&".bitrix_sessid_get()."&install=Y");
	}
}

$errorMessage = "";
$arCurrentModules = CUpdateClientPartner::GetCurrentModules($errorMessage);
if ($errorMessage <> '')
	$lAdmin->AddGroupError($errorMessage, 0);

$arFilterFields = array(
	"filter_category",
	"filter_type",
	"filter_name",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if ($filter_category <> '')
	$arFilter["CATEGORY"] = $filter_category;
if ($filter_type <> '')
	$arFilter["TYPE"] = $filter_type;
if ($filter_name <> '')
	$arFilter["NAME"] = $filter_name;

$errorMessage = "";
$arModules = CUpdateClientPartner::SearchModulesEx(array($by => $order), $arFilter, (intval($_REQUEST["PAGEN_1"]) > 0 ? intval($_REQUEST["PAGEN_1"]) : 1), LANG, $errorMessage);
if ($errorMessage <> '')
	$lAdmin->AddGroupError($errorMessage, 0);

$arResultListTmp = array();
if (is_array($arModules["MODULE"]))
{
	foreach ($arModules["MODULE"] as $module)
		$arResultListTmp[] = $module["@"];
}
$dbResultList = new CDBResult();
$dbResultList->InitFromArray($arResultListTmp);

//echo "<pre>!1!<br>";print_r($arModules);echo "</pre>";

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart(array("bShowAll" => false, "nPageSize" => 20));

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("USMP_MODULES")));

$lAdmin->AddHeaders(array(
	array("id"=>"IMAGE", "content"=>GetMessage("USMP_H_IMAGE"), "sort"=>"", "default"=>true),
	array("id"=>"ID", "content"=>GetMessage("USMP_H_ID"), "sort"=>"CODE", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("USMP_H_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("USMP_H_DESCR"), "default"=>true),
	array("id"=>"PARTNER", "content"=>GetMessage("USMP_H_PARTNER"), "default"=>true),
	array("id"=>"DATE_UPDATE", "content"=>GetMessage("USMP_H_DATE_UPDATE"), "sort"=>"DATE_UPDATE", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("USMP_H_DATE_CREATE"), "sort"=>"DATE_CREATE", "default"=>false),
	array("id"=>"CATEGORY", "content"=>GetMessage("USMP_H_CAT"), "default"=>false),
	array("id"=>"TYPE", "content"=>GetMessage("USMP_H_TYPE"), "default"=>false),
	array("id"=>"LOADED", "content"=>GetMessage("USMP_H_LOADED"), "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arResultItem = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($arResultItem["ID"], $arResultItem);

	$row->AddField(
		"ID",
		'<a href="update_system_market_detail.php?id='.$arResultItem["ID"].'&'.GetFilterParams("filter_").'" title="'.GetMessage("USMP_VIEW").'">'.$arResultItem["ID"].'</a>'
	);
	$row->AddField("NAME", $arResultItem["NAME"]);

	$row->AddField("DESCRIPTION", nl2br($arResultItem["DESCRIPTION"]));
	$row->AddField("DATE_UPDATE", $arResultItem["DATE_UPDATE"]);
	$row->AddField("DATE_CREATE", $arResultItem["DATE_CREATE"]);
	$row->AddField("PARTNER", $arResultItem["PARTNER"]);

	$strImage = "";
	if ($arResultItem["IMAGE"] <> '')
		$strImage = '<img src="'.$arResultItem["IMAGE"].'" width="'.$arResultItem["IMAGE_WIDTH"].'" height="'.$arResultItem["IMAGE_HEIGHT"].'">';
	$row->AddField("IMAGE", $strImage);

	$row->AddField("LOADED", array_key_exists($arResultItem["ID"], $arCurrentModules) ? GetMessage("USMP_YES") : GetMessage("USMP_NO"));

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("USMP_VIEW"),
		"ACTION" => $lAdmin->ActionRedirect('update_system_market_detail.php?id='.$arResultItem["ID"].'&'.GetFilterParams("filter_").''),
		"DEFAULT" => true
	);
	if (!array_key_exists($arResultItem["ID"], $arCurrentModules))
	{
		$arActions[] = array(
			"ICON" => "load",
			"TEXT" => GetMessage("USMP_DO_LOAD"),
			"ACTION" => $lAdmin->ActionRedirect('update_system_market.php?id='.$arResultItem["ID"].'&action=load&'.bitrix_sessid_get()),
			"DEFAULT" => false
		);
	}

	$row->AddActions($arActions);
}

$aContext = array();
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">

<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("USMP_F_NAME"),
		GetMessage("USMP_H_CAT"),
		GetMessage("USMP_H_TYPE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("USMP_F_NAME") ?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("USMP_H_CAT") ?>:</td>
		<td><select name="filter_category">
			<option value="0"><?= GetMessage("USMP_CAT_0") ?></option>
			<?
			foreach ($arModules['MODULES_CATEGORIES'][0]["#"]["CATEGORY"] as $ct)
				echo '<option value="'.$ct["@"]["ID"].'"'.(($filter_category == $ct["@"]["ID"]) ? ' selected' : '').'>'.$ct["@"]["NAME"].'</option>';
			?></select></td>
	</tr>
	<tr>
		<td><?= GetMessage("USMP_H_TYPE") ?>:</td>
		<td><select name="filter_type">
			<option value="0"><?= GetMessage("USMP_TYPE_0") ?></option>
			<?
			if (isset($arModules['MODULES_TYPES'][0]["#"]["TYPE"]) && is_array($arModules['MODULES_TYPES'][0]["#"]["TYPE"]))
			{
				foreach ($arModules['MODULES_TYPES'][0]["#"]["TYPE"] as $ct)
				{
					echo '<option value="'.$ct["@"]["ID"].'"'.(($filter_type == $ct["@"]["ID"]) ? ' selected' : '').'>'.$ct["@"]["NAME"].'</option>';
				}
			}
			?></select></td>
	</tr>

<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>