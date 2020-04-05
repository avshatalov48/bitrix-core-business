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
 * Class ReiteratedJob
 * @package Bitrix\Sender\Runtime
 */
class ReiteratedJob extends Job
{
	/**
	 * Actualize jobs.

	 * @return $this
	 */
	public function actualize()
	{
		$agentName = static::getAgentName();
		self::removeAgent($agentName);

		if (Env::isReiteratedJobCron())
		{
			return $this;
		}

		$reiterated = LetterTable::getRow([
			'select' => ['AUTO_SEND_TIME'],
			'filter' => [
				'=CAMPAIGN.ACTIVE' => 'Y',
				'=REITERATE' => 'Y',
				'=STATUS' => LetterTable::STATUS_WAIT,
			],
			'order' => ['AUTO_SEND_TIME' => 'ASC'],
			'limit' => 1
		]);
		if (!$reiterated)
		{
			return $this;
		}

		$interval = Option::get('sender', 'reiterate_interval');
		self::addAgent($agentName, $interval, $reiterated['AUTO_SEND_TIME']);

		return $this;
	}

	/**
	 * Get agent name.
	 *
	 * @return string
	 */
	public static function getAgentName()
	{
		return '\Bitrix\Sender\MailingManager::checkPeriod();';
	}
}