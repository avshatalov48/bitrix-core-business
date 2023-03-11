<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var bool $bSubCopy */
/** @var bool $bVarsFromForm */
/** @var int $IBLOCK_ID */
/** @var int $ID */

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Currency;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

if (
	isset($urlBuilderId)
	&& in_array(
		$urlBuilderId,
		[
			'SHOP',
			'INVENTORY',
			'CRM',
		]
	)
)
{
	$selfFolderUrl = '/shop/settings/';
	$publicMode = true;
	$adminSidePanelHelper->setPublicPageProcessMode(true);
}
else
{
	$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
	$publicMode = defined("SELF_FOLDER_URL");
}

$isCloud = Loader::includeModule('bitrix24');

$PRODUCT_ID = (0 < $ID ? CIBlockElement::GetRealElement($ID) : 0);

$accessController = AccessController::getCurrent();

$iblockEditProduct = $PRODUCT_ID > 0 && !$bSubCopy
	? CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, 'element_edit_price')
	: CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'element_edit_price')
;

$allowEdit = false;
if ($iblockEditProduct)
{
	$allowEdit = $PRODUCT_ID > 0 && !$bSubCopy
		? $accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT)
		: $accessController->check(ActionDictionary::ACTION_PRODUCT_ADD);
}
$allowEditPrices = $allowEdit
	&& $accessController->check(ActionDictionary::ACTION_PRICE_EDIT)
;
$allowView = $allowEdit
	|| $accessController->check(ActionDictionary::ACTION_CATALOG_READ)
	|| $accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
;

if (!$allowView)
{
	return;
}

$disableFieldAttributes = ' disabled readonly';
$disableProduct = $allowEdit
	? ''
	: $disableFieldAttributes
;
$disablePrice = $allowEditPrices
	? ''
	: $disableFieldAttributes
;

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

$bDiscount = $accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET);
$bStore = $accessController->check(ActionDictionary::ACTION_STORE_VIEW);
$bUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
$bEnableReservation = (COption::GetOptionString('catalog', 'enable_reservation') !== 'N');
$allowedShowQuantity = \CCatalogAdminTools::allowedShowQuantityFields();
$enableQuantityRanges = Catalog\Config\Feature::isPriceQuantityRangesEnabled();
$quantityRangesHelpLink = null;
if (!$enableQuantityRanges)
{
	$quantityRangesHelpLink = Catalog\Config\Feature::getPriceQuantityRangesHelpLink();
}

$availQuantityTrace = COption::GetOptionString("catalog", "default_quantity_trace");
$availCanBuyZero = COption::GetOptionString("catalog", "default_can_buy_zero");
$strGlobalSubscribe = COption::GetOptionString("catalog", "default_subscribe");

$arCatalog = CCatalog::GetByID($IBLOCK_ID);
$vatInclude = (Main\Config\Option::get('catalog', 'default_product_vat_included') === 'Y' ? 'Y' : 'N');

$arBaseProduct = null;
$periodTimeTypes = [];
if ($arCatalog['SUBSCRIPTION'] == 'Y')
{
	$defaultProduct = [
		'QUANTITY' => '',
		'QUANTITY_RESERVED' => '',
		'VAT_ID' => 0,
		'VAT_INCLUDED' => $vatInclude,
		'QUANTITY_TRACE_ORIG' => Catalog\ProductTable::STATUS_NO,
		'CAN_BUY_ZERO_ORIG' => Catalog\ProductTable::STATUS_NO,
		'SUBSCRIBE_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
		'SUBSCRIBE' => $strGlobalSubscribe,
		'PURCHASING_PRICE' => '',
		'PURCHASING_CURRENCY' => '',
		'BARCODE_MULTI' => '',
		'PRICE_TYPE' => '',
		'RECUR_SCHEME_TYPE' => '',
		'RECUR_SCHEME_LENGTH' => '',
		'TRIAL_PRICE_ID' => '',
		'WITHOUT_ORDER' => '',
	];
	$periodTimeTypes = Catalog\ProductTable::getPaymentPeriods(true);
}
else
{
	$defaultProduct = [
		'QUANTITY' => '',
		'QUANTITY_RESERVED' => '',
		'VAT_ID' => 0,
		'VAT_INCLUDED' => $vatInclude,
		'QUANTITY_TRACE_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
		'CAN_BUY_ZERO_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
		'SUBSCRIBE_ORIG' => Catalog\ProductTable::STATUS_DEFAULT,
		'SUBSCRIBE' => $strGlobalSubscribe,
		'PURCHASING_PRICE' => '',
		'PURCHASING_CURRENCY' => '',
		'WEIGHT' => '',
		'WIDTH' => '',
		'LENGTH' => '',
		'HEIGHT' => '',
		'MEASURE' => '',
		'BARCODE_MULTI' => ''
	];
}
if ($PRODUCT_ID > 0)
{
	$iterator = Catalog\ProductTable::getList([
		'select' => array_merge(
			[
				'ID',
				'TYPE',
			],
			array_keys($defaultProduct)
		),
		'filter' => [
			'=ID' => $PRODUCT_ID,
		]
	]);
	$arBaseProduct = $iterator->fetch();
	unset($iterator);
	if (!empty($arBaseProduct))
	{
		if ($bSubCopy)
		{
			$arBaseProduct['QUANTITY'] = '';
			$arBaseProduct['QUANTITY_RESERVED'] = '';
		}
		if ($arCatalog['SUBSCRIPTION'] == 'Y')
		{
			$arBaseProduct['QUANTITY_TRACE_ORIG'] = Catalog\ProductTable::STATUS_NO;
			$arBaseProduct['CAN_BUY_ZERO_ORIG'] = Catalog\ProductTable::STATUS_NO;
		}
	}
}

if ($arBaseProduct === null)
{
	$arBaseProduct = $defaultProduct;
	$arBaseProduct['TYPE'] = Catalog\ProductTable::TYPE_OFFER;
}

$disableQuantityFields = !$allowEdit || $bUseStoreControl
	? $disableFieldAttributes
	: ''
;

$subscribeEnabled = $arBaseProduct["SUBSCRIBE"] == 'Y';
$activitySubscribeTab = $PRODUCT_ID > 0 && !$bSubCopy && $subscribeEnabled;

$arExtraList = array();
$l = CExtra::GetList(array("NAME" => "ASC"));
while ($l_res = $l->Fetch())
{
	$arExtraList[] = $l_res;
}
?>
<tr class="heading">
<td colspan="2"><?php
	echo GetMessage("IBLOCK_TCATALOG");
	if (!$allowEdit)
	{
		echo ' ' . GetMessage("IBLOCK_TREADONLY");
	}
	?>
<script type="text/javascript">
var allowSubPriceEdit = <?= ($allowEditPrices ? 'true' : 'false'); ?>;
var allowSubEdit = <?= ($allowEdit ? 'true' : 'false'); ?>;

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
	if (allowSubEdit && !!obEditSubBarCode && !!obSubBarCode)
	{
		if (obEditSubBarCode.checked)
		{
			if (confirm('<?= GetMessageJS("CAT_BARCODE_EDIT_CONFIRM"); ?>'))
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
	{
		if (tbl.rows[i].cells[0].colSpan == 1)
		{
			tbl.rows[i].cells[0].classList.add('field-name');
		}
	}
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
		<?php
		$aTabs1 = array();
		$aTabs1[] = array("DIV" => "subcat_edit1", "TAB" => GetMessage("C2IT_PRICES"), "TITLE" => GetMessage("C2IT_PRICES_D"));

		$aTabs1[] = array("DIV" => "subcat_edit3", "TAB" => GetMessage("C2IT_PARAMS"), "TITLE" => GetMessage("C2IT_PARAMS_D"));
		if($arCatalog["SUBSCRIPTION"] == "Y")
			$aTabs1[] = array("DIV" => "subcat_edit4", "TAB" => GetMessage("C2IT_GROUPS"), "TITLE" => GetMessage("C2IT_GROUPS_D"));
		$aTabs1[] = array("DIV" => "subcat_edit6", "TAB" => GetMessage("C2IT_DISCOUNTS"), "TITLE" => GetMessage("C2IT_DISCOUNTS_D"));
		if ($allowedShowQuantity)
		{
			$aTabs1[] = [
				"DIV" => "subcat_edit5",
				"TAB" => GetMessage("C2IT_STORE"),
				"TITLE" => GetMessage("C2IT_STORE_D")
			];

			if ($bUseStoreControl)
			{
				$aTabs1[] = [
					"DIV" => "subcat_edit7",
					"TAB" => GetMessage("C2IT_BAR_CODE"),
					"TITLE" => GetMessage("C2IT_BAR_CODE_D")
				];
			}
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
		else
		{
			$arPriceBoundaries[] = [
				'FROM' => false,
				'TO' => false
			];
		}

// prices tab
		$subtabControl1->BeginNextTab();
$arCatPricesExist = array(); // attr for exist prices for range
if ($enableQuantityRanges)
	$bUseExtendedPrice = $bVarsFromForm ? $subprice_useextform == 'Y' : $usedRanges;
else
	$bUseExtendedPrice = false;
if ($isCloud)
{
	$str_CAT_VAT_ID = (int)($bVarsFromForm
		? $SUBCAT_VAT_ID
		: ($arBaseProduct['VAT_ID'] == 0 ? $arCatalog['VAT_ID'] : $arBaseProduct['VAT_ID'])
	);
}
else
{
	$str_CAT_VAT_ID = (int)($bVarsFromForm ? $SUBCAT_VAT_ID : $arBaseProduct['VAT_ID']);
}
$vatList = [];
$iterator = Catalog\VatTable::getList([
	'select' => [
		'ID',
		'NAME',
		'SORT',
	],
	'filter' => [
		'=ACTIVE' => 'Y',
	],
	'order' => [
		'SORT' => 'ASC',
		'NAME' => 'ASC',
	]
]);
while ($row = $iterator->fetch())
{
	$vatList[$row['ID']] = $row['NAME'];
}
unset($row, $iterator);
if (!isset($vatList[$arCatalog['VAT_ID']]))
{
	$arCatalog['VAT_ID'] = 0;
}
if (!isset($vatList[$str_CAT_VAT_ID]))
{
	$str_CAT_VAT_ID = 0;
}
$str_CAT_VAT_INCLUDED = (string)($bVarsFromForm ? $SUBCAT_VAT_INCLUDED : $arBaseProduct['VAT_INCLUDED']);
if ($str_CAT_VAT_INCLUDED != 'Y' && $str_CAT_VAT_INCLUDED != 'N')
	$str_CAT_VAT_INCLUDED = (Main\Config\Option::get('catalog', 'default_product_vat_included') === 'Y' ? 'Y' : 'N');
		?>
<input type="hidden" name="subprice_useextform" id="subprice_useextform_N" value="N" />
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="subcatalog_vat_table">
<?php
if ($enableQuantityRanges || !empty($quantityRangesHelpLink))
{
	?>
	<tr>
		<td width="40%"><label for="subprice_useextform"><?= GetMessage('C2IT_PRICES_USEEXT'); ?>:</label></td>
		<td width="60%"><?php
		if ($enableQuantityRanges)
		{
			?><input type="checkbox" name="subprice_useextform" id="subprice_useextform" value="Y" onclick="toggleSubPriceType()" <?= $bUseExtendedPrice ? 'checked="checked"' : '' ?><?= $disablePrice; ?> /><?php
		}
		else
		{
			?><input type="hidden" name="subprice_useextform" value="N"><?php
			if ($quantityRangesHelpLink['TYPE'] == 'ONCLICK')
			{
				?><a href="#" onclick="<?=$quantityRangesHelpLink['LINK']; ?>"><?=GetMessage('C2IT_PRICES_EXT_TARIFF_ENABLE'); ?></a><?php
				Catalog\Config\Feature::initUiHelpScope();
			}
		}
		?></td>
	</tr>
	<?php
}
else
{
	?><input type="hidden" name="subprice_useextform" value="N"><?php
}
?>
	<tr>
		<td width="40%">
			<?php
			if (!$isCloud && (int)$arCatalog['VAT_ID'] !== 0)
			{
				$hintMessage = GetMessage(
					'CAT_VAT_ID_CATALOG_HINT',
					[
					'#VAT_NAME#' => $vatList[$arCatalog['VAT_ID']],
					]
				);
				?>
				<span id="hint_SUBCAT_VAT_ID"></span>
				<script type="text/javascript">
					BX.hint_replace(BX('hint_SUBCAT_VAT_ID'), '<?=\CUtil::JSEscape($hintMessage); ?>');
				</script>&nbsp;<?php
			}
			echo GetMessage("CAT_VAT")?>:
		</td>
		<td width="60%">
			<select name="SUBCAT_VAT_ID" id="SUBCAT_VAT_ID" <?= $disableProduct; ?>>
				<?php
				if (!$isCloud)
				{
				$vatSelected = ($str_CAT_VAT_ID === 0 ? ' selected' : '');
				?>
				<option value="0"<?=$vatSelected; ?>><?=htmlspecialcharsbx(GetMessage('CAT_VAT_ID_EMPTY')); ?></option>
				<?php
				}
				foreach ($vatList as $vatId => $vatName)
				{
					$vatSelected = ($str_CAT_VAT_ID === $vatId ? ' selected' : '');
					?><option value="<?=htmlspecialcharsbx($vatId); ?>"<?=$vatSelected; ?>><?=htmlspecialcharsbx($vatName); ?></option><?php
				}
				unset($vatList);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="SUBCAT_VAT_INCLUDED"><?= GetMessage("CAT_VAT_INCLUDED");?></label>:</td>
		<td width="60%">
			<input type="hidden" name="SUBCAT_VAT_INCLUDED" id="SUBCAT_VAT_INCLUDED_N" value="N">
			<input type="checkbox" name="SUBCAT_VAT_INCLUDED" id="SUBCAT_VAT_INCLUDED" value="Y" <?=$str_CAT_VAT_INCLUDED == 'Y' ? 'checked="checked"' : ''?><?= $disableProduct; ?> />
		</td>
	</tr>

	<?php
	if ($accessController->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW)):?>
		<tr id="tr_SUB_PURCHASING_PRICE">
			<?php
			$str_CAT_PURCHASING_PRICE = $bVarsFromForm ? $SUBCAT_PURCHASING_PRICE : $arBaseProduct['PURCHASING_PRICE'];
			?>
			<td width="40%"><?= GetMessage("C2IT_COST_PRICE_EXT")?></td>
			<td width="60%"><?php
				$isDisabled = (!$allowEdit || $bUseStoreControl)
					? ' disabled'
					: ''
				;
				?>
				<input type="hidden" id="SUBCAT_PURCHASING_PRICE_hidden" name="SUBCAT_PURCHASING_PRICE" value="<?= htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>">
				<input type="text"<?= $isDisabled; ?> id="SUBCAT_PURCHASING_PRICE" name="SUBCAT_PURCHASING_PRICE" value="<?= htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>" size="30">
				<input type="hidden" id="SUBCAT_PURCHASING_CURRENCY_hidden" name="SUBCAT_PURCHASING_CURRENCY" value="<?= htmlspecialcharsbx($arBaseProduct['PURCHASING_CURRENCY']) ?>"><?php
				echo CCurrency::SelectBox("SUBCAT_PURCHASING_CURRENCY", $arBaseProduct['PURCHASING_CURRENCY'], "", true, "", "id='SUBCAT_PURCHASING_CURRENCY' $isDisabled");?>
			</td>
		</tr>
	<?php
	endif;?>
	<tr id="tr_SUB_BASE_PRICE" style="display: <?= ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
		<td width="40%">
			<?php
			$arBaseGroup = Catalog\GroupTable::getBasePriceType();
			$arBasePrice = CPrice::GetBasePrice(
				$PRODUCT_ID,
				$arPriceBoundaries[0]["FROM"],
				$arPriceBoundaries[0]["TO"],
				false
			);
			echo GetMessage("BASE_PRICE")?> (<?= GetMessage('C2IT_PRICE_TYPE'); ?> "<?= htmlspecialcharsbx(!empty($arBaseGroup['NAME_LANG']) ? $arBaseGroup['NAME_LANG'] : $arBaseGroup["NAME"]); ?>"):
		</td>
		<td width="60%">
			<script type="text/javascript">
				var arExtra = [];
				var arExtraPrc = [];
				<?php
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
					if (!allowSubPriceEdit)
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
					if (!allowSubPriceEdit)
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
					if (!allowSubPriceEdit)
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
					if (!allowSubPriceEdit)
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
					if (!allowSubPriceEdit)
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
					if (!allowSubPriceEdit)
						return;

					BX('SUBCAT_BASE_CURRENCY_0').selectedIndex = BX('SUBCAT_BASE_CURRENCY').selectedIndex;
				}

				function ChangeSubPrice(codID)
				{
					if (!allowSubPriceEdit)
						return;

					var e_price = BX('SUBCAT_PRICE_' + codID + '_0');
					e_price.value = BX('SUBCAT_PRICE_' + codID).value;
					OnChangeSubPriceExist();
					OnChangeSubPriceExistEx(e_price);
				}

				function ChangeSubCurrency(codID)
				{
					if (!allowSubPriceEdit)
						return;

					var e_currency = BX('SUBCAT_CURRENCY_' + codID + "_0");
					e_currency.selectedIndex = BX('SUBCAT_CURRENCY_' + codID).selectedIndex;
				}

				function OnChangeSubPriceExist()
				{
					if (!allowSubPriceEdit)
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
			<?php
			$boolBaseExistPrice = false;
			$str_CAT_BASE_PRICE = "";
			if ($arBasePrice)
				$str_CAT_BASE_PRICE = $arBasePrice["PRICE"];
			if ($bVarsFromForm)
				$str_CAT_BASE_PRICE = $SUBCAT_BASE_PRICE;
			if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
				$boolBaseExistPrice = true;
			?>
			<input type="text"<?= $disablePrice; ?> id="SUBCAT_BASE_PRICE" name="SUBCAT_BASE_PRICE" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="30" OnBlur="ChangeSubBasePrice(this)">
		</td>
	</tr>
	<tr id="tr_SUB_BASE_CURRENCY" style="display: <?= ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
		<td width="40%">
			<?= GetMessage("BASE_CURRENCY")?>:
		</td>
		<td width="60%">
			<?php
			$str_CAT_BASE_CURRENCY = '';
			if ($arBasePrice)
				$str_CAT_BASE_CURRENCY = $arBasePrice["CURRENCY"];
			if ($bVarsFromForm)
				$str_CAT_BASE_CURRENCY = $SUBCAT_BASE_CURRENCY;

			?>
			<select id="SUBCAT_BASE_CURRENCY" name="SUBCAT_BASE_CURRENCY"<?= $disablePrice; ?> OnChange="ChangeSubBaseCurrency()">
				<?php
				foreach ($currencyList as &$currency)
				{
					?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ? " selected" : '');?>><?= $currency["FULL_NAME"]; ?></option><?php
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
<?php
// simple price form
?>
<div id="subprices_simple" style="display: <?=$bUseExtendedPrice ? 'none' : 'block'?>;">
		<?php
		$intCount = count($arPriceBoundariesError);
		if ($intCount > 0)
		{
			?>
			<font class="errortext">
			<?= GetMessage("C2IT_BOUND_WRONG")?><br>
			<?php
			for ($i = 0; $i < $intCount; $i++)
			{
				echo $arPriceBoundariesError[$i]."<br>";
			}
			?>
			<?= GetMessage("C2IT_BOUND_RECOUNT")?>
			</font>
			<?php
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
					<td><?= GetMessage("PRICE_TYPE"); ?></td>
					<td><?= GetMessage("PRICE_EXTRA"); ?></td>
					<td><?= GetMessage("PRICE_SUM"); ?></td>
					<td><?= GetMessage("PRICE_CURRENCY"); ?></td>
				</tr>
				<?php
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
			<tr>
				<td valign="top" align="left">
					<?= htmlspecialcharsbx(!empty($arCatalogGroup["NAME_LANG"]) ? $arCatalogGroup["NAME_LANG"] : $arCatalogGroup["NAME"]); ?>
					<?php
					if ($arPrice):
					?>
					<input type="hidden" name="SUBCAT_ID_<?= $arCatalogGroup["ID"] ?>" value="<?= $arPrice["ID"] ?>">
					<?php
					endif;
					?>
				</td>
				<td valign="top" align="center">
					<?php
					echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"], $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtra(".$arCatalogGroup["ID"].")", $disablePrice.' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"].'" ');
					?>
				</td>
				<td valign="top" align="center">
					<input type="text"<?= $disablePrice; ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE) ?>" size="8" OnChange="ChangeSubPrice(<?= $arCatalogGroup["ID"] ?>)">
				</td>
				<td valign="top" align="center">
					<?php
					echo CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"], $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrency(".$arCatalogGroup["ID"].")", $disablePrice.' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"].'" ')
					?>
					<script type="text/javascript">
						ChangeSubExtra(<?= $arCatalogGroup["ID"] ?>);
					</script>
				</td>
			</tr>
			<?php
		}// endwhile
		if (!$bFirst) echo "</table>";
	}
		?><input type="hidden" name="SUBCAT_PRICE_EXIST" id="SUBCAT_PRICE_EXIST" value="<?= ($boolBaseExistPrice == true ? 'Y' : 'N'); ?>">
</div>
		<?php
		//$subtabControl1->BeginNextTab();
// extended price form
		?>
<div id="subprices_ext" style="display: <?=$bUseExtendedPrice ? 'block' : 'none'?>;">
<script type="text/javascript">
function CloneSubBasePriceGroup()
{
	if (!allowSubPriceEdit)
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
	oCell.innerHTML = '<input type="text" <?= $disablePrice; ?> name="SUBCAT_BASE_QUANTITY_FROM_'+cnt+'" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?= $disablePrice; ?> name="SUBCAT_BASE_QUANTITY_TO_'+cnt+'" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?= $disablePrice; ?> id="SUBCAT_BASE_PRICE_'+cnt+'" name="SUBCAT_BASE_PRICE_'+cnt+'" value="" size="15" OnBlur="ChangeSubBasePriceEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="SUBCAT_BASE_CURRENCY_'+cnt+'" name="SUBCAT_BASE_CURRENCY_'+cnt+'" <?= $disablePrice; ?> OnChange="ChangeSubBaseCurrencyEx(this)">';
	<?php
	foreach ($currencyList as &$currency)
	{
		?>str += '<option value="<?= $currency["CURRENCY"] ?>"><?= $currency["FULL_NAME_JS"]; ?></option>';<?php
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
	if (!allowSubPriceEdit)
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
	str += '<select id="SUBCAT_EXTRA_'+ind+'_'+cnt+'" name="SUBCAT_EXTRA_'+ind+'_'+cnt+'" OnChange="ChangeSubExtraEx(this)" <?= $disablePrice; ?>>';
	str += '<option value=""><?= GetMessage("VAL_NOT_SET") ?></option>';
	<?php
	foreach ($arExtraList as $arOneExtra)
	{
		?>
		str += '<option value="<?= $arOneExtra["ID"] ?>"><?= CUtil::JSEscape(htmlspecialcharsbx($arOneExtra["NAME"]))." (".htmlspecialcharsbx($arOneExtra["PERCENTAGE"])."%)" ?></option>';
		<?php
	}
	?>
	str += '</select>';
	oCell.innerHTML = str;

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" <?= $disablePrice; ?> id="SUBCAT_PRICE_'+ind+'_'+cnt+'" name="SUBCAT_PRICE_'+ind+'_'+cnt+'" value="" size="10" OnChange="ptSubPriceChangeEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="SUBCAT_CURRENCY_'+ind+'_'+cnt+'" name="SUBCAT_CURRENCY_'+ind+'_'+cnt+'" OnChange="ChangeSubCurrencyEx(this)" <?= $disablePrice; ?>>';
	str += '<option value=""><?= GetMessage("VAL_BASE") ?></option>';
	<?php
	foreach ($currencyList as &$currency)
	{
		?>str += '<option value="<?= $currency["CURRENCY"] ?>"><?= $currency["FULL_NAME_JS"]; ?></option>';<?php
	}
	unset($currency);
	?>
	str += '</select>';
	oCell.innerHTML = str;

	oCntr.value = cnt;
}

function CloneSubPriceSections()
{
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
	if (!allowSubPriceEdit)
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
		<?php
		$intCount = count($arPriceBoundariesError);
		if ($intCount > 0)
		{
			?>
			<font class="errortext">
			<?= GetMessage("C2IT_BOUND_WRONG")?><br>
			<?php
			for ($i = 0; $i < $intCount; $i++)
			{
				echo $arPriceBoundariesError[$i]."<br>";
			}
			?>
			<?= GetMessage("C2IT_BOUND_RECOUNT")?>
			</font>
			<?php
		}
		$boolExistPrice = false;
		?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
			<tr>
				<td valign="top" align="right">
				<?php
					echo GetMessage("BASE_PRICE")?> (<?= GetMessage('C2IT_PRICE_TYPE'); ?> "<?= htmlspecialcharsbx(!empty($arBaseGroup['NAME_LANG']) ? $arBaseGroup['NAME_LANG'] : $arBaseGroup["NAME"]); ?>"):
				</td>
				<td valign="top" align="left">
					<table border="0" cellspacing="1" cellpadding="3" id="SUBBASE_PRICE_GROUP_TABLE">
						<thead>
						<tr>
							<td align="center"><?= GetMessage("C2IT_FROM")?></td>
							<td align="center"><?= GetMessage("C2IT_TO")?></td>
							<td align="center"><?= GetMessage("C2IT_PRICE")?></td>
							<td align="center"><?= GetMessage("C2IT_CURRENCY")?></td>
						</tr>
						</thead>
						<tbody id="subcontainer3">
							<?php
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
										<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
										<input type="hidden" name="SUBCAT_BASE_ID[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_BASE_ID) ?>">
									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeSubBasePriceEx(this)">
									</td>
									<td valign="top" align="center">
										<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?= $allowEditPrices; ?> OnChange="ChangeSubBaseCurrencyEx(this)">
											<?php
											foreach ($currencyList as &$currency)
											{
												?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ? " selected" : '');?>><?= $currency["FULL_NAME"];?></option><?php
											}
											unset($currency);
											?>
										</select>
									</td>
								</tr>
								<?php
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
											<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
											<input type="hidden" name="SUBCAT_BASE_ID[<?= $ind ?>]" value="<?= 0 ?>">
										</td>
										<td valign="top" align="center">
											<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
										</td>
										<td valign="top" align="center">
											<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeSubBasePriceEx(this)">
										</td>
										<td valign="top" align="center">
											<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?= $allowEditPrices; ?> OnChange="ChangeSubBaseCurrencyEx(this)">
												<?php
												foreach ($currencyList as &$currency)
												{
													?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ? " selected" : '');?>><?= $currency["FULL_NAME"];?></option><?php
												}
												unset($currency);
												?>
											</select>
										</td>
									</tr>
									<?php
								}
							}
							if ($ind == -1)
							{
								$ind++;
								?>
								<tr id="submodel3">
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> name="SUBCAT_BASE_QUANTITY_TO_<?= $ind ?>" value="" size="3" OnChange="ChangeSubBaseQuantityEx(this)">
									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_BASE_PRICE_<?= $ind ?>" name="SUBCAT_BASE_PRICE_<?= $ind ?>" value="" size="15" OnBlur="ChangeSubBasePriceEx(this)">
									</td>
									<td valign="top" align="center">
										<select id="SUBCAT_BASE_CURRENCY_<?= $ind ?>" name="SUBCAT_BASE_CURRENCY_<?= $ind ?>" <?= $allowEditPrices; ?> OnChange="ChangeSubBaseCurrencyEx(this)">
											<?php
											foreach ($currencyList as &$currency)
											{
												?><option value="<?= $currency["CURRENCY"]; ?>"><?= $currency["FULL_NAME"];?></option><?php
											}
											unset($currency);
											?>
										</select>
									</td>
								</tr>
								<?php
								$arCatPricesExist[$ind][$arBaseGroup['ID']] = 'N';
							}
							?>
						</tbody>
					</table>
					<input type="hidden" name="SUBCAT_ROW_COUNTER" id="SUBCAT_ROW_COUNTER" value="<?= $ind ?>">
					<input type="button" value="<?= GetMessage("C2IT_MORE")?>" OnClick="CloneSubPriceSections()">
				</td>
			</tr>
			<script type="text/javascript">
			arCatalogGroups = new Array();
			catalogGroupsInd = 0;
			</script>
			<?php
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
						<?= GetMessage("C2IT_PRICE_TYPE")?> "<?= htmlspecialcharsbx(!empty($arCatalogGroup["NAME_LANG"]) ? $arCatalogGroup["NAME_LANG"] : $arCatalogGroup["NAME"]); ?>":
					</td>
					<td valign="top" align="left">
						<table border="0" cellspacing="1" cellpadding="3" id="SUBOTHER_PRICE_GROUP_TABLE_<?= $arCatalogGroup["ID"] ?>">
							<thead>
							<tr>
							<td align="center"><?= GetMessage("C2IT_FROM")?></td>
							<td align="center"><?= GetMessage("C2IT_TO")?></td>
							<td align="center"><?= GetMessage("C2IT_NAC_TYPE")?></td>
							<td align="center"><?= GetMessage("C2IT_PRICE")?></td>
							<td align="center"><?= GetMessage("C2IT_CURRENCY")?></td>
							</tr>
							</thead>
							<tbody id="subcontainer3_<?= $arCatalogGroup["ID"] ?>">
							<?php
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
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
										<input type="hidden" name="SUBCAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_ID) ?>">
									</td>
									<td valign="top" align="center">
										<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

									</td>
									<td valign="top" align="center">
										<?php
										echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", $allowEditPrices.' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
										?>

									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE) ?>" size="10" OnChange="ptSubPriceChangeEx(this)">

									</td>
									<td valign="top" align="center">

											<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", $allowEditPrices.' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
											<script type="text/javascript">
												jsUtils.addEvent(window, 'load', function() {ChangeSubExtraEx(BX('SUBCAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
											</script>

									</td>
								</tr>
								<?php
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
											<input type="text" disabled readonly id="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
											<input type="hidden" name="SUBCAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= 0 ?>">
										</td>
										<td valign="top" align="center">
											<input type="text" disabled readonly id="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

										</td>
										<td valign="top" align="center">
											<?php
											echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", $allowEditPrices.' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
											?>

										</td>
										<td valign="top" align="center">
											<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE) ?>" size="10" OnChange="ptSubPriceChangeEx(this)">

										</td>
										<td valign="top" align="center">

												<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", $allowEditPrices.' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
												<script type="text/javascript">
													jsUtils.addEvent(window, 'load', function () {ChangeSubExtraEx(BX('SUBCAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
												</script>

										</td>
									</tr>
									<?php
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
										<?php
										echo CExtra::SelectBox("SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_NOT_SET"), "ChangeSubExtraEx(this)", $allowEditPrices.' id="'."SUBCAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
										?>

									</td>
									<td valign="top" align="center">
										<input type="text" <?= $allowEditPrices; ?> id="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="SUBCAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="10" OnChange="ptSubPriceChangeEx(this)">

									</td>
									<td valign="top" align="center">

											<?= CCurrency::SelectBox("SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_BASE"), true, "ChangeSubCurrencyEx(this)", $allowEditPrices.' id="'."SUBCAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>

									</td>
								</tr>
								<?php
								$arCatPricesExist[$ind][$arCatalogGroup['ID']] = 'N';
							}
							?>
							</tbody>
						</table>
						<input type="hidden" name="SUBCAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" id="SUBCAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" value="<?= $ind ?>">
					</td>
				</tr>
				<?php
			}
	}
			?>
		</table>
		<div id="ext_subprice_exist">
		<?php
		foreach ($arCatPricesExist as $ind => $arPriceExist)
		{
			$strExist = (in_array('Y',$arPriceExist) ? 'Y' : 'N');
			?>
			<input type="hidden" name="SUBCAT_PRICE_EXIST_<?= $ind; ?>" id="SUBCAT_PRICE_EXIST_<?= $ind; ?>" value="<?= $strExist; ?>"><?php
		}
		?>
		</div>
</div>
		<?php
		$subtabControl1->BeginNextTab();
		?>

		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="subcatalog_properties_table"><?php
			if ($allowedShowQuantity)
			{
				$currentQuantity = [
					'QUANTITY' => $arBaseProduct['QUANTITY'],
					'QUANTITY_RESERVED' => $arBaseProduct['QUANTITY_RESERVED'],
				];
				if (\CCatalogAdminTools::needSummaryStoreAmountByPermissions())
				{
					$storeQuantity = \CCatalogAdminTools::getSummaryStoreAmountByPermissions([$PRODUCT_ID]);
					$currentQuantity = $storeQuantity[$PRODUCT_ID] ?? [
							'QUANTITY' => '',
							'QUANTITY_RESERVED' => '',
						];
					unset($storeQuantity);
				}
				else
				{
					if (!$bUseStoreControl && $bVarsFromForm)
					{
						if (isset($_POST['SUBCAT_BASE_QUANTITY']) && is_string($_POST['SUBCAT_BASE_QUANTITY']))
						{
							$currentQuantity['QUANTITY'] = $_POST['SUBCAT_BASE_QUANTITY'];
						}
						if (isset($_POST['SUBCAT_BASE_QUANTITY_RESERVED']) && is_string($_POST['SUBCAT_BASE_QUANTITY_RESERVED']))
						{
							$currentQuantity['QUANTITY_RESERVED'] = $_POST['SUBCAT_BASE_QUANTITY_RESERVED'];
						}
					}
				}
				?>
				<tr>
				<td width="40%">
					<?= GetMessage("FULL_QUANTITY")?>:
				</td>
				<td width="60%">
					<input type="text" name="SUBCAT_BASE_QUANTITY" <?= $disableQuantityFields; ?> value="<?= htmlspecialcharsbx($currentQuantity['QUANTITY']); ?>" size="30">
				</td>
				</tr><?php
				if ($bEnableReservation)
				{
					?>
					<tr id="SUBCAT_BASE_QUANTITY_RESERV">
						<td width="40%">
							<?= GetMessage("BASE_QUANTITY_RESERVED")?>:
						</td>
						<td width="60%">
							<input type="text" id="SUBCAT_BASE_QUANTITY_RESERVED" name="SUBCAT_BASE_QUANTITY_RESERVED" <?= $disableQuantityFields; ?>  onfocus="ShowNotice()" onblur="HideNotice()" value="<?= htmlspecialcharsbx($currentQuantity['QUANTITY_RESERVED']); ?>" size="30">
							<span id="CAT_QUANTITY_RESERVED_DIV" style="color: #af2d49; margin-left: 10px; display: none;">	<?= GetMessage("QUANTITY_RESERVED_NOTICE")?></span>
						</td>
					</tr><?php
				}
				unset($currentQuantity);
			}
			?>
			<tr>
				<td width="40%">
					<?= GetMessage("C2IT_MEASURE")?>:
				</td>
				<td width="60%">
					<?php
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
					<?php
					if(!empty($arAllMeasure)):
					?>
						<select style="max-width:220px" id="SUBCAT_MEASURE" name="SUBCAT_MEASURE"<?= $disableProduct; ?>>
							<?php
							foreach($arAllMeasure as $arMeasure):
							?>
								<option <?= ($str_CAT_MEASURE == $arMeasure["ID"] || ($str_CAT_MEASURE == '' && $arMeasure["IS_DEFAULT"] == 'Y') ? " selected" : '');?> value="<?=$arMeasure["ID"]?>"><?=htmlspecialcharsbx($arMeasure["MEASURE_TITLE"])?></option>
							<?php
							endforeach;
							unset($arMeasure);
							?>
						</select>
					<?php
					else:
						$measureListUrl = $selfFolderUrl.'cat_measure_list.php?lang='.LANGUAGE_ID;
						$measureListUrl = $adminSidePanelHelper->editUrlToPublicPage($measureListUrl);
						echo GetMessage("C2IT_MEASURE_NO_MEASURE"); ?> <a target="_top" href="<?=$measureListUrl?>"><?=GetMessage("C2IT_MEASURES"); ?></a><br>
					<?php
					endif;
					?>
				</td>
			</tr>
			<?php
			if(!empty($arAllMeasure)):
			?>
				<tr>
					<td width="40%">
						<?= GetMessage("C2IT_MEASURE_RATIO")?>:
					</td>
					<td width="60%">
						<?php
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
						<input type="text"<?= $disableProduct; ?> id="SUBCAT_MEASURE_RATIO" name="SUBCAT_MEASURE_RATIO" value="<?= htmlspecialcharsbx($str_CAT_MEASURE_RATIO) ?>" size="30">
						<input type="hidden" id="SUBCAT_MEASURE_RATIO_ID" name="SUBCAT_MEASURE_RATIO_ID" value="<?= htmlspecialcharsbx($SUBCAT_MEASURE_RATIO_ID) ?>">
					</td>
				</tr>
			<?php
			endif;
			?>
			<tr class="heading">
				<td colspan="2">
					<?= GetMessage("C2IT_PARAMS")?>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("ENABLE_STORE_TRACE")?>:
				</td>
				<td width="60%">
					<?php
					$str_CAT_BASE_QUANTITY_TRACE = $arBaseProduct["QUANTITY_TRACE_ORIG"];
					if ($bVarsFromForm) $str_CAT_BASE_QUANTITY_TRACE = $SUBCAT_BASE_QUANTITY_TRACE;
					?>
					<select id="SUBCAT_BASE_QUANTITY_TRACE" name="SUBCAT_BASE_QUANTITY_TRACE"<?= $disableProduct; ?>>
						<option value="D" <?= ("D"==$str_CAT_BASE_QUANTITY_TRACE ? " selected" : '');?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?= $availQuantityTrace=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>) </option>
						<option value="Y" <?= ("Y"==$str_CAT_BASE_QUANTITY_TRACE ? " selected" : '');?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?= ("N"==$str_CAT_BASE_QUANTITY_TRACE ? " selected" : '');?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("C2IT_CAN_BUY_NULL_EXT")?>:
				</td>
				<td width="60%">
					<?php
					$str_CAT_BASE_CAN_BUY_ZERO = $arBaseProduct["CAN_BUY_ZERO_ORIG"];
					if ($bVarsFromForm) $str_CAT_BASE_CAN_BUY_ZERO = $SUBUSE_STORE;
					?>
					<select id="SUBUSE_STORE" name="SUBUSE_STORE" <?= $disableProduct; ?>>
						<option value="D" <?= ("D"==$str_CAT_BASE_CAN_BUY_ZERO ? " selected" : '');?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?= $availCanBuyZero=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>) </option>
						<option value="Y" <?= ("Y"==$str_CAT_BASE_CAN_BUY_ZERO ? " selected" : '');?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?= ("N"==$str_CAT_BASE_CAN_BUY_ZERO ? " selected" : '');?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%"><?= GetMessage("C2IT_SUBSCRIBE"); ?>:</td>
				<td width="60%">
					<?php
					$str_CAT_SUBSCRIBE = $arBaseProduct["SUBSCRIBE_ORIG"];
					if ($bVarsFromForm) $str_CAT_SUBSCRIBE = $SUBSUBSCRIBE;
					?>
					<select id="SUBSUBSCRIBE" name="SUBSUBSCRIBE"<?= $disableProduct; ?>>
						<option value="D" <?= ("D"==$str_CAT_SUBSCRIBE ? " selected" : '');?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?= 'Y' == $strGlobalSubscribe ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>)</option>
						<option value="Y" <?= ("Y"==$str_CAT_SUBSCRIBE ? " selected" : '');?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
						<option value="N" <?= ("N"==$str_CAT_SUBSCRIBE ? " selected" : '');?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
					</select>
				</td>
			</tr>
			<tr class="heading">
				<td colspan="2">
					<?= GetMessage("C2IT_MEASUREMENTS_EXT")?>
				</td>
			</tr>
			<tr>
				<td>
					<?= GetMessage("BASE_WEIGHT")?>:
				</td>
				<td>
					<?php
					$str_CAT_BASE_WEIGHT = $arBaseProduct["WEIGHT"];
					if ($bVarsFromForm) $str_CAT_BASE_WEIGHT = $SUBCAT_BASE_WEIGHT;
					?>
					<input type="text"<?= $disableProduct; ?> name="SUBCAT_BASE_WEIGHT" value="<?= htmlspecialcharsbx($str_CAT_BASE_WEIGHT) ?>" size="30">

				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("C2IT_BASE_LENGTH")?>:
				</td>
				<td width="60%">
					<?php
					$str_CAT_BASE_LENGTH = $arBaseProduct["LENGTH"];
					if ($bVarsFromForm) $str_CAT_BASE_LENGTH = $SUBCAT_BASE_LENGTH;
					?>
					<input type="text"<?= $disableProduct; ?> id="SUBCAT_BASE_LENGTH" name="SUBCAT_BASE_LENGTH" value="<?= htmlspecialcharsbx($str_CAT_BASE_LENGTH) ?>" size="30">
				</td>
			</tr>

			<tr>
				<td width="40%">
					<?= GetMessage("C2IT_BASE_WIDTH")?>:
				</td>
				<td width="60%">
					<?php
					$str_CAT_BASE_WIDTH = $arBaseProduct["WIDTH"];
					if ($bVarsFromForm) $str_CAT_BASE_WIDTH = $SUBCAT_BASE_WIDTH;
					?>
					<input type="text"<?= $disableProduct; ?> id="SUBCAT_BASE_WIDTH" name="SUBCAT_BASE_WIDTH" value="<?= htmlspecialcharsbx($str_CAT_BASE_WIDTH) ?>" size="30">
				</td>
			</tr>

			<tr>
				<td width="40%">
					<?= GetMessage("C2IT_BASE_HEIGHT")?>:
				</td>
				<td width="60%">
					<?php
					$str_CAT_BASE_HEIGHT = $arBaseProduct["HEIGHT"];
					if ($bVarsFromForm) $str_CAT_BASE_HEIGHT = $SUBCAT_BASE_HEIGHT;
					?>
					<input type="text"<?= $disableProduct; ?> id="SUBCAT_BASE_HEIGHT" name="SUBCAT_BASE_HEIGHT" value="<?= htmlspecialcharsbx($str_CAT_BASE_HEIGHT) ?>" size="30">
				</td>
			</tr>
			<?php
			if ($arCatalog["SUBSCRIPTION"]=="Y")
			{
				?>
				<tr class="heading">
					<td colspan="2"><?= GetMessage("C2IT_SUBSCR_PARAMS")?></td>
				</tr>
				<tr>
					<td>
						<?= GetMessage("C2IT_PAY_TYPE")?>
					</td>
					<td>
						<script type="text/javascript">
						function ChangeSubPriceType()
						{
							if (!allowSubPriceEdit)
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

						<?php
						$str_CAT_PRICE_TYPE = $arBaseProduct["PRICE_TYPE"];
						if ($bVarsFromForm) $str_CAT_PRICE_TYPE = $SUBCAT_PRICE_TYPE;
						?>
						<select id="SUBCAT_PRICE_TYPE" name="SUBCAT_PRICE_TYPE"<?= $disableProduct; ?> OnChange="ChangeSubPriceType()">
							<option value="S"<?= ($str_CAT_PRICE_TYPE=="S" ? " selected" : '');?>><?= GetMessage("C2IT_SINGLE")?></option>
							<option value="R"<?= ($str_CAT_PRICE_TYPE=="R" ? " selected" : '');?>><?= GetMessage("C2IT_REGULAR")?></option>
							<option value="T"<?= ($str_CAT_PRICE_TYPE=="T" ? " selected" : '');?>><?= GetMessage("C2IT_TRIAL")?></option>
						</select>

					</td>
				</tr>
				<tr>
					<td>
						<?= GetMessage("C2IT_PERIOD_LENGTH")?>
					</td>
					<td>

						<?php
						$str_CAT_RECUR_SCHEME_LENGTH = $arBaseProduct["RECUR_SCHEME_LENGTH"];
						if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_LENGTH = $SUBCAT_RECUR_SCHEME_LENGTH;
						?>
						<input type="text"<?= $disableProduct; ?> id="SUBCAT_RECUR_SCHEME_LENGTH" name="SUBCAT_RECUR_SCHEME_LENGTH" value="<?= htmlspecialcharsbx($str_CAT_RECUR_SCHEME_LENGTH) ?>" size="10">

					</td>
				</tr>
				<tr>
					<td>
						<?= GetMessage("C2IT_PERIOD_TIME")?>
					</td>
					<td>
						<?php
						$str_CAT_RECUR_SCHEME_TYPE = $arBaseProduct["RECUR_SCHEME_TYPE"];
						if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_TYPE = $SUBCAT_RECUR_SCHEME_TYPE;
						?>
						<select id="SUBCAT_RECUR_SCHEME_TYPE" name="SUBCAT_RECUR_SCHEME_TYPE"<?= $disableProduct; ?>>
							<?php
							foreach ($periodTimeTypes as $key => $value)
							{
								?><option value="<?= $key ?>"<?= ($str_CAT_RECUR_SCHEME_TYPE==$key ? " selected" : '');?>><?= $value ?></option><?php
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?= GetMessage("C2IT_TRIAL_FOR")?>
					</td>
					<td>

						<?php
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
						<input<?= $disableProduct; ?> id="SUBCAT_TRIAL_PRICE_ID" name="SUBCAT_TRIAL_PRICE_ID" value="<?= htmlspecialcharsbx($str_CAT_TRIAL_PRICE_ID) ?>" size="5" type="text"><input type="button"<?= $disableProduct; ?> id="SUBCAT_TRIAL_PRICE_ID_BUTTON" name="SUBCAT_TRIAL_PRICE_ID_BUTTON" value="..." onClick="window.open('cat_product_search.php?IBLOCK_ID=<?= $IBLOCK_ID ?>&amp;field_name=SUBCAT_TRIAL_PRICE_ID&amp;alt_name=subtrial_price_alt&amp;form_name='+getElementSubFormName(), '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));">&nbsp;<span id="subtrial_price_alt"><?= $catProductName ?></span>

					</td>
				</tr>
				<tr>
					<td>
						<?= GetMessage("C2IT_WITHOUT_ORDER")?>
					</td>
					<td>
						<?php
						$str_CAT_WITHOUT_ORDER = $arBaseProduct["WITHOUT_ORDER"];
						if ($bVarsFromForm) $str_CAT_WITHOUT_ORDER = $SUBCAT_WITHOUT_ORDER;
						?>
						<input type="checkbox"<?= $disableProduct; ?> name="SUBCAT_WITHOUT_ORDER" value="Y" <?= ($str_CAT_WITHOUT_ORDER=="Y" ?  "checked" : '');?>>

					</td>
				</tr>
				<?php
			}

			$productUserFieldsHtml = \CCatalogAdminTools::getAllProductFieldsHtml(
				[
					'ID' => $ID,
					'PRODUCT_ID' => $PRODUCT_ID,
					'IBLOCK_ID' => $IBLOCK_ID,
					'TYPE' => $arBaseProduct['TYPE'],
				],
				[
					'FROM_FORM' => $bVarsFromForm,
					'ALLOW_EDIT' => $allowEdit,
				]
			);
			if ($productUserFieldsHtml[0] !== '')
			{
				?><tr class="heading">
				<td colspan="2"><?=GetMessage("C2IT_UF_SYSTEM_FIELDS"); ?></td>
				</tr><?php
				echo $productUserFieldsHtml[0];
			}
			if ($productUserFieldsHtml[1] !== '')
			{
				?><tr class="heading">
				<td colspan="2"><?= GetMessage("C2IT_UF_FIELDS")?></td>
				</tr><?php
				echo $productUserFieldsHtml[1];
			}
			unset($productUserFieldsHtml);
			?>
		</table>
<script type="text/javascript">
SetSubFieldsStyle('subcatalog_properties_table');
</script>
		<?php
		if ($arCatalog["SUBSCRIPTION"]=="Y"):?>
			<script type="text/javascript">
			ChangeSubPriceType();
			</script>
		<?php
		endif;

		if ($arCatalog["SUBSCRIPTION"]=="Y"):

			$subtabControl1->BeginNextTab();
			?>

			<script type="text/javascript">
			function SubCatGroupsActivate(obj, id)
			{
				if (!allowSubEdit)
					return;

				var ed = BX('SUBCAT_ACCESS_LENGTH_' + id);
				var ed1 = BX('SUBCAT_ACCESS_LENGTH_TYPE_' + id);
				ed.disabled = !obj.checked;
				ed.readOnly = !obj.checked;
				ed1.disabled = !obj.checked;
			}
			</script>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
				<tr class="heading">
					<td><?= GetMessage("C2IT_VKL")?></td>
					<td><?= GetMessage("C2IT_USERS_GROUP")?></td>
					<td><?= GetMessage("C2IT_ACTIVE_TIME")?> <sup>1)</sup></td>
				</tr>
				<?php
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
						"c_sort",
						"asc",
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
					$usedProductGroup = isset($arCurProductGroups[$arGroup['ID']]);
					if ($usedProductGroup)
					{
						$accessLenghtValue = $arCurProductGroups[$arGroup["ID"]][0];
						$accessLenghtType = $arCurProductGroups[$arGroup["ID"]][1];
						$accessLenghtDisable = '';
					}
					else
					{
						$accessLenghtValue = '';
						$accessLenghtType = '';
						$accessLenghtDisable = $disableFieldAttributes;
					}
					if (!$allowEdit)
					{
						$accessLenghtDisable = $disableFieldAttributes;
					}
					?>
					<tr>
						<td align="center">
							<input type="checkbox" name="SUBCAT_USER_GROUP_ID_<?= $arGroup["ID"] ?>" value="Y"<?= ($usedProductGroup ? " checked" : '');?> onclick="SubCatGroupsActivate(this, <?= $arGroup["ID"] ?>)"<?= $disableProduct; ?>>
						</td>
						<td align="left"><?= htmlspecialcharsbx($arGroup["NAME"]); ?></td>
						<td align="center">
							<input type="text" id="SUBCAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" name="SUBCAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" size="5" <?php
								echo 'value="' . htmlspecialcharsbx($accessLenghtValue) . '" ';
								echo $accessLenghtDisable;
							?>>
							<select id="SUBCAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>" name="SUBCAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>"<?= $accessLenghtDisable;?>>
								<?php
								foreach ($periodTimeTypes as $key => $value)
								{
									?><option value="<?= $key ?>"<?= ($accessLenghtType == $key ?  ' selected' : ''); ?>><?= $value ?></option><?php
								}
								?>
							</select>
						</td>
					</tr>
					<?php
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
					<?php
				}
				?>
			</table>
			<br><b>1)</b> <?= GetMessage("C2IT_ZERO_HINT")?>
		<?php
		endif;
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
			?><b><?= GetMessage("C2IT_NO_ACTIVE_DISCOUNTS")?></b><br><?php
		}
		else
		{
			$showDiscountUrl = $bDiscount;
			$discountUrl = $selfFolderUrl.'cat_discount_edit.php?ID=';
			if (Main\ModuleManager::isModuleInstalled('sale') && Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y')
			{
				$showDiscountUrl = ($APPLICATION->GetGroupRight('sale') >= 'W');
				$discountUrl = $selfFolderUrl.'sale_discount_edit.php?ID=';
			}
			?><table border="0" cellspacing="0" cellpadding="0" class="internal" align="center" width="100%">
				<tr class="heading">
					<td>ID</td>
					<td><?= GetMessage("C2IT_SITE")?></td>
					<td><?= GetMessage("C2IT_ACTIVITY")?></td>
					<td><?= GetMessage("C2IT_NAME")?></td>
					<td><?= GetMessage("C2IT_AMOUNT")?></td>
					<?php
					if ($showDiscountUrl)
					{
					?><td><?= GetMessage("C2IT_ACTIONS")?></td><?php
					}
					?>
				</tr><?php
			foreach ($arDiscountList as $arProductDiscounts)
			{
					$boolWork = true;
				?><tr>
						<td style="text-align: right;"><?= $arProductDiscounts["ID"] ?></td>
						<td style="text-align: center;"><?= $arProductDiscounts["SITE_ID"] ?></td>
						<td style="text-align: center;"><?= GetMessage("C2IT_YES")?></td>
						<td style="text-align: left;"><?= htmlspecialcharsbx($arProductDiscounts["NAME"]) ?></td>
						<td style="text-align: right;">
							<?php
							if ($arProductDiscounts["VALUE_TYPE"]=="P")
							{
								echo $arProductDiscounts["VALUE"]."%";
							}
							elseif ($arProductDiscounts["VALUE_TYPE"]=="S")
							{
								?>= <?= CCurrencyLang::CurrencyFormat($arProductDiscounts["VALUE"], $arProductDiscounts["CURRENCY"], true);
							}
							else
							{
								echo CCurrencyLang::CurrencyFormat($arProductDiscounts["VALUE"], $arProductDiscounts["CURRENCY"], true);
							}
							?>
						</td>
						<?php
						if ($showDiscountUrl)
						{
						?>
						<td style="text-align: center;">
							<a href="<?=$discountUrl.$arProductDiscounts["ID"] ?>&lang=<?=LANGUAGE_ID; ?>#tb" target="_blank"><?= GetMessage("C2IT_MODIFY")?></a>
						</td>
						<?php
						}
						?>
					</tr>
					<?php
			}
			?></table><?php
		}
		?>
		<br>
		<?= GetMessage("C2IT_DISCOUNT_HINT");
		if ($allowedShowQuantity)
		{
	$subtabControl1->BeginNextTab();

		$showStoreReserve = Catalog\Config\State::isShowedStoreReserve();
		$stores = array();
		$storeLink = array();
		$storeCount = 0;
		$permissionFilter = [];
		if (Loader::includeModule('crm'))
		{
			$permissionFilter = $accessController->getEntityFilter(
				ActionDictionary::ACTION_STORE_VIEW,
				Catalog\StoreTable::class
			);
		}
		$iterator = Catalog\StoreTable::getList(array(
			'select' => array('ID', 'TITLE', 'ADDRESS', 'SORT'),
			'filter' => array_merge(
				array('=ACTIVE' => 'Y'),
				$permissionFilter
			),
			'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['ADDRESS'] = trim($row['ADDRESS']);
			$row['PRODUCT_AMOUNT'] = '';
			$row['QUANTITY_RESERVED'] = '';
			$stores[$storeCount] = $row;
			$storeLink[$row['ID']] = &$stores[$storeCount];
			$storeCount++;
		}
		unset($row, $iterator);
		if ($storeCount > 0)
		{
			$storeIds = array_keys($storeLink);
			if (!$bSubCopy)
			{
				$select = [
					'STORE_ID',
					'AMOUNT'
				];
				if ($showStoreReserve)
				{
					$select[] = 'QUANTITY_RESERVED';
				}
				$iterator = Catalog\StoreProductTable::getList([
					'select' => $select,
					'filter' => [
						'=PRODUCT_ID' => $PRODUCT_ID,
						'@STORE_ID' => $storeIds
					],
				]);
				while ($row = $iterator->fetch())
				{
					$storeId = (int)$row['STORE_ID'];
					$row['AMOUNT'] = (string)$row['AMOUNT'];
					$row['QUANTITY_RESERVED'] = (string)$row['QUANTITY_RESERVED'];
					if ($row['AMOUNT'] !== '0' || $row['QUANTITY_RESERVED'] !== '0')
					{
						$storeLink[$storeId]['PRODUCT_AMOUNT'] = $row['AMOUNT'];
					}
					if (
						$showStoreReserve
						&& $row['QUANTITY_RESERVED'] !== '0'
					)
					{
						$storeLink[$storeId]['QUANTITY_RESERVED'] = $row['QUANTITY_RESERVED'];
					}
				}
				unset($row, $iterator);
			}
			if ($bVarsFromForm)
			{
				if ($bStore && !$bUseStoreControl)
				{
					foreach ($storeIds as $store)
					{
						if (isset($_POST['SUBAR_AMOUNT'][$store]) && is_string($_POST['SUBAR_AMOUNT'][$store]))
						{
							$storeLink[$store]['PRODUCT_AMOUNT'] = $_POST['SUBAR_AMOUNT'][$store];
						}
					}
					unset($store);
				}
			}
			unset($storeIds);
		}
		unset($storeLink);
		if ($storeCount > 0)
		{
			?><table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
			<tr class="heading">
				<td><?= GetMessage("C2IT_STORE_NUMBER"); ?></td>
				<td><?= GetMessage('C2IT_STORE_ID'); ?></td>
				<td><?= GetMessage("C2IT_NAME"); ?></td>
				<td><?= GetMessage("C2IT_STORE_ADDR"); ?></td>
				<td><?= GetMessage("C2IT_PROD_AMOUNT"); ?></td><?php
				if ($showStoreReserve)
				{
					?><td><?= GetMessage("C2IT_PROD_QUANTITY_RESERVED"); ?></td><?php
				}
				?>
			</tr>
			<?php
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
				<td style="text-align:center;"><input type="text" id="SUBAR_AMOUNT_<?=$row['ID']; ?>" name="SUBAR_AMOUNT[<?=$row['ID']?>]" size="12" value="<?=htmlspecialcharsbx($row['PRODUCT_AMOUNT']); ?>" <?= ((!$bStore || $bUseStoreControl) ? 'disabled readonly' : ''); ?>><?php
				if ($bStore)
				{
					?><input type="hidden" name="SUBAR_STORE_ID[<?=$row['ID']?>]" value="<?=$row['ID']?>"><?php
				}
				?></td><?php
				if ($showStoreReserve)
				{
					?><td><input type="text" size="12" disable readonly value="<?=htmlspecialcharsbx($row['QUANTITY_RESERVED']); ?>"></td><?php
				}
				?></tr><?php
				unset($storeUrl, $address, $storeId);
			}
			unset($storeIndex, $row);
			?></table><?php
		}
		else
		{
			if ($bStore)
			{
				$storeListUrl = $selfFolderUrl.'cat_store_list.php?lang='.LANGUAGE_ID;
				$storeListUrl = $adminSidePanelHelper->editUrlToPublicPage($storeListUrl);
				?><b><?= GetMessage("C2IT_STORE_NO_STORE"); ?> <a target="_top" href="<?=$storeListUrl?>"><?=GetMessage("C2IT_STORE"); ?></a></b><br><?php
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
					<td width="40%"><label for="SUBCAT_BARCODE_MULTIPLY"><?= GetMessage('C2IT_BARCODE_MULTIPLY'); ?>:</label></td>
					<td width="60%">
						<input type="checkbox" name="SUBCAT_BARCODE_MULTIPLY" id="SUBCAT_BARCODE_MULTIPLY" value="Y"<?=$bUseMultiplyBarcode ? ' checked="checked"' : ''?><?= $disableProduct; ?> onclick="checkSubBarCode();">
					</td>
				</tr>

				<tr id="tr_SUB_CAT_BARCODE"<?= ($bUseMultiplyBarcode ? ' style="display: none;"' : ''); ?>>
					<td><?= GetMessage("C2IT_BAR_CODE")?>:</td>
					<td><input type="text" name="SUBCAT_BARCODE" id="SUBCAT_BARCODE" size="30" value="<?= htmlspecialcharsbx($barcode); ?>"<?= $disableProduct; ?>></td>
				</tr><?php
				if (0 < $PRODUCT_ID && '' != $barcode)
				{
				?>
				<tr id="tr_SUBCAT_BARCODE_EDIT"<?= (($bUseMultiplyBarcode) ? ' style="display: none;"' : ''); ?>>
					<td><?= GetMessage("C2IT_BAR_CODE_EDIT")?>:</td>
					<td>
						<input type="hidden" name="SUBCAT_BARCODE_EDIT" id="SUBCAT_BARCODE_EDIT_N" value="N" />
						<input type="checkbox" name="SUBCAT_BARCODE_EDIT" id="SUBCAT_BARCODE_EDIT_Y" size="30" value="Y"<?= $disableProduct; ?> onclick="editSubBarCode();">
					</td>
				</tr><?php
				}
				?>
			</table>
			<?php
		}
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
							<?php
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
			<?php
		}
		$subtabControl1->End();
		?>
	</td>
</tr>
