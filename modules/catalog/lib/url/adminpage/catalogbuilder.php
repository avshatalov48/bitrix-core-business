<?php
namespace Bitrix\Catalog\Url\AdminPage;

use Bitrix\Main\Loader,
	Bitrix\Iblock;

if (Loader::includeModule('iblock'))
{
	class CatalogBuilder extends Iblock\Url\AdminPage\IblockBuilder
	{
		public const TYPE_ID = 'CATALOG';

		protected const TYPE_WEIGHT = 200;

		public const PAGE_OFFER_DETAIL = 'offerDetail';
		/** @var null|array */
		protected $catalog = null;
		/** @var null|string */
		protected $catalogType = null;

		protected $parents = [];

		public function use(): bool
		{
			return (defined('CATALOG_PRODUCT') && parent::use());
		}

		public function setIblockId(int $iblockId): void
		{
			if ($this->iblockId !== $iblockId)
			{
				parent::setIblockId($iblockId);
				$this->setCatalog();
			}
		}

		public function clearPreloadedUrlData(): void
		{
			$this->parents = [];
		}

		public function getProductDetailUrl(int $entityId, array $options = [], string $additional = ''): string
		{
			if (empty($this->catalog))
			{
				return '';
			}
			if ($this->catalogType !== \CCatalogSku::TYPE_OFFERS)
			{
				return $this->getElementDetailUrl($entityId, $options, $additional);
			}
			else
			{
				$result = '';
				$parentId = $this->getProductParent($entityId);
				if (!empty($parentId))
				{
					$result =  $this->fillUrlTemplate(
						$this->getUrlTemplate(self::PAGE_OFFER_DETAIL),
						$this->getOfferVariables($entityId, $parentId, $options, $additional)
					);
				}
				unset($parentId);
				return $result;
			}
		}

		protected function resetIblock(): void
		{
			parent::resetIblock();
			$this->catalog = null;
			$this->catalogType = null;
		}

		protected function setCatalog()
		{
			$this->catalog = \CCatalogSku::GetInfoByIBlock($this->iblockId);
			if ($this->catalog === false)
			{
				$this->catalog = null;
			}
			if (empty($this->catalog))
			{
				$this->catalogType = null;
				$this->setTemplateVariable('#PRODUCT_IBLOCK_ID#', '');
				$this->setTemplateVariable('#OFFER_IBLOCK_ID#', '');
			}
			else
			{
				$this->catalogType = $this->catalog['CATALOG_TYPE'];
				$productIblockId = '';
				$offerIblockId = '';
				switch ($this->catalogType)
				{
					case \CCatalogSku::TYPE_CATALOG:
						$productIblockId = $this->catalog['IBLOCK_ID'];
						break;
					case \CCatalogSku::TYPE_OFFERS:
					case \CCatalogSku::TYPE_FULL:
					case \CCatalogSku::TYPE_PRODUCT:
						$productIblockId = $this->catalog['PRODUCT_IBLOCK_ID'];
						$offerIblockId = $this->catalog['IBLOCK_ID'];
						break;
				}
				$this->setTemplateVariable('#PRODUCT_IBLOCK_ID#', $productIblockId);
				$this->setTemplateVariable('#OFFER_IBLOCK_ID#', $offerIblockId);
				unset($offerIblockId, $productIblockId);
			}
		}

		protected function initUrlTemplates(): void
		{
			$this->urlTemplates[self::PAGE_SECTION_LIST] = '#PATH_PREFIX#'
				.($this->iblockListMixed ? 'cat_product_list.php' : 'cat_section_admin.php')
				.'?#BASE_PARAMS#'
				.'#PARENT_FILTER#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_SECTION_DETAIL] = '#PATH_PREFIX#'
				.'cat_section_edit.php'
				.'?#BASE_PARAMS#'
				.'&ID=#ENTITY_ID#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_SECTION_COPY] = $this->urlTemplates[self::PAGE_SECTION_DETAIL]
				.$this->getCopyAction();
			$this->urlTemplates[self::PAGE_SECTION_SAVE] = '#PATH_PREFIX#'
				.'cat_section_edit.php'
				.'?#BASE_PARAMS#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_SECTION_SEARCH] = '/bitrix/tools/iblock/section_search.php'
				.'?#LANGUAGE#'
				.'#ADDITIONAL_PARAMETERS#';

			$this->urlTemplates[self::PAGE_ELEMENT_LIST] = '#PATH_PREFIX#'
				.($this->iblockListMixed ? 'cat_product_list.php' : 'cat_product_admin.php')
				.'?#BASE_PARAMS#'
				.'#PARENT_FILTER#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
				.'cat_product_edit.php'
				.'?#BASE_PARAMS#'
				.'&ID=#ENTITY_ID#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL]
				.$this->getCopyAction();
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = '#PATH_PREFIX#'
				.'cat_product_edit.php'
				.'?#BASE_PARAMS#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_SEARCH] = '/bitrix/tools/iblock/element_search.php'
				.'?#LANGUAGE#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
		}

		protected function preloadElementUrlData(array $elementIds): void
		{
			if ($this->catalogType !== \CCatalogSku::TYPE_OFFERS)
			{
				parent::preloadElementUrlData($elementIds);
			}
			else
			{
				$load = [];
				foreach ($elementIds as $id)
				{
					if (!isset($this->parents[$id]))
					{
						$this->parents[$id] = false;
						$load[] = $id;
					}
				}
				unset($id);
				if (!empty($load))
				{
					$parents = \CCatalogSku::getProductList($load, $this->iblockId);
					if (!empty($parents))
					{
						foreach ($parents as $id => $data)
						{
							$this->parents[$id] = $data['ID'];
						}
						unset($id, $data);
					}
					unset($parents);
				}
				unset($load);
			}
		}

		protected function getProductParent(int $entityId): ?int
		{
			if (!isset($this->parents[$entityId]))
			{
				$this->parents[$entityId] = false;
				$parents = \CCatalogSku::getProductList([$entityId], $this->iblockId);
				if (!empty($parents) && isset($parents[$entityId]))
				{
					$this->parents[$entityId] = $parents[$entityId]['ID'];
				}
				unset($parents);
			}
			return (!empty($this->parents[$entityId]) ? $this->parents[$entityId] : null);
		}

		protected function getOfferVariables(int $entityId, int $productId, array $options = [], string $additional = ''): array
		{
			$replaces = $this->getExtendedVariables($options, $additional);
			$replaces['#PRODUCT_ID#'] = (string)$productId;
			$replaces['#ENTITY_ID#'] = (string)$entityId;
			$replaces['#ENTITY_FILTER#'] = $this->getEntityFilter($entityId);
			return $replaces;
		}
	}
}