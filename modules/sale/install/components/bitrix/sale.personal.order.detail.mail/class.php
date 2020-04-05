<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
use Bitrix\Main;
use Bitrix\Main\Localization;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
	CBitrixComponent::includeComponentClass("bitrix:sale.personal.order.detail");
class CBitrixPersonalOrderDetailMail extends CBitrixPersonalOrderDetailComponent
{
	/**
	 * @return void
	 */
	protected function checkOrder()
	{
		if (!($this->order))
		{
			$this->doCaseOrderIdNotSet();
		}
	}

	/**
	 * Function could describe what to do when order ID not set. By default, component will redirect to list page.
	 * @return void
	 */
	protected function doCaseOrderIdNotSet()
	{
		throw new Main\SystemException(
			Localization\Loc::getMessage("SPOD_NO_ORDER", array("#ID#" => $this->requestData["ID"])),
			self::E_ORDER_NOT_FOUND
		);
	}

	/**
	 * @return array
	 */
	protected function createCacheId()
	{
		global $APPLICATION;

		return array(
			$APPLICATION->GetCurPage(),
			$this->dbResult["ID"],
			$this->dbResult["PERSON_TYPE_ID"],
			$this->dbResult["DATE_UPDATE"]->toString(),
			$this->useCatalog,
			false
		);
	}

	/**
	 * Function implements all the life cycle of the component
	 * @return void
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkRequiredModules();

			//$this->checkAuthorized();
			$this->loadOptions();
			$this->processRequest();

			$this->obtainData();
			$this->formatResult();

			$this->setTitle();
		}
		catch(Exception $e)
		{
			$this->errorsFatal[htmlspecialcharsbx($e->getCode())] = htmlspecialcharsbx($e->getMessage());
		}

		$this->formatResultErrors();

		$this->includeComponentTemplate();
	}
}