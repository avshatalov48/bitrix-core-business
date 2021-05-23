<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Internals;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders\IOrderProvider;
use Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders\Order;

/**
 * Class OrderAnalyticsProvider
 * @package Sale\Handlers\Delivery\YandexTaxi\Internals
 * @internal
 */
final class OrderAnalyticsProvider implements IOrderProvider
{
	/**
	 * @inheritDoc
	 */
	public function provideOrders(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];

		$claims = ClaimsTable::getList(
			[
				'select' => [
					'CREATED_AT',
					'EXTERNAL_ID',
					'EXTERNAL_STATUS',
					'EXTERNAL_RESOLUTION',
					'EXTERNAL_FINAL_PRICE',
					'EXTERNAL_CURRENCY',
				],
				'filter' => [
					'>=CREATED_AT' => $dateFrom,
					'<CREATED_AT' => $dateTo,
					'IS_SANDBOX_ORDER' => 'N',
				],
			]
		)->fetchAll();

		foreach ($claims as $claim)
		{
			/**
			 * Delivery order success indicator
			 */
			$isSuccessful = null;
			if (in_array($claim['EXTERNAL_RESOLUTION'], ClaimsTable::$externalStatuses, true))
			{
				$isSuccessful = ($claim['EXTERNAL_RESOLUTION'] === ClaimsTable::EXTERNAL_STATUS_SUCCESS);
			}

			$order = (new Order())
				->setId($claim['EXTERNAL_ID'])
				->setCreatedAt($claim['CREATED_AT']->getTimestamp())
				->setStatus($claim['EXTERNAL_STATUS'])
				->setIsSuccessful($isSuccessful);

			if ($claim['EXTERNAL_FINAL_PRICE'] && $claim['EXTERNAL_CURRENCY'])
			{
				$order
					->setAmount($claim['EXTERNAL_FINAL_PRICE'])
					->setCurrency($claim['EXTERNAL_CURRENCY']);
			}

			$result[] = $order;
		}

		return $result;
	}
}
