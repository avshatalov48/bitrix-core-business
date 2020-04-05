<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
global $APPLICATION, $USER_FIELD_MANAGER;

$id = $arParams['id'];
$event = $arParams['event'];

$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
$toTs = CCalendar::Timestamp($event['DATE_TO']);

if ($event['DT_SKIP_TIME'] == "Y")
{
	$toTs += CCalendar::DAY_LENGTH;
}

if ($event['DT_SKIP_TIME'] !== "Y")
{
	$fromTs -= $event['~USER_OFFSET_FROM'];
	$toTs -= $event['~USER_OFFSET_TO'];
}

$UF = CCalendarEvent::GetEventUserFields($event);

if ($event['PARENT_ID'] && $event['IS_MEETING'])
{
	$attRes = CCalendarEvent::GetAttendees(array($event['PARENT_ID']));
	if ($attRes && isset($attRes[$event['PARENT_ID']]))
		$event['~ATTENDEES'] = $attRes[$event['PARENT_ID']];
}

if (!is_null($event['UF_CRM_CAL_EVENT']))
{
	$event['UF_CRM_CAL_EVENT'] = $UF['UF_CRM_CAL_EVENT'];
	if (empty($event['UF_CRM_CAL_EVENT']['VALUE']))
		$event['UF_CRM_CAL_EVENT'] = false;
}

if (!is_null($event['UF_WEBDAV_CAL_EVENT']))
{
	$event['UF_WEBDAV_CAL_EVENT'] = $UF['UF_WEBDAV_CAL_EVENT'];
	if(empty($event['UF_WEBDAV_CAL_EVENT']['VALUE']))
		$event['UF_WEBDAV_CAL_EVENT'] = false;
}

$event['FROM_WEEK_DAY'] = FormatDate('D', $fromTs);
$event['FROM_MONTH_DAY'] = FormatDate('j', $fromTs);
$event['FROM_MONTH'] = FormatDate('n', $fromTs);

$arHost = CCalendar::GetUser($event['MEETING_HOST'], true);
$arHost['AVATAR_SRC'] = CCalendar::GetUserAvatarSrc($arHost);
$arHost['URL'] = CCalendar::GetUserUrl($event['MEETING_HOST'], $arParams["PATH_TO_USER"]);
$arHost['DISPLAY_NAME'] = CCalendar::GetUserName($arHost);
$curUserStatus = '';
$userId = CCalendar::GetCurUserId();

$viewComments = CCalendar::IsPersonal($event['CAL_TYPE'], $event['OWNER_ID'], $userId) || CCalendarSect::CanDo('calendar_view_full', $event['SECT_ID'], $userId);

if ($event['IS_MEETING'] && empty($event['ATTENDEES_CODES']))
	$event['ATTENDEES_CODES'] = CCalendarEvent::CheckEndUpdateAttendeesCodes($event);

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
		),
		'm' => array(
			'users' => array(),
			'count' => 4,
			'countMax' => 8,
			'title' => GetMessage('EC_ATT_M'),
			'id' => "bxview-att-cont-m-".$event['ID']
		)
	);

	if (is_array($event['~ATTENDEES']))
	{
		foreach ($event['~ATTENDEES'] as $att)
		{
			if ($userId == $att["USER_ID"])
			{
				$curUserStatus = $att['STATUS'];
				$viewComments = true;
			}
			$att['AVATAR_SRC'] = CCalendar::GetUserAvatarSrc($att);
			$att['URL'] = CCalendar::GetUserUrl($att["USER_ID"], $arParams["PATH_TO_USER"]);
			$status = (strtolower($att['STATUS']) == 'h' || $att['STATUS'] == '') ? 'y' : $att['STATUS']; // ?
			$attendees[strtolower($status)]['users'][] = $att;
		}
	}
}

$arTabs = array(
	array('name' => GetMessage('EC_BASIC'), 'title' => GetMessage('EC_BASIC_TITLE'), 'id' => $id."view-tab-0", 'active' => true),
	array('name' => GetMessage('EC_EDEV_ADD_TAB'), 'title' => GetMessage('EC_EDEV_ADD_TAB_TITLE'), 'id' => $id."view-tab-1")
);
?>

<div id="bxec_view_ed_<?=$id?>" class="bxec-popup">
<div style="width: 700px; height: 1px;"></div>
<div class="popup-window-tabs" id="<?=$id?>_viewev_tabs">
	<?foreach($arTabs as $tab):?>
		<span class="popup-window-tab<? if($tab['active']) echo ' popup-window-tab-selected';?>" title="<?= (isset($tab['title']) ? $tab['title'] : $tab['name'])?>" id="<?= $tab['id']?>" <?if($tab['show'] === false) echo'style="display:none;"';?>>
			<?=$tab['name']?>
		</span>
	<?endforeach;?>
</div>
<div class="popup-window-tabs-content">
<?/* ####### TAB 0 : BASIC ####### */?>
<div id="<?=$id?>view-tab-0-cont" class="popup-window-tab-content popup-window-tab-content-selected">
<div class="bx-cal-view-icon">
	<div class="bx-cal-view-icon-day"><?= $event['FROM_WEEK_DAY']?></div>
	<div class="bx-cal-view-icon-date"><?= $event['FROM_MONTH_DAY']?></div>
</div>
<div class="bx-cal-view-text">
	<table>
		<tr>
			<td class="bx-cal-view-text-cell-l"><?= GetMessage('EC_T_NAME')?>:</td>
			<td class="bx-cal-view-text-cell-r"><span class="bx-cal-view-name"><?= $event['NAME']?></span></td>
		</tr>
		<tr>
			<td class="bx-cal-view-text-cell-l"><?= GetMessage('EC_DATE')?>:</td>
			<td class="bx-cal-view-text-cell-r bx-cal-view-from-to">
				<span><?= CCalendar::GetFromToHtml($fromTs, $toTs, $event['DT_SKIP_TIME'] == 'Y', $event['DT_LENGTH']);?>
				</span>
				<?
				if (
					$event['DT_SKIP_TIME'] != 'Y' &&
					(intVal($event['~USER_OFFSET_FROM']) !== 0 ||
					intVal($event['~USER_OFFSET_TO']) !== 0 ||
					$event['TZ_FROM'] != $event['TZ_TO'] ||
					$event['TZ_FROM'] !== CCalendar::GetUserTimezoneName($userId))
				)
				{
					if ($event['TZ_FROM'] == $event['TZ_TO'])
					{
						$timezoneHint = CCalendar::GetFromToHtml(CCalendar::Timestamp($event['DATE_FROM']), CCalendar::Timestamp($event['DATE_TO']), $event['DT_SKIP_TIME'] == 'Y', $event['DT_LENGTH']);
						$timezoneHint .= ' ('.$event['TZ_FROM'].')';
					}
					else
					{
						$timezoneHint = GetMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $event['DATE_FROM'].' ('.$event['TZ_FROM'].')', '#DATE_TO#' => $event['DATE_TO'].' ('.$event['TZ_TO'].')'));
					}
					?>
					<span id="bxec-view-tz-hint<?=$id?>" data-bx-hint="<?= $timezoneHint?>" class="bx-cal-view-timezon-icon"></span>
					<?
				}
				?>
			</td>
		</tr>
		<?if ($event['RRULE'])
		{?>
			<tr>
				<td class="bx-cal-view-text-cell-l"><?=GetMessage('EC_T_REPEAT')?>:</td>
				<td class="bx-cal-view-text-cell-r"><?= CCalendarEvent::GetRRULEDescription($event, true)?></td>
			</tr>
		<?} /* if ($event['RRULE']) */?>
		<?if (!empty($event['LOCATION']))
		{?>
			<tr>
				<td class="bx-cal-view-text-cell-l"><?= GetMessage('EC_LOCATION')?>:</td>
				<td class="bx-cal-view-text-cell-r"><span class="bx-cal-location"><?= htmlspecialcharsEx(CCalendar::GetTextLocation($event['LOCATION']))?></span></td>
			</tr>
		<?} /* if (!empty($event['LOCATION'])) */?>
	</table>
</div>

<?if (!empty($event['~DESCRIPTION'])):?>
	<div class="bx-cal-view-description">
		<div class="feed-cal-view-desc-title"><?= GetMessage('EC_T_DESC')?>:</div>
		<div class="bx-cal-view-desc-cont"><?= htmlspecialcharsback($event['~DESCRIPTION'])?></div>
	</div>
<?endif;?>

<?if ($event['UF_WEBDAV_CAL_EVENT']):?>
	<div class="bx-cal-view-files" id="bx-cal-view-files-<?=$id?><?=$event['ID']?>">
		<?$APPLICATION->IncludeComponent(
			"bitrix:system.field.view",
			$event['UF_WEBDAV_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_WEBDAV_CAL_EVENT']),
			null,
			array("HIDE_ICONS"=>"Y")
		);?>
	</div>
<?endif;?>

<?if ($event['UF_CRM_CAL_EVENT']):?>
	<div class="bx-cal-view-crm">
		<div class="bxec-crm-title"><?= htmlspecialcharsbx($event['UF_CRM_CAL_EVENT']["EDIT_FORM_LABEL"])?>:</div>
		<?$APPLICATION->IncludeComponent(
			"bitrix:system.field.view",
			$event['UF_CRM_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
			array("arUserField" => $event['UF_CRM_CAL_EVENT']),
			null,
			array("HIDE_ICONS"=>"Y")
		);?>
	</div>
<?endif;?>

<div id="<?=$id?>bxec_view_uf_group" class="bxec-popup-row" style="display: none;">
	<div class="bxec-popup-row-title"><?= GetMessage('EC_EDEV_ADD_TAB')?></div>
	<div id="<?=$id?>bxec_view_uf_cont"></div>
</div>

<?if($arParams['bSocNet'] && $event['IS_MEETING']):?>
	<div class="bx-cal-view-meeting-cnt">
		<table>
			<tr>
				<td class="bx-cal-view-att-cell-l bx-cal-bot-border"><span><?= GetMessage('EC_EDEV_HOST')?>:</span></td>
				<td class="bx-cal-view-att-cell-r bx-cal-bot-border">
					<a title="<?= htmlspecialcharsbx($arHost['DISPLAY_NAME'])?>" href="<?= $arHost['URL']?>" target="_blank" class="bxcal-att-popup-img bxcal-att-popup-att-full"><span class="bxcal-att-popup-avatar-outer"><span class="bxcal-att-popup-avatar"><img src="<?= $arHost['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" /></span></span><span class="bxcal-att-name"><?= htmlspecialcharsbx($arHost['DISPLAY_NAME'])?></span></a>
				</td>
			</tr>
			<tr>
				<td class="bx-cal-view-att-cell-l"></td>
				<td class="bx-cal-view-att-cell-r" style="padding-top: 5px;">
					<div class="bx-cal-view-title"><?= GetMessage('EC_EDEV_GUESTS')?></div>
					<div class="bx-cal-att-dest-cont">
						<?
						$arDest = CCalendar::GetFormatedDestination($event['ATTENDEES_CODES']);
						$cnt = count($arDest);
						for($i = 0; $i < $cnt; $i++ )
						{
							$dest = $arDest[$i];
							?><span class="bx-cal-att-dest-block"><?= $dest['TITLE']?></span><?
							if ($i < count($arDest) - 1)
								echo ', ';
						}
						?>
					</div>
				</td>
			</tr>

			<?
			foreach($attendees as $k => $arAtt)
			{
				if (!$arAtt || empty($arAtt['users']))
					continue;
				?>
				<tr>
					<td class="bx-cal-view-att-cell-l"><?= $arAtt['title']?>:</td>
					<td class="bx-cal-view-att-cell-r">
						<div class="bx-cal-view-att-cont" id="<?= $arAtt['id']?>">
							<?
							$cnt = 0;
							$bShowAll = count($arAtt['users']) <= $arAtt['countMax'];
							foreach($arAtt['users'] as $att)
							{
								$cnt++;
								if (!$bShowAll && $cnt > $arAtt['count'])
								{
									?>
									<a title="<?= htmlspecialcharsbx($att['DISPLAY_NAME'])?>" href="<?= $att['URL']?>" target="_blank" class="bxcal-att-popup-img bxcal-att-popup-img-hidden"><span class="bxcal-att-popup-avatar-outer"><span class="bxcal-att-popup-avatar"><img src="<?= $att['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" /></span></span><span class="bxcal-att-name"><?= htmlspecialcharsbx($att['DISPLAY_NAME'])?></span></a>
								<?
								}
								else // Display attendee
								{
									?>
									<a title="<?= htmlspecialcharsbx($att['DISPLAY_NAME'])?>" href="<?= $att['URL']?>" target="_blank" class="bxcal-att-popup-img"><span class="bxcal-att-popup-avatar-outer"><span class="bxcal-att-popup-avatar"><img src="<?= $att['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" /></span></span><span class="bxcal-att-name"><?= htmlspecialcharsbx($att['DISPLAY_NAME'])?></span></a>
								<?
								}
							}
							if (!$bShowAll)
							{
								?>
								<span data-bx-more-users="<?= $arAtt['id']?>" class="bxcal-more-attendees"><?= CCalendar::GetMoreAttendeesMessage(count($arAtt['users']) - $arAtt['count'])?></span>
							<?
							}?>
						</div>
					</td>
				</tr>
			<?}/*foreach($attendees as $arAtt)*/?>

			<?if (!empty($event['MEETING']['TEXT'])):?>
				<tr>
					<td class="bx-cal-view-att-cell-l" style="padding-top: 3px;"><?=GetMessage('EC_MEETING_TEXT2')?>:</td>
					<td class="bx-cal-view-att-cell-r"><pre><?= htmlspecialcharsEx($event['MEETING']['TEXT'])?></pre></td>
				</tr>
			<?endif; /*if (!empty($event['MEETING']['TEXT']))*/?>
		</table>

		<div class="bxc-confirm-row">
			<?if($curUserStatus == 'Q'): /* User still haven't take a decision*/?>
				<div id="<?=$id?>status-conf-cnt2" class="bxc-conf-cnt">
					<span data-bx-set-status="Y" class="popup-window-button popup-window-button-accept"><span class="popup-window-button-left"></span><span class="popup-window-button-text"><?= GetMessage('EC_ACCEPT_MEETING')?></span><span class="popup-window-button-right"></span></span>
					<a data-bx-set-status="N" class="bxc-decline-link" href="javascript:void(0)" title="<?= GetMessage('EC_EDEV_CONF_N_TITLE')?>" id="<?=$id?>decline-link-2"><?= GetMessage('EC_EDEV_CONF_N')?></a>
				</div>
			<?elseif($curUserStatus == 'Y' || $curUserStatus == 'H'):/* User accepts inviting */?>
				<div id="<?=$id?>status-conf-cnt1" class="bxc-conf-cnt">
					<span><?= GetMessage('EC_ACCEPTED_STATUS')?></span>
					<a data-bx-set-status="N" class="bxc-decline-link" href="javascript:void(0)" title="<?= GetMessage('EC_EDEV_CONF_N_TITLE')?>"><?= GetMessage('EC_EDEV_CONF_N')?></a>
				</div>
			<?elseif($curUserStatus == 'N'): /* User declines inviting*/ ?>
				<div class="bxc-conf-cnt">
					<span class="bxc-conf-label"><?= GetMessage('EC_DECLINE_INFO')?></span>. <a data-bx-set-status="Y" href="javascript:void(0)" title="<?= GetMessage('EC_ACCEPT_MEETING_2')?>"><?= GetMessage('EC_ACCEPT_MEETING')?></a>
				</div>
			<?elseif ($event['MEETING']['OPEN']): /* it's open meeting*/?>
				<div class="bxc-conf-cnt">
					<span class="bxc-conf-label" title="<?= GetMessage('EC_OPEN_MEETING_TITLE')?>"><?= GetMessage('EC_OPEN_MEETING')?>:</span>
					<span data-bx-set-status="Y" class="popup-window-button popup-window-button-accept" title="<?= GetMessage('EC_EDEV_CONF_Y_TITLE')?>"><span class="popup-window-button-left"></span><span class="popup-window-button-text"><?= GetMessage('EC_ACCEPT_MEETING')?></span><span class="popup-window-button-right"></span></span>
				</div>
			<?endif;?>
		</div>
	</div>

<?endif; /*$event['IS_MEETING'])*/?>
</div>
<?/* ####### END TAB 0 ####### */?>

<?/* ####### TAB 1 : ADDITIONAL ####### */?>
<div id="<?=$id?>view-tab-1-cont" class="popup-window-tab-content">
	<div class="bx-cal-view-text-additional">
		<table>
			<?if ($arParams['sectionName'] != ''):?>
				<tr>
					<td class="bx-cal-view-text-cell-l"><?=GetMessage('EC_T_CALENDAR')?>:</td>
					<td class="bx-cal-view-text-cell-r"><?= $arParams['sectionName']?></td>
				</tr>
			<?endif;?>
			<?if ($event['IMPORTANCE'] != ''):?>
				<tr>
					<td class="bx-cal-view-text-cell-l"><?=GetMessage('EC_IMPORTANCE_TITLE')?>:</td>
					<td class="bx-cal-view-text-cell-r"><?= GetMessage("EC_IMPORTANCE_".strtoupper($event['IMPORTANCE']))?></td>
				</tr>
			<?endif;?>
			<?if ($event['ACCESSIBILITY'] != '' && $arParams['bIntranet']):?>
				<tr>
					<td class="bx-cal-view-text-cell-l"><?=GetMessage('EC_ACCESSIBILITY_TITLE')?>:</td>
					<td class="bx-cal-view-text-cell-r"><?= GetMessage("EC_ACCESSIBILITY_".strtoupper($event['ACCESSIBILITY']))
						?></td>
				</tr>
			<?endif;?>
			<?if ($event['PRIVATE_EVENT'] && $arParams['bIntranet']):?>
				<tr>
					<td class="bx-cal-view-text-cell-l"><?=GetMessage('EC_EDDIV_SPECIAL_NOTES')?>:</td>
					<td class="bx-cal-view-text-cell-r"><?=GetMessage('EC_PRIVATE_EVENT')?></td>
				</tr>
			<?endif;?>
		</table>
	</div>
</div>
<?/* ####### END TAB 1 ####### */?>
</div>

<?if ($viewComments && CModule::IncludeModule("forum")):?>
	<div class="bxec-d-cont-comments-title">
		<?= GetMessage('EC_COMMENTS')?>
	</div>
	<div class="bxec-d-cont bxec-d-cont-comments" id="<?=$id?>comments-cont" style="opacity: 0;">
		<?
		if ($userId == $event['CREATED_BY'] && ($event['PARENT_ID'] == $event['ID'] || !$event['PARENT_ID']))
			$permission = "Y";
		else
			$permission = 'M';
		$set = CCalendar::GetSettings();
		$eventCommentId = $event['PARENT_ID'] ? $event['PARENT_ID'] : $event['ID'];

		// A < E < I < M < Q < U < Y
		// A - NO ACCESS, E - READ, I - ANSWER
		// M - NEW TOPIC
		// Q - MODERATE, U - EDIT, Y - FULL_ACCESS
		if ($eventCommentId > 0)
		{
			$APPLICATION->IncludeComponent("bitrix:forum.comments", "bitrix24", array(
					"FORUM_ID" => $set['forum_id'],
					"ENTITY_TYPE" => "EV", //
					"ENTITY_ID" => $eventCommentId, //Event id
					"ENTITY_XML_ID" => CCalendarEvent::GetEventCommentXmlId($event), //
					"PERMISSION" => $permission, //
					"URL_TEMPLATES_PROFILE_VIEW" => $set['path_to_user'],
					"SHOW_RATING" => "Y",
					"SHOW_LINK_TO_MESSAGE" => "N",
					"BIND_VIEWER" => "Y"
				),
				false,
				array('HIDE_ICONS' => 'Y')
			);
		}
		?>
	</div>
<?endif;?>
</div>