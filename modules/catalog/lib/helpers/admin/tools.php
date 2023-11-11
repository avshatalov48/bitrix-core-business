<?php

namespace Bitrix\Catalog\Helpers\Admin;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

/**
 * Class Tools
 * Provides various useful methods for admin pages.
 *
 * @package Bitrix\Catalog\Helpers\Admin
 */
class Tools
{
	/**
	 * Return array with edit url for all price types.
	 *
	 * @return array
	 */
	public static function getPriceTypeLinkList(): array
	{
		global $adminPage, $adminSidePanelHelper;

		$selfFolderUrl = $adminPage->getSelfFolderUrl();

		if (!(
			AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			|| AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
		))
		{
			return [];
		}

		$priceTypeLinkTitle = Main\Text\HtmlFilter::encode(
			AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
				? Loc::getMessage('CATALOG_HELPERS_ADMIN_TOOLS_MESS_PRICE_TYPE_EDIT_TITLE')
				: Loc::getMessage('CATALOG_HELPERS_ADMIN_TOOLS_MESS_PRICE_TYPE_VIEW_TITLE')
		);

		$priceTypeList = Catalog\GroupTable::getTypeList();
		if (empty($priceTypeList))
		{
			return [];
		}
		$result = [];
		foreach ($priceTypeList as $priceType)
		{
			$id = $priceType['ID'];
			$title = $priceType['NAME_LANG'];
			$fullTitle = '['. $id .'] [' . $priceType['NAME'] . ']' . ($title !== null ? ' ' . $title : '');
			$editUrl = $selfFolderUrl . 'cat_group_edit.php?ID=' . $id . '&lang=' . LANGUAGE_ID;
			$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
			$result[$id] =
				'<a href="' . $editUrl . '"'
				. ' title="' . Main\Text\HtmlFilter::encode($priceTypeLinkTitle) . '">'
				. Main\Text\HtmlFilter::encode($fullTitle)
				. '</a>'
			;

			unset($fullTitle, $title, $id);
		}
		unset($priceType, $priceTypeList, $priceTypeLinkTitle);

		return $result;
	}

	/**
	 * Return price type list for dropdown selectors.
	 *
	 * @param bool $codeIndex	Use price code for result index.
	 * @return array
	 */
	public static function getPriceTypeList(bool $codeIndex = false): array
	{
		$result = [];
		$codeIndex = ($codeIndex === true);

		foreach (Catalog\GroupTable::getTypeList() as $priceType)
		{
			$id = $priceType['ID'];
			$title = $priceType['NAME_LANG'];
			$index = ($codeIndex ? $priceType['NAME'] : $id);
			$result[$index] = '['. $id .'] [' . $priceType['NAME'] . ']' . ($title !== null ? ' ' . $title : '');
			unset($index, $title, $id);
		}
		unset($priceType);

		return $result;
	}
}
