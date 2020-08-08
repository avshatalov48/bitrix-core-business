<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_delivery";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_lang",
	"filter_weight_from",
	"filter_weight_to",
	"filter_order_price_from",
	"filter_order_price_to",
	"filter_order_currency",
	"filter_active",
	"filter_location"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_lang <> '' && $filter_lang!="NOT_REF") $arFilter["LID"] = Trim($filter_lang);
if (intval($filter_weight_from)>0) $arFilter["+>=WEIGHT_TO"] = intval($filter_weight_from);
if (intval($filter_weight_to)>0) $arFilter["+<=WEIGHT_FROM"] = intval($filter_weight_to);
if (DoubleVal($filter_order_price_from)>0) $arFilter["+>=ORDER_PRICE_TO"] = DoubleVal($filter_order_price_from);
if (DoubleVal($filter_order_price_to)>0) $arFilter["+<=ORDER_PRICE_FROM"] = DoubleVal($filter_order_price_to);
if ($filter_order_currency <> '') $arFilter["ORDER_CURRENCY"] = Trim($filter_order_currency);
if ($filter_active <> '') $arFilter["ACTIVE"] = Trim($filter_active);
if (intval($filter_location)>0) $arFilter["LOCATION"] = intval($filter_location);


if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleDelivery::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleDelivery::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SDAN_ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CSaleDelivery::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SDAN_ERROR_UPDATE"), $ID);
				}

				break;
		}
	}
}

$dbResultList = CSaleDelivery::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("*")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("SALE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"LID", "content"=>GetMessage('SALE_LID'),	"sort"=>"LID", "default"=>true),
	array("id"=>"WEIGHT", "content"=>GetMessage("SALE_WEIGHT"),  "sort"=>"", "default"=>true),
	array("id"=>"ORDER_PRICE", "content"=>GetMessage("SALE_ORDER_PRICE"),  "sort"=>"", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SALE_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SALE_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"PRICE", "content"=>GetMessage("SALE_PRICE"),  "sort"=>"PRICE", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arCCard, "sale_delivery_edit.php?ID=".$f_ID."&lang=".LANG, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", "<a href=\"sale_delivery_edit.php?ID=".$f_ID."&lang=".LANG."\">".$f_ID."</a>");
	$row->AddField("NAME", $f_NAME);
	$row->AddField("LID", $f_LID);

	$fieldValue = "";
	if (intval($f_WEIGHT_FROM) > 0)
		$fieldValue .= " ".GetMessage("SALE_FROM")." ".$f_WEIGHT_FROM;
	if (intval($f_WEIGHT_TO) > 0)
		$fieldValue .= " ".GetMessage("SALE_TO")." ".$f_WEIGHT_TO;
	$row->AddField("WEIGHT", $fieldValue);

	$fieldValue = "";
	if (DoubleVal($f_ORDER_PRICE_FROM) > 0)
		$fieldValue .= " ".GetMessage("SALE_FROM")." ".SaleFormatCurrency($f_ORDER_PRICE_FROM, $f_ORDER_CURRENCY);
	if (DoubleVal($f_ORDER_PRICE_TO) > 0)
		$fieldValue .= " ".GetMessage("SALE_TO")." ".SaleFormatCurrency($f_ORDER_PRICE_TO, $f_ORDER_CURRENCY);
	$row->AddField("ORDER_PRICE", $fieldValue);

	$row->AddField("SORT", $f_SORT);
	$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? GetMessage("SD_YSE") : GetMessage("SD_NO")));
	$row->AddField("PRICE", SaleFormatCurrency($f_PRICE, $f_CURRENCY));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_delivery_edit.php?ID=".$f_ID."&lang=".LANG), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
			"TEXT" => GetMessage("SDAN_ADD_NEW"),
			"LINK" => "sale_delivery_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE" => GetMessage("SDAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
		array(
			"TEXT" => GetMessage("SDAN_HANDLERS"),
			"LINK" => "sale_delivery_handlers.php?lang=".LANG,
			"TITLE" => GetMessage("SDAN_HANDLERS_ALT"),
//			"ICON" => "btn_list"
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

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_LANG"),
		GetMessage("SALE_F_WEIGHT"),
		GetMessage("SALE_F_ORDER_PRICE"),
		GetMessage("SALE_F_ACTIVE")
	)
);

$oFilter->Begin();
?>
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
						<option value="<?echo $vars["ID"]?>"<?if (intval($vars["ID"])==intval($filter_location)) echo " selected"?>><?echo htmlspecialcharsbx($vars["COUNTRY_NAME"]." - ".$vars["CITY_NAME"])?></option>
					<?endwhile;?>
				</select>
			<?endif?>

		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_LANG");?>:</td>
		<td>
			<script language="JavaScript">
			var arLang = new Array();
			var arCurr = new Array();
			<?
			$db_extras = CLang::GetList(($b="name"), ($o="asc"));
			$i = 0;
			while ($extras = $db_extras->Fetch())
			{
				echo "arLang[".$i."]='".$extras["LID"]."';";
				echo "arCurr[".$i."]='".CSaleLang::GetLangCurrency($extras["LID"])."';";
				$i++;
			}
			?>

			function LangChange()
			{
				filter_lang = eval("document.find_form.filter_lang");
				filter_order_price_from = eval("document.find_form.filter_order_price_from");
				filter_order_price_to = eval("document.find_form.filter_order_price_to");
				f_currency = eval("document.find_form.f_currency");

				var i, esum;
				if (parseInt(filter_lang.selectedIndex)==0)
				{
					filter_order_price_from.disabled = true;
					filter_order_price_to.disabled = true;
					f_currency.value = "";
				}
				else
				{
					filter_order_price_from.disabled = false;
					filter_order_price_to.disabled = false;
					for (i = 0; i < arLang.length; i++)
					{
						if (filter_lang.options[filter_lang.selectedIndex].value == arLang[i])
						{
							f_currency.value = arCurr[i];
							break;
						}
					}
				}
			}
			</script>
			<?echo CLang::SelectBox("filter_lang", $filter_lang, GetMessage("SALE_ALL"), "LangChange()") ?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_WEIGHT")?>:</td>
		<td>
			<?echo GetMessage("SALE_F_FROM")?>
			<input type="text" name="filter_weight_from" value="<?echo htmlspecialcharsbx($filter_weight_from) ?>" size="5">
			<?echo GetMessage("SALE_F_TO")?>
			<input type="text" name="filter_weight_to" value="<?echo htmlspecialcharsbx($filter_weight_to) ?>" size="5">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_ORDER_PRICE")?>:</td>
		<td>
			<?echo GetMessage("SALE_F_FROM")?>
			<input type="text" name="filter_order_price_from" value="<?echo htmlspecialcharsbx($filter_order_price_from) ?>" size="10">
			<?echo GetMessage("SALE_F_TO")?>
			<input type="text" name="filter_order_price_to" value="<?echo htmlspecialcharsbx($filter_order_price_to) ?>" size="10">
			<input type="text" name="f_currency" value="<?echo CSaleLang::GetLangCurrency($filter_lang) ?>" size="3" readonly>
			<script language="JavaScript">
			LangChange();
			</script>
			<br>
			<small><?echo GetMessage("SALE_F_ORDER_PRICE_DESC")?></small>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?echo GetMessage("SALE_ALL")?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
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

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>