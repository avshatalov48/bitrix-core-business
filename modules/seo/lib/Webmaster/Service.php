<?php

namespace Bitrix\Seo\Webmaster;

use Bitrix\Main\Application;
use Bitrix\Seo\Retargeting;

/**
 * Class Service
 *
 * @package Bitrix\Seo\LeadAds
 */
class Service extends Retargeting\Service
{
	public const GROUP = 'webmaster';
	public const TYPE_GOOGLE = 'google';
	public const METHOD_PREFIX = 'webmaster';

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [
			static::TYPE_GOOGLE,
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		return self::METHOD_PREFIX;
	}

	/**
	 * Get list of added sites with statuses
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getSites(): array
	{
		$engine = new Engine\Google();
		$engine->setService(static::getInstance());
		$response = $engine->getSites();

		if (!$response->isSuccess())
		{
			return ['error' => $response->getErrors()];
		}

		$result = [];
		$sites = $response->getData();

		$sites = $sites['siteEntry'] ?? [];
		foreach ($sites as $siteInfo)
		{
			$siteUrlInfo = parse_url($siteInfo['siteUrl']);
			if ($siteUrlInfo)
			{
				$errors = [];
				$hostKey = \CBXPunycode::toASCII($siteUrlInfo["host"], $errors);
				if (count($errors) > 0)
				{
					$hostKey = $siteUrlInfo["host"];
				}

				$result[$hostKey] = [
					'binded' => $siteInfo["permissionLevel"] !== "siteRestrictedUser",
					'verified' => (
						$siteInfo["permissionLevel"] !== "siteRestrictedUser"
						&& $siteInfo["permissionLevel"] !== "siteUnverifiedUser"
					),
				];
			}
		}

		return $result;
	}

	/**
	 * Add site to webmaster
	 * @param string $domain - site domain
	 * @param string $dir - subdir
	 * @return array|true[]
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function addSite(string $domain, string $dir = '/'): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? "https://" : "http://";

		$engine = new Engine\Google();
		$engine->setService(static::getInstance());
		$response = $engine->addSite($protocol . $domain . $dir);

		if (!$response->isSuccess())
		{
			return ['error' => implode(',', $response->getErrorMessages())];
		}

		$result = $response->getData();
		if ($result['errors'])
		{
			return ['error' => $result['errors']['message']];
		}

		return ['result' => true];
	}

	/**
	 * Get token-string for naming verify file
	 * @param string $domain - site domain
	 * @param string $dir - subdir
	 * @return array|string[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getVerifyToken(string $domain, string $dir = '/'): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? "https://" : "http://";
		$data = [
			"site" => [
				"identifier" => $protocol . $domain . $dir,
				"type" => "SITE",
			],
			"verificationMethod" => "FILE",
		];

		$engine = new Engine\Google();
		$engine->setService(static::getInstance());
		$response = $engine->getVerifyToken($data);

		if (!$response->isSuccess())
		{
			return ['error' => implode(',', $response->getErrorMessages())];
		}

		$result = $response->getData();
		if (!$result || !$result["token"])
		{
			return ['error' => 'empty response'];
		}
		if ($result['errors'])
		{
			return ['error' => $result['errors']['message']];
		}

		return ['token' => $result["token"]];
	}

	/**
	 * Pass site to verify
	 * @param string $domain - site domain
	 * @param string $dir - subdir
	 * @return array|string[]|true[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function verifySite(string $domain, string $dir = '/'): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? "https://" : "http://";
		$data = [
			"site" => [
				"identifier" => $protocol . $domain . $dir,
				"type" => "SITE",
			],
		];

		$engine = new Engine\Google();
		$engine->setService(static::getInstance());
		$response = $engine->verifySite($data);

		if (!$response->isSuccess())
		{
			return ['error' => implode(',', $response->getErrorMessages())];
		}

		$result = $response->getData();
		if (!$result || !$result["token"])
		{
			return ['error' => 'empty response'];
		}
		if ($result['errors'])
		{
			return ['error' => $result['errors']['message']];
		}

		return ['result' => true];
	}

	public static function canUseMultipleClients()
	{
		return false;
	}
}

