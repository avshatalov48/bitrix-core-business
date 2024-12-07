<?php
namespace Bitrix\MessageService\Sender;

use Bitrix\Main;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Result;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Message;
use Bitrix\MessageService\MessageType;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\MessageService\Sender\Result\SendMessage;

class SmsManager
{
	public const ON_MESSAGE_SUCCESSFULLY_SENT_EVENT = 'OnMessageSuccessfullySent';

	private static $senders;

	/**
	 * @return Base[] List of senders.
	 */
	public static function getSenders()
	{
		if (self::$senders === null)
		{
			self::$senders = [];

			if (Sms\Twilio::isSupported())
			{
				self::$senders[] = new Sms\Twilio();
			}
			if (Sms\Twilio2::isSupported())
			{
				self::$senders[] = new Sms\Twilio2();
			}
			if (Sms\SmsLineBy::isSupported())
			{
				self::$senders[] = new Sms\SmsLineBy();
			}
			if (Sms\MfmsRu::isSupported())
			{
				self::$senders[] = new Sms\MfmsRu();
			}
			if (Sms\Rest::isSupported())
			{
				self::$senders[] = new Sms\Rest();
			}
			if (Sms\SmscUa::isSupported())
			{
				self::$senders[] = new Sms\SmscUa();
			}
			if (Sms\ISmsCenter::isSupported())
			{
				self::$senders[] = new Sms\ISmsCenter();
			}

			$sender = new Sms\SmsRu();
			if (Sms\SmsRu::isSupported() || $sender->isRegistered())
			{
				self::$senders[] = $sender;
			}

			$sender = new Sms\SmsAssistentBy();
			if (Sms\SmsAssistentBy::isSupported() || $sender->isRegistered())
			{
				self::$senders[] = $sender;
			}

			$sender = new Sms\SmsEdnaru();
			if (Sms\SmsEdnaru::isSupported() || $sender->isRegistered())
			{
				self::$senders[] = $sender;
			}

			$sender = new Sms\Ednaru();
			if (Sms\Ednaru::isSupported() || $sender->isRegistered())
			{
				self::$senders[] = $sender;
			}

			$sender = new Sms\EdnaruImHpx();
			if (Sms\EdnaruImHpx::isSupported() || $sender->isRegistered())
			{
				self::$senders[] = $sender;
			}

			self::fireSendersEvent();
		}
		return self::$senders;
	}

	private static function fireSendersEvent()
	{
		$event = new Event('messageservice', 'onGetSmsSenders');
		$event->send();
		foreach ($event->getResults() as $result)
		{
			if ($result->getType() === EventResult::ERROR)
			{
				continue;
			}
			$resultData = $result->getParameters();
			if (is_array($resultData))
			{
				foreach ($resultData as $sender)
				{
					if (
						$sender instanceof Base
						&& $sender->getType() === MessageType::SMS
						&& $sender::isSupported()
					)
					{
						self::$senders[] = $sender;
					}
				}
			}
		}
	}

	/**
	 * @return array Simple list of senders, array(id => name)
	 */
	public static function getSenderSelectList()
	{
		$select = array();
		foreach (static::getSenders() as $sender)
		{
			$select[$sender->getId()] = $sender->getName();
		}
		return $select;
	}

	/**
	 * @return array Sender list information.
	 */
	public static function getSenderInfoList()
	{
		$info = array();
		foreach (static::getSenders() as $sender)
		{
			$info[] = array(
				'id' => $sender->getId(),
				'type' => $sender->getType(),
				'isTemplatesBased' => ($sender->isConfigurable() && $sender->isTemplatesBased()),
				'name' => $sender->getName(),
				'shortName' => $sender->getShortName(),
				'canUse' => $sender->canUse()
			);
		}
		return $info;
	}

	/**
	 * @param $id
	 * @return Base|null Sender instance.
	 */
	public static function getSenderById($id)
	{
		foreach (static::getSenders() as $sender)
		{
			if ($sender->getId() === $id)
			{
				return $sender;
			}
		}
		return null;
	}

	/**
	 * Get default SMS sender.
	 * @return Base
	 */
	public static function getDefaultSender()
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();

		if ($region === 'ru')
		{
			return static::getSenderById(\Bitrix\MessageService\Sender\Sms\SmsRu::ID);
		}
		if ($region === 'by')
		{
			return static::getSenderById(\Bitrix\MessageService\Sender\Sms\SmsAssistentBy::ID);
		}

		return static::getSenders()[0];
	}

	/**
	 * @return bool Can use SMS transport.
	 */
	public static function canUse()
	{
		return static::getUsableSender() !== null;
	}

	/**
	 * @return string Manage url
	 */
	public static function getManageUrl()
	{
		/** @var BaseConfigurable $defaultProvider */
		$defaultProvider = static::getDefaultSender();
		return $defaultProvider instanceof BaseConfigurable ? $defaultProvider->getManageUrl() : '';
	}

	/**
	 * Get first Sender which is ready to use it.
	 * @return Base|null Sender instance.
	 */
	public static function getUsableSender()
	{
		foreach (static::getSenders() as $sender)
		{
			if ($sender->canUse())
			{
				return $sender;
			}
		}
		return null;
	}

	/**
	 * @return BaseConfigurable[]
	 */
	public static function getRegisteredSenderList(): array
	{
		$senderList = [];
		foreach (static::getSenders() as $sender)
		{
			if (
				$sender instanceof BaseConfigurable
				&& $sender->isRegistered()
			)
			{
				$senderList[] = $sender;
			}
		}

		return $senderList;
	}

	/**
	 * @param array $messageFields
	 * @param Base|null $sender
	 * @return Message
	 * @throws Main\ArgumentTypeException
	 */
	public static function createMessage(array $messageFields, Base $sender = null)
	{
		if (!$sender && !isset($messageFields['SENDER_ID']))
		{
			$sender = static::getUsableSender();
		}

		if ($sender === null)
		{
			$message = new Message($sender);
			$message->setError(new Main\Error('There are no registered SMS providers.'));
		}

		if (isset($messageFields['MESSAGE_TO']))
		{
			$normalizedTo = \NormalizePhone($messageFields['MESSAGE_TO']);
			if ($normalizedTo)
			{
				$messageFields['MESSAGE_TO'] = '+'.$normalizedTo;
			}
		}

		$message = Message::createFromFields($messageFields, $sender);
		$message->setType(MessageType::SMS);

		$sender = $message->getSender();
		if ($sender && !$message->getFrom())
		{
			$message->setFrom($sender->getDefaultFrom());
		}

		return $message;
	}

	/**
	 * @param string $eventName
	 * @param array $fields
	 * @return array<int, Message>
	 */
	public static function createMessageListByTemplate(string $eventName, array $fields = []): array
	{
		$messageList = [];
		$event = new Main\Sms\Event($eventName, $fields);
		$templateMessageListResult = $event->createMessageList();
		if (!$templateMessageListResult->isSuccess())
		{
			return $messageList;
		}

		$templateMessages = $templateMessageListResult->getData();
		$sender = isset($fields['SENDER_ID']) ? static::getSenderById($fields['SENDER_ID']) : static::getUsableSender();

		/** @var \Bitrix\Main\SMS\Message $templateMessage */
		foreach($templateMessages as $templateMessage)
		{
			$message = Message::createFromFields(
				[
					'MESSAGE_FROM' => $fields['DEFAULT_FROM'] ?? $sender->getDefaultFrom(),
					'MESSAGE_TO' => $templateMessage->getReceiver(),
					'MESSAGE_BODY' => $templateMessage->getText(),
				],
				$sender
			);
			$message->setType(MessageType::SMS);
			$messageList[] = $message;
		}

		return $messageList;
	}

	/**
	 * @param array $messageFields
	 * @param Base|null $sender
	 * @return Result|AddResult
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendMessage(array $messageFields, Base $sender = null)
	{
		if (!$sender && isset($messageFields['SENDER_ID']))
		{
			$sender = static::getSenderById($messageFields['SENDER_ID']);

			if ($sender === null)
			{
				return (new Result())->addError(new Main\Error('Incorrect sender id.'));
			}
		}
		$message = static::createMessage($messageFields, $sender);

		if ($message->getError() !== null)
		{
			return (new Result())->addError($message->getError());
		}

		$result = $message->send();

		if ($result->isSuccess())
		{
			(new Event(
				'messageservice',
				static::ON_MESSAGE_SUCCESSFULLY_SENT_EVENT,
				[
					'ID' => $result->getId(),
					'ADDITIONAL_FIELDS' => $messageFields['ADDITIONAL_FIELDS'] ?? [],
				]
			))->send();
		}

		return $result;
	}

	/**
	 * @param array $messageFields
	 * @param Base|null $sender
	 * @return SendMessage
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendMessageDirectly(array $messageFields, Base $sender = null)
	{
		$message = static::createMessage($messageFields, $sender);

		return $message->sendDirectly();
	}

	public static function getMessageStatus($messageId)
	{
		$message = MessageTable::getById($messageId)->fetch();
		if (!$message)
		{
			$result = new Result\MessageStatus();
			$result->setId($messageId);
			$result->addError(new Main\Error('Message not found'));

			return $result;
		}

		/** @var BaseConfigurable $sender */
		$sender = static::getSenderById($message['SENDER_ID']);
		if (!$sender || !$sender->isConfigurable())
		{
			$result = new Result\MessageStatus();
			$result->setId($messageId);
			$result->addError(new Main\Error('Incorrect sender id.'));

			return $result;
		}

		return $sender->getMessageStatus($message);
	}
}