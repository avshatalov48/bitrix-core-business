<?php

namespace Bitrix\Sale\PaySystem\Cashbox\Events;

use Bitrix\Sale;

class DeleteCashboxesOnDeletePaySystem implements IExecuteEvent
{
	/** @var Sale\PaySystem\Service $service */
	private $service;

	public function __construct(Sale\PaySystem\Service $service)
	{
		$this->service = $service;
	}

	/**
	 * @return Sale\Result
	 */
	public function executeEvent(): Sale\Result
	{
		$result = new Sale\Result();

		if (!$this->service || !$this->service->isSupportPrintCheck())
		{
			return $result;
		}

		/** @var Sale\Cashbox\CashboxPaySystem $cashboxClass */
		$cashboxClass = $this->service->getCashboxClass();
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
				$supportedKkmModels[] = $cashboxClass::getKkmValue($this->service);
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
			$serviceCashbox = Sale\Cashbox\Manager::getObjectById($cashboxItem['ID']);
			$deleteResult = Sale\Cashbox\Manager::delete($cashboxItem['ID']);
			if ($deleteResult->isSuccess())
			{
				AddEventToStatFile('sale', 'deleteCashbox', '', $serviceCashbox::getCode());
			}
			else
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}
}