<?php

namespace Bitrix\Catalog\Url;

class InventoryBuilder extends ShopBuilder
{
	public const TYPE_ID = 'INVENTORY';

	protected const TYPE_WEIGHT = 350;

	protected const PATH_PREFIX = '/shop/documents-catalog/';

	public function use(): bool
	{
		if (defined('URL_BUILDER_TYPE') && URL_BUILDER_TYPE === self::TYPE_ID)
		{
			return true;
		}
		if (!$this->request->isAdminSection())
		{
			if ($this->checkCurrentPage([
				self::PATH_PREFIX,
			]))
			{
				return true;
			}
		}

		return false;
	}

	protected function initUrlTemplates(): void
	{
		$this->urlTemplates[self::PAGE_SECTION_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'list/' : 'section_list/')
			.'#PARENT_ID#/'
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_DETAIL] = '#PATH_PREFIX#'
			.'section/'
			.'#ENTITY_ID#/'
			.'?#BASE_PARAMS#'
			.'&ID=#ENTITY_ID#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_COPY] = $this->urlTemplates[self::PAGE_SECTION_DETAIL]
			.$this->getCopyAction();
		$this->urlTemplates[self::PAGE_SECTION_SAVE] = '/bitrix/tools/catalog/section_save.php'
			.'?#BASE_PARAMS#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_SEARCH] = '/bitrix/tools/iblock/section_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';

		$this->urlTemplates[self::PAGE_ELEMENT_LIST] = '#PATH_PREFIX#'
			.'list/'
			.'#PARENT_ID#/'
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		if ($this->isUiCatalog())
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
				. '#IBLOCK_ID#/product/#ENTITY_ID#/'
				. '?#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = '#PATH_PREFIX#'
				. '#IBLOCK_ID#/product/0/copy/#ENTITY_ID#/';
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = '#PATH_PREFIX#'
				. '#PRODUCT_IBLOCK_ID#/product/#PRODUCT_ID#/'
				. 'variation/#ENTITY_ID#/';
		}
		else
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
				.'product/'
				.'#ENTITY_ID#/'
				.'?#BASE_PARAMS#'
				.'&ID=#ENTITY_ID#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL]
				.$this->getCopyAction();
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = '/bitrix/tools/catalog/product_save.php'
				.'?#BASE_PARAMS#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
		}
		$this->urlTemplates[self::PAGE_ELEMENT_SEARCH] = '/bitrix/tools/iblock/element_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';

		$this->urlTemplates[self::PAGE_CATALOG_SEO] = self::PATH_PREFIX . '#IBLOCK_ID#/seo/';
		$this->urlTemplates[self::PAGE_ELEMENT_SEO] = self::PATH_PREFIX . '#IBLOCK_ID#/seo/product/#PRODUCT_ID#/';
		$this->urlTemplates[self::PAGE_SECTION_SEO] = self::PATH_PREFIX . '#IBLOCK_ID#/seo/section/#SECTION_ID#/';
	}

	protected function getSliderPathTemplates(): array
	{
		return [
			'/^\/shop\/documents-catalog\/[0-9]+\/product\/[0-9]+\/$/',
			'/^\/shop\/documents-catalog\/[0-9]+\/product\/[0-9]+\/variation\/[0-9]+\/$/',
		];
	}
}
