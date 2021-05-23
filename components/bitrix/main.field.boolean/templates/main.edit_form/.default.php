<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BooleanType;

/**
 * @var array $arResult
 */

$name = $arResult['name'];
$value = $arResult['value'];
$label = $arResult['label'];
$editInList = $arResult['userField']['EDIT_IN_LIST'];

if($arResult['type'] === BooleanType::DISPLAY_DROPDOWN)
{
	?>
	<select
		name="<?= $name ?>"
		<?= ($editInList !== 'Y' ? ' disabled="disabled"' : '') ?>
	>
		<option
			value="1"
			<?= ($value ? ' selected' : '') ?>
		>
			<?= HtmlFilter::encode($label[1]) ?>
		</option>
		<option
			value="0"
			<?= (!$value ? ' selected' : '') ?>
		>
			<?= HtmlFilter::encode($label[0]) ?>
		</option>
	</select>
	<?php
}
else if($arResult['type'] === BooleanType::DISPLAY_RADIO)
{
	?>
	<label>
		<input
			type="radio"
			value="1"
			name="<?= $name ?>"
			<?= ($value ? ' checked' : '') ?>
			<?= ($editInList !== 'Y' ? ' disabled="disabled"' : '') ?>
		>
		<?= HtmlFilter::encode($label[1]) ?>
	</label>
	<br>
	<label>
		<input
			type="radio"
			value="0"
			name="<?= $name ?>"
			<?= (!$value ? ' checked' : '') ?>
			<?= ($editInList !== 'Y' ? ' disabled="disabled"' : '') ?>
		>
		<?= HtmlFilter::encode($label[0]) ?>
	</label>
	<?php
}
else
{
	$label = Loc::getMessage('MAIN_YES');
	if(
		isset($arResult['userField']['SETTINGS']['LABEL_CHECKBOX'])
		&&
		mb_strlen($arResult['userField']['SETTINGS']['LABEL_CHECKBOX'])
	)
	{
		$label = $arResult['userField']['SETTINGS']['LABEL_CHECKBOX'];
	}
	?>
	<input
		type="hidden"
		value="0"
		name="<?= $name ?>"
	>
	<label>
		<input
			type="checkbox"
			value="1"
			name="<?= $name ?>"
			<?= ($value ? ' checked' : '') ?>
			id="<?= $name ?>"
			<?= ($editInList !== 'Y' ? ' disabled="disabled"' : '') ?>
		>
		<?= HtmlFilter::encode($label) ?>
	</label>
	<?php
}
?>