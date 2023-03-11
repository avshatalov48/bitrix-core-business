<?php

namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;


class Settings
	extends Translate\IO\File
	implements \Iterator, \Countable, \ArrayAccess
{
	public const FILE_NAME = '.settings.php';

	public const OPTION_LANGUAGES = 'languages';

	/** @var string[] */
	protected $options;

	/** @var int */
	protected $optionsCount;

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
	public static function instantiateByPath(string $fullPath): ?self
	{
		if (empty($fullPath))
		{
			throw new Main\ArgumentException();
		}

		$file = null;
		if (\mb_substr($fullPath, -5) === '/lang' || \mb_substr($fullPath, -6) === '/lang/')
		{
			$file = new static($fullPath. '/'. self::FILE_NAME);
		}
		elseif (preg_match("#^(.*?/lang/)([^/]+)/*(.+)#".(Translate\Config::isUtfMode() ? 'u' : ''), $fullPath, $parts))
		{
			$file = new static($parts[1]. '/'. self::FILE_NAME);
		}

		return $file;
	}


	//region Load & Save

	/**
	 * Returns option for file/folder.
	 *
 	 * @param string $langPath Path to language file.
	 * @param string $optionType Option type.
	 *
	 * @return array
	 */
	public function getOption(string $langPath, string $optionType): array
	{
		$options = $this->getOptions($langPath);
		if (!empty($options[$optionType]))
		{
			return $options[$optionType];
		}

		return [];
	}

	/**
	 * Returns option for file/folder.
	 *
 	 * @param string $langPath Path to language file.
	 *
	 * @return array
	 */
	public function getOptions(string $langPath = ''): array
	{
		// lazy load
		if ($this->options === null && !$this->load())
		{
			return [];
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

		$options = [];
		if (isset($this->options['*']))
		{
			$options = $this->options['*'];
		}

		if (\preg_match("#^(.*?/lang/)([^/]+)/+(.+)#".(Translate\Config::isUtfMode() ? 'u' : ''), $langPath, $parts))
		{
			$langPath = $parts[3];
		}

		if (isset($this->options[$langPath]))
		{
			$options = $this->options[$langPath];
		}
		else
		{
			if (\mb_strpos($langPath, '/') !== false)
			{
				$parts = \explode('/', $langPath);
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
	public function load(): bool
	{
		if (!$this->isExists() || !$this->isFile() || $this->getName() !== self::FILE_NAME)
		{
			return false;
		}

		$this->options = [];
		$this->optionCodes = [];
		$this->optionsCount = 0;

		$options = include $this->getPhysicalPath();

		if (\is_array($options) && \count($options) > 0)
		{
			$this->options = $options;
			$this->optionCodes = \array_keys($options);
			$this->optionsCount = \count($options);
		}

		return true;
	}


	/**
	 * Save changes or create new file.
	 *
	 * @return boolean
	 * @throws Main\IO\IoException
	 */
	public function save(): bool
	{
		$content = '';
		if ($this->count() > 0)
		{
			$content = \var_export($this->options, true);
			$content = \preg_replace("/^[ ]{6}(.*)/m", "\t\t\t$1", $content);
			$content = \preg_replace("/^[ ]{4}(.*)/m", "\t\t$1", $content);
			$content = \preg_replace("/^[ ]{2}(.*)/m", "\t$1", $content);
			$content = \str_replace(["\r\n", "\r"], ["\n", ''], $content);
		}

		\set_error_handler(
			function ($severity, $message, $file, $line)
			{
				throw new \ErrorException($message, $severity, $severity, $file, $line);
			}
		);

		try
		{
			if ($content <> '')
			{
				if (parent::putContents("<". "?php\nreturn ". $content. "\n?". '>') === false)
				{
					$filePath = $this->getPath();
					throw new Main\IO\IoException("Couldn't write option file '{$filePath}'");
				}
			}
			elseif ($this->isExists())
			{
				$this->markWritable();
				$this->delete();
			}
		}
		catch (\ErrorException $exception)
		{
			\restore_error_handler();
			throw new Main\IO\IoException($exception->getMessage());
		}

		\restore_error_handler();

		return true;
	}

	//endregion

	//region ArrayAccess

	/**
	 * @param string $code Phrase code.
	 *
	 * @return boolean
	 */
	public function offsetExists($code): bool
	{
		return isset($this->options[$code]);
	}

	/**
	 * @param string $code Phrase code.
	 *
	 * @return string|null
	 */
	#[\ReturnTypeWillChange]
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
	public function offsetSet($code, $phrase): void
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
	public function offsetUnset($code): void
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
	#[\ReturnTypeWillChange]
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
	public function next(): void
	{
		++ $this->dataPosition;
	}

	/**
	 * Return the key of the current phrase element.
	 *
	 * @return string|null
	 */
	public function key(): ?string
	{
		return $this->optionCodes[$this->dataPosition] ?: null;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @return boolean
	 */
	public function valid(): bool
	{
		$code = $this->optionCodes[$this->dataPosition];
		return isset($this->options[$code]);
	}

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @return void
	 */
	public function rewind(): void
	{
		$this->dataPosition = 0;
		$this->optionCodes = \array_keys($this->options);
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
	public function count($allowDirectFileAccess = false): int
	{
		if ($this->optionsCount === null)
		{
			if ($this->options !== null && \count($this->options) > 0)
			{
				$this->optionsCount = \count($this->options);
			}
			elseif ($allowDirectFileAccess)
			{
				$options = include $this->getPhysicalPath();

				if (\is_array($options) && \count($options) > 0)
				{
					$this->optionsCount = \count($options);
				}
			}
		}

		return $this->optionsCount ?: 0;
	}

	//endregion
}
