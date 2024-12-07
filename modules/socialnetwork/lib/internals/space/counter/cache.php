<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Space\List\Invitation\InvitationManager;
use Bitrix\Socialnetwork\Space\List\Provider;

class Cache
{
	private const TTL = 3600;
	private int $userId;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	/**
	 * This method is called by:
	 * $eventManager->registerEventHandler('socialnetwork', 'OnSocNetUserToGroupAdd',
	 * $eventManager->registerEventHandler('socialnetwork', 'OnSocNetUserToGroupUpdate',
	 * $eventManager->registerEventHandler('socialnetwork', 'OnSocNetUserToGroupDelete',
	 * @param int $id
	 * @param array $fields
	 * @param array $fieldsOld
	 * @return EventResult
	 */
	public static function invalidateCache(int $id, array $fields = [], array $fieldsOld = []): EventResult
	{
		// TODO: spaces stub
		return new EventResult(EventResult::SUCCESS, [], 'socialnetwork');

		$userId = (int)($fields['USER_ID'] ?? 0);
		if ($userId)
		{
			$userCache = new self($userId);
			$userCache->fillUserSpaces();
			$userCache->fillUserSpacesInvitations();
		}

		return new EventResult(EventResult::SUCCESS, [], 'socialnetwork');
	}

	public function getUserSpaceIds(): array
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($cache->initCache(self::TTL, $this->getUserSpacesKey(), $this->getUserSpacesDir()))
		{
			return $cache->getVars();
		}

		return $this->fillUserSpaces();
	}

	public function fillUserSpaces(): array
	{
		$userSpaceIds = (new Provider($this->userId))->getMySpaceIds();
		// append common space
		$userSpaceIds[] = 0;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->initCache(self::TTL, $this->getUserSpacesKey(), $this->getUserSpacesDir());
		$cache->startDataCache();
		$cache->forceRewriting(true);
		$cache->endDataCache($userSpaceIds);

		return $userSpaceIds;
	}

	public function getUserInvitationIds(): array
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($cache->initCache(self::TTL, $this->getUserSpacesInvitationKey(), $this->getUserSpacesInvitationDir()))
		{
			return $cache->getVars();
		}

		return $this->fillUserSpacesInvitations();
	}

	public function fillUserSpacesInvitations(): array
	{
		$invitations = [];

		foreach ((new InvitationManager($this->userId))->getInvitations()->toArray() as $invitation)
		{
			$invitations[] = $invitation->getSpaceId();
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->initCache(self::TTL, $this->getUserSpacesInvitationKey(), $this->getUserSpacesInvitationDir());
		$cache->startDataCache();
		$cache->endDataCache($invitations);

		return $invitations;
	}

	public function isSameLeftMenuTotal(string $code, int $value): bool
	{
		global $CACHE_MANAGER;

		$cache = $CACHE_MANAGER->Get('user_counter' . $this->userId);
		if (!$cache)
		{
			return false;
		}

		foreach ($cache as $item)
		{
			if (
				$item['CODE'] === $code
				&& $item['SITE_ID'] === '**'
				&& (int)$item['CNT'] === $value
			)
			{
				return true;
			}
		}

		return false;
	}

	private function getUserSpacesKey(): string
	{
		return 'user_spaces' . $this->userId;
	}

	private function getUserSpacesDir(): string
	{
		$key = $this->getUserSpacesKey();

		return '/spaces/' . substr(md5($key),2,2) . '/' . $key . '/';
	}

	private function getUserSpacesInvitationKey(): string
	{
		return 'user_spaces_invitations' . $this->userId;
	}

	private function getUserSpacesInvitationDir(): string
	{
		$key = $this->getUserSpacesInvitationKey();

		return '/spaces/' . substr(md5($key),2,2) . '/' . $key . '/';
	}
}