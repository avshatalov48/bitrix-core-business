<?php
namespace Bitrix\MessageService\Sender;

use Bitrix\Main;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Message;
use Bitrix\MessageService\MessageType;

class SmsManager
{
	private static $senders;

	/**
	 * @return Base[] List of senders.
	 */
	public static function getSenders()
	{
		if (self::$senders === null)
		{
			self::$senders = [];

			if (Sms\SmsRu::isSupported())
			{
				self::$senders[] = new Sms\SmsRu();
			}
			if (Sms\SmsAssistentBy::isSupported())
			{
				self::$senders[] = new Sms\SmsAssistentBy();
			}
			if (Sms\Twilio::isSupported())
			{
				self::$senders[] = new Sms\Twilio();
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

			self::fireSendersEvent();
		}
		return self::$senders;
	}

	private static function fireSendersEvent()
	{
		foreach (Main\EventManager::getInstance()->findEventHandlers(
			'messageservice', 'onGetSmsSenders'
		) as $event
		)
		{
			$result = (array) ExecuteModuleEventEx($event);
			foreach ($result as $sender)
			{
				if (
					$sender instanceof Base
					&&
					$sender->getType() === MessageType::SMS
					&&
					$sender::isSupported()
				)
				{
					self::$senders[] = $sender;
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
		$senders = static::getSenders();
		return $senders[0];
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
		if (!$message->getFrom() && $sender instanceof BaseConfigurable)
		{
			$message->setFrom($sender->getDefaultFrom());
		}

		return $message;
	}

	/**
	 * @param array $messageFields
	 * @param Base|null $sender
	 * @return Main\Entity\AddResult
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendMessage(array $messageFields, Base $sender = null)
	{
		$message = static::createMessage($messageFields, $sender);
		return $message->send();
	}

	/**
	 * @param array $messageFields
	 * @param Base|null $sender
	 * @return Main\Entity\AddResult
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