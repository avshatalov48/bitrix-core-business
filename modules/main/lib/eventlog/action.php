<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog;

abstract class Action
{
	const TYPE_EMAIL = 'email';
	const TYPE_SMS = 'sms';

	protected $type;
	protected $recipient;
	protected $text;

	/**
	 * Action constructor.
	 * @param string $type
	 * @param string $recipient
	 * @param string $text
	 */
	public function __construct($type, $recipient, $text)
	{
		$this->type = $type;
		$this->recipient = $recipient;
		$this->text = $text;
	}

	/**
	 * Creates an action by its type.
	 * @param string $type
	 * @param string $recipient
	 * @param string $text
	 * @return Action
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function createByType($type, $recipient, $text)
	{
		switch($type)
		{
			case self::TYPE_EMAIL:
				return new ActionEmail($recipient, $text);
			case self::TYPE_SMS:
				return new ActionSms($recipient, $text);
			default:
				throw new \Bitrix\Main\ArgumentException("Unknown action type", "type");
		}
	}

	/**
	 * Returns a type of the action.
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Returns a receiver.
	 * @return string
	 */
	public function getRecipient()
	{
		return $this->recipient;
	}

	/**
	 * Returns a text.
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Sends a notification.
	 * @param Notification $notification
	 */
	abstract public function send(Notification $notification);

	protected static function getNotificationFields(Notification $notification)
	{
		return [
			"NAME" => $notification->getName(),
			"AUDIT_TYPE_ID" => $notification->getAuditTypeId(),
			"ITEM_ID" => $notification->getItemId(),
			"USER_ID" => $notification->getUserId(),
			"REMOTE_ADDR" => $notification->getRemoteAddr(),
			"USER_AGENT" => $notification->getUserAgent(),
			"REQUEST_URI" => $notification->getRequestUri(),
			"EVENT_COUNT" => $notification->getEventCount(),
		];
	}
}
