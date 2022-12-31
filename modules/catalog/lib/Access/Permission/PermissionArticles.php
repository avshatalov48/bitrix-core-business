<?php

namespace Bitrix\Catalog\Access\Permission;

use Bitrix\Catalog\Access\Component\PermissionConfig;
use Bitrix\Main\Localization\Loc;

class PermissionArticles
{
	private const PERMISSIONS_ARTICLES = [
		PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS => 16342604,
		PermissionDictionary::CATALOG_STORE_VIEW => 16342618,
		PermissionDictionary::CATALOG_RESERVE_SETTINGS => 16342830,
		PermissionDictionary::CATALOG_SETTINGS_ACCESS => 16377052,
		PermissionDictionary::CATALOG_SETTINGS_EDIT_RIGHTS => 16377052,
		PermissionDictionary::CATALOG_SETTINGS_SELL_NEGATIVE_COMMODITIES => 16377052,
		PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_EDIT => 16342856,
		PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_SET_PROFILE_FOR_USERS => 16342856,
		PermissionDictionary::CATALOG_PRODUCT_EDIT_CATALOG_PRICE => 16342446,
		PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE => 16342542,
		PermissionDictionary::CATALOG_PRODUCT_PUBLIC_VISIBILITY => 16342560,
		PermissionDictionary::CATALOG_SETTINGS_STORE_DOCUMENT_CARD_EDIT => 16342652,
		PermissionDictionary::CATALOG_EXPORT_EXECUTION => 16342582,
		PermissionDictionary::CATALOG_IMPORT_EXECUTION => 16342582,
	];
	private const SECTION_ARTICLES = [
		PermissionConfig::SECTION_STORE_DOCUMENT_ARRIVAL => 16342676,
		PermissionConfig::SECTION_STORE_DOCUMENT_STORE_ADJUSTMENT => 16342722,
		PermissionConfig::SECTION_STORE_DOCUMENT_MOVING => 16342750,
		PermissionConfig::SECTION_STORE_DOCUMENT_DEDUCT => 16342812,
		PermissionConfig::SECTION_STORE_DOCUMENT_SALES_ORDER => 16342768,
	];

	private function getLinkHtml(int $code): string
	{
		$onclick = "top.BX.Helper.show('redirect=detail&code={$code}'); return false;";
		$text = Loc::getMessage('CATALOG_PERMISSION_DICTIONARY_ARTICLES_LINK_TEXT');

		return '<a href="javascript:;" onclick="' . $onclick . '">' . $text .'</a>';
	}

	/**
	 * HTML link to permission article.
	 *
	 * @param string $permissionId
	 *
	 * @return string|null
	 */
	public function getPermissionArticleLink(string $permissionId): ?string
	{
		$code = self::PERMISSIONS_ARTICLES[$permissionId] ?? null;
		if (!$code)
		{
			return null;
		}

		return $this->getLinkHtml($code);
	}

	/**
	 * HTML link to section article.
	 *
	 * @param string $sectionCode
	 *
	 * @return string|null
	 */
	public function getSectionArticleLink(string $sectionCode): ?string
	{
		$code = self::SECTION_ARTICLES[$sectionCode] ?? null;
		if (!$code)
		{
			return null;
		}

		return $this->getLinkHtml($code);
	}
}
