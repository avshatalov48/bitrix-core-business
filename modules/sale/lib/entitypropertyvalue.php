<?php

namespace Bitrix\Sale;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Main;
use Bitrix\Sale\Internals\OrderPropsValueTable;

/**
 * Class EntityPropertyValue
 * @package Bitrix\Sale;
 */
abstract class EntityPropertyValue extends CollectableEntity
{
	/** @var EntityProperty|null $property */
	protected $property = null;

	/**
	 * @return string Registry::ENTITY_SHIPMENT or Registry::ENTITY_ORDER
	 */
	abstract protected static function getEntityType(): string;

	/**
	 * @return string Property class name.
	 */
	abstract protected static function getPropertyClassName(): string;

	/**
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @return EntityPropertyValue
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	abstract protected static function createPropertyValueObject(
		array $property = null,
		array $value = [],
		array $relation = null
	): EntityPropertyValue;

	/**
	 * Returns OnSaved event name
	 * @return string
	 */
	abstract protected static function getOnSavedEventName(): string;

	protected static function extractPaySystemIdList(Entity $entity)
	{
		return [];
	}

	protected static function extractDeliveryIdList(Entity $entity)
	{
		return [];
	}

	protected static function extractTpLandingIdList(Entity $entity) : array
	{
		return [];
	}

	protected static function extractTradingPlatformIdList(Entity $entity): array
	{
		return [];
	}

	/**
	 * @param Entity $entity
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function loadFromDb(Entity $entity): array
	{
		$propertyValues = [];
		$propertyValuesMap = [];

		if ($entity->getId() > 0)
		{
			$dbRes = static::getList(
				[
					'select' => ['ID', 'NAME', 'VALUE', 'CODE', 'ORDER_PROPS_ID'],
					'filter' => [
						'=ENTITY_ID' => $entity->getId(),
						'=ENTITY_TYPE' => static::getEntityType()
					]
				]
			);
			while ($row = $dbRes->fetch())
			{
				$propertyValues[$row['ID']] = $row;
				$propertyValuesMap[$row['ORDER_PROPS_ID']] = $row['ID'];
			}
		}

		/** @var EntityProperty $propertyClassName */
		$propertyClassName = static::getPropertyClassName();

		$getListParams = [
			'select' => [
				'ID',
				'PERSON_TYPE_ID',
				'NAME',
				'TYPE',
				'REQUIRED',
				'DEFAULT_VALUE',
				'SORT',
				'USER_PROPS',
				'IS_LOCATION',
				'PROPS_GROUP_ID',
				'DESCRIPTION',
				'IS_EMAIL',
				'IS_PROFILE_NAME',
				'IS_PAYER',
				'IS_LOCATION4TAX',
				'IS_FILTERED',
				'CODE',
				'IS_ZIP',
				'IS_PHONE',
				'IS_ADDRESS',
				'IS_ADDRESS_FROM',
				'IS_ADDRESS_TO',
				'ACTIVE',
				'UTIL',
				'INPUT_FIELD_LOCATION',
				'MULTIPLE',
				'SETTINGS',
				'ENTITY_TYPE'
			],
			'filter' => static::constructPropertyFilter($entity),
			'runtime' => static::getRelationRuntimeFields(),
			'order' => ['SORT' => 'ASC'],
		];

		$dbRes = $propertyClassName::getList($getListParams);
		$properties = [];
		$propRelation = [];

		while ($row = $dbRes->fetch())
		{
			$properties[$row['ID']] = $row;
			$propRelation[$row['ID']] = [];
		}

		if (!empty($properties))
		{
			$dbRes = OrderPropsRelationTable::getList(
				[
					'select' => [
						'PROPERTY_ID',
						'ENTITY_ID',
						'ENTITY_TYPE'
					],
					'filter' => [
						'PROPERTY_ID' => array_keys($properties)
					]
				]
			);

			while ($row = $dbRes->fetch())
			{
				$propRelation[$row['PROPERTY_ID']][] = $row;
			}
		}

		return [$properties, $propertyValues, $propRelation, $propertyValuesMap];
	}

	/**
	 * @return bool
	 */
	public function needDeleteOnRefresh() : bool
	{
		$property = $this->getPropertyObject();

		return $property ? !empty($property->getRelations()) : false;
	}

	protected static function constructPropertyFilter(Entity $entity) : array
	{
		$filter = [
			'=ENTITY_TYPE' => static::getEntityType()
		];

		if ($entity->getPersonTypeId() > 0)
		{
			$filter['=PERSON_TYPE_ID'] = $entity->getPersonTypeId();
		}

		$subFilter = [
			'LOGIC' => 'OR',
			static::constructPropertyRelatedEntitiesFilter($entity)
		];

		if ($entity->getId() > 0)
		{
			$dbRes = static::getList([
				'select' => ['ORDER_PROPS_ID'],
				'filter' => [
					'=ENTITY_ID' => $entity->getId(),
					'=ENTITY_TYPE' => static::getEntityType()
				]
			]);

			while ($row = $dbRes->fetch())
			{
				$subFilter['@ID'][] = $row['ORDER_PROPS_ID'];
			}
		}

		$filter[] = $subFilter;

		return $filter;
	}

	protected static function hasPresetForLanding(Entity $entity) : bool
	{
		$tpLandingList = static::extractTpLandingIdList($entity);

		if ($tpLandingList)
		{
			$dbRes = Internals\OrderPropsRelationTable::getList([
				'filter' => [
					'@ENTITY_ID' => $tpLandingList,
					'=ENTITY_TYPE' => OrderPropsRelationTable::ENTITY_TYPE_LANDING,
				],
				'cache' => ['ttl' => 86400],
				'limit' => 1
			]);

			return (bool)$dbRes->fetch();
		}

		return false;
	}

	protected static function hasPresetFotTradingPlatform(Entity $entity): bool
	{
		$tpList = static::extractTradingPlatformIdList($entity);

		if ($tpList)
		{
			$dbRes = Internals\OrderPropsRelationTable::getList([
				'filter' => [
					'@ENTITY_ID' => $tpList,
					'=ENTITY_TYPE' => OrderPropsRelationTable::ENTITY_TYPE_TRADING_PLATFORM
				],
				'cache' => ['ttl' => 86400],
				'limit' => 1
			]);

			return (bool)$dbRes->fetch();
		}

		return false;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected static function constructPropertyRelatedEntitiesFilter(Entity $entity): array
	{
		$result = [];

		$psFilter = ['=RELATION_PS.ENTITY_ID' => null];

		if ($paySystemList = static::extractPaySystemIdList($entity))
		{
			$psFilter['LOGIC'] = 'OR';
			$psFilter['@RELATION_PS.ENTITY_ID'] = $paySystemList;
		}

		$result[] = $psFilter;
		$dlvFilter = ['=RELATION_DLV.ENTITY_ID' => null];

		if ($deliveryList = static::extractDeliveryIdList($entity))
		{
			$dlvFilter['LOGIC'] = 'OR';
			$dlvFilter['@RELATION_DLV.ENTITY_ID'] = $deliveryList;
		}

		$result[] = $dlvFilter;

		if (self::hasPresetForLanding($entity))
		{
			$result[] = [
				'LOGIC' => 'OR',
				'!RELATION_PS.ENTITY_ID' => null,
				'!RELATION_DLV.ENTITY_ID' => null,
			];

			$result = [
				'LOGIC' => 'OR',
				'@RELATION_TP_LANDING.ENTITY_ID' => static::extractTpLandingIdList($entity),
				$result,
			];
		}
		else
		{
			$result = [
				'=RELATION_TP_LANDING.ENTITY_ID' => null,
				$result,
			];
		}

		if (self::hasPresetFotTradingPlatform($entity))
		{
			$result[] = [
				'LOGIC' => 'OR',
				'!RELATION_PS.ENTITY_ID' => null,
				'!RELATION_DLV.ENTITY_ID' => null,
				'!RELATION_TP_LANDING.ENTITY_ID' => null,
			];

			$result = [
				'LOGIC' => 'OR',
				'@RELATION_TP.ENTITY_ID' => static::extractTradingPlatformIdList($entity),
				$result,
			];
		}
		else
		{
			$result = [
				'=RELATION_TP.ENTITY_ID' => null,
				$result,
			];
		}

		return $result;
	}

	protected static function getRelationRuntimeFields(): array
	{
		return [
			new ReferenceField(
				'RELATION_PS',
				'\Bitrix\Sale\Internals\OrderPropsRelation',
				[
					'=this.ID' => 'ref.PROPERTY_ID',
					'ref.ENTITY_TYPE' => new SqlExpression('?', OrderPropsRelationTable::ENTITY_TYPE_PAY_SYSTEM)
				],
				'left_join'
			),
			new ReferenceField(
				'RELATION_DLV',
				'\Bitrix\Sale\Internals\OrderPropsRelation',
				[
					'=this.ID' => 'ref.PROPERTY_ID',
					'ref.ENTITY_TYPE' => new SqlExpression('?', OrderPropsRelationTable::ENTITY_TYPE_DELIVERY)
				],
				'left_join'
			),
			new Main\Entity\ReferenceField(
				'RELATION_TP_LANDING',
				'\Bitrix\Sale\Internals\OrderPropsRelation',
				[
					'=this.ID' => 'ref.PROPERTY_ID',
					'ref.ENTITY_TYPE' => new Main\DB\SqlExpression('?', OrderPropsRelationTable::ENTITY_TYPE_LANDING)
				],
				'left_join'
			),
			new Main\Entity\ReferenceField(
				'RELATION_TP',
				'\Bitrix\Sale\Internals\OrderPropsRelation',
				[
					'=this.ID' => 'ref.PROPERTY_ID',
					'ref.ENTITY_TYPE' => new Main\DB\SqlExpression('?', OrderPropsRelationTable::ENTITY_TYPE_TRADING_PLATFORM)
				],
				'left_join'
			),
		];
	}

	/**
	 * @param array $properties
	 * @param array $propertyValues
	 * @param array $propRelation
	 * @param array $propertyValuesMap
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected static function createPropertyValuesObjects(
		array $properties,
		array $propertyValues,
		array $propRelation,
		array $propertyValuesMap): array
	{
		$result = [];

		foreach ($properties as $property)
		{
			$id = $property['ID'];

			if (isset($propertyValuesMap[$id]))
			{
				$fields = $propertyValues[$propertyValuesMap[$id]];
				unset($propertyValues[$propertyValuesMap[$id]]);
				unset($propertyValuesMap[$id]);
			}
			else
			{
				if ($property['ACTIVE'] === 'N')
				{
					continue;
				}

				$fields = [];
			}

			$result[$id] = static::createPropertyValueObject($property, $fields, $propRelation[$id]);
		}

		foreach ($propertyValues as $propertyValue)
		{
			$result[$propertyValue['ORDER_PROPS_ID']] = static::createPropertyValueObject(null, $propertyValue);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return OrderPropsValueTable::getMap();
	}

	/**
	 * EntityPropertyValue constructor.
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @throws Main\SystemException|Main\LoaderException
	 */
	protected function __construct(array $property = null, array $value = [], array $relation = null)
	{
		if (!$property && !$value)
		{
			throw new Main\SystemException('invalid arguments', 0, __FILE__, __LINE__);
		}

		if ($property)
		{
			if (is_array($property['SETTINGS']))
			{
				$property += $property['SETTINGS'];
				unset ($property['SETTINGS']);
			}
		}
		else
		{
			$property = [
				'TYPE' => 'STRING',
				'PROPS_GROUP_ID' => 0,
				'NAME' => $value['NAME'],
				'CODE' => $value['CODE'],
				// defaults
				'PERSON_TYPE_ID' => null,
				'DESCRIPTION' => null,
				'REQUIRED' => null,
				'DEFAULT_VALUE' => null,
				'SORT' => null,
				'USER_PROPS' => null,
				'IS_LOCATION' => 'N',
				'IS_EMAIL' => 'N',
				'IS_PROFILE_NAME' => 'N',
				'IS_PAYER' => 'N',
				'IS_LOCATION4TAX' => 'N',
				'IS_FILTERED' => 'N',
				'IS_ZIP' => 'N',
				'IS_PHONE' => 'N',
				'IS_ADDRESS' => 'N',
				'IS_ADDRESS_FROM' => 'N',
				'IS_ADDRESS_TO' => 'N',
				'ACTIVE' => null,
				'UTIL' => null,
				'INPUT_FIELD_LOCATION' => null,
				'MULTIPLE' => null,
			];
		}

		$property['ENTITY_TYPE'] = static::getEntityType();

		$propertyClassName = static::getPropertyClassName();

		$this->property = new $propertyClassName($property, $relation);

		if (isset($value['VALUE']))
		{
			$value['VALUE'] = $this->property->normalizeValue($value['VALUE']);
		}

		parent::__construct($value);

		if (!$value)
		{
			$value = [
				'ORDER_PROPS_ID' => $this->property->getId(),
				'NAME' => $this->property->getName(),
				'CODE' => $this->property->getField('CODE'),
				'XML_ID' => static::generateXmlId(),
				'ENTITY_TYPE' => $this->property->getField('ENTITY_TYPE')
			];

			if (!empty($this->property->getField('DEFAULT_VALUE')))
			{
				$value['VALUE'] = $this->property->getField('DEFAULT_VALUE');
			}

			$this->setFieldsNoDemand($value);
		}
	}

	/**
	 * @return string
	 */
	public static function generateXmlId(): string
	{
		return uniqid('bx_');
	}

	/**
	 * @param EntityPropertyValueCollection $collection
	 * @param array $property
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function create(EntityPropertyValueCollection $collection, array $property = [])
	{
		$propertyValue = static::createPropertyValueObject($property);
		$propertyValue->setCollection($collection);
		return $propertyValue;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return ['VALUE'];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return [];
	}

	/**
	 * @param $name
	 * @param $value
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function normalizeValue($name, $value)
	{
		if ($name === 'VALUE')
		{
			$value = $this->property->normalizeValue($value);
		}

		return parent::normalizeValue($name, $value);
	}

	/**
	 * @internal
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function save(): Result
	{
		$this->checkCallingContext();

		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		if ($this->getId() > 0)
		{
			$res = $this->update();
		}
		else
		{
			$res = $this->add();
		}

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		$this->callEventOnPropertyValueEntitySaved();

		return $result;
	}

	private function checkCallingContext(): void
	{
		$order = $this->getOrder();

		if (!$order->isSaveRunning())
		{
			trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity", E_USER_WARNING);
		}
	}

	/**
	 * @return Order|null
	 */
	public function getOrder(): ?Order
	{
		/** @var PropertyValueCollectionBase $collection */
		$collection = $this->getCollection();
		if (!$collection)
		{
			return null;
		}

		/** @var Order $order */
		$order = $collection->getOrder();
		if (!$order)
		{
			return null;
		}

		return $order;
	}

	/**
	 * @return Result
	 * @throws Main\NotImplementedException|Main\ArgumentOutOfRangeException
	 */
	protected function update()
	{
		$result = new Result();

		$value = $this->property->getPreparedValueForSave($this);
		$res = $this->updateInternal($this->getId(), ['VALUE' => $value]);

		if ($res->isSuccess())
		{
			$result->setId($res->getId());
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function add()
	{
		$result = new Result();

		$value = $this->property->getPreparedValueForSave($this);
		/** @var PropertyValueCollection $collection */

		$res = $this->addInternal(
			[
				'ORDER_ID' => $this->getOrder()->getId(),
				'ORDER_PROPS_ID' => $this->property->getId(),
				'NAME' => $this->property->getName(),
				'VALUE' => $value,
				'CODE' => $this->property->getField('CODE'),
				'XML_ID' => $this->getField('XML_ID'),
				'ENTITY_ID' => $this->getCollection()->getEntityParentId(),
				'ENTITY_TYPE' => $this->getField('ENTITY_TYPE')
			]
		);

		if ($res->isSuccess())
		{
			$this->setFieldNoDemand('ID', $res->getId());
			$result->setId($res->getId());
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function callEventOnPropertyValueEntitySaved(): void
	{
		$event = new Main\Event(
			'sale',
			static::getOnSavedEventName(),
			[
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues(),
			]
		);

		$event->send();
	}

	/**
	 * @param array $post
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setValueFromPost(array $post): Result
	{
		$result = new Result();

		$key = ($this->getField('ORDER_PROPS_ID')) ?: "n".$this->getInternalIndex();

		if (is_array($post['PROPERTIES']) && array_key_exists($key, $post['PROPERTIES']))
		{
			$this->setValue($post['PROPERTIES'][$key]);
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkValue($key, $value)
	{
		$result = new Result();

		$res = $this->property->checkValue($value);
		if (!$res->isSuccess())
		{
			$errors = $res->getErrors();
			foreach ($errors as $error)
			{
				$result->addError(new ResultError($error->getMessage(), "PROPERTIES[$key]"));
				$result->addError(new ResultWarning($error->getMessage(), "PROPERTIES[$key]"));
			}
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkRequiredValue($key, $value)
	{
		$result = new Result();

		$res = $this->property->checkRequiredValue($value);
		if (!$res->isSuccess())
		{
			$errors = $res->getErrors();
			foreach ($errors as $error)
			{
				$result->addError(new ResultError($error->getMessage(), "PROPERTIES[$key]"));
				$result->addError(new ResultWarning($error->getMessage(), "PROPERTIES[$key]"));
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getProperty(): array
	{
		return $this->property->getFields();
	}

	/**
	 * @return EntityProperty|null
	 */
	public function getPropertyObject(): ?EntityProperty
	{
		return $this->property;
	}

	/**
	 * @return null|string
	 */
	public function getValueId(): ?string
	{
		return $this->getField('ID');
	}

	/**
	 * @return mixed
	 */
	public function getPropertyId()
	{
		return $this->property->getId();
	}

	/**
	 * @return mixed
	 */
	public function getPersonTypeId()
	{
		return $this->property->getPersonTypeId();
	}

	/**
	 * @return mixed
	 */
	public function getGroupId()
	{
		return $this->property->getGroupId();
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->property->getName();
	}

	/**
	 * @return mixed
	 */
	public function getRelations()
	{
		return $this->property->getRelations();
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->property->getDescription();
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->property->getType();
	}

	/**
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->property->isRequired();
	}

	/**
	 * @return bool
	 */
	public function isUtil()
	{
		return $this->property->isUtil();
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return string
	 */
	public static function getRegistryEntity(): string
	{
		return Registry::ENTITY_PROPERTY_VALUE;
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		return OrderPropsValueTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		return OrderPropsValueTable::update($primary, $data);
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result|Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = [])
	{
		return OrderPropsValueTable::getList($parameters);
	}

	/**
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setValue($value)
	{
		return $this->setField('VALUE', $value);
	}

	/**
	 * @return string
	 * @throws Main\SystemException
	 */
	public function getViewHtml()
	{
		return $this->property->getViewHtml($this->getValue());
	}

	/**
	 * @return string
	 * @throws Main\SystemException
	 */
	public function getEditHtml()
	{
		return $this->property->getEditHtml($this->getFieldValues());
	}

	/**
	 * @return null|string|array
	 */
	public function getValue()
	{
		return $this->getField("VALUE");
	}

	/**
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function verify()
	{
		$res = $this->checkValue($this->getPropertyId(), $this->getValue());
		if (!$res->isSuccess())
		{
			$order = $this->getOrder();

			$registry = Registry::getInstance(static::getRegistryType());

			/** @var EntityMarker $entityMarker */
			$entityMarker = $registry->getEntityMarkerClassName();
			$entityMarker::addMarker($order, $this, $res);
		}

		return $res;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	public static function loadForEntity(Entity $entity): array
	{
		[$properties, $propertyValues, $propRelation, $propertyValuesMap] = static::loadFromDb($entity);
		return static::createPropertyValuesObjects($properties, $propertyValues, $propRelation, $propertyValuesMap);
	}

	public static function getTableEntity()
	{
		return \Bitrix\Sale\Internals\OrderPropsValueTable::getEntity();
	}
}
