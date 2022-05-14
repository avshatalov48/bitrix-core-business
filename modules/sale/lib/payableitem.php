<?php

namespace Bitrix\Sale;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class PayableItem
 * @package Bitrix\Sale
 */
abstract class PayableItem extends Internals\CollectableEntity
{
	abstract public function getEntityObject();

	abstract public static function getEntityType();

	/** @var Internals\CollectableEntity */
	protected $item;

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return [
			'ORDER_ID', 'PAYMENT_ID', 'ENTITY_ID', 'ENTITY_TYPE',
			'DATE_INSERT', 'QUANTITY', 'XML_ID'
		];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\PayableItemTable::getMap();
	}

	/**
	 * @param PayableItemCollection $collection
	 * @param Internals\CollectableEntity $entity
	 * @return PayableItem
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\SystemException
	 */
	public static function create(PayableItemCollection $collection, Internals\CollectableEntity $entity)
	{
		/** @var PayableItem $item */
		$item = static::createPayableItemObject();

		$item->setCollection($collection);

		$item->item = $entity;

		if ($entity->getId() > 0)
		{
			$item->setFieldNoDemand('ENTITY_ID', $entity->getId());
		}

		$item->setFieldNoDemand('ENTITY_TYPE', static::getEntityType());
		$item->setFieldNoDemand('XML_ID', static::generateXmlId());

		return $item;
	}

	public function getQuantity() : float
	{
		return (float)$this->getField('QUANTITY');
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId() : string
	{
		return uniqid('bx_');
	}

	/**
	 * @param array $fields
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	private static function createPayableItemObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$entityClassName = $registry->get(static::getRegistryEntity());

		return new $entityClassName($fields);
	}

	/**
	 * @param $id
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadForPayment($id)
	{
		if (intval($id) <= 0)
		{
			return [];
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PayableItemCollection $payableItemCollection */
		$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);
		$dbRes = $payableItemCollection::getList([
			'filter' => [
				'=PAYMENT_ID' => $id,
				'=ENTITY_TYPE' => static::getEntityType()
			]
		]);

		$entityList = [];
		while ($data = $dbRes->fetch())
		{
			$entityList[] = static::createPayableItemObject($data);
		}

		return $entityList;
	}

	/**
	 * @return Result
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		$id = $this->getId();

		if ($id > 0)
		{
			$fields = $this->getFields()->getChangedValues();

			$r = $this->updateInternal($id, $fields);
		}
		else
		{
			$payment = $this->getCollection()->getPayment();

			if ((int)$this->getField('ENTITY_ID') === 0)
			{
				$this->setFieldNoDemand('ENTITY_ID', $this->getEntityObject()->getId());
			}

			$this->setFieldNoDemand('DATE_INSERT', new Main\Type\DateTime());
			$this->setFieldNoDemand('PAYMENT_ID', $payment->getId());

			$fields = $this->getFields()->getValues();
			$r = $this->addInternal($fields);
			if ($r->isSuccess())
			{
				$id = $r->getId();
				$this->setFieldNoDemand('ID', $id);
			}
		}

		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$result->setId($id);

		return $result;
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\PayableItemTable::update($primary, $data);
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		return Internals\PayableItemTable::add($data);
	}
}