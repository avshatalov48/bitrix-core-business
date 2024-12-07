<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\Controller\ErrorCode;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class Offer extends Base
{
	protected const TYPE = ProductTable::TYPE_OFFER;

	private static array $parentProductCache = [];

	protected function getAllowedProductTypes(): array
	{
		return [
			ProductTable::TYPE_OFFER,
			ProductTable::TYPE_FREE_OFFER,
		];
	}

	protected function prepareFieldsForAdd(array $fields): ?array
	{
		$fields = parent::prepareFieldsForAdd($fields);
		if ($fields === null)
		{
			return null;
		}

		$iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
		if ($iblockId <= 0)
		{
			return null;
		}

		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()->getView($this);

		$result = $view->getCatalogDescription($iblockId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$catalog = $result->getData();
		unset($result);

		$skuPropertyId = 'PROPERTY_' . $catalog['SKU_PROPERTY_ID'];

		$result = $this->prepareParentId($fields[$skuPropertyId] ?? null, $catalog['PRODUCT_IBLOCK_ID']);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$parentId = ($result->getData()['PARENT_ID']) ?? null;
		$fields[$skuPropertyId] = $parentId;
		if ($fields[$skuPropertyId] === null)
		{
			unset($fields[$skuPropertyId]);
		}
		$fields['TYPE'] = $parentId ? ProductTable::TYPE_OFFER : ProductTable::TYPE_FREE_OFFER;

		return $fields;
	}

	protected function getParentProduct(int $id): ?array
	{
		if (isset(self::$parentProductCache[$id]))
		{
			return self::$parentProductCache[$id] !== false ? self::$parentProductCache[$id] : null;
		}

		self::$parentProductCache[$id] = false;
		$result = Producttable::getRow([
			'select' => [
				'ID',
				'TYPE',
				'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
			],
			'filter' => [
				'=ID' => $id,
			]
		]);
		if ($result === null)
		{
			return null;
		}

		$result['ID'] = (int)$result['ID'];
		$result['TYPE'] = (int)$result['TYPE'];
		$result['IBLOCK_ID'] = (int)$result['IBLOCK_ID'];

		self::$parentProductCache[$id] = $result;

		return $result;
	}

	protected function prepareParentId(mixed $parentId, int $iblockId): Result
	{
		$emptyParent = ['PARENT_ID' => null];
		$result = new Result();

		if ($parentId === null)
		{
			$result->setData($emptyParent);

			return $result;
		}

		$parentId =
			is_array($parentId) && isset($parentId['VALUE'])
				? (int)$parentId['VALUE']
				: (int)$parentId
		;
		if ($parentId <= 0)
		{
			$result->setData($emptyParent);

			return $result;
		}

		$row = $this->getParentProduct($parentId);
		if ($row === null)
		{
			$result->addError(new Error(
				'Parent product not found.',
				ErrorCode::PRODUCT_OFFER_PARENT_NOT_FOUND
			));

			return $result;
		}

		if (
			$row['TYPE'] !== ProductTable::TYPE_PRODUCT
			&& $row['TYPE'] !== ProductTable::TYPE_SKU
			&& $row['TYPE'] !== ProductTable::TYPE_EMPTY_SKU
		)
		{
			$result->addError(new Error(
				'Invalid parent product type.',
				ErrorCode::PRODUCT_OFFER_BAD_PARENT_TYPE
			));

			return $result;
		}

		if ($row['IBLOCK_ID'] !== $iblockId)
		{
			$result->addError(new Error(
				'Invalid information block of the parent product.',
				ErrorCode::PRODUCT_OFFER_BAD_PARENT_IBLOCK_ID
			));

			return $result;
		}

		$result->setData(['PARENT_ID' => $row['ID']]);

		return $result;
	}
}
