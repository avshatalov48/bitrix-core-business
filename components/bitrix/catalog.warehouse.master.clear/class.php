<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;

/**
 * Class WarehouseMasterClear
 */
class WarehouseMasterClear extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
	/**
	 * @return void
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function executeComponent()
	{
		Main\Loader::includeModule('crm');

		$this->arResult['IS_USED_ONEC'] = \Bitrix\Catalog\Component\UseStore::isUsedOneC();
		$this->arResult['IS_PLAN_RESTRICTED'] = \Bitrix\Catalog\Component\UseStore::isPlanRestricted();
		$this->arResult['IS_USED'] = \Bitrix\Catalog\Component\UseStore::isUsed();
		$this->arResult['IS_EMPTY'] = \Bitrix\Catalog\Component\UseStore::isEmpty();
		$this->arResult['CONDUCTED_DOCUMENTS_EXIST'] = \Bitrix\Catalog\Component\UseStore::conductedDocumentsExist();
		$this->includeComponentTemplate();
	}

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [];
	}
}
