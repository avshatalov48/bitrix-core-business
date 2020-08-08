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
		if ($this->canUseTimeman())
		{
			$tmUser = new \CTimeManUser($this->userId);
			return ($tmUser->State() == 'CLOSED');
		}

		return false;
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