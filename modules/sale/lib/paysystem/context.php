<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main;

/**
 * Class Context
 *
 * @package Bitrix\Sale\PaySystem
 */
final class Context
{
	private $url;

	/**
	 * Context constructor.
	 */
	public function __construct()
	{
		$this->url = $this->getCurrentUrl();
	}

	/**
	 * @param string $url
	 */
	public function setUrl(string $url): void
	{
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @return string
	 */
	private function getCurrentUrl(): string
	{
		$request = Main\Application::getInstance()->getContext()->getRequest();

		$requestUri = null;
		$host = null;
		if (class_exists('\LandingPubComponent'))
		{
			$landingInstance = \LandingPubComponent::getMainInstance();
			if ($landingInstance)
			{
				$context = Main\Context::getCurrent();

				$realFilePath = $context->getServer()->get('REAL_FILE_PATH');
				if (!$realFilePath)
				{
					$realFilePath = $_SERVER['REAL_FILE_PATH'] ?? null;
				}
				if (!$realFilePath)
				{
					$realFilePath = $context->getServer()->get('SCRIPT_NAME');
				}

				$realFilePath = str_replace('/index.php', '/', $realFilePath);
				$requestUri = $request->getRequestUri();

				$landingUrl = \Bitrix\Landing\Site::getPublicUrl($landingInstance['SITE_ID']);
				if (mb_strpos($landingUrl, $realFilePath) === false)
				{
					$requestUri = str_replace($realFilePath.$landingInstance['SITE_ID'], '', $requestUri);
				}

				$uri = new Main\Web\Uri($landingUrl);
				$host = $uri->getHost();
			}
		}

		if (!$host)
		{
			$host = $request->getHttpHost();
		}

		if (!$requestUri)
		{
			$requestUri = $request->getRequestUri();
		}

		return ($request->isHttps() ? "https://" : "http://").$host.$requestUri;
	}
}
