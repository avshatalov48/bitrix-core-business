<?php

namespace Bitrix\Main\Web\WebPacker;

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Config\Option;

/**
 * Class Builder
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Builder
{
	/** @var Output\Base $output Output. */
	protected $output;

	/** @var bool $useMinification Use minification. */
	protected $useMinification;

	/** @var Module[] $modules Modules.  */
	private $modules = [Converter::CORE_EXTENSION];

	/** @var string[] $extensions Extensions.  */
	private $extensions = [];

	/** @var string $siteUri Site uri.*/
	protected static $siteUri;

	/**
	 * Build.
	 *
	 * @return Output\Result
	 */
	public function build()
	{
		return $this->getOutput()->output($this);
	}

	/**
	 * Set output.
	 *
	 * @param Output\Base $output Output.
	 * @return $this
	 */
	public function setOutput(Output\Base $output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * Get output.
	 *
	 * @return Output\Base
	 */
	public function getOutput()
	{
		if (!$this->output)
		{
			$this->output = new Output\Base();
		}

		return $this->output;
	}

	/**
	 * Add child module to root module.
	 *
	 * @param Module $module Module.
	 * @return $this
	 */
	public function addModule(Module $module)
	{
		$this->modules[] = $module;
		return $this;
	}

	/**
	 * Add child module to root module.
	 *
	 * @param string $name Name.
	 * @param bool $appendDependencies Append dependencies.
	 * @return $this
	 */
	public function addExtension($name, $appendDependencies = true)
	{
		$list = $appendDependencies ? Extension::getDependencyList($name) : [];
		if (!in_array($name, $list))
		{
			$list[] = $name;
		}

		$this->extensions = array_unique(array_merge($this->extensions, $list));

		$list = array_diff($list, $this->modules);
		$list = array_filter(
			$list,
			function ($item)
			{
				return $item !== Converter::CORE_EXTENSION;
			}
		);
		$this->modules = array_merge($this->modules, $list);

		return $this;
	}

	/**
	 * Return true if it has core extension.
	 *
	 * @return bool
	 */
	public function hasCoreExtension()
	{
		return in_array(Converter::CORE_EXTENSION, $this->extensions);
	}

	private function convertExtensionsToModules()
	{
		foreach ($this->modules as $index => $module)
		{
			if ($module === 'main.core')
			{
				$module .= '.minimal';
			}

			if ($module === Converter::CORE_EXTENSION && !$this->hasCoreExtension())
			{
				continue;
			}

			if (!is_string($module))
			{
				continue;
			}

			$name = $module;
			$extension = Extension::getResourceList($name, ['with_dependency' => false]);

			$package = (new Resource\Package());
			$profile = (new Resource\Profile());
			foreach ($extension as $key => $values)
			{
				if ($key === 'options')
				{
					$this->applyExtensionOptions($profile, $values);
					continue;
				}

				if (!in_array($key, Resource\Asset::getTypeList()))
				{
					continue;
				}

				foreach ($values as $path)
				{
					$type = Resource\Asset::detectType($path);
					if (!$type)
					{
						continue;
					}

					$package->addAsset(Resource\Asset::create($path));
				}
			}

			$this->modules[$index] = new Module($name, $package, $profile);
		}

		return $this;
	}

	/**
	 * Use minification.
	 *
	 * @param bool $use Use minification.
	 * @return $this
	 */
	public function useMinification($use)
	{
		$this->useMinification = $use;
		return $this;
	}

	/**
	 * Apply extension options.
	 *
	 * @param Resource\Profile $profile Profile.
	 * @param array $extensionOptions Extension options.
	 * @return void
	 */
	protected function applyExtensionOptions($profile, $extensionOptions)
	{
		if (!is_array($extensionOptions))
		{
			return;
		}

		$webPacker = self::getValueByKey($extensionOptions, Resource\Profile::WEBPACKER);
		$properties = self::getValueByKey($webPacker, Resource\Profile::PROPERTIES);
		if (is_array($properties))
		{
			foreach ($properties as $propertyName => $propertyValue)
			{
				$profile->setProperty($propertyName, $propertyValue);
			}
		}
		$profile->useAllLangs(!!self::getValueByKey($webPacker, Resource\Profile::USE_ALL_LANGS));
		$profile->useLangCamelCase(!!self::getValueByKey($webPacker, Resource\Profile::USE_LANG_CAMEL_CASE));
		$deleteLangPrefixes = self::getValueByKey($webPacker, Resource\Profile::DELETE_LANG_PREFIXES);
		if (is_array($deleteLangPrefixes))
		{
			$profile->deleteLangPrefixes($deleteLangPrefixes);
		}

		$callMethod = self::getValueByKey($webPacker, Resource\Profile::CALL_METHOD);
		if ($callMethod)
		{
			$profile->setCallMethod($callMethod);
		}
	}

	protected function getValueByKey($array, $key)
	{
		if (!is_array($array))
		{
			return null;
		}

		return (isset($array[$key]) && $array[$key]) ? $array[$key] : null;
	}

	/**
	 * Get modules.
	 *
	 * @return Module[]
	 */
	public function getModules()
	{
		$this->convertExtensionsToModules();
		return array_filter(
			$this->modules,
			function ($module)
			{
				return $module instanceof Module;
			}
		);
	}

	/**
	 * Get module.
	 *
	 * @param string $name Name.
	 * @return Module|null
	 */
	public function getModule($name)
	{
		foreach ($this->getModules() as $module)
		{
			if ($module->getName() === $name)
			{
				return $module;
			}
		}

		return null;
	}

	/**
	 * Convert to string.
	 *
	 * @return string
	 */
	public function stringify()
	{
		return Converter::stringify($this);
	}

	/**
	 * Get site uri.
	 *
	 * @return string
	 */
	public static function getDefaultSiteUri()
	{
		if (self::$siteUri)
		{
			return self::$siteUri;
		}

		$server = Context::getCurrent()->getServer();
		$url = $server->getHttpHost();

		$canSave = !empty($url) && (!defined('BX_CRONTAB') || !BX_CRONTAB);
		$isRestored = false;

		if (!$url)
		{
			$url = Option::get('main', 'last_site_url', null);
			if ($url)
			{
				$isRestored = true;
			}
			else
			{
				$url = Option::get('main', 'server_name', null);
				$url = $url ?: $server->getServerName();
				if (!$url)
				{
					$defaultSites = \CAllSite::getDefList();
					while($defaultSite = $defaultSites->fetch())
					{
						$url = $defaultSite['SERVER_NAME'];
						if ($url)
						{
							break;
						}
					}
				}
			}
		}

		if (!$isRestored)
		{
			if (mb_strpos($url, ':') === false && $server->getServerPort())
			{
				if (!in_array($server->getServerPort(), array('80', '443')))
				{
					$url .= ':' . $server->getServerPort();
				}
			}

			$url = (Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")
				. "://" . $url;
		}

		$uri = new Uri($url);
		$url = $uri->getLocator();
		if (mb_substr($url, -1) == '/')
		{
			$url = mb_substr($url, 0, -1);
		}

		if ($canSave)
		{
			Option::set('main', 'last_site_url', $url);
		}

		self::$siteUri = $url;
		return $url;
	}
}