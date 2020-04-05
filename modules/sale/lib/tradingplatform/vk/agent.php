<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\TradingPlatform\Vk\Feed\Manager;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;

Loc::loadMessages(__FILE__);

/**
 * Class Agent
 * Working with agent for VK-export: running, update, delete. Provide multistep processing.
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class Agent
{
	/**
	 * Starting agents from start position. Need for multisteps.
	 *
	 * @param $feedType - type of export. May be ALL - then after ending ALBUMS export will be created PRODUCTS agent
	 * @param $exportId
	 * @param string $startPosition - in first run must be ""
	 * @param bool $once - if true - agent will be deleted after ending process
	 * @param int $execNumber - number of repeated running without changing position. Need for alert of slow agent.
	 * @return string
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function start($feedType, $exportId, $startPosition = "", $once = false, $execNumber = 1)
	{
		if (empty($exportId))
			throw new ArgumentNullException('exportId');
		$exportId = \EscapePHPString($exportId);
		
		if (!in_array($feedType, Vk::getExportTypes()))
			throw new ArgumentOutOfRangeException('feedType');
		
		$result = "";
		$vk = Vk::getInstance();
		$vk->log(
			TradingPlatform\Logger::LOG_LEVEL_DEBUG,
			"VK_AGENT__FEED_STARTED",
			'FEED_' . $feedType,
			"VKontakte export of " . $feedType . " started from agent. Export profile: " . $exportId . ", start position: " . $startPosition
		);
		
		try
		{
//			if we run ALL export - first we must add ALBUMS. After this we create PRODUCTS agent
			if ($feedType == 'ALL')
				$feedTypeCurr = 'ALBUMS';
			else
				$feedTypeCurr = $feedType;
			
			$timelimit = $vk->getTimelimit($exportId);
			$vkFeed = Manager::createFeed($feedTypeCurr, $exportId, $timelimit, $startPosition);
			$vkFeed->processData($exportId);
		}
		
		catch (TimeIsOverException $e)
		{
			$endPosition = $e->getEndPosition();
//			control of slow export
			if ($startPosition == $endPosition)
			{
				$execNumber++;
				if ($execNumber >= 3)
				{
					\CAdminNotify::Add(array(
						'MESSAGE' => Loc::getMessage("SALE_VK__TOO_MUCH_TIMES_NOTIFY"),
						'MODULE_ID' => 'sale',
						'TAG' => 'vk_agent_much_times_notify',
						'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
					));
				}
			}
			else
			{
				$execNumber = 1;
			}
			$result = self::createAgentNameForAdd($feedType, $exportId, $endPosition, $once, $execNumber);
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_DEBUG,
				"VK_AGENT__FEED_TIMELIMIT",
				'FEED_' . $feedType,
				"VKontakte export of " . $feedType . " for profile " . $exportId . " takes too long and was finished at position '" . $startPosition . "'. To continue will be create agent."
			);
		}
		
		catch (ExecuteException $e)
		{
			$msg = $e->getFullMessage() ? $e->getFullMessage() : Loc::getMessage("SALE_VK__UNKNOWN_ERROR");
			$vk->log(TradingPlatform\Logger::LOG_LEVEL_ERROR, "VK_FEED__FEED_ERRORS", 'FEED_' . $feedType, $msg);
		}
		
		catch (\Exception $e)
		{
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_ERROR,
				"VK_AGENT__FEED_ERRORS", 'FEED_' . $feedType,
				"VKontakte export of " . $feedType . " for profile " . $exportId . " finished with some errors. " .
				$e->getMessage()
			);
		}

//		if ALL export - we catch end of one cycle and run next type (ALBUMS => PRODUCTS)
//		if ALBUM part end normal (not with timer) - run agent to PRODUCTS export
		if ($feedType == 'ALL' && !isset($endPosition))
		{
//			for ALL export we open PRODUCTS journal after ALBUMS export end. Need for correctly show statistic to user
			$journal = new Journal($exportId, 'PRODUCTS');
			$journal->start();

			$result = self::createAgentNameForAdd('PRODUCTS', $exportId, '', $once);
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_DEBUG,
				"VK_FEED__FEED_ALBUM_PART_FINISH",
				'FEED_' . $feedType,
				"VKontakte export of ALBUMS for profile " . $exportId . " was finished successfull. PRODUCTS export will be continue on agent."
			);
		}

//		all OK - create new agent with null start position
		elseif (strlen($result) <= 0 && !$once)
		{
			$result = self::createAgentNameForAdd($feedType, $exportId, "", $once);
		}

		$vk->log(
			TradingPlatform\Logger::LOG_LEVEL_DEBUG,
			"VK_AGENT__FEED_FINISH",
			'FEED_' . $feedType,
			"VKontakte export of " . $feedType . " for profile " . $exportId . " was finished."
		);
		return $result;
	}
	
	
	/**
	 * @param $feedType - type of export. May be ALL - then after ending ALBUMS export will be created PRODUCTS agent
	 * @param $exportId
	 * @param string $startPosition - in first run must be ""
	 * @param $interval - time of repeating
	 * @param bool $once - if true - agent will be deleted after ending process
	 * @return bool|int
	 * @throws ArgumentNullException
	 *
	 * Add new agent for export products, albums or all
	 */
	public static function add($feedType, $exportId, $startPosition = "", $interval, $once = false)
	{
		if ($interval <= 0)
			return 0;
		
		if (empty($exportId))
			throw new ArgumentNullException('exportId');
		
		$exportId = \EscapePHPString($exportId);
		
		$timeToStart = ConvertTimeStamp(strtotime(date('Y-m-d H:i:s', time() + $interval)), 'FULL');
		
		$result = \CAgent::AddAgent(
			self::createAgentNameForAdd($feedType, $exportId, $startPosition, $once),
			'sale',
			"N",
			$interval,
			$timeToStart,
			"Y",
			$timeToStart
		);
		
		if ($result)
		{
			$vk = Vk::getInstance();
			$vk->log(
				TradingPlatform\Logger::LOG_LEVEL_DEBUG,
				"VK_AGENT__NEW_AGENT",
				'FEED_' . $feedType,
				"New agent was crated for VKontakte export " . $feedType . ". Agent ID: " . $result . "."
			);
		}
		
		return $result;
	}
	
	/**
	 * @param $feedType - type of export. May be ALL - then after ending ALBUMS export will be created PRODUCTS agent
	 * @param $exportId
	 * @param $startPosition
	 * @param bool $once - if true - agent will be deleted after ending process
	 * @param int $execNumber
	 * @return string
	 *
	 * Create name for creating new agent
	 */
	protected static function createAgentNameForAdd($feedType, $exportId, $startPosition, $once = false, $execNumber = 1)
	{
		return 'Bitrix\Sale\TradingPlatform\Vk\Agent::start("' . $feedType . '","' . $exportId . '","' . $startPosition . '",' . ($once ? 'true' : 'false') . ',' . $execNumber . ');';
	}
	

	/**
	 * Update params for existing agent. If agent if not exist - create new
	 *
	 * @param $exportId
	 * @param $feedType - type of export. May be ALL - then after ending ALBUMS export will be created PRODUCTS agent
	 * @param $interval - time of repeating
	 * @param bool $once - if true - agent will be deleted after ending process
	 * @return bool|int
	 * @throws ArgumentNullException
	 */
	public static function update($exportId, $feedType, $interval, $once = false)
	{
		$result = false;
		$interval = intval($interval);

//		check existing AGENTS
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createAgentNameForAdd($feedType, $exportId, "", $once),
			)
		);


//		current agent existing - UPDATE
		if ($agent = $dbRes->Fetch())
		{
			if ($interval <= 0)
			{
				self::deleteAgent($agent["ID"]);
			}

			else
			{
				\CAgent::Update(
					$agent["ID"],
					array('AGENT_INTERVAL' => $interval,)
				);

				$result = $agent["ID"];
			}
		}

//		agent not exist - CREATE
		else
		{
			if ($interval > 0)
				$result = self::add($feedType, $exportId, "", $interval, $once);
		}

		return $result;
	}
	

	/**
	 * Remove all agents, saving in VK settings
	 */
	public static function deleteAll()
	{
		$vk = Vk::getInstance();
		$settings = $vk->getSettings();

		foreach ($settings as $exportSettings)
		{
			self::deleteAgent($exportSettings["AGENT"]["ID"]);
		}
	}


	/**
	 * Delete agent by ID
	 */
	public static function deleteAgent($agentId)
	{
		return \CAgent::Delete($agentId);
	}
	
	
	/**
	 * Find vk-export agents which running once.
	 * Return array of IDs.
	 *
	 * @param $feedType - type of export. May be ALL - then after ending ALBUMS export will be created PRODUCTS agent
	 * @param $exportId
	 * @return array - IDs of existing agents
	 */
	public static function getExistingOnceAgent($feedType, $exportId)
	{
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createOnceAgentName($feedType, $exportId),
			)
		);
		
		$agents = array();
		while ($agent = $dbRes->Fetch())
			$agents[$agent["ID"]] = $agent["ID"];

		return $agents;
	}


	/**
	 * Find vk-export agents which was running but not finished (have not null start position)
	 * Return array of IDs.
	 *
	 * @param $feedType
	 * @param $exportId
	 * @return array
	 */
	public static function getRunningPereodicalAgents($feedType, $exportId)
	{
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createRunningPereodicalAgentName($feedType, $exportId),
			)
		);

		$agents = array();
		while ($agent = $dbRes->Fetch())
			$agents[$agent["ID"]] = $agent["ID"];

		return $agents;
	}

	/**
	 * Create name for vk-export agents which running onced.
	 * @param $feedType
	 * @param $exportId
	 * @return string
	 */
	protected static function createOnceAgentName($feedType, $exportId)
	{
		return 'Bitrix\Sale\TradingPlatform\Vk\Agent::start("' . $feedType . '","' . $exportId . '",%true%';
	}


	/**
	 * Create name for vk-export agents which was running but not finished (have not null start position)
	 *
	 * @param $feedType
	 * @param $exportId
	 * @return string
	 */
	protected static function createRunningPereodicalAgentName($feedType, $exportId)
	{
		return 'Bitrix\Sale\TradingPlatform\Vk\Agent::start("' . $feedType . '","' . $exportId . '","_%"%';
	}
}