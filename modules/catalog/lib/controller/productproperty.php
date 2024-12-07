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

		$typeCheckResult = $this->checkPropertyType($fields);
		if (!$typeCheckResult->isSuccess())
		{
			$this->addErrors($typeCheckResult->getErrors());

			return null;
		}

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

		$this->processCustomTypesAfterAdd((int)$addResult, $fields);

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

		$property = $this->getPropertyById($id);
		$type = [
			'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'] ?? $property['PROPERTY_TYPE'],
			'USER_TYPE' => $fields['USER_TYPE'] ?? $property['USER_TYPE'],
		];
		$typeCheckResult = $this->checkPropertyType($type);
		if (!$typeCheckResult->isSuccess())
		{
			$this->addErrors($typeCheckResult->getErrors());

			return null;
		}

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
	protected function exists($id)
	{
		$result = new Result();
		$property = $this->get($id);
		if (!$property || !$this->isIblockCatalog((int)$property['IBLOCK_ID']))
		{
			$result->addError($this->getErrorEntityNotExists());
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	private function checkPropertyType(array $fields): Result
	{
		$result = new Result();

		$type = [
			'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'] ?? null,
			'USER_TYPE' => $fields['USER_TYPE'] ?? null,
		];

		if (!in_array($type, Enum::getProductPropertyTypes(), false))
		{
			$result->addError(new Error('Invalid property type specified'));
		}

		return $result;
	}

	private function processCustomTypesBeforeAdd(array &$fields)
	{
		if (
			$fields['PROPERTY_TYPE'] === \Bitrix\Iblock\PropertyTable::TYPE_LIST
			&& $fields['USER_TYPE'] === Enum::PROPERTY_USER_TYPE_BOOL_ENUM
		)
		{
			$fields['LIST_TYPE'] = \Bitrix\Iblock\PropertyTable::CHECKBOX;
			$this->customUserType = Enum::PROPERTY_USER_TYPE_BOOL_ENUM;
			unset($fields['USER_TYPE']);
		}
	}

	private function processCustomTypesAfterAdd(int $id, array $fields)
	{
		if ($this->customUserType === Enum::PROPERTY_USER_TYPE_BOOL_ENUM)
		{
			\CIBlockPropertyEnum::Add([
				'PROPERTY_ID' => $id,
				'VALUE' => Catalog\RestView\Product::BOOLEAN_VALUE_YES,
			]);
		}
	}
}
