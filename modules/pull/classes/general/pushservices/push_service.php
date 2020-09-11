<?php

use Bitrix\Main\Text\Encoding;

abstract class CPushService
{
	protected $allowEmptyMessage = true;
	const DEFAULT_EXPIRY = 14400;

	protected function getBatchWithModifier($appMessages = Array(), $modifier = "")
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
					$message->setCustomProperty('target', md5($messageArray["USER_ID"] . CMain::GetServerUniqID()));

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
			$appModifier = ";tkey=" . $appID . ";";
			$batch = $appModifier . $batch;
		}

		if ($batch == '')
		{
			return $batch;
		}

		return $modifier . $batch;
	}

	protected static function getGroupedByServiceMode($arMessages)
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
				$groupedMessages[$mode][$app_id][$keyToken][] = $mess;
			}
		}

		return $groupedMessages;
	}

	protected static function getGroupedByAppID($arMessages)
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

	/**
	 * @param string $token
	 * @return CPushMessage
	 */
	abstract function getMessageInstance($token);
	abstract static function shouldBeSent($messageRowData);
	abstract function getBatch($messages);
}