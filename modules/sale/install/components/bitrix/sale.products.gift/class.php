<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\Component\ElementList;

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('SPG_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class SaleProductsGiftComponent extends ElementList
{
	/** @var \Bitrix\Sale\Discount\Gift\Manager */
	protected $giftManager;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(true)->setMultiIblockMode(true)->setPaginationMode(false);
	}

	public function onPrepareComponentParams($params)
	{
		if (empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}

		if (empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		if (!isset($params['PAGE_ELEMENT_COUNT']))
		{
			$params['PAGE_ELEMENT_COUNT'] = 4;
		}

		$params = parent::onPrepareComponentParams($params);

		if (empty($params['POTENTIAL_PRODUCT_TO_BUY']))
		{
			$params['POTENTIAL_PRODUCT_TO_BUY'] = array();
		}

		if (!empty($params['POTENTIAL_PRODUCT_TO_BUY']) && empty($params['POTENTIAL_PRODUCT_TO_BUY']['QUANTITY']))
		{
			$params['POTENTIAL_PRODUCT_TO_BUY']['QUANTITY'] = 1;
		}

		$params['POTENTIAL_PRODUCT_TO_BUY']['ELEMENT'] = array(
			'ID' => $params['POTENTIAL_PRODUCT_TO_BUY']['ID']
		);

		$offerId = $this->request->getPost('offerId');
		if ($offerId)
		{
			$params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID'] = $offerId;
		}

		if (!empty($params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID']))
		{
			$params['POTENTIAL_PRODUCT_TO_BUY']['ID'] = $params['POTENTIAL_PRODUCT_TO_BUY']['PRIMARY_OFFER_ID'];
		}

		return $params;
	}

	protected function checkModules()
	{
		if ($success = parent::checkModules())
		{
			if (!$this->useCatalog || !Main\Loader::includeModule('sale'))
			{
				$success = false;
				$this->abortResultCache();

				if (!$this->useCatalog)
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPG_CATALOG_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}

				if (!Main\Loader::includeModule('sale'))
				{
					$this->errorCollection->setError(new Error(Loc::getMessage('SPG_SALE_MODULE_NOT_INSTALLED'), self::ERROR_TEXT));
				}
			}
		}

		if ($success)
		{
			$this->initGiftManager();
		}

		return $success;
	}

	/**
	 * Initializes gift manager.
	 * @return void
	 */
	protected function initGiftManager()
	{
		global $USER;
		$userId = $USER instanceof CAllUser? $USER->getId() : null;
		$this->giftManager = \Bitrix\Sale\Discount\Gift\Manager::getInstance()->setUserId($userId);
	}

	/**
	 * Returns gift collections for current basket.
	 * @return array
	 */
	protected function getGiftCollections()
	{
		$collections = array();

		if (!empty($this->arParams['POTENTIAL_PRODUCT_TO_BUY']))
		{
			if ($this->isCurrentProductGift($this->arParams['POTENTIAL_PRODUCT_TO_BUY']))
			{
				return $collections;
			}

			$potentialBuy = array_intersect_key($this->arParams['POTENTIAL_PRODUCT_TO_BUY'], array(
				'ID' => true,
				'MODULE' => true,
				'PRODUCT_PROVIDER_CLASS' => true,
				'QUANTITY' => true,
			));

			$collections = $this->giftManager->getCollectionsByProduct(
				\Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), $this->getSiteId()), $potentialBuy
			);
		}

		return $collections;
	}

	/**
	 * Returns list of product ids which will be showed on first hit.
	 * @return array
	 */
	protected function getProductIds()
	{
		return array();
	}

	/**
	 * Returns list of product ids which will be showed via ajax.
	 * @return array
	 */
	protected function getDeferredProductIds()
	{
		if (empty($this->arParams['POTENTIAL_PRODUCT_TO_BUY']['ID']))
		{
			return array();
		}

		\Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
		$collections = $this->getGiftCollections();
		\Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();

		$productIds = array();

		foreach ($collections as $collection)
		{
			/** @var \Bitrix\Sale\Discount\Gift\Gift $gift */
			foreach ($collection as $gift)
			{
				$productIds[] = $gift->getProductId();
			}
			unset($gift);
		}
		unset($collection);

		return $productIds;
	}

	protected function processProducts()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processProducts();

		foreach ($this->elementLinks as &$element)
		{
			if (!empty($element['ITEM_PRICES']))
			{
				$this->setGiftDiscountToMinPrice($element);
			}
		}
		unset($element);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	protected function processOffers()
	{
		$isEnabledCalculationDiscounts = CIBlockPriceTools::isEnabledCalculationDiscounts();
		CIBlockPriceTools::disableCalculationDiscounts();

		parent::processOffers();

		foreach ($this->elementLinks as &$item)
		{
			if (!isset($item['OFFERS']))
			{
				continue;
			}

			foreach ($item['OFFERS'] as &$offer)
			{
				if (!empty($offer['ITEM_PRICES']))
				{
					$this->setGiftDiscountToMinPrice($offer);
				}
			}
			unset($offer);
		}
		unset($item);

		if ($isEnabledCalculationDiscounts)
		{
			CIBlockPriceTools::enableCalculationDiscounts();
		}
	}

	/**
	 * @param array $item
	 */
	protected function setGiftDiscountToMinPrice(array &$item)
	{
		$selectedPrice =& $item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED']];

		$selectedPrice['PRICE'] = $selectedPrice['DISCOUNT'];
		$selectedPrice['PRINT_PRICE'] = $selectedPrice['PRINT_DISCOUNT'];
		$selectedPrice['DISCOUNT'] = $selectedPrice['BASE_PRICE'];
		$selectedPrice['PRINT_DISCOUNT'] = $selectedPrice['PRINT_BASE_PRICE'];
		$selectedPrice['RATIO_PRICE'] = $selectedPrice['RATIO_DISCOUNT'];
		$selectedPrice['PRINT_RATIO_PRICE'] = $selectedPrice['PRINT_RATIO_DISCOUNT'];
		$selectedPrice['RATIO_DISCOUNT'] = $selectedPrice['RATIO_BASE_PRICE'];
		$selectedPrice['PRINT_RATIO_DISCOUNT'] = $selectedPrice['PRINT_RATIO_BASE_PRICE'];
		$selectedPrice['PERCENT'] = 100;
	}

	protected function isCurrentProductGift(array $product)
	{
		global $USER;

		if ($product['MODULE'] !== 'catalog')
		{
			return false;
		}

		$elementIds = array($product['ID']);
		if ($product['ID'] != $product['ELEMENT']['ID'])
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
		$this->arResult['POTENTIAL_PRODUCT_TO_BUY'] = $this->arParams['POTENTIAL_PRODUCT_TO_BUY'];
		parent::executeComponent();
	}
}