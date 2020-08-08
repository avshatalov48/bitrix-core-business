<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration\Service\Batchable;

Loc::loadMessages(__FILE__);

class ActivityAdd extends Base
	implements INamingEntity
{
	public function adds(array $params)
	{
		$activity = new Batchable\Activity();
		return $activity->init($params)
			->adds()
			->getCollection();
	}

	static public function prepareFields($params)
	{
		$result = [];
		foreach ($params as $index=>$param)
		{
			$deal = new Batchable\Deal();

			$relation = static::loadRelation($index, $deal->getSrcEntityTypeId(), $deal->getDstEntityTypeId());
			$dealId = $relation->getDestinationEntityId();
			if($dealId>0)
			{
				$result[$index] = [
					//'ID' => $param['ID'],
					'SUBJECT' => static::getNamingEntity(['ID'=>$relation->getSourceEntityId()]),
					'OWNER_TYPE_ID' => $relation->getDestinationEntityTypeId(),
					'OWNER_ID' => $relation->getDestinationEntityId(),
				];
			}
		}
		return $result;
	}

	static public function getNamingEntity(array $fields)
	{
		return Loc::getMessage("SALE_INTEGRATIONB24_SERVICE_SCENARIOS_NAME").$fields['ID'];
	}
}