<?php

namespace Bitrix\Pull\Push\Message;

class AppleMessage extends BaseMessage
{
	protected const DEFAULT_PAYLOAD_MAXIMUM_SIZE = 2048;
	protected const APPLE_RESERVED_NAMESPACE = 'aps';
	protected const JSON_OPTIONS = JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE;

	protected $_bAutoAdjustLongPayload = true;
	protected $payloadMaxSize;

	public function __construct($sDeviceToken = null, $maxPayloadSize = 2048)
	{
		if (isset($sDeviceToken))
		{
			$this->addRecipient($sDeviceToken);
		}

		$this->payloadMaxSize = (int)$maxPayloadSize ?: self::DEFAULT_PAYLOAD_MAXIMUM_SIZE;
	}

	public function setAutoAdjustLongPayload($bAutoAdjust)
	{
		$this->_bAutoAdjustLongPayload = (boolean)$bAutoAdjust;
	}

	public function getAutoAdjustLongPayload()
	{
		return $this->_bAutoAdjustLongPayload;
	}

	protected function getAlertData()
	{
		$this->text = $this->customProperties["senderMessage"] ?? $this->text;
		unset($this->customProperties["senderMessage"]);
		$this->title = $this->customProperties["senderName"] ?? $this->title ?? "";
		unset($this->customProperties["senderName"]);
		if ($this->text != null && $this->text != "")
		{
			return [
				'body' => $this->text,
				'title'=>  $this->title,
			];
		}

		return [];
	}

	protected function _getPayload()
	{
		$alertData = $this->getAlertData();
		if(!empty($alertData))
		{
			$aPayload[self::APPLE_RESERVED_NAMESPACE] = [
				'alert' => $alertData
			];

			$aPayload[self::APPLE_RESERVED_NAMESPACE]['mutable-content'] = 1;
		}
		else
		{
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['content-available'] = 1;
		}

		if (isset($this->category))
		{
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['category'] = (string)$this->category;
		}

		if (isset($this->badge) && $this->badge >= 0)
		{
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['badge'] = (int)$this->badge;
		}
		if (isset($this->sound) && $this->sound <> '')
		{
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['sound'] = (string)$this->sound;
		}

		if (is_array($this->customProperties))
		{
			foreach ($this->customProperties as $sPropertyName => $mPropertyValue)
			{
				$aPayload[$sPropertyName] = $mPropertyValue;
			}
		}

		return $aPayload;
	}

	public function getPayload()
	{
		$sJSONPayload = str_replace(
			'"' . self::APPLE_RESERVED_NAMESPACE . '":[]',
			'"' . self::APPLE_RESERVED_NAMESPACE . '":{}',
			json_encode($this->_getPayload(), static::JSON_OPTIONS)
		);
		$nJSONPayloadLen = strlen($sJSONPayload);
		if ($nJSONPayloadLen <= $this->payloadMaxSize)
		{
			return $sJSONPayload;
		}
		if (!$this->_bAutoAdjustLongPayload)
		{
			return false;
		}

		$text = $this->text;
		$useSenderText = false;
		if(array_key_exists("senderMessage", $this->customProperties))
		{
			$useSenderText = true;
			$text = $this->customProperties["senderMessage"];
		}
		$nMaxTextLen = $nTextLen = strlen($text) - ($nJSONPayloadLen - $this->payloadMaxSize);
		if ($nMaxTextLen <= 0)
		{
			return false;
		}

		while (strlen($text) > $nMaxTextLen)
		{
			$text = substr($text, 0, --$nTextLen);
		}
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

	public function getBatch()
	{
		$arTokens = $this->getRecipients();
		$sPayload = $this->getPayload();

		if (!$sPayload)
		{
			return false;
		}

		$nPayloadLength = strlen($sPayload);
		$totalBatch = "";
		foreach ($arTokens as $token)
		{
			$sDeviceToken = $token;

			$sRet = pack('CNNnH*',
				1,
				$this->getCustomIdentifier(),
				$this->getExpiry() > 0 ? time() + $this->getExpiry() : 0,
				32,
				$sDeviceToken)
			;
			$sRet .= pack('n', $nPayloadLength);
			$sRet .= $sPayload;
			if ($totalBatch <> '')
			{
				$totalBatch .= ";";
			}
			$totalBatch .= base64_encode($sRet);
		}

		return $totalBatch;
	}

}