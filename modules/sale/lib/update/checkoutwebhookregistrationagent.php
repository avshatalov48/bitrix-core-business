<?php

namespace Bitrix\Sale\Update;

use Bitrix\Main;
use Bitrix\Seo;

class CheckoutWebhookRegistrationAgent
{
	public static function exec()
	{
		if (!Main\Loader::includeModule('seo'))
		{
			return;
		}

		if (!Main\Loader::includeModule('socialservices'))
		{
			return;
		}

		if (self::isYandexOauth())
		{
			self::registerWebhook(Seo\Checkout\Service::TYPE_YANDEX);
		}
		elseif (self::isYookassaOauth())
		{
			self::registerWebhook(Seo\Checkout\Service::TYPE_YOOKASSA);
		}
	}

	private static function isYandexOauth(): bool
	{
		$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YANDEX);
		return $authAdapter->hasAuth();
	}

	private static function isYookassaOauth(): bool
	{
		$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YOOKASSA);
		return $authAdapter->hasAuth();
	}

	private static function registerWebhook(string $type): void
	{
		$authAdapter = Seo\Checkout\Service::getAuthAdapter($type);
		$oauthService = Seo\Checkout\Services\Factory::createService($authAdapter->getType());

		$registerPaymentSucceededResult = $oauthService->registerPaymentSucceededWebhook();
		$registerPaymentCanceledWebhookResult = $oauthService->registerPaymentCanceledWebhook();
		if ($registerPaymentSucceededResult->isSuccess() && $registerPaymentCanceledWebhookResult->isSuccess())
		{
			Main\Config\Option::set('sale', 'YANDEX_CHECKOUT_OAUTH_WEBHOOK_REGISTER', true);
		}
	}
}