<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher\Mode;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\AbstractSwitcher;
use CSocNetLogFollow;

class SmartTracking extends AbstractSwitcher
{
	public const COMMON_LIVEFEED = '**';

	public function enable(): Result
	{
		$result = new Result();
		if ($this->isEnabled())
		{
			return $result;
		}

		if (!CSocNetLogFollow::Set($this->userId, $this->code, static::TYPE_OFF))
		{
			$result->addError(new Error('Cannot switch type'));
		}

		$this->invalidate();

		return $result;
	}

	public function disable(): Result
	{
		$result = new Result();
		if (!$this->isEnabled())
		{
			return $result;
		}

		if (!CSocNetLogFollow::Set($this->userId, $this->code, static::TYPE_ON))
		{
			$result->addError(new Error('Cannot switch type'));
		}

		$this->invalidate();

		return $result;
	}

	public function getValue(): string
	{
		if ($this->isInitialized)
		{
			return $this->value;
		}

		$this->value = CSocNetLogFollow::GetDefaultValue($this->userId);
		$this->isInitialized = true;

		return $this->value;
	}

	public function isEnabled(): bool
	{
		return $this->getValue() === static::TYPE_OFF;
	}

	public static function getDefaultCode(): string
	{
		return static::COMMON_LIVEFEED;
	}

	protected function canSwitch(): bool
	{
		return true;
	}
}