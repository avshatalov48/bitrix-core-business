<?php

namespace Bitrix\Mail\ImapCommands;

use Bitrix\Mail;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * Class MailsFoldersManager
 * @package Bitrix\Mail\ImapCommands
 */
class MailsFoldersManager extends SyncInternalManager
{
	public function deleteMails($deleteImmediately = false)
	{
		$result = $this->initData(MailboxDirectoryTable::TYPE_TRASH);
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->processDelete($this->getDirPathByType(MailboxDirectoryTable::TYPE_TRASH),$deleteImmediately);
	}

	public function moveMails($folderToMoveName)
	{
		$result = $this->initData();
		if (!$result->isSuccess())
		{
			return $result;
		}
		$folders = [];
		foreach ($this->messages as $index => $message)
		{
			if (in_array($message['ID'], $this->messagesIds, true))
			{
				$folders[$message['DIR_MD5']] = $message['DIR_MD5'];
			}
		}
		foreach ($folders as $index => $folderHash)
		{
			if ($folderHash === md5($folderToMoveName))
			{
				return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_MOVE_TO_SELF_FOLDER', ['#FOLDER#' => $folderToMoveName]),
					'MAIL_CLIENT_MOVE_TO_SELF_FOLDER'));
			}
		}

		$dir = $this->getDirByPath($folderToMoveName);

		if (!$dir)
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_MAILBOX_NOT_FOUND'),
				'MAIL_CLIENT_MAILBOX_NOT_FOUND'));
		}

		if ($dir->isDisabled())
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_FOLDER_IS_DISABLED', ['#FOLDER#' => $folderToMoveName]),
				'MAIL_CLIENT_FOLDER_IS_DISABLED'));
		}

		if ($dir->isSpam())
		{
			return $this->sendMailsToSpam();
		}
		elseif ($dir->isTrash())
		{
			return $this->deleteMails();
		}
		elseif ($dir->isIncome())
		{
			return $this->restoreMailsFromSpam();
		}

		$result = $this->moveMailsToFolder($folderToMoveName);
		if (!$result->isSuccess())
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
		}

		return (new Main\Result());
	}

	public function restoreMailsFromSpam()
	{
		$result = $this->initData(MailboxDirectoryTable::TYPE_SPAM);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$result = $this->moveMailsToFolder($this->getDirPathByType(MailboxDirectoryTable::TYPE_INCOME));
		if (!$result->isSuccess())
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
		}
		$filter = Mail\BlacklistTable::getUserAddressesListQuery($this->userId, false)->getFilter();
		$filter[] = ['@ITEM_VALUE' => array_column($this->messages, 'EMAIL')];
		$filter[] = ['=ITEM_TYPE' => Mail\Blacklist\ItemType::EMAIL];
		\Bitrix\Mail\BlacklistTable::deleteList($filter);

		return $result;
	}

	/**
	 * @return Main\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function sendMailsToSpam()
	{
		$result = $this->initData(MailboxDirectoryTable::TYPE_SPAM);
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->processSpam($this->getDirPathByType(MailboxDirectoryTable::TYPE_SPAM));
	}

	private function processDelete($folderTrashName, $deleteImmediately = false)
	{
		$messagesToMove = $messagesToDelete = [];

		foreach ($this->messages as $messageUid)
		{
			if ($this->isMailToBeDeleted($messageUid) || $deleteImmediately)
			{
				$messagesToDelete[] = $messageUid;
			}
			else
			{
				$messagesToMove[] = $messageUid;
			}
		}

		$result = $this->processMoving($messagesToMove, $folderTrashName);
		if (!$result->isSuccess())
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
		}

		return $this->deleteMessages($messagesToDelete, $this->mailbox);
	}

	private function processMoving($messagesToMove, $destinationDirectory)
	{
		if (!$messagesToMove)
		{
			return new Main\Result();
		}
		$result = $this->moveMailsByImap($messagesToMove, $destinationDirectory);
		if ($result->isSuccess())
		{
			$this->repository->updateMessageFieldsAfterMove($this->messages, $destinationDirectory, $this->mailbox);
			$this->imapSyncMovedMessages($messagesToMove, $destinationDirectory);
			return (new Main\Result())->setData($messagesToMove);
		}
		else
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
		}
	}

	private function processSpam($folderSpamName)
	{
		$result = $this->moveMailsToFolder($folderSpamName);
		if (!$result->isSuccess())
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
		}
		if ($result->getData())
		{
			$mailsToBlacklist = [];
			foreach ($this->messages as $messageUid)
			{
				if ($messageUid['EMAIL'] !== $this->mailbox['EMAIL'])
				{
					$mailsToBlacklist[] = $messageUid['EMAIL'];
				}
			}
			return $this->repository->addMailsToBlacklist($mailsToBlacklist, $this->userId);
		}
		return $result;
	}

	private function deleteMessages($messagesToDelete, $mailbox)
	{
		if (empty($messagesToDelete))
		{
			return new Main\Result();
		}

		$result = $this->mailboxHelper->deleteMails($messagesToDelete);

		if ($result->isSuccess())
		{
			$this->repository->deleteMailsCompletely($messagesToDelete, $this->mailbox['USER_ID']);
			return new Main\Result();
		}

		return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
	}

	private function isMailToBeDeleted($messageUid)
	{
		$trashFolder = $this->getDirPathByType(MailboxDirectoryTable::TYPE_TRASH);
		return md5($trashFolder) === $messageUid['DIR_MD5'];
	}

	private function moveMailsToFolder($folderToName)
	{
		$mailsToMove = [];
		foreach ($this->messages as $messageUid)
		{
			if (md5($folderToName) !== $messageUid['DIR_MD5'])
			{
				$mailsToMove[] = $messageUid;
			}
		}
		return $this->processMoving($mailsToMove, $folderToName);
	}

	private function moveMailsByImap($messagesToMove, $folder)
	{
		if (empty($messagesToMove))
		{
			return new Main\Result();
		}

		return $this->mailboxHelper->moveMailsToFolder($messagesToMove, $folder);
	}

	private function processSyncMovedMessages($folderCurrentNameEncoded)
	{
		$folderCurrentName = base64_decode($folderCurrentNameEncoded);
		$this->mailboxHelper->syncDir($folderCurrentName);

		Mail\MailMessageUidTable::updateList(
			[
				'=MAILBOX_ID' => $this->mailboxId,
				'=DIR_MD5' => md5($folderCurrentName),
				'==MSG_UID' => 0,
				'!@IS_OLD' => ['D','R'],
			],
			[
				'IS_OLD' => 'M',
			],
		);
	}

	public static function syncMovedMessages($mailboxId, $mailboxUserId, $folderName)
	{
		try
		{
			$mailManager = new static($mailboxId, []);
			$mailManager->setMailboxUserId($mailboxUserId);
			$mailManager->processSyncMovedMessages($folderName);
		}
		catch (\Exception $e)
		{
		}

		return '';
	}

	protected function imapSyncMovedMessages($messagesToMove, $folderName)
	{
		$messIds = array_map(
			function ($item)
			{
				return $item['ID'];
			},
			$messagesToMove
		);

		\CAgent::addAgent(
			sprintf(
				static::class . "::syncMovedMessages(%u, %u, '%s');",
				$this->mailbox['ID'],
				$this->mailbox['USER_ID'],
				base64_encode($folderName)
			),
			'mail'
		);
	}

	public function setMailboxUserId($mailboxUserId)
	{
		$this->mailboxUserId = $mailboxUserId;
	}
}
