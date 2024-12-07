<?php
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Currency;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/** Check CSRF Token */
if (!check_bitrix_sessid())
{
	die("Provided security token is invalid!");
}

if (!Loader::includeModule('catalog'))
{
	die('catalog module is not included!');
}

Loc::loadMessages(__FILE__);

$currenciesList = Currency\CurrencyManager::getCurrencyList();
$basePriceType = -1;
$priceTypeList = array();
foreach (CCatalogGroup::GetListArray() as $row)
{
	$row['ID'] = (int)$row['ID'];
	$row['NAME_LANG'] = (string)$row['NAME_LANG'];
	$priceTypeList[$row['ID']] = htmlspecialcharsbx('['.$row['NAME'].']'.($row['NAME_LANG'] != '' ? ' ' : '').$row['NAME_LANG']);
	if ($row['BASE'] == 'Y')
		$basePriceType = $row['ID'];
}
unset($row);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$sourcePriceType = (isset($_SESSION['CHANGE_PRICE_PARAMS']['INITIAL_PRICE_TYPE'])
	? (int)$_SESSION['CHANGE_PRICE_PARAMS']['INITIAL_PRICE_TYPE']
	: 0
);
if ($sourcePriceType < 0 || !isset($priceTypeList[$sourcePriceType]))
	$sourcePriceType = 0;

$destinationPriceType = (isset($_SESSION['CHANGE_PRICE_PARAMS']['PRICE_TYPE'])
	? (int)$_SESSION['CHANGE_PRICE_PARAMS']['PRICE_TYPE']
	: 0
);
if ($destinationPriceType < 0 || !isset($priceTypeList[$destinationPriceType]))
	$destinationPriceType = 0;


?>
<style>
	.inactive-element
	{
		opacity: 0.4;
	}
</style>
<form method="post" id="form_SetValue">
	<?=bitrix_sessid_post()?>

	<table style="width:100%;padding-bottom: 15px;">
		<tr>
			<td style="width:25.5%; padding-left:10px">
				<input id="initialPriceTypeCheckbox" type="checkbox">
				<label class="inactive-element" id="initialPriceTypeLabel" for="initialPriceTypeSelect">
					<?=Loc::getMessage("IBLIST_CHPRICE_TABLE_INITIAL_PRICE_TYPE")?>
				</label>
			</td>
			<td>
				<span class="adm-select-wrap">
					<select id="initialPriceTypeSelect" class="adm-select inactive-element" style="width: 169px;" disabled>
						<option value="0"<?=($sourcePriceType == 0 ? ' selected' : ''); ?>><?
							echo Loc::getMessage('IBLIST_CHPRICE_PRICE_TYPE_EMPTY'); ?></option>
						<?
						foreach ($priceTypeList as $id => $title)
						{
							?><option value="<?=$id; ?>"<?=($sourcePriceType == $id ? ' selected' : ''); ?>><?
							echo $title; ?></option><?
						}
						unset($id, $title);
						?>
					</select>
				</span>
			</td>
		</tr>
	</table>

	<table class="internal" id="generator_price_table" style="width:auto;margin-bottom:10px;">
		<tbody>
			<tr class="heading">
				<td>
					<label for="tablePriceTypeIdSelect">
						<?=Loc::getMessage("IBLIST_CHPRICE_HEAD_TABLE_TYPE")?>:
					</label>
				</td>
				<td>
					<label for="tableActionChangingSelect">
						<?=Loc::getMessage("IBLIST_CHPRICE_HEAD_TABLE_ACTION")?>:
					</label>
				</td>
				<td>
					<label>
						<?=Loc::getMessage("IBLIST_CHPRICE_HEAD_TABLE_VALUE")?>:
					</label>
				</td>
				<td>
					<label for="tableUnitsSelect">
						<?=Loc::getMessage("IBLIST_CHPRICE_HEAD_TABLE_UNITS")?><span style="color:red">*</span>:
				</label>
				</td>
			</tr>
		</tbody>
		<tbody>
			<tr id="IB_SEG_0">
				<td width="25%">
					<span class="adm-select-wrap">
						<select id="tablePriceTypeIdSelect" class="adm-select" style="width: 169px;">
							<option value="0"<?=($destinationPriceType == 0 ? ' selected' : ''); ?>><?
								echo Loc::getMessage('IBLIST_CHPRICE_PRICE_TYPE_EMPTY'); ?></option>
							<?
							foreach ($priceTypeList as $id => $title)
							{
								?><option value="<?=$id; ?>"<?=($destinationPriceType == $id ? ' selected' : ''); ?>><?
								echo $title; ?></option><?
							}
							unset($id, $title);
							?>
						</select>
					</span>
				</td>
				<td width="25%">
					<span class="adm-select-wrap">
						<select id="tableActionChangingSelect" class="adm-select" style="width: 169px; max-width: 300px;" name="tableActionChangingSelect">
							<option  id="radio_changing_add" value="add"><?=Loc::getMessage("IBLIST_CHPRICE_TABLE_ACTION_TYPE_ADD")?></option>
							<option  id="radio_changing_sub" value="subtract"><?=Loc::getMessage("IBLIST_CHPRICE_TABLE_ACTION_TYPE_SUB")?></option>
						</select>
					</span>
				</td>
				<td width="25%">
					<input type="text" name="tableValueChangingPriceInput" id="tableValueChangingPriceInput" class="adm-input" placeholder="0.00">
				</td>
				<td width="25%">
					<span class="adm-select-wrap">
						<select id="tableUnitsSelect" class="adm-select" style="width: 169px;">
							<option selected value="percent">%</option>
							<option value="multiple"><?=Loc::getMessage("IBLIST_CHPRICE_TABLE_UNIT_MULTYPLE")?></option>
							<?
							foreach ($currenciesList as $currencyCode => $currencyElement)
							{
								?>
								<option
									<?
										if ( isset($_SESSION['CHANGE_PRICE_PARAMS']['UNITS'])
											&& ($_SESSION['CHANGE_PRICE_PARAMS']['UNITS'] === $currencyCode) )
										{
											echo('selected');
										}
									?> value=<?=htmlspecialcharsbx($currencyCode); ?>>
									<?=htmlspecialcharsbx($currencyElement)?>
								</option>
								<?
							}
							?>
						</select>
					</span>
				</td>
			</tr>
		</tbody>
	</table>
	<table width="100%" id="chp_radioTable">
		<tr>
			<td width="45%">
				<p style="font-weight: bold;color: #3f4b54;padding-left: 25px;">
					<?=Loc::getMessage('IBLIST_CHPRICE_TABLE_ACTION_RESULT_LABEL')?>:
				</p>
				<p>
					<input type="radio"
						<?
						if (isset($_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'])
							&& $_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'] === "floor")
						{
							echo('checked');
						}
						?>
						id="floorRadio" name="formatResultRadio" value="floor"/>
					<label for="floorRadio">
						<?=Loc::getMessage('IBLIST_CHPRICE_TABLE_ACTION_RESULT_FLOOR')?>
					</label>
				</p>
				<p>
					<input type="radio"
						<?
						if ( empty($_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'])
							|| ($_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'] === "ceil") )
						{
							echo('checked');
						}
						?>
						id="ceilRadio" name="formatResultRadio"  value="ceil"/>
					<label for="ceilRadio">
						<?=Loc::getMessage('IBLIST_CHPRICE_TABLE_ACTION_RESULT_CEIL')?>
					</label>
				</p>
				<p>
					<input type="radio"
						<?
						if (isset($_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'])
							&& $_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'] === "round")
						{
							echo('checked');
						}
						?>
						id="roundRadio" name="formatResultRadio" value="round"/>
					<label for="roundRadio">
						<?=Loc::getMessage('IBLIST_CHPRICE_TABLE_ACTION_ROUND_RESULT_ROUND')?>
					</label>
				</p>
			</td>
			<td>
				<table>
					<tr>
						<td style="width:50%; padding-left:10px">
							<input id="resultMaskCheckbox" type="checkbox">
							<label class="inactive-element" id="resultMaskLabel" for="resultMaskSelect"><?=Loc::getMessage("IBLIST_CHPRICE_TABLE_RESULT_MASK_LABEL")?></label>
						</td>
						<td style="padding-left: 20px">
							<span class="adm-select-wrap">
								<select id="resultMaskSelect" class="adm-select inactive-element" style="width: 70px;" disabled>
									<option value="100">0.01</option>
									<option value="20">0.05</option>
									<option value="10">0.1</option>
									<option value="2">0.5</option>
									<option value="1" selected>1</option>
									<option value="0.2">5</option>
									<option value="0.1">10</option>
									<option value="0.02">50</option>
									<option value="0.01">100</option>
									<option value="0.002">500</option>
									<option value="0.001">1000</option>
								</select>
							</span>
						</td>
					</tr>
					<tr>
						<td style="width:50%; padding-left:10px">
							<input id="differenceValueCheckbox" type="checkbox">
							<label class="inactive-element" id="differenceValueLabel" for="differenceValueCheckbox"><?=Loc::getMessage("IBLIST_CHPRICE_TABLE_MINUS_COUNT_LABEL")?></label>
						</td>
						<td style="padding-left: 20px">
							<input type="text" id="differenceValueInput" style="width: 140px;" placeholder="0.00" value="" disabled>
						</td>
					</tr>
				</table>
				<table style="width:100%;margin-top:15px;">
					<tr class="heading">
						<td style="width:100%">
							<?=Loc::getMessage("IBLIST_CHPRICE_EXAMPLE_LABEL")?>
						</td>
					</tr>
				</table>
				<table style="width: 100%;">
					<tr>
						<?
						$sourceInput = "<input type='text' style='width:70px;margin:0 5px' id='exampleSourceValueInput' value='11111.11'>";
						?>
						<td style="text-align: center;">
							<?=Loc::getMessage("IBLIST_CHPRICE_EXAMPLE_VALUE",array("#VALUE_BEFORE#"=>$sourceInput))?>

							<span id='resultValueSpan' style="color:#01B10E;font-weight: bold;">11111.11<span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<div style="margin-top: 45px;">
	<div style="position:relative">
		<span style="color:red;position:absolute;left:-10px;">*</span>
		<div>
			<?=Loc::getMessage('IBLIST_CHPRICE_UNITS_NOTE_PERCENT')?>
		</div>
		<div>
			<?=Loc::getMessage('IBLIST_CHPRICE_UNITS_NOTE_CURRENCY')?>
		</div>
	</div>
</div>

<?

$javascriptParams = array(
	"tableReloadId" => htmlspecialcharsbx($_POST['sTableID']),
	"alertMessages" => array(
		"onePriceType" => Loc::getMessage('IBLIST_CHPRICE_ALERT_ONE_PRICE_TYPE'),
		"nullValue" => Loc::getMessage('IBLIST_CHPRICE_ALERT_NOT_NULL'),
		"equalPriceTypes" => Loc::getMessage('IBLIST_CHPRICE_ERR_EQUAL_PRICE_TYPES'),
		"basePriceChange" => Loc::getMessage('IBLIST_CHPRICE_ERR_BASE_PRICE_SELECTED'),
		"destinationPriceEmpty" => Loc::getMessage('IBLIST_CHPRICE_ERR_DESTINATION_PRICE_EMPTY'),
		"sourcePriceEmpty" => Loc::getMessage('IBLIST_CHPRICE_ERR_SOURCE_PRICE_EMPTY')
	),
	"basePriceType" => (string)$basePriceType
);
$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/iblock_change_price.js');
?>
<script>
	var iblockChangeScript = BX.Catalog.Admin.IblockChangePrice();
	iblockChangeScript.init(<?=$javascriptParams?>);
</script>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");