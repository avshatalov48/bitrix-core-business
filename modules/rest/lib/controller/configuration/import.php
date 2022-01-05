<?php

namespace Bitrix\Rest\Controller\Configuration;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Rest\Engine\ActionFilter\AuthType;
use Bitrix\Rest\Configuration\Action;
use Bitrix\Rest\OAuth\Auth;
use CRestServer;

class Import extends Controller
{
	/**
	 * Registers import action
	 * @param array $data
	 * @param array $option
	 *
	 * @return array
	 */
	public function registerAction(array $data, array $option = [], CRestServer $server = null)
	{
		$import = new Action\Import();
		$userId = 0;
		$appCode = '';
		if ($server instanceof CRestServer && $server->getAuthType() === Auth::AUTH_TYPE)
		{
			$appCode = $server->getClientId();
			$auth = $server->getAuthData();
			if (!empty($auth['user_id']))
			{
				$userId = (int)$auth['user_id'];
			}
		}

		if ($userId === 0)
		{
			global $USER;
			$userId = $USER->getId();
		}

		return $import->register($data, $option, $userId, $appCode);
	}

	/**
	 * Unregisters import action
	 * @param $processId
	 *
	 * @return array
	 */
	public function unregisterAction($processId): array
	{
		$import = new Action\Import($processId);
		return $import->unregister();
	}

	/**
	 * Returns information about import
	 * @param $processId
	 *
	 * @return array
	 */
	public function getAction($processId): array
	{
		$import = new Action\Import($processId);
		return $import->get();
	}

	/**
	 * @return array
	 */
	public function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\Scope(ActionFilter\Scope::REST),
			new AuthType(AuthType::APPLICATION || AuthType::PASSWORD),
		];
	}
}
