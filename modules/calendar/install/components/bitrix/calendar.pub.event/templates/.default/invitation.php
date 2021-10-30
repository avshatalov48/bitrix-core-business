<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(["ui.buttons"]);
//$APPLICATION->AddHeadString('<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">');
?>

<div class="calendar-pub-event-wrap
	<?= $arResult['IS_SHOW_LIST_BOX'] ? 'calendar-pub-event--decision' : '' ?>
	<?= $arResult['IS_POSITIVE_DECISION'] ? 'calendar-pub-event--accept ' : 'calendar-pub-event--decline '?>">
	<div class="calendar-pub-event-main">

		<!--event name block-->
		<div class="calendar-pub-event-head">
			<div class="calendar-pub-event-head-left">
				<div class="calendar-pub-event-date-box">
					<div class="calendar-pub-event-date">
						<div class="calendar-pub-event-date-head">
							<div class="calendar-pub-event-date-head-rect">
								<div class="calendar-pub-event-date-head-tree"></div>
								<div class="calendar-pub-event-date-head-circle"></div>
							</div>
							<div class="calendar-pub-event-date-head-rect">
								<div class="calendar-pub-event-date-head-tree"></div>
								<div class="calendar-pub-event-date-head-circle"></div>
							</div>
							<div class="calendar-pub-event-date-month">
								<?= $arResult['SHORT_NAME_MONTH'] ?>
							</div>
						</div>
						<div class="calendar-pub-event-date-num">
							<?= $arResult['DATE_FROM_NUMBER'] ?>
						</div>
					</div>
				</div>
			</div>
			<div id="decisionBlock" class="<?= $arResult['IS_SHOW_LIST_BOX'] ? 'calendar-pub-event-head-right' : 'calendar-pub-event-head-center' ?>">
				<h2 class="calendar-pub-event-title-main">
					<?= $arResult['NAME'] ?>
				</h2>
				<div class="calendar-pub-event-desc">
					<?=
						$arResult['HAS_DECISION']
							? $arResult['IS_POSITIVE_DECISION']
								? \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_DECISION_YES')
								: \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_DECISION_NO')
							: ''
					?>
				</div>
			</div>
		</div>

		<!--list box-->
		<?php if ($arResult['IS_SHOW_LIST_BOX']): ?>
			<div class="calendar-pub-event-user-list-box">
				<div class="calendar-pub-event-user-list-inner">
					<?php if ($arResult['IS_SHOW_ATTENDEES_BOX']): ?>
						<div class="calendar-pub-event-user-list-members calendar-pub-event-list-box-item">
							<div class="calendar-pub-event-user-list-title">
								<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_ATTENDEES_TITLE')?>
							</div>
							<div class="calendar-pub-event-user-list-content">
								<?php
									/** @var \Bitrix\Calendar\ICal\Builder\Attendee $attendee */
									foreach($arResult['ATTENDEES_COLLECTION'] as $attendee):
								?>
									<div class="calendar-pub-event-user-list-item <?= $component->getStyleClassAttendeeStatus($attendee->getStatus()) ?>">
										<?= $attendee->getFullName() ?>
									</div>
								<?php endforeach ?>
							</div>

							<?php if ($arResult['ATTENDEES_COUNT'] > 3): ?>
								<div data-button="users" class="calendar-pub-event-user-list-btn">
									<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_ALL_ATTENDEES_TITLE') ?>
									<span>
										(<?= $arResult['ATTENDEES_COUNT']?>)
									</span>
								</div>
							<?php endif ?>
						</div>
					<?php endif ?>
					<?php if ($arResult['IS_SHOW_DESCRIPTION_BOX']): ?>
						<div class="calendar-pub-event-user-list-desc calendar-pub-event-list-box-item">
							<div class="calendar-pub-event-user-list-title">
								<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_DESCRIPTION_TITLE') ?>
							</div>
							<div class="calendar-pub-event-user-list-content">
								<?= $arResult['EVENT_DESCRIPTION'] ?>
							</div>
						</div>
					<?php endif ?>
					<?php if ($arResult['IS_SHOW_LOCATION_BOX']): ?>
						<div class="calendar-pub-event-user-list-location calendar-pub-event-list-box-item">
							<div class="calendar-pub-event-user-list-title">
								<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_LOCATION_TITLE') ?>
							</div>
							<div class="calendar-pub-event-location-content">
								<?= $arResult['EVENT_LOCATION'] ?>
							</div>
						</div>
					<?php endif?>
					<?php if ($arResult['IS_SHOW_ATTACHMENTS_BOX']): ?>
						<div class="calendar-pub-event-attachment-list-members calendar-pub-event-list-box-item">
							<div class="calendar-pub-event-attachment-list-title">
								<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_ATTACHMENTS_TITLE') ?>
							</div>
							<div class="calendar-pub-event-user-attachment-content">
								<?php foreach($arResult['ATTACHMENTS_COLLECTION'] as $attachment): ?>
								<div class="calendar-pub-event-attachment-list-item">
									<div class="calendar-pub-event-attachment-file-box">
										<span class="calendar-pub-event-attachment-file-name-box">
											<a class="calendar-pub-event-attachment-file-name" href="<?= $attachment->getLink() ?>">
												<?= $attachment->getName() ?>
											</a>
											<span class="calendar-pub-event-attachment-file-size">
												<?= $attachment->getFormatSize() ?>
											</span>
										</span>

									</div>
								</div>
								<?php endforeach ?>
							</div>
							<?php if ($arResult['ATTACHMENTS_COLLECTION']->getCount() > 3): ?>
								<div data-button="files" class="calendar-pub-event-user-attachment-btn">
									<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_ALL_ATTACHMENTS_TITLE') ?>
									<span>
										(<?= $arResult['ATTACHMENTS_COLLECTION']->getCount() ?>)
									</span>
								</div>
							<?php endif ?>
						</div>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>

		<!--datetime block-->
		<div class="calendar-pub-event-info">
			<?php if ($arResult['FULL_DAY']): ?>
				<?php if ($arResult['IS_LONG_DATETIME_FORMAT']): ?>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_FROM'] . " -" ?>
					</p>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_TO'] ?>
					</p>
				<?php else: ?>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_FROM'] ?>
					</p>
				<?php endif ?>
			<?php else: ?>
				<?php if ($arResult['IS_LONG_DATETIME_FORMAT']): ?>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_FROM'] . ' ' . $arResult['TIME_FROM'] . ' -' ?>
					</p>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_TO'] . ' ' . $arResult['TIME_TO'] ?>
					</p>
				<?php else: ?>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['DATE_FROM'] ?>
					</p>
					<p class="calendar-pub-event-info-text">
						<?= $arResult['TIME_FROM'] . ' - ' . $arResult['TIME_TO'] ?>
					</p>
				<?php endif ?>
				<?php if ($arResult['IS_SHOW_RRULE']): ?>
					<p class="calendar-pub-event-info-text calendar-pub-event-info-text--sm">
						<?= $arResult['RRULE'] ?>
					</p>
				<?php endif ?>
				<!--offset box-->
				<div class="calendar-pub-event-info-time">
					UTC
					<?php if ($arResult['IS_SHOW_TIME_OFFSET']): ?>
						<?= $arResult['OFFSET_FROM'] . ' ' . $arResult['TIMEZONE_NAME_FROM'] ?>
					<?php endif ?>
				</div>
			<?php endif ?>
		</div>

		<!--decision buttons wrapper block-->
		<div id="buttonsContainer" class="calendar-pub-event-btn-container">
			<div style="align-self: center; margin-bottom: 19px;">
			</div>
			<div style="align-self: center; margin-bottom: -12px;">
			</div>
		</div>

	</div>

	<div class="calendar-pub-event-footer">
		<div class="calendar-pub-event-footer-logo"></div>
		<div class="calendar-pub-event-footer-desc">
			<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_BITRIX24_SLOGAN') ?>
		</div>
	</div>
</div>


<script>
	BX( function() {
		new BX.Calendar.Pub.CalendarEvent({
			eventId: <?= $component->getEventId() ?>,
			hasDecision: <?= CUtil::PhpToJSObject((bool)$arResult['HAS_DECISION']) ?>,
			isPositiveDecision: <?= CUtil::PhpToJSObject((bool)$arResult['IS_POSITIVE_DECISION']) ?>,
			hash: <?= CUtil::PhpToJSObject($arResult['HASH']) ?>,
			downloadLink: <?= CUtil::PhpToJSObject($arResult['DOWNLOAD_INVITATION_LINK']) ?>,
		});
	});

	BX.Loc.setMessage(<?=CUtil::PhpToJSObject([
		'EC_CALENDAR_CHANGE_DECISION_TITLE' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_CHANGE_DECISION_TITLE'),
		'EC_CALENDAR_PUB_EVENT_DECISION_YES' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_DECISION_YES'),
		'EC_CALENDAR_PUB_EVENT_DECISION_NO' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_DECISION_NO'),
		'EC_CALENDAR_DECISION_TITLE_YES' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_DECISION_TITLE_YES'),
		'EC_CALENDAR_DECISION_TITLE_NO' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_DECISION_TITLE_NO'),
		'EC_CALENDAR_ICAL_INVITATION_DOWNLOAD_INVITATION' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_ICAL_INVITATION_DOWNLOAD_INVITATION'),
		'EC_CALENDAR_PUB_EVENT_ALL_ATTENDEES_TITLE' => \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_EVENT_ALL_ATTENDEES_TITLE'),
	])?>);
</script>
