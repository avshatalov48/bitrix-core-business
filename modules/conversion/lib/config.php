<?php

namespace Bitrix\Conversion;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class Config
{
	static private $baseCurrency;

	/**
	 * Default currency value from 'currency' module.
	 * If can't get base currency from 'currency' module, returns 'RUB'.
	 *
	 * @return string
	 */
	public static function getDefaultCurrency()
	{
		if (Loader::includeModule('currency'))
		{
			return CurrencyManager::getBaseCurrency();
		}
		return 'RUB';
	}

	static public function getBaseCurrency()
	{
		if (! $currency =& self::$baseCurrency)
		{
			$currency = Option::get('conversion', 'BASE_CURRENCY', self::getDefaultCurrency());
		}

		return $currency;
	}

	/** @internal
	 * @param string $currency - currency code
	 */
	static public function setBaseCurrency($currency)
	{
		if (! $currency)
		{
			$currency = self::getDefaultCurrency();
		}

		self::$baseCurrency = $currency;

		Option::set('conversion', 'BASE_CURRENCY', $currency);
	}



	/** @deprecated */
	static public function convertToBaseCurrency($value, $currency) // TODO remove from sale
	{
		return Utils::convertToBaseCurrency($value, $currency);
	}

	/** @deprecated */
	static public function formatToBaseCurrency($value, $format = null) // TODO remove from sale
	{
		return Utils::formatToBaseCurrency($value, $format);
	}

	/** @deprecated */
	static public function getBaseCurrencyUnit() // TODO remove from sale
	{
		return Utils::getBaseCurrencyUnit();
	}



	static private $modules = array();

	static public function getModules()
	{
		if (! $modules =& self::$modules)
		{
			$default = array('ACTIVE' => ! ModuleManager::isModuleInstalled('sale'));

			foreach (
				array(
					AttributeManager::getTypesInternal(),
					CounterManager::getTypesInternal(),
					RateManager::getTypesInternal(),
				) as $types)
			{
				foreach ($types as $type)
				{
					$modules[$type['MODULE']] = $default;
				}
			}

			if ($modules['sale'])
			{
				$modules['sale']['ACTIVE'] = true;
			}

			$modules = unserialize(Option::get('conversion', 'MODULES', 'a:0:{}'), ['allowed_classes' => false]) + $modules;

			// TODO all modules with attributes must be active
			$modules['conversion'] = $modules['abtest'] = $modules['sender'] = $modules['seo'] = array('ACTIVE' => true);

			ksort($modules);
		}

		return $modules;
	}

	/** @internal */
	static public function setModules(array $modules)
	{
		self::$modules = $modules;
		Option::set('conversion', 'MODULES', serialize($modules));
	}

	static public function isModuleActive($name)
	{
		$modules = self::getModules();
		$module = $modules[$name];
		return $module && $module['ACTIVE'];
	}
}
