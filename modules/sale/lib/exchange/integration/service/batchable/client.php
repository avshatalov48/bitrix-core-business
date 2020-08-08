<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;


use Bitrix\Main\Error;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Order;

abstract class Client extends Proxy
{
	static public function resolveFieldsValuesFromOrderList(array $params)
	{
		$indexes = static::getIndexesFromParams($params);
		$list = static::getUsersFieldsValues($indexes);

		return count($list)>0 ? static::collapseUserList($list):[];
	}
	static public function getUserCollectionFromOrderList(array $params)
	{
		$indexes = static::getIndexesFromParams($params);
		return static::loadUserCollection($indexes);
	}

	static protected function collapseUserList(array $list)
	{
		$result = [];
		foreach($list as $item)
		{
			$result[$item['ID']] = $item;
		}
		return $result;
	}

	abstract static protected function getUsersFieldsValues(array $indexes);

	//resolveClients

	static protected function loadUserCollection($indexes)
	{
		$clients = new Integration\Service\User\Container\Collection();
		foreach ($indexes as $index)
		{
			$order = Order::load($index);

			$typeName = Integration\Service\User\Entity\Base::resolveNamePersonDomain($order->getPersonTypeId());
			$clients->addItem(
				Integration\Service\User\Container\Item::create(
					Integration\Service\User\Factory::create(
						Integration\Service\User\EntityType::resolveId($typeName))
						->load($order))->setInternalIndex($index));
		}
		return $clients;
	}
	static protected function getUserCollectionByTypeId(Integration\Service\User\Container\Collection $collection, $typeId)
	{
		$clients = new Integration\Service\User\Container\Collection();
		/** @var Integration\Service\User\Container\Item $item */
		foreach ($collection as $item)
		{
			if($item->getEntity()->getType() == $typeId)
			{
				$clients->addItem($item);
			}
		}
		return $clients;
	}
}