<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct;
use Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection;
use Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

class FacebookProductRepository
{
	private const SERVICE_ID = 'facebook';

	public function save(array $data)
	{
		$collection = $this->loadCollection(array_keys($data));
		foreach ($collection as $exportedProduct)
		{
			$productId = $exportedProduct->getProductId();
			$exportedProduct->setTimestampX(new DateTime());
			$error = $data[$productId]['ERROR'] ?? null;
			$exportedProduct->setError($error);
			unset($data[$productId]);
		}

		foreach ($data as $productId => $productData)
		{
			$exportedProduct = new ExportedProduct();
			$exportedProduct
				->setServiceId(self::SERVICE_ID)
				->setProductId($productId)
				->setError($productData['ERROR'] ?? null)
			;
			$collection[] = $exportedProduct;
		}

		return $collection->save(true);
	}

	public function delete(array $ids): Result
	{
		$result = new Result();

		$collection = $this->loadCollection($ids);
		foreach ($collection as $exportedProduct)
		{
			$res = $exportedProduct->delete();
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	public function getProductsByIds(array $ids): array
	{
		$products = [];
		$collection = $this->loadCollection($ids);
		foreach ($collection as $exportedProduct)
		{
			$products[] = [
				'ID' => $exportedProduct->getProductId(),
				'ERROR' => $exportedProduct->getError(),
			];
		}

		return $products;
	}

	public function loadCollection(array $productIds): ExportedProductCollection
	{
		if (empty($productIds))
		{
			return ExportedProductTable::createCollection();
		}

		return
			ExportedProductTable::getList([
				'filter' => [
					'=SERVICE_ID' => self::SERVICE_ID,
					'@PRODUCT_ID' => $productIds,
				],
			])
				->fetchCollection()
			;
	}
}
