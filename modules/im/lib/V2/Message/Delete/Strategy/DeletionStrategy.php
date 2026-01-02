<?php

namespace Bitrix\Im\V2\Message\Delete\Strategy;

use Bitrix\Disk\SystemUser;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Link\File\FileService;
use Bitrix\Im\V2\Message\Delete\DeletionMode;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Sync\Event;
use Bitrix\Im\V2\Sync\Logger;

abstract class DeletionStrategy
{
	use ContextCustomer;

	protected MessageCollection $messages;
	protected Chat $chat;

	final protected function __construct(MessageCollection $messages)
	{
		$this->messages = $messages;
		$chatId = $messages->getCommonChatId();
		$this->chat = Chat::getInstance($chatId);
	}

	public static function getInstance(MessageCollection $messages, DeletionMode $deletionMode): DeletionStrategy
	{
		return match ($deletionMode)
		{
			DeletionMode::Soft => (new SoftDeletionStrategy($messages)),
			DeletionMode::Complete => (new CompleteDeletionStrategy($messages)),
			default => (new NoneDeletionStrategy($messages)),
		};
	}

	/**
	 * @throws InterruptedExecutionException
	 */
	abstract protected function onBeforeDelete(): void;

	/**
	 * @throws InterruptedExecutionException
	 */
	abstract protected function onAfterDelete(): void;

	/**
	 * @throws InterruptedExecutionException
	 */
	abstract protected function execute(): void;

	protected function logToSync(string $event): void
	{
		$ids = $this->messages->getIds();

		foreach ($ids as $id)
		{
			Logger::getInstance()->add(
				new Event($event, Event::MESSAGE_ENTITY, $id),
				fn () => $this->chat->getRelations()->getUserIds(),
				$this->chat
			);
		}
	}

	protected function deleteFiles(): void
	{
		$messageIdsToDeleteLinks = [];

		foreach ($this->messages as $message)
		{
			if ($message->getId() === null || $message->getFiles()->isEmpty())
			{
				continue;
			}

			$needToDeleteLink = true;

			/**
			 * @var FileItem $file
			 */
			foreach ($message->getFiles() as $file)
			{
				$diskFile = $file->getDiskFile();
				$contextUserId = $this->getContext()->getUserId();

				if (!isset($diskFile))
				{
					continue;
				}

				if (
					(int)$diskFile->getCreatedBy() === $contextUserId
					&& (int)$diskFile->getParentId() === $this->chat->getDiskFolderId()
					&& $diskFile->delete(SystemUser::SYSTEM_USER_ID)
				)
				{
					/**
					 * If we delete the file directly, the links will be deleted in the event handler.
					 * @see \CIMDisk::OnAfterDeleteFile
					 */
					$needToDeleteLink = false;
				}
			}

			if ($needToDeleteLink)
			{
				$messageIdsToDeleteLinks[] = $message->getId();
			}
		}

		(new FileService())->deleteFilesByMessageIds($messageIdsToDeleteLinks);
	}

	/**
	 * @throws InterruptedExecutionException
	 */
	protected function checkResult(Result $result): void
	{
		if (!$result->isSuccess())
		{
			throw new InterruptedExecutionException($result);
		}
	}

	final public function delete(): Result
	{
		$result = new Result();

		try
		{
			$this->onBeforeDelete();
			$this->execute();
			$this->onAfterDelete();
		}
		catch (InterruptedExecutionException $exception)
		{
			$result = $exception->getResult();
		}
		finally
		{
			return $result;
		}
	}
	abstract protected function getDeletionMode(): DeletionMode;
}
