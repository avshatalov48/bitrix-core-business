<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use \Bitrix\Main\Localization\Loc;

$id = $arParams['id'];

$arDays = CCalendarSceleton::GetWeekDays();
$arWorTimeList = array();
for ($i = 0; $i < 24; $i++)
{
	$arWorTimeList[strval($i)] = CCalendar::FormatTime($i, 0);
	$arWorTimeList[strval($i).'.30'] = CCalendar::FormatTime($i, 30);
}
$timezoneList = CCalendar::GetTimezoneList();
$isPersonal = $arParams['is_personal'];
$showGeneralSettings = $arParams['show_general_settings'];
$showAccess = $arParams['show_access_control'] || $showGeneralSettings;
$showPersonalTitle = $showGeneralSettings && $isPersonal;
?>
<div class="webform-buttons calendar-form-buttons-fixed">
	<span data-role="save_btn" class="ui-btn ui-btn-success"><?= Loc::getMessage('EC_T_SAVE')?></span>
	<span data-role="close_btn" class="ui-btn ui-btn-link"><?= Loc::getMessage('EC_T_CLOSE')?></span>
</div>
<div class="calendar-slider-calendar-wrap">
	<div class="calendar-slider-header">
		<div class="calendar-head-area">
			<div class="calendar-head-area-inner">
				<div class="calendar-head-area-title">
					<span class="calendar-head-area-name"><?= Loc::getMessage('EC_BUT_SET')?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="calendar-slider-workarea" style="min-width: 400px;">
		<div class="calendar-slider-content">
			<div class="calendar-settings">
				<?if($isPersonal):?>
				<?if($showPersonalTitle):?>
				<div class="calendar-settings-title"><?= Loc::getMessage('EC_SET_TAB_PERSONAL')?></div>
				<?endif;?>
				<?if (\CTimeZone::optionEnabled()):?>
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_TIMEZONE')?></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select data-role="set_tz_sel" class="calendar-field calendar-field-select">
								<option value=""> - </option>
								<?foreach($timezoneList as $tz):?>
									<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
				<?endif;?>
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_ADV_MEETING_CAL')?></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select data-role="meet_section" class="calendar-field calendar-field-select"></select>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_CRM_SECTION')?></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select data-role="crm_section" class="calendar-field calendar-field-select"></select>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input data-role="show_declined" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_DECLINED')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input data-role="show_tasks" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_TASKS')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input data-role="sync_tasks" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SYNC_TASKS')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input data-role="show_completed_tasks" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_COMPLETED_TASKS')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-block">
						<label type="text" class="calendar-field-checkbox-label">
							<input data-role="deny_busy_invitation" type="checkbox" class="calendar-field-checkbox">
							<?=Loc::getMessage('EC_DENY_BUSY_INVITATION')?>
						</label>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-block">
						<label type="text" class="calendar-field-checkbox-label">
							<input data-role="show_week_numbers" type="checkbox" class="calendar-field-checkbox">
							<?=Loc::getMessage('EC_SHOW_WEEK_NUMBERS')?>
						</label>
					</div>
				</div>

				<?$APPLICATION->IncludeComponent('bitrix:main.mail.confirm', '', []);?>
				<div class="calendar-settings-control calendar-settings-email-wrap">
					<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_SEND_FROM_EMAIL')?>
						<div
							class="ui-icon ui-icon-common-question calendar-settings-question"
							data-hint="<?=Loc::getMessage('EC_SEND_FROM_EMAIL_HELP_TITLE')?>">
							<i></i>
						</div>
					</div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select data-role="send_from_email" class="calendar-field calendar-field-select"></select>
							<span class="tariff-lock"></span>
						</div>
					</div>
				</div>

				<?endif; //if($isPersonal)?>

				<?if ($showGeneralSettings):?>
				<div class="calendar-settings-title"><?=Loc::getMessage('EC_SET_TAB_BASE')?></div>
				<div class="calendar-settings-control calendar-settings-control-time">
						<div class="calendar-settings-control-name">
							<?= Loc::getMessage("EC_WORK_TIME")?>
						</div>
						<div class="calendar-settings-control-inner">
							<div class="calendar-event-time">
								<div class="calendar-field-container calendar-field-container-select">
									<div class="calendar-field-block">
										<select data-role="work_time_start" class="calendar-field calendar-field-select">
											<?foreach($arWorTimeList as $key => $val):?>
												<option value="<?= $key?>"><?= $val?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
							<div class="calendar-event-mdash"></div>
							<div class="calendar-event-time">
								<div class="calendar-field-container calendar-field-container-select">
									<div class="calendar-field-block">
										<select data-role="work_time_end" class="calendar-field calendar-field-select">
											<?foreach($arWorTimeList as $key => $val):?>
												<option value="<?= $key?>"><?= $val?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				<div class="calendar-settings-control">
						<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_WEEK_HOLIDAYS')?></div>
						<select size="7" multiple=true data-role="week_holidays" class="calendar-field-multiple-select">
							<?foreach($arDays as $day):?>
								<option class="calendar-field-multiple-select-item" value="<?= $day[2]?>" ><?= $day[0]?></option>
							<?endforeach;?>
						</select>
					</div>
				<div class="calendar-settings-control">
						<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_YEAR_HOLIDAYS')?></div>
						<div class="calendar-field-block">
							<input type="text" data-role="year_holidays" class="calendar-field calendar-field-string"/>
						</div>
					</div>
				<div class="calendar-settings-control">
						<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_YEAR_WORKDAYS')?></div>
						<div class="calendar-field-block">
							<input type="text" data-role="year_workdays" value="" class="calendar-field calendar-field-string"/>
						</div>
					</div>
				<?endif; //if ($showGeneralSettings):?>

				<!-- access -->
				<?if ($showAccess):?>
				<div class="calendar-settings-title"><?=Loc::getMessage('EC_SECT_ACCESS_TAB')?>
					<div
						class="ui-hint calendar-settings-access-hint"
						data-hint="<?=Loc::getMessage('EC_ACCESS_HINT_TITLE')?>">
						<span class="ui-hint-icon"></span>
					</div>
					<div data-role="type-access-message-card" class="calendar-settings-message-card"></div>
				</div>
					<div class="calendar-settings-access-rights">
						<div data-role="type-access-values-cont" class="bxec-access-cont"></div>
					</div>
					<!-- End of access -->
				<?endif; //if ($showAccess):?>
			</div>
		</div>
	</div>
</div>
