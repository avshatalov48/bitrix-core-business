<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use CIMStatus;
use CPullOptions;
use CUser;
use CUserCounter;
use DateTimeInterface;

class UpdateState
{
	public function getUpdateStateData(CurrentUser $user, ?string $siteId = null): array
	{
		$userId = (int)$user->getId();
		$eventParams = $this->getEventParams();

		isset($siteId)
			? $mainCounters = CUserCounter::GetValues($userId, $siteId)
			: $mainCounters = CUserCounter::GetValues($userId)
		;

		$chatCounters = \Bitrix\Im\Counter::get(null, ['JSON' => 'Y']);

		return [
			'revision' => \Bitrix\Im\Revision::getWeb(),
			'mobileRevision' => \Bitrix\Im\Revision::getMobile(),
			'counters' => $mainCounters,
			'chatCounters' => $chatCounters,
			'notifyLastId' => (new \Bitrix\Im\Notify())->getLastId(),
			'desktopStatus' => $this->CheckDesktopStatusOnline($userId),
			'serverTime' => time(),
			'lastUpdate' => (new \Bitrix\Main\Type\DateTime())->format(DateTimeInterface::RFC3339),
			'eventParams' => $eventParams,
		];
	}

	private function getEventParams(): array
	{
		$event = new Event('im', 'OnUpdateState');
		$event->send();

		$result = [];

		if ($event->getResults())
		{
			foreach($event->getResults() as $eventResult)
			{
				if (\Bitrix\Main\EventResult::SUCCESS)
				{
					$result[$eventResult->getModuleId()] = $eventResult->getParameters();
				}
			}
		}

		return $result;
	}

	private function checkDesktopStatusOnline(int $userId): bool
	{
		$maxOnlineTime = 120;
		if ($this->isPullEnable() && CPullOptions::GetNginxStatus())
		{
			$maxOnlineTime = $this->GetSessionLifeTime();
		}

		$status = CIMStatus::GetStatus($userId);
		$desktopLastDateStatus = $status['DESKTOP_LAST_DATE'] ?? null;

		if (
			$desktopLastDateStatus instanceof \Bitrix\Main\Type\DateTime
			&& $desktopLastDateStatus->getTimestamp() + $maxOnlineTime + 60 > time()
		)
		{
			return true;
		}

		return false;
	}

	public function getInterval(): ?int
	{
		$updateStateInterval = $this->getSessionLifeTime() - 60;

		if ($updateStateInterval < 100)
		{
			$updateStateInterval = 100;
		}
		elseif ($updateStateInterval > 3600)
		{
			$updateStateInterval = 3600;
		}

		return $updateStateInterval;
	}

	private function getSessionLifeTime(): int
	{
		global $USER;

		$sessTimeout = CUser::GetSecondsForLimitOnline();

		if ($USER instanceof CUser)
		{
			$arPolicy = $USER->GetSecurityPolicy();

			if($arPolicy["SESSION_TIMEOUT"] > 0)
			{
				$sessTimeout = min($arPolicy["SESSION_TIMEOUT"] * 60, $sessTimeout);
			}
		}

		$sessTimeout = (int)$sessTimeout;

		if ($sessTimeout <= 120)
		{
			$sessTimeout = 100;
		}

		return $sessTimeout;
	}

	private function  isPullEnable(): bool
	{
		return \Bitrix\Main\Loader::includeModule('pull');
	}
}