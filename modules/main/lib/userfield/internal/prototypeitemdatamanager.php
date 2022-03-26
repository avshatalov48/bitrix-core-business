<?php

namespace Bitrix\Main\UserField\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ScalarField;

/**
 * @deprecated
 */
abstract class PrototypeItemDataManager extends ORM\Data\DataManager
{
	protected static $temporaryStorage;
	protected static $isCheckUserFields = true;

	/**
	 * This method disabled check that required user fields are not empty for the next saving (no matter, add or update).
	 * This check will be disabled only once.
	 * You should invoke this method before every saving.
	 */
	public static function disableUserFieldsCheck(): void
	{
		static::$isCheckUserFields = false;
	}

	public static function getType(): ?array
	{
		return Registry::getInstance()->getTypeByEntity(static::getEntity());
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
		];
	}

	protected static function getTemporaryStorage(): TemporaryStorage
	{
		if(!static::$temporaryStorage)
		{
			static::$temporaryStorage = new TemporaryStorage();
		}

		return static::$temporaryStorage;
	}

	public static function checkFields(ORM\Data\Result $result, $primary, array $data)
	{
		// check for unknown fields
		foreach ($data as $k => $v)
		{
			if (!(static::getEntity()->hasField($k) && static::getEntity()->getField($k) instanceof ScalarField))
			{
				throw new Main\SystemException(sprintf(
					'Field `%s` not found in entity when trying to query %s row.',
					$k, static::getEntity()->getName()
				));
			}
		}

		parent::checkFields($result, $primary, $data);
	}

	public static function getItemUserFieldEntityId(): ?string
	{
		return Registry::getInstance()->getUserFieldEntityIdByItemEntity(static::getEntity());
	}

	protected static function getErrorFromException(): ORM\EntityError
	{
		$application = UserFieldHelper::getInstance()->getApplication();

		if(is_object($application) && $application->getException())
		{
			$e = $application->getException();
			$error = new Main\ORM\EntityError($e->getString());
			$application->resetException();
		}
		else
		{
			$error = new Main\ORM\EntityError("Unknown error while checking userfields");
		}

		return $error;
	}

	/**
	 * @param $id
	 * @param array $data
	 * @param array $options
	 * @return Main\ORM\EventResult
	 */
	protected static function modifyValuesBeforeSave($id, array $data, array $options = []): ORM\EventResult
	{
		$userFieldManager = UserFieldHelper::getInstance()->getManager();
		$isUpdate = (isset($options['isUpdate']) && $options['isUpdate'] === true);

		$result = new Main\ORM\EventResult();
		if (!$userFieldManager)
		{
			static::$isCheckUserFields = true;
			return $result;
		}

		if($isUpdate)
		{
			$oldData = static::getByPrimary($id)->fetch();
			static::getTemporaryStorage()->saveData($id, $oldData);
			if (
				static::$isCheckUserFields
				&& !$userFieldManager->checkFieldsWithOldData(
					static::getItemUserFieldEntityId(),
					$oldData,
					$data
				)
			)
			{
				$result->addError(static::getErrorFromException());
			}

			$fields = $userFieldManager->getUserFieldsWithReadyData(
				static::getItemUserFieldEntityId(),
				$oldData,
				LANGUAGE_ID,
				false,
				'ID'
			);
		}
		else
		{
			$fields = $userFieldManager->getUserFields(static::getItemUserFieldEntityId());

			if(
				static::$isCheckUserFields
				&& !$userFieldManager->checkFields(
					static::getItemUserFieldEntityId(),
					null,
					$data,
					false,
					true
				)
			)
			{
				$result->addError(static::getErrorFromException());
			}
		}

		if(!$result->getErrors())
		{
			$data = static::convertValuesBeforeSave($data, $fields);
			$result->modifyFields($data);
		}

		static::$isCheckUserFields = true;

		return $result;
	}

	/**
	 * @param $id
	 * @param array $data
	 * @param array $options
	 * @return Main\ORM\EventResult
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	protected static function saveMultipleValues($id, array $data, array $options = []): ORM\EventResult
	{
		$id = static::getTemporaryStorage()->getIdByPrimary($id);
		$result = new Main\ORM\EventResult();

		$isUpdate = (isset($options['isUpdate']) && $options['isUpdate']);
		$type = static::getType();
		$userFieldManager = UserFieldHelper::getInstance()->getManager();
		$connection = Main\Application::getConnection();
		[$factory] = UserFieldHelper::getInstance()->parseUserFieldEntityId(static::getItemUserFieldEntityId());
		/** @var TypeFactory $factory */
		$typeDataClass = $factory->getTypeDataClass();

		$fields = $userFieldManager->getUserFields(static::getItemUserFieldEntityId());
		$oldData = static::getTemporaryStorage()->getData($id);

		$multiValues = [];
		foreach($fields as $fieldName => $field)
		{
			if(is_array($data[$fieldName]) && $field['MULTIPLE'] === 'Y')
			{
				$multiValues[$fieldName] = array_filter($data[$fieldName], array('static', 'isNotNull'));
			}
			elseif($field['USER_TYPE']['BASE_TYPE'] === 'file')
			{
				if(is_numeric($oldData[$fieldName]) && array_key_exists($fieldName, $data) && (int) $oldData[$fieldName] !== (int) $data[$fieldName])
				{
					\CFile::Delete($oldData[$fieldName]);
				}
			}
		}

		// save multi values
		foreach ($multiValues as $fieldName => $values)
		{
			$utmTableName = $typeDataClass::getMultipleValueTableName($type, $fields[$fieldName]);

			if($isUpdate)
			{
				// another clutch to delete files if they had not been deleted before
				if($fields[$fieldName]['USER_TYPE']['BASE_TYPE'] === 'file')
				{
					foreach($oldData[$fieldName] as $fileId)
					{
						if(is_numeric($fileId) && !in_array($fileId, $values))
						{
							\CFile::Delete($fileId);
						}
					}
				}

				$helper = $connection->getSqlHelper();
				// first, delete old values
				$connection->query(sprintf(
					'DELETE FROM %s WHERE %s = %d',
					$helper->quote($utmTableName), $helper->quote('ID'), $id
				));
			}

			foreach ($values as $value)
			{
				$connection->add($utmTableName, array('ID' => $id, 'VALUE' => $value));
			}
		}

		return $result;
	}

	public static function onBeforeAdd(Event $event): ORM\EventResult
	{
		return static::modifyValuesBeforeSave($event->getParameter('id'), $event->getParameter('fields'));
	}

	public static function onAfterAdd(Event $event): ORM\EventResult
	{
		return static::saveMultipleValues($event->getParameter('id'), $event->getParameter('fields'));
	}

	public static function onBeforeUpdate(Event $event): ORM\EventResult
	{
		return static::modifyValuesBeforeSave($event->getParameter('id'), $event->getParameter('fields'), [
			'isUpdate' => true,
		]);
	}

	public static function onAfterUpdate(Event $event): ORM\EventResult
	{
		return static::saveMultipleValues($event->getParameter('id'), $event->getParameter('fields'), [
			'isUpdate' => true,
		]);
	}

	public static function onBeforeDelete(Event $event): ORM\EventResult
	{
		$oldData = static::getByPrimary($event->getParameter('id'))->fetch();
		static::getTemporaryStorage()->saveData($event->getParameter('id'), $oldData);

		return new Main\ORM\EventResult();
	}

	public static function onAfterDelete(Event $event): ORM\EventResult
	{
		$result = new Main\ORM\EventResult();
		$oldData = static::getTemporaryStorage()->getData($event->getParameter('id'));
		$id = static::getTemporaryStorage()->getIdByPrimary($event->getParameter('id'));
		$userFieldManager = UserFieldHelper::getInstance()->getManager();
		$type = static::getType();
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		[$factory] = UserFieldHelper::getInstance()->parseUserFieldEntityId(static::getItemUserFieldEntityId());
		/** @var TypeFactory $factory */
		$typeDataClass = $factory->getTypeDataClass();

		$fields = $userFieldManager->getUserFields(static::getItemUserFieldEntityId());
		foreach ($oldData as $k => $v)
		{
			$userfield = $fields[$k];

			// remove multi values
			if ($userfield['MULTIPLE'] == 'Y')
			{
				$utmTableName = $typeDataClass::getMultipleValueTableName($type, $userfield);

				try
				{
					$connection->query(sprintf(
						'DELETE FROM %s WHERE %s = %d',
						$helper->quote($utmTableName), $helper->quote('ID'), $id
					));
				}
				catch(Main\DB\SqlQueryException $e)
				{
					$result->addError(new ORM\EntityError($e->getMessage()));
				}
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

		return $result;
	}

	/**
	 * Convert values of user fields using their callback.
	 *
	 * @param array $data
	 * @param array $userFields
	 * @return array
	 */
	protected static function convertValuesBeforeSave(array $data, array $userFields): array
	{
		foreach ($data as $k => $v)
		{
			if (static::isOwnField($k))
			{
				continue;
			}

			$userField = $userFields[$k];

			if ($userField['MULTIPLE'] == 'N')
			{
				$inputValue = [$v];
			}
			else
			{
				$inputValue = $v;
			}

			$tmpValue = [];

			foreach ($inputValue as $singleValue)
			{
				$tmpValue[] = static::convertSingleValueBeforeSave($singleValue, $userField);
			}

			// write value back
			if ($userField['MULTIPLE'] == 'N')
			{
				$data[$k] = $tmpValue[0];
			}
			else
			{
				// remove empty (false) values
				$tmpValue = array_filter($tmpValue, ['static', 'isNotNull']);

				$data[$k] = $tmpValue;
				$multiValues[$k] = $tmpValue;
			}
		}

		return $data;
	}

	/**
	 * Modify value before save.
	 * @param mixed $value Value for converting.
	 * @param array $userField Field array.
	 * @return mixed
	 */
	protected static function convertSingleValueBeforeSave($value, array $userField)
	{
		if (!isset($userField['USER_TYPE']) || !is_array($userField['USER_TYPE']))
		{
			$userField['USER_TYPE'] = array();
		}
		elseif (
			isset($userField['USER_TYPE']['BASE_TYPE'])
			&& $userField['USER_TYPE']['BASE_TYPE'] === 'datetime'
			&& $value instanceof Main\Type\DateTime
			&& isset($userField['SETTINGS']['USE_TIMEZONE'])
			&& $userField['SETTINGS']['USE_TIMEZONE'] === 'Y'
		)
		{
			$value = $value::createFromUserTime($value->format(Main\Type\DateTime::getFormat()));
		}

		if (
			isset($userField['USER_TYPE']['CLASS_NAME']) &&
			is_callable(array($userField['USER_TYPE']['CLASS_NAME'], 'onbeforesave'))
		)
		{
			$value = call_user_func_array(
				array($userField['USER_TYPE']['CLASS_NAME'], 'onbeforesave'), array($userField, $value)
			);
		}

		if (static::isNotNull($value))
		{
			return $value;
		}
		elseif (
				isset($userField['USER_TYPE']['BASE_TYPE']) &&
				(
					$userField['USER_TYPE']['BASE_TYPE'] == 'int' ||
					$userField['USER_TYPE']['BASE_TYPE'] == 'double'
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

	protected static function isNotNull($value): bool
	{
		return !($value === null || $value === false || $value === '');
	}

	public static function isOwnField(string $fieldName): bool
	{
		return array_key_exists($fieldName, static::getOwnFieldNames());
	}

	/**
	 * Returns list of field names that are not user fields.
	 *
	 * @return array
	 */
	public static function getOwnFieldNames(): array
	{
		static $fields;
		if($fields === null)
		{
			$fields = [];

			foreach(static::getMap() as $field)
			{
				$fields[$field->getName()] = $field->getName();
			}
		}

		return $fields;
	}

	public static function getUserFieldValues(int $id, array $userFields): ?array
	{
		$data = static::getList([
			'select' => array_keys($userFields),
			'filter' => [
				'=ID' => $id,
			]
		])->fetch();

		if (is_array($data))
		{
			return $data;
		}

		return null;
	}

	public static function updateUserFieldValues(int $id, array $fields): Main\Result
	{
		return static::update($id, $fields);
	}

	public static function deleteUserFieldValues(int $id): Main\Result
	{
		$fields = [];
		$userFields = UserFieldHelper::getInstance()->getManager()->GetUserFields(static::getItemUserFieldEntityId());
		foreach($userFields as $userField)
		{
			$fields[$userField['FIELD_NAME']] = null;
		}

		return static::update($id, $fields);
	}
}
