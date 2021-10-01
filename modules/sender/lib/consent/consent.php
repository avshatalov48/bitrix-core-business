<?php

namespace Bitrix\Sender\Consent;

use Bitrix\Main\Web\Json;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Transport;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Security\Sign\TimeSigner;

/**
 * Class Consent
 * @package Bitrix\Sender\Consent
 */
final class Consent
{
	private const SALT = "SENDER_CONSENT_SALT";
	
	/**
	 * @param string|null $code
	 *
	 * @return Transport\Adapter
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function getTransport(?string $code): Transport\Adapter
	{
		static $transports = [];
		if(!isset($transports[$code]))
		{
			$transports[$code] = Transport\Adapter::create($code);
		}
		return $transports[$code];
	}
	
	/**
	 * check if status equal 'deny' or consent request num is beyond the maximum
	 *
	 * @param string $status consent status
	 * @param int|null $requests int consent requests num
	 *
	 * @param string|null $code
	 *
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function isUnsub(string $status, ?int $requests, ?string $code): bool
	{
		return Consent::getTransport($code)->isConsentAvailable() &&
			(
				$status === ContactTable::CONSENT_STATUS_DENY ||
				Consent::checkIfConsentRequestLimitExceeded($requests, $code) &&
				$status !== ContactTable::CONSENT_STATUS_ACCEPT
			);
	}

	/**
	 * check is consent request num is beyond the maximum
	 *
	 * @param int $requests
	 *
	 * @param string $code
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkIfConsentRequestLimitExceeded(int $requests, string $code): bool
	{
		return  $requests > Consent::getTransport($code)->getConsentMaxRequests();
	}
	
	/**
	 * sign $fields with contact parameters
	 * @param array $fields
	 *
	 * @return string
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function encodeTag(array $fields): string
	{
		if(isset($fields))
		{
			$signer = new TimeSigner();
			$tagString = Json::encode($fields);
			return $signer->sign($tagString,"+ 4 weeks", Consent::SALT);
		}
		
		throw new ArgumentNullException("fields");
	}
	/**
	 * return unsigned tag
	 * @param string $tag
	 *
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Security\Sign\BadSignatureException
	 */
	public static function decodeTag(string $tag)
	{
		$signer = new TimeSigner();
		$tag = $signer->unsign($tag,static::SALT);
		return Json::decode($tag);
	}
}