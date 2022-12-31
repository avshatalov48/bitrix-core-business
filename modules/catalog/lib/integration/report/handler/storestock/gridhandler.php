<?php

namespace Bitrix\Catalog\Integration\Report\Handler\StoreStock;

use \Bitrix\Catalog\Integration\Report\Handler\BaseHandler;

class GridHandler extends BaseHandler
{
	public function prepare()
	{
		$reportData = [
			'filter' => $this->getFilterParameters(),
		];

		$storeTotals = $this->getStoreTotals();
		if (!empty($storeTotals))
		{
			$reportData['items'] = $storeTotals;
			$reportData['overall'] = $this->prepareOverallTotals($storeTotals);
		}

		return $reportData;
	}
}
