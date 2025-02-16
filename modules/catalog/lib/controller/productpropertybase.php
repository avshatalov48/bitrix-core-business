<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

abstract class ProductPropertyBase extends Controller
{
	/**
	 * @return array
	 */
	protected function getCatalogIds(): array
	{
		$catalogIds = [];
		$iterator = CatalogIblockTable::getList([
			'select' => [
				'IBLOCK_ID'
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$catalogIds[] = (int)$row['IBLOCK_ID'];
		}
		unset($row, $iterator);

		return $catalogIds;
	}

	/**
	 * @param int $iblockId
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
		if (!$newProperty)
		{
			$result->addError($this->getErrorEntityNotExists());

			return $result;
		}

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
		$result = new Result();

		if (!($this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)))
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
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
	 * @return array|null
	 */
	protected function getPropertyById(int $id): ?array
	{
		if ($id <= 0)
		{
			return null;
		}

		return PropertyTable::getRow([
			'select' => ['*'],
			'filter' => [
				'=ID' => $id,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
	}

	protected function getErrorPropertyIblockIsNotCatalog(): Error
	{
		return new Error('The specified property does not belong to a product catalog');
	}

	protected function getErrorPropertyInvalidType(): Error
	{
		return new Error('Invalid property type specified');
	}

	protected function getErrorBadPropertyFieldValues(): Error
	{
		return new Error('Invalid property field values');
	}
}
