<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\EnumType;

$isWasSelected = false;
if(
	empty($arParams['userField']['ENTITY_VALUE_ID'])
	&& array_key_exists('SETTINGS', $arParams['userField'])
	&& !empty($arParams['userField']['SETTINGS']['DEFAULT_VALUE'])
)
{
	$arResult['VALUE'] = [(string)$arParams['userField']['SETTINGS']['DEFAULT_VALUE']];
}

?>
	<input type="hidden" name="<?= $arParams['userField']['FIELD_NAME'] ?>" value="">

<?php

if($arParams['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_CHECKBOX)
{
	foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
	{
		$isSelected = (
			in_array($key, $arResult['VALUE'])
			&& (
				!$isWasSelected
				|| $arParams['userField']['MULTIPLE'] === 'Y'
			)
		);
		$isWasSelected = ($isWasSelected || $isSelected);

		if($arParams['userField']['MULTIPLE'] === 'Y')
		{
			?>
			<label>
				<input
					type="checkbox"
					value="<?= HtmlFilter::encode($key) ?>"
					name="<?= $arParams['userField']['FIELD_NAME'] ?>"
					<?= ($isSelected ? ' checked' : '') ?>
				>
				<?= HtmlFilter::encode($val) ?>
			</label>
			<br/>
			<?php
		}
		else
		{
			?>
			<label>
				<input
					type="radio"
					value="<?= HtmlFilter::encode($key) ?>"
					name="<?= $arParams['userField']['FIELD_NAME'] ?>"
					<?= ($isSelected ? ' checked' : '') ?>
				>
				<?= HtmlFilter::encode($val) ?>
			</label>
			<br/>
		<?php }
	}
}
else
{
	?>
	<select
		class="bx-user-field-enum"
		name="<?= $arParams['userField']['FIELD_NAME'] ?>"
		<?= ($arParams['userField']['SETTINGS']['LIST_HEIGHT'] > 1 ? 'size="' . $arParams['userField']['SETTINGS']['LIST_HEIGHT'] . '"' : '') ?>
		<?= ($arParams['userField']['MULTIPLE'] === 'Y' ? 'multiple="multiple"' : '') ?>
	>
	<?php
	foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
	{
		$isSelected = (
			in_array($key, $arResult['VALUE'])
			&& (
				!$isWasSelected
				|| $arParams['userField']['MULTIPLE'] === 'Y'
			)
		);
		$isWasSelected = $isWasSelected || $isSelected;

		?>
		<option
			value="<?= HtmlFilter::encode($key) ?>"
			<?= ($isSelected ? ' selected' : '') ?>
		>
			<?= HtmlFilter::encode($val) ?>
		</option>
		<?php
	}
	?>
	</select>
	<?php
}
