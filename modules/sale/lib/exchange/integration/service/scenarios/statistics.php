<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;

use Bitrix\Sale\Exchange\Integration\Service\Command;

class Statistics
{
	public function modify(array $params)
	{
		$statistic = new Command\Batch\Statistics();
		return $statistic
			->init($params)
			->modify();
	}
}