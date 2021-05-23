<?php
namespace Bitrix\Sale\Cashbox\Internals\Analytics;

use Bitrix\Sale\Internals\Analytics,
	Bitrix\Sale\Cashbox\CheckManager,
	Bitrix\Sale\Cashbox\Manager,
	Bitrix\Main\Type\DateTime;

/**
 * Class Cachbox
 * @package Bitrix\Sale\Internals\Analytics
 */
final class Provider extends Analytics\Provider
{
	private const TYPE = 'cashbox';

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getProviderData(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];
		/** @var \Bitrix\Sale\Cashbox\Cashbox $cashboxHandler */
		foreach ($this->getCashboxHandlers() as $cashboxHandler)
		{
			$checkData = $this->getCheckData($cashboxHandler, $dateFrom, $dateTo);
			if ($checkData)
			{
				$result[] = [
					'cashbox' => $cashboxHandler::getCode(),
					'date_time' => $checkData['date_time'],
				];
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private function getCashboxHandlers(): array
	{
		return [
			\Bitrix\Sale\Cashbox\CashboxOrangeData::class,
			\Bitrix\Sale\Cashbox\CashboxCheckbox::class,
			\Bitrix\Sale\Cashbox\CashboxBusinessRu::class,
		];
	}

	/**
	 * @param string $cashboxHandler
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getCheckData(string $cashboxHandler, DateTime $dateFrom, DateTime $dateTo): array
	{
		$cashboxIdList = $this->getCashboxIdList($cashboxHandler);
		if (!$cashboxIdList)
		{
			return [];
		}

		$result = [];

		$checkData = CheckManager::getList([
			'select' => ['ID', 'DATE_PRINT_END'],
			'filter' => [
				'CASHBOX_ID' => $cashboxIdList,
				'STATUS' => 'Y',
				'>=DATE_PRINT_END' => $dateFrom,
				'<=DATE_PRINT_END' => $dateTo,
			],
			'limit' => 1,
			'order' => ['ID' => 'DESC'],
		])->fetch();
		if ($checkData)
		{
			$result = [
				'date_time' => $checkData['DATE_PRINT_END']->format('Y-m-01'),
			];
		}

		return $result;
	}

	/**
	 * @param string $cashboxHandler
	 * @return array|array[]|null[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getCashboxIdList(string $cashboxHandler): array
	{
		$result = [];

		$cashboxList = Manager::getList([
			'select' => ['ID'],
			'filter' => [
				'=HANDLER' => '\\'.$cashboxHandler,
			],
		])->fetchAll();

		if ($cashboxList)
		{
			$result = array_column($cashboxList, 'ID');
		}

		return $result;
	}
}
