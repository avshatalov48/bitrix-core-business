<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Text\HtmlFilter;

if(
	$arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI
)
{
	?>
	<span id="<?= $arResult['valueContainerId'] ?>" style="display: none">
	<?php
	for($i = 0, $n = count($arResult['startValue']); $i < $n; $i++)
	{
		?>
		<input
			type="hidden"
			name="<?= HtmlFilter::encode($arResult['fieldName']) ?>"
			value="<?= HtmlFilter::encode($arResult['startValue'][$i]['VALUE']) ?>"
		>
		<?php
	}
	?>
	</span>
	<span id="<?= $arResult['controlNodeIdJs'] ?>"></span>
	<?php
	$script = <<<EOT
	<script>
		function changeHandler_{$arResult['fieldNameJs']}(controlObject, value)
		{						
			if(controlObject.params.fieldName === '{$arResult['fieldNameJs']}' && !!BX('{$arResult['valueContainerIdJs']}'))
			{
				var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));

				if(BX.type.isPlainObject(currentValue))
				{
					BX('{$arResult['valueContainerIdJs']}').firstChild.value = currentValue['VALUE'];
				} else {				
					var s = '';
					if(!BX.type.isArray(currentValue))
					{
						if(currentValue === null)
						{
							currentValue = [{VALUE:''}];
						}
						else
						{
							currentValue = [currentValue];
						}
					}

					if(currentValue.length > 0)
					{
						for(var i = 0; i < currentValue.length; i++)
						{
							s += '<input type="hidden" name="{$arResult['htmlFieldNameJs']}" value="'+BX.util.htmlspecialchars(currentValue[i].VALUE)+'" />';
						}
					}
					else
					{
						s += '<input type="hidden" name="{$arResult['htmlFieldNameJs']}" value="" />';
					}

					BX('{$arResult['valueContainerIdJs']}').innerHTML = s;					
				}
				BX.fireEvent(BX('{$arResult['fieldNameJs']}_default'), 'change');
			}
		}

		BX.ready(function(){
			var params = {$arResult['params']};			

			BX('{$arResult['controlNodeIdJs']}').appendChild(BX.decl({
				block: '{$arResult['block']}',
				name: '{$arResult['fieldNameJs']}',
				items: {$arResult['items']},
				value: {$arResult['currentValue']},
				params: params,
				valueDelete: false
			}));				

			BX.addCustomEvent(
				window,
				'UI::Select::change',
				changeHandler_{$arResult['fieldNameJs']}
			);

			BX.bind(BX('{$arResult['controlNodeIdJs']}'), 'click', BX.defer(function(){
				changeHandler_{$arResult['fieldNameJs']}(
					{
						params: params,
						node: BX('{$arResult['controlNodeIdJs']}').firstChild
					});
			}));
		});
	</script>
EOT;

	print $script;
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_CHECKBOX)
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
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST)
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
			$isSelected = (
				in_array($itemId, $arResult['additionalParameters']['VALUE'])
				||
				($arResult['userField']['ENTITY_VALUE_ID'] <= 0 && $item['DEF'] === 'Y')
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