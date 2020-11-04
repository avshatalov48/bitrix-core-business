<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;


use Bitrix\Sale\Exchange\Integration\Service\Command\Line;
use Bitrix\Sale\Exchange\Integration\Service\Command\Batch;

class Connector
{
	public function add(array $params)
	{
		Line\App::optionSet($params['OPTIONS']);
		Batch\Placement::binds($params['PLACEMENTS']);
		(new StatisticsProvider())->register($params['PROVIDER']);
	}

	public function delete(array $params)
	{
		Batch\Placement::unbinds($params['PLACEMENTS']);
	}
}