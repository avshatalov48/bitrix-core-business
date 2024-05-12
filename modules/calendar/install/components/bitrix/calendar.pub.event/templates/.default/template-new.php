<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arResult
 */

$APPLICATION->SetPageProperty('BodyClass', 'calendar-pub-body');
$APPLICATION->AddHeadString('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">');
$APPLICATION->SetTitle($arResult['PAGE_TITLE']);

\Bitrix\Main\UI\Extension::load([
	'calendar.public.public-event',
]);

$isRu = ($arResult['CURRENT_LANG'] ?? null) === 'ru';
$hasBitrix24Link = is_string($arResult['BITRIX24_LINK'] ?? null);
$hasEvent = is_array($arResult['EVENT'] ?? null);

?>
<div class="calendar-pub-body-inner">
	<div class="<?= $hasEvent ? 'calendar-event-container' : 'calendar-alert-container' ?>">
		<div class="calendar-pub__block" id="event"></div>
	</div>
	<div class="calendar-pub-footer">
		<div class="calendar-pub-footer-top"><?= Loc::getMessage('EC_CALENDAR_PUB_EVENT_BITRIX24_LOGO_POWERED', [
				'#CLASS#' => 'calendar-pub-footer__logo-b24 ' . ($isRu ? '--ru' : ''),
			])?> <?= Loc::getMessage('EC_CALENDAR_PUB_EVENT_FREE_SITES_AND_CRM', [
				'#TAG#' => ($hasBitrix24Link ? 'a' : 'span'),
				'#CLASS#' => 'calendar-pub-footer__link ' . (!$hasBitrix24Link ? '--no-link' : ''),
				'#HREF#' => ($hasBitrix24Link ? $arResult['BITRIX24_LINK'] : '#'),
			])?>
		</div>
	</div>
</div>

<script>
	window.history.replaceState(null, null, window.location.pathname);

	BX.ready(() => {
		new BX.Calendar.Public.PublicEvent({
			container: BX('event'),
			event: <?= \CUtil::PhpToJSObject($arResult['EVENT'] ?? null) ?>,
			isRu: <?= $isRu ? 'true' : 'false' ?>,
			action: '<?= $arResult['ACTION'] ?? '' ?>',
		}).render();
	});
</script>