<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */

namespace Bitrix\Seo\Engine;

use Bitrix\Main\Loader;
use Bitrix\Seo\Engine;
use Bitrix\Seo\IEngine;

if (!defined("BITRIX_CLOUD_ADV_URL"))
{
	define("BITRIX_CLOUD_ADV_URL", 'https://cloud-adv.bitrix.info');
}

if (!defined("SEO_BITRIX_API_URL"))
{
	define("SEO_BITRIX_API_URL", BITRIX_CLOUD_ADV_URL . "/rest/");
}

class Bitrix extends Engine implements IEngine
{
	const ENGINE_ID = 'bitrix';

	protected $engineId = 'bitrix';

	const API_URL = SEO_BITRIX_API_URL;

	public function __construct()
	{
		$this->findEngine();
		if ($this->engine)
		{
			parent::__construct();
		}
	}

	protected function findEngine(): void
	{
		$this->engine = static::getEngine($this->engineId);
	}

	/**
	 * Checks if domain is registered.
	 *
	 * @return bool
	 */
	public function isRegistered(): bool
	{
		$this->findEngine();

		return (bool) $this->engine;
	}

	public function getInterface()
	{
		$this->findEngine();

		if (!$this->engine || !Loader::includeModule('socialservices'))
		{
			return null;
		}

		if ($this->authInterface === null)
		{
			$this->authInterface =
				new \CBitrixSeoOAuthInterface($this->engine['CLIENT_ID'], $this->engine['CLIENT_SECRET']);
		}

		return $this->authInterface;
	}

	public function setAuthSettings($settings = null): void
	{
		if (is_array($settings) && array_key_exists("expires_in", $settings))
		{
			$settings["expires_in"] += time();
		}

		$this->engineSettings['AUTH'] = $settings;
		$this->saveSettings();
	}
}