<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;

Loc::loadMessages(__FILE__);

/** @var \CAllMain $APPLICATION */
/** @var \SenderLetterEditComponent $component */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-letter-edit';

Extension::load("ui.buttons");
Extension::load("ui.buttons.icons");
Extension::load("ui.notification");
CJSCore::Init(array('admin_interface'));
?>
<script type="text/javascript">
	BX.ready(function () {

		BX.Sender.Letter.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'isOutside' => $arParams['IS_OUTSIDE'],
			'isTemplateShowed' => $arResult['SHOW_TEMPLATE_SELECTOR'],
			'letterTile' => $arResult['LETTER_TILE'],
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'mess' => array(
				'patternTitle' => Loc::getMessage('SENDER_COMP_TMPL_LETTER_PATTERN_TITLE'),
				'name' => $arResult['MESSAGE_NAME'],
				'applyClose' => $component->getLocMessage('SENDER_LETTER_APPLY_CLOSE'),
				'applyCloseTitle' => $component->getLocMessage('SENDER_LETTER_APPLY_CLOSE_TITLE'),
				'applyYes' => $component->getLocMessage('SENDER_LETTER_APPLY_YES'),
				'applyCancel' => $component->getLocMessage('SENDER_LETTER_APPLY_CANCEL'),
				'outsideSaveSuccess' => $component->getLocMessage(
					'SENDER_LETTER_EDIT_OUTSIDE_ADD_SUCCESS',
					['%path%' => $arParams['PATH_TO_LIST']]
				)
			)
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="bx-sender-letter-steps">

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(
			array('type' => 'feedback'),
			($arResult['USE_TEMPLATES'] && $arResult['CAN_CHANGE_TEMPLATE'])
			?
				array(
					'type' => 'default',
					'id' => 'SENDER_LETTER_BUTTON_CHANGE',
					'caption' => Loc::getMessage('SENDER_LETTER_EDIT_CHANGE_TEMPLATE'),
					'visible' => !$arResult['SHOW_TEMPLATE_SELECTOR']
				)
			:
				null
		)),
	)));
	?>

	<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>

		<div data-role="template-selector" class="bx-sender-letter-template-selector <?=(!$arResult['SHOW_TEMPLATE_SELECTOR'] ? 'bx-sender-letter-hide' : ' ')?>">
			<?
			if ($arResult['USE_TEMPLATES'])
			{
				$APPLICATION->IncludeComponent(
					"bitrix:sender.template.selector",
					"",
					array(
						"MESSAGE_CODE" => $arResult['MESSAGE_CODE'],
						"IS_TRIGGER" => $arParams['IS_TRIGGER'],
						"CACHE_TIME" => "60",
						"CACHE_TYPE" => "N",
					)
				);
			}
			?>
		</div>

		<div data-role="letter-editor" class="bx-sender-letter-step-2 <?=($arResult['SHOW_TEMPLATE_SELECTOR'] ? 'bx-sender-letter-hide' : 'bx-sender-letter-show')?>">
			<input type="hidden" name="MESSAGE_CODE" value="<?=htmlspecialcharsbx($arResult['MESSAGE_CODE'])?>">
			<input type="hidden" name="MESSAGE_ID" value="<?=htmlspecialcharsbx($arResult['MESSAGE_ID'])?>">

			<input data-role="template-type" type="hidden" name="TEMPLATE_TYPE" value="<?=htmlspecialcharsbx($arResult['ROW']['TEMPLATE_TYPE'])?>">
			<input data-role="template-id" type="hidden" name="TEMPLATE_ID" value="<?=htmlspecialcharsbx($arResult['ROW']['TEMPLATE_ID'])?>">

			<input data-role="dispatch" data-code="METHOD_CODE" type="hidden" name="DISPATCH[METHOD_CODE]">
			<input data-role="dispatch" data-code="DAYS_OF_WEEK" type="hidden" name="DISPATCH[DAYS_OF_WEEK]">
			<input data-role="dispatch" data-code="DAYS_OF_MONTH" type="hidden" name="DISPATCH[DAYS_OF_MONTH]">
			<input data-role="dispatch" data-code="MONTHS_OF_YEAR" type="hidden" name="DISPATCH[MONTHS_OF_YEAR]">
			<input data-role="dispatch" data-code="TIMES_OF_DAY" type="hidden" name="DISPATCH[TIMES_OF_DAY]">

			<?
			if ($arResult['USE_TEMPLATES'] && $arResult['CAN_CHANGE_TEMPLATE']):
				/*
				$this->SetViewTarget("pagetitle", 100);
				?>
				<span id="SENDER_LETTER_BUTTON_CHANGE" class="webform-small-button webform-small-button-transparent" style="<?=($arResult['SHOW_TEMPLATE_SELECTOR'] ? 'display: none;' : '')?>">
					<?=Loc::getMessage('SENDER_LETTER_EDIT_CHANGE_TEMPLATE')?>
				</span>
				<?
				$this->EndViewTarget();
				*/
			endif;
			?>

			<div class="bx-sender-letter-field sender-letter-edit-row" style="<?=($arParams['IFRAME'] == 'Y' ? 'display: none;' : '')?>">
				<div class="bx-sender-caption sender-letter-edit-title"><?=Loc::getMessage('SENDER_LETTER_EDIT_FIELD_NAME')?>:</div>
				<div class="bx-sender-value">
					<input data-role="letter-title" type="text" name="TITLE" value="<?=htmlspecialcharsbx($arResult['ROW']['TITLE'])?>" class="bx-sender-letter-form-control bx-sender-letter-field-input" <?if(!$arParams['CAN_EDIT']):?>disabled="disabled"<?endif;?>>
				</div>
			</div>

			<?if ($arParams['SHOW_CAMPAIGNS']):?>
				<div class="sender-letter-edit-row">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:sender.campaign.selector",
						"",
						array(
							'PATH_TO_ADD' => $arParams['PATH_TO_CAMPAIGN_ADD'],
							'PATH_TO_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'],
							'ID' => $arResult['CAMPAIGN_ID'],
							'READONLY' => !empty($arResult['ROW']['ID']),
						),
						false
					);
					?>
				</div>
			<?endif;?>

			<?if ($arParams['SHOW_SEGMENTS']):?>
				<div class="sender-letter-edit-row">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:sender.segment.selector",
						"",
						array(
							'PATH_TO_ADD' => $arParams['PATH_TO_SEGMENT_ADD'],
							'PATH_TO_EDIT' => $arParams['PATH_TO_SEGMENT_EDIT'],
							'INCLUDE' => $arResult['SEGMENTS']['INCLUDE'],
							'EXCLUDE' => $arResult['SEGMENTS']['EXCLUDE'],
							'MESSAGE_CODE' => $arResult['MESSAGE_CODE'],
							'READONLY' => $arResult['SEGMENTS']['READONLY'],
							'RECIPIENT_COUNT' => $arResult['SEGMENTS']['RECIPIENT_COUNT'],
							'IS_RECIPIENT_COUNT_EXACT' => $arResult['SEGMENTS']['IS_RECIPIENT_COUNT_EXACT'],
							'DURATION_FORMATTED' => $arResult['SEGMENTS']['DURATION_FORMATTED'],
							'SHOW_COUNTERS' => $arParams['SHOW_SEGMENT_COUNTERS'],
							'MESS' => $arParams['MESS'],
						),
						false
					);
					?>
				</div>
			<?endif;?>

			<?
			$APPLICATION->IncludeComponent(
				"bitrix:sender.message.editor",
				"",
				array(
					"MESSAGE_CODE" => $arResult['MESSAGE_CODE'],
					"MESSAGE_ID" => $arResult['MESSAGE_ID'],
					"MESSAGE" => $arResult['MESSAGE'],
					"TEMPLATE_TYPE" => $arResult['ROW']['TEMPLATE_TYPE'],
					"TEMPLATE_ID" => $arResult['ROW']['TEMPLATE_ID'],
					"CAN_EDIT" => $arParams['CAN_EDIT'],
					"IS_TRIGGER" => $arParams['IS_TRIGGER'],
				),
				false
			);
			?>
		</div>

		<div data-role="letter-buttons" style="<?=($arResult['SHOW_TEMPLATE_SELECTOR'] ? 'display: none;' : '')?>">
			<?
			$buttons = [];
			if ($arParams['CAN_EDIT'])
			{
				if ( $arResult['CAN_SAVE_AS_TEMPLATE'])
				{
					$buttons[] = [
						'TYPE' => 'checkbox',
						'CAPTION' => Loc::getMessage('SENDER_LETTER_EDIT_BTN_SAVE_AS_TEMPLATE'),
						'NAME' => 'save_as_template'
					];
				}
				$buttons[] = ['TYPE' => 'save'];
				$buttons[] = ['TYPE' => 'apply', 'ONCLICK' => 'BX.Sender.Letter.applyChanges()'];
			}
			$buttons[] = ['TYPE' => 'cancel', 'LINK' => $arParams['PATH_TO_LIST']];
			$APPLICATION->IncludeComponent(
				"bitrix:ui.button.panel",
				"",
				array(
					'BUTTONS' => $buttons
				),
				false
			);
			?>
		</div>

	</form>
</div>



