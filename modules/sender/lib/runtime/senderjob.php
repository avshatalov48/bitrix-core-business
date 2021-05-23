<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Runtime;

use Bitrix\Main\Config\Option;
use Bitrix\Sender\Internals\Model\LetterTable;

/**
 * Class SenderJob
 * @package Bitrix\Sender\Runtime
 */
class SenderJob extends Job
{
	/** @var  int $letterId Letter ID. */
	protected $letterId;

	/** @var  int $campaignId Campaign ID. */
	protected $campaignId;

	/**
	 * Set campaign ID.
	 *
	 * @param int $campaignId Campaign ID.
	 * @return $this
	 */
	public function withCampaignId($campaignId)
	{
		$this->campaignId = $campaignId;
		return $this;
	}

	/**
	 * Set letter ID.
	 *
	 * @param int $letterId Letter ID.
	 * @return $this
	 */
	public function withLetterId($letterId)
	{
		$this->letterId = $letterId;
		return $this;
	}

	/**
	 * Actualize jobs.

	 * @return $this
	 */
	public function actualize()
	{
		$filter = [];
		if ($this->campaignId)
		{
			$filter['=CAMPAIGN_ID'] = $this->campaignId;
		}
		if ($this->letterId)
		{
			$filter['=ID'] = $this->letterId;
		}

		$list = LetterTable::getList(array(
			'select' => ['ID', 'POSTING_ID', 'STATUS', 'AUTO_SEND_TIME', 'CAMPAIGN_ACTIVE' => 'CAMPAIGN.ACTIVE'],
			'filter' => $filter
		));

		$data = [];

		foreach ($list as $row)
		{
			$data[] = $row;
		}

		foreach ($data as $row)
		{
			$agentName = static::getAgentName($row['ID']);
			if (!$agentName)
			{
				continue;
			}

			self::removeAgent($agentName);

			if (Env::isSenderJobCron())
			{
				continue;
			}

			if (empty($row['AUTO_SEND_TIME']))
			{
				continue;
			}

			if ($row['CAMPAIGN_ACTIVE'] !== 'Y')
			{
				continue;
			}

			$allowedStatuses = [LetterTable::STATUS_SEND, LetterTable::STATUS_PLAN];
			if (!in_array($row['STATUS'], $allowedStatuses))
			{
				continue;
			}

			$interval = Option::get('sender', 'auto_agent_interval');
			self::addAgent($agentName, $interval, $row['AUTO_SEND_TIME']);
		}

		return $this;
	}

	/**
	 * Get agent name.
	 *
	 * @param int $letterId Letter ID.
	 * @param bool|int $threadId
	 *
	 * @return string
	 */
	public static function getAgentName($letterId, $threadId = false)
	{
		$letterId = (int) $letterId;
		if (!$letterId)
		{
			return '';
		}

		return '\Bitrix\Sender\MailingManager::chainSend('. $letterId. ');';
	}
}