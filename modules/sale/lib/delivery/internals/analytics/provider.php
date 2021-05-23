<?php

namespace Bitrix\Sale\Delivery\Internals\Analytics;

use Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders\IOrderProvider;
use Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders\YandexTaxiOrderProvider;
use Bitrix\Sale\Internals\Analytics;
use Bitrix\Main\Type\DateTime;
use Sale\Handlers\Delivery\YandexTaxi\Internals\OrderAnalyticsProvider;

/**
 * Class Provider
 * @package Bitrix\Sale\Delivery\Internals\Analytics
 * @internal
 */
final class Provider extends Analytics\Provider
{
	/**
	 * @inheritDoc
	 */
	public static function getCode(): string
	{
		return 'delivery';
	}

	/**
	 * @inheritDoc
	 */
	protected function getProviderData(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];

		$providers = $this->getProviders();

		/**
		 * @var string $providerCode
		 * @var IOrderProvider $provider
		 */
		foreach ($providers as $providerCode => $provider)
		{
			$result[] = [
				'delivery' => $providerCode,
				'date_from' => $dateFrom->getTimestamp(),
				'date_to' => $dateTo->getTimestamp(),
				'orders' => $provider->provideOrders($dateFrom, $dateTo),
			];
		}

		return $result;
	}

	/**
	 * @return IOrderProvider[]
	 */
	private function getProviders(): array
	{
		return [
			'yandex_taxi' => new OrderAnalyticsProvider(),
		];
	}
}
