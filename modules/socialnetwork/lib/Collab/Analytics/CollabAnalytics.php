<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Analytics;

use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Socialnetwork\Collab\User\User;
use Bitrix\Socialnetwork\Helper\Analytics;

class CollabAnalytics extends Analytics
{
	public const EVENT_SETTINGS_CHANGED = 'edit_permissions';
	public const EVENT_USER_ADDED = 'add_user';
	public const EVENT_USER_DELETED = 'delete_user';

	public const TOOL_IM = 'im';

	public const CATEGORY_COLLAB = 'collab';

	public const SECTION_EDITOR = 'editor';

	public const TYPE_OWNER = 'owner';
	public const TYPE_MODERATOR = 'moderator';

	public function onOwnerChanged(int $userId, int $collabId): void
	{
		$this->onSettingsChanged($userId, $collabId, static::TYPE_OWNER);
	}

	public function onModeratorChanged(int $userId, int $collabId): void
	{
		$this->onSettingsChanged($userId, $collabId, static::TYPE_MODERATOR);
	}

	public function onSettingsChanged(int $userId, int $collabId, string $optionName): void
	{
		$analyticsEvent = new AnalyticsEvent(
			static::EVENT_SETTINGS_CHANGED,
			static::TOOL_IM,
			static::CATEGORY_COLLAB,
		);

		$parameters = [
			'p2' => $this->getUserTypeParameter($userId),
			'p4' => $this->getCollabParameter($collabId),
		];

		$this->sendAnalytics(
			analyticsEvent: $analyticsEvent,
			type: $optionName,
			section: static::SECTION_EDITOR,
			params: $parameters,
		);
	}

	public function onMemberAdded(int $userId, int $collabId): void
	{
		$analyticsEvent = new AnalyticsEvent(
			static::EVENT_USER_ADDED,
			static::TOOL_IM,
			static::CATEGORY_COLLAB,
		);

		$this->sendAnalytics(
			analyticsEvent: $analyticsEvent,
			section: static::SECTION_EDITOR,
			params: $this->getParameters($userId, $collabId),
		);
	}

	public function onMemberDeleted(int $userId, int $collabId): void
	{
		$analyticsEvent = new AnalyticsEvent(
			static::EVENT_USER_DELETED,
			static::TOOL_IM,
			static::CATEGORY_COLLAB,
		);

		$this->sendAnalytics(
			analyticsEvent: $analyticsEvent,
			section: static::SECTION_EDITOR,
			params: $this->getParameters($userId, $collabId),
		);
	}

	public function getUserTypeParameter(int $userId): string
	{
		if ($userId <= 0)
		{
			return '';
		}

		$user = new User($userId);

		if ($user->isIntranet())
		{
			return 'user_intranet';
		}

		if ($user->isCollaber())
		{
			return 'user_collaber';
		}

		return 'user_extranet';
	}

	public function getCollabParameter(int $collabId): string
	{
		return 'collabId_' . $collabId;
	}

	private function getParameters(int $userId, int $collabId): array
	{
		return [
			'p2' => $this->getUserTypeParameter($userId),
			'p4' => $this->getCollabParameter($collabId),
		];
	}
}