<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!\Bitrix\Main\Loader::includeModule("sale") || !\Bitrix\Main\Loader::includeModule("catalog"))
{
	throw new \Bitrix\Main\SystemException('Modules `sale` and `catalog` should be installed');
}

CBitrixComponent::includeComponentClass("bitrix:catalog.bigdata.products");


class CSaleBigdataFollowupMail extends CatalogBigdataProductsComponent
{
	protected function getProductIds()
	{
		if (!empty($this->arParams['ORDER_ID']))
		{
			// get order products
			$orderInfo = \Bitrix\Main\Analytics\Catalog::getOrderInfo($this->arParams['ORDER_ID']);

			$productIds = array();
			foreach ($orderInfo['products'] as $_product)
			{
				$productIds[] = $_product['product_id'];
			}

			if (!empty($productIds))
			{
				// get related products
				$response = \Bitrix\Sale\Bigdata\Cloud::getFollowUpProducts($productIds);
			}
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