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
		BX.Sender.TriggerEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'] ?? null,
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'campaignTile' => $arResult['CAMPAIGN_TILE'],
			'triggers' => $arResult['TRIGGERS']['AVAILABLE'],
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'mess' => array(
				'patternTitle' => Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_PATTERN_TITLE'),
				'newTitle' => Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_NEW_TITLE'),
			)
		))?>);

		<?if(empty($arResult['ROW']['TRIGGER_FIELDS'])):?>
			BX.Sender.TriggerEditor.setTrigger(false, BX('EVENT_START').value);
			BX.Sender.TriggerEditor.setTrigger(true, BX('EVENT_END').value);
		<?endif;?>
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

	<form name="post_form" method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
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

		<div class="bx-sender-letter-field">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_DESC')?>:
			</div>
			<div class="bx-sender-value">
				<textarea name="DESCRIPTION"
					class="bx-sender-form-control bx-sender-message-editor-field-text"
				><?=htmlspecialcharsbx($arResult['ROW']['DESCRIPTION'] ?? '')?></textarea>
			</div>
		</div>

		<div class="bx-sender-letter-field">
			<table class="adm-detail-content-table edit-table">
				<tr>
					<td colspan="2">
						<div class="sender-mailing-group-container sender-mailing-group-add">
							<span class="sender-mailing-group-container-title">
								<span><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_COND_START')?></span>
							</span>
							<span class="adm-white-container-p">
								<span><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_COND_START_DESC')?></span>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table class="sender-mailing-group">
							<tr>
								<td>
									<span class="bx-sender-caption">
										<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_SELECT_COND')?>
									</span>
								</td>
								<td>
									<select id="EVENT_START" name="EVENT_START"
										onchange="BX.Sender.TriggerEditor.setTrigger(false, this.value);"
										class="bx-sender-form-control bx-sender-message-editor-field-select"
									>
										<?foreach($arResult['TRIGGERS']['AVAILABLE']['START'] as $triggerId => $triggerParams):?>
											<option
												value="<?=htmlspecialcharsbx($triggerId)?>"
												<?=(($arResult['TRIGGERS']['EXISTS']['START']['ID'] ?? null) == $triggerId ? 'selected' : '')?>
											><?=htmlspecialcharsbx($triggerParams['NAME'])?></option>
										<?endforeach;?>
									</select>
									<input type="hidden" id="ENDPOINT_START_MODULE_ID" name="ENDPOINT[START][MODULE_ID]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['START']['MODULE_ID'] ?? '')?>">
									<input type="hidden" id="ENDPOINT_START_CODE" name="ENDPOINT[START][CODE]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['START']['CODE'] ?? '')?>">
									<input type="hidden" id="ENDPOINT_START_IS_CLOSED_TRIGGER" name="ENDPOINT[START][IS_CLOSED_TRIGGER]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['START']['IS_CLOSED_TRIGGER'] ?? '')?>">
									<input type="hidden" id="ENDPOINT_START_WAS_RUN_FOR_OLD_DATA" name="ENDPOINT[START][WAS_RUN_FOR_OLD_DATA]" value="<?=(($arResult['TRIGGERS']['EXISTS']['START']['WAS_RUN_FOR_OLD_DATA'] ?? null) == 'Y' ? 'Y' : 'N')?>">
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<div id="ENDPOINT_START_SETTINGS" class="sender-mailing-container" style="<?=((!empty($arResult['TRIGGERS']['EXISTS']['START']['FORM']) || ($arResult['TRIGGERS']['EXISTS']['START']['IS_CLOSED_TRIGGER'] ?? null) == 'Y') ? '' : 'display: none;')?>">
										<div id="ENDPOINT_START_CLOSED_FORM" style="<?=(($arResult['TRIGGERS']['EXISTS']['START']['IS_CLOSED_TRIGGER'] ?? null) == 'Y' ? '' : 'display:none;')?>">
											<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_RUN_TIME')?>
											<select id="ENDPOINT_START_CLOSED_TRIGGER_TIME" name="ENDPOINT[START][CLOSED_TRIGGER_TIME]">
												<?
												$timesOfDayHours = array('00', '30');
												for($hour=0; $hour<24; $hour++):
													$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
													foreach($timesOfDayHours as $timePartHour):
														$hourFullPrint = $hourPrint.":".$timePartHour;
														?>
														<option value="<?=$hourFullPrint?>" <?=($hourFullPrint==$arResult['TRIGGERS']['EXISTS']['START']['CLOSED_TRIGGER_TIME'] ? 'selected': '')?>><?=$hourFullPrint?></option>
														<?
													endforeach;
												endfor;
												?>
											</select>
										</div>
										<div id="ENDPOINT_START_RUN_FOR_OLD_DATA_FORM" style="<?=($arResult['TRIGGERS']['EXISTS']['START']['CAN_RUN_FOR_OLD_DATA'] == 'Y' ? '' : 'display:none;')?>">
											<br>
											<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_RUN_FOR_OLD_DATA')?>
											<?if($arResult['TRIGGERS']['EXISTS']['START']['WAS_RUN_FOR_OLD_DATA'] == 'Y'):?>
												<input type="hidden" id="ENDPOINT_START_RUN_FOR_OLD_DATA" name="ENDPOINT[START][RUN_FOR_OLD_DATA]" value="Y">
												<span id="ENDPOINT_START_RUN_FOR_OLD_DATA_RESET" style="color: #878787;">
												<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_RUN_FOR_OLD_DATA_STATE')?>
											</span>
											<?else:?>
												<input class="adm-designed-checkbox" type="checkbox" id="ENDPOINT_START_RUN_FOR_OLD_DATA" name="ENDPOINT[START][RUN_FOR_OLD_DATA]" value="Y" <?=($arResult['TRIGGERS']['EXISTS']['START']['RUN_FOR_OLD_DATA']=='Y' ? 'checked' : '')?>>
												<label for="ENDPOINT_START_RUN_FOR_OLD_DATA" class="adm-designed-checkbox-label"></label>
											<?endif;?>
											<span data-hint="<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_RUN_FOR_OLD_DATA_HINT')?>">
										</div>
										<br>
										<br>
										<div id="ENDPOINT_START_FORM"><?=$arResult['TRIGGERS']['EXISTS']['START']['FORM']?></div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td colspan="2">
						<div class="sender-mailing-group-container sender-mailing-group-ok">
							<span class="sender-mailing-group-container-title"><span><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_COND_END')?></span></span>
							<span class="adm-white-container-p"><span><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_FIELD_COND_END_DESC')?></span></span>
						</div>

					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table class="sender-mailing-group">
							<tr>
								<td>
									<span class="bx-sender-caption">
										<?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_SELECT_COND')?>
									</span>
								</td>
								<td>
									<select id="EVENT_END" name="EVENT_END"
										onchange="BX.Sender.TriggerEditor.setTrigger(true, this.value);"
										class="bx-sender-form-control bx-sender-message-editor-field-select"
									>
										<option value=""><?=Loc::getMessage('SENDER_CAMPAIGN_EDIT_TMPL_CONDITION_NOTIFY')?></option>
										<?foreach($arResult['TRIGGERS']['AVAILABLE']['END'] as $triggerId => $triggerParams):?>
											<option
												value="<?=htmlspecialcharsbx($triggerId)?>"
												<?=($arResult['TRIGGERS']['EXISTS']['END']['ID'] == $triggerId ? 'selected' : '')?>
											><?=htmlspecialcharsbx($triggerParams['NAME'])?></option>
										<?endforeach;?>
									</select>
									<input type="hidden" id="ENDPOINT_END_MODULE_ID" name="ENDPOINT[END][MODULE_ID]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['END']['MODULE_ID'])?>">
									<input type="hidden" id="ENDPOINT_END_CODE" name="ENDPOINT[END][CODE]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['END']['CODE'])?>">
									<input type="hidden" id="ENDPOINT_END_IS_CLOSED_TRIGGER" name="ENDPOINT[END][IS_CLOSED_TRIGGER]" value="<?=htmlspecialcharsbx($arResult['TRIGGERS']['EXISTS']['END']['IS_CLOSED_TRIGGER'])?>">
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<div id="ENDPOINT_END_SETTINGS" class="sender-mailing-container" style="<?=((!empty($arResult['TRIGGERS']['EXISTS']['END']['FORM']) || $arResult['TRIGGERS']['EXISTS']['END']['IS_CLOSED_TRIGGER'] == 'Y') ? '' : 'display: none;')?>">
										<div id="ENDPOINT_END_CLOSED_FORM" style="<?=($arResult['TRIGGERS']['EXISTS']['END']['IS_CLOSED_TRIGGER'] == 'Y' ? '' : 'display:none;')?>">
											<?=GetMessage('SENDER_CAMPAIGN_EDIT_TMPL_RUN_TIME')?>
											<select id="ENDPOINT_END_CLOSED_TRIGGER_TIME" name="ENDPOINT[END][CLOSED_TRIGGER_TIME]">
												<?
												$timesOfDayHours = array('00', '30');
												for($hour=0; $hour<24; $hour++):
													$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
													foreach($timesOfDayHours as $timePartHour):
														$hourFullPrint = $hourPrint.":".$timePartHour;
														?>
														<option value="<?=$hourFullPrint?>" <?=($hourFullPrint==$arResult['TRIGGERS']['EXISTS']['END']['CLOSED_TRIGGER_TIME'] ? 'selected': '')?>><?=$hourFullPrint?></option>
														<?
													endforeach;
												endfor;
												?>
											</select>
											<br>
											<br>
										</div>
										<div id="ENDPOINT_END_FORM"><?=$arResult['TRIGGERS']['EXISTS']['END']['FORM']?></div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
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