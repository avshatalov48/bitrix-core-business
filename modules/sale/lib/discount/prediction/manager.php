<?php

namespace Bitrix\Sale\Discount\Prediction;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Discount;
use Bitrix\Sale\Order;
use Bitrix\Sale\PriceMaths;
use Sale\Handlers\DiscountPreset\ConnectedProduct;
use Sale\Handlers\DiscountPreset\OrderAmount;

final class Manager
{
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  Manager */
	private static $instance;
	/** @var  int */
	private $userId;

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @param int $userId
	 * @return $this
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;

		return $this;
	}

	private function __clone()
	{}

	/**
	 * Returns Singleton of Manager
	 * @return Manager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns first prediction which has user by current basket.
	 * @param Basket $basket Basket.
	 * @param array  $product Target product.
	 * @return mixed|null
	 * @throws SystemException
	 */
	public function getFirstPredictionTextByProduct(Basket $basket, array $product)
	{
		$this->errorCollection->clear();

		if(!$this->isValidProduct($product))
		{
			return null;
		}

		$basketCopied = $basket->copy();
		$discounts = $this->getDiscounts($basketCopied);
		Discount\Preset\Manager::getInstance()->registerAutoLoader();
		$predictionDiscount = $this->findFirstPredictionDiscount($discounts, OrderAmount::className());

		if($predictionDiscount)
		{
			$text = $this->buildTextByPredictionDiscount($basketCopied, $predictionDiscount);
			if($text)
			{
				return $text;
			}
		}

		return $this->tryToFindPredictionConnectedProducts($basket->copy(), $product);
	}

	private function tryToFindPredictionConnectedProducts(Basket $basket, array $product)
	{
		if(!$this->checkProductInBasket($product, $basket))
		{
			$this->addProductToBasket($basket, $product);
		}

		$discounts = $this->getDiscounts($basket);
		$predictionDiscount = $this->findFirstPredictionDiscount($discounts, ConnectedProduct::className());

		$manager = Discount\Preset\Manager::getInstance();
		$preset = $manager->getPresetById($predictionDiscount['PRESET_ID']);
		if(!$preset instanceof ConnectedProduct)
		{
			return null;
		}

		$currentProductIds = $this->extendProductIds(array($product['ID']));
		$currentSectionIds = $this->getSectionIdsByProduct($product);

		$state = $preset->generateState($predictionDiscount);
		list($typeConditionProduct, $dataCondition) = $preset->getDescribedDataProductCondition($state);
		list($typeActionProduct, $dataAction) = $preset->getDescribedDataProductAction($state);

		$isAct = $isCond = false;

		$actProductIds = $condProductIds = array();
		if($typeConditionProduct === $preset::TYPE_PRODUCT)
		{
			$condProductIds = $this->extendProductIds($dataCondition);
			if(array_intersect($condProductIds, $currentProductIds))
			{
				$isCond = true;
			}
		}
		elseif($typeConditionProduct === $preset::TYPE_SECTION)
		{
			if(array_intersect($dataCondition, $currentSectionIds))
			{
				$isCond = true;
			}
		}

		if($typeActionProduct === $preset::TYPE_PRODUCT)
		{
			$actProductIds = $this->extendProductIds($dataAction);
			if(array_intersect($actProductIds, $currentProductIds))
			{
				$isAct = true;
			}
		}
		elseif($typeActionProduct === $preset::TYPE_SECTION)
		{
			if(array_intersect($dataAction, $currentSectionIds))
			{
				$isAct = true;
			}
		}

		if(!$isAct && !$isCond)
		{
			return null;
		}

		$predictionText = $preset->getPredictionText(
			$state,
			$isAct? $preset::PREDICTION_TEXT_TYPE_ACTION : $preset::PREDICTION_TEXT_TYPE_CONDITION
		);

		$currencyFormat = '# ' . $predictionDiscount['CURRENCY'];
		if(Loader::includeModule('currency'))
		{
			$currencyFormat = \CCurrencyLang::getCurrencyFormat($predictionDiscount['CURRENCY']);
			$currencyFormat = $currencyFormat['FORMAT_STRING'];
		}

		$discountValue = str_replace('#', $state['discount_value'], $currencyFormat);
		if($state['discount_type'] === 'Perc')
		{
			$discountValue = $state['discount_value'] . ' %';
		}

		$placeholders = array(
			'#DISCOUNT_VALUE#' => $discountValue,
		);

		if($isCond)
		{
			if($typeActionProduct === $preset::TYPE_SECTION)
			{
				$placeholders['#NAME#'] = $this->getSectionName($dataAction);
				$placeholders['#LINK#'] = $this->getSectionUrl($dataAction);
			}
			if($typeActionProduct === $preset::TYPE_PRODUCT)
			{
				$placeholders['#NAME#'] = $this->getProductName($dataAction);
				$placeholders['#LINK#'] = $this->getProductUrl($dataAction);
			}
		}
		elseif($isAct)
		{
			if($typeConditionProduct === $preset::TYPE_SECTION)
			{
				$placeholders['#NAME#'] = $this->getSectionName($dataCondition);
				$placeholders['#LINK#'] = $this->getSectionUrl($dataCondition);
			}
			if($typeConditionProduct === $preset::TYPE_PRODUCT)
			{
				$placeholders['#NAME#'] = $this->getProductName($dataCondition);
				$placeholders['#LINK#'] = $this->getProductUrl($dataCondition);
			}
		}

		if (empty($placeholders['#NAME#']) || empty($placeholders['#LINK#']))
		{
			return null;
		}

		return str_replace(
			array_keys($placeholders),
			array_values($placeholders),
			$predictionText
		);
	}

	private function getProductUrl($productId)
	{
		if(is_array($productId))
		{
			$productId = array_pop($productId);
		}

		$arElement = \CIBlockElement::GetList(array(), array("=ID" => $productId), false, array("nTopCount" => 1))->fetch();
		$url = \CIBlock::ReplaceDetailUrl($arElement['DETAIL_PAGE_URL'], $arElement, false, "E");
		
		return $url;
	}

	private function getSectionUrl($sectionId)
	{
		if(is_array($sectionId))
		{
			$sectionId = array_pop($sectionId);
		}

		$arSection = \CIBlockSection::GetList(array(), array("=ID" => $sectionId), false, array("nTopCount" => 1))->fetch();
		$url = \CIBlock::ReplaceDetailUrl($arSection['SECTION_PAGE_URL'], $arSection, false, "S");

		return $url;
	}

	private function getProductName($productId)
	{
		if(is_array($productId))
		{
			$productId = array_pop($productId);
		}

		$product = ElementTable::getList(array(
			'select' => array('NAME'),
			'filter' => array('ID' => $productId)
		))->fetch();

		return $product['NAME'];
	}

	private function getSectionName($sectionId)
	{
		if(is_array($sectionId))
		{
			$sectionId = array_pop($sectionId);
		}

		$section = SectionTable::getList(array(
			'select' => array('NAME'),
			'filter' => array('ID' => $sectionId)
		))->fetch();

		return $section['NAME'];
	}

	private function getSectionIdsByProduct(array $product)
	{
		$sectionIds = array();
		foreach($this->extendProductIds(array($product['ID'])) as $productId)
		{
			$sectionIds = array_merge($sectionIds, $this->getSectionIdsByElement($productId));
		}

		return $this->extendSectionIds(array_unique($sectionIds));
	}

	private function getSectionIdsByElement($elementId)
	{
		$sectionIds = array();
		$query = \CIBlockElement::getElementGroups($elementId, true, array(
			"ID",
			"IBLOCK_SECTION_ID",
			"IBLOCK_ELEMENT_ID",
		));
		while($section = $query->fetch())
		{
			$sectionIds[] = $section['ID'];
		}

		return $sectionIds;
	}

	private function extendSectionIds(array $sectionIds)
	{
		if(empty($sectionIds))
		{
			return array();
		}

		$extendedSectionIds = array();

		$query = \CIBlockSection::GetList(array(), array(
			'ID' => $sectionIds
		), false, array('IBLOCK_ID', 'ID'));

		while($row = $query->fetch())
		{
			$rsParents = \CIBlockSection::getNavChain($row['IBLOCK_ID'], $row['ID'], array('ID'));
			while($arParent = $rsParents->fetch())
			{
				$extendedSectionIds[] = $arParent['ID'];
			}
		}


		return $extendedSectionIds;
	}

	/**
	 * Extends to sku ids.
	 * @param array $productIds
	 * @return array
	 */
	private function extendProductIds(array $productIds)
	{
		//todo catalog!!!
		$products = \CCatalogSku::getProductList($productIds);
		if (empty($products))
		{
			return $productIds;
		}

		foreach($products as $product)
		{
			$productIds[] = $product['ID'];
		}

		return $productIds;
	}

	private function checkProductInBasket(array $product, Basket $basket)
	{
		foreach($basket as $item)
		{
			/** @var BasketItem $item */
			if(
					$item->getProductId() == $product['ID'] &&
					$item->getField('MODULE') == $product['MODULE']
			)
			{
				return true;
			}
		}

		return false;
	}

	private function buildTextByPredictionDiscount(Basket $basket, array $discount)
	{
		$manager = Discount\Preset\Manager::getInstance();
		$preset = $manager->getPresetById($discount['PRESET_ID']);
		$state = $preset->generateState($discount);

		$currencyFormat = '# ' . $discount['CURRENCY'];
		if(Loader::includeModule('currency'))
		{
			$currencyFormat = \CCurrencyLang::getCurrencyFormat($discount['CURRENCY']);
			$currencyFormat = $currencyFormat['FORMAT_STRING'];
		}

		$placeholders = array();
		if($preset instanceof OrderAmount)
		{
			$discountValue = str_replace('#', $state['discount_value'], $currencyFormat);
			if($state['discount_type'] === 'Perc')
			{
				$discountValue = $state['discount_value'] . ' %';
			}

			$shortage = $state['discount_order_amount'] - $basket->getPrice();
			if($shortage <= 0)
			{
				return '';
			}

			$shortage = PriceMaths::roundByFormatCurrency($shortage, $discount['CURRENCY']);
			
			$placeholders = array(
				'#SHORTAGE#' => str_replace('#', $shortage, $currencyFormat),
				'#DISCOUNT_VALUE#' => $discountValue,
			);
		}

		return str_replace(
			array_keys($placeholders), 
			array_values($placeholders), 
			(string)$discount['PREDICTION_TEXT']
		);
	}

	private function findFirstPredictionDiscount(array $discounts, $typePrediction)
	{
		if(empty($discounts))
		{
			return null;
		}

		$manager = Discount\Preset\Manager::getInstance();
		foreach($discounts as $discount)
		{
			if(empty($discount['PRESET_ID']) || empty($discount['PREDICTION_TEXT']))
			{
				continue;
			}

			$preset = $manager->getPresetById($discount['PRESET_ID']);
			if($preset instanceof $typePrediction)
			{
				return $discount;
			}
		}

		return null;
	}

	private function addProductToBasket(Basket $basket, array $product)
	{
		$basketItem = $basket->createItem($product['MODULE'], $product['ID']);
		unset($product['MODULE'], $product['ID']);

		$basketItem->setFields($product);
	}

	private function getDiscounts(Basket $basket)
	{
		if($basket->getOrder())
		{
			throw new SystemException('Could not get discounts by basket which has order.');
		}

		/** @var Order $order */
		$order = Order::create($basket->getSiteId(), $this->userId);
		$discount = $order->getDiscount();
		$discount->enableCheckingPrediction();
		if(!$order->setBasket($basket)->isSuccess())
		{
			return array();
		}
		$calcResults = $discount->getApplyResult(true);

		return $calcResults['FULL_DISCOUNT_LIST']?: array();
	}

	private function isValidProduct(array $product)
	{
		if(empty($product['ID']))
		{
			$this->errorCollection[] = new Error('Product array has to have ID');
		}
		if(empty($product['MODULE']))
		{
			$this->errorCollection[] = new Error('Product array has to have MODULE');
		}
		if(empty($product['PRODUCT_PROVIDER_CLASS']))
		{
			$this->errorCollection[] = new Error('Product array has to have PRODUCT_PROVIDER_CLASS');
		}
		if(empty($product['QUANTITY']))
		{
			$this->errorCollection[] = new Error('Product array has to have QUANTITY');
		}
		if($this->errorCollection->count())
		{
			return false;
		}

		return true;
	}
}