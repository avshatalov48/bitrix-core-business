<?php
namespace Bitrix\Landing\Source;

class Seo
{
	const TITLE = 'title';
	const BROWSER_TITLE = 'browser_title';
	const KEYWORDS = 'keywords';
	const DESCRIPTION = 'description';

	/** @var array seo properties */
	protected $properties = [];

	public function __construct()
	{

	}

	/**
	 * @param array $values
	 * @return void
	 */
	public function setProperties(array $values)
	{
		$values = array_filter($values, [__CLASS__, 'clearValues'], ARRAY_FILTER_USE_BOTH);
		if (!empty($values))
		{
			$this->properties = array_merge($this->properties, $values);
		}
	}

	/**
	 * @param string $name
	 * @param string|array $value
	 * @return void
	 */
	public function setProperty($name, $value)
	{
		$name = (string)$name;
		if ($name !== '' && $value !== null)
		{
			$this->properties[$name] = $value;
		}
	}

	/**
	 * @return array|null
	 */
	public function getProperties()
	{
		return (!empty($this->properties) ? $this->properties : null);
	}

	/**
	 * @param string $name
	 * @return string|array|null
	 */
	public function getProperty($name)
	{
		$name = (string)$name;
		if ($name !== '')
		{
			return (isset($this->properties[$name]) ? $this->properties[$name] : null);
		}
		return null;
	}

	/**
	 * @return void
	 */
	public function clear()
	{
		$this->clearProperties();
	}

	/**
	 * @return void
	 */
	public function clearProperties()
	{
		$this->properties = [];
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function clearProperty($name)
	{
		$name = (string)$name;
		if ($name !== '' && isset($this->properties[$name]))
		{
			unset($this->properties[$name]);
		}
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setTitle($value)
	{
		if (is_string($value))
		{
			$this->setProperty(self::TITLE, strip_tags(trim($value)));
		}
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setBrowserTitle($value)
	{
		if (is_string($value))
		{
			$this->setProperty(self::BROWSER_TITLE, strip_tags(trim($value)));
		}
	}

	/**
	 * @param string|array $value
	 * @return void
	 */
	public function setKeywords($value)
	{
		if (is_array($value))
		{
			$value = array_filter($value, [__CLASS__, 'clearValues'], ARRAY_FILTER_USE_BOTH);
			$value = implode(', ', $value);
		}
		if (is_string($value))
		{
			$this->setProperty(self::KEYWORDS, strip_tags(trim($value)));
		}
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setDescription($value)
	{
		if (is_string($value))
		{
			$this->setProperty(self::DESCRIPTION, strip_tags(trim($value)));
		}
	}

	/**
	 * @return string|null
	 */
	public function getTitle()
	{
		return $this->getProperty(self::TITLE);
	}

	/**
	 * @return string|null
	 */
	public function getBrowserTitle()
	{
		return $this->getProperty(self::BROWSER_TITLE);
	}

	/**
	 * @return string|null
	 */
	public function getKeywords()
	{
		return $this->getProperty(self::KEYWORDS);
	}

	/**
	 * @return string|null
	 */
	public function getDescription()
	{
		return $this->getProperty(self::DESCRIPTION);
	}

	/**
	 * @param string $value
	 * @param mixed $name
	 * @return bool
	 */
	protected static function clearValues($value, $name)
	{
		return ((string)$name !== '' && $value !== null);
	}
}