<?php

namespace Bitrix\Im\V2\Chat\Update;

use Bitrix\Im\V2\Analytics\ChatAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Converter;
use Bitrix\Im\V2\Entity\File\ChatAvatar;
use Bitrix\Im\V2\Integration\HumanResources\Structure;
use Bitrix\Im\V2\Integration\Socialnetwork\Collab\Collab;
use Bitrix\Im\V2\Integration\Socialnetwork\Group;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Result;

class UpdateService
{
	protected UpdateFields $updateFields;
	protected Chat\GroupChat $chat;
	protected ?string $newChatType = null;

	public function __construct(Chat\GroupChat $chat, UpdateFields $updateFields)
	{
		$this->chat = $chat;
		$this->updateFields  = $updateFields;
	}

	public function updateChat(): Result
	{
		$prevAnalyticsData = $this->getAnalyticsData();

		$convertResult = $this->convertChat();

		if (!$convertResult->isSuccess())
		{
			return $convertResult;
		}

		$this->updateAvatarBeforeSave();

		$this->chat->fill($this->getArrayToSave());
		$result = $this->chat->save();

		if (!$result->isSuccess())
		{
			return $result->setResult($this->chat);
		}

		$svc = $this
			->sendMessageAfterUpdateAvatar()
			->deleteUsers()
			->addUsers()
			->deleteManagers()
			->addManagers()
		;

		ChatAnalytics::blockSingleUserEvents($this->chat);

		$svc
			->deleteDepartments()
			->addDepartments()
		;

		$this->sendPushUpdateChat();
		$this->compareAnalyticsData($prevAnalyticsData);

		ChatAnalytics::unblockSingleUserEventsByChat($this->chat);

		return $result->setResult($this->chat);
	}

	protected function convertChat(): Result
	{
		$result = new Result();

		$newType = $this->getConvertType();

		if (!isset($newType) || $this->chat->getType() === $newType)
		{
			return $result;
		}

		$convertResult = (new Converter($this->chat->getId(), $newType))->convert();

		if (!$convertResult->isSuccess())
		{
			return $result->addErrors($convertResult->getErrors());
		}

		$this->newChatType = $newType;

		// replace object after conversion
		$this->chat = Chat\GroupChat::getInstance($this->chat->getChatId());

		return $result;
	}

	protected function getConvertType(): ?string
	{
		$searchable = $this->updateFields->getSearchable();
		$currentType = $this->chat->getType();
		$newType = $this->updateFields->getType();

		return match (true)
		{
			$currentType === Chat::IM_TYPE_CHAT && $searchable === 'Y' => \Bitrix\Im\V2\Chat::IM_TYPE_OPEN,
			$currentType === Chat::IM_TYPE_OPEN && $searchable === 'N' => \Bitrix\Im\V2\Chat::IM_TYPE_CHAT,
			$currentType === Chat::IM_TYPE_CHANNEL && $searchable === 'Y' => \Bitrix\Im\V2\Chat::IM_TYPE_OPEN_CHANNEL,
			$currentType === Chat::IM_TYPE_OPEN_CHANNEL && $searchable === 'N' => \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL,
			default => $newType,
		};

	}

	protected function addUsers(): self
	{
		$updateFields = $this->updateFields;

		$addedUsers = array_unique(array_merge(
			$updateFields->getAddedUsers(),
			$updateFields->getAddedManagers(),
			[$updateFields->getOwnerId()]
		));

		$config = new AddUsersConfig(
			managerIds: $updateFields->getAddedManagers(),
			hideHistory: $updateFields->shouldHideHistory(),
			skipAnalytics: false
		);
		$this->chat->addUsers($addedUsers, $config);

		return $this;
	}

	protected function deleteUsers(): self
	{
		$deletedUsers = $this->updateFields->getDeletedUsers();

		foreach ($deletedUsers as $userId)
		{
			$this->chat->deleteUser((int)$userId, new DeleteUserConfig(false));
		}

		return $this;
	}

	protected function addManagers(): self
	{
		$addManagers = $this->updateFields->getAddedManagers();

		if (empty($addManagers))
		{
			return $this;
		}

		$this->chat->addManagers($addManagers, false);

		return $this;
	}

	protected function deleteManagers(): self
	{
		$deleteManagers = $this->updateFields->getDeletedManagers();

		if (empty($deleteManagers))
		{
			return $this;
		}

		$this->chat->deleteManagers($deleteManagers, false);

		return $this;
	}

	protected function addDepartments(): self
	{
		$addNodes = $this->updateFields->getAddedDepartments();

		if (empty($addNodes))
		{
			return $this;
		}

		(new Structure($this->chat))->link($addNodes);

		foreach ($addNodes as $node)
		{
			(new ChatAnalytics($this->chat))->addAddDepartment();
		}

		return $this;
	}

	protected function deleteDepartments(): self
	{
		$deleteNodes = $this->updateFields->getDeletedDepartments();

		if (empty($deleteNodes))
		{
			return $this;
		}

		$currentNodes = (new Structure($this->chat))->getNodesAccessCodes();

		foreach ($deleteNodes as $key => $node)
		{
			if (!in_array($node, $currentNodes, true))
			{
				unset($deleteNodes[$key]);
			}
		}

		(new Structure($this->chat))->unlink($deleteNodes);

		foreach ($deleteNodes as $node)
		{
			(new ChatAnalytics($this->chat))->addDeleteDepartment();
		}

		return $this;
	}

	protected function updateAvatarBeforeSave(): self
	{
		$avatarId = $this->updateFields->getAvatar();
		if (!isset($avatarId))
		{
			return $this;
		}

		(new ChatAvatar($this->chat))->update($avatarId, false, false, true);

		return $this;
	}

	protected function sendMessageAfterUpdateAvatar(): self
	{
		$avatarId = $this->updateFields->getAvatar();
		if (!isset($avatarId))
		{
			return $this;
		}

		$this->chat->sendMessageUpdateAvatar();

		return $this;
	}

	protected function sendPushUpdateChat(): void
	{
		if (!\Bitrix\Main\Loader::includeModule("pull"))
		{
			return;
		}

		$pushMessage = [
			'module_id' => 'im',
			'command' => 'chatUpdate',
			'expiry' => 3600,
			'params' => [
				'chat' => $this->chat->toPullFormat(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		\Bitrix\Pull\Event::add($this->chat->getRelations()->getUserIds(), $pushMessage);
		if ($this->chat->needToSendPublicPull())
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chat->getId(), $pushMessage);
		}
	}

	protected function getArrayToSave(): array
	{
		$fields = $this->filterFieldsByDifference($this->updateFields->getArrayToSave());

		if (isset($this->newChatType))
		{
			$fields['TYPE'] = $this->newChatType;
		}

		return $fields;
	}

	protected function filterFieldsByDifference(array $fields): array
	{
		if ($this->chat->getDescription() === $fields['DESCRIPTION'])
		{
			unset($fields['DESCRIPTION']);
		}

		return $fields;
	}

	protected function compareAnalyticsData(array $prevData): void
	{
		$currentData = $this->getAnalyticsData();
		$analytics = new ChatAnalytics($this->chat);
		$diff = fn(string $key) => $currentData[$key] !== $prevData[$key];

		if ($diff('description'))
		{
			$analytics->addEditDescription();
		}

		if ($diff('type'))
		{
			$analytics->addSetType();
		}

		if (
			$diff('owner') ||
			$diff('manageUI') ||
			$diff('manageUsersAdd') ||
			$diff('manageUsersDelete') ||
			$diff('manageMessages')
		)
		{
			$analytics->addEditPermissions();
		}
	}

	protected function getAnalyticsData(): array
	{
		return [
			'description' => $this->chat->getDescription(),
			'type' => $this->chat->getType(),
			'owner' => $this->chat->getAuthorId(),
			'manageUI' => $this->chat->getManageUI(),
			'manageUsersAdd' => $this->chat->getManageUsersAdd(),
			'manageUsersDelete' => $this->chat->getManageUsersDelete(),
			'manageMessages' => $this->chat->getManageMessages(),
		];
	}
}
