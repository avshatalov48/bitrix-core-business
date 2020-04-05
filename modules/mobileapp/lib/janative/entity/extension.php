<?php

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\IO\File;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;

class Extension extends Base
{
	protected static $modificationDates = [];
	protected static $dependencies = [];
	/**
	 * Extension constructor.
	 * @param $identifier
	 * @throws \Exception
	 */
	public function __construct($identifier)
	{
		$this->path = Manager::getExtensionPath($identifier);
		$this->baseFileName = "extension";
		$desc = Utils::extractEntityDescription($identifier);
		$this->name = $desc["name"];
		$this->namespace = $desc["namespace"];
		if (!$this->path)
		{
			throw new \Exception("Extension '{$desc["fullname"]}' doesn't exists");
		}
	}

	/**
	 * Returns content of extension without depending extensions
	 * @return string
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public function getContent()
	{
		$content = "";
		$extensionFile = new File($this->path . "/extension.js");
		if ($extensionFile->isExists() && $extensionContent = $extensionFile->getContents())
		{
			$localizationPhrases = $this->getLangDefinitionExpression();
			$content .= "\n//extension '{$this->name}'\n";

			$content .= $localizationPhrases;
			$content .= $extensionContent;
			$content .= "\n\n";
		}

		return $content;
	}

	public function getIncludeExpression($callbackName = "onExtensionsLoaded")
	{
		$relativePath = $this->getPath() . "extension.js";
		$localizationPhrases = $this->getLangDefinitionExpression();
		$content = "\n//extension '{$this->name}'\n";
		$content .= "{$localizationPhrases}\n";
		$content .= "loadScript(\"{$relativePath}\", false, {$callbackName});";

		return $content;
	}


	/**
	 * Returns list of dependencies by name of extensions
	 * @param $name
	 * @param array $list
	 * @param array $alreadyResolved
	 * @return array
	 * @throws \Exception
	 */
	public static function getResolvedDependencyList($name, &$list = [], &$alreadyResolved = [])
	{
		$baseExtension = new Extension($name);
		$depsList = $baseExtension->getDependencyList();
		$alreadyResolved[] = $name;
		if (count($depsList) > 0)
		{
			foreach ($depsList as $ext)
			{
				$depExtension = new Extension($ext);
				$extDepsList = $depExtension->getDependencyList();
				if (count($extDepsList) == 0)
				{
					array_unshift($list, $ext);
				}
				elseif (!in_array($ext, $alreadyResolved))
				{
					self::getResolvedDependencyList($ext, $list, $alreadyResolved);
				}
			}
		}

		$list[] = $name;

		return array_unique($list);
	}

	protected function onBeforeModificationDateSave(&$value)
	{
		// TODO: Implement onBeforeModificationDateSave() method.
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function resolveDependencies()
	{
		return self::getResolvedDependencyList($this->name);
	}
}