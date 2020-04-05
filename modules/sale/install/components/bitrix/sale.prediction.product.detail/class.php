<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CSalePredictionProductDetailComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		global $APPLICATION;

		// remember src params for further ajax query
		if (!isset($params['SGP_CUR_BASE_PAGE']))
		{
			$params['SGP_CUR_BASE_PAGE'] = $APPLICATION->GetCurPage();
		}

		$this->arResult['_ORIGINAL_PARAMS'] = $params;

		if(empty($params["POTENTIAL_PRODUCT_TO_BUY"]))
		{
			$params["POTENTIAL_PRODUCT_TO_BUY"] = array();
		}
		if(!empty($params["POTENTIAL_PRODUCT_TO_BUY"]) && empty($params["POTENTIAL_PRODUCT_TO_BUY"]['QUANTITY']))
		{
			$params["POTENTIAL_PRODUCT_TO_BUY"]['QUANTITY'] = 1;
		}

		$params['POTENTIAL_PRODUCT_TO_BUY']['ELEMENT'] = array(
			'ID' => $params['POTENTIAL_PRODUCT_TO_BUY']['ID'],
		);
		$offerId = $this->request->getPost('offerId');
		if($offerId)
		{
			$params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID'] = $offerId;
		}
		if(!empty($params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID']))
		{
			$params['POTENTIAL_PRODUCT_TO_BUY']['ID'] = $params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID'];
		}

		return $params;
	}

	public function executeComponent()
	{
		if(!Loader::includeModule('sale') || !Loader::includeModule('catalog'))
		{
			return;
		}

		if(!$this->request->isAjaxRequest())
		{
			$this->arResult['REQUEST_ITEMS'] = true;
			$this->arResult['RCM_TEMPLATE'] = $this->getTemplateName();
		}
		else
		{
			$potentialBuy = array_intersect_key($this->arParams['POTENTIAL_PRODUCT_TO_BUY'], array(
				'ID' => true,
				'MODULE' => true,
				'PRODUCT_PROVIDER_CLASS' => true,
				'QUANTITY' => true,
			));

			$manager = Bitrix\Sale\Discount\Prediction\Manager::getInstance();
			$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID)->getOrderableItems();

			global $USER;
			if ($USER instanceof \CUser && $USER->getId())
			{
				$manager->setUserId($USER->getId());
			}

			$this->arResult['PREDICTION_TEXT'] = $manager->getFirstPredictionTextByProduct($basket, $potentialBuy);
		}

		$this->includeComponentTemplate();
	}
}