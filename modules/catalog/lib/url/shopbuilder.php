<?php
namespace Bitrix\Catalog\Url;

use Bitrix\Catalog;

class ShopBuilder extends AdminPage\CatalogBuilder
{
	public const TYPE_ID = 'SHOP';

	protected const TYPE_WEIGHT = 300;

	protected const PATH_PREFIX = '/shop/settings/';

	protected const PATH_DETAIL_CARD_PREFIX = '/shop/catalog/';

	public function __construct()
	{
		parent::__construct();
	}

	public function use(): bool
	{
		if (defined('CATALOG_PRODUCT') && defined('SELF_FOLDER_URL'))
		{
			return true;
		}
		if (!$this->request->isAdminSection())
		{
			if ($this->checkCurrentPage([
				self::PATH_PREFIX,
				self::PATH_DETAIL_CARD_PREFIX
			]))
			{
				return true;
			}
		}

		return false;
	}

	protected function initConfig(): void
	{
		parent::initConfig();
		$this->config['UI_CATALOG'] = Catalog\Config\State::isProductCardSliderEnabled();
	}

	protected function isUiCatalog(): bool
	{
		return (isset($this->config['UI_CATALOG']) && $this->config['UI_CATALOG']);
	}

	protected function initUrlTemplates(): void
	{
		$this->urlTemplates[self::PAGE_SECTION_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'menu_catalog_goods_#IBLOCK_ID#/' : 'menu_catalog_category_#IBLOCK_ID#/')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_DETAIL] = '#PATH_PREFIX#'
			.'cat_section_edit/'
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
			.($this->iblockListMixed ? 'menu_catalog_goods_#IBLOCK_ID#/' : 'menu_catalog_#IBLOCK_ID#/')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		if ($this->isUiCatalog())
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = self::PATH_DETAIL_CARD_PREFIX
				.'#IBLOCK_ID#/product/#ENTITY_ID#/'
				.'?#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = self::PATH_DETAIL_CARD_PREFIX
				.'#IBLOCK_ID#/product/0/copy/#ENTITY_ID#/';
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = '/shop/catalog/'
				.'#PRODUCT_IBLOCK_ID#/product/#PRODUCT_ID#/'
				.'variation/#ENTITY_ID#/';
		}
		else
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
				.'cat_product_edit/'
				.'?#BASE_PARAMS#'
				.'&ID=#ENTITY_ID#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL]
				.$this->getCopyAction();
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = '#PATH_PREFIX#'
				.'cat_product_edit.php'
				.'?#BASE_PARAMS#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
		}
		$this->urlTemplates[self::PAGE_ELEMENT_SEARCH] = '/bitrix/tools/iblock/element_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';
	}

	protected function getSliderPathTemplates(): array
	{
		return [
			'/^\/shop\/catalog\/[0-9]+\/product\/[0-9]+\/$/',
			'/^\/shop\/catalog\/[0-9]+\/product\/[0-9]+\/variation\/[0-9]+\/$/',
		];
	}
}
