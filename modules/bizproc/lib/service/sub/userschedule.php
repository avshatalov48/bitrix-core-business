<?php
namespace Bitrix\Bizproc\Service\Sub;

use Bitrix\Main;

/**
 * Class UserSchedule
 * @package Bitrix\Bizproc\Service\Sub
 * @internal
 */
class UserSchedule
{
	protected $userId;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function isAbsent(): bool
	{
		if ($this->canUseIntranet())
		{
			return \CIntranetUtils::isUserAbsent($this->userId);
		}

		return false;
	}

	public function isWorkDayClosed(): bool
	{
		return ($this->getWorkDayStatus() === 'CLOSED');
	}

	public function getWorkDayStatus(): string
	{
		if ($this->canUseTimeman())
		{
			$tmUser = new \CTimeManUser($this->userId);

			//speed up!
			if (method_exists($tmUser, 'getCurrentRecordStatus'))
			{
				return $tmUser->getCurrentRecordStatus();
			}

			return $tmUser->state();
		}

		return 'UNDEFINED';
	}

	private function canUseIntranet()
	{
		return Main\Loader::includeModule('intranet');
	}

	private function canUseTimeman()
	{
		return Main\Loader::includeModule('timeman');
	}
}