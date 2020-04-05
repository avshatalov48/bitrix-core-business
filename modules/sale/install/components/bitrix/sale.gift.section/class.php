<?php
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Discount\Gift\RelatedDataTable;
use Bitrix\Sale\Internals\DiscountTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

/**
 * Class CSaleGiftSectionComponent
 * @deprecated No longer used by internal code and not recommended.
 * Use "sale.products.gift.section" instead.
 */
class CSaleGiftSectionComponent extends CCatalogViewedProductsComponent
{
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
		if(empty($params['SHOW_DISCOUNT_PERCENT']))
		{
			$params['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}
		if(empty($params['SHOW_OLD_PRICE']))
		{
			$params['SHOW_OLD_PRICE'] = 'Y';
		}

		return parent::onPrepareComponentParams($params);
	}

	private function getSectionId()
	{
		$sectionId = null;

		$sectionSearch = $this->arParams["SECTION_ID"] > 0 || $this->arParams["SECTION_CODE"] !== '';
		$sectionByItemSearch = $this->arParams["SECTION_ELEMENT_ID"] > 0 || $this->arParams["SECTION_ELEMENT_CODE"] !== '';

		if($sectionSearch || $sectionByItemSearch)
		{
			if($sectionSearch)
			{
				$sectionId = ($this->arParams["SECTION_ID"] > 0) ? $this->arParams["SECTION_ID"] : $this->getSectionIdByCode($this->arParams["SECTION_CODE"]);
			}
			else
			{
				$sectionId = $this->getSectionIdByElement($this->arParams["SECTION_ELEMENT_ID"], $this->arParams["SECTION_ELEMENT_CODE"]);
			}
		}

		return $sectionId;
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

		list($elementIds, $sectionIds) = $this->getGiftData();

		$this->productIds = array_unique(
			array_merge($elementIds, $this->getElementIdsFromSection(reset($sectionIds)))
		);
		return $this->productIds;
	}

	private function getElementIdsFromSection($sectionId)
	{
		if(empty($sectionId))
		{
			return array();
		}
		$ids = array();
		$query = CIBlockElement::getList(array(), array(
			'ACTIVE_DATE' => 'Y',
			'SECTION_ID' => $sectionId,
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
			'ACTIVE' => 'Y',
		), false, array('nTopCount' => $this->arParams['PAGE_ELEMENT_COUNT']), array('ID'));

		while($row = $query->fetch())
		{
			$ids[] = $row['ID'];
		}

		return $ids;
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
				$this->items[$pureOffer['ID']]['OFFERS'] = $pureOffers;
			}
			else
			{
				//we have to delete another offers, because they will repeat base product.
				unset($this->items[$pureOffer['ID']]);
			}
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

	private function getGiftData()
	{
		$elementIds = array();
		$sectionIds = array();
		$sectionId = $this->getSectionId();

		if(empty($sectionId))
		{
			return array($elementIds, $sectionIds);
		}

		$query = new \Bitrix\Main\Entity\Query(RelatedDataTable::getEntity());
		$query->addFilter('MAIN_PRODUCT_SECTION_ID', $sectionId);

		global $USER;
		$query->addFilter('=DISCOUNT_GROUP.ACTIVE', 'Y');
		$query->addFilter('DISCOUNT_GROUP.GROUP_ID', $USER->getUserGroupArray());

		$referenceField2 = new ReferenceField(
			'D',
			DiscountTable::getEntity(),
			array('=this.DISCOUNT_ID' => 'ref.ID'),
			array('join_type' => 'INNER')
		);
		$query->registerRuntimeField('', $referenceField2);

		$query->addSelect('D.ID', 'ID2');
		$query->addSelect('D.XML_ID', 'XML_ID');
		$query->addSelect('D.LID', 'LID');
		$query->addSelect('D.NAME', 'NAME');
		$query->addSelect('D.PRICE_FROM', 'PRICE_FROM');
		$query->addSelect('D.PRICE_TO', 'PRICE_TO');
		$query->addSelect('D.CURRENCY', 'CURRENCY');
		$query->addSelect('D.DISCOUNT_VALUE', 'DISCOUNT_VALUE');
		$query->addSelect('D.DISCOUNT_TYPE', 'DISCOUNT_TYPE');
		$query->addSelect('D.ACTIVE', 'ACTIVE');
		$query->addSelect('D.SORT', 'SORT');
		$query->addSelect('D.ACTIVE_FROM', 'ACTIVE_FROM');
		$query->addSelect('D.ACTIVE_TO', 'ACTIVE_TO');
		$query->addSelect('D.TIMESTAMP_X', 'TIMESTAMP_X');
		$query->addSelect('D.MODIFIED_BY', 'MODIFIED_BY');
		$query->addSelect('D.DATE_CREATE', 'DATE_CREATE');
		$query->addSelect('D.CREATED_BY', 'CREATED_BY');
		$query->addSelect('D.PRIORITY', 'PRIORITY');
		$query->addSelect('D.LAST_DISCOUNT', 'LAST_DISCOUNT');
		$query->addSelect('D.VERSION', 'VERSION');
		$query->addSelect('D.CONDITIONS_LIST', 'CONDITIONS_LIST');
		$query->addSelect('D.CONDITIONS', 'CONDITIONS');
		$query->addSelect('D.UNPACK', 'UNPACK');
		$query->addSelect('D.ACTIONS_LIST', 'ACTIONS_LIST');
		$query->addSelect('D.ACTIONS', 'ACTIONS');
		$query->addSelect('D.APPLICATION', 'APPLICATION');
		$query->addSelect('D.USE_COUPONS', 'USE_COUPONS');
		$query->addSelect('D.EXECUTE_MODULE', 'EXECUTE_MODULE');

		$discounts = array();
		$dbResult = $query->exec();

		while($row = $dbResult->fetch())
		{
			$row['ID'] = $row['ID2'];
			unset($row['ID2']);
			$discounts[$row['ID']] = $row;

			list($productElementIds, $productSectionIds) = Bitrix\Sale\Discount\Gift\RelatedDataTable::getGiftsData($discounts[$row['ID']]);
			$elementIds = array_merge($elementIds, $productElementIds);
			$sectionIds = array_merge($sectionIds, $productSectionIds);
		}

		return array(array_unique($elementIds), array_unique($sectionIds));
	}
}