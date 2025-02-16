<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class ProductProperty extends ProductPropertyBase
{
	use GetAction; // default getAction realization

	private const LIST_NAME = 'PRODUCT_PROPERTIES';

	private string $customUserType = '';

	protected function getServiceListName(): string
	{
		return self::LIST_NAME;
	}

	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * @param PageNavigation $pageNavigation
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param bool $__calculateTotalCount
	 * @return Page
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): Page
	{
		if (!isset($filter['IBLOCK_ID']))
		{
			$filter['IBLOCK_ID'] = $this->getCatalogIds();
		}
		else
		{
			$iblockId = (int)($filter['IBLOCK_ID']);
			if (!$this->isIblockCatalog($iblockId))
			{
				return new Page($this->getServiceListName(), [], 0);
			}
		}

		return new Page(
			$this->getServiceListName(),
			$this->getList($select, $filter, $order, $pageNavigation),
			$__calculateTotalCount ? $this->count($filter) : 0
		);
	}

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		if (!$this->isIblockCatalog((int)$fields['IBLOCK_ID']))
		{
			$this->addError(new Error('The specified iblock is not a product catalog'));

			return null;
		}

		$iblockPermissionsCheckResult = $this->checkIblockModifyPermission($fields['IBLOCK_ID']);
		if (!$iblockPermissionsCheckResult->isSuccess())
		{
			$this->addErrors($iblockPermissionsCheckResult->getErrors());

			return null;
		}

		$prepareResult = $this->prepareFieldsForAdd($fields);
		if (!$prepareResult->isSuccess())
		{
			$this->addErrors($prepareResult->getErrors());

			return null;
		}
		$fields = $prepareResult->getData();
		unset($prepareResult);

		$this->processCustomTypesBeforeAdd($fields);

		$property = new \CIBlockProperty();
		$addResult = $property->Add($fields);
		if (!$addResult)
		{
			$error = $property->getLastError();
			if ($error !== '')
			{
				$this->addError(new Error($error));
			}
			else
			{
				$this->addError(new Error('Error adding property'));
			}

			return null;
		}

		$this->processCustomTypesAfterAdd((int)$addResult);

		return [
			$this->getServiceItemName() => $this->get($addResult),
		];
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$prepareResult = $this->prepareFieldsForUpdate($id, $fields);
		if (!$prepareResult->isSuccess())
		{
			$this->addErrors($prepareResult->getErrors());

			return null;
		}
		$fields = $prepareResult->getData();
		unset($prepareResult);

		$property = new \CIBlockProperty();
		$updateResult = $property->Update($id, $fields);
		if (!$updateResult)
		{
			$error = $property->getLastError();
			if ($error !== '')
			{
				$this->addError(new Error($error));
			}
			else
			{
				$this->addError(new Error('Error updating product property'));
			}

			return null;
		}

		return [
			$this->getServiceItemName() => $this->get($id),
		];
	}

	/**
	 * @param int $id
	 * @return bool|null
	 */
	public function deleteAction(int $id): ?bool
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$application = self::getApplication();
		$application->ResetException();

		$deleteResult = \CIBlockProperty::Delete($id);
		if (!$deleteResult)
		{
			$exception = $application->GetException();
			if ($exception)
			{
				$this->addError(new Error($exception->GetString()));
			}
			else
			{
				$this->addError(new Error('Error deleting product property'));
			}

			return null;
		}

		return true;
	}

	// endregion

	/**
	 * @inheritDoc
	 */
	public function getEntityTable()
	{
		return PropertyTable::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function exists($id): Result
	{
		$result = new Result();
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError($this->getErrorEntityNotExists());

			return $result;
		}

		$property = PropertyTable::getRow([
			'select' => [
				'ID',
				'IBLOCK_ID',
			],
			'filter' => [
				'=ID' => $id,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);

		if (!$property)
		{
			$result->addError($this->getErrorEntityNotExists());

			return $result;
		}

		if (!$this->isIblockCatalog((int)$property['IBLOCK_ID']))
		{
			$result->addError($this->getErrorPropertyIblockIsNotCatalog());

			return $result;
		}

		return $result;
	}

	private function processCustomTypesBeforeAdd(array &$fields): void
	{
		$this->customUserType = '';
		if ($this->isPseudoCheckboxPropertyType($fields['PROPERTY_TYPE'], $fields['USER_TYPE']))
		{
			$fields['LIST_TYPE'] = \Bitrix\Iblock\PropertyTable::CHECKBOX;
			$this->customUserType = Enum::PROPERTY_USER_TYPE_BOOL_ENUM;
			unset($fields['USER_TYPE']);
		}
	}

	private function processCustomTypesAfterAdd(int $id): void
	{
		if ($this->customUserType === Enum::PROPERTY_USER_TYPE_BOOL_ENUM)
		{
			\CIBlockPropertyEnum::Add([
				'PROPERTY_ID' => $id,
				'VALUE' => Catalog\RestView\Product::BOOLEAN_VALUE_YES,
			]);
		}
		$this->customUserType = '';
	}

	private function isPseudoCheckboxPropertyType(string $baseType, ?string $userType): bool
	{
		return
			$baseType === PropertyTable::TYPE_LIST
			&& $userType === Enum::PROPERTY_USER_TYPE_BOOL_ENUM
		;
	}

	protected function prepareFieldsForAdd(array $fields): Result
	{
		$result = new Result();

		$propertyTypeDescr = $this->getPropertyTypeDescription($fields);
		if ($propertyTypeDescr === null)
		{
			$result->addError($this->getErrorPropertyInvalidType());

			return $result;
		}

		$property = $propertyTypeDescr['PROPERTY'];
		$fields['PROPERTY_TYPE'] = $propertyTypeDescr['PROPERTY_TYPE'];
		$fields['USER_TYPE'] = $propertyTypeDescr['USER_TYPE'];
		if ($fields['USER_TYPE'] === null)
		{
			$fields['USER_TYPE'] = false; // for \CIBlockProperty - old api use false as null database field value
			$fields['USER_TYPE_SETTINGS'] = false;
		}
		elseif ($property === null)
		{
			$fields['USER_TYPE_SETTINGS'] = false;
		}
		else
		{
			if (empty($fields['USER_TYPE_SETTINGS']))
			{
				$fields['USER_TYPE_SETTINGS'] = false;
			}
			elseif (!is_array($fields['USER_TYPE_SETTINGS']))
			{
				$result->addError($this->getErrorInvalidCustomTypeSettings());

				return $result;
			}
			else
			{
				if (!$this->validateUserSettings($fields['USER_TYPE_SETTINGS']))
				{
					$result->addError($this->getErrorInvalidCustomTypeSettings());

					return $result;
				}
			}
		}

		$result->setData($fields);

		return $result;
	}

	protected function prepareFieldsForUpdate(int $id, array $fields): Result
	{
		$result = new Result();

		$propertyTypeExists = array_key_exists('PROPERTY_TYPE', $fields);
		$userTypeExists = array_key_exists('USER_TYPE', $fields);
		$userTypeSettingsExists = array_key_exists('USER_TYPE_SETTINGS', $fields);

		if (
			$propertyTypeExists
			|| $userTypeExists
			|| $userTypeSettingsExists
		)
		{
			$compiledPropertyType = $fields;
			if (
				!$propertyTypeExists
				|| !$userTypeExists
			)
			{
				$row = $this->getPropertyById($id);
				if ($row === null)
				{
					$result->addError($this->getErrorEntityNotExists());

					return $result;
				}

				$compiledPropertyType = [
					'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'] ?? $row['PROPERTY_TYPE'],
					'USER_TYPE' => $fields['USER_TYPE'] ?? $row['USER_TYPE'],
				];
				unset($row);
			}

			$propertyTypeDescr = $this->getPropertyTypeDescription($compiledPropertyType);
			if ($propertyTypeDescr === null)
			{
				$result->addError($this->getErrorPropertyInvalidType());

				return $result;
			}

			$property = $propertyTypeDescr['PROPERTY'];
			$fields['PROPERTY_TYPE'] = $propertyTypeDescr['PROPERTY_TYPE'];
			$fields['USER_TYPE'] = $propertyTypeDescr['USER_TYPE'];

			if ($fields['USER_TYPE'] === null)
			{
				$fields['USER_TYPE'] = false; // for \CIBlockProperty - old api use false as null database field value
				$fields['USER_TYPE_SETTINGS'] = false;
			}
			elseif ($property === null)
			{
				$fields['USER_TYPE_SETTINGS'] = false;
			}
			elseif ($userTypeSettingsExists)
			{
				if (empty($fields['USER_TYPE_SETTINGS']))
				{
					$fields['USER_TYPE_SETTINGS'] = false;
				}
				elseif (!is_array($fields['USER_TYPE_SETTINGS']))
				{
					$result->addError($this->getErrorInvalidCustomTypeSettings());

					return $result;
				}
				else
				{
					if (!$this->validateUserSettings($fields['USER_TYPE_SETTINGS']))
					{
						$result->addError($this->getErrorInvalidCustomTypeSettings());

						return $result;
					}
				}
			}
		}

		$result->setData($fields);

		return $result;
	}

	protected function getPropertyTypeDescription(array $fields): ?array
	{
		$baseTypeList = [
			PropertyTable::TYPE_NUMBER => true,
			PropertyTable::TYPE_STRING => true,
			PropertyTable::TYPE_LIST => true,
			PropertyTable::TYPE_FILE => true,
			PropertyTable::TYPE_ELEMENT => true,
			PropertyTable::TYPE_SECTION => true,
		];

		$baseType = $fields['PROPERTY_TYPE'] ?? null;
		if (!is_string($baseType))
		{
			return null;
		}
		if (!isset($baseTypeList[$baseType]))
		{
			return null;
		}

		$userType = $fields['USER_TYPE'] ?? null;
		if ($userType === '' || $userType === false)
		{
			$userType = null;
		}
		if (!is_string($userType) && $userType !== null)
		{
			return null;
		}

		$property = null;
		if (
			$userType !== null
			&& !$this->isPseudoCheckboxPropertyType($baseType, $userType)
		)
		{
			$property = \CIBlockProperty::GetUserType($userType);
			if (!$property)
			{
				return null;
			}

			if ($property['PROPERTY_TYPE'] !== $baseType)
			{
				return null;
			}
		}

		return [
			'PROPERTY_TYPE' => $baseType,
			'USER_TYPE' => $userType,
			'PROPERTY' => $property,
		];
	}

	protected function validateUserSettings(array $row): bool
	{
		if (empty($row))
		{
			return true;
		}

		$result = true;
		foreach ($row as $field)
		{
			if (is_array($field))
			{
				if (!$this->validateUserSettings($field))
				{
					$result = false;
					break;
				}
			}
			elseif (!is_scalar($field) && $field !== null)
			{
				$result = false;
				break;
			}
		}

		return $result;
	}

	protected function getErrorInvalidCustomTypeSettings(): Error
	{
		return new Error('Invalid custom property type settings specified');
	}
}
