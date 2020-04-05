<?php

namespace Bitrix\Sale\Discount;

use Bitrix\Main;
use Bitrix\Main\Loader;

final class CumulativeCalculator
{
	const TYPE_ORDER_ARCHIVED     = 2;
	const TYPE_ORDER_NON_ARCHIVED = 3;

	const TYPE_COUNT_PERIOD_ALL_TIME = 'all_time';
	const TYPE_COUNT_PERIOD_INTERVAL = 'interval';
	const TYPE_COUNT_PERIOD_RELATIVE = 'relative';

	private $userId;
	private $siteId;
	private $sumConfiguration = array();

	public function __construct($userId, $siteId)
	{
		$this->userId = $userId;
		$this->siteId = $siteId;
	}

	public function setSumConfiguration(array $sumConfiguration)
	{
		$this->sumConfiguration = $sumConfiguration;

		return $this;
	}

	public function calculate()
	{
		if (!Loader::includeModule('currency'))
		{
			return 0;
		}

		if(empty($this->userId))
		{
			return 0;
		}

		$filter = $this->createFilterBySumConfiguration($this->sumConfiguration);
		$orderUserId = $this->userId;
		$filter = array_merge(array(
			'USER_ID' => $orderUserId,
			'=LID' => $this->siteId,
			'=PAYED' => 'Y',
			'=CANCELED' => 'N',
		), $filter);

		$sum = 0;
		foreach (array(self::TYPE_ORDER_NON_ARCHIVED, self::TYPE_ORDER_ARCHIVED) as $orderType)
		{
			$sum += $this->sumOrders($filter, $orderType);
		}

		return $sum;
	}

	private function createFilterBySumConfiguration($sumConfiguration)
	{
		$filter = array();
		if (empty($sumConfiguration))
		{
			return $filter;
		}

		$type = $sumConfiguration['type_sum_period'];
		$periodData = $sumConfiguration['sum_period_data'];

		if ($type === self::TYPE_COUNT_PERIOD_INTERVAL)
		{
			if (!empty($periodData['order_start']))
			{
				$filter['>=DATE_INSERT'] = Main\Type\DateTime::createFromTimestamp($periodData['order_start']);
			}
			if (!empty($periodData['order_end']))
			{
				$filter['<DATE_INSERT'] = Main\Type\DateTime::createFromTimestamp($periodData['order_end']);
			}
		}
		elseif ($type === self::TYPE_COUNT_PERIOD_RELATIVE)
		{
			$value = (int)$periodData['period_value'];
			$typeRelativePeriod = $periodData['period_type'];
			if (!in_array($typeRelativePeriod, array('D', 'M', 'Y')))
			{
				return array();
			}

			$start = new Main\Type\DateTime();
			$end = $start->add("-P{$value}{$typeRelativePeriod}");

			$filter['>=DATE_INSERT'] = $end;
		}
		elseif ($type === self::TYPE_COUNT_PERIOD_ALL_TIME)
		{
			return array();
		}

		return $filter;
	}

	private function sumOrders($filter, $orderType)
	{
		$provider = null;
		if ($orderType === self::TYPE_ORDER_ARCHIVED)
		{
			/** @var \Bitrix\Sale\Archive\Manager $provider */
			$provider = '\Bitrix\Sale\Archive\Manager';
		}
		elseif ($orderType === self::TYPE_ORDER_NON_ARCHIVED)
		{
			/** @var \Bitrix\Sale\Order $provider */
			$provider = '\Bitrix\Sale\Order';
		}

		if ($provider === null)
		{
			return false;
		}

		$orders = $provider::getList(
			array(
				'filter' => $filter,
				'select' => array('DATE_INSERT', 'PRICE', 'CURRENCY')
			)
		);

		$sum = 0;
		$currency = null;
		foreach ($orders as $orderData)
		{
			if (!$currency)
			{
				$currency = $orderData['CURRENCY'];
			}

			if ($currency !== $orderData['CURRENCY'])
			{
				$sum += \CCurrencyRates::ConvertCurrency($orderData['PRICE'], $orderData['CURRENCY'], $currency);
			}
			else
			{
				$sum += $orderData['PRICE'];
			}
		}

		return $sum;
	}
}