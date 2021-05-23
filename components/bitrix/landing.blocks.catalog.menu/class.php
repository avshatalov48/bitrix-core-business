<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class LandingBlocksCatalogmenuComponent extends \CBitrixComponent
{
	/**
	 * Get sections from iblock by filter.
	 * @param array $filter Filter.
	 * @return array
	 */
	public static function getCatalogSections(array $filter = array())
	{
		$items = array();

		if (
			\Bitrix\Main\Loader::includeModule('iblock') &&
			($params = \Bitrix\Landing\Node\Component::getIblockParams())
		)
		{
			if (empty($filter))
			{
				$filter['SECTION_ID'] = false;
			}
			$filter = array(
				'IBLOCK_ID' => $params['id']
			) + $filter;
			$res = \CIBlockSection::getList(
				array(
					'SORT' => 'ASC'
				),
				$filter,
				false,
				array(
					'ID', 'NAME', 'SECTION_PAGE_URL'
				)
			);
			while ($row = $res->getNext())
			{
				$row['SECTION_PAGE_URL'] = \Bitrix\Landing\Node\Component::getIblockURL(
					$row['ID'],
					'section'
				);
				$items[$row['ID']] = $row;
			}
		}

		return $items;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (!\Bitrix\Main\Loader::includeModule('landing'))
		{
			return;
		}
		if ($params = \Bitrix\Landing\Node\Component::getIblockParams())
		{
			$filter = array();
			if (
				isset($this->arParams['AVAILABLE']) &&
				!empty($this->arParams['AVAILABLE'])
			)
			{
				$filter['ID'] = $this->arParams['AVAILABLE'];
			}
			$this->arResult['ITEMS'] = $this->getCatalogSections($filter);

			$this->IncludeComponentTemplate();
		}
	}
}