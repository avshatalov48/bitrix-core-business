<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Entity\Address\Converter\ArrayConverter;
use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Service;

/**
 * Class Address
 * @package Bitrix\Location\Controller
 * Facade
 */
class Address extends \Bitrix\Main\Engine\Controller
{
	protected function init()
	{
		parent::init();
		ErrorService::getInstance()->setThrowExceptionOnError(true);
	}

	protected function getDefaultPreFilters()
	{
		return [];
	}

	/**
	 * @param int $addressId
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function findById(int $addressId): array
	{
		$result = null;

		if($address = Service\AddressService::getInstance()->findById($addressId))
		{
			$result = ArrayConverter::convertToArray($address);
		}

		return $result;
	}

	/**
	 * @param \Bitrix\Location\Entity\Address[] $collection
	 * @return array
	 */
	protected function createArrayFromAddressCollection(array $collection): array
	{
		$result = [];

		foreach ($collection as $address)
		{
			$result[] = ArrayConverter::convertToArray($address);
		}

		return $result;
	}

	/**
	 * @param array $address
	 * @return array
	 */
	public static function saveAction(array $address): array
	{
		$entity = ArrayConverter::convertFromArray($address);
		$result = $entity->save();

		return [
			'isSuccess' => $result->isSuccess(),
			'errors' => $result->getErrorMessages(),
			'address' => ArrayConverter::convertToArray($entity)
		];
	}

	/**
	 * @param int $addressId
	 * @return \Bitrix\Main\ORM\Data\DeleteResult
	 * @throws \Exception
	 */
	public function deleteAction(int $addressId): \Bitrix\Main\ORM\Data\DeleteResult
	{
		return Service\AddressService::getInstance()->delete($addressId);
	}
}
