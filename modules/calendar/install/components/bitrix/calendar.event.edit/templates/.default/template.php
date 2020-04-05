<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
global $APPLICATION, $USER_FIELD_MANAGER;

$id = $arParams['id'];
$event = $arParams['event'];

$event['~DT_FROM_TS'] = $event['DT_FROM_TS'];
$event['~DT_TO_TS'] = $event['DT_TO_TS'];
$event['DT_FROM_TS'] = $arParams['fromTs'];
$event['DT_TO_TS'] = $arParams['fromTs'] + $event['DT_LENGTH'];

$UF = CCalendarEvent::GetEventUserFields($event);

$event['UF_CRM_CAL_EVENT'] = $UF['UF_CRM_CAL_EVENT'];
if (empty($event['UF_CRM_CAL_EVENT']['VALUE']))
	$event['UF_CRM_CAL_EVENT'] = false;

$event['UF_WEBDAV_CAL_EVENT'] = $UF['UF_WEBDAV_CAL_EVENT'];
if (empty($event['UF_WEBDAV_CAL_EVENT']['VALUE']))
	$event['UF_WEBDAV_CAL_EVENT'] = false;

$userId = CCalendar::GetCurUserId();

$arHost = CCalendar::GetUser($userId, true);
$arHost['AVATAR_SRC'] = CCalendar::GetUserAvatarSrc($arHost);
$arHost['URL'] = CCalendar::GetUserUrl($event['MEETING_HOST'], $arParams["PATH_TO_USER"]);
$arHost['DISPLAY_NAME'] = CCalendar::GetUserName($arHost);
$arParams['host'] = $arHost;

if ($event['IS_MEETING'])
{
	$attendees = array(
		'y' => array(
			'users' => array(),
			'count' => 4,
			'countMax' => 8,
			'title' => GetMessage('EC_ATT_Y'),
			'id' => "bxview-att-cont-y-".$event['ID']
		),
		'n' => array(
			'users' => array(),
			'count' => 2,
			'countMax' => 3,
			'title' => GetMessage('EC_ATT_N'),
			'id' => "bxview-att-cont-n-".$event['ID']
		),
		'q' => array(
			'users' => array(),
			'count' => 2,
			'countMax' => 3,
			'title' => GetMessage('EC_ATT_Q'),
			'id' => "bxview-att-cont-q-".$event['ID']
		)
	);

	$userIds = array();
	if (is_array($event['~ATTENDEES']) && count($event['~ATTENDEES']) > 0)
	{
		foreach ($event['~ATTENDEES'] as $i => $att)
		{
			$userIds[] = $att["USER_ID"];
			if ($userId == $att["USER_ID"])
				$curUserStatus = $att['STATUS'];
			$att['AVATAR_SRC'] = CCalendar::GetUserAvatarSrc($att);
			$att['URL'] = CCalendar::GetUserUrl($att["USER_ID"], $arParams["PATH_TO_USER"]);
			$attendees[strtolower($att['STATUS'])]['users'][] = $att;
		}
	}
}

if ($event['IS_MEETING'] && empty($event['ATTENDEES_CODES']))
	$event['ATTENDEES_CODES'] = CCalendarEvent::CheckEndUpdateAttendeesCodes($event);

$arParams['event'] = $event;
$arParams['UF'] = $UF;

$arTabs = array(
	array('name' => GetMessage('EC_EDEV_EVENT'), 'title' => GetMessage('EC_EDEV_EVENT_TITLE'), 'id' => $id."ed-tab-0", 'active' => true),
	array('name' => GetMessage('EC_T_DESC'), 'title' => GetMessage('EC_T_DESC_TITLE'), 'id' => $id."ed-tab-1"),
	array('name' => GetMessage('EC_EDEV_GUESTS'), 'title' => GetMessage('EC_EDEV_GUESTS_TITLE'), 'id' => $id."ed-tab-2", "show" => !!$arParams['bSocNet']),
	array('name' => GetMessage('EC_EDEV_ADD_TAB'), 'title' => GetMessage('EC_EDEV_ADD_TAB_TITLE'), 'id' => $id."ed-tab-3")
);

if($arParams['bSocNet'])
{
	CSocNetTools::InitGlobalExtranetArrays();
	$DESTINATION = CCalendar::GetSocNetDestination(false, $arParams['event']['ATTENDEES_CODES']);
}

$addWidthStyle = IsAmPmMode() ? ' ampm-width' : '';
?>
<form enctype="multipart/form-data" method="POST" name="event_edit_form" id="<?=$id?>_form">
<input type="hidden" value="Y" name="skip_unescape"/>
<input id="event-id<?=$id?>" type="hidden" value="0" name="id"/>
<input id="event-month<?=$id?>" type="hidden" value="0" name="month"/>
<input id="event-year<?=$id?>" type="hidden" value="0" name="year"/>
<input id="event-current-date-from<?=$id?>" type="hidden" name="current_date_from" value="0"/>
<input id="event-rec-edit-mode<?=$id?>" type="hidden" name="rec_edit_mode" value="0"/>
<div id="bxec_edit_ed_<?=$id?>" class="bxec-popup">
	<div style="width: 750px; height: 1px;"></div>
	<div class="popup-window-tabs" id="<?=$id?>_edit_tabs">
		<?foreach($arTabs as $tab):?>
			<span class="popup-window-tab<?if($tab['active']) echo' popup-window-tab-selected';?>" title="<?=$tab['title']?>" id="<?=$tab['id']?>" <?if($tab['show'] === false) echo'style="display:none;"';?>>
				<?= $tab['name']?>
			</span>
		<?endforeach;?>
	</div>
	<div class="popup-window-tabs-content"  id="<?=$id?>_edit_ed_d_tabcont">
		<?/* ####### TAB 0 : MAIN ####### */?>
		<div id="<?=$id?>ed-tab-0-cont" class="popup-window-tab-content popup-window-tab-content-selected">
			<div class="bxc-meeting-edit-note"><?= GetMessage('EC_EDIT_MEETING_NOTE')?></div>
			<div class="bxec-from-to-reminder" id="feed-cal-from-to-cont<?=$id?>">
				<div class="bxec-from-to-reminder-inner">
					<span class="bxec-date">
						<label class="bxec-date-label" for="<?=$id?>edev-from"><?=GetMessage('EC_EDEV_FROM_DATE_TIME')?></label>
						<label class="bxec-date-label-full-day" for="<?=$id?>edev-from"><?=GetMessage('EC_EDEV_DATE_FROM')?></label>
						<input id="feed-cal-event-from<?=$id?>" type="text" class="calendar-inp calendar-inp-cal" name="date_from"/>
					</span>
					<span class="bxec-time<?=$addWidthStyle?>"><?CClock::Show(array('inputId' => 'feed_cal_event_from_time'.$id, 'inputName' => 'time_from', 'inputTitle' => GetMessage('EC_EDEV_TIME_FROM'), 'showIcon' => false));?></span>
					<span class="bxec-mdash">&mdash;</span>
					<span class="bxec-date">
						<label class="bxec-date-label" for="<?=$id?>edev-from"><?=GetMessage('EC_EDEV_TO_DATE_TIME')?></label>
						<label class="bxec-date-label-full-day" for="<?=$id?>edev-from"><?=GetMessage('EC_EDEV_DATE_TO')?></label>
						<input id="feed-cal-event-to<?=$id?>" type="text" class="calendar-inp calendar-inp-cal" name="date_to"/>
					</span>
					<span class="bxec-time<?=$addWidthStyle?>"><?CClock::Show(array('inputId' => 'feed_cal_event_to_time'.$id, 'inputName' => 'time_to','inputTitle' => GetMessage('EC_EDEV_TIME_TO'), 'showIcon' => false));?></span>
					<div style="display:none;"><?$APPLICATION->IncludeComponent("bitrix:main.calendar",	"",Array("FORM_NAME" => "","INPUT_NAME" => "","INPUT_VALUE" => "","SHOW_TIME" => "N","HIDE_TIMEBAR" => "Y","SHOW_INPUT" => "N"),false, array("HIDE_ICONS" => "Y"));?></div>
					<span class="bxec-full-day">
						<input type="checkbox" id="event-full-day<?=$id?>" value="Y" name="skip_time"/>
						<label style="display: inline-block;" for="event-full-day<?=$id?>"><?= GetMessage('EC_FULL_DAY')?></label>
					</span>
				</div>

				<div id="event-tz-cont-outer<?=$id?>" class="bxec-timezone-outer-wrap bxec-tz-wrap">
					<span class="bxec-timezone-link bxec-tz-wrap" id="event-tz-switch<?=$id?>">
						<span class="bxec-tz-open"><?= GetMessage('EC_EVENT_TZ_BUT_OPEN')?></span>
						<span class="bxec-tz-close"><?= GetMessage('EC_EVENT_TZ_BUT_CLOSE')?></span>
					</span>
					<div id="event-tz-cont<?=$id?>" class="bxec-timezone-hidden-wrap bxec-tz-wrap">
						<div id="event-tz-inner-cont<?=$id?>" class="bxec-timezone-hidden">
							<div class="bxec-timezone-hidden-item">
								<select id="event-tz-from<?=$id?>" class="calendar-select calendar-tz-select" name="tz_from">
									<option value=""> - </option>
									<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
										<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
									<?endforeach;?>
								</select>
								<span class="bxec-mdash">&mdash;</span>
								<select id="event-tz-to<?=$id?>" class="calendar-select calendar-tz-select" name="tz_to">
									<option value=""> - </option>
									<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
										<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
									<?endforeach;?>
								</select>
								<span id="event-tz-tip<?=$id?>" class="bxec-popup-tip-btn"></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="event-tz-def-wrap<?=$id?>" class="bxec-popup-timezone bxec-tz-wrap" style="display: none;">
					<span class="bxec-field-label-edev">
						<label><?= GetMessage('EC_EVENT_ASK_TZ')?></label>
					</span>
				<select id="event-tz-def<?=$id?>" class="calendar-select calendar-tz-select" name="default_tz" style="width: 280px;">
					<option value=""> - </option>
					<?foreach($arResult['TIMEZONE_LIST'] as $tz):?>
						<option value="<?= $tz['timezone_id']?>"><?= htmlspecialcharsEx($tz['title'])?></option>
					<?endforeach;?>
				</select>
				<span id="event-tz-def-tip<?=$id?>" class="bxec-popup-tip-btn"></span>
			</div>

			<div class="bxec-popup-row">
				<input name="name" placeholder="<?= GetMessage('EC_T_EVENT_NAME')?>" type="text" id="<?=$id?>_edit_ed_name" class="calendar-inp bxec-inp-active" style="width: 560px; font-size: 18px!important;"/>
			</div>

			<div class="bxec-popup-row">
				<div class="bxec-reminder-collapsed" id="feed-cal-reminder-cont<?=$id?>">
					<span class="bxec-field-label-edev">
						<input class="bxec-check" type="checkbox" id="event-reminder<?=$id?>" value="Y" name="remind[checked]"/>
						<label class="bxec-rem-lbl" for="event-reminder<?=$id?>"><?= GetMessage('EC_EDEV_REMIND_EVENT')?>
						</label>
						<label class="bxec-rem-lbl-for" for="event-reminder<?=$id?>"><?= GetMessage('EC_EDEV_REMIND_FOR')?>:
						</label>
					</span>
					<span class="bxec-rem-value">
						<input class="calendar-inp" id="event_remind_count<?=$id?>" type="text" style="width: 30px" size="2" name="remind[count]">
						<select id="event_remind_type<?=$id?>" class="calendar-select" name="remind[type]" style="width: 106px;">
							<option value="min" selected="true"><?=GetMessage('EC_EDEV_REM_MIN')?></option>
							<option value="hour"><?=GetMessage('EC_EDEV_REM_HOUR')?></option>
							<option value="day"><?=GetMessage('EC_EDEV_REM_DAY')?></option>
						</select>
						<?=GetMessage('ECLF_REM_DE_VORHER')?>
					</span>
				</div>
			</div>

			<div class="bxec-popup-row" id="<?=$id?>_location_cnt">
				<span class="bxec-field-label-edev"><label for="<?=$id?>_planner_location1"><?=GetMessage('EC_LOCATION')?>:</label></span>
				<span class="bxec-field-val-2 bxecpl-loc-cont" >
				<input class="calendar-inp" style="width: 320px;" id="<?=$id?>_planner_location1" type="text"  title="<?=GetMessage('EC_LOCATION_TITLE')?>" value="<?= GetMessage('EC_PL_SEL_MEET_ROOM')?>" class="ec-label" />
				</span>
				<input id="event-location-old<?=$id?>" type="hidden" value="" name="location[OLD]"/>
				<input id="event-location-new<?=$id?>" type="hidden" value="" name="location[NEW]"/>
			</div>

			<?if($arParams['bIntranet']):?>
			<div class="bxec-popup-row bxec-ed-meeting-vis">
				<span class="bxec-field-label-edev"><label for="<?=$id?>_bxec_accessibility"><?=GetMessage('EC_ACCESSIBILITY')?>:</label></span>
				<span class="bxec-field-val-2" >
				<select  class="calendar-select" id="<?=$id?>_bxec_accessibility" name="accessibility" style="width: 360px;">
					<option value="busy" title="<?=GetMessage('EC_ACCESSIBILITY_B')?>"><?=GetMessage('EC_ACCESSIBILITY_B')?></option>
					<option value="quest" title="<?=GetMessage('EC_ACCESSIBILITY_Q')?>"><?=GetMessage('EC_ACCESSIBILITY_Q')?></option>
					<option value="free" title="<?=GetMessage('EC_ACCESSIBILITY_F')?>"><?=GetMessage('EC_ACCESSIBILITY_F')?></option>
					<option value="absent" title="<?=GetMessage('EC_ACCESSIBILITY_A')?> (<?=GetMessage('EC_ACC_EX')?>)"><?=GetMessage('EC_ACCESSIBILITY_A')?> (<?=GetMessage('EC_ACC_EX')?>)</option>
				</select>
				</span>
			</div>
			<?endif;?>

			<div class="bxec-popup-row" id="<?=$id?>_sect_cnt">
				<span class="bxec-field-label-edev"><label for="<?=$id?>_edit_ed_calend_sel"><?=GetMessage('EC_T_CALENDAR')?>:</label></span>
				<span class="bxec-field-val-2" >
				<select name="section" id="<?=$id?>_edit_ed_calend_sel" class="calendar-select" style="width: 360px;"></select><span id="<?=$id?>_edit_sect_sel_warn" class="bxec-warn" style="display: none;"><?=GetMessage('EC_T_CALEN_DIS_WARNING')?></span>
				</span>
			</div>

		</div>
		<?/* ####### END TAB 0 ####### */?>

		<?/* ####### TAB 1 : DESCRIPTION - LHE ####### */?>
		<div id="<?=$id?>ed-tab-1-cont" class="popup-window-tab-content bxec-d-cont-div-lhe">
			<!-- Description + files -->
			<?
			$APPLICATION->IncludeComponent(
				"bitrix:main.post.form",
				"",
				array(
					"FORM_ID" => "event_edit_form",
					"SHOW_MORE" => "Y",
					"PARSER" => Array(
						"Bold", "Italic", "Underline", "Strike", "ForeColor",
						"FontList", "FontSizeList", "RemoveFormat", "Quote",
						"Code", "CreateLink",
						"Image", "UploadFile",
						"InputVideo",
						"Table", "Justify", "InsertOrderedList",
						"InsertUnorderedList",
						"Source", "MentionUser"
					),
					"BUTTONS" => IsModuleInstalled('disk') ? Array(
						"UploadFile",
						"CreateLink",
						"InputVideo",
						"Quote"
					) : Array(
						"CreateLink",
						"InputVideo",
						"Quote"
					),
					"TEXT" => Array(
						"ID" => $id.'_edit_ed_desc',
						"NAME" => "desc",
						"VALUE" => $arParams['event']['DESCRIPTION'],
						"HEIGHT" => "280px"
					),
					"UPLOAD_WEBDAV_ELEMENT" => $arParams['UF']['UF_WEBDAV_CAL_EVENT'],
					"UPLOAD_FILE_PARAMS" => array("width" => 400, "height" => 400),
					"FILES" => Array(
						"VALUE" => array(),
						"DEL_LINK" => '',
						"SHOW" => "N"
					),
					"SMILES" => Array("VALUE" => array()),
					"LHE" => array(

						"id" => $arParams['id'].'_event_editor',
						"documentCSS" => "",
						"jsObjName" => $arParams['id'].'_event_editor',
						"fontFamily" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
						"fontSize" => "12px",
						"lazyLoad" => false,
						"setFocusAfterShow" => false
					)
				),
				false,
				array(
					"HIDE_ICONS" => "Y"
				)
			);
			?>
		</div>
		<?/* ####### END TAB 1 ####### */?>

		<?
		/* ####### TAB 2 : GUESTS ####### */
		if($arParams['bSocNet']):?>
		<div id="<?=$id?>ed-tab-2-cont" class="popup-window-tab-content">
			<a id="<?=$id?>_planner_link" href="javascript:void(0);" title="<?=GetMessage('EC_PLANNER_TITLE')?>" class="bxex-planner-link"><i></i><?=GetMessage('EC_PLANNER2')?></a>
			<div id="event-grid-att<?= $id?>" class="event-grid-dest-block">
				<div class="event-grid-dest-wrap-outer">
					<div class="event-grid-dest-label"><?=GetMessage("EC_EDEV_GUESTS")?>:</div>
					<div class="event-grid-dest-wrap" id="event-grid-dest-cont">
						<span id="event-grid-dest-item"></span>
					<span class="feed-add-destination-input-box" id="event-grid-dest-input-box">
						<input type="text" value="" class="feed-add-destination-inp" id="event-grid-dest-input">
					</span>
						<a href="#" class="feed-add-destination-link" id="event-grid-dest-add-link"></a>
						<script>
							<?
							if (is_array($GLOBALS["arExtranetGroupID"]))
							{
								?>
							if (typeof window['arExtranetGroupID'] == 'undefined')
							{
								window['arExtranetGroupID'] = <?=CUtil::PhpToJSObject($GLOBALS["arExtranetGroupID"])?>;
							}
							<?
						}
						?>
							BX.message({
								'BX_FPD_LINK_1':'<?=GetMessageJS("EC_DESTINATION_1")?>',
								'BX_FPD_LINK_2':'<?=GetMessageJS("EC_DESTINATION_2")?>'
							});
							window.editEventDestinationFormName = top.editEventDestinationFormName = 'edit_event_<?=randString(6)?>';
							//
							BX.SocNetLogDestination.init({
								name : editEventDestinationFormName,
								searchInput : BX('event-grid-dest-input'),
								extranetUser :  false,
								userSearchArea: 'I',
								bindMainPopup : { 'node' : BX('event-grid-dest-cont'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
								bindSearchPopup : { 'node' : BX('event-grid-dest-cont'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
								callback : {
									select : BxEditEventGridSelectCallback,
									unSelect : BxEditEventGridUnSelectCallback,
									openDialog : BxEditEventGridOpenDialogCallback,
									closeDialog : BxEditEventGridCloseDialogCallback,
									openSearch : BxEditEventGridOpenDialogCallback,
									closeSearch : BxEditEventGridCloseSearchCallback
								},
								items : {
									users : <?=(empty($DESTINATION['USERS'])? '{}': CUtil::PhpToJSObject($DESTINATION['USERS']))?>,
									groups : <?=(
									$DESTINATION["EXTRANET_USER"] == 'Y'
								|| (array_key_exists("DENY_TOALL", $DESTINATION) && $DESTINATION["DENY_TOALL"])
									? '{}'
									: "{'UA' : {'id':'UA','name': '".(!empty($DESTINATION['DEPARTMENT']) ? GetMessageJS("MPF_DESTINATION_3"): GetMessageJS("MPF_DESTINATION_4"))."'}}"
								)?>,
									sonetgroups : <?=(empty($DESTINATION['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($DESTINATION['SONETGROUPS']))?>,
									department : <?=(empty($DESTINATION['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($DESTINATION['DEPARTMENT']))?>,
									departmentRelation : <?=(empty($DESTINATION['DEPARTMENT_RELATION'])? '{}': CUtil::PhpToJSObject($DESTINATION['DEPARTMENT_RELATION']))?>
								},
								itemsLast : {
									users : <?=(empty($DESTINATION['LAST']['USERS'])? '{}': CUtil::PhpToJSObject($DESTINATION['LAST']['USERS']))?>,
									sonetgroups : <?=(empty($DESTINATION['LAST']['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($DESTINATION['LAST']['SONETGROUPS']))?>,
									department : <?=(empty($DESTINATION['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($DESTINATION['LAST']['DEPARTMENT']))?>,
									groups : <?=($DESTINATION["EXTRANET_USER"] == 'Y'? '{}': "{'UA':true}")?>
								},
								itemsSelected : <?=(empty($DESTINATION['SELECTED'])? '{}': CUtil::PhpToJSObject($DESTINATION['SELECTED']))?>
							});
						</script>
					</div>
				</div>

				<div class="event-grid-planner-cont" id="event-grid-planner-cont<?= $id?>">
				<?CCalendarPlanner::Init(array(
					'id' => $id.'_Planner'
				));?>
				</div>

				<!-- Meeting host -->
				<div class="event-grid-host-cont">
					<span class="event-grid-host-cont-label"><?= GetMessage('EC_EDEV_HOST')?>:</span>
					<a title="<?= htmlspecialcharsbx($arParams['host']['DISPLAY_NAME'])?>" href="<?= $arParams['host']['URL']?>" target="_blank" class="bxcal-user"><span class="bxcal-user-avatar-outer"><span class="bxcal-user-avatar"><img src="<?= $arParams['host']['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" /></span></span><span class="bxcal-user-name"><?= htmlspecialcharsbx($arParams['host']['DISPLAY_NAME'])?></span></a>
				</div>

				<!-- Attendees cont -->
				<div class="event-grid-attendees-cont">
					<div id="event-edit-att-y" class="event-grid-attendees-cont-y"></div>
					<div id="event-edit-att-n" class="event-grid-attendees-cont-n"></div>
					<div id="event-edit-att-q" class="event-grid-attendees-cont-q"></div>
				</div>
			</div>

			<div id="event-grid-meeting-params<?= $id?>" class="event-grid-params">
				<div class="bxec-add-meet-text"><a id="<?=$id?>_add_meet_text" href="javascript:void(0);"><?=GetMessage('EC_ADD_METTING_TEXT')?></a></div>
				<div class="bxec-meet-text" id="<?=$id?>_meet_text_cont">
					<div class="bxec-mt-d"><?=GetMessage('EC_METTING_TEXT')?> (<a id="<?=$id?>_hide_meet_text" href="javascript:void(0);" title="<?=GetMessage('EC_HIDE_METTING_TEXT_TITLE')?>"><?=GetMessage('EC_HIDE')?></a>): </div><br />
					<textarea name="meeting_text" class="bxec-mt-t" cols="63" id="<?=$id?>_meeting_text" rows="3"></textarea>
				</div>

				<div class="bxec-popup-row bxec-popup-row-checkbox">
					<input type="checkbox" id="<?=$id?>_ed_open_meeting" value="Y" name="open_meeting"/>
					<label style="display: inline-block;" for="<?=$id?>_ed_open_meeting"><?=GetMessage('EC_OPEN_MEETING')?></label>
				</div>
				<div class="bxec-popup-row bxec-popup-row-checkbox">
					<input type="checkbox" id="<?=$id?>_ed_notify_status" value="Y" name="meeting_notify"/>
					<label for="<?=$id?>_ed_notify_status"><?=GetMessage('EC_NOTIFY_STATUS')?></label>
				</div>
				<div class="bxec-popup-row bxec-popup-row-checkbox" id="<?=$id?>_ed_reivite_cont">
					<input type="checkbox" id="<?=$id?>_ed_reivite" value="Y" name="meeting_reinvite"/>
					<label for="<?=$id?>_ed_reivite"><?=GetMessage('EC_REINVITE')?></label>
				</div>
			</div>

			<div class="bxc-att-cont-cont">
				<span class="bxc-add-guest-link"  id="<?=$id?>_user_control_link"></span>
				<div id="<?=$id?>_attendees_cont" class="bxc-attendees-cont" style="display: none;">
					<div class="bxc-owner-cont">
						<div class="bxc-owner-cont">
							<span class="bxc-owner-title"><span><?= GetMessage('EC_EDEV_HOST')?>:</span></span>
							<span class="bxc-owner-value"><a id="<?=$id?>edit_host_link" href="javascript:void(0);"></a></span>
						</div>
					</div>
					<div class="bxc-no-att-notice"> - <?= GetMessage('EC_NO_ATTENDEES')?> - </div>
					<div class="bxc-att-title">
						<span><?= GetMessage('EC_EDEV_GUESTS')?>:</span>
						<div id="<?=$id?>_att_summary"></div>
					</div>
					<div class="bxc-att-cont" id="<?=$id?>_attendees_list" style="height: 200px;"></div>
				</div>
			</div>

		</div>
		<?/* ####### END TAB 2 ####### */?>
		<?endif; /* bSocNet */?>

		<?/* ####### TAB 3 : ADDITIONAL INFO ####### */?>
		<div id="<?=$id?>ed-tab-3-cont" class="popup-window-tab-content">
			<div class="bxec-popup-row-repeat" id="<?=$id?>_edit_ed_rep_cont">
				<div class="bxec-popup-row-2" id="<?=$id?>_edit_ed_rep_tr">
					<input id="event-rrule-byday<?=$id?>" type="hidden" value="0" name="rrule[BYDAY]"/>
					<input id="event-rrule-until<?=$id?>" type="hidden" value="0" name="rrule[UNTIL]"/>
					<input id="<?=$id?>_edit_ed_rep_check" type="checkbox" value="Y" name="rrule_enabled"/>
					<label for="<?=$id?>_edit_ed_rep_check" style="display: inline-block; margin: 3px 0 0 0; vertical-align:top;"><?=GetMessage('EC_T_REPEAT_CHECK_LABEL')?></label>
				</div>

				<div class="bxec-popup-row-bordered bxec-popup-repeat-details">

					<label for="<?=$id?>_edit_ed_rep_sel" class="event-grid-repeat-label"><?=GetMessage('EC_T_REPEAT')?>:</label>
					<select id="<?=$id?>_edit_ed_rep_sel" class="calendar-select" name="rrule[FREQ]" style="width: 175px;">
						<option value="DAILY"><?=GetMessage('EC_T_REPEAT_DAILY')?></option>
						<option value="WEEKLY"><?=GetMessage('EC_T_REPEAT_WEEKLY')?></option>
						<option value="MONTHLY"><?=GetMessage('EC_T_REPEAT_MONTHLY')?></option>
						<option value="YEARLY"><?=GetMessage('EC_T_REPEAT_YEARLY')?></option>
					</select>

					<span class="event-grid-repeat-cont">
						<span class="event-grid-rep-phrases" id="<?=$id?>_edit_ed_rep_phrase1"></span>
						<select id="<?=$id?>_edit_ed_rep_count" class="calendar-select" name="rrule[INTERVAL]">
							<?for ($i = 1; $i < 36; $i++):?>
								<option value="<?=$i?>"><?=$i?></option>
							<?endfor;?>
						</select>
						<span class="event-grid-rep-phrases" id="<?=$id?>_edit_ed_rep_phrase2"></span>

						<span id="<?=$id?>_edit_ed_rep_week_days" class="bxec-rep-week-days">
							<?
							$week_days = CCalendarSceleton::GetWeekDays();
							for($i = 0; $i < 7; $i++):
								$id_ = $id.'bxec_week_day_'.$i;?>
								<input id="<?=$id_?>" type="checkbox" value="<?= $week_days[$i][2]?>">
								<label for="<?=$id_?>" title="<?=$week_days[$i][0]?>"><?=$week_days[$i][1]?></label>
								<?if($i == 2)
								{
									echo '<br>';
								}?>
							<?endfor;?>
						</span>
					</span>

				</div>

				<div class="bxec-popup-row-bordered bxec-popup-repeat-details">
					<label class="bxec-popup-endson-label"><?= GetMessage('EC_ENDS_ON_LABEL')?>:</label>

					<div class="bxec-popup-endson-wrap">
						<span class="bxec-popup-endson-row">
							<input id="<?=$id?>edit-ev-rep-endson-never" name="rrule_endson" type="radio" checked="checked" value="never">
							<label for="<?=$id?>edit-ev-rep-endson-never"><?= GetMessage('EC_ENDS_ON_NEVER')?></label>
						</span>
						<span class="bxec-popup-endson-row">
							<input id="<?=$id?>edit-ev-rep-endson-count" name="rrule_endson" type="radio" value="count">
							<label for="<?=$id?>edit-ev-rep-endson-count">
								<?= GetMessage('EC_ENDS_ON_COUNT', array('#COUNT#' => '<input class="calendar-inp" id="'.$id.'edit-ev-rep-endson-count-input" type="text" style="width: 30px" size="2" name="rrule[COUNT]" placeholder="'.GetMessage('EC_ENDS_ON_COUNT_PLACEHOLDER').'">'))?>
							</label>
						</span>
						<span class="bxec-popup-endson-row">
							<input id="<?=$id?>edit-ev-rep-endson-until" name="rrule_endson" type="radio" value="until">
							<label for="<?=$id?>edit-ev-rep-endson-until">
								<?= GetMessage('EC_ENDS_ON_UNTIL', array('#UNTIL_DATE#' => '<input name="rrule[UNTIL]" class="calendar-inp calendar-inp-cal" id="'.$id.'edit-ev-rep-diap-to" type="text" style="width: 100px;" placeholder="'.GetMessage('EC_ENDS_ON_UNTIL_PLACEHOLDER').'"/>'))?>
							</label>
						</span>
					</div>
				</div>
			</div>

			<div class="bxec-popup-row-2 bxec-popup-row-bordered">
				<label for="<?=$id?>_bxec_importance"><?=GetMessage('EC_IMPORTANCE_TITLE')?>:</label>
				<select id="<?=$id?>_bxec_importance" class="calendar-select" name="importance" style="width: 250px;">
					<option value="high" style="font-weight: bold;"><?=GetMessage('EC_IMPORTANCE_H')?></option>
					<option value="normal" selected="true"><?=GetMessage('EC_IMPORTANCE_N')?></option>
					<option value="low" style="color: #909090;"><?=GetMessage('EC_IMPORTANCE_L')?></option>
				</select>
			</div>

			<?/*
			<div class="bxec-popup-row-2">
				<label for="<?=$id?>_bxec_accessibility"><?=GetMessage('EC_EVENT_TYPE')?>:</label>
				<span class="bxec-field-val-2" >
				<select  class="calendar-select" id="<?=$id?>_bxec_type" name="event_type" style="width: 360px;">
					<option value=""><?=GetMessage('EC_EVENT_TYPE_NO')?></option>
					<option value="business_trip"><?=GetMessage('EC_EVENT_TYPE_BUSINESS_TRIP')?></option>
					<option value="meeting"><?=GetMessage('EC_EVENT_TYPE_MEETING')?></option>
					<option value="call"><?=GetMessage('EC_EVENT_TYPE_CALL')?></option>
					<option value="discussion"><?=GetMessage('EC_EVENT_TYPE_DISCUSSION')?></option>
					<option value="conference"><?=GetMessage('EC_EVENT_TYPE_CONFERENCE')?></option>
					<option value="vacation"><?=GetMessage('EC_EVENT_TYPE_VACATION')?></option>
					<option value="sick"><?=GetMessage('EC_EVENT_TYPE_SICK')?></option>
				</select>
				</span>
			</div>
			*/?>

			<?if($arParams['type'] == 'user'):?>
			<div class="bxec-popup-row-bordered bxec-popup-row-private">
				<input id="<?=$id?>_bxec_private" type="checkbox" value="Y" title="<?=GetMessage('EC_PRIVATE_NOTICE')?>" name="private_event">
				<label for="<?=$id?>_bxec_private" title="<?=GetMessage('EC_PRIVATE_NOTICE')?>"><?=GetMessage('EC_PRIVATE_EVENT')?></label>
				<div><?= GetMessage('EC_PRIVATE_NOTICE')?></div>
			</div>
			<?endif;?>

			<!-- Color -->
			<div class="bxec-popup-row-bordered bxec-popup-row-color">
				<input id="<?=$id?>_bxec_color" type="hidden" value="" name="color" />
				<input id="<?=$id?>_bxec_text_color" type="hidden" value="" name="text_color" />
				<label class="bxec-color-label" for="<?=$id?>-event-color-inp"><?=GetMessage('EC_T_COLOR')?>:</label>
				<div class="bxec-color-selector-cont">
				<?CCalendarSceleton::DisplayColorSelector($id, 'event');?>
				</div>
			</div>

			<!-- Userfields -->
			<? if (isset($UF['UF_CRM_CAL_EVENT'])):?>
			<div id="<?=$id?>bxec_uf_group" class="bxec-popup-row-bordered">
				<?$crmUF = $UF['UF_CRM_CAL_EVENT'];?>
				<label for="event-crm<?=$id?>" class="bxec-uf-crm-label"><?= htmlspecialcharsbx($crmUF["EDIT_FORM_LABEL"])?>:</label>
				<div class="bxec-uf-crm-cont">
					<?$APPLICATION->IncludeComponent(
						"bitrix:system.field.edit",
						$crmUF["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"bVarsFromForm" => false,
							"arUserField" => $crmUF,
							"form_name" => 'event_edit_form'
						), null, array("HIDE_ICONS" => "Y")
					);?>
				</div>
			</div>
			<?endif;?>
		</div>
		<?/* ####### END TAB 3 ####### */?>
	</div>
</div>
</form>