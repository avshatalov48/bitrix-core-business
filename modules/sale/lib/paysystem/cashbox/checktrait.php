<?php

namespace Bitrix\Sale\PaySystem\Cashbox;

use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Trait CheckTrait
 * @package Bitrix\Sale\PaySystem\Cashbox
 */
trait CheckTrait
{
	/**
	 * @param Sale\Payment $payment
	 * @return Sale\PaySystem\ServiceResult
	 */
	private function buildCheckQuery(Sale\Payment $payment): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$documents = Sale\Cashbox\CheckManager::collateDocuments([$payment]);
		$document = current($documents);
		if ($document)
		{
			$check = Sale\Cashbox\CheckManager::createByType($document['TYPE']);
			if ($check)
			{
				$check->setEntities($document['ENTITIES']);
				$check->setRelatedEntities($document['RELATED_ENTITIES']);

				$service = $payment->getPaySystem();
				if ($service)
				{
					/** @var Sale\Cashbox\CashboxPaySystem $cashboxClass */
					$cashboxClass = $service->getCashboxClass();

					$params = $service->getParamsBusValue($payment);
					$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();

					$cashboxData = Sale\Cashbox\Manager::getList([
						'select' => ['ID'],
						'filter' => [
							'=ACTIVE' => 'Y',
							'=HANDLER' => $cashboxClass,
							'=KKM_ID' => $params[$paySystemCodeForKkm],
						],
					])->fetch();

					if ($cashboxData)
					{
						$cashbox = Sale\Cashbox\Manager::getObjectById($cashboxData['ID']);
						if ($cashbox)
						{
							$result->setData($cashbox->buildCheckQuery($check));
						}
					}
					else
					{
						$result->addError(
							new Main\Error(
								Main\Localization\Loc::getMessage('SALE_PAYSYSTEM_CASHBOX_CHECKTRAIT_CASHBOX_NOT_FOUND_ERROR')
							)
						);
					}
				}
				else
				{
					$result->addError(
						new Main\Error(
							Main\Localization\Loc::getMessage('SALE_PAYSYSTEM_CASHBOX_CHECKTRAIT_SERVICE_IS_EMPTY_ERROR')
						)
					);
				}
			}
			else
			{
				$result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_PAYSYSTEM_CASHBOX_CHECKTRAIT_CHECK_IS_EMPTY_ERROR')
					)
				);
			}
		}
		else
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage('SALE_PAYSYSTEM_CASHBOX_CHECKTRAIT_DOCUMENT_IS_EMPTY_ERROR')
				)
			);
		}

		return $result;
	}
}
