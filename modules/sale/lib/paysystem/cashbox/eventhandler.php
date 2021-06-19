<?php

namespace Bitrix\Sale\PaySystem\Cashbox;

use Bitrix\Sale;

/**
 * Class EventHandler
 * @package Bitrix\Sale\PaySystem\Cashbox
 */
class EventHandler
{
	/**
	 * @return Sale\Result
	 */
	public static function onDeletePaySystem(Sale\PaySystem\Service $service): Sale\Result
	{
		$result = new Sale\Result();

		if (!$service->isSupportPrintCheck())
		{
			return $result;
		}

		/** @var Sale\Cashbox\CashboxPaySystem $cashboxClass */
		$cashboxClass = $service->getCashboxClass();
		$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();

		$supportedKkmModels = [];

		$paySystemIterator = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ACTIVE' => 'Y',
			]
		]);
		while ($paySystemItem = $paySystemIterator->fetch())
		{
			$paySystemService = new Sale\PaySystem\Service($paySystemItem);
			if (
				$paySystemService->isSupportPrintCheck()
				&& $paySystemService->getCashboxClass() === $cashboxClass
			)
			{
				$supportedKkmModels[] = Sale\BusinessValue::getValuesByCode($paySystemService->getConsumerName(), $paySystemCodeForKkm);
			}
		}

		$supportedKkmModels = array_unique(array_merge(...$supportedKkmModels));

		$cashboxList = Sale\Cashbox\Manager::getList([
			'select' => ['ID', 'KKM_ID'],
			'filter' => [
				'=HANDLER' => $cashboxClass,
				'!@KKM_ID' => $supportedKkmModels,
			],
		]);
		foreach ($cashboxList as $cashboxItem)
		{
			$deleteResult = Sale\Cashbox\Manager::delete($cashboxItem['ID']);
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param string $kkmId
	 * @return Sale\Result
	 */
	public static function onDisabledFiscalization(Sale\PaySystem\Service $service, string $kkmId): Sale\Result
	{
		$result = new Sale\Result();

		if (!$service->isSupportPrintCheck())
		{
			return $result;
		}

		$cashboxList = Sale\Cashbox\Manager::getList([
			'select' => ['ID', 'KKM_ID'],
			'filter' => [
				'=HANDLER' => $service->getCashboxClass(),
				'=KKM_ID' => $kkmId,
			],
		]);
		foreach ($cashboxList as $cashboxItem)
		{
			$deleteResult = Sale\Cashbox\Manager::delete($cashboxItem['ID']);
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}
}
