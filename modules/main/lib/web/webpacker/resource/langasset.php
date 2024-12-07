<?php

namespace Bitrix\Main\Web\WebPacker\Resource;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * Class LangAsset
 *
 * @package Bitrix\Main\Web\WebPacker\Resource
 */
class LangAsset extends Asset
{
	protected $type = self::LANG;
	protected $useAllLangs = false;

	/**
	 * Get content.
	 *
	 * @return array
	 */
	public function getContent()
	{
		if (is_array($this->content) && !empty($this->content))
		{
			return [Loc::getCurrentLang() => $this->content];
		}
		elseif ($this->path)
		{
			$languages = $this->useAllLangs
				? self::getLanguages()
				: [Loc::getCurrentLang()];

			$result = [];
			foreach ($languages as $language)
			{
				$messages = Loc::loadLanguageFile(
					self::getAbsolutePath($this->path),
					$language
				);
				if (!empty($messages))
				{
					$result[$language] = $messages;
				}
			}

			return $result;
		}

		return [];
	}

	/**
	 * Use all languages.
	 *
	 * @param bool $use Use.
	 * @return $this
	 */
	public function useAllLangs($use)
	{
		$this->useAllLangs = (bool) $use;
		return $this;
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
			$code = str_replace('_', ' ', mb_strtolower($code));
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
				if (!str_starts_with($code, $prefix))
				{
					continue;
				}

				unset($messages[$code]);
				$code = mb_substr($code, mb_strlen($prefix));
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

	protected static function getLanguages()
	{
		static $list = null;
		if ($list !== null)
		{
			return $list;
		}

		$langDir = Main\Application::getDocumentRoot() . '/bitrix/modules/main/lang/';
		$dir = new Main\IO\Directory($langDir);
		if ($dir->isExists())
		{
			foreach($dir->getChildren() as $childDir)
			{
				if (!$childDir->isDirectory())
				{
					continue;
				}

				$list[] = $childDir->getName();
			}
		}

		return $list;
	}
}