<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/** @var array $arResult */

if ($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_CHECKBOX)
{
	if($arResult['userField']['MULTIPLE'] === 'Y')
	{
		$type = 'checkbox';
		?>
		<input
			type="hidden"
			value=""
			name="<?= HtmlFilter::encode($arResult['fieldName']) ?>"
		>
		<?php
	}
	else
	{
		$type = 'radio';
	}

	$isWasSelect = false;
	$result = '';

	foreach($arResult['additionalParameters']['items'] as $itemId => $item)
	{
		$isSelected = in_array($itemId, $arResult['additionalParameters']['VALUE']);
		$isWasSelect = ($isWasSelect || $isSelected);
		$checked = ($isSelected ? ' checked' : '');
		$editInList = ($arResult['userField']['EDIT_IN_LIST'] !== 'Y' ? ' disabled="disabled" ' : '');
		$result .= <<<EOL
				<label>
					<input 
						type="{$type}" 
						value="{$item['ID']}"  
						name="{$arResult['fieldName']}"
						{$checked} 
						{$editInList}
					>
					{$item['VALUE']}
				</label>
				<br>
EOL;
	}
	if(
		$arResult['userField']['MANDATORY'] !== 'Y'
		&& $arResult['userField']['MULTIPLE'] !== 'Y'
	)
	{
		?>
		<label>
			<input
				type="<?= $type ?>"
				value=""
				name="<?= HtmlFilter::encode($arResult['fieldName']) ?>"
				<?= (!$isWasSelect ? ' checked' : '') ?>
				<?= ($arResult['userField']['EDIT_IN_LIST'] !== 'Y' ? ' disabled="disabled" ' : '') ?>
			>
			<?= HtmlFilter::encode(\CUserTypeHlblock::getEmptyCaption($arResult['userField'])) ?>
		</label>
		<br>
		<?php
	}
	print $result;
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_LIST)
{
	?>
	<select
		name="<?= HtmlFilter::encode($arResult['fieldName']) ?>"
		size="<?= $arResult['size'] ?>"
		<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
		<?= ($arResult['userField']['EDIT_IN_LIST'] !== 'Y' ? ' disabled="disabled" ' : '') ?>
	>
		<?php
		$isWasSelect = false;
		$result = '';

		foreach($arResult['additionalParameters']['items'] as $itemId => $item)
		{
			$isSelected = in_array($itemId, $arResult['additionalParameters']['VALUE']);
			$isWasSelect = ($isWasSelect || $isSelected);
			$selected = ($isSelected ? ' selected' : '');
			$result .= <<<EOL
				<option value="{$item['ID']}" {$selected}>
					{$item['VALUE']}
				</option>
EOL;
		}
		if($arResult['userField']['MANDATORY'] !== 'Y')
		{
			?>
			<option
				value=""
				<?= (!$isWasSelect ? ' selected' : '') ?>
			>
				<?= HtmlFilter::encode(\CUserTypeHlblock::getEmptyCaption($arResult['userField'])) ?>
			</option>
			<?php
		}
		print $result;
		?>
	</select>
	<?php
}
