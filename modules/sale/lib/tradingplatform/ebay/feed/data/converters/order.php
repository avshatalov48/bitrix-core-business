<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Feed\Data\Converters;

use Bitrix\Sale\TradingPlatform\Xml2Array;

class Order extends DataConverter
{
	public function convert($data)
	{
		$result = Xml2Array::convert($data);
		return isset($result["Order"]) && !empty($result["Order"]) ? Xml2Array::normalize($result["Order"]) : array();
	}
}