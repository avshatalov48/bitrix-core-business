<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

/**
 * Class CSaleGiftProductComponent
 * @deprecated No longer used by internal code and not recommended.
 * Use "sale.products.gift" instead.
 */
class CSaleGiftProductComponent extends CCatalogViewedProductsComponent
{
	/** @var \Bitrix\Sale\Discount\Gift\Manager */
	protected $giftManager;
	/** @var array */
	private $productIds;

	/**
	 * Checks required modules.
	 * @throws Exception
	 * @return void
	 */
	protected function checkModules()
	{
		parent::checkModules();
		if(!$this->isSale)
		{
			throw new SystemException(Loc::getMessage("CVP_SALE_MODULE_NOT_INSTALLED"));
		}

		$this->initGiftManager();
	}

	/**
	 * Initializes gift manager.
	 * @return void
	 */
	protected function initGiftManager()
	{
		global $USER;
		$userId = $USER instanceof CUser? $USER->getId() : null;
		$this->giftManager = \Bitrix\Sale\Discount\Gift\Manager::getInstance()->setUserId($userId);
	}

	/**
	 * Event called from includeComponent before component execution.
	 *
	 * <p>Takes component parameters as argument and should return it formatted as needed.</p>
	 * @param array[string]mixed $arParams
	 * @return array[string]mixed
	 *
	 */
	public function onPrepareComponentParams($params)
	{
		global $APPLICATION;

		// remember src params for further ajax query
		if (!isset($params['SGP_CUR_BASE_PAGE']))
		{
			$params['SGP_CUR_BASE_PAGE'] = $APPLICATION->GetCurPage();
		}
		if(empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}
		if(empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		$this->arResult['_ORIGINAL_PARAMS'] = $params;

		$params = parent::onPrepareComponentParams($params);
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

	protected function fillUrlTemplates()
	{
		parent::fillUrlTemplates();

		if(isset(
			$this->arParams['BUY_URL_TEMPLATE'],
			$this->arParams['ADD_URL_TEMPLATE'],
			$this->arParams['SUBSCRIBE_URL_TEMPLATE'],
			$this->arParams['COMPARE_URL_TEMPLATE']
		))
		{

			$this->recreateUrlTemplate('BUY_URL_TEMPLATE', $this->arParams['BUY_URL_TEMPLATE']);
			$this->recreateUrlTemplate('ADD_URL_TEMPLATE', $this->arParams['ADD_URL_TEMPLATE']);
			$this->recreateUrlTemplate('SUBSCRIBE_URL_TEMPLATE', $this->arParams['SUBSCRIBE_URL_TEMPLATE']);
			$this->recreateUrlTemplate('COMPARE_URL_TEMPLATE', $this->arParams['COMPARE_URL_TEMPLATE']);
		}
	}

	private function recreateUrlTemplate($keyTemplate, $urlTemplate)
	{
		$this->arParams[$keyTemplate] = CHTTP::urlDeleteParams(
			$urlTemplate,
			array($this->arParams['PRODUCT_ID_VARIABLE'], $this->arParams['ACTION_VARIABLE'], '')
		);
		$this->arParams[$keyTemplate] .= (mb_stripos($this->arParams[$keyTemplate], '?') === false ? '?' : '&');

		$this->urlTemplates['~' . $keyTemplate] = $this->arParams[$keyTemplate].$this->arParams['ACTION_VARIABLE'].'='.self::ACTION_BUY.'&'.$this->arParams['PRODUCT_ID_VARIABLE'].'=';
		$this->urlTemplates[$keyTemplate] = htmlspecialcharsbx($this->urlTemplates['~' . $keyTemplate]);
	}

	/**
	 * Returns gift collections for current basket.
	 * @return array
	 */
	protected function getGiftCollections()
	{
		$collections = array();

		if($this->arResult['REQUEST_ITEMS'])
		{
			return $collections;
		}

		if(!empty($this->arParams['POTENTIAL_PRODUCT_TO_BUY']))
		{
			if($this->isCurrentProductGift($this->arParams['POTENTIAL_PRODUCT_TO_BUY']))
			{
				return $collections;
			}

			$potentialBuy = array_intersect_key($this->arParams['POTENTIAL_PRODUCT_TO_BUY'], array(
				'ID' => true,
				'MODULE' => true,
				'PRODUCT_PROVIDER_CLASS' => true,
				'QUANTITY' => true,
			));

			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			$collections = $this->giftManager->getCollectionsByProduct(
				$basketClass::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID), $potentialBuy
			);
		}

		return $collections;
	}

	/**
	 * Returns list of product ids which will be showed.
	 * @return array
	 */
	protected function getProductIds()
	{
		if($this->productIds !== null)
		{
			return $this->productIds;
		}

		if(empty($this->arParams['POTENTIAL_PRODUCT_TO_BUY']['ID']))
		{
			return array();
		}

		\Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
		$collections = $this->getGiftCollections();
		\Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();

		$this->productIds = array();
		foreach($collections as $collection)
		{
			foreach($collection as $gift)
			{
				$this->productIds[] = $gift->getProductId();
			}
			unset($gift);
		}
		unset($collection);

		return $this->productIds;
	}

	/**
	 * Returns pure offers which exist in $this->linkItems, $this->items.
	 * You can use the method after execution parent::setItemsOffers(),
	 * which fills necessary $this->linkItems, $this->items.
	 * @return array
	 */
	private function getPureOffers()
	{
		$pureOffers = array();
		foreach($this->getProductIds() as $productId)
		{
			if(isset($this->linkItems[$productId]))
			{
				continue;
			}
			$pureOffer = $this->findPureOfferInItemsByOfferId($productId);
			if(!$pureOffer)
			{
				continue;
			}
			if(!isset($pureOffers[$pureOffer['LINK_ELEMENT_ID']]))
			{
				$pureOffers[$pureOffer['LINK_ELEMENT_ID']] = array();
			}
			$pureOffers[$pureOffer['LINK_ELEMENT_ID']][] = $pureOffer;
		}
		unset($productId);

		return $pureOffers;
	}

	/**
	 * Finds array with data which fully describes offer (SKU) by offer id.
	 * The method uses $this->items.
	 * @param $offerId
	 * @return null|array
	 */
	private function findPureOfferInItemsByOfferId($offerId)
	{
		if(!empty($this->items[$offerId]['OFFERS']))
		{
			//positive search
			foreach($this->items[$offerId]['OFFERS'] as $i => $offer)
			{
				if($offer['ID'] == $offerId)
				{
					return $offer;
				}
			}
			unset($offer);
		}

		//if we have two or more offers for one product, then only one of them has OFFERS, all of another don't have.
		foreach($this->items as $item)
		{
			if(!$item['OFFERS'])
			{
				continue;
			}
			foreach($item['OFFERS'] as $offer)
			{
				if($offer['ID'] == $offerId)
				{
					return $offer;
				}
			}
			unset($offer);
		}
		unset($offer);


		return null;
	}

	/**
	 * Sets offers to specific product. If exists another offer, then it will delete.
	 * Notice! List of offers have to belong to identical product.
	 * @param array $pureOffers
	 * @return void
	 */
	private function setPureOffersToProduct(array $pureOffers)
	{
		$parentElementId = null;
		foreach($pureOffers as $pureOffer)
		{
			if(!$parentElementId)
			{
				$parentElementId = $pureOffer['LINK_ELEMENT_ID'];
			}
			//we have to delete another offers, because they will repeat base product.
			unset($this->items[$pureOffer['ID']]);
		}
		unset($pureOffer);

		if($parentElementId)
		{
			$this->linkItems[$parentElementId]['OFFERS'] = $pureOffers;
		}
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	final protected function setItemsOffers()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::setItemsOffers();

		foreach($this->linkItems as &$item)
		{
			if(!isset($item['OFFERS']))
			{
				continue;
			}

			foreach($item['OFFERS'] as &$offer)
			{
				$this->setGiftDiscountToMinPrice($offer);
			}
			unset($offer);
		}
		unset($item);

		foreach($this->getPureOffers() as $offers)
		{
			$this->setPureOffersToProduct($offers);
		}
		unset($offerId);

		if($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	protected function setItemsPrices()
	{
		parent::setItemsPrices();

		foreach ($this->items as &$item)
		{
			if (!empty($item['OFFERS']))
			{
				continue;
			}

			$this->setGiftDiscountToMinPrice($item);
		}
	}

	protected function formatResult()
	{
		$this->items = array_slice($this->items, 0, $this->arParams['PAGE_ELEMENT_COUNT']);
		parent::formatResult();
	}

	/**
	 * @param $offer
	 * @return mixed
	 */
	private function setGiftDiscountToMinPrice(array &$offer)
	{
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE_NOVAT'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];
		$offer['MIN_PRICE']['PRINT_DISCOUNT_VALUE_VAT'] = $offer['MIN_PRICE']['PRINT_DISCOUNT_DIFF'];

		$offer['MIN_PRICE']['DISCOUNT_DIFF'] = $offer['MIN_PRICE']['VALUE'];
		$offer['MIN_PRICE']['DISCOUNT_DIFF'] = $offer['MIN_PRICE']['PRINT_VALUE'];
		$offer['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] = 100;
		$offer['MIN_PRICE']['DISCOUNT_VALUE_NOVAT'] = 0;
		$offer['MIN_PRICE']['DISCOUNT_VALUE_VAT'] = 0;
		$offer['MIN_PRICE']['DISCOUNT_VALUE'] = 0;
	}

	/**
	 * Returns catalog prices data by product.
	 * @param array $item Product.
	 * @return array
	 */
	protected function getPriceDataByItem(array $item)
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		$priceDataByItem = parent::getPriceDataByItem($item);

		if($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}

		return $priceDataByItem;
	}

	protected function isCurrentProductGift(array $product)
	{
		global $USER;
		if($product['MODULE'] !== 'catalog')
		{
			return false;
		}

		$elementIds = array($product['ID']);
		if($product['ID'] != $product['ELEMENT']['ID'])
		{
			$elementIds[] = $product['ELEMENT']['ID'];
		}

		return (bool)\Bitrix\Sale\Discount\Gift\RelatedDataTable::getRow(array(
			'select' => array('ID'),
			'filter' => array(
				array(
					'LOGIC' => 'OR',

					'@ELEMENT_ID' => $elementIds,
					'SECTION_ID' => $product['SECTION']['ID']
				),
				'=DISCOUNT_GROUP.ACTIVE' => 'Y',
				'DISCOUNT_GROUP.GROUP_ID' => $USER->getUserGroupArray(),
			),
		));
	}

	public function executeComponent()
	{
		if(!$this->request->isAjaxRequest())
		{
			$this->arResult['REQUEST_ITEMS'] = true;
			$this->arResult['RCM_TEMPLATE'] = $this->getTemplateName();
		}
		$this->arResult['POTENTIAL_PRODUCT_TO_BUY'] = $this->arParams['POTENTIAL_PRODUCT_TO_BUY'];
		parent::executeComponent();
	}
}