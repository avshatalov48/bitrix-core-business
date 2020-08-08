<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;


use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Exchange\Integration\Exception;
use Bitrix\Sale\Rest\Entity\BusinessValuePersonDomainType;

class Deal extends Proxy
{
	public function init($params)
	{
		foreach($params as $index=>$item)
		{
			$entity = Integration\Service\Internal\Entity\Factory::create($this->getDstEntityTypeId());

			$entity->setTitle($item['TITLE']);
			$entity->setOriginId($index);
			$entity->setOriginatorId(static::ANALITICS_ORIGINATOR_ID);
			$entity->setOpportunity($item['PRICE']);
			$entity->setCurrency($item['CURRENCY']);

			if($item['COMPANY_ID'])
			{
				$entity->setCompanyId($item['COMPANY_ID']);
			}
			if($item['CONTACT_ID'])
			{
				$entity->setContactId($item['CONTACT_ID'])	;
			}

			$this->collection->addItem(
				Integration\Service\Internal\Container\Item::create($entity)
					->setInternalIndex($index)
			);
		}
		return $this;
	}

	static public function contactItemsGet($id)
	{
		$proxy = static::getProxy();
		$r = $proxy->contactItemsGet($id);

		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			throw new Exception\BatchableException(implode(',', $r->getErrorMessages()));
		}

		return $result;
	}
	static public function dealContactUpdates($id, $params, $contacts)
	{
		$result = [];
		$indexes = self::getIndexesFromParams($params);
		$contacts = static::getIndexesContactFromParams($contacts);

		$relations = static::clientRelation($indexes, BusinessValuePersonDomainType::TYPE_I_NAME);

		$contactIds = [];
		/** @var Integration\Relation\Relation $relation */
		foreach ($relations as $relation)
		{
			$contactIds[] = $relation->getDestinationEntityId();
		}

		if(count(array_diff($contactIds, $contacts))>0)
		{
			$proxy = static::getProxy();
			$r = $proxy->contactItemsSet($id, static::getContactItemsFromIndexes(array_merge($contactIds, $contacts)));
			if($r->isSuccess())
			{
				$result = $r->getData()['DATA']['result'];
			}
			else
			{
				throw new Exception\BatchableException(implode(',', $r->getErrorMessages()));
			}
		}
		return $result;
	}
	static public function dealContactAdds($id, $params)
	{
		$result = [];
		$indexes = self::getIndexesFromParams($params);

		$relations = static::clientRelation($indexes, BusinessValuePersonDomainType::TYPE_I_NAME);

		$contactIds = [];
		/** @var Integration\Relation\Relation $relation */
		foreach ($relations as $relation)
		{
			$contactIds[] = $relation->getDestinationEntityId();
		}

		if(count($contactIds)>0)
		{
			$proxy = static::getProxy();
			$r = $proxy->contactItemsSet($id, static::getContactItemsFromIndexes($contactIds));
			if($r->isSuccess())
			{
				$result = $r->getData()['DATA']['result'];
			}
			else
			{
				throw new Exception\BatchableException(implode(',', $r->getErrorMessages()));
			}
		}
		return $result;
	}

	public function getSrcEntityTypeId()
	{
		return Integration\EntityType::ORDER;
	}
	public function getDstEntityTypeId()
	{
		return Integration\CRM\EntityType::DEAL;
	}

	static protected function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\Deal();
	}
}