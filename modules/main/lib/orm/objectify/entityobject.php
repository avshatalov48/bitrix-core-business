<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Objectify;

use Bitrix\Main\Authentication\Context;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IReadable;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;

/**
 * Entity object
 *
 * @property-read \Bitrix\Main\ORM\Entity $entity
 * @property-read array $primary
 * @property-read int $state @see State
 * @property Context $authContext For UF values validation
 *
 * @package    bitrix
 * @subpackage main
 */
abstract class EntityObject implements \ArrayAccess
{
	/**
	 * Entity Table class. Read-only property.
	 * @var DataManager
	 */
	static public $dataClass;

	/** @var Entity */
	protected $_entity;

	/**
	 * @var int
	 * @see State
	 */
	protected $_state = State::RAW;

	/**
	 * Actual values fetched from DB and collections of relations
	 * @var mixed[]|static[]|Collection[]
	 */
	protected $_actualValues = [];

	/**
	 * Current values - new or rewritten by setter (except changed collections - they are still in actual values)
	 * @var mixed[]|static[]
	 */
	protected $_currentValues = [];

	/**
	 * Container for non-entity data
	 * @var mixed[]
	 */
	protected $_runtimeValues = [];

	/** @var callable[] */
	protected $_onPrimarySetListeners = [];

	/** @var Context */
	protected $_authContext;

	/**
	 * Cache for lastName => LAST_NAME transforming
	 * @var string[]
	 */
	static protected $_camelToSnakeCache = [];

	/**
	 * Cache for LAST_NAME => lastName transforming
	 * @var string[]
	 */
	static protected $_snakeToCamelCache = [];

	final public function __construct($setDefaultValues = true)
	{
		if ($setDefaultValues)
		{
			foreach ($this->entity->getScalarFields() as $fieldName => $field)
			{
				$defaultValue = $field->getDefaultValue($this);

				if ($defaultValue !== null)
				{
					$this->sysSetValue($fieldName, $defaultValue);
				}
			}
		}
	}

	/**
	 * Returns all objects values as an array
	 *
	 * @param int $valuesType
	 * @param int $fieldsMask
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function collectValues($valuesType = Values::ALL, $fieldsMask = FieldTypeMask::ALL)
	{
		switch ($valuesType)
		{
			case Values::ACTUAL:
				$objectValues = $this->_actualValues;
				break;
			case Values::CURRENT:
				$objectValues = $this->_currentValues;
				break;
			default:
				$objectValues = array_merge($this->_actualValues, $this->_currentValues);
		}

		// filter with field mask
		if ($fieldsMask !== FieldTypeMask::ALL)
		{
			foreach ($objectValues as $fieldName => $value)
			{
				$fieldMask = $this->entity->getField($fieldName)->getTypeMask();
				if (!($fieldsMask & $fieldMask))
				{
					unset($objectValues[$fieldName]);
				}
			}
		}

		// remap from uppercase to real field names
		$values = [];

		foreach ($objectValues as $k => $v)
		{
			$values[$this->entity->getField($k)->getName()] = $v;
		}

		return $values;
	}

	/**
	 * ActiveRecord save.
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws \Exception
	 */
	final public function save()
	{
		$result = new Result;
		$dataClass = $this->entity->getDataClass();

		if ($this->_state == State::RAW)
		{
			$data = $this->_currentValues;
			$data['__object'] = $this;

			// put secret key __object to array
			$result = $dataClass::add($data);

			// check for error
			if (!$result->isSuccess())
			{
				return $result;
			}

			// set primary
			foreach ($result->getPrimary() as $primaryName => $primaryValue)
			{
				$this->sysSetActual($primaryName, $primaryValue);
			}

			// on primary gain event
			$this->sysOnPrimarySet();
		}
		elseif ($this->_state == State::CHANGED)
		{
			// changed scalar and reference
			if (!empty($this->_currentValues))
			{
				$data = $this->_currentValues;
				$data['__object'] = $this;

				// put secret key __object to array
				$result = $dataClass::update($this->primary, $data);

				// check for error
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}
		else
		{
			// nothing to do
			return $result;
		}

		// set other fields, as long as some values could be added or modified in events
		foreach ($result->getData() as $fieldName => $fieldValue)
		{
			$field = $this->entity->getField($fieldName);

			if ($field instanceof ScalarField)
			{
				$fieldValue = $field->cast($fieldValue);
			}

			$this->sysSetActual($fieldName, $fieldValue);
		}

		// changed collections
		$this->sysSaveRelations($result);

		// return if there were errors
		if (!$result->isSuccess())
		{
			return $result;
		}

		$this->sysPostSave();

		return $result;
	}

	/**
	 * ActiveRecord delete.
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function delete()
	{
		$result = new Result;

		// delete relations
		foreach ($this->entity->getFields() as $field)
		{
			if ($field instanceof OneToMany || $field instanceof ManyToMany)
			{
				$this->sysRemoveAllFromCollection($field->getName());
			}
		}

		$this->sysSaveRelations($result);

		// delete object itself
		$dataClass = static::$dataClass;
		$deleteResult = $dataClass::delete($this->primary);

		if (!$deleteResult->isSuccess())
		{
			$result->addErrors($deleteResult->getErrors());
		}

		// clear status
		foreach ($this->entity->getPrimaryArray()as $primaryName)
		{
			unset($this->_actualValues[$primaryName]);
		}

		$this->sysChangeState(State::RAW);

		return $result;
	}

	/**
	 * Constructs existing object from pre-selected data, including references and relations.
	 *
	 * @param mixed $row Array of [field => value] or single scalar primary value.
	 *
	 * @return static
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public static function wakeUp($row)
	{
		/** @var static $objectClass */
		$objectClass = get_called_class();

		/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass */
		$dataClass = static::$dataClass;

		$entity = $dataClass::getEntity();
		$entityPrimary = $entity->getPrimaryArray();

		// normalize input data and primary
		$primary = [];

		if (!is_array($row))
		{
			// it could be single primary
			if (count($entityPrimary) == 1)
			{
				$primary[$entityPrimary[0]] = $row;
				$row = [];
			}
			else
			{
				throw new ArgumentException(sprintf(
					'Multi-primary for %s was not found', $objectClass
				));
			}
		}
		else
		{
			foreach ($entityPrimary as $primaryName)
			{
				if (!isset($row[$primaryName]))
				{
					throw new ArgumentException(sprintf(
						'Primary %s for %s was not found', $primaryName, $objectClass
					));
				}

				$primary[$primaryName] = $row[$primaryName];
				unset($row[$primaryName]);
			}
		}

		// create object
		/** @var static $object */
		$object = new $objectClass(false); // here go with false to not set default values
		$object->sysChangeState(State::ACTUAL);

		// set primary
		foreach ($primary as $primaryName => $primaryValue)
		{
			/** @var ScalarField $primaryField */
			$primaryField = $entity->getField($primaryName);
			$object->sysSetActual($primaryName, $primaryField->cast($primaryValue));
		}

		// set other data
		foreach ($row as $fieldName => $value)
		{
			/** @var ScalarField $primaryField */
			$field = $entity->getField($fieldName);

			if ($field instanceof IReadable)
			{
				$object->sysSetActual($fieldName, $field->cast($value));
			}
			else
			{
				// we have a relation
				if ($value instanceof static || $value instanceof Collection)
				{
					// it is ready data
					$object->sysSetActual($fieldName, $value);
				}
				else
				{
					// wake up relation
					if ($field instanceof Reference)
					{
						// wake up an object
						$remoteObjectClass = $field->getRefEntity()->getObjectClass();
						$remoteObject = $remoteObjectClass::wakeUp($value);

						$object->sysSetActual($fieldName, $remoteObject);
					}
					elseif ($field instanceof OneToMany || $field instanceof ManyToMany)
					{
						// wake up collection
						$remoteCollectionClass = $field->getRefEntity()->getCollectionClass();
						$remoteCollection = $remoteCollectionClass::wakeUp($value);

						$object->sysSetActual($fieldName, $remoteCollection);
					}
				}
			}
		}

		return $object;
	}

	/**
	 * Fills all the values and relations of object
	 *
	 * @param int|string[] $fields Names of fields to fill
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function fill($fields = FieldTypeMask::ALL)
	{
		// object must have primary
		$primaryFilter = Query::filter();

		foreach ($this->sysRequirePrimary() as $primaryName => $primaryValue)
		{
			$primaryFilter->where($primaryName, $primaryValue);
		}

		// collect fields to be selected
		if (is_array($fields))
		{
			// go through IDLE fields
			$fieldsToSelect = $this->sysGetIdleFields($fields);
		}
		elseif (is_scalar($fields) && !is_numeric($fields))
		{
			// one custom field
			$fieldsToSelect = $this->sysGetIdleFields([$fields]);
		}
		else
		{
			// get fields according to selector mask
			$fieldsToSelect = $this->sysGetIdleFieldsByMask($fields);
		}

		if (!empty($fieldsToSelect))
		{
			$fieldsToSelect = array_merge($fieldsToSelect, $this->entity->getPrimaryArray());

			// build query
			$dataClass = $this->entity->getDataClass();
			$result = $dataClass::query()->setSelect($fieldsToSelect)->where($primaryFilter)->exec();

			// set object to identityMap of result, and it will be partially completed by fetch
			$im = new IdentityMap;
			$im->put($this);

			$result->setIdentityMap($im);
			$result->fetchObject();

			// set filled flag to collections
			foreach ($fieldsToSelect as $fieldName)
			{
				// check field before continue, it could be remote REF.ID definition so we skip it here
				if ($this->entity->hasField($fieldName))
				{
					$field = $this->entity->getField($fieldName);

					if ($field instanceof OneToMany || $field instanceof ManyToMany)
					{
						/** @var Collection $collection */
						$collection = $this->sysGetValue($fieldName);

						if (empty($collection))
						{
							$collection = $field->getRefEntity()->createCollection();
							$this->_actualValues[$fieldName] = $collection;
						}

						$collection->sysSetFilled();
					}
				}
			}
		}

		// return field value it it was only one
		if (is_array($fields) && count($fields) == 1 && $this->entity->hasField(current($fields)))
		{
			return $this->sysGetValue(current($fields));
		}

		return null;
	}

	/**
	 * Fast popular alternative to __call().
	 *
	 * @return Collection|EntityObject|mixed
	 * @throws SystemException
	 */
	public function getId()
	{
		if (array_key_exists('ID', $this->_currentValues))
		{
			return $this->_currentValues['ID'];
		}
		elseif (array_key_exists('ID', $this->_actualValues))
		{
			return $this->_actualValues['ID'];
		}
		elseif (!$this->entity->hasField('ID'))
		{
			throw new SystemException(sprintf(
				'Unknown method `%s` for object `%s`', 'getId', get_called_class()
			));
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function get($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function remindActual($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	/* TODO PHP7 ONLY
	final public function require($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}*/

	/**
	 * @param $fieldName
	 * @param $value
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function set($fieldName, $value)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function reset($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	/* TODO PHP7 ONLY
	final public function unset($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}*/

	/**
	 * @param $fieldName
	 * @param $value
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function addTo($fieldName, $value)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 * @param $value
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function removeFrom($fieldName, $value)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * @param $fieldName
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function removeAll($fieldName)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	final public function defineAuthContext(Context $authContext)
	{
		$this->_authContext = $authContext;
	}

	/**
	 * Magic read-only properties
	 *
	 * @param $name
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'entity':
				return $this->sysGetEntity();
			case 'primary':
				return $this->sysGetPrimary();
			case 'state':
				return $this->sysGetState();
			case 'dataClass':
				throw new SystemException('Property `dataClass` should be received as static.');
			case 'authContext':
				return $this->_authContext;
		}

		throw new SystemException(sprintf(
			'Unknown property `%s` for object `%s`', $name, get_called_class()
		));
	}

	/**
	 * Magic read-only properties
	 *
	 * @param $name
	 * @param $value
	 *
	 * @throws SystemException
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authContext':
				return $this->defineAuthContext($value);
			case 'entity':
			case 'primary':
			case 'dataClass':
			case 'state':
				throw new SystemException(sprintf(
					'Property `%s` for object `%s` is read-only', $name, get_called_class()
				));
		}

		throw new SystemException(sprintf(
			'Unknown property `%s` for object `%s`', $name, get_called_class()
		));
	}

	/**
	 * Magic to handle getters, setters etc.
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function __call($name, $arguments)
	{
		$first3 = substr($name, 0, 3);

		// regular getter
		if ($first3 == 'get')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 3));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check runtime
				if (array_key_exists($fieldName, $this->_runtimeValues))
				{
					return $this->sysGetRuntime($fieldName);
				}

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
			}

			// check if field exists
			if ($this->entity->hasField($fieldName))
			{
				return $this->sysGetValue($fieldName);
			}
		}

		// regular setter
		if ($first3 == 'set')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 3));
			$value = $arguments[0];

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);
				$value = $arguments[1];

				// check for runtime field
				if (array_key_exists($fieldName, $this->_runtimeValues))
				{
					throw new SystemException(sprintf(
						'Setting value for runtime field `%s` in `%s` is not allowed, it is read-only field',
						$fieldName, get_called_class()
					));
				}

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
			}

			// check if field exists
			if ($this->entity->hasField($fieldName))
			{
				$field = $this->entity->getField($fieldName);

				if ($field instanceof IReadable)
				{
					$value = $field->cast($value);
				}

				return $this->sysSetValue($fieldName, $value);
			}
		}

		if ($first3 == 'has')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 3));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				return $this->sysHasValue($fieldName);
			}
		}

		$first4 = substr($name, 0, 4);

		// filler
		if ($first4 == 'fill')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 4));

			// no custom/personal method for fill

			// check if field exists
			if ($this->entity->hasField($fieldName))
			{
				return $this->fill([$fieldName]);
			}
		}

		$first5 = substr($name, 0, 5);

		// relation adder
		if ($first5 == 'addTo')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 5));
			$value = $arguments[0];

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);
				$value = $arguments[1];

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				return $this->sysAddToCollection($fieldName, $value);
			}
		}

		// unsetter
		if ($first5 == 'unset')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 5));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				return $this->sysUnset($fieldName);
			}
		}

		// resetter
		if ($first5 == 'reset')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 5));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				$field = $this->entity->getField($fieldName);

				if ($field instanceof OneToMany || $field instanceof ManyToMany)
				{
					return $this->sysResetRelation($fieldName);
				}
				else
				{
					return $this->sysReset($fieldName);
				}
			}
		}

		$first9 = substr($name, 0, 9);

		// relation mass remover
		if ($first9 == 'removeAll')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 9));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				return $this->sysRemoveAllFromCollection($fieldName);
			}
		}

		$first10 = substr($name, 0, 10);

		// relation remover
		if ($first10 == 'removeFrom')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 10));
			$value = $arguments[0];

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);
				$value = $arguments[1];

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				return $this->sysRemoveFromCollection($fieldName, $value);
			}
		}

		$first12 = substr($name, 0, 12);

		// actual value getter
		if ($first12 == 'remindActual')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 12));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
			}

			// check if field exists
			if ($this->entity->hasField($fieldName))
			{
				return $this->_actualValues[$fieldName];
			}
		}

		$first7 = substr($name, 0, 7);

		// strict getter
		if ($first7 == 'require')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 7));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
			}

			// check if field exists
			if ($this->entity->hasField($fieldName))
			{
				return $this->sysGetValue($fieldName, true);
			}
		}

		$first2 = substr($name, 0, 2);
		$last6 = substr($name, -6);

		// actual value checker
		if ($first2 == 'is' && $last6 =='Filled')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 2, -6));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $first2.static::sysFieldToMethodCase($fieldName).$last6;

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				$field = $this->entity->getField($fieldName);

				if ($field instanceof OneToMany || $field instanceof ManyToMany)
				{
					return array_key_exists($fieldName, $this->_actualValues) && $this->_actualValues[$fieldName]->sysIsFilled();
				}
				else
				{
					return $this->sysIsFilled($fieldName);
				}
			}
		}

		$last7 = substr($name, -7);

		// runtime value checker
		if ($first2 == 'is' && $last7 == 'Changed')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 2, -7));

			if (!strlen($fieldName))
			{
				$fieldName = strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $first2.static::sysFieldToMethodCase($fieldName).$last7;

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}
			}

			if ($this->entity->hasField($fieldName))
			{
				$field = $this->entity->getField($fieldName);

				if ($field instanceof OneToMany || $field instanceof ManyToMany)
				{
					return array_key_exists($fieldName, $this->_actualValues) && $this->_actualValues[$fieldName]->sysIsChanged();
				}
				else
				{
					return $this->sysIsChanged($fieldName);
				}
			}
		}

		throw new SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $name, get_called_class()
		));
	}

	/**
	 * @return Entity
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function sysGetEntity()
	{
		if ($this->_entity === null)
		{
			/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass */
			$dataClass = static::$dataClass;
			$this->_entity = $dataClass::getEntity();
		}

		return $this->_entity;
	}

	/**
	 * Returns [primary => value] array.
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysGetPrimary()
	{
		$primaryValues = [];

		foreach ($this->entity->getPrimaryArray() as $primaryName)
		{
			$primaryValues[$primaryName] = $this->sysGetValue($primaryName);
		}

		return $primaryValues;
	}

	/**
	 * Query Runtime Field values or just any runtime value getter
	 * @internal For internal system usage only.
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function sysGetRuntime($name)
	{
		return $this->_runtimeValues[$name];
	}

	/**
	 * Any runtime value setter
	 * @internal For internal system usage only.
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return $this
	 */
	public function sysSetRuntime($name, $value)
	{
		$this->_runtimeValues[$name] = $value;

		return $this;
	}

	/**
	 * Sets actual value.
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 * @param $value
	 */
	public function sysSetActual($fieldName, $value)
	{
		$this->_actualValues[strtoupper($fieldName)] = $value;
	}

	/**
	 * Changes state.
	 * @see State
	 * @internal For internal system usage only.
	 *
	 * @param $state
	 */
	public function sysChangeState($state)
	{
		if ($this->_state !== $state)
		{
			/* not sure if we need check or changes here
			if ($state == State::RAW)
			{
				// actual should be empty
			}
			elseif ($state == State::ACTUAL)
			{
				// runtime values should be empty
			}
			elseif ($state == State::CHANGED)
			{
				// runtime values should not be empty
			}*/

			$this->_state = $state;
		}

	}

	/**
	 * Returns current state.
	 * @see State
	 * @internal For internal system usage only.
	 *
	 * @return int
	 */
	public function sysGetState()
	{
		return $this->_state;
	}

	/**
	 * Regular getter, called by __call.
	 * @internal For internal system usage only.
	 *
	 * @param string $fieldName
	 * @param bool $require Throws an exception in the absence of value
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public function sysGetValue($fieldName, $require = false)
	{
		$fieldName = strtoupper($fieldName);

		if (array_key_exists($fieldName, $this->_currentValues))
		{
			return $this->_currentValues[$fieldName];
		}
		else
		{
			if ($require && !array_key_exists($fieldName, $this->_actualValues))
			{
				throw new SystemException(sprintf(
					'%s value is required for further operations', $fieldName
				));
			}

			return isset($this->_actualValues[$fieldName])
				? $this->_actualValues[$fieldName]
				: null;
		}
	}

	/**
	 * Regular setter, called by __call. Doesn't validate values.
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 * @param $value
	 *
	 * @return $this
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysSetValue($fieldName, $value)
	{
		$fieldName = strtoupper($fieldName);
		$field = $this->entity->getField($fieldName);

		// system validations
		if ($field instanceof ScalarField)
		{
			// restrict updating primary
			if ($this->_state !== State::RAW && in_array($field->getName(), $this->entity->getPrimaryArray()))
			{
				throw new SystemException(sprintf(
					'Setting value for Primary `%s` in `%s` is not allowed, it is read-only field',
					$field->getName(), get_called_class()
				));
			}
		}

		// no setter for expressions
		if ($field instanceof ExpressionField && !($field instanceof UserTypeField))
		{
			throw new SystemException(sprintf(
				'Setting value for ExpressionField `%s` in `%s` is not allowed, it is read-only field',
				$fieldName, get_called_class()
			));
		}

		if ($field instanceof Reference)
		{
			if (!empty($value))
			{
				// validate object class and skip null
				$remoteObjectClass = $field->getRefEntity()->getObjectClass();

				if (!($value instanceof $remoteObjectClass))
				{
					throw new ArgumentException(sprintf(
						'Expected instance of `%s`, got `%s` instead', $remoteObjectClass, get_class($value)
					));
				}
			}
		}

		// change only if value is different from actual
		if (array_key_exists($fieldName, $this->_actualValues))
		{
			if ($field instanceof IReadable)
			{
				if ($field->cast($value) === $this->_actualValues[$fieldName])
				{
					// forget previous runtime change
					unset($this->_currentValues[$fieldName]);
					return $this;
				}
			}
			elseif ($field instanceof Reference)
			{
				/** @var static $value */
				if ($value->primary === $this->_actualValues[$fieldName]->primary)
				{
					// forget previous runtime change
					unset($this->_currentValues[$fieldName]);
					return $this;
				}
			}
		}

		// set value
		if ($field instanceof ScalarField || $field instanceof UserTypeField)
		{
			$this->_currentValues[$fieldName] = $value;
		}
		elseif ($field instanceof Reference)
		{
			/** @var static $value */
			$this->_currentValues[$fieldName] = $value;

			// set elemental fields if there are any
			$elementals = $field->getElementals();

			if (!empty($elementals))
			{
				foreach ($elementals as $localFieldName => $remoteFieldName)
				{
					$elementalValue = empty($value) ? null : $value->sysGetValue($remoteFieldName);
					$this->sysSetValue($localFieldName, $elementalValue);
				}
			}
		}
		else
		{
			throw new SystemException(sprintf(
				'Unknown field type `%s` in system setter of `%s`', get_class($field), get_called_class()
			));
		}

		if ($this->_state == State::ACTUAL)
		{
			$this->sysChangeState(State::CHANGED);
		}

		// on primary gain event
		$this->sysOnPrimarySet();

		return $this;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return bool
	 */
	public function sysHasValue($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		return $this->sysIsFilled($fieldName) || $this->sysIsChanged($fieldName);
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return bool
	 */
	public function sysIsFilled($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		return array_key_exists($fieldName, $this->_actualValues);
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return bool
	 */
	public function sysIsChanged($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		return array_key_exists($fieldName, $this->_currentValues);
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @return bool
	 */
	public function sysHasPrimary()
	{
		foreach ($this->primary as $primaryValue)
		{
			if ($primaryValue === null)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @internal For internal system usage only.
	 */
	public function sysOnPrimarySet()
	{
		// call subscribers
		if ($this->sysHasPrimary())
		{
			foreach ($this->_onPrimarySetListeners as $listener)
			{
				call_user_func($listener, $this);
			}
		}
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param callable $callback
	 */
	public function sysAddOnPrimarySetListener($callback)
	{
		// add to listeners
		$this->_onPrimarySetListeners[] = $callback;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return $this
	 */
	public function sysUnset($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		unset($this->_currentValues[$fieldName]);
		unset($this->_actualValues[$fieldName]);

		return $this;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return $this
	 */
	public function sysReset($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		unset($this->_currentValues[$fieldName]);

		return $this;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return $this
	 */
	public function sysResetRelation($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		if (isset($this->_actualValues[$fieldName]))
		{
			/** @var Collection $collection */
			$collection = $this->_actualValues[$fieldName];
			$collection->sysResetChanges(true);
		}

		return $this;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysRequirePrimary()
	{
		$primaryValues = [];

		foreach ($this->entity->getPrimaryArray() as $primaryName)
		{
			try
			{
				$primaryValues[$primaryName] = $this->sysGetValue($primaryName, true);
			}
			catch (SystemException $e)
			{
				throw new SystemException(sprintf(
					'Primary `%s` value is required for further operations', $primaryName
				));
			}
		}

		return $primaryValues;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * Returns non-filled field names according to array of $fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function sysGetIdleFields($fields = [])
	{
		$list = [];

		if (empty($fields))
		{
			// all fields by default
			$fields = array_keys($this->entity->getFields());
		}

		foreach ($fields as $fieldName)
		{
			if (!isset($this->_actualValues[strtoupper($fieldName)]))
			{
				$list[] = $fieldName;
			}
		}

		return $list;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * Returns non-filled field names according to $mask
	 *
	 * @param int $mask
	 *
	 * @return array
	 */
	public function sysGetIdleFieldsByMask($mask = FieldTypeMask::ALL)
	{
		$list = [];

		foreach ($this->entity->getFields() as $field)
		{
			$fieldMask = $field->getTypeMask();

			if (!isset($this->_actualValues[strtoupper($field->getName())])
				&& ($mask & $fieldMask)
			)
			{
				$list[] = $field->getName();
			}
		}

		return $list;
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param Result $result
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysSaveRelations(Result $result)
	{
		foreach ($this->_actualValues as $fieldName => $value)
		{
			$field = $this->entity->getField($fieldName);

			if ($field instanceof OneToMany && $value->sysIsChanged())
			{
				// save changed elements of collection
				$collection = $value;

				foreach ($collection->sysGetChanges() as $change)
				{
					list($remoteObject,) = $change;

					// no matter what changeType is, just save the remote object
					// elementals will be changed after add or nulled after remove
					/** @var static $remoteObject */
					$remoteResult = $remoteObject->save();

					if (!$remoteResult->isSuccess())
					{
						$result->addErrors($remoteResult->getErrors());
					}
				}

				// forget collection changes
				$collection->sysResetChanges();
			}
			elseif ($field instanceof ManyToMany && $value->sysIsChanged())
			{
				$collection = $value;

				foreach ($collection->sysGetChanges() as $change)
				{
					list($remoteObject, $changeType) = $change;

					// initialize mediator object
					$mediatorObjectClass = $field->getMediatorEntity()->getObjectClass();
					$localReferenceName = $field->getLocalReferenceName();
					$remoteReferenceName = $field->getRemoteReferenceName();

					/** @var static $mediatorObject */
					$mediatorObject = new $mediatorObjectClass;
					$mediatorObject->sysSetValue($localReferenceName, $this);
					$mediatorObject->sysSetValue($remoteReferenceName, $remoteObject);

					// add or remove mediator depending on changeType
					if ($changeType == Collection::OBJECT_ADDED)
					{
						$mediatorObject->save();
					}
					elseif ($changeType == Collection::OBJECT_REMOVED)
					{
						// destroy directly through data class
						$mediatorDataClass = $field->getMediatorEntity()->getDataClass();
						$mediatorDataClass::delete($mediatorObject->primary);
					}
				}

				// forget collection changes
				$collection->sysResetChanges();
			}
		}
	}

	public function sysPostSave()
	{
		// clear current values
		$this->_currentValues = [];

		// change state
		$this->sysChangeState(State::ACTUAL);
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 * @param $remoteObject
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysAddToCollection($fieldName, $remoteObject)
	{
		$fieldName = strtoupper($fieldName);

		/** @var OneToMany $field */
		$field = $this->entity->getField($fieldName);
		$remoteObjectClass = $field->getRefEntity()->getObjectClass();

		// validate object class
		if (!($remoteObject instanceof $remoteObjectClass))
		{
			throw new ArgumentException(sprintf(
				'Expected instance of `%s`, got `%s` instead', $remoteObjectClass, get_class($remoteObject)
			));
		}

		// initialize collection
		$collection = $this->sysGetValue($fieldName);

		if (empty($collection))
		{
			$collection = $field->getRefEntity()->createCollection();
			$this->_actualValues[$fieldName] = $collection;
		}

		// add to collection
		$collection->add($remoteObject);

		if ($field instanceof OneToMany)
		{
			// set self to the object
			$remoteFieldName = $field->getRefField()->getName();
			$remoteObject->sysSetValue($remoteFieldName, $this);
		}

		// mark object as changed
		if ($this->_state == State::ACTUAL)
		{
			$this->sysChangeState(State::CHANGED);
		}
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 * @param $remoteObject
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysRemoveFromCollection($fieldName, $remoteObject)
	{
		$fieldName = strtoupper($fieldName);

		/** @var OneToMany $field */
		$field = $this->entity->getField($fieldName);
		$remoteObjectClass = $field->getRefEntity()->getObjectClass();

		// validate object class
		if (!($remoteObject instanceof $remoteObjectClass))
		{
			throw new ArgumentException(sprintf(
				'Expected instance of `%s`, got `%s` instead', $remoteObjectClass, get_class($remoteObject)
			));
		}

		/** @var Collection $collection Initialize collection */
		$collection = $this->sysGetValue($fieldName);

		if (empty($collection))
		{
			$collection = $field->getRefEntity()->createCollection();
			$this->_actualValues[$fieldName] = $collection;
		}

		// remove from collection
		$collection->remove($remoteObject);

		if ($field instanceof OneToMany)
		{
			// remove self from the object
			$remoteFieldName = $field->getRefField()->getName();
			$remoteObject->sysSetValue($remoteFieldName, null);
		}

		// mark object as changed
		if ($this->_state == State::ACTUAL)
		{
			$this->sysChangeState(State::CHANGED);
		}
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysRemoveAllFromCollection($fieldName)
	{
		$fieldName = strtoupper($fieldName);

		/** @var OneToMany|ManyToMany $field */
		$field = $this->entity->getField($fieldName);

		/** @var Collection $collection initialize collection */
		$collection = $this->sysGetValue($fieldName);

		if (empty($collection))
		{
			$collection = $field->getRefEntity()->createCollection();
			$this->_actualValues[$fieldName] = $collection;
		}

		// check collection fullness
		if (!$collection->sysIsFilled())
		{
			// we need only primary here
			$remotePrimaryDefinitions = [];

			foreach ($field->getRefEntity()->getPrimaryArray() as $primaryName)
			{
				$remotePrimaryDefinitions[] = $fieldName.'.'.$primaryName;
			}

			$this->fill($remotePrimaryDefinitions);

			// we can set fullness flag here
			$collection->sysSetFilled();
		}

		// remove one by one
		foreach ($collection as $remoteObject)
		{
			$this->sysRemoveFromCollection($fieldName, $remoteObject);
		}
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $methodName
	 *
	 * @return string
	 */
	public static function sysMethodToFieldCase($methodName)
	{
		if (!isset(static::$_camelToSnakeCache[$methodName]))
		{
			static::$_camelToSnakeCache[$methodName] = strtoupper(
				preg_replace('/(.)([A-Z])/', '$1_$2', $methodName)
			);
		}

		return static::$_camelToSnakeCache[$methodName];
	}

	/**
	 * @internal For internal system usage only.
	 *
	 * @param $fieldName
	 *
	 * @return string
	 */
	public static function sysFieldToMethodCase($fieldName)
	{
		if (!isset(static::$_snakeToCamelCache[$fieldName]))
		{
			static::$_snakeToCamelCache[$fieldName] = str_replace(' ', '', ucwords(
					str_replace('_', ' ', strtolower($fieldName))
			));
		}

		return static::$_snakeToCamelCache[$fieldName];
	}

	/**
	 * ArrayAccess interface implementation.
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function offsetExists($offset)
	{
		return $this->sysHasValue($offset) && $this->sysGetValue($offset) !== null;
	}

	/**
	 * ArrayAccess interface implementation.
	 *
	 * @param mixed $offset
	 *
	 * @return mixed|null
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			// regular field
			return $this->get($offset);
		}
		elseif (array_key_exists($offset, $this->_runtimeValues))
		{
			// runtime field
			return $this->sysGetRuntime($offset);
		}

		return $this->offsetExists($offset) ? $this->get($offset) : null;
	}

	/**
	 * ArrayAccess interface implementation.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			throw new ArgumentException('Field name should be set');
		}
		else
		{
			$this->set($offset, $value);
		}
	}

	/**
	 * ArrayAccess interface implementation.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		$this->unset($offset);
	}
}
