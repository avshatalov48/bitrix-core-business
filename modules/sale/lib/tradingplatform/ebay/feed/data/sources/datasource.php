<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Feed\Data\Sources;

abstract class DataSource implements \Iterator
{
	public function setStartPosition($startPosition) { return true; }
	public function setData(array $data) { return true; }
} 