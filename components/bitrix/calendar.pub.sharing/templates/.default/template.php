<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetPageProperty('BodyClass', 'calendar-pub-body calendar-pub__state');
$APPLICATION->AddHeadString('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">');
$APPLICATION->SetTitle($arResult['PAGE_TITLE'] ?? Loc::getMessage('EC_CALENDAR_PUB_SHARING_TITLE'));
\Bitrix\Main\UI\Extension::load([
	'calendar.sharing.public-v2',
	'calendar.util',
	'ui.buttons',
	'ui.icons.b24',
	'ui.avatar-editor',
	'avatar_editor',
	'ui.design-tokens',
]);

$currentLang = $arResult['CURRENT_LANG'] ?? null;
$hasBitrix24Link = is_string($arResult['BITRIX24_LINK']);

?>
<div class="calendar-pub-body-inner">
	<div id="calendar"></div>
	<div class="calendar-pub-footer">
		<div class="calendar-pub-footer-top"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_BITRIX24_LOGO_POWERED', [
				'#CLASS#' => 'calendar-pub-footer__logo-b24 ' . ($currentLang === 'ru' ? '--ru' : ''),
			])?> <?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_FREE_SITES_AND_CRM', [
				'#TAG#' => ($hasBitrix24Link ? 'a' : 'span'),
				'#CLASS#' => 'calendar-pub-footer__link ' . (!$hasBitrix24Link ? '--no-link' : ''),
				'#HREF#' => ($hasBitrix24Link ? $arResult['BITRIX24_LINK'] : '#'),
			])?>
		</div>
		<?php if (!is_null($arResult['ABUSE_LINK'])):?>
			<div class="calendar-pub-footer-bottom">
				<?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_FOOTER_REPORT', [
					'#CLASS#' => 'calendar-pub-footer__link',
					'#HREF#' => $arResult['ABUSE_LINK'],
				])?>
			</div>
		<?php endif?>
	</div>
</div>

<?/*
<div class="calendar-pub__cookies" id="calendar-pub__cookies">
	<div class="calendar-pub__cookies-title"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_COOKIES_TITLE') ?></div>
	<div class="calendar-pub__cookies-info"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_COOKIES_TEXT') ?></div>
	<div class="calendar-pub__cookies-buttons">
		<div class="calendar-pub-ui__btn --inline --m --light-border">
			<div class="calendar-pub-ui__btn-text"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_COOKIES_DECLINE') ?></div>
		</div>
		<div class="calendar-pub-ui__btn --inline --m">
			<div class="calendar-pub-ui__btn-text"><?= Loc::getMessage('EC_CALENDAR_PUB_SHARING_COOKIES_ACCEPT') ?></div>
		</div>
	</div>
</div>
*/?>

<script>
	BX.ready(function() {

		// adjust cookies popop
		// setTimeout(()=> {
		// 	BX('calendar-pub__cookies').classList.add('--show');
		// }, 3000);
		window.history.replaceState(null, null, window.location.pathname);

		document.documentElement.lang = <?= '"' . $currentLang . '"' ?>;
		document.documentElement.setAttribute('prefix', 'og: http://ogp.me/ns#');
		document.documentElement.setAttribute('xml:lang', <?= '"' . $currentLang . '"' ?>);

		new BX.Calendar.Sharing.PublicV2({
			target: BX('calendar'),

			owner: <?= CUtil::PhpToJSObject($arResult['OWNER']) ?>,
			link: <?= CUtil::PhpToJSObject($arResult['LINK'])?>,
			timezoneList: <?= CUtil::PhpToJSObject($arResult['TIMEZONE_LIST'] ?? null) ?>,
			calendarSettings: <?= CUtil::PhpToJSObject($arResult['CALENDAR_SETTINGS'] ?? null) ?>,
			sharingUser: <?= CUtil::PhpToJSObject($arResult['SHARING_USER']) ?>,
			userAccessibility: <?= CUtil::PhpToJSObject($arResult['USER_ACCESSIBILITY'] ?? null) ?>,
			hasContactData: <?= CUtil::PhpToJSObject($arResult['HAS_CONTACT_DATA'] ?? null) ?>,
			parentLink: <?= CUtil::PhpToJSObject($arResult['PARENT_LINK'] ?? null) ?>,
			event: <?= CUtil::PhpToJSObject($arResult['EVENT'] ?? null) ?>,
			action: <?= '"' . ($arResult['ACTION'] ?? null) . '"'?>,
			currentLang: <?= '"' . $currentLang . '"' ?>,
		});
	});
</script>