<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\Text\HtmlFilter;

if ($arResult['userField']['EDIT_IN_LIST'] === 'Y')
{
	?>
	<div class="money-editor">
		<input
			type="hidden"
			name="<?= $arResult['additionalParameters']['NAME'] ?>"
			value="<?= $arResult['additionalParameters']['VALUE'] ?>"
		>
		<input
			type="text"
			tabindex="0"
			value="<?= HtmlFilter::encode($arResult['VALUE_NUMBER']) ?>"
			onchange="var currency = this.nextElementSibling.value; var money = this.value; this.previousElementSibling.value = money+'|'+currency;"
		/>
		<select
			tabindex="0"
			onchange="var currency = this.value; var money = this.previousElementSibling.value; this.parentNode.firstElementChild.value = money+'|'+currency;"
		>
			<?php
			foreach ($arResult['CURRENCY_LIST'] as $currency => $currencyTitle)
			{
				?>
				<option value="<?= HtmlFilter::encode($currency) ?>"
					<?= ($currency === $arResult['VALUE_CURRENCY'] ? ' selected="selected"' : '') ?>
				>
					<?= HtmlFilter::encode($currencyTitle) ?>
				</option>
				<?php
			}
			?>
		</select>
	</div>
	<?php
}
elseif (($arResult['additionalParameters']['VALUE'] ?? '') !== '')
{
	$explode = MoneyType::unFormatFromDB($arResult['additionalParameters']['VALUE']);
	$currentValue = $explode[0] ?: '';
	$currentCurrency = $explode[1]?? '';

	if (!$currentCurrency)
	{
		print ((int)$currentValue? $currentValue : '');
	}
	else
	{
		print CCurrencyLang::CurrencyFormat($currentValue, $currentCurrency, true);
	}

}
else
{
	print '&nbsp;';
}
