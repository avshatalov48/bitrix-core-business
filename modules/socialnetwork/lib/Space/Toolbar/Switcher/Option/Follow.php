<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher\Option;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\AbstractSwitcher;
use CSocNetSubscription;

class Follow extends AbstractSwitcher
{
	public const SOCIALNETWORK_GROUP = 'SG';

	public function enable(): Result
	{
		$result = new Result();
		if ($this->isEnabled())
		{
			return $result;
		}

		if (!CSocNetSubscription::Set($this->userId, $this->getCode(), static::TYPE_ON))
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

		if (!CSocNetSubscription::Set($this->getUserId(), $this->getCode(), static::TYPE_OFF))
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

		$result = \CSocNetSubscription::GetList(
			[],
			[
				'USER_ID' => $this->getUserId(),
				'CODE' => $this->getCode(),
			]
		);

		if ($result === false)
		{
			$this->value = static::TYPE_OFF;
			return $this->value;
		}

		$result = $result->Fetch();
		$this->value = empty($result) ? static::TYPE_OFF : static::TYPE_ON;
		$this->isInitialized = true;

		return $this->value;
	}

	public function getMessage(): ?string
	{
		return $this->isEnabled() ? static::getUnfollowedMessage() : static::getFollowedMessage();
	}

	public function getCode(): string
	{
		return $this->code . $this->spaceId;
	}

	public static function getDefaultCode(): string
	{
		return static::SOCIALNETWORK_GROUP;
	}

	public static function getFollowedMessage(): ?string
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('SOCIALNETWORK_SPACES_SPACE_FOLLOW');
	}

	public static function getUnfollowedMessage(): ?string
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('SOCIALNETWORK_SPACES_SPACE_UNFOLLOW');
	}
}