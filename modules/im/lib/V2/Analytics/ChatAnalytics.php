<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Disk\Analytics\DiskAnalytics;
use Bitrix\Disk\Analytics\Enum\ImSection;
use Bitrix\Disk\File;
use Bitrix\Im\V2\Analytics\Event\ChatEvent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\CollabChat;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Main\Loader;

class ChatAnalytics extends AbstractAnalytics
{
	public const SUBMIT_CREATE_NEW = 'submit_create_new';

	protected const JOIN = 'join';
	protected const ADD_DEPARTMENT = 'add_department';
	protected const ADD_USER = 'add_user';
	protected const DELETE_USER = 'delete_user';
	protected const FOLLOW_COMMENTS = 'follow_comments';
	protected const UNFOLLOW_COMMENTS = 'unfollow_comments';
	protected const EDIT_DESCRIPTION = 'edit_description';
	protected const DELETE_DEPARTMENT = 'delete_department';
	protected const EDIT_PERMISSIONS = 'edit_permissions';
	protected const SET_TYPE = 'set_type';

	protected static array $oneTimeEvents = [];
	protected static array|bool $blockSingleUserEvents = [];

	public function addSubmitCreateNew(): void
	{
		$this->async(function () {
			$this
				->createChatEvent(self::SUBMIT_CREATE_NEW)
				?->setChatType()
				?->send()
			;
		});
	}

	public function addAddUser(Reason $reason = Reason::DEFAULT, bool $isJoin = false): void
	{
		if ($this->isSingleUserEventsBlocked())
		{
			return;
		}

		$this->async(function () use ($reason, $isJoin) {
			if ($isJoin)
			{
				$eventName = self::JOIN;
			}
			else
			{
				$eventName = match ($reason) {
					Reason::STRUCTURE => self::ADD_DEPARTMENT,
					default => self::ADD_USER,
				};
			}

			$this
				->createChatEvent($eventName)
				?->send()
			;
			(new CopilotAnalytics($this->chat))->addAddUser();
		});
	}

	public function addDeleteUser(): void
	{
		if ($this->isSingleUserEventsBlocked())
		{
			return;
		}

		$this->async(function () {
			$this
				->createChatEvent(self::DELETE_USER)
				?->send()
			;
			(new CopilotAnalytics($this->chat))->addDeleteUser();
		});
	}

	public function addFollowComments(bool $flag): void
	{
		$this->async(function () use ($flag) {
			$this
				->createChatEvent($flag ? self::FOLLOW_COMMENTS : self::UNFOLLOW_COMMENTS)
				?->send()
			;
		});
	}

	protected function addChatEditEvent(string $eventName): void
	{
		if ($this->chat instanceof CollabChat)
		{
			return;
		}

		$this->async(function () use ($eventName) {
			$this
				->createChatEvent($eventName)
				?->send()
			;
		});
	}

	public function addEditDescription(): void
	{
		$this->async(function () {
			$this
				->createChatEvent(self::EDIT_DESCRIPTION)
				?->send()
			;
		});
	}

	public function addAddDepartment(): void
	{
		$this->addChatEditEvent(self::ADD_DEPARTMENT);
	}

	public function addDeleteDepartment(): void
	{
		$this->addChatEditEvent(self::DELETE_DEPARTMENT);
	}

	public function addEditPermissions(): void
	{
		if ($this->isFirstTimeEvent(self::EDIT_PERMISSIONS))
		{
			$this->addChatEditEvent(self::EDIT_PERMISSIONS);
		}
	}

	public function addSetType(): void
	{
		$this->addChatEditEvent(self::SET_TYPE);
	}

	public function addUploadFile(File $file): void
	{
		if (Loader::includeModule('disk'))
		{
			$this->async(fn () => DiskAnalytics::sendUploadFileToImEvent($file, $this->getImSectionForDiskEvent()));
		}
	}

	protected function getImSectionForDiskEvent(): ImSection
	{
		return match ($this->chat->getType())
		{
			Chat::IM_TYPE_CHANNEL, Chat::IM_TYPE_OPEN_CHANNEL => ImSection::Channel,
			Chat::IM_TYPE_OPEN_LINE => ImSection::Openline,
			default => ImSection::Chat
		};
	}

	protected function createChatEvent(
		string $eventName,
	): ?ChatEvent
	{
		if (!$this->isChatTypeAllowed($this->chat))
		{
			return null;
		}

		return (new ChatEvent($eventName, $this->chat));
	}

	protected function isFirstTimeEvent(string $eventName): bool
	{
		$result = self::$oneTimeEvents[$eventName][$this->chat->getId()] ?? true;
		self::$oneTimeEvents[$eventName][$this->chat->getId()] = false;

		return $result;
	}

	public static function blockSingleUserEvents(?Chat $chat = null): void
	{
		if (self::$blockSingleUserEvents === true)
		{
			return;
		}

		if ($chat === null)
		{
			self::$blockSingleUserEvents = true;

			return;
		}

		self::$blockSingleUserEvents[$chat->getId()] = true;
	}

	public static function unblockSingleUserEventsByChat(Chat $chat): void
	{
		if (is_bool(self::$blockSingleUserEvents))
		{
			return;
		}

		unset(self::$blockSingleUserEvents[$chat->getId()]);
	}

	protected function isSingleUserEventsBlocked(): bool
	{
		if (!is_array(self::$blockSingleUserEvents))
		{
			return true;
		}

		return self::$blockSingleUserEvents[$this->chat->getId()] ?? false;
	}
}
