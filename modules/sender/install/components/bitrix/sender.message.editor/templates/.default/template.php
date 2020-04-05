<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Message\ConfigurationOption as ConOpt;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-message-editor';

$getHintText = function (array $option)
{
	if (empty($option['hint']))
	{
		return '';
	}

	$hint = '';
	if (is_string($option['hint']))
	{
		$hint = $option['hint'];
	}
	elseif (is_array($option['hint']))
	{
		if (isset($option['hint']['text']))
		{
			$hint = $option['hint']['text'];
		}
	}

	return $hint;
};

$getHintHtml = function (array $option)
{
	if (empty($option['hint']))
	{
		return '';
	}

	$hint = '';
	$hintDesc = '';
	$hintList = array();
	if (is_string($option['hint']))
	{
		$hintDesc = $option['hint'];
	}
	elseif (is_array($option['hint']))
	{
		if (isset($option['hint']['text']))
		{
			$hintDesc = $option['hint']['text'];
		}
		if (isset($option['hint']['menu']))
		{
			$hintList = $option['hint']['menu'];
		}
	}

	if (count($hintList) > 0)
	{
		$hintTags = htmlspecialcharsbx(Json::encode($hintList));
		ob_start();
		?>
		<span data-tag="<?=$hintTags?>"></span>
		<?
		$hint .= ob_get_clean();
	}

	if ($hintDesc)
	{
		$hintDesc = htmlspecialcharsbx($hintDesc);
		ob_start();
		?>
		<div data-hint="<?=$hintDesc?>"></div>
		<?
		$hint .= ob_get_clean();
	}

	return $hint;
};

$fieldPrefix = 'CONFIGURATION_';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Message.Editor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'messageCode' => $arResult['MESSAGE_CODE'],
			'fieldPrefix' => $fieldPrefix,
			'templateType' => $arParams['TEMPLATE_TYPE'],
			'templateId' => $arParams['TEMPLATE_ID'],
			'mess' => array()
		))?>);
	});
</script>

<div id="bx-sender-message-editor" class="bx-sender-message-editor">
	<?
	$optionValueReplaces = array();
	foreach ($arResult['LIST'] as $group)
	{
		foreach ($group['options'] as $option)
		{
			$inputCode = htmlspecialcharsbx($option['code']);
			$inputName = "$fieldPrefix$inputCode";
			$optionValueReplaces["%INPUT_NAME_$inputCode%"] = $inputName;
		}
	}

	if ($arResult['MESSAGE_VIEW'])
	{
		echo str_replace(
			array_keys($optionValueReplaces),
			array_values($optionValueReplaces),
			$arResult['MESSAGE_VIEW']
		);
	}
	else foreach ($arResult['LIST'] as $group)
	{
		if ($group['isAdditional'])
		{
			?>
			<div>
				<div class="sender-message-editor-more-wrap">
					<div data-role="more-btn" class="sender-message-editor-more">
						<div class="sender-message-editor-more-caption">
							<?=Loc::getMessage('SENDER_MESSAGE_EDITOR_ADDITIONAL')?>
						</div>
						<div class="sender-message-editor-more-list">
							<?foreach ($group['options'] as $option):?>
								<span class="sender-message-editor-more-list-item">
									<?=htmlspecialcharsbx($option['name'])?>
								</span>
							<?endforeach;?>
						</div>
					</div>
					<div data-role="more-fields" class="bx-sender-message-editor-more-fields" style="display: none;">
			<?
		}

		foreach ($group['options'] as $option)
		{
			$inputCaption = htmlspecialcharsbx($option['name']);
			$inputCode = htmlspecialcharsbx($option['code']);
			$inputId = "$fieldPrefix$inputCode";
			$inputName = "$fieldPrefix$inputCode";
			$inputValue = htmlspecialcharsbx(is_array($option['value']) ? '' : $option['value']);

			$inputView = is_callable($option['view']) ? $option['view']() : $option['view'];
			$inputView = str_replace('%INPUT_NAME%', $inputName, $inputView);
			$inputView = str_replace('%INPUT_VALUE%', $inputValue, $inputView);
			$inputView = str_replace(
				array_keys($optionValueReplaces),
				array_values($optionValueReplaces),
				$inputView
			);
			$hint = $getHintHtml($option);
			$hintText = htmlspecialcharsbx($getHintText($option));
			$placeholder = strip_tags($getHintText($option));

			$inputHtml = '';
			$inputDisplay = '';
			$isEditor = false;
			$isCustomCaption = false;
			$optionType = strlen($inputView) == 0 ? $option['type'] : ConOpt::TYPE_CUSTOM;
			switch ($optionType)
			{
				case ConOpt::TYPE_FILE:
					$fileParameters = array();
					if (!is_array($option['value']))
					{
						$option['value'] = array();
					}
					foreach($option['value'] as $fileId)
					{
						$fileParameters["$inputName" . "[$fileId]"] = $fileId;
					}

					$inputHtml = \Bitrix\Main\UI\FileInput::createInstance((array(
						"name" => $inputName . "[n#IND#]",
						"upload" => true,
						"medialib" => true,
						"fileDialog" => true,
						"cloud" => true
					)
					))->show($fileParameters);

					/*
					ob_start();
					global $USER_FIELD_MANAGER;
					$arUserFieldType = $USER_FIELD_MANAGER->GetUserType('disk_file');
					$arUserField = array(
						'ENTITY_ID' => 'SENDER_FILE',
						'FIELD_NAME' => 'SENDER_FILE',
						'USER_TYPE_ID' => 'disk_file',
						'SORT' => 100,
						'MULTIPLE' => 'Y',
						'MANDATORY' => 'N',
						"SHOW_FILTER" => "N",
						"SHOW_IN_LIST" => "N",
						"EDIT_IN_LIST" => "Y",
						'EDIT_FORM_LABEL' => 'XXXXXXXX_EDIT_FORM_LABEL',
						'VALUE' => $fieldValue,
						'USER_TYPE' => $arUserFieldType,
						'SETTINGS' => array(),
						'ENTITY_VALUE_ID' => 1,
					);

					?>
					<div class="sender-message-editor-field-file-cloud">
					<?
					$APPLICATION->IncludeComponent(
						'bitrix:system.field.edit',
						'disk_file',
						array(
							'arUserField' => $arUserField,
							'bVarsFromForm' => false,
							'form_name' => '',
							'FILE_MAX_HEIGHT' => 400,
							'FILE_MAX_WIDTH' => 400,
							'FILE_SHOW_POPUP' => false,
							'HIDE_SELECT_DIALOG' => 'N',
							'HIDE_CHECKBOX_ALLOW_EDIT' => 'Y',
							'DISABLE_CREATING_FILE_BY_CLOUD' => 'Y',
						),
						false,
						array('HIDE_ICONS' => 'Y')
					);
					?>
						<script type="text/javascript">
							BX.ready(function () {
								var list = document.getElementsByClassName('diskuf-selectdialog-switcher');
								list.length > 0 ? BX.fireEvent(list.item(0), 'click') : null;
							});
						</script>
					</div>
					<?
					$inputHtml = ob_get_clean();
					*/

					break;

				case ConOpt::TYPE_MAIL_EDITOR:
					ob_start();
					$APPLICATION->IncludeComponent(
						"bitrix:sender.mail.editor",
						"",
						array(
							"INPUT_NAME" => $inputName,
							"VALUE" => $option['value'],
							"TEMPLATE_TYPE" => $arParams['TEMPLATE_TYPE'],
							"TEMPLATE_ID" => $arParams['TEMPLATE_ID'],
						),
						null
					);
					$inputHtml = ob_get_clean();
					$isEditor = true;
					break;

				case ConOpt::TYPE_SMS_EDITOR:
					ob_start();
					$APPLICATION->IncludeComponent(
						"bitrix:sender.message.editor.sms",
						"",
						array(
							"INPUT_NAME" => $inputName,
							"VALUE" => $option['value'],
						),
						null
					);
					$inputHtml = ob_get_clean();
					$isEditor = true;
					break;

				case ConOpt::TYPE_TEMPLATE_TYPE:
				case ConOpt::TYPE_TEMPLATE_ID:
					continue 2;

				case ConOpt::TYPE_LIST:
					if (empty($option['items']))
					{
						$option['items'] = array();
					}

					$inputHtml = "<select name=\"$inputName\" class=\"bx-sender-form-control bx-sender-message-editor-field-select\">";
					foreach ($option['items'] as $item)
					{
						$itemKey = htmlspecialcharsbx($item['code']);
						$itemValue = htmlspecialcharsbx($item['value']);
						$selected = $itemKey == $inputValue ? 'selected' : '';
						$inputHtml .= "<option value=\"$itemKey\" $selected>$itemValue</option>";
					}
					$inputHtml .= '</select>';
					break;

				case ConOpt::TYPE_TEXT:
					$inputHtml = "<textarea id=\"$inputId\" name=\"$inputName\" placeholder=\"$placeholder\" class=\"bx-sender-form-control bx-sender-message-editor-field-text\">";
					$inputHtml .= $inputValue;
					$inputHtml .= "</textarea>";
					break;

				case ConOpt::TYPE_CUSTOM:
					$inputHtml = $inputView;
					break;

				case ConOpt::TYPE_CHECKBOX:
					$inputHtml = "<input type=\"checkbox\" id=\"$inputId\"  name=\"$inputName\" class=\"bx-sender-message-editor-field-checkbox\" value=\"Y\" " . ($inputValue === 'Y' ? 'checked' : '') . ">";
					$inputHtml .= "<label for=\"$inputId\" class=\"bx-sender-caption sender-message-title\">" . $inputCaption . "</label>";
					$isCustomCaption = true;
					break;

				case ConOpt::TYPE_STRING:
				default:
					$inputHtml = "<input type=\"text\" id=\"$inputId\"  name=\"$inputName\" value=\"$inputValue\" class=\"bx-sender-form-control bx-sender-message-editor-field-input\">";
					break;
			}

			?>
			<div data-bx-field="<?=$inputId?>" class="bx-sender-message-editor-field <?=$inputDisplay?>">
				<div class="bx-sender-caption sender-message-title">
					<?if (!$isCustomCaption):?>
						<?=$inputCaption?>:
					<?endif;?>
				</div>
				<div data-role="editor-field" class="bx-sender-value">
					<?=$inputHtml?>

					<?if ($hint):?>
						<?=$hint?>
					<?endif;?>
				</div>
			</div>
			<?
		}  //foreach ($group['options'] as $option)

		if ($group['isAdditional'])
		{
			?>
				</div>
			</div>
			<?
		}

	}  //foreach ($arResult['LIST'] as $group)


	?>
	<div class="sender-message-editor-test-wrap">
		<?
		if ($arResult['IS_SUPPORT_TESTING'])
		{
			$APPLICATION->IncludeComponent(
				'bitrix:sender.message.tester', '',
				array(
					'MESSAGE_CODE' => $arResult['MESSAGE_CODE']
				)
			);
		}
		?>
	</div>
</div>