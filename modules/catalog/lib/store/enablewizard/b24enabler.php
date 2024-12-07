<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Main\Result;

class B24Enabler extends Enabler
{
	public static function enable(array $options = []): Result
	{
		$result = parent::enable($options);
		if (!$result->isSuccess())
		{
			return $result;
		}

		CostPriceCalculator::setMethod(
			$options['costPriceCalculationMethod'] ?? CostPriceCalculator::METHOD_AVERAGE
		);

		return $result;
	}

	public static function disable(): Result
	{
		$result = parent::disable();
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $result;
	}
}
