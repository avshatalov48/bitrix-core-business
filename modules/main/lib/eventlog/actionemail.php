<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Main\EventLog;

class ActionEmail extends Action
{
	const EVENT_TYPE = 'EVENT_LOG_NOTIFICATION';

	public function __construct($recipient, $text)
	{
		parent::__construct(Action::TYPE_EMAIL, $recipient, $text);
	}

	/**
	 * @inheritDoc
	 */
	public function send(Notification $notification)
	{
		$site = \CSite::GetDefSite();

		$fields = static::getNotificationFields($notification);
		$fields["EMAIL"] = $this->getRecipient();
		$fields["ADDITIONAL_TEXT"] = $this->getText();

		\Bitrix\Main\Mail\Event::send([
			'EVENT_NAME' => self::EVENT_TYPE,
			'C_FIELDS' => $fields,
			'LID' => $site,
		]);
	}
}
