<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Text\HtmlFilter;

/** @var array $arResult */

if($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_UI)
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
	<span id="<?= $arResult['controlNodeIdJs'] ?>" class="iblock-element-selector-wrapper"></span>
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
			
			BX.fireEvent(BX('{$arResult['controlNodeIdJs']}'), 'click');
		});
	</script>
EOT;

	print $script;
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_CHECKBOX)
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
			<?= HtmlFilter::encode(ElementType::getEmptyCaption($arResult['userField'])) ?>
		</label>
		<br>
		<?php
	}
	print $result;
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_LIST)
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
				<?= HtmlFilter::encode(ElementType::getEmptyCaption($arResult['userField'])) ?>
			</option>
			<?php
		}
		print $result;
		?>
	</select>
	<?php
}
