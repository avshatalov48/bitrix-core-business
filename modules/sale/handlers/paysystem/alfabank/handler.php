<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;

PaySystem\Manager::includeHandler('SberbankOnline');

/**
 * Class AlfaBankHandler
 * @package Sale\Handlers\PaySystem
 */
class AlfaBankHandler extends SberbankOnlineHandler
{
	/**
	 * @return string[]
	 */
	protected static function getDescriptionCodesMap(): array
	{
		return [
			'LOGIN' => 'ALFABANK_LOGIN',
			'PASSWORD' => 'ALFABANK_PASSWORD',
			'MERCHANT' => 'ALFABANK_MERCHANT',
			'SECRET_KEY' => 'ALFABANK_SECRET_KEY',
			'RETURN_SUCCESS_URL' => 'ALFABANK_RETURN_SUCCESS_URL',
			'RETURN_FAIL_URL' => 'ALFABANK_RETURN_FAIL_URL',
			'ORDER_DESCRIPTION' => 'ALFABANK_ORDER_DESCRIPTION',
			'TEST_MODE' => 'ALFABANK_TEST_MODE',
		];
	}

	/**
	 * @return array
	 */
	public function getCurrencyList(): array
	{
		return ['BYN'];
	}

	/**
	 * @param Payment|null $payment
	 * @param string $action
	 * @return string
	 */
	protected function getUrl(Payment $payment = null, $action): string
	{
		$url = parent::getUrl($payment, $action);
		if ($payment !== null && $action === 'formUrl')
		{
			$url = str_replace('#merchant#', $this->getBusinessValue($payment, static::getDescriptionCode('MERCHANT')), $url);
		}

		return $url;
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		$testUrl = 'https://web.rbsuat.com/ab_by/';
		$activeUrl = 'https://ecom.alfabank.by/payment/';

		return [
			'register.do' => [
				self::TEST_URL => $testUrl.'rest/register.do',
				self::ACTIVE_URL => $activeUrl.'rest/register.do',
			],
			'getOrderStatusExtended.do' => [
				self::TEST_URL => $testUrl.'rest/getOrderStatusExtended.do',
				self::ACTIVE_URL => $activeUrl.'rest/getOrderStatusExtended.do',
			],
			'refund.do' => [
				self::TEST_URL => $testUrl.'rest/refund.do',
				self::ACTIVE_URL => $activeUrl.'rest/refund.do',
			],
			'formUrl' => [
				self::TEST_URL => $testUrl.'merchants/#merchant#/payment_ru.html?mdOrder=',
				self::ACTIVE_URL => $activeUrl.'merchants/#merchant#/payment_ru.html?mdOrder=',
			],
		];
	}
}
