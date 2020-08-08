<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Feed\Data\Converters;

use Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\SystemException;

class Inventory extends DataConverter
{
	protected $maxProductQuantity = null;

	public function __construct($params)
	{
		if(!isset($params["SITE_ID"]) || $params["SITE_ID"] == '')
			throw new ArgumentNullException("SITE_ID");

		$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();
		$settings = $ebay->getSettings();

		if(!empty($settings[$params["SITE_ID"]]['MAX_PRODUCT_QUANTITY']))
			$this->maxProductQuantity = (float)$settings[$params["SITE_ID"]]['MAX_PRODUCT_QUANTITY'];
	}

	public function convert($data)
	{
		$result = "";

		if(isset($data["OFFERS"]) && is_array($data["OFFERS"]) && !empty($data["OFFERS"]))
		{
			foreach($data["OFFERS"] as $offer)
				$result .= $this->getItemData($offer, $data["IBLOCK_ID"]."_".$data["ID"]."_");
		}
		else
		{
			$result .= $this->getItemData($data, $data["IBLOCK_ID"]."_");
		}

		return $result;
	}

	protected function getItemData($data, $skuPrefix = "")
	{
		if(!isset($data["PRICES"]["MIN"]) || $data["PRICES"]["MIN"] <= 0)
			throw new SystemException("Can't find the price for product id: ".$data["ID"]." ! ".__METHOD__);

		if((float)$data["QUANTITY"] <= 0)
			return '';

		$quantity = (float)$data["QUANTITY"];

		if($this->maxProductQuantity !== null && $quantity > $this->maxProductQuantity)
			$quantity = $this->maxProductQuantity;

		$result = "\t<Inventory>\n";
		$result .= "\t\t<SKU>".$skuPrefix.$data["ID"]."</SKU>\n";
		$result .= "\t\t<Price>".$data["PRICES"]["MIN"]."</Price>\n";
		$result .= "\t\t<Quantity>".$quantity."</Quantity>\n";
		$result .= "\t</Inventory>\n";
	
		return $result;
	}
} 