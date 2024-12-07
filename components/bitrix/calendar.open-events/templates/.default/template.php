<?php

use Bitrix\Calendar\OpenEvents\Component\Toolbar;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

if(
	!Bitrix\Main\Loader::includeModule('calendar')
	|| !Bitrix\Calendar\OpenEvents\Feature::getInstance()->isAvailable()
)
{
	ShowError(GetMessage('CALENDAR_OPEN_EVENTS_NOT_ALLOWED'));
	return;
}

global $APPLICATION;

$bodyClass = $APPLICATION->getPageProperty('BodyClass') || '';
$APPLICATION->SetPageProperty(
	'BodyClass',
	$bodyClass . ' no-all-paddings display-absolute top-menu-mode calendar-open-events__pagetitle-view',
);

$APPLICATION->SetTitle(Loc::getMessage('CALENDAR_OPEN_EVENTS'));

(new Toolbar())->build();

Extension::load('calendar.open-events.list');

/** @var array $arResult */

?>

<div id="calendar-open-events" class="calendar-open-events"></div>

<script>
	BX.ready(() => {
		const container = document.getElementById('calendar-open-events');
		const filterId = '<?= $arResult['FILTER_ID'] ?>';

		new BX.Calendar.OpenEvents.List({
			container,
			filterId,
		});
	});
</script>
