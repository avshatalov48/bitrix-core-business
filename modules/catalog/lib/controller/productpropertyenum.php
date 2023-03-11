<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class ProductPropertyEnum extends ProductPropertyBase
{
	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['PRODUCT_PROPERTY_ENUM' => $this->getViewFields()];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$filter['PROPERTY.IBLOCK_ID'] = $this->getCatalogIds();

		return new Page(
			'PRODUCT_PROPERTY_ENUMS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param int $id
	 * @return array|null
	 */
	public function getAction(int $id): ?array
	{
		$r = $this->exists($id);
		if ($r->isSuccess())
		{
			return ['PRODUCT_PROPERTY_ENUM' => $this->get($id)];
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	public function addAction(array $fields): ?array
	{
		$checkFieldsResult = $this->checkFieldsBeforeModify($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$property = $this->getPropertyById($fields['PROPERTY_ID']);
		$propertyType = $property['PROPERTY_TYPE'];
		if ($propertyType !== PropertyTable::TYPE_LIST)
		{
			$this->addError(new Error('Only list properties are supported'));
			return null;
		}

		$application = self::getApplication();
		$application->ResetException();

		$addResult = PropertyEnumerationTable::add($fields);
		if (!$addResult->isSuccess())
		{
			$this->addErrors($addResult->getErrors());
			return null;
		}

		return ['PRODUCT_PROPERTY_ENUM' => $this->get($addResult->getId())];
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

		$checkFieldsResult = $this->checkFieldsBeforeModify($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$propertyId = $this->get($id)['PROPERTY_ID'];
		$updateResult = PropertyEnumerationTable::update([
			'ID' => $id,
			'PROPERTY_ID' => $propertyId,
		], $fields);
		if (!$updateResult)
		{
			$this->addErrors($updateResult->getErrors());
			return null;
		}

		return ['PRODUCT_PROPERTY_ENUM' => $this->get($id)];
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

		$propertyId = $this->get($id)['PROPERTY_ID'];
		$deleteResult = PropertyEnumerationTable::delete([
			'ID' => $id,
			'PROPERTY_ID' => $propertyId,
		]);

		if (!$deleteResult)
		{
			$this->addErrors($deleteResult->getErrors());
			return null;
		}

		return $deleteResult->isSuccess();
	}

	// endregion

	/**
	 * @inheritDoc
	 */
	protected function get($id)
	{
		return PropertyEnumerationTable::getRow([
			'select' => ['*', 'IBLOCK_ID' => 'PROPERTY.IBLOCK_ID'],
			'filter' => ['=ID' => $id],
		]);
	}

	/**
	 * @inheritDoc
	 */
	protected function exists($id)
	{
		$result = new Result();
		$propertyEnum = $this->get($id);
		if (!$propertyEnum || !$this->isIblockCatalog((int)$propertyEnum['IBLOCK_ID']))
		{
			$result->addError(new Error('Property enum does not exist'));
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return PropertyEnumerationTable::class;
	}
}
