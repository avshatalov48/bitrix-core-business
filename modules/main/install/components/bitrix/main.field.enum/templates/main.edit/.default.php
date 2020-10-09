<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\EnumType;

/**
 * @var EnumUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
?>

<span class="fields enumeration field-wrap" data-has-input="no">
	<?php
	if ($arResult['isEnabled'])
	{
		$multipleClass = $arResult['userField']['MULTIPLE'] === 'Y' ? '-multiselect' : '-select';
		if($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST)
		{
			?>
			<span
				class="<?= EnumType::USER_TYPE_ID . $multipleClass ?> field-item"
			>
				<select
					<?= $component->getHtmlBuilder()->buildTagAttributes($arResult['attrList']) ?>
				>
				<?php
				if(
					isset($arResult['userField']['USER_TYPE']['FIELDS'])
					&&
					is_array($arResult['userField']['USER_TYPE']['FIELDS'])
				)
				{
					$isWasSelect = false;
					foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
					{
						$isSelected = (
							in_array($key, $arResult['value'])
							&&
							(
								(!$isWasSelect) || ($arResult['userField']['MULTIPLE'] === 'Y')
							)
						);
						$isWasSelect = $isWasSelect || $isSelected;
						?>
						<option
							value="<?= HtmlFilter::encode($key) ?>"
							<?= ($isSelected ? ' selected="selected"' : '') ?>
						><?= $val ?></option>
						<?php
					}
				}
				?>
				</select>
			</span>
			<?php
		}
		elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI)
		{
			?>

			<input
				type="hidden"
				value=""
				id="<?= $arResult['userField']['FIELD_NAME'] ?>_default"
			>

			<span <?= $component->getHtmlBuilder()->buildTagAttributes($arResult['spanAttrList']) ?>>
			<?php
			if(count($arResult['attrList']))
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
			$script = <<<EOT
			<script>
			function changeHandler_{$arResult['fieldNameJs']}(controlObject, value)
			{
				if(controlObject.params.fieldName === '{$arResult['fieldNameJs']}' && !!BX('{$arResult['valueContainerIdJs']}'))
				{
					var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));
	
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
			$isFirst = true;
			if($arResult['userField']['MULTIPLE'] === 'Y')
			{
				?>
				<input
					type="hidden"
					name="<?= $arResult['fieldName'] ?>"
					value=""
				>
				<?php
			}

			$isWasSelect = false;
			foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
			{
				?>
				<span
					class="fields enumeration <?= EnumType::USER_TYPE_ID . '-checkbox' ?> field-item"
				>
					<?php
					if($isFirst)
					{
						$isFirst = false;
					}
					else
					{
						print $component->getHtmlBuilder()->getMultipleValuesSeparator();
					}

					$isSelected = (
						in_array($key, $arResult['value'])
						&&
						(
							(!$isWasSelect) ||
							($arResult['userField']['MULTIPLE'] === 'Y')
						));

					$isWasSelect = $isWasSelect || $isSelected;
					?>
					<label>
						<input
							value="<?= HtmlFilter::encode($key) ?>"
							type="<?= $arResult['userField']['MULTIPLE'] === 'Y' ? 'checkbox' : 'radio' ?>"
							name="<?= $arResult['fieldName'] ?>"
							<?= ($isSelected ? 'checked="checked"' : '') ?>
							tabindex="0"
						>
						<?= $val ?>
					</label>
					<br>
				</span>
				<?php
			}
		}
	}
	else
	{
		$arResult['additionalParameters']['mode'] = BaseType::MODE_VIEW;
		$field = new \Bitrix\Main\UserField\Renderer($arResult['userField'], $arResult['additionalParameters']);
		print $field->render();
	}
	?>
</span>