<?php

namespace Bitrix\Sale\Controller\Action\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Seo;

/**
 * Class RegisterYookassaWebhookAction
 * @package Bitrix\Sale\Controller\Action\PaySystem
 * @example BX.ajax.runAction("sale.paysystem.entity.registerYookassaWebhook");
 * @internal
 */
class RegisterYookassaWebhookAction extends Sale\Controller\Action\BaseAction
{
	public function run()
	{
		if (!Main\Loader::includeModule('seo'))
		{
			$this->addError(new Main\Error('Module seo not installed'));
			return;
		}

		$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YOOKASSA);
		$hasAuth = $authAdapter->hasAuth();
		if ($hasAuth)
		{
			Main\Config\Option::set('sale', 'YANDEX_CHECKOUT_OAUTH', true);

			$oauthService = Seo\Checkout\Services\Factory::createService($authAdapter->getType());
			$this->registerWebhooks($oauthService);
		}
		else
		{
			$this->addError(new Main\Error('Yokassa is not authorized'));
		}
	}

	private function registerWebhooks(Seo\Checkout\Services\AccountYookassa $oauthService): void
	{
		$registerPaymentSucceededResult = $oauthService->registerPaymentSucceededWebhook();
		$registerPaymentCanceledWebhookResult = $oauthService->registerPaymentCanceledWebhook();
		if ($registerPaymentSucceededResult->isSuccess() && $registerPaymentCanceledWebhookResult->isSuccess())
		{
			Main\Config\Option::set('sale', 'YANDEX_CHECKOUT_OAUTH_WEBHOOK_REGISTER', true);
		}

		if (!$registerPaymentSucceededResult->isSuccess())
		{
			$this->addErrors($registerPaymentSucceededResult->getErrors());
		}
		if (!$registerPaymentCanceledWebhookResult->isSuccess())
		{
			$this->addErrors(($registerPaymentCanceledWebhookResult->getErrors()));
		}
	}
}