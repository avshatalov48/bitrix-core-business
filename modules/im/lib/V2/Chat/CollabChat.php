<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Disk\Driver;
use Bitrix\Disk\Folder;
use Bitrix\Im\Access\ChatAuthProvider;
use Bitrix\Im\V2\Chat\Collab\CollabInfo;
use Bitrix\Im\V2\Chat\Collab\GuestCounter;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserCollaber;
use Bitrix\Im\V2\Integration\Socialnetwork\Group;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Permission\ActionGroup;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Recent\Initializer;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Result;

class CollabChat extends GroupChat
{
	protected const EXTRANET_CAN_SEE_HISTORY = true;

	private const UNAVAILABLE_ACTION_GROUPS = [
		ActionGroup::ManageUi,
		ActionGroup::ManageSettings,
		ActionGroup::ManageUsersDelete,
		ActionGroup::ManageUsersAdd
	];
	private const UNAVAILABLE_ACTIONS = [
		Action::Leave,
		Action::LeaveOwner,
		Action::Update,
		Action::Delete,
	];

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_COLLAB;
	}

	protected function prepareParams(array $params = []): Result
	{
		if (!isset($params['ENTITY_ID']))
		{
			return (new Result())->addError(new ChatError(ChatError::ENTITY_ID_EMPTY));
		}

		$params['ENTITY_TYPE'] = Type::Sonet->value;

		return parent::prepareParams($params);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return parent::getPopupData($excludedList)->add(new CollabInfo($this));
	}

	public function getStorageId(): int
	{
		return Group::getStorageId((int)$this->getEntityId()) ?? 0;
	}

	protected function createDiskFolder(): ?Folder
	{
		$storage = \CIMDisk::GetStorage($this->chatId);
		if (!$storage)
		{
			return null;
		}

		$folder = $storage->getFolderForUploadedFiles();
		if (!$folder)
		{
			return null;
		}

		$this->setDiskFolderId($folder->getId())->save();
		Driver::getInstance()->getRightsManager()->append($folder, $this->getAccessCodesForDiskFolder());
		$accessProvider = new ChatAuthProvider;
		$accessProvider->updateChatCodesByRelations($this->getId());

		return $folder;
	}

	public function canDo(Action $action, mixed $target = null): bool
	{
		if (in_array($action, self::UNAVAILABLE_ACTIONS, true))
		{
			return false;
		}

		$actionGroup = ActionGroup::tryFromAction($action);

		if (in_array($actionGroup, self::UNAVAILABLE_ACTION_GROUPS, true))
		{
			return false;
		}

		return parent::canDo($action, $target);
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		Initializer::onAfterUsersAddToCollab($usersToAdd, $this->getId());

		if (!empty($this->filterCollabers($usersToAdd)))
		{
			GuestCounter::cleanCache($this->chatId);
		}

		return parent::updateStateAfterUsersAdd($usersToAdd);
	}

	protected function updateStateAfterUserDelete(int $deletedUserId, DeleteUserConfig $config): self
	{
		if (!empty($this->filterCollabers([$deletedUserId])))
		{
			GuestCounter::cleanCache($this->chatId);
		}

		return parent::updateStateAfterUserDelete($deletedUserId, $config);
	}

	protected function sendPushUsersAdd(array $usersToAdd, RelationCollection $oldRelations): void
	{
		if (!empty($this->filterCollabers($usersToAdd)))
		{
			(new GuestCounter($this))->sendPushGuestCount();
		}

		parent::sendPushUsersAdd($usersToAdd, $oldRelations);
	}

	protected function sendPushUserDelete(int $userId, RelationCollection $oldRelations): void
	{
		if (!empty($this->filterCollabers([$userId])))
		{
			(new GuestCounter($this))->sendPushGuestCount();
		}

		parent::sendPushUserDelete($userId, $oldRelations);
	}

	protected function filterCollabers(array $userIds): array
	{
		return array_filter($userIds, fn (int $userId) => User::getInstance($userId) instanceof UserCollaber);
	}
}
