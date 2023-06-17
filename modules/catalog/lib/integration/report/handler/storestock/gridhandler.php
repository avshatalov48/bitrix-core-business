<?php

namespace Bitrix\Catalog\Integration\Report\Handler\StoreStock;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;

class GridHandler extends BaseHandler
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
