<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Sale\Services\Base\RestrictionInfo;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;

trait RestrictionCurrencyTrait
{
	abstract public function getCurrencyList();

	protected function getRestrictionCurrency(RestrictionInfoCollection $collection): void
	{
		$currencyList = $this->getCurrencyList();
		if (!empty($currencyList) && is_array($currencyList))
		{
			$currencyRestrictionContainer = new RestrictionInfo(
				'Currency',
				[
					'CURRENCY' => $currencyList,
				]
			);
			$collection->add($currencyRestrictionContainer);
		}
	}
}