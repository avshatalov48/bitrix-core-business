<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

$isFirst = true;

$nodes = [$arResult['userField']['~id']];
?>

<select
	name="<?= $arResult['fieldName'] ?>"
	id="<?= $arResult['userField']['~id'] ?>"
	class="mobile-grid-data-select"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
>
	<?php
	foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $optionValue => $optionName)
	{
		if($optionValue)
		{
			?>
			<option
				value="<?= $optionValue ?>"
				<?= in_array($optionValue, $arResult['value']) ? ' selected="selected"' : '' ?>
			><?= $optionName ?></option>
			<?php
		}
	}
	?>
</select>

<a
	href="#"
	id="<?= $arResult['userField']['~id'] ?>_select"
>
	<?php
	if(count($arResult['value']))
	{
		foreach($arResult['value'] as $value)
		{
			if(!$isFirst)
			{
				print '<br>';
			}
			$isFirst = false;
			print $arResult['userField']['USER_TYPE']['FIELDS'][$value];
		}
	}
	?>
</a>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Enum(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.Enum',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>