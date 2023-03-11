<?php

namespace Bitrix\Sale\PaySystem\Cashbox\Events;

use Bitrix\Main\Event;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem\Manager;

class ToggleCashboxesOnUpdatePaySystem implements IExecuteEvent
{
	/** @var Sale\PaySystem\Service $service */
	private $service;
	/** @var array $oldFields */
	private $oldFields;
	/** @var array $newFields */
	private $newFields;

	public function __construct(Event $event)
	{
		$paySystemId = $event->getParameter('PAY_SYSTEM_ID');
		$this->service = Manager::getObjectById($paySystemId);
		$this->oldFields = $event->getParameter('OLD_FIELDS');
		$this->newFields = $event->getParameter('NEW_FIELDS');
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

		if (
			!isset($this->oldFields['ACTIVE'], $this->newFields['ACTIVE'])
			||
			(
				isset($this->oldFields['ACTIVE'], $this->newFields['ACTIVE'])
				&& $this->oldFields['ACTIVE'] === $this->newFields['ACTIVE']
			)
		)
		{
			return $result;
		}

		$newStatus = $this->newFields['ACTIVE'];

		/** @var Sale\Cashbox\CashboxPaySystem $cashboxClass */
		$cashboxClass = $this->service->getCashboxClass();

		$kkmId = $this->getKkmID();

		if ($this->isCashboxUsedByOtherPaySystems())
		{
			return $result;
		}

		$cashboxList = Sale\Cashbox\Manager::getList([
			'select' => ['ID', 'KKM_ID'],
			'filter' => [
				'=HANDLER' => $cashboxClass,
				'=KKM_ID' => $kkmId,
			],
		]);
		foreach ($cashboxList as $cashboxItem)
		{
			$updateResult = Sale\Cashbox\Manager::update($cashboxItem['ID'], ['ACTIVE' => $newStatus]);
			if (!$updateResult->isSuccess())
			{
				$result->addErrors($updateResult->getErrors());
			}
		}

		return $result;
	}

	private function isCashboxUsedByOtherPaySystems(): bool
	{
		$cashboxClass = $this->service->getCashboxClass();

		$paySystemIterator = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ACTIVE' => 'Y',
				'!=ID' => $this->service->getField('ID'),
			]
		]);

		$kkmId = $this->getKkmId();
		$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();

		while ($paySystemItem = $paySystemIterator->fetch())
		{
			$paySystemService = new Sale\PaySystem\Service($paySystemItem);
			if (
				$paySystemService->isSupportPrintCheck()
				&& $paySystemService->getCashboxClass() === $cashboxClass
				&& Sale\BusinessValue::getValuesByCode($paySystemService->getConsumerName(), $paySystemCodeForKkm) === $kkmId
			)
			{
				return true;
			}
		}

		return false;
	}

	private function getKkmId(): array
	{
		$cashboxClass = $this->service->getCashboxClass();
		$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();

		return Sale\BusinessValue::getValuesByCode($this->service->getConsumerName(), $paySystemCodeForKkm);
	}
}