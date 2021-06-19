<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

?>


<div class="calendar-pub-event-wrap calendar-pub-event--warning">
	<div class="calendar-pub-event-main">
		<div class="calendar-pub-event-head">
			<div class="calendar-pub-event-date-icon"></div>
		</div>
		<div class="calendar-pub-event-message">
			<div class="calendar-pub-event-message-icon"></div>
			<div class="calendar-pub-event-message-warning">
				<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_ALERT_TITLE_NOT_ATTENDEES') ?>
			</div>
			<div class="calendar-pub-event-message-dark">
				<?= \Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_ALERT_DESCRIPTION_NOT_ATTENDEES') ?>
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
