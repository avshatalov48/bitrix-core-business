<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\Json;

Loc::loadLanguageFile(__FILE__);

/**
 * Class AgreementLink
 * @package Bitrix\Main\UserConsent
 */
class AgreementLink
{
	const SIGN_SALT = 'user_consent';

	/** @var array $errors Errors. */
	protected static $errors = [];

	/**
	 * Return Uri parameters.
	 *
	 * @param integer $agreementId Agreement ID.
	 * @param array $replace Replace data.
	 * @param string $baseUri Base Uri.
	 * @return string
	 */
	public static function getUri($agreementId, array $replace = [], $baseUri = '')
	{
		self::clearErrors();

		$agreement = new Agreement($agreementId, $replace);
		$signer = new Signer();
		$data = [
			'id' => $agreement->getId(),
			'replace' => $replace,
		];
		$data = Json::encode($data);
		$parameters = [
			'data' => base64_encode($data),
			'sec' => base64_encode($signer->getSignature($data, self::SIGN_SALT)),
		];
		$uri = new Uri($baseUri);
		$uri->addParams($parameters);

		return $uri->getLocator();
	}

	/**
	 * Replace template by data.
	 *
	 * @param array $parameters Uri parameters.
	 * @return Agreement
	 */
	public static function getAgreementFromUriParameters(array $parameters = [])
	{
		self::clearErrors();

		$data = $parameters['data'];
		$sec = $parameters['sec'];
		if (!$data || !$sec || !is_string($data) || !is_string($sec))
		{
			self::$errors[] = new Error('Parameters not found', 1);
			return null;
		}

		$data = base64_decode($data);
		$sec = base64_decode($sec);
		if (!$data || !$sec)
		{
			self::$errors[] = new Error('Can not decode parameters', 2);
			return null;
		}

		try
		{
			$signer = new Signer();
			if (!$signer->validate($data, $sec, self::SIGN_SALT))
			{
				self::$errors[] = new Error('Parameters signature is not valid', 3);
				return null;
			}
		}
		catch (\Exception $exception)
		{
			self::$errors[] = new Error('Parameters signature error: ' . $exception->getMessage(), 7);
			return null;
		}

		try
		{
			$data = Json::decode($data);
		}
		catch (\Exception)
		{
			$data = null;
		}
		if (!$data || !isset($data['id']) || !isset($data['replace']))
		{
			self::$errors[] = new Error('Decode data parameters failed', 6);
			return null;
		}

		$agreement = new Agreement($data['id'], $data['replace']);
		if (!$agreement->isExist())
		{
			self::$errors[] = new Error('Agreement is not exist', 4);
			return null;
		}
		if (!$agreement->isActive())
		{
			self::$errors[] = new Error('Agreement is not active', 5);
			return null;
		}

		return $agreement;
	}

	protected static function clearErrors()
	{
		self::$errors = [];
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public static function getErrors()
	{
		return self::$errors;
	}
}
