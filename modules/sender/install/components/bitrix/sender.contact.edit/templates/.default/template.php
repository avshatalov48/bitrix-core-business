<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-campaign-edit';
?>
<script>
	BX.ready(function () {
		BX.Sender.ContactEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'mess' => array()
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-template-edit-wrap">

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(

		)),
	)));
	?>

	<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
		<?=bitrix_sessid_post()?>

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_TYPE')?>:
			</div>
			<div class="bx-sender-value">
				<select name="TYPE_ID" class="bx-sender-form-control bx-sender-message-editor-field-select">
					<?foreach ($arResult['TYPES'] as $site):?>
						<option value="<?=htmlspecialcharsbx($site['ID'])?>"
							<?=($site['SELECTED'] ? 'selected' : '')?>
						>
							<?=htmlspecialcharsbx($site['NAME'])?>
						</option>
					<?endforeach;?>
				</select>
			</div>
		</div>

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_ADDRESS')?>:
			</div>
			<div class="bx-sender-value">
				<input type="text" name="CODE"
					value="<?=htmlspecialcharsbx($arResult['ROW']['CODE'])?>"
					class="bx-sender-form-control bx-sender-letter-field-input"
				>
			</div>
		</div>

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_NAME')?>:
			</div>
			<div class="bx-sender-value">
				<input type="text" name="NAME"
					value="<?=htmlspecialcharsbx($arResult['ROW']['NAME'])?>"
					class="bx-sender-form-control bx-sender-letter-field-input"
				>
			</div>
		</div>

		<?if ($arParams['SHOW_SETS']):?>
			<div class="bx-sender-letter-field">
				<div class="bx-sender-caption">
					<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_SET')?>:
				</div>
				<div class="bx-sender-value">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:sender.contact.set.selector",
						"",
						array(
							//'PATH_TO_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'],
							'ID' => $arResult['ROW']['SET_LIST'],
							'INPUT_NAME' => 'SET_LIST',
							'READONLY' => !$arParams['CAN_EDIT'],
							'MULTIPLE' => true,
							'SELECT_ONLY' => true,
						),
						false
					);
					?>
				</div>
			</div>
		<?endif;?>

		<?if ($arParams['SHOW_CAMPAIGNS']):?>
			<div class="bx-sender-letter-field">
				<div class="bx-sender-caption">
					<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_SUB')?>:
				</div>
				<div class="bx-sender-value">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:sender.campaign.selector",
						"",
						array(
							'PATH_TO_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'],
							'ID' => $arResult['ROW']['SUB_LIST'],
							'INPUT_NAME' => 'SUB_LIST',
							'READONLY' => !$arParams['CAN_EDIT'],
							'MULTIPLE' => true,
							'SELECT_ONLY' => true,
						),
						false
					);
					?>
				</div>
			</div>
		<?endif;?>

		<?if ($arParams['SHOW_CAMPAIGNS']):?>
			<div class="bx-sender-letter-field">
				<div class="bx-sender-caption">
					<?=Loc::getMessage('SENDER_CONTACT_EDIT_TMPL_FIELD_UNSUB')?>:
				</div>
				<div class="bx-sender-value">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:sender.campaign.selector",
						"",
						array(
							'PATH_TO_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'],
							'ID' => $arResult['ROW']['UNSUB_LIST'],
							'INPUT_NAME' => 'UNSUB_LIST',
							'READONLY' => !$arParams['CAN_EDIT'],
							'MULTIPLE' => true,
							'SELECT_ONLY' => true,
						),
						false
					);
					?>
				</div>
			</div>
		<?endif;?>


		<?
		$APPLICATION->IncludeComponent(
			"bitrix:sender.ui.button.panel",
			"",
			array(
				'SAVE' => $arParams['CAN_EDIT'] ? [] : null,
				'CANCEL' => array(
					'URL' => $arParams['PATH_TO_LIST']
				),
			),
			false
		);
		?>

	</form>

</div>