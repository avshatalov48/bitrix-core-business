<?php

namespace Bitrix\Main\Web\WebPacker\Resource;

use Bitrix\Main\Localization\Loc;

/**
 * Class LangAsset
 *
 * @package Bitrix\Main\Web\WebPacker\Resource
 */
class LangAsset extends Asset
{
	protected $type = self::LANG;

	/**
	 * Get content.
	 *
	 * @return array
	 */
	public function getContent()
	{
		if (is_array($this->content) && !empty($this->content))
		{
			return $this->content;
		}
		elseif ($this->path)
		{
			$messages = Loc::loadLanguageFile(self::getAbsolutePath($this->path));
			return $messages ?: [];
		}

		return [];
	}

	/**
	 * Convert messages to camel case.
	 *
	 * @param array $messages Messages.
	 * @return array
	 */
	public static function toCamelCase(array $messages)
	{
		foreach ($messages as $code => $value)
		{
			unset($messages[$code]);
			$code = str_replace('_', ' ', strtolower($code));
			$code = str_replace(' ', '', ucwords($code));
			$code = lcfirst($code);
			$messages[$code] = $value;
		}

		return $messages;
	}

	/**
	 * Delete prefixes in messages.
	 *
	 * @param array $messages Messages.
	 * @param array $prefixes Prefixes.
	 * @return array
	 */
	public static function deletePrefixes(array $messages, array $prefixes)
	{
		foreach ($messages as $code => $value)
		{
			foreach ($prefixes as $prefix)
			{
				if (strpos($code, $prefix) !== 0)
				{
					continue;
				}

				unset($messages[$code]);
				$code = substr($code, strlen($prefix));
				$messages[$code] = $value;
			}
		}

		return $messages;
	}

	/**
	 * Return true if asset exists.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public static function isExists($path)
	{
		return true;
	}
}