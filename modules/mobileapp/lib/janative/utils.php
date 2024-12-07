<?php

namespace Bitrix\MobileApp\Janative;

use Bitrix\Main\IO\File;
use Bitrix\Main\Web\Json;

class Utils
{
	protected static array $descriptionCache = [];
	/**
	 * @param $entityIdentifier
	 * @param string $defaultNamespace
	 * @return array["name", "namespace","fullname", "defaultFullname"]
	 */
	public static function extractEntityDescription($entityIdentifier, string $defaultNamespace = "bitrix"): array
	{
		$namespace = $defaultNamespace;
		$name = $entityIdentifier;
		$cacheId =  "$namespace:$name";

		if(!isset(self::$descriptionCache[$cacheId]))
		{
			if (strpos($entityIdentifier, ":"))
			{
				[$namespace, $name] = explode(":", $entityIdentifier);
			}

			self::$descriptionCache[$cacheId] = [
				"name" => $name,
				"namespace" => $namespace,
				"fullname" => "$namespace:$name",
				"relativePath" => "$namespace/$name",
				"defaultFullname" => $namespace && $namespace != "bitrix" ? "$namespace:$name" : $name
			];
		}

		return self::$descriptionCache[$cacheId];
	}

	/**
	 * @param $string
	 * @param int $options
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function jsonEncode($string, $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)
	{
		return Json::encode($string, $options);
	}

	public static function getFileHash(File $file) {
		return filectime($file->getPhysicalPath()) ?? "";
	}
}