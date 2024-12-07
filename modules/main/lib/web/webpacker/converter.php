<?php

namespace Bitrix\Main\Web\WebPacker;

use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;

/**
 * Class Converter
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Converter
{
	const CORE_EXTENSION = 'ui.webpacker';

	protected static $hasCoreExtension = false;

	/**
	 * Stringify module.
	 *
	 * @param Builder $builder Builder.
	 * @return string
	 */
	public static function stringify(Builder $builder)
	{
		self::$hasCoreExtension = $builder->hasCoreExtension();
		if (self::$hasCoreExtension)
		{
			$content = JsonEncoder::encode([
				'address' => Builder::getDefaultSiteUri()
			]);
			$content = "var webPacker = $content;" . self::getEol();
		}
		else
		{
			$content = '';
		}

		foreach ($builder->getModules() as $module)
		{
			$moduleContent = self::encodeModule($module);
			$content .= self::wrap($moduleContent) . self::getEol();
		}

		return self::wrap($content);
	}

	/**
	 * Wrap by closure.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	protected static function wrap($content)
	{
		return <<<EOD
;(function () {
$content
})();
EOD;

	}

	/**
	 * Encode module.
	 *
	 * @param Module $module Module.
	 * @return string
	 */
	protected static function encodeModule(Module $module)
	{
		$name = $module->getName();
		$content = '';
		if (!self::isCoreExtension($name) && self::$hasCoreExtension)
		{
			$name = \CUtil::jsEscape($name);
			$content = "var module = new webPacker.module('$name');" . self::getEol(1);

			if ($module->getProfile())
			{
				$properties = $module->getProfile()->getProperties();
				if (!empty($properties))
				{
					$properties = JsonEncoder::encode($properties);
					$content .= "module.setProperties($properties);" . self::getEol(1);
				}
			}
		}

		if ($module->getPackage())
		{
			$content .= self::encodePackage($module->getPackage(), $module->getProfile());
		}
		if ($module->getProfile())
		{
			$method = $module->getProfile()->getCallMethod();
			if ($method)
			{
				$parameter = $module->getProfile()->getCallParameter();
				$parameter = $parameter ? JsonEncoder::encode($parameter) : '{}';
				$content .=  "$method($parameter);";
			}
		}

		return $content;
	}

	/**
	 * Encode resource package.
	 *
	 * @param Resource\Package $package Package.
	 * @param Resource\Profile $profile Profile.
	 * @return string
	 */
	protected static function encodePackage(Resource\Package $package, Resource\Profile $profile = null)
	{
		$content = '';
		foreach ($package::getOrderedTypeList() as $type)
		{
			$assets = $package->getAssets($type);
			if (empty($assets))
			{
				continue;
			}

			switch ($type)
			{
				case Resource\Asset::CSS:
				case Resource\Asset::LAYOUT:
					if (!self::$hasCoreExtension)
					{
						throw new InvalidOperationException("Resource of type `$type` not allowed without core extension.");
					}

					$resources = $list = JsonEncoder::encode($package->toArray($type));
					$content .= "module.loadResources($resources);" . self::getEol();
					break;

				case Resource\Asset::JS:
					foreach ($assets as $asset)
					{
						$content .= $asset->getContent() . self::getEol();
					}
					break;

				case Resource\Asset::LANG:
					if (!self::$hasCoreExtension)
					{
						throw new InvalidOperationException("Resource of type `$type` not allowed without core extension.");
					}

					$messages = [];
					$languages = [];
					$language = ($profile ? $profile->getLanguage() : null) ?: Loc::getCurrentLang();
					$isAllLangs = $profile ? $profile->isAllLangs() : false;
					foreach ($assets as $asset)
					{
						/** @var Resource\LangAsset $asset */
						$mess = $asset->useAllLangs($isAllLangs)->getContent();
						if (!is_array($mess))
						{
							break;
						}

						if ($profile)
						{
							foreach ($mess as $messLanguage => $messList)
							{
								$messList = Resource\LangAsset::deletePrefixes(
									$messList,
									$profile->getDeleteLangPrefixes()
								);
								if ($profile->isLangCamelCase())
								{
									$messList = Resource\LangAsset::toCamelCase($messList);
								}
								if (!isset($messages[$messLanguage]) || !is_array($messages[$messLanguage]))
								{
									$messages[$messLanguage] = [];
								}

								$messages[$messLanguage] = array_merge(
									$messages[$messLanguage],
									$messList
								);
							}
						}

						$languages = array_unique(array_merge(
							$languages,
							array_keys($messages)
						));
					}

					if (count($messages) === 1)
					{
						$messages = current($messages);
					}

					$messages = JsonEncoder::encode($messages);
					$languages = JsonEncoder::encode($languages);
					$content .= "module.language = \"$language\";" . self::getEol(1);
					$content .= "module.languages = $languages;" . self::getEol(1);
					$content .= "module.messages = $messages;" . self::getEol();

					break;
			}
		}

		return $content;
	}

	protected static function getEol($multiplier = 2)
	{
		return str_repeat("\n", $multiplier);
	}

	protected static function isCoreExtension($name)
	{
		return self::CORE_EXTENSION === $name;
	}
}