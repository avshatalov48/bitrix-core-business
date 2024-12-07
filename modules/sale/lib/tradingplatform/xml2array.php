<?php

namespace Bitrix\Sale\TradingPlatform;

/**
 * Class Xml2Array
 * @package Bitrix\Sale\TradingPlatform
 */
class Xml2Array
{
	/**
	 * @param string $xmlData XML.
	 * @return array Converted.
	 */
	public static function convert($xmlData)
	{
		if($xmlData == '')
			return array();

		$result = array();

		if(preg_replace('/[[:^print:]]/', '', $xmlData) == "<?xml version='1.0' encoding='UTF-8'?>")
			return array();
			
		//$xmlData = preg_replace('/[[:^print:]]/', '', $xmlData);
		libxml_use_internal_errors(true);

		try
		{
			$results = new \SimpleXMLElement($xmlData, LIBXML_NOCDATA);
		}
		catch(\Exception $e)
		{
			$logger = new Logger;
			$logger->addRecord(
				Logger::LOG_LEVEL_ERROR,
				'TRADING_PLATFORM_XML2ARRAY_ERROR',
				'convert',
				'Can\'t convert xmlData to SimpleXMLElement. Data: ('.$xmlData.'). Error: '.$e->getMessage()
			);

			return array();
		}

		if(!$results)
		{
			$logger = new Logger;
			$logger->addRecord(
				Logger::LOG_LEVEL_ERROR,
				'TRADING_PLATFORM_XML2ARRAY_ERROR',
				'convert',
				'Wrong xmlData format. Data: ('.$xmlData.').'
			);

			return array();
		}
		elseif($jsonString = json_encode($results))
		{
			$result = json_decode($jsonString, TRUE);
		}

		return $result;
	}

	/**
	 * @param array $branch.
	 * @return array
	 */
	public static function normalize(array $branch)
	{
		reset($branch);

		if(key($branch) !== 0)
			$branch = array( 0 => $branch);

		return $branch;
	}
} 