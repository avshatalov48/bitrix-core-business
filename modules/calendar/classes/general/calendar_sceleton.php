<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;


class CCalendarSceleton
{
	// Show html
	public static function Build($params)
	{
		return;
		global $APPLICATION;
		$id = $params['id'];

		$Tabs = array(
			array('name' => Loc::getMessage('EC_TAB_MONTH'), 'title' => Loc::getMessage('EC_TAB_MONTH_TITLE'), 'id' => $id."_tab_month"),
			array('name' => Loc::getMessage('EC_TAB_WEEK'), 'title' => Loc::getMessage('EC_TAB_WEEK_TITLE'), 'id' => $id."_tab_week"),
			array('name' => Loc::getMessage('EC_TAB_DAY'), 'title' => Loc::getMessage('EC_TAB_DAY_TITLE'), 'id' => $id."_tab_day")
		);

		$bCalDAV = CCalendar::IsCalDAVEnabled() && $params['type'] == 'user';

		// Here can be added user's dialogs, scripts, html
		foreach(GetModuleEvents("calendar", "OnBeforeBuildSceleton", true) as $arEvent)
			ExecuteModuleEventEx($arEvent);

		$days = self::GetWeekDaysEx(CCalendar::GetWeekStart());
		?>
<script>
/* Event handler for user control*/
function bxcUserSelectorOnchange(arUsers){BX.onCustomEvent(window, 'onUserSelectorOnChange', [arUsers]);}
</script>
		<?if ($params['bShowSections'] || $params['bShowSuperpose']):?>
<div class="bxec-sect-cont" id="<?=$id?>_sect_cont">
	<b class="r2"></b><b class="r1"></b><b class="r0"></b>
		<?if ($params['bShowSections']):?>
		<span class="bxec-sect-cont-wrap" id="<?=$id?>sections">
			<b class="r-2"></b><b class="r-1"></b><b class="r-0"></b>
			<div class="bxec-sect-cont-inner">
				<div class="bxec-sect-title"><span class="bxec-spr bxec-flip"></span><span class="bxec-sect-title-text"><?=Loc::getMessage('EC_T_CALENDARS')?></span>
				<a id="<?=$id?>-add-section" class="bxec-sect-top-action" href="javascript:void(0);" title="<?=Loc::getMessage('EC_ADD_CAL_TITLE')?>"  hidefocus="true" style="visibility:hidden;"><?= strtolower(Loc::getMessage('EC_T_ADD'))?></a>
				</div>
				<div class="bxec-sect-cont-white">
					<div id="<?=$id?>sections-cont"></div>
					<?if($params['bShowTasks']):?>
					<div id="<?=$id?>tasks-sections-cont"></div>
					<?endif;?>
					<div id="<?=$id?>caldav-sections-cont"></div>
				</div>
			</div>
			<i class="r-0"></i><i class="r-1"></i><i class="r-2"></i>
		</span>
		<?endif; /*bShowSections*/ ?>

		<?if ($params['bShowSuperpose']):?>
		<span class="bxec-sect-cont-wrap" id="<?=$id?>sp-sections">
			<b class="r-2"></b><b class="r-1"></b><b class="r-0"></b>
			<div class="bxec-sect-cont-inner bxec-sect-superpose">
				<div class="bxec-sect-title"><span class="bxec-spr bxec-flip"></span><span class="bxec-sect-title-text"><?=Loc::getMessage('EC_T_SP_CALENDARS')?></span>
				<a id="<?=$id?>-manage-superpose" class="bxec-sect-top-action" href="javascript:void(0);" title="<?=Loc::getMessage('EC_ADD_EX_CAL_TITLE')?>"  hidefocus="true" style="visibility:hidden;"><?= strtolower(Loc::getMessage('EC_ADD_EX_CAL'))?></a>
				</div>
				<div class="bxec-sect-cont-white"  id="<?=$id?>sp-sections-cont"></div>
			</div>
			<i class="r-0"></i><i class="r-1"></i><i class="r-2"></i>
		</span>
		<?endif; /*bShowSuperpose*/ ?>
		<?if ($params['syncPannel']):?>
		<div class="bxec-sect-cont-inner">
			<div class="bxec-sect-title">
				<span class="bxec-sect-title-text"><?= Loc::getMessage('EC_CAL_SYNC_TITLE')?></span>
			</div>
			<div class="bxec-sect-cont-white" id="<?=$id?>-sync-inner-wrap"></div>
		</div>
		<?endif; /*syncPannel*/ ?>
		<span class="bxec-access-settings-wrap" id="<?=$id?>-access-settings-wrap">
			<a hidefocus="true" href="javascript:void(0);" class="bxec-access-settings" id="<?=$id?>-access-settings"><?=Loc::getMessage('EC_CAL_ACCESS_SETTINGS')?></a>
		</span>
	<i class="r0"></i><i class="r1"></i><i class="r2"></i>
</div>
		<?endif; /* bShowSections || bShowSuperpose*/?>


<div class="bxcal-loading" id="<?=$id?>_bxcal" style="">
<div class="bxec-tabs-cnt">
	<div class="bxec-tabs-div">
		<?foreach($Tabs as $tab):?>
		<div class="bxec-tab-div" title="<?=$tab['title']?>" id="<?=$tab['id']?>">
			<div class="bxec-tab-c"><span><?=$tab['name']?></span></div>
		</div>
		<?endforeach;?>
	</div>
	<div class="bxec-view-selector-cont">
		<div id="<?=$id?>_selector" class="bxec-selector-cont">
		<a class="bxec-sel-left"  id="<?=$id?>selector-prev"></a>
		<span class="bxec-sel-cont">
			<a class="bxec-sel-but" id="<?=$id?>selector-cont"><b></b><span class="bxec-sel-but-inner" id="<?=$id?>selector-cont-inner"><span class="bxec-sel-but-arr"></span></span><i></i></a>
		</span>
		<a class="bxec-sel-right" id="<?=$id?>selector-next"></a>
		</div>
		<div id="bxec_month_win_<?=$id?>" class="bxec-month-dialog">
			<div class="bxec-md-year-selector">
				<a class="bxec-sel-left"  id="<?=$id?>md-selector-prev"></a>
				<span class="bxec-md-year-text"><span class="bxec-md-year-text-inner" id="<?=$id?>md-year"></span></span>
				<a class="bxec-sel-right" id="<?=$id?>md-selector-next"></a>
			</div>
			<div class="bxec-md-month-list"  id="<?=$id?>md-month-list"></div>
		</div>
	</div>
	<div id="<?=$id?>_buttons_cont" class="bxec-buttons-cont"></div>
</div>
<div>
	<table class="BXEC-Calendar" cellPadding="0" cellSpacing="0" id="<?=$id?>_scel_table_month" style="display:none;">
	<tr class="bxec-days-title"><td>
		<div id="<?=$id?>_days_title" class="bxc-month-title"><?foreach($days as $day):?><b id="<?=$id.'_'.$day['2']?>" title="<?= $day['0']?>"><i><?= $day['1']?></i></b><?endforeach;?></div>
	</td></tr>
	<tr><td class="bxec-days-grid-td"><div id="<?=$id?>_days_grid" class="bxec-days-grid-cont"></div>
	</td></tr>
	</table>

	<table class="BXEC-Calendar-week" id="<?=$id?>_scel_table_week" cellPadding="0" cellSpacing="0" style="display:none;">
		<tr class="bxec-days-tbl-title">
			<td class="bxec-pad">
				<div class="bxec-day-t-event-holder"></div><img src="/bitrix/images/1.gif" width="40" height="1"/></td>
			<td class="bxec-pad2">
				<img src="/bitrix/images/1.gif" width="16" height="1"/>
			</td>
		</tr>
		<tr class="bxec-days-tbl-more-ev">
			<td class="bxec-pad"></td>
			<td class="bxec-pad2"></td>
		</tr>
		<tr class="bxec-days-tbl-grid">
			<td class="bxec-cont">
				<div id="<?=$id?>-week-timeline-wrap" class="bxec-timeline-div"></div>
			</td>
		</tr>
	</table>

	<table class="BXEC-Calendar-week" id="<?=$id?>_scel_table_day" cellPadding="0" cellSpacing="0" style="display:none;">
		<tr class="bxec-days-tbl-title"><td class="bxec-pad"><div class="bxec-day-t-event-holder"></div><img src="/bitrix/images/1.gif" width="40" height="1" /></td></tr>
		<tr class="bxec-days-tbl-more-ev"><td class="bxec-pad"></td></tr>
		<tr class="bxec-days-tbl-grid"><td class="bxec-cont" colSpan="2"><div class="bxec-timeline-div"></div></td></tr>
	</table>
</div>
</div>
<?
		if($params['bShowTasks'])
		{
		?>
<script>
// Js event handlers which will be captured in calendar's js
function onPopupTaskAdded(arTask){BX.onCustomEvent(window, 'onCalendarPopupTaskAdded', [arTask]);}
function onPopupTaskChanged(arTask){BX.onCustomEvent(window, 'onCalendarPopupTaskChanged', [arTask]);}
function onPopupTaskDeleted(taskId){BX.onCustomEvent(window, 'onCalendarPopupTaskDeleted', [taskId]);}
</script>
		<?
			$APPLICATION->IncludeComponent(
				"bitrix:tasks.iframe.popup",
				"",
				array(
					"ON_TASK_ADDED" => "onPopupTaskAdded",
					"ON_TASK_CHANGED" => "onPopupTaskChanged",
					"ON_TASK_DELETED" => "onPopupTaskDeleted",
					"TASKS_LIST" => $params['arTaskIds']
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
		}

		// Here can be added user's dialogs, scripts, html
		foreach(GetModuleEvents("calendar", "OnAfterBuildSceleton", true) as $arEvent)
			ExecuteModuleEventEx($arEvent);
	}

	public static function InitJS($config = array(), $data = array(), $additionalParams = array())
	{
		global $APPLICATION;
		CJSCore::Init(array('ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest','color_picker', 'sidepanel', 'clipboard'));

		if(\Bitrix\Main\Loader::includeModule('webservice'))
		{
			CJSCore::Init(array('stssync'));
		}

		if (\Bitrix\Main\Loader::includeModule('bitrix24') && !in_array(\CBitrix24::getLicenseType(), array('company', 'demo', 'edu', 'bis_inc', 'nfr')))
		{
			\CBitrix24::initLicenseInfoPopupJS();
		}

		?>
		<script>
			top.BXCRES = {};
			(function(window) {
				if (!window.BXEventCalendar)
				{
					var BXEventCalendar = {
						instances: {},

						Show: function(config, data, additionalParams)
						{
							BX.ready(function()
							{
								BXEventCalendar.instances[config.id] = new window.BXEventCalendar.Core(config, data, additionalParams);
							});
						},
						Get: function(id)
						{
							return BXEventCalendar.instances[id] || false;
						}
					};

					window.BXEventCalendar = BXEventCalendar;
				}
				BX.onCustomEvent(window, "onBXEventCalendarInit");
			})(window);
		</script><?

		$basePath = '/bitrix/js/calendar/new/';

		CJSCore::RegisterExt('event_calendar', array(
			'js' => array(
				$basePath.'calendar-core.js',
				$basePath.'calendar-view.js',
				$basePath.'calendar-view-transition.js',
				$basePath.'calendar-entry.js',
				$basePath.'calendar-section.js',
				$basePath.'calendar-controls.js',
				$basePath.'calendar-dialogs.js',
				$basePath.'calendar-simple-popup.js',
				$basePath.'calendar-simple-view-popup.js',
				$basePath.'calendar-section-slider.js',
				$basePath.'calendar-settings-slider.js',
				$basePath.'calendar-edit-entry-slider.js',
				$basePath.'calendar-view-entry-slider.js',
				$basePath.'calendar-sync-slider.js',
				$basePath.'calendar-util.js',
				$basePath.'calendar-search.js'
			),
			'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php',
			'css' => $basePath.'calendar.css',
			'rel' => array('ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest', 'dnd')
		));
		CUtil::InitJSCore(array('event_calendar'));

		$config['days'] = self::GetWeekDays();
		$config['month'] = array(Loc::getMessage('EC_JAN'), Loc::getMessage('EC_FEB'), Loc::getMessage('EC_MAR'), Loc::getMessage('EC_APR'), Loc::getMessage('EC_MAY'), Loc::getMessage('EC_JUN'), Loc::getMessage('EC_JUL'), Loc::getMessage('EC_AUG'), Loc::getMessage('EC_SEP'), Loc::getMessage('EC_OCT'), Loc::getMessage('EC_NOV'), Loc::getMessage('EC_DEC'));
		$config['month_r'] = array(Loc::getMessage('EC_JAN_R'), Loc::getMessage('EC_FEB_R'), Loc::getMessage('EC_MAR_R'), Loc::getMessage('EC_APR_R'), Loc::getMessage('EC_MAY_R'), Loc::getMessage('EC_JUN_R'), Loc::getMessage('EC_JUL_R'), Loc::getMessage('EC_AUG_R'), Loc::getMessage('EC_SEP_R'), Loc::getMessage('EC_OCT_R'), Loc::getMessage('EC_NOV_R'), Loc::getMessage('EC_DEC_R'));

		$APPLICATION->SetAdditionalCSS("/bitrix/js/calendar/cal-style.css");

		/*
		// Add scripts
		$arJS = array(
			'/bitrix/js/calendar/cal-core.js',
			'/bitrix/js/calendar/cal-dialogs.js',
			'/bitrix/js/calendar/cal-week.js',
			'/bitrix/js/calendar/cal-events.js',
			'/bitrix/js/calendar/cal-controlls.js'
		);

		 //Drag & drop
		$arJS[] = '/bitrix/js/main/dd.js';

		for($i = 0, $l = count($arJS); $i < $l; $i++)
		{
			$APPLICATION->AddHeadScript($arJS[$i]);
		}
		*/

		?>
		<div class="calendar-main-container" id="<?=$config['id']?>-main-container"></div>
		<script type="text/javascript">
		<?
		/*
		self::Localization();?>
		// Old one
		BX.ready(function(){
			new JCEC(<?= CUtil::PhpToJSObject($config)?>);
		});
		<? */?>

		window.BXEventCalendar.Show(
			<?= Json::encode($config)?>,
			<?= Json::encode($data)?>,
			<?= Json::encode($additionalParams)?>
		);
		</script>
		<?
	}

	private static function BuildDialogs($params)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
		$id = $params['id'];
		?><div id="<?=$id?>_dialogs_cont" style="display: none;"><?
		if (!$params['bReadOnly'])
		{
			self::DialogAddEventSimple($params);
			self::DialogEditSection($params);
			self::DialogExternalCalendars($params);
		}
		self::DialogSettings($params);
		self::DialogExportCalendar($params);
		self::DialogMobileCon($params);

		if ($params['bShowSuperpose'])
			self::DialogSuperpose($params);
		?></div><?
	}

	public static function Localization()
	{
		$arLangMess = array(
			'DelMeetingConfirm' => 'EC_JS_DEL_MEETING_CONFIRM',
			'DeclineConfirm' => 'EC_JS_DEL_MEETING_GUEST_CONFIRM',
			'DelEventConfirm' => 'EC_JS_DEL_EVENT_CONFIRM',
			'DelEventError' => 'EC_JS_DEL_EVENT_ERROR',
			'EventNameError' => 'EC_JS_EV_NAME_ERR',
			'EventSaveError' => 'EC_JS_EV_SAVE_ERR',
			'EventDatesError' => 'EC_JS_EV_DATES_ERR',
			'NewEvent' => 'EC_JS_NEW_EVENT',
			'EditEvent' => 'EC_JS_EDIT_EVENT',
			'DelEvent' => 'EC_JS_DEL_EVENT',
			'ViewEvent' => 'EC_JS_VIEW_EVENT',
			'From' => 'EC_JS_FROM',
			'To' => 'EC_JS_TO',
			'From_' => 'EC_JS_FROM_',
			'To_' => 'EC_JS_TO_',
			'EveryM' => 'EC_JS_EVERY_M',
			'EveryF' => 'EC_JS_EVERY_F',
			'EveryN' => 'EC_JS_EVERY_N',
			'EveryM_' => 'EC_JS_EVERY_M_',
			'EveryF_' => 'EC_JS_EVERY_F_',
			'EveryN_' => 'EC_JS_EVERY_N_',
			'DeDot' => 'EC_JS_DE_DOT',
			'DeAm' => 'EC_JS_DE_AM',
			'DeDes' => 'EC_JS_DE_DES',
			'_J' => 'EC_JS__J',
			'_U' => 'EC_JS__U',
			'WeekP' => 'EC_JS_WEEK_P',
			'DayP' => 'EC_JS_DAY_P',
			'MonthP' => 'EC_JS_MONTH_P',
			'YearP' => 'EC_JS_YEAR_P',
			'DateP_' => 'EC_JS_DATE_P_',
			'MonthP_' => 'EC_JS_MONTH_P_',
			'ShowPrevYear' => 'EC_JS_SHOW_PREV_YEAR',
			'ShowNextYear' => 'EC_JS_SHOW_NEXT_YEAR',
			'AddCalen' => 'EC_JS_ADD_CALEN',
			'AddCalenTitle' => 'EC_JS_ADD_CALEN_TITLE',
			'Edit' => 'EC_JS_EDIT',
			'Delete' => 'EC_JS_DELETE',
			'EditCalendarTitle' => 'EC_JS_EDIT_CALENDAR',
			'DelCalendarTitle' => 'EC_JS_DEL_CALENDAR',
			'NewCalenTitle' => 'EC_JS_NEW_CALEN_TITLE',
			'EditCalenTitle' => 'EC_JS_EDIT_CALEN_TITLE',
			'EventDiapStartError' => 'EC_JS_EV_FROM_ERR',
			'EventDiapEndError' => 'EC_JS_EV_DIAP_END_ERR',
			'CalenNameErr' => 'EC_JS_CALEN_NAME_ERR',
			'CalenSaveErr' => 'EC_JS_CALEN_SAVE_ERR',
			'DelCalendarConfirm' => 'EC_JS_DEL_CALENDAR_CONFIRM',
			'DelCalendarErr' => 'EC_JS_DEL_CALEN_ERR',
			'AddNewEvent' => 'EC_JS_ADD_NEW_EVENT',
			'SelectMonth' => 'EC_JS_SELECT_MONTH',
			'ShowPrevMonth' => 'EC_JS_SHOW_PREV_MONTH',
			'ShowNextMonth' => 'EC_JS_SHOW_NEXT_MONTH',
			'LoadEventsErr' => 'EC_JS_LOAD_EVENTS_ERR',
			'MoreEvents' => 'EC_JS_MORE',
			'Item' => 'EC_JS_ITEM',
			'Export' => 'EC_JS_EXPORT',
			'ExportTitle' => 'EC_JS_EXPORT_TILE',
			'CalHide' => 'EC_CAL_HIDE',
			'CalHideTitle' => 'EC_CAL_HIDE_TITLE',
			'CalAdd2SP' => 'EC_ADD_TO_SP',
			'CalAdd2SPTitle' => 'EC_CAL_ADD_TO_SP_TITLE',
			'HideSPCalendarErr' => 'EC_HIDE_SP_CALENDAR_ERR',
			'AppendSPCalendarErr' => 'EC_APPEND_SP_CALENDAR_ERR',
			'FlipperHide' => 'EC_FLIPPER_HIDE',
			'FlipperShow' => 'EC_FLIPPER_SHOW',
			'SelectAll' => 'EC_SHOW_All_CALS',
			'DeSelectAll' => 'EC_HIDE_All_CALS',
			'ExpDialTitle' => 'EC_EXP_DIAL_TITLE',
			'ExpDialTitleSP' => 'EC_EXP_DIAL_TITLE_SP',
			'ExpText' => 'EC_EXP_TEXT',
			'ExpTextSP' => 'EC_EXP_TEXT_SP',
			'UserCalendars' => 'EC_USER_CALENDARS',
			'DeleteDynSPGroupTitle' => 'EC_DELETE_DYN_SP_GROUP_TITLE',
			'DeleteDynSPGroup' => 'EC_DELETE_DYN_SP_GROUP',
			'CalsAreAbsent' => 'EC_CALS_ARE_ABSENT',
			'DelAllTrackingUsersConfirm' => 'EC_DEL_ALL_TRACK_USERS_CONF',
			'ShowPrevWeek' => 'EC_SHOW_PREV_WEEK',
			'ShowNextWeek' => 'EC_SHOW_NEXT_WEEK',
			'CurTime' => 'EC_CUR_TIME',
			'GoToDay' => 'EC_GO_TO_DAY',
			'DelGuestTitle' => 'EC_DEL_GUEST_TITLE',
			'DelGuestConf' => 'EC_DEL_GUEST_CONFIRM',
			'DelAllGuestsConf' => 'EC_DEL_ALL_GUESTS_CONFIRM',
			'GuestStatus_q' => 'EC_GUEST_STATUS_Q',
			'GuestStatus_y' => 'EC_GUEST_STATUS_Y',
			'GuestStatus_n' => 'EC_GUEST_STATUS_N',
			'UserProfile' => 'EC_USER_PROFILE',
			'AllGuests' => 'EC_ALL_GUESTS',
			'ShowAllGuests' => 'EC_ALL_GUESTS_TITLE',
			'DelEncounter' => 'EC_DEL_ENCOUNTER',
			'ConfirmEncY' => 'EC_ACCEPT_MEETING',
			'ConfirmEncN' => 'EC_EDEV_CONF_N',
			'ConfirmEncYTitle' => 'EC_EDEV_CONF_Y_TITLE',
			'ConfirmEncNTitle' => 'EC_EDEV_CONF_N_TITLE',
			'Confirmed' => 'EC_EDEV_CONFIRMED',
			'NotConfirmed' => 'EC_NOT_CONFIRMED',
			'NoLimits' => 'EC_T_DIALOG_NEVER',
			'Acc_busy' => 'EC_ACCESSIBILITY_B',
			'Acc_quest' => 'EC_ACCESSIBILITY_Q',
			'Acc_free' => 'EC_ACCESSIBILITY_F',
			'Acc_absent' => 'EC_ACCESSIBILITY_A',
			'Importance' => 'EC_IMPORTANCE',
			'Importance_high' => 'EC_IMPORTANCE_H',
			'Importance_normal' => 'EC_IMPORTANCE_N',
			'Importance_low' => 'EC_IMPORTANCE_L',
			'PrivateEvent' => 'EC_PRIVATE_EVENT',
			'LostSessionError' => 'EC_LOST_SESSION_ERROR',
			'ConnectToOutlook' => 'EC_CONNECT_TO_OUTLOOK',
			'ConnectToOutlookTitle' => 'EC_CONNECT_TO_OUTLOOK_TITLE',
			'UsersNotFound' => 'EC_USERS_NOT_FOUND',
			'UserBusy' => 'EC_USER_BUSY',
			'UsersNotAvailable' => 'EC_USERS_NOT_AVAILABLE',
			'UserAccessibility' => 'EC_ACCESSIBILITY',
			'CantDelGuestTitle' => 'EC_CANT_DEL_GUEST_TITLE',
			'Host' => 'EC_EDEV_HOST',
			'ViewingEvent' => 'EC_T_VIEW_EVENT',
			'NoCompanyStructure' => 'EC_NO_COMPANY_STRUCTURE',
			'DelOwnerConfirm' => 'EC_DEL_OWNER_CONFIRM',
			'MeetTextChangeAlert' => 'EC_MEET_TEXT_CHANGE_ALERT',
			'ImpGuest' => 'EC_IMP_GUEST',
			'NotImpGuest' => 'EC_NOT_IMP_GUEST',
			'DurDefMin' => 'EC_EDEV_REM_MIN',
			'DurDefHour1' => 'EC_PL_DUR_HOUR1',
			'DurDefHour2' => 'EC_PL_DUR_HOUR2',
			'DurDefDay' => 'EC_JS_DAY_P',
			'SelectMR' => 'EC_PL_SEL_MEET_ROOM',
			'OpenMRPage' => 'EC_PL_OPEN_MR_PAGE',
			'Location' => 'EC_LOCATION',
			'FreeMR' => 'EC_MR_FREE',
			'MRNotReservedErr' => 'EC_MR_RESERVE_ERR_BUSY',
			'MRReserveErr' => 'EC_MR_RESERVE_ERR',
			'FirstInList' => 'EC_FIRST_IN_LIST',
			'Settings' => 'EC_BUT_SET',
			'AddNewEventPl' => 'EC_JS_ADD_NEW_EVENT_PL',
			'DefMeetingName' => 'EC_DEF_MEETING_NAME',
			'NoGuestsErr' => 'EC_NO_GUESTS_ERR',
			'NoFromToErr' => 'EC_NO_FROM_TO_ERR',
			'MRNotExpireErr' => 'EC_MR_EXPIRE_ERR_BUSY',
			'CalDavEdit' => 'EC_CALDAV_EDIT',
			'NewExCalendar' => 'EC_NEW_EX_CAL',
			'CalDavDel' => 'EC_CALDAV_DEL',
			'CalDavCollapse' => 'EC_CALDAV_COLLAPSE',
			'CalDavRestore' => 'EC_CALDAV_RESTORE',
			'CalDavNoChange' => 'EC_CALDAV_NO_CHANGE',
			'CalDavTitle' => 'EC_MANAGE_CALDAV',
			'SyncOk' => 'EC_CALDAV_SYNC_OK',
			'SyncDate' => 'EC_CALDAV_SYNC_DATE',
			'SyncError' => 'EC_CALDAV_SYNC_ERROR',
			'AllCalendars' => 'EC_ALL_CALENDARS',
			'DelConCalendars' => 'DEL_CON_CALENDARS',
			'ExchNoSync' => 'EC_BAN_EXCH_NO_SYNC',
			'Add' => 'EC_T_ADD',
			'Save' => 'EC_T_SAVE',
			'Close' => 'EC_T_CLOSE',
			'GoExt' => 'EC_EXT_DIAL',
			'GoExtTitle' => 'EC_GO_TO_EXT_DIALOG',
			'Event' => 'EC_NEW_EVENT',
			'EventPl' => 'EC_NEW_EV_PL',
			'NewTask' => 'EC_NEW_TASK',
			'NewTaskTitle' => 'EC_NEW_TASK_TITLE',
			'NewSect' => 'EC_NEW_SECT',
			'NewSectTitle' => 'EC_NEW_SECT_TITLE',
			'NewExtSect' => 'EC_NEW_EX_SECT',
			'NewExtSectTitle' => 'EC_NEW_EX_SECT_TITLE',
			'DelSect' => 'EC_T_DELETE_CALENDAR',
			'Clear' => 'EC_CLEAR',
			'TaskView' => 'EC_TASKS_VIEW',
			'TaskEdit' => 'EC_TASKS_EDIT',
			'MyTasks' => 'EC_MY_TASKS',
			'NoAccessRights' => 'EC_NO_ACCESS_RIGHTS',
			'AddAttendees' => 'EC_ADD_ATTENDEES',
			'AddGuestsDef' => 'EC_ADD_GUESTS_DEF',
			'AddGuestsEmail' => 'EC_ADD_GUESTS_EMAIL',
			'AddGroupMemb' => 'EC_ADD_GROUP_MEMBER',
			'AddGroupMembTitle' => 'EC_ADD_GROUP_MEMBER_TITLE',
			'UserEmail' => 'EC_USER_EMAIL',
			'AttSumm' => 'EC_ATT_SUM',
			'AttAgr' => 'EC_ATT_AGR',
			'AttDec' => 'EC_ATT_DEC',
			'CalDavDialogTitle' => 'EC_CALDAV_TITLE',
			'AddCalDav' => 'EC_ADD_CALDAV',
			'UserSettings' => 'EC_SET_TAB_PERSONAL_TITLE',
			'ClearUserSetConf' => 'EC_CLEAR_SET_CONFIRM',
			'Adjust' => 'EC_ADD_EX_CAL',
			'ItIsYou' => 'EC_IT_IS_YOU',
			'DefaultColor' => 'EC_DEFAULT_COLOR',
			'SPCalendars' => 'EC_T_SP_CALENDARS',
			'NoCalendarsAlert' => 'EC_NO_CALENDARS_ALERT',
			'EventMRCheckWarn' => 'EC_MR_CHECK_PERIOD_WARN',
			'CalDavConWait' => 'EC_CAL_DAV_CON_WAIT',
			'Refresh' => 'EC_CAL_DAV_REFRESH',
			'acc_status_absent' => 'EC_PRIVATE_ABSENT',
			'acc_status_busy' => 'EC_ACCESSIBILITY_B',
			'ddDenyTask' => 'EC_DD_DENY_TASK',
			'ddDenyEvent' => 'EC_DD_DENY_EVENT',
			'eventTzHint' => 'EC_EVENT_TZ_HINT',
			'eventTzDefHint' => 'EC_EVENT_TZ_DEF_HINT',
			'reservePeriodWarn' => 'EC_RESERVE_PERIOD_WARN',
			'OpenCalendar' => 'EC_CAL_OPEN_LINK',
			'accessSettingsWarn' => 'EC_CAL_ACCESS_SETTINGS_WARN',
			'googleHide' => 'EC_CAL_GOOGLE_HIDE',
			'googleHideConfirm' => 'EC_CAL_GOOGLE_HIDE_CONFIRM',
			'googleDisconnectConfirm' => 'EC_CAL_REMOVE_GOOGLE_SYNC_CONFIRM',
			'syncConnect' => 'EC_CAL_SYNC_CONNECT',
			'syncDisconnect' => 'EC_CAL_SYNC_DISCONNECT',
			'syncMac' => 'EC_CAL_SYNC_MAC',
			'syncIphone' => 'EC_CAL_SYNC_IPHONE',
			'syncAndroid' => 'EC_CAL_SYNC_ANDROID',
			'syncOutlook' => 'EC_CAL_SYNC_OUTLOOK',
			'syncOffice365' => 'EC_CAL_SYNC_OFFICE_365',
			'syncGoogle' => 'EC_CAL_SYNC_GOOGLE',
			'syncExchange' => 'EC_CAL_SYNC_EXCHANGE',
			'syncOk' => 'EC_CAL_SYNC_OK',
			'connectMore' => 'EC_CAL_CONNECT_MORE',
			'showLess' => 'EC_CAL_SHOW_LESS',
			'SyncTitleMacOSX' => 'EC_MOBILE_SYNC_TITLE_MACOSX',
			'SyncTitleIphone' => 'EC_MOBILE_SYNC_TITLE_IPHONE',
			'SyncTitleAndroid' => 'EC_MOBILE_SYNC_TITLE_ANDROID',
			'disconnectOutlook' => 'EC_CAL_DISCONNECT_OUTLOOK',
			'disconnectIphone' => 'EC_CAL_DISCONNECT_IPHONE',
			'disconnectMac' => 'EC_CAL_DISCONNECT_MAC',
			'disconnectAndroid' => 'EC_CAL_DISCONNECT_ANDROID',
			'connectExchange' => 'EC_CAL_CONNECT_EXCHANGE',
			'disconnectExchange' => 'EC_CAL_DISCONNECT_EXCHANGE',
			'syncExchangeTitle' => 'EC_BAN_EXCH_SYNC_TITLE',
			'EC_DEL_REC_EVENT' => 'EC_DEL_REC_EVENT',
			'EC_EDIT_REC_EVENT' => 'EC_EDIT_REC_EVENT',
			'EC_REC_EV_ONLY_THIS_EVENT' => 'EC_REC_EV_ONLY_THIS_EVENT',
			'EC_REC_EV_NEXT' => 'EC_REC_EV_NEXT',
			'EC_REC_EV_ALL' => 'EC_REC_EV_ALL',
			'EC_REINVITE' => 'EC_REINVITE',
			'EC_DECLINE_REC_EVENT' => 'EC_DECLINE_REC_EVENT',
			'EC_D_REC_EV_ONLY_THIS_EVENT' => 'EC_D_REC_EV_ONLY_THIS_EVENT',
			'EC_D_REC_EV_NEXT' => 'EC_D_REC_EV_NEXT',
			'EC_D_REC_EV_ALL' => 'EC_D_REC_EV_ALL',
			'EC_BUSY_ALERT' => 'EC_BUSY_ALERT'
		);
?>
var EC_MESS = {
	0:0<?
		foreach($arLangMess as $m1 => $m2)
		{
			echo ', '.$m1." : '".GetMessageJS($m2)."'";
		}
	?>};<?
	}


	public static function GetWeekDays()
	{
		return array(
			array(Loc::getMessage('EC_MO_F'), Loc::getMessage('EC_MO'), 'MO'),
			array(Loc::getMessage('EC_TU_F'), Loc::getMessage('EC_TU'), 'TU'),
			array(Loc::getMessage('EC_WE_F'), Loc::getMessage('EC_WE'), 'WE'),
			array(Loc::getMessage('EC_TH_F'), Loc::getMessage('EC_TH'), 'TH'),
			array(Loc::getMessage('EC_FR_F'), Loc::getMessage('EC_FR'), 'FR'),
			array(Loc::getMessage('EC_SA_F'), Loc::getMessage('EC_SA'), 'SA'),
			array(Loc::getMessage('EC_SU_F'), Loc::getMessage('EC_SU'), 'SU')
		);
	}

	public static function GetWeekDaysEx($weekStart = 'MO')
	{
		$days = self::GetWeekDays();
		if ($weekStart == 'MO')
			return $days;
		$res = array();
		$start = false;
		while(list($k, $day) = each($days))
		{
			if ($day[2] == $weekStart)
			{
				$start = !$start;
				if (!$start)
					break;
			}
			if ($start)
				$res[] = $day;

			if ($start && $k == 6)
				reset($days);
		}
		return $res;
	}

	public static function GetAccessHTML($binging = 'calendar_section', $id = false)
	{
		if ($id === false)
			$id = 'bxec-'.$binging;
		$arTasks = CCalendar::GetAccessTasks($binging);
		?>
		<span style="display:none;">
		<select id="<?= $id?>" class="bxec-task-select">
			<?foreach ($arTasks as $taskId => $task):?>
				<option value="<?=$taskId?>"><?= htmlspecialcharsex($task['title']);?></option>
			<?endforeach;?>
		</select>
		</span>
		<?
	}

	public static function GetUserfieldsEditHtml($eventId, $url = '')
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
		if (!$USER_FIELDS || count($USER_FIELDS) == 0)
			return;

		$url = CHTTP::urlDeleteParams($url, array("action", "sessid", "bx_event_calendar_request", "event_id", "reqId"));
		$url = $url.(strpos($url,'?') === false ? '?' : '&').'action=userfield_save&bx_event_calendar_request=Y&'.bitrix_sessid_get();
?>
<form method="post" name="calendar-event-uf-form<?=$eventId?>" action="<?= $url?>" enctype="multipart/form-data" encoding="multipart/form-data">
<input name="event_id" type="hidden" value="" />
<input name="reqId" type="hidden" value="" />
<table cellspacing="0" class="bxc-prop-layout">
	<?foreach ($USER_FIELDS as $arUserField):?>
		<tr>
			<td class="bxc-prop"><?= htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
			<td class="bxc-prop">
				<?$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$arUserField["USER_TYPE"]["USER_TYPE_ID"],
					array(
						"bVarsFromForm" => false,
						"arUserField" => $arUserField,
						"form_name" => "calendar-event-uf-form".$eventId
					), null, array("HIDE_ICONS" => "Y")
				);?>
			</td>
		</tr>
	<?endforeach;?>
</table>
</form>
<?
	}

	public static function GetUserfieldsViewHtml($eventId)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
		if (!$USER_FIELDS || count($USER_FIELDS) == 0)
			return;
		$bFound = false;

		foreach ($USER_FIELDS as $arUserField)
		{
			if ($arUserField['VALUE'] == "" || (is_array($arUserField['VALUE']) && !count($arUserField['VALUE'])))
				continue;

			if (!$bFound)
			{
				$bFound = true;
				?><table cellspacing="0" class="bxc-prop-layout"><?
			}
			?>

			<tr>
				<td class="bxc-prop-name"><?= htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
				<td class="bxc-prop-value">
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.view",
						$arUserField["USER_TYPE"]["USER_TYPE_ID"],
						array("arUserField" => $arUserField),
						null,
						array("HIDE_ICONS"=>"Y")
					);?>
				</td>
			</tr>
		<?
		}

		if ($bFound)
		{
			?></table><?
		}
	}

	public static function DisplayColorSelector($id, $key = 'sect', $colors = false)
	{
		if (!$colors)
		{
			$colors = array(
				'#DAA187','#78D4F1','#C8CDD3','#43DAD2','#EECE8F','#AEE5EC','#B6A5F6','#F0B1A1','#82DC98','#EE9B9A',
				'#B47153','#2FC7F7','#A7ABB0','#04B4AB','#FFA801','#5CD1DF','#6E54D1','#F73200','#29AD49','#FE5957'
			);
		}

		?>
		<div  class="bxec-color-inp-cont">
			<input class="bxec-color-inp" id="<?=$id?>-<?=$key?>-color-inp"/>
			<a  id="<?=$id?>-<?=$key?>-text-color-inp" href="javascript:void('');" class="bxec-color-text-link"><?= Loc::getMessage('EC_TEXT_COLOR')?></a>
		</div>
		<div class="bxec-color-cont" id="<?=$id?>-<?=$key?>-color-cont">
		<?foreach($colors as $i => $color):?><span class="bxec-color-it"><a id="<?=$id?>-<?=$key?>-color-<?=$i?>" style="background-color:<?= $color?>" href="javascript:void(0);"></a></span><?endforeach;?>
		</div>
		<?
	}

	public static function CheckBitrix24Limits($params)
	{
		global $APPLICATION;
		$result = !CCalendar::IsBitrix24() || CBitrix24BusinessTools::isToolAvailable(CCalendar::GetCurUserId(), "calendar");
		if (!$result)
		{
			?><div id="<?=$params['id']?>-bitrix24-limit" class="bxec-b24-limit-wrap"><?
			$APPLICATION->IncludeComponent("bitrix:bitrix24.business.tools.info", "", array("SHOW_TITLE" => "Y"));
			?></div><?
		}
		return $result;
	}
}
?>