<?php

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;

/**
 * @property array $extensions
 * @property array $dynamicData
 */
class Config
{
	private ?string $path;
	private array $config = [];

	public function __construct($path)
	{
		$this->path = Path::normalize($path);
		$file = new File($this->path);
		if ($file->isExists())
		{
			$content = include($this->path);

			if (is_array($content))
			{
				$this->config = $content;
			}
		}
	}

	public function __get($name)
	{
		$config = $this->config;
		if ($name == "extensions")
		{
			if (array_keys($config) !== range(0, count($config) - 1) && array_key_exists('extensions', $config))
			{
				return $config['extensions'];
			}
			else
			{
				return [];
			}
		}
		elseif ($name == "dynamicData")
		{
			return $config['dynamicData'] ?? [];
		}
	}
}