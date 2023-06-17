<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Pull\Push\Message\BaseMessage;

abstract class BaseService implements PushService
{
	protected bool $allowEmptyMessage = true;
	const DEFAULT_EXPIRY = 14400;

	protected function getBatchWithModifier($appMessages = Array(), $modifier = ""): string
	{
		$batch = "";
		if (!is_array($appMessages) || count($appMessages) <= 0)
		{
			return $batch;
		}
		foreach ($appMessages as $appID => $tokenMessages)
		{
			foreach ($tokenMessages as $token => $messages)
			{
				foreach ($messages as $messageArray)
				{
					if (
						(!$this->allowEmptyMessage && trim($messageArray["MESSAGE"]) == '')
						|| !static::shouldBeSent($messageArray)
					)
					{
						continue;
					}

					$message = static::getMessageInstance($token);
					$id = random_int(1, 10000);
					$message->setCustomIdentifier($id);
					$message->setFromArray($messageArray);
					$message->setCustomProperty('target', md5($messageArray["USER_ID"] . \CMain::GetServerUniqID()));

					if ($batch <> '')
					{
						$batch .= ";";
					}

					$messageBatch = $message->getBatch();
					if($messageBatch && $messageBatch <> '')
					{
						$batch .= $messageBatch;
					}
				}
			}
			$host = static::getHost();
			$appModifier = ";tkey={$appID};";
			if ($host != '')
			{
				$appModifier .= "h={$host};";
			}
			$batch = $appModifier . $batch;
		}

		if ($batch == '')
		{
			return $batch;
		}

		return $modifier . $batch;
	}

	protected static function getGroupedByServiceMode($arMessages): array
	{
		$groupedMessages = array();
		foreach ($arMessages as $keyToken => $messTokenData)
		{
			$count = count($messTokenData["messages"]);
			for ($i = 0; $i < $count; $i++)
			{
				$mode = $messTokenData["mode"];
				$mess = $messTokenData["messages"][$i];
				$app_id = $mess["APP_ID"];
				$groupedMessages[$mode][$app_id][$keyToken][] = $mess;
			}
		}

		return $groupedMessages;
	}

	protected static function getGroupedByAppID($arMessages): array
	{
		$groupedMessages = array();
		foreach ($arMessages as $keyToken => $messTokenData)
		{
			$count = count($messTokenData["messages"]);
			for ($i = 0; $i < $count; $i++)
			{
				$mode = $arMessages[$keyToken]["mode"];
				$mess = $messTokenData["messages"][$i];
				$app_id = $mess["APP_ID"];
				$groupedMessages[$app_id][$keyToken][] = $mess;
			}
		}

		return $groupedMessages;
	}

	private static function getHost(): string {
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME)
		{
			return SITE_SERVER_NAME;
		}
		else
		{
			return Option::get("main", "server_name", Context::getCurrent()->getRequest()->getHttpHost());
		}
	}

	abstract function getMessageInstance(string $token): BaseMessage;
	abstract static function shouldBeSent(array $messageRowData): bool;
	abstract function getBatch(array $messages): string;
}