<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Service;
use Bitrix\Main\Engine\ActionFilter\Cors;

/**
 * Class Source
 * @package Bitrix\Location\Controller
 * Facade
 */
class Source extends \Bitrix\Main\Engine\Controller
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

	public function getPropsAction(): array
	{
		$sourceCode = '';
		$sourceParams = [];

		if($source = Service\SourceService::getInstance()->getSource())
		{
			$sourceCode = $source->getCode();
			$sourceParams = $source->getJSParams();
		}

		return [
			'sourceCode' => $sourceCode,
			'sourceParams' => $sourceParams
		];
	}
}
