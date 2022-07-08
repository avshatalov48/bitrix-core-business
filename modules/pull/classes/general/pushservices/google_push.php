<?

class CGoogleMessage extends CPushMessage
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
		$batch .= "Content-length: " . \Bitrix\Main\Text\BinaryString::getLength($data) . "\r\n";
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
		$payloadLength = \Bitrix\Main\Text\BinaryString::getLength($jsonPayload);
		if ($payloadLength > self::DEFAULT_PAYLOAD_MAXIMUM_SIZE)
		{
			$text = $this->text;
			$useSenderText = false;
			if(array_key_exists("senderMessage", $this->customProperties))
			{
				$useSenderText = true;
				$text = $this->customProperties["senderMessage"];
			}
			$maxTextLength = $nTextLen = \Bitrix\Main\Text\BinaryString::getLength($text) - ($payloadLength - self::DEFAULT_PAYLOAD_MAXIMUM_SIZE);
			if ($maxTextLength <= 0)
			{
				return false;
			}
			while (\Bitrix\Main\Text\BinaryString::getLength($text = mb_substr($text, 0, --$nTextLen)) > $maxTextLength) ;
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

class CGooglePush extends CPushService
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
	 * @return bool|string
	 */
	public function getBatch($messages = Array())
	{
		$arGroupedMessages = self::getGroupedByAppID($messages);
		if (is_array($arGroupedMessages) && count($arGroupedMessages) <= 0)
		{
			return false;
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
	 * @return CGoogleMessage
	 */
	function getMessageInstance($token)
	{
		return new CGoogleMessage($token);
	}

	public static function shouldBeSent($messageRowData)
	{
		return true;
	}
}

class CGooglePushInteractive extends CGooglePush
{
	function __construct()
	{
		parent::__construct();
		$this->allowEmptyMessage = true;
	}

}

?>