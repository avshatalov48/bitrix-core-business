<?php
namespace Bitrix\Iblock\Url\AdminPage;

use Bitrix\Main;

class IblockBuilder extends BaseBuilder
{
	public const TYPE_ID = 'IBLOCK';

	protected const TYPE_WEIGHT = 100;

	protected const PATH_PREFIX = '/bitrix/admin/';

	public function use(): bool
	{
		return Main\Context::getCurrent()->getRequest()->isAdminSection();
	}

	protected function initUrlTemplates(): void
	{
		$this->urlTemplates[self::PAGE_SECTION_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'iblock_list_admin.php' : 'iblock_section_admin.php')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_DETAIL] = '#PATH_PREFIX#'
			.'iblock_section_edit.php'
			.'?#BASE_PARAMS#'
			.'&ID=#ENTITY_ID#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_COPY] = $this->urlTemplates[self::PAGE_SECTION_DETAIL]
			.$this->getCopyAction();
		$this->urlTemplates[self::PAGE_SECTION_SAVE] = '#PATH_PREFIX#'
			.'iblock_section_edit.php'
			.'?#BASE_PARAMS#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_SEARCH] = '/bitrix/tools/iblock/section_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';

		$this->urlTemplates[self::PAGE_ELEMENT_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'iblock_list_admin.php' : 'iblock_element_admin.php')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
			.'iblock_element_edit.php'
			.'?#BASE_PARAMS#'
			.'&ID=#ENTITY_ID#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_ELEMENT_COPY] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL]
			.$this->getCopyAction();
		$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = '#PATH_PREFIX#'
			.'iblock_element_edit.php'
			.'?#BASE_PARAMS#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_ELEMENT_SEARCH] = '/bitrix/tools/iblock/element_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';
	}
}