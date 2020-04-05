<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed;

use \Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use \Bitrix\Sale\TradingPlatform\Timer;
use \Bitrix\Sale\TradingPlatform\Vk;
use \Bitrix\Sale\TradingPlatform;

/**
 * Class Manager
 * Create FEED object for export
 *
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed
 */
class Manager
{
	const DEFAULT_PROCESS_TYPE = 'ALL';
	const FIRST_PROCESS_TYPE = 'ALBUMS';
	const SECOND_PROCESS_TYPE = 'PRODUCTS';
	const DEFAULT_START_POSITION = '';
	const DEFAULT_EXEC_COUNT = 1;
	const MAX_EXEC_COUNT = 3;
	
	public static function runProcess($exportId, $processType)
	{
		$result = array();
		
		if (empty($exportId))
			throw new ArgumentNullException('exportId');
		$exportId = \EscapePHPString($exportId);
		
		$currProcess = Vk\Journal::getCurrentProcess($exportId);
		$startPosition = $currProcess ? $currProcess['START_POSITION'] : self::DEFAULT_START_POSITION;
		$execCount = $currProcess ? $currProcess['EXEC_COUNT'] : self::DEFAULT_EXEC_COUNT;
//		if we run ALL export - first we must add ALBUMS. After this we create PRODUCTS
		$currProcessType = self::prepareType($processType);
		if (!in_array($currProcessType, Vk\Vk::getExportTypes()))
			throw new ArgumentOutOfRangeException('currProcessType');
		
		$journal = new Vk\Journal($exportId, $currProcessType);

//		if first running - start journal
		if ($startPosition == self::DEFAULT_START_POSITION)
			$journal->start();
		
		$vk = Vk\Vk::getInstance();
		$vk->log(
			TradingPlatform\Logger::LOG_LEVEL_DEBUG,
			"VK_PROCESS__START",
			'FEED_' . $currProcessType,
			"VKontakte export of " . $currProcessType . " started. Export profile: " . $exportId . ", start position: " . $startPosition
		);
		
		try
		{
			$timelimit = $vk->getTimelimit($exportId);
			$feed = Manager::createFeed($currProcessType, $exportId, $timelimit, $startPosition);
			$feed->processData($exportId);
		}
		
		catch (TimeIsOverException $e)
		{
			$endPosition = $e->getEndPosition();
//			control of slow export
			if ($startPosition == $endPosition)
			{
				$execCount++;
				if ($execCount >= self::MAX_EXEC_COUNT)
					$result['TOO_MUCH_TIMES'] = Vk\Journal::getTooMuchTimeExportMessage();
			}
			else
			{
				$execCount = self::DEFAULT_EXEC_COUNT;
			}
			
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_DEBUG,
				"VK_PROCESS__TIMELIMIT",
				'FEED_' . $currProcessType,
				"VKontakte export of " . $currProcessType . " for profile " . $exportId . " takes too long and was finished at position '" . $startPosition . "'."
			);
		}
		
		catch (Vk\ExecuteException $e)
		{
//			reload to show errors
			$result['ERRORS_CRITICAL'] = true;
			$msg = $e->getFullMessage() ? $e->getFullMessage() : Loc::getMessage("SALE_VK__UNKNOWN_ERROR");
			$vk->log(TradingPlatform\Logger::LOG_LEVEL_ERROR, "VK_FEED__FEED_ERRORS", 'FEED_' . $currProcessType, $msg);
		}
		
		catch (\Exception $e)
		{
//			todo: need create normal errors desc
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_ERROR,
				"VK_PROCESS__ERRORS", 'FEED_' . $currProcessType,
				"VKontakte export of " . $currProcessType . " for profile " . $exportId . " finished with some errors. " .
				$e->getMessage()
			);
		}
		
		$journal = new Vk\Journal($exportId, $currProcessType);
//		If export not set endPosition - we catch finish element.
		if (!isset($endPosition))
		{
//			close journal for current type and write to log
			$journal->end();
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_DEBUG,
				"VK_PROCESS__FINISH",
				'FEED_' . $currProcessType,
				"VKontakte export of " . $currProcessType . " for profile " . $exportId . " was finished."
			);

//			if ALL export - after ALBUMS run PRODUCTS
			if ($currProcessType == self::FIRST_PROCESS_TYPE && $processType == self::DEFAULT_PROCESS_TYPE)
			{
				$processTypeToSave = self::SECOND_PROCESS_TYPE;
				$positionToSave = self::DEFAULT_START_POSITION;
				$execCountToSave = self::DEFAULT_EXEC_COUNT;
				
				$result['CONTINUE'] = true;
				$result['TYPE'] = $processTypeToSave;
			}

//			end of export process sovsem
			else
			{
				$processTypeToSave = false;
				$positionToSave = false;
				$execCountToSave = false;
				
				$result['CONTINUE'] = false;
			}
		}

//		CONTINUE export in current type
		else
		{
//			if ALBUM - save ALL value ($processType)
			$processTypeToSave = $processType;
			$positionToSave = $endPosition;
			$execCountToSave = $execCount;
			
			$result['CONTINUE'] = true;
			$result['TYPE'] = $processTypeToSave;
		}

//		SAVE params of process
		$journal->saveProcessParams($exportId, $processTypeToSave, $positionToSave, $execCountToSave);
		
		return $result;
	}
	
	
	/**
	 * Created params for feed. Return FEED object
	 *
	 * @param $feedType
	 * @param $exportId
	 * @param int $timeLimit
	 * @param $startPosition - position of first element to export
	 * @return Feed
	 * @throws SystemException
	 */
	private static function createFeed($feedType, $exportId, $timeLimit = 0, $startPosition = '')
	{
		$feedParams = array(
			"TIMER" => new Timer($timeLimit, false),
			"FEED_TYPE" => $feedType,
		);
		
		switch ($feedType)
		{
			case 'PRODUCTS':
				$feedParams["DATA_SOURCE"] = new Data\Sources\Product($exportId, $startPosition);
				$feedParams["DATA_CONVERTER"] = new Data\Converters\Product($exportId);
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\ProductAdd($exportId);
				break;
			
			case 'PRODUCTS_DELETE':
				$feedParams["DATA_SOURCE"] = false;
				$feedParams["DATA_CONVERTER"] = false;
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\ProductsDelete($exportId);
				break;
			
			case 'PRODUCTS_DELETE_ALL':
				$feedParams["DATA_SOURCE"] = false;
				$feedParams["DATA_CONVERTER"] = false;
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\ProductsDeleteAll($exportId);
				break;
			
			case 'ALBUMS':
				$feedParams["DATA_SOURCE"] = new Data\Sources\Section($exportId, $startPosition);
				$feedParams["DATA_CONVERTER"] = new Data\Converters\Album($exportId);
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\AlbumAdd($exportId);
				break;
			
			case 'ALBUMS_DELETE':
				$feedParams["DATA_SOURCE"] = false;
				$feedParams["DATA_CONVERTER"] = false;
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\AlbumsDelete($exportId);
				break;
			
			case 'ALBUMS_DELETE_ALL':
				$feedParams["DATA_SOURCE"] = false;
				$feedParams["DATA_CONVERTER"] = false;
				$feedParams["DATA_PROCESSOR"] = new Data\Processors\AlbumsDeleteAll($exportId);
				break;
			
			default:
				throw new SystemException("Unknown type of feed \"" . $feedType . "\". " . __METHOD__);
				break;
		}
		
		$feed = new Feed($feedParams, $startPosition);
		
		return $feed;
	}
	
	
	/**
	 * Convert export type if type ALL (return ALBUMS type)
	 * @param $type
	 * @return string of type
	 */
	public static function prepareType($type)
	{
		return ($type == self::DEFAULT_PROCESS_TYPE) ? self::FIRST_PROCESS_TYPE : $type;
	}
}