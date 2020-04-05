<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$id = $arResult['ID'];
$event = $arResult['EVENT'];
?>
<div class="feed-event-view" id="feed-event-view-cont-<?= $id?>">
	<div class="feed-calendar-view-icon">
		<a class="feed-calendar-view-icon-fake-link" id="feed-event-view-icon-link-<?= $id?>" href="#"><img src="/bitrix/images/1.gif"></a>
		<div class="feed-calendar-view-icon-day"><?= $event['FROM_WEEK_DAY']?></div>
		<div class="feed-calendar-view-icon-date"><?= $event['FROM_MONTH_DAY']?></div>
	</div>
	<div class="feed-calendar-view-text">
		<table>
			<tr>
				<td class="feed-calendar-view-text-cell-l"><?= GetMessage('ECLFV_EVENT_NAME')?>:</td>
				<td class="feed-calendar-view-text-cell-r"><a id="feed-event-view-link-<?= $id?>" href="#"><?= htmlspecialcharsex($event['NAME'])?></a></td>
			</tr>
			<tr>
				<td class="feed-calendar-view-text-cell-l"><?= GetMessage('ECLFV_EVENT_START')?>:</td>
				<td class="feed-calendar-view-text-cell-r"><span id="feed-event-view-from-<?= $id?>"></span>
				<?
				if (
					$event['DT_SKIP_TIME'] != 'Y' &&
					(intVal($event['~USER_OFFSET_FROM']) != 0 ||
					intVal($event['~USER_OFFSET_TO']) != 0 ||
					$event['TZ_FROM'] != $event['TZ_TO'])
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
					<span id="feed-event-tz-hint-<?= $id?>" data-bx-hint="<?= $timezoneHint?>" class="bx-cal-view-timezon-icon feed-event-view-timezon-icon"></span>
				<?
				}
				?>
				</td>
			</tr>

			<?if (isset($event['RRULE']) && $event['RRULE'] !== ''):?>
			<?
			$repeatHTML = '';
			$RRULE = CCalendarEvent::ParseRRULE($event['RRULE']);
			switch ($RRULE['FREQ'])
			{
				case 'DAILY':
					if ($RRULE['INTERVAL'] == 1)
						$repeatHTML = GetMessage('EC_RRULE_EVERY_DAY');
					else
						$repeatHTML = GetMessage('EC_RRULE_EVERY_DAY_1', array('#DAY#' => $RRULE['INTERVAL']));
					break;
				case 'WEEKLY':

					$daysList = array();
					foreach ($RRULE['BYDAY'] as $day)
						$daysList[] = GetMessage('EC_'.$day);
					$daysList = implode(', ', $daysList);
					if ($RRULE['INTERVAL'] == 1)
						$repeatHTML = GetMessage('EC_RRULE_EVERY_WEEK', array('#DAYS_LIST#' => $daysList));
					else
						$repeatHTML = GetMessage('EC_RRULE_EVERY_WEEK_1', array('#WEEK#' => $RRULE['INTERVAL'], '#DAYS_LIST#' => $daysList));
					break;
				case 'MONTHLY':
					if ($RRULE['INTERVAL'] == 1)
						$repeatHTML = GetMessage('EC_RRULE_EVERY_MONTH');
					else
						$repeatHTML = GetMessage('EC_RRULE_EVERY_MONTH_1', array('#MONTH#' => $RRULE['INTERVAL']));
					break;
				case 'YEARLY':
					if ($RRULE['INTERVAL'] == 1)
						$repeatHTML = GetMessage('EC_RRULE_EVERY_YEAR', array('#DAY#' => 0, '#MONTH#' => 0));
					else
						$repeatHTML = GetMessage('EC_RRULE_EVERY_YEAR_1', array('#YEAR#' => $RRULE['INTERVAL'], '#DAY#' => 0, '#MONTH#' => 0));
					break;
			}

			if($RRULE['COUNT'] > 0)
			{
				$repeatHTML .= ', '.GetMessage('EC_RRULE_COUNT', array('#COUNT#' => $RRULE['COUNT']));
			}
			elseif ($RRULE['UNTIL'] != '' && $RRULE['UNTIL'] != CCalendar::GetMaxDate())
			{
				$repeatHTML .= '<br>'.GetMessage('EC_RRULE_UNTIL', array('#UNTIL_DATE#' => CCalendar::Date(CCalendar::Timestamp($RRULE['UNTIL']), false)));
			}
			?>
			<tr>
				<td class="feed-calendar-view-text-cell-l"><?=GetMessage('EC_T_REPEAT')?>:</td>
				<td class="feed-calendar-view-text-cell-r"><?= $repeatHTML?></td>
			</tr>
			<?endif;/*if ($event['RRULE'] !== '')*/?>


			<?if (!empty($event['LOCATION'])):?>
			<tr>
				<td class="feed-calendar-view-text-cell-l"><?= GetMessage('ECLFV_EVENT_LOCATION')?>:</td>
				<td class="feed-calendar-view-text-cell-r"><?= htmlspecialcharsex($event['LOCATION'])?></td>
			</tr>
			<?endif;?>

			<tr id="feed-event-accepted-row-<?= $id?>" style="<?if (count($event['ACCEPTED_ATTENDEES']) == 0){echo "display:none;";}?>">
				<td class="feed-calendar-view-text-cell-l"><?= GetMessage('ECLFV_EVENT_ATTENDEES')?>:</td>
				<td class="feed-calendar-view-text-cell-r">
					<? if (count($event['ACCEPTED_ATTENDEES']) > 0):?>
					<?
					$cnt = 0;
					$bShowAll = count($event['ACCEPTED_ATTENDEES']) <= $arParams['ATTENDEES_SHOWN_COUNT_MAX'];
					$popupContent = '';
					foreach($event['ACCEPTED_ATTENDEES'] as $att)
					{
						$cnt++;
						if (!$bShowAll && $cnt > $arParams['ATTENDEES_SHOWN_COUNT'])
						{
							// Put to popup
							$popupContent .= '<a href="'.$att['URL'].'" target="_blank" class="bxcal-att-popup-img bxcal-att-popup-att-full">'.
								'<span class="bxcal-att-popup-avatar">'.
									($att['AVATAR_SRC'] ? '<img src="'.$att['AVATAR_SRC'].'" width="'.$arParams['AVATAR_SIZE'].'" height="'.$arParams['AVATAR_SIZE'].'" class="bxcal-att-popup-img-not-empty" />' : '').
								'</span>'.
								'<span class="bxcal-att-popup-name">'.htmlspecialcharsbx($att['DISPLAY_NAME']).'</span>'.
							'</a>';
						}
						else // Display avatar
						{
							?><a title="<?= htmlspecialcharsbx($att['DISPLAY_NAME'])?>" href="<?= $att['URL']?>" target="_blank" class="bxcal-att-popup-img"><?
								?><span class="bxcal-att-popup-avatar"><?
									if ($att['AVATAR_SRC'])
									{
										?><img src="<?= $att['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" class="bxcal-att-popup-img-not-empty" /><?
									}
								?></span><?
							?></a><?
						}
					}?>

					<?if (!$bShowAll):?>
						<span id="feed-event-more-att-link-y-<?= $id?>" class="bxcal-more-attendees"><?= CCalendar::GetMoreAttendeesMessage(count($event['ACCEPTED_ATTENDEES']) - $arParams['ATTENDEES_SHOWN_COUNT'])?></span>
						<div id="feed-event-more-attendees-y-<?= $id?>" class="bxcal-more-attendees-popup" style="display: none;">
							<?= $popupContent?>
						</div>
					<?endif;?>
					<?endif; /*if (count($event['ACCEPTED_ATTENDEES']) > 0)*/?>
				</td>
			</tr>

			<tr id="feed-event-declined-row-<?= $id?>" style="<? if (count($event['DECLINED_ATTENDEES']) == 0){echo "display:none;";}?>">
				<td class="feed-calendar-view-text-cell-l"><?= GetMessage('ECLFV_EVENT_ATTENDEES_DES')?>:</td>
				<td class="feed-calendar-view-text-cell-r">
					<? if (count($event['DECLINED_ATTENDEES']) > 0):?>
						<?
						$cnt = 0;
						$bShowAll = count($event['DECLINED_ATTENDEES']) <= $arParams['ATTENDEES_SHOWN_COUNT_MAX'];
						$popupContent = '';
						foreach($event['DECLINED_ATTENDEES'] as $att)
						{
							$cnt++;
							if (!$bShowAll && $cnt > $arParams['ATTENDEES_SHOWN_COUNT'])
							{
								// Put to popup
								$popupContent .= '<a href="'.$att['URL'].'" target="_blank" class="bxcal-att-popup-img bxcal-att-popup-att-full">'.
									'<span class="bxcal-att-popup-avatar">'.
										($att['AVATAR_SRC'] ? ('<img src="'.$att['AVATAR_SRC'].'" width="'.$arParams['AVATAR_SIZE'].'" height="'.$arParams['AVATAR_SIZE'].'" class="bxcal-att-popup-img-not-empty" />') : '').
									'</span>'.
									'<span class="bxcal-att-popup-name">'.htmlspecialcharsbx($att['DISPLAY_NAME']).'</span>'.
								'</a>';
							}
							else // Display avatar
							{
								?><a title="<?= htmlspecialcharsbx($att['DISPLAY_NAME'])?>" href="<?= $att['URL']?>" target="_blank" class="bxcal-att-popup-img"><?
									?><span class="bxcal-att-popup-avatar"><?
										if($att['AVATAR_SRC'])
										{
											?><img src="<?= $att['AVATAR_SRC']?>" width="<?= $arParams['AVATAR_SIZE']?>" height="<?= $arParams['AVATAR_SIZE']?>" class="bxcal-att-popup-img-not-empty" /><?
										}
									?></span><?
								?></a><?
							}
						}?>

						<?if (!$bShowAll):?>
							<span id="feed-event-more-att-link-n-<?= $id?>" class="bxcal-more-attendees"><?= CCalendar::GetMoreAttendeesMessage(count($event['DECLINED_ATTENDEES']) - $arParams['ATTENDEES_SHOWN_COUNT'])?></span>
							<div id="feed-event-more-attendees-n-<?= $id?>" class="bxcal-more-attendees-popup" style="display: none;">
								<?= $popupContent?>
							</div>
						<?endif;?>
					<?endif;/*if (count($event['DECLINED_ATTENDEES']) > 0)*/?>
				</td>
			</tr>
		</table>
	</div>

	<?if ($event['DESCRIPTION'] != ""):?>
	<div class="feed-calendar-view-description">
		<div class="feed-cal-view-desc-title"><?= GetMessage('ECLFV_DESCRIPTION')?>:</div>
		<?= $event['~DESCRIPTION']?>
	</div>
	<?endif;?>
</div>

<script>
	if (!window.oViewEventManager)
		window.oViewEventManager = {};
	window.oViewEventManager[('<?= $event['ID']?>' || 0)] = new window.ViewEventManager(<?=CUtil::PhpToJSObject(
	array(
		"id" => $id,
		"eventId" => $event['ID'],
		"EVENT" => $event,
		"attendees" => $arResult['ATTENDEES_INDEX'],
		"actionUrl" => $arParams['ACTION_URL'],
		"viewEventUrlTemplate" => $arParams['EVENT_TEMPLATE_URL'],
		"EC_JS_DEL_EVENT_CONFIRM" => GetMessageJS('EC_JS_DEL_EVENT_CONFIRM'),
		'ATTENDEES_SHOWN_COUNT' => $arParams['ATTENDEES_SHOWN_COUNT'],
		'ATTENDEES_SHOWN_COUNT_MAX' => $arParams['ATTENDEES_SHOWN_COUNT_MAX'],
		'AVATAR_SIZE' => $arParams['AVATAR_SIZE'],
		"AJAX_PARAMS" => array(
			'PATH_TO_USER' => $arParams['PATH_TO_USER'],
			'ATTENDEES_SHOWN_COUNT' => $arParams['ATTENDEES_SHOWN_COUNT'],
			'ATTENDEES_SHOWN_COUNT_MAX' => $arParams['ATTENDEES_SHOWN_COUNT_MAX'],
		)
	));?>
	);
</script>

<?/* Don't delete or change html comments below. It used to display results */?>
<!--#BX_FEED_EVENT_FOOTER_MESSAGE#-->
<?if ($arResult['UF_WEBDAV_CAL_EVENT']):?>
<div id="bx-feed-cal-view-files-<?=$id?>" class="feed-cal-view-uf-block">
<?$APPLICATION->IncludeComponent(
	"bitrix:system.field.view",
	$arResult['UF_WEBDAV_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
	array("arUserField" => $arResult['UF_WEBDAV_CAL_EVENT']),
	null,
	array("HIDE_ICONS"=>"Y")
);
?>
</div>
<?endif;?>

<?if ($arResult['UF_CRM_CAL_EVENT']):?>
<div class="feed-cal-view-uf-block">
	<div class="feed-cal-view-uf-block-title"><?= GetMessage('ECLFV_CRM')?>:</div>
	<div>
	<?$APPLICATION->IncludeComponent(
		"bitrix:system.field.view",
		$arResult['UF_CRM_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
		array("arUserField" => $arResult['UF_CRM_CAL_EVENT']),
		null,
		array("HIDE_ICONS"=>"Y")
	);
	?>
	</div>
</div>
<?endif;?>

<div id="feed-event-invite-controls-<?= $id?>" class="feed-cal-view-inv-controls">
	<div class="feed-calendar-view-invite-cont">
		<span class="webform-small-button webform-small-button-accept" id="feed-event-accept-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_INVITE_ACCEPT')?></span><span class="webform-small-button-right"></span></span>

		<span class="webform-small-button" id="feed-event-decline-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_INVITE_DECLINE')?></span><span class="webform-small-button-right"></span></span>
	</div>

	<div class="feed-event-att-status feed-event-att-status-accepted" id="feed-event-stat-link-y-<?= $id?>">
		<?= GetMessage('ECLFV_EVENT_ACCEPTED')?>
	</div>
	<div class="feed-event-att-status feed-event-att-status-declined" id="feed-event-stat-link-n-<?= $id?>">
		<?= GetMessage('ECLFV_EVENT_DECLINED')?>
	</div>

	<div id="feed-event-stat-link-popup-y-<?= $id?>" class="feed-event-status-popup">
		<span class="webform-small-button" id="feed-event-decline-2-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_INVITE_DECLINE2')?></span><span class="webform-small-button-right"></span></span>

		<div id="feed-rec-decline-<?= $id?>" class="feed-event-rec-decline-popup">
			<div style="margin-bottom: 8px;"><?= GetMessage('ECLFV_REC_DECLINE')?></div>
			<span class="webform-small-button" id="feed-rec-decline-this-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_REC_DECLINE_THIS')?></span><span class="webform-small-button-right"></span></span>
			<span class="webform-small-button" id="feed-rec-decline-next-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_REC_DECLINE_NEXT')?></span><span class="webform-small-button-right"></span></span>
			<span class="webform-small-button" id="feed-rec-decline-all-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_REC_DECLINE_ALL')?></span><span class="webform-small-button-right"></span></span>
		</div>
	</div>

	<div id="feed-event-stat-link-popup-n-<?= $id?>" class="feed-event-status-popup">
		<span class="webform-small-button webform-small-button-accept" id="feed-event-accept-2-<?= $id?>"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?= GetMessage('ECLFV_INVITE_ACCEPT2')?></span><span class="webform-small-button-right"></span></span>
	</div>
</div>
<!--#BX_FEED_EVENT_FOOTER_MESSAGE_END#-->