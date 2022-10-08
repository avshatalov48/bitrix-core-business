<?php
namespace PHPSTORM_META
{
	registerArgumentsSet('bitrix_seo_serviceLocator_codes',
		'seo.leadads.service',
		'seo.business.service',
		'seo.business.adapter',
		'seo.business.conversion',
		'seo.catalog.webhook.handler',
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_seo_serviceLocator_codes'));

	override(\Bitrix\Main\DI\ServiceLocator::get(0), map([
		'seo.leadads.service' => \Bitrix\Seo\LeadAds\Service::class,
		'seo.business.service' => \Bitrix\Seo\BusinessSuite\Service::class,
		'seo.business.adapter' => \Bitrix\Seo\BusinessSuite\ServiceAdapter::class,
		'seo.business.conversion' => \Bitrix\Seo\Conversion\Facebook\Conversion::class,
		'seo.catalog.webhook.handler' => \Bitrix\Seo\Catalog\CatalogWebhookHandler::class,
	]));

	exitPoint(\Bitrix\Seo\Catalog\CatalogWebhookHandler::handle());
}
