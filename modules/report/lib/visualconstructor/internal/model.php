<?php
namespace Bitrix\Report\VisualConstructor\Internal;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query\Filter\ConditionTree;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Report\VisualConstructor\Config\Common;

use Bitrix\Report\VisualConstructor\Internal\Error\IErrorable;

/**
 * This class developed now only for models influencing with report visual constructor.
 * @package Bitrix\Report\VisualConstructor\Internal
 */
abstract class Model implements IErrorable
{
	const ATTRIBUTE_SLICE_DELIMITER = '__';
	protected $id;
	protected $createdAt;
	protected $updatedAt;
	protected $errors;
	private $deletedEntities = array();
	private $currentDbState = array();
	//private $lazyAttributes;

	/**
	 * Model constructor.
	 */
	public function __construct()
	{
		$this->createdAt = new DateTime();
		$this->updatedAt = new DateTime();
	}

	/**
	 * Gets the fully qualified name of table class which belongs to current model.
	 * @throws \Bitrix\Main\NotImplementedException
	 * @return void
	 */
	public static function getTableClassName()
	{
		throw new NotImplementedException;
	}


	/**
	 * Returns the list of pair for mapping data and object properties.
	 * Key is field in DataManager, value is object property.
	 * @return array
	 */
	public static function getMapAttributes()
	{
		return array(
			'ID' => 'id',
			'CREATED_DATE' => 'createdAt',
			'UPDATED_DATE' => 'updatedAt'
		);
	}

	/**
	 * Returns map of lazy loaded attributes of current model.
	 * supported relation types ONE_TO_MANY, MANY_TO_ONE, MANY_TO_MANY
	 * example:
	 *      array(
	 *          'lazyLoadAttributeName_1' => array(
	 *              'type' => Common::ONE_TO_MANY,
	 *              'targetEntity' => TargetEntityClass::getClassName(), //inheritor of this class
	 *              'mappedBy' => 'targetEntityField',
	 *          ),
	 *          'lazyLoadAttributeName_2' => array(
	 *              'type' => Common::MANY_TO_ONE,
	 *              'targetEntity' => TargetEntityClass::getClassName(), //inheritor of this class
	 *              'inveredBy' => '',
	 *              'join' => array(
	 *                  'field' => array('thisFieldName', 'relationEntityFieldMame')
	 *              )
	 *          ),
	 *          'lazyLoadAttributeName_1' => array(
	 *              'type' => Common::MANY_TO_MANY,
	 *              'targetEntity' => TargetEntityClass::getClassName(), //inheritor of this class
	 *              'join' => array(
	 *                  'tableClassName' => TableClassName::getClassName //Supporting table ORM class name for connecting 2 entities
	 *                  'column' => array(SUPPORTING_CONNECT_COLUMN => array('thisPrimaryFieldName', 'SUPPORTING_TABLE_APPROPRIATE_FIELD_NAME')),
	 *                  'inverseColumn' => array(SUPPORTING_CONNECT_COLUMN => array('relationEntityPrimaryFieldName', 'SUPPORTING_TABLE_APPROPRIATE_FIELD_NAME')),
	 *              ),
	 *           )
	 *      )
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array();
	}


	/**
	 * @return Model
	 */
	public function save()
	{

		$referenceMapAttributes = static::getMapReferenceAttributes();

		foreach ($referenceMapAttributes as $referenceAttributeName => $assoc)
		{
			if (!empty($this->{$referenceAttributeName}))
			{
				switch ($assoc['type'])
				{
					case Common::MANY_TO_ONE:
						$this->saveManyToOneReference($this->{$referenceAttributeName}, $assoc);
						break;
					case Common::ONE_TO_ONE:
						//TODO:
						break;
				}
			}
		}

		$ormFields = $this->getConvertedMapAttributesToOrmFields();
		if (!$ormFields['ID'])
		{
			$addResult = $this->add($ormFields);
			$ownerId = $addResult->getId();
			$this->setId($ownerId);
		}
		else
		{
			$ownerId = $ormFields['ID'];
			$changedAttributes = $this->getChangedOrmAttributes($ormFields);
			if ($changedAttributes)
			{
				$this->update(array('ID' => $ownerId), $changedAttributes);
			}
			if (!empty($this->deletedEntities))
			{
				$this->deleteReference($this->deletedEntities);
			}
		}




		foreach ($referenceMapAttributes as $referenceAttributeName => $assoc)
		{
			if (!empty($this->{$referenceAttributeName}))
			{
				switch ($assoc['type'])
				{
					case Common::ONE_TO_MANY:
						$this->saveOneToManyReferences($this->{$referenceAttributeName}, $assoc, $ownerId);
						break;
					case Common::MANY_TO_MANY:
						$this->saveManyToManyReferences($this->{$referenceAttributeName}, $assoc, $ownerId);
						break;
				}
			}
		}

		return $ownerId;
	}

	/**
	 * @param static[] $references
	 * @param $assoc
	 * @param $ownerEntityId
	 */
	private function saveOneToManyReferences($references, $assoc, $ownerEntityId)
	{
		foreach ($references as $key => $reference)
		{
			if ($reference instanceof $assoc['targetEntity'])
			{
				$mapReferenceAttributes = $reference::getMapReferenceAttributes();
				$reference->{$mapReferenceAttributes[$assoc['mappedBy']]['join']['field'][0]} = $ownerEntityId;
				$reference->save();
			}

		}
	}

	/**
	 * @param static[] $references
	 * @param $assoc
	 * @param $ownerEntityId
	 */
	private function saveManyToManyReferences($references, $assoc, $ownerEntityId)
	{
		foreach ($references as $key => $reference)
		{
			if ($reference instanceof $assoc['targetEntity'])
			{
				$isReferenceNew = !(boolean)$reference->getId();
				$referenceId = $reference->save();
				if ($isReferenceNew)
				{
					$column = array_values($assoc['join']['column']);
					$column = $column[0];
					$inverseColumn = array_values($assoc['join']['inverseColumn']);
					$inverseColumn = $inverseColumn[0];
					$connectData = array(
						$column[1] => $ownerEntityId,
						$inverseColumn[1] => $referenceId,
					);
					/** @var \Bitrix\Main\Entity\DataManager $ormTableClassName */
					$ormTableClassName = $assoc['join']['tableClassName'];
					$ormTableClassName::add($connectData);
				}
			}
		}
	}

	/**
	 * @param static $reference
	 * @param $assoc
	 */
	private function saveManyToOneReference($reference, $assoc)
	{
		if ($reference instanceof $assoc['targetEntity'])
		{
			$reference = clone $reference;
			$reference->{$assoc['inversedBy']} = null;
			$reference->save();
			$this->{$assoc['join']['field'][0]} = $reference->getId();
		}
	}



	/**
	 * @return array
	 */
	private function getConvertedMapAttributesToOrmFields()
	{
		$result = array();
		$fieldsMap = static::getMapAttributes();
		foreach ($fieldsMap as $ormFieldName => $objectProperty)
		{
			$result[$ormFieldName] = $this->{$objectProperty};
		}
		return $result;
	}


	/**
	 * @param array $data
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	private function add(array $data)
	{
		$tableClassName = static::getTableClassName();
		$resultData = $tableClassName::add($data);
		$this->currentDbState = $resultData->getData();
		return $resultData;
	}

	/**
	 * @param $newEntityAttributes
	 * @return array
	 */
	private function getChangedOrmAttributes($newEntityAttributes)
	{

		/**
		 * DONE
		 * Optimise here: maybe add some property where will located state of values of entity when it select from DB
		 */
		$oldEntityAttributes = $this->getCurrentDbState();
		unset($oldEntityAttributes['CREATED_DATE']);
		unset($oldEntityAttributes['UPDATED_DATE']);
		unset($newEntityAttributes['CREATED_DATE']);
		unset($newEntityAttributes['UPDATED_DATE']);
		$result = array();
		foreach ($oldEntityAttributes as $key => $value)
		{
			if ($newEntityAttributes[$key] != $value)
			{
				$result[$key] = $newEntityAttributes[$key];
			}
		}

		return $result;
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return \Bitrix\Main\Entity\UpdateResult
	 */
	private function update($primary, array $data)
	{
		$tableClassName = static::getTableClassName();
		$data['UPDATED_DATE'] = new DateTime();
		$resultData = $tableClassName::update($primary, $data);

		foreach ($resultData->getData() as $key => $value)
		{
			$this->currentDbState[$key] = $value;
		}
		return $resultData;
	}


	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}


	/**
	 * @param array|ConditionTree $filter Filter parameters.
	 * @param array $with Relation keys to load.
	 * @param array $order Order parameters.
	 * @return static
	 */
	public static function load($filter, array $with = array(), $order = array())
	{
		$models = static::getModelList(array(
			'select' => array('*'),
			'filter' => $filter,
			'with' => $with,
			'order' => $order
		));
		return array_shift($models);
	}

	/**
	 * Get model list like getList
	 * @param array $parameters
	 * @return static[]
	 */
	protected static function getModelList(array $parameters)
	{
		$modelList = array();
		$query = static::getList($parameters);
		while ($row = $query->fetch())
		{
			if (!$modelList[$row['ID']])
			{
				$model = static::buildFromArray($row);
			}
			else
			{
				$model = static::buildFromArray($row, $modelList[$row['ID']]);
			}
			if ($model->id)
			{
				$modelList[$model->id] = $model;
			}
		}

		return $modelList;
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected static function getList(array $parameters)
	{
		/** @var DataManager $tableClass */
		$tableClass = static::getTableClassName();
		return $tableClass::getList(static::prepareGetListParameters($parameters));
	}

	/**
	 * Builds model from array.
	 * @param array $attributes Model attributes.
	 * @param $parentEntity
	 * @return static
	 * @internal
	 */
	protected static function buildFromArray(array $attributes, $parentEntity = null)
	{
		/** @var Model $model */
		$model = new static;
		return $model->setAttributes($attributes, $parentEntity);
	}

	/**
	 * Method which transfer from Array to object of special type.
	 *
	 * @param array $attributes
	 * @param $parentEntity
	 * @return Model
	 */
	private function setAttributes(array $attributes, $parentEntity)
	{
		foreach ($attributes as $key => $value)
		{
			if ($value === null)
			{
				unset($attributes[$key]);
			}
		}
		if (!$parentEntity)
		{
			$mapAttributes = static::getMapAttributes();
			foreach ($attributes as $key => $value)
			{
				if (!empty($mapAttributes[$key]))
				{
					$this->{$mapAttributes[$key]} = $value;
					$this->currentDbState[$key] = $value;
					unset($attributes[$key]);
				}
			}
			$parentEntity = $this;
		}

		if (empty($attributes))
		{
			return $parentEntity;
		}

		$subEntitiesMapAttributes = static::getMapReferenceAttributes();
		$subEntitiesKeys = array_keys($subEntitiesMapAttributes);


		$subEntityAttributes = array();
		$loadedSubEntitiesKeys = array();
		foreach ($attributes as $key => $value)
		{
			$delimiter = self::ATTRIBUTE_SLICE_DELIMITER;
			$selectedAttributeParts = explode($delimiter, $key);
			if (count($selectedAttributeParts) == 2 && in_array($selectedAttributeParts[0], $subEntitiesKeys))
			{
				$loadedSubEntitiesKeys[$selectedAttributeParts[0]] = $selectedAttributeParts[0];
				$subEntityAttributes[$selectedAttributeParts[0]][$selectedAttributeParts[1]] = $value;
			}
			elseif (count($selectedAttributeParts) >= 2)
			{
				$nestedEntityParentKey = array_shift($selectedAttributeParts);
				$nestedElementKey = implode(self::ATTRIBUTE_SLICE_DELIMITER, $selectedAttributeParts);
				$subEntityAttributes[$nestedEntityParentKey][$nestedElementKey] = $value;
			}
		}

		foreach ($subEntityAttributes as $key => $validAttributes)
		{
			if (!empty($subEntitiesMapAttributes[$key]))
			{
				/** @var static $targetEntityClass */
				$targetEntityClass = $subEntitiesMapAttributes[$key]['targetEntity'];
				if ($subEntitiesMapAttributes[$key]['type'] != Common::MANY_TO_ONE)
				{
					if (!isset($parentEntity->{$key}[$validAttributes['ID']]))
					{
						$subEntity = $targetEntityClass::buildFromArray($validAttributes);

						$nestedEntityReferenceMap = $subEntity::getMapReferenceAttributes();


						/**
						 * If connection type is one to many we can map to nested parent entity automatically
						 */
						if ($subEntitiesMapAttributes[$key]['type'] == Common::ONE_TO_MANY)
						{
							if (!empty($nestedEntityReferenceMap[$subEntitiesMapAttributes[$key]['mappedBy']]))
							{
								$subEntity->{$subEntitiesMapAttributes[$key]['mappedBy']} = $parentEntity;
							}
						}
						$parentEntity->{$key}[$subEntity->id] = $subEntity;
					}
					else
					{
						$targetEntityClass::buildFromArray($validAttributes, $parentEntity->{$key}[$validAttributes['ID']]);
					}
				}
				else
				{
					$subEntity = $targetEntityClass::buildFromArray($validAttributes);
					$parentEntity->{$key} = $subEntity;
				}

			}
		}


		return $parentEntity;
	}

	/**
	 * @param array $parameters
	 * @throws \Bitrix\Main\SystemException
	 * @return array
	 */
	protected static function prepareGetListParameters(array $parameters)
	{
		if (!empty($parameters['with']))
		{
			if (!is_array($parameters['with']))
			{
				throw new ArgumentException('"with" must be array');
			}
			if (!isset($parameters['select']))
			{
				$parameters['select'] = array('*');
			}
			elseif (!in_array('*', $parameters['select']) && !in_array('ID', $parameters['select']))
			{
				$parameters['select'][] = 'ID';
			}
			$parameters['select'] = array_merge($parameters['select'], static::buildOrmSelectForReference($parameters['with']));
		}

		unset($parameters['with']);
		return $parameters;
	}

	/**
	 * @param array $with
	 * @return array
	 * @throws ArgumentException
	 */
	protected static function buildOrmSelectForReference(array $with)
	{
		$select = array();
		$referenceAttributes = static::getMapReferenceAttributes();
		foreach ($with as $referenceKey)
		{
			$testNesting = explode('.', $referenceKey);
			$nestedReferenceAttributes = $referenceAttributes;
			$prefix = '';
			$fromKeyNamePrefix = '';
			foreach ($testNesting as $reference)
			{
				if (!empty($nestedReferenceAttributes[$reference]))
				{
					$prefix = $prefix . $reference . self::ATTRIBUTE_SLICE_DELIMITER;
					switch ($nestedReferenceAttributes[$reference]['type'])
					{
						case Common::ONE_TO_MANY:
							/** @var static $targetEntity */
							$targetEntity = $nestedReferenceAttributes[$reference]['targetEntity'];
							$targetOrmTable = $targetEntity::getTableClassName();
							$fromKeyNamePrefix = !empty($fromKeyNamePrefix) ? $fromKeyNamePrefix . '.' : '';
							$select[$prefix] = $fromKeyNamePrefix . $targetOrmTable::getClassName() . ':' . strtoupper($nestedReferenceAttributes[$reference]['mappedBy']);
							$fromKeyNamePrefix .= $targetOrmTable::getClassName() . ':' . strtoupper($nestedReferenceAttributes[$reference]['mappedBy']);
							$nestedReferenceAttributes = $targetEntity::getMapReferenceAttributes();
							break;
						case Common::MANY_TO_MANY:
							$fromKeyName = array_keys($nestedReferenceAttributes[$reference]['join']['column']);
							$fromKeyName = $fromKeyName[0];
							$fromKeyNamePrefix = !empty($fromKeyNamePrefix) ? $fromKeyNamePrefix . '.' : '';
							$toKeyName = array_keys($nestedReferenceAttributes[$reference]['join']['inverseColumn']);
							$toKeyName = $toKeyName[0];
							$select[$prefix] = $fromKeyNamePrefix . $nestedReferenceAttributes[$reference]['join']['tableClassName']
								. ':'
								. $fromKeyName
								. '.'
								. $toKeyName;
							$targetEntity = $nestedReferenceAttributes[$reference]['targetEntity'];
							$nestedReferenceAttributes = $targetEntity::getMapReferenceAttributes();
							break;
						case Common::MANY_TO_ONE:
							$fromKeyNamePrefix = !empty($fromKeyNamePrefix) ? $fromKeyNamePrefix . '.' : '';
							$select[$prefix] = $fromKeyNamePrefix . strtoupper($reference);
							$fromKeyNamePrefix .= strtoupper($reference);
							break;

					}
				}
				else
				{
					throw new ArgumentException("Reference with name:" . $reference . ' not define in reference map');
				}
			}
		}
		return $select;
	}

	/**
	 * @return bool|null
	 */
	public function delete()
	{
		$ownerId = $this->getId();
		$referenceAttributesMap = static::getMapReferenceAttributes();

		foreach ($referenceAttributesMap as $referenceKey => $referenceAttributes)
		{
			if (!empty($referenceAttributes['options']['deleteSkip']))
			{
				continue;
			}

			if (!$this->{$referenceKey})
			{
				$this->loadAttribute($referenceKey);
			}


			if ($this->{$referenceKey})
			{
				switch ($referenceAttributes['type'])
				{
					case Common::ONE_TO_MANY:
						$this->deleteOneToManyReferences($this->{$referenceKey});
						break;
					case Common::MANY_TO_MANY:
						$this->deleteManyToManyReferences($this->{$referenceKey}, $referenceAttributes, $ownerId);
						break;
					case Common::MANY_TO_ONE:
						$this->deleteManyToOneReference($this->{$referenceKey}, $referenceAttributes, $ownerId);
						break;
					case Common::ONE_TO_ONE:
						//TODO
						break;
				}
			}

		}
		$entityTableClass = static::getTableClassName();
		$deleteEntity = $entityTableClass::delete($ownerId);
		if ($deleteEntity->isSuccess())
		{
			return true;
		}
		else
		{
			$this->errors[] = $deleteEntity->getErrors();
			return null;
		}
	}

	/**
	 * @param static[] $referenceEntities
	 */
	private function deleteOneToManyReferences($referenceEntities)
	{
		foreach ($referenceEntities as $referenceEntity)
		{
			$referenceEntity->delete();
		}
	}

	/**
	 * @param static[] $referenceEntities
	 * @param $assoc
	 * @param $ownerId
	 */
	private function deleteManyToManyReferences($referenceEntities, $assoc, $ownerId)
	{
		$connectColumn = array_shift($assoc['join']['column']);
		$connectInverseColumn = array_shift($assoc['join']['inverseColumn']);
		foreach ($referenceEntities as $referenceEntity)
		{
			$connectPrimaryKey = array();
			/** @var \Bitrix\Main\Entity\DataManager $connectTableClass */
			$connectTableClass = $assoc['join']['tableClassName'];
			$connectPrimaryKey[$connectColumn[1]] = $ownerId;
			$connectPrimaryKey[$connectInverseColumn[1]] = $referenceEntity->getId();;
			$connectTableClass::delete($connectPrimaryKey);
			$referenceEntity->delete();
		}
	}

	/**
	 * Clean from parent reference list deleted entity
	 * @param $referenceEntity
	 * @param $assoc
	 * @param $ownerId
	 */
	private function deleteManyToOneReference(&$referenceEntity, $assoc, $ownerId)
	{
		if ($referenceEntity  instanceof $assoc['targetEntity'])
		{
			unset($referenceEntity->{$assoc['inversedBy']}[$ownerId]);
		}
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id Id property value.
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param DateTime $createdAt Record create time.
	 * @return void
	 */
	public function setCreatedAt(DateTime $createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param DateTime $updatedAt Record update time.
	 * @return void
	 */
	public function setUpdatedAt(DateTime $updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 * @param mixed $id Load entity by id.
	 * @return static
	 */
	public static function loadById($id)
	{
		$entity = static::load(array('ID' => $id));
		return $entity;
	}

	/**
	 * @param string $attributeName Attribute name to load to property from db.
	 * @return void
	 */
	public function loadAttribute($attributeName)
	{
		if (property_exists($this, $attributeName) && $this->{$attributeName} == null)
		{
			$entity = static::load(array('ID' => $this->getId()), array($attributeName));
			$referencesAttributeMap = $this::getMapReferenceAttributes();
			foreach ($referencesAttributeMap as $referenceKey => $referenceMapAttributes)
			{
				if ($referenceMapAttributes['type'] === Common::ONE_TO_MANY && $referenceKey === $attributeName && !empty($entity->{$attributeName}))
				{
					foreach ($entity->{$attributeName} as $subEntity)
					{
						if ($subEntity instanceof $referenceMapAttributes['targetEntity'])
						{
							$entity->{$attributeName}->{$referenceMapAttributes['mappedBy']} = $this;
						}
					}
				}
			}
			$this->{$attributeName} = $entity->{$attributeName};
		}
	}


	/**
	 * Implement delete and add actions for nested relations.
	 *
	 * @param string $name Getter name.
	 * @param array $arguments Arguments passed to getter.
	 * @return void
	 */
	public function __call($name , array $arguments)
	{
		$isDeleteReferenceCall = preg_match_all('/^delete(\w+)/', $name, $deleteCallNameParts);
		if ($isDeleteReferenceCall)
		{
			$referenceName = $deleteCallNameParts[1][0];
			$referenceName = strtolower($referenceName);
			$referenceMapAttributes = $this::getMapReferenceAttributes();
			if (!empty($referenceMapAttributes[$referenceName]))
			{
				/** @var static[] $entities */
				$entities = !empty($arguments[0]) ? $arguments[0] : array();
				if (!is_array($entities))
				{
					$entities = array(
						$entities
					);
				}

				foreach ($entities as $entity)
				{
					$this->deletedEntities[$referenceName][$entity->getId()] = $entity;
					unset($this->{$referenceName}[$entity->getId()]);
				}
			}
		}

		$isAddReferenceCall = preg_match_all('/^add(\w+)/', $name, $addCallNameParts);
		if ($isAddReferenceCall)
		{
			$referenceName = $addCallNameParts[1][0];
			$referenceName = strtolower($referenceName);
			$referenceMapAttributes = $this::getMapReferenceAttributes();
			if (!empty($referenceMapAttributes[$referenceName]))
			{
				/** @var static[] $entities */
				$entities = !empty($arguments[0]) ? $arguments[0] : array();
				if (!is_array($entities))
				{
					$entities = array(
						$entities
					);
				}

				foreach ($entities as $entity)
				{
					$this->{$referenceName}[] = $entity;
				}
			}
		}

	}

	/**
	 * @param static[][] $deletedReferenceEntities
	 */
	private function deleteReference($deletedReferenceEntities)
	{
		foreach ($deletedReferenceEntities as $referenceName => $referenceEntities)
		{
			$map = $this::getMapReferenceAttributes();
			$map = $map[$referenceName];
			switch ($map['type'])
			{
				case Common::ONE_TO_MANY:
					foreach ($referenceEntities as $referenceEntity)
					{
						$referenceEntity->delete();
					}
					break;
				case Common::MANY_TO_MANY:
					$connectColumn = array_shift($map['join']['column']);
					$connectInverseColumn = array_shift($map['join']['inverseColumn']);

					foreach ($referenceEntities as $referenceEntity)
					{
						$connectPrimaryKey = array();
						/** @var \Bitrix\Main\Entity\DataManager $connectTableClass */
						$connectTableClass = $map['join']['tableClassName'];
						$connectPrimaryKey[$connectColumn[1]] = $this->getId();
						$connectPrimaryKey[$connectInverseColumn[1]] = $referenceEntity->getId();
						$connectTableClass::delete($connectPrimaryKey);
					}
					break;
				case Common::ONE_TO_ONE:
					//TODO
					break;
				case Common::MANY_TO_ONE:
					//TODO
					break;
			}
		}


	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @return array
	 */
	public function getCurrentDbState()
	{
		return $this->currentDbState;
	}
}