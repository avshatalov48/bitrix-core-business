<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\Logger;
use Bitrix\Sale\TradingPlatform\Vk;

/**
 * Class for manage Feed - object, which manipulate exporter - fetching items, converted and processed them
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed
 */
class Feed
{
	/** @var  Data\Converters\DataConverter $dataConvertor */
	protected $dataConvertor;
	/** @var  Data\Sources\DataSource $sourceDataIterator */
	protected $sourceDataIterator;
	/** @var  Data\Processors\DataProcessor $dataProcessor */
	protected $dataProcessor;
	protected $feedType;
	protected $startPosition;
	
	/** @var \Bitrix\Sale\TradingPlatform\Timer|null $t iimer */
	static $timer = NULL;
	
	/**
	 * Feed constructor.
	 *
	 * @param $params
	 * @param $startPosition string - ID of first element to export
	 */
	public function __construct($params, $startPosition)
	{
		if (isset($params["TIMER"]) && $params["TIMER"] instanceof Timer)
			self::$timer = $params["TIMER"];
		
		if (!isset($params["DATA_SOURCE"]) /*|| (!($params["DATA_SOURCE"] instanceof Data\Sources\DataSource))*/)
			throw new ArgumentException("DATA_SOURCE must be instanceof DataSource!", "DATA_SOURCE");
		
		if (!isset($params["DATA_CONVERTER"]) /*|| (!($params["DATA_CONVERTER"] instanceof Data\Converters\DataConverter))*/)
			throw new ArgumentException("DATA_CONVERTER must be instanceof DataConverter!", "DATA_CONVERTER");
		
		if (!isset($params["DATA_PROCESSOR"]) || (!($params["DATA_PROCESSOR"] instanceof Data\Processors\DataProcessor)))
			throw new ArgumentException("DATA_PROCESSOR must be instanceof DataProcessor!", "DATA_PROCESSOR");
		
		$this->feedType = $params["FEED_TYPE"];
		$this->sourceDataIterator = $params["DATA_SOURCE"];
		$this->dataConvertor = $params["DATA_CONVERTER"];
		$this->dataProcessor = $params["DATA_PROCESSOR"];
		$this->startPosition = $startPosition;
	}
	
	/**
	 * Return Timer
	 * @return Timer|null
	 */
	private static function getTimer()
	{
		return self::$timer;
	}
	
	/**
	 * Consistently get data from source, convert them and processing export.
	 * Export process runs on the steps.
	 * Export controlling by timer. When timer expired - throw exception.
	 *
	 * @param null $exportId
	 */
	public function processData($exportId = NULL)
	{
//		EMPTY data for deleteAll-operations
		if (!$this->sourceDataIterator)
		{
			$this->dataProcessor->process(NULL, self::getTimer());
		}
		
		else
		{
			$vk = Vk\Vk::getInstance();
			$executionItemsLimit = $exportId ? $vk->getExecutionItemsLimit($exportId) : Vk\Vk::MAX_EXECUTION_ITEMS;
			
			$journal = new Vk\Journal($exportId, $this->feedType);
			$logger = new Vk\Logger($exportId);
			$logger->addLog('Feed start', 'Feed type ' . $this->feedType);
			
			$convertedData = array();
			$nextStepItem = NULL;
			$nextStepFlag = false;
			
			foreach ($this->sourceDataIterator as $data)
			{
				$logger->addLog('Item to convert', 'ID: ' . $data["ID"] . ' NAME: ' . $data["NAME"]);
				if ($nextStepFlag)
				{
					$nextStepItem = $data["ID"];
					break;
				}
				
				if ($currData = $this->dataConvertor->convert($data))
				{
					$convertedData += $currData;
				}
				
				if (count($convertedData) >= $executionItemsLimit)
				{
					$nextStepFlag = true;
				}
			}

//			PROCESSING
			if (count($convertedData) > 0)
			{
				$logger->addLog('Items to process', 'Count '.count($convertedData));
				$this->dataProcessor->process($convertedData, self::getTimer());
				$logger->addLog('Finish process items', 'Count '.count($convertedData));
				$journal->addItemsCount(count($convertedData));
//				for running next step
				if ($nextStepItem)
				{
					throw new TimeIsOverException("VK export next step", $nextStepItem);
				}
			}
			
			if (count($convertedData) == 0 && $this->feedType == 'PRODUCTS')
			{
				$logger->addError('EMPTY_SECTION_PRODUCTS');
			}

//			all OK - close journal
			$journal->end();
			
			$vk->log(
				Logger::LOG_LEVEL_ERROR,
				"VK_FEED__FEED_FINISH_OK",
				'FEED_' . $this->feedType,
				"VKontakte export of " . $this->feedType . " for export profile " . $exportId . " was finished successful. "
			);
		}
	}
	
}