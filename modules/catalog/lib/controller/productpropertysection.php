<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Main\Error;

final class ProductPropertySection extends ProductPropertyBase
{
	use ListAction; // default listAction realization

	private const BLANK_SECTION = 0;

	// region Actions

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

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

		return [$this->getServiceItemName() => $this->get($propertyId)];
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
		if (!$property)
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}
		$fields['IBLOCK_ID'] = $property['IBLOCK_ID'];

		\CIBlockSectionPropertyLink::Set(self::BLANK_SECTION, $propertyId, $fields);

		$result = $this->get($propertyId);
		if (!$result)
		{
			$this->addError(new Error('Error setting section properties'));
			return null;
		}

		return [$this->getServiceItemName() => $this->get($propertyId)];
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

	/**
	 * @inheritDoc
	 * @param array $params
	 * @return array
	 */
	protected function modifyListActionParameters(array $params): array
	{
		$params['filter']['PROPERTY.IBLOCK_ID'] = $this->getCatalogIds();
		$params['order'] = empty($params['order']) ? ['IBLOCK_ID' => 'ASC'] : $params['order'];

		return $params;
	}
}
