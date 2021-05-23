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
 * Class RecipientBuilderJob
 * @package Bitrix\Sender\Runtime
 */
class RecipientBuilderJob extends Job
{
	/**
	 * Add agent to build
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function addEventAgent(int $postingId)
	{
		(new SegmentDataBuilderJob())->addAgent(
			static::getAgentName((int)$postingId),
			60,
			(new DateTime())->add('+60 seconds')
		);
	}

	/**
	 * Remove agent from DB
	 *
	 * @param int $groupStateId
	 *
	 * @return void
	 */
	public static function removeAgentFromDB(int $postingId)
	{
		(new SegmentDataBuilderJob())->removeAgent(
			static::getAgentName((int)$postingId)
		);
	}

	/**
	 * get recipient builder agent name
	 *
	 * @param int $groupStateId
	 *
	 * @return string
	 */
	public static function getAgentName(int $postingId)
	{
		return "\Bitrix\Sender\Runtime\RecipientBuilderJob::runAgent({$postingId});";
	}

	public static function runAgent(int $postingId)
	{
		$builder = new \Bitrix\Sender\Posting\Builder($postingId);

		if (!$builder->isResult())
		{
			return self::getAgentName($postingId);
		}

		return '';
	}
}