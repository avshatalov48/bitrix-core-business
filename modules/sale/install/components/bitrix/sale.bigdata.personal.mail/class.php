<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!\Bitrix\Main\Loader::includeModule("sale") || !\Bitrix\Main\Loader::includeModule("catalog"))
{
	throw new \Bitrix\Main\SystemException('Modules `sale` and `catalog` should be installed');
}

CBitrixComponent::includeComponentClass("bitrix:catalog.bigdata.products");


class CSaleBigdataPersonalMail extends CatalogBigdataProductsComponent
{
	protected function getProductIds()
	{
		if (!empty($this->arParams['USER_ID']))
		{
			$response = \Bitrix\Sale\Bigdata\Cloud::getPersonalRecommendation(
				$this->arParams['USER_ID'],
				min(1000, $this->arParams['PAGE_ELEMENT_COUNT'])
			);
		}

		if (!empty($response['items']))
		{
			return $response['items'];
		}
		else
		{
			return parent::getProductIds();
		}
	}

	public function executeComponent()
	{
		$this->arResult['ITEMS'] = $this->getProductIds();

		$this->includeComponentTemplate();
	}
}
