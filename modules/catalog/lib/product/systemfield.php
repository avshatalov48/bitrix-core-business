<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Catalog;
use Bitrix\Catalog\Grid\Panel\ProductGroupAction;
use Bitrix\Highloadblock as Highload;

final class SystemField
{
	public const EVENT_ID_BUILD_FIELD_LIST = 'OnProductUserFieldBuildList';

	public const STATUS_CONTINUE = 'continue';
	public const STATUS_FINAL = 'final';

	/** @deprecated */
	public const CODE_MARKING_CODE_GROUP = Catalog\Product\SystemField\MarkingCodeGroup::FIELD_ID;

	public const OPERATION_EXPORT = 'EXPORT';
	public const OPERATION_IMPORT = 'IMPORT';
	public const OPERATION_PROVIDER = 'PROVIDER';

	public const DESCRIPTION_MODE_FIELD_NAME = 'FIELD_NAME';
	public const DESCRIPTION_MODE_UI_LIST = 'UI_ENTITY_LIST';
	public const DESCRIPTION_MODE_UI_FORM_EDITOR = 'UI_FORM_EDITOR';
	public const DESCRIPTION_MODE_FULL = 'FULL';

	private static ?array $currentFieldSet = null;

	private static array $defaultFieldList = [
		Catalog\Product\SystemField\MarkingCodeGroup::class,
		Catalog\Product\SystemField\ProductMapping::class,
	];

	/**
	 * @return string
	 */
	public static function execAgent(): string
	{
		$result = '';
		$createResult = self::create();
		if (!$createResult->isSuccess())
		{
			$result = '\Bitrix\Catalog\Product\SystemField::execAgent();';
		}
		return $result;
	}

	/**
	 * @return Main\Result
	 */
	public static function create(): Main\Result
	{
		$result = new Main\Result();

		self::$currentFieldSet = null;

		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			$result->setData(['STATUS' => self::STATUS_FINAL]);
			return $result;
		}

		foreach ($fieldList as $field)
		{
			$internalResult = $field::create();
			if (!$internalResult->isSuccess())
			{
				foreach ($internalResult->getErrors() as $error)
				{
					$result->addError($error);
				}
			}
		}

		$result->setData(['STATUS' => self::STATUS_FINAL]);

		return $result;
	}

	/**
	 * @return void
	 */
	public static function delete(): void
	{
		self::$currentFieldSet = null;
	}

	public static function getSelectFields(string $operation): array
	{
		$result = [];
		foreach (self::getCurrentFieldSet($operation) as $field)
		{
			$result = array_merge(
				$result,
				$field::getOperationSelectFieldList($operation)
			);
		}

		return $result;
	}

	/**
	 * @param string $operation
	 * @return array|Catalog\Product\SystemField\Base[]
	 */
	private static function getCurrentFieldSet(string $operation): array
	{
		self::loadCurrentFieldSet($operation);

		return self::$currentFieldSet[$operation] ?? [];
	}

	private static function getDefaultFieldSet(): array
	{
		return [
			self::OPERATION_PROVIDER => null,
			self::OPERATION_IMPORT => null,
			self::OPERATION_EXPORT => null,
		];
	}

	private static function loadCurrentFieldSet(string $operation): void
	{
		if (self::$currentFieldSet === null)
		{
			self::$currentFieldSet = self::getDefaultFieldSet();
		}
		if (!array_key_exists($operation, self::$currentFieldSet))
		{
			return;
		}
		if (self::$currentFieldSet[$operation] === null)
		{
			self::$currentFieldSet[$operation] = [];

			$fieldList = self::getBuildedFieldList();
			if (!empty($fieldList))
			{
				foreach ($fieldList as $field)
				{
					if ($field::checkAllowedOperation($operation) && $field::isExists())
					{
						self::$currentFieldSet[$operation][] = $field;
					}
				}
				unset($field);
			}
			unset($fieldList);
		}
	}

	public static function getProviderSelectFields(): array
	{
		return self::getSelectFields(self::OPERATION_PROVIDER);
	}

	public static function getExportSelectFields(): array
	{
		return self::getSelectFields(self::OPERATION_EXPORT);
	}

	public static function getImportSelectFields(): array
	{
		return self::getSelectFields(self::OPERATION_IMPORT);
	}

	/**
	 * @deprecated
	 * @see self::getSelectFields
	 *
	 * @return array
	 */
	public static function getFieldList(): array
	{
		return self::getProviderSelectFields();
	}

	/**
	 * @deprecated
	 * @see prepareRow()
	 *
	 * @param array &$row
	 * @param string $operation
	 * @return void
	 */
	public static function convertRow(array &$row, string $operation = self::OPERATION_PROVIDER): void
	{
		self::prepareRow($row, $operation);
	}

	public static function prepareRow(array &$row, string $operation = self::OPERATION_IMPORT): void
	{
		foreach (self::getCurrentFieldSet($operation) as $field)
		{
			$row = $field::prepareValue($operation, $row);
		}
		unset($field);
	}

	/**
	 * @param ProductGroupAction $panel
	 * @return array|null
	 */
	public static function getGroupActions(ProductGroupAction $panel): ?array
	{
		$catalog = $panel->getCatalogConfig();
		if (empty($catalog))
		{
			return null;
		}

		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			return null;
		}

		$result = [];
		foreach ($fieldList as $field)
		{
			$action = $field::getGridAction($panel);
			if (!empty($action))
			{
				$result[] = $action;
			}
		}
		unset($action, $field, $fieldList);

		return (!empty($result) ? $result : null);
	}

	public static function getByUserFieldName(string $fieldName): ?string
	{
		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			return null;
		}

		$result = null;
		foreach ($fieldList as $field)
		{
			$baseParams = $field::getUserFieldBaseParam();
			if ($baseParams['FIELD_NAME'] === $fieldName)
			{
				/** @var string $result */
				$result = $field;
				break;
			}
		}
		unset($baseParams, $field, $fieldList);

		return $result;
	}

	public static function getFieldsByRestrictions(array $restrictions, array $config = []): array
	{
		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			return [];
		}

		$resultMode = self::DESCRIPTION_MODE_FULL;
		if (isset($config['RESULT_MODE']) && is_string($config['RESULT_MODE']))
		{
			$resultMode = $config['RESULT_MODE'];
		}

		$result = [];
		foreach ($fieldList as $field)
		{
			if (
				$field::checkRestictions($restrictions)
				&& $field::isExists()
			)
			{
				$data = $field::getUserFieldBaseParam();
				switch ($resultMode)
				{
					case self::DESCRIPTION_MODE_FIELD_NAME:
						$result[] = $data['FIELD_NAME'];
						break;
					case self::DESCRIPTION_MODE_UI_LIST:
						$result[] = [
							$data['FIELD_NAME'] => $field::getTitle(),
						];
						break;
					case self::DESCRIPTION_MODE_UI_FORM_EDITOR:
						$result[] = [
							'name' => $data['FIELD_NAME'],
						];
						break;
					case self::DESCRIPTION_MODE_FULL:
					default:
						$result[$data['FIELD_NAME']] = $data;
						break;
				}
			}
		}
		unset($field, $fieldList);

		return $result;
	}

	public static function getFieldNamesByRestrictions(array $restrictions): array
	{
		return self::getFieldsByRestrictions(
			$restrictions,
			[
				'RESULT_MODE' => self::DESCRIPTION_MODE_FIELD_NAME,
			]
		);
	}

	public static function getPermissionFieldsByRestrictions(array $restrictions): array
	{
		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			return [];
		}

		$result = [];
		foreach ($fieldList as $field)
		{
			if ($field::isExists())
			{
				$data = $field::getUserFieldBaseParam();
				$result[$data['FIELD_NAME']] = $field::checkRestictions($restrictions);
			}
		}
		unset($field, $fieldList);

		return $result;
	}

	public static function getAllowedProductTypes(): array
	{
		$fieldList = self::getBuildedFieldList();
		if (empty($fieldList))
		{
			return [];
		}

		$result = [];
		foreach ($fieldList as $field)
		{
			$baseParams = $field::getUserFieldBaseParam();
			$result[$baseParams['FIELD_NAME']] = $field::getAllowedProductTypeList();
		}
		unset($field, $fieldList);

		return $result;
	}

	/**
	 * @param ProductGroupAction $panel
	 * @param string $fieldName
	 * @return array|null
	 */
	public static function getGroupActionRequest(ProductGroupAction $panel, string $fieldName): ?array
	{
		$catalog = $panel->getCatalogConfig();
		if (empty($catalog))
		{
			return null;
		}

		/** @var Catalog\Product\SystemField\Base $field */
		$field = self::getByUserFieldName($fieldName);
		if (empty($field))
		{
			return null;
		}

		return $field::getGroupActionRequest($panel);
	}

	/**
	 * @param ORM\Event $event
	 * @return ORM\EventResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function handlerHighloadBlockBeforeDelete(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult();

		if (Catalog\Product\SystemField\Type\HighloadBlock::isAllowed())
		{
			$primary = $event->getParameter('primary');
			if (!empty($primary))
			{
				$iterator = Highload\HighloadBlockTable::getList([
					'filter' => $primary,
				]);
				$row = $iterator->fetch();
				unset($iterator);

				if (!empty($row))
				{
					$fieldList = self::getBuildedFieldList();
					foreach ($fieldList as $field)
					{
						if ($field::getTypeId() !== Catalog\Product\SystemField\Type\HighloadBlock::class)
						{
							continue;
						}
						if (!$field::isAllowed() || !$field::isExists())
						{
							continue;
						}
						$config = $field::getConfig();

						if ($row['NAME'] === $config['HIGHLOADBLOCK']['NAME'])
						{
							$result->addError(new ORM\EntityError(
								Loc::getMessage(
									'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_CANNOT_DELETE_HIGHLOADBLOCK',
									['#NAME#' => $row['NAME']]
								)
							));
						}
						unset($config);
					}
					unset($field, $fieldList);
				}
				unset($row);
			}
			unset($primary);
		}

		return $result;
	}

	/**
	 * @param ORM\Event $event
	 * @return ORM\EventResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function handlerHighloadBlockBeforeUpdate(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult();

		if (Catalog\Product\SystemField\Type\HighloadBlock::isAllowed())
		{
			$primary = $event->getParameter('primary');
			$fields = $event->getParameter('fields');
			if (!empty($primary))
			{
				$iterator = Highload\HighloadBlockTable::getList([
					'filter' => $primary,
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (!empty($row))
				{
					$fieldList = self::getBuildedFieldList();
					foreach ($fieldList as $field)
					{
						if ($field::getTypeId() !== Catalog\Product\SystemField\Type\HighloadBlock::class)
						{
							continue;
						}
						if (!$field::isAllowed() || !$field::isExists())
						{
							continue;
						}
						$config = $field::getConfig();

						if ($row['NAME'] === $config['HIGHLOADBLOCK']['NAME'])
						{
							if (
								(isset($fields['NAME']) && $row['NAME'] != $fields['NAME'])
								|| (isset($fields['TABLE_NAME']) && $row['TABLE_NAME'] != $fields['TABLE_NAME'])
							)
							{
								$result->addError(new ORM\EntityError(
									Loc::getMessage(
										'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_CANNOT_UPDATE_HIGHLOADBLOCK',
										['#NAME#' => $row['NAME']]
									)
								));
							}
						}
						unset($config);
					}
					unset($field, $fieldList);
				}
				unset($row);
			}
			unset($primary);
		}

		return $result;
	}

	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function handlerHighloadBlockBeforeUninstall(Main\Event $event): Main\EventResult
	{
		$blockNames = [];

		$module = $event->getParameter('module');
		if ($module === 'highloadblock')
		{
			$fieldList = self::getBuildedFieldList();
			foreach ($fieldList as $field)
			{
				if ($field::getTypeId() !== Catalog\Product\SystemField\Type\HighloadBlock::class)
				{
					continue;
				}
				if (!$field::isAllowed() || !$field::isExists())
				{
					continue;
				}
				$config = $field::getConfig();
				/** @var Catalog\Product\SystemField\Type\HighloadBlock $fieldType */
				$fieldType = $field::getTypeId();
				$row = $fieldType::getStorageTable($config['HIGHLOADBLOCK']);
				if (!empty($row))
				{
					$blockNames[] = $config['HIGHLOADBLOCK']['NAME'];
				}
				unset($row, $fieldType, $config);
			}
			unset($fieldList);
		}
		unset($module);

		if (empty($blockNames))
		{
			return new Main\EventResult(Main\EventResult::SUCCESS);
		}
		else
		{
			if (count($blockNames) === 1)
			{
				$error = Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_DISALLOW_UNINSTALL_HIGHLOADBLOCK',
					[
						'#NAME#' => reset($blockNames),
					]
				);
			}
			else
			{
				$error = Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_DISALLOW_UNINSTALL_HIGHLOADBLOCK_LIST',
					[
						'#NAME#' => implode(', ', $blockNames),
					]
				);
			}

			return new Main\EventResult(
				Main\EventResult::ERROR,
				[
					'error' => $error,
				],
				'catalog'
			);
		}
	}

	/**
	 * @return array|Catalog\Product\SystemField\Base[]
	 */
	protected static function getBuildedFieldList(): array
	{
		$result = [];

		$list = array_merge(
			self::$defaultFieldList,
			self::getExternalFieldList()
		);
		/** @var Catalog\Product\SystemField\Base $className */
		foreach ($list as $className)
		{
			if ($className::isAllowed())
			{
				$result[] = $className;
			}
		}

		return $result;
	}

	/**
	 * @return array|Catalog\Product\SystemField\Base[]
	 */
	protected static function getExternalFieldList(): array
	{
		$result = [];
		$event = new Main\Event(
			'catalog',
			self::EVENT_ID_BUILD_FIELD_LIST,
			[]
		);
		$event->send();
		$eventResult = $event->getResults();
		if (!empty($eventResult) && is_array($eventResult))
		{
			foreach ($eventResult as $row)
			{
				if ($row->getType() != Main\EventResult::SUCCESS)
				{
					continue;
				}
				$classList = $row->getParameters();
				if (empty($classList) || !is_array($classList))
				{
					continue;
				}
				foreach ($classList as $item)
				{
					if (!is_string($item))
					{
						continue;
					}
					$item = trim($item);
					if (
						$item === ''
						|| !class_exists($item)
						|| !is_a($item, Catalog\Product\SystemField\Base::class, true)
					)
					{
						continue;
					}
					$result[] = $item;
				}
			}
		}

		return $result;
	}
}
