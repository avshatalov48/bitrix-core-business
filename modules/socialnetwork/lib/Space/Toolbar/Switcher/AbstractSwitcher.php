<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Internals\Registry\UserRegistry;
use Bitrix\Socialnetwork\WorkgroupTable;
use Exception;

abstract class AbstractSwitcher implements SwitcherInterface
{
	use CacheTrait;

	public const TYPE_ON = 'Y';
	public const TYPE_OFF = 'N';

	protected ?int $spaceId = null;
	protected int $userId;
	protected string $value;
	protected string $code = '';
	protected bool $isInitialized = false;

	abstract public function getValue(): string;

	abstract public static function getDefaultCode(): string;

	public function __construct(int $userId, ?int $spaceId, string $code)
	{
		$this->userId = $userId;
		$this->spaceId = $spaceId;
		$this->code = $code;
	}

	public function switch(): Result
	{
		$result = new Result();
		if (!$this->canSwitch())
		{
			$result->addError(new Error('No permissions.'));
			return $result;
		}

		$result = $this->isEnabled() ? $this->disable() : $this->enable();
		$result->setData([
			'value' => $this->getValue(),
			'message' => $this->getMessage(),
		]);

		return $result;
	}

	public function isEnabled(): bool
	{
		return $this->getValue() === static::TYPE_ON;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getSpaceId(): ?int
	{
		return $this->spaceId;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function getMessage(): ?string
	{
		return null;
	}

	protected function canSwitch(): bool
	{
		return $this->isSpaceExists() && $this->isMember();
	}

	protected function isSpaceExists(): bool
	{
		if (is_null($this->spaceId))
		{
			return true;
		}

		try
		{
			return !is_null(WorkgroupTable::getByPrimary($this->spaceId)->fetchObject());
		}
		catch (Exception)
		{
			return false;
		}
	}

	protected function isMember(): bool
	{
		$userSpaces = UserRegistry::getInstance($this->userId)->getUserGroups();
		if (empty($userSpaces))
		{
			return false;
		}

		return in_array($this->spaceId, array_keys($userSpaces), true);
	}
}