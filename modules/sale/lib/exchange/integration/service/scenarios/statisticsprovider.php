<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;


use Bitrix\Sale\Exchange\Integration\Service\Command\Line;

class StatisticsProvider
{
	public function register(array $params)
	{
		$result = Line\StatisticsProvider::getList(['xmlId'=>$params['xmlId']]);

		if($result['statisticProviders'][0])
		{
			$providerId = $result['statisticProviders'][0]['id'];
		}
		else
		{
			$providerId = Line\StatisticsProvider::add($params)['statisticProvider']['id'];
		}

		return $providerId > 0;
	}
}