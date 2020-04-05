<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Runtime;

/**
 * Class Job
 * @package Bitrix\Sender\Runtime
 */
abstract class Job
{
	/**
	 * Actualize jobs by campaign ID.

	 * @return void
	 */
	public static function actualizeByCampaignId($campaignId)
	{
		(new SenderJob())->withCampaignId($campaignId)->actualize();
		(new ReiteratedJob())->actualize();
	}

	/**
	 * Actualize jobs by letter ID.

	 * @return void
	 */
	public static function actualizeByLetterId($letterId)
	{
		(new SenderJob())->withLetterId($letterId)->actualize();
		(new ReiteratedJob())->actualize();
	}

	/**
	 * Actualize all jobs.

	 * @return void
	 */
	public static function actualizeAll()
	{
		(new SenderJob())->actualize();
		(new ReiteratedJob())->actualize();
	}

	protected function addAgent($agentName, $interval = 60, $nextDateExec = '')
	{
		if (!$agentName || !is_string($agentName))
		{
			return;
		}

		$agent = new \CAllAgent();
		$agent->AddAgent(
			$agentName,
			"sender",
			"N",
			(int) $interval,
			null,
			"Y",
			(string) $nextDateExec
		);
	}

	protected function removeAgent($agentName)
	{
		if (!$agentName || !is_string($agentName))
		{
			return;
		}

		$agent = new \CAllAgent();
		$list = $agent->getList(
			["ID" => "DESC"],
			["MODULE_ID" => "sender", "NAME" => $agentName]
		);
		while ($row = $list->fetch())
		{
			$agent->delete($row["ID"]);
		}
	}
}