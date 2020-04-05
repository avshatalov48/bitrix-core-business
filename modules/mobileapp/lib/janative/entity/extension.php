<?php

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;

class Extension
{
	private $path;
	private $namespace;
	public $name;

	/**
	 * Extension constructor.
	 * @param $identifier
	 * @throws \Exception
	 */
	public function __construct($identifier)
	{
		$this->path = Manager::getInstance()->getExtensionPath($identifier);
		$desc = Utils::extractEntityDescription($identifier);
		$this->name = $desc["name"];
		$this->namespace = $desc["namespace"];
		if (!$this->path)
		{
			throw new \Exception("Extension '{$desc["fullname"]}' doesn't exists");
		}
	}

	/**
	 * Returns list of dependencies
	 * @return array|mixed
	 */
	public function getDependencies()
	{
		$file = new File("$this->path/deps.php");
		if ($file->isExists())
		{
			/** @noinspection PhpIncludeInspection */
			$list = include($file->getPath());
			if (is_array($list))
			{
				return $list;
			}
		}

		return [];
	}

	public function getPath()
	{
		return "{$this->path}/extension.js";
	}

	public function getRelativePath()
	{
		$relativePath = str_replace(Application::getDocumentRoot(), "", "{$this->path}/extension.js");
		return Path::normalize($relativePath);
	}

	/**
	 * Returns content of extension without depending extensions
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public function getContent()
	{
		$content = "";
		$extensionFile = new File($this->getPath());
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

	public function getLangMessages()
	{
		$langPhrases = Localization\Loc::loadLanguageFile("{$this->path}/extension.php");
		return $langPhrases?: [];
	}

	public function getLangDefinitionExpression()
	{
		$langPhrases = $this->getLangMessages();
		if(count($langPhrases)>0)
		{
			$jsonLangMessages = Utils::jsonEncode($langPhrases, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
			return  <<<JS
BX.message($jsonLangMessages);
JS;
		}

		return "";
	}

	public function getIncludeExpression($callbackName = "onExtensionsLoaded")
	{
		$relativePath = $this->getRelativePath();
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
		$depsList = $baseExtension->getDependencies();
		$alreadyResolved[] = $name;
		if (count($depsList) > 0)
		{
			foreach ($depsList as $ext)
			{
				$depExtension = new Extension($ext);
				$extDepsList = $depExtension->getDependencies();
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
}