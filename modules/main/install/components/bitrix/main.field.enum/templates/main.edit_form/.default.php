<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var EnumUfComponent $component */

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Text\HtmlFilter;

if ($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI)
{
	?>
	<input
		type="hidden"
		value=""
		id="<?= $arResult['fieldName'] ?>_default"
	>
	<span <?= $component->getHtmlBuilder()->buildTagAttributes($arResult['spanAttrList']) ?>>
			<?php
			if(!empty($arResult['attrList']))
			{
				foreach($arResult['attrList'] as $attrList)
				{
					?>
					<input <?= $component->getHtmlBuilder()->buildTagAttributes($attrList) ?>>
					<?php
				}
			}
			?>
			</span>

	<span id="<?= $arResult['controlNodeId'] ?>"></span>

	<?php
	$scriptParams = CUtil::PhpToJSObject([
		'fieldName' => $arResult['fieldNameJs'],
		'container' => $arResult['controlNodeId'],
		'valueContainerId' => $arResult['valueContainerId'],
		'block' => $arResult['block'],
		'value' => $arResult['currentValue'],
		'items' => $arResult['items'],
		'params' => $arResult['params']
	]);
	$script = <<<EOT
<script>
	BX.ready(function (){
		new BX.Desktop.Field.Enum.Ui({$scriptParams});
	});
</script>
EOT;
	print $script;
}
elseif ($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_CHECKBOX)
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
		$isSelected = (
			(in_array($itemId, $arResult['additionalParameters']['VALUE']))
			||
			($arResult['userField']['ENTITY_VALUE_ID'] <= 0 && $item['DEF'] === 'Y')
		);
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
	if($arResult['userField']['MANDATORY'] !== 'Y')
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
			<?= HtmlFilter::encode(EnumType::getEmptyCaption($arResult['userField'])) ?>
		</label>
		<br>
		<?php
	}
	print $result;
}
elseif ($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST)
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

		$showNoValue = ($arParams['userField']['SETTINGS']['SHOW_NO_VALUE'] ?? 'N');
		if ($showNoValue === 'Y')
		{
			$result .= '<option></option>';
		}

		foreach($arResult['additionalParameters']['items'] as $itemId => $item)
		{
			$isSelected = (
				in_array($itemId, $arResult['additionalParameters']['VALUE'])
				|| (
					(!isset($arResult['userField']['ENTITY_VALUE_ID']) || $arResult['userField']['ENTITY_VALUE_ID'] <= 0)
					&& $item['DEF'] === 'Y'
				)
			);

			$fullValue = $shortValue = $item['VALUE'];
			$valueTitle = '';

			if(mb_strlen($item['~VALUE']) > EnumUfComponent::MAX_OPTION_LENGTH)
			{
				$textParser = new CTextParser();
				$shortValue = HtmlFilter::encode($textParser->html_cut($item['~VALUE'], EnumUfComponent::MAX_OPTION_LENGTH));
				$valueTitle = 'title="' . HtmlFilter::encode($item['~VALUE']) . '"';
			}

			$isWasSelect = ($isWasSelect || $isSelected);
			$selected = ($isSelected ? ' selected' : '');
			$result .= <<<EOL
				<option 
					{$valueTitle} 
					value="{$item['ID']}" 
					{$selected}
				>
					{$shortValue}
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
				<?= HtmlFilter::encode(EnumType::getEmptyCaption($arResult['userField'])) ?>
			</option>
			<?php
		}
		print $result;
		?>
	</select>
	<?php
}
