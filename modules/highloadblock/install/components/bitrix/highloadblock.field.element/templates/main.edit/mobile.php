<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var HighloadblockElementUfComponent $component
 * @var array $arParams
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;

$nodes = [$arResult['userField']['~id']];
?>

<select
	name="<?= $arResult['fieldName'] ?>"
	id="<?= $arResult['userField']['~id'] ?>"
	class="mobile-grid-data-select"
	data-is-inline-edit="false"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
>
	<?php
	foreach ($arResult['userField']['USER_TYPE']['FIELDS'] as $optionValue => $optionName)
	{
		?>
		<option
			value="<?= $optionValue ?>"
			<?= in_array($optionValue, $arResult['value']) ? ' selected="selected"' : '' ?>
		><?= HtmlFilter::encode($optionName) ?></option>
		<?php
	}
	?>
</select>

<a
	href="#"
	id="<?= $arResult['userField']['~id'] ?>_select"
>
	<?php
	if (
		is_array($arResult['userField']['VALUE'])
		&& empty($arResult['userField']['VALUE'])
	)
	{
		echo \CUserTypeHlblock::getEmptyCaption($arResult['userField']);
	}
	elseif (is_array($arResult['value']))
	{
		foreach ($arResult['value'] as $value)
		{
			if (!$isFirst)
			{
				echo '<br>';
			}
			$isFirst = false;
			echo HtmlFilter::encode($arResult['userField']['USER_TYPE']['FIELDS'][$value]);
		}
	}
	?>
</a>
<?php
if ($arParams['additionalParameters']['canDrop'] !== false)
{
	?>
	<del
		id="<?= $item['attrList']['id'] ?>_del"
		<?= ($item['value'] ? '' : 'style="display:none"') ?>
	>
	</del>
	<?php
}
?>

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
