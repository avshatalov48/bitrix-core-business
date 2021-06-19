<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Internals;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders\IOrderProvider;

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

			$order = [
				'id' => $claim['EXTERNAL_ID'],
				'is_successful' => $isSuccessful ? 'Y' : 'N',
				'status' => $claim['EXTERNAL_STATUS'],
				'created_at' => $claim['CREATED_AT']->getTimestamp(),
			];

			if ($claim['EXTERNAL_FINAL_PRICE'] && $claim['EXTERNAL_CURRENCY'])
			{
				$order['amount'] = $claim['EXTERNAL_FINAL_PRICE'];
				$order['currency'] = $claim['EXTERNAL_CURRENCY'];
			}

			$result[] = $order;
		}

		return $result;
	}
}
