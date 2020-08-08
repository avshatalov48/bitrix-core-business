<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;

use Bitrix\Sale\Exchange\Integration;

class Contact extends Client
{
	public function init($params)
	{
		//FIO				-> NAME, LAST_NAME
		//EMAIL				-> EMAIL
		//PHONE				-> PHONE
		//ZIP				-> ADDRESS_POSTAL_CODE
		//LOCATION			->
		//CITY				-> ADDRESS_CITY
		//ADDRESS			-> ADDRESS

		foreach($params as $index=>$item)
		{
			$this->collection->addItem(
				Integration\Service\Internal\Container\Item::create(
					Integration\Service\Internal\Entity\Factory::create($this->getDstEntityTypeId())
						->setOriginId($index)
						->setOriginatorId(static::ANALITICS_ORIGINATOR_ID)
						->setName($item['NAME'])
						->setLastName($item['LAST_NAME'])
						->setEmail($item['EMAIL'])
						->setPhone($item['PHONE'])
						->setAddressPostalCode($item['ZIP'])
						->setAddress($item['ADDRESS']))
					->setInternalIndex($index)
			);
		}

		$this->relationLoad();

		return $this;
	}

	static protected function getUsersFieldsValues(array $indexes)
	{
		return static::getUserCollectionByTypeId(
			static::loadUserCollection($indexes), Integration\Service\User\EntityType::TYPE_I)
			->toArray();
	}

	static protected function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\Contact();
	}

	public function getSrcEntityTypeId()
	{
		return Integration\EntityType::USER;
	}
	public function getDstEntityTypeId()
	{
		return Integration\CRM\EntityType::CONTACT;
	}
}