<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use CIMContactList;

class OpenChat extends GroupChat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN;
	}

	protected function checkAccessInternal(int $userId): Result
	{
		if (User::getInstance($userId)->isExtranet())
		{
			return parent::checkAccessInternal($userId);
		}

		return new Result();
	}

	public function filterUsersToMention(array $userIds): array
	{
		$result = [];
		$relations = $this->getRelationsByUserIds($userIds);

		foreach ($userIds as $userId)
		{
			$relation = $relations->getByUserId($userId, $this->getChatId());
			if (
				($relation === null
				|| $relation->getNotifyBlock())
				&& \CIMSettings::GetNotifyAccess($userId, 'im', 'mention', \CIMSettings::CLIENT_SITE)
			)
			{
				$result[$userId] = $userId;
			}
		}

		return $result;
	}

	protected function getAccessCodesForDiskFolder(): array
	{
		$accessCodes = parent::getAccessCodesForDiskFolder();
		$departmentCode = \CIMDisk::GetTopDepartmentCode();

		if ($departmentCode)
		{
			$driver = \Bitrix\Disk\Driver::getInstance();
			$rightsManager = $driver->getRightsManager();
			$accessCodes[] = [
				'ACCESS_CODE' => $departmentCode,
				'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
			];
		}

		return $accessCodes;
	}

	public function extendPullWatch(): void
	{
		if (Loader::includeModule('pull'))
		{
			\CPullWatch::Add($this->getContext()->getUserId(), "IM_PUBLIC_{$this->getId()}", true);
		}
	}

	public function needToSendPublicPull(): bool
	{
		return true;
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		parent::updateStateAfterUsersAdd($usersToAdd);

		if (Loader::includeModule('pull'))
		{
			foreach ($usersToAdd as $userId)
			{
				\CPullWatch::Delete($userId, 'IM_PUBLIC_' . $this->getId());
			}
		}

		$this->clearAllLegacyCache();

		return $this;
	}

	protected function updateStateAfterUserDelete(int $deletedUserId, DeleteUserConfig $config): self
	{
		parent::updateStateAfterUserDelete($deletedUserId, $config);
		$this->clearAllLegacyCache();

		return $this;
	}

	protected function clearLegacyCache(int $userId): void
	{
		return;
	}

	protected function clearAllLegacyCache()
	{
		CIMContactList::CleanAllChatCache();
	}
}
