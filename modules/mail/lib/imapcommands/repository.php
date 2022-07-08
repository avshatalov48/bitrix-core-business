<?php
namespace Bitrix\Mail\ImapCommands;

use Bitrix\Mail;
use Bitrix\Main;
use Bitrix\Main\Entity\ReferenceField;
use \Bitrix\Mail\Helper\MessageFolder;

class Repository
{
	private $mailboxId;
	private $messagesIds;

	public function __construct($mailboxId, $messagesIds)
	{
		$this->mailboxId = $mailboxId;
		$this->messagesIds = $messagesIds;
	}

	public function getMailbox($mailboxUserId = null)
	{
		return Mail\MailboxTable::getUserMailbox($this->mailboxId, $mailboxUserId);
	}

	public function deleteOldMessages($folderCurrentName)
	{
		$connection = Main\Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$sql = 'DELETE from ' . Mail\MailMessageUidTable::getTableName() .
			' WHERE MAILBOX_ID = ' . intval($this->mailboxId) .
			" AND DIR_MD5 = '" . $sqlHelper->forSql(md5($folderCurrentName)) . "'" .
			' AND MSG_UID = 0;';
		$connection->query($sql);
		return $connection->getAffectedRowsCount();
	}

	public function markMessagesUnseen($messages, $mailbox)
	{
		$this->setMessagesSeen('N', $messages, $mailbox);
	}

	public function markMessagesSeen($messages, $mailbox)
	{
		$this->setMessagesSeen('Y', $messages, $mailbox);
	}

	protected function setMessagesSeen($isSeen, $messages, $mailbox)
	{
		$messagesIds = [];

		foreach ($this->messagesIds as $index => $messageId)
		{
			$messagesIds[$index] = $messageId;
		}

		if (empty($messagesIds) || empty($messages) || empty($mailbox))
		{
			return;
		}

		$mailsData = [];

		foreach ($messages as $messageData)
		{
			$mailsData[] = [
				'HEADER_MD5' => $messageData['HEADER_MD5'],
				'MAILBOX_USER_ID' => $mailbox['USER_ID'],
				'IS_SEEN' => $isSeen,
			];
		}

		$mailboxId = intval($this->mailboxId);

		Mail\MailMessageUidTable::updateList(
			[
				'=MAILBOX_ID' => $mailboxId,
				'@ID' => $messagesIds,
			],
			[
				'IS_SEEN' => $isSeen,
			],
			$mailsData
		);

		$dirWithMessagesId = MessageFolder::getDirIdForMessages($mailboxId,$messagesIds);

		if($isSeen === 'Y')
		{
			MessageFolder::decreaseDirCounter($mailboxId, $dirWithMessagesId, count($messagesIds));
		}
		else
		{
			MessageFolder::increaseDirCounter($mailboxId, false, $dirWithMessagesId, count($messagesIds));
		}

		\Bitrix\Mail\Helper::updateMailboxUnseenCounter($mailboxId);
	}

	public function updateMessageFieldsAfterMove($messages, $folderNewName, $mailbox)
	{
		$messagesIds = [];
		foreach ($messages as $message)
		{
			$messagesIds[] = $message['ID'];
		}
		if (empty($messagesIds))
		{
			return;
		}

		$mailsData = [];
		foreach ($messages as $messageData)
		{
			$mailsData[] = [
				'HEADER_MD5' => $messageData['HEADER_MD5'],
				'MAILBOX_USER_ID' => $mailbox['USER_ID']
			];
		}

		Mail\MailMessageUidTable::updateList(
			[
				'=MAILBOX_ID' => intval($this->mailboxId),
				'@ID' => $messagesIds,
			],
			[
				'MSG_UID' => 0,
				'DIR_MD5' => md5($folderNewName),
			],
			$mailsData
		);
	}

	public function addMailsToBlacklist($blacklistMails, $userId)
	{
		$result = new Main\Result();
		$result->setData([Mail\BlacklistTable::addMailsBatch($blacklistMails, $userId)]);
		return $result;
	}

	/**
	 * Used to delete small sample of messages from the database ( at the user's request ).
	 *
	 * @param array $messagesToDelete Each message in the array must be represented by an associative array containing the "MESSAGE_ID" field.
	 * @param $mailboxUserId
	 *
	 * @return null - if messages are missing
	 */
	public function deleteMailsCompletely($messagesToDelete, $mailboxUserId)
	{
		// @TODO: make a log optional
		/*$messageToLog = [
			'cause' => 'deleteMailsCompletely',
			'filter' => 'manual deletion of messages',
			'removedMessages'=>$messagesToDelete,
		];
		AddMessage2Log($messageToLog);*/

		$ids = array_map(
			function ($mail)
			{
				return intval($mail['MESSAGE_ID']);
			},
			$messagesToDelete
		);
		if (empty($ids))
		{
			return;
		}
		$mailFieldsForEvent = [];

		foreach ($messagesToDelete as $index => $item)
		{
			$mailFieldsForEvent[] = [
				'HEADER_MD5' => $item['HEADER_MD5'],
				'MESSAGE_ID' => $item['MESSAGE_ID'],
				'MAILBOX_USER_ID' => $mailboxUserId,
			];
		}
		Mail\MailMessageUidTable::deleteList(
			[
				'=MAILBOX_ID' => $this->mailboxId,
				'@MESSAGE_ID' => $ids,
			],
			$mailFieldsForEvent
		);

		// @TODO: use API
		$connection = Main\Application::getInstance()->getConnection();
		$connection->query(
			'DELETE from ' . Mail\MailMessageTable::getTableName() .
			' WHERE ID IN (' . implode(',', $ids) . ');'
		);
	}

	public function getMessages()
	{
		if (empty($this->messagesIds))
		{
			return [];
		}
		$messages = [];
		$messagesSelected = Mail\MailMessageUidTable::query()
			->addSelect('MESSAGE_ID')
			->where('MAILBOX_ID', $this->mailboxId)
			->whereIn('ID', $this->messagesIds)
			->whereNot('MSG_UID', 0)
			->where('MESSAGE_ID', '>', 0)
			->addFilter('==DELETE_TIME', 0)
			->exec()
			->fetchAll();
		if ($messagesSelected)
		{
			$messagesSelectedIds = array_map(
				function ($item)
				{
					return $item['MESSAGE_ID'];
				},
				$messagesSelected
			);
			if (empty($messagesSelectedIds))
			{
				return [];
			}
			$messages = Mail\MailMessageUidTable::query()
				->registerRuntimeField(
					'',
					new ReferenceField(
						'ref',
						Mail\MailMessageTable::class,
						['=this.MESSAGE_ID' => 'ref.ID']
					)
				)
				->addSelect('ID')
				->addSelect('MAILBOX_ID')
				->addSelect('DIR_MD5')
				->addSelect('DIR_UIDV')
				->addSelect('MSG_UID')
				->addSelect('HEADER_MD5')
				->addSelect('IS_SEEN')
				->addSelect('SESSION_ID')
				->addSelect('MESSAGE_ID')
				->addSelect('ref.FIELD_FROM', 'FIELD_FROM')
				->whereIn('MESSAGE_ID', $messagesSelectedIds)
				->where('MAILBOX_ID', $this->mailboxId)
				->whereNot('MSG_UID', 0)
				->exec()
				->fetchAll();
		}

		return $messages;
	}
}
