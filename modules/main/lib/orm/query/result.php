<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\ArrayResult;
use \Bitrix\Main\DB\Result as BaseResult;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\IReadable;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\IdentityMap;
use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\SystemException;

/**
 * Decorates base Result, adds new method fetchObject().
 * @package    bitrix
 * @subpackage main
 */
class Result extends BaseResult
{
	/** @var BaseResult */
	protected $result;

	/** @var Query */
	protected $query;

	/** @var Chain[][] Result chains map by entity path */
	protected $selectChainsMap = [];

	/** @var EntityObject|string Cache for object class of init entity */
	protected $objectClass;

	/** @var IdentityMap */
	protected $identityMap;

	/** @var bool Status of base object fetching initialization */
	protected $objectInitPassed = false;

	/** @var array Column names (chain aliases) of primary fields in result */
	protected $primaryAliases = [];

	/** @var string[] Fields available for for fetchObject, but hidden for fetch */
	protected $hiddenObjectFields;

	public function __construct(Query $query, BaseResult $result)
	{
		$this->query = $query;
		$this->result = $result;
	}

	/**
	 * @param string[] $hiddenObjectFields
	 */
	public function setHiddenObjectFields($hiddenObjectFields)
	{
		$this->hiddenObjectFields = $hiddenObjectFields;
	}

	protected function hideObjectFields(&$row)
	{
		foreach ($this->hiddenObjectFields as $fieldName)
		{
			unset($row[$fieldName]);
		}

		return $row;
	}

	public function getFields()
	{
		return $this->result->getFields();
	}

	public function getSelectedRowsCount()
	{
		return $this->result->getSelectedRowsCount();
	}

	protected function fetchRowInternal()
	{
		return $this->result->fetchRowInternal();
	}

	/**
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	final public function fetchObject()
	{
		// TODO when join, add primary and hide it in ARRAY result, but use for OBJECT fetch
		// e.g. when first fetchObject, remove data modifier that cuts 'unexpected' primary fields

		// TODO wakeup reference objects with only primary if there are enough data in result

		// base object initialization
		$this->initializeFetchObject();

		// array data
		$row = $this->result->fetch();

		if (empty($row))
		{
			return null;
		}

		if (is_object($row) && $row instanceof EntityObject)
		{
			// all rows has already been fetched in initializeFetchObject
			return $row;
		}

		// get primary of base object
		$basePrimaryValues = [];

		foreach ($this->primaryAliases as $primaryName => $primaryAlias)
		{
			/** @var ScalarField $primaryField */
			$primaryField = $this->query->getEntity()->getField($primaryName);
			$primaryValue = $primaryField->cast($row[$primaryAlias]);

			$basePrimaryValues[$primaryName] = $primaryValue;
		}

		// check for object in identity map
		$baseAddToIM = false;
		$objectClass = $this->objectClass;

		/** @var EntityObject $object */
		$object = $this->identityMap->get($objectClass, $basePrimaryValues);

		if (empty($object))
		{
			$object = new $objectClass(false);

			// set right state
			$object->sysChangeState(State::ACTUAL);

			// add to identityMap later, when primary is set
			$baseAddToIM = true;
		}

		/** @var EntityObject[] $relEntityCache Last reference and relation object that has been woken up by definition */
		$relEntityCache = [];

		// go through select chains
		foreach ($this->query->getSelectChains() as $selectChain)
		{
			// object for current chain element, for the first element is object of init entity
			$currentObject = $object;

			// accumulated definition from the first to the current chain element
			$currentDefinitionParts = [];
			$currentDefinition = null;

			// cut first element as long as it is init entity
			$iterableElements = array_slice($selectChain->getAllElements(), 1);

			// dive deep from the start to the end of chain
			foreach ($iterableElements as $element)
			{
				if ($currentObject === null)
				{
					continue;
				}

				/** @var $element ChainElement $field */
				$field = $element->getValue();

				if (!($field instanceof Field))
				{
					// ignore old-style back references, OneToMany is expected instead
					// skip for the next chain
					continue 2;
				}

				// actualize current definition
				$currentDefinitionParts[] = $field->getName();
				$currentDefinition = join('.', $currentDefinitionParts);

				// is it runtime field? then ->tmpSet()
				$isRuntimeField = !empty($this->query->getRuntimeChains()[$currentDefinition]);

				if ($field instanceof IReadable)
				{
					// for remote objects all values have been already set during compose
					if ($currentObject !== $object)
					{
						continue;
					}

					// normalize value
					$value = $field->cast($row[$selectChain->getAlias()]);

					// set value as actual to the object
					$isRuntimeField
						? $currentObject->sysSetRuntime($field->getName(), $value)
						: $currentObject->sysSetActual($field->getName(), $value);
				}
				else
				{
					// define remote entity definition
					// check if this reference has already been woken up
					// main part of current chain (w/o last element) should be the same
					if (array_key_exists($currentDefinition, $relEntityCache))
					{
						$currentObject = $relEntityCache[$currentDefinition];
						continue;
					} // else it will be set after object identification

					// define remote entity of reference
					$remoteEntity = $field->getRefEntity();

					// define values and primary of remote object
					// we can set all values at one time and skip other iterations with values of this object
					$remotePrimary = $remoteEntity->getPrimaryArray();
					$remoteObjectValues = [];
					$remotePrimaryValues = [];

					foreach ($this->selectChainsMap[$currentDefinition] as $remoteChain)
					{
						/** @var ScalarField|ExpressionField $remoteField */
						$remoteField = $remoteChain->getLastElement()->getValue();
						$remoteValue = $row[$remoteChain->getAlias()];

						$remoteObjectValues[$remoteField->getName()] = $remoteValue;
					}

					foreach ($remotePrimary as $primaryName)
					{
						if (!array_key_exists($primaryName, $remoteObjectValues))
						{
							throw new SystemException(sprintf(
								'Primary of %s was not found in database result', $remoteEntity->getDataClass()
							));
						}

						$remotePrimaryValues[$primaryName] = $remoteObjectValues[$primaryName];
					}

					// compose relative object
					if ($field instanceof Reference)
					{
						// get object via identity map
						$remoteObject = $this->composeRemoteObject($remoteEntity, $remotePrimaryValues, $remoteObjectValues);

						// set remoteObject to baseObject
						$isRuntimeField
							? $currentObject->sysSetRuntime($field->getName(), $remoteObject)
							: $currentObject->sysSetActual($field->getName(), $remoteObject);
					}
					elseif ($field instanceof OneToMany || $field instanceof ManyToMany)
					{
						// get collection of remote objects
						if ($isRuntimeField)
						{
							if (empty($currentObject->sysGetRuntime($field->getName())))
							{
								// create new collection and set as value for current object
								/** @var Collection $collection */
								$collection = $remoteEntity->createCollection();
								$currentObject->sysSetRuntime($field->getName(), $collection);
							}
							else
							{
								$collection = $currentObject->sysGetRuntime($field->getName());
							}
						}
						else
						{
							if (empty($currentObject->sysGetValue($field->getName())))
							{
								// create new collection and set as value for current object
								/** @var Collection $collection */
								$collection = $remoteEntity->createCollection();

								// collection should be filled if there are no LIMIT and relation filter in query
								if ($this->query->getLimit() === null)
								{
									// noting in filter should start with $currentDefinition
									$noRelationInFilter = true;

									foreach ($this->query->getFilterChains() as $chain)
									{
										if (strpos($chain->getDefinition(), $currentDefinition) === 0)
										{
											$noRelationInFilter = false;
											break;
										}
									}

									if ($noRelationInFilter)
									{
										// now we are sure the set is complete
										$collection->sysSetFilled();
									}
								}

								$currentObject->sysSetActual($field->getName(), $collection);
							}
							else
							{
								$collection = $currentObject->sysGetValue($field->getName());
							}
						}

						// define remote object
						if (current($remotePrimaryValues) === null || !$collection->hasByPrimary($remotePrimaryValues))
						{
							// get object via identity map
							$remoteObject = $this->composeRemoteObject($remoteEntity, $remotePrimaryValues, $remoteObjectValues);

							// add to collection
							if ($remoteObject !== null)
							{
								$collection->sysAddActual($remoteObject);
							}
						}
						else
						{
							$remoteObject = $collection->getByPrimary($remotePrimaryValues);
						}
					}
					else
					{
						throw new SystemException('Unknown chain element value while fetching object');
					}

					// switch current object, further chain elements belong to this object
					$currentObject = $remoteObject;

					// save as ready object for current row
					$relEntityCache[$currentDefinition] = $remoteObject;
				}
			}
		}

		if ($baseAddToIM)
		{
			// save to identityMap
			$this->identityMap->put($object);
		}

		return $object;
	}

	/**
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws \Bitrix\Main\SystemException
	 */
	final public function fetchCollection()
	{
		// base object initialization
		$this->initializeFetchObject(true);

		/** @var Collection $collection */
		$collection = $this->query->getEntity()->createCollection();

		while ($object = $this->fetchObject())
		{
			$collection->sysAddActual($object);
		}

		return $collection;
	}

	/**
	 * One-time initialization actions when fetch objects
	 *
	 * @param bool $asCollection
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function initializeFetchObject($asCollection = false)
	{
		if (empty($this->objectInitPassed))
		{
			// validate query
			if (!empty($this->query->getGroupChains()))
			{
				throw new SystemException(
					'Result of query with aggregation could not be fetched as an object'
				);
			}

			// initialize
			if (empty($this->identityMap))
			{
				// identity map could have been set before first fetch
				$this->identityMap = new IdentityMap;
			}

			$this->objectClass = $this->query->getEntity()->getObjectClass();

			$this->buildSelectChainsMap();
			$this->definePrimaryAliases();

			// values will be cast anyway based on original fields, not just associated with column types
			//$this->setStrictValueConverters();

			$this->objectInitPassed = true;

			// if there are back references, fetch everything and make virtual ArrayResult
			if (!$asCollection && $this->query->hasBackReference())
			{
				/** @var Collection $collection */
				$collection = $this->fetchCollection();

				// remember original result
				$originalResult = $this->result;

				$this->result = new ArrayResult($collection->getAll());

				// recover count total
				try
				{
					if ($originalResult->getCount())
					{
						$this->result->setCount($originalResult->getCount());
					}
				}
				catch (\Bitrix\Main\ObjectPropertyException $e) {}
			}
		}
	}

	/**
	 * Builds chains map by entity path
	 */
	protected function buildSelectChainsMap()
	{
		foreach ($this->query->getSelectChains() as $selectChain)
		{
			$this->selectChainsMap[$selectChain->getDefinition(-1)][] = $selectChain;
		}
	}

	/**
	 * Builds base object primary aliases map
	 */
	protected function definePrimaryAliases()
	{
		$primaryNames = $this->query->getEntity()->getPrimaryArray();

		foreach ($this->query->getSelectChains() as $selectChain)
		{
			$field = $selectChain->getLastElement()->getValue();

			// get 0-level simple fields: entity + field
			if ($field->getEntity()->getDataClass() === $this->query->getEntity()->getDataClass()
				&& in_array($field->getName(), $primaryNames))
			{
				$this->primaryAliases[$field->getName()] = $selectChain->getAlias();

				if (count($this->primaryAliases) == count($primaryNames))
				{
					break;
				}
			}
		}

		if (count($this->primaryAliases) != count($primaryNames))
		{
			throw new SystemException(sprintf(
				'Primary of %s was not found in database result', $this->query->getEntity()->getDataClass()
			));
		}
	}

	/**
	 * Low-level data type cast
	 */
	protected function setStrictValueConverters()
	{
		foreach ($this->query->getSelectChains() as $selectChain)
		{
			$alias = $selectChain->getAlias();

			if (!isset($this->result->converters[$alias]))
			{
				$this->result->converters[$alias] = [
					$this->result->getFields()[$alias],
					'convertValueFromDb'
				];
			}
		}
	}

	/**
	 * @param Entity $entity
	 * @param $primaryValues
	 * @param $objectValues
	 *
	 * @return EntityObject
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function composeRemoteObject($entity, $primaryValues, $objectValues)
	{
		// if null primary then return null
		if (current($primaryValues) === null)
		{
			return null;
		}

		// try to get remote object from identity map
		/** @var $remoteObject EntityObject */
		$objectClass = $entity->getObjectClass();
		$remoteObject = $this->identityMap->get($objectClass, $primaryValues);

		// do we have a new object to add to identity map
		$addToIM = false;

		if (empty($remoteObject))
		{
			// define new object
			$remoteObject = new $objectClass(false);

			// set right state
			$remoteObject->sysChangeState(State::ACTUAL);

			// add to identityMap later, when primary is set
			$addToIM = true;
		}

		// set all values of remote object
		foreach ($objectValues as $fieldName => $objectValue)
		{
			/** @var ScalarField $field */
			$field = $entity->getField($fieldName);
			$castValue = $field->cast($objectValue);

			$remoteObject->sysSetActual($fieldName, $castValue);
		}

		// save to identityMap
		if ($addToIM)
		{
			$this->identityMap->put($remoteObject);
		}

		return $remoteObject;
	}

	/**
	 * Sets custom identity map
	 *
	 * @param IdentityMap $map
	 *
	 * @return Result
	 */
	public function setIdentityMap(IdentityMap $map)
	{
		$this->identityMap = $map;

		return $this;
	}

	/**
	 * @return IdentityMap
	 */
	public function getIdentityMap()
	{
		return $this->identityMap;
	}

	// decorate other methods
	public function getResource()
	{
		return $this->result->getResource();
	}

	public function setReplacedAliases(array $replacedAliases)
	{
		$this->result->setReplacedAliases($replacedAliases);
	}

	public function addReplacedAliases(array $replacedAliases)
	{
		$this->result->addReplacedAliases($replacedAliases);
	}

	public function setSerializedFields(array $serializedFields)
	{
		$this->result->setSerializedFields($serializedFields);
	}

	public function addFetchDataModifier($fetchDataModifier)
	{
		$this->result->addFetchDataModifier($fetchDataModifier);
	}

	public function fetchRaw()
	{
		return $this->result->fetchRaw();
	}

	public function fetch(\Bitrix\Main\Text\Converter $converter = null)
	{
		$row = $this->result->fetch($converter);

		return empty($this->hiddenObjectFields)
			? $row
			: $this->hideObjectFields($row);
	}

	public function fetchAll(\Bitrix\Main\Text\Converter $converter = null)
	{
		if (empty($this->hiddenObjectFields))
		{
			return $this->result->fetchAll($converter);
		}
		else
		{
			$data = $this->result->fetchAll($converter);

			foreach ($data as &$row)
			{
				$this->hideObjectFields($row);
			}

			return $data;
		}

	}

	public function getTrackerQuery()
	{
		return $this->result->getTrackerQuery();
	}

	public function getConverters()
	{
		return $this->result->getConverters();
	}

	public function setConverters($converters)
	{
		$this->result->setConverters($converters);
	}

	public function setCount($n)
	{
		$this->result->setCount($n);
	}

	public function getCount()
	{
		return $this->result->getCount();
	}

	public function getIterator()
	{
		return $this->result->getIterator();
	}

}
