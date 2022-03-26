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
			'icon_src' => '/bitrix/templates/landing24/assets/vendor/icon/',
			'icon_vendors' => [
				'fa',
				'fat',
				'fal',
				'far',
				'fas',
				'fab',
				'et-icon',
				'hs-icon',
				'icon-christmas',
				'icon-clothes',
				'icon-communication',
				'icon-education',
				'icon-electronics',
				'icon-finance',
				'icon-food',
				'icon-furniture',
				'icon-hotel-restaurant',
				'icon-media',
				'icon-medical',
				'icon-music',
				'icon-real-estate',
				'icon-science',
				'icon-sport',
				'icon-transport',
				'icon-travel',
				'icon-weather',
				'icon',
			],
			'icon_vendors_config' => [
				'fat' => [
					'class_prefix' => 'fa',
				],
				'fal' => [
					'class_prefix' => 'fa',
				],
				'far' => [
					'class_prefix' => 'fa',
				],
				'fas' => [
					'class_prefix' => 'fa',
				],
				'fab' => [
					'class_prefix' => 'fa',
				],
			],
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