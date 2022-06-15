<?php

namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main;

/**
 * Class Storage
 *
 * @package Bitrix\Sale\Internals
 * @internal
 */
final class Storage
{
	/** @var Provider $provider */
	private $provider;

	/**
	 * @param Provider $provider
	 */
	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * @return void
	 */
	public function save(): void
	{
		$providerData = $this->provider->getData();
		if ($providerData)
		{
			$this->savePayload($providerData);
		}
	}

	/**
	 * @param string $code
	 * @param Main\Type\DateTime $dateTo
	 * @return array
	 */
	public static function getPayloadByCode(string $code, Main\Type\DateTime $dateTo): array
	{
		if (empty($code))
		{
			return [];
		}

		$result = [];

		$analyticsIterator = AnalyticsTable::getList([
			'select' => ['ID', 'PAYLOAD'],
			'filter' => [
				'=CODE' => $code,
				'<=CREATED_AT' => $dateTo,
			],
		]);
		while ($analyticsData = $analyticsIterator->fetch())
		{
			$result[] = [
				'data' => $analyticsData['PAYLOAD'],
				'hash' => self::calculateHash($analyticsData),
			];
		}

		return $result;
	}

	/**
	 * @param string $providerCode
	 * @param Main\Type\DateTime $dateTo
	 * @return void
	 */
	public static function clean(string $providerCode, Main\Type\DateTime $dateTo): void
	{
		if (empty($providerCode))
		{
			return;
		}

		AnalyticsTable::deleteByCodeAndDate($providerCode, $dateTo);
	}

	private function savePayload(array $data): void
	{
		AnalyticsTable::add([
			'CODE' => $this->provider::getCode(),
			'PAYLOAD' => $data,
		]);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	private static function calculateHash(array $data): string
	{
		if (self::isB24())
		{
			$uniqParam = BX24_HOST_NAME;
		}
		else
		{
			$uniqParam = Main\Analytics\Counter::getPrivateKey();
		}

		return md5(serialize($data) . $uniqParam);
	}

	/**
	 * @return bool
	 */
	private static function isB24(): bool
	{
		return Main\Loader::includeModule('bitrix24');
	}

	/**
	 * Cleans up old events
	 *
	 * @return string
	 */
	public static function cleanUpAgent(): string
	{
		$dateTo = new Main\Type\DateTime();
		$dateTo->add('-30D');

		AnalyticsTable::deleteByDate($dateTo);

		return '\Bitrix\Sale\Internals\Analytics\Storage::cleanUpAgent();';
	}
}
