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
$showAccess = $showGeneralSettings;
$showPersonalTitle = $showGeneralSettings && $isPersonal;
?>
<div class="webform-buttons calendar-form-buttons-fixed">
	<span id="<?= $id?>_save" class="webform-small-button webform-small-button-blue"><?= Loc::getMessage('EC_T_SAVE')?></span>
	<span id="<?= $id?>_close" class="webform-button-link"><?= Loc::getMessage('EC_T_CLOSE')?></span>
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
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><?=Loc::getMessage('EC_TIMEZONE')?></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select id="<?=$id?>_set_tz_sel" class="calendar-field calendar-field-select">
								<option value=""> - </option>
								<?foreach($timezoneList as $tz):?>
									<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><label for="<?=$id?>_uset_calend_sel"><?=Loc::getMessage('EC_ADV_MEETING_CAL')?></label></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select id="<?=$id?>_meet_section" class="calendar-field calendar-field-select"></select>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control">
					<div class="calendar-settings-control-name"><label for="<?=$id?>_uset_calend_sel"><?=Loc::getMessage('EC_CRM_SECTION')?></label></div>
					<div class="calendar-field-container calendar-field-container-select">
						<div class="calendar-field-block">
							<select id="<?=$id?>_crm_section" class="calendar-field calendar-field-select"></select>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input id="<?=$id?>_show_declined" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_DECLINED')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input id="<?=$id?>_show_tasks" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_TASKS')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-container calendar-field-container-checkbox">
						<div class="calendar-field-block">
							<label type="text" class="calendar-field-checkbox-label">
								<input id="<?=$id?>_show_completed_tasks" type="checkbox" class="calendar-field-checkbox">
								<?=Loc::getMessage('EC_OPTION_SHOW_COMPLETED_TASKS')?>
							</label>
						</div>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-block">
						<label type="text" class="calendar-field-checkbox-label">
							<input id="<?=$id?>_deny_busy_invitation" type="checkbox" class="calendar-field-checkbox">
							<?=Loc::getMessage('EC_DENY_BUSY_INVITATION')?>
						</label>
					</div>
				</div>
				<div class="calendar-settings-control calendar-settings-checkbox">
					<div class="calendar-field-block">
						<label type="text" class="calendar-field-checkbox-label">
							<input id="<?=$id?>_show_week_numbers" type="checkbox" class="calendar-field-checkbox">
							<?=Loc::getMessage('EC_SHOW_WEEK_NUMBERS')?>
						</label>
					</div>
				</div>
				<span id="<?=$id?>_manage_caldav" class="calendar-settings-link-option"><?= Loc::getMessage('EC_MANAGE_CALDAV')?></span>
				<?endif; //if($isPersonal)?>

				<?if ($showGeneralSettings):?>
				<div class="calendar-settings-title"><?=Loc::getMessage('EC_SET_TAB_BASE')?></div>
				<div class="calendar-settings-control calendar-settings-control-time">
						<div class="calendar-settings-control-name">
							<label for="<?=$id?>work_time_start"><?= Loc::getMessage("EC_WORK_TIME")?></label></div>
						<div class="calendar-settings-control-inner">
							<div class="calendar-event-time">
								<div class="calendar-field-container calendar-field-container-select">
									<div class="calendar-field-block">
										<select id="<?=$id?>work_time_start" class="calendar-field calendar-field-select">
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
										<select id="<?=$id?>work_time_end" class="calendar-field calendar-field-select">
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
						<div class="calendar-settings-control-name"><label for="<?=$id?>week_holidays"><?=Loc::getMessage('EC_WEEK_HOLIDAYS')?></label></div>
						<select size="7" multiple=true id="<?=$id?>week_holidays" class="calendar-field-multiple-select">
							<?foreach($arDays as $day):?>
								<option class="calendar-field-multiple-select-item" value="<?= $day[2]?>" ><?= $day[0]?></option>
							<?endforeach;?>
						</select>
					</div>
				<div class="calendar-settings-control">
						<div class="calendar-settings-control-name"><label for="<?=$id?>year_holidays"><?=Loc::getMessage('EC_YEAR_HOLIDAYS')?></label></div>
						<div class="calendar-field-block">
							<input type="text" id="<?=$id?>year_holidays" class="calendar-field calendar-field-string"/>
						</div>
					</div>
				<div class="calendar-settings-control">
						<div class="calendar-settings-control-name"><label for="<?=$id?>year_workdays"><?=Loc::getMessage('EC_YEAR_WORKDAYS')?></label></div>
						<div class="calendar-field-block">
							<input type="text" id="<?=$id?>year_workdays" value="" class="calendar-field calendar-field-string"/>
						</div>
					</div>
				<?endif; //if ($showGeneralSettings):?>

				<!-- access -->
				<?if ($showAccess):?>
				<div class="calendar-settings-title"><?=Loc::getMessage('EC_SECT_ACCESS_TAB')?></div>
					<div class="calendar-settings-access-rights">
						<div id="<?= $id?>type-access-values-cont" class="bxec-access-cont"></div>
					</div>
					<!-- End of access -->
				<?endif; //if ($showAccess):?>
			</div>
		</div>
	</div>
</div>
