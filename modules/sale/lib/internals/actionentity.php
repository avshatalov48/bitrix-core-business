<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Sale\Result;

class ActionEntity extends PoolBase
{
	protected static $pool = array();

	const ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY = 'shipment_collection_reserved_quantity';
	const ACTION_ENTITY_SHIPMENT_RESERVED_QUANTITY = 'shipment_reserved_quantity';

	/**
	 * @param $code
	 * @param array $typeList
	 *
	 * @return Result
	 */
	public static function runActions($code, array $typeList = array())
	{
		$result = new Result();
		$actionsList = static::getPoolByCode($code);

		if (empty($actionsList))
		{
			return $result;
		}

		if (empty($typeList))
		{
			$typeList = array_keys($actionsList);
		}

		if (empty($typeList))
		{
			return $result;
		}

		foreach ($typeList as $type)
		{
			if (!isset($actionsList[$type]))
				continue;

			foreach ($actionsList[$type] as $actionParams)
			{
				/** @var Result $r */
				$r = call_user_func_array($actionParams['METHOD'], $actionParams['PARAMS']);

				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				if ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
			}

			static::resetPool($code, $type);
		}

		return $result;
	}
}