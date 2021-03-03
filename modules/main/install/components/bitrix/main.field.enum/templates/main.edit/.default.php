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
			$postfix = $this->randString();
			if ($component->isAjaxRequest())
			{
				$postfix .= time();
			}
			$arResult['valueContainerId'] .= $postfix;
			$arResult['spanAttrList']['id'] = $arResult['valueContainerId'];
			$arResult['controlNodeId'] .= $postfix;
			$defaultFieldName = $arResult['fieldName'].'_default_'.$postfix;
			?>

			<input
				type="hidden"
				value=""
				id="<?= $defaultFieldName ?>"
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
			$scriptParams = CUtil::PhpToJSObject([
				'defaultFieldName' => $defaultFieldName,
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
	BX.ready(function ()
	{
		new BX.Desktop.Field.Enum({$scriptParams});
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
		$arResult['additionalParameters']['showInputs'] = true;
		$field = new \Bitrix\Main\UserField\Renderer($arResult['userField'], $arResult['additionalParameters']);
		print $field->render();
	}
	?>
</span>