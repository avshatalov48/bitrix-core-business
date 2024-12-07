<?php

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;

class Extension extends Base
{
	protected static array $modificationDates = [];
	protected static array $dependencies = [];
	protected static array $extensionCache = [];
	protected static $paths = [];
	public ?string $result = null;

	/**
	 * Extension constructor.
	 *
	 * @param $identifier
	 * @throws \Exception
	 */
	public static function getInstance($identifier): Extension
	{
		if (!isset(self::$extensionCache[$identifier]))
		{
			self::$extensionCache[$identifier] = new Extension($identifier);
		}

		return self::$extensionCache[$identifier];
	}

	public function __construct($identifier)
	{
		$identifier = Path::normalize($identifier);
		$this->baseFileName = 'extension';
		$desc = Utils::extractEntityDescription($identifier);
		$this->name = $desc['name'];
		$this->namespace = $desc['namespace'];
		if (isset(self::$paths[$desc['fullname']]))
		{
			$this->path = self::$paths[$desc['fullname']];
		}
		else
		{
			$this->path = Manager::getExtensionPath($identifier);
			self::$paths[$desc['fullname']] = $this->path;
		}

		if (!$this->path)
		{
			throw new SystemException("Extension '{$desc['fullname']}' doesn't exists");
		}
	}

	private function getBundleContent(): string
	{
		$files = $this->getBundleFiles();
		$content = "";
		foreach ($files as $path)
		{
			$file = new File($path);
			if ($file->isExists())
			{
				$content .= "\n".$file->getContents()."\n";
			}
		}

		return $content;
	}

	/**
	 * Returns content of extension without depending extensions
	 *
	 * @return string
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public function getContent($excludeResult = false): string
	{
		$content = "";
		$extensionFile = new File($this->path . '/' . $this->baseFileName . '.js');
		if ($excludeResult !== true) {
			$content .= $this->getResultExpression();
		}
		else
		{
			$this->result = $this->getResult();
		}

		if ($extensionFile->isExists() && $extensionContent = $extensionFile->getContents())
		{
			$content .= $this->getBundleContent();
			$content .= $extensionContent;
		}

		$content .= "\n\n";

		return $content;
	}

	public function getResultExpression(): string {
		$this->result = $this->getResult();
		if ($this->result != null && $this->result !== '')
		{
			$name = ($this->namespace != "bitrix"? $this->namespace.":" : "").$this->name;
			return <<<JS
this.jnExtensionData.set("{$name}", {$this->result});
JS;
		}

		return "";
	}

	private function getResult(): ?string
	{
		$file = new File($this->path . '/extension.php');
		$result = null;
		if ($file->isExists())
		{
			$result = include($file->getPath());
		}

		if (!empty($result) && is_array($result))
		{
			return Json::encode($result);
		}

		return null;
	}

	public function getIncludeExpression($callbackName = 'onExtensionsLoaded'): string
	{
		$relativePath = $this->getPath() . 'extension.js';
		$localizationPhrases = $this->getLangDefinitionExpression();
		$content = "\n//extension '{$this->name}'\n";
		$content .= "{$localizationPhrases}\n";
		$content .= "loadScript(\"{$relativePath}\", false, {$callbackName});";

		return $content;
	}

	/**
	 * Returns list of dependencies by name of extensions
	 *
	 * @param $name
	 * @param array $list
	 * @param array $alreadyResolved
	 * @return array
	 * @throws \Exception
	 */
	public static function getResolvedDependencyList($name, &$list = [], &$alreadyResolved = [], $margin = 0): array
	{
		$baseExtension = Extension::getInstance($name);
		$depsList = $baseExtension->getDependencyList();
		$alreadyResolved[] = $name;
		if (!empty($depsList))
		{
			$margin++;
			foreach ($depsList as $ext)
			{
				$depExtension = Extension::getInstance($ext);
				$extDepsList = $depExtension->getDependencyList();
				if (empty($extDepsList))
				{
					array_unshift($list, $ext);
				}
				elseif (!in_array($ext, $alreadyResolved))
				{
					self::getResolvedDependencyList($ext, $list, $alreadyResolved, $margin);
				}
			}
		}

		$list[] = $name;

		return array_unique($list);
	}

	protected function onBeforeModificationMarkerSave(array &$value)
	{
		$files = $this->getBundleFiles();
		foreach ($files as $path)
		{
			$file = new File($path);
			if ($file->isExists())
			{
				$value[] = Utils::getFileHash($file);
			}
		}
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function resolveDependencies(): array
	{
		$name = ($this->namespace !== "bitrix" ? $this->namespace . ":" : "") . $this->name;
		return self::getResolvedDependencyList($name);
	}

	public function getDependencyList()
	{
		$fullName = "$this->namespace:$this->name";
		if (isset(self::$dependencies[$fullName]))
		{
			return self::$dependencies[$fullName];
		}
		else
		{
			$list = parent::getDependencyList();
			self::$dependencies[$fullName] = $list;
		}

		return self::$dependencies[$fullName];
	}

}