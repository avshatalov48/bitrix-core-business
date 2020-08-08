<?php

use Bitrix\Main\Text\Encoding;

abstract class CPushService
{
	protected $allowEmptyMessage = true;
	const DEFAULT_EXPIRY = 14400;

	protected function getBatchWithModifier($appMessages = Array(), $modifier = "")
	{
		global $APPLICATION;
		$batch = "";
		if (!is_array($appMessages) || count($appMessages) <= 0)
		{
			return $batch;
		}
		foreach ($appMessages as $appID => $arMessages)
		{
			$appModifier = ";tkey=" . $appID . ";";
			foreach ($arMessages as $token => $messages)
			{
				if (!count($messages))
				{
					continue;
				}
				$mess = 0;
				$messCount = count($messages);
				while ($mess < $messCount)
				{
					/**
					 * @var CPushMessage $message ;
					 */

					$messageArray = $messages[$mess];
					if (
						(!$this->allowEmptyMessage && trim($messageArray["MESSAGE"]) == '')
						|| !static::shouldBeSent($messageArray)
					)
					{
							$mess++;
						continue;
					}

					$message = static::getMessageInstance($token);
					$id = random_int(1, 10000);
					$message->setCustomIdentifier($id);
					$text = \Bitrix\Main\Text\Encoding::convertEncoding($messageArray["MESSAGE"], SITE_CHARSET, "utf-8");
					$title = \Bitrix\Main\Text\Encoding::convertEncoding($messageArray["TITLE"], SITE_CHARSET, "utf-8");
					$message->setSound('');
					$message->setText($text);
					$message->setTitle($title);
					if ($text <> '')
					{
						$message->setSound(
							($messageArray["SOUND"] <> '')
								? $messageArray["SOUND"]
								: "default"
						);
					}

					if ($messages[$mess]["CATEGORY"])
					{
						$message->setCategory($messages[$mess]["CATEGORY"]);
					}

					if (array_key_exists("EXPIRY", $messageArray))
					{
						$expiry = intval($messageArray["EXPIRY"]);
						$message->setExpiry((intval($expiry) > 0)
							? intval($expiry)
							: self::DEFAULT_EXPIRY
						);
					}

					if ($messageArray["PARAMS"])
					{
						$message->setCustomProperty(
							'params',
							is_array($messageArray["PARAMS"])
								? json_encode($messageArray["PARAMS"])
								: $messageArray["PARAMS"]
						);
					}

					if (is_array($messageArray["ADVANCED_PARAMS"]))
					{
						$messageArray["ADVANCED_PARAMS"] = \Bitrix\Main\Text\Encoding::convertEncoding($messageArray["ADVANCED_PARAMS"], SITE_CHARSET, "UTF-8");
						if(array_key_exists("senderMessage",$messageArray["ADVANCED_PARAMS"]))
						{
							$message->setText("");
						}
						
						foreach ($messageArray["ADVANCED_PARAMS"] as $param => $value)
						{
							$message->setCustomProperty($param, $value);
						}
					}
					$message->setCustomProperty('target', md5($messages[$mess]["USER_ID"] . CMain::GetServerUniqID()));
					$badge = (int)$messages[$mess]["BADGE"];
					if (array_key_exists("BADGE", $messages[$mess]) && $badge >= 0)
					{
						$message->setBadge($badge);
					}

					if ($batch <> '')
					{
						$batch .= ";";
					}

					$messageBatch = $message->getBatch();
					if($messageBatch && $messageBatch <> '')
					{
						$batch .= $messageBatch;
					}

					$mess++;
				}
			}
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

	abstract function getMessageInstance($token);
	abstract static function shouldBeSent($messageRowData);
	abstract function getBatch($messages);
}