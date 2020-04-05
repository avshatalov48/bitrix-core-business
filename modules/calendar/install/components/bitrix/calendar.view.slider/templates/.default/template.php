<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
global $APPLICATION, $USER_FIELD_MANAGER;

$userId = CCalendar::GetCurUserId();
$id = $arParams['id'];
$event = $arParams['event'];
$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
$toTs = CCalendar::Timestamp($event['DATE_TO']);
$skipTime = $event['DT_SKIP_TIME'] == "Y";
$meetingCreator = false;

if (empty($event))
{
	?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger ui-alert-text-center">
		<span class="ui-alert-message"><?= Loc::getMessage('EC_VIEW_SLIDER_EVENT_NOT_FOUND')?></span>
	</div>
	<?
	return;
}

if ($skipTime)
{
	$toTs += CCalendar::DAY_LENGTH;
}
else
{
	$fromTs -= $event['~USER_OFFSET_FROM'];
	$toTs -= $event['~USER_OFFSET_TO'];
}

// Timezone Hint
$timezoneHint = '';
if (
	!$skipTime &&
	(intval($event['~USER_OFFSET_FROM']) !== 0 ||
		intval($event['~USER_OFFSET_TO']) !== 0 ||
		$event['TZ_FROM'] != $event['TZ_TO'] ||
		$event['TZ_FROM'] !== CCalendar::GetUserTimezoneName($userId))
)
{
	if ($event['TZ_FROM'] == $event['TZ_TO'])
	{
		$timezoneHint = CCalendar::GetFromToHtml(CCalendar::Timestamp($event['DATE_FROM']), CCalendar::Timestamp($event['DATE_TO']), $skipTime, $event['DT_LENGTH']);
		if ($event['TZ_FROM'])
			$timezoneHint .= ' ('.$event['TZ_FROM'].')';
	}
	else
	{
		$timezoneHint = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $event['DATE_FROM'].' ('.$event['TZ_FROM'].')', '#DATE_TO#' => $event['DATE_TO'].' ('.$event['TZ_TO'].')'));
	}
}
// From - to html
$fromToHtml = CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $event['DT_LENGTH']);
$location = CCalendar::GetTextLocation($event['LOCATION']);

$UF = CCalendarEvent::GetEventUserFields($event);

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

$avatarSize = 34;
$event['REMIND'] = CCalendarEvent::GetTextReminders($event['REMIND']);

$curUserStatus = '';
$userId = CCalendar::GetCurUserId();

$viewComments = CCalendar::IsPersonal($event['CAL_TYPE'], $event['OWNER_ID'], $userId) || CCalendarSect::CanDo('calendar_view_full', $event['SECT_ID'], $userId);

if ($event['EVENT_TYPE'] === '#resourcebooking#')
{
	$viewComments = false;
}

$codes = array();
$meetingHost = false;
if ($event['IS_MEETING'])
{
	$userIndex = CCalendarEvent::getUserIndex();
	$attendees = ['y' => [], 'n' => [], 'q' => [], 'i' => []];

	if (is_array($event['ATTENDEE_LIST']))
	{
		foreach ($event['ATTENDEE_LIST'] as $attendee)
		{
			$codes[] = 'U'.intval($attendee['id']);
			$userDetails = $userIndex[$attendee['id']];

			if ($userId == $attendee["id"])
			{
				$curUserStatus = $attendee['status'];
				$viewComments = true;
			}

			$status = (strtolower($attendee['status']) == 'h' || $attendee['status'] == '') ? 'y' : $attendee['status'];
			$attendees[strtolower($status)][] = $userIndex[$attendee['id']];
			if ($attendee['status'] == 'H')
			{
				$meetingHost = $userIndex[$attendee['id']];
				$meetingHost['ID'] = $attendee['id'];
			}
		}
	}
}

if ($event['CAL_TYPE'] == 'user')
{
	$codes[] = 'U'.intval($event['OWNER_ID']);
}
else
{
	$codes[] = 'U'.intval($event['CREATED_BY']);
}

$codes = array_unique($codes);

if (!isset($meetingHost) || !$meetingHost)
{
	$meetingHost = CCalendar::GetUser($event['CREATED_BY'], true);
	$meetingHost['DISPLAY_NAME'] = CCalendar::GetUserName($meetingHost);
	$meetingHost['AVATAR'] = CCalendar::GetUserAvatarSrc($meetingHost);
	$meetingHost['URL'] = CCalendar::GetUserUrl($meetingHost["ID"], $arParams["PATH_TO_USER"]);
}

if ($event['IS_MEETING'] && $event['MEETING']['MEETING_CREATOR'] && $event['MEETING']['MEETING_CREATOR'] !== $event['MEETING_HOST'])
{
	$meetingCreator = CCalendar::GetUser($event['MEETING']['MEETING_CREATOR'], true);
	$meetingCreator['DISPLAY_NAME'] = CCalendar::GetUserName($meetingCreator);
	$meetingCreator['URL'] = CCalendar::GetUserUrl($meetingCreator["ID"], $meetingCreator["PATH_TO_USER"]);
}

$arParams['event'] = $event;
$arParams['UF'] = $UF;
?>
<div class="calendar-slider-calendar-wrap">
	<div class="calendar-slider-header">
		<div class="calendar-head-area">
			<div class="calendar-head-area-inner">
				<div class="calendar-head-area-title">
					<span id="<?= $id?>_title" class="calendar-head-area-title-name"><?= $event['NAME']?></span>
					<span id="<?= $id?>_copy_url_btn" class="calendar-page-link-btn" title="<?= Loc::getMessage('EC_VIEW_SLIDER_COPY_LINK')?>"></span>
				</div>
			</div>
		</div>
	</div>
	<div class="calendar-slider-workarea">
		<div class="calendar-slider-sidebar">
			<div id="<?= $id?>_time_wrap" class="calendar-slider-sidebar-head" <?= $timezoneHint ? 'title="'.$timezoneHint.'"' : ''?>>
				<div id="<?= $id?>_time_inner_wrap" class="calendar-slider-sidebar-head-title"><?= $fromToHtml?>
					<?if ($timezoneHint):?>
					<div class="calendar-slider-sidebar-head-timezone" title="<?= $timezoneHint?>">
						<div class="calendar-slider-sidebar-head-timezone-icon"></div>
					</div>
					<?endif;?>
				</div>
			</div>
			<div id="<?= $id?>_sidebar_inner" class="calendar-slider-sidebar-inner">
				<div class="calendar-slider-sidebar-layout calendar-slider-sidebar-user">
					<div class="calendar-slider-sidebar-layout-top calendar-slider-sidebar-user-top calendar-slider-sidebar-border-bottom">
						<div class="calendar-slider-sidebar-left-side">
							<div class="calendar-slider-sidebar-name">
								<?if ($event['IS_MEETING']):?>
									<?= Loc::getMessage('EC_VIEW_ATTENDEES_TITLE')?>
								<?else:?>
									<?= Loc::getMessage('EC_VIEW_HOST')?>
								<?endif;?>
							</div>
						</div>
						<div class="calendar-slider-sidebar-right-side" id="<?= $id?>_add_link" style="display: none;">
							<div class="calendar-slider-sidebar-property calendar-slider-sidebar-link-user">
								<?= Loc::getMessage('EC_VIEW_ATTENDEES_ADD')?>
							</div>
						</div>
					</div>
					<div class="calendar-slider-sidebar-layout-main">
						<div class="calendar-slider-sidebar-user-block">
						<?if ($event['IS_MEETING']):?>
								<div class="calendar-slider-sidebar-user-container">
									<div class="calendar-slider-sidebar-user-block-avatar">
										<a href="<?= $meetingHost['URL']?>">
											<div class="calendar-slider-sidebar-user-icon-top"></div>
											<div class="calendar-slider-sidebar-user-block-item"><img src="<?= $meetingHost['AVATAR']?>" width="<?= $avatarSize?>" height="<?= $avatarSize?>" /></div>
											<div class="calendar-slider-sidebar-user-icon-bottom"></div>
										</a>
									</div>
								</div>
								<?for($i = 0, $l = count($attendees['y']); $i < $l; $i++):?>
									<?
									$att = $attendees['y'][$i];
									if ($i > 10)
										break;
									if ($meetingHost['ID'] == $att['ID'])
										continue;
									?>
									<div class="calendar-slider-sidebar-user-container">
										<div class="calendar-slider-sidebar-user-block-avatar">
											<a href="<?= $att['URL']?>">
												<div class="calendar-slider-sidebar-user-block-item">
													<img src="<?= $att['AVATAR']?>" width="<?= $avatarSize?>" height="<?= $avatarSize?>" />
												</div>
												<div class="calendar-slider-sidebar-user-icon-bottom"></div>
											</a>
										</div>
									</div>
								<?endfor;?>

								<? if ($meetingCreator):?>
								<div class="calendar-slider-sidebar-row calendar-slider-sidebar-border-bottom">
									<div class="calendar-slider-sidebar-string-name"><?= Loc::getMessage('EC_VIEW_CREATED_BY')?>:</div>
									<div class="calendar-slider-sidebar-string-value">
										<a href="<?= $meetingCreator['URL']?>" class="calendar-slider-sidebar-user-info-name"><?= htmlspecialcharsbx($meetingCreator['DISPLAY_NAME'])?></a>
									</div>
								</div>
								<? endif;?>
						<?else:?>
							<div class="calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card">
								<div class="calendar-slider-sidebar-user-block-avatar">
									<a href="<?= $meetingHost['URL']?>">
										<div class="calendar-slider-sidebar-user-block-item"><img src="<?= $meetingHost['AVATAR']?>" width="<?= $avatarSize?>" height="<?= $avatarSize?>" /></div>
									</a>
									<div class="calendar-slider-sidebar-user-icon-bottom"></div>
								</div>
								<div class="calendar-slider-sidebar-user-info">
									<a href="<?= $meetingHost['URL']?>" class="calendar-slider-sidebar-user-info-name"><?= htmlspecialcharsbx($meetingHost['DISPLAY_NAME'])?></a>
									<?if ($meetingHost['WORK_POSITION']):?>
										<div class="calendar-slider-sidebar-user-info-status"><?= htmlspecialcharsbx($meetingHost['WORK_POSITION'])?></div>
									<?endif;?>
								</div>
							</div>
						<?endif;?>
						</div>

						<?if ($event['IS_MEETING']):?>
						<div class="calendar-slider-sidebar-user-social calendar-slider-sidebar-border-bottom">
							<div class="calendar-slider-sidebar-user-social-left">
								<div id="<?= $id?>_attendees_y" class="calendar-slider-sidebar-user-social-item">
									<span class="calendar-slider-sidebar-user-social-number">
										<?= count($attendees['y'])?>
									</span>
									<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
										<?= Loc::getMessage('EC_VIEW_STATUS_TITLE_Y')?>
									</span>
								</div>
								<div id="<?= $id?>_attendees_q" class="calendar-slider-sidebar-user-social-item">
									<span class="calendar-slider-sidebar-user-social-number">
										<?= count($attendees['q'])?>
									</span>
									<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
										<?= Loc::getMessage('EC_VIEW_STATUS_TITLE_Q')?>
									</span>
								</div>
							</div>
							<div class="calendar-slider-sidebar-user-social-right">
								<div id="<?= $id?>_attendees_i" class="calendar-slider-sidebar-user-social-item" style="visibility: hidden;">
									<span class="calendar-slider-sidebar-user-social-number">
										<?= count($attendees['i'])?>
									</span>
									<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
										<?= Loc::getMessage('EC_VIEW_STATUS_TITLE_I')?>
									</span>
								</div>
								<div id="<?= $id?>_attendees_n" class="calendar-slider-sidebar-user-social-item">
									<span class="calendar-slider-sidebar-user-social-number">
										<?= count($attendees['n'])?>
									</span>
									<span class="calendar-slider-sidebar-user-social-name calendar-slider-sidebar-color-grey-opacity">
										<?= Loc::getMessage('EC_VIEW_STATUS_TITLE_N')?>
									</span>
								</div>
							</div>
						</div>
						<?endif; /*if ($event['IS_MEETING'])*/?>
					</div>
				</div>

				<?if (is_array($event['REMIND']) && count($event['REMIND']) > 0):?>
				<div class="calendar-slider-sidebar-layout-main calendar-slider-sidebar-border-bottom calendar-slider-sidebar-remind">
					<div class="calendar-slider-sidebar-row">
						<div class="calendar-slider-sidebar-string-name"><?= Loc::getMessage('EC_VIEW_REMINDERS')?>:</div>
						<span class="calendar-slider-sidebar-remind-link calendar-slider-sidebar-string-value" id="<?= $id?>_add_reminder_link" style="display: none;">
							<span class="calendar-slider-sidebar-remind-link-name"><?= Loc::getMessage('EC_VIEW_REMINDER_ADD')?></span>
						</span>
					</div>
					<?foreach($event['REMIND'] as $remind):?>
						<div class="calendar-slider-sidebar-remind-warning">
							<span class="calendar-slider-sidebar-remind-warning-name"><?= $remind['text']?></span>
							<div class="calendar-close-button"></div>
						</div>
					<?endforeach;?>
				</div>
				<?endif;?>

				<?if ($event['RRULE']):?>
				<div class="calendar-slider-sidebar-row calendar-slider-sidebar-border-bottom">
					<div class="calendar-slider-sidebar-string-name"><?= Loc::getMessage('EC_T_REPEAT')?>:</div>
					<div class="calendar-slider-sidebar-string-value"><?= CCalendarEvent::GetRRULEDescription($event, true)?></div>
				</div>
				<?endif;?>
			</div>
			<div class="calendar-slider-sidebar-copy" style="display: none;">
				<span class="calendar-slider-sidebar-copy-link"><?= Loc::getMessage('EC_VIEW_SLIDER_COPY_LINK')?></span>
			</div>
		</div>
		<div class="calendar-slider-content">
			<div class="calendar-slider-detail calendar-slider-detail-panel">
				<div class="calendar-slider-detail-info">
					<div class="calendar-slider-detail-header">
						<?if ($event['IMPORTANCE'] == 'high'):?>
						<div id="calendar-slider-detail-important-button" class="calendar-slider-info-panel-important mutable">
							<span class="if-not-no"><?= Loc::getMessage('EC_VIEW_SLIDER_IMPORTANT_EVENT')?></span>
						</div>
						<?endif;?>
						<div class="calendar-slider-detail-subtitle-status" style="visibility: hidden">
							#calendar-slider-detail-subtitle-status#
							<span class="calendar-slider-detail-status-below-name"></span>
						</div>
					</div>

					<div class="calendar-slider-detail-content">
						<?if (!empty($event['~DESCRIPTION'])):?>
						<div id="calendar-slider-detail-description" class="calendar-slider-detail-description">
							<?= htmlspecialcharsback($event['~DESCRIPTION'])?>
						</div>
						<?endif;?>

						<?if ($event['UF_WEBDAV_CAL_EVENT']):?>
							<div class="calendar-slider-detail-files" id="<?=$id?>_<?=$event['ID']?>_files_wrap">
								<?$APPLICATION->IncludeComponent(
									"bitrix:system.field.view",
									$event['UF_WEBDAV_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
									array("arUserField" => $event['UF_WEBDAV_CAL_EVENT']),
									null,
									array("HIDE_ICONS"=>"Y")
								);?>
							</div>
						<?endif;?>

						<!--region planner-->
						<div class="calendar-slider-detail-timeline hidden" id="<?=$id?>_view_planner_wrap">
							<? if (count($codes) > 0):?>
							<div class="calendar-view-planner-wrap">
								<?
								$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
								$toTs = CCalendar::Timestamp($event['DATE_TO']);
								if ($event['DT_SKIP_TIME'] !== "Y")
								{
									$fromTs -= $event['~USER_OFFSET_FROM'];
									$toTs -= $event['~USER_OFFSET_TO'];
								}
								$event['DATE_FROM'] = CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] != 'Y');
								$event['DATE_TO'] = CCalendar::Date($toTs, $event['DT_SKIP_TIME'] != 'Y');

								if ($event['DT_SKIP_TIME'] == 'Y')
								{
									$loadedFrom = CCalendar::Date($fromTs - CCalendar::DAY_LENGTH * 5, false);
									$loadedTo = CCalendar::Date($toTs + CCalendar::DAY_LENGTH * 10, false);
								}
								else
								{
									$loadedFrom = CCalendar::Date($fromTs - CCalendar::DAY_LENGTH * 2, false);
									$loadedTo = CCalendar::Date($toTs + CCalendar::DAY_LENGTH * 5, false);
								}

								$updatePlannerParams = CCalendarPlanner::PrepareData(array(
									'user_id' => CCalendar::GetCurUserId(),
									'host_id' => $meetingHost['ID'],
									'entries' => false,
									'codes' => $codes,
									'date_from' => $loadedFrom,
									'date_to' => $loadedTo,
									'location' => $event['LOCATION'],
									'roomEventId' => 0
								));

								CCalendarPlanner::Init(
									array(
										'id' => $id.'_view_slider_planner',
										'readonly' => true,
										'useSolidBlueSelector' => true,
										'scaleLimitOffsetLeft' => 2,
										'scaleLimitOffsetRight' => 2,
										'maxTimelineSize' => 30
									),
									array(
										'show' => true,
										'config' => array(
											'scaleDateFrom' => $loadedFrom,
											'scaleDateTo' => $loadedTo,
											'changeFromFullDay' => array(
												'scaleType' => '1hour',
												'timelineCellWidth' => 40,
											),
											'entriesListWidth' => 200,
											'width' => 1300
										),
										'focusSelector' => true,
										'selector' => array(
											'focus' => true,
											'from' => $event['DATE_FROM'],
											'to' => $event['DATE_TO'],
											'fullDay' => $event['DT_SKIP_TIME'] == 'Y',
											'RRULE' => false,
											'animation' => false,
											'updateScaleLimits' => true
										),
										'loadedDataFrom' => $loadedFrom,
										'loadedDataTo' => $loadedTo,
										'data' => array(
											'entries' => $updatePlannerParams['entries'],
											'accessibility' => $updatePlannerParams['accessibility']
										)
									)
								);?>
							</div>
							<? endif;?>
						</div>
						<!--endregion-->

						<div class="calendar-slider-detail-option">
							<?if ($event['UF_CRM_CAL_EVENT']):?>
							<div class="calendar-slider-detail-option-block">
								<div class="calendar-slider-detail-option-name"><?= Loc::getMessage('EC_CRM_TITLE')?>:</div>

								<div class="calendar-slider-detail-option-value calendar-slider-detail-option-crm">
									<?$APPLICATION->IncludeComponent(
										"bitrix:system.field.view",
										$event['UF_CRM_CAL_EVENT']["USER_TYPE"]["USER_TYPE_ID"],
										array("arUserField" => $event['UF_CRM_CAL_EVENT']),
										null,
										array("HIDE_ICONS"=>"Y")
									);?>
								</div>
							</div>
							<?endif;?>

							<?if ($event['ACCESSIBILITY'] != '' && $arParams['bIntranet']):?>
								<div class="calendar-slider-detail-option-block">
									<div class="calendar-slider-detail-option-name"><?= Loc::getMessage('EC_ACCESSIBILITY_TITLE')?>:</div>
									<div class="calendar-slider-detail-option-value"><?= Loc::getMessage("EC_ACCESSIBILITY_".strtoupper($event['ACCESSIBILITY']))?></div>
								</div>
							<?endif;?>
							<?if ($arParams['sectionName'] != ''):?>
								<div class="calendar-slider-detail-option-block">
									<div class="calendar-slider-detail-option-name"><?= Loc::getMessage('EC_VIEW_SECTION')?>:</div>
									<div class="calendar-slider-detail-option-value"><?= $arParams['sectionName']?></div>
								</div>
							<?endif;?>
							<?if ($event['PRIVATE_EVENT'] && $arParams['bIntranet']):?>
								<div class="calendar-slider-detail-option-block">
									<div class="calendar-slider-detail-option-name"><?=Loc::getMessage('EC_EDDIV_SPECIAL_NOTES')?>:</div>
									<div class="calendar-slider-detail-option-value"><?=Loc::getMessage('EC_PRIVATE_EVENT')?></div>
								</div>
							<?endif;?>
						</div>

						<?if (!empty($location)):?>
						<div class="calendar-slider-detail-place">
							<div class="calendar-slider-detail-place-title"><?= Loc::getMessage('EC_VIEW_SLIDER_LOCATION')?></div>
							<div class="calendar-slider-detail-place-name"><?= htmlspecialcharsbx($location)?></div>
						</div>
						<?endif;?>
					</div>

					<div class="calendar-slider-detail-buttons">
						<div class="calendar-slider-view-buttonset calendar-slider-view-button-more-right">
							<div id="<?=$id?>_buttonset" class="calendar-slider-view-buttonset-inner">
								<input type="hidden" id="<?=$id?>_current_status" value="<?= $curUserStatus?>"/>
								<span id="<?=$id?>_status_buttonset"></span>
								<button id="<?=$id?>_but_edit" class="ui-btn ui-btn-light-border"><?= Loc::getMessage('EC_VIEW_SLIDER_EDIT')?></button>
								<button id="<?=$id?>_but_del" class="ui-btn ui-btn-link"><?= Loc::getMessage('EC_VIEW_SLIDER_DEL')?></button>

						</div>
					</div>
				</div>
			</div>
			<?if ($viewComments): ?>
			<div class="calendar-slider-comments">
				<div class="calendar-slider-comments-title"><?= Loc::getMessage('EC_VIEW_SLIDER_COMMENTS')?></div>
				<div class="calendar-slider-comments-main"  id="<?=$id?>comments-cont" style="opacity: 1;">
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
			</div>
			<?endif;?>
		</div>
	</div>
</div>