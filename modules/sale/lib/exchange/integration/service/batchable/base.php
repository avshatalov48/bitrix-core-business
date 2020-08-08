<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;

use Bitrix\Main\Error;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Rest\Entity\BusinessValuePersonDomainType;

abstract class Base
{
	const ANALITICS_ORIGINATOR_ID = 'bitrix.cms.sync';

	protected $collection;

	public function __construct()
	{
		$this->collection = new Integration\Service\Internal\Container\Collection();
	}

	abstract public function getSrcEntityTypeId();
	abstract public function getDstEntityTypeId();
	abstract public function init($params);
	abstract static public function proxyAdds(array $list);

	protected function relationLoad()
	{
		$collection = $this->getCollection();

		$relations = static::relationEntityList([
				'SRC_ENTITY_ID' => $collection->getIndexes(),
				'SRC_ENTITY_TYPE_ID' => $this->getSrcEntityTypeId(),
				'DST_ENTITY_TYPE_ID' => $this->getDstEntityTypeId()]
		);
		if(count($relations)>0)
		{
			/** @var Integration\Relation\Relation $relation */
			foreach ($relations as $relation)
			{
				$item = $collection->getItemByIndex($relation->getSourceEntityId());
				$item->getEntity()
					->setRelation($relation);
			}
		}
		return $this;
	}
	public function relationListDstEntity()
	{
		$result = [];
		/** @var Integration\Service\Internal\Container\Item $item */
		foreach ($this->getCollection() as $item)
		{
			if($item->getEntity()->hasRelation())
			{
				$result[] = $item->getEntity()->getRelation()->getDestinationEntityId();
			}
		}
		return $result;
	}
	public function relationDeleteByDstEntity($dstEntityList)
	{
		/** @var Integration\Service\Internal\Container\Item $item */
		foreach($this->getCollection() as $item)
		{
			if($item->getEntity()->hasRelation())
			{
				if(!in_array($item->getEntity()->getRelation()->getDestinationEntityId(), $dstEntityList))
				{
					$item->getEntity()->getRelation()->setDestinationEntityId(0);
					$item->getEntity()->getRelation()->save();
				}
			}
		}
	}
	public function relationVoid()
	{
		$collection = new Integration\Service\Internal\Container\Collection();

		/** @var Integration\Service\Internal\Container\Item $item */
		foreach($this->getCollection() as $item)
		{
			if($item->getEntity()->hasRelation() == false
				|| $item->getEntity()->getRelation()->getDestinationEntityId() == 0)
			{
				$collection->addItem($item);
			}
		}
		return $collection;
	}
	public function relationRefreshByDstEntity($list)
	{
		foreach($list as $index=>$dstEntityId)
		{
			$item = $this->getCollection()->getItemByIndex($index);
			if(is_null($item) == false)
			{
				$relation = new \Bitrix\Sale\Exchange\Integration\Relation\Relation(
					$this->getSrcEntityTypeId(),
					$index,
					$this->getDstEntityTypeId(),
					$dstEntityId);

				$relation->save();
				//collection->getItemByIndex(index)->getEntity->getRelation->setDstEntityId($data['ID'])
			}
		}
		$this->relationLoad();
	}

	static protected function relationEntityList($filter=[])
	{
		$result = [];

		$list = Integration\Entity\B24IntegrationRelationTable::getList(['filter'=>$filter])
			->fetchAll();

		foreach ($list as $item)
		{
			$result[] = Integration\Relation\Relation::createFromArray([

				'SRC_ENTITY_TYPE_ID'=>$item['SRC_ENTITY_TYPE_ID'],
				'SRC_ENTITY_ID'=>$item['SRC_ENTITY_ID'],
				'DST_ENTITY_TYPE_ID'=>$item['DST_ENTITY_TYPE_ID'],
				'DST_ENTITY_ID'=>$item['DST_ENTITY_ID']
			]);
		}

		return $result;
	}

	/*protected function clientRelation($params, $typeName)
	{
		$list = $params[$typeName];
		$client = static::factoryClient($typeName);

		return $this->relationEntityList([
				'SRC_ENTITY_ID' => array_keys($list),
				'SRC_ENTITY_TYPE_ID' => $client->getSrcEntityTypeId(),
				'DST_ENTITY_TYPE_ID' => $client->getDstEntityTypeId()]
		);
	}*/
	static protected function clientRelation($indexes, $typeName)
	{
		$client = static::factoryClient($typeName);
		return count($indexes)>0 ?
			static::relationEntityList([
				'SRC_ENTITY_ID' => $indexes,
				'SRC_ENTITY_TYPE_ID' => $client->getSrcEntityTypeId(),
				'DST_ENTITY_TYPE_ID' => $client->getDstEntityTypeId()])
			:[];
	}

	static protected function factoryClient($typeName)
	{
		$typeId = BusinessValuePersonDomainType::resolveID($typeName);

		if(BusinessValuePersonDomainType::isDefined($typeId))
		{
			if($typeName == BusinessValuePersonDomainType::TYPE_E_NAME)
			{
				return new Company();
			}
			elseif($typeName == BusinessValuePersonDomainType::TYPE_I_NAME)
			{
				return new Contact();
			}
		}

		throw new \Bitrix\Main\NotSupportedException("Client : '".$typeId."' is not supported in current context");
	}

	static protected function getIndexesFromParams(array $params)
	{
		return array_keys($params);
	}
	static protected function getIndexesContactFromParams($contacts)
	{
		$result = [];
		if(count($contacts)>0)
		{
			foreach ($contacts as $contact)
			{
				$result[] = $contact['CONTACT_ID'];
			}
		}
		return $result;
	}
	static protected function getContactItemsFromIndexes($indexes)
	{
		$result = [];
		if(count($indexes)>0)
		{
			foreach ($indexes as $index)
			{
				$result[] = ['CONTACT_ID'=>$index];
			}
		}
		return $result;
	}

	static protected function prepareFieldsAdds($fields)
	{
		$result = [];
		foreach ($fields as $index=>$item)
		{
			$result[$index] = ['fields'=>$item];
		}
		return $result;
	}

	protected function addsFromParams(array $params)
	{
		$res = static::proxyAdds($params);
		if(count($res['result'])>0)
		{
			$this->relationRefreshByDstEntity($res['result']);
		}
		if(count($res['result_error'])>0)
		{
			foreach($res['result_error'] as $index=>$error)
			{
				$item = $this->getCollection()->getItemByIndex($index);
				$item->setError(new Error($error['error_description']));
			}
		}
		return $this;
	}
	public function adds()
	{
		return $this
			->addsFromParams($this
				->getCollection()->toArray());
	}

	public function getCollection()
	{
		return $this->collection;
	}
}