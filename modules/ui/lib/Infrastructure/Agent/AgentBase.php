<?php

namespace Bitrix\UI\Infrastructure\Agent;

class AgentBase
{
	public static function run()
	{
		return static::doRun() ? get_called_class() . '::run();' : '';
	}

	public static function doRun(): bool
	{
		return false;
	}

	protected function setExecutionPeriod(int $period): void
	{
		global $pPERIOD;

		$pPERIOD = $period; // some magic to run the agent next time in $periodInSeconds seconds
	}
}
