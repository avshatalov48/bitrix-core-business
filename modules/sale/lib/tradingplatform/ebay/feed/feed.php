<?php

namespace Bitrix\Sale\TradingPlatform\Ebay\Feed;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use \Bitrix\Sale\TradingPlatform\Timer;
use \Bitrix\Sale\TradingPlatform\Logger;
use \Bitrix\Sale\TradingPlatform\Ebay\Ebay;
use \Bitrix\Sale\TradingPlatform\TimeIsOverException;

class Feed
{
	/** @var  Data\Converters\DataConverter $dataConvertor */
	protected $dataConvertor;
	/** @var  Data\Sources\DataSource $sourceDataIterator */
	protected $sourceDataIterator;
	/** @var  Data\Processors\DataProcessor $dataProcessor */
	protected $dataProcessor;

	/** @var \Bitrix\Sale\TradingPlatform\Timer|null $timer */
	protected $timer = null;
	protected $siteId = "";

	public function __construct($params)
	{
		if(isset($params["TIMER"]) && $params["DATA_SOURCE"] instanceof Timer)
			$this->timer = $params["TIMER"];

		if(!isset($params["DATA_SOURCE"]) || (!($params["DATA_SOURCE"] instanceof Data\Sources\DataSource)))
			throw new ArgumentException("DATA_SOURCE must be instanceof DataSource!", "DATA_SOURCE");

		if(!isset($params["DATA_CONVERTER"]) || (!($params["DATA_CONVERTER"] instanceof Data\Converters\DataConverter)))
			throw new ArgumentException("DATA_CONVERTER must be instanceof DataConverter!", "DATA_CONVERTER");

		if(!isset($params["DATA_PROCESSOR"]) || (!($params["DATA_PROCESSOR"] instanceof Data\Processors\DataProcessor)))
			throw new ArgumentException("DATA_PROCESSOR must be instanceof DataProcessor!", "DATA_PROCESSOR");

		if(empty($params["SITE_ID"]))
			throw new ArgumentNullException("params[\"SITE_ID\"]");

		$this->sourceDataIterator = $params["DATA_SOURCE"];
		$this->dataConvertor = $params["DATA_CONVERTER"];
		$this->dataProcessor = $params["DATA_PROCESSOR"];
		$this->site = $params["SITE_ID"];
	}

	public function processData($startPosition = "")
	{
		$this->sourceDataIterator->setStartPosition($startPosition);
		$errorsMsgs = '';

		foreach($this->sourceDataIterator as $position => $data)
		{
			try
			{
				$convertedData = $this->dataConvertor->convert($data);
				$this->dataProcessor->process($convertedData);
			}
			catch(SystemException $e)
			{
				$errorsMsgs .= $e->getMessage().'\n';
			}

			if ($this->timer !== null && !$this->timer->check())
			{
				if(!empty($errorsMsgs))
					$_SESSION['SALE_EBAY_FEED_PROCESSDATA_ERRORS'] .= $errorsMsgs;

				throw new TimeIsOverException("Timelimit is over", $position);
			}
		}

		if(!empty($_SESSION['SALE_EBAY_FEED_PROCESSDATA_ERRORS']))
		{
			$errorsMsgs = $_SESSION['SALE_EBAY_FEED_PROCESSDATA_ERRORS'].$errorsMsgs;
			unset($_SESSION['SALE_EBAY_FEED_PROCESSDATA_ERRORS']);
		}

		if(!empty($errorsMsgs))
			Ebay::log(Logger::LOG_LEVEL_ERROR, "EBAY_FEED_PROCESS_DATA_ERRORS", '', $errorsMsgs, $this->site);
	}

	public function setSourceData($data)
	{
		$this->sourceDataIterator->setData($data);
	}
}