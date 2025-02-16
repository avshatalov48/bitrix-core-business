<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class ProductPropertyFeature extends ProductPropertyBase
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization

	// region Actions

	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
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
		if (!$property)
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}

		return [
			'FEATURES' => PropertyCatalogFeature::getPropertyFeatureList($property),
		];
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
		return [$this->getServiceItemName() => $this->get($propertyFeatureId)];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

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

		return [$this->getServiceItemName() => $this->get($id)];
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
			$result->addError($this->getErrorEntityNotExists());
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

	/**
	 * @inheritDoc
	 * @param array $params
	 * @return array
	 */
	protected function modifyListActionParameters(array $params): array
	{
		$params['filter']['PROPERTY.IBLOCK_ID'] = $this->getCatalogIds();

		return $params;
	}
}
