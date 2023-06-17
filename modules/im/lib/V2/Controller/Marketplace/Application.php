<?php

namespace Bitrix\Im\V2\Controller\Marketplace;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class Application extends Controller
{
	protected function processBeforeAction(Action $action): bool
	{
		if (!Loader::includeModule('rest'))
		{
			$this->addError(new Error('Module Rest is not installed.'));

			return false;
		}

		return true;
	}

	/**
	 * @restMethod im.v2.Marketplace.Application.list
	 */
	public function listAction(?\CRestServer $server = null): ?array
	{
		if ($server && $server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE && !$this->isDebugEnabled())
		{
			$this->addError(new \Bitrix\Main\Error(
				"Access access to test method available only for session authorization.",
				"WRONG_AUTH_TYPE"
			));

			return null;
		}
		return (new \Bitrix\Im\V2\Marketplace\Application())->toRestFormat();
	}

	/**
	 * @restMethod im.v2.Marketplace.Application.update
	 */
	public function updateAction(int $id, array $params, ?\CRestServer $server = null): ?bool
	{
		if ($server && $server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE && !$this->isDebugEnabled())
		{
			$this->addError(new \Bitrix\Main\Error(
				"Access access to test method available only for session authorization.",
				"WRONG_AUTH_TYPE"
			));

			return null;
		}

		$params = array_change_key_case($params, CASE_UPPER);

		if (isset($params['ORDER']))
		{
			$application = new \Bitrix\Im\V2\Marketplace\Application();
			$result = $application->setOrder($id, (int)$params['ORDER']);
			$this->addErrors($result->getErrors());
		}

		return true;
	}

	private function isDebugEnabled(): bool
	{
		$settings = \Bitrix\Main\Config\Configuration::getValue('im');

		return $settings['rest_debug'] === true;
	}
}