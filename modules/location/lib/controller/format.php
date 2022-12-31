<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Service;
use Bitrix\Main\Engine\ActionFilter\Cors;
use \Bitrix\Location\Entity;

/**
 * Class Format
 * @package Bitrix\Location\Controller
 * Facade
 */
class Format extends \Bitrix\Main\Engine\Controller
{
	protected function init()
	{
		parent::init();
		ErrorService::getInstance()->setThrowExceptionOnError(true);
	}

	protected function getDefaultPreFilters()
	{
		return [];
	}

	public function findByCodeAction(string $formatCode, string $languageId)
	{
		$result = [];

		if($format = Service\FormatService::getInstance()->findByCode($formatCode, $languageId))
		{
			$result = Entity\Format\Converter\ArrayConverter::convertToArray($format);
		}

		return $result;
	}

	public function findAllAction(string $languageId)
	{
		$result = [];

		foreach(Service\FormatService::getInstance()->findAll($languageId) as $format)
		{
			$result[] = Entity\Format\Converter\ArrayConverter::convertToArray($format);
		}

		return $result;
	}

	public function findDefaultAction(string $languageId)
	{
		$result = null;

		if($format = Service\FormatService::getInstance()->findDefault($languageId))
		{
			$result = Entity\Format\Converter\ArrayConverter::convertToArray($format);
		}

		return $result;
	}
}
