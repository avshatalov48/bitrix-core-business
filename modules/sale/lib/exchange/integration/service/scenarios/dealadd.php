<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios;


use Bitrix\Sale\Exchange\Integration\Relation\Relation;
use Bitrix\Sale\Exchange\Integration\Service\Batchable;
use Bitrix\Sale\Exchange\Integration\Service\User;

class DealAdd extends Base
	implements INamingEntity
{
	public function adds(array $params)
	{
		$contact = new RefreshClient\Contact();
		$contact->refresh($params);

		$company = new RefreshClient\Company();
		$company->refresh($params);

		/** @var User\Container\Item $company */
		/*foreach ($company->getCollection() as $company)
		{
			if($company->hasError())
			{
				///???
			}
		}*/

		$listFields = static::prepareFields($params);
		$deal = new Batchable\Deal();
		$deal
			->init($listFields)
			->adds();

		$activity = new ActivityAdd();
		$activity->adds($activity::prepareFields($params));
	}

	static public function prepareFields($params)
	{
		$userCollection = Batchable\Client::getUserCollectionFromOrderList($params);

		$result = [];
		/** @var User\Container\Item $item */
		foreach ($userCollection as $item)
		{
			foreach ($params as $index=>$param)
			{
				if($index == $item->getInternalIndex())
				{
					if($item->getEntity()->getType() == User\EntityType::TYPE_I)
					{
						$result[$index] = [
							//'ID' => $param['ID'],
							'TITLE' => static::getNamingEntity($param),
							'CONTACT_ID' => static::getDestinationEntityId($item),
							'PRICE' => $param['PRICE'],
							'CURRENCY' => $param['CURRENCY']
						];
					}
					elseif($item->getEntity()->getType() == User\EntityType::TYPE_E)
					{
						$result[$index] = [
							//'ID' => $param['ID'],
							'TITLE' => static::getNamingEntity($param),
							'COMPANY_ID' => static::getDestinationEntityId($item),
							'PRICE' => $param['PRICE'],
							'CURRENCY' => $param['CURRENCY']
						];
					}
				}
			}
		}
		return $result;
	}

	static protected function getDestinationEntityId(User\Container\Item $item)
	{
		$client = static::resolveClient($item->getEntity()->getType());
		$relation = static::loadRelation($item->getEntity()->getId(), $client->getSrcEntityTypeId(), $client->getDstEntityTypeId());

		return ($relation instanceof Relation) ? $relation->getDestinationEntityId():0;
	}

	static protected function resolveClient($userTypeId)
	{
		if(User\EntityType::isDefined($userTypeId))
		{
			if($userTypeId == User\EntityType::TYPE_I)
			{
				return new Batchable\Contact();
			}
			elseif($userTypeId == User\EntityType::TYPE_E)
			{
				return new Batchable\Company();
			}
		}
		throw new \Bitrix\Main\NotSupportedException("UserTypeId : '".$userTypeId."' is not supported in current context");
	}

	static public function getNamingEntity(array $fields)
	{
		return 'new Deal by order: '.$fields['ID'];
	}
}