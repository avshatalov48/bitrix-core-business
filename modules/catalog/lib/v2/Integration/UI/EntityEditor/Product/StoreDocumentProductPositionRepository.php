<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntityEditor\Product;

use Bitrix\Catalog\Url\InventoryBuilder;
use Bitrix\Catalog\v2\Helpers\PropertyValue;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\Config\State;
use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Main\Loader;
use Bitrix\Sale\PriceMaths;

/**
 * Class StoreDocumentProductPositionRepository
 *
 * @package Bitrix\Catalog\v2\Integration\UI\EntityEditor\Product
 *
 * * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class StoreDocumentProductPositionRepository
{
	protected const PRODUCT_PRICE_TYPE = 'PURCHASING_PRICE';

	/** @var StoreDocumentProductPositionRepository */
	private static $instance;

	private array $documentProductPositionListCollection = [];
	private int $catalogId;

	/**
	 * @return StoreDocumentProductPositionRepository
	 */
	public static function getInstance(): StoreDocumentProductPositionRepository
	{
		if (is_null(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Get array of product positions in document
	 *
	 * @param int $documentId
	 * @param int $limit
	 * @return array
	 */
	public function getList(int $documentId, int $limit = 10): array
	{
		$productPositionList = $this->fetchDocumentProductPositionList($documentId);
		return array_slice($productPositionList, 0, $limit);
	}

	/**
	 * Get product positions count of document
	 *
	 * @param int $documentId
	 * @return int
	 */
	public function getCount(int $documentId): int
	{
		return count($this->fetchDocumentProductPositionList($documentId));
	}


	private function fetchDocumentProductPositionList(int $documentId): array
	{
		if (!Loader::includeModule('sale'))
		{
			return [];
		}

		if (!empty($this->documentProductPositionListCollection[$documentId]))
		{
			return $this->documentProductPositionListCollection[$documentId];
		}

		$documentProductListData = \CCatalogStoreDocsElement::getList(
			['ID' => 'ASC'],
			['DOC_ID' => $documentId],
			false,
			false,
			['ELEMENT_ID', 'ELEMENT_NAME', 'AMOUNT', 'PURCHASING_PRICE', 'BASE_PRICE']
		);

		$documentProductList = [];
		while ($product = $documentProductListData->Fetch())
		{
			$documentProductList[] = $this->formProductPositionData($product);
		}

		$this->documentProductPositionListCollection[$documentId] = $documentProductList;
		return $documentProductList;
	}

	private function formProductPositionData(array $product): array
	{
		$productPositionData = [
			'PRODUCT_NAME' => $product['ELEMENT_NAME'],
			'SUM' =>  PriceMaths::roundPrecision((float)$product[self::PRODUCT_PRICE_TYPE] * (float)$product['AMOUNT']),
		];

		$productId = $product['ELEMENT_ID'];
		$productVariation = ServiceContainer::getRepositoryFacade()->loadVariation($product['ELEMENT_ID']);
		if ($productVariation)
		{
			$image = $productVariation->getFrontImageCollection()->getFrontImage();
			$productPositionData['PHOTO_URL'] = $image ? $image->getSource() : null;
			$productPositionData['VARIATION_INFO'] = PropertyValue::getSkuPropertyDisplayValues($productVariation);

			if (!State::isProductCardSliderEnabled())
			{
				$productId = $productVariation->getParent() ? $productVariation->getParent()->getId() : $productId;
			}
		}

		$productPositionData['URL'] = $this->buildPositionUrl($productId);

		return $productPositionData;
	}

	private function buildPositionUrl(int $productId): string
	{
		$urlBuilder = BuilderManager::getInstance()->getBuilder(InventoryBuilder::TYPE_ID);
		if ($urlBuilder)
		{
			$urlBuilder->setIblockId($this->getCatalogId());
			return $urlBuilder->getElementDetailUrl($productId);
		}

		return '';
	}

	private function getCatalogId(): int
	{
		if (!isset($this->catalogId))
		{
			$this->catalogId = 0;
			if (\Bitrix\Main\Loader::includeModule('crm'))
			{
				$this->catalogId = \Bitrix\Crm\Product\Catalog::getDefaultId() ?? 0;
			}
		}

		return $this->catalogId;
	}
}
