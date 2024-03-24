<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Sale;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class BuyerStatistic
{
	/**
	 * Executes the query and returns selection by parameters of the query. This function is an alias to the Query object functions
	 *
	 * @return Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getList($filter)
	{
		return Internals\BuyerStatisticTable::getList($filter);
	}

	/**
	 * Fill statistic for user for certain site and currency.
	 * The function'll update values if user entry exists or it'll create new if user entry doesn't exist
	 *
	 * @param $userId
	 * @param $currency
	 * @param $lid
	 *
	 * @return Main\Result
	 */
	public static function calculate($userId, $currency, $lid)
	{
		$result = static::collectUserData($userId, $currency, $lid);

		if (!$result->isSuccess() || $result->hasWarnings())
		{
			return $result;
		}

		$statisticData = static::getList(
			array(
				'select' => array('ID'),
				'filter' => array('=USER_ID' => $userId, '=CURRENCY' => $currency, '=LID' => $lid),
				'limit' => 1
			)
		);

		$buyerStatistic = $statisticData->fetch();
		if (!$buyerStatistic)
		{
			return Internals\BuyerStatisticTable::add($result->getData());
		}

		return Internals\BuyerStatisticTable::update($buyerStatistic['ID'], $result->getData());
	}

	/**
	 * Collect statistic (last order date, count of full paid orders, count of partially paid orders and sum of paid payments) for user, certain site and currency.
	 *
	 * @param $userId
	 * @param $currency
	 * @param $lid
	 *
	 * @return Result
	 */
	protected static function collectUserData($userId, $currency, $lid)
	{
		$result = new Result();
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$result->addError(new Main\Error('Wrong user id'));
			return $result;
		}

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$lastOrderDate = null;
		$lastArchiveDate = null;

		$orderData = $orderClass::getList([
			'select' => ['DATE_INSERT'],
			'filter' => ['=USER_ID' => $userId, '=CURRENCY' => $currency, '=LID' => $lid],
			'order' => ['DATE_INSERT' => 'DESC'],
			'limit' => 1
		]);

		if ($resultOrder = $orderData->fetch())
		{
			$lastOrderDate = $resultOrder['DATE_INSERT'];
		}

		$archiveData = Archive\Manager::getList([
			'select' => ['DATE_INSERT'],
			'filter' => ['=USER_ID' => $userId, '=CURRENCY' => $currency, '=LID' => $lid],
			'order' => ['DATE_INSERT' => 'DESC'],
			'limit' => 1
		]);

		if ($resultOrder = $archiveData->fetch())
		{
			$lastArchiveDate = $resultOrder['DATE_INSERT'];
		}

		if ($lastOrderDate || $lastArchiveDate)
		{
			$statistic = array(
				'USER_ID' => $userId,
				'CURRENCY' => $currency,
				'LID' => $lid,
				'LAST_ORDER_DATE' => ($lastOrderDate) ?: $lastArchiveDate
			);

			if ($lastOrderDate)
			{
				$orderDataCount = $orderClass::getList([
					'select' => ['FULL_SUM_PAID', 'COUNT_FULL_PAID_ORDER', 'COUNT_PART_PAID_ORDER'],
					'filter' => [
						'=USER_ID' => $userId,
						'=CURRENCY' => $currency,
						'=LID' => $lid,
						'>SUM_PAID' => 0
					],
					'group' => ['USER_ID'],
					'runtime' => [
						new ExpressionField('COUNT_PART_PAID_ORDER', 'COUNT(1)'),
						new ExpressionField('COUNT_FULL_PAID_ORDER', 'SUM(CASE WHEN PAYED = \'Y\' THEN 1 ELSE 0 END)'),
						new ExpressionField('FULL_SUM_PAID', 'SUM(SUM_PAID)')
					],
				]);

				$countData = $orderDataCount->fetch();

				$statistic['SUM_PAID'] = !empty($countData['FULL_SUM_PAID']) ? $countData['FULL_SUM_PAID'] : "0.0000";
				$statistic['COUNT_PART_PAID_ORDER'] = !empty($countData['COUNT_PART_PAID_ORDER']) ? $countData['COUNT_PART_PAID_ORDER'] : 0;
				$statistic['COUNT_FULL_PAID_ORDER'] = !empty($countData['COUNT_FULL_PAID_ORDER']) ? $countData['COUNT_FULL_PAID_ORDER'] : 0;
			}

			if ($lastArchiveDate)
			{
				$archiveDataCount = Archive\Manager::getList([
					'select' => ['FULL_SUM_PAID', 'COUNT_FULL_PAID_ORDER', 'COUNT_PART_PAID_ORDER'],
					'filter' => [
						'=USER_ID' => $userId,
						'=CURRENCY' => $currency,
						'=LID' => $lid,
						'>SUM_PAID' => 0
					],
					'group' => ['USER_ID'],
					'runtime' => [
						new ExpressionField('COUNT_PART_PAID_ORDER', 'COUNT(1)'),
						new ExpressionField('COUNT_FULL_PAID_ORDER', 'SUM(CASE WHEN PAYED = \'Y\' THEN 1 ELSE 0 END)'),
						new ExpressionField('FULL_SUM_PAID', 'SUM(SUM_PAID)')
					],
				]);

				$countArchiveData = $archiveDataCount->fetch();

				if ($countArchiveData['FULL_SUM_PAID'] > 0)
					$statistic['SUM_PAID'] += $countArchiveData['FULL_SUM_PAID'];
				if ($countArchiveData['COUNT_PART_PAID_ORDER'] > 0)
					$statistic['COUNT_PART_PAID_ORDER'] += $countArchiveData['COUNT_PART_PAID_ORDER'];
				if ($countArchiveData['COUNT_FULL_PAID_ORDER'] > 0)
					$statistic['COUNT_FULL_PAID_ORDER'] += $countArchiveData['COUNT_FULL_PAID_ORDER'];
			}

			$result->setData($statistic);
		}
		else
		{
			$result->addWarning(new Main\Error('User doesn\'t have orders' ));
		}

		return $result;
	}
}
