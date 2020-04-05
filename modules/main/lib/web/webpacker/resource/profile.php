<?php

namespace Bitrix\Main\Web\WebPacker\Resource;

/**
 * Class Profile
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Profile
{
	const WEBPACKER = 'webpacker';
	const USE_LANG_CAMEL_CASE = 'useLangCamelCase';
	const USE_ALL_LANGS = 'useAllLangs';
	const DELETE_LANG_PREFIXES = 'deleteLangPrefixes';
	const CALL_METHOD = 'callMethod';
	const PROPERTIES = 'properties';

	protected $callMethod = '';
	protected $callParameter = [];
	protected $properties = [];
	protected $useLangCamelCase = false;
	protected $useAllLangs = false;
	protected $language = null;
	protected $deleteLangPrefixes = [];

	/**
	 * Profile constructor.
	 *
	 * @param string $callMethod Call method.
	 * @param null|array $callParameter Call parameter.
	 */
	public function __construct($callMethod = null, array $callParameter = null)
	{
		if ($callMethod)
		{
			$this->setCallMethod($callMethod);
		}
		if ($callParameter)
		{
			$this->setCallParameter($callParameter);
		}
	}

	/**
	 * Add property.
	 *
	 * @param string $name Name.
	 * @param mixed $value Value.
	 * @return $this
	 */
	public function setProperty($name, $value)
	{
		if ($value === null || $value === '')
		{
			unset($this->properties[$name]);
		}
		else
		{
			$this->properties[$name] = $value;
		}

		return $this;
	}

	/**
	 * Get property.
	 *
	 * @param string $code Code.
	 * @return mixed
	 */
	public function getProperty($code)
	{
		return isset($this->properties[$code]) ? $this->properties[$code] : null;
	}

	/**
	 * Get properties.
	 *
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * Get call method.
	 *
	 * @return string|null
	 */
	public function getCallMethod()
	{
		return $this->callMethod;
	}

	/**
	 * Get call parameter.
	 *
	 * @return array
	 */
	public function getCallParameter()
	{
		return $this->callParameter;
	}

	/**
	 * Set call method.
	 *
	 * @param string $callMethod Call method.
	 * @return $this
	 */
	public function setCallMethod($callMethod)
	{
		$this->callMethod = $callMethod;
		return $this;
	}

	/**
	 * Set parameter.
	 *
	 * @param array $callParameter Call parameter.
	 * @return $this
	 */
	public function setCallParameter(array $callParameter)
	{
		$this->callParameter = $callParameter;
		return $this;
	}

	/**
	 * Use lang camel case.
	 *
	 * @param bool $use Use.
	 * @return $this
	 */
	public function useLangCamelCase($use)
	{
		$this->useLangCamelCase = (bool) $use;
		return $this;
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
	 * Delete lang prefixes.
	 *
	 * @param array $prefixes Prefixes.
	 * @return $this
	 */
	public function deleteLangPrefixes($prefixes)
	{
		$this->deleteLangPrefixes = $prefixes;
		return $this;
	}

	/**
	 * Return true if lang camel case uses.
	 *
	 * @return bool
	 */
	public function isLangCamelCase()
	{
		return $this->useLangCamelCase;
	}

	/**
	 * Return true if all langs uses.
	 *
	 * @return bool
	 */
	public function isAllLangs()
	{
		return $this->useAllLangs;
	}

	/**
	 * Get language.
	 *
	 * @return string|null
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Set language.
	 *
	 * @param string|null $language
	 * @return $this
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * Return true if lang prefixes will delete.
	 *
	 * @return array
	 */
	public function getDeleteLangPrefixes()
	{
		return $this->deleteLangPrefixes;
	}
}