<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$marketExpiredPopup = \Bitrix\Rest\Notification\MarketExpiredPopup::createByDefault();

return [
	'css' => 'dist/market-expired.bundle.css',
	'js' => 'dist/market-expired.bundle.js',
	'rel' => [
		'main.popup',
		'ui.buttons',
		'main.core.events',
		'ui.info-helper',
		'ui.notification',
		'ui.analytics',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'type' => $marketExpiredPopup->getCurrentType(),
		'olWidgetCode' => $marketExpiredPopup->getOpenLinesWidgetCode(),
		'transitionPeriodEndDate' => $marketExpiredPopup->getFormattedTransitionPeriodEndDate(),
		'marketSubscriptionUrl' => $marketExpiredPopup->marketSubscription->getBuyUrl(),
		'withDiscount' => $marketExpiredPopup->marketSubscription->isDiscountAvailable(),
		'withDemo' => $marketExpiredPopup->marketSubscription->isDemoAvailable(),
	]
];
