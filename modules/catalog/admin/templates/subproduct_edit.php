<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Currency,
	Bitrix\Catalog;

$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
$publicMode = (defined("SELF_FOLDER_URL") ? true : false);

if ($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_price') || $USER->CanDoOperation('catalog_view'))
{
	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/templates/product_edit.php');

	$currencyList = array();
	foreach (Currency\CurrencyManager::getCurrencyList() as $currency => $currencyName)
	{
		$currencyList[$currency] = array(
			'CURRENCY' => $currency,
			'FULL_NAME' => htmlspecialcharsbx($currencyName)
		);
		$currencyList[$currency]['FULL_NAME_JS'] = CUtil::JSEscape($currencyList[$currency]['FULL_NAME']);
	}
	unset($currency, $currencyName);

	$bDiscount = $USER->CanDoOperation('catalog_discount');
	$bStore = $USER->CanDoOperation('catalog_store');
	$bUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
	$bEnableReservation = ('N' != COption::GetOptionString('catalog', 'enable_reservation'));
	$enableQuantityRanges = Catalog\Config\Feature::isPriceQuantityRangesEnabled();

	$availQuantityTrace = COption::GetOptionString("catalog", "default_quantity_trace");
	$availCanBuyZero = COption::GetOptionString("catalog", "default_can_buy_zero");
	$strGlobalSubscribe = COption::GetOptionString("catalog", "default_subscribe");

	$IBLOCK_ID = intval($IBLOCK_ID);
	if ($IBLOCK_ID <= 0)
		return;
	$arCatalog = CCatalog::GetByID($IBLOCK_ID);
	$PRODUCT_ID = (0 < $ID ? CIBlockElement::GetRealElement($ID) : 0);
	$arBaseProduct = CCatalogProduct::GetByID($PRODUCT_ID);

	$periodTimeTypes = array();
	if ($arCatalog["SUBSCRIPTION"]=="Y")
	{
		$periodTimeTypes = Catalog\ProductTable::getPaymentPeriods(true);
	}

	if (0 < $PRODUCT_ID)
	{
		$bReadOnly = !($USER->CanDoOperation('catalog_price') && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, "element_edit_price"));
	}
	else
	{
		$bReadOnly = !($USER->CanDoOperation('catalog_price') && CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_edit_price"));
	}
	if ($bSubCopy)
	{
		$arBaseProduct['QUANTITY'] = '';
		$arBaseProduct['QUANTITY_RESERVED'] = '';
	}

	$subscribeEnabled = $arBaseProduct["SUBSCRIBE"] == 'Y';
	$activitySubscribeTab = $PRODUCT_ID > 0 && !$bCopy && $subscribeEnabled;

	$arExtraList = array();
	$l = CExtra::GetList(array("NAME" => "ASC"));
	while ($l_res = $l->Fetch())
	{
		$arExtraList[] = $l_res;
	}
	?>
<tr class="heading">
<td colspan="2"><?
	echo GetMessage("IBLOCK_TCATALOG");
	if ($bReadOnly) echo " ".GetMessage("IBLOCK_TREADONLY");
	?>
<script type="text/javascript">
var bReadOnly = <? echo ($bReadOnly ? 'true' : 'false'); ?>;

function getElementSubForm()
{
	for(var i = 0; i < document.forms.length; i++)
	{
		var check = document.forms[i].name.substring(0, 10).toUpperCase();
		if(check == 'FORM_ELEME' || check == 'TABCONTROL')
			return document.forms[i];
	}
}
function getElementSubFormName()
{
	var form = getElementSubForm();
	if (form)
		return form.name;
	else
		return '';
}
function checkSubForm(e)
{
	if (window.BX_CANCEL)
		return true;

	if (!e)
		e = window.event;

	var bReturn = true;

	if (BX('SUBCAT_ROW_COUNTER').value > 0 && !!BX('subprice_useextform') && !BX('subprice_useextform').checked)
	{
		bReturn = confirm('<?=CUtil::JSEscape(GetMessage("CAT_E_PRICE_EXT"))?>');
	}
	if (!bReturn)
	{
		if (e.preventDefault)
			e.preventDefault();

		return false;
	}

	return true;
}

jsUtils.addEvent(window, 'load', function () {
	var obForm = getElementSubForm();
	jsUtils.addEvent(obForm, 'submit', checkSubForm);
	jsUtils.addEvent(obForm.dontsave, 'click', function() {window.BX_CANCEL = true; setTimeout('window.BX_CANCEL = false', 10);});
});

function checkSubBarCode()
{
	var obTrSubBarCode = BX('tr_SUB_CAT_BARCODE');
	var obTrSubBarCodeEdit = BX('tr_SUBCAT_BARCODE_EDIT');

	if(BX('SUBCAT_BARCODE_MULTIPLY').checked)
	{
		if (!!obTrSubBarCode)
		{
			obTrSubBarCode.disabled = true;
			obTrSubBarCode.style.display = 'none';
		}
		if (!!obTrSubBarCodeEdit)
		{
			obTrSubBarCodeEdit.disabled = true;
			obTrSubBarCodeEdit.style.display = 'none';
		}
	}
	else
	{
		if (!!obTrSubBarCode)
		{
			obTrSubBarCode.disabled = false;
			obTrSubBarCode.style.display = 'table-row';
		}
		if (!!obTrSubBarCodeEdit)
		{
			obTrSubBarCodeEdit.disabled = false;
			obTrSubBarCodeEdit.style.display = 'table-row';
		}
	}
}

function editSubBarCode()
{
	var obEditSubBarCode = BX('SUBCAT_BARCODE_EDIT_Y');
	var obSubBarCode = BX('SUBCAT_BARCODE');
	if (!bReadOnly && !!obEditSubBarCode && !!obSubBarCode)
	{
		if (obEditSubBarCode.checked)
		{
			if (confirm('<? echo GetMessageJS("CAT_BARCODE_EDIT_CONFIRM"); ?>'))
			{
				obSubBarCode.disabled = false;
			}
			else
			{
				obEditSubBarCode.checked = false;
				obSubBarCode.disabled = true;
			}
		}
		else
		{
			obSubBarCode.disabled = true;
		}
	}
}

function SetSubFieldsStyle(table_id)
{
	var tbl = BX(table_id);
	var n = tbl.rows.length;
	for(var i=0; i<n; i++)
		if(tbl.rows[i].cells[0].colSpan == 1)
			tbl.rows[i].cells[0].className = 'field-name';
}

function toggleSubPriceType()
{
	var obSubPriceSimple = BX('subprices_simple');
	var obSubPriceExt = BX('subprices_ext');
	var obSubBasePrice = BX('tr_SUB_BASE_PRICE');
	var obSubBaseCurrency = BX('tr_SUB_BASE_CURRENCY');

	if (obSubPriceSimple.style.display == 'block')
	{
		obSubPriceSimple.style.display = 'none';
		obSubPriceExt.style.display = 'block';
		if (!!obSubBasePrice)
			BX.style(obSubBasePrice, 'display', 'none');
		if (!!obSubBaseCurrency)
			BX.style(obSubBaseCurrency, 'display', 'none');
	}
	else
	{
		obSubPriceSimple.style.display = 'block';
		obSubPriceExt.style.display = 'none';
		if (!!obSubBasePrice)
			BX.style(obSubBasePrice, 'display', 'table-row');
		if (!!obSubBaseCurrency)
			BX.style(obSubBaseCurrency, 'display', 'table-row');
	}
}
</script>
	</td>
</tr>
<tr>
	<td valign="top" colspan="2">
		<?
		$aTabs1 = array();
		$aTabs1[] = array("DIV" => "subcat_edit1", "TAB" => GetMessage("C2IT_PRICES"), "TITLE" => GetMessage("C2IT_PRICES_D"));

		$aTabs1[] = array("DIV" => "subcat_edit3", "TAB" => GetMessage("C2IT_PARAMS"), "TITLE" => GetMessage("C2IT_PARAMS_D"));
		if($arCatalog["SUBSCRIPTION"] == "Y")
			$aTabs1[] = array("DIV" => "subcat_edit4", "TAB" => GetMessage("C2IT_GROUPS"), "TITLE" => GetMessage("C2IT_GROUPS_D"));
		$aTabs1[] = array("DIV" => "subcat_edit6", "TAB" => GetMessage("C2IT_DISCOUNTS"), "TITLE" => GetMessage("C2IT_DISCOUNTS_D"));
		$aTabs1[] = array("DIV" => "subcat_edit5", "TAB" => GetMessage("C2IT_STORE"), "TITLE" => GetMessage("C2IT_STORE_D"));
		if($bUseStoreControl)
		{
			$aTabs1[] = array("DIV" => "subcat_edit7", "TAB" => GetMessage("C2IT_BAR_CODE"), "TITLE" => GetMessage("C2IT_BAR_CODE_D"));
		}
		if($activitySubscribeTab)
		{
			$aTabs1[] = array(
				"DIV" => "subcat_edit8",
				"TAB" => GetMessage("C2IT_SUBSCRIBE_TAB"),
				"TITLE" => GetMessage("C2IT_SUBSCRIBE_TAB_TITLE"),
				"ONSELECT" => "getDataSubscriptions();"
			);
		}

		$subtabControl1 = new CAdminViewTabControl("subtabControl1", $aTabs1);
		$subtabControl1->Begin();

		// Define boundaries
		$usedRanges = false;
		$arProductFilter = array("PRODUCT_ID" => $PRODUCT_ID);
		if (!Catalog\Config\Feature::isMultiPriceTypesEnabled())
		{
			$arProductFilter['BASE'] = 'Y';
		}
		$arPriceBoundariesError = array();
		$arPriceBoundaries = array();
		$dbPrice = CPrice::GetList(
				array("BASE" => "DESC", "CATALOG_GROUP_ID" => "ASC", "QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
				$arProductFilter
			);
		while ($arPrice = $dbPrice->Fetch())
		{
			$arPrice['RAW_QUANTITY_FROM'] = $arPrice['QUANTITY_FROM'];
			$arPrice['RAW_QUANTITY_TO'] = $arPrice['QUANTITY_TO'];
			$arPrice['QUANTITY_FROM'] = (int)$arPrice['QUANTITY_FROM'];
			$arPrice['QUANTITY_TO'] = (int)$arPrice['QUANTITY_TO'];
			if ($arPrice['RAW_QUANTITY_FROM'] !== null && $arPrice['QUANTITY_FROM'] > 0)
				$usedRanges = true;
			if ($arPrice['RAW_QUANTITY_TO'] !== null && $arPrice['QUANTITY_TO'] > 0)
				$usedRanges = true;
			if ($arPrice["BASE"] == "Y")
			{
				$arPriceBoundaries[] = array(
						"FROM" => $arPrice["QUANTITY_FROM"],
						"TO" => $arPrice["QUANTITY_TO"]
					);
				if ($arPrice["QUANTITY_FROM"] > $arPrice["QUANTITY_TO"]
					&& $arPrice["QUANTITY_TO"] != 0)
				{
					$arPriceBoundariesError[] = str_replace("#RIGHT#", $arPrice["QUANTITY_TO"], str_replace("#LEFT#", $arPrice["QUANTITY_FROM"], GetMessage("C2IT_BOUND_LR")));
				}
			}
			else
			{
				if ($arPrice["QUANTITY_FROM"] > $arPrice["QUANTITY_TO"]
					&& $arPrice["QUANTITY_TO"] != 0)
				{
					$arPriceBoundariesError[] = str_replace("#TYPE#", $arPrice["CATALOG_GROUP_NAME"], str_replace("#RIGHT#", $arPrice["QUANTITY_TO"], str_replace("#LEFT#", $arPrice["QUANTITY_FROM"], GetMessage("C2IT_BOUND_LR1"))));
				}
				else
				{
					$bNewSegment = true;
					$intCount = count($arPriceBoundaries);
					for ($i = 0; $i < $intCount; $i++)
					{
						if ($arPriceBoundaries[$i]["FROM"] == $arPrice["QUANTITY_FROM"])
						{
							if ($arPriceBoundaries[$i]["TO"] != $arPrice["QUANTITY_TO"])
							{
								$arPriceBoundariesError[] = str_replace("#TYPE#", $arPrice["CATALOG_GROUP_NAME"], str_replace("#RIGHT#", $arPrice["QUANTITY_TO"], str_replace("#LEFT#", $arPrice["QUANTITY_FROM"], GetMessage("C2IT_BOUND_DIAP"))));
							}
							$bNewSegment = false;
							break;
						}
						else
						{
							if ($arPriceBoundaries[$i]["FROM"] < $arPrice["QUANTITY_FROM"]
								&& $arPriceBoundaries[$i]["TO"] >= $arPrice["QUANTITY_TO"]
								&& $arPrice["QUANTITY_TO"] != 0)
							{
								$arPriceBoundariesError[] = str_replace("#TYPE#", $arPrice["CATALOG_GROUP_NAME"], str_replace("#RIGHT#", $arPrice["QUANTITY_TO"], str_replace("#LEFT#", $arPrice["QUANTITY_FROM"], GetMessage("C2IT_BOUND_DIAP"))));
								$bNewSegment = false;
								break;
							}
						}
					}
					if ($bNewSegment)
					{
						$arPriceBoundaries[] = array("FROM" => $arPrice["QUANTITY_FROM"], "TO" => $arPrice["QUANTITY_TO"]);
					}
				}
			}
		}

		if (!empty($arPriceBoundaries))
		{
			if (count($arPriceBoundaries) > 1)
			{
				Main\Type\Collection::sortByColumn($arPriceBoundaries, array('FROM' => SORT_ASC));
			}
			elseif (!$usedRanges)
			{
				$arPriceBoundaries[0]['FROM'] = false;
				$arPriceBoundaries[0]['TO'] = false;
			}
		}

// prices tab
		$subtabControl1->BeginNextTab();
$arCatPricesExist = array(); // attr for exist prices for range
if ($enableQuantityRanges)
	$bUseExtendedPrice = $bVarsFromForm ? $subprice_useextform == 'Y' : $usedRanges;
else
	$bUseExtendedPrice = false;
$str_CAT_VAT_ID = $bVarsFromForm ? $SUBCAT_VAT_ID : ($arBaseProduct['VAT_ID'] == 0 ? $arCatalog['VAT_ID'] : $arBaseProduct['VAT_ID']);
$str_CAT_VAT_INCLUDED = (string)($bVarsFromForm ? $SUBCAT_VAT_INCLUDED : $arBaseProduct['VAT_INCLUDED']);
if ($str_CAT_VAT_INCLUDED != 'Y' && $str_CAT_VAT_INCLUDED != 'N')
	$str_CAT_VAT_INCLUDED = ((string)Main\Config\Option::get('catalog', 'default_product_vat_included') == 'Y' ? 'Y' : 'N');
		?>
<input type="hidden" name="subprice_useextform" id="subprice_useextform_N" value="N" />
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="subcatalog_vat_table">
<?
if ($enableQuantityRanges)
{
	?>
	<tr>
		<td width="40%"><label for="subprice_useextform"><? echo GetMessage('C2IT_PRICES_USEEXT'); ?>:</label></td>
		<td width="60%">
			<input type="checkbox" name="subprice_useextform" id="subprice_useextform" value="Y" onclick="toggleSubPriceType()" <?= $bUseExtendedPrice ? 'checked="checked"' : '' ?> <? echo($bReadOnly ? ' disabled readonly' : ''); ?> />
		</td>
	</tr>
	<?
}
else
{
	?><input type="hidden" name="subprice_useextform" value="N"><?
}
?>
	<tr>
		<td width="40%">
			<?echo GetMessage("CAT_VAT")?>:
		</td>
		<td width="60%">
<?
	$arVATRef = CatalogGetVATArray(array(), true);
	echo SelectBoxFromArray('SUBCAT_VAT_ID', $arVATRef, $str_CAT_VAT_ID, "", $bReadOnly ? "disabled readonly" : '');
?>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="SUBCAT_VAT_INCLUDED"><? echo GetMessage("CAT_VAT_INCLUDED");?></label>:</td>
		<td width="60%">
			<input type="hidden" name="SUBCAT_VAT_INCLUDED" id="SUBCAT_VAT_INCLUDED_N" value="N">
			<input type="checkbox" name="SUBCAT_VAT_INCLUDED" id="SUBCAT_VAT_INCLUDED" value="Y" <?=$str_CAT_VAT_INCLUDED == 'Y' ? 'checked="checked"' : ''?> <?=$bReadOnly ? 'disabled readonly' : ''?> />
		</td>
	</tr>

	<?if($USER->CanDoOperation('catalog_purchas_info')):?>
		<tr id="tr_SUB_PURCHASING_PRICE">
			<?
			$str_CAT_PURCHASING_PRICE = $bVarsFromForm ? $SUBCAT_PURCHASING_PRICE : $arBaseProduct['PURCHASING_PRICE'];
			?>
			<td width="40%"><?echo GetMessage("C2IT_COST_PRICE")?>:</td>
			<td width="60%">
				<input type="hidden" id="SUBCAT_PURCHASING_PRICE_hidden" name="SUBCAT_PURCHASING_PRICE" value="<?echo htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>">
				<input type="text" <?if ($bReadOnly || $bUseStoreControl) echo "disabled readonly" ?> id="SUBCAT_PURCHASING_PRICE" name="SUBCAT_PURCHASING_PRICE" value="<?echo htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>" size="30">
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("C2IT_COST_CURRENCY") ?>:</td>
			<td>
				<input type="hidden" id="SUBCAT_PURCHASING_CURRENCY_hidden" name="SUBCAT_PURCHASING_CURRENCY" value="<?echo htmlspecialcharsbx($arBaseProduct['PURCHASING_CURRENCY']) ?>">

				<? $isDisabled = ''; if($bUseStoreControl) $isDisabled = " disabled"; echo CCurrency::SelectBox("SUBCAT_PURCHASING_CURRENCY", $arBaseProduct['PURCHASING_CURRENCY'], "", true, "", "id='SUBCAT_PURCHASING_CURRENCY' $isDisabled");?></td>
		</tr>
	<?endif;?>

	<tr id="tr_SUB_BASE_PRICE" style="display: <? echo ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
		<td width="40%">
			<?
			$arBaseGroup = CCatalogGroup::GetBaseGroup();
			$arBasePrice = CPrice::GetBasePrice($PRODUCT_ID, $arPriceBoundaries[0]["FROM"], $arPriceBoundaries[0]["TO"]);
			echo GetMessage("BASE_PRICE")?> (<? echo GetMessage('C2IT_PRICE_TYPE'); ?> "<? echo htmlspecialcharsbx(!empty($arBaseGroup['NAME_LANG']) ? $arBaseGroup['NAME_LANG'] : $arBaseGroup["NAME"]); ?>"):
		</td>
		<td width="60%">
			<script type="text/javascript">
				var arExtra = new Array();
				var arExtraPrc = new Array();
				<?
				$db_extras = CExtra::GetList(($by3="NAME"), ($order3="ASC"));
				$i = 0;
				while ($extras = $db_extras->Fetch())
				{
					echo "arExtra[".$i."]=".$extras["ID"].";";
					echo "arExtraPrc[".$i."]=".$extras["PERCENTAGE"].";";
					$i++;
				}
				?>

				function OnChangeSubExtra(priceType)
				{
					if (bReadOnly)
						return;

					var e_base_price = BX('SUBCAT_BASE_PRICE');
					var e_extra = BX('SUBCAT_EXTRA_' + priceType);
					var e_price = BX('SUBCAT_PRICE_' + priceType);
					var e_currency = BX('SUBCAT_CURRENCY_' + priceType);

					if (isNaN(e_base_price.value) || e_base_price.value <= 0)
					{
						e_currency.disabled = false;
						e_price.disabled = false;
						return;
					}

					var i, esum, eps;
					if (parseInt(e_extra.selectedIndex)==0)
					{
						e_currency.disabled = false;
						e_price.disabled = false;
					}
					else
					{
						e_currency.selectedIndex = 0;
						e_currency.disabled = true;
						e_price.disabled = true;
						for (i = 0; i < arExtra.length; i++)
						{
							if (parseInt(e_extra.options[e_extra.selectedIndex].value) == parseInt(arExtra[i]))
							{
								esum = parseFloat(e_base_price.value) * (1 + arExtraPrc[i] / 100);
								eps = 1.00/Math.pow(10, 6);
								e_price.value = Math.round((esum+eps)*100)/100;
								break;
							}
						}
					}
				}


				function OnChangeSubExtraEx(e)
				{
					if (bReadOnly)
						return;

					var thename = e.name;

					var pos = thename.lastIndexOf("_");
					var ind = thename.substr(pos + 1);
					thename = thename.substr(0, pos);
					pos = thename.lastIndexOf("_");
					var ptype = thename.substr(pos + 1);

					var e_ext = BX('SUBCAT_EXTRA_'+ptype+"_"+ind);
					var e_price = BX('SUBCAT_PRICE_'+ptype+"_"+ind);
					var e_currency = BX('SUBCAT_CURRENCY_'+ptype+"_"+ind);

					var e_base_price = BX('SUBCAT_BASE_PRICE_'+ind);

					if (isNaN(e_base_price.value) || e_base_price.value <= 0)
					{
						e_price.disabled = false;
						e_currency.disabled = false;
						return;
					}

					var i, esum;
					if (parseInt(e_ext.selectedIndex)==0)
					{
						e_price.disabled = false;
						e_currency.disabled = false;
					}
					else
					{
						e_currency.selectedIndex = 0;
						e_currency.disabled = true;
						e_price.disabled = true;
						for (i = 0; i < arExtra.length; i++)
						{
							if (parseInt(e_ext.options[e_ext.selectedIndex].value) == parseInt(arExtra[i]))
							{
								esum = parseFloat(e_base_price.value) * (1 + arExtraPrc[i] / 100);
								eps = 1.00/Math.pow(10, 6);
								e_price.value = Math.round((esum+eps)*100)/100;
								break;
							}
						}
					}
				}

				function ChangeSubExtra(codID)
				{
					if (bReadOnly)
						return;

					OnChangeSubExtra(codID);

					var e_extra = BX('SUBCAT_EXTRA_' + codID + '_0');
					if (e_extra)
					{
						var e_extra_s = BX('SUBCAT_EXTRA_' + codID);
						e_extra.selectedIndex = e_extra_s.selectedIndex;
						OnChangeSubExtraEx(e_extra);
					}
				}

				function OnChangeSubBasePrice()
				{
					if (bReadOnly)
						return;

					var e_base_price = BX('SUBCAT_BASE_PRICE');

					if (isNaN(e_base_price.value) || e_base_price.value <= 0)
					{
						var k;
						for (k = 0; k < arCatalogGroups.length; k++)
						{
							e_price = BX('SUBCAT_PRICE_' + arCatalogGroups[k]);
							e_price.disabled = false;
							e_currency = BX('SUBCAT_CURRENCY_' + arCatalogGroups[k]);
							e_currency.disabled = false;
						}
						OnChangeSubPriceExist();
						return;
					}

					var i, j, esum, eps;
					var e_price;
					for (i = 0; i < arCatalogGroups.length; i++)
					{
						e_extra = BX('SUBCAT_EXTRA_' + arCatalogGroups[i]);
						if (e_extra.selectedIndex > 0)
						{
							e_price = BX('SUBCAT_PRICE_' + arCatalogGroups[i]);
							e_currency = BX('SUBCAT_CURRENCY_' + arCatalogGroups[i]);

							for (j = 0; j < arExtra.length; j++)
							{
								if (parseInt(e_extra.options[e_extra.selectedIndex].value) == parseInt(arExtra[j]))
								{
									esum = parseFloat(e_base_price.value) * (1 + arExtraPrc[j] / 100);
									eps = 1.00/Math.pow(10, 6);
									e_price.value = Math.round((esum+eps)*100)/100;
									e_currency.selectedIndex = 0;
									e_currency.disabled = true;
									e_price.disabled = true;
									break;
								}
							}
						}
					}
					OnChangeSubPriceExist();
				}

				function ChangeSubBasePrice(e)
				{
					if (bReadOnly)
						return;

					if (e.value != '' && (isNaN(e.value) || e.value <= 0))
					{
					}
					else
					{
						e.className = '';
					}

					OnChangeSubBasePrice();

					var e_base_price = BX('SUBCAT_BASE_PRICE_0');
					e_base_price.value = BX('SUBCAT_BASE_PRICE').value;
					OnChangeSubBasePriceEx(e_base_price);
					OnChangeSubPriceExistEx(e_base_price);
				}

				function ChangeSubBaseCurrency()
				{
					if (bReadOnly)
						return;

					BX('SUBCAT_BASE_CURRENCY_0').selectedIndex = BX('SUBCAT_BASE_CURRENCY').selectedIndex;
				}

				function ChangeSubPrice(codID)
				{
					if (bReadOnly)
						return;

					var e_price = BX('SUBCAT_PRICE_' + codID + '_0');
					e_price.value = BX('SUBCAT_PRICE_' + codID).value;
					OnChangeSubPriceExist();
					OnChangeSubPriceExistEx(e_price);
				}

				function ChangeSubCurrency(codID)
				{
					if (bReadOnly)
						return;

					var e_currency = BX('SUBCAT_CURRENCY_' + codID + "_0");
					e_currency.selectedIndex = BX('SUBCAT_CURRENCY_' + codID).selectedIndex;
				}

				function OnChangeSubPriceExist()
				{
					if (bReadOnly)
						return;

					var bExist = 'N';
					var e_price_exist = BX('SUBCAT_PRICE_EXIST');
					var e_ext_price_exist = BX('SUBCAT_PRICE_EXIST_0');
					var e_base_price = BX('SUBCAT_BASE_PRICE');

					if (isNaN(e_base_price.value) || e_base_price.value <= 0)
					{
						var i;
						var e_price;
						for (i = 0; i < arCatalogGroups.length; i++)
						{
							e_price = BX('SUBCAT_PRICE_' + arCatalogGroups[i]);
							if (!(isNaN(e_price.value) || e_price.value <= 0))
							{
								bExist = 'Y';
								break;
							}
						}
					}
					else
					{
						bExist = 'Y';
					}
					e_price_exist.value = bExist;
					e_ext_price_exist.value = bExist;
				}
			</script>
			<?
			$boolBaseExistPrice = false;
			$str_CAT_BASE_PRICE = "";
			if ($arBasePrice)
				$str_CAT_BASE_PRICE = $arBasePrice["PRICE"];
			if ($bVarsFromForm)
				$str_CAT_BASE_PRICE = $SUBCAT_BASE_PRICE;
			if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
				$boolBaseExistPrice = true;
			?>
			<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_PRICE" name="SUBCAT_BASE_PRICE" value="<?echo htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="30" OnBlur="ChangeSubBasePrice(this)">
		</td>
	</tr>
	<tr id="tr_SUB_BASE_CURRENCY" style="display: <? echo ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
		<td width="40%">
			<?echo GetMessage("BASE_CURRENCY")?>:
		</td>
		<td width="60%">
			<?
			if ($arBasePrice)
				$str_CAT_BASE_CURRENCY = $arBasePrice["CURRENCY"];
			if ($bVarsFromForm)
				$str_CAT_BASE_CURRENCY = $SUBCAT_BASE_CURRENCY;

			?>
			<select id="SUBCAT_BASE_CURRENCY" name="SUBCAT_BASE_CURRENCY" <?if ($bReadOnly) echo "disabled readonly" ?> OnChange="ChangeSubBaseCurrency()">
				<?
				foreach ($currencyList as &$currency)
				{
					?><option value="<? echo $currency["CURRENCY"]; ?>"<? if ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY) echo " selected"?>><? echo $currency["FULL_NAME"]; ?></option><?
				}
				unset($currency);
				?>
			</select>
		</td>
	</tr>
</table>
<script type="text/javascript">
SetSubFieldsStyle('subcatalog_vat_table');
</script>
<?
// simple price form
?>
<div id="subprices_simple" style="display: <?=$bUseExtendedPrice ? 'none' : 'block'?>;">
		<?
		$intCount = count($arPriceBoundariesError);
		if ($intCount > 0)
		{
			?>
			<font class="errortext">
			<?echo GetMessage("C2IT_BOUND_WRONG")?><br>
			<?
			for ($i = 0; $i < $intCount; $i++)
			{
				echo $arPriceBoundariesError[$i]."<br>";
			}
			?>
			<?echo GetMessage("C2IT_BOUND_RECOUNT")?>
			</font>
			<?
		}

	if (Catalog\Config\Feature::isMultiPriceTypesEnabled())
	{
		$bFirst = true;
		$dbCatalogGroups = CCatalogGroup::GetList(
				array("SORT" => "ASC","NAME" => "ASC","ID" => "ASC"),
				array("!BASE" => "Y")
			);

		while ($arCatalogGroup = $dbCatalogGroups->Fetch())
		{
			if($bFirst)
			{
				?>
			<br>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
				<tr class="heading">
					<td><? echo GetMessage("PRICE_TYPE"); ?></td>
					<td><? echo GetMessage("PRICE_EXTRA"); ?></td>
					<td><? echo GetMessage("PRICE_SUM"); ?></td>
					<td><? echo GetMessage("PRICE_CURRENCY"); ?></td>
				</tr>
				<?
				$bFirst = false;
			}
			$str_CAT_EXTRA = 0;
			$str_CAT_PRICE = "";
			$str_CAT_CURRENCY = "";

			$dbPriceList = CPrice::GetList(
				array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
				array(
					"PRODUCT_ID" => $PRODUCT_ID,
					"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
					"QUANTITY_FROM" => $arPriceBoundaries[0]["FROM"],
					"QUANTITY_TO" => $arPriceBoundaries[0]["TO"]
				)
			);
			if ($arPrice = $dbPriceList->Fetch())
			{
				$str_CAT_EXTRA = $arPrice["EXTRA_ID"];
				$str_CAT_PRICE = $arPrice["PRICE"];
				$str_CAT_CURRENCY = $arPrice["CURRENCY"];
			}
			if ($bVarsFromForm)
			{
				$str_CAT_EXTRA = ${"SUBCAT_EXTRA_".$arCatalogGroup["ID"]};
				$str_CAT_PRICE = ${"SUBCAT_PRICE_".$arCatalogGroup["ID"]};
				$str_CAT_CURRENCY = ${"SUBCAT_CURRENCY_".$arCatalogGroup["ID"]};
			}
			if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
				$boolBaseExistPrice = true;
			?>
			<tr <?if ($bReadOnly) echo "disabled" ?>>
				<td valign="top" align="left">
					<? echo htmlspecialcharsbx(!empty($arCatalogGroup["NAME_LANG"]) ? $arCatalogGroup["NAME_LANG"] : $arCatalogGroup["NAME"]); ?>
					<?if ($arPrice):?>
					<input type="hidden" name="SUBCAT_ID_<?echo $arCatalogGroup["ID"] ?>" value="<?echo $arPrice["ID"] ?>">
					<?endif;?>
				</td>
				<td valign="top" align="center">
					<?
					echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"], $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtra(".$arCatalogGroup["ID"].")", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"].'" ');
					?>
				</td>
				<td valign="top" align="center">
					<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_PRICE_<?echo $arCatalogGroup["ID"] ?>" name="SUBCAT_PRICE_<?echo $arCatalogGroup["ID"] ?>" value="<?echo htmlspecialcharsbx($str_CAT_PRICE) ?>" size="8" OnChange="ChangeSubPrice(<?= $arCatalogGroup["ID"] ?>)">
				</td>
				<td valign="top" align="center">
					<?
					echo CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"], $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrency(".$arCatalogGroup["ID"].")", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"].'" ')
					?>
					<script type="text/javascript">
						ChangeSubExtra(<?echo $arCatalogGroup["ID"] ?>);
					</script>
				</td>
			</tr>
			<?
		}// endwhile
		if (!$bFirst) echo "</table>";
	}
		?><input type="hidden" name="SUBCAT_PRICE_EXIST" id="SUBCAT_PRICE_EXIST" value="<? echo ($boolBaseExistPrice == true ? 'Y' : 'N'); ?>">
</div>
		<?
		//$subtabControl1->BeginNextTab();
// extended price form
		?>
<div id="subprices_ext" style="display: <?=$bUseExtendedPrice ? 'block' : 'none'?>;">
<script type="text/javascript">
function addSubNewElementsGroup(parentId, modelId, counterId, keepValues, typefocus)
{
	if (bReadOnly)
		return;

	if (!BX(counterId))
		return false;
	var n = ++BX(counterId).value;
	var thebody = BX(parentId);
	if (!thebody)
		return false;
	var therow = BX(modelId);
	if (!therow)
		return false;
	var thecopy = duplicateSubElement(therow, n, keepValues);
	thebody.appendChild(thecopy);

	return true;
}

function duplicateSubElement(e, n, keepVal)
{
	if (bReadOnly)
		return;

	if (typeof e.tagName != "undefined")
	{
		var copia = document.createElement(e.tagName);

		var attr = e.attributes;
		if (attr)
		{
			for (i=0; i<attr.length; i++)
			{
				copia.setAttribute(attr[i].name, attr[i].value);
			}
		}

		if (e.id) copia.id = e.id + n;
		if (e.text) copia.text = e.text;

		if (e.tagName.toLowerCase() == "textarea" && !keepVal)
		{
			copia.text = "";
		}
		if (e.name)
		{
			var thename = e.name;

			if (thename.substr(thename.length-1)!="]")
			{
				var ind = thename.lastIndexOf("_");
				if (ind > -1)
				{
					var thename_postf = thename.substr(ind + 1);
					if (!isNaN(parseFloat(thename_postf)))
					{
						thename = thename.substring(0, ind);
					}
				}
				thename = thename + "_" + n;
			}
			else
			{
				var ind = thename.indexOf("[");
				if (ind > -1)
				{
					thename = thename.substring(0, ind);
					thename = thename + "[" + n + "]";
				}
			}

			copia.name = thename;
		}

		copia.value = ((keepVal == true) ?  e.value : ((e.tagName.toLowerCase() == "option" || e.type == "button") ? e.value : null));

		var hijos = e.childNodes;
		if (hijos)
		{
			for (key in hijos)
			{
				if (typeof hijos[key] != "undefined")
				{
					hijocopia = duplicateSubElement(hijos[key], n, keepVal);
					if (hijocopia) copia.appendChild(hijocopia);
				}
			}
		}
		return copia;
	}
	return null;
}

function CloneSubBasePriceGroup()
{
	if (bReadOnly)
		return;

	var oTbl = BX("SUBBASE_PRICE_GROUP_TABLE");
	if (!oTbl)
		return;

	var oCntr = BX("SUBCAT_ROW_COUNTER");
	var cnt = parseInt(oCntr.value);
	cnt = cnt + 1;

	var oRow = oTbl.insertRow(-1);
	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_FROM_'+cnt+'" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_TO_'+cnt+'" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_PRICE_'+cnt+'" name="SUBCAT_BASE_PRICE_'+cnt+'" value="" size="15" OnBlur="ChangeSubBasePriceEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="SUBCAT_BASE_CURRENCY_'+cnt+'" name="SUBCAT_BASE_CURRENCY_'+cnt+'" <?if ($bReadOnly) echo "disabled readonly" ?> OnChange="ChangeSubBaseCurrencyEx(this)">';
	<?
	foreach ($currencyList as &$currency)
	{
		?>str += '<option value="<?echo $currency["CURRENCY"] ?>"><?echo $currency["FULL_NAME_JS"]; ?></option>';<?
	}
	unset($currency);
	?>
	str += '</select>';
	oCell.innerHTML = str;

	var div_ext_price_exist = BX('ext_subprice_exist');
	var new_price_exist = BX.create('input',
									{'attrs': {
										'type': 'hidden',
										'name': 'SUBCAT_PRICE_EXIST_'+cnt,
										'value': 'N'
										}
									});
	new_price_exist.id = 'SUBCAT_PRICE_EXIST_'+cnt,
	div_ext_price_exist.appendChild(new_price_exist);
	oCntr.value = cnt;
}

function CloneSubOtherPriceGroup(ind)
{
	if (bReadOnly)
		return;

	var oTbl = BX("SUBOTHER_PRICE_GROUP_TABLE_"+ind);
	if (!oTbl)
		return;

	var oCntr = BX("SUBCAT_ROW_COUNTER_"+ind);
	var cnt = parseInt(oCntr.value);
	cnt = cnt + 1;

	var oRow = oTbl.insertRow(-1);
	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_'+ind+'_'+cnt+'" name="SUBCAT_QUANTITY_FROM_'+ind+'_'+cnt+'" value="" size="3">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_'+ind+'_'+cnt+'" name="SUBCAT_QUANTITY_TO_'+ind+'_'+cnt+'" value="" size="3">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";

	var str = '';
	oCell.valign = "top";
	oCell.align = "center";
	//oCell.className = "tablebody";
	str += '<select id="SUBCAT_EXTRA_'+ind+'_'+cnt+'" name="SUBCAT_EXTRA_'+ind+'_'+cnt+'" OnChange="ChangeSubExtraEx(this)" <?if ($bReadOnly) echo "disabled readonly" ?>>';
	str += '<option value=""><?= GetMessage("VAL_NOT_SET") ?></option>';
	<?
	foreach ($arExtraList as $arOneExtra)
	{
		?>
		str += '<option value="<?= $arOneExtra["ID"] ?>"><?= CUtil::JSEscape(htmlspecialcharsbx($arOneExtra["NAME"]))." (".htmlspecialcharsbx($arOneExtra["PERCENTAGE"])."%)" ?></option>';
		<?
	}
	?>
	str += '</select>';
	oCell.innerHTML = str;

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_PRICE_'+ind+'_'+cnt+'" name="SUBCAT_PRICE_'+ind+'_'+cnt+'" value="" size="10" OnChange="ptSubPriceChangeEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="SUBCAT_CURRENCY_'+ind+'_'+cnt+'" name="SUBCAT_CURRENCY_'+ind+'_'+cnt+'" OnChange="ChangeSubCurrencyEx(this)" <?if ($bReadOnly) echo "disabled readonly" ?>>';
	str += '<option value=""><?= GetMessage("VAL_BASE") ?></option>';
	<?
	foreach ($currencyList as &$currency)
	{
		?>str += '<option value="<?echo $currency["CURRENCY"] ?>"><?echo $currency["FULL_NAME_JS"]; ?></option>';<?
	}
	unset($currency);
	?>
	str += '</select>';
	oCell.innerHTML = str;

	oCntr.value = cnt;
}

function CloneSubPriceSections()
{
	if (bReadOnly)
		return;

	CloneSubBasePriceGroup();

	var i, n;
	for (i = 0; i < arCatalogGroups.length; i++)
	{
		CloneSubOtherPriceGroup(arCatalogGroups[i]);

		n = BX('SUBCAT_ROW_COUNTER_'+arCatalogGroups[i]).value;
		ChangeSubExtraEx(BX('SUBCAT_EXTRA_'+arCatalogGroups[i]+"_"+n));
	}
}

function ChangeSubBaseQuantityEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	var type;
	if (thename.substring(0, "SUBCAT_BASE_QUANTITY_FROM_".length) == "SUBCAT_BASE_QUANTITY_FROM_")
	{
		type = "FROM";
	}
	else
	{
		type = "TO";
	}

	var i;
	var quantity;

	for (i = 0; i < arCatalogGroups.length; i++)
	{
		quantity = BX('SUBCAT_QUANTITY_'+type+"_"+arCatalogGroups[i]+"_"+ind);
		quantity.value = e.value;
	}
}

function OnChangeSubBasePriceEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (isNaN(e.value) || e.value <= 0)
	{
		for (i = 0; i < arCatalogGroups.length; i++)
		{
			e_price = document.getElementById('SUBCAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
			e_price.disabled = false;
			e_cur = document.getElementById('SUBCAT_CURRENCY_'+arCatalogGroups[i]+"_"+ind);
			e_cur.disabled = false;
		}
		OnChangeSubPriceExistEx(e);
		return;
	}

	var i;
	var e_price, e_ext;

	for (i = 0; i < arCatalogGroups.length; i++)
	{
		e_price = BX('SUBCAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
		e_cur = BX('SUBCAT_CURRENCY_'+arCatalogGroups[i]+"_"+ind);
		e_ext = BX('SUBCAT_EXTRA_'+arCatalogGroups[i]+"_"+ind);

		if (!e_ext)
			continue;

		for (j = 0; j < arExtra.length; j++)
		{
			if (parseInt(e_ext.options[e_ext.selectedIndex].value) == parseInt(arExtra[j]))
			{
				esum = parseFloat(e.value) * (1 + arExtraPrc[j] / 100);
				eps = 1.00/Math.pow(10, 6);
				e_price.value = Math.round((esum+eps)*100)/100;
				e_price.disabled = true;
				e_cur.selectedIndex = 0;
				e_cur.disabled = true;
				break;
			}
		}
	}
	OnChangeSubPriceExistEx(e);
}

function ChangeSubBasePriceEx(e)
{
	if (bReadOnly)
		return;

	if (isNaN(e.value) || e.value <= 0)
	{
	}
	else
	{
		e.className = '';
	}

	OnChangeSubBasePriceEx(e);

	var thename = e.name;
	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		BX('SUBCAT_BASE_PRICE').value = e.value;
		OnChangeSubBasePrice();
		OnChangeSubPriceExist();
	}
}

function ChangeSubExtraEx(e)
{
	if (bReadOnly)
		return;

	if (null == e)
		return;

	OnChangeSubExtraEx(e);
	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);
	thename = thename.substr(0, pos);
	pos = thename.lastIndexOf("_");
	var ptype = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		BX('SUBCAT_EXTRA_'+ptype).selectedIndex = e.selectedIndex;
		OnChangeSubExtra(ptype);
	}
}

function ChangeSubBaseCurrencyEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		BX('SUBCAT_BASE_CURRENCY').selectedIndex = e.selectedIndex;
	}
}

function ptSubPriceChangeEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		thename = thename.substr(0, pos);
		pos = thename.lastIndexOf("_");
		var ptype = thename.substr(pos + 1);

		BX('SUBCAT_PRICE_'+ptype).value = e.value;
		OnChangeSubPriceExist();
	}
	OnChangeSubPriceExistEx(e);
}

function ChangeSubCurrencyEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		thename = thename.substr(0, pos);
		pos = thename.lastIndexOf("_");
		var ptype = thename.substr(pos + 1);

		BX('SUBCAT_CURRENCY_'+ptype).selectedIndex = e.selectedIndex;
	}
}

function OnChangeSubPriceExistEx(e)
{
	if (bReadOnly)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (!(isNaN(ind) || parseInt(ind) < 0))
	{
		var price_ext = BX('SUBCAT_PRICE_EXIST_'+ind);
		if (!price_ext)
			return;

		var i;
		var e_price;
		bExist = 'N';
		e_price = BX('SUBCAT_BASE_PRICE_'+ind);
		if (!e_price)
			return;

		if (isNaN(e_price.value) || e_price.value <= 0)
		{
			for (i = 0; i < arCatalogGroups.length; i++)
			{
				e_price = document.getElementById('SUBCAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
				if (!(isNaN(e_price.value) || e_price.value <= 0))
				{
					bExist = 'Y';
					break;
				}
			}
		}
		else
		{
			bExist = 'Y';
		}
		price_ext.value = bExist;
	}
}

function ShowNotice()
{
	BX('CAT_QUANTITY_RESERVED_DIV').style.display = 'inline-block';
}
function HideNotice()
{
	BX('CAT_QUANTITY_RESERVED_DIV').style.display = 'none';
}
</script>
		<?
		$intCount = count($arPriceBoundariesError);
		if ($intCount > 0)
		{
			?>
			<font class="errortext">
			<?echo GetMessage("C2IT_BOUND_WRONG")?><br>
			<?
			for ($i = 0; $i < $intCount; $i++)
			{
				echo $arPriceBoundariesError[$i]."<br>";
			}
			?>
			<?echo GetMessage("C2IT_BOUND_RECOUNT")?>
			</font>
			<?
		}
		$boolExistPrice = false;
		?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
			<tr>
				<td valign="top" align="right">
				<?
					echo GetMessage("BASE_PRICE")?> (<? echo GetMessage('C2IT_PRICE_TYPE'); ?> "<? echo htmlspecialcharsbx(!empty($arBaseGroup['NAME_LANG']) ? $arBaseGroup['NAME_LANG'] : $arBaseGroup["NAME"]); ?>"):
				</td>
				<td valign="top" align="left">
					<table border="0" cellspacing="1" cellpadding="3" id="SUBBASE_PRICE_GROUP_TABLE">
						<thead>
						<tr>
							<td align="center"><?echo GetMessage("C2IT_FROM")?></td>
							<td align="center"><?echo GetMessage("C2IT_TO")?></td>
							<td align="center"><?echo GetMessage("C2IT_PRICE")?></td>
							<td align="center"><?echo GetMessage("C2IT_CURRENCY")?></td>
						</tr>
						</thead>
						<tbody id="subcontainer3">
							<?
							$ind = -1;
							$dbBasePrice = CPrice::GetList(
									array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
									array("BASE" => "Y", "PRODUCT_ID" => $PRODUCT_ID)
								);
							$arBasePrice = $dbBasePrice->Fetch();

							$intCount = count($arPriceBoundaries);
							for ($i = 0; $i < $intCount; $i++)
							{
								$boolExistPrice = false;
								$ind++;
								$str_CAT_BASE_QUANTITY_FROM = $arPriceBoundaries[$i]["FROM"];
								$str_CAT_BASE_QUANTITY_TO = $arPriceBoundaries[$i]["TO"];

								if ($arBasePrice
									&& intval($arBasePrice["QUANTITY_FROM"]) == $arPriceBoundaries[$i]["FROM"])
								{
									$str_CAT_BASE_ID = $arBasePrice["ID"];
									$str_CAT_BASE_PRICE = $arBasePrice["PRICE"];
									$str_CAT_BASE_CURRENCY = $arBasePrice["CURRENCY"];

									$arBasePrice = $dbBasePrice->Fetch();
								}
								else
								{
									$str_CAT_BASE_ID = 0;
									$str_CAT_BASE_PRICE = "";
									$str_CAT_BASE_CURRENCY = "";
								}

								if ($bVarsFromForm)
								{
									$str_CAT_BASE_QUANTITY_FROM = ${"SUBCAT_BASE_QUANTITY_FROM_".$ind};
									$str_CAT_BASE_QUANTITY_TO = ${"SUBCAT_BASE_QUANTITY_TO_".$ind};
									$str_CAT_BASE_PRICE = ${"SUBCAT_BASE_PRICE_".$ind};
									$str_CAT_BASE_CURRENCY = ${"SUBCAT_BASE_CURRENCY_".$ind};
								}
								if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
									$boolExistPrice = true;
								$arCatPricesExist[$ind][$arBaseGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
								?>
								<tr id="submodel3">
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?echo ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
										<input type="hidden" name="SUBCAT_BASE_ID[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_BASE_ID) ?>">
									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?echo ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="<?echo htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeSubBasePriceEx(this)">
									</td>
									<td valign="top" align="center">
										<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?if ($bReadOnly) echo "disabled readonly" ?> OnChange="ChangeSubBaseCurrencyEx(this)">
											<?
											foreach ($currencyList as &$currency)
											{
												?><option value="<? echo $currency["CURRENCY"]; ?>"<? if ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY) echo " selected"?>><? echo $currency["FULL_NAME"];?></option><?
											}
											unset($currency);
											?>
										</select>
									</td>
								</tr>
								<?
							}

							if ($bVarsFromForm && $ind < intval($SUBCAT_ROW_COUNTER))
							{
								for ($i = $ind + 1; $i <= intval($SUBCAT_ROW_COUNTER); $i++)
								{
									$boolExistPrice = false;
									$ind++;
									$str_CAT_BASE_QUANTITY_FROM = ${"SUBCAT_BASE_QUANTITY_FROM_".$ind};
									$str_CAT_BASE_QUANTITY_TO = ${"SUBCAT_BASE_QUANTITY_TO_".$ind};
									$str_CAT_BASE_PRICE = ${"SUBCAT_BASE_PRICE_".$ind};
									$str_CAT_BASE_CURRENCY = ${"SUBCAT_BASE_CURRENCY_".$ind};
									if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
										$boolExistPrice = true;
									$arCatPricesExist[$ind][$arBaseGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
									?>
									<tr id="submodel3">
										<td valign="top" align="center">
											<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?echo ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
											<input type="hidden" name="SUBCAT_BASE_ID[<?= $ind ?>]" value="<?= 0 ?>">
										</td>
										<td valign="top" align="center">
											<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?echo ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
										</td>
										<td valign="top" align="center">
											<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="<?echo htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeSubBasePriceEx(this)">
										</td>
										<td valign="top" align="center">
											<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?if ($bReadOnly) echo "disabled readonly" ?> OnChange="ChangeSubBaseCurrencyEx(this)">
												<?
												foreach ($currencyList as &$currency)
												{
													?><option value="<? echo $currency["CURRENCY"]; ?>"<? if ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY) echo " selected"?>><? echo $currency["FULL_NAME"];?></option><?
												}
												unset($currency);
												?>
											</select>
										</td>
									</tr>
									<?
								}
							}
							if ($ind == -1)
							{
								$ind++;
								?>
								<tr id="submodel3">
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="" size="15" OnBlur="ChangeSubBasePriceEx(this)">
									</td>
									<td valign="top" align="center">
										<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?if ($bReadOnly) echo "disabled readonly" ?> OnChange="ChangeSubBaseCurrencyEx(this)">
											<?
											foreach ($currencyList as &$currency)
											{
												?><option value="<? echo $currency["CURRENCY"]; ?>"><? echo $currency["FULL_NAME"];?></option><?
											}
											unset($currency);
											?>
										</select>
									</td>
								</tr>
								<?
								$arCatPricesExist[$ind][$arBaseGroup['ID']] = 'N';
							}
							?>
						</tbody>
					</table>
					<input type="hidden" name="SUBCAT_ROW_COUNTER" id="SUBCAT_ROW_COUNTER" value="<?= $ind ?>">
					<input type="button" value="<?echo GetMessage("C2IT_MORE")?>" OnClick="CloneSubPriceSections()">
				</td>
			</tr>
			<script type="text/javascript">
			arCatalogGroups = new Array();
			catalogGroupsInd = 0;
			</script>
			<?
	if (Catalog\Config\Feature::isMultiPriceTypesEnabled())
	{

			$dbCatalogGroups = CCatalogGroup::GetList(
					array("SORT" => "ASC","NAME" => "ASC","ID" => "ASC"),
					array("!BASE" => "Y")
				);
			while ($arCatalogGroup = $dbCatalogGroups->Fetch())
			{
				?>
				<script type="text/javascript">
				arCatalogGroups[catalogGroupsInd] = <?= $arCatalogGroup["ID"] ?>;
				catalogGroupsInd++;
				</script>
				<tr>
					<td valign="top" align="right">
						<?echo GetMessage("C2IT_PRICE_TYPE")?> "<? echo htmlspecialcharsbx(!empty($arCatalogGroup["NAME_LANG"]) ? $arCatalogGroup["NAME_LANG"] : $arCatalogGroup["NAME"]); ?>":
					</td>
					<td valign="top" align="left">
						<table border="0" cellspacing="1" cellpadding="3" id="SUBOTHER_PRICE_GROUP_TABLE_<?= $arCatalogGroup["ID"] ?>">
							<thead>
							<tr>
							<td align="center"><?echo GetMessage("C2IT_FROM")?></td>
							<td align="center"><?echo GetMessage("C2IT_TO")?></td>
							<td align="center"><?echo GetMessage("C2IT_NAC_TYPE")?></td>
							<td align="center"><?echo GetMessage("C2IT_PRICE")?></td>
							<td align="center"><?echo GetMessage("C2IT_CURRENCY")?></td>
							</tr>
							</thead>
							<tbody id="subcontainer3_<?= $arCatalogGroup["ID"] ?>">
							<?
							$ind = -1;
							$dbPriceList = CPrice::GetList(
									array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
									array("PRODUCT_ID" => $PRODUCT_ID, "CATALOG_GROUP_ID" => $arCatalogGroup["ID"])
								);
							$arPrice = $dbPriceList->Fetch();
							$intCount = count($arPriceBoundaries);
							for ($i = 0; $i < $intCount; $i++)
							{
								$boolExistPrice = false;
								$ind++;
								$str_CAT_QUANTITY_FROM = $arPriceBoundaries[$i]["FROM"];
								$str_CAT_QUANTITY_TO = $arPriceBoundaries[$i]["TO"];

								if ($arPrice
									&& intval($arPrice["QUANTITY_FROM"]) == $arPriceBoundaries[$i]["FROM"])
								{
									$str_CAT_ID = $arPrice["ID"];
									$str_CAT_EXTRA = $arPrice["EXTRA_ID"];
									$str_CAT_PRICE = $arPrice["PRICE"];
									$str_CAT_CURRENCY = $arPrice["CURRENCY"];

									$arPrice = $dbPriceList->Fetch();
								}
								else
								{
									$str_CAT_ID = 0;
									$str_CAT_EXTRA = 0;
									$str_CAT_PRICE = "";
									$str_CAT_CURRENCY = "";
								}

								if ($bVarsFromForm)
								{
									$str_CAT_EXTRA = ${"SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind};
									$str_CAT_PRICE = ${"SUBCAT_PRICE_".$arCatalogGroup["ID"]."_".$ind};
									$str_CAT_CURRENCY = ${"SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind};
									$str_CAT_QUANTITY_FROM = ${"SUBCAT_BASE_QUANTITY_FROM_".$ind};
									$str_CAT_QUANTITY_TO = ${"SUBCAT_BASE_QUANTITY_TO_".$ind};
								}
								if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
									$boolExistPrice = true;
								$arCatPricesExist[$ind][$arCatalogGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
								?>
								<tr id="submodel3_<?= $arCatalogGroup["ID"] ?>">
									<td valign="top" align="center">
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
										<input type="hidden" name="SUBCAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_ID) ?>">
									</td>
									<td valign="top" align="center">
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

									</td>
									<td valign="top" align="center">
										<?
										echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
										?>

									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo htmlspecialcharsbx($str_CAT_PRICE) ?>" size="10" OnChange="ptSubPriceChangeEx(this)">

									</td>
									<td valign="top" align="center">

											<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
											<script type="text/javascript">
												jsUtils.addEvent(window, 'load', function() {ChangeSubExtraEx(BX('SUBCAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
											</script>

									</td>
								</tr>
								<?
							}

							if ($bVarsFromForm && $ind < intval(${"SUBCAT_ROW_COUNTER_".$arCatalogGroup["ID"]}))
							{
								for ($i = $ind + 1; $i <= intval(${"SUBCAT_ROW_COUNTER_".$arCatalogGroup["ID"]}); $i++)
								{
									$boolExistPrice = false;
									$ind++;
									$str_CAT_QUANTITY_FROM = ${"SUBCAT_BASE_QUANTITY_FROM_".$ind};
									$str_CAT_QUANTITY_TO = ${"SUBCAT_BASE_QUANTITY_TO_".$ind};
									$str_CAT_EXTRA = ${"SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind};
									$str_CAT_PRICE = ${"SUBCAT_PRICE_".$arCatalogGroup["ID"]."_".$ind};
									$str_CAT_CURRENCY = ${"SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind};
									if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
										$boolExistPrice = true;
									$arCatPricesExist[$ind][$arCatalogGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
									?>
									<tr id="submodel3_<?= $arCatalogGroup["ID"] ?>">
										<td valign="top" align="center">
											<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
											<input type="hidden" name="SUBCAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= 0 ?>">
										</td>
										<td valign="top" align="center">
											<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

										</td>
										<td valign="top" align="center">
											<?
											echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
											?>

										</td>
										<td valign="top" align="center">
											<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?echo htmlspecialcharsbx($str_CAT_PRICE) ?>" size="10" OnChange="ptSubPriceChangeEx(this)">

										</td>
										<td valign="top" align="center">

												<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
												<script type="text/javascript">
													jsUtils.addEvent(window, 'load', function () {ChangeSubExtraEx(BX('SUBCAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
												</script>

										</td>
									</tr>
									<?
								}
							}
							if ($ind == -1)
							{
								$ind++;
								?>
								<tr id="submodel3_<?= $arCatalogGroup["ID"] ?>">
									<td valign="top" align="center">
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="3">
									</td>
									<td valign="top" align="center">
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="3">

									</td>
									<td valign="top" align="center">
										<?
										echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
										?>

									</td>
									<td valign="top" align="center">
										<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="10" OnChange="ptSubPriceChangeEx(this)">

									</td>
									<td valign="top" align="center">

											<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", (($bReadOnly) ? "disabled readonly" : "").' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>

									</td>
								</tr>
								<?
								$arCatPricesExist[$ind][$arCatalogGroup['ID']] = 'N';
							}
							?>
							</tbody>
						</table>
						<input type="hidden" name="SUBCAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" id="SUBCAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" value="<?= $ind ?>">
					</td>
				</tr>
				<?
			}
	}
			?>
		</table>
		<div id="ext_subprice_exist">
		<?
		foreach ($arCatPricesExist as $ind => $arPriceExist)
		{
			$strExist = (in_array('Y',$arPriceExist) ? 'Y' : 'N');
			?>
			<input type="hidden" name="SUBCAT_PRICE_EXIST_<? echo $ind; ?>" id="SUBCAT_PRICE_EXIST_<? echo $ind; ?>" value="<? echo $strExist; ?>"><?
		}
		?>
		</div>
</div>
		<?
		$subtabControl1->BeginNextTab();
		?>

		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="subcatalog_properties_table">
			<tr>
				<td width="40%">
					<?echo GetMessage("FULL_QUANTITY")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_QUANTITY = $arBaseProduct["QUANTITY"];
					if (!$bUseStoreControl && $bVarsFromForm) $str_CAT_BASE_QUANTITY = $SUBCAT_BASE_QUANTITY;
					?>
					<input type="text" name="SUBCAT_BASE_QUANTITY" <?if ($bReadOnly  || $bUseStoreControl) echo "disabled readonly" ?> value="<?echo htmlspecialcharsbx($str_CAT_BASE_QUANTITY) ?>" size="30">

				</td>
			</tr><?
			if ($bEnableReservation)
			{
			?>
			<tr id="SUBCAT_BASE_QUANTITY_RESERV">
				<td width="40%">
					<?echo GetMessage("BASE_QUANTITY_RESERVED")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_QUANTITY_RESERVED = $arBaseProduct["QUANTITY_RESERVED"];
					?>
					<input type="hidden" id="SUBCAT_BASE_QUANTITY_RESERVED_hidden" name="SUBCAT_BASE_QUANTITY_RESERVED" value="<?echo htmlspecialcharsbx($str_CAT_BASE_QUANTITY_RESERVED) ?>">
					<input type="text" id="SUBCAT_BASE_QUANTITY_RESERVED" name="SUBCAT_BASE_QUANTITY_RESERVED" <?if ($bReadOnly  || $bUseStoreControl) echo "disabled readonly" ?>  onfocus="ShowNotice()" onblur="HideNotice()" value="<?echo htmlspecialcharsbx($str_CAT_BASE_QUANTITY_RESERVED) ?>" size="30"><span id="CAT_QUANTITY_RESERVED_DIV" style="color: #af2d49; margin-left: 10px; display: none;">	<?echo GetMessage("QUANTITY_RESERVED_NOTICE")?></span>

				</td>
			</tr><?
			}
			?>
			<tr>
				<td width="40%">
					<?echo GetMessage("C2IT_MEASURE")?>:
				</td>
				<td width="60%">
					<?
					$arAllMeasure = array();
					$dbResultList = CCatalogMeasure::getList(
						array(),
						array(),
						false,
						false,
						array("ID", "CODE", "MEASURE_TITLE", "SYMBOL_INTL", "IS_DEFAULT")
					);
					while($arMeasure = $dbResultList->Fetch())
					{
						$arAllMeasure[] = $arMeasure;
					}
					$str_CAT_MEASURE = $arBaseProduct["MEASURE"];
					if ($bVarsFromForm)
						$str_CAT_MEASURE = $SUBCAT_MEASURE;
					?>
					<?if(!empty($arAllMeasure)):?>
						<select style="max-width:220px" id="SUBCAT_MEASURE" name="SUBCAT_MEASURE" <?if ($bReadOnly) echo "disabled readonly" ?>>
							<?foreach($arAllMeasure as $arMeasure):?>
								<option <?if($str_CAT_MEASURE == $arMeasure["ID"] || ($str_CAT_MEASURE == '' && $arMeasure["IS_DEFAULT"] == 'Y')) echo " selected";?> value="<?=$arMeasure["ID"]?>"><?=htmlspecialcharsbx($arMeasure["MEASURE_TITLE"])?></option>
							<?endforeach;
							unset($arMeasure);
							?>
						</select>
					<?else:
						$measureListUrl = $selfFolderUrl.'cat_measure_list.php?lang='.LANGUAGE_ID;
						$measureListUrl = $adminSidePanelHelper->editUrlToPublicPage($measureListUrl);
						echo GetMessage("C2IT_MEASURE_NO_MEASURE"); ?> <a target="_top" href="<?=$measureListUrl?>"><?=GetMessage("C2IT_MEASURES"); ?></a><br>
					<?endif;?>
				</td>
			</tr>
			<?if(!empty($arAllMeasure)):?>
				<tr>
					<td width="40%">
						<?echo GetMessage("C2IT_MEASURE_RATIO")?>:
					</td>
					<td width="60%">
						<?
						$str_CAT_MEASURE_RATIO = null;
						$SUBCAT_MEASURE_RATIO_ID = 0;
						$db_CAT_MEASURE_RATIO = CCatalogMeasureRatio::getList(
							["ID" => "ASC"],
							["PRODUCT_ID" => $PRODUCT_ID],
							false,
							false,
							[]
						);
						while ($ar_CAT_MEASURE_RATIO = $db_CAT_MEASURE_RATIO->Fetch())
						{
							if ($str_CAT_MEASURE_RATIO === null || $ar_CAT_MEASURE_RATIO['IS_DEFAULT'] == 'Y')
							{
								$str_CAT_MEASURE_RATIO = $ar_CAT_MEASURE_RATIO["RATIO"];
								if (!$bSubCopy)
									$SUBCAT_MEASURE_RATIO_ID = $ar_CAT_MEASURE_RATIO["ID"];
								if ($ar_CAT_MEASURE_RATIO['IS_DEFAULT'] == 'Y')
									break;
							}
						}
						unset($db_CAT_MEASURE_RATIO, $ar_CAT_MEASURE_RATIO);
						if ($str_CAT_MEASURE_RATIO === null)
							$str_CAT_MEASURE_RATIO = 1;
						if($bVarsFromForm)
							$str_CAT_MEASURE_RATIO = $SUBCAT_MEASURE_RATIO;
						?>
						<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_MEASURE_RATIO" name="SUBCAT_MEASURE_RATIO" value="<?echo htmlspecialcharsbx($str_CAT_MEASURE_RATIO) ?>" size="30">
						<input type="hidden" id="SUBCAT_MEASURE_RATIO_ID" name="SUBCAT_MEASURE_RATIO_ID" value="<?echo htmlspecialcharsbx($SUBCAT_MEASURE_RATIO_ID) ?>">
					</td>
				</tr>
			<?endif;?>
			<tr class="heading">
				<td colspan="2">
					<?echo GetMessage("C2IT_PARAMS")?>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?echo GetMessage("ENABLE_STORE_TRACE")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_QUANTITY_TRACE = $arBaseProduct["QUANTITY_TRACE_ORIG"];
					if ($bVarsFromForm) $str_CAT_BASE_QUANTITY_TRACE = $SUBCAT_BASE_QUANTITY_TRACE;
					?>
					<select id="SUBCAT_BASE_QUANTITY_TRACE" name="SUBCAT_BASE_QUANTITY_TRACE" <?if ($bReadOnly) echo "disabled readonly" ?>>
						<option value="D" <?if ("D"==$str_CAT_BASE_QUANTITY_TRACE) echo " selected"?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?echo $availQuantityTrace=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>) </option>
						<option value="Y" <?if ("Y"==$str_CAT_BASE_QUANTITY_TRACE) echo " selected"?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?if ("N"==$str_CAT_BASE_QUANTITY_TRACE) echo " selected"?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?echo GetMessage("C2IT_CAN_BUY_NULL_EXT")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_CAN_BUY_ZERO = $arBaseProduct["CAN_BUY_ZERO_ORIG"];
					if ($bVarsFromForm) $str_CAT_BASE_CAN_BUY_ZERO = $SUBUSE_STORE;
					?>
					<select id="SUBUSE_STORE" name="SUBUSE_STORE" <? echo ($bReadOnly ? "disabled readonly" : ''); ?>>
						<option value="D" <?if ("D"==$str_CAT_BASE_CAN_BUY_ZERO) echo " selected"?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?echo $availCanBuyZero=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>) </option>
						<option value="Y" <?if ("Y"==$str_CAT_BASE_CAN_BUY_ZERO) echo " selected"?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?if ("N"==$str_CAT_BASE_CAN_BUY_ZERO) echo " selected"?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%"><? echo GetMessage("C2IT_SUBSCRIBE"); ?>:</td>
				<td width="60%">
					<?
					$str_CAT_SUBSCRIBE = $arBaseProduct["SUBSCRIBE_ORIG"];
					if ($bVarsFromForm) $str_CAT_SUBSCRIBE = $SUBSUBSCRIBE;
					?>
					<select id="SUBSUBSCRIBE" name="SUBSUBSCRIBE" <?if ($bReadOnly) echo "disabled readonly" ?>>
						<option value="D" <?if ("D"==$str_CAT_SUBSCRIBE) echo " selected"?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?echo 'Y' == $strGlobalSubscribe ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>)</option>
						<option value="Y" <?if ("Y"==$str_CAT_SUBSCRIBE) echo " selected"?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?if ("N"==$str_CAT_SUBSCRIBE) echo " selected"?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr class="heading">
				<td colspan="2">
					<?echo GetMessage("C2IT_MEASUREMENTS_EXT")?>
				</td>
			</tr>
			<tr>
				<td>
					<?echo GetMessage("BASE_WEIGHT")?>:
				</td>
				<td>
					<?
					$str_CAT_BASE_WEIGHT = $arBaseProduct["WEIGHT"];
					if ($bVarsFromForm) $str_CAT_BASE_WEIGHT = $SUBCAT_BASE_WEIGHT;
					?>
					<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_BASE_WEIGHT" value="<?echo htmlspecialcharsbx($str_CAT_BASE_WEIGHT) ?>" size="30">

				</td>
			</tr>
			<tr>
				<td width="40%">
					<?echo GetMessage("C2IT_BASE_LENGTH")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_LENGTH = $arBaseProduct["LENGTH"];
					if ($bVarsFromForm) $str_CAT_BASE_LENGTH = $SUBCAT_BASE_LENGTH;
					?>
					<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_LENGTH" name="SUBCAT_BASE_LENGTH" value="<?echo htmlspecialcharsbx($str_CAT_BASE_LENGTH) ?>" size="30">
				</td>
			</tr>

			<tr>
				<td width="40%">
					<?echo GetMessage("C2IT_BASE_WIDTH")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_WIDTH = $arBaseProduct["WIDTH"];
					if ($bVarsFromForm) $str_CAT_BASE_WIDTH = $SUBCAT_BASE_WIDTH;
					?>
					<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_WIDTH" name="SUBCAT_BASE_WIDTH" value="<?echo htmlspecialcharsbx($str_CAT_BASE_WIDTH) ?>" size="30">
				</td>
			</tr>

			<tr>
				<td width="40%">
					<?echo GetMessage("C2IT_BASE_HEIGHT")?>:
				</td>
				<td width="60%">
					<?
					$str_CAT_BASE_HEIGHT = $arBaseProduct["HEIGHT"];
					if ($bVarsFromForm) $str_CAT_BASE_HEIGHT = $SUBCAT_BASE_HEIGHT;
					?>
					<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_BASE_HEIGHT" name="SUBCAT_BASE_HEIGHT" value="<?echo htmlspecialcharsbx($str_CAT_BASE_HEIGHT) ?>" size="30">
				</td>
			</tr>
			<?
			if ($arCatalog["SUBSCRIPTION"]=="Y")
			{
				?>
				<tr class="heading">
					<td colspan="2"><?echo GetMessage("C2IT_SUBSCR_PARAMS")?></td>
				</tr>
				<tr>
					<td>
						<?echo GetMessage("C2IT_PAY_TYPE")?>
					</td>
					<td>
						<script type="text/javascript">
						function ChangeSubPriceType()
						{
							if (bReadOnly)
								return;

							var e_pt = BX('SUBCAT_PRICE_TYPE');

							if (e_pt.options[e_pt.selectedIndex].value == "S")
							{
								BX('SUBCAT_RECUR_SCHEME_TYPE').disabled = true;
								BX('SUBCAT_RECUR_SCHEME_LENGTH').disabled = true;
								BX('SUBCAT_TRIAL_PRICE_ID').disabled = true;
								BX('SUBCAT_TRIAL_PRICE_ID_BUTTON').disabled = true;
							}
							else
							{
								if (e_pt.options[e_pt.selectedIndex].value == "R")
								{
									BX('SUBCAT_RECUR_SCHEME_TYPE').disabled = false;
									BX('SUBCAT_RECUR_SCHEME_LENGTH').disabled = false;
									BX('SUBCAT_TRIAL_PRICE_ID').disabled = true;
									BX('SUBCAT_TRIAL_PRICE_ID_BUTTON').disabled = true;
								}
								else
								{
									BX('SUBCAT_RECUR_SCHEME_TYPE').disabled = false;
									BX('SUBCAT_RECUR_SCHEME_LENGTH').disabled = false;
									BX('SUBCAT_TRIAL_PRICE_ID').disabled = false;
									BX('SUBCAT_TRIAL_PRICE_ID_BUTTON').disabled = false;
								}
							}
						}
						</script>

						<?
						$str_CAT_PRICE_TYPE = $arBaseProduct["PRICE_TYPE"];
						if ($bVarsFromForm) $str_CAT_PRICE_TYPE = $SUBCAT_PRICE_TYPE;
						?>
						<select id="SUBCAT_PRICE_TYPE" name="SUBCAT_PRICE_TYPE" OnChange="ChangeSubPriceType()">
							<option value="S"<?if ($str_CAT_PRICE_TYPE=="S") echo " selected";?>><?echo GetMessage("C2IT_SINGLE")?></option>
							<option value="R"<?if ($str_CAT_PRICE_TYPE=="R") echo " selected";?>><?echo GetMessage("C2IT_REGULAR")?></option>
							<option value="T"<?if ($str_CAT_PRICE_TYPE=="T") echo " selected";?>><?echo GetMessage("C2IT_TRIAL")?></option>
						</select>

					</td>
				</tr>
				<tr>
					<td>
						<?echo GetMessage("C2IT_PERIOD_LENGTH")?>
					</td>
					<td>

						<?
						$str_CAT_RECUR_SCHEME_LENGTH = $arBaseProduct["RECUR_SCHEME_LENGTH"];
						if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_LENGTH = $SUBCAT_RECUR_SCHEME_LENGTH;
						?>
						<input type="text" <?if ($bReadOnly) echo "disabled readonly" ?> id="SUBCAT_RECUR_SCHEME_LENGTH" name="SUBCAT_RECUR_SCHEME_LENGTH" value="<?echo htmlspecialcharsbx($str_CAT_RECUR_SCHEME_LENGTH) ?>" size="10">

					</td>
				</tr>
				<tr>
					<td>
						<?echo GetMessage("C2IT_PERIOD_TIME")?>
					</td>
					<td>
						<?
						$str_CAT_RECUR_SCHEME_TYPE = $arBaseProduct["RECUR_SCHEME_TYPE"];
						if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_TYPE = $SUBCAT_RECUR_SCHEME_TYPE;
						?>
						<select id="SUBCAT_RECUR_SCHEME_TYPE" name="SUBCAT_RECUR_SCHEME_TYPE">
							<?
							foreach ($periodTimeTypes as $key => $value)
							{
								?><option value="<?= $key ?>"<?if ($str_CAT_RECUR_SCHEME_TYPE==$key) echo " selected";?>><?= $value ?></option><?
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?echo GetMessage("C2IT_TRIAL_FOR")?>
					</td>
					<td>

						<?
						$str_CAT_TRIAL_PRICE_ID = $arBaseProduct["TRIAL_PRICE_ID"];
						if ($bVarsFromForm) $str_CAT_TRIAL_PRICE_ID = $SUBCAT_TRIAL_PRICE_ID;
						$catProductName = "";
						if (intval($str_CAT_TRIAL_PRICE_ID) > 0)
						{
							$dbCatElement = CIBlockElement::GetByID(intval($str_CAT_TRIAL_PRICE_ID));
							if ($arCatElement = $dbCatElement->GetNext())
								$catProductName = $arCatElement["NAME"];
						}
						?>
						<input id="SUBCAT_TRIAL_PRICE_ID" name="SUBCAT_TRIAL_PRICE_ID" value="<?= htmlspecialcharsbx($str_CAT_TRIAL_PRICE_ID) ?>" size="5" type="text"><input type="button" id="SUBCAT_TRIAL_PRICE_ID_BUTTON" name="SUBCAT_TRIAL_PRICE_ID_BUTTON" value="..." onClick="window.open('cat_product_search.php?IBLOCK_ID=<?= $IBLOCK_ID ?>&amp;field_name=SUBCAT_TRIAL_PRICE_ID&amp;alt_name=subtrial_price_alt&amp;form_name='+getElementSubFormName(), '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));">&nbsp;<span id="subtrial_price_alt"><?= $catProductName ?></span>

					</td>
				</tr>
				<tr>
					<td>
						<?echo GetMessage("C2IT_WITHOUT_ORDER")?>
					</td>
					<td>
						<?
						$str_CAT_WITHOUT_ORDER = $arBaseProduct["WITHOUT_ORDER"];
						if ($bVarsFromForm) $str_CAT_WITHOUT_ORDER = $SUBCAT_WITHOUT_ORDER;
						?>
						<input type="checkbox" <?if ($bReadOnly) echo "disabled readonly" ?> name="SUBCAT_WITHOUT_ORDER" value="Y" <?if ($str_CAT_WITHOUT_ORDER=="Y") echo "checked"?>>

					</td>
				</tr>
				<?
			}

			$arUserFields = $USER_FIELD_MANAGER->GetUserFields(Catalog\ProductTable::getUfId(), $PRODUCT_ID, LANGUAGE_ID);
			if (!empty($arUserFields))
			{
				if ($arCatalog['SUBSCRIPTION'] == 'Y')
				{
					if (isset($arUserFields['UF_PRODUCT_GROUP']))
						unset($arUserFields['UF_PRODUCT_GROUP']);
				}
			}
			if (!empty($arUserFields))
			{
				?><tr class="heading">
				<td colspan="2"><?echo GetMessage("C2IT_UF_FIELDS")?></td>
				</tr><?

				foreach ($arUserFields as $FIELD_NAME => $arUserField)
				{
					$arUserField["VALUE_ID"] = $PRODUCT_ID;
					$strLabel = $arUserField["EDIT_FORM_LABEL"] ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
					$arUserField["EDIT_FORM_LABEL"] = $strLabel;

					$html = $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);
					//TODO: remove this code after refactoring UF fields
					if ($FIELD_NAME == 'UF_PRODUCT_GROUP')
					{
						$html = str_replace('<select', '<select style="max-width: 300px;"', $html);
					}
					echo $html;
				}
				unset($FIELD_NAME, $arUserField);
			}
			unset($arUserFields);
			?>
		</table>
<script type="text/javascript">
SetSubFieldsStyle('subcatalog_properties_table');
</script>
		<?if ($arCatalog["SUBSCRIPTION"]=="Y"):?>
			<script type="text/javascript">
			ChangeSubPriceType();
			</script>
		<?endif;?>

		<?if ($arCatalog["SUBSCRIPTION"]=="Y"):?>

			<?
			$subtabControl1->BeginNextTab();
			?>

			<script type="text/javascript">
			function SubCatGroupsActivate(obj, id)
			{
				if (bReadOnly)
					return;

				var ed = BX('SUBCAT_ACCESS_LENGTH_' + id);
				var ed1 = BX('SUBCAT_ACCESS_LENGTH_TYPE_' + id);
				ed.disabled = !obj.checked;
				ed1.disabled = !obj.checked;
			}
			</script>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
				<tr class="heading">
					<td><?echo GetMessage("C2IT_VKL")?></td>
					<td><?echo GetMessage("C2IT_USERS_GROUP")?></td>
					<td><?echo GetMessage("C2IT_ACTIVE_TIME")?> <sup>1)</sup></td>
				</tr>
				<?
				$arCurProductGroups = array();

				$dbProductGroups = CCatalogProductGroups::GetList(
						array(),
						array("PRODUCT_ID" => $ID),
						false,
						false,
						array("ID", "GROUP_ID", "ACCESS_LENGTH", "ACCESS_LENGTH_TYPE")
					);
				while ($arProductGroup = $dbProductGroups->Fetch())
				{
					$arCurProductGroups[intval($arProductGroup["GROUP_ID"])] = array(intval($arProductGroup["ACCESS_LENGTH"]), $arProductGroup["ACCESS_LENGTH_TYPE"]);
				}

				$arAvailContentGroups = array();
				$availContentGroups = COption::GetOptionString("catalog", "avail_content_groups");
				if ($availContentGroups <> '')
					$arAvailContentGroups = explode(",", $availContentGroups);

				$bNoAvailGroups = true;

				$dbGroups = CGroup::GetList(
						($b="c_sort"),
						($o="asc"),
						array("ANONYMOUS" => "N")
					);
				while ($arGroup = $dbGroups->Fetch())
				{
					$arGroup["ID"] = intval($arGroup["ID"]);

					if ($arGroup["ID"] == 2
						|| !in_array($arGroup["ID"], $arAvailContentGroups))
						continue;

					if ($bVarsFromForm)
					{
						if (isset(${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
						{
							$arCurProductGroups[$arGroup["ID"]] = array(intval(${"SUBCAT_ACCESS_LENGTH_".$arGroup["ID"]}), ${"SUBCAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]});
						}
						elseif (array_key_exists($arGroup["ID"], $arCurProductGroups))
						{
							unset($arCurProductGroups[$arGroup["ID"]]);
						}
					}

					$bNoAvailGroups = false;
					?>
					<tr>
						<td align="center">

								<input type="checkbox" name="SUBCAT_USER_GROUP_ID_<?= $arGroup["ID"] ?>" value="Y"<?if (isset($arCurProductGroups[$arGroup["ID"]])) echo " checked";?> onclick="SubCatGroupsActivate(this, <?= $arGroup["ID"] ?>)">

						</td>
						<td align="left"><? echo htmlspecialcharsbx($arGroup["NAME"]); ?></td>
						<td align="center">

								<input type="text" id="SUBCAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" name="SUBCAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" size="5" <?
								if (isset($arCurProductGroups[$arGroup["ID"]]))
									echo 'value="'.$arCurProductGroups[$arGroup["ID"]][0].'" ';
								else
									echo 'disabled ';
								?>>
								<select id="SUBCAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>" name="SUBCAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>"<?
								if (!isset($arCurProductGroups[$arGroup["ID"]]))
									echo ' disabled';
								?>>
									<?
									foreach ($periodTimeTypes as $key => $value)
									{
										?><option value="<?= $key ?>"<?if ($arCurProductGroups[$arGroup["ID"]][1] == $key) echo " selected";?>><?= $value ?></option><?
									}
									?>
								</select>

						</td>
					</tr>
					<?
				}

				if ($bNoAvailGroups)
				{
					$textForSettingsNotify = "<a href=\"".$selfFolderUrl."settings.php?mid=catalog&lang=".LANGUAGE_ID."\">".
						GetMessage("C2IT_NO_USER_GROUPS2")."</a>";
					if ($adminSidePanelHelper->isPublicSidePanel())
					{
						$textForSettingsNotify = GetMessage("C2IT_NO_USER_GROUPS2");
					}
					?>
					<tr>
						<td colspan="3"><?=GetMessage("C2IT_NO_USER_GROUPS1")?><?=" ".$textForSettingsNotify?></td>
					</tr>
					<?
				}
				?>
			</table>
			<br><b>1)</b> <?echo GetMessage("C2IT_ZERO_HINT")?>
		<?endif;?>
		<?
		$subtabControl1->BeginNextTab();

		$arParams = array();
		$arSKU = CCatalogSku::GetInfoByOfferIBlock($IBLOCK_ID);
		if (is_array($arSKU))
		{
			$arParams['SKU'] = 'Y';
			$arParams['SKU_PARAMS'] = $arSKU;
		}

		$arDiscountList = CCatalogDiscount::GetDiscountForProduct(array('ID' => $PRODUCT_ID, 'IBLOCK_ID' => $IBLOCK_ID), $arParams);

		if (empty($arDiscountList))
		{
			?><b><?echo GetMessage("C2IT_NO_ACTIVE_DISCOUNTS")?></b><br><?
		}
		else
		{
			$showDiscountUrl = $bDiscount;
			$discountUrl = $selfFolderUrl.'cat_discount_edit.php?ID=';
			if (Main\ModuleManager::isModuleInstalled('sale') && (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y')
			{
				$showDiscountUrl = ($APPLICATION->GetGroupRight('sale') >= 'W');
				$discountUrl = $selfFolderUrl.'sale_discount_edit.php?ID=';
			}
			?><table border="0" cellspacing="0" cellpadding="0" class="internal" align="center" width="100%">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("C2IT_SITE")?></td>
					<td><?echo GetMessage("C2IT_ACTIVITY")?></td>
					<td><?echo GetMessage("C2IT_NAME")?></td>
					<td><?echo GetMessage("C2IT_AMOUNT")?></td>
					<? if ($showDiscountUrl)
					{
					?><td><?echo GetMessage("C2IT_ACTIONS")?></td><?
					}
					?>
				</tr><?
			foreach ($arDiscountList as $arProductDiscounts)
			{
					$boolWork = true;
				?><tr>
						<td style="text-align: right;"><? echo $arProductDiscounts["ID"] ?></td>
						<td style="text-align: center;"><? echo $arProductDiscounts["SITE_ID"] ?></td>
						<td style="text-align: center;"><? echo GetMessage("C2IT_YES")?></td>
						<td style="text-align: left;"><? echo htmlspecialcharsbx($arProductDiscounts["NAME"]) ?></td>
						<td style="text-align: right;">
							<?
							if ($arProductDiscounts["VALUE_TYPE"]=="P")
							{
								echo $arProductDiscounts["VALUE"]."%";
							}
							elseif ($arProductDiscounts["VALUE_TYPE"]=="S")
							{
								?>= <? echo CCurrencyLang::CurrencyFormat($arProductDiscounts["VALUE"], $arProductDiscounts["CURRENCY"], true);
							}
							else
							{
								echo CCurrencyLang::CurrencyFormat($arProductDiscounts["VALUE"], $arProductDiscounts["CURRENCY"], true);
							}
							?>
						</td>
						<?
						if ($showDiscountUrl)
						{
						?>
						<td style="text-align: center;">
							<a href="<?=$discountUrl.$arProductDiscounts["ID"] ?>&lang=<?=LANGUAGE_ID; ?>#tb" target="_blank"><?echo GetMessage("C2IT_MODIFY")?></a>
						</td>
						<?
						}
						?>
					</tr>
					<?
			}
			?></table><?
		}
		?>
		<br>
		<?echo GetMessage("C2IT_DISCOUNT_HINT");
	$subtabControl1->BeginNextTab();
		$stores = array();
		$storeLink = array();
		$storeCount = 0;
		$iterator = Catalog\StoreTable::getList(array(
			'select' => array('ID', 'TITLE', 'ADDRESS', 'SORT'),
			'filter' => array('=ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['ADDRESS'] = trim($row['ADDRESS']);
			$row['PRODUCT_AMOUNT'] = '';
			$stores[$storeCount] = $row;
			$storeLink[$row['ID']] = &$stores[$storeCount];
			$storeCount++;
		}
		unset($row, $iterator);
		if ($storeCount > 0)
		{
			$storeIds = array_keys($storeLink);
			if (!$bCopy)
			{
				$iterator = Catalog\StoreProductTable::getList(array(
					'select' => array('STORE_ID', 'AMOUNT'),
					'filter' => array('=PRODUCT_ID' => $PRODUCT_ID, '@STORE_ID' => $storeIds)
				));
				while ($row = $iterator->fetch())
				{
					$storeId = (int)$row['STORE_ID'];
					$storeLink[$storeId]['PRODUCT_AMOUNT'] = $row['AMOUNT'];
				}
				unset($row, $iterator);
			}
			if ($bVarsFromForm)
			{
				foreach ($storeIds as $store)
				{
					if (isset($_POST['SUBAR_AMOUNT'][$store]) && is_string($_POST['SUBAR_AMOUNT'][$store]))
						$storeLink[$store]['PRODUCT_AMOUNT'] = $_POST['SUBAR_AMOUNT'][$store];
				}
				unset($store);
			}
			unset($storeIds);
		}
		unset($storeLink);
		if ($storeCount > 0)
		{
			?><table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
			<tr class="heading">
				<td><?echo GetMessage("C2IT_STORE_NUMBER"); ?></td>
				<td><? echo GetMessage('C2IT_STORE_ID'); ?></td>
				<td><?echo GetMessage("C2IT_NAME"); ?></td>
				<td><?echo GetMessage("C2IT_STORE_ADDR"); ?></td>
				<td><?echo GetMessage("C2IT_PROD_AMOUNT"); ?></td>
			</tr>
			<?
			foreach ($stores as $storeIndex => $row)
			{
				$storeId = '';
				$address = '';
				$storeUrl = $storeIndex + 1;
				if ($bStore)
				{
					$storeId = $row['ID'];
					$storeEditUrl = $selfFolderUrl.'cat_store_edit.php?ID='.$row['ID'].'&lang='.LANGUAGE_ID;
					$storeEditUrl = ($publicMode ? str_replace(".php", "/", $storeEditUrl) : $storeEditUrl);
					$address = ('' != $row['ADDRESS'] ? htmlspecialcharsbx($row['ADDRESS']) : '<a href="'.$storeEditUrl.'">'.GetMessage("C2IT_EDIT").'</a>');
					$storeUrl = '<a href="'.$storeEditUrl.'">'.$storeUrl.'</a>';
				}
				?><tr>
				<td style="text-align:center;"><?=$storeUrl; ?></td>
				<td style="text-align:center;"><?=$storeId; ?></td>
				<td style="text-align:center;"><?=htmlspecialcharsbx($row['TITLE']); ?></td>
				<td style="text-align:center;"><?=$address; ?></td>
				<td style="text-align:center;"><input type="text" id="SUBAR_AMOUNT_<?=$row['ID']; ?>" name="SUBAR_AMOUNT[<?=$row['ID']?>]" size="12" value="<?=htmlspecialcharsbx($row['PRODUCT_AMOUNT']); ?>" <? echo ((!$bStore || $bUseStoreControl) ? 'disabled readonly' : ''); ?>><?
				if ($bStore)
				{
					?><input type="hidden" name="SUBAR_STORE_ID[<?=$row['ID']?>]" value="<?=$row['ID']?>"><?
				}
				?></td></tr><?
				unset($storeUrl, $address, $storeId);
			}
			unset($storeIndex, $row);
			?></table><?
		}
		else
		{
			if ($bStore)
			{
				$storeListUrl = $selfFolderUrl.'cat_store_list.php?lang='.LANGUAGE_ID;
				$storeListUrl = $adminSidePanelHelper->editUrlToPublicPage($storeListUrl);
				?><b><? echo GetMessage("C2IT_STORE_NO_STORE"); ?> <a target="_top" href="<?=$storeListUrl?>"><?=GetMessage("C2IT_STORE"); ?></a></b><br><?
			}
		}
		if (!$bUseStoreControl)
			echo "<br>".GetMessage("C2IT_STORE_HINT");
		unset($storeCount, $stores);

		if($bUseStoreControl)
		{
			$subtabControl1->BeginNextTab();
			$barcode = '';
			$bUseMultiplyBarcode = ($arBaseProduct['BARCODE_MULTI'] == "Y");
			if (!$bSubCopy)
			{
				$dbBarcode = CCatalogStoreBarCode::GetList(array(), array("PRODUCT_ID" => $PRODUCT_ID));

				if($arBarcode = $dbBarcode->Fetch())
					$barcode = $arBarcode["BARCODE"];
			}
			?>
			<input type="hidden" name="SUBCAT_BARCODE_MULTIPLY" id="SUBCAT_BARCODE_MULTIPLY_N" value="N" />
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="subcatalog_barcode_table">

				<tr>
					<td width="40%"><label for="SUBCAT_BARCODE_MULTIPLY"><? echo GetMessage('C2IT_BARCODE_MULTIPLY'); ?>:</label></td>
					<td width="60%">
						<input type="checkbox" name="SUBCAT_BARCODE_MULTIPLY" id="SUBCAT_BARCODE_MULTIPLY" value="Y" <?=$bUseMultiplyBarcode ? 'checked="checked"' : ''?> <? echo (($bReadOnly) ? 'disabled readonly' : 'onclick="checkSubBarCode();"'); ?>/>
					</td>
				</tr>

				<tr id="tr_SUB_CAT_BARCODE"<? echo (($bUseMultiplyBarcode) ? ' style="display: none;"' : ''); ?>>
					<td><?echo GetMessage("C2IT_BAR_CODE")?>:</td><?
					$strDisable = '';
					if ($bReadOnly)
					{
						$strDisable = ' disabled readonly';
					}
					elseif ('' != $barcode)
					{
						$strDisable = ' disabled';
					}
					?>
					<td><input type="text" name="SUBCAT_BARCODE" id="SUBCAT_BARCODE" size="30" value="<? echo htmlspecialcharsbx($barcode); ?>" <? echo $strDisable; ?>/></td>
				</tr><?
				if (0 < $PRODUCT_ID && '' != $barcode)
				{
				?>
				<tr id="tr_SUBCAT_BARCODE_EDIT"<? echo (($bUseMultiplyBarcode) ? ' style="display: none;"' : ''); ?>>
					<td><?echo GetMessage("C2IT_BAR_CODE_EDIT")?>:</td>
					<td>
						<input type="hidden" name="SUBCAT_BARCODE_EDIT" id="SUBCAT_BARCODE_EDIT_N" value="N" />
						<input type="checkbox" name="SUBCAT_BARCODE_EDIT" id="SUBCAT_BARCODE_EDIT_Y" size="30" value="Y" <? echo (($bReadOnly) ? ' disabled readonly' : ' onclick="editSubBarCode();"'); ?> />
					</td>
				</tr><?
				}
				?>
			</table>
			<?
		}

		if($activitySubscribeTab)
		{
			$subtabControl1->BeginNextTab();
			?>
			<script type="text/javascript">
				function getDataSubscriptions() {
					BX.ajax({
						method: 'POST',
						dataType: 'json',
						url: '/bitrix/tools/catalog/subscription_card_product.php',
						data: {
							sessid: BX.bitrix_sessid(),
							getSubscriptionData: 'Y',
							itemId: '<?=$PRODUCT_ID?>'
						},
						onsuccess: BX.delegate(function (result) {
							if(result.success)
							{
								if(result.hasOwnProperty('data'))
								{
									if(result.data.hasOwnProperty('totalCount'))
										BX('bx-catalog-subscribe-total-count').innerHTML = result.data.totalCount;
									if(result.data.hasOwnProperty('activeCount'))
										BX('bx-catalog-subscribe-active-count').innerHTML = result.data.activeCount;
								}
								BX('subscription-data-table').style.display = '';
								BX('subscription-data-error').innerHTML = '';
							}
							else
							{
								if(result.hasOwnProperty('message'))
								{
									BX('subscription-data-error').innerHTML = result.message;
									BX('subscription-data-table').style.display = 'none';
								}
							}
						}, this)
					});
				}
			</script>
			<div id="subscription-data">
				<span id="subscription-data-error" class="errortext"></span>
				<table border="0" cellspacing="0" cellpadding="5" width="100%" class="edit-table" id="subscription-data-table">
					<tr>
						<td width="40%" style="text-align:right;"><?=GetMessage('C2IT_NUMBER_SUBSCRIPTIONS')?></td>
						<td width="60%" id="bx-catalog-subscribe-total-count"></td>
					</tr>
					<tr>
						<td width="40%" style="text-align:right;"><?=GetMessage('C2IT_NUMBER_ACTIVE_SUBSCRIPTIONS')?></td>
						<td width="60%" id="bx-catalog-subscribe-active-count"></td>
					</tr>
					<tr>
						<td width="40%" style="text-align:right;"><?=GetMessage('C2IT_LIST_SUBSCRIPTIONS')?></td>
						<td width="60%">
							<?
							$subscriptionUrl = $selfFolderUrl."cat_subscription_list.php?ITEM_ID=".htmlspecialcharsbx($PRODUCT_ID)."&lang=".LANGUAGE_ID;
							$subscriptionUrl = ($publicMode ? str_replace(".php", "/", $subscriptionUrl) : $subscriptionUrl);
							?>
							<a target="_top" href="<?=$subscriptionUrl?>">
								<?=GetMessage('C2IT_LIST_SUBSCRIPTIONS_TEXT')?>
							</a>
						</td>
					</tr>
				</table>
			</div>
			<?
		}
		$subtabControl1->End();
		?>
	</td>
</tr>
<?
}