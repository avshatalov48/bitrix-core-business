<?php

namespace Bitrix\Main\Component;

use Bitrix\Main\Security;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;

class ParameterSigner
{
	/**
	 * @param string $componentName
	 * @param array $parameters
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function signParameters($componentName, $parameters)
	{
		$signer = new Security\Sign\Signer;

		return $signer->sign(
			base64_encode(serialize($parameters)),
			self::refineComponentName($componentName)
		);
	}

	/**
	 * @param string $componentName
	 * @param string $signedParameters
	 *
	 * @return array
	 * @throws Security\Sign\BadSignatureException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function unsignParameters($componentName, $signedParameters)
	{
		$signer = new Security\Sign\Signer;

		$unsignedParameters = $signer->unsign(
			$signedParameters,
			self::refineComponentName($componentName)
		);

		$decoded = base64_decode($unsignedParameters);
		if ($decoded === false)
		{
			return [];
		}

		return static::unserialize($decoded);
	}

	private static function unserialize(string $str)
	{
		return unserialize($str, ['allowed_classes' => [
			DateTime::class,
			Date::class,
			Uri::class,
			\DateTime::class,
			\DateTimeZone::class,
		]]);
	}

	protected static function refineComponentName($componentName)
	{
		if (!is_string($componentName))
		{
			return null;
		}

		return str_replace(':', '', $componentName);
	}
}
