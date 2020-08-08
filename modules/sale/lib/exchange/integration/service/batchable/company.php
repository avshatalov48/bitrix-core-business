<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;

use Bitrix\Main\Error;
use Bitrix\Sale\Exchange\Integration;

class Company extends Client
{
	public function init($params)
	{
		//COMPANY			-> TITLE
		//COMPANY_ADR		-> REG_ADDRESS
		//INN				->
		//KPP				->
		//CONTACT_PERSON	->
		//EMAIL				-> EMAIL
		//PHONE				-> PHONE
		//FAX				->
		//ZIP				-> ADDRESS_POSTAL_CODE
		//CITY				-> ADDRESS_CITY
		//LOCATION			->
		//ADDRESS			-> ADDRESS

		foreach($params as $index=>$item)
		{
			$this->collection->addItem(
				Integration\Service\Internal\Container\Item::create(
					Integration\Service\Internal\Entity\Factory::create($this->getDstEntityTypeId())
						->setOriginId($index)
						->setOriginatorId(static::ANALITICS_ORIGINATOR_ID)
						->setTitle($item['TITLE'])
						->setRegAddress($item['COMPANY_ADR'])
						->setEmail($item['EMAIL'])
						->setPhone($item['PHONE'])
						->setAddressPostalCode($item['ZIP'])
						->setAddressCity($item['CITY'])
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
			static::loadUserCollection($indexes), Integration\Service\User\EntityType::TYPE_E)
			->toArray();
	}

	static protected function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\Company();
	}

	public function getSrcEntityTypeId()
	{
		return Integration\EntityType::USER;
	}
	public function getDstEntityTypeId()
	{
		return Integration\CRM\EntityType::COMPANY;
	}
}