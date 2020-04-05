<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

?>

<div class="money-editor" id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['CONTROL_ID'])?>_wrap">
	<input type="hidden" id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['CONTROL_ID'])?>_value" name="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['FIELD_NAME'])?>" value="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['VALUE'])?>">
	<input type="text" tabindex="0" id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['CONTROL_ID'])?>_number" value="<?=\Bitrix\Main\Text\HtmlFilter::encode($arResult['VALUE_NUMBER'])?>" />&nbsp;<?if($arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y'):?><span id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['CONTROL_ID'])?>_currency_selector" class="money-editor-currency-selector-wrap"></span><?else:?><select tabindex="0" <?if(strlen($arParams['FIELD_NAME_CURRENCY']) > 0):?>name="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['FIELD_NAME_CURRENCY'])?>" <?endif;?>id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arParams['CONTROL_ID'])?>_currency" onchange="BX.Currency.MoneyInput.get('<?=\Bitrix\Main\Text\HtmlFilter::encode(\CUtil::JSEscape($arParams['CONTROL_ID']))?>').setCurrency(this.value)">
<?
foreach($arResult['CURRENCY_LIST'] as $currency => $currencyTitle)
{
?>
		<option value="<?=\Bitrix\Main\Text\HtmlFilter::encode($currency)?>" <?=$currency === $arResult['VALUE_CURRENCY'] ? ' selected="selected"' : ''?>><?=\Bitrix\Main\Text\HtmlFilter::encode($currencyTitle)?></option>
<?
}
?>
	</select><?endif;?>
</div>
<script>
<?
if($arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y'):
?>
	(function(){
		var currencyItems = [
<?
$index = 0;
$jsValueIndex = 0;
foreach($arResult['CURRENCY_LIST'] as $currency => $currencyTitle)
{
	if($currency === $arResult['VALUE_CURRENCY'])
	{
		$jsValueIndex = $index;
	}

	$index++
?>
			{NAME:'<?=\CUtil::JSEscape($currencyTitle)?>',VALUE:'<?=\CUtil::JSEscape($currency)?>'},
<?
}
?>
		];

		BX('<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>_currency_selector').appendChild(BX.decl({
			block: 'main-ui-select',
			name: '<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>',
			items: currencyItems,
			value: currencyItems[<?=$jsValueIndex?>],
			params: {fieldName: '<?=\CUtil::JSEscape($arParams['CONTROL_ID']);?>', isMulti: false},
			valueDelete: false
		}));

		BX.addCustomEvent(window, 'UI::Select::change', function(controlObject, value){
			if(controlObject.params.fieldName === '<?=\CUtil::JSEscape($arParams['CONTROL_ID']);?>')
			{
				var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
				if(!!currentValue)
				{
					BX.Currency.MoneyInput.get('<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>').setCurrency(currentValue.VALUE);
				}
			}
		});

	})();
<?
endif;
?>

	new BX.Currency.MoneyInput({
		controlId: '<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>',
		input: BX('<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>_number'),
		resultInput: BX('<?=\CUtil::JSEscape($arParams['CONTROL_ID'])?>_value'),
		currency: '<?=$arResult['VALUE_CURRENCY']?>'
	});

</script>