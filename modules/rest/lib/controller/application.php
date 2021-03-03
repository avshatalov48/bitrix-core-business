<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Marketplace;

class Application extends Controller
{
	public function installAction($code, $version = false, $checkHash = false, $installHash = false, $from = null)
	{
		return Marketplace\Application::install($code, $version, $checkHash, $installHash, $from);
	}

	public function uninstallAction($code, $clean = 'N', $from = null)
	{
		return Marketplace\Application::uninstall($code, ($clean === 'Y'), $from);
	}

	public function reinstallAction($id)
	{
		return Marketplace\Application::reinstall($id);
	}

	public function setRightsAction($appId, $rights)
	{
		return Marketplace\Application::setRights($appId, $rights);
	}

	public function getRightsAction($appId)
	{
		return Marketplace\Application::getRights($appId);
	}
}