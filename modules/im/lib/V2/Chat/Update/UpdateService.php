<?php

namespace Bitrix\Im\V2\Chat\Update;

use Bitrix\Im\V2\Analytics\ChatAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Converter;
use Bitrix\Im\V2\Entity\File\ChatAvatar;
use Bitrix\Im\V2\Integration\HumanResources\Structure;
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

		$this
			->convertChat()
			->updateAvatarBeforeSave()
		;

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

	protected function convertChat(): self
	{
		$chat = $this->chat;
		$searchable = $this->updateFields->getSearchable();

		if (!isset($searchable) || $chat instanceof Chat\VideoConfChat)
		{
			return $this;
		}

		$conversionMap = [
			Chat::IM_TYPE_CHAT . '_Y' => \Bitrix\Im\V2\Chat::IM_TYPE_OPEN,
			Chat::IM_TYPE_OPEN . '_N' => \Bitrix\Im\V2\Chat::IM_TYPE_CHAT,
			Chat::IM_TYPE_CHANNEL . '_Y' => \Bitrix\Im\V2\Chat::IM_TYPE_OPEN_CHANNEL,
			Chat::IM_TYPE_OPEN_CHANNEL . '_N' => \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL,
		];

		$key = $chat->getType() . '_' . $searchable;
		if (isset($conversionMap[$key]))
		{
			$newType = $conversionMap[$key];
			(new Converter($chat->getId(), $newType))->convert();
			$this->newChatType = $newType;

			// replace object after conversion
			$this->chat = Chat\GroupChat::getInstance($this->chat->getChatId());
		}

		return $this;
	}

	protected function addUsers(): self
	{
		$updateFields = $this->updateFields;

		$addedUsers = array_unique(array_merge(
			$updateFields->getAddedUsers(),
			$updateFields->getAddedManagers(),
			[$updateFields->getOwnerId()]
		));

		$this->chat->addUsers($addedUsers, new AddUsersConfig($updateFields->getAddedManagers(), $updateFields->shouldHideHistory()));

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

		$currentNodes = (new Structure($this->chat))->getChatDepartments();

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
