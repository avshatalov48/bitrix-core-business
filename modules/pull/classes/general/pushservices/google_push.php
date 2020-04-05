<?

class CGoogleMessage extends CPushMessage
{
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

		$data = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		$batch = "Content-type: application/json\r\n";
		$batch .= "Content-length: " . CUtil::BinStrlen($data) . "\r\n";
		$batch .= "\r\n";
		$batch .= $data;

		return base64_encode($batch);
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
	 * @param array $messageData
	 *
	 * @return bool|string
	 */
	public function getBatch($messageData = Array())
	{
		$arGroupedMessages = self::getGroupedByAppID($messageData);
		if (is_array($arGroupedMessages) && count($arGroupedMessages) <= 0)
		{
			return false;
		}

		$batch = $this->getBatchWithModifier($arGroupedMessages, ";3;");

		if (strlen($batch) == 0)
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