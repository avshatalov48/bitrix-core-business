<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Runtime;

use Bitrix\Main\Type\DateTime;

/**
 * Class TimeLine
 * @package Bitrix\Sender\Runtime
 */
class SegmentDataBuilderJob extends Job
{
	/**
	 * Add agent to build
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function addEventAgent(int $groupStateId)
	{
		(new SegmentDataBuilderJob())->addAgent(
			static::getAgentName((int)$groupStateId),
			10,
			(new DateTime())->add('+10 seconds')
		);
	}

	/**
	 * Remove agent from DB
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function removeAgentFromDB(int $groupStateId)
	{
		(new SegmentDataBuilderJob())->removeAgent(
			static::getAgentName((int)$groupStateId)
		);
	}

	/**
	 * Remove agent from DB
	 *
	 * @param int $groupStateId
	 *
	 * @return bool
	 */
	public static function existsInDB(int $groupStateId): bool
	{
		return (new SegmentDataBuilderJob())->agentExists(
			static::getAgentName((int)$groupStateId)
		);
	}

	/**
	 * get timeline agent name
	 *
	 * @param int $groupStateId
	 *
	 * @return string
	 */
	public static function getAgentName(int $groupStateId)
	{
		return "\Bitrix\Sender\Posting\SegmentDataBuilder::run({$groupStateId});";
	}
}