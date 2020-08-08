<?php
namespace Bitrix\Sale\Cashbox\Internals\Analytics;

use Bitrix\Sale\Internals\Analytics,
	Bitrix\Sale\Cashbox\CheckManager,
	Bitrix\Sale\Cashbox\Manager,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Main\Context;

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
			if ($this->isCheckExists($cashboxHandler, $dateFrom, $dateTo))
			{
				$data['cashbox'] = $cashboxHandler::getCode();
				$result[] = $data;
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
		];
	}

	/**
	 * @param string $cashboxHandler
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function isCheckExists(string $cashboxHandler, DateTime $dateFrom, DateTime $dateTo): bool
	{
		$cashboxIdList = $this->getCashboxIdList($cashboxHandler);
		if (!$cashboxIdList)
		{
			return false;
		}

		return (bool)CheckManager::getList([
			'select' => ['ID'],
			'filter' => [
				'CASHBOX_ID' => $cashboxIdList,
				'STATUS' => 'Y',
				'>=DATE_PRINT_END' => $dateFrom,
				'<=DATE_PRINT_END' => $dateTo,
			],
			'limit' => 1,
		])->fetch();
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

	/**
	 * @param array $data
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getHash(array $data): string
	{
		$hostName = Loader::includeModule('bitrix24')
			? BX24_HOST_NAME
			: Context::getCurrent()->getRequest()->getHttpHost();

		return md5((new \DateTime())->format('m.Y').serialize($data).$hostName);
	}
}
