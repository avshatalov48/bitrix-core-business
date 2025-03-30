<?php

namespace Bitrix\Main\UserField\Internal;

use Bitrix\Main\Application;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Validator\RegExp;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;

/**
 * @deprecated
 */
abstract class TypeDataManager extends DataManager
{
	public const MAXIMUM_TABLE_NAME_LENGTH = 64;

	protected static $temporaryStorage;

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new StringField('NAME'))
				->configureRequired()
				->configureUnique()
				->configureSize(100)
				->configureFormat('/^[A-Z][A-Za-z0-9]*$/')
				->addValidator(new RegExp(
					'/(?<!Table)$/i'
				)),
			(new StringField('TABLE_NAME'))
				->configureRequired()
				->configureUnique()
				->configureSize(64)
				->configureFormat('/^[a-z0-9_]+$/')
				->addValidator([get_called_class(), 'validateTableExisting']),
		];
	}

	public static function getFactory(): TypeFactory
	{
		return Registry::getInstance()->getFactoryByTypeDataClass(static::class);
	}

	protected static function getTemporaryStorage(): TemporaryStorage
	{
		if(!static::$temporaryStorage)
		{
			static::$temporaryStorage = new TemporaryStorage();
		}

		return static::$temporaryStorage;
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 * @throws SystemException
	 */
	public static function onAfterAdd(Event $event): EventResult
	{
		$id = $event->getParameter('id');

		static::compileEntity($id)->createDbTable();

		return new EventResult();
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 */
	public static function onBeforeUpdate(Event $event): EventResult
	{
		$data = static::getByPrimary($event->getParameter('id'))->fetch();
		static::getTemporaryStorage()->saveData($event->getParameter('id'), $data);

		return new EventResult();
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function onAfterUpdate(Event $event): EventResult
	{
		$id = $event->getParameter('id');
		$data = $event->getParameter('fields');
		$oldData = static::getTemporaryStorage()->getData($id);
		if(!$oldData)
		{
			return new EventResult();
		}

		// rename table in db
		if (isset($data['TABLE_NAME']) && $data['TABLE_NAME'] !== $oldData['TABLE_NAME'])
		{
			$userFieldManager = UserFieldHelper::getInstance()->getManager();
			$connection = Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$connection->renameTable($oldData['TABLE_NAME'], $data['TABLE_NAME']);

			if ($connection instanceof MssqlConnection)
			{
				// rename constraint
				$connection->query(sprintf(
					"EXEC sp_rename %s, %s, 'OBJECT'",
					$sqlHelper->quote($oldData['TABLE_NAME'].'_ibpk_1'),
					$sqlHelper->quote($data['TABLE_NAME'].'_ibpk_1')
				));
			}

			// rename also uf multiple tables and its constraints, sequences, and triggers
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			foreach ($userFieldManager->getUserFields(static::getFactory()->getUserFieldEntityId($oldData['ID'])) as $field)
			{
				if ($field['MULTIPLE'] == 'Y')
				{
					$oldUtmTableName = static::getMultipleValueTableName($oldData, $field);
					$newUtmTableName = static::getMultipleValueTableName($data, $field);

					$connection->renameTable($oldUtmTableName, $newUtmTableName);
				}
			}
		}

		return new EventResult();
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function onBeforeDelete(Event $event): EventResult
	{
		$result = new EventResult();

		$id = $event->getParameter('id');
		$oldData = static::getByPrimary($id)->fetch();
		static::getTemporaryStorage()->saveData($id, $oldData);
		if(!$oldData)
		{
			return $result;
		}

		$userFieldManager = UserFieldHelper::getInstance()->getManager();

		// get file fields
		$file_fields = [];
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		$fields = $userFieldManager->getUserFields(static::getFactory()->getUserFieldEntityId($oldData['ID']));

		foreach ($fields as $name => $field)
		{
			if ($field['USER_TYPE']['BASE_TYPE'] === 'file')
			{
				$file_fields[] = $name;
			}
		}

		// delete files
		if (!empty($file_fields))
		{
			$oldEntity = static::compileEntity($oldData);

			$query = new Query($oldEntity);

			// select file ids
			$query->setSelect($file_fields);

			// if they are not empty
			$filter = array('LOGIC' => 'OR');

			foreach ($file_fields as $file_field)
			{
				$filter['!'.$file_field] = false;
			}

			$query->setFilter($filter);

			// go
			$queryResult = $query->exec();

			while ($row = $queryResult->fetch())
			{
				foreach ($file_fields as $file_field)
				{
					if (!empty($row[$file_field]))
					{
						if (is_array($row[$file_field]))
						{
							foreach ($row[$file_field] as $value)
							{
								\CFile::delete($value);
							}
						}
						else
						{
							\CFile::delete($row[$file_field]);
						}
					}
				}
			}
		}

		$connection = Application::getConnection();

		foreach ($fields as $field)
		{
			// delete from uf registry
			if ($field['USER_TYPE']['BASE_TYPE'] === 'enum')
			{
				$enumField = new \CUserFieldEnum;
				$enumField->DeleteFieldEnum($field['ID']);
			}

			$connection->query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = ".$field['ID']);
			$connection->query("DELETE FROM b_user_field WHERE ID = ".$field['ID']);

			// if multiple - drop utm table
			if ($field['MULTIPLE'] == 'Y')
			{
				try
				{
					$utmTableName = static::getMultipleValueTableName($oldData, $field);
					$connection->dropTable($utmTableName);
				}
				catch(SqlQueryException $e)
				{
					$result->addError(new EntityError($e->getMessage()));
				}
			}
		}

		// clear uf cache
		$managedCache = Application::getInstance()->getManagedCache();
		if(CACHED_b_user_field !== false)
		{
			$managedCache->cleanDir("b_user_field");
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 * @throws SystemException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function onAfterDelete(Event $event): EventResult
	{
		$id = $event->getParameter('id');
		$oldData = static::getTemporaryStorage()->getData($id);
		if(!$oldData)
		{
			return new EventResult();
		}

		if(Application::getConnection()->isTableExists($oldData['TABLE_NAME']))
		{
			Application::getConnection()->dropTable($oldData['TABLE_NAME']);
		}

		return new EventResult();
	}

	/**
	 * @param array|int|string|Type $type Could be an object, an array, ID or NAME of block.
	 * @return array|null
	 */
	public static function resolveType($type): ?array
	{
		if($type instanceof Type)
		{
			$type = $type->collectValues();
		}
		if (!is_array($type))
		{
			if (is_int($type) || is_numeric(mb_substr($type, 0, 1)))
			{
				// we have an id
				$type = static::getById($type)->fetch();
			}
			elseif (is_string($type) && $type !== '')
			{
				// we have a name
				$type = static::query()->addSelect('*')->where('NAME', $type)->exec()->fetch();
			}
			else
			{
				$type = null;
			}
		}
		if (empty($type))
			return null;

		if (!isset($type['ID']))
			return null;
		if (!isset($type['NAME']) || !preg_match('/^[a-z0-9_]+$/i', $type['NAME']))
			return null;
		if (empty($type['TABLE_NAME']))
			return null;

		return $type;
	}

	/**
	 * @param $type
	 * @return Entity
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function compileEntity($type): Entity
	{
		$rawType = $type;
		$type = static::resolveType($type);
		if (empty($type))
		{
			throw new SystemException(sprintf(
				'Invalid type description `%s`.', mydump($rawType)
			));
		}
		$factory = static::getFactory();
		$type['code'] = $factory->getCode();
		/** @var PrototypeItemDataManager $itemDataClass */
		$itemDataClass = $factory->getItemPrototypeDataClass();
		$userFieldManager = UserFieldHelper::getInstance()->getManager();

		$userFields = $userFieldManager->getUserFields($factory->getUserFieldEntityId($type['ID']));

		$entityName = $type['code'] . '_items_' . $type['ID'];
		$entityClassName = $entityName.'Table';
		$entityTableName = $type['TABLE_NAME'];
		if(class_exists($entityClassName))
		{
			// rebuild if it already exists
			Entity::destroy($entityClassName);
			$entity = Entity::getInstance($entityClassName);
		}
		else
		{
			$entity = Entity::compileEntity($entityName, [], [
				'table_name' => $entityTableName,
				'parent' => $itemDataClass,
				'object_parent' => $factory->getItemParentClass(),
				//'namespace' => __NAMESPACE__,
			]);
		}
		Registry::getInstance()->registerTypeByEntity($entity, $type);

		foreach ($userFields as $userField)
		{
			if ($userField['MULTIPLE'] == 'N')
			{
				// just add single field
				$params = [
					'required' => ($userField['MANDATORY'] === 'Y')
				];
				$field = $userFieldManager->getEntityField($userField, $userField['FIELD_NAME'], $params);
				$entity->addField($field);
				foreach ($userFieldManager->getEntityReferences($userField, $field) as $reference)
				{
					$entity->addField($reference);
				}
			}
			else
			{
				// build utm entity
				static::compileUtmEntity($entity, $userField);
			}
		}

		return $entity;
	}

	/**
	 * @param Entity $typeEntity
	 * @param $userField
	 * @return mixed
	 */
	protected static function compileUtmEntity(Entity $typeEntity, $userField): Entity
	{
		$userFieldManager = UserFieldHelper::getInstance()->getManager();

		// build utm entity
		/** @var PrototypeItemDataManager $itemDataClass */
		$itemDataClass = $typeEntity->getDataClass();
		$typeData = $itemDataClass::getType();

		$utmClassName = static::getUtmEntityClassName($typeEntity, $userField);
		$utmTableName = static::getMultipleValueTableName($typeData, $userField);

		if (class_exists($utmClassName.'Table'))
		{
			// rebuild if it already exists
			Entity::destroy($utmClassName.'Table');
			$utmEntity = Entity::getInstance($utmClassName);
		}
		else
		{
			// create entity from scratch
			$utmEntity = Entity::compileEntity($utmClassName, [], [
				'table_name' => $utmTableName,
				//'namespace' => __NAMESPACE__,
			]);
		}

		// main fields
		$utmValueField = $userFieldManager->getEntityField($userField, 'VALUE');

		$utmEntityFields = array(
			new IntegerField('ID'),
			$utmValueField
		);

		// references
		$references = $userFieldManager->getEntityReferences($userField, $utmValueField);

		foreach ($references as $reference)
		{
			$utmEntityFields[] = $reference;
		}

		foreach ($utmEntityFields as $field)
		{
			$utmEntity->addField($field);
		}

		// add original entity reference
		$referenceField = new Reference(
			'OBJECT',
			$typeEntity,
			['=this.ID' => 'ref.ID']
		);

		$utmEntity->addField($referenceField);

		// add short alias for back-reference
		$aliasField = new ExpressionField(
			$userField['FIELD_NAME'].'_SINGLE',
			'%s',
			$utmEntity->getFullName().':'.'OBJECT.VALUE',
			array(
				'data_type' => get_class($utmEntity->getField('VALUE')),
				'required' => $userField['MANDATORY'] == 'Y'
			)
		);

		$typeEntity->addField($aliasField);

		$cacheField = new ArrayField($userField['FIELD_NAME']);
		$cacheField->configureRequired($userField['MANDATORY'] === 'Y');
		\Bitrix\Main\UserField\Internal\MultipleFieldSerializer::setMultipleFieldSerialization($cacheField, $utmValueField, $userField);
		$typeEntity->addField($cacheField);

		return $utmEntity;
	}

	/**
	 * @param Entity $typeEntity
	 * @param $userField
	 * @return string
	 */
	public static function getUtmEntityClassName(Entity $typeEntity, array $userField): string
	{
		return $typeEntity->getName() . 'Utm' . StringHelper::snake2camel($userField['FIELD_NAME']);
	}

	/**
	 * @param $type
	 * @param $userField
	 * @return string
	 */
	public static function getMultipleValueTableName(array $type, array $userField): string
	{
		$tableName = $type['TABLE_NAME'] . '_' . mb_strtolower($userField['FIELD_NAME']);

		if (mb_strlen($tableName) > static::MAXIMUM_TABLE_NAME_LENGTH && !empty($userField['ID']))
		{
			$tableName = $type['TABLE_NAME'] . '_' . $userField['ID'];
		}

		return $tableName;
	}

	public static function validateTableExisting($value, $primary, array $row, Field $field)
	{
		$checkName = null;

		if (empty($primary))
		{
			// new row
			$checkName = $value;
		}
		else
		{
			$factory = static::getFactory();
			$typeDataClass = $factory->getTypeDataClass();
			// update row
			$oldData = $typeDataClass::getByPrimary($primary)->fetch();

			if ($value != $oldData['TABLE_NAME'])
			{
				// table name has been changed for existing row
				$checkName = $value;
			}
		}

		if (!empty($checkName))
		{
			if (Application::getConnection()->isTableExists($checkName))
			{
				Loc::loadLanguageFile(__DIR__.'/highloadblock.php');
				return Loc::getMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_TABLE_NAME_ALREADY_EXISTS',
					['#TABLE_NAME#' => $value]
				);
			}
		}

		return true;
	}

	public static function getObjectParentClass(): string
	{
		return Type::class;
	}
}