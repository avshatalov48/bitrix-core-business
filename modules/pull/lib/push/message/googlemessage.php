<?php

namespace Bitrix\Pull\Push\Message;

class GoogleMessage extends BaseMessage
{
	const DEFAULT_PAYLOAD_MAXIMUM_SIZE = 4096;
	public function __construct($sDeviceToken = null)
	{
		if (isset($sDeviceToken))
		{
			$this->addRecipient($sDeviceToken);
		}
	}

	/**
	 * Returns batch of the message
	 * @return string
	 */
	public function getBatch()
	{
		$data = $this->getPayload();
		$batch = "Content-type: application/json\r\n";
		$batch .= "Content-length: " . strlen($data) . "\r\n";
		$batch .= "\r\n";
		$batch .= $data;

		return base64_encode($batch);
	}

	public function getPayload(): string
	{
		$data = [
			"data" => [
				'contentTitle' => $this->title,
				"contentText" => $this->text,
				"badge" => $this->badge,
				"messageParams" => $this->customProperties,
				"category" => $this->getCategory(),
				"sound" => $this->getSound(),
			],
			"time_to_live" => $this->expiryValue,
			"priority" => "high",
			"registration_ids" => $this->deviceTokens
		];

		return $this->strippedPayload($data);
	}

	public function strippedPayload($data): string {
		$jsonPayload = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		$payloadLength = strlen($jsonPayload);
		if ($payloadLength > self::DEFAULT_PAYLOAD_MAXIMUM_SIZE)
		{
			$text = $this->text;
			$useSenderText = false;
			if(array_key_exists("senderMessage", $this->customProperties))
			{
				$useSenderText = true;
				$text = $this->customProperties["senderMessage"];
			}
			$maxTextLength = $nTextLen = strlen($text) - ($payloadLength - self::DEFAULT_PAYLOAD_MAXIMUM_SIZE);
			if ($maxTextLength <= 0)
			{
				return false;
			}
			while (strlen($text = mb_substr($text, 0, --$nTextLen)) > $maxTextLength) ;
			if($useSenderText)
			{
				$this->setCustomProperty("senderMessage", $text);
			}
			else
			{
				$this->setText($text);
			}


			return $this->getPayload();
		}

		return $jsonPayload;
	}

}