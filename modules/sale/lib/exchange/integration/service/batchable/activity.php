<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;


use Bitrix\Sale\Exchange\Integration;

class Activity extends Proxy
{
	public function init($params)
	{
		foreach($params as $index=>$item)
		{
			$this->collection->addItem(
				Integration\Service\Internal\Container\Item::create(
					Integration\Service\Internal\Entity\Factory::create($this->getDstEntityTypeId())
						->setOriginId($index)
						->setOriginatorId(static::ANALITICS_ORIGINATOR_ID)
						->setSubject($item['SUBJECT'])
						->setOwnerTypeId($item['OWNER_TYPE_ID'])
						->setOwnerId($item['OWNER_ID']))
					->setInternalIndex($index)
			);
		}
		return $this;
	}

	static protected function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\Activity();
	}

	public function getSrcEntityTypeId()
	{
		return Integration\EntityType::ORDER;
	}
	public function getDstEntityTypeId()
	{
		return Integration\CRM\EntityType::ACTIVITY;
	}
}