<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class ProductPropertyFeature extends ProductPropertyBase
{
	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['PRODUCT_PROPERTY_FEATURE' => $this->getViewFields()];
	}

	/**
	 * @param int $propertyId
	 * @return array|null
	 */
	public function getAvailableFeaturesByPropertyAction(int $propertyId): ?array
	{
		$checkPropertyResult = $this->checkProperty($propertyId);
		if (!$checkPropertyResult->isSuccess())
		{
			$this->addErrors($checkPropertyResult->getErrors());
			return null;
		}

		$property = $this->getPropertyById($propertyId);

		return ['FEATURES' => PropertyCatalogFeature::getPropertyFeatureList($property)];
	}

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$checkFieldsResult = $this->checkFieldsBeforeModify($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$propertyId = (int)$fields['PROPERTY_ID'];
		unset($fields['PROPERTY_ID']);

		$addResult = PropertyCatalogFeature::addFeatures($propertyId, [$fields]);
		if (!$addResult->isSuccess())
		{
			$this->addErrors($addResult->getErrors());
			return null;
		}

		$propertyFeatureId = current($addResult->getData());
		return ['PRODUCT_PROPERTY_FEATURE' => $this->get($propertyFeatureId)];
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
			return ['PRODUCT_PROPERTY_FEATURE' => $this->get($id)];
		}

		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$filter['PROPERTY.IBLOCK_ID'] = $this->getCatalogIds();

		return new Page(
			'PRODUCT_PROPERTY_FEATURES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
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

		$propertyId = (int)$fields['PROPERTY_ID'];
		unset($fields['PROPERTY_ID']);
		$updateResult = PropertyCatalogFeature::updateFeatures($propertyId, [$fields]);
		if (!$updateResult)
		{
			$this->addErrors($updateResult->getErrors());
			return null;
		}

		return ['PRODUCT_PROPERTY_FEATURE' => $this->get($id)];
	}

	// endregion

	/**
	 * @inheritDoc
	 */
	protected function get($id)
	{
		return PropertyFeatureTable::getRow([
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
		$propertyFeature = $this->get($id);
		if (!$propertyFeature || !$this->isIblockCatalog((int)$propertyFeature['IBLOCK_ID']))
		{
			$result->addError(new Error('Property feature does not exist'));
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return PropertyFeatureTable::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkPermissionEntity($name, $arguments = [])
	{
		if ($name === 'getavailablefeaturesbyproperty')
		{
			return $this->checkReadPermissionEntity();
		}

		return parent::checkPermissionEntity($name);
	}
}
