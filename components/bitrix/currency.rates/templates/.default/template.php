<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 */

?>
<table class="currency-list">
<?php
if ($arParams['SHOW_CB'] === 'Y'):
	?>
	<tr>
		<td colspan="3"><b><?= Loc::getMessage('CURRENCY_SITE') ?></b></td>
	</tr>
	<?php
endif;
foreach ($arResult['CURRENCY'] as $key => $arCurrency):
	?>
	<tr>
		<td><?= $arCurrency['FROM'] ?></td>
		<td>=</td>
		<td><?= $arCurrency['BASE'] ?></td>
	</tr>
	<?php
endforeach;
if (is_array($arResult['CURRENCY_CBRF']) && $arParams['SHOW_CB'] === 'Y'):
	?>
	<tr>
		<td colspan="3"><b><?=Loc::getMessage('CURRENCY_CBRF')?></b></td>
	</tr>
	<?php
	foreach ($arResult['CURRENCY_CBRF'] as $arCurrency):
		?>
		<tr>
			<td><?=$arCurrency['FROM']?></td>
			<td>=</td>
			<td><?=$arCurrency['BASE']?></td>
		</tr>
		<?php
	endforeach;
endif;
?>
</table>