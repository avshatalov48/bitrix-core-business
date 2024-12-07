<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Web\Json;

/**
 * @var EnumUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
$isMultiple = $arResult['isMultiple'];
?>

<span class="fields enumeration field-wrap" data-has-input="no">
	<?php
	if ($arResult['isEnabled'])
	{
		$multipleClass = ($isMultiple ? '-multiselect' : '-select');
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
					&& is_array($arResult['userField']['USER_TYPE']['FIELDS'])
				)
				{
					$isWasSelect = false;
					foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
					{
						$isSelected = (in_array($key, $arResult['value']) && (!$isWasSelect || $isMultiple));
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
			$scriptParams = Json::encode([
				'defaultFieldName' => $defaultFieldName,
				'fieldName' => $arResult['fieldNameJs'],
				'container' => $arResult['controlNodeId'],
				'valueContainerId' => $arResult['valueContainerId'],
				'block' => $arResult['block'],
				'items' => $arResult['items'],
				'value' => $arResult['selectedItems'],
				'params' => $arResult['params']
			]);
			$script = <<<EOT
<script>
	BX.ready(function ()
	{
		new BX.Desktop.Field.Enum.Ui({$scriptParams});
	});
</script>
EOT;
			print $script;
		}
		elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_CHECKBOX)
		{
			$isFirst = true;
			if($isMultiple)
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
						&& (!$isWasSelect || $isMultiple)
					);

					$isWasSelect = $isWasSelect || $isSelected;
					?>
					<label>
						<input
							value="<?= HtmlFilter::encode($key) ?>"
							type="<?= ($isMultiple ? 'checkbox' : 'radio') ?>"
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
		elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_DIALOG)
		{
			$postfix = $this->randString();
			if ($component->isAjaxRequest())
			{
				$postfix .= time();
			}
			//$context = 'UF_FIELD_ENUM_' . $arResult['valueContainerId'];
			$fieldNameForEvent = $arResult['targetNodeId'] . '_default_' . $postfix;
			$arResult['targetNodeId'] .= $postfix;

			$scriptParams = Json::encode([
				'targetNodeId' => $arResult['targetNodeId'],
				'fieldName' => $arResult['fieldName'],
				//'context' => $context,
				'fieldNameForEvent' => $fieldNameForEvent,
				'isMultiple' => ($isMultiple ? 'true' : 'false'),
				'items' => $arResult['items'],
				'fieldTitle' => $arResult['userField']['EDIT_FORM_LABEL'],
				'messages' => [
					'addButtonCaption' => Loc::getMessage('MAIN_FIELD_ENUM_TAG_SELECTOR_SELECT_ELEMENT'),
					'addButtonCaptionMore' => Loc::getMessage('MAIN_FIELD_ENUM_TAG_SELECTOR_SELECT_MORE'),
				]
			]);
	?>
			<input type="hidden" id="<?= $fieldNameForEvent ?>" value="">
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="<?= $arResult['targetNodeId'] ?>"></div>
	<?php
			$script = <<<EOT
<script>
	BX.ready(function ()
		{
			var dialog = new BX.Desktop.Field.Enum.Dialog({$scriptParams});
		});
		</script>
EOT;
			print $script;
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
