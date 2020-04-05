<?php

namespace Bitrix\Main\Component;


use Bitrix\Main\Security;

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

		return unserialize(base64_decode($unsignedParameters));
	}

	protected static function refineComponentName($componentName)
	{
		return str_replace(':', '', $componentName);
	}
}