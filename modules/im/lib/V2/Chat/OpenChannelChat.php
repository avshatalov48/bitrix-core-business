<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class OpenChannelChat extends ChannelChat
{
	public const PULL_TAG_SHARED_LIST = 'IM_SHARED_CHANNEL_LIST';

	protected function sendMessageUsersAdd(array $usersToAdd, AddUsersConfig $config): void
	{
		parent::sendMessageUsersAdd($this->getExtranetUsersToAdd($usersToAdd), $config);
	}

	protected function getExtranetUsersToAdd(array $usersToAdd): array
	{
		if (!$this->getExtranet())
		{
			return [];
		}

		$extranetUsersToAdd = [];
		foreach ($usersToAdd as $userId)
		{
			if (User::getInstance($userId)->isExtranet())
			{
				$extranetUsersToAdd[$userId] = $userId;
			}
		}

		return $extranetUsersToAdd;
	}

	protected function sendMessageUserDelete(int $userId, DeleteUserConfig $config): void
	{
		return;
	}

	public function extendPullWatch(): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		parent::extendPullWatch();

		if ($this->getSelfRelation() === null)
		{
			\CPullWatch::Add($this->getContext()->getUserId(), "IM_PUBLIC_{$this->getId()}", true);
		}
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

		return $this;
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

	public function needToSendPublicPull(): bool
	{
		return true;
	}

	public static function sendSharedPull(array $pull): void
	{
		$pull['extra']['is_shared_event'] = true;
		\CPullWatch::AddToStack(\Bitrix\Im\V2\Chat\OpenChannelChat::PULL_TAG_SHARED_LIST, $pull);
	}

	public function isNew(): bool
	{
		$lastDay = (new DateTime())->add('-1 day');
		$dateCreate = $this->getDateCreate();

		if (!$dateCreate instanceof DateTime)
		{
			return false;
		}

		return $dateCreate->getTimestamp() > $lastDay->getTimestamp();
	}

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN_CHANNEL;
	}

	protected function checkAccessInternal(int $userId): Result
	{
		$checkResult = parent::checkAccessInternal($userId);

		if ($checkResult->isSuccess())
		{
			return $checkResult;
		}

		$result = new Result();

		if (User::getInstance($userId)->isExtranet())
		{
			$result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		return $result;
	}

	public static function extendPullWatchToCommonList(?int $userId = null): void
	{
		$userId ??= User::getCurrent()->getId();

		if (Loader::includeModule('pull'))
		{
			\CPullWatch::Add($userId, static::PULL_TAG_SHARED_LIST, true);
		}
	}
}
