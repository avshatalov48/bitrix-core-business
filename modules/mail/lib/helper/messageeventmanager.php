<?php
namespace Bitrix\Mail\Helper;

use Bitrix\Mail\MailboxDirectory;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Mail\MailMessageUidTable;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class MessageEventManager
{
	const EVENT_DELETE_MESSAGES = 'onMailMessageDeleted';

	/** Dispatches OnMessageModified event for compatibility
	 * If event parameters do not have ['HEADER_MD5', 'MAILBOX_USER_ID']
	 * data will be obtained from database
	 * @param Event $event
	 * @return MessageEventManager
	 */
	public static function onMailMessageDeleted(Event $event)
	{
		$manager = new static();
		$manager->processOnMailMessageDeletedEvent($event);
		return $manager;
	}

	private function processOnMailMessageDeletedEvent(Event $event)
	{
		$params = $event->getParameters();
		$filter = empty($params['DELETED_BY_FILTER']) ? [] : $params['DELETED_BY_FILTER'];
		$fieldsData = empty($params['MAIL_FIELDS_DATA']) ? [] : $params['MAIL_FIELDS_DATA'];
		$this->handleRemovedEvent($fieldsData, $filter);
	}

	/** Dispatches OnMessageObsolete event for compatibility
	 * If messages data from event parameters do not have ['HEADER_MD5', 'MAILBOX_USER_ID', 'IS_SEEN']
	 * data will be obtained from database
	 * @param Event $event
	 * @return MessageEventManager
	 */
	public static function onMailMessageModified(Event $event)
	{
		$manager = new static();
		$manager->processOnMailMessageModified($event);
		return $manager;
	}

	private function processOnMailMessageModified(Event $event)
	{
		$params = $event->getParameters();
		$updatedFieldValues = empty($params['UPDATED_FIELDS_VALUES']) ? [] : $params['UPDATED_FIELDS_VALUES'];
		$fieldsData = empty($params['MAIL_FIELDS_DATA']) ? [] : $params['MAIL_FIELDS_DATA'];
		$filter = empty($params['UPDATED_BY_FILTER']) ? [] : $params['UPDATED_BY_FILTER'];

		if (!empty($updatedFieldValues) && isset($updatedFieldValues['IS_SEEN']))
		{
			$fieldsData = $this->getMailsFieldsData($fieldsData, ['HEADER_MD5', 'MAILBOX_USER_ID', 'IS_SEEN'], $filter);
			$this->sendMessageModifiedEvent($fieldsData);
		}

		if (!empty($updatedFieldValues) && isset($updatedFieldValues['DIR_MD5']) && isset($filter['=MAILBOX_ID']))
		{
			$dirHash = empty($updatedFieldValues['DIR_MD5']) ? null : $updatedFieldValues['DIR_MD5'];
			$dir = MailboxDirectory::fetchOneByHash($filter['=MAILBOX_ID'], $dirHash);

			if (!empty($dirHash) && $dir != null)
			{
				if ($dir->isTrash() || $dir->isSpam() || $dir->isDisabled())
				{
					$this->handleRemovedEvent($fieldsData, $filter);
				}
			}
		}
	}

	protected function sendMessageModifiedEvent($fieldsData)
	{
		foreach ($fieldsData as $fields)
		{
			$event = new Event(
				'mail', 'OnMessageModified',
				[
					'user' => $fields['MAILBOX_USER_ID'],
					'hash' => $fields['HEADER_MD5'],
					'seen' => $fields['IS_SEEN'] === 'Y',
				]
			);
			$event->send();
		}
	}

	private function handleRemovedEvent($fieldsData, $filter)
	{
		$this->sendMessageDeletedEvent($fieldsData);
	}

	protected function sendMessageDeletedEvent($fieldsData)
	{
		foreach ($fieldsData as $fields)
		{
			$event = new Event(
				'mail', 'OnMessageObsolete',
				[
					'user' => $fields['MAILBOX_USER_ID'],
					'hash' => $fields['HEADER_MD5'],
				]
			);
			$event->send();
		}
	}

	private function getMailsFieldsData($eventData, $requiredKeys, $filter)
	{
		$fieldsData = $eventData;
		$missingKeys = $requiredKeys;
		$messagesCount = count($eventData);
		if ($messagesCount)
		{
			foreach ($requiredKeys as $requiredKey)
			{
				if (count(array_column($eventData, $requiredKey)) === $messagesCount)
				{
					$missingKeys = array_diff($missingKeys, [$requiredKey]);
				}
			}
		}

		if (!empty($missingKeys) && !empty($filter))
		{
			$fieldsData = $this->getMailMessagesList($filter, $missingKeys);
		}
		$results = [];
		foreach ($fieldsData as $index => $mailFieldsData)
		{
			$results[$mailFieldsData['HEADER_MD5']] = $mailFieldsData;
		}
		return $results;
	}

	protected function getMailMessagesList($filter, $selectingFields)
	{
		$dateLastMonth = new DateTime();
		$dateLastMonth->add('-1 MONTH');
		foreach ($selectingFields as $index => $selectingField)
		{
			if (strncmp('MAILBOX_', $selectingField, 8) === 0)
			{
				$selectingFields[$selectingField] = 'MAILBOX.'.mb_substr($selectingField, 8);
				unset($selectingFields[$index]);
			}
		}

		return MailMessageUidTable::getList([
				'select' => $selectingFields,
				'filter' => array_merge($filter, [
					'>=INTERNALDATE' => $dateLastMonth,
					'==DELETE_TIME' => 0,
				]),
			]
		)->fetchAll();
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Exception
	 */
	public static function onMailEventMailRead(array $data)
	{
		$messageId = $data['msgid'];
		if($messageId)
		{
			$message = MailMessageTable::getList([
				'select' => [
					'OPTIONS', 'ID', 'READ_CONFIRMED',
				],
				'filter' => [
					'=MSG_ID' => $messageId,
					'READ_CONFIRMED' => null,
				]
			])->fetch();
			if($message)
			{
				$readTime = new DateTime();
				$result = MailMessageTable::update($message['ID'], [
					'READ_CONFIRMED' => $readTime,
				]);
				if($result->isSuccess())
				{
					if(Loader::includeModule("pull"))
					{
						\CPullWatch::addToStack(static::getPullTagName($message['ID']), [
							'module_id' => 'mail',
							'command' => 'onMessageRead',
							'params' => [
								'messageId' => $message['ID'],
								'readTime' => $readTime->getTimestamp(),
							],
						]);
					}
				}
			}
		}

		return $data;
	}

	public static function getPullTagName($messageId)
	{
		return 'MAILMESSAGEREADED'.$messageId;
	}

	public static function getRequiredFieldNamesForEvent($eventName)
	{
		if ($eventName === static::EVENT_DELETE_MESSAGES)
		{
			return array('HEADER_MD5', 'MESSAGE_ID', 'MAILBOX_USER_ID');
		}

		return [];
	}
}