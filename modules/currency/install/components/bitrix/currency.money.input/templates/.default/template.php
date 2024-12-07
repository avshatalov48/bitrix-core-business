<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

$safeCountrolId = HtmlFilter::encode($arParams['CONTROL_ID']);
$jsSafeControlId = \CUtil::JSEscape($arParams['CONTROL_ID']);

?>
<div
	class="money-editor"
	id="<?= $safeCountrolId ?>_wrap"
>
	<input
		id="<?= $safeCountrolId ?>_value"
		type="hidden"
		name="<?= HtmlFilter::encode($arParams['FIELD_NAME']) ?>"
		value="<?= HtmlFilter::encode($arParams['VALUE']) ?>"
	>
	<input
		id="<?= $safeCountrolId ?>_number"
		type="text"
		tabindex="0"
		value="<?= \Bitrix\Main\Text\HtmlFilter::encode($arResult['VALUE_NUMBER']) ?>"
	/> <?php
	if ($arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y'):
		?>
		<span
			class="money-editor-currency-selector-wrap"
			id="<?= $safeCountrolId ?>_currency_selector"
		></span>
		<?php
	else:
		?>
		<select
			id="<?= $safeCountrolId ?>_currency"
			<?php
			if ($arParams['FIELD_NAME_CURRENCY'] !== ''):
				?>
				name="<?= HtmlFilter::encode($arParams['FIELD_NAME_CURRENCY']) ?>"
				<?php
			endif;
			?>
			tabindex="0"
			onchange="BX.Currency.MoneyInput.get('<?= HtmlFilter::encode($jsSafeControlId) ?>').setCurrency(this.value)"
		>
		<?php
		foreach($arResult['CURRENCY_LIST'] as $currency => $currencyTitle):
			$selected =
				$currency === $arResult['VALUE_CURRENCY']
					? ' selected="selected"'
					: ''
			;
			?>
			<option value="<?= HtmlFilter::encode($currency) ?>" <?= $selected ?>><?= HtmlFilter::encode($currencyTitle) ?></option>
			<?php
		endforeach;
		?>
		</select>
		<?php
	endif;
	?>
</div>
<script>
	<?php
	if ($arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y'):
	?>
	(function ()
	{
		const currencyItems = [
			<?php
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
				{
					NAME: '<?= \CUtil::JSEscape($currencyTitle) ?>',
					VALUE: '<?= \CUtil::JSEscape($currency) ?>',
				},
				<?php
			}
			?>
		];

		BX('<?= $jsSafeControlId ?>_currency_selector').appendChild(BX.decl({
			block: 'main-ui-select',
			name: '<?= $jsSafeControlId ?>',
			items: currencyItems,
			value: currencyItems[<?=$jsValueIndex?>],
			params: {
				fieldName: '<?= $jsSafeControlId ?>',
				isMulti: false,
				classPopup: 'currency-money-popup-full-width',
			},
			valueDelete: false,
		}));

		BX.addCustomEvent(window, 'UI::Select::change', function (controlObject, value)
		{
			if (controlObject.params.fieldName === '<?= $jsSafeControlId ?>')
			{
				const currentValue = JSON.parse(controlObject.node.getAttribute('data-value'))
				if (BX.type.isPlainObject(currentValue))
				{
					BX.Currency.MoneyInput.get('<?= $jsSafeControlId ?>').setCurrency(currentValue.VALUE);
				}
			}
		});

	})();
	<?php
	endif;
	?>

	new BX.Currency.MoneyInput({
		controlId: '<?= $jsSafeControlId ?>',
		input: BX('<?= $jsSafeControlId ?>_number'),
		resultInput: BX('<?= $jsSafeControlId ?>_value'),
		currency: '<?=$arResult['VALUE_CURRENCY']?>'
	});

</script>