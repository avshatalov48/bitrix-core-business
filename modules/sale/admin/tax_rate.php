<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

ClearVars("l_");

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_tax_rate";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_tax_id",
	"filter_person_type_id",
	"filter_lang",
	"filter_location"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_lang) > 0 && $filter_lang != "NOT_REF")
	$arFilter["LID"] = Trim($filter_lang);
if (IntVal($filter_tax_id) > 0)
	$arFilter["TAX_ID"] = IntVal($filter_tax_id);
if (IntVal($filter_person_type_id) > 0)
	$arFilter["PERSON_TYPE_ID"] = IntVal($filter_person_type_id);
if (IntVal($filter_location) > 0)
	$arFilter["LOCATION"] = IntVal($filter_location);


if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleTaxRate::GetList(
				array($by => $order),
				$arFilter
			);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleTaxRate::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SALE_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CSaleTaxRate::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_EDIT_TAX_RATE"), $ID);
				}

				break;
		}
	}
}


$dbResultList = CSaleTaxRate::GetList(Array($by => $order), $arFilter);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_TAX_RATE_LIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("RATE_ACTIVE"), "sort"=>"ACTIVE", "default"=>true, "align" => "center",),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("TAX_TIMESTAMP"), "sort"=>"TIMESTAMP_X", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("TAX_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"PERSON_TYPE_ID", "content"=>GetMessage("RATE_PERSON_TYPE"), "sort"=>"PERSON_TYPE_ID", "default"=>true),
	array("id"=>"VALUE", "content"=>GetMessage("RATE_VALUE"), "sort"=>"", "default"=>true),
	array("id"=>"IS_IN_PRICE", "content"=>GetMessage("RATE_IS_INPRICE"), "sort"=>"IS_IN_PRICE", "default"=>true),
	array("id"=>"APPLY_ORDER", "content"=>GetMessage("RATE_APPLY_ORDER"), "sort"=>"APPLY_ORDER", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arPersonTypeList = array();
$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
while ($arPersonType = $dbPersonType->Fetch())
{
	$arPersonTypeList[$arPersonType["ID"]] = Array("ID" => $arPersonType["ID"], "NAME" => htmlspecialcharsEx($arPersonType["NAME"]), "LID" => implode(", ", $arPersonType["LIDS"]));
}

while ($arTaxRate = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arTaxRate);

	$row->AddField("ID", $f_ID);

	$row->AddField("ACTIVE", ($f_ACTIVE=="Y") ? GetMessage("RATE_YES") : GetMessage("RATE_NET"));

	$row->AddField("TIMESTAMP_X", $f_TIMESTAMP_X);

	$fieldShow = '<a href="sale_tax_edit.php?ID='.$f_TAX_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage('TAX_EDIT_DESCR').'">'.$f_NAME.'</a> ('.$f_LID.')';
	$row->AddField("NAME", $fieldShow);

	$fieldShow = "";
	if (in_array("PERSON_TYPE_ID", $arVisibleColumns))
	{
		if (IntVal($f_PERSON_TYPE_ID)>0)
		{
			$arPerType = $arPersonTypeList[$f_PERSON_TYPE_ID];
			$fieldShow .= "[".$arPerType["ID"]."] ".$arPerType["NAME"]." (".htmlspecialcharsEx($arPerType["LID"]).")";
		}
		else
		{
			$fieldShow .= "&nbsp;";
		}
	}
	$row->AddField("PERSON_TYPE_ID", $fieldShow);

	$row->AddField("VALUE", $f_VALUE.(($f_IS_PERCENT=="Y") ? "%" : " ".$f_CURRENCY));
	$row->AddField("IS_IN_PRICE", ($f_IS_IN_PRICE=="Y") ? GetMessage("RATE_YES") : GetMessage("RATE_NET"));
	$row->AddField("APPLY_ORDER", $f_APPLY_ORDER);

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("RATE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_tax_rate_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("RATE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('TAX_RATE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("STRAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_tax_rate_edit.php?lang=".LANG,
			"TITLE" => GetMessage("STRAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}


$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_LANG"),
		GetMessage("SALE_F_PERSON_TYPE"),
		GetMessage("SALE_F_LOCATION"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_TAX")?>:</td>
		<td>
			<?$db_TAX = CSaleTax::GetList(array("NAME" => "ASC"), array());?>
			<select name="filter_tax_id">
				<option value=""><?echo GetMessage("SALE_ALL") ?></option>
				<?
				while ($db_TAX_arr = $db_TAX->NavNext(true, "fp_"))
				{
					?><option value="<?echo $fp_ID ?>" <?if (IntVal($fp_ID)==IntVal($filter_tax_id)) echo "selected";?>><?echo $fp_NAME ?> (<?echo $fp_LID ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_LANG");?>:</td>
		<td>
			<?echo CLang::SelectBox("filter_lang", $filter_lang, GetMessage("SALE_ALL")) ?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_PERSON_TYPE")?>:</td>
		<td>
			<?echo CSalePersonType::SelectBox("filter_person_type_id", $filter_person_type_id, GetMessage("SALE_ALL"), True, "", "")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_LOCATION")?>:</td>
		<td>
			<?if(CSaleLocation::isLocationProEnabled()):?>

				<div style="width: 100%; margin-left: 12px">

					<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
						"ID" => $filter_location,
						"CODE" => "",
						"INPUT_NAME" => 'filter_location',
						"PROVIDE_LINK_BY" => "id",
						"SHOW_ADMIN_CONTROLS" => 'N',
						"SELECT_WHEN_SINGLE" => 'N',
						"FILTER_BY_SITE" => 'N',
						"SHOW_DEFAULT_LOCATIONS" => 'N',
						"SEARCH_BY_PRIMARY" => 'Y',
						"INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
						"GLOBAL_EVENT_SCOPE" => 'window'
						),
						false
					);?>

				</div>

				<style>
					.adm-filter-item-center,
					.adm-filter-content {
						overflow: visible !important;
					}
				</style>

			<?else:?>
				<select name="filter_location">
					<option value=""><?echo GetMessage("SALE_ALL")?></option>
					<?$db_vars = CSaleLocation::GetList(Array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANG)?>
					<?while ($vars = $db_vars->Fetch()):?>
						<option value="<?echo $vars["ID"]?>"<?if (IntVal($vars["ID"])==IntVal($filter_location)) echo " selected"?>><?echo htmlspecialcharsbx($vars["COUNTRY_NAME"]." - ".$vars["CITY_NAME"])?></option>
					<?endwhile;?>
				</select>
			<?endif?>
		</td>
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

<?echo BeginNote();?>
	<?echo GetMessage("RATE_ORDER_NOTES")?><br>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>