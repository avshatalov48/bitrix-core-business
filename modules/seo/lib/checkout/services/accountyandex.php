<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Main\Web;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Checkout\BaseApiObject;
use Bitrix\Seo\Checkout\Response;
use Bitrix\Seo\Checkout\Service;

Loc::loadMessages(__FILE__);

/**
 * Class AccountYandex
 * @deprecated
 * @package Bitrix\Seo\Checkout\Services
 */
class AccountYandex extends BaseApiObject
{
	const TYPE_CODE = 'yandex';

	/** @var Web\Uri $callbackEventUrl */
	private $callbackEventUrl = null;

	/**
	 * Get service.
	 *
	 * @return Service
	 */
	public static function getService()
	{
		return Service::getInstance();
	}

	/**
	 * @param Web\Uri $callbackEventUrl
	 */
	public function setCallbackEventUrl(Web\Uri $callbackEventUrl)
	{
		$this->callbackEventUrl = $callbackEventUrl;
	}

	/**
	 * @return Web\Uri
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getCallbackEventUrl()
	{
		if (!$this->callbackEventUrl)
		{
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$host = $request->isHttps() ? 'https' : 'http';

			$this->callbackEventUrl = new Web\Uri($host.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php');
		}

		return $this->callbackEventUrl;
	}

	/**
	 * @param Web\Uri $url
	 * @return bool
	 */
	private function isHttps(Web\Uri $url)
	{
		return ($url->getScheme() === 'https' ? true : false);
	}

	/**
	 * @return array|Response|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getProfile()
	{
		$response = $this->getRequest()->send([
			'methodName' => 'profile.info',
			'parameters' => []
		]);

		$response = $response->getData();
		if (\is_array($response))
		{
			return [
				'ID' => $response['id'],
				'NAME' => $response['login'],
				'LINK' => '',
				'PICTURE' => '',
			];
		}

		return null;
	}

	/**
	 * Remove auth.
	 *
	 * @return void
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function removeAuth()
	{
		static::getService()->getAuthAdapter(self::TYPE_CODE)->removeAuth();
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function registerPaymentSucceededWebhook()
	{
		$callbackUrl = $this->getCallbackEventUrl();
		if (!$this->isHttps($callbackUrl))
		{
			$response = Response::create(self::TYPE_CODE);
			$response->addError(new \Bitrix\Main\Error(Loc::getMessage('SEO_CHECKOUT_SERVICE_ACCOUNT_YANDEX_ERROR_SCHEME_CALLBACK_URL')));
			return $response;
		}

		$response = $this->getRequest()->send([
			'methodName' => 'webhook.register',
			'parameters' => [
				'EVENT' => 'payment.succeeded',
				'URL' => $callbackUrl->getUri(),
			]
		]);

		return $response;
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function registerPaymentCanceledWebhook()
	{
		$callbackUrl = $this->getCallbackEventUrl();
		if (!$this->isHttps($callbackUrl))
		{
			$response = Response::create(self::TYPE_CODE);
			$response->addError(new \Bitrix\Main\Error(Loc::getMessage('SEO_CHECKOUT_SERVICE_ACCOUNT_YANDEX_ERROR_SCHEME_CALLBACK_URL')));
			return $response;
		}

		$response = $this->getRequest()->send([
			'methodName' => 'webhook.register',
			'parameters' => [
				'EVENT' => 'payment.canceled',
				'URL' => $callbackUrl->getUri(),
			]
		]);

		return $response;
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function registerRefundSucceededWebhook()
	{
		$callbackUrl = $this->getCallbackEventUrl();
		if (!$this->isHttps($callbackUrl))
		{
			$response = Response::create(self::TYPE_CODE);
			$response->addError(new \Bitrix\Main\Error(Loc::getMessage('SEO_CHECKOUT_SERVICE_ACCOUNT_YANDEX_ERROR_SCHEME_CALLBACK_URL')));
			return $response;
		}

		$response = $this->getRequest()->send([
			'methodName' => 'webhook.register',
			'parameters' => [
				'EVENT' => 'refund.succeeded',
				'URL' => $callbackUrl->getUri(),
			]
		]);

		return $response;
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function removeWebhook($id)
	{
		$response = $this->getRequest()->send([
			'methodName' => 'webhook.remove',
			'parameters' => [
				'ID' => $id,
			]
		]);

		return $response;
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getWebhookList()
	{
		$response = $this->getRequest()->send([
			'methodName' => 'webhook.list',
			'parameters' => []
		]);

		return $response;
	}

	/**
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getShopInfo()
	{
		$response = $this->getRequest()->send([
			'methodName' => 'shop.info',
			'parameters' => []
		]);

		return $response;
	}
}