<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class Mail
{
	protected const LAST_SEND_MESSAGE = 'last_send_mail_message';
	protected const LAST_SEND_NOTIFICATION = 'last_send_mail_notification';
	protected const SEND_DELAY_OPTION_NAME = 'send_mail_delay_seconds';
	protected const MODULE_ID = 'im';

	protected const DEFAULT_SEND_DELAY = 600;

	/**
	 * todo: Make private later
	 * @return array
	 */
	public function getMessageIdsToSend(): array
	{
		$lastSend = (int)Option::get(static::MODULE_ID, self::LAST_SEND_MESSAGE, 0);

		return $this->getMessageIdsToSendByType(\IM_MESSAGE_PRIVATE, $lastSend);
	}

	/**
	 * todo: Make private later
	 * @return array
	 */
	public function getNotificationIdsToSend(?int $limit = null): array
	{
		$lastSend = (int)Option::get(static::MODULE_ID, self::LAST_SEND_NOTIFICATION, 0);

		return $this->getMessageIdsToSendByType(\IM_MESSAGE_SYSTEM, $lastSend, $limit);
	}

	protected function getMessageIdsToSendByType(string $type, int $lastSend, ?int $limit = null): array
	{
		$sendDelayInSeconds = (int)Option::get(static::MODULE_ID, self::SEND_DELAY_OPTION_NAME, self::DEFAULT_SEND_DELAY);
		$sendDelayInSeconds = abs($sendDelayInSeconds);
		$readDeadline = (new DateTime())->add("-{$sendDelayInSeconds} seconds");
		$query = MessageUnreadTable::query()
			->setSelect(['MESSAGE_ID'])
			->where('MESSAGE_ID', '>', $lastSend)
			->where('CHAT_TYPE', $type)
			->where('DATE_CREATE', '<', $readDeadline)
		;

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		return $query
			->fetchCollection()
			->getMessageIdList()
		;
	}
}