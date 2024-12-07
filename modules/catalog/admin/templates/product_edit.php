<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var array $arShowTabs */
/** @var bool $bCopy */
/** @var bool $bVarsFromForm */
/** @var int $IBLOCK_ID */
/** @var int $MENU_SECTION_ID */
/** @var int $ID */
/** @var array $arMainCatalog */

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

$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
$publicMode = defined("SELF_FOLDER_URL");

$isCloud = Loader::includeModule('bitrix24');

$MENU_SECTION_ID = (int)$MENU_SECTION_ID;
$PRODUCT_ID = ($ID > 0 ? CIBlockElement::GetRealElement($ID) : 0);

$accessController = AccessController::getCurrent();

$iblockEditProduct = $PRODUCT_ID > 0 && !$bCopy
	? CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, 'element_edit_price')
	: CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $MENU_SECTION_ID, 'element_edit_price')
;

$allowEdit = false;
if ($iblockEditProduct)
{
	$allowEdit = $PRODUCT_ID > 0 && !$bCopy
		? $accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT)
		: $accessController->check(ActionDictionary::ACTION_PRODUCT_ADD)
	;
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

$currentTab = \CCatalogAdminTools::getCurrentTabFromRequest();

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

$arBaseProduct = null;
$periodTimeTypes = [];
$vatInclude = (Main\Config\Option::get('catalog', 'default_product_vat_included') === 'Y' ? 'Y' : 'N');
if ($arMainCatalog['SUBSCRIPTION'] == 'Y')
{
	$defaultProduct = array(
		'AVAILABLE' => 'N',
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
	);
	$periodTimeTypes = Catalog\ProductTable::getPaymentPeriods(true);
}
else
{
	$defaultProduct = array(
		'AVAILABLE' => 'N',
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
	);
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
		$arBaseProduct['TYPE'] = (int)$arBaseProduct['TYPE'];
		if ($bCopy)
		{
			$arBaseProduct['QUANTITY'] = '';
			$arBaseProduct['QUANTITY_RESERVED'] = '';
		}
		if ($arMainCatalog['SUBSCRIPTION'] == 'Y')
		{
			$arBaseProduct['QUANTITY_TRACE_ORIG'] = Catalog\ProductTable::STATUS_NO;
			$arBaseProduct['CAN_BUY_ZERO_ORIG'] = Catalog\ProductTable::STATUS_NO;
		}
	}
}

if (empty($arBaseProduct))
{
	$arBaseProduct = $defaultProduct;
	$arBaseProduct['TYPE'] = (int)CCatalogAdminTools::getProductTypeForNewProduct($arMainCatalog);
}
$productIsSet = (
	Catalog\Config\Feature::isProductSetsEnabled()
	&& (
		$arBaseProduct['TYPE'] == Catalog\ProductTable::TYPE_SET
		|| $arShowTabs['product_set']
	)
);
$isService = ($arBaseProduct['TYPE'] === Catalog\ProductTable::TYPE_SERVICE);
if (
	$currentTab !== null
)
{
	$isService = $currentTab === \CCatalogAdminTools::TAB_SERVICE;
	if (
		!$isService
		&& $bUseStoreControl
	)
	{
		$arBaseProduct['QUANTITY'] = 0;
	}
}

$disableQuantityFields = !$allowEdit || $bUseStoreControl || $productIsSet
	? $disableFieldAttributes
	: ''
;

$disableBundle = !$allowEdit || $productIsSet
	? $disableFieldAttributes
	: ''
;

$subscribeEnabled = $arBaseProduct['SUBSCRIBE'] == 'Y';
$activitySubscribeTab = $PRODUCT_ID > 0 && !$bCopy && $subscribeEnabled;

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
<script>
var allowPriceEdit = <?= ($allowEditPrices ? 'true' : 'false'); ?>;
var allowEdit = <?= ($allowEdit ? 'true' : 'false'); ?>;

function getElementForm()
{
	for(var i = 0; i < document.forms.length; i++)
	{
		var check = document.forms[i].name.substring(0, 10).toUpperCase();
		if(check == 'FORM_ELEME' || check == 'TABCONTROL')
			return document.forms[i];
	}
}
function getElementFormName()
{
	var form = getElementForm();
	if (form)
		return form.name;
	else
		return '';
}
function checkForm(e)
{
	if (window.BX_CANCEL)
		return true;

	if (!e)
		e = window.event;

	var bReturn = true;

	if (document.getElementById('CAT_ROW_COUNTER').value > 0 && !!document.getElementById('price_useextform') && !document.getElementById('price_useextform').checked)
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
	var obForm = getElementForm();
	jsUtils.addEvent(obForm, 'submit', checkForm);
	if (obForm.dontsave)
	{
		jsUtils.addEvent(obForm.dontsave, 'click', function() {
			window.BX_CANCEL = true; setTimeout('window.BX_CANCEL = false', 10);
		});
	}
});

function checkBarCode()
{
	var arTrBarCode = document.getElementsByClassName('tr-barcode-class');

	if(BX('CAT_BARCODE_MULTIPLY').checked)
	{
		if (!!arTrBarCode)
		{
			for (var i = 0; i < arTrBarCode.length; i++)
			{
				arTrBarCode[i].disabled = true;
				arTrBarCode[i].style.display = "none";
			}
		}
	}
	else
	{
		if (!!arTrBarCode)
		{
			for(i = 0; i < arTrBarCode.length; i++)
			{
				arTrBarCode[i].disabled = false;
				arTrBarCode[i].style.display = "table-row";
			}
		}
	}
}

function editBarCode()
{
	var obEditBarCode = BX('CAT_BARCODE_EDIT_Y');
	var obBarCode = BX('CAT_BARCODE');
	if (allowEdit && !!obEditBarCode && !!obBarCode)
	{
		if (obEditBarCode.checked)
		{
			if (confirm('<?= GetMessageJS("CAT_BARCODE_EDIT_CONFIRM"); ?>'))
			{
				obBarCode.disabled = false;
			}
			else
			{
				obEditBarCode.checked = false;
				obBarCode.disabled = true;
			}
		}
		else
		{
			obBarCode.disabled = true;
		}
	}
}
function SetFieldsStyle(table_id)
{
	var tbl = document.getElementById(table_id);
	var n = tbl.rows.length;
	for(var i=0; i<n; i++)
	{
		if (tbl.rows[i].cells[0].colSpan == 1)
		{
			tbl.rows[i].cells[0].classList.add('field-name');
		}
	}
}

function togglePriceType()
{
	var obPriceSimple = BX('prices_simple');
	var obPriceExt = BX('prices_ext');
	var obBasePrice = BX('tr_BASE_PRICE');
	var obBaseCurrency = BX('tr_BASE_CURRENCY');

	if (obPriceSimple.style.display == 'block')
	{
		obPriceSimple.style.display = 'none';
		obPriceExt.style.display = 'block';
		if (!!obBasePrice)
			BX.style(obBasePrice, 'display', 'none');
		if (!!obBaseCurrency)
			BX.style(obBaseCurrency, 'display', 'none');
	}
	else
	{
		obPriceSimple.style.display = 'block';
		obPriceExt.style.display = 'none';
		if (!!obBasePrice)
			BX.style(obBasePrice, 'display', 'table-row');
		if (!!obBaseCurrency)
			BX.style(obBaseCurrency, 'display', 'table-row');
	}
}
</script>
</td>
</tr>
<tr>
<td valign="top" colspan="2">
	<?php
	$aTabs1 = array();
	$aTabs1[] = array("DIV" => "cat_edit1", "TAB" => GetMessage("C2IT_PRICES"), "TITLE" => GetMessage("C2IT_PRICES_D"));
	$aTabs1[] = array("DIV" => "cat_edit3", "TAB" => GetMessage("C2IT_PARAMS"), "TITLE" => GetMessage("C2IT_PARAMS_D"));
	if ($arMainCatalog['SUBSCRIPTION'] == 'Y')
	{
		$aTabs1[] = ["DIV" => "cat_edit4", "TAB" => GetMessage("C2IT_GROUPS"), "TITLE" => GetMessage("C2IT_GROUPS_D")];
	}
	$aTabs1[] = array("DIV" => "cat_edit6", "TAB" => GetMessage("C2IT_DISCOUNTS"), "TITLE" => GetMessage("C2IT_DISCOUNTS_D"));
	if (
		!$productIsSet
		&& !$isService
		&& $allowedShowQuantity
	)
	{
		$aTabs1[] = [
			"DIV" => "cat_edit5",
			"TAB" => GetMessage("C2IT_STORE"),
			"TITLE" => GetMessage("C2IT_STORE_D"),
		];

		if ($bUseStoreControl)
		{
			$aTabs1[] = [
				"DIV" => "cat_edit7",
				"TAB" => GetMessage("C2IT_BAR_CODE"),
				"TITLE" => GetMessage("C2IT_BAR_CODE_D"),
			];
		}
	}

	if($activitySubscribeTab)
	{
		$aTabs1[] = array(
			"DIV" => "cat_edit8",
			"TAB" => GetMessage("C2IT_SUBSCRIBE_TAB"),
			"TITLE" => GetMessage("C2IT_SUBSCRIBE_TAB_TITLE"),
			"ONSELECT" => "getDataSubscriptions();"
		);
	}

	$tabControl1 = new CAdminViewTabControl("tabControl1", $aTabs1);
	$tabControl1->Begin();

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
				for ($i = 0, $intCount = count($arPriceBoundaries); $i < $intCount; $i++)
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
	$tabControl1->BeginNextTab();
	$arCatPricesExist = array(); // attr for exist prices for range
	if ($enableQuantityRanges)
		$bUseExtendedPrice = $bVarsFromForm ? $price_useextform == 'Y' : $usedRanges;
	else
		$bUseExtendedPrice = false;
	if ($isCloud)
	{
		$str_CAT_VAT_ID = (int)($bVarsFromForm
			? $CAT_VAT_ID
			: ($arBaseProduct['VAT_ID'] == 0 ? $arMainCatalog['VAT_ID'] : $arBaseProduct['VAT_ID'])
		);
	}
	else
	{
		$str_CAT_VAT_ID = (int)($bVarsFromForm ? $CAT_VAT_ID : $arBaseProduct['VAT_ID']);
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
	if (!isset($vatList[$arMainCatalog['VAT_ID']]))
	{
		$arMainCatalog['VAT_ID'] = 0;
	}
	if (!isset($vatList[$str_CAT_VAT_ID]))
	{
		$str_CAT_VAT_ID = 0;
	}

	$str_CAT_VAT_INCLUDED = $bVarsFromForm ? $CAT_VAT_INCLUDED : $arBaseProduct['VAT_INCLUDED'];
	?>
<input type="hidden" name="price_useextform" id="price_useextform_N" value="N" />
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="catalog_vat_table">
<?php
if ($enableQuantityRanges || !empty($quantityRangesHelpLink))
{
	?>
	<tr>
		<td width="40%"><label for="price_useextform"><?= GetMessage('C2IT_PRICES_USEEXT'); ?>:</label></td>
		<td width="60%"><?php
		if ($enableQuantityRanges)
		{
			?>
			<input type="checkbox" name="price_useextform" id="price_useextform" value="Y" onclick="togglePriceType()" <?= $bUseExtendedPrice ? 'checked="checked"' : '' ?> <?= $disablePrice; ?>/>
			<?php
		}
		else
		{
			?><input type="hidden" value="N" name="price_useextform"><?php
			if ($quantityRangesHelpLink['TYPE'] == 'ONCLICK')
			{
				?><a href="#" onclick="<?=$quantityRangesHelpLink['LINK']; ?>"><?= GetMessage('C2IT_PRICES_EXT_TARIFF_ENABLE'); ?></a><?php
				Catalog\Config\Feature::initUiHelpScope();
			}
		}
		?></td>
	</tr>
	<?php
}
else
{
	?><input type="hidden" value="N" name="price_useextform"><?php
}
?>
	<tr>
		<td width="40%">
			<?php
			if (!$isCloud && (int)$arMainCatalog['VAT_ID'] !== 0)
			{
				$hintMessage = GetMessage(
					'CAT_VAT_ID_CATALOG_HINT',
					[
						'#VAT_NAME#' => $vatList[$arMainCatalog['VAT_ID']],
					]
				);
				?>
				<span id="hint_CAT_VAT_ID"></span>
				<script>
					BX.hint_replace(BX('hint_CAT_VAT_ID'), '<?= \CUtil::JSEscape($hintMessage); ?>');
				</script>&nbsp;<?php
			}
			echo GetMessage("CAT_VAT")?>:
		</td>
		<td width="60%">
			<select name="CAT_VAT_ID" id="CAT_VAT_ID" <?= $disableProduct; ?>>
				<?php
				if (!$isCloud)
				{
				$vatSelected = ($str_CAT_VAT_ID === 0 ? ' selected' : '');
				?>
				<option value="0"<?=$vatSelected; ?>><?= htmlspecialcharsbx(GetMessage('CAT_VAT_ID_EMPTY')); ?></option>
				<?php
				}
				foreach ($vatList as $vatId => $vatName)
				{
					$vatSelected = ($str_CAT_VAT_ID === $vatId ? ' selected' : '');
					?><option value="<?= htmlspecialcharsbx($vatId); ?>"<?= $vatSelected; ?>><?= htmlspecialcharsbx($vatName); ?></option><?php
				}
				unset($vatList);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="CAT_VAT_INCLUDED"><?= GetMessage("CAT_VAT_INCLUDED")?></label>:</td>
		<td width="60%">
			<input type="hidden" name="CAT_VAT_INCLUDED" id="CAT_VAT_INCLUDED_N" value="N">
			<input type="checkbox" name="CAT_VAT_INCLUDED" id="CAT_VAT_INCLUDED" value="Y" <?=$str_CAT_VAT_INCLUDED == 'Y' ? 'checked="checked"' : ''?><?= $disableProduct; ?> />
		</td>
	</tr>
	<?php
	if ($accessController->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW)):?>
		<tr id="tr_PURCHASING_PRICE">
			<?php
			$str_CAT_PURCHASING_PRICE = $bVarsFromForm ? $CAT_PURCHASING_PRICE : $arBaseProduct['PURCHASING_PRICE'];
			?>
			<td width="40%"><?php
				echo ($isService
					? GetMessage('C2IT_SERVICE_COST_PRICE')
					: GetMessage("C2IT_COST_PRICE_EXT")
				);
			?></td>
			<td width="60%"><?php
				$isDisabled = (!$allowEdit || $bUseStoreControl)
					? ' disabled'
					: ''
				;
				?>
				<input type="hidden" id="CAT_PURCHASING_PRICE_hidden" name="CAT_PURCHASING_PRICE" value="<?= htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>">
				<input type="text"<?= $isDisabled; ?> id="CAT_PURCHASING_PRICE" name="CAT_PURCHASING_PRICE" value="<?= htmlspecialcharsbx($str_CAT_PURCHASING_PRICE) ?>" size="30">
				<input type="hidden" id="CAT_PURCHASING_CURRENCY_hidden" name="CAT_PURCHASING_CURRENCY" value="<?= htmlspecialcharsbx($arBaseProduct['PURCHASING_CURRENCY']) ?>"><?php
				echo CCurrency::SelectBox("CAT_PURCHASING_CURRENCY", $arBaseProduct['PURCHASING_CURRENCY'], "", true, "", "id='CAT_PURCHASING_CURRENCY' $isDisabled");?></td>
			</td>
		</tr>
	<?php
	endif;
	?>
	<tr id="tr_BASE_PRICE" style="display: <?= ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
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
<script>
var arExtra = [], arExtraPrc = [];
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
function OnChangeExtra(priceType)
{
	if (!allowPriceEdit)
		return;
	var e_base_price = BX('CAT_BASE_PRICE');
	var e_extra = BX('CAT_EXTRA_' + priceType);
	var e_price = BX('CAT_PRICE_' + priceType);
	var e_currency = BX('CAT_CURRENCY_' + priceType);

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

function OnChangeExtraEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);
	thename = thename.substr(0, pos);
	pos = thename.lastIndexOf("_");
	var ptype = thename.substr(pos + 1);

	var e_ext = BX('CAT_EXTRA_'+ptype+"_"+ind);
	var e_price = BX('CAT_PRICE_'+ptype+"_"+ind);
	var e_currency = BX('CAT_CURRENCY_'+ptype+"_"+ind);

	var e_base_price = BX('CAT_BASE_PRICE_'+ind);

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

function ChangeExtra(codID)
{
	if (!allowPriceEdit)
		return;

	OnChangeExtra(codID);

	var e_extra = BX('CAT_EXTRA_' + codID + '_0');
	if (e_extra)
	{
		var e_extra_s = document.getElementById('CAT_EXTRA_' + codID);
		e_extra.selectedIndex = e_extra_s.selectedIndex;
		OnChangeExtraEx(e_extra);
	}
}

function OnChangeBasePrice()
{
	if (!allowPriceEdit)
		return;

	var e_base_price = document.getElementById('CAT_BASE_PRICE');

	if (isNaN(e_base_price.value) || e_base_price.value <= 0)
	{
		var k;
		for (k = 0; k < arCatalogGroups.length; k++)
		{
			e_price = BX('CAT_PRICE_' + arCatalogGroups[k]);
			e_price.disabled = false;
			e_currency = BX('CAT_CURRENCY_' + arCatalogGroups[k]);
			e_currency.disabled = false;
		}
		OnChangePriceExist();
		return;
	}

	var i, j, esum, eps;
	var e_price;
	for (i = 0; i < arCatalogGroups.length; i++)
	{
		e_extra = document.getElementById('CAT_EXTRA_' + arCatalogGroups[i]);
		if (e_extra.selectedIndex > 0)
		{
			e_price = document.getElementById('CAT_PRICE_' + arCatalogGroups[i]);
			e_currency = BX('CAT_CURRENCY_' + arCatalogGroups[i]);

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
	OnChangePriceExist();
}

function ChangeBasePrice(e)
{
	if (!allowPriceEdit)
		return;

	if (e.value != '' && (isNaN(e.value) || e.value <= 0))
	{
	}
	else
	{
		e.className = '';
	}

	OnChangeBasePrice();

	var e_base_price = BX('CAT_BASE_PRICE_0');
	e_base_price.value = BX('CAT_BASE_PRICE').value;
	OnChangeBasePriceEx(e_base_price);
	OnChangePriceExistEx(e_base_price);
}

function ChangeBaseCurrency()
{
	if (!allowPriceEdit)
		return;

	document.getElementById('CAT_BASE_CURRENCY_0').selectedIndex = document.getElementById('CAT_BASE_CURRENCY').selectedIndex;
}

function ChangePrice(codID)
{
	if (!allowPriceEdit)
		return;

	var e_price = document.getElementById('CAT_PRICE_' + codID + '_0');
	e_price.value = document.getElementById('CAT_PRICE_' + codID).value;
	OnChangePriceExist();
	OnChangePriceExistEx(e_price);
}

function ChangeCurrency(codID)
{
	if (!allowPriceEdit)
		return;

	var e_currency = document.getElementById('CAT_CURRENCY_' + codID + "_0");
	e_currency.selectedIndex = document.getElementById('CAT_CURRENCY_' + codID).selectedIndex;
}

function OnChangePriceExist()
{
	if (!allowPriceEdit)
		return;

	var bExist = 'N';
	var e_price_exist = BX('CAT_PRICE_EXIST');
	var e_ext_price_exist = BX('CAT_PRICE_EXIST_0');
	var e_base_price = BX('CAT_BASE_PRICE');

	if (isNaN(e_base_price.value) || e_base_price.value <= 0)
	{
		var i;
		var e_price;
		for (i = 0; i < arCatalogGroups.length; i++)
		{
			e_price = BX('CAT_PRICE_' + arCatalogGroups[i]);
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
	{
		$str_CAT_BASE_PRICE = $arBasePrice["PRICE"];
	}
	if ($bVarsFromForm)
	{
		$str_CAT_BASE_PRICE = $CAT_BASE_PRICE;
	}
	if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
	{
		$boolBaseExistPrice = true;
	}
	?>
			<input type="text"<?= $disablePrice; ?> id="CAT_BASE_PRICE" name="CAT_BASE_PRICE" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="30">
		</td>
	</tr>
	<tr id="tr_BASE_CURRENCY" style="display: <?= ($bUseExtendedPrice ? 'none' : 'table-row'); ?>;">
		<td width="40%">
			<?= GetMessage("BASE_CURRENCY")?>:
		</td>
		<td width="60%">
		<?php
		$str_CAT_BASE_CURRENCY = '';
		if ($arBasePrice)
		{
			$str_CAT_BASE_CURRENCY = $arBasePrice["CURRENCY"];
		}
		if ($bVarsFromForm)
		{
			$str_CAT_BASE_CURRENCY = $CAT_BASE_CURRENCY;
		}

		?>
			<select id="CAT_BASE_CURRENCY" name="CAT_BASE_CURRENCY"<?= $disablePrice; ?> OnChange="ChangeBaseCurrency()">
			<?php
			foreach ($currencyList as $currency)
			{
				?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ? ' selected' : ''); ?>><?= $currency["FULL_NAME"]; ?></option><?php
			}
			unset($currency);
			?>
			</select>
		</td>
	</tr>
</table>
<script>
	SetFieldsStyle('catalog_vat_table');
</script>
	<?php
// simple price form
	?>
<div id="prices_simple" style="display: <?=$bUseExtendedPrice ? 'none' : 'block'?>;">
	<?php
	if (!empty($arPriceBoundariesError))
	{
		?>
	<span class="errortext">
		<?= GetMessage("C2IT_BOUND_WRONG"); ?><br>
		<?= implode('<br>', $arPriceBoundariesError); ?><br>
		<?= GetMessage("C2IT_BOUND_RECOUNT"); ?>
	</span>
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
				$str_CAT_EXTRA = ${"CAT_EXTRA_".$arCatalogGroup["ID"]};
				$str_CAT_PRICE = ${"CAT_PRICE_".$arCatalogGroup["ID"]};
				$str_CAT_CURRENCY = ${"CAT_CURRENCY_".$arCatalogGroup["ID"]};
			}
			if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
				$boolBaseExistPrice = true;
			?>
			<tr>
				<td valign="top" align="left">
					<?= htmlspecialcharsbx(!empty($arCatalogGroup['NAME_LANG']) ? $arCatalogGroup['NAME_LANG'] : $arCatalogGroup["NAME"]); ?>
					<?php
					if ($arPrice):?>
					<input type="hidden" name="CAT_ID_<?= $arCatalogGroup["ID"] ?>" value="<?= $arPrice["ID"] ?>">
					<?php
					endif;?>
				</td>
				<td valign="top" align="center">
					<?php
					echo CExtra::SelectBox("CAT_EXTRA_".$arCatalogGroup["ID"], $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeExtra(".$arCatalogGroup["ID"].")", $disablePrice.' id="'."CAT_EXTRA_".$arCatalogGroup["ID"].'" ');
					?>
				</td>
				<td valign="top" align="center">
					<input type="text"<?= $disablePrice; ?> id="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>" name="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE) ?>" size="8" OnChange="ChangePrice(<?= $arCatalogGroup["ID"] ?>)">
				</td>
				<td valign="top" align="center">
					<?php
					echo CCurrency::SelectBox("CAT_CURRENCY_".$arCatalogGroup["ID"], $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeCurrency(".$arCatalogGroup["ID"].")", $disablePrice.' id="'."CAT_CURRENCY_".$arCatalogGroup["ID"].'" ')
					?>
					<script>
						ChangeExtra(<?= $arCatalogGroup["ID"] ?>);
					</script>
				</td>
			</tr>
			<?php
		}// endwhile
		if(!$bFirst) echo "</table>";
	}
	?><input type="hidden" name="CAT_PRICE_EXIST" id="CAT_PRICE_EXIST" value="<?= ($boolBaseExistPrice ? 'Y' : 'N'); ?>">
</div>
	<?php
	//$tabControl1->BeginNextTab();
// extended price form
	?>
<div id="prices_ext" style="display: <?=$bUseExtendedPrice ? 'block' : 'none'?>;">
<script>
function CloneBasePriceGroup()
{
	if (!allowPriceEdit)
		return;

	var oTbl = BX("BASE_PRICE_GROUP_TABLE");
	if (!oTbl)
		return;

	var oCntr = document.getElementById("CAT_ROW_COUNTER");
	var cnt = parseInt(oCntr.value);
	cnt = cnt + 1;

	var oRow = oTbl.insertRow(-1);
	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text"<?= $disablePrice; ?> name="CAT_BASE_QUANTITY_FROM_'+cnt+'" value="" size="3" OnChange="ChangeBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text"<?= $disablePrice; ?> name="CAT_BASE_QUANTITY_TO_'+cnt+'" value="" size="3" OnChange="ChangeBaseQuantityEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text"<?= $disablePrice; ?> id="CAT_BASE_PRICE_'+cnt+'" name="CAT_BASE_PRICE_'+cnt+'" value="" size="15" OnBlur="ChangeBasePriceEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="CAT_BASE_CURRENCY_'+cnt+'" name="CAT_BASE_CURRENCY_'+cnt+'"<?= $disablePrice; ?> onchange="ChangeBaseCurrencyEx(this)">';
	<?php
	foreach ($currencyList as &$currency)
	{
		?>str += '<option value="<?= $currency["CURRENCY"] ?>"><?= $currency["FULL_NAME_JS"]; ?></option>';<?php
	}
	unset($currency);
	?>
	str += '</select>';
	oCell.innerHTML = str;

	var div_ext_price_exist = BX('ext_price_exist');
	var new_price_exist = BX.create('input',
		{'attrs': {
			'type': 'hidden',
			'name': 'CAT_PRICE_EXIST_'+cnt,
			'value': 'N'
		}
		});
	new_price_exist.id = 'CAT_PRICE_EXIST_'+cnt,
		div_ext_price_exist.appendChild(new_price_exist);
	oCntr.value = cnt;
}

function CloneOtherPriceGroup(ind)
{
	if (!allowPriceEdit)
		return;

	var oTbl = document.getElementById("OTHER_PRICE_GROUP_TABLE_"+ind);
	if (!oTbl)
		return;

	var oCntr = document.getElementById("CAT_ROW_COUNTER_"+ind);
	var cnt = parseInt(oCntr.value);
	cnt = cnt + 1;

	var oRow = oTbl.insertRow(-1);
	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" disabled readonly id="CAT_QUANTITY_FROM_'+ind+'_'+cnt+'" name="CAT_QUANTITY_FROM_'+ind+'_'+cnt+'" value="" size="3">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text" disabled readonly id="CAT_QUANTITY_TO_'+ind+'_'+cnt+'" name="CAT_QUANTITY_TO_'+ind+'_'+cnt+'" value="" size="3">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '';
	oCell.valign = "top";
	oCell.align = "center";
	str += '<select id="CAT_EXTRA_'+ind+'_'+cnt+'" name="CAT_EXTRA_'+ind+'_'+cnt+'" onchange="ChangeExtraEx(this)"<?= $disablePrice; ?>>';
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
	oCell.innerHTML = '<input type="text"<?= $disablePrice; ?> id="CAT_PRICE_'+ind+'_'+cnt+'" name="CAT_PRICE_'+ind+'_'+cnt+'" value="" size="10" OnChange="ptPriceChangeEx(this)">';

	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	var str = '<select id="CAT_CURRENCY_'+ind+'_'+cnt+'" name="CAT_CURRENCY_'+ind+'_'+cnt+'" onchange="ChangeCurrencyEx(this)"<?= $disablePrice; ?>>';
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

function ClonePriceSections()
{
	if (!allowPriceEdit)
		return;

	CloneBasePriceGroup();

	var i, n;
	for (i = 0; i < arCatalogGroups.length; i++)
	{
		CloneOtherPriceGroup(arCatalogGroups[i]);

		n = document.getElementById('CAT_ROW_COUNTER_'+arCatalogGroups[i]).value;
		ChangeExtraEx(document.getElementById('CAT_EXTRA_'+arCatalogGroups[i]+"_"+n));
	}
}

function ChangeBaseQuantityEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	var type;
	if (thename.substring(0, "CAT_BASE_QUANTITY_FROM_".length) == "CAT_BASE_QUANTITY_FROM_")
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
		quantity = document.getElementById('CAT_QUANTITY_'+type+"_"+arCatalogGroups[i]+"_"+ind);
		quantity.value = e.value;
	}
}

function OnChangeBasePriceEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (isNaN(e.value) || e.value <= 0)
	{
		for (i = 0; i < arCatalogGroups.length; i++)
		{
			e_price = document.getElementById('CAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
			e_price.disabled = false;
			e_cur = document.getElementById('CAT_CURRENCY_'+arCatalogGroups[i]+"_"+ind);
			e_cur.disabled = false;
		}
		OnChangePriceExistEx(e);
		return;
	}

	var i;
	var e_price, e_ext;

	for (i = 0; i < arCatalogGroups.length; i++)
	{
		e_price = BX('CAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
		e_cur = BX('CAT_CURRENCY_'+arCatalogGroups[i]+"_"+ind);
		e_ext = BX('CAT_EXTRA_'+arCatalogGroups[i]+"_"+ind);

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
	OnChangePriceExistEx(e);
}

function ChangeBasePriceEx(e)
{
	if (!allowPriceEdit)
		return;

	if (isNaN(e.value) || e.value <= 0)
	{
	}
	else
	{
		e.className = '';
	}

	OnChangeBasePriceEx(e);

	var thename = e.name;
	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		BX('CAT_BASE_PRICE').value = e.value;
		OnChangeBasePrice();
		OnChangePriceExist();
	}
}

function ChangeExtraEx(e)
{
	if (!allowPriceEdit)
		return;

	if (null == e)
		return;

	OnChangeExtraEx(e);
	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);
	thename = thename.substr(0, pos);
	pos = thename.lastIndexOf("_");
	var ptype = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		document.getElementById('CAT_EXTRA_'+ptype).selectedIndex = e.selectedIndex;
		OnChangeExtra(ptype);
	}
}

function ChangeBaseCurrencyEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		document.getElementById('CAT_BASE_CURRENCY').selectedIndex = e.selectedIndex;
	}
}

function ptPriceChangeEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		thename = thename.substr(0, pos);
		pos = thename.lastIndexOf("_");
		var ptype = thename.substr(pos + 1);
		BX('CAT_PRICE_'+ptype).value = e.value;
		OnChangePriceExist();
	}
	OnChangePriceExistEx(e);
}

function ChangeCurrencyEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (parseInt(ind) == 0)
	{
		thename = thename.substr(0, pos);
		pos = thename.lastIndexOf("_");
		var ptype = thename.substr(pos + 1);

		document.getElementById('CAT_CURRENCY_'+ptype).selectedIndex = e.selectedIndex;
	}
}

function OnChangePriceExistEx(e)
{
	if (!allowPriceEdit)
		return;

	var thename = e.name;

	var pos = thename.lastIndexOf("_");
	var ind = thename.substr(pos + 1);

	if (!(isNaN(ind) || parseInt(ind) < 0))
	{
		var price_ext = BX('CAT_PRICE_EXIST_'+ind);
		if (!price_ext)
			return;

		var i;
		var e_price;
		bExist = 'N';
		e_price = BX('CAT_BASE_PRICE_'+ind);
		if (!e_price)
			return;

		if (isNaN(e_price.value) || e_price.value <= 0)
		{
			for (i = 0; i < arCatalogGroups.length; i++)
			{
				e_price = document.getElementById('CAT_PRICE_'+arCatalogGroups[i]+"_"+ind);
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

function CloneBarcodeField()
{
	if (!allowEdit)
		return;

	var oTbl = BX("catalog_barcode_table");
	if (!oTbl)
		return;

	var oCntr = document.getElementById("CAT_BARCODE_COUNTER");
	var cnt = parseInt(oCntr.value);
	cnt = cnt + 1;

	var oRow = oTbl.insertRow(-1);
	oRow.setAttribute('id','tr_CAT_BARCODE');
	oRow.setAttribute('class', "tr-barcode-class");
	var oCell1 = oRow.insertCell(-1);
	var oCell = oRow.insertCell(-1);
	oCell.valign = "top";
	oCell.align = "center";
	oCell.innerHTML = '<input type="text"<?= $disableProduct; ?> name="CAT_BARCODE_ADD['+cnt+']" value="" size="30">';

	oCntr.value = cnt;
}
</script>

	<?php
	if (!empty($arPriceBoundariesError))
	{
		?>
	<span class="errortext">
		<?= GetMessage("C2IT_BOUND_WRONG"); ?><br>
		<?= implode('<br>', $arPriceBoundariesError); ?><br>
		<?= GetMessage("C2IT_BOUND_RECOUNT"); ?>
	</span>
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
		<table border="0" cellspacing="1" cellpadding="3" id="BASE_PRICE_GROUP_TABLE">
			<thead>
			<tr>
				<td align="center"><?= GetMessage("C2IT_FROM"); ?></td>
				<td align="center"><?= GetMessage("C2IT_TO"); ?></td>
				<td align="center"><?= GetMessage("C2IT_PRICE"); ?></td>
				<td align="center"><?= GetMessage("C2IT_CURRENCY"); ?></td>
			</tr>
			</thead>
			<tbody id="container3">
				<?php
				$ind = -1;
				$dbBasePrice = CPrice::GetList(
					array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
					array("PRODUCT_ID" => $PRODUCT_ID, "BASE" => "Y")
				);
				$arBasePrice = $dbBasePrice->Fetch();

				for ($i = 0, $intCount = count($arPriceBoundaries); $i < $intCount; $i++)
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
						$str_CAT_BASE_QUANTITY_FROM = ${"CAT_BASE_QUANTITY_FROM_".$ind};
						$str_CAT_BASE_QUANTITY_TO = ${"CAT_BASE_QUANTITY_TO_".$ind};
						$str_CAT_BASE_PRICE = ${"CAT_BASE_PRICE_".$ind};
						$str_CAT_BASE_CURRENCY = ${"CAT_BASE_CURRENCY_".$ind};
					}
					if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
						$boolExistPrice = true;
					$arCatPricesExist[$ind][$arBaseGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
					?>
				<tr id="model3">
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeBaseQuantityEx(this)">
						<input type="hidden" name="CAT_BASE_ID[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_BASE_ID) ?>">
					</td>
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeBaseQuantityEx(this)">
					</td>
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> id="CAT_BASE_PRICE_<?= $ind ?>" name="CAT_BASE_PRICE_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeBasePriceEx(this)">
					</td>
					<td valign="top" align="center">
						<select id="CAT_BASE_CURRENCY_<?= $ind ?>" name="CAT_BASE_CURRENCY_<?= $ind ?>"<?= $allowEditPrices; ?> OnChange="ChangeBaseCurrencyEx(this)">
							<?php
							foreach ($currencyList as &$currency)
							{
								?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ? ' selected' : '');?>><?= $currency["FULL_NAME"];?></option><?php
							}
							unset($currency);
							?>
						</select>
					</td>
				</tr>
					<?php
				}

				if ($bVarsFromForm && $ind < intval($CAT_ROW_COUNTER))
				{
					for ($i = $ind + 1; $i <= intval($CAT_ROW_COUNTER); $i++)
					{
						$boolExistPrice = false;
						$ind++;
						$str_CAT_BASE_QUANTITY_FROM = ${"CAT_BASE_QUANTITY_FROM_".$ind};
						$str_CAT_BASE_QUANTITY_TO = ${"CAT_BASE_QUANTITY_TO_".$ind};
						$str_CAT_BASE_PRICE = ${"CAT_BASE_PRICE_".$ind};
						$str_CAT_BASE_CURRENCY = ${"CAT_BASE_CURRENCY_".$ind};
						if (trim($str_CAT_BASE_PRICE) != '' && doubleval($str_CAT_BASE_PRICE) >= 0)
							$boolExistPrice = true;
						$arCatPricesExist[$ind][$arBaseGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
						?>
					<tr id="model3">
						<td valign="top" align="center">
							<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_FROM) : "") ?>" size="3" OnChange="ChangeBaseQuantityEx(this)">
							<input type="hidden" name="CAT_BASE_ID[<?= $ind ?>]" value="<?= 0 ?>">
						</td>
						<td valign="top" align="center">
							<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_TO_<?= $ind ?>" value="<?= ($str_CAT_BASE_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_BASE_QUANTITY_TO) : "") ?>" size="3" OnChange="ChangeBaseQuantityEx(this)">
						</td>
						<td valign="top" align="center">
							<input type="text"<?= $allowEditPrices; ?> id="CAT_BASE_PRICE_<?= $ind ?>" name="CAT_BASE_PRICE_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_BASE_PRICE) ?>" size="15" OnBlur="ChangeBasePriceEx(this)">
						</td>
						<td valign="top" align="center">
							<select id="CAT_BASE_CURRENCY_<?= $ind ?>" name="CAT_BASE_CURRENCY_<?= $ind ?>"<?= $allowEditPrices; ?> OnChange="ChangeBaseCurrencyEx(this)">
								<?php
								foreach ($currencyList as &$currency)
								{
									?><option value="<?= $currency["CURRENCY"]; ?>"<?= ($currency["CURRENCY"] == $str_CAT_BASE_CURRENCY ?  ' selected' : ''); ?>><?= $currency["FULL_NAME"];?></option><?php
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
				<tr id="model3">
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_FROM_<?= $ind ?>" value="" size="3" OnChange="ChangeBaseQuantityEx(this)">
					</td>
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> name="CAT_BASE_QUANTITY_TO_<?= $ind ?>" value="" size="3" OnChange="ChangeBaseQuantityEx(this)">
					</td>
					<td valign="top" align="center">
						<input type="text"<?= $allowEditPrices; ?> id="CAT_BASE_PRICE_<?= $ind ?>" name="CAT_BASE_PRICE_<?= $ind ?>" value="" size="15" OnBlur="ChangeBasePriceEx(this)">
					</td>
					<td valign="top" align="center">
						<select id="CAT_BASE_CURRENCY_<?= $ind ?>" name="CAT_BASE_CURRENCY_<?= $ind ?>"<?= $allowEditPrices; ?> OnChange="ChangeBaseCurrencyEx(this)">
							<?php
							foreach ($currencyList as $currency)
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
		<input type="hidden" name="CAT_ROW_COUNTER" id="CAT_ROW_COUNTER" value="<?= $ind ?>">
		<input type="button" value="<?= htmlspecialcharsbx(GetMessage("C2IT_MORE")); ?>" OnClick="ClonePriceSections()">
	</td>
</tr>
<script>
	arCatalogGroups = [];
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
	<script>
		arCatalogGroups[catalogGroupsInd] = <?= $arCatalogGroup["ID"] ?>;
		catalogGroupsInd++;
	</script>
	<tr>
		<td valign="top" align="right">
			<?= GetMessage("C2IT_PRICE_TYPE")?> "<?= htmlspecialcharsbx(!empty($arCatalogGroup['NAME_LANG']) ? $arCatalogGroup['NAME_LANG'] : $arCatalogGroup["NAME"]); ?>":
		</td>
		<td valign="top" align="left">
			<table border="0" cellspacing="1" cellpadding="3" id="OTHER_PRICE_GROUP_TABLE_<?= $arCatalogGroup["ID"] ?>">
				<thead>
				<tr>
					<td align="center"><?= GetMessage("C2IT_FROM"); ?></td>
					<td align="center"><?= GetMessage("C2IT_TO"); ?></td>
					<td align="center"><?= GetMessage("C2IT_NAC_TYPE"); ?></td>
					<td align="center"><?= GetMessage("C2IT_PRICE"); ?></td>
					<td align="center"><?= GetMessage("C2IT_CURRENCY"); ?></td>
				</tr>
				</thead>
				<tbody id="container3_<?= $arCatalogGroup["ID"] ?>">
					<?php
					$ind = -1;
					$dbPriceList = CPrice::GetList(
						array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
						array("PRODUCT_ID" => $PRODUCT_ID, "CATALOG_GROUP_ID" => $arCatalogGroup["ID"])
					);
					$arPrice = $dbPriceList->Fetch();
					for ($i = 0, $intCount = count($arPriceBoundaries); $i < $intCount; $i++)
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
							$str_CAT_EXTRA = ${"CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind};
							$str_CAT_PRICE = ${"CAT_PRICE_".$arCatalogGroup["ID"]."_".$ind};
							$str_CAT_CURRENCY = ${"CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind};
							$str_CAT_QUANTITY_FROM = ${"CAT_BASE_QUANTITY_FROM_".$ind};
							$str_CAT_QUANTITY_TO = ${"CAT_BASE_QUANTITY_TO_".$ind};
						}
						if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
							$boolExistPrice = true;
						$arCatPricesExist[$ind][$arCatalogGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
						?>
					<tr id="model3_<?= $arCatalogGroup["ID"] ?>">
						<td valign="top" align="center">
							<input type="text" disabled readonly id="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
							<input type="hidden" name="CAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= htmlspecialcharsbx($str_CAT_ID) ?>">
						</td>
						<td valign="top" align="center">
							<input type="text" disabled readonly id="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

						</td>
						<td valign="top" align="center">
							<?php
							echo CExtra::SelectBox("CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeExtraEx(this)", $allowEditPrices.' id="'."CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
							?>

						</td>
						<td valign="top" align="center">
							<input type="text"<?= $allowEditPrices; ?> id="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE) ?>" size="10" OnChange="ptPriceChangeEx(this)">

						</td>
						<td valign="top" align="center">

							<?= CCurrency::SelectBox("CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeCurrencyEx(this)", $allowEditPrices.' id="'."CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
							<script>
								jsUtils.addEvent(window, 'load', function() {ChangeExtraEx(document.getElementById('CAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
							</script>

						</td>
					</tr>
						<?php
					}

					if ($bVarsFromForm && $ind < intval(${"CAT_ROW_COUNTER_".$arCatalogGroup["ID"]}))
					{
						for ($i = $ind + 1; $i <= intval(${"CAT_ROW_COUNTER_".$arCatalogGroup["ID"]}); $i++)
						{
							$boolExistPrice = false;
							$ind++;
							$str_CAT_QUANTITY_FROM = ${"CAT_BASE_QUANTITY_FROM_".$ind};
							$str_CAT_QUANTITY_TO = ${"CAT_BASE_QUANTITY_TO_".$ind};
							$str_CAT_EXTRA = ${"CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind};
							$str_CAT_PRICE = ${"CAT_PRICE_".$arCatalogGroup["ID"]."_".$ind};
							$str_CAT_CURRENCY = ${"CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind};
							if (trim($str_CAT_PRICE) != '' && doubleval($str_CAT_PRICE) >= 0)
								$boolExistPrice = true;
							$arCatPricesExist[$ind][$arCatalogGroup['ID']] = ($boolExistPrice == true ? 'Y' : 'N');
							?>
						<tr id="model3_<?= $arCatalogGroup["ID"] ?>">
							<td valign="top" align="center">
								<input type="text" disabled readonly id="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_FROM != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_FROM) : "") ?>" size="3">
								<input type="hidden" name="CAT_ID_<?= $arCatalogGroup["ID"] ?>[<?= $ind ?>]" value="<?= 0 ?>">
							</td>
							<td valign="top" align="center">
								<input type="text" disabled readonly id="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= ($str_CAT_QUANTITY_TO != 0 ? htmlspecialcharsbx($str_CAT_QUANTITY_TO) : "") ?>" size="3">

							</td>
							<td valign="top" align="center">
								<?php
								echo CExtra::SelectBox("CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_EXTRA, GetMessage("VAL_NOT_SET"), "ChangeExtraEx(this)", $allowEditPrices.' id="'."CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
								?>

							</td>
							<td valign="top" align="center">
								<input type="text"<?= $allowEditPrices; ?> id="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="<?= htmlspecialcharsbx($str_CAT_PRICE); ?>" size="10" OnChange="ptPriceChangeEx(this)">

							</td>
							<td valign="top" align="center">

								<?= CCurrency::SelectBox("CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, $str_CAT_CURRENCY, GetMessage("VAL_BASE"), true, "ChangeCurrencyEx(this)", $allowEditPrices.' id="'."CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>
								<script>
									jsUtils.addEvent(window, 'load', function () {ChangeExtraEx(document.getElementById('CAT_EXTRA_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>'));});
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
					<tr id="model3_<?= $arCatalogGroup["ID"] ?>">
						<td valign="top" align="center">
							<input type="text" disabled readonly id="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_FROM_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="3">
						</td>
						<td valign="top" align="center">
							<input type="text" disabled readonly id="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_QUANTITY_TO_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="3">

						</td>
						<td valign="top" align="center">
							<?php
							echo CExtra::SelectBox("CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_NOT_SET"), "ChangeExtraEx(this)", $allowEditPrices.' id="'."CAT_EXTRA_".$arCatalogGroup["ID"]."_".$ind.'" ');
							?>

						</td>
						<td valign="top" align="center">
							<input type="text"<?= $allowEditPrices; ?> id="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" name="CAT_PRICE_<?= $arCatalogGroup["ID"] ?>_<?= $ind ?>" value="" size="10" OnChange="ptPriceChangeEx(this)">

						</td>
						<td valign="top" align="center">

							<?= CCurrency::SelectBox("CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind, "", GetMessage("VAL_BASE"), true, "ChangeCurrencyEx(this)", $allowEditPrices.' id="'."CAT_CURRENCY_".$arCatalogGroup["ID"]."_".$ind.'" ') ?>

						</td>
					</tr>
						<?php
						$arCatPricesExist[$ind][$arCatalogGroup['ID']] = 'N';
					}
					?>
				</tbody>
			</table>
			<input type="hidden" name="CAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" id="CAT_ROW_COUNTER_<?= $arCatalogGroup["ID"] ?>" value="<?= $ind ?>">
		</td>
	</tr>
		<?php
	}
	}
	?>
</table>
<div id="ext_price_exist">
	<?php
	foreach ($arCatPricesExist as $ind => $arPriceExist)
	{
		$strExist = (in_array('Y',$arPriceExist) ? 'Y' : 'N');
		?><input type="hidden" name="CAT_PRICE_EXIST_<?= $ind; ?>" id="CAT_PRICE_EXIST_<?= $ind; ?>" value="<?= $strExist; ?>"><?php
	}
	?>
</div>
</div>
	<?php
	$tabControl1->BeginNextTab();
	?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="catalog_properties_table">
	<?php
	if ($isService)
	{
		?>
		<tr id="CAT_BASE_QUANTITY2">
			<td width="40%"><?= GetMessage('C2IT_SERVICE_SIMPLE_AVAILABLE'); ?></td>
			<td width="60%"><?php
				$serviceAvailable = $arBaseProduct['AVAILABLE'] === Catalog\ProductTable::STATUS_YES;
				if (
					$bVarsFromForm
				)
				{
					$serviceAvailable = isset($CAT_BASE_AVAILABLE) && $CAT_BASE_AVAILABLE === 'Y';
				}
				?>
				<input type="hidden" id="CAT_BASE_AVAILABLE_N" name="CAT_BASE_AVAILABLE" value="N">
				<input type="checkbox" id="CAT_BASE_AVAILABLE_Y" name="CAT_BASE_AVAILABLE" value="Y"<?= $disableProduct; ?><?= ($serviceAvailable ? ' checked="checked"' : ''); ?>>
			</td>
		</tr><?php
	}
	else
	{
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
					if (isset($_POST['CAT_BASE_QUANTITY']) && is_string($_POST['CAT_BASE_QUANTITY']))
					{
						$currentQuantity['QUANTITY'] = $_POST['CAT_BASE_QUANTITY'];
					}
					if (isset($_POST['CAT_BASE_QUANTITY_RESERVED']) && is_string($_POST['CAT_BASE_QUANTITY_RESERVED']))
					{
						$currentQuantity['QUANTITY_RESERVED'] = $_POST['CAT_BASE_QUANTITY_RESERVED'];
					}
				}
			}
			?>
			<tr id="CAT_BASE_QUANTITY2">
			<td width="40%"><?= GetMessage("FULL_QUANTITY"); ?>:</td>
			<td width="60%">
				<input type="text" id="CAT_BASE_QUANTITY" name="CAT_BASE_QUANTITY"<?= $disableQuantityFields; ?> value="<?= htmlspecialcharsbx($currentQuantity['QUANTITY']); ?>" size="30">
			</td>
			</tr><?php
			if (
				$bEnableReservation
				&& !$productIsSet
			)
			{
				?>
				<tr id="CAT_BASE_QUANTITY_RESERV">
					<td width="40%"><?= GetMessage("BASE_QUANTITY_RESERVED"); ?>:</td>
					<td width="60%">
						<input type="text" id="CAT_BASE_QUANTITY_RESERVED" name="CAT_BASE_QUANTITY_RESERVED"<?= $disableQuantityFields; ?> onfocus="ShowNotice()" onblur="HideNotice()" value="<?= htmlspecialcharsbx($currentQuantity['QUANTITY_RESERVED']); ?>" size="30">
						<span id="CAT_QUANTITY_RESERVED_DIV" style="color: #af2d49; margin-left: 10px; display: none;">	<?= GetMessage("QUANTITY_RESERVED_NOTICE"); ?></span>
					</td>
				</tr>
				<?php
			}
			unset($currentQuantity);
		}
	}
	if ($productIsSet)
	{
		?>
		<tr><td colspan="2">
			<div class="adm-info-message-wrap">
				<div class="adm-info-message">
					<?= GetMessage('SET_NOTICE_QUANTITY'); ?>
				</div>
			</div>
		</td></tr><?php
	}
	$showMeasures = $arMainCatalog['SUBSCRIPTION'] !== 'Y';
	$showMeasureRatio = (
		$arMainCatalog['SUBSCRIPTION'] !== 'Y'
		&& !$isService
	);
	$showQuantityTrace = (
		$arMainCatalog['SUBSCRIPTION'] !== 'Y'
		&& !$isService
	);
	$showProductSubscribe = (
		$arMainCatalog['SUBSCRIPTION'] !== 'Y'
		&& !$isService
	);
	$showPhysics = (
		$arMainCatalog['SUBSCRIPTION'] !== 'Y'
		&& !$isService
	);
	if ($showMeasures):
	?>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_MEASURE"); ?>:</td>
			<td width="60%"><?php
				$arAllMeasure = [];
				$dbResultList = CCatalogMeasure::getList(
					[],
					[],
					false,
					false,
					["ID", "CODE", "MEASURE_TITLE", "SYMBOL_INTL", "IS_DEFAULT"]
				);
				while($arMeasure = $dbResultList->Fetch())
				{
					$arAllMeasure[] = $arMeasure;
				}
				$str_CAT_MEASURE = $arBaseProduct["MEASURE"];
				if($bVarsFromForm)
					$str_CAT_MEASURE = $CAT_MEASURE;
				if(!empty($arAllMeasure)):?>
					<select style="max-width:220px" id="CAT_MEASURE" name="CAT_MEASURE"<?= $disableBundle; ?>>
						<?php
						foreach($arAllMeasure as $arMeasure):?>
							<option<?= ($str_CAT_MEASURE == $arMeasure["ID"] || ($str_CAT_MEASURE == '' && $arMeasure["IS_DEFAULT"] == 'Y') ? ' selected' : ''); ?> value="<?=$arMeasure["ID"]?>"><?= htmlspecialcharsbx($arMeasure["MEASURE_TITLE"]); ?></option>
						<?php
						endforeach;
						unset($arMeasure);
						?>
					</select>
				<?php
				else:
					$measureListUrl = $selfFolderUrl.'cat_measure_list.php?lang='.LANGUAGE_ID;
					$measureListUrl = $adminSidePanelHelper->editUrlToPublicPage($measureListUrl);
					echo GetMessage("C2IT_MEASURE_NO_MEASURE"); ?> <a target="_top" href="<?=$measureListUrl?>"><?= GetMessage("C2IT_MEASURES"); ?></a><br><?php
				endif;?>
			</td>
		</tr>
	<?php
	endif;
	if ($showMeasureRatio)
	{
	if (!empty($arAllMeasure))
	{
		?>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_MEASURE_RATIO"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_MEASURE_RATIO = null;
				$CAT_MEASURE_RATIO_ID = 0;
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
						if (!$bCopy)
							$CAT_MEASURE_RATIO_ID = $ar_CAT_MEASURE_RATIO["ID"];
						if ($ar_CAT_MEASURE_RATIO['IS_DEFAULT'] == 'Y')
							break;
					}
				}
				unset($db_CAT_MEASURE_RATIO, $ar_CAT_MEASURE_RATIO);
				if ($str_CAT_MEASURE_RATIO === null)
					$str_CAT_MEASURE_RATIO = 1;
				if($bVarsFromForm)
					$str_CAT_MEASURE_RATIO = $CAT_MEASURE_RATIO;
				?>
				<input type="text"<?= $disableBundle; ?> id="CAT_MEASURE_RATIO" name="CAT_MEASURE_RATIO" value="<?= htmlspecialcharsbx($str_CAT_MEASURE_RATIO); ?>" size="30">
				<input type="hidden" id="CAT_MEASURE_RATIO_ID" name="CAT_MEASURE_RATIO_ID" value="<?= htmlspecialcharsbx($CAT_MEASURE_RATIO_ID); ?>">
			</td>
		</tr>
		<?php
	}
	if ($productIsSet)
	{
		?>
		<tr><td colspan="2">
			<div class="adm-info-message-wrap">
				<div class="adm-info-message">
					<?= GetMessage('SET_NOTICE_MEASURE'); ?>
				</div>
			</div>
		</td></tr><?php
	}
	}
	else
	{
		?><input type="hidden" id="CAT_MEASURE_RATIO" name="CAT_MEASURE_RATIO" value="1"><?php
	}
	if (
		$showQuantityTrace || $showProductSubscribe
	):
	?>
		<tr class="heading">
			<td colspan="2"><?= GetMessage("C2IT_PARAMS"); ?></td>
		</tr>
<?php
	endif;
	if ($showQuantityTrace):
?>
		<tr>
			<td width="40%"><?= GetMessage("ENABLE_STORE_TRACE"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_QUANTITY_TRACE = $arBaseProduct["QUANTITY_TRACE_ORIG"];
				if ($bVarsFromForm) $str_CAT_BASE_QUANTITY_TRACE = $CAT_BASE_QUANTITY_TRACE;
				?>
				<select id="CAT_BASE_QUANTITY_TRACE" name="CAT_BASE_QUANTITY_TRACE"<?= $disableBundle; ?>>
					<option value="D" <?= ("D"==$str_CAT_BASE_QUANTITY_TRACE ? ' selected' : ''); ?>><?=GetMessage("C2IT_DEFAULT_NEGATIVE")." ("?><?= $availQuantityTrace=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>)</option>
					<option value="Y" <?= ("Y"==$str_CAT_BASE_QUANTITY_TRACE ? ' selected' : ''); ?>><?=GetMessage("C2IT_YES_NEGATIVE")?></option>
					<option value="N" <?= ("N"==$str_CAT_BASE_QUANTITY_TRACE ? ' selected' : ''); ?>><?=GetMessage("C2IT_NO_NEGATIVE")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_CAN_BUY_NULL_EXT"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_CAN_BUY_ZERO = $arBaseProduct["CAN_BUY_ZERO_ORIG"];
				if ($bVarsFromForm) $str_CAT_BASE_CAN_BUY_ZERO = $USE_STORE;
				?>
				<select id="USE_STORE" name="USE_STORE"<?= $disableBundle; ?>>
					<option value="D"<?= ("D"==$str_CAT_BASE_CAN_BUY_ZERO ? ' selected' : ''); ?>><?= GetMessage("C2IT_DEFAULT_NEGATIVE")." ("; ?><?= $availCanBuyZero=='Y' ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>)</option>
					<option value="Y"<?= ("Y"==$str_CAT_BASE_CAN_BUY_ZERO ? ' selected' : ''); ?>><?= GetMessage("C2IT_YES_NEGATIVE"); ?></option>
					<option value="N"<?= ("N"==$str_CAT_BASE_CAN_BUY_ZERO ? ' selected' : ''); ?>><?= GetMessage("C2IT_NO_NEGATIVE"); ?></option>
				</select>
			</td>
		</tr>
	<?php
	endif;
	if ($showProductSubscribe):
	?>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_SUBSCRIBE"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_SUBSCRIBE = $arBaseProduct["SUBSCRIBE_ORIG"];
				if ($bVarsFromForm) $str_CAT_SUBSCRIBE = $SUBSCRIBE;
				?>
				<select id="SUBSCRIBE" name="SUBSCRIBE"<?= $disableProduct; ?>>
					<option value="D"<?= ("D"==$str_CAT_SUBSCRIBE ? ' selected' : ''); ?>><?= GetMessage("C2IT_DEFAULT_NEGATIVE")." ("; ?><?= 'Y' == $strGlobalSubscribe ? GetMessage("C2IT_YES_NEGATIVE") : GetMessage("C2IT_NO_NEGATIVE")?>)</option>
					<option value="Y"<?= ("Y"==$str_CAT_SUBSCRIBE ? ' selected' : ''); ?>><?= GetMessage("C2IT_YES_NEGATIVE"); ?></option>
					<option value="N"<?= ("N"==$str_CAT_SUBSCRIBE ? ' selected' : ''); ?>><?= GetMessage("C2IT_NO_NEGATIVE"); ?></option>
				</select>
			</td>
		</tr>
		<?php
		if ($productIsSet)
		{
			?>
			<tr><td colspan="2">
				<div class="adm-info-message-wrap">
					<div class="adm-info-message">
						<?= GetMessage('SET_NOTICE_AVAILAVLE'); ?>
					</div>
				</div>
			</td></tr><?php
		}
	endif;
	if ($showPhysics):
		?>
		<tr class="heading">
			<td colspan="2"><?= GetMessage("C2IT_MEASUREMENTS_EXT")?></td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("BASE_WEIGHT")?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_WEIGHT = $arBaseProduct["WEIGHT"];
				if ($bVarsFromForm) $str_CAT_BASE_WEIGHT = $CAT_BASE_WEIGHT;
				?>
				<input type="text"<?= $disableBundle; ?> id="CAT_BASE_WEIGHT" name="CAT_BASE_WEIGHT" value="<?= htmlspecialcharsbx($str_CAT_BASE_WEIGHT); ?>" size="30">
			</td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_BASE_LENGTH"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_LENGTH = $arBaseProduct["LENGTH"];
				if ($bVarsFromForm) $str_CAT_BASE_LENGTH = $CAT_BASE_LENGTH;
				?>
				<input type="text"<?= $disableProduct; ?> id="CAT_BASE_LENGTH" name="CAT_BASE_LENGTH" value="<?= htmlspecialcharsbx($str_CAT_BASE_LENGTH); ?>" size="30">
			</td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_BASE_WIDTH"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_WIDTH = $arBaseProduct["WIDTH"];
				if ($bVarsFromForm) $str_CAT_BASE_WIDTH = $CAT_BASE_WIDTH;
				?>
				<input type="text"<?= $disableProduct; ?> id="CAT_BASE_WIDTH" name="CAT_BASE_WIDTH" value="<?= htmlspecialcharsbx($str_CAT_BASE_WIDTH); ?>" size="30">
			</td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("C2IT_BASE_HEIGHT"); ?>:</td>
			<td width="60%"><?php
				$str_CAT_BASE_HEIGHT = $arBaseProduct["HEIGHT"];
				if ($bVarsFromForm) $str_CAT_BASE_HEIGHT = $CAT_BASE_HEIGHT;
				?>
				<input type="text"<?= $disableProduct; ?> id="CAT_BASE_HEIGHT" name="CAT_BASE_HEIGHT" value="<?= htmlspecialcharsbx($str_CAT_BASE_HEIGHT); ?>" size="30">
			</td>
		</tr>
	<?php
		if ($productIsSet)
		{
			?>
			<tr><td colspan="2">
				<div class="adm-info-message-wrap">
					<div class="adm-info-message">
						<?= GetMessage('SET_NOTICE_WEIGHT'); ?>
					</div>
				</div>
			</td></tr><?php
		}
	endif;
	if ($arMainCatalog['SUBSCRIPTION'] == 'Y')
	{
	?>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("C2IT_SUBSCR_PARAMS"); ?></td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("C2IT_PAY_TYPE"); ?></td>
		<td width="60%">
			<script>
			function ChangePriceType()
			{
				if (!allowEdit)
					return;

				var e_pt = document.getElementById('CAT_PRICE_TYPE');

				var e_pt_value = '';
				if (-1 < e_pt.selectedIndex)
					e_pt_value = e_pt.options[e_pt.selectedIndex].value;
				if (e_pt_value == "S")
				{
					document.getElementById('CAT_RECUR_SCHEME_TYPE').disabled = true;
					document.getElementById('CAT_RECUR_SCHEME_LENGTH').disabled = true;
					document.getElementById('CAT_TRIAL_PRICE_ID').disabled = true;
					document.getElementById('CAT_TRIAL_PRICE_ID_BUTTON').disabled = true;
				}
				else if (e_pt_value == "R")
				{
						document.getElementById('CAT_RECUR_SCHEME_TYPE').disabled = false;
						document.getElementById('CAT_RECUR_SCHEME_LENGTH').disabled = false;
						document.getElementById('CAT_TRIAL_PRICE_ID').disabled = true;
						document.getElementById('CAT_TRIAL_PRICE_ID_BUTTON').disabled = true;
				}
				else
				{
					document.getElementById('CAT_RECUR_SCHEME_TYPE').disabled = false;
					document.getElementById('CAT_RECUR_SCHEME_LENGTH').disabled = false;
					document.getElementById('CAT_TRIAL_PRICE_ID').disabled = false;
					document.getElementById('CAT_TRIAL_PRICE_ID_BUTTON').disabled = false;
				}
			}
			</script>
			<?php
			$str_CAT_PRICE_TYPE = $arBaseProduct["PRICE_TYPE"];
			if ($bVarsFromForm) $str_CAT_PRICE_TYPE = $CAT_PRICE_TYPE;
			?>
			<select id="CAT_PRICE_TYPE" name="CAT_PRICE_TYPE"<?= $disableProduct; ?> onchange="ChangePriceType()">
				<option value="S"<?= ($str_CAT_PRICE_TYPE=="S" ? ' selected' : ''); ?>><?= GetMessage("C2IT_SINGLE")?></option>
				<option value="R"<?= ($str_CAT_PRICE_TYPE=="R" ? ' selected' : ''); ?>><?= GetMessage("C2IT_REGULAR")?></option>
				<option value="T"<?= ($str_CAT_PRICE_TYPE=="T" ? ' selected' : ''); ?>><?= GetMessage("C2IT_TRIAL")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("C2IT_PERIOD_LENGTH")?></td>
		<td width="60%"><?php
		$str_CAT_RECUR_SCHEME_LENGTH = $arBaseProduct["RECUR_SCHEME_LENGTH"];
		if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_LENGTH = $CAT_RECUR_SCHEME_LENGTH;
		?>
			<input type="text"<?= $disableProduct; ?> id="CAT_RECUR_SCHEME_LENGTH" name="CAT_RECUR_SCHEME_LENGTH" value="<?= htmlspecialcharsbx($str_CAT_RECUR_SCHEME_LENGTH); ?>" size="10">
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("C2IT_PERIOD_TIME"); ?></td>
		<td width="60%"><?php
		$str_CAT_RECUR_SCHEME_TYPE = $arBaseProduct["RECUR_SCHEME_TYPE"];
		if ($bVarsFromForm) $str_CAT_RECUR_SCHEME_TYPE = $CAT_RECUR_SCHEME_TYPE;
		?>
			<select id="CAT_RECUR_SCHEME_TYPE" name="CAT_RECUR_SCHEME_TYPE"<?= $disableProduct; ?>>
			<?php
			foreach ($periodTimeTypes as $key => $value)
			{
				?><option value="<?= $key ?>"<?= ($str_CAT_RECUR_SCHEME_TYPE == $key ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option><?php
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("C2IT_TRIAL_FOR")?></td>
		<td width="60%"><?php
		$str_CAT_TRIAL_PRICE_ID = $arBaseProduct["TRIAL_PRICE_ID"];
		if ($bVarsFromForm) $str_CAT_TRIAL_PRICE_ID = $CAT_TRIAL_PRICE_ID;
		$catProductName = "";
		$str_CAT_TRIAL_PRICE_ID = intval($str_CAT_TRIAL_PRICE_ID);
		if (0 < $str_CAT_TRIAL_PRICE_ID)
		{
			$dbCatElement = CIBlockElement::GetList(
				array(),
				array('ID' => $str_CAT_TRIAL_PRICE_ID),
				false,
				false,
				array('ID', 'NAME')
			);
			if ($arCatElement = $dbCatElement->Fetch())
				$catProductName = $arCatElement["NAME"];
		}
		?>
			<input<?= $disableProduct; ?> id="CAT_TRIAL_PRICE_ID" name="CAT_TRIAL_PRICE_ID" value="<?= htmlspecialcharsbx($str_CAT_TRIAL_PRICE_ID); ?>" size="5" type="text"><input type="button"<?= $disableProduct; ?> id="CAT_TRIAL_PRICE_ID_BUTTON" name="CAT_TRIAL_PRICE_ID_BUTTON" value="..." onclick="window.open('cat_product_search.php?IBLOCK_ID=<?= $IBLOCK_ID ?>&amp;field_name=CAT_TRIAL_PRICE_ID&amp;alt_name=trial_price_alt&amp;form_name='+getElementFormName(), '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));">&nbsp;<span id="trial_price_alt"><?= htmlspecialcharsex($catProductName); ?></span>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("C2IT_WITHOUT_ORDER"); ?></td>
		<td width="60%"><?php
		$str_CAT_WITHOUT_ORDER = $arBaseProduct["WITHOUT_ORDER"];
		if ($bVarsFromForm) $str_CAT_WITHOUT_ORDER = $CAT_WITHOUT_ORDER;
		?>
			<input type="checkbox"<?= $disableProduct; ?> name="CAT_WITHOUT_ORDER" value="Y"<?= ($str_CAT_WITHOUT_ORDER=="Y" ? ' checked' : ''); ?>>
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
<script>
	SetFieldsStyle('catalog_properties_table');
<?php
if ('Y' == $arMainCatalog['SUBSCRIPTION'])
{
?>
	ChangePriceType();
<?php
}
?>
</script>
<?php
if ('Y' == $arMainCatalog['SUBSCRIPTION']):
	$tabControl1->BeginNextTab();
	?>
<script>
	function CatGroupsActivate(obj, id)
	{
		if (!allowEdit)
			return;

		var ed = document.getElementById('CAT_ACCESS_LENGTH_' + id);
		var ed1 = document.getElementById('CAT_ACCESS_LENGTH_TYPE_' + id);
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
			if (isset(${"CAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"CAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
			{
				$arCurProductGroups[$arGroup["ID"]] = array(intval(${"CAT_ACCESS_LENGTH_".$arGroup["ID"]}), ${"CAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]});
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
				<input type="checkbox" name="CAT_USER_GROUP_ID_<?= $arGroup["ID"] ?>" value="Y"<?= ($usedProductGroup ? ' checked' : ''); ?> onclick="CatGroupsActivate(this, <?= $arGroup["ID"] ?>)"<?= $disableProduct; ?>>
			</td>
			<td align="left"><?= htmlspecialcharsbx($arGroup["NAME"]); ?></td>
			<td align="center">
				<input type="text" id="CAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" name="CAT_ACCESS_LENGTH_<?= $arGroup["ID"] ?>" size="5" <?php
					echo 'value="' . htmlspecialcharsbx($accessLenghtValue) . '" ';
					echo $accessLenghtDisable;
					?>>
				<select id="CAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>" name="CAT_ACCESS_LENGTH_TYPE_<?= $arGroup["ID"] ?>"<?= $accessLenghtDisable;?>>
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

	$tabControl1->BeginNextTab();

	$arParams = array();
	if (CCatalogSku::TYPE_OFFERS == $arMainCatalog['CATALOG_TYPE'])
	{
		$arParams['SKU'] = 'Y';
		$arParams['SKU_PARAMS'] = array(
			'IBLOCK_ID' => $arMainCatalog['IBLOCK_ID'],
			'PRODUCT_IBLOCK_ID' => $arMainCatalog['PRODUCT_IBLOCK_ID'],
			'SKU_PROPERTY_ID' => $arMainCatalog['SKU_PROPERTY_ID'],
		);
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
					<a href="<?=$discountUrl.$arProductDiscounts["ID"] ?>&lang=<?=LANGUAGE_ID; ?>" target="_blank"><?= GetMessage("C2IT_MODIFY"); ?></a>
				</td>
			<?php
			}
			?>
			</tr>
			<?php
		}
		?></table><?php
	}
	?><br><?php
	echo GetMessage("C2IT_DISCOUNT_HINT");

	if (
		!$productIsSet
		&& !$isService
		&& $allowedShowQuantity
	)
	{
	$tabControl1->BeginNextTab();

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
		$select = [
			'STORE_ID',
			'AMOUNT'
		];
		if ($showStoreReserve)
		{
			$select[] = 'QUANTITY_RESERVED';
		}
		$storeIds = array_keys($storeLink);
		if (!$bCopy)
		{
			$iterator = Catalog\StoreProductTable::getList(array(
				'select' => $select,
				'filter' => [
					'=PRODUCT_ID' => $PRODUCT_ID,
					'@STORE_ID' => $storeIds,
				],
			));
			while ($row = $iterator->fetch())
			{
				$storeId = (int)$row['STORE_ID'];
				$row['AMOUNT'] = (string)$row['AMOUNT'];
				$row['QUANTITY_RESERVED'] = (string)($row['QUANTITY_RESERVED'] ?? '');
				if (
					$row['AMOUNT'] !== '0'
					|| (
						$row['QUANTITY_RESERVED'] !== '0'
						&& $row['QUANTITY_RESERVED'] !== ''
					)
				)
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
					if (isset($_POST['AR_AMOUNT'][$store]) && is_string($_POST['AR_AMOUNT'][$store]))
					{
						$storeLink[$store]['PRODUCT_AMOUNT'] = $_POST['AR_AMOUNT'][$store];
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
			<td style="text-align:center;"><input type="text" id="AR_AMOUNT_<?=$row['ID']; ?>" name="AR_AMOUNT[<?=$row['ID']?>]" size="12" value="<?=htmlspecialcharsbx($row['PRODUCT_AMOUNT']); ?>" <?= (!$bStore || $bUseStoreControl ? 'disabled readonly' : ''); ?>><?php
			if ($bStore)
			{
				?><input type="hidden" name="AR_STORE_ID[<?=$row['ID']?>]" value="<?=$row['ID']?>"><?php
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
			?><b><?= GetMessage("C2IT_STORE_NO_STORE"); ?> <a target="_top" href="<?=$storeListUrl?>"><?= GetMessage("C2IT_STORE"); ?></a></b><br><?php
		}
	}
	if (!$bUseStoreControl)
		echo "<br>".GetMessage("C2IT_STORE_HINT");
	unset($storeCount, $stores);

	if($bUseStoreControl)
	{
		$tabControl1->BeginNextTab();
		$bUseMultiplyBarcode = ($arBaseProduct['BARCODE_MULTI'] == "Y");
		$arBarcodes = array();
		if (!$bCopy)
		{
			$dbBarcode = CCatalogStoreBarCode::GetList(array(), array("PRODUCT_ID" => $PRODUCT_ID, "STORE_ID" => 0));
			while($arBarcode = $dbBarcode->Fetch())
				$arBarcodes[$arBarcode["ID"]] = $arBarcode["BARCODE"];
		}
		?>
		<input type="hidden" name="CAT_ROW_BARCODE_COUNTER" id="CAT_ROW_BARCODE_COUNTER" value="<?= $ind ?>">
		<input type="hidden" name="CAT_BARCODE_MULTIPLY" id="CAT_BARCODE_MULTIPLY_N" value="N" />
		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="catalog_barcode_table">

			<tr>
				<td width="40%"><label for="CAT_BARCODE_MULTIPLY"><?= GetMessage('C2IT_BARCODE_MULTIPLY'); ?>:</label></td>
				<td width="60%">
					<input type="checkbox" name="CAT_BARCODE_MULTIPLY" id="CAT_BARCODE_MULTIPLY" value="Y"<?= ($bUseMultiplyBarcode ? ' checked="checked"' : ''); ?><?= $disableProduct; ?> onclick="checkBarCode();">
				</td>
			</tr>

			<tr id="tr_CAT_BARCODE" class="tr-barcode-class"<?= (($bUseMultiplyBarcode) ? ' style="display: none;"' : ''); ?>>
				<td><?= GetMessage("C2IT_BAR_CODE")?>:</td><?php
				$firstBarcodeKey = intval(key($arBarcodes));
				$firstBarcode = current($arBarcodes);
				unset($arBarcodes[$firstBarcodeKey]);
				?>
				<td>
					<input type="hidden" name="AR_BARCODE_ID[<?=$firstBarcodeKey?>]" value="<?=$firstBarcodeKey?>" />
					<input type="hidden" name="CAT_BARCODE_COUNTER" id="CAT_BARCODE_COUNTER" value="0" />
					<input type="text" name="CAT_BARCODE_<?=$firstBarcodeKey?>" id="CAT_BARCODE" size="30" value="<?=htmlspecialcharsbx($firstBarcode); ?>"<?= $disableProduct; ?>>
					<input type="button" value="<?= GetMessage("C2IT_MORE")?>"<?= $disableProduct; ?> OnClick="CloneBarcodeField()">
				</td>
			</tr>
			<?php
			if(!empty($arBarcodes))
			{
				foreach($arBarcodes as $id => $barcode)
				{
				?>
					<tr id="tr_CAT_BARCODE" class="tr-barcode-class"<?= (($bUseMultiplyBarcode) ? ' style="display: none;"' : ''); ?>>
						<td></td>
						<td>
							<input type="hidden" name="AR_BARCODE_ID[<?=$id?>]" value="<?=$id?>" />
							<input type="text" name="CAT_BARCODE_<?=$id?>" id="CAT_BARCODE_<?=$id?>" size="30" value="<?= htmlspecialcharsbx($barcode); ?>"<?= $disableProduct; ?>>
						</td>
					</tr>
				<?php
				}
			}
			?>
			<?php
			if (0 < $PRODUCT_ID && '' != $arBarcodes)
			{
			?>
			<tr id="tr_CAT_BARCODE_EDIT"<?= ' style="display: none;"'; ?>>
				<td><?= GetMessage("C2IT_BAR_CODE_EDIT")?>:</td>
				<td>
					<input type="hidden" name="CAT_BARCODE_EDIT" id="CAT_BARCODE_EDIT_N" value="Y" />
					<input type="checkbox" name="CAT_BARCODE_EDIT" id="CAT_BARCODE_EDIT_Y" size="30" value="Y"<?= $disableProduct; ?>>
				</td>
			</tr>
			<?php
			}
			?>
		</table>
		<?php
	}
	}

	if($activitySubscribeTab)
	{
		$tabControl1->BeginNextTab();
		?>
		<script>
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
					<td width="40%" class="field-name"><?=GetMessage('C2IT_NUMBER_SUBSCRIPTIONS')?></td>
					<td width="60%" id="bx-catalog-subscribe-total-count"></td>
				</tr>
				<tr>
					<td width="40%" class="field-name"><?=GetMessage('C2IT_NUMBER_ACTIVE_SUBSCRIPTIONS')?></td>
					<td width="60%" id="bx-catalog-subscribe-active-count"></td>
				</tr>
				<tr>
					<td width="40%" class="field-name"><?=GetMessage('C2IT_LIST_SUBSCRIPTIONS')?></td>
					<td width="60%">
						<?php
						$subscriptionUrl = $selfFolderUrl."cat_subscription_list.php?ITEM_ID=".htmlspecialcharsbx($PRODUCT_ID)."&lang=".LANGUAGE_ID;
						$subscriptionUrl = ($publicMode ? str_replace(".php", "/", $subscriptionUrl) : $subscriptionUrl);
						?>
						<a href="<?=$subscriptionUrl?>" target="_top">
							<?=GetMessage('C2IT_LIST_SUBSCRIPTIONS_TEXT')?>
						</a>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	$tabControl1->End();
	?>
<script>
BX.ready(function(){
	var basePrice = BX('CAT_BASE_PRICE');
	if (!!basePrice && !basePrice.disabled)
		BX.bind(basePrice, 'bxchange', function(e){ ChangeBasePrice(e); });

});
</script>
	</td>
</tr>
