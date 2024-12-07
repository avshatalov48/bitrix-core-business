<?php

namespace Bitrix\Calendar\Relation\Builder\Owner;

use Bitrix\Calendar\Relation\Item\Owner;

class OwnerBuilder
{
	public function __construct(private int $userId)
	{}

	public function build(): Owner
	{
		$owner = new Owner($this->userId);
		$user = \CCalendar::GetUser($this->userId);
		if (!$user)
		{
			return $owner;
		}

		$owner
			->setName(\CUser::FormatName(\CSite::GetNameFormat(), $user))
			->setAvatar($this->prepareAvatar($user))
			->setLink('/company/personal/user/'.$this->userId.'/')
		;

		return $owner;
	}

	private function prepareAvatar(array $user): ?string
	{
		$avatarSrc = \CCalendar::GetUserAvatarSrc($user);

		if ($avatarSrc === '/bitrix/images/1.gif')
		{
			$avatarSrc = null;
		}

		return $avatarSrc;
	}
}
