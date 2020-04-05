<?php

namespace Bitrix\Sale;

use	Bitrix\Sale\Internals\Input;
use Bitrix\Main;

/**
 * Class PropertyValueBase
 * @package Bitrix\Sale
 */
abstract class PropertyValueBase extends Internals\CollectableEntity
{
	/** @var PropertyBase|null $property */
	protected $property = null;

	/**
	 * PropertyValueBase constructor.
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @throws Main\SystemException
	 */
	protected function __construct(array $property = null, array $value = null, array $relation = null)
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
			$property = array(
				'TYPE' => 'STRING',
				'PROPS_GROUP_ID' => 0,
				'NAME' => $value['NAME'],
				'CODE' => $value['CODE'],
			);
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();

		$this->property = new $propertyClassName($property, $relation);

		if (!$value)
		{
			$value = array(
				'ORDER_PROPS_ID' => $this->property->getId(),
				'NAME' => $this->property->getName(),
				'CODE' => $this->property->getField('CODE'),
				'XML_ID' => static::generateXmlId()
			);
		}

		if (isset($value['VALUE']))
		{
			$value['VALUE'] = $this->property->normalizeValue($value['VALUE']);
		}

		parent::__construct($value);

		if (!isset($value['VALUE']) && !empty($this->property->getField('DEFAULT_VALUE')))
		{
			$this->setFieldNoDemand('VALUE', $this->property->getField('DEFAULT_VALUE'));
		}
	}

	/**
	 * @return string
	 */
	public static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param OrderBase $order
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadForOrder(OrderBase $order)
	{
		$result = [];

		$propertyValues = [];
		$propertyValuesMap = [];

		if ($order->getId() > 0)
		{
			$dbRes = static::getList(array(
				'select' => array('ID', 'NAME', 'VALUE', 'CODE', 'ORDER_PROPS_ID'),
				'filter' => array('ORDER_ID' => $order->getId())
			));
			while ($row = $dbRes->fetch())
			{
				$propertyValues[$row['ID']] = $row;
				$propertyValuesMap[$row['ORDER_PROPS_ID']] = $row['ID'];
			}
		}

		$filter = array();
		if ($order->getPersonTypeId() > 0)
		{
			$filter['=PERSON_TYPE_ID'] = $order->getPersonTypeId();
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();

		$dbRes = $propertyClassName::getList(array(
			'select' => array('ID', 'PERSON_TYPE_ID', 'NAME', 'TYPE', 'REQUIRED', 'DEFAULT_VALUE', 'SORT',
				'USER_PROPS', 'IS_LOCATION', 'PROPS_GROUP_ID', 'DESCRIPTION', 'IS_EMAIL', 'IS_PROFILE_NAME',
				'IS_PAYER', 'IS_LOCATION4TAX', 'IS_FILTERED', 'CODE', 'IS_ZIP', 'IS_PHONE', 'IS_ADDRESS',
				'ACTIVE', 'UTIL', 'INPUT_FIELD_LOCATION', 'MULTIPLE', 'SETTINGS'
			),
			'filter' => $filter,
			'order' => array('SORT' => 'ASC')
		));

		$properties = array();
		$propRelation = array();
		while ($row = $dbRes->fetch())
		{
			$properties[$row['ID']] = $row;
			$propRelation[$row['ID']] = [];
		}

		$dbRes = Internals\OrderPropsRelationTable::getList(array(
			'select' => [
				'PROPERTY_ID', 'ENTITY_ID', 'ENTITY_TYPE'
			],
			'filter' => [
				'PROPERTY_ID' => array_keys($properties)
			]
		));

		while ($row = $dbRes->fetch())
		{
			$propRelation[$row['PROPERTY_ID']][] = $row;
		}

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
				if ($property['ACTIVE'] == 'N')
				{
					continue;
				}

				$fields = null;
			}

			$result[] = static::createPropertyValueObject($property, $fields, $propRelation[$id]);
		}

		foreach ($propertyValues as $propertyValue)
		{
			$result[] = static::createPropertyValueObject(null, $propertyValue);
		}

		return $result;
	}

	/**
	 * @param PropertyValueCollectionBase $collection
	 * @param array $property
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function create(PropertyValueCollectionBase $collection, array $property = array())
	{
		$propertyValue = static::createPropertyValueObject($property);
		$propertyValue->setCollection($collection);

		return $propertyValue;
	}

	/**
	 * @param array|null $property
	 * @param array|null $value
	 * @param array|null $relation
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected static function createPropertyValueObject(array $property = null, array $value = null, array $relation = null)
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueClassName = $registry->getPropertyValueClassName();

		return new $propertyValueClassName($property, $value, $relation);
	}

	/**
	 * @return int
	 */
	private function getParentOrderId()
	{
		/** @var PropertyValueCollectionBase $collection */
		$collection = $this->getCollection();
		$order = $collection->getOrder();

		return $order->getId();
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array('VALUE');
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array();
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	public function setField($name, $value)
	{
		$result = new Result();

		$value = $this->property->normalizeValue($value);

		$r = parent::setField($name, $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		if ($this->getId() > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$this->callEventOnPropertyValueEntitySaved();

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\NotImplementedException
	 */
	protected function update()
	{
		$result = new Result();

		$value = $this->property->prepareValueBeforeSave($this->fields->get('VALUE'));

		$r = static::updateInternal($this->getId(), array('VALUE' => $value));
		if ($r->isSuccess())
		{
			$result->setId($r->getId());
		}
		else
		{
			$result->addErrors($r->getErrors());
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

		$originalFields = $this->fields->getOriginalValues();

		$value = $this->property->prepareValueBeforeSave($this->fields->get('VALUE'), $originalFields['VALUE']);

		$r = static::addInternal(
			array(
				'ORDER_ID' => $this->getParentOrderId(),
				'ORDER_PROPS_ID' => $this->property->getId(),
				'NAME' => $this->property->getName(),
				'VALUE' => $value,
				'CODE' => $this->property->getField('CODE'),
				'XML_ID' => $this->getField('XML_ID'),
			)
		);

		if ($r->isSuccess())
		{
			$this->setFieldNoDemand('ID', $r->getId());
			$result->setId($r->getId());
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return void
	 */
	private function callEventOnPropertyValueEntitySaved()
	{
		$eventName = static::getEntityEventName();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'On'.$eventName.'EntitySaved', array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		));

		$event->send();
	}

	/**
	 * @param array $post
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setValueFromPost(array $post)
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

		$r = $this->property->checkValue($value);
		if (!$r->isSuccess())
		{
			$errors = $r->getErrors();
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

		$r = $this->property->checkRequiredValue($value);
		if (!$r->isSuccess())
		{
			$errors = $r->getErrors();
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
	public function getProperty()
	{
		return $this->property->getFields();
	}

	/**
	 * @return PropertyBase|null
	 */
	public function getPropertyObject()
	{
		return $this->property;
	}

	/**
	 * @return null|string
	 */
	public function getValueId()
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
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\AddResult
	 */
	abstract protected function addInternal(array $data);

	/**
	 * @param $primary
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\UpdateResult
	 */
	abstract protected function updateInternal($primary, array $data);

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setValue($value)
	{
		$this->setField('VALUE', $value);
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
	 * @return null|string
	 */
	public function getValue()
	{
		return $this->getField("VALUE");
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Sale\Property::getOptions
	 *
	 * @param $propertyId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadOptions($propertyId)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		$property = $propertyClassName::getObjectById($propertyId);

		return $property->getOptions();
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Sale\Property::getMeaningfulValues
	 *
	 * @param $personTypeId
	 * @param $request
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getMeaningfulValues($personTypeId, $request)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		return $propertyClassName::getMeaningfulValues($personTypeId, $request);
	}
}
