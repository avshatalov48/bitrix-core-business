<?php

namespace Bitrix\Catalog\Integration\Report\Handler\StoreProfit;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

class GridHandler extends ProfitHandler
{
	public function prepare()
	{
		$reportData = [
			'filter' => $this->getFilterParameters(),
		];

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW))
		{
			$reportData['stub'] = static::getNoAccessToStoresStub();
		}
		else
		{
			$storeTotals = $this->getStoreTotals();
			if (!empty($storeTotals))
			{
				$reportData['items'] = $storeTotals;
				$reportData['overall'] = $this->prepareOverallTotals($storeTotals);
			}
		}

		return $reportData;
	}
}
