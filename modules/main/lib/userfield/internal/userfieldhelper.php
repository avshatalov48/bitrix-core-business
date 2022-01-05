<?php

namespace Bitrix\Main\UserField\Internal;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Query\Query;

final class UserFieldHelper
{
	/** @var UserFieldHelper */
	private static $instance;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	/**
	 * Returns Singleton of Driver
	 * @return UserFieldHelper
	 */
	public static function getInstance(): UserFieldHelper
	{
		if (!isset(self::$instance))
		{
			self::$instance = new UserFieldHelper;
		}

		return self::$instance;
	}

	/**
	 * @return \CUserTypeManager
	 */
	public function getManager(): ?\CUserTypeManager
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER;
	}

	/**
	 * @return \CAllMain
	 */
	public function getApplication(): ?\CAllMain
	{
		global $APPLICATION;

		return $APPLICATION;
	}

	/**
	 * @param $entityId
	 * @return array|null
	 */
	public function parseUserFieldEntityId(string $entityId): ?array
	{
		if(preg_match('/^([A-Z]+)_([0-9A-Z_]+)$/', $entityId, $matches))
		{
			$typeCode = TypeFactory::getCodeByPrefix($matches[1]);
			$factory = Registry::getInstance()->getFactoryByCode($typeCode);
			if($factory)
			{
				$typeId = $factory->prepareIdentifier($matches[2]);
				if ($typeId > 0)
				{
					return [$factory, $typeId];
				}
			}
		}

		return null;
	}

	/**
	 * @param $field
	 * @return array|bool
	 */
	public static function OnBeforeUserTypeAdd($field)
	{
		if (static::getInstance()->parseUserFieldEntityId($field['ENTITY_ID']))
		{
			if (mb_substr($field['FIELD_NAME'], -4) === '_REF')
			{
				/**
				 * postfix _REF reserved for references to other highloadblocks
				 * @see CUserTypeHlblock::getEntityReferences
				 */
				global $APPLICATION;

				Loc::loadLanguageFile(__DIR__.'/highloadblock.php');
				$APPLICATION->ThrowException(
					Loc::getMessage('HIGHLOADBLOCK_HIGHLOAD_BLOCK_ENTITY_FIELD_NAME_REF_RESERVED')
				);

				return false;
			}

			return [
				'PROVIDE_STORAGE' => false
			];
		}

		return true;
	}

	/**
	 * @param $field
	 * @return array|bool
	 */
	public static function onAfterUserTypeAdd($field)
	{
		$userFieldHelper = static::getInstance();
		$parseResult = $userFieldHelper->parseUserFieldEntityId($field['ENTITY_ID']);
		if($parseResult)
		{
			[$factory, $typeId] = $parseResult;
			$userFieldManager = $userFieldHelper->getManager();
			$application = $userFieldHelper->getApplication();
			/** @var TypeFactory $factory */
			/** @var TypeDataManager $dataClass */
			$dataClass = $factory->getTypeDataClass();

			$field['USER_TYPE'] = $userFieldManager->getUserType($field['USER_TYPE_ID']);

			$typeData = $dataClass::getById($typeId)->fetch();

			if (empty($typeData))
			{
				$application->throwException(sprintf(
					'Entity "%s" wasn\'t found.', $factory->getUserFieldEntityId($typeId)
				));

				return false;
			}

			// get usertype info
			$sql_column_type = $userFieldManager->getUtsDBColumnType($field);

			// create field in db
			$connection = Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();

			$connection->query(sprintf(
				'ALTER TABLE %s ADD %s %s',
				$sqlHelper->quote($typeData['TABLE_NAME']), $sqlHelper->quote($field['FIELD_NAME']), $sql_column_type
			));

			if ($field['MULTIPLE'] == 'Y')
			{
				// create table for this relation
				$typeEntity = $dataClass::compileEntity($typeData);
				$utmEntity = Entity::getInstance($dataClass::getUtmEntityClassName($typeEntity, $field));

				$utmEntity->createDbTable();

				// add indexes
				$connection->query(sprintf(
					'CREATE INDEX %s ON %s (%s)',
					$sqlHelper->quote('IX_UTM_HL'.$typeId.'_'.$field['ID'].'_ID'),
					$sqlHelper->quote($utmEntity->getDBTableName()),
					$sqlHelper->quote('ID')
				));

				$connection->query(sprintf(
					'CREATE INDEX %s ON %s (%s)',
					$sqlHelper->quote('IX_UTM_HL'.$typeId.'_'.$field['ID'].'_VALUE'),
					$sqlHelper->quote($utmEntity->getDBTableName()),
					$sqlHelper->quote('VALUE')
				));
			}

			return [
				'PROVIDE_STORAGE' => false
			];
		}

		return true;
	}

	/**
	 * @param $field
	 * @return array|bool
	 */
	public static function OnBeforeUserTypeDelete($field)
	{
		$userFieldHelper = static::getInstance();
		$parseResult = $userFieldHelper->parseUserFieldEntityId($field['ENTITY_ID']);
		if($parseResult)
		{
			/** @var TypeFactory $factory */
			[$factory, $typeId] = $parseResult;
			/** @var TypeDataManager $dataClass */
			$dataClass = $factory->getTypeDataClass();
			// get entity info
			$typeData = $dataClass::getById($typeId)->fetch();

			if (empty($typeData))
			{
				// non-existent or zombie. let it go
				return [
					'PROVIDE_STORAGE' => false,
				];
			}

			$userFieldManager = $userFieldHelper->getManager();
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			$fieldType = $userFieldManager->getUserType($field["USER_TYPE_ID"]);

			if ($fieldType['BASE_TYPE'] === 'file')
			{
				// if it was file field, then delete all files
				$itemEntity = $dataClass::compileEntity($typeData);
				$query = new Query($itemEntity);
				$rows = $query->addSelect($field['FIELD_NAME'])->exec();

				while ($oldData = $rows->fetch())
				{
					if (empty($oldData[$field['FIELD_NAME']]))
					{
						continue;
					}

					if(is_array($oldData[$field['FIELD_NAME']]))
					{
						foreach($oldData[$field['FIELD_NAME']] as $value)
						{
							\CFile::delete($value);
						}
					}
					else
					{
						\CFile::delete($oldData[$field['FIELD_NAME']]);
					}
				}
			}

			// drop db column
			$connection = Application::getConnection();
			try
			{
				$connection->dropColumn($typeData['TABLE_NAME'], $field['FIELD_NAME']);
			}
			catch(SqlQueryException $e)
			{
				// no column is ok
			}

			// if multiple - drop utm table
			if ($field['MULTIPLE'] === 'Y')
			{
				$utmTableName = $dataClass::getMultipleValueTableName($typeData, $field);
				if ($connection->isTableExists($utmTableName))
				{
					$connection->dropTable($utmTableName);
				}
			}

			return [
				'PROVIDE_STORAGE' => false,
			];
		}

		return true;
	}

	public static function onGetUserFieldValues(Event $event): EventResult
	{
		$result = new EventResult(EventResult::SUCCESS);

		$entityId = $event->getParameter('entityId');
		$userFieldHelper = static::getInstance();
		$parseResult = $userFieldHelper->parseUserFieldEntityId($entityId);
		if($parseResult)
		{
			$userFields = $event->getParameter('userFields');
			$value = $event->getParameter('value');

			/** @var TypeFactory $factory */
			[$factory, $typeId] = $parseResult;
			$dataClass = $factory->getTypeDataClass();
			$typeData = $dataClass::getById($typeId)->fetch();
			if(!$typeData)
			{
				return $result;
			}
			$itemDataClass = $factory->getItemDataClass($typeData);
			$values = $itemDataClass::getUserFieldValues($value, $userFields);

			if(!$values)
			{
				$values = [];
			}

			$result = new EventResult(EventResult::SUCCESS, [
				'values' => $values,
			]);
		}

		return $result;
	}

	public static function onUpdateUserFieldValues(Event $event): EventResult
	{
		$result = new EventResult(EventResult::UNDEFINED);

		$entityId = $event->getParameter('entityId');
		$userFieldHelper = static::getInstance();
		$parseResult = $userFieldHelper->parseUserFieldEntityId($entityId);
		if($parseResult)
		{
			$fields = $event->getParameter('fields');
			$id = $event->getParameter('id');

			/** @var TypeFactory $factory */
			[$factory, $typeId] = $parseResult;
			$dataClass = $factory->getTypeDataClass();
			$typeData = $dataClass::getById($typeId)->fetch();
			if(!$typeData)
			{
				return $result;
			}
			$itemDataClass = $factory->getItemDataClass($typeData);
			$updateResult = $itemDataClass::updateUserFieldValues($id, $fields);
			if($updateResult->isSuccess())
			{
				$result = new EventResult(EventResult::SUCCESS);
			}
			else
			{
				$result = new EventResult(EventResult::ERROR);
			}
		}

		return $result;
	}

	public static function onDeleteUserFieldValues(Event $event): EventResult
	{
		$result = new EventResult(EventResult::UNDEFINED);

		$entityId = $event->getParameter('entityId');
		$userFieldHelper = static::getInstance();
		$parseResult = $userFieldHelper->parseUserFieldEntityId($entityId);
		if($parseResult)
		{
			$id = $event->getParameter('id');

			/** @var TypeFactory $factory */
			[$factory, $typeId] = $parseResult;
			$dataClass = $factory->getTypeDataClass();
			$typeData = $dataClass::getById($typeId)->fetch();
			if(!$typeData)
			{
				return $result;
			}
			$itemDataClass = $factory->getItemDataClass($typeData);
			$updateResult = $itemDataClass::deleteUserFieldValues($id);
			if($updateResult->isSuccess())
			{
				$result = new EventResult(EventResult::SUCCESS);
			}
			else
			{
				$result = new EventResult(EventResult::ERROR);
			}
		}

		return $result;
	}
}