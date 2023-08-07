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
 * Class SegmentDataClearJob
 * @package Bitrix\Sender\Runtime
 */
class SegmentDataClearJob extends Job
{
	/**
	 * Add agent to build
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function addEventAgent(int $groupId)
	{
		(new SegmentDataClearJob())->addAgent(
			static::getAgentName($groupId),
			5,
			(new DateTime())->add('+5 seconds')
		);
	}

	/**
	 * Remove agent from DB
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function removeAgentFromDB(int $groupId)
	{
		(new SegmentDataBuilderJob())->removeAgent(
			static::getAgentName($groupId)
		);
	}

	/**
	 * Remove agent from DB
	 *
	 * @param int $groupStateId
	 *
	 * @return bool
	 */
	public static function existsInDB(int $groupId): bool
	{
		return (new SegmentDataBuilderJob())->agentExists(
			static::getAgentName($groupId)
		);
	}

	/**
	 * get timeline agent name
	 *
	 * @param int $groupStateId
	 *
	 * @return string
	 */
	public static function getAgentName(int $groupId)
	{
		return "\Bitrix\Sender\SegmentDataTable::deleteByGroupId($groupId);";
	}
}