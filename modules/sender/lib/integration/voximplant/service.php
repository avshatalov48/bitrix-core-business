<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\VoxImplant;

use Bitrix\Main\Loader;
use Bitrix\Sender\PostingManager;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class Service
{
	/**
	 * Can use.
	 *
	 * @return bool|null
	 */
	public static function canUse()
	{
		if (!Loader::includeModule('voximplant'))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Send.
	 *
	 * @param string $outputNumber Id of the line to perform outgoing call.
	 * @param string $number Number to be called.
	 * @param string $text Text to say.
	 * @param string $voiceLanguage TTS voice (@see: Tts\Language).
	 * @param string $voiceSpeed TTS voice speed (@see Tts\Speed).
	 * @param string $voiceVolume TTS voice volume (@see Tts\Volume).
	 * @return string|null
	 */
	public static function send($outputNumber, $number, $text, $voiceLanguage = '', $voiceSpeed = '', $voiceVolume = '')
	{
		if (!static::canUse())
		{
			return false;
		}

		$result = \CVoxImplantOutgoing::StartInfoCallWithText(
			$outputNumber,
			$number,
			$text,
			$voiceLanguage,
			$voiceSpeed,
			$voiceVolume
		);

		$data = $result->getData();
		if (!is_array($data) || !isset($data['CALL_ID']))
		{
			return null;
		}

		return $data['CALL_ID'];
	}

	/**
	 * Send recordered audio file.
	 *
	 * @param string $outputNumber Id of the line to perform outgoing call.
	 * @param string $number Number to be called.
	 * @param string $fileUrl Audio file to be voiced.
	 * @return string|null
	 */
	public static function sendFile($outputNumber, $number, $fileUrl)
	{
		if (!static::canUse())
		{
			return false;
		}

		$result = \CVoxImplantOutgoing::StartInfoCallWithSound(
			$outputNumber,
			$number,
			$fileUrl
		);

		$data = $result->getData();
		if (!is_array($data) || !isset($data['CALL_ID']))
		{
			return null;
		}

		return $data['CALL_ID'];
	}

	/**
	 * OnInfoCallResult event handler.
	 * @param int  $callId Call id.
	 * @param array $callData Call data.
	 * @return void
	 */
	public static function onInfoCallResult($callId, $callData)
	{
		if (!is_array($callData))
		{
			$callData = array();
		}

		$recipientId = CallLogTable::getRecipientIdByCallId($callId);
		if ($recipientId && isset($callData['RESULT']) && $callData['RESULT'])
		{
			PostingManager::read($recipientId);
		}

		CallLogTable::removeByCallId($callId);
	}

	public static function getFormattedOutputNumber($value)
	{
		static $lines;
		if (null === $lines)
		{
			if (static::canUse())
			{
				$lines = \CVoxImplantConfig::GetPortalNumbers(false);
			}
			else
			{
				$lines = [];
			}
		}

		return $lines[$value] ?: $value;
	}
}