<?php

namespace Bitrix\Sale\PaySystem\Cashbox\Events;

use Bitrix\Main\Event;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Sale;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Sale\PaySystem\Service;

class UpdateCashboxesOnBusinessValueUpdate implements IExecuteEvent
{
	/** @var array $oldMapping */
	private $oldMapping;
	/** @var array $newMapping */
	private $newMapping;
	/** @var string $consumerKey */
	private $consumerKey;
	/** @var string $codeKey */
	private $codeKey;
	/** @var Service $currentPaySystemService */
	private $currentPaySystemService;

	public function __construct(Event $event)
	{
		$this->oldMapping = $event->getParameter('OLD_MAPPING');
		$this->newMapping = $event->getParameter('NEW_MAPPING');
		$this->consumerKey = $event->getParameter('CONSUMER_KEY');
		$this->codeKey = $event->getParameter('CODE_KEY');
	}

	/**
	 * @return Sale\Result
	 */
	public function executeEvent(): Sale\Result
	{
		$valueUnchanged =
			isset($this->oldMapping['PROVIDER_VALUE'], $this->newMapping['PROVIDER_VALUE'])
			&& $this->oldMapping['PROVIDER_VALUE'] === $this->newMapping['PROVIDER_VALUE']
		;
		$isPaySystemValue = isset($this->consumerKey) && mb_strpos($this->consumerKey, Service::PAY_SYSTEM_PREFIX) === 0;

		if (empty($this->oldMapping) || $valueUnchanged || !$isPaySystemValue)
		{
			return new Sale\Result();
		}

		$paySystemIterator = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ACTIVE' => 'Y',
			]
		]);

		while ($paySystemItem = $paySystemIterator->fetch())
		{
			$this->currentPaySystemService = new Service($paySystemItem);
			if ($this->currentPaySystemService->getConsumerName() !== $this->consumerKey || !($this->currentPaySystemService->isSupportPrintCheck()))
			{
				continue;
			}

			/** @var Sale\Cashbox\CashboxPaySystem $cashboxClass */
			$cashboxClass = $this->currentPaySystemService->getCashboxClass();
			$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();
			if ($paySystemCodeForKkm !== $this->codeKey)
			{
				continue;
			}

			$cashboxesToChange = $this->getListOfCashboxesToChange();

			$newKkmId = $this->newMapping['PROVIDER_VALUE'];

			if ($this->valueHasBeenCleared())
			{
				$newKkmId = $this->getNewKkmId();

				/*
				 * if we can't set a new ID, then it's
				 * impossible to use an existing cashbox
				 * or create a new one, so we have to
				 * delete the old cashbox
				 */
				if (empty($newKkmId))
				{
					$this->deleteCashboxesToChange();
					continue;
				}

				$existingCashboxes = Sale\Cashbox\Manager::getList([
					'select' => ['ID', 'KKM_ID'],
					'filter' => [
						'=HANDLER' => $cashboxClass,
						'=KKM_ID' => $newKkmId,
					],
				]);

				/**
				 * If any cashboxes with the default ID exist, we can use them
				 * We don't need any cashboxes with the old ids, and we
				 * also don't need to edit any cashboxes as the needed ones
				 * exist already
				 */
				if ($existingCashboxes->fetch())
				{
					$this->deleteCashboxesToChange();
					continue;
				}
			}

			foreach ($cashboxesToChange as $cashboxItem)
			{
				Sale\Cashbox\Manager::update($cashboxItem['ID'], ['KKM_ID' => $newKkmId]);
			}
		}

		return new Sale\Result();
	}

	/**
	 * get list of cashboxes that might need to be changed
	 * they will either get updated with the new id
	 * or removed if it's impossible to set a new id
	 * @return Result
	 */
	private function getListOfCashboxesToChange(): Result
	{
		return Sale\Cashbox\Manager::getList([
			'select' => ['ID', 'KKM_ID'],
			'filter' => [
				'=HANDLER' => $this->currentPaySystemService->getCashboxClass(),
				'=KKM_ID' => $this->oldMapping['PROVIDER_VALUE'],
			],
		]);
	}

	private function deleteCashboxesToChange(): void
	{
		$cashboxesToChange = $this->getListOfCashboxesToChange();
		foreach ($cashboxesToChange as $cashboxItem)
		{
			$serviceCashbox = Sale\Cashbox\Manager::getObjectById($cashboxItem['ID']);
			$deleteResult = Sale\Cashbox\Manager::delete($cashboxItem['ID']);
			if ($deleteResult->isSuccess())
			{
				AddEventToStatFile('sale', 'deleteCashbox', '', $serviceCashbox::getCode());
			}
		}
	}

	/**
	 * get the default value for kkm id
	 * @return mixed
	 */
	private function getNewKkmId()
	{
		$paySystemCodeForKkm = $this->currentPaySystemService->getCashboxClass()::getPaySystemCodeForKkm();
		return Sale\BusinessValue::getMapping(
			$paySystemCodeForKkm,
			$this->currentPaySystemService->getConsumerName(),
			BusinessValueTable::COMMON_PERSON_TYPE_ID
		)['PROVIDER_VALUE'];
	}

	/**
	 * check if an id value has been cleared
	 * i.e. set to default or otherwise set to an empty string
	 * @return bool
	 */
	private function valueHasBeenCleared(): bool
	{
		return empty($this->newMapping);
	}
}
