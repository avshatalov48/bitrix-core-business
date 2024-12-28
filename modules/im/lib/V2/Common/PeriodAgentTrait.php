<?php

namespace Bitrix\Im\V2\Common;

trait PeriodAgentTrait
{
	private static array $wasPeriodUpdated = [];

	abstract protected static function isAgentPeriodShort(int $newPeriod): bool;

	protected static function setPeriodByName(bool $fromAgent, string $agentName, callable $periodGetter): void
	{
		if (static::$wasPeriodUpdated[$agentName] ?? false)
		{
			return;
		}

		$period = $periodGetter();
		self::setPeriod($period, $fromAgent, $agentName);
		static::$wasPeriodUpdated[$agentName] = true;
	}

	protected static function setPeriod(int $period, bool $fromAgent, string $agentName): void
	{
		if ($fromAgent)
		{
			global $pPERIOD;
			$pPERIOD = $period;

			return;
		}

		if (!self::isAgentPeriodShort($period))
		{
			return;
		}

		$agent = \CAgent::GetList(
			[],
			[
				"MODULE_ID" => "im",
				"=NAME" => $agentName,
			]
		)->Fetch();

		if ($agent === false || $agent['ACTIVE'] === 'Y')
		{
			return;
		}

		\CAgent::Update(
			(int)$agent['ID'],
			['NEXT_EXEC' => \ConvertTimeStamp(time() + \CTimeZone::GetOffset() + $period, 'FULL')]
		);
	}
}
