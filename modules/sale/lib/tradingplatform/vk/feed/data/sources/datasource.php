<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Sources;

abstract class DataSource
{
	protected $startPos = 0;
	protected $startFeed = 0;

	/**
	 * Set start position for complex source iterator
	 *
	 * @param string $startPos - string in format iBlockNumber_RecordNumber
	 */

	abstract protected function setStartPosition($startPosition);
}