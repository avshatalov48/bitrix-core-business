<?php

namespace Bitrix\Pull\Push\Message;

class FirebaseAndroidMessage extends GoogleMessage
{
	function getPayload(): string
	{
		$customProperties = json_encode($this->customProperties, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		$deviceToken = "";
		if (count($this->deviceTokens) > 0)
		{
			$deviceToken = $this->deviceTokens[0];
		}

		$data = [
			"message" => [
				"data" => [
					'contentTitle' => $this->title,
					"contentText" => $this->text,
					"badge" => (string)$this->badge,
					"messageParams" => $customProperties,
					"category" => $this->getCategory(),
					"sound" => $this->getSound(),
				],
				"android" => [
					"ttl" => $this->expiryValue . "s",
					"priority" => "high",
				],
				"token" => $deviceToken,
			],
		];

		return $this->strippedPayload($data);
	}
}