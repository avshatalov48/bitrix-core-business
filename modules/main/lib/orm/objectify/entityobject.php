<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Objectify;

use ArrayAccess;
use Bitrix\Main\Authentication\Context;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IReadable;
use Bitrix\Main\ORM\Fields\ObjectField;
use Bitrix\Main\ORM\Fields\Relations\CascadePolicy;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Relation;
use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Web\Json;

/**
 * Entity object
 *
 * @property-read \Bitrix\Main\ORM\Entity $entity
 * @property-read array $primary
 * @property-read string $primaryAsString
 * @property-read int $state @see State
 * @property-read Dictionary $customData
 * @property Context $authContext For UF values validation
 *
 * @package    bitrix
 * @subpackage main
 */
abstract class EntityObject implements ArrayAccess
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

	/**
	 * @var Dictionary
	 */
	protected $_customData = null;

	/** @var callable[] */
	protected $_onPrimarySetListeners = [];

	/** @var Context */
	protected $_authContext;

	/** @var bool Save lock */
	protected $_savingInProgress = false;

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

	/**
	 * EntityObject constructor
	 *
	 * @param bool|array $setDefaultValues
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	final public function __construct($setDefaultValues = true)
	{
		if (is_array($setDefaultValues))
		{
			// we have custom default values
			foreach ($setDefaultValues as $fieldName => $defaultValue)
			{
				$field = $this->entity->getField($fieldName);

				if ($field instanceof Reference)
				{
					if (is_array($defaultValue))
					{
						$defaultValue = $field->getRefEntity()->createObject($defaultValue);
					}

					$this->set($fieldName, $defaultValue);
				}
				elseif (($field instanceof OneToMany || $field instanceof ManyToMany)
					&& is_array($defaultValue))
				{
					foreach ($defaultValue as $subValue)
					{
						if (is_array($subValue))
						{
							$subValue = $field->getRefEntity()->createObject($subValue);
						}

						$this->addTo($fieldName, $subValue);
					}
				}
				else
				{
					$this->set($fieldName, $defaultValue);
				}
			}
		}

		// set map default values
		if ($setDefaultValues || is_array($setDefaultValues))
		{
			foreach ($this->entity->getScalarFields() as $fieldName => $field)
			{
				if ($this->sysHasValue($fieldName))
				{
					// already set custom default value
					continue;
				}

				$defaultValue = $field->getDefaultValue($this);

				if ($defaultValue !== null)
				{
					$this->set($fieldName, $defaultValue);
				}
			}
		}
	}

	public function __clone()
	{
		$this->_actualValues = $this->cloneValues($this->_actualValues);
		$this->_currentValues = $this->cloneValues($this->_currentValues);
	}

	protected function cloneValues(array $values): array
	{
		// Do not clone References to avoid infinite recursion
		$valuesWithoutReferences = $this->filterValuesByMask($values, FieldTypeMask::REFERENCE, true);
		$references = array_diff_key($values, $valuesWithoutReferences);

		return array_merge($references, \Bitrix\Main\Type\Collection::clone($valuesWithoutReferences));
	}

	protected function filterValuesByMask(array $values, int $fieldsMask, bool $invertedFilter = false): array
	{
		if ($fieldsMask === FieldTypeMask::ALL)
		{
			return $invertedFilter ? [] : $values;
		}

		return array_filter($values, function($fieldName) use ($fieldsMask, $invertedFilter)
		{
			$maskOfSingleField = $this->entity->getField($fieldName)->getTypeMask();
			$matchesMask = (bool)($fieldsMask & $maskOfSingleField);

			return $invertedFilter ? !$matchesMask: $matchesMask;
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Returns all objects values as an array
	 *
	 * @param int  $valuesType
	 * @param int  $fieldsMask
	 * @param bool $recursive
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	final public function collectValues($valuesType = Values::ALL, $fieldsMask = FieldTypeMask::ALL, $recursive = false)
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

		// recursive convert object to array
		if ($recursive)
		{
			foreach ($objectValues as $fieldName => $value)
			{
				if ($value instanceof EntityObject)
				{
					$objectValues[$fieldName] = $value->collectValues($valuesType, $fieldsMask, $recursive);
				}
				elseif ($value instanceof Collection)
				{
					$arrayCollection = [];
					foreach ($value as $relationObject)
					{
						$arrayCollection[] = $relationObject->collectValues($valuesType, $fieldsMask, $recursive);
					}
					$objectValues[$fieldName] = $arrayCollection;
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
		// default empty result
		switch ($this->state)
		{
			case State::RAW:
				$result = new AddResult;
				break;
			case State::CHANGED:
			case State::ACTUAL:
				$result = new UpdateResult;
				break;
			default:
				$result = new Result;
		}

		if ($this->_savingInProgress)
		{
			return $result;
		}

		$this->_savingInProgress = true;

		$dataClass = $this->entity->getDataClass();

		// check for object fields, it could be changed without notification
		foreach ($this->_currentValues as $fieldName => $currentValue)
		{
			$field = $this->entity->getField($fieldName);

			if ($field instanceof ObjectField)
			{
				$actualValue = $this->_actualValues[$fieldName];

				if ($field->encode($currentValue) !== $field->encode($actualValue))
				{
					if ($this->_state === State::ACTUAL)
					{
						// value has changed, set new state
						$this->_state = State::CHANGED;
					}
				}
				else
				{
					// value has not changed, hide it until postSave
					unset($this->_currentValues[$fieldName]);
				}
			}
		}

		// save data
		if ($this->_state == State::RAW)
		{
			$data = $this->_currentValues;
			$data['__object'] = $this;

			// put secret key __object to array
			$result = $dataClass::add($data);

			// check for error
			if (!$result->isSuccess())
			{
				$this->_savingInProgress = false;

				return $result;
			}

			// set primary
			foreach ($result->getPrimary() as $primaryName => $primaryValue)
			{
				$this->sysSetActual($primaryName, $primaryValue);

				// db value has priority in case of custom value for autocomplete
				$this->sysSetValue($primaryName, $primaryValue);
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
					$this->_savingInProgress = false;

					return $result;
				}
			}
		}

		// changed collections
		$this->sysSaveRelations($result);

		// return if there were errors
		if (!$result->isSuccess())
		{
			return $result;
		}

		$this->sysPostSave();

		$this->_savingInProgress = false;

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
			if ($field instanceof Reference)
			{
				if ($field->getCascadeDeletePolicy() === CascadePolicy::FOLLOW)
				{
					/** @var EntityObject $remoteObject */
					$remoteObject = $this->sysGetValue($field->getName());
					$remoteObject->delete();
				}
			}
			elseif ($field instanceof OneToMany)
			{
				if ($field->getCascadeDeletePolicy() === CascadePolicy::FOLLOW)
				{
					// delete
					$collection = $this->sysFillRelationCollection($field);

					foreach ($collection as $object)
					{
						$object->delete();
					}
				}
				elseif ($field->getCascadeDeletePolicy() === CascadePolicy::SET_NULL)
				{
					// set null
					$this->sysRemoveAllFromCollection($field->getName());
				}
			}
			elseif ($field instanceof ManyToMany)
			{
				if ($field->getCascadeDeletePolicy() === CascadePolicy::FOLLOW_ORPHANS)
				{
					// delete
				}
				elseif ($field->getCascadeDeletePolicy() === CascadePolicy::SET_NULL)
				{
					// set null
				}

				// always delete mediator records
				$this->sysRemoveAllFromCollection($field->getName());
			}
		}

		$this->sysSaveRelations($result);

		// delete object itself
		$dataClass = static::$dataClass;
		$dataClass::setCurrentDeletingObject($this);
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

		$this->sysChangeState(State::DELETED);

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
			$fields = [$fields];
			$fieldsToSelect = $this->sysGetIdleFields($fields);
		}
		else
		{
			// get fields according to selector mask
			$fieldsToSelect = $this->sysGetIdleFieldsByMask($fields);
		}

		if (!empty($fieldsToSelect))
		{
			$fieldsToSelect = array_merge($this->entity->getPrimaryArray(), $fieldsToSelect);

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
	final public function require($fieldName)
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
	final public function unset($fieldName)
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
	final public function has($fieldName)
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
	final public function isFilled($fieldName)
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
	final public function isChanged($fieldName)
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
			case 'primaryAsString':
				return $this->sysGetPrimaryAsString();
			case 'state':
				return $this->sysGetState();
			case 'dataClass':
				throw new SystemException('Property `dataClass` should be received as static.');
			case 'customData':

				if ($this->_customData === null)
				{
					$this->_customData = new Dictionary;
				}

				return $this->_customData;

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
			case 'customData':
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);
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

				if ($field instanceof IReadable && !($value instanceof SqlExpression))
				{
					$value = $field->cast($value);
				}

				return $this->sysSetValue($fieldName, $value);
			}
		}

		if ($first3 == 'has')
		{
			$fieldName = self::sysMethodToFieldCase(substr($name, 3));

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);
				$value = $arguments[1];

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);
				$value = $arguments[1];

				// check if custom method exists
				$personalMethodName = $name.static::sysFieldToMethodCase($fieldName);

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $first2.static::sysFieldToMethodCase($fieldName).$last6;

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

			if ($fieldName == '')
			{
				$fieldName = StringHelper::strtoupper($arguments[0]);

				// check if custom method exists
				$personalMethodName = $first2.static::sysFieldToMethodCase($fieldName).$last7;

				if (method_exists($this, $personalMethodName))
				{
					return $this->$personalMethodName(...array_slice($arguments, 1));
				}

				// hard field check
				$this->entity->getField($fieldName);
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

	public function sysGetPrimaryAsString()
	{
		return static::sysSerializePrimary($this->sysGetPrimary(), $this->_entity);
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
		$fieldName = StringHelper::strtoupper($fieldName);
		$this->_actualValues[$fieldName] = $value;

		// special condition for object values - it should be gotten and changed as current value
		// and actual value will be used for comparison
		if ($this->entity->getField($fieldName) instanceof ObjectField)
		{
			$this->_currentValues[$fieldName] = clone $value;
		}
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
		$fieldName = StringHelper::strtoupper($fieldName);

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
		$fieldName = StringHelper::strtoupper($fieldName);
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
		// exclude UF fields for this check as long as UF file fields look into request to change value
		// (\Bitrix\Main\UserField\Types\FileType::onBeforeSave)
		// let UF manager handle all the values without optimization
		if (array_key_exists($fieldName, $this->_actualValues) && !($field instanceof UserTypeField))
		{
			if ($field instanceof IReadable)
			{
				if ($field->cast($value) === $this->_actualValues[$fieldName]
					// double check if value objects are different, but db values are the same
					|| $field->convertValueToDb($field->modifyValueBeforeSave($value, []))
						=== $field->convertValueToDb($field->modifyValueBeforeSave($this->_actualValues[$fieldName], []))
				)
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
				$elementalsChanged = false;

				foreach ($elementals as $localFieldName => $remoteFieldName)
				{
					if ($this->entity->getField($localFieldName)->isPrimary())
					{
						// skip local primary in non-raw state
						if ($this->state !== State::RAW)
						{
							continue;
						}

						// skip autocomplete
						if ($this->state === State::RAW && $this->entity->getField($localFieldName)->isAutocomplete())
						{
							continue;
						}
					}

					$elementalValue = empty($value) ? null : $value->sysGetValue($remoteFieldName);
					$this->sysSetValue($localFieldName, $elementalValue);

					$elementalsChanged = true;
				}

				if (!$elementalsChanged)
				{
					// object was not changed actually
					return $this;
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
		if ($field instanceof ScalarField && $field->isPrimary() && $this->sysHasPrimary())
		{
			$this->sysOnPrimarySet();
		}

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
		$fieldName = StringHelper::strtoupper($fieldName);
		$field = $this->entity->getField($fieldName);

		if ($field instanceof ObjectField)
		{
			$currentValue = $this->_currentValues[$fieldName];
			$actualValue = $this->_actualValues[$fieldName];

			return $field->encode($currentValue) !== $field->encode($actualValue);
		}

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
			$fieldName = StringHelper::strtoupper($fieldName);

			if (!isset($this->_actualValues[$fieldName]))
			{
				// regular field
				$list[] = $fieldName;
			}
			elseif ($this->_actualValues[$fieldName] instanceof Collection && !$this->_actualValues[$fieldName]->sysIsFilled())
			{
				// non-filled collection
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

			if (!isset($this->_actualValues[StringHelper::strtoupper($field->getName())])
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
		$saveCascade = true;

		foreach ($this->_actualValues as $fieldName => $value)
		{
			$field = $this->entity->getField($fieldName);

			if ($field instanceof Reference)
			{
				if ($saveCascade && !empty($value))
				{
					$value->save();
				}
			}
			elseif ($field instanceof OneToMany)
			{
				$collection = $value;

				/** @var static[] $objectsToSave */
				$objectsToSave = [];

				/** @var static[] $objectsToDelete */
				$objectsToDelete = [];

				if ($collection->sysIsChanged())
				{
					// save changed elements of collection
					foreach ($collection->sysGetChanges() as $change)
					{
						list($remoteObject, $changeType) = $change;

						if ($changeType == Collection::OBJECT_ADDED)
						{
							$objectsToSave[] = $remoteObject;
						}
						elseif ($changeType == Collection::OBJECT_REMOVED)
						{
							if ($field->getCascadeDeletePolicy() == CascadePolicy::FOLLOW)
							{
								$objectsToDelete[] = $remoteObject;
							}
							else
							{
								// set null by default
								$objectsToSave[] = $remoteObject;
							}
						}
					}
				}

				if ($saveCascade)
				{
					// everything should be saved, except deleted
					foreach ($collection->getAll() as $remoteObject)
					{
						if (!in_array($remoteObject, $objectsToDelete) && !in_array($remoteObject, $objectsToSave))
						{
							$objectsToSave[] = $remoteObject;
						}
					}
				}

				// save remote objects
				foreach ($objectsToSave as $remoteObject)
				{
					$remoteResult = $remoteObject->save();

					if (!$remoteResult->isSuccess())
					{
						$result->addErrors($remoteResult->getErrors());
					}
				}

				// delete remote objects
				foreach ($objectsToDelete as $remoteObject)
				{
					$remoteResult = $remoteObject->delete();

					if (!$remoteResult->isSuccess())
					{
						$result->addErrors($remoteResult->getErrors());
					}
				}

				// forget collection changes
				if ($collection->sysIsChanged())
				{
					$collection->sysResetChanges();
				}
			}
			elseif ($field instanceof ManyToMany)
			{
				$collection = $value;

				if ($value->sysIsChanged())
				{
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

				// should everything be saved?
				if ($saveCascade)
				{
					foreach ($collection->getAll() as $remoteObject)
					{
						$remoteResult = $remoteObject->save();

						if (!$remoteResult->isSuccess())
						{
							$result->addErrors($remoteResult->getErrors());
						}
					}
				}
			}

			// remove deleted objects from collections
			if ($value instanceof Collection)
			{
				$value->sysReviseDeletedObjects();
			}
		}
	}

	public function sysPostSave()
	{
		// clear current values
		foreach ($this->_currentValues as $k => $v)
		{
			$field = $this->entity->getField($k);

			// handle references
			if ($v instanceof EntityObject)
			{
				// hold raw references
				if ($v->state === State::RAW)
				{
					continue;
				}

				// move actual or changed
				if ($v->state === State::ACTUAL || $v->state === State::CHANGED)
				{
					$this->sysSetActual($k, $v);
				}
			}
			elseif ($field instanceof ScalarField || $field instanceof UserTypeField)
			{
				$v = $field->cast($v);

				if ($v instanceof SqlExpression)
				{
					continue;
				}

				$this->sysSetActual($k, $v);
			}

			// clear values
			unset($this->_currentValues[$k]);
		}

		// change state
		$this->sysChangeState(State::ACTUAL);

		// return object field to current values
		foreach ($this->_actualValues as $fieldName => $actualValue)
		{
			if ($this->entity->getField($fieldName) instanceof ObjectField)
			{
				$this->_currentValues[$fieldName] = clone $actualValue;
			}
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
	public function sysAddToCollection($fieldName, $remoteObject)
	{
		$fieldName = StringHelper::strtoupper($fieldName);

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

		if ($field instanceof OneToMany)
		{
			// set self to the object
			$remoteFieldName = $field->getRefField()->getName();
			$remoteObject->sysSetValue($remoteFieldName, $this);

			// if we don't have primary right now, repeat setter later
			if ($this->state == State::RAW)
			{
				$localObject = $this;

				$this->sysAddOnPrimarySetListener(function () use ($localObject, $remoteObject, $remoteFieldName) {
					$remoteObject->sysSetValue($remoteFieldName, $localObject);
				});
			}
		}

		/** @var Collection $collection Add to collection */
		$collection->add($remoteObject);

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
		$fieldName = StringHelper::strtoupper($fieldName);

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
			if ($field->getCascadeDeletePolicy() == CascadePolicy::FOLLOW)
			{
				// nothing to do
			}
			else
			{
				// set null by default
				$remoteFieldName = $field->getRefField()->getName();
				$remoteObject->sysSetValue($remoteFieldName, null);
			}

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
		$fieldName = StringHelper::strtoupper($fieldName);
		$collection = $this->sysFillRelationCollection($fieldName);

		// remove one by one
		foreach ($collection as $remoteObject)
		{
			$this->sysRemoveFromCollection($fieldName, $remoteObject);
		}
	}

	/**
	 * @param OneToMany|ManyToMany|string $field
	 *
	 * @return Collection
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sysFillRelationCollection($field)
	{
		if ($field instanceof Relation)
		{
			$fieldName = $field->getName();
		}
		else
		{
			$fieldName = $field;
			$field = $this->entity->getField($fieldName);
		}

		/** @var Collection $collection initialize collection */
		$collection = $this->sysGetValue($fieldName);

		if (empty($collection))
		{
			$collection = $field->getRefEntity()->createCollection();
			$this->_actualValues[$fieldName] = $collection;
		}

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

		return $collection;
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
			static::$_camelToSnakeCache[$methodName] = StringHelper::strtoupper(
				StringHelper::camel2snake($methodName)
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
			static::$_snakeToCamelCache[$fieldName] = StringHelper::snake2camel($fieldName);
		}

		return static::$_snakeToCamelCache[$fieldName];
	}

	/**
	 * @param array $primary
	 * @param Entity $entity
	 *
	 * @return string
	 * @throws ArgumentException
	 */
	public static function sysSerializePrimary($primary, $entity)
	{
		if (count($entity->getPrimaryArray()) == 1)
		{
			return (string) current($primary);
		}

		return (string) Json::encode(array_values($primary));
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
