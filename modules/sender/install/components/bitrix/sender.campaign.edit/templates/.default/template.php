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
		BX.Sender.CampaignEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'campaignTile' => $arResult['CAMPAIGN_TILE'],
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'mess' => array(
				'patternTitle' => Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_PATTERN_TITLE'),
				'newTitle' => Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_NEW_TITLE'),
			)
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-template-edit-wrap">

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(
			array('type' => 'feedback')
		)),
	)));
	?>

	<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
		<?=bitrix_sessid_post()?>

		<div class="bx-sender-letter-field" style="<?=($arParams['IFRAME'] == 'Y' ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_NAME')?>:
			</div>
			<div class="bx-sender-value">
				<input data-role="campaign-title" type="text" name="NAME"
					value="<?=htmlspecialcharsbx($arResult['ROW']['NAME'])?>"
					class="bx-sender-form-control bx-sender-letter-field-input"
				>
			</div>
		</div>

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_SITE')?>:
			</div>
			<div class="bx-sender-value">
				<select name="SITE_ID" class="bx-sender-form-control bx-sender-message-editor-field-select">
					<?foreach ($arResult['SITES'] as $site):?>
						<option value="<?=htmlspecialcharsbx($site['ID'])?>"
							<?=($site['SELECTED'] ? 'selected' : '')?>
						>
							<?=htmlspecialcharsbx($site['NAME'])?>
						</option>
					<?endforeach;?>
				</select>
			</div>
		</div>

		<div class="bx-sender-letter-field" style="<?=($arParams['IS_TRIGGER'] ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">
				<input type="checkbox" id="ACTIVE" name="ACTIVE"
					value="Y" <?=($arResult['ROW']['ACTIVE'] === 'Y' ? 'checked' : '')?>
				>
				<label for="ACTIVE"><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_ACTIVE')?></label>
			</div>
		</div>

		<div class="bx-sender-letter-field" style="<?=($arParams['IS_TRIGGER'] ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">
				<input type="checkbox" id="IS_PUBLIC" name="IS_PUBLIC"
					value="Y" <?=($arResult['ROW']['IS_PUBLIC'] === 'Y' ? 'checked' : '')?>
				>
				<label for="IS_PUBLIC"><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_IS_PUBLIC')?></label>
			</div>
		</div>

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_DESC')?>:
			</div>
			<div class="bx-sender-value">
				<textarea name="DESCRIPTION"
					class="bx-sender-form-control bx-sender-message-editor-field-text"
				><?=htmlspecialcharsbx($arResult['ROW']['DESCRIPTION'])?></textarea>
			</div>
		</div>

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