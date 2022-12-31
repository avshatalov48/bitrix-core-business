<?php

namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Collection;

abstract class ProductPropertyBase extends Controller
{
	/**
	 * @return array
	 */
	protected function getCatalogIds(): array
	{
		static $catalogIds = null;

		if (is_null($catalogIds))
		{
			$catalogIds = array_column(CatalogIblockTable::getList(['select' => ['IBLOCK_ID']])->fetchAll(), 'IBLOCK_ID');
			Collection::normalizeArrayValuesByInt($catalogIds);
		}

		return $catalogIds;
	}

	/**
	 * @param $iblockId
	 * @return bool
	 */
	protected function isIblockCatalog(int $iblockId): bool
	{
		return in_array($iblockId, $this->getCatalogIds(), true);
	}

	/**
	 * @param int $iblockId
	 * @return Result
	 */
	protected function checkIblockModifyPermission(int $iblockId): Result
	{
		$result = new Result();

		if (!\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_EDIT))
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
	}

	/**
	 * @param int $propertyId
	 * @return Result
	 */
	protected function checkProperty(int $propertyId): Result
	{
		$result = new Result();
		$property = $this->getPropertyById($propertyId);
		if (!$property)
		{
			$result->addError(new Error('The specified property does not exist'));
			return $result;
		}

		if (!$this->isIblockCatalog((int)$property['IBLOCK_ID']))
		{
			$result->addError(new Error('The specified property does not belong to a product catalog'));
			return $result;
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	protected function checkFieldsBeforeModify(array $fields): Result
	{
		$result = new Result();

		$newPropertyId = (int)$fields['PROPERTY_ID'];
		$checkPropertyResult = $this->checkProperty($newPropertyId);
		if (!$checkPropertyResult->isSuccess())
		{
			$result->addErrors($checkPropertyResult->getErrors());
			return $result;
		}

		$newProperty = $this->getPropertyById($newPropertyId);
		$iblockPermissionsCheckResult = $this->checkIblockModifyPermission($newProperty['IBLOCK_ID']);
		if (!$iblockPermissionsCheckResult->isSuccess())
		{
			$result->addErrors($iblockPermissionsCheckResult->getErrors());
			return $result;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!($this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)))
		{
			$r->addError(new Error('Access Denied'));
		}

		return $r;
	}

	/**
	 * @inheritDoc
	 */
	protected function checkModifyPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	/**
	 * @param int $id
	 * @return array|bool
	 */
	protected function getPropertyById(int $id)
	{
		static $map = [];

		if (!isset($map[$id]))
		{
			$map[$id] = PropertyTable::getById($id)->fetch();
		}

		return $map[$id];
	}
}
