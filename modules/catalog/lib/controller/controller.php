<?php


namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Rest\Integration\CatalogViewManager;
use Bitrix\Rest\Integration\Controller\Base;

class Controller extends Base
{
	const IBLOCK_READ = 'iblock_admin_display';
	const IBLOCK_ELEMENT_READ = 'element_read';
	const IBLOCK_ELEMENT_EDIT = 'element_edit';
	const IBLOCK_ELEMENT_DELETE = 'element_delete';
	const IBLOCK_SECTION_READ = 'section_read';
	const IBLOCK_SECTION_EDIT = 'section_edit';
	const IBLOCK_SECTION_DELETE = 'section_delete';
	const IBLOCK_ELEMENT_EDIT_PRICE = 'element_edit_price';
	const IBLOCK_SECTION_SECTION_BIND = 'section_section_bind';
	const IBLOCK_ELEMENT_SECTION_BIND = 'section_element_bind';
	const IBLOCK_EDIT = 'iblock_edit';
	const CATALOG_STORE = 'catalog_store';
	const CATALOG_READ = 'catalog_read';

	public const ERROR_ACCESS_DENIED = 'Access denied';

	protected function createViewManager(Action $action)
	{
		return new CatalogViewManager($action);
	}

	protected static function getApplication()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		return $APPLICATION;
	}

	protected static function getGlobalUser()
	{
		/** @global \CUser $USER */
		global $USER;

		return $USER;
	}

	protected static function getNavData($start, $orm = false)
	{
		if($start >= 0)
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT, 'offset' => intval($start)]
				:['nPageSize' => \IRestService::LIST_LIMIT, 'iNumPage' => intval($start / \IRestService::LIST_LIMIT) + 1]
			);
		}
		else
		{
			return ($orm ?
				['limit' => \IRestService::LIST_LIMIT]
				:['nTopCount' => \IRestService::LIST_LIMIT]
			);
		}
	}
}