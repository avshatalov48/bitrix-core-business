<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Type\DateTime;

/**
 * Class Provider
 * @package Bitrix\Sale\Internals\Analytics
 */
abstract class Provider
{
	/**
	 * @return string
	 */
	abstract public static function getCode(): string;

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 */
	abstract protected function getProviderData(DateTime $dateFrom, DateTime $dateTo): array;

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 */
	public function getData(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];

		foreach ($this->getProviderData($dateFrom, $dateTo) as $data)
		{
			$result[] = [
				'data' => $data,
				'hash' => $this->getHash($data),
			];
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	abstract protected function getHash(array $data): string;
}
