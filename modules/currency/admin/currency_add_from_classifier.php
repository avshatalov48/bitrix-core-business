<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\CurrencyClassifier;
use Bitrix\Main\Text\HtmlFilter;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/prolog.php");

$currencyRights = $APPLICATION->GetGroupRight("currency");
if ($currencyRights == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

Loader::includeModule('currency');
Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_TITLE'));

$tabs = array(
	array(
		"DIV" => "classifier_tab_1",
		"TAB" => Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_TAB_SEARCH_AND_SETTINGS'),
		"ICON"=>"",
		"TITLE"=> Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_TAB_SEARCH_AND_SETTINGS_TITLE')
	),
	array(
		"DIV" => "classifier_tab_2",
		"TAB" => Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_TAB_LANGUAGE_SETTINGS'),
		"ICON"=>"",
		"TITLE"=> Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_TAB_LANGUAGE_SETTINGS_TITLE')
	),
);
$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage('MAIN_ADMIN_MENU_LIST'),
		"LINK" => "currencies.php?lang=".LANGUAGE_ID,
		"TITLE" => Loc::getMessage('MAIN_ADMIN_MENU_LIST')
	),
);

$tabControl = new CAdminTabControl("tabControl", $tabs);
$adminContext = new CAdminContextMenu($aContext);

$errorMessage = array();
$languages = array();
$languageIdList = array();

$languageList = LanguageTable::getList();
while ($language = $languageList->fetch())
{
	$languageId = $language['LID'];
	$languageIdList[] = $languageId;
	$languages[$languageId] = $language['NAME'];
}

$classifier = CurrencyClassifier::get($languageIdList, LANGUAGE_ID);
$baseCurrencyId = CurrencyManager::getBaseCurrency();
$currentElement = current($classifier);
$lastValues = array(
	'NEEDLE' => '',
	'SELECTED_INDEX' => $currentElement['SYM_CODE'],
	'NOMINAL' => 1,
	'EXCHANGE_RATE' => '',
	'SORT_INDEX' => 100
);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $currencyRights == "W" && (!empty($_POST['save']) || !empty($_POST['apply'])) && check_bitrix_sessid())
{
	$needle = $_POST['admin_classifier_currency_needle'];
	$selectedIndex = $_POST['sym_code'];
	$nominal = $_POST['nominal'];
	$exchangeRate = str_replace(',', '.', $_POST['exchange_rate']);
	$sortIndex = $_POST['sort_index'];

	$maxIntValue = 2147483647;

	if (!preg_match('/^[1-9][0-9]{0,10}$/', $nominal) || ($nominal > $maxIntValue))
		$errorMessage[] = Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELDS_NOMINAL_ERROR');

	if (!preg_match('/^[0-9]{0,14}[\.]{0,1}[0-9]{0,4}$/', $exchangeRate) ||
		($exchangeRate <= 0) ||
		($exchangeRate > 99999999999999))
	{
		$errorMessage[] = Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELDS_EXCHANGE_RATE_ERROR');
	}

	if (!preg_match('/^[1-9][0-9]{0,10}$/', $sortIndex) || ($sortIndex > $maxIntValue))
		$errorMessage[] = Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELDS_SORT_INDEX_ERROR');

	if (!empty($errorMessage))
	{
		$lastValues['NEEDLE'] = $needle;
		$lastValues['SELECTED_INDEX'] = $selectedIndex;
		$lastValues['NOMINAL'] = $nominal;
		$lastValues['EXCHANGE_RATE'] = $exchangeRate;
		$lastValues['SORT_INDEX'] = $sortIndex;
	}
	else
	{
		$currencyData = $classifier[$selectedIndex];

		$fields = array();
		$fields['NUMCODE'] = $currencyData['NUM_CODE'];
		$fields['AMOUNT_CNT'] = $nominal;
		$fields['AMOUNT'] = $exchangeRate;
		$fields['SORT'] = $sortIndex;

		$currencyId = $currencyData['SYM_CODE'];

		if ($currencyId == $baseCurrencyId)
		{
			$fields['AMOUNT_CNT'] = 1;
			$fields['AMOUNT'] = 1;
		}

		foreach ($languageIdList as $languageId)
		{
			$locFields = $currencyData[strtoupper($languageId)];

			$locFields['CURRENCY'] = $currencyId;
			$locFields['HIDE_ZERO'] = $_POST['hide_zero_' . $languageId] ? 'Y' : 'N';
			$locFields['FORMAT_STRING'] = str_replace('#VALUE#', '#', $locFields['FORMAT_STRING']);

			$fields['LANG'][$languageId] = $locFields;
		}

		$DB->StartTransaction();

		$fields['CURRENCY'] = $currencyId;
		$currencyId = CCurrency::Add($fields);
		$result = is_string($currencyId) && ($currencyId !== '');
		if (!$result)
		{
			$exception = $APPLICATION->GetException();
			$errorMessage[] = ($exception) ? $exception->GetString() : Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_UNKNOWN_ERROR_ADD');
		}

		if (!$result)
		{
			$DB->Rollback();
			$lastValues['NEEDLE'] = $needle;
			$lastValues['SELECTED_INDEX'] = $selectedIndex;
			$lastValues['NOMINAL'] = $nominal;
			$lastValues['EXCHANGE_RATE'] = $exchangeRate;
			$lastValues['SORT_INDEX'] = $sortIndex;
		}
		else
		{
			$DB->Commit();

			if ($_POST['apply'])
				LocalRedirect('/bitrix/admin/currency_edit.php?ID=' . $currencyId . '&lang=' . LANGUAGE_ID . '&' . $tabControl->ActiveTabParam());
			else
				LocalRedirect('/bitrix/admin/currencies.php?lang=' . LANGUAGE_ID);
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminContext->Show();

if (!empty($errorMessage))
	CAdminMessage::ShowMessage(implode('<br>', $errorMessage));
?>

<form method="post" action="<?$APPLICATION->GetCurPage()?>" name="admin_currency_classifier">
	<?= bitrix_sessid_post()?>
	<?$tabControl->Begin()?>
	<?$tabControl->BeginNextTab();?>
	<tr class="heading">
		<td colspan="2">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_SECTION_SEARCH_AND_CHOICE')?></label>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= HtmlFilter::encode(Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_SEARCH'));?>:</label>
		</td>
		<td width="60%">
			<input id="admin_classifier_currency_needle" name="admin_classifier_currency_needle" type="text" style="width: 300px"
				   placeholder="<?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_SEARCH_PLACEHOLDER')?>"
				   value="<?= HtmlFilter::encode($lastValues['NEEDLE']);?>">
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_CHOICE')?>:</label>
		</td>
		<td width="60%">
			<select id="admin_classifier_currency_id" name="admin_classifier_currency_id" size="10" style="width: 312px">
				<?foreach ($classifier as $key => $value)
					echo "<option value=".HtmlFilter::encode($key).">".HtmlFilter::encode($value[strtoupper(LANGUAGE_ID)]['FULL_NAME'])."</option>";?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FORM_SECTION_MAIN_SETTINGS')?></label>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_NUM_CODE')?>:</label>
		</td>
		<td width="60%" style="height: 25px">
			<label id="num_code" name="num_code" style="width: 300px"></label>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_SYM_CODE')?>:</label>
		</td>
		<td width="60%" style="height: 25px">
			<input hidden id="hidden_sym_code" name="sym_code">
			<label id="sym_code" name="sym_code" style="width: 300px"></label>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_EXCHANGE_RATE')?>:</label>
			<span class="required" style="vertical-align: super; font-size: smaller">1</span>
		</td>
		<td width="60%" style="padding-left: 1px">
			<table>
				<tr>
					<td>
						<span>
							<input id="nominal" name="nominal" type="text" style="width: 100px;" value="<?= HtmlFilter::encode($lastValues['NOMINAL']);?>" />
							<label id="nominal_sym_code" name="nominal_sym_code" style="width: 30px; display: inline-block"></label>
							<label style="margin: 0 5px 0 0">=</label>
							<input id="exchange_rate" name="exchange_rate" type="text" style="width: 100px" value="<?= HtmlFilter::encode($lastValues['EXCHANGE_RATE']);?>" />
							<label style="width: 30px; display: inline-block"><?= HtmlFilter::encode($baseCurrencyId);?></label>
						</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_SORT_INDEX')?>:</label>
		</td>
		<td width="60%">
			<input id="sort_index" name="sort_index" type="text" style="width: 300px" value="<?= HtmlFilter::encode($lastValues['SORT_INDEX']);?>">
		</td>
	</tr>
	<?$tabControl->BeginNextTab();
		foreach ($languages as $key => $value)
		{?>
			<tr class="heading">
				<td colspan="2">
					<label><?= HtmlFilter::encode($value);?></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_FULL_NAME')?>:</label>
				</td>
				<td width="60%" style="height: 25px">
					<label id="full_name_<?= HtmlFilter::encode($key);?>" name="full_name_<?= HtmlFilter::encode($key);?>"></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_FORMAT_STRING')?>:</label>
				</td>
				<td width="60%" style="height: 25px">
					<label id="format_string_<?= HtmlFilter::encode($key);?>" name="format_string_<?= HtmlFilter::encode($key);?>"></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_DEC_POINT')?>:</label>
				</td>
				<td width="60%" style="height: 25px">
					<label id="dec_point_<?= HtmlFilter::encode($key);?>" name="dec_point_<?= HtmlFilter::encode($key);?>"></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_THOUSANDS_SEPARATOR')?>:</label>
				</td>
				<td width="60%" style="height: 25px">
					<label id="thousands_sep_<?= HtmlFilter::encode($key);?>" name="thousands_sep_<?= HtmlFilter::encode($key);?>"></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_DECIMALS')?>:</label>
					<span class="required" style="vertical-align: super; font-size: smaller">2</span>
				</td>
				<td width="60%" style="height: 25px">
					<label id="decimals_<?= HtmlFilter::encode($key);?>" name="decimals_<?= HtmlFilter::encode($key);?>"></label>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FIELD_HIDE_ZERO')?>:</label>
					<span class="required" style="vertical-align: super; font-size: smaller">3</span>
				</td>
				<td width="60%" style="height: 25px">
					<input id="hide_zero_<?= HtmlFilter::encode($key);?>" name="hide_zero_<?= HtmlFilter::encode($key);?>" type="checkbox" checked onclick="return false;">
				</td>
			</tr>
		<?}
	$tabControl->EndTab();
	$tabControl->Buttons(array("disabled" => $currencyRights < "W", "back_url" =>"/bitrix/admin/currencies.php?lang=".LANGUAGE_ID));
	$tabControl->End();?>
</form>

<?= BeginNote();?>
<div style="padding: 5px">
	<label><?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FOOTER_ISO_STANDART', array('#ISO_LINK#' => CURRENCY_ISO_STANDART_URL))?></label>
</div>
<div style="padding: 5px">
	<span class="required" style="vertical-align: super; font-size: smaller;">1</span> - <?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FOOTER_EXCHANGE_RATE')?>
</div>
<div style="padding: 5px">
	<span class="required" style="vertical-align: super; font-size: smaller;">2</span> - <?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FOOTER_DECIMALS_NUMBER')?>
</div>
<div style="padding: 5px">
	<span class="required" style="vertical-align: super; font-size: smaller;">3</span> - <?= Loc::getMessage('ADMIN_CURRENCY_CLASSIFIER_FOOTER_HIDE_ZERO')?>
</div>
<?= EndNote();?>

<script type="text/javascript">
	BX.AdminCurrencyClassifierClass = (function ()
	{
		var lids = [];
		var currencies = [];
		var baseLanguage = null;

		var AdminCurrencyClassifierClass = function (parameters)
		{
			lids = parameters.lids;
			currencies = parameters.currencies;
			baseLanguage = parameters.baseLanguage;

			this.bindElements();
			this.getCurrenciesList(parameters);
			this.fillFields(parameters);
			AdminCurrencyClassifierClass.setFocus();
		};

		AdminCurrencyClassifierClass.prototype.bindElements = function()
		{
			BX.bind(BX('admin_classifier_currency_id'), 'change', function ()
			{
				AdminCurrencyClassifierClass.prototype.fillFields({
					index: BX('admin_classifier_currency_id').value
				});
			});

			BX.bind(BX('admin_classifier_currency_needle'), 'keyup', function ()
			{
				AdminCurrencyClassifierClass.prototype.getCurrenciesList({
					index: BX('admin_classifier_currency_id').value,
					lastIndex: BX('hidden_sym_code').value,
					needle: BX('admin_classifier_currency_needle').value
				});
			});
		};

		AdminCurrencyClassifierClass.prototype.getCurrenciesList = function(parameters)
		{
			var needle = parameters.needle;
			var selectIndex = parameters.index;
			var lastIndex = parameters.lastIndex;

			var select = BX('admin_classifier_currency_id');

			if (selectIndex === null || selectIndex === "")
				selectIndex = lastIndex;

			select.options.length = 0;

			Object.keys(currencies).forEach(function (key)
			{
				var haystack = currencies[key][baseLanguage.toUpperCase()]['FULL_NAME'];

				if (haystack.toLowerCase().indexOf(needle.toLowerCase()) !== -1)
				{
					var option = document.createElement("option");
					option.value = key;
					option.text = haystack;
					if (key === selectIndex)
						option.selected = true;
					select.appendChild(option);
				}
			});
		};

		AdminCurrencyClassifierClass.prototype.fillFields = function(parameters)
		{
			var currency = currencies[parameters.index];

			BX('num_code').innerHTML = currency['NUM_CODE'];
			BX('sym_code').innerHTML = currency['SYM_CODE'];
			BX('hidden_sym_code').value = currency['SYM_CODE'];
			BX('nominal_sym_code').innerHTML = currency['SYM_CODE'];

			lids.forEach(function (item)
			{
				var currencyLang = currency[item.toUpperCase()];

				BX('full_name_' + item).innerHTML = currencyLang['FULL_NAME'];
				BX('format_string_' + item).innerHTML = currencyLang['FORMAT_STRING'].replace('#VALUE#', '#');
				BX('dec_point_' + item).innerHTML = currencyLang['DEC_POINT'];
				BX('thousands_sep_' + item).innerHTML = currencyLang['THOUSANDS_SEP_DESCR'];
				BX('decimals_' + item).innerHTML = currencyLang['DECIMALS'];
			});
		};

		AdminCurrencyClassifierClass.setFocus = function()
		{
			var length = BX('admin_classifier_currency_needle').value.length;
			BX('admin_classifier_currency_needle').focus();
			BX('admin_classifier_currency_needle').setSelectionRange(length, length);
		};

		return AdminCurrencyClassifierClass;
	})();

	BX(function () {
		BX.AdminCurrencyClassifier = new BX.AdminCurrencyClassifierClass(<?= Json::encode(array(
			'index' => $lastValues['SELECTED_INDEX'],
			'lastIndex' => $lastValues['SELECTED_INDEX'],
			'needle' => $lastValues['NEEDLE'],
			'currencies' => $classifier,
			'lids' => $languageIdList,
			'baseLanguage' => LANGUAGE_ID
		));?>);
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");