<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class ProductPropertySection extends ProductPropertyBase
{
	private const BLANK_SECTION = 0;

	// region Actions

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
		$order = empty($order) ? ['IBLOCK_ID' => 'ASC'] : $order;

		return new Page(
			'PRODUCT_PROPERTY_SECTIONS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param int $propertyId
	 * @return array|null
	 */
	public function getAction(int $propertyId): ?array
	{
		$checkPropertyResult = $this->checkProperty($propertyId);
		if (!$checkPropertyResult->isSuccess())
		{
			$this->addErrors($checkPropertyResult->getErrors());
			return null;
		}

		return ['PRODUCT_PROPERTY_SECTION' => $this->get($propertyId)];
	}

	/**
	 * @param int $propertyId
	 * @param array $fields
	 * @return array|null
	 */
	public function setAction(int $propertyId, array $fields): ?array
	{
		$checkPropertyResult = $this->checkProperty($propertyId);
		if (!$checkPropertyResult->isSuccess())
		{
			$this->addErrors($checkPropertyResult->getErrors());
			return null;
		}

		$property = $this->getPropertyById($propertyId);
		$fields['IBLOCK_ID'] = $property['IBLOCK_ID'];

		\CIBlockSectionPropertyLink::Set(self::BLANK_SECTION, $propertyId, $fields);

		$result = $this->get($propertyId);
		if (!$result)
		{
			$this->addError(new Error('Error setting section properties'));
			return null;
		}

		return ['PRODUCT_PROPERTY_SECTION' => $this->get($propertyId)];
	}

	// endregion

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return SectionPropertyTable::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkPermissionEntity($name, $arguments = [])
	{
		if ($name === 'set')
		{
			return $this->checkModifyPermissionEntity();
		}

		return parent::checkPermissionEntity($name);
	}

	/**
	 * @inheritDoc
	 */
	protected function get($id)
	{
		return SectionPropertyTable::getRow([
			'filter' => ['=PROPERTY_ID' => $id],
			'order' => ['IBLOCK_ID' => 'ASC'],
		]);
	}
}
