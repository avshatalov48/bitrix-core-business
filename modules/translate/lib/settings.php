<?php

namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;


class Settings
	extends Translate\IO\File
	implements \Iterator, \Countable, \ArrayAccess
{
	const OPTION_FILE_NAME = '.settings.php';

	/** @var string[] */
	protected $options = null;

	/** @var int */
	protected $optionsCount = null;

	/** @var array */
	protected $optionCodes = [];

	/** @var int */
	protected $dataPosition = 0;



	/**
	 * Constructs instance by lang path.
	 *
	 * @param string $fullPath Full path to language file.
	 *
	 * @return Translate\Settings|null
	 * @throws Main\ArgumentException
	 */
	public static function instantiateByPath($fullPath)
	{
		if (empty($fullPath) || !is_string($fullPath))
		{
			throw new Main\ArgumentException();
		}

		$file = null;
		if (mb_substr($fullPath, -5) === '/lang' || mb_substr($fullPath, -6) === '/lang/')
		{
			$file = new static($fullPath. '/'. self::OPTION_FILE_NAME);
		}
		elseif (preg_match("#^(.*?/lang/)([^/]+)/*(.+)#".(Translate\Config::isUtfMode() ? 'u' : ''), $fullPath, $parts))
		{
			$file = new static($parts[1]. '/'. self::OPTION_FILE_NAME);
		}

		return $file;
	}



	//region Load & Save

	/**
	 * Returns option for file/folder.
	 *
 	 * @param string $langPath Path to language file.
	 *
	 * @return array
	 */
	public function getOptions($langPath = '')
	{
		// lazy load
		if ($this->options === null)
		{
			if ($this->isExists())
			{
				$this->load();
			}
		}

		if (empty($langPath))
		{
			return $this->options;
		}
		// for all in lang/
		if ($langPath === '*' && isset($this->options['*']))
		{
			return $this->options['*'];
		}

		$options = array();
		if (isset($this->options['*']))
		{
			$options = $this->options['*'];
		}

		if (preg_match("#^(.*?/lang/)([^/]+)/+(.+)#".(Translate\Config::isUtfMode() ? 'u' : ''), $langPath, $parts))
		{
			$langPath = $parts[3];
		}

		if (isset($this->options[$langPath]))
		{
			$options = $this->options[$langPath];
		}
		else
		{
			if (mb_strpos($langPath, '/') !== false)
			{
				$parts = explode('/', $langPath);
				$path = '';
				foreach ($parts as $part)
				{
					$path .= ($path != '' ? '/' : ''). $part;
					if (isset($this->options[$path]))
					{
						$options = $this->options[$path];
					}
				}
			}
		}

		return $options;
	}

	//endregion


	//region Load & Save

	/**
	 * Loads option file for operate.
	 *
	 * @return bool
	 */
	public function load()
	{
		$this->options = [];
		$this->optionCodes = [];
		$this->optionsCount = 0;

		if (!$this->isExists() || !$this->isFile() ||
			($this->getExtension() !== 'php' || $this->getName() !== self::OPTION_FILE_NAME))
		{
			return false;
		}

		$options = include $this->getPhysicalPath();

		if (is_array($options) && count($options) > 0)
		{
			$this->options = $options;
			$this->optionCodes = array_keys($options);
			$this->optionsCount = count($options);
		}

		return true;
	}


	/**
	 * Save changes or create new file.
	 *
	 * @return boolean
	 */
	public function save()
	{
		$content = '';
		if ($this->count() > 0)
		{
			$content = var_export($this->options, true);
			$content = preg_replace("/^[ ]{6}(.*)/m", "\t\t\t$1", $content);
			$content = preg_replace("/^[ ]{4}(.*)/m", "\t\t$1", $content);
			$content = preg_replace("/^[ ]{2}(.*)/m", "\t$1", $content);
			$content = str_replace(["\r\n", "\r"], ["\n", ''], $content);
		}

		if ($content <> '')
		{
			if (parent::putContents("<". "?php\nreturn ". $content. "\n?". '>') === false)
			{
				$filePath = $this->getPath();
				throw new Main\IO\IoException("Couldn't write option file '{$filePath}'");
			}
		}
		else
		{
			if ($this->isExists())
			{
				$this->markWritable();
				$this->delete();
			}
		}

		return true;
	}


	//region ArrayAccess

	/**
	 * Checks existence of the phrase by its code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return boolean
	 */
	public function offsetExists($code)
	{
		return isset($this->options[$code]);
	}

	/**
	 * Returns phrase by its code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return string|null
	 */
	public function offsetGet($code)
	{
		if (isset($this->options[$code]))
		{
			return $this->options[$code];
		}

		return null;
	}

	/**
	 * Offset to set
	 *
	 * @param string $code Phrase code.
	 * @param string $phrase Phrase.
	 *
	 * @return void
	 */
	public function offsetSet($code, $phrase)
	{
		$this->options[$code] = $phrase;
	}

	/**
	 * Unset phrase by code.
	 *
	 * @param string $code Language code.
	 *
	 * @return void
	 */
	public function offsetUnset($code)
	{
		unset($this->options[$code]);
	}

	//endregion

	//region Iterator

	/**
	 * Return the current phrase element.
	 *
	 * @return array|null
	 */
	public function current()
	{
		$code = $this->optionCodes[$this->dataPosition];

		return $this->options[$code] ?: null;
	}

	/**
	 * Move forward to next phrase element.
	 *
	 * @return void
	 */
	public function next()
	{
		++ $this->dataPosition;
	}

	/**
	 * Return the key of the current phrase element.
	 *
	 * @return string|null
	 */
	public function key()
	{
		return $this->optionCodes[$this->dataPosition] ?: null;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		$code = $this->optionCodes[$this->dataPosition];
		return isset($this->options[$code]);
	}

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->dataPosition = 0;
		$this->optionCodes = array_keys($this->options);
	}

	//endregion

	//region Countable

	/**
	 * Returns amount phrases in the language file.
	 *
	 * @param bool $allowDirectFileAccess Allow include file to count phrases.
	 *
	 * @return int
	 */
	public function count($allowDirectFileAccess = false)
	{
		if ($this->optionsCount === null)
		{
			if ($this->options !== null && count($this->options) > 0)
			{
				$this->optionsCount = count($this->options);
			}
			elseif ($allowDirectFileAccess)
			{
				$options = include $this->getPhysicalPath();

				if (is_array($options) && count($options) > 0)
				{
					$this->optionsCount = count($options);
				}
			}
		}

		return $this->optionsCount ?: 0;
	}

	//endregion
}
