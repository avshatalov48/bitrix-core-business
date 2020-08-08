<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Mail\Internals\Entity\UserSignature;
use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main\Engine\Binder;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Text\StringHelper;

abstract class Base extends Controller
{
	protected function init()
	{
		parent::init();

		Binder::registerParameterDependsOnName(
			UserSignature::class,
			function($className, $id)
			{
				return UserSignatureTable::getById($id)->fetchObject();
			}
		);
	}

	protected function sanitize($text)
	{
		$text = preg_replace('/<!--.*?-->/is', '', $text);
		$text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $text);
		$text = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $text);

		$sanitizer = new \CBXSanitizer();
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
		$sanitizer->applyDoubleEncode(false);
		$sanitizer->addTags(array('style' => array()));

		return $sanitizer->sanitizeHtml($text);
	}

	/**
	 * @param array $array
	 * @param int $levels
	 * @param int $currentLevel
	 * @return array
	 */
	protected function convertArrayKeysToCamel(array $array, $levels = 0, $currentLevel = 0)
	{
		$result = [];
		foreach($array as $key => $value)
		{
			if($levels > 0 && is_array($value) && $currentLevel < $levels)
			{
				$currentLevel++;
				$value = $this->convertArrayKeysToCamel($value, $levels, $currentLevel);
				$currentLevel--;
			}
			$result[$this->toCamelCase($key)] = $value;
		}

		return $result;
	}

	/**
	 * @param array $array
	 * @param int $levels
	 * @param int $currentLevel
	 * @return array
	 */
	protected function convertArrayKeysToUpper(array $array, $levels = 0, $currentLevel = 0)
	{
		$result = [];
		foreach($array as $key => $value)
		{
			if($levels > 0 && is_array($value) && $currentLevel < $levels)
			{
				$currentLevel++;
				$value = $this->convertArrayKeysToUpper($value, $levels, $currentLevel);
				$currentLevel--;
			}
			$result[$this->toUpperCase($key)] = $value;
		}

		return $result;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function toCamelCase($string)
	{
		if(is_numeric($string))
		{
			return $string;
		}

		return lcfirst(StringHelper::snake2camel($string));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function toUpperCase($string)
	{
		if(is_numeric($string))
		{
			return $string;
		}

		return mb_strtoupper(StringHelper::camel2snake($string));
	}
}