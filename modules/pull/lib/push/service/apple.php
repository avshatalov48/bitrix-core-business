<?php

namespace Bitrix\Pull\Push\Service;

use Bitrix\Pull\Push\Message\AppleMessage;

class Apple extends BaseService
{
	protected int $sandboxModifier = 1;
	protected int $productionModifier = 2;

	/**
	 * Gets the batch for Apple push notification service
	 *
	 * @param array $messages
	 *
	 * @return string
	 */
	public function getBatch(array $messages = []): string
	{
		$arGroupedMessages = self::getGroupedByServiceMode($messages);
		if (empty($arGroupedMessages))
		{
			return '';
		}

		$batch = $this->getProductionBatch($arGroupedMessages["PRODUCTION"]);
		$batch .= $this->getSandboxBatch($arGroupedMessages["SANDBOX"]);

		return $batch;
	}

	/**
	 * Returns message instance
	 */
	function getMessageInstance(string $token): AppleMessage
	{
		return new AppleMessage($token, 2048);
	}

	/**
	 * Gets batch  with ;1; modifier only for sandbox server
	 *
	 * @param $appMessages
	 *
	 * @return string
	 */
	public function getSandboxBatch($appMessages)
	{
		return $this->getBatchWithModifier($appMessages, ";" . $this->sandboxModifier . ";");
	}

	/**
	 * Gets batch  with ;2; modifier only for production server
	 *
	 * @param $appMessages
	 *
	 * @return string
	 */
	public function getProductionBatch($appMessages)
	{
		return $this->getBatchWithModifier($appMessages, ";" . $this->productionModifier . ";");
	}

	public static function shouldBeSent(array $messageRowData): bool
	{
		$params = $messageRowData["ADVANCED_PARAMS"];
		return !($params && !$params["senderName"] && mb_strlen($params["senderMessage"]) > 0);
	}
}