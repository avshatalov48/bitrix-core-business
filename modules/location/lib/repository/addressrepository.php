<?php

namespace Bitrix\Location\Repository;

use Bitrix\Location\Entity;
use Bitrix\Location\Model;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Result;
use Bitrix\Main\Application;

/**
 * Class Address
 * @package Bitrix\Location\Repository
 */
class AddressRepository
{
	/** @var Model\AddressTable  */
	protected $tableClass = Model\AddressTable::class;
	/** @var Model\AddressFieldTable  */
	protected $fieldTableClass = Model\AddressFieldTable::class;
	/** @var Model\AddressLinkTable  */
	protected $linkTableClass = Model\AddressLinkTable::class;

	/**
	 * @param int $id
	 * @return Entity\Address|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * todo: do we really always need all this additional fields?
	 * todo: no location - no address.
	 */
	public function findById(int $id)
	{
		$res = $this->tableClass::getList([
			'select' => ['*', 'FIELDS', 'LOCATION', 'LOCATION.NAME', 'LINKS'],
			'filter' => ['=ID' => $id],
		])->fetchObject();

		if(!$res)
		{
			$result = null;
		}
		else
		{
			$result = Entity\Address\Converter\OrmConverter::convertFromOrm($res);
		}

		return $result;
	}

	/**
	 * @param string $entityId
	 * @param string $entityType
	 * @return Entity\Address\AddressCollection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function findByLinkedEntity(string $entityId, string $entityType): Entity\Address\AddressCollection
	{
		$res = $this->tableClass::getList([
			'select' => ['*', 'FIELDS', 'LOCATION', 'LOCATION.NAME', 'LOCATION.FIELDS', 'LINKS'],
			'filter' => [
				'=LINKS.ENTITY_ID' => $entityId,
				'=LINKS.ENTITY_TYPE' => $entityType,
			],
		])->fetchCollection();

		return Entity\Address\Converter\OrmConverter::convertCollectionFromOrm($res);
	}

	/**
	 * @param Entity\Address $address
	 * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\Result|\Bitrix\Main\ORM\Data\UpdateResult
	 */
	public function save(Entity\Address $address)
	{
		$result = new Result();

		if($location = $address->getLocation())
		{
			// Even if location has id > 0. It could contain unsaved name on other language.
			$res = $location->save();

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		$fields = Entity\Address\Converter\DbFieldConverter::convertToDbField($address);

		if((int)$fields['ID'] > 0)
		{
			$result = $this->tableClass::update($fields['ID'], $fields);
		}
		else
		{
			$result = $this->tableClass::add($fields);

			if($result->isSuccess())
			{
				$address->setId($result->getId());
			}
		}

		if ($address->getId() <= 0 || !$result->isSuccess())
		{
			return  $result;
		}

		$this->saveFieldCollection($address);
		$this->saveLinkCollection($address);

		return $result;
	}

	protected function saveFieldCollection(Entity\Address $address): void
	{
		if ($address->getId() <= 0)
		{
			throw new ArgumentNullException('Address Id');
		}

		$connection = Application::getConnection();

		$this->fieldTableClass::deleteByAddressId($address->getId());

		$fields = [];
		/** @var Entity\Address\Field $field */
		foreach ($address->getFieldCollection() as $field)
		{
			$value = $field->getValue();

			$fields[] = [
				'TYPE' => $field->getType(),
				'VALUE' => $value,
				'ADDRESS_ID' => $address->getId(),
				'VALUE_NORMALIZED' => Entity\Address\Normalizer\Builder::build($address->getLanguageId())
					->normalize($value)
				,
			];
		}

		$sqls = $connection->getSqlHelper()->prepareMergeMultiple(
			$this->fieldTableClass::getTableName(),
			[
				'ADDRESS_ID',
				'TYPE',
			],
			$fields
		);
		foreach ($sqls as $sql)
		{
			$connection->query($sql);
		}
	}

	protected function saveLinkCollection(Entity\Address $address)
	{
		if ($address->getId() <= 0)
		{
			throw new ArgumentNullException('Address Id');
		}

		$connection = Application::getConnection();

		$this->linkTableClass::deleteByAddressId($address->getId());

		$links = [];
		/** @var Entity\Address\IAddressLink $link */
		foreach ($address->getLinks() as $link)
		{
			$links[] = [
				'ADDRESS_ID' => $address->getId(),
				'ENTITY_ID' => $link->getAddressLinkEntityId(),
				'ENTITY_TYPE' => $link->getAddressLinkEntityType(),
			];
		}

		$sqls = $connection->getSqlHelper()->prepareMergeMultiple(
			$this->linkTableClass::getTableName(),
			[
				'ADDRESS_ID',
				'ENTITY_ID',
				'ENTITY_TYPE',
			],
			$links
		);
		foreach ($sqls as $sql)
		{
			$connection->query($sql);
		}
	}

	public function delete(int $addressId)
	{
		if($addressId <= 0)
		{
			throw new ArgumentNullException('Address Id');
		}

		$result = $this->tableClass::delete($addressId);
		$this->fieldTableClass::deleteByAddressId($addressId);
		$this->linkTableClass::deleteByAddressId($addressId);
		return $result;
	}
}
