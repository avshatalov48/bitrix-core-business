<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var MoneyUfComponent $component
 * @var array $arResult
 */
?>

<div class="fields money field-wrap">
	<?php
	$isFirst = true;
	$nodes = [];

	foreach($arResult['value'] as $item)
	{
		if(!$isFirst)
		{
			print $component->getHtmlBuilder()->getMultipleValuesSeparator();
		}
		$isFirst = false;
		$nodes[] = $item['attrList']['id'];
		$id = $item['attrList']['id'];
		?>

		<span class="fields money field-item">
			<input <?= $component->getHtmlBuilder()->buildTagAttributes($item['attrList']) ?>>

			<input type="hidden" id="<?= $id ?>_input_currency" value="<?= $item['currentValue'] ?>">
			<span id="<?= $id ?>_value" class="text"><?= $item['currentValue'] ?></span>

			<select
				id="<?= $id ?>_select_currency"
				hidden
			>
				<?php
				foreach($arResult['currencies'] as $currency)
				{
					?>
					<option
						value="<?= $currency ?>"
						<?= ($currency === $item['currentCurrency'] ? 'selected ' : '') ?>
					><?= $currency ?></option>
					<?php
				}
				?>
			</select>
			<span
				id="<?= $id ?>_currency"
				class="text"
			><?= $item['currentCurrency'] ?></span>
		</span>

		<?php
	}
	?>
</div>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Money(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.Money',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>