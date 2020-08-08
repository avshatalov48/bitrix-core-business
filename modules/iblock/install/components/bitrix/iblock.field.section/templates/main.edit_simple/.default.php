<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\UserField\Types\SectionType;
use Bitrix\Main\Localization\Loc;

if(
	$arParams['userField']['SETTINGS']['DISPLAY'] !== SectionType::DISPLAY_CHECKBOX
	&&
	$arParams['userField']['MULTIPLE'] === 'Y'
)
{
	?>
	<select
		multiple="multiple"
		name="<?= $arParams['userField']['FIELD_NAME'] ?>"
		size="<?= $arParams['userField']['SETTINGS']['LIST_HEIGHT'] ?>"
		style="width: 225px;">
		<?php
		foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
		{
			$isSelected = in_array($key, $arResult['value']);
			?>
			<option
				value="<? echo $key ?>"
				<?= ($isSelected ? ' selected' : '') ?>
				title="<?= trim($val, ' .') ?>"
			>
				<?= $val ?>
			</option>
			<?php
		}
		?>
	</select>
	<?php
}
elseif($arParams['userField']['SETTINGS']['DISPLAY'] !== SectionType::DISPLAY_CHECKBOX)
{
	?>
	<select
		name="<?= $arParams['userField']['FIELD_NAME'] ?>"
		size="<?= $arParams['userField']['SETTINGS']['LIST_HEIGHT'] ?>"
		style="width: 225px;"
	>
		<?php
		$wasSelected = false;
		foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
		{
			if($wasSelected)
			{
				$isSelected = false;
			}
			else
			{
				$isSelected = in_array($key, $arResult['value']);
			}

			if($isSelected)
			{
				$wasSelected = true;
			}
			?>
			<option
				value="<?= $key ?>"
				<?= ($isSelected ? ' selected' : '') ?>
				title="<?= trim($val, ' .') ?>"
			>
				<?= $val ?>
			</option>
			<?php
		}
		?>
	</select>
	<?php
}
elseif($arParams['userField']['MULTIPLE'] === 'Y')
{
	?>
	<input
		type="hidden"
		value=""
		name="<?= $arParams['userField']['FIELD_NAME'] ?>"
	>
	<?php
	foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
	{
		$id = $arParams['userField']['FIELD_NAME'] . '_' . $key;
		$isSelected = in_array($key, $arResult['value']);
		?>
		<input
			type="checkbox"
			value="<?= $key ?>"
			name="<?= $arParams['userField']['FIELD_NAME'] ?>"
			<?= ($isSelected ? 'checked' : '') ?>
			id="<?= $id ?>"
		>
		<label for="<?= $id ?>"><?= $val ?></label>
		<br/>
		<?php
	}
}
else
{
	if($arParams['userField']['MANDATORY'] !== 'Y')
	{
		$id = $arParams['userField']['FIELD_NAME'] . '_no';
		?>
		<input
			type="radio"
			value=""
			name="<?= $arParams['userField']['FIELD_NAME'] ?>"
			id="<?= $id ?>"
		>
		<label for="<?= $id ?>"><?= Loc::getMessage('MAIN_NO') ?></label>
		<br/>
		<?
	}

	$wasSelected = false;
	foreach($arParams['userField']['USER_TYPE']['FIELDS'] as $key => $val)
	{
		$id = $arParams['userField']['FIELD_NAME'] . '_' . $key;

		if($wasSelected)
		{
			$isSelected = false;
		}
		else
		{
			$isSelected = in_array($key, $arResult['value']);
		}

		if($isSelected)
		{
			$bWasSelect = true;
		}
		?>
		<input
			type="radio"
			value="<?= $key ?>"
			name="<?= $arParams['userField']['FIELD_NAME'] ?>"
			<?= ($isSelected ? 'checked' : '') ?>
			id="<?= $id ?>"
		>
		<label for="<?= $id ?>"><?= $val ?></label>
		<br/>
		<?php
	}
}

