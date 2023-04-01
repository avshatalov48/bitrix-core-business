<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}
if (($arResult['LINK']['type'] ?? null) === 'event')
{
	$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_SHARING_ALERT_EVENT_TITLE'));
}
else
{
	$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_SHARING_ALERT_CALENDAR_TITLE'));
}
\Bitrix\Main\UI\Extension::load([
	"calendar.sharing.alert",
	"calendar.util",
	'ui.buttons',
]);
?>
<div class="calendar-sharing-alert-main" id="calendar-sharing-alert"></div>

<script>
	BX (function() {
		new BX.Calendar.Sharing.Alert({
			link: <?= CUtil::PhpToJSObject($arResult['LINK'])?>,
		});
	});
</script>