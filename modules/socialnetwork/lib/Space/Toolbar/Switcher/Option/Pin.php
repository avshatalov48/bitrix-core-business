<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher\Option;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\AbstractSwitcher;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\Internals;
use Exception;

class Pin extends AbstractSwitcher
{
	public const PIN_CONTEXT = '';

	private ?Internals\Pin\Pin $pin = null;

	public function enable(): Result
	{
		$result = new Result();
		if ($this->isEnabled())
		{
			return $result;
		}

		$result = (new Internals\Pin\Pin())
			->setGroupId($this->spaceId)
			->setUserId($this->userId)
			->setContext($this->code)
			->save();

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

		$result = $this->pin->delete();
		$this->invalidate();

		return $result;
	}

	public function getValue(): string
	{
		if ($this->isInitialized)
		{
			return $this->value;
		}

		try
		{
			$this->setPin();
			$this->value = is_null($this->pin) ? static::TYPE_OFF : static::TYPE_ON;
			$this->isInitialized = true;
		}
		catch (Exception)
		{
			$this->value = static::TYPE_OFF;
		}

		return $this->value;
	}

	/**
	 * @throws ArgumentException
	 */
	private function getContextFilter(): ConditionTree
	{
		$filter = Query::filter();
		return $this->code === ''
			? $filter->logic('or')->whereNull('CONTEXT')->where('CONTEXT', '')
			: $filter->where('CONTEXT', $this->code);
	}

	public function getMessage(): ?string
	{
		return $this->isEnabled() ? static::getUnpinnedMessage() : static::getPinnedMessage();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function setPin(): static
	{
		$query = WorkgroupPinTable::query()
			->setSelect([
				'ID',
				'GROUP_ID',
				'USER_ID',
			])
			->where('GROUP_ID', $this->spaceId)
			->where('USER_ID', $this->userId)
			->where($this->getContextFilter());

		$this->pin = $query->exec()->fetchObject();

		return $this;
	}

	protected function canSwitch(): bool
	{
		return $this->isSpaceExists();
	}

	public static function getDefaultCode(): string
	{
		return static::PIN_CONTEXT;
	}

	public static function getPinnedMessage(): ?string
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('SOCIALNETWORK_SPACES_SPACE_PIN');
	}

	public static function getUnpinnedMessage(): ?string
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('SOCIALNETWORK_SPACES_SPACE_UNPIN');
	}
}
