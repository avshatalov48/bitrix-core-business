<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\ORM;

use Bitrix\Main;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\StringHelper;

/**
 * Base entity
 */
class Entity
{
	/** @var string | DataManager */
	protected $className;

	protected
		$module,
		$name,
		$connectionName,
		$dbTableName,
		$primary,
		$autoIncrement;

	protected
		$uf_id,
		$isUts,
		$isUtm;

	/** @var Field[] */
	protected $fields;

	protected $fieldsMap;

	/** @var UField[] */
	protected $u_fields;

	/** @var string Unique code */
	protected $code;

	protected $references;

	/** @var static[] dataClass => entity */
	protected static $instances;

	/** @var array ufId => dataClass */
	protected static $ufIdIndex = [];

	/** @var bool */
	protected $isClone = false;

	const DEFAULT_OBJECT_PREFIX = 'EO_';

	/**
	 * Returns entity object
	 *
	 * @param $entityName
	 *
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function get($entityName)
	{
		return static::getInstance($entityName);
	}

	/**
	 * Checks if entity exists
	 *
	 * @param $entityName
	 *
	 * @return bool
	 */
	public static function has($entityName)
	{
		$entityClass = static::normalizeEntityClass($entityName);
		return class_exists($entityClass);
	}

	/**
	 * @static
	 *
	 * @param string $entityName
	 *
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function getInstance($entityName)
	{
		$entityName = static::normalizeEntityClass($entityName);

		return self::getInstanceDirect($entityName);
	}

	/**
	 * @param DataManager|string $className
	 *
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected static function getInstanceDirect($className)
	{
		if (empty(self::$instances[$className]))
		{
			/** @var Entity $entity */
			$entityClass = $className::getEntityClass();

			// in case of calling Table class was not ended with entity initialization
			if (empty(self::$instances[$className]))
			{
				$entity = new $entityClass;
				$entity->initialize($className);
				$entity->postInitialize();

				// call user-defined postInitialize
				$className::postInitialize($entity);

				self::$instances[$className] = $entity;
			}
		}

		return self::$instances[$className];
	}

	/**
	 * Fields factory
	 *
	 * @param string $fieldName
	 * @param array|Field $fieldInfo
	 *
	 * @return Field
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function initializeField($fieldName, $fieldInfo)
	{
		if ($fieldInfo instanceof Field)
		{
			$field = $fieldInfo;

			// rewrite name
			if (!empty($fieldName) && !is_numeric($fieldName))
			{
				$field->setName($fieldName);
			}
		}
		elseif (is_array($fieldInfo))
		{
			if (!empty($fieldInfo['reference']))
			{
				if (is_string($fieldInfo['data_type']) && !str_contains($fieldInfo['data_type'], '\\'))
				{
					// if reference has no namespace, then it's in the same namespace
					$fieldInfo['data_type'] = $this->getNamespace().$fieldInfo['data_type'];
				}

				//$refEntity = Base::getInstance($fieldInfo['data_type']."Table");
				$field = new Reference($fieldName, $fieldInfo['data_type'], $fieldInfo['reference'], $fieldInfo);
			}
			elseif (!empty($fieldInfo['expression']))
			{
				$expression = array_shift($fieldInfo['expression']);
				$buildFrom =  $fieldInfo['expression'];

				$field = new ExpressionField($fieldName, $expression, $buildFrom, $fieldInfo);
			}
			elseif (!empty($fieldInfo['USER_TYPE_ID']))
			{
				$field = new UField($fieldInfo);
			}
			else
			{
				$fieldClass = StringHelper::snake2camel($fieldInfo['data_type']) . 'Field';
				$fieldClass = '\\Bitrix\\Main\\Entity\\'.$fieldClass;

				if (strlen($fieldInfo['data_type']) && class_exists($fieldClass))
				{
					$field = new $fieldClass($fieldName, $fieldInfo);
				}
				elseif (strlen($fieldInfo['data_type']) && class_exists($fieldInfo['data_type']))
				{
					$fieldClass = $fieldInfo['data_type'];
					$field = new $fieldClass($fieldName, $fieldInfo);
				}
				else
				{
					throw new Main\ArgumentException(sprintf(
						'Unknown data type "%s" found for `%s` field in %s Entity.',
						$fieldInfo['data_type'], $fieldName, $this->getName()
					));
				}
			}
		}
		else
		{
			throw new Main\ArgumentException(sprintf('Unknown field type `%s`',
				is_object($fieldInfo) ? get_class($fieldInfo) : gettype($fieldInfo)
			));
		}

		$field->setEntity($this);
		$field->postInitialize();

		return $field;
	}

	public function initialize($className)
	{
		/** @var $className DataManager */
		$this->className = $className;

		/** @var DataManager $className */
		$this->connectionName = $className::getConnectionName();
		$this->dbTableName = $className::getTableName();
		$this->fieldsMap = $className::getMap();
		$this->uf_id = $className::getUfId();
		$this->isUts = $className::isUts();
		$this->isUtm = $className::isUtm();

		// object & collection classes
		// Loader::registerObjectClass($className::getObjectClass(), $className);
		// Loader::registerCollectionClass($className::getCollectionClass(), $className);
	}

	/**
	 * Reinitializing entity object for another Table class.
	 * Can be useful for complex inheritance with cloning.
	 *
	 * @param $className
	 */
	public function reinitialize($className)
	{
		// reset class
		$this->className = static::normalizeEntityClass($className);

		$classPath = explode('\\', ltrim($this->className, '\\'));
		$this->name = substr(end($classPath), 0, -5);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function postInitialize()
	{
		// basic properties
		$classPath = explode('\\', ltrim($this->className, '\\'));
		$this->name = substr(end($classPath), 0, -5);

		// default db table name
		if (is_null($this->dbTableName))
		{
			$_classPath = array_slice($classPath, 0, -1);

			$this->dbTableName = 'b_';

			foreach ($_classPath as $i => $_pathElem)
			{
				if ($i == 0 && $_pathElem == 'Bitrix')
				{
					// skip bitrix namespace
					continue;
				}

				if ($i == 1 && $_pathElem == 'Main')
				{
					// also skip Main module
					continue;
				}

				$this->dbTableName .= strtolower($_pathElem).'_';
			}

			// add class
			if ($this->name !== end($_classPath))
			{
				$this->dbTableName .= StringHelper::camel2snake($this->name);
			}
			else
			{
				$this->dbTableName = substr($this->dbTableName, 0, -1);
			}
		}

		$this->primary = array();
		$this->references = array();

		// attributes
		foreach ($this->fieldsMap as $fieldName => &$fieldInfo)
		{
			$this->addField($fieldInfo, $fieldName);
		}

		if (!empty($this->fieldsMap) && empty($this->primary))
		{
			throw new Main\SystemException(sprintf('Primary not found for %s Entity', $this->name));
		}

		// attach userfields
		if (empty($this->uf_id))
		{
			// try to find ENTITY_ID by map
			$userTypeManager = Main\Application::getUserTypeManager();
			if($userTypeManager instanceof \CUserTypeManager)
			{
				$entityList = $userTypeManager->getEntityList();
				$ufId = is_array($entityList) ? array_search($this->className, $entityList) : false;
				if ($ufId !== false)
				{
					$this->uf_id = $ufId;
				}
			}
		}

		if (!empty($this->uf_id))
		{
			// attach uf fields and create uts/utm entities
			Main\UserFieldTable::attachFields($this, $this->uf_id);

			// save index
			static::$ufIdIndex[$this->uf_id] = $this->className;
		}
	}

	/**
	 * Returns class of Object for current entity.
	 *
	 * @return EntityObject|string
	 */
	public function getObjectClass()
	{
		$dataManagerClass = $this->className;
		return static::normalizeName($dataManagerClass::getObjectClass());
	}

	/**
	 * Returns class name of Object for current entity.
	 *
	 * @return EntityObject|string
	 */
	public function getObjectClassName()
	{
		$dataManagerClass = $this->className;
		return $dataManagerClass::getObjectClassName();
	}

	public static function getDefaultObjectClassName($entityName)
	{
		$className = $entityName;

		if ($className == '')
		{
			// entity without name
			$className = 'NNM_Object';
		}

		$className = static::DEFAULT_OBJECT_PREFIX.$className;

		return $className;
	}

	/**
	 * @return Collection|string
	 */
	public function getCollectionClass()
	{
		$dataClass = $this->getDataClass();
		return static::normalizeName($dataClass::getCollectionClass());
	}

	/**
	 * @return Collection|string
	 */
	public function getCollectionClassName()
	{
		$dataClass = $this->getDataClass();
		return $dataClass::getCollectionClassName();
	}

	public static function getDefaultCollectionClassName($entityName)
	{
		$className = static::DEFAULT_OBJECT_PREFIX.$entityName.'_Collection';

		return $className;
	}

	/**
	 * @param bool $setDefaultValues
	 *
	 * @return null Actual type should be annotated by orm:annotate
	 */
	public function createObject($setDefaultValues = true)
	{
		$objectClass = $this->getObjectClass();
		return new $objectClass($setDefaultValues);
	}

	/**
	 * @return null Actual type should be annotated by orm:annotate
	 */
	public function createCollection()
	{
		$collectionClass = $this->getCollectionClass();
		return new $collectionClass($this);
	}

	/**
	 * @see EntityObject::wakeUp()
	 *
	 * @param $row
	 *
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function wakeUpObject($row)
	{
		$objectClass = $this->getObjectClass();
		return $objectClass::wakeUp($row);
	}

	/**
	 * @see Collection::wakeUp()
	 *
	 * @param $rows
	 *
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function wakeUpCollection($rows)
	{
		$collectionClass = $this->getCollectionClass();
		return $collectionClass::wakeUp($rows);
	}

	/**
	 * @param Field $field
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function appendField(Field $field)
	{
		if (isset($this->fields[StringHelper::strtoupper($field->getName())]) && !$this->isClone)
		{
			trigger_error(sprintf(
				'Entity `%s` already has Field with name `%s`.', $this->getFullName(), $field->getName()
			), E_USER_WARNING);

			return false;
		}

		if ($field instanceof Reference)
		{
			// references cache
			$this->references[$field->getRefEntityName()][] = $field;
		}

		$this->fields[StringHelper::strtoupper($field->getName())] = $field;

		if ($field instanceof ScalarField && $field->isPrimary())
		{
			$this->primary[] = $field->getName();

			if($field->isAutocomplete())
			{
				$this->autoIncrement = $field->getName();
			}
		}

		// add reference field for UField iblock_section
		if ($field instanceof UField && $field->getTypeId() == 'iblock_section')
		{
			$refFieldName = $field->getName().'_BY';

			if ($field->isMultiple())
			{
				$localFieldName = $field->getValueFieldName();
			}
			else
			{
				$localFieldName = $field->getName();
			}

			$newFieldInfo = array(
				'data_type' => 'Bitrix\Iblock\Section',
				'reference' => array($localFieldName, 'ID'),
			);

			$newRefField = new Reference($refFieldName, $newFieldInfo['data_type'], $newFieldInfo['reference'][0], $newFieldInfo['reference'][1]);
			$newRefField->setEntity($this);

			$this->fields[StringHelper::strtoupper($refFieldName)] = $newRefField;
		}

		return true;
	}

	/**
	 * @param array|Field $fieldInfo
	 * @param null|string $fieldName
	 *
	 * @return Field|false
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function addField($fieldInfo, $fieldName = null)
	{
		$field = $this->initializeField($fieldName, $fieldInfo);

		return $this->appendField($field) ? $field : false;
	}

	public function getReferencesCountTo($refEntityName)
	{
		if (array_key_exists($key = strtolower($refEntityName), $this->references))
		{
			return count($this->references[$key]);
		}

		return 0;
	}


	public function getReferencesTo($refEntityName)
	{
		if (array_key_exists($key = strtolower($refEntityName), $this->references))
		{
			return $this->references[$key];
		}

		return array();
	}

	// getters
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param $name
	 *
	 * @return Field|ScalarField
	 * @throws Main\ArgumentException
	 */
	public function getField($name)
	{
		if ($this->hasField($name))
		{
			return $this->fields[StringHelper::strtoupper($name)];
		}

		throw new Main\ArgumentException(sprintf(
			'%s Entity has no `%s` field.', $this->getName(), $name
		));
	}

	public function hasField($name)
	{
		return isset($this->fields[StringHelper::strtoupper($name)]);
	}

	/**
	 * @return ScalarField[]
	 */
	public function getScalarFields()
	{
		$scalarFields = array();

		foreach ($this->getFields() as $field)
		{
			if ($field instanceof ScalarField)
			{
				$scalarFields[$field->getName()] = $field;
			}
		}

		return $scalarFields;
	}

	/**
	 * @deprecated
	 *
	 * @param $name
	 *
	 * @return UField
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function getUField($name)
	{
		if ($this->hasUField($name))
		{
			return $this->u_fields[$name];
		}

		throw new Main\ArgumentException(sprintf(
			'%s Entity has no `%s` userfield.', $this->getName(), $name
		));
	}

	/**
	 * @deprecated
	 *
	 * @param $name
	 *
	 * @return bool
	 * @throws Main\SystemException
	 */
	public function hasUField($name)
	{
		if (is_null($this->u_fields))
		{
			$this->u_fields = array();

			if($this->uf_id <> '')
			{
				/** @var \CUserTypeManager $USER_FIELD_MANAGER */
				global $USER_FIELD_MANAGER;

				foreach($USER_FIELD_MANAGER->getUserFields($this->uf_id) as $info)
				{
					$this->u_fields[$info['FIELD_NAME']] = new UField($info);
					$this->u_fields[$info['FIELD_NAME']]->setEntity($this);

					// add references for ufield (UF_DEPARTMENT_BY)
					if($info['USER_TYPE_ID'] == 'iblock_section')
					{
						$info['FIELD_NAME'] .= '_BY';
						$this->u_fields[$info['FIELD_NAME']] = new UField($info);
						$this->u_fields[$info['FIELD_NAME']]->setEntity($this);
					}
				}
			}
		}

		return isset($this->u_fields[$name]);
	}

	public function getName()
	{
		return $this->name;
	}

	public function getFullName()
	{
		return substr($this->className, 0, -5);
	}

	public function getNamespace()
	{
		return substr($this->className, 0, strrpos($this->className, '\\') + 1);
	}

	public function getModule()
	{
		if($this->module === null)
		{
			// \Bitrix\Main\Site -> "main"
			// \Partner\Module\Thing -> "partner.module"
			// \Thing -> ""
			$parts = explode("\\", $this->className);
			if($parts[1] == "Bitrix")
				$this->module = strtolower($parts[2]);
			elseif(!empty($parts[1]) && isset($parts[2]))
				$this->module = strtolower($parts[1].".".$parts[2]);
			else
				$this->module = "";
		}
		return $this->module;
	}

	/**
	 * @return DataManager|string
	 */
	public function getDataClass()
	{
		return $this->className;
	}

	/**
	 * @return Main\DB\Connection
	 */
	public function getConnection()
	{
		/** @var Main\DB\Connection $conn */
		$conn = Main\Application::getInstance()->getConnectionPool()->getConnection($this->connectionName);
		return $conn;
	}

	public function getDBTableName()
	{
		return $this->dbTableName;
	}

	public function getPrimary()
	{
		return count($this->primary) == 1 ? $this->primary[0] : $this->primary;
	}

	public function getPrimaryArray()
	{
		return $this->primary;
	}

	public function getAutoIncrement()
	{
		return $this->autoIncrement;
	}

	public function isUts()
	{
		return $this->isUts;
	}

	public function isUtm()
	{
		return $this->isUtm;
	}

	public function getUfId()
	{
		return $this->uf_id;
	}

	/**
	 * @param Query $query
	 *
	 * @return Query
	 */
	public function setDefaultScope($query)
	{
		$dataClass = $this->className;
		return $dataClass::setDefaultScope($query);
	}

	public static function isExists($name)
	{
		return class_exists(static::normalizeEntityClass($name));
	}

	/**
	 * @param $entityName
	 *
	 * @return string|DataManager
	 */
	public static function normalizeEntityClass($entityName)
	{
		if (strtolower(substr($entityName, -5)) !== 'table')
		{
			$entityName .= 'Table';
		}

		if (!str_starts_with($entityName, '\\'))
		{
			$entityName = '\\'.$entityName;
		}

		return $entityName;
	}

	public static function getEntityClassParts($class)
	{
		$class = static::normalizeEntityClass($class);
		$lastPos = strrpos($class, '\\');

		if($lastPos === 0)
		{
			//global namespace
			$namespace = "";
		}
		else
		{
			$namespace = substr($class, 1, $lastPos - 1);
		}
		$name = substr($class, $lastPos + 1, -5);

		return compact('namespace', 'name');
	}

	public function getCode()
	{
		if ($this->code === null)
		{
			$this->code = '';

			// get absolute path to class
			$class_path = explode('\\', strtoupper(ltrim($this->className, '\\')));

			// cut class name to leave namespace only
			$class_path = array_slice($class_path, 0, -1);

			// cut Bitrix namespace
			if (count($class_path) && $class_path[0] === 'BITRIX')
			{
				$class_path = array_slice($class_path, 1);
			}

			// glue module name
			if (!empty($class_path))
			{
				$this->code = join('_', $class_path).'_';
			}

			// glue entity name
			$this->code .= strtoupper(StringHelper::camel2snake($this->getName()));
		}

		return $this->code;
	}

	public function getLangCode()
	{
		return $this->getCode().'_ENTITY';
	}

	public function getTitle()
	{
		$dataClass = $this->getDataClass();
		$title = $dataClass::getTitle();

		if ($title === null)
		{
			$title = Main\Localization\Loc::getMessage($this->getLangCode());
		}

		return $title;
	}

	/**
	 * @deprecated Use Bitrix\StringHelper::camel2snake instead
	 *
	 * @param $str
	 *
	 * @return string
	 */
	public static function camel2snake($str)
	{
		return StringHelper::camel2snake($str);
	}

	/**
	 * @deprecated Use Bitrix\StringHelper::snake2camel instead
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function snake2camel($str)
	{
		return StringHelper::snake2camel($str);
	}

	public static function normalizeName($entityName)
	{
		if (!str_starts_with($entityName, '\\'))
		{
			$entityName = '\\'.$entityName;
		}

		if (strtolower(substr($entityName, -5)) === 'table')
		{
			$entityName = substr($entityName, 0, -5);
		}

		return $entityName;
	}

	public function __clone()
	{
		$this->isClone = true;

		// reset entity in fields
		foreach ($this->fields as $field)
		{
			$field->resetEntity();
			$field->setEntity($this);
		}
	}

	/**
	 * @param Query $query
	 * @param null  $entity_name
	 *
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function getInstanceByQuery(Query $query, &$entity_name = null)
	{
		if ($entity_name === null)
		{
			$entity_name = 'Tmp'.randString().'x';
		}
		elseif (!preg_match('/^[a-z0-9_]+$/i', $entity_name))
		{
			throw new Main\ArgumentException(sprintf(
				'Invalid entity name `%s`.', $entity_name
			));
		}

		$query_string = '('.$query->getQuery().')';
		$query_chains = $query->getChains();

		$replaced_aliases = array_flip($query->getReplacedAliases());

		// generate fieldsMap
		$fieldsMap = array();

		foreach ($query->getSelect() as $k => $v)
		{
			// convert expressions to regular field, clone in case of regular scalar field
			if (is_array($v))
			{
				// expression
				$fieldsMap[$k] = array('data_type' => $v['data_type']);
			}
			else
			{
				if ($v instanceof ExpressionField)
				{
					$fieldDefinition = $v->getName();

					// better to initialize fields as objects after entity is created
					$dataType = Field::getOldDataTypeByField($query_chains[$fieldDefinition]->getLastElement()->getValue());
					$fieldsMap[$fieldDefinition] = array('data_type' => $dataType);
				}
				else
				{
					$fieldDefinition = is_numeric($k) ? $v : $k;

					/** @var Field $field */
					$field = $query_chains[$fieldDefinition]->getLastElement()->getValue();

					if ($field instanceof ExpressionField)
					{
						$dataType = Field::getOldDataTypeByField($query_chains[$fieldDefinition]->getLastElement()->getValue());
						$fieldsMap[$fieldDefinition] = array('data_type' => $dataType);
					}
					else
					{
						/** @var ScalarField[] $fieldsMap */
						$fieldsMap[$fieldDefinition] = clone $field;
						$fieldsMap[$fieldDefinition]->setName($fieldDefinition);
						$fieldsMap[$fieldDefinition]->setColumnName($fieldDefinition);
						$fieldsMap[$fieldDefinition]->resetEntity();
					}
				}
			}

			if (isset($replaced_aliases[$k]))
			{
				if (is_array($fieldsMap[$k]))
				{
					$fieldsMap[$k]['column_name'] = $replaced_aliases[$k];
				}
				elseif ($fieldsMap[$k] instanceof ScalarField)
				{
					/** @var ScalarField[] $fieldsMap */
					$fieldsMap[$k]->setColumnName($replaced_aliases[$k]);
				}
			}
		}

		// generate class content
		$eval = 'class '.$entity_name.'Table extends '.DataManager::class.' {'.PHP_EOL;
		$eval .= 'public static function getMap() {'.PHP_EOL;
		$eval .= 'return '.var_export(['TMP_ID' => ['data_type' => 'integer', 'primary' => true, 'auto_generated' => true]], true).';'.PHP_EOL;
		$eval .= '}';
		$eval .= 'public static function getTableName() {'.PHP_EOL;
		$eval .= 'return '.var_export($query_string, true).';'.PHP_EOL;
		$eval .= '}';
		$eval .= '}';

		eval($eval);

		$entity = self::getInstance($entity_name);

		foreach ($fieldsMap as $k => $v)
		{
			$entity->addField($v, $k);
		}

		return $entity;
	}

	/**
	 * @param string               $entityName
	 * @param null|array[]|Field[] $fields
	 * @param array                $parameters [namespace, table_name, uf_id, parent, parent_map, default_scope]
	 *
	 * @return Entity
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function compileEntity($entityName, $fields = null, $parameters = array())
	{
		$classCode = '';
		$classCodeEnd = '';

		if (strtolower(substr($entityName, -5)) !== 'table')
		{
			$entityName .= 'Table';
		}

		// validation
		if (!preg_match('/^[a-z0-9_]+$/i', $entityName))
		{
			throw new Main\ArgumentException(sprintf(
				'Invalid entity className `%s`.', $entityName
			));
		}

		/** @var string | DataManager $fullEntityName */
		$fullEntityName = $entityName;

		// namespace configuration
		if (!empty($parameters['namespace']) && $parameters['namespace'] !== '\\')
		{
			$namespace = $parameters['namespace'];

			if (!preg_match('/^[a-z0-9_\\\\]+$/i', $namespace))
			{
				throw new Main\ArgumentException(sprintf(
					'Invalid namespace name `%s`', $namespace
				));
			}

			$classCode = $classCode."namespace {$namespace} "."{";
			$classCodeEnd = '}'.$classCodeEnd;

			$fullEntityName = '\\'.$namespace.'\\'.$fullEntityName;
		}

		$parentClass = !empty($parameters['parent']) ? $parameters['parent'] : DataManager::class;

		// build entity code
		$classCode = $classCode."class {$entityName} extends \\".$parentClass." {";
		$classCodeEnd = '}'.$classCodeEnd;

		if (!empty($parameters['table_name']))
		{
			$classCode .= 'public static function getTableName(){return '.var_export($parameters['table_name'], true).';}';
		}

		if (!empty($parameters['uf_id']))
		{
			$classCode .= 'public static function getUfId(){return '.var_export($parameters['uf_id'], true).';}';
		}

		if (!empty($parameters['default_scope']))
		{
			$classCode .= 'public static function setDefaultScope($query){'.$parameters['default_scope'].'}';
		}

		if (isset($parameters['parent_map']) && !$parameters['parent_map'])
		{
			$classCode .= 'public static function getMap(){return [];}';
		}

		if(isset($parameters['object_parent']) && is_a($parameters['object_parent'], EntityObject::class, true))
		{
			$classCode .= 'public static function getObjectParentClass(){return '.var_export($parameters['object_parent'], true).';}';
		}

		// create entity
		eval($classCode.$classCodeEnd);

		$entity = $fullEntityName::getEntity();

		// add fields
		if (!empty($fields))
		{
			foreach ($fields as $fieldName => $field)
			{
				$entity->addField($field, $fieldName);
			}
		}

		return $entity;
	}

	/**
	 * @return string[] Array of SQL queries
	 * @throws Main\SystemException
	 */
	public function compileDbTableStructureDump()
	{
		$fields = $this->getScalarFields();

		/** @var Main\DB\MysqlCommonConnection $connection */
		$connection = $this->getConnection();

		$autocomplete = [];
		$unique = [];

		foreach ($fields as $field)
		{
			if ($field->isAutocomplete())
			{
				$autocomplete[] = $field->getName();
			}

			if ($field->isUnique())
			{
				$unique[] = $field->getName();
			}
		}

		// start collecting queries
		$connection->disableQueryExecuting();

		// create table
		$connection->createTable($this->getDBTableName(), $fields, $this->getPrimaryArray(), $autocomplete);

		// create indexes
		foreach ($unique as $fieldName)
		{
			$connection->createIndex($this->getDBTableName(), $fieldName, [$fieldName], null,
				Main\DB\Connection::INDEX_UNIQUE);
		}

		// stop collecting queries
		$connection->enableQueryExecuting();

		return $connection->getDisabledQueryExecutingDump();
	}

	/**
	 * @param $dataClass
	 *
	 * @return EntityObject|string
	 */
	public static function compileObjectClass($dataClass)
	{
		$dataClass = static::normalizeEntityClass($dataClass);
		$classParts = static::getEntityClassParts($dataClass);

		if (class_exists($dataClass::getObjectClass(), false)
			&& is_subclass_of($dataClass::getObjectClass(), EntityObject::class))
		{
			// class is already defined
			return $dataClass::getObjectClass();
		}

		$baseObjectClass = '\\'.$dataClass::getObjectParentClass();
		$objectClassName = static::getDefaultObjectClassName($classParts['name']);

		$eval = "";
		if($classParts['namespace'] <> '')
		{
			$eval .= "namespace {$classParts['namespace']} {";
		}
		$eval .= "class {$objectClassName} extends {$baseObjectClass} {";
		$eval .= "static public \$dataClass = '{$dataClass}';";
		$eval .= "}"; // end class
		if($classParts['namespace'] <> '')
		{
			$eval .= "}"; // end namespace
		}

		eval($eval);

		return $dataClass::getObjectClass();
	}

	/**
	 * @param $dataClass
	 *
	 * @return Collection|string
	 */
	public static function compileCollectionClass($dataClass)
	{
		$dataClass = static::normalizeEntityClass($dataClass);
		$classParts = static::getEntityClassParts($dataClass);

		if (class_exists($dataClass::getCollectionClass(), false)
			&& is_subclass_of($dataClass::getCollectionClass(), Collection::class))
		{
			// class is already defined
			return $dataClass::getCollectionClass();
		}

		$baseCollectionClass = '\\'.$dataClass::getCollectionParentClass();
		$collectionClassName = static::getDefaultCollectionClassName($classParts['name']);

		$eval = "";
		if($classParts['namespace'] <> '')
		{
			$eval .= "namespace {$classParts['namespace']} {";
		}
		$eval .= "class {$collectionClassName} extends {$baseCollectionClass} {";
		$eval .= "static public \$dataClass = '{$dataClass}';";
		$eval .= "}"; // end class
		if($classParts['namespace'] <> '')
		{
			$eval .= "}"; // end namespace
		}

		eval($eval);

		return $dataClass::getCollectionClass();
	}

	/**
	 * Creates table according to Fields collection
	 *
	 * @return void
	 * @throws Main\SystemException
	 */
	public function createDbTable()
	{
		foreach ($this->compileDbTableStructureDump() as $sqlQuery)
		{
			$this->getConnection()->query($sqlQuery);
		}
	}

	/**
	 * @param Entity|string $entity
	 *
	 * @return bool
	 */
	public static function destroy($entity)
	{
		if ($entity instanceof Entity)
		{
			$entityName = $entity->getDataClass();
		}
		else
		{
			$entityName = static::normalizeEntityClass($entity);
		}

		if (isset(self::$instances[$entityName]))
		{
			unset(self::$instances[$entityName]);
			DataManager::unsetEntity($entityName);

			return true;
		}

		return false;
	}

	public static function onUserTypeChange($userfield, $id = null)
	{
		// resolve UF ENTITY_ID
		if (!empty($userfield['ENTITY_ID']))
		{
			$ufEntityId = $userfield['ENTITY_ID'];
		}
		elseif (!empty($id))
		{
			$usertype = new \CUserTypeEntity();
			$userfield =  $usertype->GetList([], ["ID" => $id])->Fetch();

			if ($userfield)
			{
				$ufEntityId = $userfield['ENTITY_ID'];
			}
		}

		if (empty($ufEntityId))
		{
			throw new Main\ArgumentException('Invalid ENTITY_ID');
		}

		// find orm entity with uf ENTITY_ID
		if (!empty(static::$ufIdIndex[$ufEntityId]))
		{
			if (!empty(static::$instances[static::$ufIdIndex[$ufEntityId]]))
			{
				// clear for further reinitialization
				static::destroy(static::$instances[static::$ufIdIndex[$ufEntityId]]);
			}
		}
	}

	/**
	 * Reads data from cache.
	 *
	 * @param int    $ttl        TTL.
	 * @param string $cacheId    The cache ID.
	 * @param bool   $countTotal Whether to read total count from the cache.
	 *
	 * @return Main\DB\ArrayResult|null
	 */
	public function readFromCache($ttl, $cacheId, $countTotal = false)
	{
		if($ttl > 0)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cacheDir = $this->getCacheDir();

			$count = null;
			if($countTotal)
			{
				if ($cache->read($ttl, $cacheId.".total", $cacheDir))
				{
					$count = $cache->get($cacheId.".total");
				}
				else
				{
					// invalidate cache
					return null;
				}
			}
			if($cache->read($ttl, $cacheId, $cacheDir))
			{
				$result = new Main\DB\ArrayResult($cache->get($cacheId));
				if($count !== null)
				{
					$result->setCount($count);
				}
				return $result;
			}
		}
		return null;
	}

	/**
	 * @param Main\DB\Result $result     A query result to cache.
	 * @param string         $cacheId    The cache ID.
	 * @param bool           $countTotal Whether to write total count to the cache.
	 *
	 * @return Main\DB\ArrayResult
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function writeToCache(Main\DB\Result $result, $cacheId, $countTotal = false)
	{
		$rows = $result->fetchAll();
		$arrayResult = new Main\DB\ArrayResult($rows);

		$cache = Main\Application::getInstance()->getManagedCache();
		$cache->set($cacheId, $rows);

		if($countTotal)
		{
			$count = $result->getCount();
			$cache->set($cacheId.".total", $count);
			$arrayResult->setCount($count);
		}
		return $arrayResult;
	}

	/**
	 * Returns cache TTL for the entity, possibly limited by the .settings.php:
	 * 'cache_flags' => array('value'=> array(
	 *		"b_group_max_ttl" => 200,
	 *		"b_group_min_ttl" => 100,
	 * ))
	 * Maximum is a higher-priority.
	 * @param int $ttl Preferable TTL
	 * @return int Calculated TTL
	 */
	public function getCacheTtl($ttl)
	{
		if (!$this->className::isCacheable())
		{
			// cache is disabled in the tablet
			return 0;
		}

		$table = $this->getDBTableName();
		$cacheFlags = Main\Config\Configuration::getValue("cache_flags");
		if(isset($cacheFlags[$table."_min_ttl"]))
		{
			$ttl = (int)max($ttl, $cacheFlags[$table."_min_ttl"]);
		}
		if(isset($cacheFlags[$table."_max_ttl"]))
		{
			$ttl = (int)min($ttl, $cacheFlags[$table."_max_ttl"]);
		}
		return $ttl;
	}

	protected function getCacheDir()
	{
		return "orm_".$this->getDBTableName();
	}

	/**
	 * Cleans all cache entries for the entity.
	 */
	public function cleanCache()
	{
		if($this->getCacheTtl(100) > 0)
		{
			//cache might be disabled in .settings.php via *_max_ttl = 0 option
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->cleanDir($this->getCacheDir());
		}
	}

	/**
	 * Sets a flag indicating full text index support for a field.
	 *
	 * @deprecated Does nothing, mysql 5.6 has fulltext always enabled.
	 * @param string $field
	 * @param bool   $mode
	 */
	public function enableFullTextIndex($field, $mode = true)
	{
	}

	/**
	 * Returns true if full text index is enabled for a field.
	 *
	 * @deprecated Always returns true, mysql 5.6 has fulltext always enabled.
	 * @param string $field
	 * @return bool
	 */
	public function fullTextIndexEnabled($field)
	{
		return true;
	}
}
