<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;

class Converter
{
	private const PUSH_CONVERT_NAME = 'chatConvert';
	private const OPEN_TYPES = [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_OPEN_CHANNEL];
	private const CHANNEL_TYPES = [Chat::IM_TYPE_CHANNEL, Chat::IM_TYPE_OPEN_CHANNEL];
	private const BLACKLIST_TYPES = [Chat::IM_TYPE_COMMENT, Chat::IM_TYPE_PRIVATE];
	private int $chatId;
	private ?Chat $chat;
	private string $oldType;
	private string $oldRestType;
	private string $newType;

	public function __construct(int $chatId, string $newType)
	{
		$this->chatId = $chatId;
		$this->initOldTypes();
		$this->newType = $newType;
	}

	public function convert(): Result
	{
		$result = new Result();

		$checkResult = $this->isAvailable();

		if (!$checkResult->isSuccess())
		{
			return $result->addErrors($checkResult->getErrors());
		}

		Application::getConnection()->startTransaction();
		try {
			$this
				->convertChatInfo()
				->updateDiskRights()
				->convertRelations()
				->convertRecent()
				->convertCounters()
			;
			Application::getConnection()->commitTransaction();
		}
		catch (\Throwable $exception)
		{
			Application::getConnection()->rollbackTransaction();

			return $result->addError(new ChatError(ChatError::CONVERT_ERROR));
		}

		$this->onBeforeConvert();

		return $result;
	}

	protected function isAvailable(): Result
	{
		$result = new Result();

		if (
			!in_array($this->newType, Chat::IM_TYPES, true)
			|| !in_array($this->oldType, Chat::IM_TYPES, true)
			|| in_array($this->newType, self::BLACKLIST_TYPES, true)
			|| in_array($this->oldType, self::BLACKLIST_TYPES, true)
			|| $this->oldType === $this->newType
		)
		{
			return $result->addError(new ChatError(ChatError::WRONG_TYPE));
		}

		return $result;
	}

	protected function onBeforeConvert(): void
	{
		Chat::cleanCache($this->chatId);
		Chat::cleanAccessCache($this->chatId);
		CounterService::clearCache();
		$this->sendPush();
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	protected function convertChatInfo(): self
	{
		ChatTable::update($this->chatId, ['TYPE' => $this->newType]);
		$this->chat = null;
		$this->setChatPermissionToDefaultValues();

		return $this;
	}

	protected function updateDiskRights(): self
	{
		if ($this->fromCloseToOpenType())
		{
			$this->addDepartmentToDiskRights();
		}
		elseif ($this->fromOpenToCloseType())
		{
			$this->deleteDepartmentFromDiskRights();
		}

		return $this;
	}

	protected function convertRelations(): self
	{
		RelationTable::updateByFilter(['=CHAT_ID' => $this->chatId], ['MESSAGE_TYPE' => $this->newType]);

		return $this;
	}

	protected function convertRecent(): self
	{
		RecentTable::updateByFilter(['=ITEM_TYPE' => $this->oldType, '=ITEM_ID' => $this->chatId], ['ITEM_TYPE' => $this->newType]);

		return $this;
	}

	protected function convertCounters(): self
	{
		MessageUnreadTable::updateByFilter(['=CHAT_ID' => $this->chatId], ['CHAT_TYPE' => $this->newType]);
		if ($this->fromChannelToOther())
		{
			return $this->deleteChildrenCounters();
		}

		return $this;
	}

	protected function deleteChildrenCounters(): self
	{
		$chatIds = CounterService::getChildrenWithCounters($this->getChat());

		if (!empty($chatIds))
		{
			MessageUnreadTable::deleteByFilter(['=CHAT_ID' => $chatIds]);
		}

		return $this;
	}

	protected function sendPush(): void
	{
		$chat = $this->getChat();
		$pushParams = [
			'module_id' => 'im',
			'command' => self::PUSH_CONVERT_NAME,
			'params' => [
				'id' => $this->chatId,
				'dialogId' => 'chat' . $this->chatId,
				'oldType' => $this->oldRestType,
				'newType' => $chat->getExtendedType(),
				'newPermissions' => $chat->getPermissions(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		\Bitrix\Pull\Event::add($chat->getRelations()->getUserIds(), $pushParams);
		if (\CIMMessenger::needToSendPublicPull($this->newType) || \CIMMessenger::needToSendPublicPull($this->oldType))
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->chatId, $pushParams);
		}
	}

	protected function initOldTypes(): void
	{
		$this->oldType = $this->getChat()->getType();
		$this->oldRestType = $this->getChat()->getExtendedType();
	}

	protected function addDepartmentToDiskRights(): void
	{
		$folder = $this->getChat()->getOrCreateDiskFolder();
		if (!$folder)
		{
			return;
		}

		$departmentCode = \CIMDisk::GetTopDepartmentCode();
		if (!$departmentCode)
		{
			return;
		}

		$driver = \Bitrix\Disk\Driver::getInstance();
		$rightsManager = $driver->getRightsManager();
		$departmentRight = [[
			'ACCESS_CODE' => $departmentCode,
			'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
		]];
		$rightsManager->append($folder, $departmentRight);
	}

	protected function deleteDepartmentFromDiskRights(): void
	{
		$folder = $this->getChat()->getOrCreateDiskFolder();
		if (!$folder)
		{
			return;
		}

		$driver = \Bitrix\Disk\Driver::getInstance();
		$rightsManager = $driver->getRightsManager();
		$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;

		$accessCodes = [];
		$accessCodes[] = [
			'ACCESS_CODE' => $accessProvider->generateAccessCode($this->chatId),
			'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_EDIT)
		];
		$rightsManager->set($folder, $accessCodes);
	}

	protected function fromCloseToOpenType(): bool
	{
		$isOldTypeClose = !in_array($this->oldType, self::OPEN_TYPES, true);
		$isNewTypeOpen = in_array($this->newType, self::OPEN_TYPES, true);

		return $isOldTypeClose && $isNewTypeOpen;
	}

	protected function fromOpenToCloseType(): bool
	{
		$isOldTypeOpen = in_array($this->oldType, self::OPEN_TYPES, true);
		$isNewTypeClose = !in_array($this->newType, self::OPEN_TYPES, true);

		return $isOldTypeOpen && $isNewTypeClose;
	}

	protected function fromChannelToOther(): bool
	{
		$isOldTypeChannel = in_array($this->oldType, self::CHANNEL_TYPES, true);
		$isNewTypeNotChannel = !in_array($this->newType, self::CHANNEL_TYPES, true);

		return $isOldTypeChannel && $isNewTypeNotChannel;
	}

	protected function setChatPermissionToDefaultValues(): void
	{
		$permissionNames = array_keys(Permission::GROUP_ACTIONS);
		$emptyPermissions = [];
		foreach ($permissionNames as $permissionName)
		{
			$emptyPermissions[$permissionName] = '';
		}
		$this->getChat()->fill($emptyPermissions);
		$this->getChat()->save();
	}

	protected function getChat(): Chat
	{
		$this->chat ??= Chat::getInstance($this->chatId);

		return $this->chat;
	}
}