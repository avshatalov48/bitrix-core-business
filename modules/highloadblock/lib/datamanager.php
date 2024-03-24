<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage highloadblock
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Highloadblock;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Objectify\Values;

abstract class DataManager extends Entity\DataManager
{
	/**
	 * Being redefined in HL classes
	 * @return null
	 */
	public static function getHighloadBlock()
	{
		return null;
	}

	public static function checkFields(Entity\Result $result, $primary, array $data)
	{
		// check for unknown fields
		foreach ($data as $k => $v)
		{
			if (!(static::getEntity()->hasField($k) && static::getEntity()->getField($k) instanceof Entity\ScalarField))
			{
				throw new Main\SystemException(sprintf(
					'Field `%s` not found in entity when trying to query %s row.',
					$k, static::getEntity()->getName()
				));
			}
		}
	}

	/**
	 * @param array $data
	 *
	 * @return Entity\AddResult
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function add(array $data)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;

		// prepare entity object for compatibility with new code
		$object = static::convertArrayToObject($data, true);

		$result = new Entity\AddResult;
		$hlblock = static::getHighloadBlock();
		$entity = static::getEntity();

		try
		{
			static::callOnBeforeAddEvent($object, $data, $result);

			// actualize old-style fields array from object
			$data = $object->collectValues(Values::CURRENT, FieldTypeMask::SCALAR);

			// check data by uf manager
			if (!$USER_FIELD_MANAGER->checkFields('HLBLOCK_'.$hlblock['ID'], null, $data))
			{
				if(is_object($APPLICATION) && $APPLICATION->getException())
				{
					$e = $APPLICATION->getException();
					$result->addError(new Entity\EntityError($e->getString()));
					$APPLICATION->resetException();
				}
				else
				{
					$result->addError(new Entity\EntityError("Unknown error while checking userfields"));
				}
			}

			// return if any error
			if (!$result->isSuccess(true))
			{
				return $result;
			}

			//event on adding
			self::callOnAddEvent($object, $data, []);

			// insert base row
			$connection = Main\Application::getConnection();

			$tableName = $entity->getDBTableName();
			$identity = $entity->getAutoIncrement();

			$id = $connection->add($tableName, [$identity => new Main\DB\SqlExpression('DEFAULT')], $identity);

			// format data before save
			$fields = $USER_FIELD_MANAGER->getUserFields('HLBLOCK_'.$hlblock['ID']);

			foreach ($fields as $k => $field)
			{
				$fields[$k]['VALUE_ID'] = $id;
			}

			list($data, $multiValues) = static::convertValuesBeforeSave($data, $fields);

			// data could be changed by uf manager
			foreach ($data as $k => $v)
			{
				$object->set($k, $v);
			}

			// use save modifiers
			foreach ($data as $fieldName => $value)
			{
				$field = static::getEntity()->getField($fieldName);
				$data[$fieldName] = $field->modifyValueBeforeSave($value, $data);
			}

			if (!empty($data))
			{
				// save data
				$helper = $connection->getSqlHelper();
				$update = $helper->prepareUpdate($tableName, $data);

				$sql = "UPDATE ".$helper->quote($tableName)." SET ".$update[0]." WHERE ".$helper->quote($identity)." = ".((int) $id);
				$connection->queryExecute($sql, $update[1]);
			}

			// save multi values
			if (!empty($multiValues))
			{
				foreach ($multiValues as $userfieldName => $values)
				{
					$utmTableName = HighloadBlockTable::getMultipleValueTableName($hlblock, $fields[$userfieldName]);

					foreach ($values as $value)
					{
						$connection->add($utmTableName, array('ID' => $id, 'VALUE' => $value));
					}
				}
			}

			// build standard primary
			$primary = null;

			if (!empty($id))
			{
				$primary = array($entity->getAutoIncrement() => $id);
				static::normalizePrimary($primary);
			}
			else
			{
				static::normalizePrimary($primary, $data);
			}

			// fill result
			$result->setPrimary($primary);
			$result->setData($data);
			$result->setObject($object);

			foreach ($primary as $primaryName => $primaryValue)
			{
				$object->sysSetActual($primaryName, $primaryValue);
			}

			$entity->cleanCache();

			static::callOnAfterAddEvent($object, $data, $id);
		}
		catch (\Exception $e)
		{
			// check result to avoid warning
			$result->isSuccess();

			throw $e;
		}

		return $result;
	}

	/**
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return Entity\UpdateResult
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function update($primary, array $data)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;

		// check primary
		static::normalizePrimary(
			$primary, isset($data["fields"]) && is_array($data["fields"]) ? $data["fields"] : $data
		);
		static::validatePrimary($primary);

		/** @var EntityObject $object prepare entity object for compatibility with new code */
		$object = static::convertArrayToObject($data, false, $primary);

		//$oldData = static::getByPrimary($primary)->fetch();
		$object->fill(FieldTypeMask::SCALAR);
		$oldData = $object->collectValues(Values::ACTUAL, FieldTypeMask::SCALAR);

		$hlblock = static::getHighloadBlock();
		$entity = static::getEntity();
		$result = new UpdateResult();

		try
		{
			static::callOnBeforeUpdateEvent($object, $data, $result);

			// actualize old-style fields array from object
			$data = $object->collectValues(Values::CURRENT, FieldTypeMask::SCALAR);

			// check data by uf manager CheckFieldsWithOldData
			if (!$USER_FIELD_MANAGER->checkFieldsWithOldData('HLBLOCK_'.$hlblock['ID'], $oldData, $data))
			{
				if(is_object($APPLICATION) && $APPLICATION->getException())
				{
					$e = $APPLICATION->getException();
					$result->addError(new Entity\EntityError($e->getString()));
					$APPLICATION->resetException();
				}
				else
				{
					$result->addError(new Entity\EntityError("Unknown error while checking userfields"));
				}
			}

			// return if any error
			if (!$result->isSuccess(true))
			{
				return $result;
			}

			static::callOnUpdateEvent($object, $data, []);

			// format data before save
			$fields = $USER_FIELD_MANAGER->getUserFieldsWithReadyData('HLBLOCK_'.$hlblock['ID'], $oldData, LANGUAGE_ID, false, 'ID');
			list($data, $multiValues) = static::convertValuesBeforeSave($data, $fields);

			// data could be changed by uf manager
			foreach ($data as $k => $v)
			{
				$object->set($k, $v);
			}

			// use save modifiers
			foreach ($data as $fieldName => $value)
			{
				$field = static::getEntity()->getField($fieldName);
				$data[$fieldName] = $field->modifyValueBeforeSave($value, $data);
			}

			$connection = Main\Application::getConnection();

			if (!empty($data))
			{
				// save data
				$helper = $connection->getSqlHelper();
				$tableName = $entity->getDBTableName();

				$update = $helper->prepareUpdate($tableName, $data);

				$id = array();
				foreach ($primary as $k => $v)
				{
					$id[] = $helper->prepareAssignment($tableName, $k, $v);
				}
				$where = implode(' AND ', $id);

				$sql = "UPDATE ".$helper->quote($tableName)." SET ".$update[0]." WHERE ".$where;
				$connection->queryExecute($sql, $update[1]);
			}

			$result->setAffectedRowsCount($connection);
			$result->setData($data);
			$result->setPrimary($primary);
			$result->setObject($object);

			// save multi values
			if (!empty($multiValues))
			{
				foreach ($multiValues as $userfieldName => $values)
				{
					$utmTableName = HighloadBlockTable::getMultipleValueTableName($hlblock, $fields[$userfieldName]);

					// first, delete old values
					$connection->query(sprintf(
						'DELETE FROM %s WHERE %s = %d',
						$helper->quote($utmTableName), $helper->quote('ID'), $primary['ID']
					));

					// insert new values
					foreach ($values as $value)
					{
						$connection->add($utmTableName, array('ID' => $primary['ID'], 'VALUE' => $value));
					}
				}
			}

			$entity->cleanCache();

			// event after update
			static::callOnAfterUpdateEvent($object, $data);
		}
		catch (\Exception $e)
		{
			// check result to avoid warning
			$result->isSuccess();

			throw $e;
		}

		return $result;
	}

	/**
	 * @param mixed $primary
	 *
	 * @return Entity\DeleteResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function delete($primary)
	{
		global $USER_FIELD_MANAGER;

		// check primary
		static::normalizePrimary($primary);
		static::validatePrimary($primary);

		$entity = static::getEntity();
		$result = new Entity\DeleteResult();
		$hlblock = static::getHighloadBlock();

		// get old data
		$oldData = static::getByPrimary($primary)->fetch();

		try
		{
			//event before delete
			static::callOnBeforeDeleteEvent($primary, $entity, $result, $oldData);

			// return if any error
			if (!$result->isSuccess(true))
			{
				return $result;
			}

			//event on delete
			static::callOnDeleteEvent($primary, $entity, $oldData);

			// remove row
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$tableName = $entity->getDBTableName();

			$id = array();
			foreach ($primary as $k => $v)
			{
				$id[] = $k." = '".$helper->forSql($v)."'";
			}
			$where = implode(' AND ', $id);

			$sql = "DELETE FROM ".$helper->quote($tableName)." WHERE ".$where;
			$connection->queryExecute($sql);

			$fields = $USER_FIELD_MANAGER->getUserFields('HLBLOCK_'.$hlblock['ID']);

			foreach ($oldData as $k => $v)
			{
				if ($k === 'ID')
				{
					continue;
				}
				$userfield = $fields[$k];

				// remove multi values
				if ($userfield['MULTIPLE'] == 'Y')
				{
					$utmTableName = HighloadBlockTable::getMultipleValueTableName($hlblock, $userfield);

					$connection->query(sprintf(
						'DELETE FROM %s WHERE %s = %d',
						$helper->quote($utmTableName), $helper->quote('ID'), $primary['ID']
					));
				}

				// remove files
				if ($userfield["USER_TYPE"]["BASE_TYPE"]=="file")
				{
					if(is_array($oldData[$k]))
					{
						foreach($oldData[$k] as $value)
						{
							\CFile::delete($value);
						}
					}
					else
					{
						\CFile::delete($oldData[$k]);
					}
				}
			}

			$entity->cleanCache();

			//event after delete
			static::callOnAfterDeleteEvent($primary, $entity, $oldData);
		}
		catch (\Exception $e)
		{
			// check result to avoid warning
			$result->isSuccess();

			throw $e;
		}

		return $result;
	}

	protected static function callOnBeforeUpdateEvent($object, $fields, $result)
	{
		//event before update
		$event = new Entity\Event($object->entity, self::EVENT_ON_BEFORE_UPDATE, [
			"id" => $object->primary,
			"fields" => $fields,
			'object' => $object
		]);

		$event->send();
		$event->getErrors($result);
		$event->mergeObjectFields($object);

		$oldData = $object->collectValues(Values::ACTUAL, FieldTypeMask::SCALAR);

		//event before update (modern with namespace)
		$event = new Entity\Event($object->entity, self::EVENT_ON_BEFORE_UPDATE, [
			"id" => $object->primary,
			"primary" => $object->primary,
			"fields" => $fields,
			'object' => $object,
			"oldFields" => $oldData
		], true);

		$event->send();
		$event->getErrors($result);
		$event->mergeObjectFields($object);
	}

	protected static function callOnUpdateEvent($object, $fields, $ufdata)
	{
		//event on update
		$event = new Event($object->entity, self::EVENT_ON_UPDATE, [
			"id" => $object->primary,
			"fields" => $fields,
			'object' => clone $object
		]);
		$event->send();

		$oldData = $object->collectValues(Values::ACTUAL, FieldTypeMask::SCALAR);

		//event on update (modern with namespace)
		$event = new Event($object->entity, self::EVENT_ON_UPDATE, [
			"id" => $object->primary,
			"primary" => $object->primary,
			"fields" => $fields,
			'object' => clone $object,
			"oldFields" => $oldData
		], true);
		$event->send();
	}

	protected static function callOnAfterUpdateEvent($object, $fields)
	{
		//event after update
		$event = new Entity\Event($object->entity, self::EVENT_ON_AFTER_UPDATE, [
			"id"=> $object->primary,
			"fields" => $fields,
			'object' => clone $object
		]);
		$event->send();

		$oldData = $object->collectValues(Values::ACTUAL, FieldTypeMask::SCALAR);

		//event after update (modern with namespace)
		$event = new Entity\Event($object->entity, self::EVENT_ON_AFTER_UPDATE, [
			"id" => $object->primary,
			"primary" => $object->primary,
			"fields" => $fields,
			'object' => clone $object,
			"oldFields" => $oldData
		], true);
		$event->send();
	}

	protected static function callOnBeforeDeleteEvent($primary, $entity, $result, $oldData = null)
	{
		//event before delete
		$event = new Entity\Event($entity, self::EVENT_ON_BEFORE_DELETE, array("id"=>$primary));
		$event->send();
		$event->getErrors($result);

		//event before delete (modern with namespace)
		$event = new Entity\Event($entity, self::EVENT_ON_BEFORE_DELETE, array(
			"id"=>$primary, "primary"=>$primary, "oldFields" => $oldData
		), true);
		$event->send();
		$event->getErrors($result);
	}

	protected static function callOnDeleteEvent($primary, $entity, $oldData = null)
	{
		//event on delete
		$event = new Entity\Event($entity, self::EVENT_ON_DELETE, array("id"=>$primary));
		$event->send();

		//event on delete (modern with namespace)
		$event = new Entity\Event($entity, self::EVENT_ON_DELETE, array(
			"id"=>$primary, "primary"=>$primary, "oldFields" => $oldData
		), true);
		$event->send();
	}

	protected static function callOnAfterDeleteEvent($primary, $entity, $oldData = null)
	{
		//event after delete
		$event = new Entity\Event($entity, self::EVENT_ON_AFTER_DELETE, array("id"=>$primary));
		$event->send();

		//event after delete (modern with namespace)
		$event = new Entity\Event($entity, self::EVENT_ON_AFTER_DELETE, array(
			"id"=>$primary, "primary" => $primary, "oldFields" => $oldData
		), true);
		$event->send();
	}

	protected static function convertValuesBeforeSave($data, $userfields)
	{
		$multiValues = array();

		foreach ($data as $k => $v)
		{
			if ($k == 'ID')
			{
				continue;
			}

			$userfield = $userfields[$k];

			if ($userfield['MULTIPLE'] == 'N')
			{
				$inputValue = array($v);
			}
			else
			{
				$inputValue = $v;
			}

			$tmpValue = array();

			foreach ($inputValue as $singleValue)
			{
				$tmpValue[] = static::convertSingleValueBeforeSave($singleValue, $userfield);
			}

			// write value back
			if ($userfield['MULTIPLE'] == 'N')
			{
				$data[$k] = $tmpValue[0];
			}
			else
			{
				// remove empty (false) values
				$tmpValue = array_filter($tmpValue, array('static', 'isNotNull'));

				$data[$k] = $tmpValue;
				$multiValues[$k] = $tmpValue;
			}
		}

		return array($data, $multiValues);
	}

	/**
	 * Modify value before save.
	 * @param mixed $value Value for converting.
	 * @param array $userfield Field array.
	 * @return boolean|null
	 */
	protected static function convertSingleValueBeforeSave($value, $userfield)
	{
		if (!isset($userfield['USER_TYPE']) || !is_array($userfield['USER_TYPE']))
		{
			$userfield['USER_TYPE'] = array();
		}

		if (
			isset($userfield['USER_TYPE']['CLASS_NAME']) &&
			is_callable(array($userfield['USER_TYPE']['CLASS_NAME'], 'onbeforesave'))
		)
		{
			$value = call_user_func_array(
				array($userfield['USER_TYPE']['CLASS_NAME'], 'onbeforesave'), array($userfield, $value)
			);
		}

		if (static::isNotNull($value))
		{
			return $value;
		}
		elseif (
				isset($userfield['USER_TYPE']['BASE_TYPE']) &&
				(
					$userfield['USER_TYPE']['BASE_TYPE'] == 'int' ||
					$userfield['USER_TYPE']['BASE_TYPE'] == 'double'
				)
		)
		{
			return null;
		}
		else
		{
			return false;
		}
	}

	protected static function isNotNull($value)
	{
		return !($value === null || $value === false || $value === '');
	}
}
