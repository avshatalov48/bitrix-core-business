<?php

namespace Bitrix\Rest\Engine\ActionFilter;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Controller;

/**
 * Class Base
 * @package Bitrix\Rest\Engine\ActionFilter
 */
abstract class Base extends Engine\ActionFilter\Base
{
	/**
	 * List allowed values of scopes where the filter should work.
	 * @return array
	 */
	public function listAllowedScopes()
	{
		return [
			Controller::SCOPE_REST
		];
	}

	/**
	 * @return null|\CRestServer
	 */
	protected function getRestServer()
	{
		$restServer = null;
		$sourceParametersList = $this->getAction()->getController()->getSourceParametersList();
		foreach ($sourceParametersList as $list)
		{
			foreach ($list as $name => $parameter)
			{
				if ($parameter instanceof \CRestServer)
				{
					$restServer = $parameter;
					break;
				}
			}
		}

		return $restServer;
	}
}