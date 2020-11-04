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
class TimeLineJob extends Job
{
	/** @var  int $letterId Letter ID. */
	protected $letterId;

	/** @var  int $campaignId Campaign ID. */
	protected $campaignId;



	/**
	 * Add agent to handle time line tasks
	 * @return void
	 */
	public static function addEventAgent($letterId)
	{
		(new TimeLineJob())->addAgent(
			static::getAgentName($letterId),
			120,
			(new DateTime())->add('+2 minutes')
		);
	}

	/**
	 * get timeline agent name
	 * @return string
	 */
	public static function getAgentName($letterId)
	{
		return "\Bitrix\Sender\Integration\Crm\EventHandler::handleTimelineEvents($letterId);";
	}
}