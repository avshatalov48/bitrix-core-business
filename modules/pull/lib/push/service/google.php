<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Pull\Push\Message\GoogleMessage;

class Google extends BaseService
{
	function __construct()
	{
		$this->allowEmptyMessage = false;
	}

	/**
	 * Returns the final batch for the Android's push notification
	 *
	 * @param array $messages
	 *
	 * @return string
	 */
	public function getBatch(array $messages = []): string
	{
		$arGroupedMessages = self::getGroupedByAppID($messages);
		if (empty($arGroupedMessages))
		{
			return '';
		}

		$batch = $this->getBatchWithModifier($arGroupedMessages, ";3;");

		if ($batch == '')
		{
			return $batch;
		}

		return $batch;
	}

	/**
	 * Gets message instance
	 * @param $token
	 *
	 * @return GoogleMessage
	 */
	function getMessageInstance($token): GoogleMessage
	{
		return new GoogleMessage($token);
	}

	public static function shouldBeSent($messageRowData): bool
	{
		return true;
	}
}