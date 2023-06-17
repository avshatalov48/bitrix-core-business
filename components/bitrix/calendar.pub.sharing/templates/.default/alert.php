<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__DIR__ . "/template.php");

$APPLICATION->SetPageProperty('BodyClass', 'calendar-pub-body calendar-pub__state');
$APPLICATION->SetTitle(Loc::getMessage('EC_CALENDAR_PUB_SHARING_ALERT_CALENDAR_TITLE'));
\Bitrix\Main\UI\Extension::load([
	"calendar.sharing.alert",
	"calendar.util",
	'ui.buttons',
]);

$currentLang = $arResult['CURRENT_LANG'] ?? null;

?>
<div class="calendar-pub-body-inner">
	<div id="calendar-sharing-alert" style="display: flex; flex: 1;"></div>
	<div class="calendar-pub-footer">
		<div class="calendar-pub-footer-top"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_BITRIX24_LOGO_POWERED', [
				'#CLASS#' => 'calendar-pub-footer__logo-b24 ' . ($currentLang === 'ru' ? '--ru' : ''),
			])?> <?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_FREE_SITES_AND_CRM', [
				'#TAG#' => 'span',
				'#CLASS#' => 'calendar-pub-footer__link --no-link',
				'#HREF#' => '#',
			])?>
		</div>
	</div>
</div>


<script>
	BX (function() {
		new BX.Calendar.Sharing.Alert({});
	});
</script>