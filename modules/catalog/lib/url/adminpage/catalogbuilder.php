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

		public function use(): bool
		{
			return (defined('CATALOG_PRODUCT') && parent::use());
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
		}
	}
}