<?php

namespace Bitrix\Sale\Bigdata;

use Bitrix\Main\Analytics\Counter;

class Cloud
{
	public static function getPersonalRecommendation($userId, $count = 10)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'recommend',
			'local_uid' => $userId,
			'count' => $count+10
		);

		$result = static::makeQuery($params);

		return $result;
	}

	public static function getFollowUpProducts($productIds)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'postcross',
			'eids' => join(',', $productIds),
			'count' => 20
		);

		$result = static::makeQuery($params);

		return $result;
	}

	public static function getPotentialConsumers($productId)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'consumers',
			'eid' => $productId
		);

		$result = static::makeQuery($params);

		return $result;
	}

	protected static function makeQuery($params)
	{
		return null;
	}
}
