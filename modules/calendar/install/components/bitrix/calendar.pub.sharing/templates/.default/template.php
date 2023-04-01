<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('EC_CALENDAR_PUB_SHARING_TITLE'));
\Bitrix\Main\UI\Extension::load([
	"calendar.sharing.public",
	"calendar.util",
	'ui.buttons',
	'ui.icons.b24',
	'ui.avatar-editor',
	'avatar_editor'
]);

?>
<div class="calendar-sharing__main" id="calendar-sharing-main"></div>

<script>
	BX (function() {
		new BX.Calendar.Sharing.Public({
			link: <?= CUtil::PhpToJSObject($arResult['LINK'])?>,
			owner: <?= CUtil::PhpToJSObject($arResult['OWNER']) ?>,
			sharingUser: <?= CUtil::PhpToJSObject($arResult['SHARING_USER']) ?>,
			userAccessibility: <?= CUtil::PhpToJSObject($arResult['USER_ACCESSIBILITY']) ?>,
			calendarSettings: <?= CUtil::PhpToJSObject($arResult['CALENDAR_SETTINGS']) ?>,
			timezoneList: <?= CUtil::PhpToJSObject($arResult['TIMEZONE_LIST']) ?>,
			welcomePageVisited: <?='"' . $arResult['WELCOME_PAGE_VISITED'] . '"'?>,
		});
	});
</script>
