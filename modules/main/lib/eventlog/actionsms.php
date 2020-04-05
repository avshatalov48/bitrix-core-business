<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog;

class ActionSms extends Action
{
	const EVENT_TYPE = 'SMS_EVENT_LOG_NOTIFICATION';

	public function __construct($recipient, $text)
	{
		parent::__construct(Action::TYPE_SMS, $recipient, $text);
	}

	/**
	 * @inheritDoc
	 */
	public function send(Notification $notification)
	{
		$site = \CSite::GetDefSite();

		$fields = static::getNotificationFields($notification);
		$fields["PHONE_NUMBER"] = $this->getRecipient();
		$fields["ADDITIONAL_TEXT"] = $this->getText();

		$sms = new \Bitrix\Main\Sms\Event(self::EVENT_TYPE, $fields);
		$sms->setSite($site);
		$sms->send();
	}
}
