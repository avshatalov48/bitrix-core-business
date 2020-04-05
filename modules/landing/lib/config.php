<?php
namespace Bitrix\Landing;

class Config
{
	/**
	 * Gets default config.
	 * @return array
	 */
	protected static function getDefaultConfig()
	{
		return [
			'js_core_public' => [
				'landing_core'
			],
			'js_core_edit' => [
				'landing_core'
			],
			'disable_namespace' => [],
			'enable_namespace' => [],
			'public_wrapper_block' => true,
			'google_font' => true
		];
	}

	/**
	 * Will loaded local or default config and return it.
	 * @return array
	 */
	protected static function loadConfig()
	{
		static $config = null;

		if ($config === null)
		{
			$config = self::getDefaultConfig();

			$siteId = Manager::getMainSiteId();
			$siteTemplateId = Manager::getTemplateId($siteId);
			$siteTemplatePath = \getLocalPath('templates/' . $siteTemplateId, BX_PERSONAL_ROOT);
			$configPath = Manager::getDocRoot() . $siteTemplatePath . '/.config.php';

			if (file_exists($configPath))
			{
				$config = include_once $configPath;
			}
		}

		return $config;
	}

	/**
	 * Gets value from config by code.
	 * @param string $code Var code.
	 * @return mixed
	 */
	public static function get($code)
	{
		$config = self::loadConfig();

		if (isset($config[$code]))
		{
			return $config[$code];
		}

		return null;
	}
}