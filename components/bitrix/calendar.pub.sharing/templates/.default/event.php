<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_SHARING_EVENT_TITLE'));
\Bitrix\Main\UI\Extension::load([
	"calendar.sharing.publicevent",
	"ui.dialogs.messagebox",
	'ui.buttons',
	'main.popup',
	'ui.icons.b24',
	'ui.avatar-editor',
	'avatar_editor'
]);

?>

<div class="calendar-sharing-event-main" id="calendar-sharing-event-main"></div>

<script>
	BX (function() {
		/*
		line below removes action parameter from url
		this is needed to avoid repeating the action if user will refresh the page
		 */
		window.history.replaceState(null, null, window.location.pathname);

		new BX.Calendar.Sharing.PublicEvent({
			link: <?= CUtil::PhpToJSObject($arResult['LINK']) ?>,
			event: <?= CUtil::PhpToJSObject($arResult['EVENT']) ?>,
			owner: <?= CUtil::PhpToJSObject($arResult['OWNER']) ?>,
			ownerMeetingStatus: <?= CUtil::PhpToJSObject($arResult['OWNER_MEETING_STATUS']) ?>,
			action: <?= '"' . $arResult['ACTION'] . '"'?>,
		});
	});
</script>
