<?php

namespace Bitrix\Main\Annotations;

use Bitrix\Main\SystemException;

class AnnotationReader
{
	const RULE_FIRST_CAPITAL_LETTER = 0x001;

	protected $collectRules = self::RULE_FIRST_CAPITAL_LETTER;

	/**
	 * AnnotationReader constructor.
	 */
	public function __construct()
	{
		if (
			extension_loaded('Zend Optimizer+') &&
			(ini_get('zend_optimizerplus.save_comments') === "0" || ini_get('opcache.save_comments') === "0"))
		{
			throw new SystemException( "You have to enable opcache.save_comments=1 or zend_optimizerplus.save_comments=1.");
		}

		if (extension_loaded('Zend OPcache') && ini_get('opcache.save_comments') == 0)
		{
			throw new SystemException( "You have to enable opcache.save_comments=1 or zend_optimizerplus.save_comments=1.");
		}
	}

	public function getMethodAnnotations(\ReflectionMethod $method)
	{
		$doc = $method->getDocComment();

		preg_match_all("/@(?=(.*)[ ]*(?:@|\r\n|\n))/U", $doc, $matches);

		if (!$matches)
		{
			return null;
		}

		$annotations = array();
		foreach ($matches[1] as $match)
		{
			if ($this->collectRules & self::RULE_FIRST_CAPITAL_LETTER)
			{
				if ($match !== ucfirst($match))
				{
					continue;
				}
			}

			$annotations[] = $match;
		}

		$parameters = array();
		foreach ($annotations as $annotation)
		{
			preg_match("/(\w+)(?:\((.*)\))?/", $annotation, $matches);
			if ($matches)
			{
				$parameters[$matches[1]] = $this->extractParameters($matches[2]);
			}
		}

		return $parameters;
	}

	private function extractParameters($string)
	{
		if (!$string)
		{
			return null;
		}

		$parameters = array();

		$parts = preg_split("/(\w+)\=([.^\=]*)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		for ($i = 0; $i < count($parts); $i++)
		{
			//there is key by even number and value is by the next odd number
			if ($i % 2 === 0)
			{
				$rawValue = trim($parts[$i + 1], ', ');

				$parameters[$parts[$i]] = $rawValue;
			}
		}

		foreach ($parameters as $name => &$rawValue)
		{
			$rawValue = trim($rawValue);
			$rawValue = $this->extractParameter($rawValue);
		}

		return $parameters;
	}

	private function extractParameter($valueInString)
	{
		if (!$valueInString)
		{
			return null;
		}

		$value = null;

		if ($valueInString === 'false')
		{
			$value = false;
		}
		elseif ($valueInString === 'true')
		{
			$value = true;
		}
		elseif (is_numeric($valueInString))
		{
			if ($valueInString === (string)(int)$valueInString)
			{
				$value = (int)$valueInString;
			}
			else
			{
				$value = (float)$valueInString;
			}
		}
		elseif (str_starts_with($valueInString, '[') && str_ends_with($valueInString, ']'))
		{
			$list = array();
			$valueInString = mb_substr($valueInString, 1, -1);
			foreach (explode(',', $valueInString) as $listValue)
			{
				$listValue = trim($listValue);
				if (!$listValue)
				{
					continue;
				}

				$list[] = $this->extractParameter($listValue);
			}

			$value = $list;
		}
		else
		{
			$value = trim($valueInString, '"');
		}

		return $value;
	}
}