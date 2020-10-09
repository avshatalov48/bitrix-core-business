<?php

namespace Bitrix\Main\Component;


use Bitrix\Main\Security;
use Bitrix\Main\SystemException;
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

		return static::unserialize($decoded, $componentName);
	}

	private static function unserialize(string $str, string $componentName)
	{
		$data = unserialize($str, ['allowed_classes' => [
			DateTime::class,
			Date::class,
			Uri::class,
			\DateTime::class,
			\DateTimeZone::class,
		]]);

		$someObjects = strpos(print_r($data, true), '__PHP_Incomplete_Class') !== false;
		if ($someObjects)
		{
			$data = unserialize($str);

			$exception = new SystemException("There is object in parameters {$componentName} and it's unsafe and it's going to be deprecated soon.");
			trigger_error($exception->getMessage(), E_USER_DEPRECATED);
			$application = \Bitrix\Main\Application::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);
		}

		return $data;
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