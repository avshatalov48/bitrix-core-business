<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios\Controller;

use \Bitrix\Sale\Exchange\Integration\Service\Scenarios;

class ActivityAdd extends Scenarios\ActivityAdd
{
	public function adds(array $params)
	{
		static::dealAddsRelation($params);
		return parent::adds($params);
	}
}