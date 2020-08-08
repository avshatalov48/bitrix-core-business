<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\Vk\Vk;
use Bitrix\Sale\TradingPlatform\Timer;

abstract class DataProcessor
{
	protected static $vk;
	protected $api;
	protected $executer;
	protected $vkGroupId;
	protected $exportId;

	abstract public function process($data, Timer $timer = NULL);

	/**
	 * DataProcessor constructor.
	 * @param $exportId - int ID of export
	 */
	public function __construct($exportId)
	{
		self::$vk = Vk::getInstance();

		if (!isset($exportId) || $exportId == '')
			throw new ArgumentNullException("EXPORT_ID");
		else
			$this->exportId = $exportId;

		$this->vkGroupId = self::$vk->getGroupId($this->exportId);
		$this->api = self::$vk->getApi($this->exportId);
		$this->executer = self::$vk->getExecuter($this->exportId);
	}
}