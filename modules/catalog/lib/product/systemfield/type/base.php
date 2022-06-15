<?php
namespace Bitrix\Catalog\Product\SystemField\Type;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\TaskTable;
use Bitrix\Main\UserField\Internal\UserFieldHelper;

abstract class Base
{
	protected static ?bool $bitrix24Included = null;

	protected static array $errors = [];

	public static function create(array $config): Main\Result
	{
		static::clearErrors();
		static::checkRequiredModules();
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		$config = static::verifyConfig($config);
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		return static::internalCreate($config);
	}

	abstract protected static function internalCreate(array $config): Main\Result;

	protected static function verifyConfig(array $config): array
	{
		return $config;
	}

	public static function isAllowed(): bool
	{
		return true;
	}

	protected static function addError(string $error): void
	{
		$error = trim($error);
		if ($error !== '')
		{
			self::$errors[] = $error;
		}
	}

	protected static function getErrors(): array
	{
		return self::$errors;
	}

	protected static function clearErrors(): void
	{
		self::$errors = [];
	}

	protected static function isSuccess(): bool
	{
		return empty(self::$errors);
	}

	protected static function getErrorResult(): Main\Result
	{
		$result = new Main\Result();
		foreach (static::getErrors() as $value)
		{
			$result->addError(new Main\Error($value));
		}
		static::clearErrors();
		return $result;
	}

	protected static function checkRequiredModules(): void
	{
		if (self::$bitrix24Included === null)
		{
			self::$bitrix24Included = Loader::includeModule('bitrix24');
		}
	}

	protected static function isBitrix24(): bool
	{
		self::checkRequiredModules();
		return self::$bitrix24Included;
	}

	public static function getDefaultSettings(): ?array
	{
		return [];
	}

	/**
	 * @param string $moduleId
	 * @param array $filter
	 * @return array
	 */
	protected static function getModuleTasks(string $moduleId, array $filter = []): array
	{
		$result = [];

		$filter['=MODULE_ID'] = $moduleId;

		$iterator = TaskTable::getList([
			'select' => [
				'ID',
				'LETTER',
			],
			'filter' => $filter,
		]);
		while ($row = $iterator->fetch())
		{
			$result[$row['LETTER']] = $row['ID'];
		}

		return $result;
	}

	/**
	 * @param array $field
	 * @return array|null
	 */
	protected static function loadUserFieldRow(array $field): ?array
	{
		$iterator = Main\UserFieldTable::getList([
			'select' => ['*'],
			'filter' => [
				'=ENTITY_ID' => $field['ENTITY_ID'],
				'=FIELD_NAME' => $field['FIELD_NAME'],
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
		{
			$row['ID'] = (int)$row['ID'];

			return $row;
		}

		return null;
	}

	protected static function isExistsUserFieldRow(array $field): bool
	{
		return (static::loadUserFieldRow($field) !== null);
	}

	/**
	 * @param array $field
	 * @return Main\Result
	 */
	protected static function createUserField(array $field): Main\Result
	{
		$result = new Main\Result();

		$userField = new \CUserTypeEntity();

		$row = static::loadUserFieldRow($field);
		$id = 0;
		if (!empty($row))
		{
			$row['ID'] = (int)$row['ID'];
			if ($userField->Update($row['ID'], $field))
			{
				$id = $row['ID'];
			}
		}
		else
		{
			$id = (int)$userField->Add($field);
		}
		unset($row);
		if ($id <= 0)
		{
			$application = UserFieldHelper::getInstance()->getApplication();
			$exception = $application->GetException();
			$error = $exception instanceof \CAdminException
				? $exception->GetString()
				: Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_BASE_ERR_CREATE_UF_COMMON',
					['#FIELD_NAME#' => $field['FIELD_NAME']]
				)
			;
			$result->addError(new Main\Error(
				$error,
				$field['FIELD_NAME']
			));
			unset($error, $exception);
		}
		else
		{
			$result->setData(['ID' => $id]);
		}

		return $result;
	}


	public static function getGridAction(array $config): ?array
	{
		static::clearErrors();
		static::checkRequiredModules();
		if (!static::isSuccess())
		{
			return null;
		}

		return static::internalGridAction($config);
	}

	protected static function internalGridAction(array $config): ?array
	{
		return null;
	}

	protected static function getEmptyListValueDescription(): array
	{
		return [
			'VALUE' => '0',
			'NAME' => Loc::getMessage('CATALOG_PRODUCT_SYSTEMFIELD_TYPE_BASE_EMPTY_LIST_VALUE'),
		];
	}
}
