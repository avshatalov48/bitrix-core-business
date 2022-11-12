<?php

namespace Bitrix\MobileApp\Janative;

use Bitrix\Main\IO\File;
use Bitrix\Main\Web\Json;

class Utils
{
	/**
	 * @param $entityIdentifier
	 * @param string $defaultNamespace
	 * @return array["name", "namespace","fullname", "defaultFullname"]
	 */
	public static function extractEntityDescription($entityIdentifier, $defaultNamespace = "bitrix")
	{
		$namespace = $defaultNamespace;
		$name = $entityIdentifier;

		if(mb_strpos($entityIdentifier, ":"))
		{
			[$namespace, $name] = explode(":", $entityIdentifier);
		}

		return [
			"name" => $name,
			"namespace" => $namespace,
			"fullname" => "$namespace:$name",
			"relativePath" => "$namespace/$name",
			"defaultFullname" => $namespace && $namespace != "bitrix" ? "$namespace:$name" : $name
		];
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
		if ($file->isExists())
		{
			return hash_file('md5', $file->getPhysicalPath());
		}

		return "";
	}
}