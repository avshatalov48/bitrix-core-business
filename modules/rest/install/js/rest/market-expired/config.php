<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$marketExpiredNotification = \Bitrix\Rest\Notification\MarketExpired\MarketExpiredNotification::createByDefault();

return [
	'css' => 'dist/market-expired.bundle.css',
	'js' => 'dist/market-expired.bundle.js',
	'rel' => [
		'main.popup',
		'main.polyfill.intersectionobserver',
		'main.core.events',
		'ui.notification',
		'ui.info-helper',
		'ui.banner-dispatcher',
		'ui.notification-panel',
		'ui.icon-set.api.core',
		'ui.analytics',
		'ui.icon-set.main',
		'ui.buttons',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'type' => $marketExpiredNotification->getType(),
		'category' => $marketExpiredNotification->getCategory(),
		'expireDate' => $marketExpiredNotification->getFormattedEndDate(),
		'expireDays' => $marketExpiredNotification->getFormattedDaysLeft(),
		'marketSubscriptionUrl' => $marketExpiredNotification->marketSubscription->getBuyUrl(),
		'withDemo' => $marketExpiredNotification->marketSubscription->isDemoAvailable(),
		'discount' => $marketExpiredNotification->marketSubscription->getDiscount()->toArray(),
		'olWidgetCode' => $marketExpiredNotification->getOpenLinesWidgetCode(),
		'isRenamedMarket' => \Bitrix\Rest\Integration\Market\Label::isRenamedMarket(),
	]
];
