<?php

namespace Bitrix\Catalog\Product\SystemField;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Grid\Panel\ProductGroupAction;

abstract class Base
{
	/**
	 * Real field identifier.
	 * Unique in the whole project.
	 * Required.
	 *
	 * @var string
	 */
	public const FIELD_ID = '';

	/**
	 * Real field type identifier.
	 * Contains interface class name - children from \Bitrix\Catalog\Product\SystemField\Type\Base.
	 * Required.
	 *
	 * @var string
	 */
	public const TYPE_ID = '';

	/**
	 * Field name prefix (all user fields have identifier with this prefix).
	 *
	 * @var string
	 */
	protected const FIELD_PREFIX = 'UF_';

	/**
	 * Contains true for cloud project.
	 */
	protected static ?bool $bitrix24Include = null;

	/**
	 * List of active languages.
	 */
	protected static ?array $languages = null;

	private static ?array $fields = null;

	/**
	 * List of allowed product types.
	 */
	protected static ?array $allowedProductTypeList = null;

	protected static ?array $allowedOperations = null;

	/**
	 * Returns field description or null, if field is not allowed.
	 * Contains identifier and interface class.
	 *
	 * @return array|null
	 * array or null. if returns array, his keys are case sensitive:
	 * 		<ul>
	 * 		<li>string ID - constains copy of className::FIELD_ID from real class.
	 * 		<li>string TYPE - constains copy of className::TYPE_ID from real class.
	 * 		<li>string TITLE - contains field name.
	 * 		</ul>
	 */
	public static function getDescription(): ?array
	{
		if (!static::isAllowed())
		{
			return null;
		}

		return [
			'ID' => static::getFieldId(),
			'TYPE' => static::getTypeId(),
			'TITLE' => static::getTitle(),
		];
	}

	/**
	 * Returns field config or null, if field is not allowed.
	 *
	 * @return array|null
	 * array or null. if returns array, his keys are case sensitive:
	 * 		<ul>
	 * 		<li>array FIELD - description of user field. Required. Contains all data for create user field.
	 * 		</ul>
	 * Other keys in context interface class (user field type).
	 */
	abstract public static function getConfig(): ?array;

	abstract public static function isAllowed(): bool;

	public static function getTitle(): string
	{
		$result = static::getTitleInternal();

		return ($result ?? static::getUserFieldName(static::getFieldId()));
	}

	abstract protected static function getTitleInternal(): ?string;

	abstract public static function getUserFieldBaseParam(): array;

	public static function isExists(): bool
	{
		$row = static::load();
		return !empty($row);
	}

	public static function create(): Main\Result
	{
		if (!static::isAllowed())
		{
			return static::getCommonError('CATALOG_SYSTEMFIELD_ERR_DISALLOWED_FIELD');
		}

		$description = static::getDescription();
		if (
			$description === null
			|| empty($description['TYPE'])
			|| empty($description['ID'])
		)
		{
			return static::getCommonError('CATALOG_SYSTEMFIELD_ERR_BAD_FIELD_DESCRIPTION');
		}
		if (!is_a($description['TYPE'], Type\Base::class, true))
		{
			return static::getCommonError('CATALOG_SYSTEMFIELD_ERR_BAD_FIELD_TYPE');
		}

		$className = $description['TYPE'];

		$config = static::getConfig();
		if ($config === null)
		{
			return static::getCommonError('CATALOG_SYSTEMFIELD_ERR_BAD_CONFIG_DESCRIPTION');
		}

		$result = $className::create($config);
		if ($result->isSuccess())
		{
			static::updateProductFormConfiguration();
		}

		return $result;
	}

	public static function updateProductFormConfiguration(): void {}

	public static function getTypeId(): string
	{
		return static::TYPE_ID;
	}

	public static function getFieldId(): string
	{
		return static::FIELD_ID;
	}

	protected static function isBitrix24(): bool
	{
		if (self::$bitrix24Include === null)
		{
			self::$bitrix24Include = Loader::includeModule('bitrix24');
		}

		return self::$bitrix24Include;
	}

	protected static function getUserFieldName(string $id): string
	{
		return self::FIELD_PREFIX.$id;
	}

	/**
	 * Fills and returns list of active languages.
	 *
	 * @return array
	 */
	protected static function getLanguages(): array
	{
		if (self::$languages === null)
		{
			self::$languages = [];
			$iterator = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => ['=ACTIVE' => 'Y']
			]);
			while ($row = $iterator->fetch())
			{
				self::$languages[] = $row['ID'];
			}
			unset($row, $iterator);
		}

		return self::$languages;
	}

	protected static function getMessages(string $file, array $messageIds): array
	{
		$messageList = array_fill_keys(array_keys($messageIds), []);
		$languages = self::getLanguages();
		foreach ($languages as $languageId)
		{
			$mess = Loc::loadLanguageFile($file, $languageId);
			foreach ($messageIds as $index => $phrase)
			{
				$message = (string)($mess[$phrase] ?? null);
				if ($message !== '')
				{
					$messageList[$index][$languageId] = $message;
				}
			}
		}
		unset($message, $languageId, $languages);

		return $messageList;
	}

	protected static function getCommonError(string $errorCode): Main\Result
	{
		$result = new Main\Result();
		$result->addError(new Main\Error(
			Loc::getMessage(
				$errorCode,
				['#TITLE#' => static::getTitle()]
			)
		));
		return $result;
	}

	public static function getGridAction(ProductGroupAction $panel): ?array
	{
		if (!static::isAllowed())
		{
			return null;
		}

		$description = static::getDescription();
		if (
			$description === null
			|| empty($description['TYPE'])
			|| empty($description['ID'])
		)
		{
			return null;
		}
		if (
			!class_exists($description['TYPE'])
			|| !is_a($description['TYPE'], Type\Base::class, true))
		{
			return null;
		}

		$actionConfig = static::getGridActionConfig($panel);
		if ($actionConfig === null)
		{
			return null;
		}

		$className = $description['TYPE'];

		return $className::getGridAction($actionConfig);
	}

	protected static function getGridActionConfig(ProductGroupAction $panel): ?array
	{
		return null;
	}

	public static function load(): ?array
	{
		if (self::$fields === null)
		{
			self::$fields = [];
		}
		$className = get_called_class();
		if (!array_key_exists($className, self::$fields))
		{
			self::$fields[$className] = self::loadInternal($className);
		}

		return self::$fields[$className];
	}

	public static function clearCache(): void
	{
		self::$languages = null;
		self::$fields = null;
		self::$allowedProductTypeList = null;
		self::$allowedOperations = null;
	}

	private static function loadInternal(string $className): ?array
	{
		/** @var self $className */
		$config = $className::getUserFieldBaseParam();
		if (empty($config))
		{
			return null;
		}
		if ($config['USER_TYPE_ID'] === null)
		{
			return null;
		}

		$iterator = Main\UserFieldTable::getList([
			'select' => array_merge(
				['*'],
				Main\UserFieldTable::getLabelsSelect()
			),
			'filter' => [
				'=ENTITY_ID' => $config['ENTITY_ID'],
				'=FIELD_NAME' => $config['FIELD_NAME'],
				'=USER_TYPE_ID' => $config['USER_TYPE_ID'],
			],
			'runtime' => [
				Main\UserFieldTable::getLabelsReference('', Loc::getCurrentLang()),
			],
		]);
		$row = $iterator->fetch();
		unset($iterator, $config);

		if (!empty($row))
		{
			return static::afterLoadInternalModify($row);
		}

		return null;
	}

	protected static function afterLoadInternalModify(array $row): array
	{
		$row['ID'] = (int)$row['ID'];
		$row['SORT'] = (int)$row['SORT'];

		foreach (Main\UserFieldTable::getLabelFields() as $fieldName)
		{
			if ($fieldName === 'LANGUAGE_ID')
			{
				unset($row[$fieldName]);
			}
			else
			{
				if (isset($row[$fieldName]) && $row[$fieldName] === '')
				{
					$row[$fieldName] = null;
				}
			}
		}

		return $row;
	}

	public static function checkAllowedProductType(int $type): bool
	{
		if (self::$allowedProductTypeList === null)
		{
			self::$allowedProductTypeList = [];
		}
		$className = get_called_class();
		if (!isset(self::$allowedProductTypeList[$className]))
		{
			self::$allowedProductTypeList[$className] = static::getAllowedProductTypeList();
		}

		return in_array($type, self::$allowedProductTypeList[$className], true);
	}

	public static function getAllowedProductTypeList(): array
	{
		return [];
	}

	public static function checkRestictions(array $restrictions): bool
	{
		if (isset($restrictions['TYPE']))
		{
			if (!static::checkAllowedProductType($restrictions['TYPE']))
			{
				return false;
			}
		}

		return true;
	}

	public static function getGroupActionRequest(ProductGroupAction $panel): ?array
	{
		$field = static::getUserFieldBaseParam();
		$requestName = $panel->getFormRowFieldName($field['FIELD_NAME']);
		$value = Main\Context::getCurrent()->getRequest()->get($requestName);

		if ($value === null)
		{
			return null;
		}
		if ($field['MULTIPLE'] === 'Y' && !is_array($value))
		{
			$value = [$value];
		}

		return [$field['FIELD_NAME'] => $value];
	}

	public static function checkAllowedOperation(string $operation): bool
	{
		if (self::$allowedOperations === null)
		{
			self::$allowedOperations = [];
		}
		$className = get_called_class();
		if (!isset(self::$allowedOperations[$className]))
		{
			self::$allowedOperations[$className] = array_fill_keys(static::getAllowedOperations(), true);
		}

		return isset(self::$allowedOperations[$className][$operation]);
	}

	public static function getAllowedOperations(): array
	{
		return [];
	}

	public static function getOperationSelectFieldList(string $operation): array
	{
		return [];
	}

	public static function prepareValue(string $operation, array $productRow): array
	{
		return $productRow;
	}
}
